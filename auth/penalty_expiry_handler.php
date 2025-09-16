<?php
require_once 'dbh.inc.php';

/**
 * Automatic penalty expiry handler
 * This script should be run periodically (e.g., via cron job) to check for expired penalties
 */
function updateExpiredPenalties($closeConnection = true) {
    $conn = db();
    
    try {
        $conn->begin_transaction();
        
        // Find all active penalties that have expired
        $expiredPenaltiesStmt = $conn->prepare("
            SELECT p.id, p.student_id 
            FROM penalty p
            WHERE p.status = 'active' 
            AND p.expires_at IS NOT NULL 
            AND p.expires_at <= NOW()
        ");
        $expiredPenaltiesStmt->execute();
        $expiredResult = $expiredPenaltiesStmt->get_result();
        
        $expiredPenalties = [];
        while ($row = $expiredResult->fetch_assoc()) {
            $expiredPenalties[] = $row;
        }
        
        if (!empty($expiredPenalties)) {
            // Update penalty status to expired
            $penaltyIds = array_column($expiredPenalties, 'id');
            $placeholders = str_repeat('?,', count($penaltyIds) - 1) . '?';
            
            $updatePenaltiesStmt = $conn->prepare("
                UPDATE penalty 
                SET status = 'expired' 
                WHERE id IN ($placeholders)
            ");
            $updatePenaltiesStmt->bind_param(str_repeat('i', count($penaltyIds)), ...$penaltyIds);
            $updatePenaltiesStmt->execute();
            
            // Update student status to active for students with no other active penalties
            foreach ($expiredPenalties as $penalty) {
                $studentId = $penalty['student_id'];
                
                // Check if student has any other active penalties
                $activePenaltyCheckStmt = $conn->prepare("
                    SELECT COUNT(*) as active_count 
                    FROM penalty 
                    WHERE student_id = ? AND status = 'active'
                ");
                $activePenaltyCheckStmt->bind_param("i", $studentId);
                $activePenaltyCheckStmt->execute();
                $activeResult = $activePenaltyCheckStmt->get_result();
                $activeCount = $activeResult->fetch_assoc()['active_count'];
                
                // If no active penalties, update student status
                if ($activeCount == 0) {
                    $updateStudentStmt = $conn->prepare("
                        UPDATE student 
                        SET PenaltyStatus = 'active', PenaltyExpiresAt = NULL 
                        WHERE StudentID = ?
                    ");
                    $updateStudentStmt->bind_param("i", $studentId);
                    $updateStudentStmt->execute();
                }
                
                // Log audit trail for expiry
                $auditStmt = $conn->prepare("
                    INSERT INTO penalty_audit (penalty_id, action, details) 
                    VALUES (?, 'expired', 'Penalty automatically expired')
                ");
                $auditStmt->bind_param("i", $penalty['id']);
                $auditStmt->execute();
            }
        }
        
        $conn->commit();
        return count($expiredPenalties);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Penalty expiry handler error: " . $e->getMessage());
        return false;
    } finally {
        if ($closeConnection) {
            $conn->close();
        }
    }
}

// If called directly (for testing or manual execution)
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $expiredCount = updateExpiredPenalties();
    if ($expiredCount !== false) {
        echo "Updated $expiredCount expired penalties\n";
    } else {
        echo "Error updating expired penalties\n";
    }
}
?>
