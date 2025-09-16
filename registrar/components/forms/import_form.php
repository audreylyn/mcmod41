<form method="post" action="includes/import_admin.php" enctype="multipart/form-data" style="display: flex;">
    <input type="hidden" name="importSubmit" value="1">
    <button type="button" class="excel" onclick="document.getElementById('adminFileInput').click();" style="border-radius: 0.3em 0 0 0.3em; display: flex; justify-content: center; width: 50px; padding: 0.5rem; background-color: #217346; border: none; cursor: pointer; margin-top: 10px;">
        <svg
            fill="#fff"
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 50 50"
            style="margin: 0;">
            <path d="M28.8125 .03125L.8125 5.34375C.339844 5.433594 0 5.863281 0 6.34375L0 43.65625C0 44.136719 .339844 44.566406 .8125 44.65625L28.8125 49.96875C28.875 49.980469 28.9375 50 29 50C29.230469 50 29.445313 49.929688 29.625 49.78125C29.855469 49.589844 30 49.296875 30 49L30 1C30 .703125 29.855469 .410156 29.625 .21875C29.394531 .0273438 29.105469 -.0234375 28.8125 .03125ZM32 6L32 13L34 13L34 15L32 15L32 20L34 20L34 22L32 22L32 27L34 27L34 29L32 29L32 35L34 35L34 37L32 37L32 44L47 44C48.101563 44 49 43.101563 49 42L49 8C49 6.898438 48.101563 6 47 6ZM36 13L44 13L44 15L36 15ZM6.6875 15.6875L11.8125 15.6875L14.5 21.28125C14.710938 21.722656 14.898438 22.265625 15.0625 22.875L15.09375 22.875C15.199219 22.511719 15.402344 21.941406 15.6875 21.21875L18.65625 15.6875L23.34375 15.6875L17.75 24.9375L23.5 34.375L18.53125 34.375L15.28125 28.28125C15.160156 28.054688 15.035156 27.636719 14.90625 27.03125L14.875 27.03125C14.8125 27.316406 14.664063 27.761719 14.4375 28.34375L11.1875 34.375L6.1875 34.375L12.15625 25.03125ZM36 20L44 20L44 22L36 22ZM36 27L44 27L44 29L36 29ZM36 35L44 35L44 37L36 37Z"></path>
        </svg>
        <input type="file" id="adminFileInput" name="file" accept=".csv,.xlsx,.xls" style="display: none;" onchange="handleFileSelect(this)" />
    </button>
    <button id="importButton" type="submit" disabled style="border-radius: 0 0.3em 0.3em 0; background-color: rgb(41, 114, 45); color: white; border: none; padding: 0.5rem 1rem; cursor: not-allowed; display: flex; align-items: center; gap: 5px; margin-top: 10px; margin-right: 7px;">
        <svg
            fill="#fff"
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24">
            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path>
        </svg>
        Import
    </button>
</form>
<small id="fileName" style="margin-top: 5px; margin-right: 7px;color: #666; font-size: 12px;">No file selected</small>
