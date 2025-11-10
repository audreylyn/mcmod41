// Update capacity limits based on room type
function updateCapacityLimit() {
  const roomType = $('#room_type').val();
  const capacityInput = $('#capacity');
  const capacityLabel = $('#capacityLabel');

  if (roomType === 'Classroom') {
    capacityInput.attr('max', 50);
    capacityLabel.text('Capacity (max 50 for classrooms)');
  } else {
    capacityInput.attr('max', 500);
    capacityLabel.text('Capacity (max 500)');
  }
}

$(document).ready(function () {
  $('#facilityTable').DataTable({
    responsive: true,
    language: {
      search: '_INPUT_',
      searchPlaceholder: 'Search facilities...',
    },
    dom: 'lfrtip',
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'All'],
    ],
    pageLength: 10,
    ordering: true,
    columnDefs: [{ targets: -1, orderable: false }],
  });

  // Handle file input display
  $('.excel input[type="file"]').on('change', function () {
    let fileName = $(this).val().split('\\').pop();
    if (fileName) {
      $(this)
        .closest('form')
        .find('.container-btn-file')
        .html(
          '<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path></svg> ' +
            fileName
        );
    } else {
      $(this)
        .closest('form')
        .find('.container-btn-file')
        .html(
          '<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path></svg> Import'
        );
    }
  });

  // Handle CSV import with form validation
  $('#importForm').on('submit', function (e) {
    e.preventDefault();

    // Validate file input
    const fileInput = $('#csvFile')[0];
    if (!fileInput.files.length) {
      showModal('Error', 'Please select a CSV file to import.', 'error');
      return false;
    }

    const file = fileInput.files[0];
    if (file.size === 0) {
      showModal('Error', 'The selected file is empty.', 'error');
      return false;
    }

    const fileExtension = file.name.split('.').pop().toLowerCase();
    if (fileExtension !== 'csv') {
      showModal('Error', 'Only CSV files are allowed.', 'error');
      return false;
    }

    // Show loading state
    const $importButton = $('#importButton');
    const originalButtonText = $importButton.html();
    $importButton.html('<div class="spinner"></div> Importing...');
    $importButton.addClass('disabled');
    $('.excel').addClass('disabled');

    // Prepare form data
    const formData = new FormData(this);
    // Add the importSubmit parameter that would normally come from the submit button
    formData.append('importSubmit', 'true');

    // Submit using AJAX
    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {
        if (response.status === 'success') {
          showModal('Success', response.message, 'success');

          // Add new rooms to the table
          const table = $('#facilityTable').DataTable();
          if (response.new_rooms && response.new_rooms.length > 0) {
            response.new_rooms.forEach(function (room) {
              // Create action buttons
              const actionButtons = `
                                <button class="styled-button is-small" onclick='openEditModal(${JSON.stringify(room)})'>
                                    <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                </button>
                                <button class="styled-button is-reset is-small" onclick="deleteRoom('${room.room_id}')">
                                    <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                </button>
                            `;

              // Add the row to the table
              table.row
                .add([
                  room.building_name || 'N/A',
                  room.department || 'N/A',
                  room.number_of_floors || 'N/A',
                  room.room_name || 'N/A',
                  room.room_type || 'N/A',
                  room.capacity || 'N/A',
                  actionButtons,
                ])
                .draw(false);
            });
          }
        } else {
          showModal('Error', response.message, 'error');
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', error);
        let errorMsg = 'An error occurred while importing the file.';

        // Try to parse the response if it's JSON
        try {
          const response = JSON.parse(xhr.responseText);
          if (response && response.message) {
            errorMsg = response.message;
          }
        } catch (e) {
          console.error('Error parsing response:', e);
        }

        showModal('Error', errorMsg, 'error');
      },
      complete: function () {
        // Reset form and button state
        $('#importForm').trigger('reset');
        $importButton.html(originalButtonText);
        $importButton.removeClass('disabled');
        $('.excel').removeClass('disabled');
      },
    });
  });

  // Simple jQuery modal implementation
  $('#addRoomBtn').click(function () {
    $('#modalTitle').text('Add Room');
    $('#roomForm').trigger('reset');
    $('#room_id').val('');
    $('#formSubmitBtn').text('Save Changes').attr('name', 'add_room');
    // Prevent body scrolling
    document.body.style.overflow = 'hidden';
    $('#roomModal').fadeIn(300);
    updateCapacityLimit();
    return false; // Prevent default action
  });

  // Update capacity limits based on room type
  $('#room_type').on('change', function () {
    updateCapacityLimit();
  });

  // Add form validation before submission
  $('#roomForm').on('submit', function (e) {
    const capacity = parseInt($('#capacity').val());
    const roomType = $('#room_type').val();

    if (roomType === 'Classroom' && capacity > 50) {
      e.preventDefault();
      showModal(
        'Error',
        'Classroom capacity cannot exceed 50 people.',
        'error'
      );
      return false;
    } else if (capacity > 500) {
      e.preventDefault();
      showModal('Error', 'Room capacity cannot exceed 500 people.', 'error');
      return false;
    }
  });

  $('#modalClose, #modalCancel').click(function () {
    $('#roomModal').fadeOut(300, function() {
      // Re-enable body scrolling after animation completes
      document.body.style.overflow = '';
    });
    return false; // Prevent default action
  });

  // Close modal when clicking outside the modal content
  $(document).mouseup(function (e) {
    var container = $('.modal-container');
    if (!container.is(e.target) && container.has(e.target).length === 0) {
      $('#roomModal').fadeOut(300, function() {
        // Re-enable body scrolling after animation completes
        document.body.style.overflow = '';
      });
    }
  });
});

