<?php
// Test script to check if connection_aborted() works properly
echo "Testing connection_aborted() detection...\n";
echo "Initial connection status: " . (connection_aborted() ? "ABORTED" : "ACTIVE") . "\n";

// Simulate a long-running process
for ($i = 0; $i < 5; $i++) {
    echo "Processing step $i...\n";
    sleep(1);
    
    // Check if client has disconnected
    if (connection_aborted()) {
        echo "Client disconnected! connection_aborted() returned true.\n";
        // This is where we would do cleanup
        exit;
    } else {
        echo "Client still connected. connection_aborted() returned false.\n";
    }
}

echo "Process completed successfully!\n";
?>