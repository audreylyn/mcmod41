
<!-- Delete Account Modal -->
<div class="modal" id="deleteAccountModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="alert-style-modal delete-modal">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5>Delete Account</h5>
                <p class="delete-warning">Warning: This action cannot be undone. All your data will be permanently deleted.</p>
                <!-- Add error message display -->
                <div id="deleteAccountError" class="modal-error-message" style="display: none;"></div>
                <form id="deleteAccountForm" action="delete_account_process.php" method="post">
                    <div class="password-form-group">
                        <label for="verifyPassword">Enter your password to confirm</label>
                        <div class="input-with-icon">
                            <input type="password" class="form-control" id="verifyPassword" name="verifyPassword" required>
                            <span class="toggle-password" data-target="verifyPassword">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 10C3.5 6 7 3 10 3C13 3 16.5 6 18 10C16.5 14 13 17 10 17C7 17 3.5 14 2 10Z" stroke="#888" stroke-width="2" fill="none"/>
                                    <circle cx="10" cy="10" r="3" stroke="#888" stroke-width="2" fill="none"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="alert-actions">
                        <button type="button" class="btn-action btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-action btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>