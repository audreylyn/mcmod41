<?php
/**
 * Email validation helper functions
 * Provides centralized email validation across all user tables
 */

/**
 * Check if an email exists across all user tables
 * @param mysqli $conn Database connection
 * @param string $email Email to check
 * @param string $excludeTable Optional table to exclude from check
 * @param int $excludeId Optional ID to exclude from check (for updates)
 * @return array ['exists' => bool, 'table' => string]
 */
function checkEmailExists($conn, $email, $excludeTable = null, $excludeId = null) {
    $tables = [
        'dept_admin' => ['email_field' => 'Email', 'id_field' => 'AdminID', 'display_name' => 'department admin'],
        'teacher' => ['email_field' => 'Email', 'id_field' => 'TeacherID', 'display_name' => 'teacher'],
        'student' => ['email_field' => 'Email', 'id_field' => 'StudentID', 'display_name' => 'student'],
        'registrar' => ['email_field' => 'Reg_Email', 'id_field' => 'regid', 'display_name' => 'registrar']
    ];
    
    foreach ($tables as $tableName => $tableInfo) {
        // Skip excluded table when updating
        if ($excludeTable === $tableName && $excludeId !== null) {
            $sql = "SELECT COUNT(*) FROM {$tableName} WHERE {$tableInfo['email_field']} = ? AND {$tableInfo['id_field']} != ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $email, $excludeId);
        } else {
            $sql = "SELECT COUNT(*) FROM {$tableName} WHERE {$tableInfo['email_field']} = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
        }
        
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            return [
                'exists' => true,
                'table' => $tableInfo['display_name']
            ];
        }
    }
    
    return [
        'exists' => false,
        'table' => null
    ];
}

/**
 * Validate email format and uniqueness
 * @param mysqli $conn Database connection
 * @param string $email Email to validate
 * @param string $excludeTable Optional table to exclude from check
 * @param int $excludeId Optional ID to exclude from check (for updates)
 * @return array ['valid' => bool, 'message' => string]
 */
function validateEmail($conn, $email, $excludeTable = null, $excludeId = null) {
    // Check email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'valid' => false,
            'message' => 'Invalid email format.'
        ];
    }
    
    // Check email uniqueness
    $emailCheck = checkEmailExists($conn, $email, $excludeTable, $excludeId);
    
    if ($emailCheck['exists']) {
        return [
            'valid' => false,
            'message' => "Email already exists in the system as a {$emailCheck['table']} account. Please use a different email."
        ];
    }
    
    return [
        'valid' => true,
        'message' => 'Email is valid and available.'
    ];
}
?>
