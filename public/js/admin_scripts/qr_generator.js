document.addEventListener('DOMContentLoaded', function () {
  const qrForm = document.getElementById('qr-form');
  const equipmentSelect = document.getElementById('equipment');
  const customId = document.getElementById('custom-id');
  const customName = document.getElementById('custom-name');
  const customRoom = document.getElementById('custom-room');
  const customBuilding = document.getElementById('custom-building');
  const qrInfo = document.getElementById('qr-info');
  const downloadBtn = document.getElementById('downloadBtn');
  // Get toggle for teacher/student mode
  const roleToggle = document.getElementById('role-toggle');

  let currentQrImageUrl = null;

  // Add event listener to populate form fields when dropdown selection changes
  equipmentSelect.addEventListener('change', function () {
    if (this.value) {
      // Parse the JSON data from the selected option
      const equipmentData = JSON.parse(this.value);

      // Fill the custom fields with the selected equipment's data
      customId.value = equipmentData.id;
      customName.value = equipmentData.name;
      customRoom.value = equipmentData.room;
      customBuilding.value = equipmentData.building;
    } else {
      // Clear the fields if "Select Equipment" is chosen
      customId.value = '';
      customName.value = '';
      customRoom.value = '';
      customBuilding.value = '';
    }
  });

  qrForm.addEventListener('submit', function (e) {
    e.preventDefault();

    let equipmentData;

    if (equipmentSelect.value) {
      equipmentData = JSON.parse(equipmentSelect.value);
    } else if (customId.value && customName.value) {
      equipmentData = {
        id: customId.value,
        name: customName.value,
        room: customRoom.value || 'Unknown',
        building: customBuilding.value || 'Unknown',
      };
    } else {
      alert('Please select equipment or enter custom details');
      return;
    }

    // Create simplified QR code content - URL with only equipment unit ID
    // Use current domain to ensure proper routing
    const currentHost = window.location.host;
    const protocol = window.location.protocol;

    // Uncomment these lines for local development if needed
    // const currentHost = '192.168.8.110';
    // const protocol = 'http:';

    // Determine if we're in a root deployment or subdirectory based on the current URL
    // This automatically adapts to Azure deployment or local development
    let basePath = '';

    // Check if we're in the mcmod41 directory for local development
    if (window.location.pathname.includes('/mcmod41/')) {
      basePath = '/mcmod41';
    }

    const baseUrl = `${protocol}//${currentHost}${basePath}/users/equipment-qr.php`;
    const redirectUrl = new URL(baseUrl);

    // Only pass the unit_id - all other details will be fetched from database
    redirectUrl.searchParams.set('id', equipmentData.id);
    const qrContent = redirectUrl.toString();

    // Debug: Log the generated URL and environment details
    console.log('Current environment:', {
      host: window.location.host,
      protocol: window.location.protocol,
      pathname: window.location.pathname,
      basePath: basePath,
    });
    console.log('Generated simplified QR URL:', qrContent);
    console.log('Equipment data being encoded:', equipmentData);

    // Display equipment info
    qrInfo.innerHTML = `
            <p><strong>${equipmentData.name}</strong></p>
            <p class="equipment-id">ID: ${equipmentData.id}</p>
            <p class="location">Location: ${equipmentData.room}, ${equipmentData.building}</p>
        `;

    // Generate QR code using GoQR API
    generateQRCodeWithGoQR(qrContent, equipmentData);
  });

  // Function to generate QR code using GoQR API
  async function generateQRCodeWithGoQR(qrContent, equipmentData) {
    const qrContainer = document.getElementById('qrcode');

    // Show loading state
    qrContainer.innerHTML =
      '<div class="qr-loading">Generating QR code...</div>';
    downloadBtn.style.display = 'none';

    try {
      // GoQR API endpoint
      const size = '200x200';
      const format = 'png';
      const errorCorrection = 'H'; // High error correction
      const encoding = 'UTF-8';

      // Encode the QR content for URL
      const encodedContent = encodeURIComponent(qrContent);
      const goQrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}&format=${format}&ecc=${errorCorrection}&charset=${encoding}&data=${encodedContent}`;

      console.log('GoQR API URL:', goQrUrl);

      // Create image element
      const qrImage = document.createElement('img');
      qrImage.style.maxWidth = '100%';
      qrImage.style.height = 'auto';
      qrImage.alt = `QR Code for ${equipmentData.name}`;

      // Handle image load
      qrImage.onload = function () {
        qrContainer.innerHTML = '';
        qrContainer.appendChild(qrImage);
        currentQrImageUrl = goQrUrl;

        // Show download button and set up download functionality
        downloadBtn.style.display = 'block';
        downloadBtn.onclick = function () {
          downloadQRCode(goQrUrl, equipmentData);
        };
      };

      // Handle image error
      qrImage.onerror = function () {
        console.error('Failed to load QR code from GoQR API');
        qrContainer.innerHTML =
          '<div class="qr-error">Failed to generate QR code. Please try again.</div>';
      };

      // Set the source to trigger loading
      qrImage.src = goQrUrl;
    } catch (error) {
      console.error('Error generating QR code:', error);
      qrContainer.innerHTML =
        '<div class="qr-error">Error generating QR code. Please try again.</div>';
    }
  }

  // Function to download QR code
  async function downloadQRCode(imageUrl, equipmentData) {
    try {
      const response = await fetch(imageUrl);
      const blob = await response.blob();

      // Create download link
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `QR_${equipmentData.name}_${equipmentData.id}.png`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);

      // Clean up
      window.URL.revokeObjectURL(url);
    } catch (error) {
      console.error('Error downloading QR code:', error);
      alert('Failed to download QR code. Please try again.');
    }
  }
});
