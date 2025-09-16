<?php
/**
 * Complete QR Flow Test
 * This simulates the complete QR scanning flow
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?error=not_authenticated&message=' . urlencode('Please log in to test QR functionality'));
    exit();
}

// Include auth to ensure we have database connection
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher', 'Department Admin']);

?>
<!DOCTYPE html>
<html>
<head>
    <title>QR Flow Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-step { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        button { padding: 8px 16px; margin: 5px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 3px; }
        button:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; max-height: 200px; }
        .qr-url { font-family: monospace; background: #f8f9fa; padding: 5px; border-radius: 3px; word-break: break-all; }
    </style>
</head>
<body>
    <h1>🔍 Complete QR Flow Test</h1>
    
    <div class="test-step info">
        <h3>👤 User Session Status</h3>
        <p><strong>User ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
        <p><strong>Role:</strong> <?php echo $_SESSION['role']; ?></p>
        <p><strong>Name:</strong> <?php echo $_SESSION['name'] ?? 'N/A'; ?></p>
        <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
    </div>
    
    <?php
    // Test different equipment IDs
    $testEquipmentIds = [24, 1, 2, 999]; // Include one that probably doesn't exist (999)
    
    foreach ($testEquipmentIds as $equipmentId) {
        echo "<div class='test-step'>";
        echo "<h3>🧪 Testing Equipment ID: $equipmentId</h3>";
        
        // Step 1: Test API call
        $apiUrl = "api/get_equipment_details.php?unit_id=$equipmentId";
        echo "<p><strong>Step 1:</strong> Testing API endpoint</p>";
        echo "<p class='qr-url'>$apiUrl</p>";
        
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
            echo "<div class='error'><strong>❌ API Error:</strong> $curlError</div>";
        } else {
            $apiResponse = json_decode($response, true);
            if ($httpCode === 200 && $apiResponse && $apiResponse['success']) {
                echo "<div class='success'><strong>✅ API Success:</strong> Equipment found</div>";
                echo "<p><strong>Equipment:</strong> " . htmlspecialchars($apiResponse['data']['equipment_name']) . "</p>";
                echo "<p><strong>Location:</strong> " . htmlspecialchars($apiResponse['data']['location']) . "</p>";
                
                // Step 2: Generate QR URL
                $protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
                $host = $_SERVER['HTTP_HOST'];
                $qrUrl = "$protocol://$host/mcmod41/users/equipment-qr.php?id=$equipmentId";
                
                echo "<p><strong>Step 2:</strong> Generated QR URL</p>";
                echo "<p class='qr-url'>$qrUrl</p>";
                
                // Step 3: Test the QR redirect
                echo "<p><strong>Step 3:</strong> Test QR redirect</p>";
                echo "<button onclick=\"window.open('$qrUrl', '_blank')\">🔗 Test QR Redirect</button>";
                
                // Step 4: Direct link to report page
                $reportUrl = "$protocol://$host/mcmod41/users/report-equipment-issue.php?id=$equipmentId";
                echo "<button onclick=\"window.open('$reportUrl', '_blank')\">📝 Direct to Report Page</button>";
                
            } else {
                echo "<div class='error'><strong>❌ API Failed:</strong> Equipment not found or API error</div>";
                echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
                if (isset($apiResponse['message'])) {
                    echo "<p><strong>Message:</strong> " . htmlspecialchars($apiResponse['message']) . "</p>";
                }
            }
        }
        
        echo "</div>";
    }
    ?>
    
    <div class="test-step info">
        <h3>📋 Test Instructions</h3>
        <ol>
            <li>For each equipment ID above, check if the API call succeeds</li>
            <li>If API succeeds, click "Test QR Redirect" to simulate scanning a QR code</li>
            <li>This should redirect you to the equipment report page</li>
            <li>You can also click "Direct to Report Page" to test the report page directly</li>
            <li>The ID 999 should fail (equipment doesn't exist) to test error handling</li>
        </ol>
        
        <h4>Expected Results:</h4>
        <ul>
            <li><strong>Valid Equipment IDs:</strong> Should redirect to report page with equipment details pre-filled</li>
            <li><strong>Invalid Equipment ID (999):</strong> Should redirect to login page with error message</li>
            <li><strong>All redirects:</strong> Should preserve your login session</li>
        </ul>
    </div>
    
    <div class="test-step warning">
        <h3>🔧 Debug Tools</h3>
        <p>If you encounter issues, use these debug tools:</p>
        <button onclick="window.open('debug-qr.php?id=24', '_blank')">🐛 Debug Tool</button>
        <button onclick="window.open('test-equipment-api.php?unit_id=24', '_blank')">🔌 API Test Tool</button>
        <button onclick="window.open('../department-admin/qr_generator.php', '_blank')">📱 QR Generator</button>
    </div>
    
    <script>
        // Add some client-side logging
        console.log('QR Flow Test loaded');
        console.log('Current URL:', window.location.href);
        console.log('User Agent:', navigator.userAgent);
    </script>
</body>
</html>