<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

include 'includes/equipment_report.php'
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Reports</title>
        <!-- Icons and Manifest -->
    <link rel="icon" href="../public/assets/logo.webp" type="image/webp" />
    <link rel="apple-touch-icon" href="../public/assets/logo.webp">
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/mobile_fix.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatable-fixes.css">
    <link rel="stylesheet" href="../public/css/admin_styles/equipment_report.css">
</head>

<body>
    <div id="app">
        <?php include 'layout/topnav.php'; ?>
        <?php include 'layout/sidebar.php'; ?>

        <section class="section main-section">
            <?php include 'components/main_contents/equipment_report.php'; ?>
        </section>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/equipment_report.js"></script>

        <script>
                // Function to toggle dropdown menus
        function toggleIcon(element) {
        // Toggle active class on the clicked dropdown
        element.classList.toggle('active');

        // Toggle the plus/minus icon
        const icon = element.querySelector('.toggle-icon i');
        icon.classList.toggle('mdi-plus');
        icon.classList.toggle('mdi-minus');

        // Toggle the submenu visibility
        const submenu = element.nextElementSibling;
        if (submenu.style.display === 'block') {
            submenu.style.display = 'none';
        } else {
            // Close all other dropdowns first
            const allDropdowns = document.querySelectorAll('.menu-list .dropdown');
            allDropdowns.forEach((dropdown) => {
            if (dropdown !== element) {
                dropdown.classList.remove('active');
                const dropdownIcon = dropdown.querySelector('.toggle-icon i');
                dropdownIcon.classList.remove('mdi-minus');
                dropdownIcon.classList.add('mdi-plus');
                const dropdownSubmenu = dropdown.nextElementSibling;
                dropdownSubmenu.style.display = 'none';
            }
            });

            submenu.style.display = 'block';
        }
        }

    </script>
    
</body>

</html>