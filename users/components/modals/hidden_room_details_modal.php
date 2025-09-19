            <!-- Create a hidden template for room details -->
<script id="roomDetailsTemplate" type="text/template">
                <div class="room-details-container">
                <div class="room-header-info">
                    <div class="row">
                        <div class="col-md-8 col-xs-12">
                            <h3 class="room-name">{roomName}</h3>
                            <p class="building-name">{buildingName}</p>
                        </div>
                        <div class="col-md-4 col-xs-12 text-right room-status-container">
                            <span class="label label-{statusClass}"{statusTooltip}>
                                <i class="fa fa-{statusIcon}"></i> {statusText}
                            </span>
                        </div>
                    </div>
                </div>

        <div class="room-info-section">
            <h4>Room Information</h4>
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="info-item">
                        <i class="fa fa-building"></i>
                        <div class="info-content">
                            <label>Building</label>
                            <p>{buildingName}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="info-item">
                        <i class="fa fa-th-large"></i>
                        <div class="info-content">
                            <label>Room Type</label>
                            <p>{roomType}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="info-item">
                        <i class="fa fa-users"></i>
                        <div class="info-content">
                            <label>Capacity</label>
                            <p>{capacity} persons</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="info-item">
                        <i class="fa fa-clock-o"></i>
                        <div class="info-content">
                            <label>Status</label>
                            <p{statusTooltip}>{statusText}</p>
                        </div>
                    </div>
                </div>
            </div>
            {maintenanceBlock}
        </div>

        <div class="equipment-section">
            <h4>Room Equipments</h4>
            <div class="equipment-list">
                {equipmentList}
            </div>
        </div>
    </div>
</script>

<!-- Add a hidden form for traditional filtering method -->
<form id="filterForm" method="GET" action="users_browse_room.php" style="display: none;">
    <!-- Hidden inputs will be populated by JavaScript -->
    <div id="hiddenInputsContainer"></div>
    <input type="submit" id="submitFilters">
</form>