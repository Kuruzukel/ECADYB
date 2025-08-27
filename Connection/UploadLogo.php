<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';
// Optional local override for secrets
if (file_exists(__DIR__ . '/BunnyConfig.php')) {
    require __DIR__ . '/BunnyConfig.php';
}
use MongoDB\Client;

function respond($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// Bunny config
$bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
$bunnyAccessKey = getenv('BUNNY_ACCESS_KEY') ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));
$bunnyCdnHost = getenv('BUNNY_CDN_HOST') ?: (defined('BUNNY_CDN_HOST') ? BUNNY_CDN_HOST : ($GLOBALS['BUNNY_CDN_HOST'] ?? 'https://ECADYB.b-cdn.net'));

if (!$bunnyStorageZone || !$bunnyAccessKey || !$bunnyCdnHost) {
    respond(false, 'Bunny configuration missing.');
}

// Validate input
$slot = isset($_POST['slot']) ? (int)$_POST['slot'] : null; // 1..9
if (!$slot || $slot < 1 || $slot > 9) {
    respond(false, 'Invalid slot.');
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'No file uploaded or upload error.');
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
$curlErr = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode < 200 || $httpCode >= 300) {
    respond(false, 'Failed to upload to Bunny Storage: ' . ($curlErr ?: ('HTTP ' . $httpCode)));
}

$publicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $path);

// Save to MongoDB: collection Settings, doc type logos
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
try {
    $client = new Client($mongoUrl);
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

respond(true, 'Logo uploaded', ['url' => $publicUrl, 'slot' => $slot]);

