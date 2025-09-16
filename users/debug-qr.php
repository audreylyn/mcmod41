<?php
/**
 * Quick debug script for QR redirect issues
 * Visit: /mcmod41/users/debug-qr.php?id=24
 */

session_start();

// Add headers
header('Content-Type: text/html; charset=UTF-8');
header('ngrok-skip-browser-warning: true');

function debugLog($message, $data = null) {
    echo "<div style='margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 5px;'>";
    echo "<strong>$message</strong>";
    if ($data) {
        echo "<pre style='margin: 5px 0 0 0; background: white; padding: 5px;'>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
    }
    echo "</div>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>QR Redirect Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; }
        .error { background-color: #f8d7da; }
        .info { background-color: #d1ecf1; }
    </style>
</head>
<body>
    <h1>QR Redirect Debug Tool</h1>
    
    <?php
    // 1. Check parameters
    debugLog("URL Parameters", $_GET);
    
    // 2. Check session
    debugLog("Session Data", [
        'user_id' => $_SESSION['user_id'] ?? 'not_set',
        'role' => $_SESSION['role'] ?? 'not_set',
        'name' => $_SESSION['name'] ?? 'not_set'
    ]);
    
    // 3. Check server variables
    debugLog("Server Variables", [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'],
        'REQUEST_SCHEME' => $_SERVER['REQUEST_SCHEME'] ?? 'not_set',
        'REQUEST_URI' => $_SERVER['REQUEST_URI'],
        'HTTPS' => $_SERVER['HTTPS'] ?? 'not_set'
    ]);
    
    // 4. Test equipment ID
    $equipmentId = $_GET['id'] ?? '';
    if ($equipmentId) {
        debugLog("Equipment ID Validation", [
            'raw_id' => $equipmentId,
            'is_numeric' => is_numeric($equipmentId),
            'is_digit' => ctype_digit($equipmentId),
            'converted' => (int)$equipmentId
        ]);
        
        // 5. Test API call
        $apiUrl = "api/get_equipment_details.php?unit_id=" . urlencode($equipmentId);
        debugLog("Testing API", ['api_url' => $apiUrl]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($curlError) {
            debugLog("API Error", ['curl_error' => $curlError]);
        } else {
            $apiResponse = json_decode($response, true);
            debugLog("API Response", [
                'http_code' => $httpCode,
                'response_length' => strlen($response),
                'parsed_response' => $apiResponse
            ]);
        }
    }
    
    // 6. Show what the redirect logic should do
    echo "<div class='section info'>";
    echo "<h3>Expected Redirect Logic</h3>";
    
    if (!isset($_SESSION['user_id'])) {
        echo "<p><strong>Status:</strong> User not authenticated</p>";
        echo "<p><strong>Action:</strong> Should redirect to login</p>";
        $loginUrl = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . '/mcmod41/index.php';
        echo "<p><strong>Login URL:</strong> <a href='$loginUrl'>$loginUrl</a></p>";
    } elseif (!in_array($_SESSION['role'] ?? '', ['Student', 'Teacher'])) {
        echo "<p><strong>Status:</strong> User not authorized</p>";
        echo "<p><strong>Action:</strong> Should redirect to login with error</p>";
    } elseif (empty($equipmentId)) {
        echo "<p><strong>Status:</strong> Invalid equipment ID</p>";
        echo "<p><strong>Action:</strong> Should redirect to login with error</p>";
    } else {
        echo "<p><strong>Status:</strong> All checks passed</p>";
        echo "<p><strong>Action:</strong> Should redirect to report page</p>";
        $reportUrl = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . '/mcmod41/users/report-equipment-issue.php?id=' . $equipmentId;
        echo "<p><strong>Report URL:</strong> <a href='$reportUrl'>$reportUrl</a></p>";
    }
    echo "</div>";
    ?>
    
    <div class="section info">
        <h3>Quick Links</h3>
        <ul>
            <li><a href="equipment-qr.php?id=24">Test QR Redirect (ID 24)</a></li>
            <li><a href="test-equipment-api.php?unit_id=24">Test API (ID 24)</a></li>
            <li><a href="../index.php">Go to Login</a></li>
            <li><a href="users_browse_room.php">Go to Browse Rooms</a></li>
        </ul>
    </div>
</body>
</html>