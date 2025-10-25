<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

ini_set('memory_limit', '256M');
ini_set('max_execution_time', '60');
set_time_limit(60);

ob_start();

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

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) {
            ob_end_clean();
        }

        error_log("FetchCoverData Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);

        $response = [
            'success' => false,
            'message' => 'Server error occurred while fetching cover data',
            'error_details' => $error['message']
        ];

        $jsonOutput = json_encode($response);
        header('Content-Type: application/json');
        header('Content-Length: ' . strlen($jsonOutput));
        echo $jsonOutput;
        exit;
    }
});

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

    require_once __DIR__ . '/../../Connection/Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();

    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 5000
    ]);

    $db = $client->ECADYB;
    $collection = $db->Yearbook_Covers;

    $query = ['slot' => $slot, 'template' => $template];
    $backgroundQuery = ['slot' => 8, 'template' => $template];

    if ($batchYear) {
        $query['batch_year'] = $batchYear;
        $backgroundQuery['batch_year'] = $batchYear;
        error_log("FetchCoverData: Querying by batch_year: $batchYear, template: $template, slot: $slot");
    } else {
        error_log("FetchCoverData: Querying by template: $template, slot: $slot (no batch_year specified)");
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

    if (!$cover) {
        $response = [
            '_id' => '',
            'slot' => $slot,
            'template' => $template,
            'batch_year' => $batchYear,
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

        if (ob_get_level()) {
            ob_clean();
        }

        $jsonOutput = json_encode([
            'success' => true,
            'data' => $response,
            'message' => 'No cover data found for this template and slot'
        ]);

        header('Content-Type: application/json');
        header('Content-Length: ' . strlen($jsonOutput));
        echo $jsonOutput;
        exit;
    }

    $response = [
        '_id' => (string)$cover['_id'],
        'slot' => (int)$cover['slot'],
        'template' => isset($cover['template']) ? (int)$cover['template'] : $template,
        'batch_year' => isset($cover['batch_year']) ? (string)$cover['batch_year'] : $batchYear,
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

    if (ob_get_level()) {
        ob_clean();
    }

    $jsonOutput = json_encode([
        'success' => true,
        'data' => $response
    ]);

    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($jsonOutput));
    echo $jsonOutput;
} catch (Exception $e) {
    error_log("FetchCoverData error: " . $e->getMessage());

    if (ob_get_level()) {
        ob_clean();
    }

    http_response_code(500);
    $jsonOutput = json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($jsonOutput));
    echo $jsonOutput;
}
