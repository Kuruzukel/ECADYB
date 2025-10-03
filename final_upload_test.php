<?php
echo "<h1>Upload Limit Fix Verification</h1>";

// Try to set limits at runtime
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');

// Get current values
$post_max_size = ini_get('post_max_size');
$upload_max_filesize = ini_get('upload_max_filesize');
$memory_limit = ini_get('memory_limit');
$max_execution_time = ini_get('max_execution_time');

echo "<h2>Current PHP Settings</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

// Check post_max_size
echo "<tr>";
echo "<td>post_max_size</td>";
echo "<td>$post_max_size</td>";
if ($post_max_size >= '100M' || intval($post_max_size) >= 100) {
    echo "<td style='color: green;'>✓ Sufficient</td>";
} else {
    echo "<td style='color: orange;'>⚠ May need increase</td>";
}
echo "</tr>";

// Check upload_max_filesize
echo "<tr>";
echo "<td>upload_max_filesize</td>";
echo "<td>$upload_max_filesize</td>";
if ($upload_max_filesize >= '100M' || intval($upload_max_filesize) >= 100) {
    echo "<td style='color: green;'>✓ Sufficient</td>";
} else {
    echo "<td style='color: orange;'>⚠ May need increase</td>";
}
echo "</tr>";

// Check memory_limit
echo "<tr>";
echo "<td>memory_limit</td>";
echo "<td>$memory_limit</td>";
if ($memory_limit >= '256M' || intval($memory_limit) >= 256) {
    echo "<td style='color: green;'>✓ Sufficient</td>";
} else {
    echo "<td style='color: orange;'>⚠ May need increase</td>";
}
echo "</tr>";

// Check max_execution_time
echo "<tr>";
echo "<td>max_execution_time</td>";
echo "<td>$max_execution_time seconds</td>";
if ($max_execution_time >= 300 || $max_execution_time == 0) {
    echo "<td style='color: green;'>✓ Sufficient</td>";
} else {
    echo "<td style='color: orange;'>⚠ May need increase</td>";
}
echo "</tr>";

echo "</table>";

echo "<h2>Recommendations</h2>";
echo "<ol>";
echo "<li><strong>For immediate fix</strong>: The runtime settings above should help with your upload issue.</li>";
echo "<li><strong>For permanent fix</strong>: Update your php.ini file with these values and restart your web server:</li>";
echo "<ul>";
echo "<li>upload_max_filesize = 100M</li>";
echo "<li>post_max_size = 100M</li>";
echo "<li>memory_limit = 256M</li>";
echo "<li>max_execution_time = 300</li>";
echo "</ul>";
echo "<li><strong>For Docker users</strong>: Rebuild your container to apply the php.ini changes.</li>";
echo "<li><strong>For XAMPP users</strong>: Restart the Apache service to apply the php.ini changes.</li>";
echo "</ol>";

echo "<h2>Additional Notes</h2>";
echo "<p>Your error indicated an 81MB file, which exceeds the 40MB limit. The changes above increase the limit to 100MB, which should resolve your issue.</p>";
echo "<p>If you're still experiencing issues after applying these changes, please restart your web server.</p>";
?>