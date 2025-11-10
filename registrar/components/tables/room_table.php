<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-office-building"></i></span>
            Facility Management
        </p>
        <div class="card-header-icon" style="display: flex; gap: 10px;">
            <button class="button batch" id="addRoomBtn">
                <span class="icon"><i class="mdi mdi-plus"></i></span>
                <span>Add Room</span>
            </button>
            <button type="button" class="download-template-btn" onclick="downloadRoomTemplate()">
                <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                </svg>
                Template
            </button>
            <form id="importForm" action="includes/import_rooms.php" class="form-data" method="post" enctype="multipart/form-data" style="display: flex;">
                <button type="button" class="excel" style="border-radius: 0.3em 0 0 0.3em; display: flex; justify-content: center; width: 50px; padding: 0.5rem;">
                    <svg
                        fill="#fff"
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 50 50"
                        style="margin: 0;">
                        <path
                            d="M28.8125 .03125L.8125 5.34375C.339844 
                        5.433594 0 5.863281 0 6.34375L0 43.65625C0 
                        44.136719 .339844 44.566406 .8125 44.65625L28.8125 
                        49.96875C28.875 49.980469 28.9375 50 29 50C29.230469 
                        50 29.445313 49.929688 29.625 49.78125C29.855469 49.589844 
                        30 49.296875 30 49L30 1C30 .703125 29.855469 .410156 29.625 
                        .21875C29.394531 .0273438 29.105469 -.0234375 28.8125 .03125ZM32 
                        6L32 13L34 13L34 15L32 15L32 20L34 20L34 22L32 22L32 27L34 27L34 
                        29L32 29L32 35L34 35L34 37L32 37L32 44L47 44C48.101563 44 49 
                        43.101563 49 42L49 8C49 6.898438 48.101563 6 47 6ZM36 13L44 
                        13L44 15L36 15ZM6.6875 15.6875L11.8125 15.6875L14.5 21.28125C14.710938 
                        21.722656 14.898438 22.265625 15.0625 22.875L15.09375 22.875C15.199219 
                        22.511719 15.402344 21.941406 15.6875 21.21875L18.65625 15.6875L23.34375 
                        15.6875L17.75 24.9375L23.5 34.375L18.53125 34.375L15.28125 
                        28.28125C15.160156 28.054688 15.035156 27.636719 14.90625 
                        27.03125L14.875 27.03125C14.8125 27.316406 14.664063 27.761719 
                        14.4375 28.34375L11.1875 34.375L6.1875 34.375L12.15625 25.03125ZM36 
                        20L44 20L44 22L36 22ZM36 27L44 27L44 29L36 29ZM36 35L44 35L44 37L36 37Z"></path>
                    </svg>
                    <input type="file" name="file" id="csvFile" class="file" accept=".csv" required />
                </button>
                <button id="importButton" class="container-btn-file" type="submit" name="importSubmit" style="border-radius: 0 0.3em 0.3em 0;">
                    <svg
                        fill="#fff"
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24">
                        <path
                            d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path>
                    </svg>
                    Import
                </button>
            </form>
        </div>
    </header>
    <div class="card-content">
        <table id="facilityTable" class="adminTable table is-fullwidth is-striped is-hoverable">
            <thead>
                <tr>
                    <th>Building Name</th>
                    <th>Department</th>
                    <th>Floors</th>
                    <th>Room Name</th>
                    <th>Room Type</th>
                    <th>Capacity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = $result->fetch_assoc()):
                    $building_name = htmlspecialchars($row['building_name'] ?? '', ENT_QUOTES, 'UTF-8');
                    $department = htmlspecialchars($row['department'] ?? '', ENT_QUOTES, 'UTF-8');
                    $floors = htmlspecialchars($row['number_of_floors'] ?? '', ENT_QUOTES, 'UTF-8');
                    $room_id = htmlspecialchars($row['room_id'] ?? '', ENT_QUOTES, 'UTF-8');
                    $room_name = htmlspecialchars($row['room_name'] ?? '', ENT_QUOTES, 'UTF-8');
                    $room_type = htmlspecialchars($row['room_type'] ?? '', ENT_QUOTES, 'UTF-8');
                    $capacity = htmlspecialchars($row['capacity'] ?? '', ENT_QUOTES, 'UTF-8');
                ?>
                    <tr>
                        <td data-label="Building Name"><?= $building_name ?></td>
                        <td data-label="Department"><?= $department ?></td>
                        <td data-label="Floors"><?= $floors ?></td>
                        <td data-label="Room Name"><?= $room_name ?: 'N/A' ?></td>
                        <td data-label="Room Type"><?= $room_type ?: 'N/A' ?></td>
                        <td data-label="Capacity"><?= $capacity ?: 'N/A' ?></td>
                        <td data-label="Actions" class="action-buttons">
                            <?php if ($room_id): ?>
                                <button class="styled-button is-small" onclick='openEditModal(<?= json_encode($row) ?>)'>
                                    <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                </button>
                                <button class="styled-button is-reset is-small" onclick="deleteRoom('<?= urlencode($room_id) ?>')">
                                    <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>