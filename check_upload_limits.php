<?php
echo "<h1>PHP Upload Limits</h1>";

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Setting</th><th>Value</th><th>Recommended</th></tr>";

$current_post_max = ini_get('post_max_size');
$current_upload_max = ini_get('upload_max_filesize');
$current_memory = ini_get('memory_limit');
$current_execution = ini_get('max_execution_time');

echo "<tr>";
echo "<td>post_max_size</td>";
echo "<td>$current_post_max</td>";
echo "<td>100M or higher</td>";
echo "</tr>";

echo "<tr>";
echo "<td>upload_max_filesize</td>";
echo "<td>$current_upload_max</td>";
echo "<td>100M or higher</td>";
echo "</tr>";

echo "<tr>";
echo "<td>memory_limit</td>";
echo "<td>$current_memory</td>";
echo "<td>256M or higher</td>";
echo "</tr>";

echo "<tr>";
echo "<td>max_execution_time</td>";
echo "<td>$current_execution seconds</td>";
echo "<td>300 seconds or higher</td>";
echo "</tr>";

echo "</table>";

echo "<h2>File Upload Information</h2>";
echo "<p>Current script is: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>Upload directory is writable: " . (is_writable(dirname($_SERVER['SCRIPT_FILENAME'])) ? 'Yes' : 'No') . "</p>";

// Check if we can upload files
echo "<h2>File Upload Status</h2>";
if (ini_get('file_uploads')) {
    echo "<p>File uploads are enabled</p>";
} else {
    echo "<p>File uploads are disabled</p>";
}

echo "<h2>Recommendations</h2>";
echo "<ol>";
echo "<li>Increase post_max_size to 100M or higher</li>";
echo "<li>Increase upload_max_filesize to 100M or higher</li>";
echo "<li>Increase memory_limit to 256M or higher</li>";
echo "<li>Increase max_execution_time to 300 seconds or higher</li>";
echo "</ol>";

echo "<p>These settings can be changed in your php.ini file or through .htaccess if using Apache.</p>";
?>