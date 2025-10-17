<?php
// Prevent direct access to this file
if (!defined('ADMIN_DASHBOARD_INCLUDED')) {
    // If accessed directly, redirect to the proper route
    header('Location: /ECADYB/Admin');
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$flashMessage = null;
if (isset($_SESSION['upload_status'])) {
    if ($_SESSION['upload_status'] === 'success') {
        $flashMessage = ['type' => 'success', 'message' => 'Upload successful!'];
    } else if ($_SESSION['upload_status'] === 'error') {
        $flashMessage = ['type' => 'error', 'message' => "One or more uploads failed. Please ensure you're using valid CSV files."];
    }
    unset($_SESSION['upload_status']);
}

if (isset($_SESSION['return_url'])) {
    unset($_SESSION['return_url']);
}

$uploadStatus = [
    'top_management_message' => null,
    'student_info' => null
];

$programMap = [
    "bsme" => "BS Marine Engineering",
    "bsmt" => "BS Marine Transportation",
    "bscje" => "BS Criminal Justice Education",
    "bstm" => "BS Tourism Management",
    "btvted" => "BS Technical-Vocational Teacher Education",
    "beced" => "BS Early Childhood Education",
    "bsn" => "BS Nursing",
    "bsis" => "BS Information System",
    "bsma" => "BS Management Accounting",
    "bse" => "BS Entrepreneurship"
];

function isValidCSV($fileTmpName)
{
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmpName);
    finfo_close($finfo);
    return in_array($mimeType, [
        'text/plain',
        'text/csv',
        'application/csv',
        'application/vnd.ms-excel',
        'application/octet-stream'
    ]);
}

function cleanHeader($col)
{
    $col = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $col);
    $col = str_replace(["\xEF\xBB\xBF"], '', $col);
    return strtolower(preg_replace('/[\s_]+/', '', trim($col)));
}

$upper = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
$lower = 'abcdefghijkmnopqrstuvwxyz';
$digits = '123456789';
$special = '!@#_$';

function generateRandomPassword($length = 8)
{
    global $upper, $lower, $digits, $special;

    $characters = $upper . $lower . $digits . $special;
    $password = '';
    $charactersLength = strlen($characters);

    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, $charactersLength - 1)];
    }

    return $password;
}

function importCSVToTemplateDepartments($tmpName, $templateDB, $programMap)
{
    if (!isValidCSV($tmpName)) return false;

    $header = null;
    $dataByDepartment = [];

    if (($handle = fopen($tmpName, 'r')) !== false) {
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_map('trim', $row);
            if (!$header) {
                $header = array_map(function ($col) {
                    return match (cleanHeader($col)) {
                        'id' => 'id',
                        'academicyear' => 'academic year',
                        'departmentsection', 'departamentsection' => 'department section',
                        'studentid' => 'student id',
                        'lastname' => 'last name',
                        'firstname' => 'first name',
                        'middlename' => 'middle name',
                        'motto' => 'motto',
                        'honors' => 'honors',
                        'milestone' => 'milestone',
                        'email' => 'email',
                        default => cleanHeader($col)
                    };
                }, $row);
            } elseif (count($row) === count($header)) {
                $record = array_combine($header, $row);

                $record['password'] = generateRandomPassword(8);

                if (!isset($record['status']) || empty($record['status'])) {
                    $record['status'] = 'Pending';
                }

                $studentName = ($record['first name'] ?? '') . ' ' . ($record['last name'] ?? '');
                error_log("Generated password for student: " . trim($studentName) . " - Password: " . $record['password']);

                if (!isset($record['department section'])) continue;

                $deptSection = $record['department section'];

                $deptSection = trim($deptSection);

                $matchedDept = null;

                $normalizedDeptSection = strtolower(preg_replace('/\s+/', '', $deptSection));

                foreach ($programMap as $code => $fullName) {
                    if (strpos($normalizedDeptSection, $code) === 0) {
                        $matchedDept = $code;
                        break;
                    }
                }

                if (!$matchedDept) {
                    foreach ($programMap as $code => $fullName) {
                        if (strpos($normalizedDeptSection, $code) !== false) {
                            $matchedDept = $code;
                            break;
                        }
                    }
                }

                if (!$matchedDept) {
                    foreach ($programMap as $code => $fullName) {
                        $normalizedFullName = strtolower(preg_replace('/\s+/', '', $fullName));
                        if (strpos($normalizedDeptSection, str_replace([' ', '-'], '', $normalizedFullName)) !== false) {
                            $matchedDept = $code;
                            break;
                        }
                    }
                }

                if (!$matchedDept) {
                    $parts = preg_split('/[\s-]+/', $deptSection, 2);
                    if (!empty($parts)) {
                        $extractedCode = strtolower($parts[0]);
                        if (array_key_exists($extractedCode, $programMap)) {
                            $matchedDept = $extractedCode;
                        }
                    }
                }

                if ($matchedDept) {
                    $dataByDepartment[$matchedDept][] = $record;
                } else {
                    $dataByDepartment['beced'][] = $record;
                }
            }
        }
        fclose($handle);
    }

    if (!empty($dataByDepartment)) {
        foreach ($dataByDepartment as $deptCode => $records) {
            try {
                $collection = $templateDB->$deptCode;

                $collection->drop();
                $collection->insertMany($records);
            } catch (Exception $e) {
                error_log("Error importing data to department $deptCode: " . $e->getMessage());
                return false;
            }
        }
        return true;
    }
    return false;
}

