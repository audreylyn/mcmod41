<?php
// Bulk delete functionality only - Clean Expired functionality removed
session_start();
require_once '../../auth/dbh.inc.php';

header('Content-Type: application/json');

// Check if user is logged in and is department admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Department Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$conn = db();
$department = $_SESSION['department'];

// Check database connection
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'preview_bulk_delete':
                $start_date = $_POST['start_date'] ?? '';
                $end_date = $_POST['end_date'] ?? '';
                
                if (empty($start_date) || empty($end_date)) {
                    throw new Exception('Start date and end date are required');
                }
                
                // Validate dates
                if (strtotime($start_date) > strtotime($end_date)) {
                    throw new Exception('Start date cannot be after end date');
                }
                
                // Count records to be deleted using direct query (based on ReservationDate)
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count
                    FROM room_requests rr
                    LEFT JOIN student s ON rr.StudentID = s.StudentID
                    LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
                    WHERE rr.ReservationDate BETWEEN ? AND ?
                    AND (s.Department = ? OR t.Department = ?)
                ");
                
                if (!$stmt) {
                    throw new Exception('Failed to prepare statement: ' . $conn->error);
                }
                
                $stmt->bind_param("ssss", $start_date, $end_date, $department, $department);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to execute query: ' . $stmt->error);
                }
                
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $count = (int)$row['count'];
                $stmt->close();
                
                echo json_encode([
                    'success' => true,
                    'count' => $count,
                    'message' => "Found {$count} room reservation(s) to delete between {$start_date} and {$end_date}"
                ]);
                break;
                
            case 'bulk_delete':
                $start_date = $_POST['start_date'] ?? '';
                $end_date = $_POST['end_date'] ?? '';
                
                if (empty($start_date) || empty($end_date)) {
                    throw new Exception('Start date and end date are required');
                }
                
                // Validate dates
                if (strtotime($start_date) > strtotime($end_date)) {
                    throw new Exception('Start date cannot be after end date');
                }
                
                // Start transaction for safety
                $conn->autocommit(false);
                
                try {
                    // First count the records to be deleted (based on ReservationDate)
                    $stmt = $conn->prepare("
                        SELECT COUNT(*) as count
                        FROM room_requests rr
                        LEFT JOIN student s ON rr.StudentID = s.StudentID
                        LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
                        WHERE rr.ReservationDate BETWEEN ? AND ?
                        AND (s.Department = ? OR t.Department = ?)
                    ");
                    
                    $stmt->bind_param("ssss", $start_date, $end_date, $department, $department);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $deleted_count = (int)$row['count'];
                    $stmt->close();
                    
                    // Delete the records (based on ReservationDate)
                    $stmt = $conn->prepare("
                        DELETE rr FROM room_requests rr
                        LEFT JOIN student s ON rr.StudentID = s.StudentID
                        LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
                        WHERE rr.ReservationDate BETWEEN ? AND ?
                        AND (s.Department = ? OR t.Department = ?)
                    ");
                    
                    $stmt->bind_param("ssss", $start_date, $end_date, $department, $department);
                    if (!$stmt->execute()) {
                        throw new Exception('Failed to delete records: ' . $stmt->error);
                    }
                    $stmt->close();
                    
                    // Commit the transaction
                    $conn->commit();
                    $conn->autocommit(true);
                    
                } catch (Exception $e) {
                    // Rollback on error
                    $conn->rollback();
                    $conn->autocommit(true);
                    throw $e;
                }
                
                echo json_encode([
                    'success' => true,
                    'deleted_count' => $deleted_count,
                    'message' => "Successfully deleted {$deleted_count} room reservation(s)"
                ]);
                break;
                
            case 'delete_expired_pending':
                // This functionality has been removed
                throw new Exception('Clean Expired functionality has been removed');
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

if (isset($conn)) {
    $conn->close();
}
exit();
?>
