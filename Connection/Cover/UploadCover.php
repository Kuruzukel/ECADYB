<?php
set_time_limit(60);
ini_set('max_execution_time', 60);

ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$uploadCancelled = false;
$uploadedFileInfo = null;

register_shutdown_function(function () {
    global $uploadCancelled, $uploadedFileInfo;

    if (connection_aborted() && $uploadedFileInfo !== null) {
        error_log("UploadCover.php: Shutdown function detected connection abort - cleaning up file: " . $uploadedFileInfo['storageUrl']);

        $deleteCh = curl_init($uploadedFileInfo['storageUrl']);
        curl_setopt_array($deleteCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $uploadedFileInfo['bunnyAccessKey']],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        curl_exec($deleteCh);
        curl_close($deleteCh);

        if (isset($uploadedFileInfo['mongoCollection']) && isset($uploadedFileInfo['filter'])) {
            try {
                $deleteResult = $uploadedFileInfo['mongoCollection']->deleteOne($uploadedFileInfo['filter']);
                error_log("UploadCover.php: Shutdown cleanup - deleted " . $deleteResult->getDeletedCount() . " MongoDB documents");
            } catch (Exception $e) {
                error_log("UploadCover.php: Shutdown cleanup - MongoDB deletion error: " . $e->getMessage());
            }
        }

        error_log("UploadCover.php: Shutdown cleanup completed");
    }
});

