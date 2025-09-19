<?php
// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get user role and ID from session
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Check if student is banned (only for students)
if ($userRole === 'Student') {
    $penaltyCheckStmt = $conn->prepare("SELECT PenaltyStatus FROM student WHERE StudentID = ?");
    $penaltyCheckStmt->bind_param("i", $userId);
    $penaltyCheckStmt->execute();
    $penaltyResult = $penaltyCheckStmt->get_result();
    
    if ($penaltyResult->num_rows > 0) {
        $penaltyData = $penaltyResult->fetch_assoc();
        if ($penaltyData['PenaltyStatus'] === 'banned') {
            // Redirect to equipment report status page and indicate banned status via query flag
            header("Location: equipment_report_status.php?banned=1");
            exit();
        }
    }
}

// Get equipment details from query parameters
// Support both old (unit_id) and new (id) parameter names for backward compatibility
$unitId = '';
$equipmentName = '';
$serialNumber = '';
$roomName = '';
$buildingName = '';
$equipmentId = '';
$equipmentDescription = '';
$department = '';

// Check for new simplified parameter structure first
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $unitId = htmlspecialchars($_GET['id']);
    
    // Fetch equipment details from database using the API endpoint
    // Use absolute URL to avoid path resolution issues
    $protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
    $host = $_SERVER['HTTP_HOST'];
    $apiUrl = $protocol . '://' . $host . '/mcmod41/users/api/get_equipment_details.php?unit_id=' . urlencode($unitId);
    
    // Use cURL to fetch equipment details
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $apiResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log the API call for debugging
    error_log("Equipment API Call - URL: $apiUrl, HTTP Code: $httpCode, cURL Error: $curlError");
    
    if (!$curlError && $httpCode === 200) {
        $responseData = json_decode($apiResponse, true);
        
        // Log the API response for debugging
        error_log("Equipment API Response: " . substr($apiResponse, 0, 500));
        
        if ($responseData && $responseData['success'] && isset($responseData['data'])) {
            $equipmentData = $responseData['data'];
            
            // Populate variables from API response
            $equipmentName = $equipmentData['equipment_name'];
            $equipmentDescription = $equipmentData['equipment_description'] ?? '';
            $equipmentCategory = $equipmentData['equipment_category'] ?? '';
            $serialNumber = $equipmentData['serial_number'] ?? '';
            $roomName = $equipmentData['room_name'];
            $buildingName = $equipmentData['building_name'];
            $equipmentId = $equipmentData['equipment_id'];
            $department = $equipmentData['department'] ?? '';
            
            // For Equipment Type, use the equipment name (which is more descriptive than category)
            $equipmentType = $equipmentName;
            
            error_log("Equipment details loaded via API for unit_id: $unitId - Name: $equipmentName, Room: $roomName, Building: $buildingName");
        } else {
            error_log("Failed to get equipment details from API - invalid response: " . $apiResponse);
        }
    } else {
        error_log("Failed to fetch equipment details via API - cURL error: $curlError, HTTP Code: $httpCode");
    }
} else {
    // Fallback to old parameter structure for backward compatibility
    $unitId = isset($_GET['unit_id']) ? htmlspecialchars($_GET['unit_id']) : '';
    $equipmentName = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '';
    $serialNumber = isset($_GET['serial']) ? htmlspecialchars($_GET['serial']) : '';
    $roomName = isset($_GET['room']) ? htmlspecialchars($_GET['room']) : '';
    $buildingName = isset($_GET['building']) ? htmlspecialchars($_GET['building']) : '';
}

// Initialize variables for equipment type (will be filled from database if not already set)
if (!isset($equipmentType)) {
    $equipmentType = '';
}

$conn = db();

// reference_number is managed by the database via a BEFORE INSERT trigger (EQ + UUID_SHORT())

// Check if we need to add rejection_reason column
$checkRejectionColumnSql = "SHOW COLUMNS FROM equipment_issues LIKE 'rejection_reason'";
$rejectionColumnExists = $conn->query($checkRejectionColumnSql)->num_rows > 0;

if (!$rejectionColumnExists) {
    // Add rejection_reason column to the table
    $alterTableSql = "ALTER TABLE equipment_issues ADD COLUMN rejection_reason TEXT DEFAULT NULL";
    $conn->query($alterTableSql);
}

// Attempt to get equipment ID if not provided but name is available
if (empty($unitId) && !empty($equipmentName) && !empty($roomName)) {
    // Try to find the equipment ID based on name and location
    $findIdSql = "SELECT eu.unit_id FROM equipment_units eu
                  JOIN equipment e ON eu.equipment_id = e.id
                  JOIN rooms r ON eu.room_id = r.id
                  JOIN buildings b ON r.building_id = b.id
                  WHERE e.name LIKE ? AND r.room_name = ? AND b.building_name = ?
                  LIMIT 1";

    $stmt = $conn->prepare($findIdSql);
    $searchName = "%$equipmentName%";
    $stmt->bind_param("sss", $searchName, $roomName, $buildingName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $unitId = $row['unit_id'];
    }
    $stmt->close();
}

