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
    $format = $_POST['format'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    if (empty($report_type) || empty($format) || empty($start_date) || empty($end_date)) {
        http_response_code(400);
        echo "Missing required parameters";
        exit;
    }
    
    if ($format === 'pdf') {
        generatePDFReport($conn, $admin_department, $report_type, $start_date, $end_date);
    } elseif ($format === 'csv') {
        generateCSVReport($conn, $admin_department, $report_type, $start_date, $end_date);
    } else {
        http_response_code(400);
        echo "Invalid format";
        exit;
    }
} else {
    http_response_code(405);
    echo "Method not allowed";
}

function generatePDFReport($conn, $department, $report_type, $start_date, $end_date) {
    // For PDF generation, we'll use a simple HTML to PDF approach
    // In a production environment, you might want to use libraries like TCPDF or DOMPDF
    
    $html = generateReportHTML($conn, $department, $report_type, $start_date, $end_date);
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $report_type . '_' . $start_date . '_to_' . $end_date . '.pdf"');
    
    // Simple HTML to PDF conversion (basic approach)
    // In production, use proper PDF libraries
    $pdf_content = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .report-header { border-bottom: 2px solid #1e5631; padding-bottom: 15px; margin-bottom: 20px; }
            .report-header h2 { color: #1e5631; margin: 0; }
            .summary-cards { display: flex; gap: 15px; margin: 20px 0; }
            .summary-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; flex: 1; }
            .summary-value { font-size: 1.5rem; font-weight: bold; color: #1e5631; }
            .summary-label { font-size: 0.9rem; color: #666; margin-top: 5px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #1e5631; color: white; }
            tr:nth-child(even) { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
        {$html}
    </body>
    </html>";
    
    echo $pdf_content;
}

function generateCSVReport($conn, $department, $report_type, $start_date, $end_date) {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $report_type . '_' . $start_date . '_to_' . $end_date . '.csv"');
    
    $data = getReportData($conn, $department, $report_type, $start_date, $end_date);
    
    echo generateCSVContent($data, $report_type, $department, $start_date, $end_date);
}

function generateReportHTML($conn, $department, $report_type, $start_date, $end_date) {
    switch ($report_type) {
        case 'room-utilization':
            return generateRoomUtilizationHTML($conn, $department, $start_date, $end_date);
        case 'booking-requests':
            return generateBookingRequestsHTML($conn, $department, $start_date, $end_date);
        case 'equipment-status':
            return generateEquipmentStatusHTML($conn, $department, $start_date, $end_date);
        default:
            return "<p>Invalid report type</p>";
    }
}

function getReportData($conn, $department, $report_type, $start_date, $end_date) {
    switch ($report_type) {
        case 'room-utilization':
            return getRoomUtilizationData($conn, $department, $start_date, $end_date);
        case 'booking-requests':
            return getBookingRequestsData($conn, $department, $start_date, $end_date);
        case 'equipment-status':
            return getEquipmentStatusData($conn, $department, $start_date, $end_date);
        default:
            return [];
    }
}

function getRoomUtilizationData($conn, $department, $start_date, $end_date) {
    $query = "
        SELECT 
            r.room_name,
            r.room_type,
            b.building_name,
            b.department,
            r.capacity,
            COALESCE(COUNT(rr.RequestID), 0) as total_requests,
            COALESCE(SUM(CASE WHEN rr.Status = 'approved' THEN 1 ELSE 0 END), 0) as approved_requests,
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
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getBookingRequestsData($conn, $department, $start_date, $end_date) {
    $query = "
        SELECT 
            DATE(rr.RequestDate) as request_date,
            COUNT(*) as total_requests,
            SUM(CASE WHEN rr.Status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN rr.Status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN rr.Status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM room_requests rr
        WHERE rr.RequestDate BETWEEN ? AND ?
        AND (
            (rr.StudentID IS NOT NULL AND EXISTS (
                SELECT 1 FROM student s WHERE s.StudentID = rr.StudentID AND s.Department = ?
            ))
            OR 
            (rr.TeacherID IS NOT NULL AND EXISTS (
                SELECT 1 FROM teacher t WHERE t.TeacherID = rr.TeacherID AND t.Department = ?
            ))
        )
        GROUP BY DATE(rr.RequestDate)
        ORDER BY request_date DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $start_date, $end_date, $department, $department);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getEquipmentStatusData($conn, $department, $start_date, $end_date) {
    $query = "
        SELECT 
            e.name as equipment_name,
            e.category,
            COALESCE(r.room_name, 'Unassigned') as room_name,
            COALESCE(b.building_name, 'No Building') as building_name,
            b.department,
            COALESCE(COUNT(eu.unit_id), 0) as total_units,
            COALESCE(SUM(CASE WHEN eu.status = 'working' THEN 1 ELSE 0 END), 0) as working,
            COALESCE(SUM(CASE WHEN eu.status = 'needs_repair' THEN 1 ELSE 0 END), 0) as needs_repair,
            COALESCE(COUNT(ei.id), 0) as reported_issues,
            CASE 
                WHEN COUNT(eu.unit_id) = 0 THEN 'No Units'
                WHEN SUM(CASE WHEN eu.status = 'working' THEN 1 ELSE 0 END) = COUNT(eu.unit_id) THEN 'All Working'
                WHEN SUM(CASE WHEN eu.status = 'needs_repair' THEN 1 ELSE 0 END) > 0 THEN 'Needs Attention'
                ELSE 'Mixed Status'
            END as equipment_status
        FROM equipment e
        LEFT JOIN equipment_units eu ON e.id = eu.equipment_id
        LEFT JOIN rooms r ON eu.room_id = r.id
        LEFT JOIN buildings b ON r.building_id = b.id
        LEFT JOIN equipment_issues ei ON eu.unit_id = ei.unit_id 
            AND ei.reported_at BETWEEN ? AND ?
        WHERE b.department = ? OR b.department IS NULL
        GROUP BY e.id, e.name, e.category, r.room_name, b.building_name, b.department
        ORDER BY reported_issues DESC, e.name
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
    $stmt->bind_param('sss', $start_date, $end_date, $building_dept);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function generateCSVContent($data, $report_type, $department, $start_date, $end_date) {
    $csv_content = "";
    
    // Add header information
    $csv_content .= "{$report_type} Report\n";
    $csv_content .= "Department: {$department}\n";
    $csv_content .= "Period: " . date('M j, Y', strtotime($start_date)) . " to " . date('M j, Y', strtotime($end_date)) . "\n";
    $csv_content .= "Generated: " . date('M j, Y g:i A') . "\n\n";
    
    if (!empty($data)) {
        // Add headers based on first row
        $headers = array_keys($data[0]);
        $csv_headers = array_map(function($header) {
            return ucwords(str_replace('_', ' ', $header));
        }, $headers);
        $csv_content .= implode(',', $csv_headers) . "\n";
        
        // Add data rows
        foreach ($data as $row) {
            $csv_row = array_map(function($cell) {
                // Escape quotes and wrap in quotes if contains comma
                $cell = str_replace('"', '""', $cell);
                if (strpos($cell, ',') !== false || strpos($cell, '"') !== false || strpos($cell, "\n") !== false) {
                    return '"' . $cell . '"';
                }
                return $cell;
            }, array_values($row));
            $csv_content .= implode(',', $csv_row) . "\n";
        }
    } else {
        $csv_content .= "No data available for the selected period.\n";
    }
    
    return $csv_content;
}

function generateExcelContent($data, $report_type, $department, $start_date, $end_date) {
    $excel_content = "
    <html>
    <head>
        <meta charset='UTF-8'>
    </head>
    <body>
        <table>
            <tr>
                <td colspan='10'><strong>" . ucwords(str_replace('-', ' ', $report_type)) . " Report</strong></td>
            </tr>
            <tr>
                <td colspan='10'>Department: {$department}</td>
            </tr>
            <tr>
                <td colspan='10'>Period: " . date('M j, Y', strtotime($start_date)) . " to " . date('M j, Y', strtotime($end_date)) . "</td>
            </tr>
            <tr>
                <td colspan='10'>Generated: " . date('M j, Y g:i A') . "</td>
            </tr>
            <tr><td colspan='10'></td></tr>";
    
    if ($report_type === 'room-utilization') {
        $excel_content .= "
            <tr>
                <th>Department</th>
                <th>Room Name</th>
                <th>Room Type</th>
                <th>Building</th>
                <th>Capacity</th>
                <th>Status</th>
                <th>Total Requests</th>
                <th>Approved Requests</th>
                <th>Approval Rate (%)</th>
            </tr>";
        
        foreach ($data as $row) {
            $excel_content .= "
            <tr>
                <td>{$row['department']}</td>
                <td>{$row['room_name']}</td>
                <td>{$row['room_type']}</td>
                <td>{$row['building_name']}</td>
                <td>{$row['capacity']}</td>
                <td>{$row['usage_status']}</td>
                <td>{$row['total_requests']}</td>
                <td>{$row['approved_requests']}</td>
                <td>{$row['approval_rate']}</td>
            </tr>";
        }
    } elseif ($report_type === 'booking-requests') {
        $excel_content .= "
            <tr>
                <th>Date</th>
                <th>Total Requests</th>
                <th>Pending</th>
                <th>Approved</th>
                <th>Rejected</th>
            </tr>";
        
        foreach ($data as $row) {
            $excel_content .= "
            <tr>
                <td>" . date('M j, Y', strtotime($row['request_date'])) . "</td>
                <td>{$row['total_requests']}</td>
                <td>{$row['pending']}</td>
                <td>{$row['approved']}</td>
                <td>{$row['rejected']}</td>
            </tr>";
        }
    } elseif ($report_type === 'equipment-status') {
        $excel_content .= "
            <tr>
                <th>Department</th>
                <th>Equipment Name</th>
                <th>Category</th>
                <th>Room</th>
                <th>Building</th>
                <th>Status</th>
                <th>Total Units</th>
                <th>Working</th>
                <th>Needs Repair</th>
                <th>Issues Reported</th>
            </tr>";
        
        foreach ($data as $row) {
            $excel_content .= "
            <tr>
                <td>{$row['department']}</td>
                <td>{$row['equipment_name']}</td>
                <td>{$row['category']}</td>
                <td>{$row['room_name']}</td>
                <td>{$row['building_name']}</td>
                <td>{$row['equipment_status']}</td>
                <td>{$row['total_units']}</td>
                <td>{$row['working']}</td>
                <td>{$row['needs_repair']}</td>
                <td>{$row['reported_issues']}</td>
            </tr>";
        }
    }
    
    $excel_content .= "
        </table>
    </body>
    </html>";
    
    return $excel_content;
}

function generateRoomUtilizationHTML($conn, $department, $start_date, $end_date) {
    $data = getRoomUtilizationData($conn, $department, $start_date, $end_date);
    
    $html = "
    <div class='report-header'>
        <h2>Room Utilization Report</h2>
        <p><strong>Department:</strong> {$department}</p>
        <p><strong>Period:</strong> " . date('M j, Y', strtotime($start_date)) . " to " . date('M j, Y', strtotime($end_date)) . "</p>
        <p><strong>Generated:</strong> " . date('M j, Y g:i A') . "</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Room</th>
                <th>Building</th>
                <th>Capacity</th>
                <th>Total Requests</th>
                <th>Approved</th>
                <th>Approval Rate</th>
            </tr>
        </thead>
        <tbody>";
    
    foreach ($data as $room) {
        $html .= "
            <tr>
                <td>{$room['room_name']}</td>
                <td>{$room['building_name']}</td>
                <td>{$room['capacity']}</td>
                <td>{$room['total_requests']}</td>
                <td>{$room['approved_requests']}</td>
                <td>{$room['approval_rate']}%</td>
            </tr>";
    }
    
    $html .= "
        </tbody>
    </table>";
    
    return $html;
}

function generateBookingRequestsHTML($conn, $department, $start_date, $end_date) {
    $data = getBookingRequestsData($conn, $department, $start_date, $end_date);
    
    $html = "
    <div class='report-header'>
        <h2>Booking Requests Summary Report</h2>
        <p><strong>Department:</strong> {$department}</p>
        <p><strong>Period:</strong> " . date('M j, Y', strtotime($start_date)) . " to " . date('M j, Y', strtotime($end_date)) . "</p>
        <p><strong>Generated:</strong> " . date('M j, Y g:i A') . "</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Total Requests</th>
                <th>Pending</th>
                <th>Approved</th>
                <th>Rejected</th>
            </tr>
        </thead>
        <tbody>";
    
    foreach ($data as $day) {
        $formatted_date = date('M j, Y', strtotime($day['request_date']));
        $html .= "
            <tr>
                <td>{$formatted_date}</td>
                <td>{$day['total_requests']}</td>
                <td>{$day['pending']}</td>
                <td>{$day['approved']}</td>
                <td>{$day['rejected']}</td>
            </tr>";
    }
    
    $html .= "
        </tbody>
    </table>";
    
    return $html;
}

function generateEquipmentStatusHTML($conn, $department, $start_date, $end_date) {
    $data = getEquipmentStatusData($conn, $department, $start_date, $end_date);
    
    $html = "
    <div class='report-header'>
        <h2>Equipment Status Report</h2>
        <p><strong>Department:</strong> {$department}</p>
        <p><strong>Period:</strong> " . date('M j, Y', strtotime($start_date)) . " to " . date('M j, Y', strtotime($end_date)) . "</p>
        <p><strong>Generated:</strong> " . date('M j, Y g:i A') . "</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Equipment</th>
                <th>Category</th>
                <th>Location</th>
                <th>Total Units</th>
                <th>Working</th>
                <th>Needs Repair</th>
                <th>Issues Reported</th>
            </tr>
        </thead>
        <tbody>";
    
    foreach ($data as $item) {
        $html .= "
            <tr>
                <td>{$item['equipment_name']}</td>
                <td>{$item['category']}</td>
                <td>{$item['room_name']}, {$item['building_name']}</td>
                <td>{$item['total_units']}</td>
                <td>{$item['working']}</td>
                <td>{$item['needs_repair']}</td>
                <td>{$item['reported_issues']}</td>
            </tr>";
    }
    
    $html .= "
        </tbody>
    </table>";
    
    return $html;
}
?>
