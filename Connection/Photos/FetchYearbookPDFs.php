<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Configuration/config.php';
require_once __DIR__ . '/../Configuration/MongoConnect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Unauthorized access']));
}

header('Content-Type: application/json');

try {
    $batchYear = $_GET['batch_year'] ?? '';

    $pdfsCollection = $client->ECADYB->Yearbook_PDFs;

    $query = [];
    if (!empty($batchYear)) {
        if (strpos($batchYear, 'Batch Year') === false) {
            $batchYear = 'Batch Year ' . $batchYear;
        }
        $query['batch_year'] = $batchYear;
    }

    $pdfs = $pdfsCollection->find($query, [
        'sort' => ['created_at' => -1]
    ])->toArray();

    $result = [];
    foreach ($pdfs as $pdf) {
        $result[] = [
            '_id' => (string) $pdf['_id'],
            'department' => $pdf['department'] ?? '',
            'batch_year' => $pdf['batch_year'] ?? '',
            'pdf_url' => $pdf['pdf_url'] ?? '',
            'created_at' => isset($pdf['created_at']) ? $pdf['created_at']->toDateTime()->format('Y-m-d H:i:s') : null,
            'created_by' => $pdf['created_by'] ?? null,
            'updated_at' => isset($pdf['updated_at']) ? $pdf['updated_at']->toDateTime()->format('Y-m-d H:i:s') : null,
            'updated_by' => $pdf['updated_by'] ?? null
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $result,
        'count' => count($result)
    ]);
} catch (Exception $e) {
    error_log("FetchYearbookPDFs Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