// Function to process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    // Get form data
    $issueType = $_POST['issue_type'];
    $condition = $_POST['condition'];
    $description = $_POST['description'];
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $userRole = $_SESSION['role']; // Get user role from session
    $imagePath = null;
    
    // Get unit_id from POST data (from sessionStorage), otherwise use the one from URL
    $formUnitId = isset($_POST['unit_id']) ? intval($_POST['unit_id']) : null;
    $finalUnitId = $formUnitId ?: $unitId;

    // Check if there's already an open report for this equipment
    $checkReportSql = "SELECT ei.id, ei.status, eu.status AS equipment_status 
                      FROM equipment_issues ei 
                      JOIN equipment_units eu ON ei.unit_id = eu.unit_id 
                      WHERE ei.unit_id = ? AND (ei.status = 'pending' OR ei.status = 'in_progress')";
    $checkReportStmt = $conn->prepare($checkReportSql);
    $checkReportStmt->bind_param("i", $finalUnitId);
    $checkReportStmt->execute();
    $reportResult = $checkReportStmt->get_result();
    
    // Check if equipment has non-working status
    $checkEquipmentSql = "SELECT status FROM equipment_units WHERE unit_id = ? AND status IN ('needs_repair', 'maintenance', 'missing')";
    $checkEquipmentStmt = $conn->prepare($checkEquipmentSql);
    $checkEquipmentStmt->bind_param("i", $finalUnitId);
    $checkEquipmentStmt->execute();
    $equipmentResult = $checkEquipmentStmt->get_result();
    
    // Prevent submission if equipment already has an open report or is in non-working state
    if ($reportResult->num_rows > 0 || $equipmentResult->num_rows > 0) {
        $reportData = $reportResult->fetch_assoc();
        $equipmentData = $equipmentResult->fetch_assoc();
        
        if ($reportResult->num_rows > 0) {
            $error_message = "This equipment already has an open report that needs to be resolved by the department admin. Please check the status of your existing report.";
        } else {
            $status = $equipmentData['status'];
            $readableStatus = str_replace('_', ' ', $status);
            $error_message = "This equipment is currently marked as '$readableStatus'. It needs to be resolved by the department admin before new reports can be submitted.";
        }
        $checkReportStmt->close();
        $checkEquipmentStmt->close();
    } else {
        $checkReportStmt->close();
        $checkEquipmentStmt->close();

        // Verify the user ID exists in the database
        if ($userId) {
            // Use different table based on user role
        if ($userRole === 'Student') {
            $checkUserSql = "SELECT StudentID FROM student WHERE StudentID = ?";
            $userIdField = "StudentID";
            $errorMsg = "student";
        } else { // Teacher
            $checkUserSql = "SELECT TeacherID FROM teacher WHERE TeacherID = ?";
            $userIdField = "TeacherID";
            $errorMsg = "teacher";
        }
        
        $checkStmt = $conn->prepare($checkUserSql);
        $checkStmt->bind_param("i", $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            // User ID doesn't exist in the database
            $error_message = "Error: Your $errorMsg ID is not found in the database. Please contact support.";
            $userId = null; // Set to null since it's invalid
        }
        $checkStmt->close();
    } else {
        $error_message = "Error: User ID not found in session. Please try logging in again.";
    }

    // Handle image upload and compression if provided
    if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/equipment_issues/';

        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $timestamp = time();
        $filename = "issue_" . $userId . "_" . $timestamp . ".jpg";
        $uploadPath = $uploadDir . $filename;

        // Simple file upload without image processing
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $imagePath = $uploadPath;
        } else {
            $error_message = "Failed to upload image.";
        }
    }

    // Only proceed with insert if we have a valid user ID and no errors
    if ($userId && !isset($error_message)) {
    // Generate a unique random reference number like EQ####### (7 digits) and ensure no collision
    $maxAttempts = 7;
    $referenceNumber = null;
    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        // 7-digit space: 10,000,000 possibilities
        $candidate = 'EQ' . str_pad(mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT);
        $checkSql = "SELECT 1 FROM equipment_issues WHERE reference_number = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        if ($checkStmt) {
            $checkStmt->bind_param("s", $candidate);
            $checkStmt->execute();
            $chkRes = $checkStmt->get_result();
            if ($chkRes && $chkRes->num_rows === 0) {
                $referenceNumber = $candidate;
                $checkStmt->close();
                break;
            }
            $checkStmt->close();
        }
    }
    if ($referenceNumber === null) {
        // fallback to uniqid-based short value if random collisions happen
        $referenceNumber = 'EQ' . substr(uniqid('', true), -7);
        // ensure fallback is alphanumeric but trimmed to digits when possible
        $referenceNumber = preg_replace('/[^0-9]/', '', $referenceNumber);
        $referenceNumber = 'EQ' . str_pad(substr($referenceNumber, -7), 7, '0', STR_PAD_LEFT);
    }

    // Prepare SQL statement to insert report with image path and reference number
    if ($userRole === 'Student') {
        $sql = "INSERT INTO equipment_issues (unit_id, student_id, issue_type, description, image_path, status, statusCondition, reported_at, reference_number)
            VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssss", $finalUnitId, $userId, $issueType, $description, $imagePath, $condition, $referenceNumber);
    } else { // Teacher
        $sql = "INSERT INTO equipment_issues (unit_id, teacher_id, issue_type, description, image_path, status, statusCondition, reported_at, reference_number)
            VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssss", $finalUnitId, $userId, $issueType, $description, $imagePath, $condition, $referenceNumber);
    }

        // Execute the statement
        try {
            if ($stmt->execute()) {
                // Also update the equipment status in equipment_units table
                $updateEquipment = "UPDATE equipment_units SET status = ?, last_updated = NOW()
                                    WHERE unit_id = ?";
                $updateStmt = $conn->prepare($updateEquipment);
                $updateStmt->bind_param("si", $condition, $finalUnitId);
                $updateStmt->execute();

                // Create an audit log entry
                // First, get the equipment_id from the equipment_units table
                $getEquipmentIdSql = "SELECT equipment_id FROM equipment_units WHERE unit_id = ?";
                $equipmentIdStmt = $conn->prepare($getEquipmentIdSql);
                $equipmentIdStmt->bind_param("i", $finalUnitId);
                $equipmentIdStmt->execute();
                $equipmentIdResult = $equipmentIdStmt->get_result();
                $equipmentIdRow = $equipmentIdResult->fetch_assoc();
                $equipmentId = $equipmentIdRow['equipment_id'];
                $equipmentIdStmt->close();
                
                $auditSql = "INSERT INTO equipment_audit (equipment_id, action, notes)
                            VALUES (?, 'Issue Reported', ?)";
                $auditStmt = $conn->prepare($auditSql);
                $roleText = ($userRole === 'Student') ? 'student' : 'teacher';
                $auditNotes = "Issue reported by $roleText ID: $userId - Type: $issueType";
                $auditStmt->bind_param("is", $equipmentId, $auditNotes);
                $auditStmt->execute();

                // Set success message
                $_SESSION['success_message'] = "Your report has been submitted successfully! The issue will be addressed by the maintenance team.";

                // Store the reference number we generated so confirmation shows the same value
                if (!empty($referenceNumber)) {
                    $_SESSION['report_reference'] = $referenceNumber;
                } else {
                    // fallback to the insert_id read (shouldn't happen normally)
                    $newId = $conn->insert_id;
                    if ($newId) {
                        $refStmt = $conn->prepare("SELECT reference_number FROM equipment_issues WHERE id = ? LIMIT 1");
                        if ($refStmt) {
                            $refStmt->bind_param("i", $newId);
                            $refStmt->execute();
                            $refResult = $refStmt->get_result();
                            if ($refResult && $refResult->num_rows > 0) {
                                $refRow = $refResult->fetch_assoc();
                                $_SESSION['report_reference'] = $refRow['reference_number'];
                            }
                            $refStmt->close();
                        }
                    }
                }

                // Redirect to equipment report status / confirmation page
                header("Location: report-confirmation.php");
                exit;
            } else {
                $error_message = "Error submitting report: " . $stmt->error;
            }
        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }

        $stmt->close();
    }
}

