<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

include 'includes/equipment_report_status.php';

// Include room status handler to automatically update room statuses
require_once '../auth/room_status_handler.php';
?>

<?php include "../partials/header.php"; ?>
<link href="../public/css/user_styles/equipment_report_status.css" rel="stylesheet">
<link href="../public/css/user_styles/report_status.css" rel="stylesheet">

<?php include "layout/sidebar.php"; ?>
<?php include "layout/topnav.php"; ?>

<?php include "components/main_contents/report_status_contents.php"; ?>

<?php include "components/shared/footer.php"; ?>


<!-- Chatbot Widget -->
<?php include "layout/chatbot-layout.php"; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const reportCards = document.querySelectorAll('.report-card');

        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();

            reportCards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                if (cardText.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Status filter functionality
        const statusFilter = document.getElementById('statusFilter');

        statusFilter.addEventListener('change', function() {
            filterReports();
        });

        // Condition filter functionality
        const conditionFilter = document.getElementById('conditionFilter');

        conditionFilter.addEventListener('change', function() {
            filterReports();
        });

        // Combined filter function
        function filterReports() {
            const statusValue = statusFilter.value.toLowerCase();
            const conditionValue = conditionFilter.value.toLowerCase();

            reportCards.forEach(card => {
                const cardStatus = card.dataset.status;
                const cardCondition = card.dataset.condition;

                // Show card if both filters match or are empty
                const statusMatch = statusValue === '' || cardStatus === statusValue;
                const conditionMatch = conditionValue === '' || cardCondition === conditionValue;

                if (statusMatch && conditionMatch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Call this when the document is ready
        if (typeof updateValidationIcons === 'function') {
            updateValidationIcons();
        }
    });

    // Toggle report details function remains the same
    function toggleDetails(button, reportId) {
        const detailsSection = document.getElementById('details-' + reportId);
        const icon = button.querySelector('i');

        if (detailsSection.style.display === 'none' || !detailsSection.style.display) {
            detailsSection.style.display = 'block';
            icon.classList.remove('fa-chevron-right');
            icon.classList.add('fa-chevron-down');
            button.innerHTML = 'Hide Details <i class="fa fa-chevron-down"></i>';
        } else {
            detailsSection.style.display = 'none';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-right');
            button.innerHTML = 'View Details <i class="fa fa-chevron-right"></i>';
        }
    }
</script>

<?php include "../partials/footer.php"; ?>

