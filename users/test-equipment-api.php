<?php
/**
 * Simple test script for the Equipment API
 * Access via: /mcmod41/users/test-equipment-api.php?unit_id=1
 */

// Add headers for testing
header('Content-Type: text/html; charset=UTF-8');
header('ngrok-skip-browser-warning: true');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Equipment API Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        button { padding: 8px 16px; margin: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Equipment API Test Tool</h1>
    
    <div class="test-section info">
        <h3>Test Equipment API Endpoint</h3>
        <form method="GET">
            <label>Equipment Unit ID: 
                <input type="number" name="unit_id" value="<?php echo $_GET['unit_id'] ?? '1'; ?>" min="1">
            </label>
            <button type="submit">Test API</button>
        </form>
    </div>
    
    <?php if (isset($_GET['unit_id'])): ?>
        <?php
        $unitId = (int)$_GET['unit_id'];
        $apiUrl = "api/get_equipment_details.php?unit_id=" . $unitId;
        
        echo "<div class='test-section info'>";
        echo "<h3>Testing API Endpoint</h3>";
        echo "<p><strong>API URL:</strong> <code>$apiUrl</code></p>";
        echo "</div>";
        
        // Test the API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $endTime = microtime(true);
        
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseTime = round(($endTime - $startTime) * 1000, 2);
        curl_close($ch);
        
        if ($curlError) {
            echo "<div class='test-section error'>";
            echo "<h3>❌ cURL Error</h3>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($curlError) . "</p>";
            echo "</div>";
        } else {
            $responseData = json_decode($response, true);
            
            if ($httpCode === 200 && $responseData && $responseData['success']) {
                echo "<div class='test-section success'>";
                echo "<h3>✅ API Response Successful</h3>";
                echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
                echo "<p><strong>Response Time:</strong> {$responseTime}ms</p>";
                echo "<h4>Equipment Details:</h4>";
                echo "<pre>" . json_encode($responseData['data'], JSON_PRETTY_PRINT) . "</pre>";
                echo "</div>";
                
                // Test QR URL generation
                $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
                $qrUrl = $baseUrl . '/mcmod41/users/equipment-qr.php?id=' . $unitId;
                
                echo "<div class='test-section info'>";
                echo "<h3>📱 Generated QR Code URL</h3>";
                echo "<p><strong>QR URL:</strong> <a href='$qrUrl' target='_blank'>$qrUrl</a></p>";
                echo "<p><em>This is the URL that would be encoded in the QR code</em></p>";
                echo "</div>";
                
            } else {
                echo "<div class='test-section error'>";
                echo "<h3>❌ API Response Failed</h3>";
                echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
                echo "<p><strong>Response Time:</strong> {$responseTime}ms</p>";
                echo "<h4>Raw Response:</h4>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
                echo "</div>";
            }
        }
        ?>
    <?php endif; ?>
    
    <div class="test-section info">
        <h3>📋 How to Use</h3>
        <ol>
            <li>Enter an equipment unit ID in the form above</li>
            <li>Click "Test API" to check if the equipment exists</li>
            <li>If successful, you'll see the equipment details</li>
            <li>The QR code URL will be generated automatically</li>
            <li>You can click the QR URL to test the redirect flow</li>
        </ol>
        
        <h4>Expected Flow:</h4>
        <ol>
            <li><strong>QR Code:</strong> Contains URL like <code>equipment-qr.php?id=123</code></li>
            <li><strong>Redirect Handler:</strong> Validates user, checks equipment exists</li>
            <li><strong>Report Page:</strong> Loads equipment details via API</li>
        </ol>
    </div>
</body>
</html>