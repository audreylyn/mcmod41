// Initialize DataTables
$(document).ready(function () {
  $('#teacherTable').DataTable({
    responsive: true,
    language: {
      search: '_INPUT_',
      searchPlaceholder: 'Search teachers...',
    },
    dom: '<"top d-flex align-items-center justify-content-between mb-3"<"d-flex align-items-center"l>f>rt<"bottom"ip><"clear">',
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'All'],
    ],
    pageLength: 10,
    ordering: true,
    columnDefs: [
      { orderable: false, targets: -1 }, // Disable sorting on the actions column
    ],
  });

  // Set up tab navigation
  $('.tab-btn').click(function () {
    const tabId = $(this).data('tab');

    $('.tab-btn').removeClass('active');
    $(this).addClass('active');

    $('.tab-content').removeClass('active');
    $('#' + tabId).addClass('active');
  });

  // Auto-hide alerts after 5 seconds
  setTimeout(function () {
    $('.alert').fadeOut('slow');
  }, 5000);

  // Add email validation to forms
  $('#addTeacherForm').on('submit', function (e) {
    return validateTeacherForm(this, e);
  });

  $('#editTeacherForm').on('submit', function (e) {
    return validateTeacherForm(this, e);
  });
});

// Toggle password visibility function
function togglePasswordVisibility(inputId) {
  const passwordInput = document.getElementById(inputId);
  const icon = passwordInput.nextElementSibling.querySelector('i');

  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    icon.classList.remove('mdi-eye');
    icon.classList.add('mdi-eye-off');
  } else {
    passwordInput.type = 'password';
    icon.classList.remove('mdi-eye-off');
    icon.classList.add('mdi-eye');
  }
}

// Email validation function
function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

// Form validation function
function validateTeacherForm(form, event) {
  const firstName = form.querySelector('[name="first_name"]').value.trim();
  const lastName = form.querySelector('[name="last_name"]').value.trim();
  const email = form.querySelector('[name="email"]').value.trim();
  const password = form.querySelector('[name="password"]').value;
  const isEditForm = form.id === 'editTeacherForm';

  // Clear previous error messages
  clearErrorMessages(form);

  let isValid = true;
  let errors = [];

  // Validate required fields
  if (!firstName) {
    errors.push('First name is required.');
    isValid = false;
  }

  if (!lastName) {
    errors.push('Last name is required.');
    isValid = false;
  }

  if (!email) {
    errors.push('Email is required.');
    isValid = false;
  } else if (!validateEmail(email)) {
    errors.push('Please enter a valid email address.');
    isValid = false;
  }

  // For add form, password is required
  if (!isEditForm && !password) {
    errors.push('Password is required.');
    isValid = false;
  }

  // For edit form, validate password only if provided
  if (isEditForm && password && password.length < 8) {
    errors.push('Password must be at least 8 characters long.');
    isValid = false;
  }

  // For add form, validate password length
  if (!isEditForm && password && password.length < 8) {
    errors.push('Password must be at least 8 characters long.');
    isValid = false;
  }

  if (!isValid) {
    event.preventDefault();
    showErrorMessages(form, errors);
    return false;
  }

  return true;
}

// Function to clear error messages
function clearErrorMessages(form) {
  const existingErrors = form.querySelectorAll('.error-message');
  existingErrors.forEach((error) => error.remove());
}

