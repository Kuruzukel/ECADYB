<?php
// Ensure no output before headers
ob_start();

// Set proper headers for Railway
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Error handling function
function respond($success, $message = '', $data = []) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set JSON header
    header('Content-Type: application/json');
    
    // Return JSON response
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method');
}

// Load dependencies
require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/BunnyConfig.php')) {
    require __DIR__ . '/BunnyConfig.php';
}

use MongoDB\Client;

try {
    // Check if client is still connected periodically
    if (connection_aborted()) {
        respond(false, 'Client disconnected');
    }

    $bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
    $bunnyAccessKey = getenv('BUNNY_ACCESS_KEY') ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));
    $bunnyCdnHost = getenv('BUNNY_CDN_HOST') ?: (defined('BUNNY_CDN_HOST') ? BUNNY_CDN_HOST : ($GLOBALS['BUNNY_CDN_HOST'] ?? 'https://ECADYB.b-cdn.net'));

    if (!$bunnyStorageZone || !$bunnyAccessKey || !$bunnyCdnHost) {
        respond(false, 'Bunny configuration missing. Please check your environment variables.');
    }

    $slot = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
    if (!$slot || $slot < 1 || $slot > 9) {
        respond(false, 'Invalid slot. Must be between 1 and 9.');
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'No file uploaded or upload error.';
        if (isset($_FILES['file']['error'])) {
            switch ($_FILES['file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $errorMsg = 'File too large (exceeds php.ini limit).';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMsg = 'File too large (exceeds form limit).';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errorMsg = 'File upload was incomplete.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorMsg = 'No file was uploaded.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorMsg = 'Missing temporary folder.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errorMsg = 'Failed to write file to disk.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errorMsg = 'File upload stopped by extension.';
                    break;
            }
        }
        respond(false, $errorMsg);
    }

    // Check if client is still connected
    if (connection_aborted()) {
        respond(false, 'Client disconnected');
    }

    $fileTmp = $_FILES['file']['tmp_name'];
    $originalName = $_FILES['file']['name'];
    $ext = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'png';
    $baseOriginal = pathinfo($originalName, PATHINFO_FILENAME);
    $safeBase = preg_replace('/[^A-Za-z0-9 _.-]/', '', $baseOriginal) ?: ('logo_' . time());
    $safeExt = preg_replace('/[^A-Za-z0-9]/', '', $ext) ?: 'png';

    // Path: Logo Container/Slot{n}-{OriginalName}.{ext}
    $folder = 'Logo Container';
    $fileName = sprintf('Slot%d-%s.%s', $slot, $safeBase, $safeExt);
    $path = $folder . '/' . $fileName;

    $storageUrl = "https://storage.bunnycdn.com/{$bunnyStorageZone}/" . str_replace(' ', '%20', $path);
    $fileContents = file_get_contents($fileTmp);

    if ($fileContents === false) {
        respond(false, 'Failed to read uploaded file.');
    }

    // Check if client is still connected before uploading to BunnyCDN
    if (connection_aborted()) {
        respond(false, 'Client disconnected');
    }

    $ch = curl_init($storageUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'AccessKey: ' . $bunnyAccessKey,
        'Content-Type: application/octet-stream',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContents);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    // Check if client is still connected after uploading to BunnyCDN
    if (connection_aborted()) {
        // Try to delete the uploaded file since client disconnected
        $deleteCh = curl_init($storageUrl);
        curl_setopt($deleteCh, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($deleteCh, CURLOPT_HTTPHEADER, ['AccessKey: ' . $bunnyAccessKey]);
        curl_setopt($deleteCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($deleteCh, CURLOPT_TIMEOUT, 30);
        curl_setopt($deleteCh, CURLOPT_SSL_VERIFYPEER, true);
        curl_exec($deleteCh);
        curl_close($deleteCh);
        
        respond(false, 'Client disconnected');
    }

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        respond(false, 'Failed to upload to Bunny Storage: ' . ($curlErr ?: ('HTTP ' . $httpCode)));
    }

    $publicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $path);

    // Check if client is still connected before saving to MongoDB
    if (connection_aborted()) {
        // Try to delete the uploaded file since client disconnected
        $deleteCh = curl_init($storageUrl);
        curl_setopt($deleteCh, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($deleteCh, CURLOPT_HTTPHEADER, ['AccessKey: ' . $bunnyAccessKey]);
        curl_setopt($deleteCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($deleteCh, CURLOPT_TIMEOUT, 30);
        curl_setopt($deleteCh, CURLOPT_SSL_VERIFYPEER, true);
        curl_exec($deleteCh);
        curl_close($deleteCh);
        
        respond(false, 'Client disconnected');
    }

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    
    try {
        $client = new Client($mongoUrl, [
            'serverSelectionTimeoutMS' => 5000,
            'connectTimeoutMS' => 5000,
            'socketTimeoutMS' => 5000
        ]);
        $db = $client->Departments;
        $collection = $db->DashboardAssets;

        $collection->updateOne(
            ['type' => 'logo_container', 'slot' => $slot],
            ['$set' => [
                'type' => 'logo_container',
                'slot' => $slot,
                'url' => $publicUrl,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]],
            ['upsert' => true]
        );
    } catch (Exception $e) {
        respond(false, 'Uploaded to CDN, but failed to save metadata: ' . $e->getMessage(), ['url' => $publicUrl]);
    }

    respond(true, 'Logo uploaded successfully', ['url' => $publicUrl, 'slot' => $slot]);

} catch (Exception $e) {
    respond(false, 'Unexpected error: ' . $e->getMessage());
}