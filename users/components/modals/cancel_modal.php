<!-- Confirm Cancel Modal -->
<div class="modal" id="confirmCancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="alert-style-modal">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5>Cancel Request</h5>
                <p>Are you sure you want to cancel this room request? This action cannot be undone.</p>
                <div class="alert-actions">
                    <button type="button" class="btn-action btn-secondary" data-dismiss="modal">No, Keep Request</button>
                    <button type="button" class="btn-action btn-danger" id="confirmCancelButton">
                        Yes, Cancel Request
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>