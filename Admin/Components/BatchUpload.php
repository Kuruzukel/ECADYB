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

// Function to get the template folder name
function getTemplateFolderName($selectedTemplate) {
    // Convert "Batch Template 1" to "Batch Template 1"
    // Convert "BatchTemplate1" to "Batch Template 1"
    if (strpos($selectedTemplate, 'Batch Template') === 0) {
        return $selectedTemplate;
    } else if (strpos($selectedTemplate, 'BatchTemplate') === 0) {
        $number = str_replace('BatchTemplate', '', $selectedTemplate);
        return "Batch Template " . $number;
    }
    return "Batch Template 1";
}

// Function to validate and upload individual files (student and top management images)
function processFolderUpload($files, $templateDB, $validDepartments, $validTopManagementFolder, $selectedTemplate) {
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
    
    // Load BunnyCDN configuration
    $configPath = __DIR__ . '/../../Connection/Configuration/BunnyConfig.php';
    if (file_exists($configPath)) {
        require $configPath;
    }
    
    // Try multiple ways to get BunnyCDN configuration
    $bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') 
        ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : null)
        ?: ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? null)
        ?: 'ecadyb';
        
    $bunnyAccessKey = getenv('BUNNY_ACCESS_KEY') 
        ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : null)
        ?: ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null);
        
    $bunnyCdnHost = getenv('BUNNY_CDN_HOST') 
        ?: (defined('BUNNY_CDN_HOST') ? BUNNY_CDN_HOST : null)
        ?: ($GLOBALS['BUNNY_CDN_HOST'] ?? null)
        ?: 'https://ECADYB.b-cdn.net';
    
    // Check if we have the required configuration
    if (!$bunnyAccessKey) {
        $results['errors'][] = 'BunnyCDN configuration missing: Access Key not found.';
        $results['failed'] = count($files['name']);
        return $results;
    }
    
    // Get template folder name
    $templateFolder = getTemplateFolderName($selectedTemplate);
    
    // Process each file
    for ($i = 0; $i < count($files['name']); $i++) {
        $fileName = $files['name'][$i];
        $filePath = $files['tmp_name'][$i];
        
        // Skip if file wasn't uploaded successfully
        if (!is_uploaded_file($filePath)) {
            $results['errors'][] = "File not uploaded properly: $fileName";
            $results['failed']++;
            continue;
        }
        
        // Extract name from filename
        $nameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
        
        // Check if it's a student photo (filename must be a valid student ID)
        if (preg_match('/^\d{4}-\d{6}\.(jpg|jpeg|png|gif)$/i', $fileName) || preg_match('/^\d+\.(jpg|jpeg|png|gif)$/i', $fileName)) {
            // Validate that filename is a valid student ID format
            if (preg_match('/^\d{4}-\d{6}$/', $nameWithoutExt) || is_numeric($nameWithoutExt)) {
                // Process student photo
                $studentId = $nameWithoutExt;
                
                // Determine department based on student ID (simplified approach)
                $department = 'unknown';
                $departmentMap = [
                    '100' => 'bsme',  // BS Marine Engineering
                    '200' => 'bsmt',  // BS Marine Transportation
                    '300' => 'bscje', // BS Criminal Justice Education
                    '400' => 'bstm',  // BS Tourism Management
                    '500' => 'btvted', // BS Technical-Vocational Teacher Education
                    '600' => 'beced', // BS Early Childhood Education
                    '700' => 'bsn',   // BS Nursing
                    '800' => 'bsis',  // BS Information System
                    '900' => 'bsma',  // BS Management Accounting
                    '1000' => 'bse'   // BS Entrepreneurship
                ];
                
                // Extract prefix from student ID to determine department
                $prefix = substr(str_replace('-', '', $studentId), 0, 3);
                if (isset($departmentMap[$prefix])) {
                    $department = $departmentMap[$prefix];
                } else {
                    // Try with 2-digit prefix
                    $prefix = substr(str_replace('-', '', $studentId), 0, 2);
                    if (isset($departmentMap[$prefix])) {
                        $department = $departmentMap[$prefix];
                    }
                }
                
                // Upload to BunnyCDN
                $safeFolder = 'Student Photos';
                $ext = pathinfo($fileName, PATHINFO_EXTENSION) ?: 'jpg';
                $safeFileName = preg_replace('/[^A-Za-z0-9 _.-]/', '', $studentId) ?: ('student_' . time());
                $safeExt = preg_replace('/[^A-Za-z0-9]/', '', $ext) ?: 'jpg';
                $filename = sprintf('%s.%s', $safeFileName, $safeExt);
                $path = $safeFolder . '/' . $templateFolder . '/' . $department . '/' . $filename;
                $storageUrl = "https://storage.bunnycdn.com/{$bunnyStorageZone}/" . str_replace(' ', '%20', $path);
                
                // Read file contents
                $fileContents = file_get_contents($filePath);
                if ($fileContents === false) {
                    $results['errors'][] = "Failed to read file: $fileName";
                    $results['failed']++;
                    continue;
                }
                
                // Upload to BunnyCDN
                $ch = curl_init($storageUrl);
                curl_setopt_array($ch, [
                    CURLOPT_CUSTOMREQUEST => 'PUT',
                    CURLOPT_HTTPHEADER => ['AccessKey: ' . $bunnyAccessKey, 'Content-Type: application/octet-stream'],
                    CURLOPT_POSTFIELDS => $fileContents,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => true
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);
                
                if ($response === false || $httpCode < 200 || $httpCode >= 300) {
                    $results['errors'][] = "Failed to upload $fileName to BunnyCDN: " . ($curlErr ?: 'HTTP ' . $httpCode);
                    $results['failed']++;
                    continue;
                }
                
                $publicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $path);
                
                // Store in MongoDB
                try {
                    // Get or create collection for this department
                    $collection = $templateDB->$department;
                    
                    // Prepare document for MongoDB
                    $document = [
                        'student_id' => $studentId,
                        'filename' => $filename,
                        'original_name' => $fileName,
                        'department' => $department,
                        'template' => str_replace('Batch Template ', '', $selectedTemplate),
                        'url' => $publicUrl,
                        'upload_time' => new \MongoDB\BSON\UTCDateTime()
                    ];
                    
                    // Insert document
                    $collection->insertOne($document);
                    $results['success']++;
                } catch (Exception $e) {
                    $results['errors'][] = "Error storing student file $fileName in database: " . $e->getMessage();
                    $results['failed']++;
                    
                    // Delete file from BunnyCDN since database storage failed
                    $deleteCh = curl_init($storageUrl);
                    curl_setopt_array($deleteCh, [
                        CURLOPT_CUSTOMREQUEST => 'DELETE',
                        CURLOPT_HTTPHEADER => ['AccessKey: ' . $bunnyAccessKey],
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 10,
                        CURLOPT_CONNECTTIMEOUT => 3
                    ]);
                    curl_exec($deleteCh);
                    curl_close($deleteCh);
                }
            } else {
                $results['errors'][] = "Invalid student ID format for file: $fileName. Expected format: 2021-004393.jpg or numeric ID.";
                $results['failed']++;
            }
        } else {
            // Treat as top management photo (any name with image extension)
            if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $fileName)) {
                // Process top management photo
                $personName = $nameWithoutExt;
                
                // Upload to BunnyCDN
                $safeFolder = 'Top Management Photos';
                $ext = pathinfo($fileName, PATHINFO_EXTENSION) ?: 'jpg';
                $safeFileName = preg_replace('/[^A-Za-z0-9 _.-]/', '', $personName) ?: ('top_management_' . time());
                $safeExt = preg_replace('/[^A-Za-z0-9]/', '', $ext) ?: 'jpg';
                $filename = sprintf('%s.%s', $safeFileName, $safeExt);
                $path = $safeFolder . '/' . $templateFolder . '/' . $filename;
                $storageUrl = "https://storage.bunnycdn.com/{$bunnyStorageZone}/" . str_replace(' ', '%20', $path);
                
                // Read file contents
                $fileContents = file_get_contents($filePath);
                if ($fileContents === false) {
                    $results['errors'][] = "Failed to read file: $fileName";
                    $results['failed']++;
                    continue;
                }
                
                // Upload to BunnyCDN
                $ch = curl_init($storageUrl);
                curl_setopt_array($ch, [
                    CURLOPT_CUSTOMREQUEST => 'PUT',
                    CURLOPT_HTTPHEADER => ['AccessKey: ' . $bunnyAccessKey, 'Content-Type: application/octet-stream'],
                    CURLOPT_POSTFIELDS => $fileContents,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => true
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);
                
                if ($response === false || $httpCode < 200 || $httpCode >= 300) {
                    $results['errors'][] = "Failed to upload $fileName to BunnyCDN: " . ($curlErr ?: 'HTTP ' . $httpCode);
                    $results['failed']++;
                    continue;
                }
                
                $publicUrl = rtrim($bunnyCdnHost, '/') . '/' . str_replace(' ', '%20', $path);
                
                // Store in MongoDB
                try {
                    // Connect to Top_Management database
                    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
                    $client = new Client($mongoUrl);
                    $topManagementDB = $client->Top_Management;
                    $collection = $topManagementDB->Photos;
                    
                    // Prepare document for MongoDB
                    $document = [
                        'name' => $personName,
                        'filename' => $filename,
                        'original_name' => $fileName,
                        'template' => str_replace('Batch Template ', '', $selectedTemplate),
                        'url' => $publicUrl,
                        'upload_time' => new \MongoDB\BSON\UTCDateTime()
                    ];
                    
                    // Insert document
                    $collection->insertOne($document);
                    $results['success']++;
                } catch (Exception $e) {
                    $results['errors'][] = "Error storing top management file $fileName in database: " . $e->getMessage();
                    $results['failed']++;
                    
                    // Delete file from BunnyCDN since database storage failed
                    $deleteCh = curl_init($storageUrl);
                    curl_setopt_array($deleteCh, [
                        CURLOPT_CUSTOMREQUEST => 'DELETE',
                        CURLOPT_HTTPHEADER => ['AccessKey: ' . $bunnyAccessKey],
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 10,
                        CURLOPT_CONNECTTIMEOUT => 3
                    ]);
                    curl_exec($deleteCh);
                    curl_close($deleteCh);
                }
            } else {
                $results['errors'][] = "Invalid file format for file: $fileName. Only image files are allowed.";
                $results['failed']++;
            }
        }
    }
    
    return $results;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    $client = new Client($mongoUrl);

    if (isset($_FILES['top_management_message']) && is_array($_FILES['top_management_message']['tmp_name']) && !empty($_FILES['top_management_message']['tmp_name'][0])) {
        // Handle multiple CSV files for top management message
        $files = $_FILES['top_management_message'];
        $allSuccess = true;
        
        // Process each uploaded file
        for ($i = 0; $i < count($files['tmp_name']); $i++) {
            $tmpName = $files['tmp_name'][$i];
            
            // Skip if file wasn't uploaded successfully
            if (!is_uploaded_file($tmpName)) {
                $allSuccess = false;
                continue;
            }
            
            // Validate headers for top management CSV
            $validTopManagementHeaders = ['name', 'message', 'batchname', 'academicyear'];
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
                    $result = importCSVByMessage($tmpName, $topManagementDB->message);
                    if (!$result) {
                        $allSuccess = false;
                    }
                } catch (Exception $e) {
                    error_log("Error importing top management message: " . $e->getMessage());
                    $allSuccess = false;
                }
            } else {
                $allSuccess = false;
            }
        }
        
        $uploadStatus['top_management_message'] = $allSuccess;
    } else if (isset($_FILES['top_management_message']) && !is_array($_FILES['top_management_message']['tmp_name']) && !empty($_FILES['top_management_message']['tmp_name'])) {
        // Handle single CSV file for backward compatibility
        $tmpName = $_FILES['top_management_message']['tmp_name'];
        
        // Validate headers for top management CSV
        $validTopManagementHeaders = ['name', 'message', 'batchname', 'academicyear'];
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

    if (isset($_FILES['student_info']) && is_array($_FILES['student_info']['tmp_name']) && !empty($_FILES['student_info']['tmp_name'][0])) {
        // Handle multiple CSV files for student info
        $files = $_FILES['student_info'];
        $allSuccess = true;
        
        // Process each uploaded file
        for ($i = 0; $i < count($files['tmp_name']); $i++) {
            $tmpName = $files['tmp_name'][$i];
            
            // Skip if file wasn't uploaded successfully
            if (!is_uploaded_file($tmpName)) {
                $allSuccess = false;
                continue;
            }
            
            // Validate headers for student info CSV
            $validStudentHeaders = ['id', 'academic year', 'department section', 'student id', 'last name', 'first name', 'middle initial', 'motto', 'honors', 'email', 'milestone', 'password'];
            $validStudentHeaders = array_map('cleanHeader', $validStudentHeaders);
            $actualHeaders = [];

            if (($handle = fopen($tmpName, 'r')) !== false) {
                if (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    $actualHeaders = array_map('cleanHeader', $row);
                }
                fclose($handle);
            }

            sort($validStudentHeaders);
            sort($actualHeaders);

            // Check if headers match student info format
            if ($actualHeaders === $validStudentHeaders) {
                try {
                    $templateDB = getSelectedTemplateDatabase($client);
                    
                    $result = importCSVToTemplateDepartments(
                        $tmpName,
                        $templateDB,
                        $programMap
                    );
                    
                    if (!$result) {
                        $allSuccess = false;
                    }
                } catch (Exception $e) {
                    error_log("Error processing student info: " . $e->getMessage());
                    $allSuccess = false;
                }
            } else {
                $allSuccess = false;
            }
        }
        
        $uploadStatus['student_info'] = $allSuccess;
    } else if (isset($_FILES['student_info']) && !is_array($_FILES['student_info']['tmp_name']) && !empty($_FILES['student_info']['tmp_name'])) {
        // Handle single CSV file for backward compatibility
        $tmpName = $_FILES['student_info']['tmp_name'];
        
        // Validate headers for student info CSV
        $validStudentHeaders = ['id', 'academic year', 'department section', 'student id', 'last name', 'first name', 'middle initial', 'motto', 'honors', 'email', 'milestone', 'password'];
        $validStudentHeaders = array_map('cleanHeader', $validStudentHeaders);
        $actualHeaders = [];

        if (($handle = fopen($tmpName, 'r')) !== false) {
            if (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $actualHeaders = array_map('cleanHeader', $row);
            }
            fclose($handle);
        }

        sort($validStudentHeaders);
        sort($actualHeaders);

        // Check if headers match student info format
        if ($actualHeaders === $validStudentHeaders) {
            try {
                $templateDB = getSelectedTemplateDatabase($client);

                $uploadStatus['student_info'] = importCSVToTemplateDepartments(
                    $tmpName,
                    $templateDB,
                    $programMap
                );
            } catch (Exception $e) {
                error_log("Error processing student info: " . $e->getMessage());
                $uploadStatus['student_info'] = false;
            }
        } else {
            $uploadStatus['student_info'] = false;
        }
    }
    
    // Handle folder upload
    if (!empty($_FILES['folder_upload'])) {
        try {
            $selectedTemplate = !empty($_POST['selected_template']) ? $_POST['selected_template'] : 'Batch Template 1';
            $templateDB = getSelectedTemplateDatabase($client);
            $folderUploadResult = processFolderUpload($_FILES['folder_upload'], $templateDB, $validDepartments, $validTopManagementFolder, $selectedTemplate);
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
                            <label class="custom-upload" for="top_management_message">Upload CSV Files</label>
                            <input type="file" name="top_management_message[]" id="top_management_message"
                                class="upload-input" accept=".csv" multiple>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header">Student Information</div>
                        <div
                            class="file-card <?= $uploadStatus['student_info'] === false ? 'upload-failed' : ($uploadStatus['student_info'] === true ? 'upload-success' : '') ?>">
                            <label class="custom-upload" for="student-info">Upload CSV Files</label>
                            <input type="file" name="student_info[]" id="student-info" class="upload-input" accept=".csv" multiple>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header">Student Images and Top Management Images</div>
                        <div class="file-card <?= isset($uploadStatus['folder_upload']) && $uploadStatus['folder_upload'] === false ? 'upload-failed' : (isset($uploadStatus['folder_upload']) && !empty($uploadStatus['folder_upload']['success']) ? 'upload-success' : '') ?>">
                            <label class="custom-upload" for="folder-upload">Upload Images</label>
                            <input type="file" name="folder_upload[]" id="folder-upload" class="upload-input" accept="image/*" multiple>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div id="notification-container"></div>

    <script src="../assets/js/BatchUpload.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectedTemplate = localStorage.getItem('selectedBatchTemplate');
        if (selectedTemplate) {
            document.getElementById('selected_template').value = selectedTemplate;
        }
        
        // Show notification if there's a result message
        <?php if (!empty($resultMsg)): ?>
        if (typeof showNotification === 'function') {
            const isSuccess = <?= json_encode(in_array(true, $uploadStatus, true)) ?>;
            showNotification("<?= $resultMsg ?>", isSuccess ? "success" : "error");
        }
        <?php endif; ?>
        
        // Show folder upload results as notifications
        <?php if (isset($uploadStatus['folder_upload']) && is_array($uploadStatus['folder_upload'])): ?>
        if (typeof showNotification === 'function') {
            const folderResult = <?= json_encode($uploadStatus['folder_upload']) ?>;
            if (folderResult.success > 0) {
                showNotification(`Successfully uploaded ${folderResult.success} files`, "success");
            }
            if (folderResult.failed > 0) {
                showNotification(`Failed to upload ${folderResult.failed} files`, "error");
            }
            // Show individual errors if any
            if (folderResult.errors && folderResult.errors.length > 0) {
                folderResult.errors.forEach(error => {
                    showNotification(error, "error");
                });
            }
        }
        <?php endif; ?>
    });
    </script>
</body>

</html>