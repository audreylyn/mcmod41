<?php
require '../../auth/middleware.php';
checkAccess(['Department Admin']);

require_once '../../auth/dbh.inc.php';

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Get the admin's department
$admin_id = $_SESSION['user_id'];
// Try to get department from session first
$admin_department = $_SESSION['department'] ?? '';

// If not in session, get from database
if (empty($admin_department)) {
    $conn = db();
    $admin_dept_query = "SELECT Department FROM dept_admin WHERE AdminID = ?";
    $admin_dept_stmt = $conn->prepare($admin_dept_query);
    $admin_dept_stmt->bind_param('i', $admin_id);
    $admin_dept_stmt->execute();
    $result = $admin_dept_stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $admin_department = $row['Department'];
        $_SESSION['department'] = $admin_department;
    }
} else {
    $conn = db();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_type = $_POST['report_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    if (empty($report_type) || empty($start_date) || empty($end_date)) {
        http_response_code(400);
        echo "Missing required parameters";
        exit;
    }
    
    // Generate report based on type
    switch ($report_type) {
        case 'room-utilization':
            echo generateRoomUtilizationReport($conn, $admin_department, $start_date, $end_date);
            break;
        case 'booking-requests':
            echo generateBookingRequestsReport($conn, $admin_department, $start_date, $end_date);
            break;
        case 'equipment-status':
            echo generateEquipmentStatusReport($conn, $admin_department, $start_date, $end_date);
            break;
        default:
            http_response_code(400);
            echo "Invalid report type";
            exit;
    }
} else {
    http_response_code(405);
    echo "Method not allowed";
}

