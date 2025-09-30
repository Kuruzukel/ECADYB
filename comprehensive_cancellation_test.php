<?php
// Comprehensive test for upload cancellation
echo "<h1>Comprehensive Upload Cancellation Test</h1>\n";

// Test 1: Check if connection_aborted() works
echo "<h2>Test 1: Connection Aborted Detection</h2>\n";
echo "<p>Initial connection status: " . (connection_aborted() ? "ABORTED" : "ACTIVE") . "</p>\n";

// Test 2: Simulate the upload process with cancellation checks
echo "<h2>Test 2: Simulated Upload Process</h2>\n";

echo "<p>Step 1: Reading file contents...</p>\n";
sleep(1);

// Check for cancellation
if (connection_aborted()) {
    echo "<p style='color: red;'>✗ Cancellation detected after reading file. No upload to BunnyCDN should occur.</p>\n";
    exit;
}

echo "<p>Step 2: Checking for cancellation before BunnyCDN upload...</p>\n";
// This is the critical check we added
if (connection_aborted()) {
    echo "<p style='color: red;'>✗ Cancellation detected before BunnyCDN upload. File should NOT be uploaded.</p>\n";
    exit;
}

echo "<p>Step 3: Simulating BunnyCDN upload...</p>\n";
sleep(2);

// Check for cancellation after upload
if (connection_aborted()) {
    echo "<p style='color: red;'>✗ Cancellation detected after BunnyCDN upload. File should be deleted from BunnyCDN.</p>\n";
    // Simulate cleanup
    echo "<p style='color: orange;'>Cleanup: Deleting file from BunnyCDN...</p>\n";
    sleep(1);
    echo "<p style='color: green;'>✓ File deleted from BunnyCDN</p>\n";
    exit;
}

echo "<p>Step 4: Updating MongoDB...</p>\n";
sleep(1);

if (connection_aborted()) {
    echo "<p style='color: red;'>✗ Cancellation detected after MongoDB operations. Cleaning up...</p>\n";
    echo "<p style='color: orange;'>Cleanup: Deleting file from BunnyCDN...</p>\n";
    sleep(1);
    echo "<p style='color: green;'>✓ File deleted from BunnyCDN</p>\n";
    echo "<p style='color: orange;'>Cleanup: Deleting MongoDB entry...</p>\n";
    sleep(1);
    echo "<p style='color: green;'>✓ MongoDB entry deleted</p>\n";
    exit;
}

echo "<p style='color: green;'>✓ Upload completed successfully</p>\n";

echo "<h2>Summary</h2>\n";
echo "<p>The cancellation mechanism has been enhanced with:</p>\n";
echo "<ul>\n";
echo "  <li>Multiple connection_aborted() checks throughout the process</li>\n";
echo "  <li>A critical check RIGHT BEFORE BunnyCDN upload to prevent unnecessary uploads</li>\n";
echo "  <li>Automatic cleanup when cancellation is detected at any stage</li>\n";
echo "  <li>Faster detection through ob_flush() and flush() calls</li>\n";
echo "  <li>Reduced timeouts for faster cancellation response</li>\n";
echo "</ul>\n";

echo "<h3>Expected Behavior When User Clicks 'Cancel':</h3>\n";
echo "<ol>\n";
echo "  <li>JavaScript immediately aborts the XMLHttpRequest</li>\n";
echo "  <li>Browser closes the connection to the server</li>\n";
echo "  <li>PHP detects connection_aborted() and stops processing</li>\n";
echo "  <li>If file was already uploaded to BunnyCDN, it's automatically deleted</li>\n";
echo "  <li>User sees 'Cancelled upload' notification</li>\n";
echo "  <li>No entry is created in MongoDB</li>\n";
echo "  <li>Upload box remains empty</li>\n";
echo "</ol>\n";
?>