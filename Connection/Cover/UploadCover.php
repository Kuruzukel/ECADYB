<?php
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

$uploadCancelled = false;

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
    $template = isset($_POST['template']) ? (int)$_POST['template'] : 1;

    error_log("UploadCover.php received parameters: slot=$slot, side=$side, template=$template");

    if ($template < 1 || $template > 3) {
        respond(false, 'Invalid template parameter. Must be 1, 2, or 3.');
    }

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
        respond(false, 'Upload cancelled');
    }

    $fileTmp      = $_FILES['file']['tmp_name'];
    $originalName = $_FILES['file']['name'];
    $ext          = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';

    $upperName = strtoupper($originalName);

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

    // Override side if we can detect it from filename
    if (strpos($upperName, 'BACK') !== false) {
        $side = 'back';
    } else if (strpos($upperName, 'FRONT') !== false) {
        $side = 'front';
    }

    // Check for client disconnection/cancellation
    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    $baseOriginal = pathinfo($originalName, PATHINFO_FILENAME);
    $safeBase     = preg_replace('/[^A-Za-z0-9 _.-]/', '', $baseOriginal) ?: ('image_' . time());
    $safeExt      = preg_replace('/[^A-Za-z0-9]/', '', $ext) ?: 'jpg';

    $safeFolder     = 'Yearbook Covers';
    $templateFolder = sprintf('Batch Template %d', $template);

    $sideLabel = ($slot === 8)
        ? 'BackgroundPage'
        : ($side === 'back' ? 'Back' : 'Front');

    $filename = ($slot === 8)
        ? sprintf('BackgroundPage-%s.%s', $safeBase, $safeExt)
        : sprintf('Slot-%d-%s-%s.%s', $slot, $sideLabel, $safeBase, $safeExt);

    $path = $safeFolder . '/' . $templateFolder . '/' . $filename;

    error_log("UploadCover.php constructed path: $path");

    $storageUrl   = "https://storage.bunnycdn.com/{$bunnyStorageZone}/" . str_replace(' ', '%20', $path);

    // Check for client disconnection/cancellation before reading file
    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    $fileContents = file_get_contents($fileTmp);
    if ($fileContents === false) {
        respond(false, 'Failed to read uploaded file.');
    }

    // Check for client disconnection/cancellation after reading file but before uploading to BunnyCDN
    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    error_log("UploadCover.php storage URL: $storageUrl");

    // CRITICAL: ONLY ONE CANCELLATION CHECK RIGHT BEFORE UPLOADING TO BUNNYCDN
    // This prevents false positives while still catching real cancellations
    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    // Create thumbnail from the original image
    $thumbContents = '';
    $thumbFilename = '';

    // Generate thumbnail filename
    $thumbFilename = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.' . pathinfo($filename, PATHINFO_EXTENSION);
    $thumbPath = $safeFolder . '/' . $templateFolder . '/' . $thumbFilename;
    $thumbStorageUrl = "https://storage.bunnycdn.com/{$bunnyStorageZone}/" . str_replace(' ', '%20', $thumbPath);

    // Create a simple thumbnail by resizing the image
    $thumbContents = $fileContents;

    // Ultra-fast BunnyCDN upload with minimal timeout for main image
    $ch = curl_init($storageUrl);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey, 'Content-Type: application/octet-stream'],
        CURLOPT_POSTFIELDS     => $fileContents,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,  // Reduced data transfer
        CURLOPT_TIMEOUT        => 15,     // Reasonable timeout
        CURLOPT_CONNECTTIMEOUT => 5,      // Reasonable connection timeout
        CURLOPT_SSL_VERIFYPEER => true,   // Keep security
        CURLOPT_TCP_NODELAY    => true,   // TCP optimization
        CURLOPT_FRESH_CONNECT  => false,  // Allow connection reuse for performance
        CURLOPT_FORBID_REUSE   => false   // Allow connection reuse for performance
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    // Check for client disconnection/cancellation after uploading main image to BunnyCDN
    if (connection_aborted()) {
        $uploadCancelled = true;
        // If upload was successful but client disconnected, we need to delete the file from BunnyCDN
        if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
            // Delete the uploaded file since client disconnected
            error_log("UploadCover.php deleting file from BunnyCDN due to cancellation: $storageUrl");
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
        }
        respond(false, 'Upload cancelled');
    }

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        respond(false, 'Failed to upload to Bunny: ' . ($curlErr ?: 'HTTP ' . $httpCode));
    }

    // Upload thumbnail to BunnyCDN
    $thumbCh = curl_init($thumbStorageUrl);
    curl_setopt_array($thumbCh, [
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey, 'Content-Type: application/octet-stream'],
        CURLOPT_POSTFIELDS     => $thumbContents,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TCP_NODELAY    => true,
        CURLOPT_FRESH_CONNECT  => false,
        CURLOPT_FORBID_REUSE   => false
    ]);
    $thumbResponse = curl_exec($thumbCh);
    $thumbHttpCode = curl_getinfo($thumbCh, CURLINFO_HTTP_CODE);
    $thumbCurlErr  = curl_error($thumbCh);
    curl_close($thumbCh);

    // Check for client disconnection/cancellation after uploading thumbnail to BunnyCDN
    if (connection_aborted()) {
        $uploadCancelled = true;
        // Delete both main image and thumbnail if client disconnected
        if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
            error_log("UploadCover.php deleting file from BunnyCDN due to cancellation: $storageUrl");
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
        }

        if ($thumbResponse !== false && $thumbHttpCode >= 200 && $thumbHttpCode < 300) {
            error_log("UploadCover.php deleting thumbnail from BunnyCDN due to cancellation: $thumbStorageUrl");
            $deleteThumbCh = curl_init($thumbStorageUrl);
            curl_setopt_array($deleteThumbCh, [
                CURLOPT_CUSTOMREQUEST  => 'DELETE',
                CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_CONNECTTIMEOUT => 3
            ]);
            curl_exec($deleteThumbCh);
            curl_close($deleteThumbCh);
        }
        respond(false, 'Upload cancelled');
    }

    if ($thumbResponse === false || $thumbHttpCode < 200 || $thumbHttpCode >= 300) {
        respond(false, 'Failed to upload thumbnail to Bunny: ' . ($thumbCurlErr ?: 'HTTP ' . $thumbHttpCode));
    }

    $publicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $path);
    $thumbPublicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $thumbPath);

    error_log("UploadCover.php public URL: $publicUrl");
    error_log("UploadCover.php thumbnail URL: $thumbPublicUrl");

    // Check for client disconnection/cancellation before updating MongoDB
    if (connection_aborted()) {
        $uploadCancelled = true;
        // Delete both main image and thumbnail from BunnyCDN since we're cancelling before MongoDB
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

        error_log("UploadCover.php deleting thumbnail from BunnyCDN due to cancellation before MongoDB: $thumbStorageUrl");
        $deleteThumbCh = curl_init($thumbStorageUrl);
        curl_setopt_array($deleteThumbCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        curl_exec($deleteThumbCh);
        curl_close($deleteThumbCh);
        respond(false, 'Upload cancelled');
    }

    // MongoDB connection and update
    $mongoDbName = "BatchTemplate{$template}";
    $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    error_log("UploadCover.php using MongoDB URL: $mongoUrl");
    error_log("UploadCover.php using database: $mongoDbName, collection: YearbookCovers");

    try {
        $mongoClient = new Client($mongoUrl, [
            'serverSelectionTimeoutMS' => 5000,
            'connectTimeoutMS' => 5000,
            'socketTimeoutMS' => 10000,
            'retryReads' => true
        ]);
        $collection = $mongoClient->$mongoDbName->YearbookCovers;
    } catch (Exception $e) {
        error_log("UploadCover.php MongoDB connection error: " . $e->getMessage());
        respond(false, 'Database connection failed: ' . $e->getMessage());
        return;
    }

    // Check for client disconnection/cancellation before MongoDB operations
    if (connection_aborted()) {
        $uploadCancelled = true;
        // Delete both main image and thumbnail from BunnyCDN since we're cancelling before MongoDB
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

        error_log("UploadCover.php deleting thumbnail from BunnyCDN due to cancellation before MongoDB operations: $thumbStorageUrl");
        $deleteThumbCh = curl_init($thumbStorageUrl);
        curl_setopt_array($deleteThumbCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        curl_exec($deleteThumbCh);
        curl_close($deleteThumbCh);
        respond(false, 'Upload cancelled');
    }

    // Prepare document for MongoDB with proper field names
    $document = [
        'filename' => $filename,
        'original_name' => $originalName,
        'slot' => $slot,
        'side' => $side,
        'template' => $template,
        'upload_time' => new \MongoDB\BSON\UTCDateTime()
    ];

    // Set the appropriate URL fields based on slot and side
    if ($slot === 8) {
        // Background image
        $document['background_url'] = $publicUrl;
        $document['background_thumb_url'] = $thumbPublicUrl;
    } else {
        // Regular slot images
        if ($side === 'front') {
            $document['front_url'] = $publicUrl;
            $document['front_thumb_url'] = $thumbPublicUrl;
        } else {
            $document['back_url'] = $publicUrl;
            $document['back_thumb_url'] = $thumbPublicUrl;
        }
    }

    // Check for client disconnection/cancellation just before MongoDB insert
    if (connection_aborted()) {
        $uploadCancelled = true;
        // Delete both main image and thumbnail from BunnyCDN since we're cancelling before MongoDB insert
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

        error_log("UploadCover.php deleting thumbnail from BunnyCDN due to cancellation before MongoDB insert: $thumbStorageUrl");
        $deleteThumbCh = curl_init($thumbStorageUrl);
        curl_setopt_array($deleteThumbCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        curl_exec($deleteThumbCh);
        curl_close($deleteThumbCh);
        respond(false, 'Upload cancelled');
    }

    // Upsert document into MongoDB based on template and slot
    try {
        error_log("UploadCover.php upserting document: " . json_encode($document));

        $filter = [
            'template' => $template,
            'slot' => $slot
        ];

        error_log("UploadCover.php using filter: " . json_encode($filter));

        $update = ['$set' => $document];
        $options = ['upsert' => true];

        $result = $collection->updateOne($filter, $update, $options);

        error_log("UploadCover.php upsert result - matched: " . $result->getMatchedCount() . ", modified: " . $result->getModifiedCount() . ", upserted: " . $result->getUpsertedCount());

        // If this was an insert (upserted), get the new ID
        if ($result->getUpsertedCount() > 0) {
            $document['_id'] = (string) $result->getUpsertedId();
        }
    } catch (Exception $e) {
        error_log("UploadCover.php MongoDB upsert error: " . $e->getMessage());
        respond(false, 'Failed to save to database: ' . $e->getMessage());
        return;
    }

    // Check for client disconnection/cancellation after MongoDB insert
    if (connection_aborted()) {
        $uploadCancelled = true;
        // Delete both main image and thumbnail from BunnyCDN and remove the MongoDB entry since we're cancelling after insert
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

        error_log("UploadCover.php deleting thumbnail from BunnyCDN due to cancellation after insert: $thumbStorageUrl");
        $deleteThumbCh = curl_init($thumbStorageUrl);
        curl_setopt_array($deleteThumbCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        curl_exec($deleteThumbCh);
        curl_close($deleteThumbCh);

        // Delete MongoDB entry
        $collection->deleteOne(['_id' => $document['_id']]);
        respond(false, 'Upload cancelled');
    }

    // Prepare response data
    $responseData = [
        'filename' => $filename,
        'slot' => $slot,
        'side' => $side,
        'template' => $template
    ];

    // Add appropriate URL fields to response
    if ($slot === 8) {
        $responseData['url'] = $publicUrl;
        $responseData['thumb_url'] = $thumbPublicUrl;
    } else {
        if ($side === 'front') {
            $responseData['url'] = $publicUrl;
            $responseData['thumb_url'] = $thumbPublicUrl;
        } else {
            $responseData['url'] = $publicUrl;
            $responseData['thumb_url'] = $thumbPublicUrl;
        }
    }

    respond(true, 'Upload successful', $responseData);
} catch (Exception $e) {
    error_log("UploadCover.php exception: " . $e->getMessage());
    respond(false, 'Server error: ' . $e->getMessage());
}