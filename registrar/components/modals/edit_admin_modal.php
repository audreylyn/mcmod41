<?php
$departments = ['Accountancy', 'Business Administration', 'Hospitality Management', 'Education and Arts', 'Criminal Justice'];
?>
<!-- Modal for editing admin -->
<div id="editModal" class="modal">
    <div class="modal-content" style="width: 500px; max-width: 90%;">
        <span class="close" id="closeEditModal">&times;</span>
        <h2>Edit Administrator</h2>
        <form id="editAdminForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="admin_id" id="edit_admin_id">
            
            <div class="field">
                <label class="label">First Name</label>
                <div class="control">
                    <input class="input" type="text" name="first_name" id="edit_first_name" pattern="[A-Za-z\s]+" required>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Last Name</label>
                <div class="control">
                    <input class="input" type="text" name="last_name" id="edit_last_name" pattern="[A-Za-z\s]+" required>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Department</label>
                <div class="control">
                    <div class="select" style="width: 100%;">
                        <select name="department" id="edit_department" required style="width: 100%;">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Email</label>
                <div class="control">
                    <input class="input" type="email" name="email" id="edit_email" required>
                </div>
            </div>
            
            <div class="field" style="margin-top: 20px; display: flex; justify-content: flex-end;">
                <div class="control">
                    <button type="button" class="is-reset modal-button" style="background-color: #ccc; margin-right: 10px;" onclick="closeEditModal()">Cancel</button>
                    <button type="button" id="saveEditButton" class="modal-button" style="background-color: #ffc107;">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
