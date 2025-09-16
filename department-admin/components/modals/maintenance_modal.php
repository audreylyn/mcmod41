<!-- Maintenance Reason Modal -->
<div id="maintenanceModal" class="modal">
    <div class="modal-content-1">
        <div class="modal-header">
            <h3 class="modal-title">Set Room to Maintenance</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="maintenanceForm">
            <div class="form-group">
                <label class="form-label">Room:</label>
                <input type="text" id="modalRoomName" class="form-input" readonly>
                <input type="hidden" id="modalRoomId">
            </div>
            <div class="form-group">
                <label class="form-label">Reason for Maintenance:</label>
                <textarea id="maintenanceReason" class="form-input" rows="4" placeholder="Enter the reason for putting this room under maintenance..." required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Maintenance Period:</label>
                <div class="date-inputs-container">
                    <div class="date-input-group">
                        <label for="modalStartDate" class="form-sublabel">Start Date:</label>
                        <input type="date" id="modalStartDate" class="form-input" required>
                        <small class="date-hint">Cannot select past dates</small>
                    </div>
                    <div class="date-input-group">
                        <label for="modalEndDate" class="form-sublabel">End Date:</label>
                        <input type="date" id="modalEndDate" class="form-input" required>
                        <small class="date-hint">Must be after start date</small>
                    </div>
                </div>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Set to Maintenance</button>
            </div>
        </form>
    </div>
</div>