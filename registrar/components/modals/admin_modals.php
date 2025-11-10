<?php require_once __DIR__ . '/../../config/departments.php'; ?>

<!-- Modal for editing admin -->
<div id="editModal" class="modal">
    <div class="modal-content" style="width: 500px; max-width: 90%; max-height: none; overflow: visible;">
        <span class="close" id="closeEditModal">&times;</span>
        <h2>Edit Administrator</h2>
        <form id="editAdminForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="admin_id" id="edit_admin_id">
            
            <div class="field">
                <label class="label">First Name</label>
                <div class="control">
                    <input class="input" type="text" name="edit_first_name" id="edit_first_name" pattern="[A-Za-z\s]+" required>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Last Name</label>
                <div class="control">
                    <input class="input" type="text" name="edit_last_name" id="edit_last_name" pattern="[A-Za-z\s]+" required>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Department</label>
                <div class="control">
                    <input class="input" type="text" name="edit_department" id="edit_department" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                    <p class="help" style="color: #666; font-size: 0.85rem; margin-top: 5px;">Department cannot be changed after creation to maintain data integrity.</p>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Email</label>
                <div class="control">
                    <input class="input" type="email" name="edit_email" id="edit_email" required>
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

<!-- Modal for displaying messages -->
<div id="messageModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2 id="modalTitle"></h2>
        <div id="modalMessage"></div>
        <div style="text-align: right;">
            <button class="modal-button" onclick="closeModal()">OK</button>
        </div>
    </div>
</div>

<!-- Modal for confirming admin deletion -->
<div id="deleteConfirmModal" class="modal">
    <div class="modal-content" style="width: 450px; max-width: 90%;">
        <span class="close" id="closeDeleteConfirmModal">&times;</span>
        <h2 id="deleteModalTitle">Confirm Deletion</h2>
        <div id="deleteModalMessage" style="margin: 20px 0;">
            Warning: Deleting this administrator will permanently remove all associated students, teachers, and their transaction records. This action cannot be undone.
        </div>
        <div class="field" style="margin-top: 20px; display: flex; justify-content: flex-end;">
            <div class="control">
                <button type="button" id="cancelDeleteButton" class="is-reset modal-button" style="background-color: #ccc; margin-right: 10px;">Cancel</button>
                <button type="button" id="confirmDeleteButton" class="modal-button" style="background-color: #d32f2f;">Delete</button>
            </div>
        </div>
    </div>
</div>

