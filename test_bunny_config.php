<?php
echo "<h1>BunnyCDN Configuration Test</h1>";

// Load BunnyCDN configuration
if (file_exists(__DIR__ . '/Connection/Configuration/BunnyConfig.php')) {
    require __DIR__ . '/Connection/Configuration/BunnyConfig.php';
    echo "<p style='color: green;'>✓ BunnyConfig.php loaded successfully</p>";
} else {
    echo "<p style='color: red;'>✗ BunnyConfig.php not found</p>";
}

// Check if constants are defined
$bunnyStorageZone = defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : (getenv('BUNNY_STORAGE_ZONE') ?: ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? null));
$bunnyAccessKey = defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : (getenv('BUNNY_ACCESS_KEY') ?: ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));
$bunnyCdnHost = defined('BUNNY_CDN_HOST') ? BUNNY_CDN_HOST : (getenv('BUNNY_CDN_HOST') ?: ($GLOBALS['BUNNY_CDN_HOST'] ?? null));

echo "<h2>Configuration Values:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

echo "<tr>";
echo "<td>BUNNY_STORAGE_ZONE</td>";
echo "<td>" . ($bunnyStorageZone ?: 'Not set') . "</td>";
if ($bunnyStorageZone) {
    echo "<td style='color: green;'>✓ Set</td>";
} else {
    echo "<td style='color: red;'>✗ Missing</td>";
}
echo "</tr>";

echo "<tr>";
echo "<td>BUNNY_ACCESS_KEY</td>";
echo "<td>" . ($bunnyAccessKey ? '********' : 'Not set') . "</td>";
if ($bunnyAccessKey) {
    echo "<td style='color: green;'>✓ Set</td>";
} else {
    echo "<td style='color: red;'>✗ Missing</td>";
}
echo "</tr>";

echo "<tr>";
echo "<td>BUNNY_CDN_HOST</td>";
echo "<td>" . ($bunnyCdnHost ?: 'Not set') . "</td>";
if ($bunnyCdnHost) {
    echo "<td style='color: green;'>✓ Set</td>";
} else {
    echo "<td style='color: red;'>✗ Missing</td>";
}
echo "</tr>";

echo "</table>";

// Check if all required values are present
if ($bunnyStorageZone && $bunnyAccessKey && $bunnyCdnHost) {
    echo "<p style='color: green; font-size: 1.2em;'><strong>✓ All BunnyCDN configuration values are properly set!</strong></p>";
} else {
    echo "<p style='color: red; font-size: 1.2em;'><strong>✗ Some BunnyCDN configuration values are missing!</strong></p>";
    echo "<p>Please check your Connection/Configuration/BunnyConfig.php file.</p>";
}

echo "<h2>Troubleshooting Tips:</h2>";
echo "<ol>";
echo "<li>Ensure Connection/Configuration/BunnyConfig.php exists and has correct values</li>";
echo "<li>Check that the file is readable by the web server</li>";
echo "<li>Verify that the access key is valid and has proper permissions</li>";
echo "<li>Confirm that the storage zone name is correct</li>";
echo "</ol>";
?>