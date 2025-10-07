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

    $template = isset($_POST['template']) ? (int)$_POST['template'] : 1;

    error_log("UploadTopManagementPhotos.php received parameters: template=$template");

    if ($template < 1 || $template > 3) {
        respond(false, 'Invalid template parameter. Must be 1, 2, or 3.');
    }

    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    if (empty($_FILES) || !isset($_FILES['files'])) {
        respond(false, 'No files were uploaded.');
    }

    $uploadedFiles = $_FILES['files'];
    $uploadedCount = 0;
    $failedCount = 0;
    $results = [];

    for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
        if (connection_aborted()) {
            $uploadCancelled = true;
            respond(false, 'Upload cancelled');
        }

        $fileName = $uploadedFiles['name'][$i];
        $fileTmp = $uploadedFiles['tmp_name'][$i];
        $fileError = $uploadedFiles['error'][$i];

        if ($fileError !== UPLOAD_ERR_OK) {
            $errorMap = [
                UPLOAD_ERR_INI_SIZE   => 'File too large (php.ini limit).',
                UPLOAD_ERR_FORM_SIZE  => 'File too large (form limit).',
                UPLOAD_ERR_PARTIAL    => 'File upload was incomplete.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'Upload stopped by extension.'
            ];
            $results[] = [
                'filename' => $fileName,
                'success' => false,
                'message' => $errorMap[$fileError] ?? 'Upload failed.'
            ];
            $failedCount++;
            continue;
        }

        $nameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);

        if (empty($nameWithoutExt)) {
            $results[] = [
                'filename' => $fileName,
                'success' => false,
                'message' => 'Invalid filename. Filename cannot be empty.'
            ];
            $failedCount++;
            continue;
        }

        $fileContents = file_get_contents($fileTmp);
        if ($fileContents === false) {
            $results[] = [
                'filename' => $fileName,
                'success' => false,
                'message' => 'Failed to read uploaded file.'
            ];
            $failedCount++;
            continue;
        }

        $ext = pathinfo($fileName, PATHINFO_EXTENSION) ?: 'jpg';
        $safeFileName = preg_replace('/[^A-Za-z0-9 _.-]/', '', $nameWithoutExt) ?: ('top_management_' . time());
        $safeExt = preg_replace('/[^A-Za-z0-9]/', '', $ext) ?: 'jpg';

        $safeFolder = 'Top Management Photos';
        $templateFolder = sprintf('Batch Template %d', $template);
        $filename = sprintf('%s.%s', $safeFileName, $safeExt);
        $path = $safeFolder . '/' . $templateFolder . '/' . $filename;
        $storageUrl = "https://storage.bunnycdn.com/{$bunnyStorageZone}/" . str_replace(' ', '%20', $path);

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
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TCP_NODELAY    => true,
            CURLOPT_FRESH_CONNECT  => false,
            CURLOPT_FORBID_REUSE   => false
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if (connection_aborted()) {
            $uploadCancelled = true;
            if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                error_log("UploadTopManagementPhotos.php deleting file from BunnyCDN due to cancellation: $storageUrl");
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
            $results[] = [
                'filename' => $fileName,
                'success' => false,
                'message' => 'Failed to upload to Bunny: ' . ($curlErr ?: 'HTTP ' . $httpCode)
            ];
            $failedCount++;
            continue;
        }

        $publicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $path);

        if (connection_aborted()) {
            $uploadCancelled = true;
            error_log("UploadTopManagementPhotos.php deleting file from BunnyCDN due to cancellation before MongoDB: $storageUrl");
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

        $mongoDbName = "BatchTemplate" . $template;
        $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
        error_log("UploadTopManagementPhotos.php using MongoDB URL: $mongoUrl");
        error_log("UploadTopManagementPhotos.php using database: $mongoDbName, collection: top_management_photos");

        try {
            $mongoClient = new Client($mongoUrl, [
                'serverSelectionTimeoutMS' => 5000,
                'connectTimeoutMS' => 5000,
                'socketTimeoutMS' => 10000,
                'retryReads' => true
            ]);
            $collection = $mongoClient->$mongoDbName->top_management_photos;
        } catch (Exception $e) {
            error_log("UploadTopManagementPhotos.php MongoDB connection error: " . $e->getMessage());
            $results[] = [
                'filename' => $fileName,
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
            $failedCount++;

            error_log("UploadTopManagementPhotos.php deleting file from BunnyCDN due to MongoDB connection error: $storageUrl");
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
            continue;
        }

        try {
            $messageCollection = $mongoClient->$mongoDbName->top_management_message;

            $allNames = $messageCollection->distinct('name');
            error_log("Available names in top_management_message: " . implode(", ", $allNames));

            $messageDoc = $messageCollection->findOne(['name' => $nameWithoutExt]);

            if (!$messageDoc) {
                $cursor = $messageCollection->find([]);
                foreach ($cursor as $doc) {
                    if (strcasecmp($doc['name'], $nameWithoutExt) === 0) {
                        $messageDoc = $doc;
                        break;
                    }
                }
            }

            if (!$messageDoc) {
                $results[] = [
                    'filename' => $fileName,
                    'success' => false,
                    'message' => "Image name '$nameWithoutExt' does not match any name in the top management message CSV. Available names: " . implode(", ", $allNames)
                ];
                $failedCount++;

                error_log("Deleting file from BunnyCDN due to no matching name: $storageUrl");
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
                continue;
            }

            $position = isset($messageDoc['position']) ? $messageDoc['position'] : '';
            $correctName = $messageDoc['name'];

            error_log("Found matching document for '$nameWithoutExt': name='$correctName', position='$position'");

            $document = [
                'name' => $correctName,
                'position' => $position,
                'filename' => $filename,
                'original_name' => $fileName,
                'template' => $template,
                'url' => $publicUrl,
                'upload_time' => new \MongoDB\BSON\UTCDateTime()
            ];
        } catch (Exception $e) {
            error_log("Error checking name in top_management_message: " . $e->getMessage());

            $results[] = [
                'filename' => $fileName,
                'success' => false,
                'message' => "Error verifying name in database: " . $e->getMessage()
            ];
            $failedCount++;

            error_log("Deleting file from BunnyCDN due to database error: $storageUrl");
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
            continue;
        }

        // Check for client disconnection/cancellation just before MongoDB insert
        if (connection_aborted()) {
            $uploadCancelled = true;
            // Delete file from BunnyCDN since we're cancelling before MongoDB insert
            error_log("UploadTopManagementPhotos.php deleting file from BunnyCDN due to cancellation before MongoDB insert: $storageUrl");
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

        // Insert document into MongoDB
        try {
            error_log("UploadTopManagementPhotos.php inserting document: " . json_encode($document));
            $result = $collection->insertOne($document);
            $document['_id'] = (string) $result->getInsertedId();
        } catch (Exception $e) {
            error_log("UploadTopManagementPhotos.php MongoDB insert error: " . $e->getMessage());
            $results[] = [
                'filename' => $fileName,
                'success' => false,
                'message' => 'Failed to save to database: ' . $e->getMessage()
            ];
            $failedCount++;

            // Delete file from BunnyCDN since database insert failed
            error_log("UploadTopManagementPhotos.php deleting file from BunnyCDN due to MongoDB insert error: $storageUrl");
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
            continue;
        }

        // Check for client disconnection/cancellation after MongoDB insert
        if (connection_aborted()) {
            $uploadCancelled = true;
            // Delete file from BunnyCDN and remove the MongoDB entry since we're cancelling after insert
            error_log("UploadTopManagementPhotos.php deleting file from BunnyCDN and MongoDB entry due to cancellation after insert: $storageUrl");
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

            // Delete MongoDB entry
            $collection->deleteOne(['_id' => $document['_id']]);
            respond(false, 'Upload cancelled');
        }

        $results[] = [
            'filename' => $fileName,
            'success' => true,
            'message' => 'Upload successful',
            'url' => $publicUrl,
            'name' => $correctName, // Use the name from the database for consistency
            'position' => $position
        ];
        $uploadedCount++;
    }

    // Prepare response data
    $responseData = [
        'uploaded' => $uploadedCount,
        'failed' => $failedCount,
        'total' => count($uploadedFiles['name']),
        'results' => $results
    ];

    respond(true, "Processed {$uploadedCount} of " . count($uploadedFiles['name']) . " files successfully", $responseData);
} catch (Exception $e) {
    error_log("UploadTopManagementPhotos.php exception: " . $e->getMessage());
    respond(false, 'Server error: ' . $e->getMessage());
}
