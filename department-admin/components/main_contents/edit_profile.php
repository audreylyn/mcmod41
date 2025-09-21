<div class="profile-container">
    <div class="message-container">
        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success alert-auto-fade"><i class="fa fa-check-circle"></i> ' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        ?>
    </div>

    <div class="profile-card">
        <div class="card-header bg-modal">
            <div class="header-content">
                <h2>Personal Information</h2>
                <p>View your profile information</p>
            </div>
        </div>
        <div class="admin-notice alert alert-info">
            <i class="fa fa-info-circle"></i> To update your profile information, please contact the system administrator.
        </div>
        <div class="profile-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName">First name</label>
                    <div class="input-with-icon">
                        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($adminData['FirstName'] ?? ''); ?>" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label for="lastName">Last name</label>
                    <div class="input-with-icon">
                        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($adminData['LastName'] ?? ''); ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($adminData['Email'] ?? ''); ?>" readonly>
                        <span class="verified-badge">
                            <i class="fa fa-check-circle"></i> Verified
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <div class="input-with-icon">
                        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($adminData['Department'] ?? ''); ?>" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="profile-card">
        <div class="card-header bg-modal">
            <div class="header-content">
                <h2>Password & Security</h2>
                <p>Update your password and security settings</p>
            </div>
        </div>
        <div class="security-section">
            <div class="security-actions">
                <button type="button" class="btn btn-action btn-secondary" data-toggle="modal" data-target="#changePasswordModal">
                    <i class="fa fa-lock mr-2"></i> Change Password
                </button>
            </div>
        </div>
    </div>
</div>