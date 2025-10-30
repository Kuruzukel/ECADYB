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

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {
    $slot = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
    if (!$slot || $slot < 1 || $slot > 9) {
        respond(false, 'Invalid slot. Must be between 1 and 9.');
    }

    require_once __DIR__ . '/../Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();

    try {
        $bunnyCfg = getBunnyConfig();
        $bunnyStorageZone = $bunnyCfg['storage_zone'];
        $bunnyAccessKey = $bunnyCfg['access_key'];
        $bunnyCdnHost = $bunnyCfg['cdn_host'];
    } catch (Exception $e) {
        respond(false, 'Bunny CDN configuration incomplete', [
            'missing' => [
                'BUNNY_STORAGE_ZONE' => (bool) (getenv('BUNNY_STORAGE_ZONE') ?: ($_ENV['BUNNY_STORAGE_ZONE'] ?? null)),
                'BUNNY_ACCESS_KEY' => (bool) (getenv('BUNNY_ACCESS_KEY') ?: ($_ENV['BUNNY_ACCESS_KEY'] ?? null)),
                'BUNNY_CDN_HOST' => (bool) (getenv('BUNNY_CDN_HOST') ?: ($_ENV['BUNNY_CDN_HOST'] ?? null)),
            ]
        ]);
    }

    try {
        $client = new Client($mongoUrl, [
            'serverSelectionTimeoutMS' => 5000,
            'connectTimeoutMS' => 5000,
            'socketTimeoutMS' => 5000
        ]);
        $db = $client->admin;
        $collection = $db->logo;

        $doc = $collection->findOne(['type' => 'logo_container', 'slot' => $slot]);
        if (!$doc) {
            respond(false, 'Logo not found');
        }

        $url = (string)($doc['url'] ?? '');
        if (!$url) {
            respond(false, 'Logo URL is missing');
        }

        $pathStart = strpos($url, '/Logo%20Container/');
        if ($pathStart === false) {
            respond(false, 'Invalid logo URL format');
        }

        $relative = substr($url, $pathStart + 1);
        $storageUrl = 'https://storage.bunnycdn.com/' . $bunnyStorageZone . '/' . $relative;

        $ch = curl_init($storageUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['AccessKey: ' . $bunnyAccessKey]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 && $httpCode !== 404) {
            error_log("Failed to delete logo from BunnyCDN. HTTP $httpCode");
            respond(false, 'Failed to delete logo from storage');
        }

        $result = $collection->deleteOne(['type' => 'logo_container', 'slot' => $slot]);

        if ($result->getDeletedCount() === 0) {
            respond(false, 'Failed to delete logo from database');
        }

        respond(true, 'Logo deleted successfully');
    } catch (Exception $e) {
        error_log('Logo deletion error: ' . $e->getMessage());
        respond(false, 'Failed to delete logo: ' . $e->getMessage());
    }
} catch (Exception $e) {
    respond(false, 'Failed to delete logo: ' . $e->getMessage());
}
