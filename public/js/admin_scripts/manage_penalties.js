$(document).ready(function () {
  // Auto-hide alerts after 3 seconds
  setTimeout(function () {
    $('.alert').fadeOut('fast');
  }, 3000);

  // Only initialize DataTable if it doesn't exist
  if (!$.fn.DataTable.isDataTable('#studentsTable')) {
    $('#studentsTable').DataTable({
      pageLength: 10,
      dom: '<"top d-flex align-items-center justify-content-between mb-3"<"d-flex align-items-center"l>f>rt<"bottom"ip><"clear">',
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, 'All'],
      ],
      order: [[0, 'asc']],
      responsive: true,
      language: {
        search: '_INPUT_',
        searchPlaceholder: 'Search students...',
        lengthMenu: 'Show _MENU_ entries',
        info: 'Showing _START_ to _END_ of _TOTAL_ students',
        paginate: {
          first: 'First',
          last: 'Last',
          next: 'Next',
          previous: 'Previous',
        },
      },
    });
  }

  // Set minimum date to current date for ban expiry
  const banExpiryInput = document.getElementById('banExpiry');
  if (banExpiryInput) {
    banExpiryInput.min = new Date().toISOString().slice(0, 16);
  }
});

function banStudent(studentId, studentName) {
  $('#banStudentId').val(studentId);
  $('#banStudentName').text(studentName);
  $('#banForm')[0].reset();
  var banModal = new bootstrap.Modal(
    document.getElementById('banStudentModal')
  );
  banModal.show();
}

function unbanStudent(studentId, studentName) {
  $('#unbanStudentId').val(studentId);
  $('#unbanStudentName').text(studentName);
  $('#unbanForm')[0].reset();
  var unbanModal = new bootstrap.Modal(
    document.getElementById('unbanStudentModal')
  );
  unbanModal.show();
}

function submitUnban() {
  const form = $('#unbanForm');

  if (!form[0].checkValidity()) {
    form[0].reportValidity();
    return;
  }

  const studentId = $('#unbanStudentId').val();
  const reasonSelect = $('#unbanReason').val() || '';
  const notes = $('#unbanNotes').val() || '';
  const reason = reasonSelect + (notes ? ' - ' + notes : '');

  const formData = new FormData();
  formData.append('action', 'unban_student');
  formData.append('student_id', studentId);
  formData.append('reason', reason);

  $.ajax({
    url: 'manage_penalties.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        var unbanModal = bootstrap.Modal.getInstance(
          document.getElementById('unbanStudentModal')
        );
        if (unbanModal) unbanModal.hide();
        showAlert('success', response.message);
        setTimeout(() => location.reload(), 1500);
      } else {
        showAlert('danger', response.message);
      }
    },
    error: function () {
      showAlert('danger', 'Error processing unban request');
    },
  });
}

