<?php
require '../../auth/middleware.php';
checkAccess(['Department Admin']);

require_once '../../auth/dbh.inc.php';

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Set JSON header
header('Content-Type: application/json');

// Get the admin's department
$admin_id = $_SESSION['user_id'];
$admin_department = $_SESSION['department'] ?? '';

// If not in session, get from database
if (empty($admin_department)) {
    $conn = db();
    $admin_dept_query = "SELECT Department FROM dept_admin WHERE AdminID = ?";
    $admin_dept_stmt = $conn->prepare($admin_dept_query);
    $admin_dept_stmt->bind_param('i', $admin_id);
    $admin_dept_stmt->execute();
    $result = $admin_dept_stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $admin_department = $row['Department'];
        $_SESSION['department'] = $admin_department;
    }
} else {
    $conn = db();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $report_type = $_GET['report_type'] ?? '';
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    
    if (empty($report_type) || empty($start_date) || empty($end_date)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required parameters'
        ]);
        exit;
    }
    
    try {
        // Generate CSV file
        $result = generateCSVExport($conn, $admin_department, $report_type, $start_date, $end_date);
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Export failed: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}

function generateCSVExport($conn, $department, $report_type, $start_date, $end_date) {
    // Create exports directory if it doesn't exist
    $exports_dir = '../../uploads/exports/';
    if (!file_exists($exports_dir)) {
        mkdir($exports_dir, 0755, true);
    }
    
    // Generate filename with timestamp
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "{$report_type}_report_{$timestamp}.csv";
    $filepath = $exports_dir . $filename;
    
    // Open file for writing
    $file = fopen($filepath, 'w');
    if (!$file) {
        throw new Exception('Could not create export file');
    }
    
    // Add UTF-8 BOM for Excel compatibility
    fwrite($file, "\xEF\xBB\xBF");
    
    // Generate report data based on type
    switch ($report_type) {
        case 'booking-requests':
            generateBookingRequestsCSV($conn, $department, $start_date, $end_date, $file);
            break;
        case 'equipment-status':
            generateEquipmentStatusCSV($conn, $department, $start_date, $end_date, $file);
            break;
        default:
            fclose($file);
            unlink($filepath);
            throw new Exception('Invalid report type');
    }
    
    fclose($file);
    
    // Return success response with file URL
    return [
        'status' => 'success',
        'fileUrl' => '../uploads/exports/' . $filename,
        'fileName' => $filename,
        'message' => 'Export completed successfully'
    ];
}


function generateBookingRequestsCSV($conn, $department, $start_date, $end_date, $file) {
    // Write CSV headers
    $headers = [
        'Request ID', 'Requester', 'Activity', 'Purpose', 'Room', 'Building', 
        'Date', 'Time', 'Participants', 'Status', 'Requested On'
    ];
    fputcsv($file, $headers);
    
    // Get booking requests data
    $query = "
        SELECT 
            rr.RequestID,
            rr.ActivityName,
            rr.Purpose,
            rr.NumberOfParticipants,
            rr.Status,
            CONCAT(r.room_name, ' (', r.room_type, ')') as room_details,
            b.building_name,
            CASE 
                WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName, ' (Student)')
                WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName, ' (Teacher)')
                ELSE 'Unknown'
            END as requester_name,
            DATE_FORMAT(rr.ReservationDate, '%M %d, %Y') as formatted_reservation_date,
            CONCAT(TIME_FORMAT(rr.StartTime, '%h:%i %p'), ' - ', TIME_FORMAT(rr.EndTime, '%h:%i %p')) as time_range,
            DATE_FORMAT(rr.RequestDate, '%M %d, %Y %h:%i %p') as formatted_request_date
        FROM room_requests rr
        LEFT JOIN rooms r ON rr.RoomID = r.id
        LEFT JOIN buildings b ON r.building_id = b.id
        LEFT JOIN student s ON rr.StudentID = s.StudentID
        LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
        WHERE rr.ReservationDate BETWEEN ? AND ?
        AND rr.Status IN ('approved', 'rejected', 'completed')
        AND (s.Department = ? OR t.Department = ?)
        ORDER BY rr.ReservationDate DESC, rr.StartTime ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $start_date, $end_date, $department, $department);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $csv_row = [
            'REQ-' . $row['RequestID'],
            $row['requester_name'],
            $row['ActivityName'],
            $row['Purpose'],
            $row['room_details'],
            $row['building_name'],
            $row['formatted_reservation_date'],
            $row['time_range'],
            $row['NumberOfParticipants'],
            ucfirst($row['Status']),
            $row['formatted_request_date']
        ];
        fputcsv($file, $csv_row);
    }
}

function generateEquipmentStatusCSV($conn, $department, $start_date, $end_date, $file) {
    // Department mapping
    $dept_map = [
        'education and arts' => 'Education and Arts',
        'criminal justice' => 'Criminal Justice',
        'accountancy' => 'Accountancy',
        'business administration' => 'Business Administration',
        'hospitality management' => 'Hospitality Management'
    ];
    
    $building_dept = $dept_map[strtolower($department)] ?? $department;
    
    // Write CSV headers
    $headers = [
        'Issue ID', 'Equipment', 'Category', 'Unit ID', 'Location', 'Issue Type', 
        'Description', 'Reporter', 'Status', 'Reported Date', 'Resolved Date'
    ];
    fputcsv($file, $headers);
    
    // Get equipment issues data
    $query = "
        SELECT 
            ei.id as issue_id,
            e.name as equipment_name,
            e.category,
            eu.unit_id,
            ei.issue_type,
            ei.description,
            ei.status,
            COALESCE(r.room_name, 'Unassigned') as room_name,
            COALESCE(b.building_name, 'No Building') as building_name,
            CASE 
                WHEN ei.student_id IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName, ' (Student)')
                WHEN ei.teacher_id IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName, ' (Teacher)')
                ELSE 'Unknown'
            END as reporter_name,
            DATE_FORMAT(ei.reported_at, '%M %d, %Y %h:%i %p') as formatted_reported_date,
            COALESCE(DATE_FORMAT(ei.resolved_at, '%M %d, %Y %h:%i %p'), 'Not Resolved') as formatted_resolved_date
        FROM equipment_issues ei
        JOIN equipment_units eu ON ei.unit_id = eu.unit_id
        JOIN equipment e ON eu.equipment_id = e.id
        LEFT JOIN rooms r ON eu.room_id = r.id
        LEFT JOIN buildings b ON r.building_id = b.id
        LEFT JOIN student s ON ei.student_id = s.StudentID
        LEFT JOIN teacher t ON ei.teacher_id = t.TeacherID
        WHERE ei.reported_at BETWEEN ? AND ?
        AND (
            (ei.student_id IS NOT NULL AND s.Department = ?)
            OR 
            (ei.teacher_id IS NOT NULL AND t.Department = ?)
            OR
            (b.department = ?)
        )
        ORDER BY ei.reported_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssss', $start_date, $end_date, $department, $department, $building_dept);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $csv_row = [
            'ISS-' . $row['issue_id'],
            $row['equipment_name'],
            $row['category'],
            $row['unit_id'],
            $row['room_name'] . ', ' . $row['building_name'],
            $row['issue_type'],
            $row['description'],
            $row['reporter_name'],
            ucfirst(str_replace('_', ' ', $row['status'])),
            $row['formatted_reported_date'],
            $row['formatted_resolved_date']
        ];
        fputcsv($file, $csv_row);
    }
}
?>