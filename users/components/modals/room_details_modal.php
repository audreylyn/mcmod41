<!-- Room Details Modal -->
<div class="modal fade" id="roomDetailsModal" tabindex="-1" role="dialog" aria-labelledby="roomDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="roomDetailsModalLabel">Room Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="roomDetailsContent">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p>Loading room details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add responsive styles for the modal -->
<style>
    /* Modal responsive styles */
    @media (max-width: 768px) {
        #roomDetailsModal .modal-dialog {
            margin: 10px;
            max-width: calc(100% - 20px);
            width: 100%;
        }
        
        #roomDetailsModal .modal-content {
            border-radius: 8px;
        }
        
        #roomDetailsModal .modal-header {
            padding: 12px 15px;
        }
        
        #roomDetailsModal .modal-header h4 {
            font-size: 18px;
            margin: 0;
        }
        
        #roomDetailsModal .modal-body {
            padding: 15px;
            max-height: 70vh;
            overflow-y: auto;
        }
        
        #roomDetailsModal .modal-footer {
            padding: 10px 15px;
            justify-content: center;
        }
        
        #roomDetailsModal .modal-footer .btn {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            font-size: 16px;
        }
    }
    
    /* Room details content responsive styles */
    #roomDetailsModal .room-details-container {
        margin: 0 -10px;
    }
    
    #roomDetailsModal .room-header-info {
        margin-bottom: 15px;
    }
    
    /* Global maintenance block styling improvements */
    #roomDetailsModal .maintenance-block {
        border: 1px solid #b8daff !important;
        background: #f0f9ff !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
        transition: all 0.2s ease !important;
    }
    
    #roomDetailsModal .maintenance-block > div {
        gap: 10px !important;
    }
    
    #roomDetailsModal .maintenance-block .label-lightblue {
        background-color: rgba(124, 205, 230, 0.15) !important;
        color: #0c5460 !important;
    }
    
    #roomDetailsModal .maintenance-block div[style*="width:100%"] > div:not(:last-child) {
        margin-bottom: 8px !important;
    }
    
    /* Maintenance info details */
    #roomDetailsModal .maintenance-block div[style*="color:#4b5563"] {
        font-size: 13px !important;
        color: #4b5563 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 5px 10px !important;
    }
    
    #roomDetailsModal .maintenance-block div[style*="color:#4b5563"] span {
        white-space: nowrap !important;
    }
    
    @media (max-width: 768px) {
        #roomDetailsModal .room-name {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        #roomDetailsModal .building-name {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        #roomDetailsModal .room-status-container {
            text-align: left !important;
            margin-top: 5px;
        }
        
        #roomDetailsModal .info-item {
            padding: 10px;
            margin-bottom: 10px;
        }
        
        #roomDetailsModal h4 {
            font-size: 18px;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        
        #roomDetailsModal .equipment-list {
            padding: 10px 0;
        }
        
        #roomDetailsModal .equipment-item {
            margin-bottom: 15px;
            padding: 10px;
        }
        
        #roomDetailsModal .equipment-status-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        #roomDetailsModal .maintenance-block {
            padding: 10px !important;
            margin: 10px 0 !important;
            border-radius: 6px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
        }
        
        #roomDetailsModal .maintenance-block > div {
            flex-direction: column !important;
        }
        
        #roomDetailsModal .maintenance-block .label {
            margin-bottom: 8px !important;
            align-self: flex-start !important;
        }
        
        #roomDetailsModal .maintenance-block div[style*="font-weight:600"] {
            font-size: 14px !important;
            margin-bottom: 3px !important;
        }
    }
    
    /* For very small screens */
    @media (max-width: 375px) {
        #roomDetailsModal .modal-header h4 {
            font-size: 16px;
        }
        
        #roomDetailsModal .modal-body {
            padding: 12px;
        }
        
        #roomDetailsModal .room-name {
            font-size: 18px;
        }
        
        #roomDetailsModal .info-item {
            padding: 8px;
        }
        
        #roomDetailsModal .info-content label {
            font-size: 12px;
        }
        
        #roomDetailsModal .info-content p {
            font-size: 14px;
            margin-bottom: 0;
        }
        
        #roomDetailsModal .equipment-item {
            padding: 8px;
        }
        
        #roomDetailsModal .equipment-name {
            font-size: 14px;
        }
        
        /* Maintenance block improvements for very small screens */
        #roomDetailsModal .maintenance-block {
            padding: 8px !important;
            margin: 8px 0 !important;
        }
        
        #roomDetailsModal .maintenance-block .label {
            padding: 3px 6px !important;
            font-size: 12px !important;
        }
        
        #roomDetailsModal .maintenance-block div[style*="font-weight:600"] {
            font-size: 13px !important;
        }
        
        #roomDetailsModal .maintenance-block p,
        #roomDetailsModal .maintenance-block div[style*="color:#4b5563"] {
            font-size: 12px !important;
            margin-bottom: 3px !important;
        }
    }
</style>