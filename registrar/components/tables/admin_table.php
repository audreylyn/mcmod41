<div class="card">
    <header class="card-header">
        <div class="new-title-container dept-list">
            <p class="new-title">Department Admin List</p>
            <div style="display: flex; gap:5px;  align-items: center;">
                <button type="button" class="download-template-btn" onclick="downloadAdminTemplate()">
                    <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                    </svg>
                    Template
                </button>
                <button id="exportButton" class="batch" style="display: inline-flex; align-items: center; padding: 8px 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </header>
    <div class="card-content">
        <table id="adminTable" class="adminTable table is-fullwidth is-striped">
            <thead>
                <tr class="titles">
                    <th>FirstName</th>
                    <th>LastName</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" class="has-text-centered">No admins found.</td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="FirstName"><?= htmlspecialchars($row['FirstName']) ?></td>
                            <td data-label="LastName"><?= htmlspecialchars($row['LastName']) ?></td>
                            <td data-label="Department"><?= htmlspecialchars($row['Department']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($row['Email']) ?></td>
                            <td class="action-buttons">
                                <div class="button-container">
                                    <button class="is-small styled-button" 
                                        onclick="openEditModal('<?= htmlspecialchars($row['AdminID']) ?>', 
                                        '<?= htmlspecialchars($row['FirstName']) ?>', 
                                        '<?= htmlspecialchars($row['LastName']) ?>', 
                                        '<?= htmlspecialchars($row['Department']) ?>', 
                                        '<?= htmlspecialchars($row['Email']) ?>')">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button class="is-small styled-button is-reset"
                                        onclick="deleteAdmin(<?= htmlspecialchars($row['AdminID']) ?>)">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
