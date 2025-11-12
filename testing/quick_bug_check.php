<?php
/**
 * Quick Bug Verification Script
 * Run this to quickly check for several identified bugs
 */

require_once '../auth/dbh.inc.php';

echo "<h1>🔍 Bug Verification Results</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .bug-found { color: red; font-weight: bold; }
    .bug-fixed { color: green; font-weight: bold; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
    pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
</style>\n";

$conn = db();

// Test 1: Check for plain text passwords
echo "<div class='test-section'>";
echo "<h2>Test 1: Registrar Password Storage</h2>";
$stmt = $conn->prepare("SELECT regid, Reg_Email, Reg_Password FROM registrar LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $password = $row['Reg_Password'];
    if (strlen($password) < 20 || !str_starts_with($password, '$2y$')) {
        echo "<p class='bug-found'>🚨 BUG FOUND: Plain text password detected!</p>";
        echo "<p>Password: " . htmlspecialchars($password) . "</p>";
    } else {
        echo "<p class='bug-fixed'>✅ FIXED: Password is properly hashed</p>";
    }
}
echo "</div>";

// Test 2: Check for duplicate emails across tables
echo "<div class='test-section'>";
echo "<h2>Test 2: Duplicate Email Check</h2>";
$duplicateQuery = "
    SELECT email, COUNT(*) as count FROM (
        SELECT Email as email FROM teacher
        UNION ALL SELECT Email as email FROM student  
        UNION ALL SELECT Email as email FROM dept_admin
        UNION ALL SELECT Reg_Email as email FROM registrar
    ) combined 
    GROUP BY email 
    HAVING COUNT(*) > 1
";
$result = $conn->query($duplicateQuery);
if ($result->num_rows > 0) {
    echo "<p class='bug-found'>🚨 BUG FOUND: Duplicate emails detected!</p>";
    while ($row = $result->fetch_assoc()) {
        echo "<p>Email: " . htmlspecialchars($row['email']) . " (appears " . $row['count'] . " times)</p>";
    }
} else {
    echo "<p class='bug-fixed'>✅ NO DUPLICATES: All emails are unique across tables</p>";
}
echo "</div>";

// Test 3: Check timezone settings
echo "<div class='test-section'>";
echo "<h2>Test 3: Timezone Consistency</h2>";
$timezoneQuery = "SELECT NOW() as db_time, @@session.time_zone as db_timezone";
$result = $conn->query($timezoneQuery);
if ($row = $result->fetch_assoc()) {
    echo "<p>Database Time: " . $row['db_time'] . "</p>";
    echo "<p>Database Timezone: " . $row['db_timezone'] . "</p>";
    echo "<p>PHP Timezone: " . date_default_timezone_get() . "</p>";
    
    if (date_default_timezone_get() !== 'Asia/Manila') {
        echo "<p class='bug-found'>🚨 POTENTIAL ISSUE: PHP timezone not set to Asia/Manila</p>";
    } else {
        echo "<p class='bug-fixed'>✅ PHP timezone correctly set</p>";
    }
}
echo "</div>";

// Test 4: Check for cross-department penalties
echo "<div class='test-section'>";
echo "<h2>Test 4: Cross-Department Penalty Access</h2>";
$crossDeptQuery = "
    SELECT s.Department as student_dept, 
           da.Department as admin_dept,
           p.reason,
           CONCAT(s.FirstName, ' ', s.LastName) as student_name,
           CONCAT(da.FirstName, ' ', da.LastName) as admin_name
    FROM penalty p
    JOIN student s ON p.student_id = s.StudentID  
    JOIN dept_admin da ON p.issued_by = da.AdminID
    WHERE s.Department != da.Department AND p.status = 'active'
";
$result = $conn->query($crossDeptQuery);
if ($result->num_rows > 0) {
    echo "<p class='bug-found'>🚨 BUG FOUND: Cross-department penalties detected!</p>";
    while ($row = $result->fetch_assoc()) {
        echo "<p>Admin " . htmlspecialchars($row['admin_name']) . " (" . htmlspecialchars($row['admin_dept']) . 
             ") penalized student " . htmlspecialchars($row['student_name']) . " (" . htmlspecialchars($row['student_dept']) . ")</p>";
    }
} else {
    echo "<p class='bug-fixed'>✅ NO CROSS-DEPT PENALTIES: All penalties issued within correct departments</p>";
}
echo "</div>";

// Test 5: Check for expired penalties still active
echo "<div class='test-section'>";
echo "<h2>Test 5: Expired Penalties Still Active</h2>";
$expiredPenaltyQuery = "
    SELECT CONCAT(s.FirstName, ' ', s.LastName) as student_name,
           s.PenaltyStatus,
           s.PenaltyExpiresAt,
           p.reason
    FROM student s
    LEFT JOIN penalty p ON s.StudentID = p.student_id AND p.status = 'active'
    WHERE s.PenaltyStatus = 'banned' 
    AND s.PenaltyExpiresAt IS NOT NULL 
    AND s.PenaltyExpiresAt < NOW()
";
$result = $conn->query($expiredPenaltyQuery);
if ($result->num_rows > 0) {
    echo "<p class='bug-found'>🚨 BUG FOUND: Students with expired penalties still marked as banned!</p>";
    while ($row = $result->fetch_assoc()) {
        echo "<p>Student: " . htmlspecialchars($row['student_name']) . 
             " - Expired: " . $row['PenaltyExpiresAt'] . 
             " - Status: " . $row['PenaltyStatus'] . "</p>";
    }
} else {
    echo "<p class='bug-fixed'>✅ NO EXPIRED PENALTIES: All banned students have valid penalty periods</p>";
}
echo "</div>";

// Test 6: Check for maintenance periods with timezone issues
echo "<div class='test-section'>";
echo "<h2>Test 6: Maintenance Timezone Issues</h2>";
$maintenanceQuery = "
    SELECT r.room_name,
           rm.end_date,
           rm.end_date < NOW() as is_expired,
           r.RoomStatus
    FROM room_maintenance rm
    JOIN rooms r ON rm.room_id = r.id
    WHERE rm.end_date IS NOT NULL
    ORDER BY rm.end_date DESC
    LIMIT 5
";
$result = $conn->query($maintenanceQuery);
echo "<p>Recent maintenance periods (check for timezone consistency):</p>";
echo "<pre>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Room: " . $row['room_name'] . 
             " | End: " . $row['end_date'] . 
             " | Expired: " . ($row['is_expired'] ? 'YES' : 'NO') . 
             " | Status: " . $row['RoomStatus'] . "\n";
    }
} else {
    echo "No maintenance records found.\n";
}
echo "</pre>";
echo "</div>";

echo "<h2>🎯 Summary</h2>";
echo "<p>Run this script after making changes to verify bug fixes.</p>";
echo "<p>For manual testing scenarios, check the <code>bug_test_scenarios.md</code> file.</p>";

$conn->close();
?>
