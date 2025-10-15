<?php
require '../../auth/middleware.php';
checkAccess(['Department Admin']);

require_once '../../auth/dbh.inc.php';

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Set proper headers
header('Content-Type: application/json');

$conn = db();

// Validate input
if (!isset($_GET['student_id']) || !is_numeric($_GET['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
    exit;
}

$studentId = (int)$_GET['student_id'];
$adminDept = $_SESSION['department'];

try {
    // Verify student is in the same department
    $deptCheckStmt = $conn->prepare("SELECT Department FROM student WHERE StudentID = ?");
    $deptCheckStmt->bind_param("i", $studentId);
    $deptCheckStmt->execute();
    $deptResult = $deptCheckStmt->get_result();
    
    if ($deptResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    
    $studentDept = $deptResult->fetch_assoc()['Department'];
    if ($studentDept !== $adminDept) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    // Get penalty history
    $historyStmt = $conn->prepare("
        SELECT p.*,
               CONCAT(issued_admin.FirstName, ' ', issued_admin.LastName) as issued_by_name,
               CONCAT(revoked_admin.FirstName, ' ', revoked_admin.LastName) as revoked_by_name
        FROM penalty p
        LEFT JOIN dept_admin issued_admin ON p.issued_by = issued_admin.AdminID
        LEFT JOIN dept_admin revoked_admin ON p.revoked_by = revoked_admin.AdminID
        WHERE p.student_id = ?
        ORDER BY p.issued_at DESC
    ");
    $historyStmt->bind_param("i", $studentId);
    $historyStmt->execute();
    $historyResult = $historyStmt->get_result();
    
    $penalties = [];
    while ($penalty = $historyResult->fetch_assoc()) {
        // Convert UTC timestamps to Philippines timezone
        $issued_date = new DateTime($penalty['issued_at'], new DateTimeZone('UTC'));
        $issued_date->setTimezone(new DateTimeZone('Asia/Manila'));
        $penalty['issued_at_formatted'] = $issued_date->format('M d, Y h:i A');
        
        $penalty['expires_at_formatted'] = null;
        if ($penalty['expires_at']) {
            $expires_date = new DateTime($penalty['expires_at'], new DateTimeZone('UTC'));
            $expires_date->setTimezone(new DateTimeZone('Asia/Manila'));
            $penalty['expires_at_formatted'] = $expires_date->format('M d, Y h:i A');
        }
        
        $penalty['revoked_at_formatted'] = null;
        if ($penalty['revoked_at']) {
            $revoked_date = new DateTime($penalty['revoked_at'], new DateTimeZone('UTC'));
            $revoked_date->setTimezone(new DateTimeZone('Asia/Manila'));
            $penalty['revoked_at_formatted'] = $revoked_date->format('M d, Y h:i A');
        }
        
        $penalties[] = $penalty;
    }
    
    echo json_encode([
        'success' => true,
        'penalties' => $penalties
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
