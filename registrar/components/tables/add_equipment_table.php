<div class="card">
    <header class="card-header">
        <div class="new-title-container">
            <p class="new-title">Equipment List</p>
        </div>
    </header>
    <div class="card-content">
        <table id="equipmentTable" class="adminTable table is-fullwidth is-striped">
            <thead>
                <tr class="titles">
                    <th>Name</th>
                    <th>Description</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($equipment_list)): ?>
                    <tr>
                        <td colspan="4" class="has-text-centered">No equipment found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($equipment_list as $equipment): ?>
                        <tr>
                            <td data-label="Name"><?= htmlspecialchars($equipment['name']) ?></td>
                            <td data-label="Description"><?= htmlspecialchars($equipment['description']) ?></td>
                            <td data-label="Category"><?= htmlspecialchars($equipment['category']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>