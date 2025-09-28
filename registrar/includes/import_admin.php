<?php
session_start();
require_once __DIR__ . '/../../auth/middleware.php';

// Create a new database connection
$db = db();

$status = 'error';
$message = 'An unknown error occurred.';

// Check if the form was submitted
if (isset($_POST['importSubmit'])) {
    $file = $_FILES['file']['tmp_name'];
    $duplicateRecords = [];
    $invalidEmails = [];
    $invalidDepartments = [];
    $successRecords = [];
    $imported = 0;
    $lineNumber = 1; // Start at line 1 (header row)

    // Validate file type
    $fileType = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($fileType), ['csv', 'xlsx', 'xls'])) {
        $message = "Only CSV and Excel files are allowed (.csv, .xlsx, .xls).";
        header("Location: ../reg_add_admin?status=$status&msg=" . urlencode($message));
        exit();
    }

    // Check if the uploaded file is not empty
    if ($_FILES['file']['size'] > 0) {
        // Initialize variables
        $imported = 0;
        $lineNumber = 1;
        $duplicateRecords = [];
        $invalidEmails = [];
        $invalidDepartments = [];
        $successRecords = [];
        
        // Handle different file types
        if (strtolower($fileType) === 'csv') {
            // Handle CSV files
            $fileHandle = fopen($file, 'r');
            
            // Skip the header row
            fgetcsv($fileHandle);
            $lineNumber++;

            // List of valid departments
            $validDepartments = ['Accountancy', 'Business Administration', 'Hospitality Management', 'Education and Arts', 'Criminal Justice'];
            
            // Process each row in the CSV file
            while (($row = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
            // Ensure the row has enough columns
            if (count($row) >= 5) {
                $firstName = trim($row[0]);
                $lastName = trim($row[1]);
                $department = trim($row[2]);
                $email = trim($row[3]);
                $rawPassword = trim($row[4]);
                
                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $invalidEmails[] = "$email (Invalid format)";
                }
                // Validate department
                elseif (!in_array($department, $validDepartments)) {
                    $invalidDepartments[] = "$department (Invalid department for $email)";
                }
                else {
                    // Check for duplicate email across all user tables
                    $email_exists = false;
                    $existing_table = '';
                    
                    // Check in dept_admin table
                    $stmt = $db->prepare("SELECT COUNT(*) FROM dept_admin WHERE Email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->bind_result($count);
                    $stmt->fetch();
                    $stmt->close();
                    
                    if ($count > 0) {
                        $email_exists = true;
                        $existing_table = 'department admin';
                    }
                    
                    // Check in teacher table
                    if (!$email_exists) {
                        $stmt = $db->prepare("SELECT COUNT(*) FROM teacher WHERE Email = ?");
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $stmt->bind_result($count);
                        $stmt->fetch();
                        $stmt->close();
                        
                        if ($count > 0) {
                            $email_exists = true;
                            $existing_table = 'teacher';
                        }
                    }
                    
                    // Check in student table
                    if (!$email_exists) {
                        $stmt = $db->prepare("SELECT COUNT(*) FROM student WHERE Email = ?");
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $stmt->bind_result($count);
                        $stmt->fetch();
                        $stmt->close();
                        
                        if ($count > 0) {
                            $email_exists = true;
                            $existing_table = 'student';
                        }
                    }
                    
                    // Check in registrar table
                    if (!$email_exists) {
                        $stmt = $db->prepare("SELECT COUNT(*) FROM registrar WHERE Reg_Email = ?");
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $stmt->bind_result($count);
                        $stmt->fetch();
                        $stmt->close();
                        
                        if ($count > 0) {
                            $email_exists = true;
                            $existing_table = 'registrar';
                        }
                    }

                    // If a duplicate is found, store the email with table info
                    if ($email_exists) {
                        $duplicateRecords[] = "$email (exists as $existing_table)";
                    } else {
                        // Hash the password
                        $password = password_hash($rawPassword, PASSWORD_DEFAULT);
                        
                        // Insert the new admin record into the database
                        $stmt = $db->prepare("INSERT INTO dept_admin (FirstName, LastName, Department, Email, Password) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssss", $firstName, $lastName, $department, $email, $password);
                        
                        if ($stmt->execute()) {
                            $imported++;
                            $successRecords[] = "$firstName $lastName ($email)";
                        }
                        $stmt->close(); // Close the statement
                    }
                }
            }
            $lineNumber++;
        }

        // Close the file handle
        fclose($fileHandle);
        
        } else {
            // Handle Excel files (requires PHPSpreadsheet or similar)
            $message = "Excel file support is not yet implemented. Please use CSV format for now.";
            header("Location: ../reg_add_admin?status=$status&msg=" . urlencode($message));
            exit();
        }

        // Build the response message
        $successMessage = '';
        if ($imported > 0) {
            $successMessage = "Successfully imported $imported administrators";
        }
        
        $errorMessage = '';
        if (!empty($duplicateRecords) || !empty($invalidEmails) || !empty($invalidDepartments)) {
            $errorMessage = "Some records were not imported:";
            
            if (!empty($duplicateRecords)) {
                $errorMessage .= "<br><strong>Duplicate Emails:</strong><br>" . implode("<br>", $duplicateRecords);
            }
            if (!empty($invalidEmails)) {
                $errorMessage .= "<br><strong>Invalid Emails:</strong><br>" . implode("<br>", $invalidEmails);
            }
            if (!empty($invalidDepartments)) {
                $errorMessage .= "<br><strong>Invalid Departments:</strong><br>" . implode("<br>", $invalidDepartments);
            }
        }

        if (!empty($errorMessage)) {
            $status = 'error';
            $message = $successMessage . ($successMessage ? '<br><br>' : '') . $errorMessage;
        } elseif ($imported > 0) {
            $status = 'success';
            $message = $successMessage;
        } else {
            $status = 'error';
            $message = 'No new records were imported.';
        }

    } else {
        $message = "The uploaded file is empty.";
    }
}

// Close the database connection
$db->close();

// Redirect back to the admin page
header("Location: ../reg_add_admin?status=$status&msg=" . urlencode($message));
exit();
?>

