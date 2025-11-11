// Room details modal functionality with enhanced maintenance info
function showRoomDetailsModal(roomCard) {
  // Extract room data from the data attributes
  const roomCard$ = $(roomCard);
  const roomId = roomCard$.data('room-id');
  const roomName = roomCard$.data('room-name');
  const buildingName = roomCard$.data('building-name');
  const roomType = roomCard$.data('room-type');
  const capacity = roomCard$.data('capacity');
  const status = roomCard$.data('status');
  const statusText = roomCard$.data('status-text');
  const statusClass = roomCard$.data('status-class');
  const hasEquipment = roomCard$.data('has-equipment') === true;

  // Set status icon based on status
  let statusIcon = 'check';
  if (status === 'occupied') {
    statusIcon = 'warning';
  } else if (status === 'maintenance') {
    statusIcon = 'wrench';
  }

  // Initialize tooltip attribute to empty string
  let statusTooltip = '';

  // Load template
  let template = $('#roomDetailsTemplate').html();

  // Fetch detailed room information including maintenance details
  if (status === 'maintenance' || status === 'occupied') {
    $.ajax({
      url: 'get_room_details.php',
      type: 'GET',
      data: { room_id: roomId },
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          // Handle maintenance info
          if (response.maintenanceInfo) {
            const maintenanceInfo = response.maintenanceInfo;
            const statusTooltip = ` title="Reason: ${maintenanceInfo.reason} | Period: ${maintenanceInfo.formatted_start_date} to ${maintenanceInfo.formatted_end_date} | By: ${maintenanceInfo.admin_name}"`;

            // Update template with maintenance tooltip
            template = template.replace('{statusTooltip}', statusTooltip);

            // Build a visible maintenance block
            const maintenanceBlock = `
              <div class="maintenance-block" style="margin: 10px 0 4px; padding: 10px 12px; border-radius: 8px; border: 1px solid #b8daff; background: #d1ecf1;">
                <div style="display:flex; gap:8px; align-items:flex-start;">
                  <span class="label label-lightblue" style="display:inline-flex; align-items:center; gap:6px; padding:4px 8px; border-radius:4px; font-weight:600;"><i class="fa fa-wrench"></i> Maintenance</span>
                  <div style="width:100%;">
                    <div style="font-weight:600; color:#111827;">Reason</div>
                    <div style="color:#1f2937;">${
                      maintenanceInfo.reason || 'N/A'
                    }</div>
                    <div style="margin-top:10px; display:flex; justify-content:space-between; color:#374151; font-size: 0.95em;">
                      <div>
                        <div style="font-weight:600; margin-bottom:2px;">Maintenance Period</div>
                        <span>${
                          maintenanceInfo.formatted_start_date || 'N/A'
                        }</span>
                        <span style="margin:0 5px;">to</span>
                        <span>${
                          maintenanceInfo.formatted_end_date || 'Ongoing'
                        }</span>
                      </div>
                      <div style="text-align:right;">
                        <div style="font-weight:600; margin-bottom:2px;">Maintenance By</div>
                        <span>${maintenanceInfo.admin_name || 'Admin'}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>`;

            template = template.replace('{maintenanceBlock}', maintenanceBlock);
          } else {
            template = template.replace('{maintenanceBlock}', '');
          }

          // Handle occupation info
          if (response.occupationInfo) {
            const occupationInfo = response.occupationInfo;
            const occupationBlock = `
              <div class="occupation-block" style="margin: 10px 0 4px; padding: 10px 12px; border-radius: 8px; border: 1px solid #ffe7b8; background: #fff3cd;">
                <div style="display:flex; gap:8px; align-items:flex-start;">
                  <span class="label label-warning" style="display:inline-flex; align-items:center; gap:6px; padding:4px 8px; border-radius:4px; font-weight:600; background-color: #c19719; color: white;"><i class="fa fa-clock-o"></i> Occupied</span>
                  <div style="width:100%;">
                    <div style="font-weight:600; color:#111827;">Activity</div>
                    <div style="color:#1f2937;">${
                      occupationInfo.activity_name || 'N/A'
                    }</div>
                    <div style="margin-top:10px; display:flex; justify-content:space-between; color:#374151; font-size: 0.95em;">
                      <div>
                        <div style="font-weight:600; margin-bottom:2px;">Occupied Period</div>
                        <div>${occupationInfo.formatted_date}</div>
                        <div>${occupationInfo.formatted_start_time} - ${occupationInfo.formatted_end_time}</div>
                      </div>
                      <div style="text-align:right;">
                        <div style="font-weight:600; margin-bottom:2px;">By</div>
                        <div>${occupationInfo.requester_name}</div>
                        <div style="font-size: 0.9em; color: #6b7280;">${occupationInfo.requester_type}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>`;

            template = template.replace('{occupationBlock}', occupationBlock);
          } else {
            template = template.replace('{occupationBlock}', '');
          }

          // Continue with the rest of the modal setup
          setupModalContent(
            template,
            roomName,
            buildingName,
            roomType,
            capacity,
            statusText,
            statusClass,
            statusIcon,
            hasEquipment,
            roomId
          );
        } else {
          // Fallback if info not available
          template = template.replace('{maintenanceBlock}', '');
          template = template.replace('{occupationBlock}', '');
          template = template.replace('{statusTooltip}', '');
          setupModalContent(
            template,
            roomName,
            buildingName,
            roomType,
            capacity,
            statusText,
            statusClass,
            statusIcon,
            hasEquipment,
            roomId
          );
        }
      },
      error: function () {
        // Fallback on error
        template = template.replace('{maintenanceBlock}', '');
        template = template.replace('{occupationBlock}', '');
        template = template.replace('{statusTooltip}', '');
        setupModalContent(
          template,
          roomName,
          buildingName,
          roomType,
          capacity,
          statusText,
          statusClass,
          statusIcon,
          hasEquipment,
          roomId
        );
      },
    });
  } else {
    // For non-maintenance and non-occupied rooms, proceed normally
    template = template.replace('{maintenanceBlock}', '');
    template = template.replace('{occupationBlock}', '');
    template = template.replace('{statusTooltip}', '');
    setupModalContent(
      template,
      roomName,
      buildingName,
      roomType,
      capacity,
      statusText,
      statusClass,
      statusIcon,
      hasEquipment,
      roomId
    );
  }
}

