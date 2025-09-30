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

function respond($success, $message = '', $data = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
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

    $fileTmp      = $_FILES['file']['tmp_name'];
    $originalName = $_FILES['file']['name'];
    $ext          = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';

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

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        respond(false, 'Failed to upload to Bunny: ' . ($curlErr ?: 'HTTP ' . $httpCode));
    }

    $publicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $path);

    error_log("UploadCover.php public URL: $publicUrl");

    // Skip thumbnail creation entirely for faster upload
    $thumbUrl = '';
    
    // Ultra-fast MongoDB connection
    $mongoUrl = getenv('MONGO_URL')
        ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    try {
        $client = new Client($mongoUrl, [
            'serverSelectionTimeoutMS' => 1000,  // Ultra-fast timeout
            'connectTimeoutMS'         => 1000,  // Ultra-fast timeout
            'socketTimeoutMS'          => 2000,  // Reduced timeout
            'retryWrites'              => true,
            'writeConcern'             => new MongoDB\Driver\WriteConcern(1, 1000) // Fast write concern
        ]);

        $dbName = "BatchTemplate" . $template;
        $db = $client->$dbName;
        $collection = $db->YearbookCovers;

        error_log("UploadCover.php using database: $dbName, collection: YearbookCovers");

        $update = [
            '$set' => [
                'template'   => $template,
                'slot'       => $slot,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ];

        if ($slot === 8) {
            $update['$set']['background_url']       = $publicUrl;
            $update['$set']['background_thumb_url'] = $thumbUrl;
        } else {
            $update['$set'][$side . '_url']       = $publicUrl;
            $update['$set'][$side . '_thumb_url'] = $thumbUrl;
        }

        error_log("UploadCover.php updating document with filter: template=$template, slot=$slot");

        // Ultra-fast database operation with minimal options
        $result = $collection->updateOne(
            ['template' => $template, 'slot' => $slot],
            $update,
            ['upsert' => true, 'writeConcern' => new MongoDB\Driver\WriteConcern(1, 1000)]
        );

        error_log("UploadCover.php update result: matched=" . $result->getMatchedCount() . ", modified=" . $result->getModifiedCount() . ", upserted=" . $result->getUpsertedCount());

        // Minimal slot 8 check (only check if needed)
        if ($slot !== 8) {
            $slot8 = $collection->findOne(['template' => $template, 'slot' => 8], ['projection' => ['_id' => 1]]);
            if (!$slot8) {
                $collection->insertOne([
                    'template' => $template,
                    'slot' => 8,
                    'background_url' => '',
                    'background_thumb_url' => '',
                    'created_at' => new MongoDB\BSON\UTCDateTime(),
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]);
            }
        }

        error_log("UploadCover.php operation completed");
    } catch (Exception $e) {
        respond(false, 'Uploaded to CDN, but failed to update MongoDB: ' . $e->getMessage(), [
            'url'       => $publicUrl,
            'thumb_url' => $thumbUrl
        ]);
    }

    respond(true, 'Cover updated successfully', [
        'url'       => $publicUrl,
        'thumb_url' => $thumbUrl,
        'slot'      => $slot,
        'side'      => $side,
        'template'  => $template
    ]);
} catch (Exception $e) {
    respond(false, 'Unexpected error: ' . $e->getMessage());
}