function openEditModal(data) {
  $('#modalTitle').text('Edit Room');
  $('#room_id').val(data.room_id);
  $('#room_name').val(data.room_name);
  $('#room_type').val(data.room_type);
  $('#capacity').val(data.capacity);
  $('#building_id').val(data.building_id);
  $('#formSubmitBtn').attr('name', 'update_room');
  // Prevent body scrolling
  document.body.style.overflow = 'hidden';
  $('#roomModal').fadeIn(300);
  updateCapacityLimit(); // Update the capacity limit when editing
  return false; // Prevent any default action
}

function deleteRoom(roomId) {
  const modal = document.getElementById('deleteRoomConfirmModal');
  const confirmBtn = document.getElementById('confirmDeleteRoomButton');
  const cancelBtn = document.getElementById('cancelDeleteRoomButton');
  const closeBtn = document.getElementById('closeDeleteRoomConfirmModal');

  // Prevent body scrolling
  document.body.style.overflow = 'hidden';

  // Show the modal
  modal.style.display = 'block';
  void modal.offsetWidth; // Trigger reflow for animation
  modal.classList.add('show');

  // When the user clicks "Delete", redirect to the delete URL
  confirmBtn.onclick = function () {
    window.location.href = '?delete_room=' + roomId;
  };

  // Functions to close the modal without deleting
  const closeModal = function () {
    modal.classList.remove('show');
    setTimeout(() => {
      modal.style.display = 'none';
      // Re-enable body scrolling
      document.body.style.overflow = '';
    }, 300);
  };

  cancelBtn.onclick = closeModal;
  closeBtn.onclick = closeModal;

  // Also close if the user clicks outside the modal content
  window.addEventListener('click', function (event) {
    if (event.target == modal) {
      closeModal();
    }
  }, { once: true }); // Use 'once' to avoid adding multiple listeners
}

// Download room template function
function downloadRoomTemplate() {
  const csvContent = 'Room Name,Room Type,Capacity,Building Name\nACC-112,Classroom,50,Accountancy Building';
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');

  if (link.download !== undefined) {
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'room_template.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
}

