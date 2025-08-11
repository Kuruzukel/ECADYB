<?php
// Railway deployment entry point
// Serve the main application or show a welcome page

if (php_sapi_name() !== 'cli') {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Parse path only, ignore query

    // Normalize slashes and lowercase for case-insensitive matching
    $normalizedUri = strtolower($requestUri);

    // Handle LandingPageYB requests
    if (strpos($normalizedUri, '/landingpage/landingpageyb/') !== false || strpos($normalizedUri, '/landingpageyb/') !== false) {
        include __DIR__ . '/LandingPage/LandingPageYB/index.php';
        exit;
    }

    // Handle StudentLogin requests
    if (strpos($normalizedUri, '/student/session/studentlogin.php') !== false) {
        include __DIR__ . '/Student/Session/StudentLogin.php';
        exit;
    }

    // Serve StudentLogin CSS and JS directly (assets inside /Student/)
    if (preg_match('#^/student/assets/(css|js)/(.+)$#i', $requestUri, $matches)) {
        $filePath = __DIR__ . '/Student/assets/' . $matches[1] . '/' . $matches[2];
        if (file_exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
            ];
            header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
            readfile($filePath);
            exit;
        }
    }

    // Handle AdminLogin requests
    if (strpos($normalizedUri, '/admin/session/adminlogin.php') !== false) {
        include __DIR__ . '/Admin/Session/AdminLogin.php';
        exit;
    }

    // Serve AdminLogin CSS and JS directly (assets inside /Admin/)
    if (preg_match('#^/admin/assets/(css|js)/(.+)$#i', $requestUri, $matches)) {
        $filePath = __DIR__ . '/Admin/assets/' . $matches[1] . '/' . $matches[2];
        if (file_exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
            ];
            header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
            readfile($filePath);
            exit;
        }
    }

    // For web requests, serve the LandingPage if it exists
    if (file_exists(__DIR__ . '/LandingPage/LandingPage.html')) {
        // Serve the HTML file directly
        $htmlContent = file_get_contents(__DIR__ . '/LandingPage/LandingPage.html');

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
        echo '<p>File check: LandingPage/LandingPage.html ' . (file_exists(__DIR__ . '/LandingPage/LandingPage.html') ? 'exists' : 'not found') . '</p>';
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
            echo '<span style="color: red;">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
        }
        echo '</p>';
    }
} else {
    echo "ECADYB Application is running\n";
}

?>