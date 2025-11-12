<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');

function db(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli && @$conn->ping()) {
        return $conn;
    }

    // Azure MySQL connection settings
    $host = getenv('DB_HOST') ?: 'smartspace.mysql.database.azure.com';
    $user = getenv('DB_USER') ?: 'adminuser'; 
    $pass = getenv('DB_PASS') ?: 'SmartDb2025!';
    $name = getenv('DB_NAME') ?: 'smartspace';
    $port = 3306;

    // SSL Certificate path (same level as index.php)
    $sslCert = __DIR__ . '/../DigiCertGlobalRootCA.crt.pem';
    
    $conn = mysqli_init();
    if (!$conn) {
        die('mysqli_init failed');
    }

    // Try connection with SSL first, then fallback without SSL
    $sslConnected = false;
    
    if (file_exists($sslCert)) {
        // Try with SSL certificate
        mysqli_ssl_set($conn, NULL, NULL, $sslCert, NULL, NULL);
        mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        
        // Disable exception throwing temporarily
        mysqli_report(MYSQLI_REPORT_OFF);
        if (mysqli_real_connect($conn, $host, $user, $pass, $name, $port, NULL, MYSQLI_CLIENT_SSL)) {
            $sslConnected = true;
            error_log("Connected to Azure MySQL with SSL certificate");
        } else {
            error_log("SSL connection failed: " . mysqli_connect_error());
        }
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }
    
    // If SSL connection failed, try without SSL
    if (!$sslConnected) {
        $conn = mysqli_init();
        mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        
        mysqli_report(MYSQLI_REPORT_OFF);
        if (mysqli_real_connect($conn, $host, $user, $pass, $name, $port, NULL, MYSQLI_CLIENT_SSL)) {
            error_log("Connected to Azure MySQL with SSL (no certificate verification)");
        } else {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            error_log("Connection error: " . mysqli_connect_error());
            die('Database connection failed: ' . mysqli_connect_error());
        }
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    if (mysqli_connect_errno()) {
        error_log("MySQL Connect Error: " . mysqli_connect_error());
        die('Database connection failed: ' . mysqli_connect_error());
    }

    $conn->set_charset('utf8mb4');
    $GLOBALS['conn'] = $conn;
    return $conn;
}

db();