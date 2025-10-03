<?php
// This script will verify that the PHP limits have been updated
echo "<h1>PHP Configuration Verification</h1>";

// Get current values
$post_max_size = ini_get('post_max_size');
$upload_max_filesize = ini_get('upload_max_filesize');

echo "<h2>Current Settings</h2>";
echo "<p><strong>post_max_size:</strong> $post_max_size</p>";
echo "<p><strong>upload_max_filesize:</strong> $upload_max_filesize</p>";

// Convert to bytes for comparison
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

$post_max_bytes = return_bytes($post_max_size);
$upload_max_bytes = return_bytes($upload_max_filesize);

echo "<h2>Size Comparison</h2>";
echo "<p><strong>post_max_size in bytes:</strong> $post_max_bytes</p>";
echo "<p><strong>upload_max_filesize in bytes:</strong> $upload_max_bytes</p>";

// Check if limits are sufficient for large uploads
$required_bytes = 81061755; // ~81MB from the error

if ($post_max_bytes > $required_bytes && $upload_max_bytes > $required_bytes) {
    echo "<p style='color: green;'><strong>✓ Limits are sufficient for your uploads</strong></p>";
} else {
    echo "<p style='color: red;'><strong>✗ Limits are still too low for your uploads</strong></p>";
    echo "<p>You need to increase the limits in your php.ini file to at least 100M.</p>";
}

echo "<h2>Recommendations</h2>";
echo "<ol>";
echo "<li>Ensure your php.ini file has been updated with the new limits</li>";
echo "<li>Restart your web server to apply the changes</li>";
echo "<li>If using Docker, rebuild your container to apply the changes</li>";
echo "</ol>";
?>