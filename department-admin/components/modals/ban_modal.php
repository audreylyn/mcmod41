<!-- Ban Student Modal -->
<div class="modal fade" id="banStudentModal" tabindex="-1" aria-labelledby="banStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="banStudentModalLabel">
                    <i class="mdi mdi-cancel text-danger"></i> Ban Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="banForm">
                    <input type="hidden" id="banStudentId" name="student_id">
                    
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert"></i>
                        You are about to ban <strong id="banStudentName"></strong>. 
                        This will prevent them from making reservations and reporting equipment issues.
                    </div>
                    
                    <div class="mb-3">
                        <label for="banReason" class="form-label">Reason for Ban <span class="text-danger">*</span></label>
                        <select class="form-select" id="banReason" name="reason" required>
                            <option value="">Select a reason...</option>
                            <option value="Repeated violations">Repeated violations</option>
                            <option value="Equipment damage">Equipment damage</option>
                            <option value="Inappropriate behavior">Inappropriate behavior</option>
                            <option value="No-show violations">No-show violations</option>
                            <option value="Other">Other (specify below)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="banDescription" class="form-label">Additional Details</label>
                        <textarea class="form-control" id="banDescription" name="description" 
                                    rows="3" placeholder="Provide additional details about the penalty..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="banExpiry" class="form-label">Ban Expiry Date (Optional)</label>
                        <input type="date" class="form-control" id="banExpiry" name="expiry_date">
                        <div class="form-text">Leave empty for permanent ban. Time will be set to current time automatically.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitBan()">
                    <i class="mdi mdi-cancel"></i> Ban Student
                </button>
            </div>
        </div>
    </div>
</div>