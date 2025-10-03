<?php
// Test script for folder structure validation
echo "<h1>Folder Structure Validation Test</h1>";

// Valid departments
$validDepartments = ['beced', 'bscje', 'bse', 'bsis', 'bsma', 'bsme', 'bsmt', 'bsn', 'bstm', 'btvted'];

echo "<h2>Valid Department Codes:</h2>";
echo "<ul>";
foreach ($validDepartments as $dept) {
    echo "<li>$dept</li>";
}
echo "</ul>";

echo "<h2>Expected Folder Structure:</h2>";
echo "<p>Departments/[DEPARTMENT]/[student_id].ext</p>";
echo "<p>Example:</p>";
echo "<ul>";
echo "<li>Departments/beced/123456789.jpg</li>";
echo "<li>Departments/bsme/987654321.png</li>";
echo "<li>Departments/bsn/456789123.gif</li>";
echo "</ul>";

echo "<h2>Validation Rules:</h2>";
echo "<ol>";
echo "<li>Root folder must be named 'Departments'</li>";
echo "<li>Second level folders must match valid department codes</li>";
echo "<li>Image filenames must be numeric (student IDs)</li>";
echo "<li>Images will be stored in MongoDB collections named after the department codes</li>";
echo "</ol>";

echo "<p>Test completed.</p>";
?>