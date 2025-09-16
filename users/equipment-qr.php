<?php
/**
 * Simplified Equipment QR Code Redirect Handler
 * Handles QR code scans and redirects to equipment reporting page
 * 
 * This script replaces the complex redirect-equipment-report.php with
 * a simpler, more reliable approach using only equipment ID.
 */

// Add ngrok compatibility and CORS headers
header('ngrok-skip-browser-warning: true');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');

// Start session management
session_start();

/**
 * Enhanced logging function for debugging QR redirects
 */
function logQrRedirect($level, $message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $sessionId = session_id();
    $userInfo = [
        'user_id' => $_SESSION['user_id'] ?? 'guest',
        'role' => $_SESSION['role'] ?? 'none'
    ];
    
    $logMessage = "[$timestamp] QR-REDIRECT [$level] [Session: $sessionId]: $message";
    
    if ($data) {
        $logMessage .= " | Data: " . json_encode($data);
    }
    
    $logMessage .= " | User: " . json_encode($userInfo);
    
    error_log($logMessage);
}

/**
 * Get the current base URL for redirects
 */
function getBaseUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
                ? 'https://' : 'http://';
    
    return $protocol . $_SERVER['HTTP_HOST'];
}

/**
 * Validate equipment ID parameter
 */
function validateEquipmentId($id) {
    if (empty($id)) {
        return false;
    }
    
    // Check if it's a valid positive integer
    if (!ctype_digit($id) || (int)$id <= 0) {
        return false;
    }
    
    return (int)$id;
}

/**
 * Redirect with error message
 */
function redirectWithError($errorCode, $message, $equipmentId = null) {
    // Use more reliable URL construction
    $protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
    $host = $_SERVER['HTTP_HOST'];
    $loginUrl = $protocol . '://' . $host . '/mcmod41/index.php';
    
    $params = [
        'error' => $errorCode,
        'message' => urlencode($message)
    ];
    
    if ($equipmentId) {
        $params['equipment_id'] = $equipmentId;
    }
    
    $url = $loginUrl . '?' . http_build_query($params);
    
    logQrRedirect('ERROR', "Redirecting with error: $message", [
        'error_code' => $errorCode,
        'redirect_url' => $url,
        'equipment_id' => $equipmentId,
        'protocol' => $protocol,
        'host' => $host
    ]);
    
    header('Location: ' . $url);
    exit();
}

/**
 * Redirect to equipment report page
 */
function redirectToReport($equipmentId) {
    // Use reliable URL construction 
    $protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
    $host = $_SERVER['HTTP_HOST'];
    $reportUrl = $protocol . '://' . $host . '/mcmod41/users/report-equipment-issue.php?id=' . $equipmentId;
    
    logQrRedirect('SUCCESS', "Redirecting to equipment report page", [
        'equipment_id' => $equipmentId,
        'redirect_url' => $reportUrl,
        'protocol' => $protocol,
        'host' => $host
    ]);
    
    header('Location: ' . $reportUrl);
    exit();
}

// Main execution starts here
try {
    // Log the incoming request
    logQrRedirect('INFO', 'QR redirect request received', [
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'query_string' => $_SERVER['QUERY_STRING'] ?? 'none',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'referer' => $_SERVER['HTTP_REFERER'] ?? 'none'
    ]);
    
    // Get and validate equipment ID parameter
    $equipmentId = $_GET['id'] ?? '';
    $validatedId = validateEquipmentId($equipmentId);
    
    if (!$validatedId) {
        logQrRedirect('ERROR', 'Invalid or missing equipment ID', ['provided_id' => $equipmentId]);
        redirectWithError('invalid_equipment_id', 'Invalid or missing equipment ID in QR code');
    }
    
    // Check user authentication and authorization
    if (!isset($_SESSION['user_id'])) {
        logQrRedirect('WARN', 'User not authenticated, redirecting to login', ['equipment_id' => $validatedId]);
        redirectWithError('not_authenticated', 'Please log in to report equipment issues', $validatedId);
    }
    
    // Check user role authorization
    $allowedRoles = ['Student', 'Teacher'];
    if (!in_array($_SESSION['role'] ?? '', $allowedRoles)) {
        logQrRedirect('WARN', 'User role not authorized', [
            'equipment_id' => $validatedId,
            'user_role' => $_SESSION['role'] ?? 'none'
        ]);
        redirectWithError('unauthorized_role', 'Your account role is not authorized to report equipment issues', $validatedId);
    }
    
    // Verify equipment exists by making a quick API call
    // Use relative path to avoid domain/SSL issues
    $apiUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/mcmod41/users/api/get_equipment_details.php?unit_id=' . $validatedId;
    
    logQrRedirect('INFO', 'Checking equipment existence via API', [
        'equipment_id' => $validatedId,
        'api_url' => $apiUrl
    ]);
    
    // Use cURL to check if equipment exists
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development environments
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        logQrRedirect('ERROR', 'Failed to verify equipment existence - cURL error', [
            'equipment_id' => $validatedId,
            'curl_error' => $curlError,
            'api_url' => $apiUrl
        ]);
        // Continue anyway - don't block user for API issues
        logQrRedirect('WARN', 'Proceeding despite API error', ['equipment_id' => $validatedId]);
    } else {
        $apiResponse = json_decode($response, true);
        
        logQrRedirect('INFO', 'API response received', [
            'equipment_id' => $validatedId,
            'http_code' => $httpCode,
            'response_length' => strlen($response),
            'api_success' => $apiResponse['success'] ?? 'unknown'
        ]);
        
        if ($httpCode === 404 || (!$apiResponse || !$apiResponse['success'])) {
            logQrRedirect('ERROR', 'Equipment not found in database', [
                'equipment_id' => $validatedId,
                'api_response' => $apiResponse,
                'http_code' => $httpCode,
                'raw_response' => substr($response, 0, 500) // Log first 500 chars
            ]);
            redirectWithError('equipment_not_found', 'The scanned equipment was not found in the system', $validatedId);
        } else {
            logQrRedirect('SUCCESS', 'Equipment verified successfully', [
                'equipment_id' => $validatedId,
                'equipment_name' => $apiResponse['data']['equipment_name'] ?? 'unknown'
            ]);
        }
    }
    
    // All checks passed - redirect to report page
    redirectToReport($validatedId);
    
} catch (Exception $e) {
    logQrRedirect('FATAL', 'Unexpected exception in QR redirect', [
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'equipment_id' => $equipmentId ?? 'unknown'
    ]);
    
    redirectWithError('system_error', 'A system error occurred while processing the QR code');
}
?>