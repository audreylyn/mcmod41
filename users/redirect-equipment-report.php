<?php
// Add ngrok compatibility headers
header('ngrok-skip-browser-warning: true');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');

session_start();

// Enhanced debug logging
error_log("=== REDIRECT SCRIPT DEBUG ===");
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("HTTP Host: " . $_SERVER['HTTP_HOST']);
error_log("PHP Self: " . $_SERVER['PHP_SELF']);
error_log("Query String: " . $_SERVER['QUERY_STRING']);
error_log("HTTPS: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'not set'));
error_log("Session data: " . json_encode([
    'user_id' => $_SESSION['user_id'] ?? 'not_set',
    'role' => $_SESSION['role'] ?? 'not_set'
]));

// Check if user is logged in and has a valid role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Student', 'Teacher'])) {
    // Redirect to login page if not authenticated or role is not allowed
    // Determine base URL dynamically to handle different environments
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
    $baseUrl .= $host;
    
    $loginUrl = $baseUrl . '/mcmod41/index.php?error=unauthorized_access';
    error_log("Unauthorized access, redirecting to: " . $loginUrl);
    header('Location: ' . $loginUrl);
    exit();
}

// The destination page for reporting equipment issues
$reportPage = 'report-equipment-issue.php';

// Get query parameters from the current URL
$queryParams = $_SERVER['QUERY_STRING'];

// Multiple approaches to construct redirect URL - try the most reliable one
$host = $_SERVER['HTTP_HOST'];
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? "https://" : "http://";

// Method 1: Use current directory path
$currentDir = dirname($_SERVER['REQUEST_URI']);
if (strpos($currentDir, '?') !== false) {
    $currentDir = substr($currentDir, 0, strpos($currentDir, '?'));
}
$redirectUrl1 = $protocol . $host . $currentDir . '/' . $reportPage;

// Method 2: Use PHP_SELF
$path = dirname($_SERVER['PHP_SELF']);
$redirectUrl2 = $protocol . $host . $path . '/' . $reportPage;

// Method 3: Simple relative redirect (as fallback)
$redirectUrl3 = $reportPage;

// Choose the best method based on environment
$redirectUrl = $redirectUrl1; // Default to method 1

if (!empty($queryParams)) {
    $redirectUrl .= '?' . $queryParams;
}

// Enhanced debug logging
error_log("Protocol: " . $protocol);
error_log("Host: " . $host);
error_log("Current dir: " . $currentDir);
error_log("PHP Self dir: " . $path);
error_log("Method 1 URL: " . $redirectUrl1);
error_log("Method 2 URL: " . $redirectUrl2);
error_log("Method 3 URL: " . $redirectUrl3);
error_log("Final redirect URL: " . $redirectUrl);

// Check if the target file exists
$targetFile = __DIR__ . '/' . $reportPage;
error_log("Target file path: " . $targetFile);
error_log("Target file exists: " . (file_exists($targetFile) ? 'YES' : 'NO'));

// Perform the redirect
header('Location: ' . $redirectUrl);
exit();
?>
