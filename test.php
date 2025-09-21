<?php
// Simple test file
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
</head>
<body>
    <h1>Test Page</h1>
    <p>If you can see this, the basic PHP file is working.</p>
    
    <h2>Server Information</h2>
    <pre>
<?php
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "PHP Version: " . phpversion() . "\n";

// Check if we're running on Azure
echo "Is Azure: " . (getenv('WEBSITE_SITE_NAME') ? 'Yes' : 'No') . "\n";

// Try to check the file path
echo "Current file: " . __FILE__ . "\n";
echo "Current directory: " . __DIR__ . "\n";
?>
    </pre>
    
    <h2>Available Files</h2>
    <ul>
<?php
// List some key files and directories
$paths_to_check = [
    'index.php',
    'auth/login.php',
    'department-admin/qr_test.php',
    'qr_test.php',
    'users/equipment-qr.php',
    'middleware/error_handler.php'
];

foreach ($paths_to_check as $path) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $path;
    if (file_exists($full_path)) {
        echo "<li>✅ $path exists</li>";
    } else {
        echo "<li>❌ $path does not exist</li>";
    }
}
?>
    </ul>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page environment:', {
                host: window.location.host,
                protocol: window.location.protocol,
                pathname: window.location.pathname,
                href: window.location.href
            });
        });
    </script>
</body>
</html>