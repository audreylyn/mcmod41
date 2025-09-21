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

    // Path to SSL certificate - works for both local and Azure environments
    $ssl_ca = file_exists(__DIR__ . '/../DigiCertGlobalRootCA.crt.pem') 
        ? __DIR__ . '/../DigiCertGlobalRootCA.crt.pem'
        : '/home/site/wwwroot/DigiCertGlobalRootCA.crt.pem';

    $conn = mysqli_init();
    if (!$conn) {
        die('mysqli_init failed');
    }

    // ✅ Attach SSL CA cert
    mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL);

    // ✅ Use SSL mode with proper error handling
    try {
        if (!mysqli_real_connect($conn, $host, $user, $pass, $name, $port, NULL, MYSQLI_CLIENT_SSL)) {
            error_log("MySQL Connection Error: " . mysqli_connect_error());
            die('Database connection failed: ' . mysqli_connect_error());
        }
    } catch (mysqli_sql_exception $e) {
        error_log("MySQL SSL Exception: " . $e->getMessage());
        die('Database SSL connection failed: ' . $e->getMessage());
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