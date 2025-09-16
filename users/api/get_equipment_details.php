<?php
/**
 * Equipment Details API Endpoint
 * Returns equipment details by unit ID for QR code system
 */

// Add CORS and ngrok compatibility headers
header('Content-Type: application/json');
header('ngrok-skip-browser-warning: true');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database connection
require_once '../../auth/dbh.inc.php';

/**
 * Log API requests for debugging
 */
function logApiRequest($message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] Equipment API: $message";
    if ($data) {
        $logMessage .= " | Data: " . json_encode($data);
    }
    error_log($logMessage);
}

/**
 * Send JSON response
 */
function sendResponse($success, $data = null, $message = '', $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('c')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit();
}

/**
 * Validate and sanitize equipment unit ID
 */
function validateUnitId($unitId) {
    if (empty($unitId)) {
        return false;
    }
    
    // Check if it's a valid integer
    if (!ctype_digit($unitId) && !is_int($unitId)) {
        return false;
    }
    
    return (int)$unitId;
}

// Main API logic
try {
    // Log the incoming request
    logApiRequest("API request received", [
        'method' => $_SERVER['REQUEST_METHOD'],
        'query_params' => $_GET,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
    
    // Only allow GET requests for this endpoint
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, null, 'Only GET requests are allowed', 405);
    }
    
    // Get and validate unit_id parameter
    $unitId = $_GET['unit_id'] ?? '';
    $validatedUnitId = validateUnitId($unitId);
    
    if (!$validatedUnitId) {
        logApiRequest("Invalid unit_id provided", ['unit_id' => $unitId]);
        sendResponse(false, null, 'Invalid or missing unit_id parameter', 400);
    }
    
    // Prepare SQL query to fetch equipment details
    $sql = "SELECT 
                eu.unit_id,
                eu.serial_number,
                e.id as equipment_id,
                e.name as equipment_name,
                e.description as equipment_description,
                e.category as equipment_category,
                r.id as room_id,
                r.room_name,
                b.id as building_id,
                b.building_name,
                b.department
            FROM equipment_units eu
            JOIN equipment e ON eu.equipment_id = e.id
            JOIN rooms r ON eu.room_id = r.id
            JOIN buildings b ON r.building_id = b.id
            WHERE eu.unit_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        logApiRequest("Database prepare failed", ['error' => $conn->error]);
        sendResponse(false, null, 'Database error occurred', 500);
    }
    
    $stmt->bind_param('i', $validatedUnitId);
    
    if (!$stmt->execute()) {
        logApiRequest("Database execute failed", ['error' => $stmt->error]);
        sendResponse(false, null, 'Database query failed', 500);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        logApiRequest("Equipment unit not found", ['unit_id' => $validatedUnitId]);
        sendResponse(false, null, 'Equipment unit not found', 404);
    }
    
    $equipment = $result->fetch_assoc();
    
    // Format the response data
    $responseData = [
        'unit_id' => (int)$equipment['unit_id'],
        'equipment_id' => (int)$equipment['equipment_id'],
        'equipment_name' => $equipment['equipment_name'],
        'equipment_description' => $equipment['equipment_description'],
        'equipment_category' => $equipment['equipment_category'],
        'serial_number' => $equipment['serial_number'],
        'room_id' => (int)$equipment['room_id'],
        'room_name' => $equipment['room_name'],
        'building_id' => (int)$equipment['building_id'],
        'building_name' => $equipment['building_name'],
        'department' => $equipment['department'],
        'location' => $equipment['room_name'] . ', ' . $equipment['building_name']
    ];
    
    logApiRequest("Equipment details retrieved successfully", ['unit_id' => $validatedUnitId]);
    sendResponse(true, $responseData, 'Equipment details retrieved successfully');
    
} catch (Exception $e) {
    logApiRequest("Unexpected error", ['error' => $e->getMessage()]);
    sendResponse(false, null, 'An unexpected error occurred', 500);
} finally {
    // Close database connection
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>