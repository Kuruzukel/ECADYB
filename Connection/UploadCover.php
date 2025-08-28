<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

// ✅ Optional local override for secrets
if (file_exists(__DIR__ . '/BunnyConfig.php')) {
    require __DIR__ . '/BunnyConfig.php';
}

use MongoDB\Client;

// ----------------------
// JSON response helper
// ----------------------
function respond($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// ----------------------
// BunnyCDN Env configuration
// ----------------------
$bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
$bunnyAccessKey   = getenv('BUNNY_ACCESS_KEY')   ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));
$bunnyCdnHost     = getenv('BUNNY_CDN_HOST')     ?: (defined('BUNNY_CDN_HOST') ? BUNNY_CDN_HOST : ($GLOBALS['BUNNY_CDN_HOST'] ?? 'https://ECADYB.b-cdn.net'));

if (!$bunnyStorageZone || !$bunnyAccessKey || !$bunnyCdnHost) {
    respond(false, 'Bunny configuration missing. Please set BUNNY_STORAGE_ZONE, BUNNY_ACCESS_KEY, BUNNY_CDN_HOST or create Connection/BunnyConfig.php');
}

// ----------------------
// Validate request
// ----------------------
$slot     = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
$side     = isset($_POST['side']) ? trim($_POST['side']) : '';
$template = isset($_POST['template']) ? (int)$_POST['template'] : 1;

if ($slot === null || ($side !== 'front' && $side !== 'back')) {
    respond(false, 'Invalid parameters.');
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'No file uploaded or upload error.');
}

// ----------------------
// Prepare file
// ----------------------
$fileTmp       = $_FILES['file']['tmp_name'];
$originalName  = $_FILES['file']['name'];
$ext           = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';

$baseOriginal  = pathinfo($originalName, PATHINFO_FILENAME);
$safeBase      = preg_replace('/[^A-Za-z0-9 _.-]/', '', $baseOriginal) ?: ('image_' . time());
$safeExt       = preg_replace('/[^A-Za-z0-9]/', '', $ext) ?: 'jpg';

// ----------------------
// Build storage path
// ----------------------
$safeFolder     = 'Yearbook Covers';
$templateFolder = sprintf('Batch Template %d', $template);

$sideLabel = ($slot === 8) ? 'BackgroundPage' : (strtolower($side) === 'back' ? 'Back' : 'Front');

$filename = ($slot === 8)
    ? sprintf('BackgroundPage-%s.%s', $safeBase, $safeExt)
    : sprintf('Slot-%d-%s-%s.%s', $slot, $sideLabel, $safeBase, $safeExt);

$path = $safeFolder . '/' . $templateFolder . '/' . $filename;

// ----------------------
// Upload to Bunny Storage
// ----------------------
$storageUrl   = "https://storage.bunnycdn.com/{$bunnyStorageZone}/" . str_replace(' ', '%20', $path);
$fileContents = file_get_contents($fileTmp);

$ch = curl_init($storageUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'AccessKey: ' . $bunnyAccessKey,
    'Content-Type: application/octet-stream',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContents);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode < 200 || $httpCode >= 300) {
    respond(false, 'Failed to upload to Bunny Storage: ' . ($curlErr ?: ('HTTP ' . $httpCode)));
}

// ----------------------
// Build public CDN URL
// ----------------------
$publicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $path);

// ----------------------
// Update MongoDB
// ----------------------
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

try {
    $client     = new Client($mongoUrl);
    $db         = $client->Departments;
    $collection = $db->YearbookCovers;

    $update = [
        '$set' => [
            'template'    => $template,
            'slot'        => $slot,
            $side . '_url'=> $publicUrl,
            'updated_at'  => new MongoDB\BSON\UTCDateTime()
        ]
    ];

    $collection->updateOne(
        ['template' => $template, 'slot' => $slot],
        $update,
        ['upsert' => true]
    );

} catch (Exception $e) {
    respond(false, 'Uploaded to CDN, but failed to update MongoDB: ' . $e->getMessage(), ['url' => $publicUrl]);
}

// ----------------------
// Success response
// ----------------------
respond(true, 'Cover updated successfully', [
    'url'      => $publicUrl,
    'slot'     => $slot,
    'side'     => $side,
    'template' => $template
]);