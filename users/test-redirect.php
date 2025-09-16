<?php
// Add ngrok compatibility headers
header('ngrok-skip-browser-warning: true');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');

session_start();

echo "<h1>Redirect Test Page</h1>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>HTTP Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p><strong>PHP Self:</strong> " . $_SERVER['PHP_SELF'] . "</p>";
echo "<p><strong>Query String:</strong> " . $_SERVER['QUERY_STRING'] . "</p>";
echo "<p><strong>HTTPS:</strong> " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'not set') . "</p>";
echo "<p><strong>X-Forwarded-Proto:</strong> " . (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : 'not set') . "</p>";

echo "<hr>";

// Check session
if (isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['Student', 'Teacher'])) {
    echo "<p><strong>Session Status:</strong> Valid (" . $_SESSION['role'] . " - ID: " . $_SESSION['user_id'] . ")</p>";
    
    // Test different redirect URL constructions
    $host = $_SERVER['HTTP_HOST'];
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? "https://" : "http://";
    
    $currentDir = dirname($_SERVER['REQUEST_URI']);
    if (strpos($currentDir, '?') !== false) {
        $currentDir = substr($currentDir, 0, strpos($currentDir, '?'));
    }
    
    $path = dirname($_SERVER['PHP_SELF']);
    $reportPage = 'report-equipment-issue.php';
    $queryParams = $_SERVER['QUERY_STRING'];
    
    $url1 = $protocol . $host . $currentDir . '/' . $reportPage;
    $url2 = $protocol . $host . $path . '/' . $reportPage;
    $url3 = $reportPage;
    
    if (!empty($queryParams)) {
        $url1 .= '?' . $queryParams;
        $url2 .= '?' . $queryParams;
        $url3 .= '?' . $queryParams;
    }
    
    echo "<h3>Redirect URL Options:</h3>";
    echo "<p><strong>Method 1 (REQUEST_URI):</strong> <a href='$url1'>$url1</a></p>";
    echo "<p><strong>Method 2 (PHP_SELF):</strong> <a href='$url2'>$url2</a></p>";
    echo "<p><strong>Method 3 (Relative):</strong> <a href='$url3'>$url3</a></p>";
    
    // Check if target file exists
    $targetFile = __DIR__ . '/' . $reportPage;
    echo "<p><strong>Target file exists:</strong> " . (file_exists($targetFile) ? 'YES' : 'NO') . " ($targetFile)</p>";
    
} else {
    echo "<p><strong>Session Status:</strong> Invalid or not logged in</p>";
    echo "<p>Please log in first</p>";
}
?>
