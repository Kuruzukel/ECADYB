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
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method');
}

require __DIR__ . '/../vendor/autoload.php';
if (file_exists(__DIR__ . '/BunnyConfig.php')) {
    require __DIR__ . '/BunnyConfig.php';
}

use MongoDB\Client;

try {
    $slot     = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
    $side     = isset($_POST['side']) ? strtolower(trim($_POST['side'])) : '';
    $template = isset($_POST['template']) ? (int)$_POST['template'] : 1;

    if ($slot === null) {
        respond(false, 'Missing slot parameter.');
    }

    if ($slot !== 8 && ($side !== 'front' && $side !== 'back')) {
        respond(false, 'Invalid parameters. Side must be "front" or "back" unless slot=8.');
    }

    $mongoUrl        = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    $bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
    $bunnyAccessKey   = getenv('BUNNY_ACCESS_KEY') ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));

    $client     = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS'         => 5000,
        'socketTimeoutMS'          => 5000
    ]);
    $db         = $client->Departments;
    $collection = $db->YearbookCovers;

    //  Find document
    $doc = $collection->findOne(['template' => $template, 'slot' => $slot]);
    if (!$doc) {
        respond(false, 'Cover not found');
    }

    // Delete from BunnyCDN
    function deleteFromBunny($cdnUrl, $zone, $key)
    {
        if (!$cdnUrl || !$zone || !$key) return;
        $parsed = parse_url($cdnUrl);
        if (!empty($parsed['path'])) {
            $relativePath = ltrim($parsed['path'], '/');
            $storageUrl   = 'https://storage.bunnycdn.com/' . $zone . '/' . $relativePath;

            $ch = curl_init($storageUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['AccessKey: ' . $key]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 && $httpCode !== 404) {
                error_log("Warning: Failed to delete $cdnUrl from BunnyCDN. HTTP $httpCode");
            }
        }
    }

    $unsetFields = [];

    if ($slot === 8) {
        // Background page
        $existingUrl      = isset($doc['background_url']) ? (string)$doc['background_url'] : '';
        $existingThumbUrl = isset($doc['background_thumb_url']) ? (string)$doc['background_thumb_url'] : '';

        deleteFromBunny($existingUrl, $bunnyStorageZone, $bunnyAccessKey);
        deleteFromBunny($existingThumbUrl, $bunnyStorageZone, $bunnyAccessKey);

        $unsetFields = [
            'background_url'       => "",
            'background_thumb_url' => ""
        ];
    } else {
        // Normal front/back slots
        $urlField   = $side . '_url';
        $thumbField = $side . '_thumb_url';

        $existingUrl      = isset($doc[$urlField]) ? (string)$doc[$urlField] : '';
        $existingThumbUrl = isset($doc[$thumbField]) ? (string)$doc[$thumbField] : '';

        deleteFromBunny($existingUrl, $bunnyStorageZone, $bunnyAccessKey);
        deleteFromBunny($existingThumbUrl, $bunnyStorageZone, $bunnyAccessKey);

        $unsetFields = [
            $urlField   => "",
            $thumbField => ""
        ];
    }

    // Update MongoDB (unset fields but keep slot record)
    $collection->updateOne(
        ['template' => $template, 'slot' => $slot],
        [
            '$unset' => $unsetFields,
            '$set'   => ['updated_at' => new MongoDB\BSON\UTCDateTime()]
        ]
    );

    respond(true, 'Cover deleted successfully');
} catch (Exception $e) {
    respond(false, 'Failed to delete cover: ' . $e->getMessage());
}
