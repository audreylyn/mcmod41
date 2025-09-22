<?php
// Include error handling configuration
require_once __DIR__ . '/../middleware/error_handler.php';

// Authentication checks
require '../auth/middleware.php';
checkAccess(['Registrar']);

// Include page-specific logic
include "includes/add_admin.php";
include "includes/message.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Admin Registration</title>
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_1.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">
    <link rel="stylesheet" href="../public/css/admin_styles/add_admin.css">
    <link rel="stylesheet" href="../public/css/admin_styles/ajax.css">

    <style>
        .button-container  { 
            display: flex;
            gap: 3px;
            justify-content: center;
        }


    </style>
</head>

<body>
    <div id="app">
        <?php 
        include 'layout/topnav.php'; 
        include 'layout/sidebar.php'; 
        ?>

        <div class="all_container">
            <div class="table-container">
                <?php include 'components/tables/admin_table.php'; ?>
            </div>

            <section class="section main-section">
                <div class="card">
                    <header class="card-header">
                        <div class="new-title-container" style="width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 5px 0 5px 20px;">
                            <p class="new-title">Add Admin</p>
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <?php include 'components/forms/import_form.php'; ?>
                            </div>
                        </div>
                    </header>
                    <div class="card-content">
                        <?php include 'components/forms/add_admin_form.php'; ?>
                    </div>
                </div>
            </section>
        </div>

        <?php include 'components/modals/admin_modals.php'; ?>

        <!-- AJAX Loader -->
        <div class="ajax-loader" id="ajaxLoader">
            <div class="ajax-loader-content">
                <div class="ajax-loader-spinner"></div>
                <div id="ajaxLoaderText">Processing...</div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="../public/js/admin_scripts/main.min.js"></script>
    <script src="../public/js/admin_scripts/custom_alert.js"></script>
    <?php include "layout/admin-scripts.js.php"; ?>
    <script src="../public/js/admin_scripts/add_admin.js"></script>
    <script src="assets/js/import-handler.js"></script>
    <script src="../public/js/admin_scripts/message.js"></script>
</body>
</html>
