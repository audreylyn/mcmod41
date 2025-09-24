// Programs data organized by department (global scope)
const programsByDepartment = {
  Accountancy: ['BSA', 'BSAT', 'BSLM'],
  'Business Administration': ['BSBA-FM', 'BSBA-M', 'BSBA-MKT'],
  'Hospitality Management': ['BSHM', 'BSTrM'],
  'Education and Arts': [
    'BEEd-PSE',
    'BEEd-GEN',
    'BSE-BIO',
    'BSE-ENG',
    'BSE-FIL',
    'BSE-MATH',
    'AB-PSYCH',
    'BSSW',
    'BLIS',
    'BPE-SPE',
    'BPE-SWM',
    'CPTE',
  ],
  'Criminal Justice': ['BSCrim'],
};

// Function to populate program dropdown (global scope)
function populateProgramDropdown(
  selectElement,
  department,
  selectedValue = ''
) {
  const programs = programsByDepartment[department] || [];

  // Clear existing options except the first one
  selectElement.innerHTML = '<option value="">Select Program</option>';

  // Add programs for the department
  programs.forEach((program) => {
    const option = document.createElement('option');
    option.value = program;
    option.textContent = program;
    if (program === selectedValue) {
      option.selected = true;
    }
    selectElement.appendChild(option);
  });
}

