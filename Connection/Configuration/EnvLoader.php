<?php

/**
 * Environment Variable Loader
 * 
 * This file loads environment variables from .env file and provides
 * helper functions to access MongoDB and Bunny CDN credentials safely.
 * 
 * Include this file at the beginning of any script that needs credentials.
 */

// Load composer autoloader if not already loaded
if (!class_exists('Dotenv\Dotenv')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

// Load .env file if it exists
$dotenvPath = __DIR__ . '/../../';
if (file_exists($dotenvPath . '.env') && !isset($_ENV['ENV_LOADED'])) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
        $dotenv->load();
        $_ENV['ENV_LOADED'] = true;
    } catch (Exception $e) {
        error_log('Failed to load .env file: ' . $e->getMessage());
    }
}

/**
 * Get MongoDB URL from environment
 * @return string MongoDB connection URL
 * @throws Exception if MongoDB URL is not configured
 */
function getMongoUrl()
{
    $mongoUrl = getenv('MONGO_URL') ?: $_ENV['MONGO_URL'] ?? getenv('MONGODB_URI') ?? $_ENV['MONGODB_URI'] ?? null;

    if (!$mongoUrl) {
        throw new Exception('MongoDB URL not configured. Please set MONGO_URL in .env file');
    }

    return $mongoUrl;
}

/**
 * Get Bunny CDN credentials from environment
 * @return array Array with keys: storage_zone, access_key, cdn_host
 * @throws Exception if Bunny CDN credentials are not configured
 */
function getBunnyConfig()
{
    $storageZone = getenv('BUNNY_STORAGE_ZONE') ?: $_ENV['BUNNY_STORAGE_ZONE'] ?? null;
    $accessKey = getenv('BUNNY_ACCESS_KEY') ?: $_ENV['BUNNY_ACCESS_KEY'] ?? null;
    $cdnHost = getenv('BUNNY_CDN_HOST') ?: $_ENV['BUNNY_CDN_HOST'] ?? null;

    if (!$storageZone || !$accessKey || !$cdnHost) {
        throw new Exception('Bunny CDN configuration incomplete. Please set BUNNY_STORAGE_ZONE, BUNNY_ACCESS_KEY, and BUNNY_CDN_HOST in .env file');
    }

    return [
        'storage_zone' => $storageZone,
        'access_key' => $accessKey,
        'cdn_host' => $cdnHost
    ];
}

/**
 * Get Bunny CDN storage zone
 * @return string Storage zone name
 */
function getBunnyStorageZone()
{
    return getenv('BUNNY_STORAGE_ZONE') ?: $_ENV['BUNNY_STORAGE_ZONE'] ??
        (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? null));
}

/**
 * Get Bunny CDN access key
 * @return string Access key
 */
function getBunnyAccessKey()
{
    return getenv('BUNNY_ACCESS_KEY') ?: $_ENV['BUNNY_ACCESS_KEY'] ??
        (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));
}

/**
 * Get Bunny CDN host
 * @return string CDN host URL
 */
function getBunnyCdnHost()
{
    return getenv('BUNNY_CDN_HOST') ?: $_ENV['BUNNY_CDN_HOST'] ??
        (defined('BUNNY_CDN_HOST') ? BUNNY_CDN_HOST : ($GLOBALS['BUNNY_CDN_HOST'] ?? null));
}
