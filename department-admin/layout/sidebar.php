<?php
// Get the current page name
$currentPage = basename($_SERVER['PHP_SELF']);

// Define the menu structure
$menuItems = [
    [
        'type' => 'single',
        'page' => 'dept-admin.php',
        'icon' => '<i class="mdi mdi-view-dashboard"></i>',
        'title' => 'Dashboard'
    ],
    [
        'type' => 'single',
        'page' => 'dept_room_approval.php',
        'icon' => '<i class="mdi mdi-clipboard-check"></i>',
        'title' => 'Room Approval'
    ],
    [
        'type' => 'single',
        'page' => 'dept_room_activity_logs.php',
        'icon' => '<i class="mdi mdi-clipboard-text"></i>',
        'title' => 'Activity Logs'
    ],
    [
        'type' => 'single',
        'page' => 'dept_room_maintenance.php',
        'icon' => '<i class="mdi mdi-wrench"></i>',
        'title' => 'Room Maintenance'
    ],
    [
        'type' => 'dropdown',
        'title' => 'Manage Accounts',
        'icon' => '<i class="mdi mdi-account-multiple"></i>',
        'children' => [
            [
                'page' => 'manage_teachers.php',
                'title' => 'Manage Teachers'
            ],
            [
                'page' => 'manage_students.php',
                'title' => 'Manage Students'
            ],
            [
                'page' => 'manage_penalties.php',
                'title' => 'Manage Penalties'
            ]
        ]
    ],
    [
        'type' => 'single',
        'page' => 'dept_equipment_report.php',
        'icon' => '<i class="mdi mdi-wrench"></i>',
        'title' => 'Equipment Report'
    ],
    [
        'type' => 'single',
        'page' => 'qr_generator.php',
        'icon' => '<i class="mdi mdi-qrcode"></i>',
        'title' => 'QR Generator'
    ]
];

// Function to check if a dropdown menu should be open
function isDropdownActive($menuItem, $currentPage) {
    if ($menuItem['type'] !== 'dropdown') return false;
    
    foreach ($menuItem['children'] as $child) {
        if ($child['page'] === $currentPage) {
            return true;
        }
    }
    return false;
}

// Function to check if a menu item is active
function isMenuItemActive($menuItem, $currentPage) {
    if ($menuItem['type'] === 'single' && $menuItem['page'] === $currentPage) {
        return true;
    } elseif ($menuItem['type'] === 'dropdown') {
        foreach ($menuItem['children'] as $child) {
            if ($child['page'] === $currentPage) {
                return true;
            }
        }
    }
    return false;
}
?>
<aside class="aside is-placed-left is-expanded">
    <div class="aside-tools">
        <div class="logo">
            <a href="#"><img class="meyclogo" src="../public/assets/logo.webp" alt="logo"></a>
            <p>MCiSmartSpace</p>
        </div>
    </div>
    <div class="menu is-menu-main">
        <ul class="menu-list">
            <?php foreach ($menuItems as $menuItem): ?>
                <?php if ($menuItem['type'] === 'single'): ?>
                    <li <?php echo isMenuItemActive($menuItem, $currentPage) ? 'class="active"' : ''; ?>>
                        <a href="<?php echo $menuItem['page']; ?>">
                            <span class="icon"><?php echo $menuItem['icon']; ?></span>
                            <span class="menu-item-label"><?php echo $menuItem['title']; ?></span>
                        </a>
                    </li>
                <?php elseif ($menuItem['type'] === 'dropdown'): ?>
                    <?php $isActive = isMenuItemActive($menuItem, $currentPage); ?>
                    <li <?php echo $isActive ? 'class="active"' : ''; ?>>
                        <a class="dropdown <?php echo $isActive ? 'active' : ''; ?>" onclick="toggleIcon(this)">
                            <span class="icon"><?php echo $menuItem['icon']; ?></span>
                            <span class="menu-item-label"><?php echo $menuItem['title']; ?></span>
                            <span class="icon toggle-icon">
                                <i class="mdi <?php echo $isActive ? 'mdi-minus' : 'mdi-plus'; ?>"></i>
                            </span>
                        </a>
                        <ul style="<?php echo $isActive ? 'display: block;' : 'display: none;'; ?>">
                            <?php foreach ($menuItem['children'] as $child): ?>
                                <?php $isChildActive = ($child['page'] === $currentPage); ?>
                                <li <?php echo $isChildActive ? 'class="active"' : ''; ?>>
                                    <a href="<?php echo $child['page']; ?>" <?php echo $isChildActive ? 'class="active"' : ''; ?>>
                                        <span><?php echo $child['title']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
</aside>

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
        allDropdowns.forEach(dropdown => {
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

// Add click event listener to the document
document.addEventListener('DOMContentLoaded', function() {
    // Initial dropdown state is set via PHP, but we need to ensure the toggle works
    // regardless of which page we're on
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });
});
</script>
