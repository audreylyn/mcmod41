<?php
// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to get time ago format
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Create a weeks property
    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    $values = array(
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,
        'd' => $days,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s
    );
    
    $parts = array();
    foreach ($string as $k => $v) {
        if ($values[$k]) {
            $parts[$k] = $values[$k] . ' ' . $v . ($values[$k] > 1 ? 's' : '');
        }
    }

    if (!$full) $parts = array_slice($parts, 0, 1);
    return $parts ? implode(', ', $parts) . ' ago' : 'just now';
}

// Connect to database
db();

// Get department for filtering
$adminDepartment = $_SESSION['department'] ?? '';

// Handle filters
$roomFilter = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
$buildingFilter = isset($_GET['building_id']) ? intval($_GET['building_id']) : 0;
$dateFilter = isset($_GET['date_range']) ? intval($_GET['date_range']) : 30;

// Base query - only show approved status rows
$sql = "SELECT rr.*, r.room_name, r.room_type, b.building_name, 
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
            WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
        END as user_name,
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN s.Department
            WHEN rr.TeacherID IS NOT NULL THEN t.Department
        END as user_department,
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN 'Student'
            WHEN rr.TeacherID IS NOT NULL THEN 'Teacher'
        END as user_role,
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN s.StudentID
            WHEN rr.TeacherID IS NOT NULL THEN t.TeacherID
        END as user_id,
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN s.Email
            WHEN rr.TeacherID IS NOT NULL THEN t.Email
        END as user_email,
        da.FirstName as admin_first_name, 
        da.LastName as admin_last_name
        FROM room_requests rr
        JOIN rooms r ON rr.RoomID = r.id
        JOIN buildings b ON r.building_id = b.id
        LEFT JOIN student s ON rr.StudentID = s.StudentID
        LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
        LEFT JOIN dept_admin da ON rr.approvedBy = da.AdminID
        WHERE rr.Status = 'approved'";

// Add filters
$params = [];
$types = "";

// Department filter
if (!empty($adminDepartment)) {
    $sql .= " AND (s.Department = ? OR t.Department = ?)";
    $params[] = $adminDepartment;
    $params[] = $adminDepartment;
    $types .= "ss";
}

// Room filter
if ($roomFilter > 0) {
    $sql .= " AND rr.RoomID = ?";
    $params[] = $roomFilter;
    $types .= "i";
}

// Building filter
if ($buildingFilter > 0) {
    $sql .= " AND b.id = ?";
    $params[] = $buildingFilter;
    $types .= "i";
}

// Date range filter
if ($dateFilter > 0) {
    $sql .= " AND rr.RequestDate >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $params[] = $dateFilter;
    $types .= "i";
}

// Order by most recent first
$sql .= " ORDER BY rr.RequestDate DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get rooms for filter dropdown
$roomsSql = "SELECT r.id, r.room_name, b.building_name 
             FROM rooms r 
             JOIN buildings b ON r.building_id = b.id 
             ORDER BY b.building_name, r.room_name";
$roomsResult = $conn->query($roomsSql);

// Get buildings for filter dropdown
$buildingsSql = "SELECT id, building_name FROM buildings ORDER BY building_name";
$buildingsResult = $conn->query($buildingsSql);

// Get activity counts - only for approved status
$countSql = "SELECT 
    COUNT(*) as total_count,
    SUM(CASE WHEN ReservationDate > CURDATE() THEN 1 ELSE 0 END) as upcoming_count,
    SUM(CASE WHEN ReservationDate = CURDATE() THEN 1 ELSE 0 END) as active_count,
    SUM(CASE WHEN ReservationDate < CURDATE() THEN 1 ELSE 0 END) as completed_count
    FROM room_requests 
    WHERE Status = 'approved'";

// Add department filter if applicable
if (!empty($adminDepartment)) {
    $countSql .= " AND (EXISTS (SELECT 1 FROM student s WHERE room_requests.StudentID = s.StudentID AND s.Department = ?) 
                   OR EXISTS (SELECT 1 FROM teacher t WHERE room_requests.TeacherID = t.TeacherID AND t.Department = ?))";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("ss", $adminDepartment, $adminDepartment);
} else {
    $countStmt = $conn->prepare($countSql);
}

$countStmt->execute();
$countResult = $countStmt->get_result();
$countData = $countResult->fetch_assoc();