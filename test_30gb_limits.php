<?php
echo "<h1>30GB Upload Limit Test</h1>";

// Try to set limits at runtime
ini_set('upload_max_filesize', '30G');
ini_set('post_max_size', '30G');
ini_set('memory_limit', '30G');
ini_set('max_execution_time', '3600');

// Get current values
$post_max_size = ini_get('post_max_size');
$upload_max_filesize = ini_get('upload_max_filesize');
$memory_limit = ini_get('memory_limit');
$max_execution_time = ini_get('max_execution_time');

echo "<h2>Current PHP Settings</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>post_max_size</td><td>$post_max_size</td></tr>";
echo "<tr><td>upload_max_filesize</td><td>$upload_max_filesize</td></tr>";
echo "<tr><td>memory_limit</td><td>$memory_limit</td></tr>";
echo "<tr><td>max_execution_time</td><td>$max_execution_time seconds</td></tr>";
echo "</table>";

// Function to convert php.ini values to bytes for comparison
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = intval($val);
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

// Convert to bytes
$post_max_bytes = return_bytes($post_max_size);
$upload_max_bytes = return_bytes($upload_max_filesize);

// 30GB in bytes
$thirty_gb_bytes = 30 * 1024 * 1024 * 1024;

echo "<h2>Size Comparison</h2>";
echo "<p><strong>post_max_size in bytes:</strong> " . number_format($post_max_bytes) . "</p>";
echo "<p><strong>upload_max_filesize in bytes:</strong> " . number_format($upload_max_bytes) . "</p>";
echo "<p><strong>30GB in bytes:</strong> " . number_format($thirty_gb_bytes) . "</p>";

// Check if limits are sufficient
if ($post_max_bytes >= $thirty_gb_bytes && $upload_max_bytes >= $thirty_gb_bytes) {
    echo "<p style='color: green;'><strong>✓ Limits have been successfully increased to 30GB</strong></p>";
} else {
    echo "<p style='color: orange;'><strong>⚠ Limits may require server restart to take effect</strong></p>";
}

echo "<h2>Important Notes</h2>";
echo "<ol>";
echo "<li>These limits will allow uploads up to 30GB in size</li>";
echo "<li>You may need to restart your web server for the php.ini changes to take effect</li>";
echo "<li>For very large uploads, consider implementing chunked uploads or a queue system</li>";
echo "<li>Ensure your server has sufficient disk space and memory to handle large uploads</li>";
echo "</ol>";
?>