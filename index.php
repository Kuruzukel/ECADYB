<?php
// Railway deployment entry point

if (php_sapi_name() !== 'cli') {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $normalizedUri = strtolower($requestUri);

    // ----------------------
    // Serve LandingPage.html at root (/)
    // ----------------------
    if ($normalizedUri === '/' || $normalizedUri === '/index.php') {
        $landingPagePath = __DIR__ . '/LandingPage/LandingPage.html';
        if (file_exists($landingPagePath)) {
            $htmlContent = file_get_contents($landingPagePath);

            // Fix relative paths for CSS/JS/images
            $htmlContent = str_replace([
                'href="LandingPage.css"',
                'src="LandingPage.js"',
                'src="../img/',
                'href="../',
                'src="LandingPageYB/',
            ], [
                'href="/LandingPage/LandingPage.css"',
                'src="/LandingPage/LandingPage.js"',
                'src="/LandingPage/img/',
                'href="/LandingPage/',
                'src="/LandingPage/LandingPageYB/',
            ], $htmlContent);

            // Optionally, ensure the Log In button points to /public/login.html
            $htmlContent = str_replace(
                'id="loginBtn"',
                'id="loginBtn" onclick="window.location.href=\'/public/login.html\'"',
                $htmlContent
            );

            echo $htmlContent;
            exit;
        } else {
            http_response_code(404);
            echo "Landing page not found";
            exit;
        }
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
    // Serve Public Login Form
    // ----------------------
    if ($normalizedUri === '/public/login.html') {
        $filePath = __DIR__ . '/public/login.html';
        if (file_exists($filePath)) {
            $htmlContent = file_get_contents($filePath);

            // Fix relative paths
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
    // Serve Public assets (CSS/JS)
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
    // Default fallback
    // ----------------------
    echo '<h1>ECADYB Application</h1>';
    echo '<p>Application is running successfully!</p>';
}
?>