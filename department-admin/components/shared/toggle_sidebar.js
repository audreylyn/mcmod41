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
