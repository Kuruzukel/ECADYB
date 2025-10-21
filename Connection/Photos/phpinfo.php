<?php
header('Content-Type: text/html');
echo "<h2>PHP Configuration Info</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>php.ini file location:</strong> " . php_ini_loaded_file() . "</p>";
echo "<p><strong>Additional ini files:</strong> " . php_ini_scanned_files() . "</p>";

echo "<h3>Current Upload Settings:</h3>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . "</p>";
echo "<p><strong>max_input_time:</strong> " . ini_get('max_input_time') . "</p>";

echo "<h3>All PHP Settings:</h3>";
echo "<pre>";
phpinfo();
echo "</pre>";
?>
