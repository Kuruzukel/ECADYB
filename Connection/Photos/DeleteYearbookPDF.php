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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        throw new Exception('Invalid request method');
    }

    // Get the PDF ID or department/batch_year
    $pdfId = $_POST['pdf_id'] ?? $_GET['pdf_id'] ?? '';
    $department = $_POST['department'] ?? $_GET['department'] ?? '';
    $batchYear = $_POST['batch_year'] ?? $_GET['batch_year'] ?? '';

    // Get the yearbook PDFs collection
    $pdfsCollection = $client->ECADYB->Yearbook_PDFs;

    // Build query
    if (!empty($pdfId)) {
        // Delete by ID
        $result = $pdfsCollection->deleteOne([
            '_id' => new MongoDB\BSON\ObjectId($pdfId)
        ]);
    } elseif (!empty($department) && !empty($batchYear)) {
        // Ensure batch year has "Batch Year" prefix
        if (strpos($batchYear, 'Batch Year') === false) {
            $batchYear = 'Batch Year ' . $batchYear;
        }

        // Delete by department and batch year
        $result = $pdfsCollection->deleteOne([
            'department' => $department,
            'batch_year' => $batchYear
        ]);
    } else {
        throw new Exception('PDF ID or (department and batch_year) required');
    }

    if ($result->getDeletedCount() > 0) {
        error_log("DeleteYearbookPDF: Deleted PDF successfully");
        echo json_encode([
            'success' => true,
            'message' => 'PDF deleted successfully'
        ]);
    } else {
        error_log("DeleteYearbookPDF: No PDF found to delete");
        echo json_encode([
            'success' => false,
            'error' => 'PDF not found'
        ]);
    }
} catch (Exception $e) {
    error_log("DeleteYearbookPDF Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
