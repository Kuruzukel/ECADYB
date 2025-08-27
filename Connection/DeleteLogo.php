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
if (!$slot || $slot < 1 || $slot > 9) {
    respond(false, 'Invalid slot.');
}

$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
$bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
$bunnyAccessKey = getenv('BUNNY_ACCESS_KEY') ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));

try {
    $client = new Client($mongoUrl);
    $db = $client->Departments;
    $collection = $db->DashboardAssets;

    $doc = $collection->findOne(['type' => 'logo_container', 'slot' => $slot]);
    if (!$doc) respond(false, 'Logo not found');

    $url = (string)($doc['url'] ?? '');
    if ($url && $bunnyStorageZone && $bunnyAccessKey) {
        $pathStart = strpos($url, '/Logo%20Container/');
        if ($pathStart !== false) {
            $relative = substr($url, $pathStart + 1);
            $storageUrl = 'https://storage.bunnycdn.com/' . $bunnyStorageZone . '/' . $relative;
            $ch = curl_init($storageUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'AccessKey: ' . $bunnyAccessKey ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    $collection->deleteOne(['type' => 'logo_container', 'slot' => $slot]);
    respond(true, 'Logo deleted');
} catch (Exception $e) {
    respond(false, 'Failed to delete logo: ' . $e->getMessage());
}

