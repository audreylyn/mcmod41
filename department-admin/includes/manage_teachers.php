<?php
// Initialize variables
$success_message = '';
$error_message = '';
$conn = db();

// Get department admin's department from session
$adminId = $_SESSION['user_id'];
$adminDepartment = $_SESSION['department'] ?? '';

// If department is not in session, show an error
if (empty($adminDepartment)) {
    $error_message = "Error: Department information not available. Please log out and log in again.";
}

// Process add teacher form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_teacher'])) {
    // Validate and sanitize input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $department = $adminDepartment; // Use admin's department
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    $isValid = true;

    if (empty($first_name) || empty($last_name) || empty($department) || empty($email) || empty($password)) {
        $error_message = "All fields are required.";
        $isValid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
        $isValid = false;
    } else {
        // Check for duplicate email across all user tables
        $email_exists = false;
        $existing_table = '';
        
        // Check in teacher table
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM teacher WHERE Email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->bind_result($email_count);
        $check_stmt->fetch();
        $check_stmt->close();
        
        if ($email_count > 0) {
            $email_exists = true;
            $existing_table = 'teacher';
        }
        
        // Check in student table
        if (!$email_exists) {
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM student WHERE Email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->bind_result($email_count);
            $check_stmt->fetch();
            $check_stmt->close();
            
            if ($email_count > 0) {
                $email_exists = true;
                $existing_table = 'student';
            }
        }
        
        // Check in dept_admin table
        if (!$email_exists) {
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM dept_admin WHERE Email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->bind_result($email_count);
            $check_stmt->fetch();
            $check_stmt->close();
            
            if ($email_count > 0) {
                $email_exists = true;
                $existing_table = 'department admin';
            }
        }
        
        // Check in registrar table
        if (!$email_exists) {
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM registrar WHERE Reg_Email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->bind_result($email_count);
            $check_stmt->fetch();
            $check_stmt->close();
            
            if ($email_count > 0) {
                $email_exists = true;
                $existing_table = 'registrar';
            }
        }

        if ($email_exists) {
            $error_message = "Email already exists in the system as a $existing_table account. Please use a different email.";
            $isValid = false;
        }
    }

    // If validation passes, add the teacher
    if ($isValid) {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO teacher (FirstName, LastName, Department, Email, Password, AdminID) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $error_message = "Prepare failed: " . htmlspecialchars($conn->error);
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Bind parameters
            $stmt->bind_param("sssssi", $first_name, $last_name, $department, $email, $hashed_password, $adminId);

            // Execute the statement
            if ($stmt->execute()) {
                $success_message = "Teacher added successfully!";
            } else {
                $error_message = "Error: " . htmlspecialchars($stmt->error);
            }

            // Close the statement
            $stmt->close();
        }
    }
}

// Process edit teacher form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_teacher'])) {
    // Get form data
    $teacherId = $_POST['teacher_id'];
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    
    // Optional password change
    $newPassword = trim($_POST['password']);
    $changePassword = !empty($newPassword);

    // Validation
    $isValid = true;

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $error_message = "All fields are required except password (only if changing).";
        $isValid = false;
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
        $isValid = false;
    }

    // Check if teacher belongs to the same department
    $checkDeptSql = "SELECT Department FROM teacher WHERE TeacherID = ?";
    $checkStmt = $conn->prepare($checkDeptSql);
    $checkStmt->bind_param("i", $teacherId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $teacherData = $checkResult->fetch_assoc();
    $checkStmt->close();

    if ($teacherData['Department'] !== $adminDepartment) {
        $error_message = "You can only edit teachers in your department.";
        $isValid = false;
    }

    // Check if email is already in use by another user across all tables
    $email_exists = false;
    $existing_table = '';
    
    // Check in teacher table (excluding current teacher)
    $checkEmailSql = "SELECT TeacherID FROM teacher WHERE Email = ? AND TeacherID != ?";
    $checkStmt = $conn->prepare($checkEmailSql);
    $checkStmt->bind_param("si", $email, $teacherId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        $email_exists = true;
        $existing_table = 'teacher';
    }
    $checkStmt->close();
    
    // Check in student table
    if (!$email_exists) {
        $checkEmailSql = "SELECT StudentID FROM student WHERE Email = ?";
        $checkStmt = $conn->prepare($checkEmailSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            $email_exists = true;
            $existing_table = 'student';
        }
        $checkStmt->close();
    }
    
    // Check in dept_admin table
    if (!$email_exists) {
        $checkEmailSql = "SELECT AdminID FROM dept_admin WHERE Email = ?";
        $checkStmt = $conn->prepare($checkEmailSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            $email_exists = true;
            $existing_table = 'department admin';
        }
        $checkStmt->close();
    }
    
    // Check in registrar table
    if (!$email_exists) {
        $checkEmailSql = "SELECT regid FROM registrar WHERE Reg_Email = ?";
        $checkStmt = $conn->prepare($checkEmailSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            $email_exists = true;
            $existing_table = 'registrar';
        }
        $checkStmt->close();
    }
    
    if ($email_exists) {
        $error_message = "This email is already in use by a $existing_table account. Please use a different email.";
        $isValid = false;
    }

    // If validation passes, update teacher information
    if ($isValid) {
        if ($changePassword) {
            // Update with new password
            $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $updateSql = "UPDATE teacher SET 
                          FirstName = ?, 
                          LastName = ?, 
                          Email = ?,
                          Password = ?
                          WHERE TeacherID = ? AND Department = ?";

            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param(
                "ssssis",
                $firstName,
                $lastName,
                $email,
                $hashed_password,
                $teacherId,
                $adminDepartment
            );
        } else {
            // Update without changing password
            $updateSql = "UPDATE teacher SET 
                          FirstName = ?, 
                          LastName = ?, 
                          Email = ?
                          WHERE TeacherID = ? AND Department = ?";

            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param(
                "sssss",
                $firstName,
                $lastName,
                $email,
                $teacherId,
                $adminDepartment
            );
        }

        if ($updateStmt->execute()) {
            $success_message = "Teacher information updated successfully!";
        } else {
            $error_message = "Error updating teacher information: " . $conn->error;
        }

        $updateStmt->close();
    }
}

// Process delete request
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];

    // Check if teacher belongs to the same department
    $checkDeptSql = "SELECT Department FROM teacher WHERE TeacherID = ?";
    $checkStmt = $conn->prepare($checkDeptSql);
    $checkStmt->bind_param("i", $deleteId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $teacherData = $checkResult->fetch_assoc();

        if ($teacherData['Department'] === $adminDepartment) {
            // Teacher belongs to admin's department, proceed with deletion
            $deleteSql = "DELETE FROM teacher WHERE TeacherID = ? AND Department = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("is", $deleteId, $adminDepartment);

            if ($deleteStmt->execute()) {
                $success_message = "Teacher deleted successfully!";
            } else {
                $error_message = "Error deleting teacher: " . $conn->error;
            }

            $deleteStmt->close();
        } else {
            $error_message = "You can only delete teachers in your department.";
        }
    } else {
        $error_message = "Teacher not found.";
    }

    $checkStmt->close();
}

// Fetch all teachers in the department
$sql = "SELECT * FROM teacher WHERE Department = ? ORDER BY TeacherID ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $adminDepartment);
$stmt->execute();
$teachersResult = $stmt->get_result();
$stmt->close();
