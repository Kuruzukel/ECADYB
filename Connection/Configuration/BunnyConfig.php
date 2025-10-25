<?php
require __DIR__ . '/../../vendor/autoload.php';

// Load .env file
$dotenvPath = __DIR__ . '/../../';
if (file_exists($dotenvPath . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}

if (!defined('BUNNY_STORAGE_ZONE')) {
    define('BUNNY_STORAGE_ZONE', getenv('BUNNY_STORAGE_ZONE') ?: $_ENV['BUNNY_STORAGE_ZONE'] ?? null);
}
if (!defined('BUNNY_ACCESS_KEY')) {
    define('BUNNY_ACCESS_KEY', getenv('BUNNY_ACCESS_KEY') ?: $_ENV['BUNNY_ACCESS_KEY'] ?? null);
}
if (!defined('BUNNY_CDN_HOST')) {
    define('BUNNY_CDN_HOST', getenv('BUNNY_CDN_HOST') ?: $_ENV['BUNNY_CDN_HOST'] ?? null);
}

if (!BUNNY_STORAGE_ZONE || !BUNNY_ACCESS_KEY || !BUNNY_CDN_HOST) {
    die('Bunny CDN configuration incomplete. Please set BUNNY_STORAGE_ZONE, BUNNY_ACCESS_KEY, and BUNNY_CDN_HOST in .env file');
}

$GLOBALS['BUNNY_STORAGE_ZONE'] = BUNNY_STORAGE_ZONE;
$GLOBALS['BUNNY_ACCESS_KEY'] = BUNNY_ACCESS_KEY;
$GLOBALS['BUNNY_CDN_HOST'] = BUNNY_CDN_HOST;