function setupModalContent(
  template,
  roomName,
  buildingName,
  roomType,
  capacity,
  statusText,
  statusClass,
  statusIcon,
  hasEquipment,
  roomId
) {
  // Initial equipment list placeholder
  let equipmentList = '';
  if (hasEquipment) {
    equipmentList =
      '<div class="equipment-loading">' +
      '<i class="fa fa-spinner fa-spin"></i> Loading equipment details...' +
      '</div>';

    // Fetch equipment details
    $.ajax({
      url: 'get_equipment_details.php',
      type: 'GET',
      data: { room_id: roomId },
      dataType: 'json',
      success: function (response) {
        if (
          response.success &&
          response.equipment &&
          response.equipment.length > 0
        ) {
          // Group equipment by name
          const equipmentGroups = {};
          response.equipment.forEach(function (item) {
            if (!equipmentGroups[item.name]) {
              equipmentGroups[item.name] = {
                name: item.name,
                description: item.description,
                statuses: {},
              };
            }
            equipmentGroups[item.name].statuses[item.status] = {
              status: item.status,
              quantity: parseInt(item.quantity),
              description: item.description,
            };
          });

          // Generate HTML for grouped equipment
          let equipmentHtml = '';
          for (const equipmentName in equipmentGroups) {
            const equipment = equipmentGroups[equipmentName];
            equipmentHtml += `
              <div class="equipment-item">
                <div class="equipment-header">
                  <span class="equipment-name">${equipment.name}</span>
                </div>`;

            // Show status breakdown
            for (const status in equipment.statuses) {
              const statusInfo = equipment.statuses[status];
              const statusClass =
                'status-' + status.toLowerCase().replace(/ /g, '-');
              const displayStatus = status.replace(/_/g, ' ');

              equipmentHtml += `
                <div class="equipment-status-item">
                  <span class="status-indicator ${statusClass}">${displayStatus}</span>
                  <span class="quantity">(${statusInfo.quantity})</span>`;

              // Add warning icon for non-working equipment
              if (status !== 'working') {
                equipmentHtml += `<span class="warning-icon" title="Requires attention"><i class="fa fa-exclamation-triangle"></i></span>`;
              }

              equipmentHtml += `</div>`;
            }

            // Add description if available
            if (equipment.description) {
              equipmentHtml += `<div class="equipment-description">${equipment.description}</div>`;
            }

            equipmentHtml += `</div>`;
          }

          $('.equipment-list').html(equipmentHtml);
        } else {
          $('.equipment-list').html(
            '<div class="no-equipment">No detailed equipment information available.</div>'
          );
        }
      },
      error: function () {
        $('.equipment-list').html(
          '<div class="equipment-error">Error loading equipment details.</div>'
        );
      },
    });
  } else {
    equipmentList =
      '<div class="no-equipment">No equipment found for this room.</div>';
  }

  // Empty reserve button section
  let reserveButton = '';

  // Replace placeholders in template
  template = template
    .replace('{roomName}', roomName)
    .replace(/{buildingName}/g, buildingName)
    .replace('{roomType}', roomType)
    .replace('{capacity}', capacity)
    .replace(/{statusText}/g, statusText)
    .replace('{statusClass}', statusClass)
    .replace('{statusIcon}', statusIcon)
    .replace(/{statusTooltip}/g, template.includes('{statusTooltip}') ? '' : '')
    .replace(/{maintenanceBlock}/g, '')
    .replace('{equipmentList}', equipmentList)
    .replace('{reserveButton}', reserveButton);

  // Update modal content
  $('#roomDetailsContent').html(template);

  // Show modal
  $('#roomDetailsModal').modal('show');
}
