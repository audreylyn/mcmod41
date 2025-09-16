<?php
// This file contains the top navigation HTML for registrar pages
?>
<style>
    .navbar-item.has-dropdown {
        position: relative;
        padding-right: 1.5em;
    }

    .navbar-item.has-dropdown:after {
        content: '';
        position: absolute;
        right: 1.125em;
        top: 50%;
        transform: translateY(-50%);
        border: 3px solid transparent;
        border-radius: 2px;
        border-right: 0;
        border-top: 3px solid #4a4a4a;
        border-left: 3px solid #4a4a4a;
        transition: transform 0.2s ease;
        transform-origin: center;
        width: 6px;
        height: 6px;
        transform: translateY(-50%) rotate(45deg);
    }

    .navbar-item.has-dropdown:hover:after {
        transform: translateY(-50%) rotate(225deg);
    }

    .navbar-item.has-dropdown:hover .navbar-dropdown {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    .navbar-dropdown {
        display: none;
        opacity: 0;
        position: absolute;
        right: 0;
        top: 100%;
        min-width: 200px;
        background: white;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transform: translateY(-10px);
        transition: all 0.2s ease;
        z-index: 999;
    }

    .navbar-dropdown .navbar-item {
        padding: 0.7rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #4a4a4a;
        font-size: 0.95rem;
        text-decoration: none;
    }

    .navbar-dropdown .navbar-item:hover {
        background-color: #f8f9fa;
        color: #2c3e50;
    }

    .navbar-divider {
        height: 1px;
        background-color: #edf2f7;
        margin: 0.25rem 0;
    }

    .navbar-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        color: #4a4a4a;
        cursor: pointer;
        font-weight: 500;
    }

    .navbar-link .icon {
        color: #4a4a4a;
    }

    .navbar-dropdown .navbar-item .icon {
        font-size: 1.2em;
        color: #4a4a4a;
        width: 1.5em;
        text-align: center;
    }
</style>

<?php
// Get user data - in a real application, this would come from your session
// You can modify this to pull actual user data from your session
$userData = [
    'name' => $_SESSION['name'] ?? 'Dept Admin',
    'role' => $_SESSION['role'] ?? 'Department Admin',
    'profileImage' => $_SESSION['profile_image'] ?? null
];

// Get breadcrumb information
$moduleName = 'Department Admin';
$currentPage = basename($_SERVER['PHP_SELF']);

// Map page filenames to readable titles for breadcrumbs
$pageTitles = [
    'dept-admin.php' => 'Dashboard',
    'dept_room_approval.php' => 'Room Approval',
    'dept_room_activity_logs.php' => 'Activity Logs',
    'dept_room_maintenance.php' => 'Room Maintenance',
    'manage_students.php' => 'Manage Students',
    'manage_teachers.php' => 'Manage Teachers',
    'manage_penalties.php' => 'Manage Penalties',
    'dept_equipment_report.php' => 'Equipment Report',
    'qr_generator.php' => 'QR Generator',
    'edit_profile_admin.php' => 'Profile',
    'delete_account_process.php' => 'Delete Account'
];

// Determine the current page title
$currentPageTitle = $pageTitles[$currentPage] ?? $pageSubTitle ?? 'Dashboard';

// Define dropdown menu items for user profile
$userMenuItems = [
    [
        'title' => 'Log Out',
        'url' => '../auth/logout.php',
        'icon' => 'logout'
    ]
];

// Notifications - this would be dynamic in a real application
$notifications = [];
// Example of how you might populate notifications
// $notifications = fetchUserNotifications($userId);
?>
<nav id="navbar-main" class="navbar is-fixed-top">
    <div class="navbar-brand">
        <a class="navbar-item mobile-aside-button">
            <span class="icon"><i class="mdi mdi-forwardburger mdi-24px"></i></span>
        </a>
        <div class="navbar-item">
            <section class="is-title-bar">
                <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
                    <ul>
                        <li><?php echo htmlspecialchars($moduleName); ?></li>
                        <li><?php echo htmlspecialchars($currentPageTitle); ?></li>
                    </ul>
                </div>
            </section>
        </div>
    </div>
    
    <div class="navbar-brand is-right">
        <a class="navbar-item --jb-navbar-menu-toggle" data-target="navbar-menu">
            <span class="icon"><i class="mdi mdi-dots-vertical mdi-24px"></i></span>
        </a>
    </div>
    <div class="navbar-menu" id="navbar-menu">
        <div class="navbar-end">
            <div class="navbar-item has-dropdown is-hoverable">
                <a class="navbar-link">
                    Hello, <?php echo htmlspecialchars($userData['name']); ?>
                </a>

                <div class="navbar-dropdown is-right">
                    <a href="edit_profile_admin.php" class="navbar-item">
                        <span class="icon"><i class="mdi mdi-account-edit"></i></span>
                        <span>Profile</span>
                    </a>
                    <hr class="navbar-divider">
                    <a href="../auth/logout.php" class="navbar-item">
                        <span class="icon"><i class="mdi mdi-logout"></i></span>
                        <span>Log Out</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

