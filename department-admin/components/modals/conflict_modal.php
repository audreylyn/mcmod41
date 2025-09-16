<!-- Conflicting Reservations Modal -->
<div id="conflictsModal" class="modal">
    <div class="modal-content-2">
        <div class="conflicting-header">
            <h3 class="modal-title">Conflicting Approved Reservations</h3>
            <span class="close" onclick="closeConflictsModal()">&times;</span>
        </div>
        <div class="modal-body" id="conflictsBody">
            <p>The room has approved reservations which block setting maintenance. Please review the list below.</p>
            <div id="conflictsList" style="max-height:300px; overflow:auto;"></div>
        </div>
    </div>
</div>