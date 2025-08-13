<?php
// Railway deployment entry point
// Serve the main application or show a welcome page

if (php_sapi_name() !== 'cli') {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Only the path, ignore query string
    $normalizedUri = strtolower($requestUri); // For case-insensitive routing

    // ----------------------
    // Serve LandingPageYB images
    // ----------------------
    if (preg_match('#^/LandingPage/LandingPageYB/pages/(.+)$#i', $requestUri, $matches)) {
        $filePath = __DIR__ . '/LandingPage/LandingPageYB/pages/' . $matches[1];
        if (file_exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp'
            ];
            header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
            readfile($filePath);
            exit;
        } else {
            http_response_code(404);
            echo "File not found";
            exit;
        }
    }

    // Handle LandingPageYB requests
    if (strpos($normalizedUri, '/landingpage/landingpageyb/') !== false || strpos($normalizedUri, '/landingpageyb/') !== false) {
        include __DIR__ . '/LandingPage/LandingPageYB/index.php';
        exit;
    }

    // Handle StudentLogin requests
    if (strpos($normalizedUri, '/student/session/studentlogin.php') !== false) {
        include __DIR__ . '/student/Session/StudentLogin.php';
        exit;
    }

    // Serve StudentLogin CSS and JS files
    if (preg_match('#^/student/assets/(css|js)/(.+)$#i', $requestUri, $matches)) {
        $filePath = __DIR__ . '/student/assets/' . $matches[1] . '/' . $matches[2];
        if (file_exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = ['css' => 'text/css', 'js' => 'application/javascript'];
            header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
            readfile($filePath);
            exit;
        }
    }

    // Handle AdminLogin requests
    if (strpos($normalizedUri, '/admin/session/adminlogin.php') !== false) {
        include __DIR__ . '/admin/Session/AdminLogin.php';
        exit;
    }

    // Serve AdminLogin CSS and JS files
    if (preg_match('#^/admin/assets/(css|js)/(.+)$#i', $requestUri, $matches)) {
        $filePath = __DIR__ . '/admin/assets/' . $matches[1] . '/' . $matches[2];
        if (file_exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = ['css' => 'text/css', 'js' => 'application/javascript'];
            header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
            readfile($filePath);
            exit;
        }
    }

    // Serve LandingPage.html for root or other requests if exists
    $landingPagePath = __DIR__ . '/LandingPage/LandingPage.html';
    if (file_exists($landingPagePath)) {
        $htmlContent = file_get_contents($landingPagePath);

        // Fix relative paths for deployment
        $htmlContent = str_replace([
            'href="LandingPage.css"',
            'src="LandingPage.js"',
            'src="../img/',
            'href="../',
            'href="admin/',
            'href="student/',
            'src="LandingPageYB/',
        ], [
            'href="LandingPage/LandingPage.css"',
            'src="LandingPage/LandingPage.js"',
            'src="img/',
            'href="',
            'href="admin/',
            'href="student/',
            'src="LandingPage/LandingPageYB/',
        ], $htmlContent);

        echo $htmlContent;
        exit;
    }

    // Default response if no match
    echo '<h1>ECADYB Application</h1>';
    echo '<p>Application is running successfully!</p>';
    echo '<p>File check: LandingPage/LandingPage.html ' . (file_exists($landingPagePath) ? 'exists' : 'not found') . '</p>';
    echo '<p>MongoDB Connection Status: ';

    try {
        require __DIR__ . '/vendor/autoload.php';

        $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://localhost:27017';
        $client = new \MongoDB\Client($mongoUrl);

        $client->listDatabases();
        echo '<span style="color: green;">✓ Connected</span>';
    } catch (Exception $e) {
        echo '<span style="color: red;">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
    echo '</p>';
} else {
    echo "ECADYB Application is running\n";
}
?>