// Initialize DataTables
$(document).ready(function () {
  // Get the department from a data attribute
  const adminDepartment = document
    .getElementById('program_select')
    .getAttribute('data-department');
  console.log('Admin Department:', adminDepartment);

  // Populate the add form program dropdown on page load
  const addProgramSelect = document.getElementById('program_select');
  if (addProgramSelect && adminDepartment) {
    populateProgramDropdown(addProgramSelect, adminDepartment);

    // Add change event to prefill program and focus on year section
    addProgramSelect.addEventListener('change', function () {
      const yearSectionInput = document.getElementById('year_section');
      if (this.value && yearSectionInput) {
        // Set the program as the base value
        yearSectionInput.value = this.value + ' ';
        yearSectionInput.readOnly = false;
        yearSectionInput.placeholder = 'Add year-section (e.g., 4-1)';

        setTimeout(() => {
          yearSectionInput.focus();
          // Set cursor at the end after the program name
          yearSectionInput.setSelectionRange(
            yearSectionInput.value.length,
            yearSectionInput.value.length
          );
          yearSectionInput.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
          });
        }, 100);
      } else if (yearSectionInput) {
        // Reset if no program selected
        yearSectionInput.value = '';
        yearSectionInput.readOnly = true;
        yearSectionInput.placeholder =
          'Program will appear here, add year-section (e.g., 4-1)';
      }
    });
  }

  // Get the edit dropdown and populate it if it exists
  const editProgramSelect = document.getElementById('edit_program_select');
  if (editProgramSelect && adminDepartment) {
    // The populateEditProgramDropdown function will be called when the edit modal is opened
    editProgramSelect.setAttribute('data-department', adminDepartment);
  }

  // Add year section validation
  function setupYearSectionValidation() {
    const yearSectionInputs = document.querySelectorAll('.year-section-input');

    yearSectionInputs.forEach((input) => {
      input.addEventListener('input', function () {
        validateYearSection(this);
      });

      input.addEventListener('blur', function () {
        validateYearSection(this);
      });
    });
  }

  function validateYearSection(input) {
    const value = input.value.trim();
    const helpElement = input
      .closest('.field')
      .querySelector('.year-section-help');

    // Check if it contains a program name followed by year-section pattern
    const programYearPattern = /^.+\s[1-4]-[1-9]$/;

    if (value && !programYearPattern.test(value)) {
      input.classList.add('invalid');
      if (helpElement) {
        helpElement.textContent =
          'Invalid format! Should be: Program Year-Section (e.g., BSA 4-1)';
        helpElement.classList.add('error');
      }
      return false;
    } else if (value) {
      input.classList.remove('invalid');
      if (helpElement) {
        helpElement.textContent = 'Correct format: Program + Year-Section';
        helpElement.classList.remove('error');
      }
      return true;
    } else {
      input.classList.remove('invalid');
      if (helpElement) {
        helpElement.textContent =
          'Select a program first, then add year-section (e.g., 4-1 for 4th year section 1)';
        helpElement.classList.remove('error');
      }
      return false;
    }
  }

  // Initialize year section validation
  setupYearSectionValidation();

  // Email validation function
  function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  // Form validation function
  function validateStudentForm(form, event) {
    const firstName = form.querySelector('[name="first_name"]').value.trim();
    const lastName = form.querySelector('[name="last_name"]').value.trim();
    const email = form.querySelector('[name="email"]').value.trim();
    const program = form.querySelector('[name="program"]').value.trim();
    const yearSection = form
      .querySelector('[name="year_section"]')
      .value.trim();
    const password = form.querySelector('[name="password"]').value;
    const isEditForm = form.id === 'editForm';

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

    if (!program) {
      errors.push('Program is required.');
      isValid = false;
    }

    if (!yearSection) {
      errors.push('Year section is required.');
      isValid = false;
    }

    // For add form, password is required
    if (!isEditForm && !password) {
      errors.push('Password is required.');
      isValid = false;
    }

    // Validate password length if provided
    if (password && password.length < 8) {
      errors.push('Password must be at least 8 characters long.');
      isValid = false;
    }

    // Validate year section format
    const yearSectionInput = form.querySelector('[name="year_section"]');
    if (yearSectionInput && !validateYearSection(yearSectionInput)) {
      errors.push('Year section format is invalid. Use format: PROGRAM 1-1');
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

  // Add form validation before submission
  const addForm = document.querySelector('.add-student-form');
  if (addForm) {
    addForm.addEventListener('submit', function (e) {
      return validateStudentForm(this, e);
    });
  }

  const editForm = document.getElementById('editForm');
  if (editForm) {
    editForm.addEventListener('submit', function (e) {
      return validateStudentForm(this, e);
    });
  }

  // Toggle password visibility
  window.togglePasswordVisibility = function (inputId) {
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
  };

  // DataTable initialization
  $('#studentTable').DataTable({
    paging: true,
    searching: true,
    ordering: true,
    info: true,
    responsive: true,
    pageLength: 10,
    dom: '<"top d-flex align-items-center justify-content-between mb-3"<"d-flex align-items-center"l>f>rt<"bottom"ip><"clear">',
    columnDefs: [
      { orderable: false, targets: -1 }, // Disable sorting on the actions column
    ],
    language: {
      paginate: {
        previous: "<i class='mdi mdi-chevron-left'></i>",
        next: "<i class='mdi mdi-chevron-right'></i>",
      },
    },
  });

  // Auto-hide alerts after 3 seconds
  setTimeout(function () {
    $('.alert').fadeOut('fast');
  }, 2000);

  // Handle URL parameters for status messages
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

    // Auto-hide after 3 seconds
    setTimeout(function () {
      alertDiv.style.opacity = '0';
      setTimeout(function () {
        if (alertDiv.parentNode) {
          alertDiv.parentNode.removeChild(alertDiv);
        }
      }, 200);
    }, 3000);

    // Clear URL parameters
    const url = new URL(window.location);
    url.searchParams.delete('status');
    url.searchParams.delete('msg');
    window.history.replaceState({}, document.title, url);
  }

  // Tab functionality
  const tabBtns = document.querySelectorAll('.tab-btn');
  const tabContents = document.querySelectorAll('.tab-content');

  tabBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      // Remove active class from all buttons and contents
      tabBtns.forEach((b) => b.classList.remove('active'));
      tabContents.forEach((c) => c.classList.remove('active'));

      // Add active class to clicked button
      btn.classList.add('active');

      // Show corresponding content
      const tabId = btn.getAttribute('data-tab');
      document.getElementById(tabId).classList.add('active');
    });
  });

  // Handle file selection for import
  window.handleFileSelect = function (input) {
    const fileName = input.files[0] ? input.files[0].name : '';
    const fileInfo = document.getElementById('fileName');

    if (fileName) {
      // Validate file type
      const allowedTypes = ['.csv', '.xlsx', '.xls'];
      const fileExtension = fileName
        .toLowerCase()
        .substring(fileName.lastIndexOf('.'));

      if (!allowedTypes.includes(fileExtension)) {
        alert('Please select a valid CSV or Excel file (.csv, .xlsx, .xls)');
        input.value = '';
        fileInfo.textContent = 'No file selected';
        fileInfo.style.color = '#666';
        return;
      }

      fileInfo.innerHTML = `
        <span style="color: #4caf50;">
          <i class="mdi mdi-file-check"></i>
          Selected: <strong>${fileName}</strong>
        </span>
      `;

      // Enable the import button
      const importButton = document.getElementById('importButton');
      importButton.style.opacity = '1';
      importButton.style.cursor = 'pointer';
      importButton.disabled = false;
    } else {
      fileInfo.textContent = 'No file selected';
      fileInfo.style.color = '#666';

      // Disable the import button
      const importButton = document.getElementById('importButton');
      importButton.style.opacity = '0.5';
      importButton.style.cursor = 'not-allowed';
      importButton.disabled = true;
    }
  };

  // Batch upload functionality
  window.triggerFileUpload = function () {
    document.getElementById('studentFileInput').click();
  };
});