function respond($success, $message = '', $data = [])
{
    global $uploadCancelled;

    while (ob_get_level()) {
        ob_end_clean();
    }

    if ($uploadCancelled && $success) {
        $success = false;
        $message = 'Upload cancelled';
    }

    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

if (connection_aborted()) {
    $uploadCancelled = true;
    respond(false, 'Upload cancelled');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method. Use POST.');
}

require __DIR__ . '/../../vendor/autoload.php';
if (file_exists(__DIR__ . '/../Configuration/BunnyConfig.php')) {
    require __DIR__ . '/../Configuration/BunnyConfig.php';
}

use MongoDB\Client;

try {
    $bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE')
        ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
    $bunnyAccessKey = getenv('BUNNY_ACCESS_KEY')
        ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));
    $bunnyCdnHost = getenv('BUNNY_CDN_HOST')
        ?: (defined('BUNNY_CDN_HOST') ? BUNNY_CDN_HOST : ($GLOBALS['BUNNY_CDN_HOST'] ?? 'https://ECADYB.b-cdn.net'));

    if (!$bunnyStorageZone || !$bunnyAccessKey || !$bunnyCdnHost) {
        respond(false, 'Bunny configuration missing. Please check environment variables.');
    }

    $slot     = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
    $side     = isset($_POST['side']) ? strtolower(trim($_POST['side'])) : '';
    $batchYear = isset($_POST['batch_year']) ? trim($_POST['batch_year']) : '';
    $template = isset($_POST['template']) ? (int)$_POST['template'] : 1;

    error_log("UploadCover.php received parameters: slot=$slot, side=$side, batch_year=$batchYear, template=$template");

    if ($slot === null || ($slot !== 8 && ($side !== 'front' && $side !== 'back'))) {
        respond(false, 'Invalid parameters: slot and side (front|back) are required, unless slot=8 (BackgroundPage).');
    }

    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorMap = [
            UPLOAD_ERR_INI_SIZE   => 'File too large (php.ini limit).',
            UPLOAD_ERR_FORM_SIZE  => 'File too large (form limit).',
            UPLOAD_ERR_PARTIAL    => 'File upload was incomplete.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'Upload stopped by extension.'
        ];
        $code = $_FILES['file']['error'] ?? null;
        respond(false, $errorMap[$code] ?? 'Upload failed.');
    }

    if (connection_aborted()) {
        $uploadCancelled = true;
        error_log("UploadCover.php connection aborted immediately after receiving file");
        respond(false, 'Upload cancelled');
    }

    $fileTmp      = $_FILES['file']['tmp_name'];
    $originalName = $_FILES['file']['name'];
    $ext          = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';

    $upperName = strtoupper($originalName);

    if ($slot !== 8) {
        $slotMapping = [
            'BSME' => 1,
            'BSCJ' => 2,
            'BSTM' => 3,
            'BSE' => 4,
            'BSN' => 5,
            'BSIS' => 6,
            'BSBA' => 7
        ];

        $detectedSlot = null;
        foreach ($slotMapping as $prefix => $slotNum) {
            if (strpos($upperName, $prefix) === 0) {
                $detectedSlot = $slotNum;
                break;
            }
        }

        if ($detectedSlot !== null && $detectedSlot != $slot) {
            respond(false, "Upload cancelled: Filename prefix doesn't match the selected slot. Expected slot $detectedSlot for this filename.");
        }

        if ($slot >= 1 && $slot <= 7 && $detectedSlot === null) {
            respond(false, "Upload cancelled: Filename must start with a valid prefix (BSME, BSCJ, BSTM, BSE, BSN, BSIS, BSBA).");
        }
    }

    if ($slot !== 8) {
        if (strpos($upperName, 'BACK') !== false) {
            $side = 'back';
        } else if (strpos($upperName, 'FRONT') !== false) {
            $side = 'front';
        }
    }

    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    $baseOriginal = pathinfo($originalName, PATHINFO_FILENAME);
    $safeBase     = preg_replace('/[^A-Za-z0-9 _.-]/', '', $baseOriginal) ?: ('image_' . time());
    $safeExt      = preg_replace('/[^A-Za-z0-9]/', '', $ext) ?: 'jpg';
    $versionToken = (string) round(microtime(true) * 1000);

    $safeBatchYear = $batchYear ? preg_replace('/[^A-Za-z0-9-]/', '', $batchYear) : 'Default';
    $safeFolder = 'Yearbook Covers/' . $safeBatchYear;

    $sideLabel = ($slot === 8)
        ? 'BackgroundPage'
        : ($side === 'back' ? 'Back' : 'Front');

    $filename = ($slot === 8)
        ? sprintf('BackgroundPage-%s-%s.%s', $safeBase, $versionToken, $safeExt)
        : sprintf('Slot-%d-%s-%s-%s.%s', $slot, $sideLabel, $safeBase, $versionToken, $safeExt);

    $path = $safeFolder . '/' . $filename;

    error_log("UploadCover.php constructed path: $path");

    $storageUrl   = "https://storage.bunnycdn.com/{$bunnyStorageZone}/" . str_replace(' ', '%20', $path);

    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    $fileContents = file_get_contents($fileTmp);
    if ($fileContents === false) {
        respond(false, 'Failed to read uploaded file.');
    }

    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    error_log("UploadCover.php storage URL: $storageUrl");

    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    $ch = curl_init($storageUrl);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey, 'Content-Type: application/octet-stream'],
        CURLOPT_POSTFIELDS     => $fileContents,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_TIMEOUT        => 45,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TCP_NODELAY    => true,
        CURLOPT_FRESH_CONNECT  => false,
        CURLOPT_FORBID_REUSE   => false,
        CURLOPT_NOPROGRESS     => false,
        CURLOPT_PROGRESSFUNCTION => function ($resource, $download_size, $downloaded, $upload_size, $uploaded) {
            if (connection_aborted()) {
                error_log("UploadCover.php upload cancelled during progress - aborting curl");
                return -1;
            }
            return 0;
        }
    ]);

    if (connection_aborted()) {
        curl_close($ch);
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);

    if ($curlErrno === 42) {
        error_log("UploadCover.php curl aborted by progress callback");
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    if (connection_aborted()) {
        $uploadCancelled = true;
        if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
            error_log("UploadCover.php deleting file from BunnyCDN due to cancellation: $storageUrl");
            $deleteCh = curl_init($storageUrl);
            curl_setopt_array($deleteCh, [
                CURLOPT_CUSTOMREQUEST  => 'DELETE',
                CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => false,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_CONNECTTIMEOUT => 3
            ]);
            curl_exec($deleteCh);
            curl_close($deleteCh);
        }
        respond(false, 'Upload cancelled');
    }

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        respond(false, 'Failed to upload to Bunny: ' . ($curlErr ?: 'HTTP ' . $httpCode));
    }

    // Track uploaded file for potential cleanup
    global $uploadedFileInfo;
    $uploadedFileInfo = [
        'storageUrl' => $storageUrl,
        'bunnyAccessKey' => $bunnyAccessKey,
        'filename' => $filename
    ];

    $publicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $path);

    error_log("UploadCover.php public URL: $publicUrl");

    if (connection_aborted()) {
        $uploadCancelled = true;
        error_log("UploadCover.php deleting file from BunnyCDN due to cancellation before MongoDB: $storageUrl");
        $deleteCh = curl_init($storageUrl);
        curl_setopt_array($deleteCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        curl_exec($deleteCh);
        curl_close($deleteCh);

        respond(false, 'Upload cancelled');
    }

    $mongoDbName = "ECADYB";
    $mongoUrl = getenv('MONGO_URL') ?: getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    error_log("UploadCover.php using MongoDB URL: $mongoUrl");
    error_log("UploadCover.php using database: $mongoDbName, collection: Covers");

    try {
        $mongoClient = new Client($mongoUrl, [
            'serverSelectionTimeoutMS' => 10000,
            'connectTimeoutMS' => 10000,
            'socketTimeoutMS' => 20000,
            'retryReads' => true
        ]);
        $collection = $mongoClient->$mongoDbName->Yearbook_Covers;
    } catch (Exception $e) {
        error_log("UploadCover.php MongoDB connection error: " . $e->getMessage());
        respond(false, 'Database connection failed: ' . $e->getMessage());
        return;
    }

    if (connection_aborted()) {
        $uploadCancelled = true;
        error_log("UploadCover.php deleting file from BunnyCDN due to cancellation before MongoDB operations: $storageUrl");
        $deleteCh = curl_init($storageUrl);
        curl_setopt_array($deleteCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        curl_exec($deleteCh);
        curl_close($deleteCh);

        respond(false, 'Upload cancelled');
    }

    $document = [
        'slot' => $slot,
        'batch_year' => $batchYear,
        'template' => $template,
        'upload_time' => new \MongoDB\BSON\UTCDateTime()
    ];

    // Check completion for all slots including background
    $checkCompletion = true;

    if ($slot === 8) {
        $document['background_url'] = $publicUrl;
        $document['background_filename'] = $filename;
        $document['background_original_name'] = $originalName;
        $document['background_side'] = 'background';
    } else {
        if ($side === 'front') {
            $document['front_url'] = $publicUrl;
            $document['front_filename'] = $filename;
            $document['front_original_name'] = $originalName;
            $document['front_side'] = $side;
        } else {
            $document['back_url'] = $publicUrl;
            $document['back_filename'] = $filename;
            $document['back_original_name'] = $originalName;
            $document['back_side'] = $side;
        }
    }

    if (connection_aborted()) {
        $uploadCancelled = true;
        error_log("UploadCover.php deleting file from BunnyCDN due to cancellation before MongoDB insert: $storageUrl");
        $deleteCh = curl_init($storageUrl);
        curl_setopt_array($deleteCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        curl_exec($deleteCh);
        curl_close($deleteCh);

        respond(false, 'Upload cancelled');
    }

    try {
        error_log("UploadCover.php upserting document: " . json_encode($document));

        $filter = [
            'slot' => $slot,
            'batch_year' => $batchYear,
            'template' => $template
        ];

        // Add MongoDB info to uploadedFileInfo for shutdown cleanup
        global $uploadedFileInfo;
        if ($uploadedFileInfo !== null) {
            $uploadedFileInfo['mongoCollection'] = $collection;
            $uploadedFileInfo['filter'] = $filter;
        }

        error_log("UploadCover.php using filter: " . json_encode($filter));

        $update = ['$set' => $document];
        $options = ['upsert' => true];

        $result = $collection->updateOne($filter, $update, $options);

        error_log("UploadCover.php upsert result - matched: " . $result->getMatchedCount() . ", modified: " . $result->getModifiedCount() . ", upserted: " . $result->getUpsertedCount());

        if ($result->getUpsertedCount() > 0) {
            $document['_id'] = (string) $result->getUpsertedId();
        }

        if ($checkCompletion) {
            $batchYearDocs = $collection->find(['batch_year' => $batchYear, 'template' => $template])->toArray();

            $slotsWithImages = [];
            foreach ($batchYearDocs as $doc) {
                $docSlot = (int)($doc['slot'] ?? 0);
                $hasFront = isset($doc['front_url']) && !empty($doc['front_url']);
                $hasBack = isset($doc['back_url']) && !empty($doc['back_url']);
                $hasBackground = isset($doc['background_url']) && !empty($doc['background_url']);

                if ($docSlot === 8 && $hasBackground) {
                    $slotsWithImages[] = 8;
                } elseif ($docSlot >= 1 && $docSlot <= 7 && ($hasFront && $hasBack)) {
                    $slotsWithImages[] = $docSlot;
                }
            }

            $slotsWithImages = array_unique($slotsWithImages);
            $isComplete = count($slotsWithImages) === 8;

            if ($isComplete) {
                $collection->updateMany(
                    ['batch_year' => $batchYear, 'template' => $template],
                    ['$set' => ['completion_date' => new \MongoDB\BSON\UTCDateTime((new DateTime())->modify('+3 years')->getTimestamp() * 1000)]],
                    ['upsert' => false]
                );
                error_log("UploadCover.php: Batch year $batchYear (template $template) is now complete!");
            } else {
                $collection->updateMany(
                    ['batch_year' => $batchYear, 'template' => $template],
                    ['$unset' => ['completion_date' => '']],
                    ['upsert' => false]
                );
                error_log("UploadCover.php: Batch year $batchYear (template $template) is incomplete. Slots filled: " . count($slotsWithImages) . "/8");
            }
        }
    } catch (Exception $e) {
        error_log("UploadCover.php MongoDB upsert error: " . $e->getMessage());
        respond(false, 'Failed to save to database: ' . $e->getMessage());
        return;
    }

    if (connection_aborted()) {
        $uploadCancelled = true;
        error_log("UploadCover.php deleting file from BunnyCDN and MongoDB entry due to cancellation after insert: $storageUrl");
        $deleteCh = curl_init($storageUrl);
        curl_setopt_array($deleteCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        curl_exec($deleteCh);
        curl_close($deleteCh);

        try {
            $deleteResult = $collection->deleteOne($filter);
            error_log("UploadCover.php deleted MongoDB entry: " . $deleteResult->getDeletedCount() . " documents");
        } catch (Exception $e) {
            error_log("UploadCover.php error deleting MongoDB entry: " . $e->getMessage());
        }

        respond(false, 'Upload cancelled');
    }

    $responseData = [
        'filename' => $filename,
        'slot' => $slot,
        'side' => $side
    ];

    if ($slot === 8) {
        $responseData['url'] = $publicUrl;
    } else {
        if ($side === 'front') {
            $responseData['url'] = $publicUrl;
        } else {
            $responseData['url'] = $publicUrl;
        }
    }

    global $uploadedFileInfo;
    $uploadedFileInfo = null;

    respond(true, 'Upload successful', $responseData);
} catch (Exception $e) {
    error_log("UploadCover.php exception: " . $e->getMessage());

    if (isset($storageUrl) && isset($bunnyAccessKey)) {
        error_log("UploadCover.php cleaning up file from BunnyCDN due to exception: $storageUrl");
        $deleteCh = curl_init($storageUrl);
        curl_setopt_array($deleteCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        curl_exec($deleteCh);
        curl_close($deleteCh);
        error_log("UploadCover.php cleaned up file: " . ($filename ?? 'unknown'));
    }

    respond(false, 'Server error: ' . $e->getMessage());
}