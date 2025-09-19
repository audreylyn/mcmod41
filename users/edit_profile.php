<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

// Get current user ID and role
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Initialize variables for error/success messages
$successMsg = "";
$errorMsg = "";

// Fetch user information based on role
if ($userRole == 'Student') {
    $sql = "SELECT * FROM student WHERE StudentID = ?";
    $tableName = "student";
    $idField = "StudentID";
} else if ($userRole == 'Teacher') {
    $sql = "SELECT * FROM teacher WHERE TeacherID = ?";
    $tableName = "teacher";
    $idField = "TeacherID";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// Include room status handler to automatically update room statuses
require_once '../auth/room_status_handler.php';
?>

<?php include "../partials/header.php"; ?>
<link href="../public/css/user_styles/edit_profile.css" rel="stylesheet">
<link href="../public/css/user_styles/edit_profile2.css" rel="stylesheet">


<?php include "layout/sidebar.php"; ?>
<?php include "layout/topnav.php"; ?>

<?php include "components/main_contents/edit_profile.php"; ?>
<?php include "components/modals/change_password_modal.php"; ?>
<?php include "components/modals/delete_account_modal.php"; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password match validation
        const passwordForm = document.getElementById('passwordChangeForm');
        const newPassword = document.getElementById('newPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        const currentPasswordInput = document.getElementById('currentPassword');

        if (passwordForm) {
            passwordForm.addEventListener('submit', function(event) {
                // Get values
                const currentVal = currentPasswordInput.value;
                const newVal = newPassword.value;
                const confirmVal = confirmPassword.value;
                const errorElement = document.getElementById('passwordChangeError');
                errorElement.style.display = 'none';
                errorElement.textContent = '';

                // Check if new password matches confirmation
                if (newVal !== confirmVal) {
                    event.preventDefault();
                    errorElement.textContent = 'New password and confirmation do not match.';
                    errorElement.style.display = 'block';
                    return;
                }

                // Check password length
                if (newVal.length < 8) {
                    event.preventDefault();
                    errorElement.textContent = 'Password must be at least 8 characters long.';
                    errorElement.style.display = 'block';
                    return;
                }

                // Check if new password is same as current
                if (currentVal === newVal) {
                    event.preventDefault();
                    errorElement.textContent = 'New password must be different from your current password.';
                    errorElement.style.display = 'block';
                    return;
                }

                // Check if new password is too similar (e.g., only 1 char different)
                let diffCount = 0;
                for (let i = 0; i < Math.max(currentVal.length, newVal.length); i++) {
                    if (currentVal[i] !== newVal[i]) diffCount++;
                }
                if (diffCount <= 1 && currentVal.length === newVal.length) {
                    event.preventDefault();
                    errorElement.textContent = 'New password is too similar to your current password.';
                    errorElement.style.display = 'block';
                    return;
                }
            });
        }

        // Delete account confirmation
        const deleteAccountForm = document.getElementById('deleteAccountForm');
        if (deleteAccountForm) {
            deleteAccountForm.addEventListener('submit', function(event) {
                if (!confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')) {
                    event.preventDefault();
                }
            });
        }

        // Check URL for error parameters
        const urlParams = new URLSearchParams(window.location.search);
        const errorType = urlParams.get('error_type');
        const errorMsg = urlParams.get('error_msg');

        // Handle error messages from redirects
        if (errorType === 'password_change' && errorMsg) {
            // Show password change modal with error
            $('#changePasswordModal').modal('show');
            const errorElement = document.getElementById('passwordChangeError');
            errorElement.textContent = decodeURIComponent(errorMsg);
            errorElement.style.display = 'block';
        } else if (errorType === 'delete_account' && errorMsg) {
            // Show delete account modal with error
            $('#deleteAccountModal').modal('show');
            const errorElement = document.getElementById('deleteAccountError');
            errorElement.textContent = decodeURIComponent(errorMsg);
            errorElement.style.display = 'block';
        }

        // Current password focus handling
        if (currentPasswordInput) {
            $('#changePasswordModal').on('shown.bs.modal', function() {
                currentPasswordInput.focus();
                // Clear any previous error
                document.getElementById('passwordChangeError').style.display = 'none';
            });
        }

        // Verify password focus handling
        const verifyPasswordInput = document.getElementById('verifyPassword');
        if (verifyPasswordInput) {
            $('#deleteAccountModal').on('shown.bs.modal', function() {
                verifyPasswordInput.focus();
                // Clear any previous error
                document.getElementById('deleteAccountError').style.display = 'none';
            });
        }

        // Show/hide password toggle
        document.querySelectorAll('.toggle-password').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const targetId = toggle.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (input) {
                    if (input.type === 'password') {
                        input.type = 'text';
                        toggle.classList.add('active');
                    } else {
                        input.type = 'password';
                        toggle.classList.remove('active');
                    }
                }
            });
        });

        // Add this function to your existing script
        function setupErrorMessageFade() {
            // Get all error message elements
            const errorMessages = document.querySelectorAll('.modal-error-message');

            errorMessages.forEach(message => {
                if (message.style.display !== 'none') {
                    // Set a timeout to fade the message after 5 seconds
                    setTimeout(function() {
                        // Add fade-out class
                        message.classList.add('fade-out');

                        // Hide after animation completes
                        setTimeout(function() {
                            message.style.display = 'none';
                            message.classList.remove('fade-out');
                        }, 1000);
                    }, 5000);
                }
            });
        }

        // Call the function when showing error messages
        if (errorType === 'password_change' && errorMsg || errorType === 'delete_account' && errorMsg) {
            setupErrorMessageFade();
        }
    });
</script>

<!-- Chatbot Widget -->
<?php include "layout/chatbot-layout.php"; ?>
<?php include "../partials/footer.php"; ?>

