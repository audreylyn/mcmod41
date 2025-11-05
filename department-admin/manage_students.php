<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

include 'includes/manage_students.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_2.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatable-fixes.css">
    <link rel="stylesheet" href="../public/css/admin_styles/manage_accounts.css">
    <link rel="stylesheet" href="../public/css/admin_styles/manage_teachers.css">

</head>

<body>
    <div id="app">


        <?php include 'layout/topnav.php'; ?>
        <?php include 'layout/sidebar.php'; ?>

        <div class="main-container">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="mdi mdi-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php include 'components/main_contents/manage_students.php'; ?>
        </div>

        <?php $conn->close(); ?>
    </div>

    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/custom_alert.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <script>
        function openDeleteModal(studentId) {
            document.getElementById('confirmDelete').href = '<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>?delete_id=' + studentId;
            document.getElementById('deleteModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    </script>

    <script type="text/javascript" src="../public/js/admin_scripts/manage_students_fixed.js"></script>

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
