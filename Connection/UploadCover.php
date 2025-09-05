<?php
// Yearbook Cover Upload API

ob_start();

// Headers (for Railway / CORS)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Helper: JSON Response
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

// Request validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method. Use POST.');
}

// Load dependencies
require __DIR__ . '/../vendor/autoload.php';
if (file_exists(__DIR__ . '/BunnyConfig.php')) {
    require __DIR__ . '/BunnyConfig.php';
}

use MongoDB\Client;

try {
    // BunnyCDN Configuration
    $bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE')
        ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
    $bunnyAccessKey = getenv('BUNNY_ACCESS_KEY')
        ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));
    $bunnyCdnHost = getenv('BUNNY_CDN_HOST')
        ?: (defined('BUNNY_CDN_HOST') ? BUNNY_CDN_HOST : ($GLOBALS['BUNNY_CDN_HOST'] ?? 'https://ECADYB.b-cdn.net'));

    if (!$bunnyStorageZone || !$bunnyAccessKey || !$bunnyCdnHost) {
        respond(false, 'Bunny configuration missing. Please check environment variables.');
    }

    // Validate input
    $slot     = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
    $side     = isset($_POST['side']) ? strtolower(trim($_POST['side'])) : '';
    $template = isset($_POST['template']) ? (int)$_POST['template'] : 1;

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

    // File preparation
    $fileTmp      = $_FILES['file']['tmp_name'];
    $originalName = $_FILES['file']['name'];
    $ext          = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';

    $baseOriginal = pathinfo($originalName, PATHINFO_FILENAME);
    $safeBase     = preg_replace('/[^A-Za-z0-9 _.-]/', '', $baseOriginal) ?: ('image_' . time());
    $safeExt      = preg_replace('/[^A-Za-z0-9]/', '', $ext) ?: 'jpg';

    // Build storage paths
    $safeFolder     = 'Yearbook Covers';
    $templateFolder = sprintf('Batch Template %d', $template);

    $sideLabel = ($slot === 8)
        ? 'BackgroundPage'
        : ($side === 'back' ? 'Back' : 'Front');

    // Main filename
    $filename = ($slot === 8)
        ? sprintf('BackgroundPage-%s.%s', $safeBase, $safeExt)
        : sprintf('Slot-%d-%s-%s.%s', $slot, $sideLabel, $safeBase, $safeExt);

    $path = $safeFolder . '/' . $templateFolder . '/' . $filename;

    // Upload main file to Bunny
    $storageUrl   = "https://storage.bunnycdn.com/{$bunnyStorageZone}/" . str_replace(' ', '%20', $path);
    $fileContents = file_get_contents($fileTmp);
    if ($fileContents === false) {
        respond(false, 'Failed to read uploaded file.');
    }

    $ch = curl_init($storageUrl);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey, 'Content-Type: application/octet-stream'],
        CURLOPT_POSTFIELDS     => $fileContents,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        respond(false, 'Failed to upload to Bunny: ' . ($curlErr ?: 'HTTP ' . $httpCode));
    }

    $publicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $path);

    // Upload duplicate thumbnail
    $thumbFilename = ($slot === 8)
        ? sprintf('BackgroundPage-Thumb-%s.%s', $safeBase, $safeExt)
        : sprintf('Slot-%d-Thumb-%s-%s.%s', $slot, $sideLabel, $safeBase, $safeExt);

    $thumbPath       = $safeFolder . '/' . $templateFolder . '/' . $thumbFilename;
    $thumbStorageUrl = "https://storage.bunnycdn.com/{$bunnyStorageZone}/" . str_replace(' ', '%20', $thumbPath);

    $ch = curl_init($thumbStorageUrl);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_HTTPHEADER     => ['AccessKey: ' . $bunnyAccessKey, 'Content-Type: application/octet-stream'],
        CURLOPT_POSTFIELDS     => $fileContents,
        CURLOPT_RETURNTRANSFER => true
    ]);
    $thumbResponse = curl_exec($ch);
    $thumbHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($thumbResponse === false || $thumbHttpCode < 200 || $thumbHttpCode >= 300) {
        respond(false, 'Failed to upload thumbnail to Bunny (HTTP ' . $thumbHttpCode . ')');
    }

    $thumbUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $thumbPath);

    // Update MongoDB
    $mongoUrl = getenv('MONGO_URL')
        ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    try {
        $client     = new Client($mongoUrl, [
            'serverSelectionTimeoutMS' => 5000,
            'connectTimeoutMS'         => 5000,
            'socketTimeoutMS'          => 5000
        ]);
        $db         = $client->Departments;
        $collection = $db->YearbookCovers;

        // ===============================
        // Always update this slot
        // ===============================
        $update = [
            '$set' => [
                'template'   => $template,
                'slot'       => $slot,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ];

        // Slot 8 → background only
        if ($slot === 8) {
            $update['$set']['background_url']       = $publicUrl;
            $update['$set']['background_thumb_url'] = $thumbUrl;
        } else {
            // Slot 1–7 → normal front/back fields
            $update['$set'][$side . '_url']       = $publicUrl;
            $update['$set'][$side . '_thumb_url'] = $thumbUrl;

            // Fetch background from slot 8 and copy
            $slot8 = $collection->findOne(['template' => $template, 'slot' => 8]);
            if ($slot8 && isset($slot8['background_url'], $slot8['background_thumb_url'])) {
                $update['$set']['background_url']       = $slot8['background_url'];
                $update['$set']['background_thumb_url'] = $slot8['background_thumb_url'];
            }
        }

        $collection->updateOne(
            ['template' => $template, 'slot' => $slot],
            $update,
            ['upsert' => true]
        );

        // Ensure slot 8 exists
        $collection->updateOne(
            ['template' => $template, 'slot' => 8],
            [
                '$setOnInsert' => [
                    'template'             => $template,
                    'slot'                 => 8,
                    'background_url'       => '',
                    'background_thumb_url' => '',
                    'created_at'           => new MongoDB\BSON\UTCDateTime()
                ]
            ],
            ['upsert' => true]
        );
    } catch (Exception $e) {
        respond(false, 'Uploaded to CDN, but failed to update MongoDB: ' . $e->getMessage(), [
            'url'       => $publicUrl,
            'thumb_url' => $thumbUrl
        ]);
    }

    // ===============================
    // Success response
    // ===============================
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
