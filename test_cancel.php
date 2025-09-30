<?php
// Test script to check if the cancellation mechanism is working
echo "Testing cancellation mechanism...\n";

// Simulate a long-running process that checks for connection abort
for ($i = 0; $i < 10; $i++) {
    echo "Processing step $i...\n";
    sleep(1);
    
    // Check if client has disconnected
    if (connection_aborted()) {
        echo "Client disconnected! Cancelling operation...\n";
        // Cleanup operations would go here
        exit;
    }
}

echo "Process completed successfully!\n";
?>