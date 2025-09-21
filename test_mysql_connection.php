<?php
// Database connection test script
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

echo "MySQL Connection Test\n";
echo "====================\n\n";

echo "Testing connection without SSL...\n";

try {
    // Define connection parameters
    $host = 'smartspace.mysql.database.azure.com';
    $user = 'adminuser';
    $pass = 'SmartDb2025!';
    $dbname = 'smartspace';
    $port = 3306;

    // Attempt connection without SSL
    $conn1 = new mysqli($host, $user, $pass, $dbname, $port);
    if ($conn1->connect_error) {
        echo "ERROR: Connection without SSL failed: " . $conn1->connect_error . "\n\n";
    } else {
        echo "SUCCESS: Connected without SSL\n";
        echo "Server version: " . $conn1->server_info . "\n";
        echo "Connection closed\n\n";
        $conn1->close();
    }

    echo "Testing connection with SSL but verification disabled...\n";
    
    // Initialize connection
    $conn2 = mysqli_init();
    
    // Disable SSL verification
    if (!mysqli_options($conn2, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false)) {
        echo "WARNING: Failed to set MYSQLI_OPT_SSL_VERIFY_SERVER_CERT option\n";
    }
    
    // Connect with SSL
    if (!mysqli_real_connect($conn2, $host, $user, $pass, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
        echo "ERROR: Connection with SSL failed: " . mysqli_connect_error() . "\n\n";
    } else {
        echo "SUCCESS: Connected with SSL (verification disabled)\n";
        echo "Server version: " . mysqli_get_server_info($conn2) . "\n";
        echo "Connection closed\n\n";
        mysqli_close($conn2);
    }
    
    echo "Test completed.\n";
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
?>