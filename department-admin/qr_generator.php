<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

// Add ngrok compatibility headers
header('ngrok-skip-browser-warning: true');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
    <!-- Icons and Manifest -->
    <link rel="icon" href="../public/assets/logo.webp" type="image/webp" />
    <link rel="apple-touch-icon" href="../public/assets/logo.webp">
    <!-- No external QR code library needed as we're using GoQR API -->
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/qr_generator.css">
    <link rel="stylesheet" href="../public/css/admin_styles/mobile_fix.css">

    <style>
        .qr-panel-bodys {
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .qr-loading {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        .qr-error {
            text-align: center;
            padding: 20px;
            color: #e11d48;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 4px;
        }
        #qrcode img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div id="app">
        <?php include 'layout/topnav.php'; ?>
        <?php include 'layout/sidebar.php'; ?>

        <div class="main-container">
            <div class="page-header">
                <h1>Equipment QR Code Generator</h1>
                <p>Generate QR codes for equipment in the MCiSmartSpace system</p>
            </div>

            <?php include 'components/main_contents/qr_generator.php'; ?>
        </div>

        <?php
        // Close the database connection
        $conn->close();
        ?>
    </div>
    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/custom_alert.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/qr_generator.js"></script>

    <script>
        window.onload = function() {
            <?php include 'components/shared/session_messages.php'; ?>
        }
    </script>

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