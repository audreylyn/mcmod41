<!-- Page content -->
<div class="right_col" role="main">
    <div class="main-content">
        <div class="profile-container">
            <!-- Update message container to exclude password/delete errors -->
            <div class="message-container">
                <?php
                // Only display error messages that aren't related to password change or account deletion
                if (
                    isset($_SESSION['error_message']) &&
                    (!isset($_GET['error_type']) || ($_GET['error_type'] !== 'password_change' && $_GET['error_type'] !== 'delete_account'))
                ) {
                    echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                    unset($_SESSION['error_message']);
                }

                // Display success messages from session
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success alert-auto-fade"><i class="fa fa-check-circle"></i> ' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                    unset($_SESSION['success_message']);
                }
                ?>
            </div>

                        <?php if ($userRole == 'Student'): ?>
            <!-- Account Status Section for Students -->
            <div class="profile-card">
                <div class="card-header bg-modal">
                    <h2 class="bold-personal">Account Status</h2>
                    <p>Your current account standing</p>
                </div>
                <div class="status-section">
                    <?php if ($userData['PenaltyStatus'] === 'banned'): ?>
                        <?php
                        // Get current penalty details
                        $penaltyStmt = $conn->prepare("
                            SELECT p.reason, p.descriptions, p.issued_at, p.expires_at,
                                   CONCAT(da.FirstName, ' ', da.LastName) as issued_by_name
                            FROM penalty p
                            LEFT JOIN dept_admin da ON p.issued_by = da.AdminID
                            WHERE p.student_id = ? AND p.status = 'active'
                            ORDER BY p.issued_at DESC LIMIT 1
                        ");
                        $penaltyStmt->bind_param("i", $userId);
                        $penaltyStmt->execute();
                        $penaltyResult = $penaltyStmt->get_result();
                        $penaltyInfo = $penaltyResult->fetch_assoc();
                        ?>
                        
                        <div class="account-status-card banned">
                            <div class="status-content">
                                <div class="status-header">
                                    <div class="status-text">
                                        <h4>Account Banned</h4>
                                        <p>Your account has been temporarily suspended</p>
                                    </div>
                                </div>
                                
                                <?php if ($penaltyInfo): ?>
                                <div class="penalty-timeline">
                                    <div class="timeline-start">
                                        <div class="timeline-date">
                                            <?php echo date('M d, Y', strtotime($penaltyInfo['issued_at'])); ?>
                                        </div>
                                        <div class="timeline-label">Issued</div>
                                    </div>
                                    <div class="timeline-line"></div>
                                    <?php if ($penaltyInfo['expires_at']): ?>
                                    <div class="timeline-end">
                                        <div class="timeline-date">
                                            <?php echo date('M d, Y', strtotime($penaltyInfo['expires_at'])); ?>
                                        </div>
                                        <div class="timeline-label">Expires</div>
                                    </div>
                                    <?php else: ?>
                                    <div class="timeline-end">
                                        <div class="timeline-date">
                                            Indefinite
                                        </div>
                                        <div class="timeline-label">Duration</div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="penalty-details">
                                    <div class="detail-row">
                                        <div class="detail-label">Reason</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($penaltyInfo['reason']); ?></div>
                                    </div>
                                    
                                    <?php if ($penaltyInfo['descriptions']): ?>
                                    <div class="detail-row">
                                        <div class="detail-label">Details</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($penaltyInfo['descriptions']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="detail-row">
                                        <div class="detail-label">Issued by</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($penaltyInfo['issued_by_name']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="ban-restrictions">
                                    <h5><i class="fa fa-exclamation-triangle"></i> Restrictions</h5>
                                    <ul>
                                        <li>Cannot make room reservations</li>
                                        <li>Cannot report equipment issues</li>
                                        <li>Limited access to system features</li>
                                    </ul>
                                </div>
                                
                                <div class="contact-info">
                                    <i class="fa fa-info-circle"></i>
                                    <strong>Need help?</strong> Contact your department administrator to appeal this decision.
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="account-status-card active">
                            <div class="status-content">
                                <div class="status-header">
                                    <div class="status-text">
                                        <h4>Account Active</h4>
                                        <p>Your account is in good standing</p>
                                    </div>
                                </div>
                                
                                <div class="active-benefits">
                                    <h5><i class="fa fa-check"></i> Available Services</h5>
                                    <div class="benefits-grid">
                                        <div class="benefit-item">
                                            <div class="benefit-icon">
                                                <i class="fa fa-bookmark"></i>
                                            </div>
                                            <div class="benefit-text">Make room reservations</div>
                                        </div>
                                        <div class="benefit-item">
                                            <div class="benefit-icon">
                                                <i class="fa fa-wrench"></i>
                                            </div>
                                            <div class="benefit-text">Report equipment issues</div>
                                        </div>
                                        <div class="benefit-item">
                                            <div class="benefit-icon">
                                                <i class="fa fa-th"></i>
                                            </div>
                                            <div class="benefit-text">Full system access</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="profile-card">
                <div class="card-header bg-modal">
                    <h2 class="bold-personal">Personal Information</h2>
                    <p>View your profile information</p>
                </div>

                <!-- Notice about contacting department admin -->
                <div class="admin-notice alert alert-info">
                    <i class="fa fa-info-circle"></i> To update your profile information, please contact your department administrator.
                </div>

                <div class="profile-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First name</label>
                            <div class="input-with-icon">
                                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($userData['FirstName'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="lastName">Last name</label>
                            <div class="input-with-icon">
                                <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($userData['LastName'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-with-icon">
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['Email'] ?? ''); ?>" readonly>
                                <span class="verified-badge">
                                    <i class="fa fa-check-circle"></i> Verified
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="department">Department</label>
                            <div class="input-with-icon">
                                <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($userData['Department'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <?php if ($userRole == 'Student'): ?>
                        <div class="form-group">
                            <label for="program">Program</label>
                            <div class="input-with-icon">
                                <input type="text" id="program" name="program" value="<?php echo htmlspecialchars($userData['Program'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="yearSection">Year & Section</label>
                            <div class="input-with-icon">
                                <input type="text" id="yearSection" name="yearSection" value="<?php echo htmlspecialchars($userData['YearSection'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        <?php elseif ($userRole == 'Teacher'): ?>
                        <!-- Teacher-specific fields can be added here in the future if needed -->
                        <?php endif; ?>
                    </div>
                </div>
            </div>



            <div class="profile-card">
                <div class="card-header bg-modal">
                    <h2 class="bold-personal">Password & Security</h2>
                    <p>Update your password and security settings</p>
                </div>
                <div class="security-section">
                    <div class="security-actions">
                        <button type="button" class="btn btn-action btn-secondary" data-toggle="modal" data-target="#changePasswordModal">
                            <i class="fa fa-lock mr-2"></i> Change Password
                        </button>
                        <button type="button" class="btn btn-action btn-danger" data-toggle="modal" data-target="#deleteAccountModal">
                            <i class="fa fa-trash mr-2"></i> Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>