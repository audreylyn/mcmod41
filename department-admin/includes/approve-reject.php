<?php
// Include email configuration and service
require_once __DIR__ . '/../config/email_config.php';
require_once __DIR__ . '/sendgrid_email_service.php';

// Process approve/reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_request'])) {
        $requestId = intval($_POST['request_id']);
        $adminId = $_SESSION['user_id'] ?? null;
        $adminFirstName = $_SESSION['firstname'] ?? null;
        $adminLastName = $_SESSION['lastname'] ?? null;

        // Fetch the request details to check for conflicts
        $sql = "SELECT RoomID, StartTime, EndTime FROM room_requests WHERE RequestID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        $stmt->close();

        if ($request) {
            $roomId = $request['RoomID'];
            $startTime = $request['StartTime'];
            $endTime = $request['EndTime'];

            // Check if room is under maintenance
            $maintenanceCheckSql = "SELECT RoomStatus FROM rooms WHERE id = ?";
            $maintenanceStmt = $conn->prepare($maintenanceCheckSql);
            $maintenanceStmt->bind_param("i", $roomId);
            $maintenanceStmt->execute();
            $maintenanceResult = $maintenanceStmt->get_result();
            
            if ($maintenanceResult->num_rows > 0) {
                $roomData = $maintenanceResult->fetch_assoc();
                if ($roomData['RoomStatus'] === 'maintenance') {
                    $_SESSION['error_message'] = "Cannot approve: Room is currently under maintenance. Please remove maintenance status first.";
                    $maintenanceStmt->close();
                    header("Location: dept_room_approval.php");
                    exit();
                }
            }
            $maintenanceStmt->close();

            // Check for overlapping approved requests
            $sql = "SELECT rr.*, CASE 
                        WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                        WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
                    END as RequesterName,
                    CASE 
                        WHEN rr.StudentID IS NOT NULL THEN 'Student'
                        WHEN rr.TeacherID IS NOT NULL THEN 'Teacher'
                    END as RequesterType
                    FROM room_requests rr
                    LEFT JOIN student s ON rr.StudentID = s.StudentID
                    LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
                    WHERE RoomID = ?
                    AND Status = 'approved'
                    AND ((StartTime < ? AND EndTime > ?) OR (StartTime < ? AND EndTime > ?))
                    AND RequestID != ?"; // Exclude the current request
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssi", $roomId, $endTime, $startTime, $startTime, $endTime, $requestId);
            $stmt->execute();
            $conflictResult = $stmt->get_result();

            if ($conflictResult->num_rows > 0) {
                $conflict = $conflictResult->fetch_assoc();
                $conflictMessage = "Cannot approve: Room is already reserved for " . htmlspecialchars($conflict['RequesterName']) . " (" . $conflict['RequesterType'] . ").";
                $_SESSION['error_message'] = $conflictMessage;
                $stmt->close();
                header("Location: dept_room_approval.php");
                exit();
            }
            $stmt->close();
        }

        // If no conflict, proceed with approval
        $sql = "UPDATE room_requests SET Status = 'approved', ApprovedBy = ?, ApproverFirstName = ?, ApproverLastName = ? WHERE RequestID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $adminId, $adminFirstName, $adminLastName, $requestId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Request approved successfully";
            
            // Send email notification if enabled
            if (defined('ENABLE_EMAIL_NOTIFICATIONS') && ENABLE_EMAIL_NOTIFICATIONS && defined('SENDGRID_API_KEY') && SENDGRID_API_KEY !== 'YOUR_SENDGRID_API_KEY') {
                // Get full request details for email
                $sql = "SELECT rr.*, r.room_name, b.building_name,
                        CASE 
                            WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                            WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
                        END as RequesterName,
                        CASE 
                            WHEN rr.StudentID IS NOT NULL THEN s.Email
                            WHEN rr.TeacherID IS NOT NULL THEN t.Email
                        END as RequesterEmail
                        FROM room_requests rr
                        LEFT JOIN rooms r ON rr.RoomID = r.id
                        LEFT JOIN buildings b ON r.building_id = b.id
                        LEFT JOIN student s ON rr.StudentID = s.StudentID
                        LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
                        WHERE rr.RequestID = ?";
                
                $approvalEmailStmt = $conn->prepare($sql);
                $approvalEmailStmt->bind_param("i", $requestId);
                $approvalEmailStmt->execute();
                $result = $approvalEmailStmt->get_result();
                $requestDetails = $result->fetch_assoc();
                $approvalEmailStmt->close();
                
                if ($requestDetails && $requestDetails['RequesterEmail']) {
                    try {
                        $emailService = new SendGridEmailService(SENDGRID_API_KEY, FROM_EMAIL, FROM_NAME);
                        
                        $reservationDetails = [
                            'activity_name' => $requestDetails['ActivityName'],
                            'room_name' => $requestDetails['room_name'],
                            'building_name' => $requestDetails['building_name'],
                            'reservation_date' => date('M j, Y', strtotime($requestDetails['ReservationDate'])),
                            'start_time' => date('g:i A', strtotime($requestDetails['StartTime'])),
                            'end_time' => date('g:i A', strtotime($requestDetails['EndTime'])),
                            'participants' => $requestDetails['NumberOfParticipants'],
                            'approver_name' => $adminFirstName . ' ' . $adminLastName
                        ];
                        
                        $emailResult = $emailService->sendApprovalEmail(
                            $requestDetails['RequesterEmail'],
                            $requestDetails['RequesterName'],
                            $reservationDetails
                        );
                        
                        if (!$emailResult['success']) {
                            error_log("Failed to send approval email for request ID {$requestId}: " . $emailResult['response']);
                        }
                    } catch (Exception $e) {
                        error_log("Email notification error: " . $e->getMessage());
                    }
                }
            }
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['reject_request'])) {
        $requestId = intval($_POST['request_id']);
        $rejectionReason = trim($_POST['rejection_reason']);
        $adminId = $_SESSION['user_id'] ?? null;
        $adminFirstName = $_SESSION['firstname'] ?? null;
        $adminLastName = $_SESSION['lastname'] ?? null;

        $sql = "UPDATE room_requests SET Status = 'rejected', RejectionReason = ?, RejectedBy = ?, RejecterFirstName = ?, RejecterLastName = ? WHERE RequestID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissi", $rejectionReason, $adminId, $adminFirstName, $adminLastName, $requestId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Request rejected successfully";
            
            // Send email notification if enabled
            if (defined('ENABLE_EMAIL_NOTIFICATIONS') && ENABLE_EMAIL_NOTIFICATIONS && defined('SENDGRID_API_KEY') && SENDGRID_API_KEY !== 'YOUR_SENDGRID_API_KEY') {
                // Get full request details for email
                $sql = "SELECT rr.*, r.room_name, b.building_name,
                        CASE 
                            WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                            WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
                        END as RequesterName,
                        CASE 
                            WHEN rr.StudentID IS NOT NULL THEN s.Email
                            WHEN rr.TeacherID IS NOT NULL THEN t.Email
                        END as RequesterEmail
                        FROM room_requests rr
                        LEFT JOIN rooms r ON rr.RoomID = r.id
                        LEFT JOIN buildings b ON r.building_id = b.id
                        LEFT JOIN student s ON rr.StudentID = s.StudentID
                        LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
                        WHERE rr.RequestID = ?";
                
                $rejectionEmailStmt = $conn->prepare($sql);
                $rejectionEmailStmt->bind_param("i", $requestId);
                $rejectionEmailStmt->execute();
                $result = $rejectionEmailStmt->get_result();
                $requestDetails = $result->fetch_assoc();
                $rejectionEmailStmt->close();
                
                if ($requestDetails && $requestDetails['RequesterEmail']) {
                    try {
                        $emailService = new SendGridEmailService(SENDGRID_API_KEY, FROM_EMAIL, FROM_NAME);
                        
                        $reservationDetails = [
                            'activity_name' => $requestDetails['ActivityName'],
                            'room_name' => $requestDetails['room_name'],
                            'building_name' => $requestDetails['building_name'],
                            'reservation_date' => date('M j, Y', strtotime($requestDetails['ReservationDate'])),
                            'start_time' => date('g:i A', strtotime($requestDetails['StartTime'])),
                            'end_time' => date('g:i A', strtotime($requestDetails['EndTime'])),
                            'participants' => $requestDetails['NumberOfParticipants'],
                            'reviewer_name' => $adminFirstName . ' ' . $adminLastName,
                            'rejection_reason' => $rejectionReason
                        ];
                        
                        $emailResult = $emailService->sendRejectionEmail(
                            $requestDetails['RequesterEmail'],
                            $requestDetails['RequesterName'],
                            $reservationDetails
                        );
                        
                        if (!$emailResult['success']) {
                            error_log("Failed to send rejection email for request ID {$requestId}: " . $emailResult['response']);
                        }
                    } catch (Exception $e) {
                        error_log("Email notification error: " . $e->getMessage());
                    }
                }
            }
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    // Redirect to prevent form resubmission
    header("Location: dept_room_approval.php");
    exit();
}