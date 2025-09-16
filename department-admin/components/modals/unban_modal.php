    <!-- Unban Student Modal -->
<div class="modal fade" id="unbanStudentModal" tabindex="-1" aria-labelledby="unbanStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unbanStudentModalLabel">
                        <i class="mdi mdi-check-circle text-success"></i> Unban Student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="unbanForm">
                        <input type="hidden" id="unbanStudentId" name="student_id">
                        
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            You are about to unban <strong id="unbanStudentName"></strong>. 
                            They will regain access to make reservations and report equipment issues.
                        </div>
                        
                        <div class="mb-3">
                            <label for="unbanReason" class="form-label">Reason for Unbanning <span class="text-danger">*</span></label>
                            <select class="form-select" id="unbanReason" name="revoke_reason" required>
                                <option value="">Select a reason...</option>
                                <option value="Appeal approved">Appeal approved</option>
                                <option value="Penalty period completed">Penalty period completed</option>
                                <option value="Administrative error">Administrative error</option>
                                <option value="Good behavior">Good behavior</option>
                                <option value="Other">Other (specify below)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="unbanNotes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="unbanNotes" name="revoke_notes" 
                                      rows="3" placeholder="Provide additional notes about the unbanning..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="submitUnban()">
                        <i class="mdi mdi-check-circle"></i> Unban Student
                    </button>
                </div>
            </div>
        </div>
</div>