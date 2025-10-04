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

    error_log("UploadStudentPhotos.php received parameters: template=$template");

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

        $studentId = pathinfo($fileName, PATHINFO_FILENAME);

        if (!preg_match('/^\d{4}-\d{6}$/', $studentId) && !is_numeric($studentId)) {
            $results[] = [
                'filename' => $fileName,
                'success' => false,
                'message' => 'Invalid filename. Filename must be a student ID in format 2021-004393 or numeric ID.'
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

        // Determine department based on student ID (this is a simplified approach)
        // In a real implementation, you would look up the student in the database to get their department
        $department = 'unknown';

        // For demonstration, we'll use a simple mapping
        // In practice, you should query the database to get the actual department
        $cleanStudentId = str_replace('-', '', $studentId);
        $departmentMap = [
            '100' => 'bsme',  // BS Marine Engineering
            '200' => 'bsmt',  // BS Marine Transportation
            '300' => 'bscje', // BS Criminal Justice Education
            '400' => 'bstm',  // BS Tourism Management
            '500' => 'btvted', // BS Technical-Vocational Teacher Education
            '600' => 'beced', // BS Early Childhood Education
            '700' => 'bsn',   // BS Nursing
            '800' => 'bsis',  // BS Information System
            '900' => 'bsma',  // BS Management Accounting
            '1000' => 'bse'   // BS Entrepreneurship
        ];

        // Extract prefix from student ID to determine department
        $prefix = substr($cleanStudentId, 0, 3);
        if (isset($departmentMap[$prefix])) {
            $department = $departmentMap[$prefix];
        } else {
            // Try with 2-digit prefix
            $prefix = substr($cleanStudentId, 0, 2);
            if (isset($departmentMap[$prefix])) {
                $department = $departmentMap[$prefix];
            }
        }

        // Generate safe filename
        $ext = pathinfo($fileName, PATHINFO_EXTENSION) ?: 'jpg';
        $safeFileName = preg_replace('/[^A-Za-z0-9 _.-]/', '', $studentId) ?: ('student_' . time());
        $safeExt = preg_replace('/[^A-Za-z0-9]/', '', $ext) ?: 'jpg';

        // Construct path for BunnyCDN
        $safeFolder = 'Student Photos';
        $templateFolder = sprintf('Batch Template %d', $template);
        $filename = sprintf('%s.%s', $safeFileName, $safeExt);
        $path = $safeFolder . '/' . $templateFolder . '/' . $filename;
        $storageUrl = "https://storage.bunnycdn.com/{$bunnyStorageZone}/" . str_replace(' ', '%20', $path);

        // Check for client disconnection/cancellation before uploading to BunnyCDN
        if (connection_aborted()) {
            $uploadCancelled = true;
            respond(false, 'Upload cancelled');
        }

        // Upload to BunnyCDN
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

        // Check for client disconnection/cancellation after uploading to BunnyCDN
        if (connection_aborted()) {
            $uploadCancelled = true;
            // If upload was successful but client disconnected, delete the file from BunnyCDN
            if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                error_log("UploadStudentPhotos.php deleting file from BunnyCDN due to cancellation: $storageUrl");
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

        // Check for client disconnection/cancellation before updating MongoDB
        if (connection_aborted()) {
            $uploadCancelled = true;
            // Delete file from BunnyCDN since we're cancelling before MongoDB
            error_log("UploadStudentPhotos.php deleting file from BunnyCDN due to cancellation before MongoDB: $storageUrl");
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

        // MongoDB connection and update
        $mongoDbName = "BatchTemplate{$template}";
        $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
        error_log("UploadStudentPhotos.php using MongoDB URL: $mongoUrl");
        error_log("UploadStudentPhotos.php using database: $mongoDbName, collection: StudentPhotos");

        try {
            $mongoClient = new Client($mongoUrl, [
                'serverSelectionTimeoutMS' => 5000,
                'connectTimeoutMS' => 5000,
                'socketTimeoutMS' => 10000,
                'retryReads' => true
            ]);
            $collection = $mongoClient->$mongoDbName->StudentPhotos;
        } catch (Exception $e) {
            error_log("UploadStudentPhotos.php MongoDB connection error: " . $e->getMessage());
            $results[] = [
                'filename' => $fileName,
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
            $failedCount++;

            // Delete file from BunnyCDN since database connection failed
            error_log("UploadStudentPhotos.php deleting file from BunnyCDN due to MongoDB connection error: $storageUrl");
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

        // Prepare document for MongoDB
        $document = [
            'student_id' => $studentId,
            'filename' => $filename,
            'original_name' => $fileName,
            'template' => $template,
            'url' => $publicUrl,
            'upload_time' => new \MongoDB\BSON\UTCDateTime()
        ];

        // Check for client disconnection/cancellation just before MongoDB insert
        if (connection_aborted()) {
            $uploadCancelled = true;
            // Delete file from BunnyCDN since we're cancelling before MongoDB insert
            error_log("UploadStudentPhotos.php deleting file from BunnyCDN due to cancellation before MongoDB insert: $storageUrl");
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
            error_log("UploadStudentPhotos.php inserting document: " . json_encode($document));
            $result = $collection->insertOne($document);
            $document['_id'] = (string) $result->getInsertedId();
        } catch (Exception $e) {
            error_log("UploadStudentPhotos.php MongoDB insert error: " . $e->getMessage());
            $results[] = [
                'filename' => $fileName,
                'success' => false,
                'message' => 'Failed to save to database: ' . $e->getMessage()
            ];
            $failedCount++;

            // Delete file from BunnyCDN since database insert failed
            error_log("UploadStudentPhotos.php deleting file from BunnyCDN due to MongoDB insert error: $storageUrl");
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
            error_log("UploadStudentPhotos.php deleting file from BunnyCDN and MongoDB entry due to cancellation after insert: $storageUrl");
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
            'student_id' => $studentId,
            'department' => $department
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
    error_log("UploadStudentPhotos.php exception: " . $e->getMessage());
    respond(false, 'Server error: ' . $e->getMessage());
}
