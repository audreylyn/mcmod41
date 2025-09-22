<?php
require_once __DIR__ . '/../../auth/middleware.php';
checkAccess(['Registrar']);

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    $conn = db();

    // Process add admin form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
        // Validate and sanitize input
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $department = trim($_POST['department']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Basic validation
        if (empty($first_name) || empty($last_name) || empty($department) || empty($email) || empty($password)) {
            throw new Exception("All fields are required.");
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        } else {
            // Check for duplicate email
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM dept_admin WHERE Email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->bind_result($email_count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($email_count > 0) {
                throw new Exception("Email already exists. Please use a different email.");
            } else {
                // Prepare statement to prevent SQL injection
                $stmt = $conn->prepare("INSERT INTO dept_admin (FirstName, LastName, Department, Email, Password) VALUES (?, ?, ?, ?, ?)");
                if ($stmt === false) {
                    throw new Exception("Prepare failed: " . htmlspecialchars($conn->error));
                } else {
                    // Hash the password for security
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Bind parameters
                    $stmt->bind_param("sssss", $first_name, $last_name, $department, $email, $hashed_password);

                    // Execute the statement
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = "Administrator added successfully!";

                        // Fetch the updated admin list for the DataTable
                        $sql = "SELECT AdminID, FirstName, LastName, Department, Email FROM dept_admin";
                        $result = $conn->query($sql);
                        $admins = [];
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $admins[] = [
                                    'FirstName' => htmlspecialchars($row['FirstName']),
                                    'LastName' => htmlspecialchars($row['LastName']),
                                    'Department' => htmlspecialchars($row['Department']),
                                    'Email' => htmlspecialchars($row['Email']),
                                    'Actions' => generateActionsHtml($row)
                                ];
                            }
                        }
                        
                        $response['data'] = $admins;
                    } else {
                        throw new Exception("Error: " . htmlspecialchars($stmt->error));
                    }
                    
                    $stmt->close();
                }
            }
        }
    }
    // Process update admin form submission
    else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
        // Get form data and sanitize
        $admin_id = filter_input(INPUT_POST, 'admin_id', FILTER_SANITIZE_NUMBER_INT);
        $first_name = trim(filter_input(INPUT_POST, 'edit_first_name', FILTER_SANITIZE_SPECIAL_CHARS));
        $last_name = trim(filter_input(INPUT_POST, 'edit_last_name', FILTER_SANITIZE_SPECIAL_CHARS));
        $department = trim(filter_input(INPUT_POST, 'edit_department', FILTER_SANITIZE_SPECIAL_CHARS));
        $email = trim(filter_input(INPUT_POST, 'edit_email', FILTER_SANITIZE_EMAIL));
        
        // Validate inputs
        if (empty($admin_id) || empty($first_name) || empty($last_name) || empty($department) || empty($email)) {
            throw new Exception("All fields are required.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        
        // Check if the email already exists (for a different admin)
        $check_email_stmt = $conn->prepare("SELECT AdminID FROM dept_admin WHERE Email = ? AND AdminID != ?");
        $check_email_stmt->bind_param("si", $email, $admin_id);
        $check_email_stmt->execute();
        $check_email_result = $check_email_stmt->get_result();
        
        if ($check_email_result->num_rows > 0) {
            throw new Exception("Email already exists. Please use a different email.");
        }
        $check_email_stmt->close();
        
        // Update the admin information
        $update_stmt = $conn->prepare("UPDATE dept_admin SET FirstName = ?, LastName = ?, Department = ?, Email = ? WHERE AdminID = ?");
        $update_stmt->bind_param("ssssi", $first_name, $last_name, $department, $email, $admin_id);
        
        if ($update_stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Administrator updated successfully.";
            
            // Fetch the updated admin list for the DataTable
            $sql = "SELECT AdminID, FirstName, LastName, Department, Email FROM dept_admin";
            $result = $conn->query($sql);
            $admins = [];
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $admins[] = [
                        'FirstName' => htmlspecialchars($row['FirstName']),
                        'LastName' => htmlspecialchars($row['LastName']),
                        'Department' => htmlspecialchars($row['Department']),
                        'Email' => htmlspecialchars($row['Email']),
                        'Actions' => generateActionsHtml($row)
                    ];
                }
            }
            
            $response['data'] = $admins;
        } else {
            throw new Exception("Error updating administrator: " . $conn->error);
        }
        
        $update_stmt->close();
    }
    // Process delete admin
    else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
        $admin_id = (int)$_POST['admin_id'];
        
        if (empty($admin_id)) {
            throw new Exception("Invalid admin ID.");
        }
        
        $delete_stmt = $conn->prepare("DELETE FROM dept_admin WHERE AdminID = ?");
        
        if ($delete_stmt === false) {
            throw new Exception("Prepare failed: " . htmlspecialchars($conn->error));
        } else {
            $delete_stmt->bind_param("i", $admin_id);
            
            if ($delete_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Administrator deleted successfully!";
                
                // Fetch the updated admin list for the DataTable
                $sql = "SELECT AdminID, FirstName, LastName, Department, Email FROM dept_admin";
                $result = $conn->query($sql);
                $admins = [];
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $admins[] = [
                            'FirstName' => htmlspecialchars($row['FirstName']),
                            'LastName' => htmlspecialchars($row['LastName']),
                            'Department' => htmlspecialchars($row['Department']),
                            'Email' => htmlspecialchars($row['Email']),
                            'Actions' => generateActionsHtml($row)
                        ];
                    }
                }
                
                $response['data'] = $admins;
            } else {
                throw new Exception("Error deleting admin: " . htmlspecialchars($delete_stmt->error));
            }
            
            $delete_stmt->close();
        }
    }
    // Export admin functionality
    else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'export') {
        // Create a file pointer
        $output = fopen('php://temp', 'w');
        
        // Set column headers
        $headers = array('AdminID', 'FirstName', 'LastName', 'Department', 'Email');
        fputcsv($output, $headers);
        
        // Get admin data
        $query = "SELECT AdminID, FirstName, LastName, Department, Email FROM dept_admin";
        $result = $conn->query($query);
        
        // Write admin data to CSV
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }
        }
        
        // Move pointer to beginning of file
        rewind($output);
        
        // Get the content of the file
        $csv_content = stream_get_contents($output);
        fclose($output);
        
        $response['success'] = true;
        $response['message'] = "Export successful";
        $response['data'] = base64_encode($csv_content);
        $response['filename'] = "admin_list_" . date('Y-m-d') . ".csv";
    } else {
        throw new Exception("Invalid request.");
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
    
    echo json_encode($response);
}

// Helper function to generate action buttons HTML
function generateActionsHtml($row) {
    $adminId = htmlspecialchars($row['AdminID']);
    $firstName = htmlspecialchars($row['FirstName']);
    $lastName = htmlspecialchars($row['LastName']);
    $department = htmlspecialchars($row['Department']);
    $email = htmlspecialchars($row['Email']);
    
    return '<div class="button-container">' .
    '<button class="is-small styled-button" onclick="openEditModal(\'' . $adminId . '\', \'' . $firstName . '\', \'' . $lastName . '\', \'' . $department . '\', \'' . $email . '\')">' .
    '<i class="mdi mdi-pencil"></i>' .
    '</button>' .
    '<button class="is-small styled-button is-reset" onclick="deleteAdmin(' . $adminId . ')">' .
    '<i class="mdi mdi-delete"></i>' .
    '</button>' .
    '</div>';
}
