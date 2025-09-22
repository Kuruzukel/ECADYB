<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$uploadStatus = [
    'top_management_message' => null,
    'student_info' => null
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

function importCSVToMongoByDepartment($tmpName, $departmentsDB)
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

                preg_match('/^[^\s-]+/', $record['department section'], $matches);
                if (empty($matches)) continue;
                $dept = strtolower($matches[0]);

                $dataByDepartment[$dept][] = $record;
            }
        }
        fclose($handle);
    }

    if (!empty($dataByDepartment)) {
        foreach ($dataByDepartment as $dept => $records) {
            $collection = $departmentsDB->$dept;
            $collection->drop();
            $collection->insertMany($records);
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
            $topManagementDB = $client->Top_Management;
            $uploadStatus['top_management_message'] = importCSVByMessage($tmpName, $topManagementDB->message);
        } else {
            $uploadStatus['top_management_message'] = false;
        }
    }


    if (!empty($_FILES['student_info']['tmp_name'])) {
        $departmentsDB = $client->Departments;
        $uploadStatus['student_info'] = importCSVToMongoByDepartment($_FILES['student_info']['tmp_name'], $departmentsDB);
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

                    <!-- Student Info Upload -->
                    <div class="section">
                        <div class="section-header">Student Information</div>
                        <div
                            class="file-card <?= $uploadStatus['student_info'] === false ? 'upload-failed' : ($uploadStatus['student_info'] === true ? 'upload-success' : '') ?>">
                            <label class="custom-upload" for="student-info">Upload CSV File</label>
                            <input type="file" name="student_info" id="student-info" class="upload-input" accept=".csv">
                        </div>
                    </div>

                    <!-- Image Upload (future use) -->
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
</body>

</html>