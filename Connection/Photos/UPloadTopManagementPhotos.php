<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

ini_set('memory_limit', '1024M');
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
set_time_limit(300);

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

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error: $errstr in $errfile on line $errline");
    http_response_code(500);
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $errstr
    ]);
    exit;
});

set_exception_handler(function ($exception) {
    error_log("PHP Exception: " . $exception->getMessage());
    http_response_code(500);
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $exception->getMessage()
    ]);
    exit;
});

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
    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    error_log("UploadTopManagementPhotos: POST data received: " . json_encode($_POST));

    $batchYear = isset($_POST['batch_year']) ? trim($_POST['batch_year']) : null;
    $academicYear = null;

    if ($batchYear) {
        $academicYear = str_replace('Batch Year ', '', $batchYear);
        error_log("UploadTopManagementPhotos: Batch year received: $batchYear, converted to academic year: $academicYear");
    } else {
        error_log("UploadTopManagementPhotos: No batch year provided - photos will not be associated with academic year");
        error_log("UploadTopManagementPhotos: Available POST keys: " . implode(', ', array_keys($_POST)));
    }

    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    $bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE')
        ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
    $bunnyAccessKey = getenv('BUNNY_ACCESS_KEY')
        ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));
    $bunnyCdnHost = getenv('BUNNY_CDN_HOST')
        ?: (defined('BUNNY_CDN_HOST') ? BUNNY_CDN_HOST : ($GLOBALS['BUNNY_CDN_HOST'] ?? 'https://ECADYB.b-cdn.net'));

    if (!$bunnyStorageZone || !$bunnyAccessKey || !$bunnyCdnHost) {
        respond(false, 'Bunny configuration missing. Please check environment variables.');
    }

    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    if (empty($_FILES) || !isset($_FILES['files'])) {
        respond(false, 'No files were uploaded.');
    }

    $uploadedFiles = $_FILES['files'];

    if (!is_array($uploadedFiles['name'])) {
        $uploadedFiles = [
            'name' => [$uploadedFiles['name']],
            'type' => [$uploadedFiles['type']],
            'tmp_name' => [$uploadedFiles['tmp_name']],
            'error' => [$uploadedFiles['error']],
            'size' => [$uploadedFiles['size']]
        ];
    }

    $MAX_FILES = 20;
    $fileCount = count($uploadedFiles['name']);

    if ($fileCount > $MAX_FILES) {
        error_log("UploadTopManagementPhotos.php - Too many files: $fileCount (max: $MAX_FILES)");
        respond(false, "You can only upload a maximum of $MAX_FILES images at a time. You attempted to upload $fileCount images. Please reduce the number of files.");
    }

    error_log("UploadTopManagementPhotos.php - File count validation passed: $fileCount files");

    $uploadedCount = 0;
    $failedCount = 0;
    $results = [];
    $uploadedFilesToCleanup = [];

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

        if ($academicYear) {
            $baseFolder = 'Top Management Photos/' . $academicYear;
            error_log("UploadTopManagementPhotos: Using academic year folder: $baseFolder");
        } else {
            $baseFolder = 'Top Management Photos';
            error_log("UploadTopManagementPhotos: No academic year, using default folder: $baseFolder");
        }

        $photoTypeFolder = '';
        $nameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);

        if (strpos($nameWithoutExt, '-FILIPINIANA') !== false) {
            $photoTypeFolder = 'FILIPINIANA';
            error_log("Detected FILIPINIANA photo for $fileName");
        } elseif (strpos($nameWithoutExt, '-TOGA') !== false) {
            $photoTypeFolder = 'TOGA';
            error_log("Detected TOGA photo for $fileName");
        } elseif (strpos($nameWithoutExt, '-UNIFORM') !== false) {
            $photoTypeFolder = 'UNIFORM';
            error_log("Detected UNIFORM photo for $fileName");
        } else {
            $photoTypeFolder = 'UNIFORM';
            error_log("No photo type detected for $fileName, defaulting to UNIFORM");
        }

        $safeFolder = $baseFolder . '/' . $photoTypeFolder;
        error_log("UploadTopManagementPhotos: Using full folder path: $safeFolder");

        $filename = sprintf('%s.%s', $safeFileName, $safeExt);
        $path = $safeFolder . '/' . $filename;
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
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TCP_NODELAY    => true,
            CURLOPT_FRESH_CONNECT  => false,
            CURLOPT_FORBID_REUSE   => false,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0
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

        $uploadedFilesToCleanup[] = [
            'storageUrl' => $storageUrl,
            'bunnyAccessKey' => $bunnyAccessKey,
            'filename' => $fileName
        ];

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

        $mongoDbName = "ECADYB";
        require_once __DIR__ . '/../Configuration/EnvLoader.php';
        $mongoUrl = getMongoUrl();
        error_log("UploadTopManagementPhotos.php using MongoDB URL: $mongoUrl");
        error_log("UploadTopManagementPhotos.php using database: $mongoDbName, collection: Photos");

        try {
            $mongoClient = new Client($mongoUrl, [
                'serverSelectionTimeoutMS' => 5000,
                'connectTimeoutMS' => 5000,
                'socketTimeoutMS' => 10000,
                'retryReads' => true
            ]);
            $collection = $mongoClient->$mongoDbName->Top_Management_Photos;
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
            $messageCollection = $mongoClient->$mongoDbName->Top_Management_Messages;

            $allNames = $messageCollection->distinct('name');
            error_log("Available names in Messages collection: " . implode(", ", $allNames));

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
                'url' => $publicUrl,
                'upload_time' => new \MongoDB\BSON\UTCDateTime(),
                'photo_type' => $photoTypeFolder,
                'folder_path' => $safeFolder
            ];

            if ($academicYear) {
                $document['academic year'] = $academicYear;
                error_log("Added academic year '$academicYear' to Top Management Photo document for '$correctName'");
            }
        } catch (Exception $e) {
            error_log("Error checking name in Messages collection: " . $e->getMessage());

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

        if (connection_aborted()) {
            $uploadCancelled = true;
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

        try {
            $filter = ['name' => $correctName];
            if ($academicYear) {
                $filter['academic year'] = $academicYear;
            }

            error_log("UploadTopManagementPhotos.php upserting document with filter: " . json_encode($filter));
            error_log("UploadTopManagementPhotos.php document data: " . json_encode($document));

            $result = $collection->updateOne(
                $filter,
                ['$set' => $document],
                ['upsert' => true]
            );

            if ($result->getUpsertedId()) {
                $document['_id'] = (string) $result->getUpsertedId();
                error_log("UploadTopManagementPhotos.php inserted new document with ID: " . $document['_id']);
            } else {
                error_log("UploadTopManagementPhotos.php updated existing document for '{$correctName}' (academic year: {$academicYear})");
            }
        } catch (Exception $e) {
            error_log("UploadTopManagementPhotos.php MongoDB upsert error: " . $e->getMessage());
            $results[] = [
                'filename' => $fileName,
                'success' => false,
                'message' => 'Failed to save to database: ' . $e->getMessage()
            ];
            $failedCount++;

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

        if (connection_aborted()) {
            $uploadCancelled = true;
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

            $collection->deleteOne(['_id' => $document['_id']]);
            respond(false, 'Upload cancelled');
        }

        $results[] = [
            'filename' => $fileName,
            'success' => true,
            'message' => 'Upload successful',
            'url' => $publicUrl,
            'name' => $correctName,
            'position' => $position
        ];
        $uploadedCount++;
    }

    $responseData = [
        'uploaded' => $uploadedCount,
        'failed' => $failedCount,
        'total' => count($uploadedFiles['name']),
        'results' => $results
    ];

    respond(true, "Processed {$uploadedCount} of " . count($uploadedFiles['name']) . " files successfully", $responseData);
} catch (Exception $e) {
    error_log("UploadTopManagementPhotos.php exception: " . $e->getMessage());

    if (!empty($uploadedFilesToCleanup)) {
        error_log("Cleaning up " . count($uploadedFilesToCleanup) . " files from BunnyCDN due to exception");
        foreach ($uploadedFilesToCleanup as $fileInfo) {
            $deleteCh = curl_init($fileInfo['storageUrl']);
            curl_setopt_array($deleteCh, [
                CURLOPT_CUSTOMREQUEST  => 'DELETE',
                CURLOPT_HTTPHEADER     => ['AccessKey: ' . $fileInfo['bunnyAccessKey']],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_CONNECTTIMEOUT => 3
            ]);
            curl_exec($deleteCh);
            curl_close($deleteCh);
            error_log("Cleaned up file: " . $fileInfo['filename']);
        }
    }

    respond(false, 'Server error: ' . $e->getMessage());
}
