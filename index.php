<?php
// Railway deployment entry point
// Serve the main application or show a welcome page

if (php_sapi_name() !== 'cli') {
    $requestUri = $_SERVER['REQUEST_URI'];
    
    // Handle LandingPageYB requests
    if (strpos($requestUri, '/LandingPage/LandingPageYB/') !== false || strpos($requestUri, '/LandingPageYB/') !== false) {
        include __DIR__ . '/LandingPage/LandingPageYB/index.php';
        exit;
    }
    
    // For web requests, serve the LandingPage if it exists
    if (file_exists('LandingPage/LandingPage.html')) {
        // Serve the HTML file directly
        $htmlContent = file_get_contents('LandingPage/LandingPage.html');
        
        // Fix relative paths in the HTML for Railway deployment
        $htmlContent = str_replace('href="LandingPage.css"', 'href="LandingPage/LandingPage.css"', $htmlContent);
        $htmlContent = str_replace('src="LandingPage.js"', 'src="LandingPage/LandingPage.js"', $htmlContent);
        $htmlContent = str_replace('src="../img/', 'src="img/', $htmlContent);
        $htmlContent = str_replace('href="../', 'href="', $htmlContent);
        $htmlContent = str_replace('href="admin/', 'href="Admin/', $htmlContent);
        $htmlContent = str_replace('href="student/', 'href="student/', $htmlContent);
        $htmlContent = str_replace('src="LandingPageYB/', 'src="LandingPage/LandingPageYB/', $htmlContent);
        
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
