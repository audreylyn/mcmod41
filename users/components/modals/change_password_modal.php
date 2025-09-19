<!-- Change Password Modal -->
<div class="modal" id="changePasswordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="alert-style-modal">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5>Change Password</h5>
                <!-- Add error message display -->
                <div id="passwordChangeError" class="modal-error-message" style="display: none;"></div>
                <form id="passwordChangeForm" action="change_password_process.php" method="post">
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