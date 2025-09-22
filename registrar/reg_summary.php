<?php
require '../auth/middleware.php';
checkAccess(['Registrar']);

// Include the logic for handling summary, add, update, and delete actions
include "includes/summary.php"; 
include "includes/add_room.php"; 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facility Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_1.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">
    <link rel="stylesheet" href="../public/css/admin_styles/facility_management.css">

</head>

<body>
    <div id="app">

        <?php 
        include 'layout/topnav.php'; 
        include 'layout/sidebar.php'; 
        ?>

        <section class="section main-section">
            <?php include 'components/tables/room_table.php'; ?>
        </section>

        <?php include 'components/modals/add_edit_room_modal.php'; ?>
        <?php include 'components/modals/message_modal.php'; ?>

        <!-- Modal for confirming room deletion -->
        <div id="deleteRoomConfirmModal" class="modal">
            <div class="modal-content" style="width: 450px; max-width: 90%;">
                <span class="close" id="closeDeleteRoomConfirmModal">&times;</span>
                <h2 id="deleteRoomModalTitle">Confirm Deletion</h2>
                <div id="deleteRoomModalMessage" style="margin: 20px 0;">
                    Are you sure you want to delete this room? This action cannot be undone.
                </div>
                <div class="field" style="margin-top: 20px; display: flex; justify-content: flex-end;">
                    <div class="control">
                        <button type="button" id="cancelDeleteRoomButton" class="is-reset modal-button" style="background-color: #ccc; margin-right: 10px;">Cancel</button>
                        <button type="button" id="confirmDeleteRoomButton" class="modal-button" style="background-color: #d32f2f;">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $stmt->close();
        $conn->close();
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/custom_alert.js"></script>

    <!-- Enhanced Modal JavaScript -->
    <script type="text/javascript" src="../public/js/admin_scripts/modal.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/room_management.js"></script>

    <script>
        window.onload = function() {
            <?php include 'components/shared/session_messages.php'; ?>
        }
    </script>


</body>
</html>