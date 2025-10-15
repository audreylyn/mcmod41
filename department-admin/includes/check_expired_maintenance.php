<?php
require '../../auth/middleware.php';
checkAccess(['Department Admin']);

require_once '../../auth/room_maintenance_expiry_handler.php';

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

try {
    $expiredCount = updateExpiredMaintenance();
    
    if ($expiredCount !== false) {
        echo json_encode([
            'success' => true,
            'message' => "Checked for expired maintenance periods. Updated {$expiredCount} rooms back to available status.",
            'expired_count' => $expiredCount
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error checking expired maintenance periods'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