// Function to show error messages
function showErrorMessages(form, errors) {
  const errorContainer = document.createElement('div');
  errorContainer.className = 'alert alert-danger error-message';
  errorContainer.innerHTML =
    '<i class="mdi mdi-alert-circle"></i> ' + errors.join('<br>');

  // Insert error message at the top of the form
  form.insertBefore(errorContainer, form.firstChild);

  // Scroll to error message
  errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Modal functions
function openEditModal(teacherId, firstName, lastName, email, department) {
  document.getElementById('edit_teacher_id').value = teacherId;
  document.getElementById('edit_first_name').value = firstName;
  document.getElementById('edit_last_name').value = lastName;
  document.getElementById('edit_email').value = email;
  document.getElementById('edit_department').value = department;
  document.getElementById('hidden_department').value = department;
  document.getElementById('edit_password').value = '';

  document.getElementById('editModal').classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('show');
  document.body.style.overflow = '';
}

// File upload functions
function updateFileName() {
  const fileInput = document.getElementById('teacherFileInput');
  const fileName = document.getElementById('fileName');

  if (fileInput.files.length > 0) {
    const selectedFile = fileInput.files[0].name;
    
    // Validate file type
    const allowedTypes = ['.csv', '.xlsx', '.xls'];
    const fileExtension = selectedFile.toLowerCase().substring(selectedFile.lastIndexOf('.'));
    
    if (!allowedTypes.includes(fileExtension)) {
      alert('Please select a valid CSV or Excel file (.csv, .xlsx, .xls)');
      fileInput.value = '';
      fileName.textContent = 'No file selected';
      fileName.style.color = '#666';
      return;
    }
    
    fileName.innerHTML = `
      <span style="color: #4caf50;">
        <i class="mdi mdi-file-check"></i>
        Selected: <strong>${selectedFile}</strong>
      </span>
    `;
    
    // Enable the import button
    const importButton = document.getElementById('importButton');
    importButton.style.opacity = '1';
    importButton.style.cursor = 'pointer';
    importButton.disabled = false;
  } else {
    fileName.textContent = 'No file selected';
    fileName.style.color = '#666';
    
    // Disable the import button
    const importButton = document.getElementById('importButton');
    importButton.style.opacity = '0.5';
    importButton.style.cursor = 'not-allowed';
    importButton.disabled = true;
  }
}

// Form validation before submit
document.getElementById('importForm').addEventListener('submit', function (e) {
  const fileInput = document.getElementById('teacherFileInput');

  if (!fileInput.files.length) {
    e.preventDefault();
    alert('Please select a file to upload.');
    return false;
  }

  const file = fileInput.files[0];
  const allowedTypes = ['.csv', '.xlsx', '.xls'];
  const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

  if (!allowedTypes.includes(fileExtension)) {
    e.preventDefault();
    alert('Please select a valid CSV or Excel file.');
    return false;
  }

  // Show loading state
  const importButton = document.getElementById('importButton');
  importButton.innerHTML =
    '<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M12 4V2A10 10 0 0 0 2 12h2a8 8 0 0 1 8-8z"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg> Importing...';
  importButton.disabled = true;

  return true;
});

// Handle URL parameters for status messages
document.addEventListener('DOMContentLoaded', function () {
  const urlParams = new URLSearchParams(window.location.search);
  const status = urlParams.get('status');
  const message = urlParams.get('msg');

  if (status && message) {
    const alertType = status === 'success' ? 'alert-success' : 'alert-danger';
    const icon = status === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle';

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertType}`;
    alertDiv.innerHTML = `<i class="mdi ${icon}"></i> ${decodeURIComponent(
      message
    )}`;

    const mainContainer = document.querySelector('.main-container');
    const firstChild = mainContainer.firstElementChild;
    mainContainer.insertBefore(alertDiv, firstChild);

    // Auto-hide after 8 seconds
    setTimeout(function () {
      alertDiv.style.opacity = '0';
      setTimeout(function () {
        if (alertDiv.parentNode) {
          alertDiv.parentNode.removeChild(alertDiv);
        }
      }, 300);
    }, 8000);

    // Clear URL parameters
    const url = new URL(window.location);
    url.searchParams.delete('status');
    url.searchParams.delete('msg');
    window.history.replaceState({}, document.title, url);
  }
});

// Download teacher template function
function downloadTeacherTemplate() {
  const csvContent = 'FirstName,LastName,Email,Password\nJohn,Doe,john.doe@example.com,password123\nJane,Smith,jane.smith@example.com,password456';
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  
  if (link.download !== undefined) {
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'teacher_template.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
}
