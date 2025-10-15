<?php
// middleware.php - Authentication and authorization middleware

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/dbh.inc.php';
require_once __DIR__ . '/../middleware/session_manager.php';

// Check for expired penalties and room maintenance periodically (every 10th request to avoid overhead)
if (rand(1, 10) === 1) {
    require_once __DIR__ . '/penalty_expiry_handler.php';
    updateExpiredPenalties(false); // Don't close connection when called from middleware
    
    require_once __DIR__ . '/room_maintenance_expiry_handler.php';
    updateExpiredMaintenance(false); // Don't close connection when called from middleware
}

$sessionManager = new SessionManager();

function checkAccess($allowedRoles)
{
    global $sessionManager;
    if (!$sessionManager->validateSession()) {
        // validateSession now handles the redirect for timeout
        exit();
    }

    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        // Redirect to a generic access denied page or the login page
        header("Location: ../index.php?error=denied");
        exit();
    }
}
