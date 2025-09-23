<?php

// As constants (preferred)
if (!defined('BUNNY_STORAGE_ZONE')) define('BUNNY_STORAGE_ZONE', 'ecadyb');
if (!defined('BUNNY_ACCESS_KEY')) define('BUNNY_ACCESS_KEY', 'db959684-d63e-41f4-a1c7de4737a9-2dd8-41fb'); // write access key
if (!defined('BUNNY_CDN_HOST')) define('BUNNY_CDN_HOST', 'https://ECADYB.b-cdn.net');

// Also expose in $GLOBALS for older includes
$GLOBALS['BUNNY_STORAGE_ZONE'] = BUNNY_STORAGE_ZONE;
$GLOBALS['BUNNY_ACCESS_KEY'] = BUNNY_ACCESS_KEY;
$GLOBALS['BUNNY_CDN_HOST'] = BUNNY_CDN_HOST;
