<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

// Get current admin ID
$adminId = $_SESSION['user_id'];

// Initialize variables for error/success messages
$successMsg = "";
$errorMsg = "";

// Fetch admin information
$sql = "SELECT * FROM dept_admin WHERE AdminID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();
$stmt->close();

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <!-- Icons and Manifest -->
    <link rel="icon" href="../public/assets/logo.webp" type="image/webp" />
    <link rel="apple-touch-icon" href="../public/assets/logo.webp">
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/mobile_fix.css">

        <!-- Include our custom CSS -->
    <link href="../public/css/user_styles/room-browser.css" rel="stylesheet">
    <link href="../public/css/user_styles/room-browser-styles.css" rel="stylesheet">
    <link href="../public/css/user_styles/edit_profile.css" rel="stylesheet">


</head>

<body>
    <div id="app">
        <?php include 'layout/topnav.php'; ?>
        <?php include 'layout/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'components/main_contents/edit_profile.php'; ?>
        </div>

<!-- Change Password Modal -->
<div class="modal" id="changePasswordModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="alert-style-modal">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h5>Change Password</h5>
				<div id="passwordChangeError" class="modal-error-message" style="display: none;"></div>
				<form id="passwordChangeForm" action="./includes/change_password_process.php" method="post">
					<div class="password-form-group">
						<label for="currentPassword">Current Password</label>
						<div class="input-with-icon">
							<input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
							<span class="toggle-password" data-target="currentPassword">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2 10C3.5 6 7 3 10 3C13 3 16.5 6 18 10C16.5 14 13 17 10 17C7 17 3.5 14 2 10Z" stroke="#888" stroke-width="2" fill="none"/>
									<circle cx="10" cy="10" r="3" stroke="#888" stroke-width="2" fill="none"/>
								</svg>
							</span>
						</div>
					</div>
					<div class="password-form-group">
						<label for="newPassword">New Password</label>
						<div class="input-with-icon">
							<input type="password" class="form-control" id="newPassword" name="newPassword" required>
							<span class="toggle-password" data-target="newPassword">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2 10C3.5 6 7 3 10 3C13 3 16.5 6 18 10C16.5 14 13 17 10 17C7 17 3.5 14 2 10Z" stroke="#888" stroke-width="2" fill="none"/>
									<circle cx="10" cy="10" r="3" stroke="#888" stroke-width="2" fill="none"/>
								</svg>
							</span>
						</div>
						<small class="password-hint">Password must be at least 8 characters long</small>
					</div>
					<div class="password-form-group">
						<label for="confirmPassword">Confirm New Password</label>
						<div class="input-with-icon">
							<input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
							<span class="toggle-password" data-target="confirmPassword">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2 10C3.5 6 7 3 10 3C13 3 16.5 6 18 10C16.5 14 13 17 10 17C7 17 3.5 14 2 10Z" stroke="#888" stroke-width="2" fill="none"/>
									<circle cx="10" cy="10" r="3" stroke="#888" stroke-width="2" fill="none"/>
								</svg>
							</span>
						</div>
					</div>
					<div class="alert-actions">
						<button type="button" class="btn-action btn-secondary" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn-action btn-primary">Update Password</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<style>
    :root {
        --primary-color: #1e5631;
        --secondary-color: #d4af37;
        --danger-color: #dc3545;
        --success-color: #28a745;
        --info-color: #17a2b8;
        --warning-color: #ffc107;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
    }

    .profile-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem;
        background-color: #fff;
    }

    .message-container {
        margin-bottom: 2rem;
    }

    .profile-card {
        background: white;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .card-header {
        padding: 2rem;
        background-color: #fff;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .card-header h2 {
        margin: 0;
        color: #1a1a1a;
        font-size: 1.375rem;
        font-weight: 600;
        line-height: 1.3;
    }

    .card-header p {
        margin: 0.25rem 0 0;
        color: #666;
        font-size: 0.875rem;
        font-weight: normal;
        opacity: 0.85;
    }

    .header-content {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .bold-personal {
        font-weight: 600;
    }

    .admin-notice {
        margin: 1.5rem 2rem;
        padding: 1rem 1.25rem;
        border-radius: 4px;
        background-color: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .admin-notice i {
        font-size: 1rem;
    }

    .profile-form {
        padding: 2rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #666;
        font-size: 0.875rem;
    }

    .input-with-icon {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-with-icon input {
        width: 100%;
        padding: 0.625rem 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-size: 0.9375rem;
        background-color: #f8f9fa;
        color: #333;
        transition: all 0.2s ease;
    }

    .input-with-icon input[readonly] {
        background-color: #f8f9fa;
        cursor: default;
        color: #495057;
    }

    .input-with-icon input:focus {
        border-color: #80bdff;
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .verified-badge {
        position: absolute;
        right: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
        color: #28a745;
        font-size: 0.75rem;
    }

    .verified-badge i {
        font-size: 0.875rem;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d;
        transition: color 0.2s;
        z-index: 2;
        padding: 0.5rem;
    }

    .toggle-password:hover {
        color: var(--primary-color);
    }

    .toggle-password.active svg path,
    .toggle-password.active svg circle {
        stroke: var(--primary-color);
    }

    .security-section {
        padding: 2rem;
    }

    .security-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 0.625rem 1.25rem;
        border-radius: 4px;
        border: 1px solid transparent;
        font-weight: 500;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-secondary {
        background-color: #f8f9fa;
        border-color: #e0e0e0;
        color: #333;
    }

    .btn-danger {
        background-color: #fff;
        border-color: #dc3545;
        color: #dc3545;
    }

    .btn-action:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .btn-secondary:hover {
        background-color: #e9ecef;
        border-color: #ddd;
    }

    .btn-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }

    .btn-action i {
        font-size: 1rem;
    }

    @media (max-width: 768px) {
        .profile-container {
            margin: 1rem auto;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .security-actions {
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
        }
    }
</style>

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

				// Optionally, check for common passwords or add more rules here
			});
		}

		// Check URL for error parameters
		const urlParams = new URLSearchParams(window.location.search);
		const errorType = urlParams.get('error_type');
		const errorMsg = urlParams.get('error_msg');

		// Handle error messages from redirects
		if (errorType === 'password_change' && errorMsg) {
			$('#changePasswordModal').modal('show');
			const errorElement = document.getElementById('passwordChangeError');
			errorElement.textContent = decodeURIComponent(errorMsg);
			errorElement.style.display = 'block';
		}

		// Current password focus handling
		if (currentPasswordInput) {
			$('#changePasswordModal').on('shown.bs.modal', function() {
				currentPasswordInput.focus();
				document.getElementById('passwordChangeError').style.display = 'none';
			});
		}

		// Add this function to your existing script
		function setupErrorMessageFade() {
			const errorMessages = document.querySelectorAll('.modal-error-message');
			errorMessages.forEach(message => {
				if (message.style.display !== 'none') {
					setTimeout(function() {
						message.classList.add('fade-out');
						setTimeout(function() {
							message.style.display = 'none';
							message.classList.remove('fade-out');
						}, 1000);
					}, 5000);
				}
			});
		}
		if (errorType === 'password_change' && errorMsg) {
			setupErrorMessageFade();
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
	});
</script>


        <?php
        // Close the database connection
        $conn->close();
        ?>
    </div>
    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/custom_alert.js"></script>

    <script>
        // Show alerts on page load if messages exist
        window.onload = function() {
            <?php
            if (isset($_SESSION['success_message'])) {
                echo 'showCustomAlert("' . addslashes($_SESSION['success_message']) . '", "success");';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo 'showCustomAlert("' . addslashes($_SESSION['error_message']) . '", "error");';
                unset($_SESSION['error_message']);
            }
            ?>
        }
    </script>

    <script>
        // Function to toggle dropdown menus
        function toggleIcon(element) {
            // Toggle active class on the clicked dropdown
            element.classList.toggle('active');
            
            // Toggle the plus/minus icon
            const icon = element.querySelector('.toggle-icon i');
            icon.classList.toggle('mdi-plus');
            icon.classList.toggle('mdi-minus');
            
            // Toggle the submenu visibility
            const submenu = element.nextElementSibling;
            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
            } else {
                // Close all other dropdowns first
                const allDropdowns = document.querySelectorAll('.menu-list .dropdown');
                allDropdowns.forEach(dropdown => {
                    if (dropdown !== element) {
                        dropdown.classList.remove('active');
                        const dropdownIcon = dropdown.querySelector('.toggle-icon i');
                        dropdownIcon.classList.remove('mdi-minus');
                        dropdownIcon.classList.add('mdi-plus');
                        const dropdownSubmenu = dropdown.nextElementSibling;
                        dropdownSubmenu.style.display = 'none';
                    }
                });
                
                submenu.style.display = 'block';
            }
        }
    </script>

<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>