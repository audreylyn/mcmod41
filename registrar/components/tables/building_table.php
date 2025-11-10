<div class="card">
    <header class="card-header">
        <div class="new-title-container dept-list">
            <p class="new-title">Building List</p>
        </div>
    </header>
    <div class="card-content">
        <table id="buildingTable" class="adminTable table is-fullwidth is-striped">
            <thead>
                <tr class="titles">
                    <th>Building Name</th>
                    <th>Department</th>
                    <th>Number Of Floors</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="4" class="has-text-centered">No buildings found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $row): ?>
                        <tr>
                            <td data-label="Building Name"><?= htmlspecialchars($row['building_name']) ?></td>
                            <td data-label="Department"><?= htmlspecialchars($row['department']) ?></td>
                            <td data-label="Number Of Floors"><?= htmlspecialchars($row['number_of_floors']) ?></td>
                            <td data-label="Created At"><?= htmlspecialchars($row['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>