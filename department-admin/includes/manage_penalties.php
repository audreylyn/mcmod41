<?php
// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

$conn = db();

// Get current admin info
$adminId = $_SESSION['user_id'] ?? null;
$adminDept = $_SESSION['department'] ?? '';

// If department is not set in session, try to get it from database
if (empty($adminDept) && $adminId) {
    $deptStmt = $conn->prepare("SELECT Department FROM dept_admin WHERE AdminID = ?");
    $deptStmt->bind_param("i", $adminId);
    $deptStmt->execute();
    $deptResult = $deptStmt->get_result();
    if ($deptRow = $deptResult->fetch_assoc()) {
        $adminDept = $deptRow['Department'];
        $_SESSION['department'] = $adminDept;
    }
}

// Validate session data
if (!$adminId || !$adminDept) {
    $_SESSION['error_message'] = 'Session data incomplete. Please log in again.';
    header('Location: ../auth/login.php');
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ban_student') {
        $studentId = (int)$_POST['student_id'];
        $reason = trim($_POST['reason']);
        $description = trim($_POST['description']);
        $expiryDate = $_POST['expiry_date'] ? $_POST['expiry_date'] : null;
        
        try {
            $conn->begin_transaction();
            
            // Check if student is already banned
            $checkStmt = $conn->prepare("
                SELECT COUNT(*) as count FROM penalty 
                WHERE student_id = ? AND status = 'active' AND penalty_type = 'ban'
            ");
            $checkStmt->bind_param("i", $studentId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existingBan = $checkResult->fetch_assoc();
            
            if ($existingBan['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Student is already banned']);
                exit;
            }
            
            // Insert penalty record
            $stmt = $conn->prepare("
                INSERT INTO penalty (student_id, reason, descriptions, penalty_type, issued_by, expires_at) 
                VALUES (?, ?, ?, 'ban', ?, ?)
            ");
            $stmt->bind_param("issss", $studentId, $reason, $description, $adminId, $expiryDate);
            $stmt->execute();
            $penaltyId = $conn->insert_id;
            
            // Update student status
            $updateStmt = $conn->prepare("
                UPDATE student SET PenaltyStatus = 'banned', PenaltyExpiresAt = ? WHERE StudentID = ?
            ");
            $updateStmt->bind_param("si", $expiryDate, $studentId);
            $updateStmt->execute();
            
            // Log audit trail
            $auditStmt = $conn->prepare("
                INSERT INTO penalty_audit (penalty_id, action, performed_by, details) 
                VALUES (?, 'issued', ?, ?)
            ");
            $auditDetails = "Ban issued: $reason";
            $auditStmt->bind_param("iis", $penaltyId, $adminId, $auditDetails);
            $auditStmt->execute();
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Student banned successfully']);
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($action === 'unban_student') {
        $studentId = (int)$_POST['student_id'];
        $reason = trim($_POST['reason']);
        
        try {
            $conn->begin_transaction();
            
            // Get current active penalty
            $penaltyStmt = $conn->prepare("
                SELECT id FROM penalty 
                WHERE student_id = ? AND status = 'active' 
                ORDER BY issued_at DESC LIMIT 1
            ");
            $penaltyStmt->bind_param("i", $studentId);
            $penaltyStmt->execute();
            $penaltyResult = $penaltyStmt->get_result();
            
            if ($penaltyRow = $penaltyResult->fetch_assoc()) {
                $penaltyId = $penaltyRow['id'];
                
                // Update penalty status
                $updatePenaltyStmt = $conn->prepare("
                    UPDATE penalty SET status = 'revoked', revoked_at = NOW(), 
                    revoked_by = ?, revoke_reason = ? WHERE id = ?
                ");
                $updatePenaltyStmt->bind_param("isi", $adminId, $reason, $penaltyId);
                $updatePenaltyStmt->execute();
                
                // Log audit trail
                $auditStmt = $conn->prepare("
                    INSERT INTO penalty_audit (penalty_id, action, performed_by, details) 
                    VALUES (?, 'revoked', ?, ?)
                ");
                $auditDetails = "Ban revoked: $reason";
                $auditStmt->bind_param("iis", $penaltyId, $adminId, $auditDetails);
                $auditStmt->execute();
            }
            
            // Update student status
            $updateStudentStmt = $conn->prepare("
                UPDATE student SET PenaltyStatus = 'active', PenaltyExpiresAt = NULL WHERE StudentID = ?
            ");
            $updateStudentStmt->bind_param("i", $studentId);
            $updateStudentStmt->execute();
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Student unbanned successfully']);
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Check and update expired penalties before displaying
require_once __DIR__ . '/../../auth/penalty_expiry_handler.php';
updateExpiredPenalties(false);

// Get students in the same department
$studentsStmt = $conn->prepare("
    SELECT s.*, 
           p.reason as penalty_reason,
           p.descriptions as penalty_description,
           p.issued_at as penalty_issued,
           p.expires_at as penalty_expires,
           CONCAT(da.FirstName, ' ', da.LastName) as issued_by_name
    FROM student s
    LEFT JOIN penalty p ON s.StudentID = p.student_id AND p.status = 'active'
    LEFT JOIN dept_admin da ON p.issued_by = da.AdminID
    WHERE s.Department = ?
    ORDER BY s.LastName, s.FirstName
");
$studentsStmt->bind_param("s", $adminDept);
$studentsStmt->execute();
$studentsResult = $studentsStmt->get_result();