// Ensure form submission works - moved outside document.ready
document.addEventListener('DOMContentLoaded', function () {
  const importForm = document.getElementById('importForm');
  if (importForm) {
    importForm.addEventListener('submit', function (e) {
      const fileInput = document.getElementById('studentFileInput');
      if (!fileInput.files || fileInput.files.length === 0) {
        e.preventDefault();
        alert('Please select a file before uploading.');
        return false;
      }

      // Show loading state
      const importButton = document.getElementById('importButton');
      importButton.innerHTML =
        '<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M12 4V2A10 10 0 0 0 2 12h2a8 8 0 0 1 8-8z"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg> Importing...';
      importButton.disabled = true;

      return true;
    });
  }
});

// Function to open the edit modal
function openEditModal(
  studentId,
  firstName,
  lastName,
  email,
  department,
  program,
  yearSection
) {
  // Set form values
  document.getElementById('edit_student_id').value = studentId;
  document.getElementById('edit_first_name').value = firstName;
  document.getElementById('edit_last_name').value = lastName;
  document.getElementById('edit_email').value = email;
  document.getElementById('edit_department').value = department;
  document.getElementById('hidden_department').value = department;

  // Populate program dropdown for the department
  const editProgramSelect = document.getElementById('edit_program_select');
  const adminDepartment = editProgramSelect.getAttribute('data-department');
  populateProgramDropdown(editProgramSelect, adminDepartment, program);

  // Set year section
  const yearSectionInput = document.getElementById('edit_year_section');
  yearSectionInput.value = yearSection;
  yearSectionInput.readOnly = false;

  // Show the modal
  document.getElementById('editModal').classList.add('show');

  // Add change event to prefill program for edit form
  editProgramSelect.addEventListener('change', function () {
    if (this.value && yearSectionInput) {
      // Extract just the year-section part if it exists
      const yearSectionPart = yearSection.split(' ').slice(1).join(' ');
      yearSectionInput.value = this.value + ' ' + (yearSectionPart || '');
      yearSectionInput.readOnly = false;
      yearSectionInput.placeholder = 'Add year-section (e.g., 4-1)';

      setTimeout(() => {
        yearSectionInput.focus();
        yearSectionInput.setSelectionRange(
          yearSectionInput.value.length,
          yearSectionInput.value.length
        );
      }, 100);
    } else {
      yearSectionInput.value = '';
      yearSectionInput.readOnly = true;
      yearSectionInput.placeholder =
        'Program will appear here, add year-section (e.g., 4-1)';
    }
  });
}

// Function to close modals
function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('show');
}

// Download student template function
function downloadStudentTemplate() {
  const csvContent = 'FirstName,LastName,Email,Program,YearSection,Password\nJohn,Doe,john.doe@example.com,BSA,BSA 4-1,password123\nJane,Smith,jane.smith@example.com,BSBA-FM,BSBA-FM 3-2,password456';
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  
  if (link.download !== undefined) {
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'student_template.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
}
