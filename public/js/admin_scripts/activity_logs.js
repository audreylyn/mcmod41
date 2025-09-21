$(document).ready(function () {
  // Initialize DataTable
  var table = $('#activityTable').DataTable({
    responsive: true,
    language: {
      search: '_INPUT_',
      searchPlaceholder: 'Search by user, room, activity...',
      info: 'Showing _START_ to _END_ of _TOTAL_ entries',
    },
    dom: '<"top d-flex align-items-center justify-content-between mb-3"<"d-flex align-items-center"l>f>rt<"bottom"ip><"clear">', // Show entries and search at top
    pageLength: 10,
    ordering: true,
    paging: true,
    lengthChange: true,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'All'],
    ],
    columnDefs: [
      {
        targets: -1,
        orderable: false,
      },
    ],
  });

  // Usage status filter
  $('#usage-filter').on('change', function () {
    var selectedStatus = $(this).val();

    if (selectedStatus === '') {
      // Show all rows
      table.column(4).search('').draw();
    } else {
      // Filter by status - using column index 4 (Status column)
      var searchTerm = '';
      if (selectedStatus === 'upcoming') {
        searchTerm = 'Upcoming|Later Today';
      } else if (selectedStatus === 'active') {
        searchTerm = 'Active Now';
      } else if (selectedStatus === 'completed') {
        searchTerm = 'Completed|Completed Today';
      }

      table.column(4).search(searchTerm, true, false).draw();
    }
  });

  // Apply the current usage filter on page load
  var currentUsageFilter = $('#usage-filter').val();
  if (currentUsageFilter) {
    $('#usage-filter').trigger('change');
  }

  // Auto-fade success messages after 3 seconds
  if ($('.alert-success').length > 0) {
    setTimeout(function () {
      $('.alert-success').fadeOut(1000, function () {
        $(this).remove();
      });
    }, 3000);
  }
});
