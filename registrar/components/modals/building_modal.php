<!-- Message Modal -->
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

<!-- AJAX Loader -->
<div class="ajax-loader" id="ajaxLoader">
    <div class="ajax-loader-content">
        <div class="ajax-loader-spinner"></div>
        <div id="ajaxLoaderText">Processing...</div>
    </div>
</div>
