<?php

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
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

    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/json');

    $response = array_merge(['success' => $success, 'message' => $message], $data);
    echo json_encode($response);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method');
}

require __DIR__ . '/../../vendor/autoload.php';
if (file_exists(__DIR__ . '/../Configuration/BunnyConfig.php')) {
    require __DIR__ . '/../Configuration/BunnyConfig.php';
}

use MongoDB\Client;

try {
    ob_clean();

    $slot     = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
    $side     = isset($_POST['side']) ? strtolower(trim($_POST['side'])) : '';
    $batchYear = isset($_POST['batch_year']) ? trim($_POST['batch_year']) : '';
    $template = isset($_POST['template']) ? (int)$_POST['template'] : 1;

    error_log("DeleteCover.php received parameters: slot=$slot, side=$side, batch_year='$batchYear', template=$template");

    if ($slot === null) {
        error_log("DeleteCover.php ERROR: Missing slot parameter");
        respond(false, 'Missing slot parameter.');
    }

    if ($slot !== 8 && ($side !== 'front' && $side !== 'back')) {
        error_log("DeleteCover.php ERROR: Invalid side parameter: '$side'");
        respond(false, 'Invalid parameters. Side must be "front" or "back" unless slot=8.');
    }

    if (empty($batchYear)) {
        error_log("DeleteCover.php WARNING: batch_year is empty, will search without batch_year filter");
    }

    $mongoUrl        = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    $bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
    $bunnyAccessKey   = getenv('BUNNY_ACCESS_KEY') ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));

    error_log("DeleteCover.php BunnyCDN credentials - Zone: $bunnyStorageZone, AccessKey: " . ($bunnyAccessKey ? 'SET' : 'NOT SET'));

    try {
        $client = new Client($mongoUrl, [
            'serverSelectionTimeoutMS' => 10000,
            'connectTimeoutMS'         => 10000,
            'socketTimeoutMS'          => 20000
        ]);
    } catch (Exception $e) {
        error_log("DeleteCover.php MongoDB connection error: " . $e->getMessage());
        respond(false, 'Database connection failed: ' . $e->getMessage());
    }

    $dbName = "ECADYB";
    $db = $client->$dbName;
    $collection = $db->Yearbook_Covers;

    error_log("DeleteCover.php using database: $dbName, collection: Covers");

    try {
        $databases = $client->listDatabases();
        $dbExists = false;
        foreach ($databases as $database) {
            if ($database->getName() === $dbName) {
                $dbExists = true;
                break;
            }
        }
        error_log("DeleteCover.php database $dbName exists: " . ($dbExists ? "true" : "false"));

        if ($dbExists) {
            $collections = $db->listCollections();
            $collectionNames = [];
            foreach ($collections as $collectionInfo) {
                $collectionNames[] = $collectionInfo->getName();
            }
            error_log("DeleteCover.php collections in $dbName: " . json_encode($collectionNames));
        }
    } catch (Exception $e) {
        error_log("DeleteCover.php error checking databases: " . $e->getMessage());
    }

    $filter = ['slot' => $slot, 'template' => $template];
    if (!empty($batchYear)) {
        $filter['batch_year'] = $batchYear;
    }

    error_log("DeleteCover.php query filter: " . json_encode($filter));

    $allDocsForSlot = $collection->find(['slot' => $slot])->toArray();
    error_log("DeleteCover.php found " . count($allDocsForSlot) . " documents for slot $slot");
    foreach ($allDocsForSlot as $idx => $d) {
        error_log("DeleteCover.php document $idx for slot $slot: batch_year='" . ($d['batch_year'] ?? 'null') . "'");
    }

    $doc = $collection->findOne($filter);

    if (!$doc) {
        error_log("DeleteCover.php: No document found with filter " . json_encode($filter));
        if (!empty($batchYear)) {
            error_log("DeleteCover.php: Trying to find document without batch_year filter");
            $doc = $collection->findOne(['slot' => $slot]);
        }
    }

    if (!$doc) {
        error_log("DeleteCover.php: No document found for slot $slot, batch_year '$batchYear' - already deleted or never existed");
        respond(false, 'Cover not found for this batch year');
    }

    error_log("DeleteCover.php found document: " . json_encode($doc));

    function deleteFromBunny($cdnUrl, $zone, $key)
    {
        if (!$cdnUrl || !$zone || !$key) {
            error_log("DeleteCover.php: Missing required parameters for BunnyCDN deletion. URL: " . ($cdnUrl ?: 'empty') . ", Zone: " . ($zone ?: 'empty'));
            return false;
        }

        error_log("DeleteCover.php original CDN URL: $cdnUrl");

        $parsed = parse_url($cdnUrl);
        if (empty($parsed['path'])) {
            error_log("DeleteCover.php: Invalid URL path for BunnyCDN deletion. URL: $cdnUrl");
            return false;
        }

        $relativePath = ltrim($parsed['path'], '/');

        $storageUrl = 'https://storage.bunnycdn.com/' . $zone . '/' . $relativePath;

        error_log("DeleteCover.php attempting to delete from BunnyCDN storage: $storageUrl");

        $ch = curl_init($storageUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['AccessKey: ' . $key]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        error_log("DeleteCover.php BunnyCDN delete response: HTTP $httpCode, Response: " . ($response ?: 'empty'));

        if ($httpCode === 200 || $httpCode === 404) {
            error_log("DeleteCover.php successfully deleted from BunnyCDN. HTTP $httpCode");
            return true;
        } else {
            error_log("DeleteCover.php FAILED to delete from BunnyCDN. HTTP $httpCode, Error: $curlError, Response: $response");
            return false;
        }
    }

    $bunnyDeleteSuccess = false;

    if ($slot === 8) {
        $existingUrl = isset($doc['background_url']) ? (string)$doc['background_url'] : '';
        if (!empty($existingUrl)) {
            $bunnyDeleteSuccess = deleteFromBunny($existingUrl, $bunnyStorageZone, $bunnyAccessKey);
        } else {
            error_log("DeleteCover.php: No background_url found in document");
            $bunnyDeleteSuccess = true;
        }
    } else {
        $urlField = $side . '_url';
        $existingUrl = isset($doc[$urlField]) ? (string)$doc[$urlField] : '';

        error_log("DeleteCover.php deleting URL - main: $existingUrl");

        if (!empty($existingUrl)) {
            $bunnyDeleteSuccess = deleteFromBunny($existingUrl, $bunnyStorageZone, $bunnyAccessKey);
        } else {
            error_log("DeleteCover.php: No $urlField found in document");
            $bunnyDeleteSuccess = true;
        }
    }

    if ($bunnyDeleteSuccess) {
        error_log("DeleteCover.php BunnyCDN deletion: SUCCESS");
    } else {
        error_log("DeleteCover.php BunnyCDN deletion: FAILED (but will continue with MongoDB deletion)");
    }

    $updateFilter = ['slot' => $slot, 'template' => $template];
    if (!empty($batchYear)) {
        $updateFilter['batch_year'] = $batchYear;
    }

    error_log("DeleteCover.php update filter: " . json_encode($updateFilter));
    error_log("DeleteCover.php IMPORTANT: This will ONLY unset the $side image for batch_year '$batchYear', slot $slot");

    $unsetFields = [];

    if ($slot === 8) {
        $unsetFields['background_url'] = '';
        $unsetFields['background_filename'] = '';
        $unsetFields['background_original_name'] = '';
        $unsetFields['background_side'] = '';
    } else {
        $unsetFields[$side . '_url'] = '';
        $unsetFields[$side . '_filename'] = '';
        $unsetFields[$side . '_original_name'] = '';
        $unsetFields[$side . '_side'] = '';
    }

    $result = $collection->updateOne(
        $updateFilter,
        ['$unset' => $unsetFields]
    );

    error_log("DeleteCover.php update result: matched=" . $result->getMatchedCount() . ", modified=" . $result->getModifiedCount());

    if ($result->getMatchedCount() === 0) {
        error_log("DeleteCover.php ERROR: No document found for batch_year '$batchYear', slot $slot");
        respond(false, 'Cover not found in database for this batch year. Please check if the image exists.');
    } else {
        error_log("DeleteCover.php SUCCESS: $side image unset from database for batch_year '$batchYear', slot $slot");
    }

    $updatedDoc = $collection->findOne($updateFilter);
    if ($updatedDoc) {
        $hasFront = isset($updatedDoc['front_url']) && !empty($updatedDoc['front_url']);
        $hasBack = isset($updatedDoc['back_url']) && !empty($updatedDoc['back_url']);
        $hasBackground = isset($updatedDoc['background_url']) && !empty($updatedDoc['background_url']);

        if (($slot === 8 && !$hasBackground) || ($slot !== 8 && !$hasFront && !$hasBack)) {
            $deleteResult = $collection->deleteOne($updateFilter);
            error_log("DeleteCover.php: Document completely empty, deleted from database. Deleted count: " . $deleteResult->getDeletedCount());
        }
    }

    if (!empty($batchYear)) {
        $batchYearDocs = $collection->find(['batch_year' => $batchYear, 'template' => $template])->toArray();

        $slotsWithImages = [];
        foreach ($batchYearDocs as $doc) {
            $docSlot = (int)($doc['slot'] ?? 0);
            $hasFront = isset($doc['front_url']) && !empty($doc['front_url']);
            $hasBack = isset($doc['back_url']) && !empty($doc['back_url']);
            $hasBackground = isset($doc['background_url']) && !empty($doc['background_url']);

            if ($docSlot === 8 && $hasBackground) {
                $slotsWithImages[] = 8;
            } elseif ($docSlot >= 1 && $docSlot <= 7 && ($hasFront && $hasBack)) {
                $slotsWithImages[] = $docSlot;
            }
        }

        $slotsWithImages = array_unique($slotsWithImages);
        $isComplete = count($slotsWithImages) === 8;

        error_log("DeleteCover.php: After deletion, slots with images: " . count($slotsWithImages) . "/8, isComplete: " . ($isComplete ? "true" : "false"));

        if (!$isComplete) {
            $updateResult = $collection->updateMany(
                ['batch_year' => $batchYear, 'template' => $template],
                ['$unset' => ['completion_date' => '']],
                ['upsert' => false]
            );
            error_log("DeleteCover.php: Batch year $batchYear is now incomplete after deletion. Slots filled: " . count($slotsWithImages) . "/8. Updated " . $updateResult->getModifiedCount() . " documents.");
        }
    }

    respond(true, 'Cover deleted successfully');
} catch (Exception $e) {
    error_log("DeleteCover.php Exception: " . $e->getMessage());
    error_log("DeleteCover.php Stack trace: " . $e->getTraceAsString());
    respond(false, 'Failed to delete cover: ' . $e->getMessage());
} catch (Error $e) {
    error_log("DeleteCover.php Error: " . $e->getMessage());
    error_log("DeleteCover.php Stack trace: " . $e->getTraceAsString());
    respond(false, 'Failed to delete cover: ' . $e->getMessage());
}
