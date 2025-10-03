<?php
echo "<h1>Folder Structure Validation Test</h1>";

echo "<h2>Valid Department Folders:</h2>";
$validDepartments = ['beced', 'bscje', 'bse', 'bsis', 'bsma', 'bsme', 'bsmt', 'bsn', 'bstm', 'btvted'];
echo "<ul>";
foreach ($validDepartments as $dept) {
    echo "<li>" . strtoupper($dept) . "</li>";
}
echo "</ul>";

echo "<h2>Valid Top Management Folder:</h2>";
echo "<ul>";
echo "<li>TOPMANAGEMENT</li>";
echo "</ul>";

echo "<h2>Expected Folder Structure:</h2>";
echo "<p>For student images:</p>";
echo "<ul>";
echo "<li>[DEPARTMENT]/[student_id].ext</li>";
echo "<li>Example: BECED/123456789.jpg</li>";
echo "<li>Example: BSME/987654321.png</li>";
echo "</ul>";

echo "<p>For top management images:</p>";
echo "<ul>";
echo "<li>TOPMANAGEMENT/[name].ext</li>";
echo "<li>Example: TOPMANAGEMENT/JohnDoe.jpg</li>";
echo "<li>Example: TOPMANAGEMENT/JaneSmith.png</li>";
echo "</ul>";

echo "<h2>Validation Rules:</h2>";
echo "<ol>";
echo "<li>Department folder names must match exactly: BECED, BSCJE, BSE, BSIS, BSMA, BSME, BSMT, BSN, BSTM, BTVTED</li>";
echo "<li>Top management folder name must be exactly: TOPMANAGEMENT</li>";
echo "<li>Student image filenames must be numeric (student IDs)</li>";
echo "<li>Top management image filenames will be used as the person's name</li>";
echo "<li>Student images will be stored in MongoDB collections named after the department codes</li>";
echo "<li>Top management images will be stored in the Top_Management database, Photos collection</li>";
echo "</ol>";

echo "<p>Test completed.</p>";
?>