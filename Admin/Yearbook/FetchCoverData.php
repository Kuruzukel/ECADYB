<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;
    $departmentSlots = [
        'BSME' => 1,
        'BSCJ' => 2,
        'BSTM' => 3,
        'BSE' => 4,
        'BSN' => 5,    // Nursing
        'BSIS' => 6,   // Information System
        'BSBA' => 7    // Business Administration
    ];

    // Get department code from URL parameter
    $departmentCode = isset($_GET['department']) ? strtoupper($_GET['department']) : null;

    // Determine slot based on department code
    $slot = $departmentCode && isset($departmentSlots[$departmentCode])
        ? $departmentSlots[$departmentCode]
        : 1; // Default to slot 1 if no match

    if ($template < 1 || $template > 3) {
        throw new Exception('Invalid template parameter. Must be 1, 2, or 3.');
    }

    if ($slot < 1 || $slot > 8) {
        throw new Exception('Invalid slot parameter. Must be between 1 and 8.');
    }

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 5000
    ]);

    $dbName = "BatchTemplate" . $template;
    $db = $client->$dbName;
    $collection = $db->YearbookCovers;

    // Find the specific cover and background (slot 8)
    $cover = $collection->findOne(['template' => $template, 'slot' => $slot]);
    $background = $collection->findOne(['template' => $template, 'slot' => 8]);

    // Debug: Log what we found
    error_log("Searching for template: " . $template . ", slot: " . $slot);
    error_log("Background data found: " . ($background ? json_encode($background) : "none"));
    if ($cover) {
        error_log("Cover found: " . json_encode($cover));
    } else {
        error_log("No cover found for template: " . $template . ", slot: " . $slot);
    }

    if (!$cover) {
        throw new Exception('Cover not found for template ' . $template . ' and slot ' . $slot);
    }

    // Format the response
    $response = [
        '_id' => (string)$cover['_id'],
        'slot' => (int)$cover['slot'],
        'template' => (int)$cover['template'],
        'front_url' => isset($cover['front_url']) ? (string)$cover['front_url'] : '',
        'back_url' => isset($cover['back_url']) ? (string)$cover['back_url'] : '',
        'front_thumb_url' => isset($cover['front_thumb_url']) ? (string)$cover['front_thumb_url'] : '',
        'back_thumb_url' => isset($cover['back_thumb_url']) ? (string)$cover['back_thumb_url'] : '',
        'background_url' => $background && isset($background['background_url']) ? (string)$background['background_url'] : '',
        'background_thumb_url' => $background && isset($background['background_thumb_url']) ? (string)$background['background_thumb_url'] : '',
        'created_at' => isset($cover['created_at']) ? $cover['created_at']->toDateTime()->format('c') : null,
        'updated_at' => isset($cover['updated_at']) ? $cover['updated_at']->toDateTime()->format('c') : null
    ];

    // Department code mapping based on first 4 letters of filename
    $departmentMap = [
        'BSBA' => 'BusinessAdministration',
        'BSCJ' => 'Criminology',
        'BSE'  => 'Education',
        'BSIS' => 'InformationSystem',
        'BSME' => 'Maritime',
        'BSN'  => 'Nursing',
        'BSTM' => 'Tourism'
    ];

    // Function to extract department code from filename
    function extractDepartmentCode($filename)
    {
        // Extract first 4 letters from the filename
        $code = substr(strtoupper(pathinfo($filename, PATHINFO_FILENAME)), 0, 4);

        // Special handling for Criminal Justice (BSCJ)
        if (strpos($code, 'BSCJ') === 0) {
            $code = 'BSCJ';
        }

        return $code;
    }

    // Determine department based on front and back cover URLs
    $departmentCode = null;
    if (isset($cover['front_url'])) {
        $departmentCode = extractDepartmentCode(basename($cover['front_url']));
    } elseif (isset($cover['back_url'])) {
        $departmentCode = extractDepartmentCode(basename($cover['back_url']));
    }

    // Add department information to the response
    $response['department'] = $departmentCode;
    $response['department_page'] = isset($departmentMap[$departmentCode])
        ? $departmentMap[$departmentCode]
        : null;

    // Debug: Print the actual data being returned
    error_log("FetchCoverData response: " . json_encode($response));

    echo json_encode([
        'success' => true,
        'data' => $response
    ]);
} catch (Exception $e) {
    error_log("FetchCoverData error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
