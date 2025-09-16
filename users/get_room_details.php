<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

require_once '../auth/dbh.inc.php';
$conn = db();

// Validate input
if (!isset($_GET['room_id']) || !is_numeric($_GET['room_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit;
}

$roomId = (int)$_GET['room_id'];

try {
    // Get room data with building information
    $stmt = $conn->prepare("
        SELECT r.*, b.building_name 
        FROM rooms r
        JOIN buildings b ON r.building_id = b.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $roomResult = $stmt->get_result();

    if ($roomResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit;
    }

    $room = $roomResult->fetch_assoc();

    // Get equipment for this room
    $equipment = [];
    $equipmentStmt = $conn->prepare("
        SELECT 
            e.name, 
            e.description, 
            COUNT(eu.unit_id) as quantity
        FROM equipment_units eu
        JOIN equipment e ON eu.equipment_id = e.id
        WHERE eu.room_id = ?
        GROUP BY e.id, e.name, e.description
    ");
    $equipmentStmt->bind_param("i", $roomId);
    $equipmentStmt->execute();
    $equipmentResult = $equipmentStmt->get_result();

    while ($equipmentRow = $equipmentResult->fetch_assoc()) {
        $equipment[] = $equipmentRow;
    }

    // Check if room is currently occupied but will be available later
    $availableTime = null;
    if ($room['RoomStatus'] === 'occupied') {
        $availabilityStmt = $conn->prepare("
            SELECT MIN(EndTime) as next_available 
            FROM room_requests 
            WHERE RoomID = ? AND Status = 'approved' AND EndTime > NOW()
        ");
        $availabilityStmt->bind_param("i", $roomId);
        $availabilityStmt->execute();
        $availabilityResult = $availabilityStmt->get_result();

        if ($availabilityRow = $availabilityResult->fetch_assoc()) {
            if ($availabilityRow['next_available']) {
                $availableTime = "Available after " . date('M d, Y h:i A', strtotime($availabilityRow['next_available']));
            }
        }
    }

    // Get maintenance information if room is under maintenance
    $maintenanceInfo = null;
    if ($room['RoomStatus'] === 'maintenance') {
        $maintenanceStmt = $conn->prepare("
            SELECT 
                rm.reason,
                rm.start_date,
                rm.end_date,
                CONCAT(da.FirstName, ' ', da.LastName) as admin_name
            FROM room_maintenance rm
            JOIN dept_admin da ON rm.admin_id = da.AdminID
            WHERE rm.room_id = ? AND (rm.end_date IS NULL OR rm.end_date >= CURDATE())
            ORDER BY rm.start_date DESC
            LIMIT 1
        ");
        $maintenanceStmt->bind_param("i", $roomId);
        $maintenanceStmt->execute();
        $maintenanceResult = $maintenanceStmt->get_result();

        if ($maintenanceRow = $maintenanceResult->fetch_assoc()) {
            $maintenanceInfo = [
                'reason' => $maintenanceRow['reason'],
                'start_date' => $maintenanceRow['start_date'],
                'end_date' => $maintenanceRow['end_date'],
                'admin_name' => $maintenanceRow['admin_name'],
                'formatted_start_date' => date('M d, Y', strtotime($maintenanceRow['start_date'])),
                'formatted_end_date' => $maintenanceRow['end_date'] ? date('M d, Y', strtotime($maintenanceRow['end_date'])) : 'Ongoing'
            ];
        }
    }

    // Return JSON response
    echo json_encode([
        'success' => true,
        'room' => $room,
        'equipment' => $equipment,
        'availableTime' => $availableTime,
        'maintenanceInfo' => $maintenanceInfo
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}

