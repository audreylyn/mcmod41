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

    // Get occupation information if room is currently occupied
    $occupationInfo = null;
    if ($room['RoomStatus'] === 'occupied') {
        // Get the current/next approved reservation
        $occupationStmt = $conn->prepare("
            SELECT 
                rr.ActivityName,
                rr.ReservationDate,
                rr.StartTime,
                rr.EndTime,
                CASE 
                    WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                    WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
                END as RequesterName,
                CASE 
                    WHEN rr.StudentID IS NOT NULL THEN 'Student'
                    WHEN rr.TeacherID IS NOT NULL THEN 'Teacher'
                END as RequesterType
            FROM room_requests rr
            LEFT JOIN student s ON rr.StudentID = s.StudentID
            LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
            WHERE rr.RoomID = ? 
            AND rr.Status = 'approved'
            AND rr.ReservationDate >= CURDATE()
            ORDER BY rr.ReservationDate ASC, rr.StartTime ASC
            LIMIT 1
        ");
        $occupationStmt->bind_param("i", $roomId);
        $occupationStmt->execute();
        $occupationResult = $occupationStmt->get_result();

        if ($occupationRow = $occupationResult->fetch_assoc()) {
            $occupationInfo = [
                'activity_name' => $occupationRow['ActivityName'],
                'requester_name' => $occupationRow['RequesterName'],
                'requester_type' => $occupationRow['RequesterType'],
                'reservation_date' => $occupationRow['ReservationDate'],
                'start_time' => $occupationRow['StartTime'],
                'end_time' => $occupationRow['EndTime'],
                'formatted_date' => date('M d, Y', strtotime($occupationRow['ReservationDate'])),
                'formatted_start_time' => date('g:i A', strtotime($occupationRow['StartTime'])),
                'formatted_end_time' => date('g:i A', strtotime($occupationRow['EndTime']))
            ];
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
        'occupationInfo' => $occupationInfo,
        'maintenanceInfo' => $maintenanceInfo
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}

