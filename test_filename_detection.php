<?php
// Test script to verify filename-based slot and side detection

echo "<h2>Filename-Based Slot and Side Detection Test</h2>";

// Test cases
$testFiles = [
    "BSMEFRONT_Cover_Photo.jpg" => ["slot" => 1, "side" => "front"],
    "BSMEBACK_Cover_Photo.jpg" => ["slot" => 1, "side" => "back"],
    "BSCJEFRONT_Cover_Photo.png" => ["slot" => 2, "side" => "front"],
    "BSCJEBACK_Cover_Photo.png" => ["slot" => 2, "side" => "back"],
    "BSTMFRONT_Cover_Photo.gif" => ["slot" => 3, "side" => "front"],
    "BSTMBACK_Cover_Photo.gif" => ["slot" => 3, "side" => "back"],
    "BSEFRONT_Cover_Photo.jpg" => ["slot" => 4, "side" => "front"],
    "BSEBACK_Cover_Photo.jpg" => ["slot" => 4, "side" => "back"],
    "BSNFRONT_Cover_Photo.png" => ["slot" => 5, "side" => "front"],
    "BSNBACK_Cover_Photo.png" => ["slot" => 5, "side" => "back"],
    "BSISFRONT_Cover_Photo.jpg" => ["slot" => 6, "side" => "front"],
    "BSISBACK_Cover_Photo.jpg" => ["slot" => 6, "side" => "back"],
    "BSBAFRONT_Cover_Photo.png" => ["slot" => 7, "side" => "front"],
    "BSBABACK_Cover_Photo.png" => ["slot" => 7, "side" => "back"],
    "BSME_Maritime_Cover.jpg" => ["slot" => 1, "side" => "front"], // Default to front if not specified
    "RandomFileName.jpg" => ["slot" => null, "side" => "front"] // Should default
];

echo "<h3>Slot and Side Detection Results:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Filename</th><th>Expected Slot</th><th>Expected Side</th><th>Detected Slot</th><th>Detected Side</th><th>Status</th></tr>";

$slotMapping = [
    'BSME' => 1,
    'BSCJ' => 2,
    'BSTM' => 3,
    'BSE' => 4,
    'BSN' => 5,
    'BSIS' => 6,
    'BSBA' => 7
];

foreach ($testFiles as $filename => $expected) {
    // Detect slot
    $detectedSlot = null;
    foreach ($slotMapping as $prefix => $slotNum) {
        if (strpos(strtoupper($filename), $prefix) === 0) {
            $detectedSlot = $slotNum;
            break;
        }
    }
    
    // Detect side
    $detectedSide = 'front'; // Default
    if (strpos(strtoupper($filename), 'BACK') !== false) {
        $detectedSide = 'back';
    } else if (strpos(strtoupper($filename), 'FRONT') !== false) {
        $detectedSide = 'front';
    }
    
    // Check if detection matches expected
    $slotMatch = ($expected['slot'] === $detectedSlot) ? "✓" : "✗";
    $sideMatch = ($expected['side'] === $detectedSide) ? "✓" : "✗";
    $overallStatus = ($slotMatch === "✓" && $sideMatch === "✓") ? "PASS" : "FAIL";
    
    $statusColor = ($overallStatus === "PASS") ? "green" : "red";
    
    echo "<tr>";
    echo "<td>$filename</td>";
    echo "<td>" . ($expected['slot'] ?? 'N/A') . "</td>";
    echo "<td>{$expected['side']}</td>";
    echo "<td>" . ($detectedSlot ?? 'N/A') . "</td>";
    echo "<td>$detectedSide</td>";
    echo "<td style='color: $statusColor; font-weight: bold;'>$overallStatus</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Slot Mapping Rules:</h3>";
echo "<ul>";
foreach ($slotMapping as $prefix => $slotNum) {
    $department = "";
    switch($prefix) {
        case 'BSME': $department = "Maritime Education"; break;
        case 'BSCJ': $department = "College of Criminology"; break;
        case 'BSTM': $department = "Tourism Management"; break;
        case 'BSE': $department = "College of Education"; break;
        case 'BSN': $department = "College of Nursing"; break;
        case 'BSIS': $department = "Information System"; break;
        case 'BSBA': $department = "Business Administration"; break;
    }
    echo "<li><strong>$prefix</strong> (Slot $slotNum) - $department</li>";
}
echo "</ul>";

echo "<h3>Side Detection Rules:</h3>";
echo "<ul>";
echo "<li>Filenames containing <strong>FRONT</strong> will be uploaded to <strong>front_url</strong></li>";
echo "<li>Filenames containing <strong>BACK</strong> will be uploaded to <strong>back_url</strong></li>";
echo "<li>If neither FRONT nor BACK is found, defaults to <strong>front_url</strong></li>";
echo "</ul>";

echo "<h3>Implementation Summary:</h3>";
echo "<p>The filename-based detection has been implemented in both the frontend (BatchTemplates.js) and backend (UploadCover.php). When uploading files:</p>";
echo "<ol>";
echo "<li>The system checks the filename prefix to determine the correct slot (1-7)</li>";
echo "<li>The system checks for 'FRONT' or 'BACK' in the filename to determine the side</li>";
echo "<li>The file is automatically uploaded to the correct slot and URL field</li>";
echo "<li>Users no longer need to manually select slots - just name files correctly</li>";
echo "</ol>";

?>