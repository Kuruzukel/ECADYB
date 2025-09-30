<?php
// Test script to verify filename validation and slot matching

echo "<h2>Filename Validation and Slot Matching Test</h2>";

// Test cases for validation
$testFiles = [
    // Valid cases
    ["filename" => "BSMEFRONT_Cover_Photo.jpg", "slot" => 1, "valid" => true],
    ["filename" => "BSMEBACK_Cover_Photo.jpg", "slot" => 1, "valid" => true],
    ["filename" => "BSCJEFRONT_Cover_Photo.png", "slot" => 2, "valid" => true],
    ["filename" => "BSCJEBACK_Cover_Photo.png", "slot" => 2, "valid" => true],
    ["filename" => "BSTMFRONT_Cover_Photo.gif", "slot" => 3, "valid" => true],
    ["filename" => "BSTMBACK_Cover_Photo.gif", "slot" => 3, "valid" => true],
    ["filename" => "BSEFRONT_Cover_Photo.jpg", "slot" => 4, "valid" => true],
    ["filename" => "BSEBACK_Cover_Photo.jpg", "slot" => 4, "valid" => true],
    ["filename" => "BSNFRONT_Cover_Photo.png", "slot" => 5, "valid" => true],
    ["filename" => "BSNBACK_Cover_Photo.png", "slot" => 5, "valid" => true],
    ["filename" => "BSISFRONT_Cover_Photo.jpg", "slot" => 6, "valid" => true],
    ["filename" => "BSISBACK_Cover_Photo.jpg", "slot" => 6, "valid" => true],
    ["filename" => "BSBAFRONT_Cover_Photo.png", "slot" => 7, "valid" => true],
    ["filename" => "BSBABACK_Cover_Photo.png", "slot" => 7, "valid" => true],
    
    // Invalid cases - mismatched slot
    ["filename" => "BSMEFRONT_Cover_Photo.jpg", "slot" => 2, "valid" => false],
    ["filename" => "BSCJEFRONT_Cover_Photo.png", "slot" => 1, "valid" => false],
    ["filename" => "BSTMFRONT_Cover_Photo.gif", "slot" => 4, "valid" => false],
    ["filename" => "BSEFRONT_Cover_Photo.jpg", "slot" => 6, "valid" => false],
    
    // Invalid cases - invalid prefix
    ["filename" => "INVALIDFRONT_Cover_Photo.jpg", "slot" => 1, "valid" => false],
    ["filename" => "XYZBACK_Cover_Photo.png", "slot" => 2, "valid" => false],
    ["filename" => "NOPREFIX_Cover_Photo.gif", "slot" => 3, "valid" => false],
    
    // Special case - slot 8 (background) should not be validated
    ["filename" => "BackgroundImage.jpg", "slot" => 8, "valid" => true],
    ["filename" => "AnyName.jpg", "slot" => 8, "valid" => true],
];

echo "<h3>Validation Results:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Filename</th><th>Provided Slot</th><th>Expected Slot</th><th>Should Pass</th><th>Validation Result</th><th>Status</th></tr>";

$slotMapping = [
    'BSME' => 1,
    'BSCJ' => 2,
    'BSTM' => 3,
    'BSE' => 4,
    'BSN' => 5,
    'BSIS' => 6,
    'BSBA' => 7
];

foreach ($testFiles as $test) {
    $filename = $test["filename"];
    $providedSlot = $test["slot"];
    $shouldPass = $test["valid"];
    
    // Detect expected slot
    $expectedSlot = null;
    $upperName = strtoupper($filename);
    
    if ($providedSlot == 8) {
        // Slot 8 is special case, always valid
        $expectedSlot = 8;
        $isValid = true;
    } else {
        // For slots 1-7, check filename prefix
        foreach ($slotMapping as $prefix => $slotNum) {
            if (strpos($upperName, $prefix) === 0) {
                $expectedSlot = $slotNum;
                break;
            }
        }
        
        // Validate: detected slot must match provided slot
        $isValid = ($expectedSlot !== null && $expectedSlot == $providedSlot);
    }
    
    // Check if result matches expectation
    $overallStatus = ($isValid === $shouldPass) ? "PASS" : "FAIL";
    $statusColor = ($overallStatus === "PASS") ? "green" : "red";
    
    echo "<tr>";
    echo "<td>$filename</td>";
    echo "<td>$providedSlot</td>";
    echo "<td>" . ($expectedSlot ?? 'N/A') . "</td>";
    echo "<td>" . ($shouldPass ? "Yes" : "No") . "</td>";
    echo "<td>" . ($isValid ? "Valid" : "Invalid") . "</td>";
    echo "<td style='color: $statusColor; font-weight: bold;'>$overallStatus</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Validation Rules:</h3>";
echo "<ul>";
echo "<li>Filenames for slots 1-7 must start with the correct prefix (BSME, BSCJ, BSTM, BSE, BSN, BSIS, BSBA)</li>";
echo "<li>The detected slot from the filename must match the provided slot</li>";
echo "<li>Slot 8 (background) has no filename validation requirements</li>";
echo "<li>If the filename doesn't match the slot, the upload is automatically cancelled</li>";
echo "</ul>";

echo "<h3>Implementation Summary:</h3>";
echo "<p>The filename validation has been implemented in both the frontend (BatchTemplates.js) and backend (UploadCover.php). When uploading files:</p>";
echo "<ol>";
echo "<li>The system checks if the filename prefix matches the expected slot</li>";
echo "<li>If there's a mismatch, the upload is automatically cancelled with an error message</li>";
echo "<li>Slot 8 (background images) are exempt from this validation</li>";
echo "<li>Users receive immediate feedback when uploads are cancelled due to filename mismatches</li>";
echo "</ol>";

?>