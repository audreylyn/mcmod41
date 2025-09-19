<?php
// Get user info from session
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Determine ID field based on user role
$idField = ($userRole === 'Student') ? 'student_id' : 'teacher_id';

// Fetch all equipment reports made by this user
$sql = "SELECT ei.*, e.name as equipment_name, r.room_name, b.building_name
        FROM equipment_issues ei
        JOIN equipment_units eu ON ei.unit_id = eu.unit_id
        JOIN equipment e ON eu.equipment_id = e.id
        JOIN rooms r ON eu.room_id = r.id
        JOIN buildings b ON r.building_id = b.id
        WHERE ei.$idField = ?
        ORDER BY ei.reported_at DESC";

// reference_number is handled by the database (set by a BEFORE INSERT trigger)
// and existing rows should already have reference numbers populated by migrations.
// No runtime schema changes are performed here.

// Check if we need to add rejection_reason column
$checkRejectionColumnSql = "SHOW COLUMNS FROM equipment_issues LIKE 'rejection_reason'";
$rejectionColumnExists = $conn->query($checkRejectionColumnSql)->num_rows > 0;

if (!$rejectionColumnExists) {
    // Add rejection_reason column to the table
    $alterTableSql = "ALTER TABLE equipment_issues ADD COLUMN rejection_reason TEXT DEFAULT NULL";
    $conn->query($alterTableSql);
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$reports = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Helper function for status badge
function getStatusBadge($status)
{
    switch ($status) {
        case 'pending':
            return '<span class="status-tag pending">PENDING</span>';
        case 'in_progress':
            return '<span class="status-tag in-progress">IN PROGRESS</span>';
        case 'resolved':
            return '<span class="status-tag resolved">RESOLVED</span>';
        case 'rejected':
            return '<span class="status-tag rejected">REJECTED</span>';
        default:
            return '<span class="status-tag">' . strtoupper($status) . '</span>';
    }
}

// Helper function for condition badge
function getConditionBadge($condition)
{
    switch ($condition) {
        case 'working':
            return '<span class="condition-tag working">WORKING</span>';
        case 'needs_repair':
            return '<span class="condition-tag needs-repair">NEEDS REPAIR</span>';
        case 'maintenance':
            return '<span class="condition-tag maintenance">MAINTENANCE</span>';
        case 'missing':
            return '<span class="condition-tag missing">MISSING</span>';
        default:
            return '<span class="condition-tag">' . strtoupper($condition) . '</span>';
    }
}
