<div class="card">
    <header class="card-header">
        <div class="new-title-container dept-list">
            <p class="new-title">Building List</p>
            <a href="javascript:void(0);" id="exportBtn" class="batch" style="display: inline-flex; align-items: center; padding: 8px 16px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                </svg>
                Export
            </a>
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