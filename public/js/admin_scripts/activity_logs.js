$(document).ready(function () {
  // Initialize DataTable
  var table = $('#activityTable').DataTable({
    responsive: true,
    language: {
      search: '_INPUT_',
      searchPlaceholder: 'Search by user, room, activity...',
      info: 'Showing _START_ to _END_ of _TOTAL_ entries',
    },
    dom: 'rt<"bottom"p><"clear">', // Only show pagination at bottom
    pageLength: 10,
    ordering: true,
    paging: true,
    lengthChange: false, // Disable built-in length changing
    columnDefs: [
      {
        targets: -1,
        orderable: false,
      },
    ],
  });

  // Custom search handling
  $('#customSearch').on('keyup', function () {
    table.search(this.value).draw();
  });

  // Handle custom entries select
  $('#entriesSelect').on('change', function () {
    table.page.len(parseInt($(this).val())).draw();
  });

  // Apply filters button
  $('#apply-filters').on('click', function () {
    var usageFilter = $('#usage-filter').val();
    var buildingFilter = $('#building-filter').val();
    var roomFilter = $('#room-filter').val();
    var dateFilter = $('#date-filter').val();

    var url = 'dept_room_activity_logs.php?';

    if (usageFilter) url += 'usage=' + usageFilter + '&';
    if (buildingFilter) url += 'building_id=' + buildingFilter + '&';
    if (roomFilter) url += 'room_id=' + roomFilter + '&';
    if (dateFilter) url += 'date_range=' + dateFilter + '&';

    // Remove trailing &
    url = url.replace(/&$/, '');

    window.location.href = url;
  });

  // Reset filters button
  $('#reset-filters').on('click', function () {
    window.location.href = 'dept_room_activity_logs.php';
  });

  // Building filter change event
  $('#building-filter').on('change', function () {
    var buildingId = $(this).val();

    // If no building is selected, show all rooms
    if (!buildingId) {
      $('#room-filter option').show();
      return;
    }

    // Hide rooms that don't belong to the selected building
    $('#room-filter option').each(function () {
      var optionText = $(this).text();
      var selectedBuilding = $('#building-filter option:selected').text();

      if ($(this).val() === '') {
        // Always show "All Rooms" option
        $(this).show();
      } else if (optionText.indexOf(selectedBuilding) >= 0) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });

    // Reset room selection if the current selection is now hidden
    if ($('#room-filter option:selected').is(':hidden')) {
      $('#room-filter').val('');
    }
  });

  // Auto-fade success messages after 3 seconds
  if ($('.alert-success').length > 0) {
    setTimeout(function () {
      $('.alert-success').fadeOut(1000, function () {
        $(this).remove();
      });
    }, 3000);
  }
});