function generateRoomUtilizationReport($conn, $department, $start_date, $end_date) {
    // Get ALL rooms for the department, including those without reservations
    $query = "
        SELECT 
            r.room_name,
            r.room_type,
            b.building_name,
            b.department,
            r.capacity,
            COALESCE(COUNT(rr.RequestID), 0) as total_requests,
            COALESCE(SUM(CASE WHEN rr.Status = 'approved' THEN 1 ELSE 0 END), 0) as approved_requests,
            COALESCE(SUM(CASE WHEN rr.Status = 'approved' THEN rr.NumberOfParticipants ELSE 0 END), 0) as total_participants,
            ROUND(
                (COALESCE(SUM(CASE WHEN rr.Status = 'approved' THEN 1 ELSE 0 END), 0) * 100.0 / 
                NULLIF(COALESCE(COUNT(rr.RequestID), 0), 0)), 2
            ) as approval_rate,
            CASE 
                WHEN COALESCE(COUNT(rr.RequestID), 0) = 0 THEN 'Unused'
                WHEN COALESCE(SUM(CASE WHEN rr.Status = 'approved' THEN 1 ELSE 0 END), 0) = 0 THEN 'No Approvals'
                ELSE 'Active'
            END as usage_status
        FROM rooms r
        JOIN buildings b ON r.building_id = b.id
        LEFT JOIN room_requests rr ON r.id = rr.RoomID 
            AND rr.ReservationDate BETWEEN ? AND ?
            AND (
                (rr.StudentID IS NOT NULL AND EXISTS (
                    SELECT 1 FROM student s WHERE s.StudentID = rr.StudentID AND s.Department = ?
                ))
                OR 
                (rr.TeacherID IS NOT NULL AND EXISTS (
                    SELECT 1 FROM teacher t WHERE t.TeacherID = rr.TeacherID AND t.Department = ?
                ))
            )
        WHERE b.department = ?
        GROUP BY r.id, r.room_name, r.room_type, b.building_name, b.department, r.capacity
        ORDER BY total_requests DESC, r.room_name
    ";
    
    // Map department names to match building department names
    $dept_map = [
        'education and arts' => 'Education and Arts',
        'criminal justice' => 'Criminal Justice',
        'accountancy' => 'Accountancy',
        'business administration' => 'Business Administration',
        'hospitality management' => 'Hospitality Management'
    ];
    
    $building_dept = $dept_map[strtolower($department)] ?? $department;
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssss', $start_date, $end_date, $department, $department, $building_dept);
    $stmt->execute();
    $result = $stmt->get_result();
    $rooms = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get summary statistics for ALL rooms in department
    $summary_query = "
        SELECT 
            COUNT(DISTINCT r.id) as total_rooms,
            COALESCE(COUNT(rr.RequestID), 0) as total_requests,
            COALESCE(SUM(CASE WHEN rr.Status = 'approved' THEN 1 ELSE 0 END), 0) as approved_requests,
            AVG(CASE WHEN rr.Status = 'approved' THEN rr.NumberOfParticipants ELSE NULL END) as avg_participants,
            COUNT(DISTINCT CASE WHEN rr.RequestID IS NULL THEN r.id END) as unused_rooms
        FROM rooms r
        JOIN buildings b ON r.building_id = b.id
        LEFT JOIN room_requests rr ON r.id = rr.RoomID
            AND rr.ReservationDate BETWEEN ? AND ?
            AND (
                (rr.StudentID IS NOT NULL AND EXISTS (
                    SELECT 1 FROM student s WHERE s.StudentID = rr.StudentID AND s.Department = ?
                ))
                OR 
                (rr.TeacherID IS NOT NULL AND EXISTS (
                    SELECT 1 FROM teacher t WHERE t.TeacherID = rr.TeacherID AND t.Department = ?
                ))
            )
        WHERE b.department = ?
    ";
    
    $summary_stmt = $conn->prepare($summary_query);
    $summary_stmt->bind_param('sssss', $start_date, $end_date, $department, $department, $building_dept);
    $summary_stmt->execute();
    $summary_result = $summary_stmt->get_result();
    $summary = $summary_result->fetch_assoc();
    
    $html = "
    <div class='report-container'>
        <div class='report-header'>
            <h2>Room Utilization Report</h2>
            <p><strong>Department:</strong> {$department}</p>
            <p><strong>Period:</strong> " . date('M j, Y', strtotime($start_date)) . " to " . date('M j, Y', strtotime($end_date)) . "</p>
            <p><strong>Generated:</strong> " . date('M j, Y g:i A') . "</p>
        </div>
        
        <div class='report-summary'>
            <h3>Summary</h3>
            <div class='summary-cards'>
                <div class='summary-card'>
                    <div class='summary-value'>{$summary['total_rooms']}</div>
                    <div class='summary-label'>Total Rooms</div>
                </div>
                <div class='summary-card'>
                    <div class='summary-value'>{$summary['total_requests']}</div>
                    <div class='summary-label'>Total Requests</div>
                </div>
                <div class='summary-card'>
                    <div class='summary-value'>{$summary['approved_requests']}</div>
                    <div class='summary-label'>Approved Requests</div>
                </div>
                <div class='summary-card'>
                    <div class='summary-value'>" . round($summary['avg_participants'] ?? 0, 1) . "</div>
                    <div class='summary-label'>Avg. Participants</div>
                </div>
                <div class='summary-card'>
                    <div class='summary-value'>{$summary['unused_rooms']}</div>
                    <div class='summary-label'>Unused Rooms</div>
                </div>
            </div>
        </div>
        
        <div class='report-table'>
            <h3>Room Details</h3>
            <table class='table is-fullwidth is-striped'>
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Room</th>
                        <th>Type</th>
                        <th>Building</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Total Requests</th>
                        <th>Approved</th>
                        <th>Approval Rate</th>
                    </tr>
                </thead>
                <tbody>";
    
    foreach ($rooms as $room) {
        $approval_rate = $room['approval_rate'] ?? 0;
        
        $html .= "
                    <tr>
                        <td>{$room['department']}</td>
                        <td>{$room['room_name']}</td>
                        <td>{$room['room_type']}</td>
                        <td>{$room['building_name']}</td>
                        <td>{$room['capacity']}</td>
                        <td>{$room['usage_status']}</td>
                        <td>{$room['total_requests']}</td>
                        <td>{$room['approved_requests']}</td>
                        <td>{$approval_rate}%</td>
                    </tr>";
    }
    
    $html .= "
                </tbody>
            </table>
        </div>
    </div>
    
    <style>
        .report-container { font-family: Arial, sans-serif; }
        .report-header { margin-bottom: 30px; border-bottom: 2px solid #1e5631; padding-bottom: 15px; }
        .report-header h2 { color: #1e5631; margin-bottom: 10px; }
        .summary-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 15px 0; }
        .summary-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; }
        .summary-value { font-size: 1.5rem; font-weight: bold; color: #1e5631; }
        .summary-label { font-size: 0.9rem; color: #6c757d; margin-top: 5px; }
        .report-table { margin-top: 30px; }
        .table { border-collapse: collapse; width: 100%; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background-color: #1e5631; color: white; }
        .table tr:nth-child(even) { background-color: #f2f2f2; }
    </style>";
    
    return $html;
}

function generateBookingRequestsReport($conn, $department, $start_date, $end_date) {
    // Get detailed room reservation requests (approved and rejected only)
    $query = "
        SELECT 
            rr.RequestID,
            rr.RequestDate,
            rr.ReservationDate,
            rr.StartTime,
            rr.EndTime,
            rr.ActivityName,
            rr.Purpose,
            rr.NumberOfParticipants,
            rr.Status,
            CONCAT(r.room_name, ' (', r.room_type, ')') as room_details,
            b.building_name,
            CASE 
                WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName, ' (Student)')
                WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName, ' (Teacher)')
                ELSE 'Unknown'
            END as requester_name,
            CASE 
                WHEN rr.StudentID IS NOT NULL THEN s.Department
                WHEN rr.TeacherID IS NOT NULL THEN t.Department
                ELSE 'Unknown'
            END as requester_department,
            DATE_FORMAT(rr.RequestDate, '%M %d, %Y %h:%i %p') as formatted_request_date,
            DATE_FORMAT(rr.ReservationDate, '%M %d, %Y') as formatted_reservation_date,
            TIME_FORMAT(rr.StartTime, '%h:%i %p') as formatted_start_time,
            TIME_FORMAT(rr.EndTime, '%h:%i %p') as formatted_end_time
        FROM room_requests rr
        LEFT JOIN rooms r ON rr.RoomID = r.id
        LEFT JOIN buildings b ON r.building_id = b.id
        LEFT JOIN student s ON rr.StudentID = s.StudentID
        LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
        WHERE rr.ReservationDate BETWEEN ? AND ?
        AND rr.Status IN ('approved', 'rejected')
        AND (s.Department = ? OR t.Department = ?)
        ORDER BY rr.ReservationDate DESC, rr.StartTime ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $start_date, $end_date, $department, $department);
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get summary counts
    $summary_query = "
        SELECT 
            COUNT(*) as total_requests,
            SUM(CASE WHEN rr.Status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN rr.Status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            ROUND(SUM(CASE WHEN rr.Status = 'approved' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as approval_rate
        FROM room_requests rr
        LEFT JOIN student s ON rr.StudentID = s.StudentID
        LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
        WHERE rr.ReservationDate BETWEEN ? AND ?
        AND rr.Status IN ('approved', 'rejected')
        AND (s.Department = ? OR t.Department = ?)
    ";
    
    $summary_stmt = $conn->prepare($summary_query);
    $summary_stmt->bind_param('ssss', $start_date, $end_date, $department, $department);
    $summary_stmt->execute();
    $summary_result = $summary_stmt->get_result();
    $summary = $summary_result->fetch_assoc();
    
    $html = "
    <div class='report-container'>
        <div class='report-header'>
            <h2>Room Reservation Requests Report</h2>
            <p><strong>Department:</strong> {$department}</p>
            <p><strong>Period:</strong> " . date('M j, Y', strtotime($start_date)) . " to " . date('M j, Y', strtotime($end_date)) . "</p>
            <p><strong>Generated:</strong> " . date('M j, Y g:i A') . "</p>
            <p><strong>Status Filter:</strong> Approved and Rejected requests only</p>
        </div>
        
        <div class='report-summary'>
            <h3>Summary</h3>
            <div class='summary-cards'>
                <div class='summary-card'>
                    <div class='summary-value'>{$summary['total_requests']}</div>
                    <div class='summary-label'>Total Requests</div>
                </div>
                <div class='summary-card approved'>
                    <div class='summary-value'>{$summary['approved']}</div>
                    <div class='summary-label'>Approved</div>
                </div>
                <div class='summary-card rejected'>
                    <div class='summary-value'>{$summary['rejected']}</div>
                    <div class='summary-label'>Rejected</div>
                </div>
                <div class='summary-card'>
                    <div class='summary-value'>" . ($summary['approval_rate'] ?? 0) . "%</div>
                    <div class='summary-label'>Approval Rate</div>
                </div>
            </div>
        </div>
        
        <div class='report-table'>
            <h3>Detailed Requests (" . count($requests) . " records)</h3>
            <table id='requestsTable' class='table is-fullwidth is-striped'>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Requester</th>
                        <th>Activity</th>
                        <th>Room</th>
                        <th>Building</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Participants</th>
                        <th>Status</th>
                        <th>Requested On</th>
                    </tr>
                </thead>
                <tbody>";
    
    foreach ($requests as $request) {
        $status_class = strtolower($request['Status']);
        $status_badge = $request['Status'] === 'approved' ? 
            "<span class='status-badge approved'>Approved</span>" : 
            "<span class='status-badge rejected'>Rejected</span>";
        
        $html .= "
                    <tr>
                        <td>REQ-{$request['RequestID']}</td>
                        <td>{$request['requester_name']}</td>
                        <td><strong>{$request['ActivityName']}</strong><br><small>{$request['Purpose']}</small></td>
                        <td>{$request['room_details']}</td>
                        <td>{$request['building_name']}</td>
                        <td>{$request['formatted_reservation_date']}</td>
                        <td>{$request['formatted_start_time']} - {$request['formatted_end_time']}</td>
                        <td>{$request['NumberOfParticipants']}</td>
                        <td>{$status_badge}</td>
                        <td>{$request['formatted_request_date']}</td>
                    </tr>";
    }
    
    if (empty($requests)) {
        $html .= "
                    <tr>
                        <td colspan='10' class='no-data'>No reservation requests found for the selected period.</td>
                    </tr>";
    }
    
    $html .= "
                </tbody>
            </table>
        </div>
    </div>
    
    <style>
        .report-container { font-family: Arial, sans-serif; }
        .report-header { margin-bottom: 30px; border-bottom: 2px solid #1e5631; padding-bottom: 15px; }
        .report-header h2 { color: #1e5631; margin-bottom: 10px; }
        .summary-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 15px 0; }
        .summary-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #1e5631; }
        .summary-card.approved { border-left-color: #28a745; }
        .summary-card.rejected { border-left-color: #dc3545; }
        .summary-value { font-size: 1.5rem; font-weight: bold; color: #1e5631; }
        .summary-label { font-size: 0.9rem; color: #6c757d; margin-top: 5px; }
        .report-table { margin-top: 30px; }
        .table { border-collapse: collapse; width: 100%; font-size: 0.9rem; }
        .table th, .table td { padding: 10px 8px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: top; }
        .table th { background-color: #1e5631; color: white; font-weight: bold; position: sticky; top: 0; }
        .table tr:nth-child(even) { background-color: #f8f9fa; }
        .table tr:hover { background-color: #e8f5e8; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
        .status-badge.approved { background-color: #d4edda; color: #155724; }
        .status-badge.rejected { background-color: #f8d7da; color: #721c24; }
        .no-data { text-align: center; color: #6c757d; font-style: italic; padding: 20px; }
        
        /* DataTable Integration Styles */
        .dataTables_wrapper { margin-top: 20px; }
        .dataTables_length, .dataTables_filter { margin-bottom: 15px; }
        .dataTables_info, .dataTables_paginate { margin-top: 15px; }
        .dataTables_paginate .paginate_button { padding: 6px 12px; margin: 0 2px; }
        .dataTables_paginate .paginate_button.current { background-color: #1e5631; color: white !important; }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#requestsTable').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                order: [[5, 'desc']], // Order by Date column
                columnDefs: [
                    { orderable: false, targets: [2] }, // Disable sorting on Activity column (has HTML)
                    { className: 'text-center', targets: [0, 7, 8] } // Center align specific columns
                ],
                language: {
                    search: 'Search requests:',
                    lengthMenu: 'Show _MENU_ requests per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_ requests',
                    infoEmpty: 'No requests found',
                    infoFiltered: '(filtered from _MAX_ total requests)',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: 'Next',
                        previous: 'Previous'
                    }
                }
            });
        }
    });
    </script>";
    
    return $html;
}

function generateEquipmentStatusReport($conn, $department, $start_date, $end_date) {
    // Get detailed equipment issues for the department
    $query = "
        SELECT 
            ei.id as issue_id,
            ei.issue_type,
            ei.description,
            ei.status,
            ei.reported_at,
            ei.resolved_at,
            e.name as equipment_name,
            e.category,
            eu.unit_id,
            eu.serial_number,
            COALESCE(r.room_name, 'Unassigned') as room_name,
            COALESCE(b.building_name, 'No Building') as building_name,
            b.department,
            CASE 
                WHEN ei.student_id IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName, ' (Student)')
                WHEN ei.teacher_id IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName, ' (Teacher)')
                ELSE 'Unknown'
            END as reporter_name,
            CASE 
                WHEN ei.student_id IS NOT NULL THEN s.Department
                WHEN ei.teacher_id IS NOT NULL THEN t.Department
                ELSE 'Unknown'
            END as reporter_department,
            DATE_FORMAT(ei.reported_at, '%M %d, %Y %h:%i %p') as formatted_reported_date,
            DATE_FORMAT(ei.resolved_at, '%M %d, %Y %h:%i %p') as formatted_resolved_date
        FROM equipment_issues ei
        JOIN equipment_units eu ON ei.unit_id = eu.unit_id
        JOIN equipment e ON eu.equipment_id = e.id
        LEFT JOIN rooms r ON eu.room_id = r.id
        LEFT JOIN buildings b ON r.building_id = b.id
        LEFT JOIN student s ON ei.student_id = s.StudentID
        LEFT JOIN teacher t ON ei.teacher_id = t.TeacherID
        WHERE ei.reported_at BETWEEN ? AND ?
        AND (
            (ei.student_id IS NOT NULL AND s.Department = ?)
            OR 
            (ei.teacher_id IS NOT NULL AND t.Department = ?)
            OR
            (b.department = ?)
        )
        ORDER BY ei.reported_at DESC
    ";
    
    // Map department names to match building department names
    $dept_map = [
        'education and arts' => 'Education and Arts',
        'criminal justice' => 'Criminal Justice',
        'accountancy' => 'Accountancy',
        'business administration' => 'Business Administration',
        'hospitality management' => 'Hospitality Management'
    ];
    
    $building_dept = $dept_map[strtolower($department)] ?? $department;
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssss', $start_date, $end_date, $department, $department, $building_dept);
    $stmt->execute();
    $result = $stmt->get_result();
    $issues = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get summary statistics
    $summary_query = "
        SELECT 
            COUNT(*) as total_issues,
            SUM(CASE WHEN ei.status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN ei.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN ei.status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN ei.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            COUNT(DISTINCT eu.equipment_id) as affected_equipment,
            COUNT(DISTINCT r.id) as affected_rooms
        FROM equipment_issues ei
        JOIN equipment_units eu ON ei.unit_id = eu.unit_id
        JOIN equipment e ON eu.equipment_id = e.id
        LEFT JOIN rooms r ON eu.room_id = r.id
        LEFT JOIN buildings b ON r.building_id = b.id
        LEFT JOIN student s ON ei.student_id = s.StudentID
        LEFT JOIN teacher t ON ei.teacher_id = t.TeacherID
        WHERE ei.reported_at BETWEEN ? AND ?
        AND (
            (ei.student_id IS NOT NULL AND s.Department = ?)
            OR 
            (ei.teacher_id IS NOT NULL AND t.Department = ?)
            OR
            (b.department = ?)
        )
    ";
    
    $summary_stmt = $conn->prepare($summary_query);
    $summary_stmt->bind_param('sssss', $start_date, $end_date, $department, $department, $building_dept);
    $summary_stmt->execute();
    $summary_result = $summary_stmt->get_result();
    $summary = $summary_result->fetch_assoc();
    
    $html = "
    <div class='report-container'>
        <div class='report-header'>
            <h2>Equipment Issues Report</h2>
            <p><strong>Department:</strong> {$department}</p>
            <p><strong>Period:</strong> " . date('M j, Y', strtotime($start_date)) . " to " . date('M j, Y', strtotime($end_date)) . "</p>
            <p><strong>Generated:</strong> " . date('M j, Y g:i A') . "</p>
        </div>
        
        <div class='report-summary'>
            <h3>Issues Overview</h3>
            <div class='summary-cards'>
                <div class='summary-card'>
                    <div class='summary-value'>{$summary['total_issues']}</div>
                    <div class='summary-label'>Total Issues</div>
                </div>
                <div class='summary-card pending'>
                    <div class='summary-value'>{$summary['pending']}</div>
                    <div class='summary-label'>Pending</div>
                </div>
                <div class='summary-card in-progress'>
                    <div class='summary-value'>{$summary['in_progress']}</div>
                    <div class='summary-label'>In Progress</div>
                </div>
                <div class='summary-card resolved'>
                    <div class='summary-value'>{$summary['resolved']}</div>
                    <div class='summary-label'>Resolved</div>
                </div>
                <div class='summary-card'>
                    <div class='summary-value'>{$summary['affected_equipment']}</div>
                    <div class='summary-label'>Affected Equipment</div>
                </div>
            </div>
        </div>
        
        <div class='report-table'>
            <h3>Equipment Issues Details (" . count($issues) . " records)</h3>
            <table id='issuesTable' class='table is-fullwidth is-striped'>
                <thead>
                    <tr>
                        <th>Issue ID</th>
                        <th>Equipment</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Issue Type</th>
                        <th>Description</th>
                        <th>Reporter</th>
                        <th>Status</th>
                        <th>Reported Date</th>
                        <th>Resolved Date</th>
                    </tr>
                </thead>
                <tbody>";
    
    foreach ($issues as $issue) {
        $status_class = strtolower(str_replace(' ', '-', $issue['status']));
        $status_badge = "<span class='status-badge {$status_class}'>" . ucfirst(str_replace('_', ' ', $issue['status'])) . "</span>";
        $resolved_date = $issue['resolved_at'] ? $issue['formatted_resolved_date'] : '-';
        
        $html .= "
                    <tr>
                        <td>ISS-{$issue['issue_id']}</td>
                        <td><strong>{$issue['equipment_name']}</strong><br><small>Unit: {$issue['unit_id']}</small></td>
                        <td>{$issue['category']}</td>
                        <td>{$issue['room_name']}<br><small>{$issue['building_name']}</small></td>
                        <td>{$issue['issue_type']}</td>
                        <td><div class='description-cell'>{$issue['description']}</div></td>
                        <td>{$issue['reporter_name']}</td>
                        <td>{$status_badge}</td>
                        <td>{$issue['formatted_reported_date']}</td>
                        <td>{$resolved_date}</td>
                    </tr>";
    }
    
    if (empty($issues)) {
        $html .= "
                    <tr>
                        <td colspan='10' class='no-data'>No equipment issues found for the selected period.</td>
                    </tr>";
    }
    
    $html .= "
                </tbody>
            </table>
        </div>
    </div>
    
    <style>
        .report-container { font-family: Arial, sans-serif; }
        .report-header { margin-bottom: 30px; border-bottom: 2px solid #1e5631; padding-bottom: 15px; }
        .report-header h2 { color: #1e5631; margin-bottom: 10px; }
        .summary-cards { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin: 15px 0; }
        .summary-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #1e5631; }
        .summary-card.pending { border-left-color: #ffc107; }
        .summary-card.in-progress { border-left-color: #17a2b8; }
        .summary-card.resolved { border-left-color: #28a745; }
        .summary-value { font-size: 1.5rem; font-weight: bold; color: #1e5631; }
        .summary-label { font-size: 0.9rem; color: #6c757d; margin-top: 5px; }
        .report-table { margin-top: 30px; }
        .table { border-collapse: collapse; width: 100%; font-size: 0.9rem; }
        .table th, .table td { padding: 10px 8px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: top; }
        .table th { background-color: #1e5631; color: white; font-weight: bold; position: sticky; top: 0; }
        .table tr:nth-child(even) { background-color: #f8f9fa; }
        .table tr:hover { background-color: #e8f5e8; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
        .status-badge.pending { background-color: #fff3cd; color: #856404; }
        .status-badge.in-progress { background-color: #d1ecf1; color: #0c5460; }
        .status-badge.resolved { background-color: #d4edda; color: #155724; }
        .status-badge.rejected { background-color: #f8d7da; color: #721c24; }
        .description-cell { max-width: 200px; word-wrap: break-word; }
        .no-data { text-align: center; color: #6c757d; font-style: italic; padding: 20px; }
        
        /* DataTable Integration Styles */
        .dataTables_wrapper { margin-top: 20px; }
        .dataTables_length, .dataTables_filter { margin-bottom: 15px; }
        .dataTables_info, .dataTables_paginate { margin-top: 15px; }
        .dataTables_paginate .paginate_button { padding: 6px 12px; margin: 0 2px; }
        .dataTables_paginate .paginate_button.current { background-color: #1e5631; color: white !important; }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#issuesTable').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                order: [[8, 'desc']], // Order by Reported Date column
                columnDefs: [
                    { orderable: false, targets: [5] }, // Disable sorting on Description column
                    { className: 'text-center', targets: [0, 7] } // Center align specific columns
                ],
                language: {
                    search: 'Search issues:',
                    lengthMenu: 'Show _MENU_ issues per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_ issues',
                    infoEmpty: 'No issues found',
                    infoFiltered: '(filtered from _MAX_ total issues)',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: 'Next',
                        previous: 'Previous'
                    }
                }
            });
        }
    });
    </script>";
    
    return $html;
}
?>
