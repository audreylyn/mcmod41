<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

include 'includes/maintenance.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Maintenance</title>
        <!-- Icons and Manifest -->
    <link rel="icon" href="../public/assets/logo.webp" type="image/webp" />
    <link rel="apple-touch-icon" href="../public/assets/logo.webp">
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/maintenance.css">
    <link rel="stylesheet" href="../public/css/admin_styles/mobile_fix.css">
    

</head>

<body>
    <div id="app">
        <?php include 'layout/topnav.php'; ?>
        <?php include 'layout/sidebar.php'; ?>

        <section class="section main-section">
            <div class="maintenance-container">
                <?php include 'components/main_contents/room_maintenance.php'; ?>
            </div>
        </section>
    </div>

    <?php include 'components/modals/maintenance_modal.php'; ?>
    <?php include 'components/modals/conflict_modal.php'; ?>


    <script type="text/javascript" src="../public/js/admin_scripts/room_maintenance.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/mobile_menu_fix.js"></script>
</body>
</html>