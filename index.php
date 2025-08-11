<?php
// Railway deployment entry point
// Serve the main application or show a welcome page

if (php_sapi_name() !== 'cli') {
    $requestUri = $_SERVER['REQUEST_URI'];

    // Handle LandingPageYB requests
    if (stripos($requestUri, '/LandingPage/LandingPageYB/') !== false || stripos($requestUri, '/LandingPageYB/') !== false) {
        include __DIR__ . '/LandingPage/LandingPageYB/index.php';
        exit;
    }

    // Handle StudentLogin requests
    if (stripos($requestUri, '/Student/Session/StudentLogin.php') !== false) {
        include __DIR__ . '/Student/Session/StudentLogin.php';
        exit;
    }

    // Serve StudentLogin CSS and JS directly (assets inside /Student/)
    if (stripos($requestUri, '/Student/assets/css/StudentLogin.css') !== false) {
        header('Content-Type: text/css');
        readfile(__DIR__ . '/Student/assets/css/StudentLogin.css');
        exit;
    }
    if (stripos($requestUri, '/Student/assets/js/StudentLogin.js') !== false) {
        header('Content-Type: application/javascript');
        readfile(__DIR__ . '/Student/assets/js/StudentLogin.js');
        exit;
    }

    // Handle AdminLogin requests
    if (stripos($requestUri, '/Admin/Session/AdminLogin.php') !== false) {
        include __DIR__ . '/Admin/Session/AdminLogin.php';
        exit;
    }

    // Serve AdminLogin CSS and JS directly (assets inside /Admin/)
    if (stripos($requestUri, '/Admin/assets/css/AdminLogin.css') !== false) {
        header('Content-Type: text/css');
        readfile(__DIR__ . '/Admin/assets/css/AdminLogin.css');
        exit;
    }
    if (stripos($requestUri, '/Admin/assets/js/AdminLogin.js') !== false) {
        header('Content-Type: application/javascript');
        readfile(__DIR__ . '/Admin/assets/js/AdminLogin.js');
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
        $htmlContent = str_replace('href="student/', 'href="Student/', $htmlContent);
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