function importCSVByMessage($tmpName, $collection)
{
    if (!isValidCSV($tmpName)) return false;

    $header = null;
    $dataByMessage = [];

    if (($handle = fopen($tmpName, 'r')) !== false) {
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_map('trim', $row);
            $row = array_map(function ($field) {
                $field = str_replace(["\x92", "\x93", "\x94", "\x96", "\x97"], ["'", '"', '"', '-', '-'], $field);
                $field = mb_convert_encoding($field, 'UTF-8', 'UTF-8');
                return $field;
            }, $row);

            if (!$header) {
                $header = array_map('cleanHeader', $row);
            } elseif (count($row) === count($header)) {
                $record = array_combine($header, $row);

                if (isset($record['message'])) {
                    $wordCount = str_word_count($record['message']);
                    if ($wordCount > 117) {
                        error_log("Message word limit exceeded for " . ($record['name'] ?? 'Unknown') . ": $wordCount words (max 117)");
                        throw new Exception("Message for " . ($record['name'] ?? 'Unknown') . " exceeds 117 words limit. Current: $wordCount words.");
                    }
                }

                $dataByMessage[] = $record;
            }
        }
        fclose($handle);
    }

    if (!empty($dataByMessage)) {
        try {
            $collection->drop();
            error_log("Dropped old top_management_message collection");

            $result = $collection->insertMany($dataByMessage);
            $insertedCount = count($dataByMessage);
            error_log("Inserted $insertedCount new top management records");

            return true;
        } catch (Exception $e) {
            error_log("Error replacing top management data: " . $e->getMessage());
            return false;
        }
    }
    return false;
}

