// Function to show the message modal
function showModal(title, message, type = 'success') {
  const modal = document.getElementById('messageModal');
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalMessage').innerHTML = message;

  // Add the type class for specific styling
  modal.classList.add(type);

  // Display the modal and add show class for animation
  modal.style.display = 'block';

  // Trigger reflow for animation to work
  void modal.offsetWidth;

  modal.classList.add('show');
}

// Close the modal when the user clicks on <span> (x)
document.getElementById('closeModal').onclick = function () {
  closeModal();
};

// Close the modal when the user clicks anywhere outside of the modal
window.onclick = function (event) {
  if (event.target == document.getElementById('messageModal')) {
    closeModal();
  } else if (event.target == document.getElementById('editModal')) {
    closeEditModal();
  }
};

// Function to close message modal with animation
function closeModal() {
  const modal = document.getElementById('messageModal');
  modal.classList.remove('show');

  // Wait for animation to complete before hiding
  setTimeout(() => {
    modal.style.display = 'none';
    modal.classList.remove('success', 'error');
  }, 300);
}

// Function to close edit modal (placeholder)
function closeEditModal() {
  const modal = document.getElementById('editModal');
  if (modal) {
    modal.style.display = 'none';
  }
}
