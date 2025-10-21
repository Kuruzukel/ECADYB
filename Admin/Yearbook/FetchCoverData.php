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
    $batchYear = isset($_GET['batch_year']) ? trim($_GET['batch_year']) : null;
    
    $departmentSlots = [
        'BSME' => 1,
        'BSCJ' => 2,
        'BSTM' => 3,
        'BSE' => 4,
        'BSN' => 5,
        'BSIS' => 6,
        'BSBA' => 7
    ];

    $departmentCode = isset($_GET['department']) ? strtoupper($_GET['department']) : null;

    $slot = $departmentCode && isset($departmentSlots[$departmentCode])
        ? $departmentSlots[$departmentCode]
        : 1;

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

    // Use ECADYB database instead of BatchTemplate databases
    $db = $client->ECADYB;
    $collection = $db->Yearbook_Covers;

    // Build query - prioritize batch_year if provided, otherwise use template
    $query = ['slot' => $slot];
    $backgroundQuery = ['slot' => 8];
    
    if ($batchYear) {
        $query['batch_year'] = $batchYear;
        $backgroundQuery['batch_year'] = $batchYear;
        error_log("FetchCoverData: Querying by batch_year: $batchYear, slot: $slot");
    } else {
        $query['template'] = $template;
        $backgroundQuery['template'] = $template;
        error_log("FetchCoverData: Querying by template: $template, slot: $slot");
    }

    $cover = $collection->findOne($query);
    $background = $collection->findOne($backgroundQuery);

    error_log("Searching for template: " . $template . ", slot: " . $slot);
    error_log("Background data found: " . ($background ? json_encode($background) : "none"));
    if ($cover) {
        error_log("Cover found: " . json_encode($cover));
    } else {
        error_log("No cover found for template: " . $template . ", slot: " . $slot);
    }

    // Return empty/default data if no cover found instead of throwing error
    if (!$cover) {
        $response = [
            '_id' => '',
            'slot' => $slot,
            'template' => $template,
            'front_url' => '',
            'back_url' => '',
            'front_thumb_url' => '',
            'back_thumb_url' => '',
            'background_url' => $background && isset($background['background_url']) ? (string)$background['background_url'] : '',
            'background_thumb_url' => $background && isset($background['background_thumb_url']) ? (string)$background['background_thumb_url'] : '',
            'created_at' => null,
            'updated_at' => null,
            'department' => null,
            'department_page' => null
        ];

        error_log("FetchCoverData response (no cover): " . json_encode($response));

        echo json_encode([
            'success' => true,
            'data' => $response,
            'message' => 'No cover data found for this template and slot'
        ]);
        exit;
    }

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

    $departmentMap = [
        'BSBA' => 'BusinessAdministration',
        'BSCJ' => 'Criminology',
        'BSE'  => 'Education',
        'BSIS' => 'InformationSystem',
        'BSME' => 'Maritime',
        'BSN'  => 'Nursing',
        'BSTM' => 'Tourism'
    ];

    function extractDepartmentCode($filename)
    {
        $code = substr(strtoupper(pathinfo($filename, PATHINFO_FILENAME)), 0, 4);

        if (strpos($code, 'BSCJ') === 0) {
            $code = 'BSCJ';
        }

        return $code;
    }

    $departmentCode = null;
    if (isset($cover['front_url'])) {
        $departmentCode = extractDepartmentCode(basename($cover['front_url']));
    } elseif (isset($cover['back_url'])) {
        $departmentCode = extractDepartmentCode(basename($cover['back_url']));
    }

    $response['department'] = $departmentCode;
    $response['department_page'] = isset($departmentMap[$departmentCode])
        ? $departmentMap[$departmentCode]
        : null;

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
