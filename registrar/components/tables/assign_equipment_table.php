<div class="card">
    <header class="card-header">
        <div class="new-title-container">
            <p class="new-title">Equipment List</p>
        </div>
    </header>
    <div class="card-content">
        <table id="assignTable" class="adminTable table is-fullwidth is-striped">
            <thead>
                <tr class="titles">
                <th>Name</th>
                <th>Serial Number</th>
                <th>Status</th>
                <th>Room Assignment</th>
                <th>Building</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($equipment_list)): ?>
                    <tr>
                        <td colspan="6" class="has-text-centered">No equipment found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($equipment_list as $equipment): ?>
                        <tr>
                            <td data-label="Name"><?= htmlspecialchars($equipment['name']) ?></td>
                            <td data-label="Serial_Number"><?= htmlspecialchars($equipment['serial_number'] ?? 'N/A') ?></td>
                            <td data-label="Status"><?= htmlspecialchars($equipment['status']) ?></td>
                            <td data-label="Room"><?= htmlspecialchars($equipment['room_name'] ?? 'Unassigned') ?></td>
                            <td data-label="Building"><?= htmlspecialchars($equipment['building_name'] ?? 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>