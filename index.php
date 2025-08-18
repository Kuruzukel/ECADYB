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

            // Fix paths for deployment
            $htmlContent = str_replace([
                'href="LandingPage.css"',
                'src="LandingPage.js"',
                'src="../img/',
                'src="LandingPageYB/',
            ], [
                'href="/LandingPage/LandingPage.css"',
                'src="/LandingPage/LandingPage.js"',
                'src="/img/',
                'src="/LandingPage/LandingPageYB/',
            ], $htmlContent);

            // Fix Log In buttons
            $htmlContent = str_replace(
                ['id="loginDropdownBtn"', 'id="mobileLoginDropdownBtn"'],
                [
                    'id="loginDropdownBtn" onclick="window.location.href=\'/Public/Login.html\'"',
                    'id="mobileLoginDropdownBtn" onclick="window.location.href=\'/Public/Login.html\'"'
                ],
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
    // Serve images from /img (including subfolders)
    // ----------------------
    if (preg_match('#^/img/(.+)$#i', $requestUri, $matches)) {
        $filePath = __DIR__ . '/img/' . $matches[1];
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
            echo "Image not found";
            exit;
        }
    }

    // ----------------------
    // Serve LandingPageYB pages
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
    // Serve Public login page (case-sensitive)
    // ----------------------
    if (preg_match('#^/Public/Login\.html$#i', $requestUri)) {
        $filePath = __DIR__ . '/Public/Login.html';
        if (file_exists($filePath)) {
            $htmlContent = file_get_contents($filePath);

            // Fix relative paths for CSS/JS
            $htmlContent = str_replace([
                'href="css/',
                'src="js/',
            ], [
                'href="/Public/css/',
                'src="/Public/js/',
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
    if (preg_match('#^/Public/(css|js)/(.+)$#i', $requestUri, $matches)) {
        $filePath = __DIR__ . '/Public/' . $matches[1] . '/' . $matches[2];
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