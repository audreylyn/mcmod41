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

    // Configure SSL options - Azure MySQL requires SSL but with relaxed verification
    if (file_exists($sslCert)) {
        // Set SSL certificate
        mysqli_ssl_set($conn, NULL, NULL, $sslCert, NULL, NULL);
        // Disable strict verification for Azure MySQL (common issue with Azure)
        mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        error_log("Using SSL certificate: " . $sslCert . " (with relaxed verification for Azure)");
    } else {
        // Disable SSL verification if certificate not found
        mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        error_log("SSL certificate not found at: " . $sslCert . " - connecting without certificate");
    }

    // ✅ Use basic connection mode with proper error handling
    try {
        // Try to connect using SSL but with verification disabled
        if (!mysqli_real_connect($conn, $host, $user, $pass, $name, $port, NULL, MYSQLI_CLIENT_SSL)) {
            error_log("MySQL SSL Connection Error: " . mysqli_connect_error());
            
            // If SSL connection fails, try without SSL as fallback
            $conn = mysqli_init();
            if (!mysqli_real_connect($conn, $host, $user, $pass, $name, $port, NULL)) {
                error_log("MySQL Non-SSL Connection Error: " . mysqli_connect_error());
                die('Database connection failed: ' . mysqli_connect_error());
            } else {
                error_log("Connected without SSL");
            }
        } else {
            error_log("Connected with SSL");
        }
    } catch (Exception $e) {
        error_log("MySQL Exception: " . $e->getMessage());
        die('Database connection failed: ' . $e->getMessage());
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