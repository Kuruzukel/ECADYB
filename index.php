<?php
// Railway deployment entry point
// Redirect to the main application or show a welcome page

if (php_sapi_name() !== 'cli') {
    // For web requests, redirect to the LandingPage if it exists
    if (file_exists('LandingPage/LandingPage.html')) {
        header('Location: LandingPage/LandingPage.html');
        exit;
    } else {
        echo '<h1>ECADYB Application</h1>';
        echo '<p>Application is running successfully!</p>';
        echo '<p>MongoDB Connection Status: ';
        
        // Test MongoDB connection
        try {
            require __DIR__ . '/vendor/autoload.php';
            
            $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://localhost:27017';
            $client = new \MongoDB\Client($mongoUrl);
            
            // Test the connection
            $client->listDatabases();
            echo '<span style="color: green;">✓ Connected</span>';
        } catch (Exception $e) {
            echo '<span style="color: red;">✗ Error: ' . $e->getMessage() . '</span>';
        }
        echo '</p>';
    }
} else {
    echo "ECADYB Application is running\n";
}
?>
