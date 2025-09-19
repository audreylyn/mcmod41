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

// Get current admin department (global scope)
const adminDepartment = '<?php echo htmlspecialchars($adminDepartment); ?>';

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

  // Add form validation before submission
  const addForm = document.querySelector('.add-student-form');
  if (addForm) {
    addForm.addEventListener('submit', function (e) {
      const yearSectionInput = this.querySelector('#year_section');
      if (yearSectionInput && !validateYearSection(yearSectionInput)) {
        e.preventDefault();
        yearSectionInput.focus();
        return false;
      }
    });
  }

  const editForm = document.getElementById('editForm');
  if (editForm) {
    editForm.addEventListener('submit', function (e) {
      const yearSectionInput = this.querySelector('#edit_year_section');
      if (yearSectionInput && !validateYearSection(yearSectionInput)) {
        e.preventDefault();
        yearSectionInput.focus();
        return false;
      }
    });
  }

  $('#studentTable').DataTable({
    responsive: true,
    language: {
      search: '_INPUT_',
      searchPlaceholder: 'Search students...',
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
});

// Modal functions
function openEditModal(
  studentId,
  firstName,
  lastName,
  email,
  department,
  program,
  yearSection
) {
  document.getElementById('edit_student_id').value = studentId;
  document.getElementById('edit_first_name').value = firstName;
  document.getElementById('edit_last_name').value = lastName;
  document.getElementById('edit_email').value = email;
  document.getElementById('edit_department').value = department;
  document.getElementById('hidden_department').value = department;
  document.getElementById('edit_password').value = '';

  // Populate program dropdown for edit form
  const editProgramSelect = document.getElementById('edit_program_select');
  const editYearSectionInput = document.getElementById('edit_year_section');

  if (editProgramSelect && department) {
    populateProgramDropdown(editProgramSelect, department, program);

    // Set the year section input with the full value
    if (editYearSectionInput) {
      editYearSectionInput.value = yearSection;
      editYearSectionInput.readOnly = false;
    }

    // Add change event to prefill program when program is changed
    editProgramSelect.addEventListener('change', function () {
      if (this.value && editYearSectionInput) {
        // Extract just the year-section part if it exists
        const currentValue = editYearSectionInput.value;
        const yearSectionMatch = currentValue.match(/\s([1-4]-[1-9])$/);
        const yearSectionPart = yearSectionMatch ? yearSectionMatch[1] : '';

        // Set new program with existing year-section or just program
        editYearSectionInput.value =
          this.value + (yearSectionPart ? ' ' + yearSectionPart : ' ');
        editYearSectionInput.readOnly = false;
        editYearSectionInput.placeholder = 'Add year-section (e.g., 4-1)';

        setTimeout(() => {
          editYearSectionInput.focus();
          editYearSectionInput.setSelectionRange(
            editYearSectionInput.value.length,
            editYearSectionInput.value.length
          );
          editYearSectionInput.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
          });
        }, 100);
      } else if (editYearSectionInput) {
        editYearSectionInput.value = '';
        editYearSectionInput.readOnly = true;
        editYearSectionInput.placeholder =
          'Program will appear here, add year-section (e.g., 4-1)';
      }
    });
  }

  document.getElementById('editModal').classList.add('show');
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('show');
}

// Batch upload functionality
function triggerFileUpload() {
  document.getElementById('studentFileInput').click();
}

function handleFileSelect(input) {
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
}

// Ensure form submission works
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
