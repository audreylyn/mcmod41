function handleFileSelect(input) {
  const fileName = input.files[0] ? input.files[0].name : '';
  const fileInfo = document.getElementById('fileName');
  const importButton = document.getElementById('importButton');

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
      importButton.disabled = true;
      importButton.style.opacity = '0.5';
      importButton.style.cursor = 'not-allowed';
      return;
    }

    fileInfo.innerHTML = `
            <span style="color: #4caf50;">
                <i class="mdi mdi-file-check"></i>
                Selected: <strong>${fileName}</strong>
            </span>
        `;

    importButton.disabled = false;
    importButton.style.opacity = '1';
    importButton.style.cursor = 'pointer';
  } else {
    fileInfo.textContent = 'No file selected';
    fileInfo.style.color = '#666';

    importButton.disabled = true;
    importButton.style.opacity = '0.5';
    importButton.style.cursor = 'not-allowed';
  }
}
