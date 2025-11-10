<?php
require '../auth/middleware.php';
checkAccess(['Registrar']);
include "includes/add_building.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Building</title>
    <!-- Icons and Manifest -->
    <link rel="icon" href="../public/assets/logo.webp" type="image/webp" />
    <link rel="apple-touch-icon" href="../public/assets/logo.webp">
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_1.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">
    <link rel="stylesheet" href="../public/css/admin_styles/add_admin.css">
    <link rel="stylesheet" href="../public/css/admin_styles/ajax.css">
    
    <!-- Scripts with defer attribute -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" defer></script>
    <script src="../public/js/admin_scripts/main.min.js" defer></script>
    <script src="../public/js/admin_scripts/custom_alert.js" defer></script>
    <script src="../public/js/admin_scripts/add_building.js" defer></script>
    <?php include "layout/admin-scripts.js.php"; ?>
</head>

<body>
    <div id="app">
        <?php 
        include 'layout/topnav.php'; 
        include 'layout/sidebar.php'; 
        ?>

        <div class="all_container">
            <div class="table-container">
                <?php include 'components/tables/building_table.php'; ?>
            </div>

            <section class="section main-section">
                <?php include 'components/forms/add_building_form.php'; ?>
            </section>
        </div>
    </div>
    
    <?php include 'components/modals/building_modal.php'; ?>

    <script src="../public/js/admin_scripts/message.js"></script>

</body>

</html>
