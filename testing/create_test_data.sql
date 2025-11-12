-- =========================
-- Test Data for Bug Verification
-- =========================

-- Create test users for different departments
INSERT INTO dept_admin (FirstName, LastName, Email, Password, Department) VALUES
('John', 'Admin', 'admin.business@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Business Administration'),
('Jane', 'Admin', 'admin.accounting@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Accountancy');

-- Create test teachers in different departments
INSERT INTO teacher (FirstName, LastName, Email, Password, Department, AdminID) VALUES
('Bob', 'Teacher', 'teacher.business@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Business Administration', 1),
('Alice', 'Teacher', 'teacher.accounting@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Accountancy', 2);

-- Create test students in different departments
INSERT INTO student (FirstName, LastName, Email, Password, Department, Program, YearSection, AdminID) VALUES
('Mike', 'Student', 'student.business@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Business Administration', 'BSBA', '4A', 1),
('Sarah', 'Student', 'student.accounting@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Accountancy', 'BSA', '3B', 2),
('Tom', 'Student', 'student.banned@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Business Administration', 'BSBA', '2A', 1);

-- Create test penalty with expired date (for testing expired penalty bug)
INSERT INTO penalty (student_id, reason, descriptions, penalty_type, issued_by, expires_at, status) VALUES
((SELECT StudentID FROM student WHERE Email = 'student.banned@test.com'), 
 'Test expired penalty', 
 'This penalty should be expired', 
 'ban', 
 1, 
 '2025-01-01 00:00:00', 
 'active');

-- Update student with expired penalty status (to test the bug)
UPDATE student 
SET PenaltyStatus = 'banned', PenaltyExpiresAt = '2025-01-01 00:00:00' 
WHERE Email = 'student.banned@test.com';

-- Create test room maintenance with various scenarios
INSERT INTO room_maintenance (room_id, reason, admin_id, start_date, end_date) VALUES
-- Expired maintenance (should auto-update but might not due to timezone issues)
(1, 'Test expired maintenance', 1, '2025-11-01 10:00:00', '2025-11-10 10:00:00'),
-- Maintenance expiring soon (within 24 hours)
(2, 'Test expiring soon', 1, NOW(), DATE_ADD(NOW(), INTERVAL 12 HOUR)),
-- Maintenance expiring in 3 days
(3, 'Test expiring in 3 days', 1, NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY));

-- Update room status to maintenance for testing
UPDATE rooms SET RoomStatus = 'maintenance' WHERE id IN (1, 2, 3);

-- =========================
-- Test Scenarios Setup
-- =========================

-- Scenario 1: Create duplicate email scenario (for testing email validation bug)
-- This will be used to test if AJAX admin creation allows duplicates
-- Run this AFTER testing the duplicate email bug:
-- INSERT INTO dept_admin (FirstName, LastName, Email, Password, Department) VALUES
-- ('Duplicate', 'Admin', 'teacher.business@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Accountancy');

-- Scenario 2: Cross-department penalty (for testing cross-dept access bug)
-- This simulates Business admin penalizing Accountancy student
INSERT INTO penalty (student_id, reason, descriptions, penalty_type, issued_by, expires_at, status) VALUES
((SELECT StudentID FROM student WHERE Email = 'student.accounting@test.com'), 
 'Cross-department penalty test', 
 'Business admin penalizing Accountancy student', 
 'ban', 
 (SELECT AdminID FROM dept_admin WHERE Department = 'Business Administration' LIMIT 1), 
 DATE_ADD(NOW(), INTERVAL 7 DAY), 
 'active');

-- Scenario 3: Create test reservations for race condition testing
INSERT INTO room_requests (user_id, user_type, room_id, request_date, start_time, end_time, purpose, status) VALUES
((SELECT StudentID FROM student WHERE Email = 'student.business@test.com'), 
 'Student', 
 5, 
 CURDATE() + INTERVAL 1 DAY, 
 '10:00:00', 
 '12:00:00', 
 'Test reservation for race condition', 
 'approved');

-- =========================
-- Verification Queries
-- =========================

-- Check for plain text passwords (should return hashed passwords starting with $2y$)
-- SELECT regid, Reg_Email, Reg_Password FROM registrar;

-- Check for duplicate emails (should return empty if no duplicates)
-- SELECT email, COUNT(*) as count FROM (
--     SELECT Email as email FROM teacher
--     UNION ALL SELECT Email as email FROM student  
--     UNION ALL SELECT Email as email FROM dept_admin
--     UNION ALL SELECT Reg_Email as email FROM registrar
-- ) combined GROUP BY email HAVING COUNT(*) > 1;

-- Check for cross-department penalties (should return records if bug exists)
-- SELECT s.Department as student_dept, da.Department as admin_dept, p.reason
-- FROM penalty p
-- JOIN student s ON p.student_id = s.StudentID  
-- JOIN dept_admin da ON p.issued_by = da.AdminID
-- WHERE s.Department != da.Department AND p.status = 'active';

-- Check for expired penalties still active (should return records if bug exists)
-- SELECT CONCAT(s.FirstName, ' ', s.LastName) as student_name, s.PenaltyStatus, s.PenaltyExpiresAt
-- FROM student s
-- WHERE s.PenaltyStatus = 'banned' AND s.PenaltyExpiresAt < NOW();

-- =========================
-- Login Credentials for Testing
-- =========================
-- All test accounts use password: "password"
-- 
-- Registrar: registrar@gmail.com / mcisuperadmin42 (or whatever you set)
-- Business Admin: admin.business@test.com / password
-- Accounting Admin: admin.accounting@test.com / password
-- Business Teacher: teacher.business@test.com / password
-- Accounting Teacher: teacher.accounting@test.com / password
-- Business Student: student.business@test.com / password
-- Accounting Student: student.accounting@test.com / password
-- Banned Student: student.banned@test.com / password (has expired penalty)
