<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Configuration/config.php';
require_once __DIR__ . '/../Configuration/MongoConnect.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'admin')) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Unauthorized access']));
}

$department = $_GET['department'] ?? '';
$batchYear = $_GET['batch_year'] ?? '';

if (empty($department) || empty($batchYear)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Missing required parameters']));
}

error_log("DownloadYearbookPDF: Fetching PDF for department: $department, batch_year: $batchYear");

try {
    $pdfsCollection = $client->ECADYB->Yearbook_PDFs;

    $pdfDoc = $pdfsCollection->findOne([
        'department' => $department,
        'batch_year' => $batchYear
    ]);

    if (!$pdfDoc) {
        error_log("DownloadYearbookPDF: No PDF found for department: $department, batch_year: $batchYear");

        // Return a helpful error page
        http_response_code(404);
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>PDF Not Available</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: linear-gradient(135deg, #1e2a38 0%, #2c3e50 100%);
                    color: #e2e8f0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                    padding: 20px;
                }

                .error-container {
                    background: rgba(255, 255, 255, 0.05);
                    border: 2px solid rgba(252, 218, 21, 0.3);
                    border-radius: 20px;
                    padding: 40px;
                    text-align: center;
                    max-width: 500px;
                }

                .error-icon {
                    font-size: 64px;
                    color: #ef4444;
                    margin-bottom: 20px;
                }

                h1 {
                    color: #fcda15;
                    margin-bottom: 16px;
                }

                p {
                    color: #cbd5e1;
                    line-height: 1.6;
                    margin-bottom: 24px;
                }

                .back-btn {
                    display: inline-block;
                    padding: 12px 32px;
                    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 10px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }

                .back-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
                }
            </style>
        </head>

        <body>
            <div class="error-container">
                <i class="fas fa-file-excel error-icon"></i>
                <h1>PDF Not Available</h1>
                <p>The yearbook PDF for <strong><?php echo htmlspecialchars($department); ?></strong>
                    (<?php echo htmlspecialchars($batchYear); ?>) is not yet available.</p>
                <p>Please contact the administration or check back later.</p>
                <a href="javascript:window.close()" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Close Window
                </a>
            </div>
        </body>

        </html>
    <?php
        exit();
    }

    // Get the PDF URL
    $pdfUrl = $pdfDoc['pdf_url'] ?? null;

    if (empty($pdfUrl)) {
        throw new Exception('PDF URL not found in database');
    }

    error_log("DownloadYearbookPDF: Found PDF URL: $pdfUrl");

    // If it's a CDN URL, redirect to it
    if (filter_var($pdfUrl, FILTER_VALIDATE_URL)) {
        // Set headers to force download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Yearbook_' . $department . '_' . str_replace(' ', '_', $batchYear) . '.pdf"');
        header('Location: ' . $pdfUrl);
        exit();
    }
    // If it's a local path
    else {
        $localPath = __DIR__ . '/../../' . $pdfUrl;

        if (!file_exists($localPath)) {
            throw new Exception('PDF file not found on server');
        }

        // Set headers for download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Yearbook_' . $department . '_' . str_replace(' ', '_', $batchYear) . '.pdf"');
        header('Content-Length: ' . filesize($localPath));

        // Output file
        readfile($localPath);
        exit();
    }
} catch (Exception $e) {
    error_log("DownloadYearbookPDF Error: " . $e->getMessage());
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error Downloading PDF</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #1e2a38 0%, #2c3e50 100%);
                color: #e2e8f0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }

            .error-container {
                background: rgba(255, 255, 255, 0.05);
                border: 2px solid rgba(239, 68, 68, 0.5);
                border-radius: 20px;
                padding: 40px;
                text-align: center;
                max-width: 500px;
            }

            .error-icon {
                font-size: 64px;
                color: #ef4444;
                margin-bottom: 20px;
            }

            h1 {
                color: #ef4444;
                margin-bottom: 16px;
            }

            p {
                color: #cbd5e1;
                line-height: 1.6;
                margin-bottom: 24px;
            }

            .back-btn {
                display: inline-block;
                padding: 12px 32px;
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                color: white;
                text-decoration: none;
                border-radius: 10px;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .back-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
            }
        </style>
    </head>

    <body>
        <div class="error-container">
            <i class="fas fa-exclamation-triangle error-icon"></i>
            <h1>Download Error</h1>
            <p>An error occurred while trying to download the yearbook PDF.</p>
            <p>Please try again later or contact support if the problem persists.</p>
            <a href="javascript:window.close()" class="back-btn">
                <i class="fas fa-arrow-left"></i> Close Window
            </a>
        </div>
    </body>

    </html>
<?php
    exit();
}
?>