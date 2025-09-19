<!-- Reservation Details Modal -->
<div class="modal" id="reservationDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reservation Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="reservationDetailsContent">
                    <!-- Content will be filled by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-action btn-print" id="printButton" style="display:none;">
                    <i class="fa fa-file-pdf-o"></i> Export PDF
                </button>
                <div id="actionButtons">
                    <!-- Action buttons will be added dynamically based on status -->
                </div>
            </div>
        </div>
    </div>
</div>