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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $department = $_POST['department'] ?? '';
    $batchYear = $_POST['batch_year'] ?? '';
    $pdfUrl = $_POST['pdf_url'] ?? '';

    if (empty($department)) {
        throw new Exception('Department is required');
    }

    if (empty($batchYear)) {
        throw new Exception('Batch year is required');
    }

    if (empty($pdfUrl)) {
        throw new Exception('PDF URL is required');
    }

    if (strpos($batchYear, 'Batch Year') === false) {
        $batchYear = 'Batch Year ' . $batchYear;
    }

    error_log("UploadYearbookPDF: Uploading PDF for department: $department, batch_year: $batchYear, url: $pdfUrl");

    $pdfsCollection = $client->ECADYB->Yearbook_PDFs;

    $existingPdf = $pdfsCollection->findOne([
        'department' => $department,
        'batch_year' => $batchYear
    ]);

    $uploadTime = new MongoDB\BSON\UTCDateTime();

    if ($existingPdf) {
        // Update existing PDF
        $result = $pdfsCollection->updateOne(
            [
                'department' => $department,
                'batch_year' => $batchYear
            ],
            [
                '$set' => [
                    'pdf_url' => $pdfUrl,
                    'updated_at' => $uploadTime,
                    'updated_by' => $_SESSION['name'] ?? 'Unknown Admin'
                ]
            ]
        );

        error_log("UploadYearbookPDF: Updated existing PDF - Modified count: " . $result->getModifiedCount());

        echo json_encode([
            'success' => true,
            'message' => 'PDF updated successfully',
            'action' => 'updated'
        ]);
    } else {
        // Insert new PDF
        $result = $pdfsCollection->insertOne([
            'department' => $department,
            'batch_year' => $batchYear,
            'pdf_url' => $pdfUrl,
            'created_at' => $uploadTime,
            'created_by' => $_SESSION['name'] ?? 'Unknown Admin'
        ]);

        error_log("UploadYearbookPDF: Inserted new PDF - ID: " . $result->getInsertedId());

        echo json_encode([
            'success' => true,
            'message' => 'PDF uploaded successfully',
            'action' => 'created',
            'id' => (string) $result->getInsertedId()
        ]);
    }
} catch (Exception $e) {
    error_log("UploadYearbookPDF Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
