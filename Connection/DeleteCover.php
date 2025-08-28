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

// 🔑 Load BunnyCDN credentials
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
$bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
$bunnyAccessKey = getenv('BUNNY_ACCESS_KEY') ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));

try {
    $client = new Client($mongoUrl);
    $db = $client->Departments;
    $collection = $db->YearbookCovers;

    // 🔎 Find the specific cover
    $doc = $collection->findOne(['template' => $template, 'slot' => $slot]);
    if (!$doc) {
        respond(false, 'Cover not found');
    }

    $urlField = $side . '_url';
    $existingUrl = isset($doc[$urlField]) ? (string)$doc[$urlField] : '';

    // 🗑️ Delete from Bunny Storage if exists
    if ($existingUrl && $bunnyStorageZone && $bunnyAccessKey) {
        // Extract relative path from CDN URL
        $parsed = parse_url($existingUrl);
        if (!empty($parsed['path'])) {
            // Ensure path starts without leading slash
            $relativePath = ltrim($parsed['path'], '/');
            $storageUrl = 'https://storage.bunnycdn.com/' . $bunnyStorageZone . '/' . $relativePath;

            $ch = curl_init($storageUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'AccessKey: ' . $bunnyAccessKey,
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 && $httpCode !== 404) {
                respond(false, "Failed to delete from BunnyCDN. HTTP $httpCode. Response: " . $response);
            }
        }
    }

    // ❌ Remove from MongoDB
    $collection->updateOne(
        ['template' => $template, 'slot' => $slot],
        ['$unset' => [$urlField => ""], '$set' => ['updated_at' => new MongoDB\BSON\UTCDateTime()]]
    );

    respond(true, 'Cover deleted successfully');
} catch (Exception $e) {
    respond(false, 'Failed to delete cover: ' . $e->getMessage());
}