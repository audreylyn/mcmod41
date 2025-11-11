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

    <title>Browse Rooms</title>
    <!-- Icons and Manifest -->
    <link rel="icon" href="../public/assets/logo.webp" type="image/webp" />
    <link rel="apple-touch-icon" href="../public/assets/logo.webp">
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
            color: #2A3F54;
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
        
        /* Style for banned account alert */
        .banned-account-alert {
            margin: 15px;
            padding: 15px 20px;
            border-radius: 8px;
            background-color: #fef1f1;
            color: #721c24;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            opacity: 1;
            transition: opacity 1s ease;
        }
        
        .banned-account-alert i {
            font-size: 20px;
            margin-right: 15px;
            color: #dc3545;
        }
        
        .banned-account-alert .alert-content {
            flex: 1;
        }
        
        .banned-account-alert h4 {
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 16px;
            font-weight: 600;
        }
        
        .banned-account-alert p {
            margin: 0;
            font-size: 14px;
        }
        
        /* Style for disabled reserve button */
        .btn-reserve.disabled {
            background-color: #e9ecef !important;
            color: #adb5bd !important;
            cursor: not-allowed !important;
            border-color: #e9ecef !important;
            pointer-events: none;
        }
        
        /* Styles for grayed-out buttons for banned accounts */
        .room-card-banned .reserve-btn {
            background-color: #e9ecef !important;
            color: #adb5bd !important;
            cursor: not-allowed !important;
            border-color: #e9ecef !important;
            pointer-events: none;
        }
        
        .room-card-banned .reserve-btn:hover {
            background-color: #e9ecef !important;
            color: #adb5bd !important;
        }

        .btn-reserve.disabled {
            background-color: #e9ecef !important;
            color: #adb5bd !important;
            cursor: not-allowed !important;
            border-color: #dee2e6 !important;
            opacity: 0.65;
            pointer-events: none;
        }
        
        /* Tooltip for disabled button */
        .tooltip-btn {
            position: relative;
            display: inline-block;
        }
        
        .tooltip-btn .tooltiptext {
            visibility: hidden;
            width: 220px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 8px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
            font-weight: normal;
        }
        
        .tooltip-btn .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
        }
        
        .tooltip-btn:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
        
        /* Responsive styles for action buttons */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        .action-buttons button {
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons button {
                width: 100%;
                margin-bottom: 8px;
                padding: 10px 0;
                font-size: 14px;
            }
            
            .btn-view, .btn-reserve, .btn-unavailable {
                border-radius: 4px;
            }
        }
        
        /* For very small screens */
        @media (max-width: 375px) {
            .room-card {
                padding-left: 5px;
                padding-right: 5px;
            }
            
            .action-buttons button {
                padding: 8px 0;
                font-size: 13px;
            }
        }

        .#reservationModal .date-input, #reservationModal .time-input {
            height: 45px;
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
                document.addEventListener("DOMContentLoaded", function() {
                    const bannedAlert = document.querySelector('.banned-account-alert');
                    if (bannedAlert) {
                        // Make sure alert is visible initially
                        bannedAlert.style.opacity = '1';
                        bannedAlert.style.transition = 'opacity 1s ease';
                        
                        // Wait 3 seconds then fade out
                        setTimeout(function() {
                            bannedAlert.style.opacity = '0';
                            
                            // Remove from DOM after fade completes
                            setTimeout(function() {
                                bannedAlert.style.display = 'none';
                            }, 1000);
                        }, 3000);
                    }
                });
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