<?php
// Railway deployment entry point
// Serve the main application or show a welcome page

if (php_sapi_name() !== 'cli') {
    // For web requests, serve the LandingPage if it exists
    if (file_exists('LandingPage/LandingPage.html')) {
        // Serve the HTML file directly
        $htmlContent = file_get_contents('LandingPage/LandingPage.html');
        
        // Fix relative paths in the HTML
        $htmlContent = str_replace('src="../img/', 'src="img/', $htmlContent);
        $htmlContent = str_replace('href="../', 'href="', $htmlContent);
        
        echo $htmlContent;
        exit;
    } else {
        echo '<h1>ECADYB Application</h1>';
        echo '<p>Application is running successfully!</p>';
        echo '<p>File check: LandingPage/LandingPage.html ' . (file_exists('LandingPage/LandingPage.html') ? 'exists' : 'not found') . '</p>';
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
