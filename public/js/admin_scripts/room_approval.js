// When the DOM is loaded, initialize everything
document.addEventListener('DOMContentLoaded', function () {
  // Auto fade-out alerts after 3 seconds
  const alerts = document.querySelectorAll('.fade-alert');
  if (alerts.length > 0) {
    setTimeout(function () {
      alerts.forEach(function (alert) {
        alert.classList.add('fade-out');
        setTimeout(function () {
          alert.style.display = 'none';
        }, 500); // Wait for fade animation to complete
      });
    }, 3000); // 3 seconds
  }

  // Initialize dropdown menus
  const dropdowns = document.querySelectorAll('.dropdown');
  dropdowns.forEach(function (dropdown) {
    const next = dropdown.nextElementSibling;
    if (next) {
      next.style.display = 'none';
    }
  });

  // Initialize filters
  document
    .getElementById('searchInput')
    .addEventListener('keyup', filterRequests);
  document
    .getElementById('statusFilter')
    .addEventListener('change', filterRequests);
  document
    .getElementById('dateFilter')
    .addEventListener('change', filterRequests);
  document
    .getElementById('priorityFilter')
    .addEventListener('change', filterRequests);
  document
    .getElementById('clearFilters')
    .addEventListener('click', clearFilters);

  // Show request count
  const totalCount = window.totalRequests || 0;
  document.getElementById(
    'requestCount'
  ).textContent = `Showing ${totalCount} of ${totalCount} requests`;
});

// Rejection modal functions
function showRejectModal(requestId) {
  document.getElementById('rejectRequestId').value = requestId;
  document.getElementById('rejectModal').style.display = 'block';
  document.getElementById('rejection_reason').focus();

  // Clear any previously selected reason buttons
  const reasonButtons = document.querySelectorAll('.reason-option');
  reasonButtons.forEach((button) => {
    button.classList.remove('selected');
  });
}

function closeRejectModal() {
  document.getElementById('rejectModal').style.display = 'none';
  document.getElementById('rejection_reason').value = '';

  // Clear any selected reason buttons
  const reasonButtons = document.querySelectorAll('.reason-option');
  reasonButtons.forEach((button) => {
    button.classList.remove('selected');
  });
}

// Function to select a predefined rejection reason
function selectReason(reason) {
  // Set the reason in the textarea
  document.getElementById('rejection_reason').value = reason;

  // Remove selected class from all reason buttons
  const reasonButtons = document.querySelectorAll('.reason-option');
  reasonButtons.forEach((button) => {
    button.classList.remove('selected');
  });

  // Add selected class to the clicked button
  event.target.classList.add('selected');

  // Focus on the textarea for any additional input
  document.getElementById('rejection_reason').focus();
}

// Request details modal functions

function closeDetailsModal() {
  document.getElementById('detailsModal').style.display = 'none';
}

function filterRequests() {
  const searchValue = document
    .getElementById('searchInput')
    .value.toLowerCase();
  const statusValue = document.getElementById('statusFilter').value;
  const dateValue = document.getElementById('dateFilter').value;
  const priorityValue = document.getElementById('priorityFilter').value;

  const requestCards = document.querySelectorAll('.request-card');
  let visibleCount = 0;

  requestCards.forEach((card) => {
    let show = true;
    const cardText = card.textContent.toLowerCase();
    const cardStatus = card.getAttribute('data-status');
    const cardDate = new Date(card.getAttribute('data-reservation-date'));
    const cardRequesterType = card.getAttribute('data-requester-type');
    const cardDaysUntil = parseInt(card.getAttribute('data-days-until'));

    // Filter by search text
    if (searchValue && !cardText.includes(searchValue)) {
      show = false;
    }

    // Filter by status
    if (statusValue && cardStatus !== statusValue) {
      show = false;
    }

    // Filter by priority
    if (priorityValue) {
      switch (priorityValue) {
        case 'expired':
          if (cardDaysUntil >= 0) {
            // Only show expired (negative days)
            show = false;
          }
          break;
        case 'teacher':
          if (cardRequesterType !== 'Teacher') {
            show = false;
          }
          break;
        case 'urgent':
          if (cardDaysUntil > 1) {
            // Today, tomorrow, or expired
            show = false;
          }
          break;
        case 'week':
          if (cardDaysUntil > 7) {
            // Within a week
            show = false;
          }
          break;
      }
    }

    // Filter by date
    if (dateValue) {
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      const tomorrow = new Date(today);
      tomorrow.setDate(tomorrow.getDate() + 1);

      const weekEnd = new Date(today);
      weekEnd.setDate(weekEnd.getDate() + 7);

      const monthEnd = new Date(today);
      monthEnd.setMonth(monthEnd.getMonth() + 1);

      const cardDateObj = new Date(cardDate);
      cardDateObj.setHours(0, 0, 0, 0);

      switch (dateValue) {
        case 'today':
          if (cardDateObj.toDateString() !== today.toDateString()) {
            show = false;
          }
          break;
        case 'tomorrow':
          if (cardDateObj.toDateString() !== tomorrow.toDateString()) {
            show = false;
          }
          break;
        case 'week':
          if (cardDateObj < today || cardDateObj > weekEnd) {
            show = false;
          }
          break;
        case 'month':
          if (cardDateObj < today || cardDateObj > monthEnd) {
            show = false;
          }
          break;
      }
    }

    // Show or hide card
    if (show) {
      card.style.display = '';
      visibleCount++;
    } else {
      card.style.display = 'none';
    }
  });

  // Show no results message if no cards are visible
  const noResultsMsg = document.querySelector('.no-results');
  if (visibleCount === 0) {
    if (!noResultsMsg) {
      const noResults = document.createElement('div');
      noResults.className = 'no-results';
      noResults.textContent = 'No matching requests found';
      document.getElementById('requestsContainer').appendChild(noResults);
    }
  } else if (noResultsMsg) {
    noResultsMsg.remove();
  }

  // Update displayed count
  document.getElementById(
    'requestCount'
  ).textContent = `Showing ${visibleCount} of ${
    window.totalRequests || 0
  } requests`;
}

function clearFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('statusFilter').value = '';
  document.getElementById('dateFilter').value = '';
  document.getElementById('priorityFilter').value = '';

  const requestCards = document.querySelectorAll('.request-card');
  requestCards.forEach((card) => {
    card.style.display = '';
  });

  // Remove no results message if it exists
  const noResultsMsg = document.querySelector('.no-results');
  if (noResultsMsg) {
    noResultsMsg.remove();
  }

  document.getElementById('requestCount').textContent = `Showing ${
    window.totalRequests || 0
  } of ${window.totalRequests || 0} requests`;
}

// Close modals when clicking outside the content
window.onclick = function (event) {
  const rejectModal = document.getElementById('rejectModal');
  const detailsModal = document.getElementById('detailsModal');

  if (event.target == rejectModal) {
    closeRejectModal();
  }

  if (event.target == detailsModal) {
    closeDetailsModal();
  }
};
