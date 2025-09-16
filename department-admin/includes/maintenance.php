<?php
// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set current page for navigation highlighting
$current_page = 'dept_room_maintenance';

$conn = db();

// Get admin ID and department
$admin_id = $_SESSION['user_id'];
$department = $_SESSION['department'] ?? '';

// If department is not set in session, try to get it from database
if (empty($department)) {
    $stmt = $conn->prepare("SELECT Department FROM dept_admin WHERE AdminID = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $department = $row['Department'];
        $_SESSION['department'] = $department;
    }
}

// Handle AJAX requests for status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_status') {
        $room_id = intval($_POST['room_id']);
        $new_status = $_POST['status'];
        $reason = $_POST['reason'] ?? '';
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        
        // Validate status
        $valid_statuses = ['available', 'maintenance'];
        if (!in_array($new_status, $valid_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }
        
        // Check if admin can modify this room (department rooms + gym)
        $stmt = $conn->prepare("
            SELECT r.id, r.room_name, b.department 
            FROM rooms r 
            JOIN buildings b ON r.building_id = b.id 
            WHERE r.id = ? AND (b.department = ? OR r.room_type = 'Gymnasium')
        ");
        $stmt->bind_param("is", $room_id, $department);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized to modify this room']);
            exit;
        }
        
        // Prevent setting maintenance if there are approved future reservations
        if ($new_status === 'maintenance') {
            // Setup date parameters for query
            $checkStart = !empty($start_date) ? $start_date : date('Y-m-d'); // Default to today
            $checkEnd = !empty($end_date) ? $end_date : '9999-12-31'; // Default to far future
            
            $checkSql = "SELECT rr.RequestID, rr.ReservationDate, rr.StartTime, rr.EndTime, rr.ActivityName, rr.StudentID, rr.TeacherID, 
                                COALESCE(CONCAT(s.FirstName, ' ', s.LastName), CONCAT(t.FirstName, ' ', t.LastName)) AS requester_name 
                         FROM room_requests rr 
                         LEFT JOIN student s ON rr.StudentID = s.StudentID 
                         LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID 
                         WHERE rr.RoomID = ? AND rr.Status = 'approved' 
                         AND ((rr.ReservationDate BETWEEN ? AND ?) 
                              OR (? BETWEEN rr.ReservationDate AND rr.ReservationDate))
                         ORDER BY rr.ReservationDate ASC, rr.StartTime ASC";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("isss", $room_id, $checkStart, $checkEnd, $checkStart);
            $checkStmt->execute();
            $checkRes = $checkStmt->get_result();
            if ($checkRes && $checkRes->num_rows > 0) {
                $conflicts = [];
                while ($row = $checkRes->fetch_assoc()) {
                    $displayStart = date('M j, Y g:i A', strtotime($row['ReservationDate'] . ' ' . $row['StartTime']));
                    $displayEnd = date('M j, Y g:i A', strtotime($row['ReservationDate'] . ' ' . $row['EndTime']));
                    $conflicts[] = [
                        'request_id' => $row['RequestID'],
                        'activity' => $row['ActivityName'],
                        'reservation_date' => $row['ReservationDate'],
                        'start' => $row['StartTime'],
                        'end' => $row['EndTime'],
                        'display_start' => $displayStart,
                        'display_end' => $displayEnd,
                        'requester' => $row['requester_name'] ?? 'Unknown',
                        'approval_url' => 'dept_room_approval.php?request_id=' . $row['RequestID']
                    ];
                }
                // Friendly message uses the earliest conflict
                $first = $conflicts[0];
                $message = "Room has approved reservation. Maintenance cannot be set until these reservations are finished.";
                echo json_encode(['success' => false, 'message' => $message, 'conflicts' => $conflicts]);
                exit;
            }
        }

        // Update room status
        $stmt = $conn->prepare("UPDATE rooms SET RoomStatus = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $room_id);
        
        if ($stmt->execute()) {
            // Log maintenance record if setting to maintenance
            if ($new_status === 'maintenance') {
                // If start_date and end_date are provided, use them
                if (!empty($start_date) && !empty($end_date)) {
                    $stmt = $conn->prepare("
                        INSERT INTO room_maintenance (room_id, reason, admin_id, start_date, end_date) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("isiss", $room_id, $reason, $admin_id, $start_date, $end_date);
                } else {
                    // Use default behavior (no end date)
                    $stmt = $conn->prepare("
                        INSERT INTO room_maintenance (room_id, reason, admin_id) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->bind_param("isi", $room_id, $reason, $admin_id);
                }
                $stmt->execute();
            } else if ($new_status === 'available') {
                // Close any open maintenance records
                $stmt = $conn->prepare("
                    UPDATE room_maintenance 
                    SET end_date = CURRENT_TIMESTAMP 
                    WHERE room_id = ? AND end_date IS NULL
                ");
                $stmt->bind_param("i", $room_id);
                $stmt->execute();
            }
            
            echo json_encode(['success' => true, 'message' => 'Room status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update room status']);
        }
        exit;
    }
}

// Get rooms for the current department + gym
$rooms_query = "
    SELECT 
        r.id,
        r.room_name,
        r.room_type,
        r.capacity,
        r.RoomStatus,
        b.building_name,
        b.department,
        rm.reason as maintenance_reason,
        rm.start_date as maintenance_start,
        rm.end_date as maintenance_end,
        CONCAT(da.FirstName, ' ', da.LastName) as maintenance_admin
    FROM rooms r
    JOIN buildings b ON r.building_id = b.id
    LEFT JOIN room_maintenance rm ON r.id = rm.room_id AND (rm.end_date IS NULL OR rm.end_date >= CURDATE())
    LEFT JOIN dept_admin da ON rm.admin_id = da.AdminID
    WHERE b.department = ? OR r.room_type = 'Gymnasium'
    ORDER BY b.building_name, r.room_name
";

$stmt = $conn->prepare($rooms_query);
$stmt->bind_param("s", $department);
$stmt->execute();
$rooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get room statistics
$stats_query = "
    SELECT 
        RoomStatus,
        COUNT(*) as count
    FROM rooms r
    JOIN buildings b ON r.building_id = b.id
    WHERE b.department = ? OR r.room_type = 'Gymnasium'
    GROUP BY RoomStatus
";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("s", $department);
$stmt->execute();
$room_stats = [];
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $room_stats[$row['RoomStatus']] = $row['count'];
}

// Get maintenance history
$history_query = "
    SELECT 
        r.room_name,
        b.building_name,
        rm.reason,
        rm.start_date,
        rm.end_date,
        CONCAT(da.FirstName, ' ', da.LastName) as admin_name
    FROM room_maintenance rm
    JOIN rooms r ON rm.room_id = r.id
    JOIN buildings b ON r.building_id = b.id
    JOIN dept_admin da ON rm.admin_id = da.AdminID
    WHERE b.department = ? OR r.room_type = 'Gymnasium'
    ORDER BY rm.start_date DESC
    LIMIT 10
";

$stmt = $conn->prepare($history_query);
$stmt->bind_param("s", $department);
$stmt->execute();
$maintenance_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);