// Check if the equipment_issues table exists, create if not
$checkTableSql = "SHOW TABLES LIKE 'equipment_issues'";
$tableExists = $conn->query($checkTableSql)->num_rows > 0;

if (!$tableExists) {
    // Create the equipment_issues table
    $createTableSql = "CREATE TABLE IF NOT EXISTS equipment_issues (
        id INT AUTO_INCREMENT PRIMARY KEY,
        unit_id INT NOT NULL,
        student_id INT DEFAULT NULL,
        teacher_id INT DEFAULT NULL,
        issue_type VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        image_path VARCHAR(255) DEFAULT NULL,
        status ENUM('pending', 'in_progress', 'resolved', 'rejected') DEFAULT 'pending',
        statusCondition ENUM('working', 'needs_repair', 'maintenance', 'missing') DEFAULT 'working',
        admin_response TEXT DEFAULT NULL,
        reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        resolved_at TIMESTAMP NULL DEFAULT NULL,
        FOREIGN KEY (unit_id) REFERENCES equipment_units(unit_id),
        FOREIGN KEY (student_id) REFERENCES student(StudentID) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES teacher(TeacherID) ON DELETE CASCADE
    )";
    $conn->query($createTableSql);
} else {
    // Check if image_path column exists, add it if not
    $checkColumnSql = "SHOW COLUMNS FROM equipment_issues LIKE 'image_path'";
    $columnExists = $conn->query($checkColumnSql)->num_rows > 0;

    if (!$columnExists) {
        // Add image_path column to existing table
        $addColumnSql = "ALTER TABLE equipment_issues ADD COLUMN image_path VARCHAR(255) DEFAULT NULL";
        $conn->query($addColumnSql);
    }
} // End of database structure check

// Close the if block for form submission
}