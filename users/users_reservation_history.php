<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

require_once '../auth/room_status_handler.php';

// Get user role and ID from session
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];
?>

<?php include "../partials/header_reservation-history.php"; ?>
<?php include "layout/sidebar.php"; ?>
<?php include "layout/topnav.php"; ?>

<?php include "components/main_contents/reservation_history.php"; ?>
<?php include "components/modals/reservation_details_modal.php"; ?>
<?php include "components/modals/cancel_modal.php"; ?>
<?php include "components/shared/footer.php"; ?>




<!-- Chatbot Widget -->
<?php include "layout/chatbot-layout.php"; ?>

<!-- Include external JavaScript file -->
<script src="../public/js/user_scripts/reservation_history_updated.js"></script>
<?php include "../partials/footer.php"; ?>