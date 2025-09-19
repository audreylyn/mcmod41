<?php
// Get user role and ID from session
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Determine if this user should be restricted to their department
$isRestrictedUser = in_array($userRole, ['Student', 'Teacher']);

// Variable to store student ban status
$isStudentBanned = false;

// Get DB connection and user's department/buildings when user is restricted
if ($isRestrictedUser) {
    require_once '../auth/dbh.inc.php';
    $conn = db();

    $user_department = '';
    if ($userRole === 'Student') {
        $stmt = $conn->prepare("SELECT Department, PenaltyStatus FROM student WHERE StudentID = ?");
    } else {
        $stmt = $conn->prepare("SELECT Department FROM teacher WHERE TeacherID = ?");
    }

    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $user_department = $row['Department'];
            
            // If it's a student, check their penalty status
            if ($userRole === 'Student' && isset($row['PenaltyStatus'])) {
                $isStudentBanned = ($row['PenaltyStatus'] === 'banned');
            }
        }
        $stmt->close();
    }

    // Get building IDs for this department
    $user_department_buildings = [];
    if (!empty($user_department)) {
        $bstmt = $conn->prepare("SELECT id FROM buildings WHERE department = ?");
        if ($bstmt) {
            $bstmt->bind_param("s", $user_department);
            $bstmt->execute();
            $bres = $bstmt->get_result();
            while ($brow = $bres->fetch_assoc()) {
                $user_department_buildings[] = $brow['id'];
            }
            $bstmt->close();
        }
    }
} 