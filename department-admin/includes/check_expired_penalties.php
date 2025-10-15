<?php
require '../../auth/middleware.php';
checkAccess(['Department Admin']);

require_once __DIR__ . '/../../auth/penalty_expiry_handler.php';

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

try {
    $expiredCount = updateExpiredPenalties();
    
    if ($expiredCount !== false) {
        echo json_encode([
            'success' => true,
            'message' => "Checked for expired penalties. Updated {$expiredCount} expired penalties.",
            'expired_count' => $expiredCount
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error checking expired penalties'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
