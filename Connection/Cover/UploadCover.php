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

// Global variable to track if upload should be cancelled
$uploadCancelled = false;

function respond($success, $message = '', $data = [])
{
    global $uploadCancelled;
    
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // If upload was cancelled, ensure we don't save anything
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

// Check for client disconnection/cancellation at the very beginning
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

// Check for client disconnection/cancellation
if (connection_aborted()) {
    $uploadCancelled = true;
    respond(false, 'Upload cancelled');
}

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

    // Check for client disconnection/cancellation
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

    // Check for client disconnection/cancellation
    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    $fileTmp      = $_FILES['file']['tmp_name'];
    $originalName = $_FILES['file']['name'];
    $ext          = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';

    // Auto-detect slot and side from filename if not properly set
    $upperName = strtoupper($originalName);
    
    // Slot mapping based on filename prefix
    $slotMapping = [
        'BSME' => 1,
        'BSCJ' => 2,
        'BSTM' => 3,
        'BSE' => 4,
        'BSN' => 5,
        'BSIS' => 6,
        'BSBA' => 7
    ];
    
    // Override slot if we can detect it from filename
    $detectedSlot = null;
    foreach ($slotMapping as $prefix => $slotNum) {
        if (strpos($upperName, $prefix) === 0) {
            $detectedSlot = $slotNum;
            break;
        }
    }
    
    // If we detected a slot from filename but it doesn't match the provided slot, cancel upload
    if ($detectedSlot !== null && $detectedSlot != $slot) {
        respond(false, "Upload cancelled: Filename prefix doesn't match the selected slot. Expected slot $detectedSlot for this filename.");
    }
    
    // If we couldn't detect a slot from filename for slots 1-7, cancel upload
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
    $fileContents = file_get_contents($fileTmp);
    if ($fileContents === false) {
        respond(false, 'Failed to read uploaded file.');
    }

    // Check for client disconnection/cancellation before uploading to BunnyCDN
    if (connection_aborted()) {
        $uploadCancelled = true;
        respond(false, 'Upload cancelled');
    }

    error_log("UploadCover.php storage URL: $storageUrl");

    // Ultra-fast BunnyCDN upload with minimal timeout
    $ch = curl_init($storageUrl);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey, 'Content-Type: application/octet-stream'],
        CURLOPT_POSTFIELDS     => $fileContents,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,  // Reduced data transfer
        CURLOPT_TIMEOUT        => 8,      // Reduced to 8 seconds
        CURLOPT_CONNECTTIMEOUT => 3,      // Fast connection timeout
        CURLOPT_SSL_VERIFYPEER => false,  // Speed optimization (in production, keep as true)
        CURLOPT_TCP_NODELAY    => true,   // TCP optimization
        CURLOPT_FRESH_CONNECT  => false,  // Reuse connections
        CURLOPT_FORBID_REUSE   => false   // Allow connection reuse
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    // Check for client disconnection/cancellation after uploading to BunnyCDN
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
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 2
            ]);
            curl_exec($deleteCh);
            curl_close($deleteCh);
        }
        respond(false, 'Upload cancelled');
    }

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        respond(false, 'Failed to upload to Bunny: ' . ($curlErr ?: 'HTTP ' . $httpCode));
    }

    $publicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $path);

    error_log("UploadCover.php public URL: $publicUrl");

    // Skip thumbnail creation entirely for faster upload
    $thumbUrl = '';
    
    // Check for client disconnection/cancellation before updating MongoDB
    if (connection_aborted()) {
        $uploadCancelled = true;
        // Delete the uploaded file from BunnyCDN since we're cancelling before MongoDB
        error_log("UploadCover.php deleting file from BunnyCDN due to cancellation before MongoDB: $storageUrl");
        $deleteCh = curl_init($storageUrl);
        curl_setopt_array($deleteCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 2
        ]);
        curl_exec($deleteCh);
        curl_close($deleteCh);
        respond(false, 'Upload cancelled');
    }

    // MongoDB connection and update
    $mongoDbName = "BatchTemplate{$template}";
    $mongoClient = new Client(getenv('MONGODB_URI') ?: 'mongodb://localhost:27017');
    $collection = $mongoClient->$mongoDbName->cover_images;

    // Check for client disconnection/cancellation before MongoDB operations
    if (connection_aborted()) {
        $uploadCancelled = true;
        // Delete the uploaded file from BunnyCDN since we're cancelling before MongoDB
        error_log("UploadCover.php deleting file from BunnyCDN due to cancellation before MongoDB operations: $storageUrl");
        $deleteCh = curl_init($storageUrl);
        curl_setopt_array($deleteCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 2
        ]);
        curl_exec($deleteCh);
        curl_close($deleteCh);
        respond(false, 'Upload cancelled');
    }

    // Prepare document for MongoDB
    $document = [
        'filename' => $filename,
        'original_name' => $originalName,
        'slot' => $slot,
        'side' => $side,
        'template' => $template,
        'public_url' => $publicUrl,
        'thumbnail_url' => $thumbUrl,
        'upload_time' => new \MongoDB\BSON\UTCDateTime()
    ];

    // Check for client disconnection/cancellation just before MongoDB insert
    if (connection_aborted()) {
        $uploadCancelled = true;
        // Delete the uploaded file from BunnyCDN since we're cancelling before MongoDB insert
        error_log("UploadCover.php deleting file from BunnyCDN due to cancellation before MongoDB insert: $storageUrl");
        $deleteCh = curl_init($storageUrl);
        curl_setopt_array($deleteCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 2
        ]);
        curl_exec($deleteCh);
        curl_close($deleteCh);
        respond(false, 'Upload cancelled');
    }

    // Insert document into MongoDB
    $result = $collection->insertOne($document);
    $document['_id'] = (string) $result->getInsertedId();

    // Check for client disconnection/cancellation after MongoDB insert
    if (connection_aborted()) {
        $uploadCancelled = true;
        // Delete the uploaded file from BunnyCDN and remove the MongoDB entry since we're cancelling after insert
        error_log("UploadCover.php deleting file from BunnyCDN and MongoDB entry due to cancellation after insert: $storageUrl");
        $deleteCh = curl_init($storageUrl);
        curl_setopt_array($deleteCh, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 2
        ]);
        curl_exec($deleteCh);
        curl_close($deleteCh);
        
        // Delete MongoDB entry
        $collection->deleteOne(['_id' => $result->getInsertedId()]);
        respond(false, 'Upload cancelled');
    }

    respond(true, 'Upload successful', [
        'filename' => $filename,
        'public_url' => $publicUrl,
        'thumbnail_url' => $thumbUrl,
        'slot' => $slot,
        'side' => $side,
        'template' => $template
    ]);
} catch (Exception $e) {
    error_log("UploadCover.php exception: " . $e->getMessage());
    respond(false, 'Server error: ' . $e->getMessage());
}