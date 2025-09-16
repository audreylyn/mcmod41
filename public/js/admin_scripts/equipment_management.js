$(document).ready(function () {
  $('#equipmentTable').DataTable({
    responsive: true,
    language: {
      search: '_INPUT_',
      searchPlaceholder: 'Search equipment...',
    },
    dom: '<"top"lf>rt<"bottom"ip><"clear">',
    lengthMenu: [
      [5, 10, 25, 50, -1],
      [5, 10, 25, 50, 'All'],
    ],
    pageLength: 10,
    ordering: true,
  });
});
