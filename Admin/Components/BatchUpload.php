<?php
// Set maximum limits at runtime (300MB)
ini_set('upload_max_filesize', '300M');
ini_set('post_max_size', '300M');
ini_set('max_execution_time', '300');
ini_set('memory_limit', '512M');

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$uploadStatus = [
    'top_management_message' => null,
    'student_info' => null,
    'folder_upload' => null
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

// Valid department codes
$validDepartments = ['beced', 'bscje', 'bse', 'bsis', 'bsma', 'bsme', 'bsmt', 'bsn', 'bstm', 'btvted'];
$validTopManagementFolder = 'TOPMANAGEMENT';

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

function getSelectedTemplateDatabase($client)
{
    $selectedTemplate = !empty($_POST['selected_template']) ? $_POST['selected_template'] : 'Batch Template 1';

    $selectedTemplate = trim($selectedTemplate);

    $dbName = str_replace(' ', '', $selectedTemplate);

    if (strpos($dbName, 'BatchTemplate') !== 0) {
        $dbName = 'BatchTemplate1';
    }

    return $client->$dbName;
}

// Function to validate folder structure and upload student images and top management images
function processFolderUpload($files, $templateDB, $validDepartments, $validTopManagementFolder) {
    $results = [
        'success' => 0,
        'failed' => 0,
        'errors' => []
    ];
    
    // Check if files were actually uploaded
    if (empty($files['name'][0])) {
        $results['errors'][] = "No files were uploaded.";
        $results['failed'] = 1;
        return $results;
    }
    
    // Group files by type (student or top management)
    $studentFiles = [];
    $topManagementFiles = [];
    
    for ($i = 0; $i < count($files['name']); $i++) {
        $fileName = $files['name'][$i];
        $filePath = $files['tmp_name'][$i];
        
        // Skip if file wasn't uploaded successfully
        if (!is_uploaded_file($filePath)) {
            $results['errors'][] = "File not uploaded properly: $fileName";
            $results['failed']++;
            continue;
        }
        
        // Extract folder from file path
        // Expected path formats: 
        // For students: [DEPARTMENT]/[student_id].ext
        // For top management: TOPMANAGEMENT/[name].ext
        $pathParts = explode(DIRECTORY_SEPARATOR, dirname($fileName));
        
        // Check if it's a top management file
        if (count($pathParts) >= 1 && strtoupper($pathParts[0]) === $validTopManagementFolder) {
            $topManagementFiles[] = [
                'name' => $fileName,
                'tmp_name' => $filePath,
                'person_name' => pathinfo(basename($fileName), PATHINFO_FILENAME)
            ];
        } 
        // Check if it's a student file
        elseif (count($pathParts) >= 1) {
            $department = strtolower($pathParts[0]);
            
            // Validate department
            if (!in_array($department, $validDepartments)) {
                $results['errors'][] = "Invalid department folder: $department. Valid departments: " . implode(', ', $validDepartments);
                $results['failed']++;
                continue;
            }
            
            // Validate that filename is a numeric student ID
            $studentId = pathinfo(basename($fileName), PATHINFO_FILENAME);
            if (!is_numeric($studentId)) {
                $results['errors'][] = "Invalid filename for file: $fileName. Filename must be a numeric student ID.";
                $results['failed']++;
                continue;
            }
            
            $studentFiles[] = [
                'name' => $fileName,
                'tmp_name' => $filePath,
                'student_id' => $studentId,
                'department' => $department
            ];
        } else {
            $results['errors'][] = "Invalid folder structure for file: $fileName. Expected: [DEPARTMENT]/[student_id].ext or TOPMANAGEMENT/[name].ext";
            $results['failed']++;
        }
    }
    
    // Process student files
    $studentFilesByDepartment = [];
    foreach ($studentFiles as $file) {
        $department = $file['department'];
        if (!isset($studentFilesByDepartment[$department])) {
            $studentFilesByDepartment[$department] = [];
        }
        $studentFilesByDepartment[$department][] = $file;
    }
    
    // Process student files for each department
    foreach ($studentFilesByDepartment as $department => $deptFiles) {
        try {
            // Get or create collection for this department
            $collection = $templateDB->$department;
            
            // Process each file in the department
            foreach ($deptFiles as $file) {
                // Prepare document for MongoDB
                $document = [
                    'student_id' => $file['student_id'],
                    'filename' => basename($file['name']),
                    'upload_time' => new \MongoDB\BSON\UTCDateTime(),
                    'file_path' => $file['name']  // Store relative path
                ];
                
                // Insert document
                $collection->insertOne($document);
                $results['success']++;
            }
        } catch (Exception $e) {
            $results['errors'][] = "Error processing department $department: " . $e->getMessage();
            $results['failed'] += count($deptFiles);
        }
    }
    
    // Process top management files
    if (!empty($topManagementFiles)) {
        try {
            // Connect to Top_Management database
            $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
            $client = new Client($mongoUrl);
            $topManagementDB = $client->Top_Management;
            $collection = $topManagementDB->Photos;
            
            // Process each top management file
            foreach ($topManagementFiles as $file) {
                // Prepare document for MongoDB
                $document = [
                    'name' => $file['person_name'],
                    'filename' => basename($file['name']),
                    'upload_time' => new \MongoDB\BSON\UTCDateTime(),
                    'file_path' => $file['name']  // Store relative path
                ];
                
                // Insert document
                $collection->insertOne($document);
                $results['success']++;
            }
        } catch (Exception $e) {
            $results['errors'][] = "Error processing top management files: " . $e->getMessage();
            $results['failed'] += count($topManagementFiles);
        }
    }
    
    return $results;
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
            $templateDB = getSelectedTemplateDatabase($client);

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
    
    // Handle folder upload
    if (!empty($_FILES['folder_upload'])) {
        try {
            $templateDB = getSelectedTemplateDatabase($client);
            $folderUploadResult = processFolderUpload($_FILES['folder_upload'], $templateDB, $validDepartments, $validTopManagementFolder);
            $uploadStatus['folder_upload'] = $folderUploadResult;
        } catch (Exception $e) {
            error_log("Error processing folder upload: " . $e->getMessage());
            $uploadStatus['folder_upload'] = false;
        }
    }

    $resultMsg = null;
    if ($uploadStatus['top_management_message'] || $uploadStatus['student_info'] || (!empty($uploadStatus['folder_upload']) && $uploadStatus['folder_upload']['success'] > 0)) {
        $resultMsg = "Upload successful!";
    } elseif ($uploadStatus['top_management_message'] === false || $uploadStatus['student_info'] === false || $uploadStatus['folder_upload'] === false) {
        $resultMsg = "One or more uploads failed. Please ensure you're using valid files.";
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
    <link rel="stylesheet" href="../assets/css/BatchUpload.css">
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
                        <div class="section-header">Student Images and Top Management Images</div>
                        <div class="file-card <?= isset($uploadStatus['folder_upload']) && $uploadStatus['folder_upload'] === false ? 'upload-failed' : (isset($uploadStatus['folder_upload']) && !empty($uploadStatus['folder_upload']['success']) ? 'upload-success' : '') ?>">
                            <label class="custom-upload" for="folder-upload">Upload Folder</label>
                            <input type="file" name="folder_upload[]" id="folder-upload" class="upload-input" accept="image/*" multiple webkitdirectory directory>
                        </div>
                        <?php if (isset($uploadStatus['folder_upload']) && is_array($uploadStatus['folder_upload'])): ?>
                            <div class="upload-results">
                                <p>Successfully uploaded: <?= $uploadStatus['folder_upload']['success'] ?> files</p>
                                <?php if ($uploadStatus['folder_upload']['failed'] > 0): ?>
                                    <p>Failed: <?= $uploadStatus['folder_upload']['failed'] ?> files</p>
                                    <?php foreach ($uploadStatus['folder_upload']['errors'] as $error): ?>
                                        <p class="error-message"><?= $error ?></p>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
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

    <script src="../assets/js/BatchUpload.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectedTemplate = localStorage.getItem('selectedBatchTemplate');
        if (selectedTemplate) {
            document.getElementById('selected_template').value = selectedTemplate;
        }
    });
    </script>
</body>

</html>