function viewPenaltyHistory(studentId, studentName) {
  $('#penaltyHistoryStudentName').text(studentName);
  $('#penaltyHistoryContent').html(
    '<div class="text-center"><i class="mdi mdi-loading mdi-spin"></i> Loading penalty history...</div>'
  );
  var historyModal = new bootstrap.Modal(
    document.getElementById('penaltyHistoryModal')
  );
  historyModal.show();

  $.ajax({
    url: 'includes/get_penalty_history.php',
    type: 'GET',
    data: { student_id: studentId },
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        let historyHtml = '';

        if (response.penalties.length === 0) {
          historyHtml =
            '<div class="alert alert-info">No penalty history found for this student.</div>';
        } else {
          historyHtml = '<div class="timeline">';

          response.penalties.forEach(function (penalty) {
            const statusClass =
              penalty.status === 'active'
                ? 'danger'
                : penalty.status === 'expired'
                ? 'warning'
                : 'success';
            const statusIcon =
              penalty.status === 'active'
                ? 'ban'
                : penalty.status === 'expired'
                ? 'clock-o'
                : 'check';

            historyHtml += `
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-${statusClass}">
                                        <i class="mdi mdi-${statusIcon}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h5 class="timeline-title">
                                            ${
                                              penalty.penalty_type
                                                .charAt(0)
                                                .toUpperCase() +
                                              penalty.penalty_type.slice(1)
                                            } - 
                                            <span class="status-badge ${statusClass}">${penalty.status.toUpperCase()}</span>
                                        </h5>
                                        <p><strong>Reason:</strong> ${
                                          penalty.reason
                                        }</p>
                                        ${
                                          penalty.descriptions
                                            ? `<p><strong>Details:</strong> ${penalty.descriptions}</p>`
                                            : ''
                                        }
                                        <p><strong>Issued:</strong> ${
                                          penalty.issued_at_formatted
                                        } by ${penalty.issued_by_name}</p>
                                        ${
                                          penalty.expires_at
                                            ? `<p><strong>Expires:</strong> ${penalty.expires_at_formatted}</p>`
                                            : '<p><strong>Type:</strong> Permanent</p>'
                                        }
                                        ${
                                          penalty.revoked_at
                                            ? `<p><strong>Revoked:</strong> ${penalty.revoked_at_formatted} by ${penalty.revoked_by_name}<br><strong>Reason:</strong> ${penalty.revoke_reason}</p>`
                                            : ''
                                        }
                                    </div>
                                </div>
                            `;
          });

          historyHtml += '</div>';
        }

        $('#penaltyHistoryContent').html(historyHtml);
      } else {
        $('#penaltyHistoryContent').html(
          '<div class="alert alert-danger">Error: ' +
            (response.message || 'Unknown error') +
            '</div>'
        );
      }
    },
    error: function (xhr, status, error) {
      $('#penaltyHistoryContent').html(
        '<div class="alert alert-danger">Error loading penalty history.</div>'
      );
    },
  });
}

function showAlert(type, message) {
  const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                ${message}
            </div>
        `;
  $('.content-header').after(alertHtml);

  setTimeout(() => {
    $('.alert').alert('close');
  }, 5000);
}

function checkExpiredPenalties() {
  const checkBtn = $('#checkExpiredBtn');
  const originalText = checkBtn.html();
  
  // Disable button and show loading
  checkBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Checking...');
  
  $.ajax({
    url: 'includes/check_expired_penalties.php',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        showAlert('success', response.message);
        if (response.expired_count > 0) {
          // Reload page if any penalties were expired
          setTimeout(() => location.reload(), 2000);
        }
      } else {
        showAlert('danger', response.message);
      }
    },
    error: function() {
      showAlert('danger', 'Error checking expired penalties');
    },
    complete: function() {
      // Re-enable button
      checkBtn.prop('disabled', false).html(originalText);
    }
  });
}

function submitBan() {
  const form = $('#banForm');
  const submitBtn = $('.btn-danger[onclick="submitBan()"]');
  const formData = new FormData(form[0]);
  formData.append('action', 'ban_student');

  if (!form[0].checkValidity()) {
    form[0].reportValidity();
    return;
  }

  // Disable submit button to prevent double-clicking
  submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Processing...');

  $.ajax({
    url: 'manage_penalties.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        var banModal = bootstrap.Modal.getInstance(
          document.getElementById('banStudentModal')
        );
        banModal.hide();
        showAlert('success', response.message);
        setTimeout(() => location.reload(), 1500);
      } else {
        showAlert('danger', response.message);
        // Re-enable button on error
        submitBtn.prop('disabled', false).html('<i class="mdi mdi-cancel"></i> Ban Student');
      }
    },
    error: function () {
      showAlert('danger', 'Error processing ban request');
      // Re-enable button on error
      submitBtn.prop('disabled', false).html('<i class="mdi mdi-cancel"></i> Ban Student');
    },
  });
}
