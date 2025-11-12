// Mobile Menu Fix for Department Admin Pages

document.addEventListener('DOMContentLoaded', function() {
  // Burger menu for sidebar
  const burgerMenu = document.querySelector('.mobile-aside-button');
  if (burgerMenu) {
    burgerMenu.addEventListener('click', function() {
      document.querySelector('#app').classList.toggle('aside-mobile-expanded');
    });
  }

  // 3-dot menu for navbar dropdown
  const navbarToggle = document.querySelector('.--jb-navbar-menu-toggle');
  if (navbarToggle) {
    navbarToggle.addEventListener('click', function() {
      const targetId = this.getAttribute('data-target');
      const targetMenu = document.getElementById(targetId);
      if (targetMenu) {
        targetMenu.classList.toggle('active');
      }
    });
  }

  // Ensure dropdowns work on mobile
  const dropdowns = document.querySelectorAll('.navbar-item.has-dropdown');
  dropdowns.forEach(dropdown => {
    dropdown.addEventListener('click', function(e) {
      if (window.innerWidth < 1024) {
        const dropdownMenu = this.querySelector('.navbar-dropdown');
        if (dropdownMenu) {
          e.preventDefault();
          dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        }
      }
    });
  });
});
