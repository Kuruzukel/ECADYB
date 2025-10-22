<?php
// Detect if we're on Railway or localhost
if (getenv('RAILWAY_PUBLIC_URL')) {
    define('BASE_URL', '/');
} else {
    define('BASE_URL', '/ECADYB/');
}

// Make BASE_URL available to JavaScript
function getBaseUrl() {
    return BASE_URL;
}
?>

