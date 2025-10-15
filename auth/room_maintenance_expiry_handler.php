<?php
require_once 'dbh.inc.php';

/**
 * Automatic room maintenance expiry handler
 * This script should be run periodically to check for expired maintenance periods
 */
function updateExpiredMaintenance($closeConnection = true) {
    $conn = db();
    
    try {
        $conn->begin_transaction();
        
        // Find all rooms with expired maintenance periods
        $expiredMaintenanceStmt = $conn->prepare("
            SELECT DISTINCT r.id as room_id, r.room_name, r.RoomStatus, 
                   rm.id as maintenance_id, rm.end_date, rm.reason,
                   b.building_name
            FROM rooms r
            JOIN buildings b ON r.building_id = b.id
            JOIN room_maintenance rm ON r.id = rm.room_id
            WHERE r.RoomStatus = 'maintenance' 
            AND rm.end_date IS NOT NULL 
            AND rm.end_date < CURDATE()
            AND rm.id IN (
                SELECT MAX(id) 
                FROM room_maintenance 
                WHERE room_id = r.id
            )
        ");
        $expiredMaintenanceStmt->execute();
        $expiredResult = $expiredMaintenanceStmt->get_result();
        
        $expiredRooms = [];
        while ($row = $expiredResult->fetch_assoc()) {
            $expiredRooms[] = $row;
        }
        
        if (!empty($expiredRooms)) {
            // Update room status back to available
            $roomIds = array_column($expiredRooms, 'room_id');
            $placeholders = str_repeat('?,', count($roomIds) - 1) . '?';
            
            $updateRoomsStmt = $conn->prepare("
                UPDATE rooms 
                SET RoomStatus = 'available' 
                WHERE id IN ($placeholders)
            ");
            $updateRoomsStmt->bind_param(str_repeat('i', count($roomIds)), ...$roomIds);
            $updateRoomsStmt->execute();
            
            // Log audit trail for each expired maintenance
            foreach ($expiredRooms as $room) {
                // Insert maintenance completion record
                $auditStmt = $conn->prepare("
                    INSERT INTO room_maintenance (room_id, admin_id, reason, start_date, end_date) 
                    VALUES (?, NULL, ?, ?, ?)
                ");
                $auditReason = "Maintenance period automatically completed - " . $room['reason'];
                $auditStmt->bind_param("isss", 
                    $room['room_id'], 
                    $auditReason, 
                    $room['end_date'], 
                    $room['end_date']
                );
                $auditStmt->execute();
                
                // Log the automatic status change
                error_log("Room maintenance auto-expired: {$room['room_name']} ({$room['building_name']}) - Status changed to available");
            }
        }
        
        $conn->commit();
        return count($expiredRooms);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Room maintenance expiry handler error: " . $e->getMessage());
        return false;
    } finally {
        if ($closeConnection) {
            $conn->close();
        }
    }
}

// If called directly (for testing or manual execution)
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $expiredCount = updateExpiredMaintenance();
    if ($expiredCount !== false) {
        echo "Updated $expiredCount expired room maintenance periods\n";
    } else {
        echo "Error updating expired room maintenance periods\n";
    }
}
?>
