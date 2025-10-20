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
if (file_exists(__DIR__ . '/../Configuration/BunnyConfig.php')) {
    require __DIR__ . '/../Configuration/BunnyConfig.php';
}

use MongoDB\Client;

try {
    $slot     = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
    $side     = isset($_POST['side']) ? strtolower(trim($_POST['side'])) : '';
    $batchYear = isset($_POST['batch_year']) ? trim($_POST['batch_year']) : '';

    error_log("DeleteCover.php received parameters: slot=$slot, side=$side, batch_year=$batchYear");

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
        'serverSelectionTimeoutMS' => 10000,
        'connectTimeoutMS'         => 10000,
        'socketTimeoutMS'          => 20000
    ]);

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

    // Build query filter - use both slot AND batch_year (same as upload)
    // This ensures each batch year has its own separate documents
    $filter = ['slot' => $slot];
    if (!empty($batchYear)) {
        $filter['batch_year'] = $batchYear;
    }
    
    error_log("DeleteCover.php query filter: " . json_encode($filter));
    
    $doc = $collection->findOne($filter);
    if (!$doc) {
        error_log("DeleteCover.php: No document found for slot $slot, batch_year $batchYear");
        respond(false, 'Cover not found');
    }
    
    error_log("DeleteCover.php found document: " . json_encode($doc->toArray()));

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
        
        // Extract the path from the CDN URL
        // CDN URL format: https://ECADYB.b-cdn.net/Yearbook%20Covers/...
        // Storage URL format: https://storage.bunnycdn.com/ecadyb/Yearbook%20Covers/...
        $relativePath = ltrim($parsed['path'], '/');
        
        // Decode URL encoding to get the actual path
        $relativePath = urldecode($relativePath);
        
        // Build the storage API URL
        $storageUrl = 'https://storage.bunnycdn.com/' . $zone . '/' . $relativePath;

        error_log("DeleteCover.php attempting to delete from BunnyCDN storage: $storageUrl");

        $ch = curl_init($storageUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['AccessKey: ' . $key]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 404) {
            error_log("DeleteCover.php successfully deleted from BunnyCDN. HTTP $httpCode");
            return true;
        } else {
            error_log("DeleteCover.php FAILED to delete from BunnyCDN. HTTP $httpCode, Error: $curlError, Response: $response");
            return false;
        }
    }

    $unsetFields = [];
    $bunnyDeleteSuccess = false;

    if ($slot === 8) {
        $existingUrl = isset($doc['background_url']) ? (string)$doc['background_url'] : '';

        if (!empty($existingUrl)) {
            $bunnyDeleteSuccess = deleteFromBunny($existingUrl, $bunnyStorageZone, $bunnyAccessKey);
        } else {
            error_log("DeleteCover.php: No background_url found in document");
            $bunnyDeleteSuccess = true; // No URL to delete, consider it successful
        }

        $unsetFields = [
            'background_url' => "",
            'background_filename' => "",
            'background_original_name' => "",
            'background_side' => ""
        ];
    } else {
        $urlField = $side . '_url';
        $filenameField = $side . '_filename';
        $originalNameField = $side . '_original_name';
        $sideField = $side . '_side';

        $existingUrl = isset($doc[$urlField]) ? (string)$doc[$urlField] : '';

        error_log("DeleteCover.php deleting URL - main: $existingUrl");

        if (!empty($existingUrl)) {
            $bunnyDeleteSuccess = deleteFromBunny($existingUrl, $bunnyStorageZone, $bunnyAccessKey);
        } else {
            error_log("DeleteCover.php: No $urlField found in document");
            $bunnyDeleteSuccess = true; // No URL to delete, consider it successful
        }

        $unsetFields = [
            $urlField => "",
            $filenameField => "",
            $originalNameField => "",
            $sideField => ""
        ];
    }

    error_log("DeleteCover.php BunnyCDN deletion result: " . ($bunnyDeleteSuccess ? "SUCCESS" : "FAILED"));
    error_log("DeleteCover.php unsetting fields: " . json_encode($unsetFields));

    // Build update filter - use both slot AND batch_year (same as upload)
    $updateFilter = ['slot' => $slot];
    if (!empty($batchYear)) {
        $updateFilter['batch_year'] = $batchYear;
    }
    
    error_log("DeleteCover.php update filter: " . json_encode($updateFilter));

    $result = $collection->updateOne(
        $updateFilter,
        [
            '$unset' => $unsetFields,
            '$set'   => ['updated_at' => new MongoDB\BSON\UTCDateTime()]
        ]
    );

    error_log("DeleteCover.php update result: matched=" . $result->getMatchedCount() . ", modified=" . $result->getModifiedCount());
    
    if ($result->getMatchedCount() === 0) {
        respond(false, 'Cover not found in database');
    }
    
    if ($result->getModifiedCount() === 0) {
        error_log("DeleteCover.php WARNING: MongoDB update matched document but did not modify it");
    }
    
    // Verify the field was actually removed
    $verifyDoc = $collection->findOne($updateFilter);
    if ($verifyDoc) {
        $fieldToCheck = ($slot === 8) ? 'background_url' : ($side . '_url');
        if (isset($verifyDoc[$fieldToCheck]) && !empty($verifyDoc[$fieldToCheck])) {
            error_log("DeleteCover.php ERROR: Field $fieldToCheck still exists after unset operation!");
            respond(false, 'Failed to remove cover from database');
        }
    }
    
    // Check if the batch year is still complete after deletion
    if (!empty($batchYear)) {
        // Get all documents for this batch year
        $batchYearDocs = $collection->find(['batch_year' => $batchYear])->toArray();
        
        // Check if all 8 slots still have images
        $slotsWithImages = [];
        foreach ($batchYearDocs as $doc) {
            $docSlot = (int)($doc['slot'] ?? 0);
            $hasFront = isset($doc['front_url']) && !empty($doc['front_url']);
            $hasBack = isset($doc['back_url']) && !empty($doc['back_url']);
            $hasBackground = isset($doc['background_url']) && !empty($doc['background_url']);
            
            if ($docSlot === 8 && $hasBackground) {
                $slotsWithImages[] = 8;
            } elseif ($docSlot >= 1 && $docSlot <= 7 && ($hasFront && $hasBack)) {
                // Both front and back must be present for slots 1-7
                $slotsWithImages[] = $docSlot;
            }
        }
        
        $slotsWithImages = array_unique($slotsWithImages);
        $isComplete = count($slotsWithImages) === 8;
        
        error_log("DeleteCover.php: After deletion, slots with images: " . count($slotsWithImages) . "/8, isComplete: " . ($isComplete ? "true" : "false"));
        
        if (!$isComplete) {
            // Remove completion_date if batch is now incomplete
            $updateResult = $collection->updateMany(
                ['batch_year' => $batchYear],
                ['$unset' => ['completion_date' => '']],
                ['upsert' => false]
            );
            error_log("DeleteCover.php: Batch year $batchYear is now incomplete after deletion. Slots filled: " . count($slotsWithImages) . "/8. Updated " . $updateResult->getModifiedCount() . " documents.");
        }
    }

    respond(true, 'Cover deleted successfully');
} catch (Exception $e) {
    respond(false, 'Failed to delete cover: ' . $e->getMessage());
}
