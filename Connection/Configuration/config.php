<?php
if (getenv('RAILWAY_PUBLIC_URL')) {
    define('BASE_URL', '/');
} else {
    define('BASE_URL', '/ECADYB/');
}

function getBaseUrl()
{
    return BASE_URL;
}