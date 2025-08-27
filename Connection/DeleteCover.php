<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';
if (file_exists(__DIR__ . '/BunnyConfig.php')) {
    require __DIR__ . '/BunnyConfig.php';
}
use MongoDB\Client;

function respond($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

$slot = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
$side = isset($_POST['side']) ? trim($_POST['side']) : '';
$template = isset($_POST['template']) ? (int)$_POST['template'] : 1;

if ($slot === null || ($side !== 'front' && $side !== 'back')) {
    respond(false, 'Invalid parameters.');
}

$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
$bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
$bunnyAccessKey = getenv('BUNNY_ACCESS_KEY') ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));

try {
    $client = new Client($mongoUrl);
    $db = $client->Departments;
    $collection = $db->YearbookCovers;

    $doc = $collection->findOne(['template' => $template, 'slot' => $slot]);
    if (!$doc) {
        respond(false, 'Cover not found');
    }

    $urlField = $side . '_url';
    $existingUrl = isset($doc[$urlField]) ? (string)$doc[$urlField] : '';

    if ($existingUrl && $bunnyStorageZone && $bunnyAccessKey) {
        // Convert CDN URL back to storage path: assume it contains /Yearbook%20Covers/...
        $pathStart = strpos($existingUrl, '/Yearbook%20Covers/');
        if ($pathStart !== false) {
            $relative = substr($existingUrl, $pathStart + 1); // remove leading '/'
            $storageUrl = 'https://storage.bunnycdn.com/' . $bunnyStorageZone . '/' . $relative;

            $ch = curl_init($storageUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'AccessKey: ' . $bunnyAccessKey,
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    $collection->updateOne(
        ['template' => $template, 'slot' => $slot],
        ['$unset' => [$urlField => ""], '$set' => ['updated_at' => new MongoDB\BSON\UTCDateTime()]]
    );

    respond(true, 'Cover deleted');
} catch (Exception $e) {
    respond(false, 'Failed to delete cover: ' . $e->getMessage());
}
?>


