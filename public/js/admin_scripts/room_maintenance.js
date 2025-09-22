let currentRoomId = null;
let currentRoomName = null;

function updateRoomStatus(roomId, newStatus, roomName) {
  if (!newStatus) return;

  currentRoomId = roomId;
  currentRoomName = roomName;

  if (newStatus === 'maintenance') {
    // Show modal for maintenance reason
    document.getElementById('modalRoomName').value = roomName;
    document.getElementById('modalRoomId').value = roomId;
    document.getElementById('maintenanceReason').value = '';

    // Initialize date fields with today and a week from today
    const today = new Date();
    const nextWeek = new Date();
    nextWeek.setDate(today.getDate() + 7);

    // Format dates for input fields (YYYY-MM-DD)
    const formatDate = (date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    };

    // Set the minimum date for both start and end date inputs to today
    const todayFormatted = formatDate(today);
    const startDateInput = document.getElementById('modalStartDate');
    const endDateInput = document.getElementById('modalEndDate');

    startDateInput.min = todayFormatted;
    endDateInput.min = todayFormatted;

    startDateInput.value = todayFormatted;
    endDateInput.value = formatDate(nextWeek);

    document.getElementById('maintenanceModal').style.display = 'block';
  } else {
    // Direct status update for available
    performStatusUpdate(roomId, newStatus, '');
  }

  // Reset the select dropdown
  event.target.value = '';
}

function closeModal() {
  document.getElementById('maintenanceModal').style.display = 'none';
}

function performStatusUpdate(
  roomId,
  status,
  reason,
  startDate = null,
  endDate = null
) {
  const formData = new FormData();
  formData.append('action', 'update_status');
  formData.append('room_id', roomId);
  formData.append('status', status);
  formData.append('reason', reason);

  // Add date parameters if provided
  if (startDate && endDate) {
    formData.append('start_date', startDate);
    formData.append('end_date', endDate);
  }

  fetch('dept_room_maintenance.php', {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        closeModal();
        showAlert('success', data.message);
        // Reload page to reflect changes
        setTimeout(() => {
          location.reload();
        }, 1500);
      } else {
        // If server returned conflicts array, show conflicts modal
        if (
          data.conflicts &&
          Array.isArray(data.conflicts) &&
          data.conflicts.length > 0
        ) {
          renderConflicts(data.conflicts, data.message);
        } else {
          showAlert('error', data.message);
        }
      }
    })
    .catch((error) => {
      showAlert('error', 'An error occurred while updating room status');
      console.error('Error:', error);
    });
}

function showAlert(type, message) {
  // Remove existing alerts
  const existingAlerts = document.querySelectorAll('.alert');
  existingAlerts.forEach((alert) => alert.remove());

  // Create new alert
  const alert = document.createElement('div');
  alert.className = `alert alert-${type}`;
  alert.innerHTML = `
                <i class="mdi ${
                  type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle'
                } icon"></i>
                ${message}
            `;

  // Insert at the top of the container
  const container = document.querySelector('.maintenance-container');
  container.insertBefore(alert, container.firstChild);

  // Auto-remove after 5 seconds
  setTimeout(() => {
    alert.remove();
  }, 5000);
}

function renderConflicts(conflicts, message) {
  // Clear existing alerts first
  const existingAlerts = document.querySelectorAll('.alert');
  existingAlerts.forEach((alert) => alert.remove());

  // Build table (without creating background alert)
  const list = document.getElementById('conflictsList');
  list.innerHTML = '';

  // Build table
  const table = document.createElement('table');
  table.style.width = '100%';
  table.style.borderCollapse = 'collapse';
  table.innerHTML = `
                <thead>
                    <tr>
                        <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd">Request #</th>
                        <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd">Activity</th>
                        <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd">Requester</th>
                        <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd">Start</th>
                        <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd">End</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
  const tbody = table.querySelector('tbody');
  conflicts.forEach((c) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
                    <td style="padding:8px;border-bottom:1px solid #eee"><a href="${escapeHtml(
                      c.approval_url
                    )}">#${c.request_id}</a></td>
                    <td style="padding:8px;border-bottom:1px solid #eee">${escapeHtml(
                      c.activity
                    )}</td>
                    <td style="padding:8px;border-bottom:1px solid #eee">${escapeHtml(
                      c.requester
                    )}</td>
                    <td style="padding:8px;border-bottom:1px solid #eee">${escapeHtml(
                      c.display_start || c.reservation_date + ' ' + c.start
                    )}</td>
                    <td style="padding:8px;border-bottom:1px solid #eee">${escapeHtml(
                      c.display_end || c.reservation_date + ' ' + c.end
                    )}</td>
                `;
    tbody.appendChild(tr);
  });
  list.appendChild(table);

  // Show modal
  document.getElementById('conflictsModal').style.display = 'block';
}

function closeConflictsModal() {
  document.getElementById('conflictsModal').style.display = 'none';
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.appendChild(document.createTextNode(text));
  return div.innerHTML;
}

// Modal form submission
document
  .getElementById('maintenanceForm')
  .addEventListener('submit', function (e) {
    e.preventDefault();

    const roomId = document.getElementById('modalRoomId').value;
    const reason = document.getElementById('maintenanceReason').value;
    const startDate = document.getElementById('modalStartDate').value;
    const endDate = document.getElementById('modalEndDate').value;

    if (!reason.trim()) {
      showAlert('error', 'Please provide a reason for maintenance');
      return;
    }

    if (!startDate) {
      showAlert('error', 'Please select a start date for maintenance');
      return;
    }

    if (!endDate) {
      showAlert('error', 'Please select an end date for maintenance');
      return;
    }

    // Validate dates
    const start = new Date(startDate);
    const end = new Date(endDate);

    // Check if start date is in the past
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Set to beginning of today

    if (start < today) {
      showAlert('error', 'Start date cannot be in the past');
      return;
    }

    // Check if end date is before or same as start date
    if (end <= start) {
      showAlert('error', 'End date must be after start date');
      return;
    }

    performStatusUpdate(roomId, 'maintenance', reason, startDate, endDate);
  });

// Add event listener to ensure end date is always after start date
document
  .getElementById('modalStartDate')
  .addEventListener('change', function () {
    const startDateValue = this.value;
    const endDateInput = document.getElementById('modalEndDate');

    if (startDateValue) {
      // Set minimum end date to one day after start date
      const startDate = new Date(startDateValue);
      const minEndDate = new Date(startDate);
      minEndDate.setDate(minEndDate.getDate() + 1);

      // Format the date as YYYY-MM-DD for the min attribute
      const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      };

      endDateInput.min = formatDate(minEndDate);

      // If current end date is before new min, update it
      const endDate = new Date(endDateInput.value);
      if (endDate <= startDate) {
        endDateInput.value = formatDate(minEndDate);
      }
    }
  });

// Close modal when clicking outside
window.onclick = function (event) {
  const modal = document.getElementById('maintenanceModal');
  if (event.target === modal) {
    closeModal();
  }
};
