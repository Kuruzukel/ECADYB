<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Only allow AJAX requests
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
  http_response_code(403);
  exit('Direct access not allowed');
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
  http_response_code(403);
  echo json_encode(['error' => 'Unauthorized']);
  exit();
}

$page = $_GET['page'] ?? 'home';
$allowedPages = ['home', 'about', 'yearbook', 'memories'];

if (!in_array($page, $allowedPages)) {
  http_response_code(404);
  echo json_encode(['error' => 'Page not found']);
  exit();
}

// Map page names to file names
$pageFiles = [
  'home' => 'StudentDashboard.php',
  'about' => 'About.php',
  'yearbook' => 'Yearbook.php',
  'memories' => 'Memories.php'
];

$pageTitles = [
  'home' => 'Student Dashboard - Graduation Gallery',
  'about' => 'About - Graduation Gallery',
  'yearbook' => 'Yearbook - Graduation Gallery',
  'memories' => 'Captured Moments - Graduation Gallery'
];

$filePath = __DIR__ . '/' . $pageFiles[$page];

if (!file_exists($filePath)) {
  http_response_code(404);
  echo json_encode(['error' => 'Page file not found']);
  exit();
}

// Start output buffering
ob_start();

// Include the page file
include $filePath;

// Get the content
$content = ob_get_clean();

// Extract main content using regex (more reliable than DOM parsing)
$mainContent = '';

// Remove everything before the first <section> or <main> tag after header
if (preg_match('/<\/header>(.*?)<footer/s', $content, $matches)) {
  $mainContent = $matches[1];
  // Remove bottom-nav if present
  $mainContent = preg_replace('/<nav class="bottom-nav"[^>]*>.*?<\/nav>/s', '', $mainContent);
  // Remove scripts
  $mainContent = preg_replace('/<script[^>]*>.*?<\/script>/s', '', $mainContent);
} elseif (preg_match_all('/<section[^>]*>.*?<\/section>/s', $content, $matches)) {
  // If no footer, just get all sections
  $mainContent = implode('', $matches[0]);
} else {
  // Fallback: get body content
  if (preg_match('/<body[^>]*>(.*?)<\/body>/s', $content, $matches)) {
    $bodyContent = $matches[1];
    // Remove header, footer, bottom-nav
    $bodyContent = preg_replace('/<header[^>]*>.*?<\/header>/s', '', $bodyContent);
    $bodyContent = preg_replace('/<footer[^>]*>.*?<\/footer>/s', '', $bodyContent);
    $bodyContent = preg_replace('/<nav class="bottom-nav"[^>]*>.*?<\/nav>/s', '', $bodyContent);
    $bodyContent = preg_replace('/<script[^>]*>.*?<\/script>/s', '', $bodyContent);
    $mainContent = $bodyContent;
  }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
  'success' => true,
  'content' => trim($mainContent),
  'title' => $pageTitles[$page],
  'page' => $page
]);

