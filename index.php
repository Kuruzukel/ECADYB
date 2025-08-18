<?php
// Railway deployment entry point
// Serve the main application or show a welcome page

if (php_sapi_name() !== 'cli') {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Only the path, ignore query string
    $normalizedUri = strtolower($requestUri); // For case-insensitive routing

    // ----------------------
    // Redirect root (/) to /login
    // ----------------------
    if ($normalizedUri === '/' || $normalizedUri === '/index.php') {
        header("Location: /login");
        exit;
    }

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

    // ----------------------
    // Serve Public/login.html
    // ----------------------
    if ($normalizedUri === '/public/login.html' || $normalizedUri === '/login' || $normalizedUri === '/login.html') {
        $filePath = __DIR__ . '/public/login.html';
        if (file_exists($filePath)) {
            $htmlContent = file_get_contents($filePath);

            // Fix relative paths (so css/js load correctly)
            $htmlContent = str_replace([
                'href="css/',
                'src="js/',
            ], [
                'href="/public/css/',
                'src="/public/js/',
            ], $htmlContent);

            echo $htmlContent;
            exit;
        } else {
            http_response_code(404);
            echo "Login page not found";
            exit;
        }
    }

    // ----------------------
    // Serve Public assets (CSS/JS for login.html)
    // ----------------------
    if (preg_match('#^/public/(css|js)/(.+)$#i', $requestUri, $matches)) {
        $filePath = __DIR__ . '/public/' . $matches[1] . '/' . $matches[2];
        if (file_exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = ['css' => 'text/css', 'js' => 'application/javascript'];
            header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
            readfile($filePath);
            exit;
        } else {
            http_response_code(404);
            echo "Asset not found";
            exit;
        }
    }

    // ----------------------
    // Serve LandingPage.html (only if someone visits /LandingPage/)
    // ----------------------
    $landingPagePath = __DIR__ . '/LandingPage/LandingPage.html';
    if ($normalizedUri === '/landingpage' || $normalizedUri === '/landingpage/landingpage.html') {
        if (file_exists($landingPagePath)) {
            $htmlContent = file_get_contents($landingPagePath);

            // Fix relative paths for deployment
            $htmlContent = str_replace([
                'href="LandingPage.css"',
                'src="LandingPage.js"',
                'src="../img/',
                'href="../',
                'src="LandingPageYB/',
            ], [
                'href="LandingPage/LandingPage.css"',
                'src="LandingPage/LandingPage.js"',
                'src="img/',
                'href="',
                'src="LandingPage/LandingPageYB/',
            ], $htmlContent);

            echo $htmlContent;
            exit;
        }
    }

    // ----------------------
    // Default response
    // ----------------------
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