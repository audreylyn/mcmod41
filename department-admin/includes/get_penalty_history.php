<?php
require '../../auth/middleware.php';
checkAccess(['Department Admin']);

require_once '../../auth/dbh.inc.php';

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
        $penalty['issued_at_formatted'] = date('M d, Y h:i A', strtotime($penalty['issued_at']));
        $penalty['expires_at_formatted'] = $penalty['expires_at'] ? 
            date('M d, Y h:i A', strtotime($penalty['expires_at'])) : null;
        $penalty['revoked_at_formatted'] = $penalty['revoked_at'] ? 
            date('M d, Y h:i A', strtotime($penalty['revoked_at'])) : null;
        
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
