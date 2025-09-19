<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

//automatically update room statuses
require_once '../auth/room_status_handler.php';

include 'includes/browse_room.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MCiSmartSpace</title>

    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/fontawesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- jQuery custom content scroller -->
    <link href="../vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet" />

    <!-- Custom Theme Style -->
    <link href="../public/css/user_styles/custom.css" rel="stylesheet">
    <link href="../public/css/user_styles/custom2.css" rel="stylesheet">

    <!-- Include our custom CSS -->
    <link href="../public/css/user_styles/room-browser.css" rel="stylesheet">
    <link href="../public/css/user_styles/room-browser-styles.css" rel="stylesheet">
    <link href="../public/css/user_styles/room-reservation.css" rel="stylesheet">
    <link href="../public/css/user_styles/equipment-details.css" rel="stylesheet">

    <!-- Custom responsive styles -->
    <style>
        @media (max-width: 768px) {
            .view-toggle.btn-group {
                display: none !important;
            }
        }
        
        /* Add style for department buildings */
        .filter-checkbox-item.user-department .checkbox-label {
            font-weight: bold;
            color: #1e7e34;
        }
        .filter-checkbox-item.user-department .fa-home {
            color: #1e7e34;
            margin-left: 5px;
        }

        /* Remove validation icons from reservation modal inputs */
        #reservationModal .form-control.is-valid,
        #reservationModal .form-control.is-invalid {
            background-image: none !important;
            padding-right: 12px !important; /* Reset to default padding */
        }
    </style>

</head>

<body class="nav-md">
    <div class="container body">
        <div class="main_container">
            <?php include "layout/sidebar.php"; ?>

            <?php include "layout/topnav.php"; ?>

            <?php include "components/main_contents/browse_room_contents.php"; ?>
            <?php include "components/modals/room_details_modal.php"; ?>
            <?php include "components/modals/reservation_modal.php"; ?>
            <?php include "components/modals/hidden_room_details_modal.php"; ?>
            <?php include "components/shared/footer.php"; ?>

            <!-- Chatbot Widget -->
            <?php include "layout/chatbot-layout.php"; ?>

            <!-- Simple direct script to handle filter toggling -->
            <script>
                // This function uses direct DOM manipulation without relying on jQuery
                function toggleFilterDropdown(event) {
                    console.log("Direct toggle function called");
                    const dropdown = document.getElementById("filterDropdown");
                    const btn = document.getElementById("filterToggleBtn");

                    if (dropdown.style.display === "none" || dropdown.style.display === "") {
                        dropdown.style.display = "block";
                        btn.classList.add("active");
                        console.log("Dropdown shown");
                    } else {
                        dropdown.style.display = "none";
                        btn.classList.remove("active");
                        console.log("Dropdown hidden");
                    }

                    // Prevent event propagation
                    event.preventDefault();
                    event.stopPropagation();
                    return false;
                }

                // Add click outside handler when DOM is loaded
                document.addEventListener("DOMContentLoaded", function() {
                    document.addEventListener("click", function(event) {
                        const dropdown = document.getElementById("filterDropdown");
                        const btn = document.getElementById("filterToggleBtn");

                        // Only act if dropdown is visible
                        if (dropdown.style.display === "block") {
                            // If click is outside button and dropdown
                            if (!event.target.closest("#filterToggleBtn") && !event.target.closest("#filterDropdown")) {
                                dropdown.style.display = "none";
                                btn.classList.remove("active");
                                console.log("Outside click - dropdown closed");
                            }
                        }
                    });
                });
            </script>


<!-- Include jQuery first if not already included earlier -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include external JavaScript files -->
<script src="../public/js/user_scripts/room-browser-scripts.js"></script>
<script src="../public/js/user_scripts/room-details-direct.js"></script>
<script src="../public/js/user_scripts/reservation_modal.js"></script>

<?php include "../partials/footer.php"; ?>