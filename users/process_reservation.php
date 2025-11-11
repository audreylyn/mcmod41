<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

// Include room status handler to automatically update room statuses
require_once '../auth/room_status_handler.php';

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get user role and ID from session
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    db();

    // Validate required fields
    $requiredFields = ['activityName', 'purpose', 'participants', 'reservationDate', 'reservationTime', 'endTime', 'roomId'];
    $missing = [];

    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        $_SESSION['error_message'] = "The following fields are required: " . implode(', ', $missing);
        header("Location: users_browse_room.php");
        exit();
    }

    // Check if student is banned (only for students)
    if ($userRole === 'Student') {
        $penaltyCheckStmt = $conn->prepare("SELECT PenaltyStatus FROM student WHERE StudentID = ?");
        $penaltyCheckStmt->bind_param("i", $userId);
        $penaltyCheckStmt->execute();
        $penaltyResult = $penaltyCheckStmt->get_result();
        
        if ($penaltyResult->num_rows > 0) {
            $penaltyData = $penaltyResult->fetch_assoc();
            if ($penaltyData['PenaltyStatus'] === 'banned') {
                $_SESSION['error_message'] = "Your account has been banned and you cannot make reservations. Please contact your department administrator.";
                header("Location: users_reservation_history.php");
                exit();
            }
        }
    }

    // Get form data
    $activityName = trim($_POST['activityName']);
    $purpose = trim($_POST['purpose']);
    $participants = intval($_POST['participants']);
    $reservationDate = $_POST['reservationDate'];
    $startTime = $_POST['reservationTime'];
    $endTime = $_POST['endTime'];
    $roomId = intval($_POST['roomId']);
    // User ID is already set in the header

    // Prevent booking past dates
    $currentDate = date('Y-m-d');
    if ($reservationDate < $currentDate) {
        $_SESSION['error_message'] = "Cannot book reservations for past dates. Please select a future date.";
        header("Location: users_browse_room.php");
        exit();
    }

    // Validate data
    if (strlen($activityName) < 3) {
        $_SESSION['error_message'] = "Activity name must be at least 3 characters";
        header("Location: users_browse_room.php");
        exit();
    }

    if (strlen($purpose) < 10) {
        $_SESSION['error_message'] = "Purpose must be at least 10 characters";
        header("Location: users_browse_room.php");
        exit();
    }

    if ($participants < 1) {
        $_SESSION['error_message'] = "Number of participants must be at least 1";
        header("Location: users_browse_room.php");
        exit();
    }

    // Format date and times for database
    // Note: Database stores StartTime and EndTime as TIME type, ReservationDate as DATE type
    $startTimeFormatted = $startTime . ':00';
    $endTimeFormatted = $endTime . ':00';

    // Check if start time is before end time
    $startTimestamp = strtotime($reservationDate . ' ' . $startTimeFormatted);
    $endTimestamp = strtotime($reservationDate . ' ' . $endTimeFormatted);

    if ($startTimestamp >= $endTimestamp) {
        $_SESSION['error_message'] = "End time must be after start time";
        header("Location: users_browse_room.php");
        exit();
    }

    // Check if the room is available for the selected time
    // Overlap occurs when:
    // 1. Same room and same date
    // 2. New booking starts before existing ends AND new booking ends after existing starts
    $checkSql = "SELECT COUNT(*) as count 
                FROM room_requests 
                WHERE RoomID = ? 
                AND ReservationDate = ?
                AND Status = 'approved' 
                AND StartTime < ? 
                AND EndTime > ?";

    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param(
        "isss",
        $roomId,
        $reservationDate,
        $endTimeFormatted,    // New booking's end time
        $startTimeFormatted   // New booking's start time
    );
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $conflictCount = $checkResult->fetch_assoc()['count'];

    if ($conflictCount > 0) {
        $_SESSION['error_message'] = "This room is already booked for the selected time. Please choose another time or room.";
        header("Location: users_reservation_history.php");
        exit();
    }

    // Check room capacity
    $capacitySql = "SELECT capacity, RoomStatus FROM rooms WHERE id = ?";
    $capacityStmt = $conn->prepare($capacitySql);
    $capacityStmt->bind_param("i", $roomId);
    $capacityStmt->execute();
    $capacityResult = $capacityStmt->get_result();

    if ($capacityResult->num_rows > 0) {
        $roomData = $capacityResult->fetch_assoc();
        $roomCapacity = $roomData['capacity'];
        $roomStatus = $roomData['RoomStatus'];
        
        // Check if room is under maintenance
        if ($roomStatus === 'maintenance') {
            $_SESSION['error_message'] = "This room is currently under maintenance and cannot be reserved. Please choose another room.";
            header("Location: users_browse_room.php");
            exit();
        }
        
        if ($participants > $roomCapacity) {
            $_SESSION['error_message'] = "The number of participants exceeds the room capacity of $roomCapacity";
            header("Location: users_browse_room.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Selected room not found";
        header("Location: users_browse_room.php");
        exit();
    }

    // Insert reservation request into database based on user role
    if ($userRole === 'Student') {
        $insertSql = "INSERT INTO room_requests (
            StudentID, 
            RoomID, 
            ActivityName, 
            Purpose, 
            StartTime, 
            EndTime, 
            NumberOfParticipants, 
            Status, 
            RequestDate,
            ReservationDate
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), ?)";
    } else { // Teacher
        $insertSql = "INSERT INTO room_requests (
            TeacherID, 
            RoomID, 
            ActivityName, 
            Purpose, 
            StartTime, 
            EndTime, 
            NumberOfParticipants, 
            Status, 
            RequestDate,
            ReservationDate
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), ?)";
    }

    $insertStmt = $conn->prepare($insertSql);
    // Bind parameters: int, int, string, string, string, string, int, string
    $insertStmt->bind_param(
        "iissssis",
        $userId,
        $roomId,
        $activityName,
        $purpose,
        $startTimeFormatted,
        $endTimeFormatted,
        $participants,
        $reservationDate  // ReservationDate (DATE) as string
    );

    if ($insertStmt->execute()) {
        $_SESSION['success_message'] = "Your room reservation request has been submitted successfully. Please check the request status page for updates.";
        header("Location: users_reservation_history.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error submitting request: " . $insertStmt->error;
        header("Location: users_browse_room.php");
        exit();
    }
} else {
    // If not a POST request, redirect to the reservation form
    $_SESSION['error_message'] = "Invalid request method";
    header("Location: users_browse_room.php");
    exit();
}
