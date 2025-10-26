<?php
header('Content-Type: application/json');

$template = 1;
$batchYear = "Batch Year 2024-2025";

echo "Testing FetchTopManagement API...\n";
echo "Template: $template\n";
echo "Batch Year: $batchYear\n\n";

$url = "http://localhost/ECADYB/Connection/Photos/FetchTopManagement.php?template=$template&batch_year=" . urlencode($batchYear);

echo "Request URL: $url\n\n";

$response = file_get_contents($url);
echo "Response:\n";
echo $response;