function getSelectedTemplateDatabase($client)
{
    $selectedTemplate = !empty($_POST['selected_template']) ? $_POST['selected_template'] : '1';

    $selectedTemplate = trim($selectedTemplate);

    if (is_numeric($selectedTemplate)) {
        $templateNumber = intval($selectedTemplate);
        if ($templateNumber >= 1 && $templateNumber <= 3) {
            $dbName = 'BatchTemplate' . $templateNumber;
        } else {
            $dbName = 'BatchTemplate1';
        }
    } else {
        $dbName = str_replace(' ', '', $selectedTemplate);

        if (strpos($dbName, 'BatchTemplate') !== 0) {
            $dbName = 'BatchTemplate1';
        }
    }

    return $client->$dbName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mongoUrl = getenv('MONGODB_URI') ?: getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    $client = new Client($mongoUrl);

    $selectedTemplateValue = isset($_POST['selected_template']) ? $_POST['selected_template'] : 'NOT SET';
    error_log("BatchUpload.php processing upload - selected_template: $selectedTemplateValue");

    if (!empty($_FILES['top_management_message']['tmp_name'])) {
        $tmpName = $_FILES['top_management_message']['tmp_name'];

        $validTopManagementHeaders = ['name', 'position', 'message', 'academicyear'];
        $validTopManagementHeaders = array_map('cleanHeader', $validTopManagementHeaders);
        $actualHeaders = [];

        if (($handle = fopen($tmpName, 'r')) !== false) {
            if (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $actualHeaders = array_map('cleanHeader', $row);
                error_log("Raw headers from CSV: " . json_encode($row));
                error_log("Cleaned headers: " . json_encode($actualHeaders));
            }
            fclose($handle);
        }

        sort($validTopManagementHeaders);
        sort($actualHeaders);

        error_log("Expected headers (sorted): " . json_encode($validTopManagementHeaders));
        error_log("Actual headers (sorted): " . json_encode($actualHeaders));

        if ($actualHeaders === $validTopManagementHeaders) {
            try {
                $templateDB = getSelectedTemplateDatabase($client);
                $dbName = $templateDB->getDatabaseName();
                error_log("BatchUpload.php: Importing top management message to database: $dbName");

                $csvNames = [];
                if (($handle = fopen($tmpName, 'r')) !== false) {
                    $header = null;
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $row = array_map('trim', $row);
                        if (!$header) {
                            $header = array_map('cleanHeader', $row);
                        } elseif (count($row) === count($header)) {
                            $record = array_combine($header, $row);
                            if (isset($record['name'])) {
                                $csvNames[] = $record['name'];
                            }
                        }
                    }
                    fclose($handle);
                }

                if (!empty($csvNames)) {
                    $photosCollection = $templateDB->top_management_photos;
                    $deleteResult = $photosCollection->deleteMany([
                        'name' => ['$nin' => $csvNames]
                    ]);
                    error_log("Cleaned up " . $deleteResult->getDeletedCount() . " orphaned top management photos");
                }

                $uploadStatus['top_management_message'] = importCSVByMessage($tmpName, $templateDB->top_management_message);
            } catch (Exception $e) {
                error_log("Error importing top management message: " . $e->getMessage());
                $uploadStatus['top_management_message'] = false;
            }
        } else {
            error_log("Header mismatch. Expected: " . implode(',', $validTopManagementHeaders) . " Got: " . implode(',', $actualHeaders));
            $uploadStatus['top_management_message'] = false;
        }
    }

    if (!empty($_FILES['student_info']['tmp_name'])) {
        try {
            $templateDB = getSelectedTemplateDatabase($client);
            $dbName = $templateDB->getDatabaseName();
            error_log("BatchUpload.php: Importing student info to database: $dbName");

            $uploadStatus['student_info'] = importCSVToTemplateDepartments(
                $_FILES['student_info']['tmp_name'],
                $templateDB,
                $programMap
            );
        } catch (Exception $e) {
            error_log("Error processing student info: " . $e->getMessage());
            $uploadStatus['student_info'] = false;
        }
    }

    if ($uploadStatus['top_management_message'] !== null || $uploadStatus['student_info'] !== null) {
        $message = '';
        $type = '';

        if ($uploadStatus['top_management_message'] || $uploadStatus['student_info']) {
            $messages = [];
            if ($uploadStatus['top_management_message']) {
                $messages[] = 'Top Management CSV uploaded successfully! Previous messages and orphaned photos have been replaced.';
            }
            if ($uploadStatus['student_info']) {
                $messages[] = 'Student Information CSV uploaded successfully! Random passwords have been generated for all students and previous data has been replaced.';
            }
            $message = implode(' ', $messages);
            $type = 'success';
        } else {
            $message = "Upload failed. CSV file must have these columns: name, position, message (max 117 words), academicyear";
            $type = 'error';
        }

        echo '<script data-notification>
            if (typeof showNotification === "function") {
                showNotification("' . $message . '", "' . $type . '");
            }
        </script>';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Batch Upload</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/ECADYB/Admin/assets/css/BatchUpload.css">
</head>

<body>
    <div class="container">
        <div class="header-container">
            <h1><i class="fas fa-cloud-upload-alt"></i> <span class="chevron"><i
                        class="fas fa-chevron-right"></i></span>Batch Upload</h1>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="selected_template" id="selected_template" value="">
            <div class="form-content">
                <div class="form-group">
                    <div class="section">
                        <div class="section-header">Top Management Message</div>
                        <div class="file-card" id="card-top-management">
                            <label class="custom-upload" for="top_management_message">Upload Top Management Message CSV
                                File</label>
                            <input type="file" name="top_management_message" id="top_management_message"
                                class="upload-input" accept=".csv">
                            <div class="file-info" id="info-top-management">
                                <p><i class="fas fa-file-csv"></i> <span class="file-name"></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header">Student Information</div>
                        <div class="file-card" id="card-student-info">
                            <label class="custom-upload" for="student-info">Upload Student Information CSV File</label>
                            <input type="file" name="student_info" id="student-info" class="upload-input" accept=".csv">
                            <div class="file-info" id="info-student-info">
                                <p><i class="fas fa-file-csv"></i> <span class="file-name"></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header">Student Photos</div>
                        <div class="file-card" id="card-student-photos">
                            <label class="custom-upload" for="student-photos">Upload Student Photos</label>
                            <input type="file" name="student_photos[]" id="student-photos" class="upload-input"
                                accept="image/*" multiple>
                            <div class="file-info" id="info-student-photos">
                                <p><i class="fas fa-images"></i> <span class="file-name"></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header">Top Management Photos</div>
                        <div class="file-card" id="card-management-photos">
                            <label class="custom-upload" for="management-photos">Upload Top Management Photos</label>
                            <input type="file" name="management_photos[]" id="management-photos" class="upload-input"
                                accept="image/*" multiple>
                            <div class="file-info" id="info-management-photos">
                                <p><i class="fas fa-images"></i> <span class="file-name"></span></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div id="notification-container"></div>
    </div>
    </form>
    </div>

    <div class="upload-overlay" id="upload-overlay">
        <div class="upload-modal" id="uploadModal">
            <h2>Uploading...</h2>
            <p id="uploadText">Please wait while we upload your files</p>

            <div class="loader">
                <div class="loading-bar-background">
                    <div class="loading-bar">
                        <div class="white-bars-container">
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-buttons">
                <button class="modal-btn cancel" id="cancel-upload-btn">Cancel</button>
            </div>
        </div>
    </div>

    <?php if ($flashMessage): ?>
        <div id="flash-data" data-message="<?= htmlspecialchars($flashMessage['message'], ENT_QUOTES) ?>"
            data-type="<?= htmlspecialchars($flashMessage['type'], ENT_QUOTES) ?>" style="display:none"></div>
    <?php endif; ?>
    <script src="/ECADYB/Admin/assets/js/BatchUpload.js?v=<?php echo time(); ?>"></script>
</body>

</html>