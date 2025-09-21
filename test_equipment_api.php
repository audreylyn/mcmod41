<?php
/**
 * Test Equipment API Endpoint
 * Use this to test if the equipment API is working on deployment
 */

header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Equipment API Test\n";
echo "==================\n\n";

// Test with a known equipment ID (you can change this to match your data)
$testUnitId = $_GET['unit_id'] ?? '23'; // Default to 23, or use query parameter

echo "Testing with unit_id: $testUnitId\n\n";

// Build the API URL using the same logic as your QR system
$protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
$host = $_SERVER['HTTP_HOST'];

// Detect if we're in a subdirectory
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$basePath = '';
if (strpos($requestUri, '/mcmod41/') !== false) {
    $basePath = '/mcmod41';
}

$apiUrl = $protocol . '://' . $host . $basePath . '/users/api/get_equipment_details.php?unit_id=' . urlencode($testUnitId);

echo "API URL: $apiUrl\n\n";

// Test the API call
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

echo "HTTP Code: $httpCode\n";
echo "cURL Error: " . ($curlError ?: 'None') . "\n";
echo "Response Length: " . strlen($response) . " characters\n\n";

if ($curlError) {
    echo "cURL Error Details: $curlError\n\n";
} else {
    echo "API Response:\n";
    echo "=============\n";
    echo $response . "\n\n";
    
    // Try to parse JSON
    $data = json_decode($response, true);
    if ($data) {
        echo "Parsed JSON:\n";
        echo "============\n";
        if ($data['success']) {
            echo "✓ API call successful!\n";
            if (isset($data['data'])) {
                echo "Equipment Name: " . ($data['data']['equipment_name'] ?? 'N/A') . "\n";
                echo "Room: " . ($data['data']['room_name'] ?? 'N/A') . "\n";
                echo "Building: " . ($data['data']['building_name'] ?? 'N/A') . "\n";
                echo "Category: " . ($data['data']['equipment_category'] ?? 'N/A') . "\n";
            }
        } else {
            echo "✗ API call failed: " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "Failed to parse JSON response\n";
    }
}

echo "\n\nTo test with a different unit_id, add ?unit_id=YOUR_ID to the URL\n";
?>