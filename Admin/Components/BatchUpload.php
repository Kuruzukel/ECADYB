<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

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
                        'batchname' => 'batch name',
                        default => cleanHeader($col)
                    };
                }, $row);
            } elseif (count($row) === count($header)) {
                $record = array_combine($header, $row);

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

                // If no direct match, try to find any occurrence of the code
                if (!$matchedDept) {
                    foreach ($programMap as $code => $fullName) {
                        if (strpos($normalizedDeptSection, $code) !== false) {
                            $matchedDept = $code;
                            break;
                        }
                    }
                }

                // If still no match, try matching with full program names
                if (!$matchedDept) {
                    foreach ($programMap as $code => $fullName) {
                        $normalizedFullName = strtolower(preg_replace('/\s+/', '', $fullName));
                        // Check if the full name is in the department section
                        if (strpos($normalizedDeptSection, str_replace([' ', '-'], '', $normalizedFullName)) !== false) {
                            $matchedDept = $code;
                            break;
                        }
                    }
                }

                // If still no match, try to extract the first part before a dash or space
                if (!$matchedDept) {
                    // Extract the part before the first dash or space
                    $parts = preg_split('/[\s-]+/', $deptSection, 2); // Limit to 2 parts
                    if (!empty($parts)) {
                        $extractedCode = strtolower($parts[0]);
                        // Check if the extracted code exactly matches a program code
                        if (array_key_exists($extractedCode, $programMap)) {
                            $matchedDept = $extractedCode;
                        }
                    }
                }

                // If we found a matching department, add record to that department
                if ($matchedDept) {
                    $dataByDepartment[$matchedDept][] = $record;
                } else {
                    // As a fallback, default to beced only if no other match found
                    $dataByDepartment['beced'][] = $record;
                }
            }
        }
        fclose($handle);
    }

    // Import data to respective department collections
    if (!empty($dataByDepartment)) {
        foreach ($dataByDepartment as $deptCode => $records) {
            try {
                // Get the collection for this department
                $collection = $templateDB->$deptCode;

                // Clear existing data and insert new records
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
            if (!$header) {
                $header = array_map('cleanHeader', $row);
            } elseif (count($row) === count($header)) {
                $dataByMessage[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }

    if (!empty($dataByMessage)) {
        $collection->drop();
        $collection->insertMany($dataByMessage);
        return true;
    }
    return false;
}

// Function to get selected template from localStorage via hidden form field
function getSelectedTemplateDatabase($client)
{
    // Default to BatchTemplate1 if no template selected
    $selectedTemplate = !empty($_POST['selected_template']) ? $_POST['selected_template'] : 'Batch Template 1';

    // Validate and sanitize the template name
    $selectedTemplate = trim($selectedTemplate);

    // Create database name by removing spaces and ensuring valid format
    $dbName = str_replace(' ', '', $selectedTemplate);

    // Ensure the database name starts with "BatchTemplate"
    if (strpos($dbName, 'BatchTemplate') !== 0) {
        $dbName = 'BatchTemplate1'; // Default to BatchTemplate1 if invalid
    }

    // Return the database object
    return $client->$dbName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    $client = new Client($mongoUrl);

    if (!empty($_FILES['top_management_message']['tmp_name'])) {
        $tmpName = $_FILES['top_management_message']['tmp_name'];

        $validTopManagementHeaders = ['name', 'message', 'batch_name', 'academic_year'];
        $validTopManagementHeaders = array_map('cleanHeader', $validTopManagementHeaders);
        $actualHeaders = [];

        if (($handle = fopen($tmpName, 'r')) !== false) {
            if (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $actualHeaders = array_map('cleanHeader', $row);
            }
            fclose($handle);
        }

        sort($validTopManagementHeaders);
        sort($actualHeaders);

        if ($actualHeaders === $validTopManagementHeaders) {
            try {
                $topManagementDB = $client->Top_Management;
                $uploadStatus['top_management_message'] = importCSVByMessage($tmpName, $topManagementDB->message);
            } catch (Exception $e) {
                error_log("Error importing top management message: " . $e->getMessage());
                $uploadStatus['top_management_message'] = false;
            }
        } else {
            $uploadStatus['top_management_message'] = false;
        }
    }

    if (!empty($_FILES['student_info']['tmp_name'])) {
        try {
            // Get the template database instead of the generic Departments database
            $templateDB = getSelectedTemplateDatabase($client);

            // Use the enhanced import function with program mapping
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

    $resultMsg = null;
    if ($uploadStatus['top_management_message'] || $uploadStatus['student_info']) {
        $resultMsg = "Upload successful!";
    } elseif ($uploadStatus['top_management_message'] === false || $uploadStatus['student_info'] === false) {
        $resultMsg = "One or more uploads failed. Please ensure you're using valid CSV files.";
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
    <link rel="stylesheet" href="../Assets/css/BatchUpload.css">
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
                        <div
                            class="file-card <?= $uploadStatus['top_management_message'] === false ? 'upload-failed' : ($uploadStatus['top_management_message'] === true ? 'upload-success' : '') ?>">
                            <label class="custom-upload" for="top_management_message">Upload CSV File</label>
                            <input type="file" name="top_management_message" id="top_management_message"
                                class="upload-input" accept=".csv">
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header">Student Information</div>
                        <div
                            class="file-card <?= $uploadStatus['student_info'] === false ? 'upload-failed' : ($uploadStatus['student_info'] === true ? 'upload-success' : '') ?>">
                            <label class="custom-upload" for="student-info">Upload CSV File</label>
                            <input type="file" name="student_info" id="student-info" class="upload-input" accept=".csv">
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header">Student and Top Management Images</div>
                        <div class="file-card">
                            <label class="custom-upload" for="image-upload">Upload Image Folder</label>
                            <input type="file" id="image-upload" class="upload-input" accept="image/*" multiple>
                        </div>
                    </div>
                </div>

                <?php if (!empty($resultMsg)): ?>
                    <?php
                    $popupClass = in_array(true, $uploadStatus, true) ? 'popup-success' : 'popup-failure';
                    ?>
                    <div class="popup-message <?= $popupClass ?>"><?= $resultMsg ?></div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script src="../Assets/js/BatchUpload.js"></script>
    <script>
        // Get selected template from localStorage (set by BatchTemplates.js)
        document.addEventListener('DOMContentLoaded', function() {
            const selectedTemplate = localStorage.getItem('selectedBatchTemplate');
            if (selectedTemplate) {
                document.getElementById('selected_template').value = selectedTemplate;
            }
        });
    </script>
</body>

</html>