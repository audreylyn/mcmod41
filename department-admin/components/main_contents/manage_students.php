<!-- Tab navigation -->
<div class="tab-nav">
    <button class="tab-btn active" data-tab="tab-list">Student List</button>
    <button class="tab-btn" data-tab="tab-add">Add New Student</button>
</div>

<!-- Student List Tab -->
<div id="tab-list" class="tab-content active">
    <div class="table-container">
        <div class="card">
            <header class="card-header">
                <div class="new-title-container">
                    <p class="new-title">STUDENT LIST</p>
                </div>
            </header>
            <div class="card-content">
                <table id="studentTable" class="adminTable student-table display is-fullwidth">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Department</th>
                            <th>Program</th>
                            <th>Year & Section</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($studentsResult->num_rows > 0): ?>
                            <?php while ($student = $studentsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['FirstName']) ?></td>
                                    <td><?= htmlspecialchars($student['LastName']) ?></td>
                                    <td><?= htmlspecialchars($student['Department']) ?></td>
                                    <td><?= htmlspecialchars($student['Program']) ?></td>
                                    <td><?= htmlspecialchars($student['YearSection']) ?></td>
                                    <td><?= htmlspecialchars($student['Email']) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="btn-edit" onclick="openEditModal(
                                                <?= $student['StudentID'] ?>,
                                                '<?= htmlspecialchars($student['FirstName']) ?>',
                                                '<?= htmlspecialchars($student['LastName']) ?>',
                                                '<?= htmlspecialchars($student['Email']) ?>',
                                                '<?= htmlspecialchars($student['Department']) ?>',
                                                '<?= htmlspecialchars($student['Program']) ?>',
                                                '<?= htmlspecialchars($student['YearSection']) ?>'
                                            )">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </button>
                                            <button type="button" class="btn-delete" onclick="openDeleteModal(<?= $student['StudentID'] ?>)">
                                                <i class="mdi mdi-delete"></i> Delete
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
    </div>
</div>

<!-- Add New Student Tab -->
<div id="tab-add" class="tab-content">
    <div class="card add-student-card">
        <header class="card-header">
            <div class="new-title-container" style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
                <p class="new-title"><i class="mdi mdi-account-plus"></i> ADD NEW STUDENT</p>
                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                    <form id="importForm" method="post" action="includes/import_students.php" enctype="multipart/form-data" style="display: flex;">
                        <input type="hidden" name="importSubmit" value="1">
                        <button class="excel" type="button" onclick="document.getElementById('studentFileInput').click();" style="border-radius: 0.3em 0 0 0.3em; display: flex; justify-content: center; width: 50px; padding: 0.5rem; background-color: #217346; border: none; cursor: pointer;">
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
                            <input type="file" id="studentFileInput" name="file" accept=".csv,.xlsx,.xls" style="display: none;" onchange="handleFileSelect(this)" />
                        </button>
                        <button id="importButton" type="submit" class="import-btn-acc" disabled style="opacity: 0.5; cursor: not-allowed;">
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
                        <button type="button" class="download-template-btn" onclick="downloadStudentTemplate()">
                            <svg
                                fill="#fff"
                                xmlns="http://www.w3.org/2000/svg"
                                width="20"
                                height="20"
                                viewBox="0 0 24 24">
                                <path
                                    d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                            </svg>
                            Template
                        </button>
                    </form>
                    <small id="fileName" style="margin-top: 5px; color: #666; font-size: 12px;">No file selected</small>
                </div>
            </div>
        </header>
        <div class="card-content">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="add-student-form">
                <input type="hidden" name="add_student" value="1">
                <div class="form-grid">
                    <div class="form-grid-column">
                        <div class="field">
                            <label class="label">First Name</label>
                            <div class="control has-icons-left">
                                <input class="input is-rounded" type="text" name="first_name" placeholder="First Name" pattern="[A-Za-z\s]+" required>
                                <span class="icon is-small is-left">
                                    <i class="mdi mdi-account"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="field">
                            <label class="label">Last Name</label>
                            <div class="control has-icons-left">
                                <input class="input is-rounded" type="text" name="last_name" placeholder="Last Name" pattern="[A-Za-z\s]+" required>
                                <span class="icon is-small is-left">
                                    <i class="mdi mdi-account-card-details"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="field">
                            <label class="label">Email Address</label>
                            <div class="control has-icons-left">
                                <input class="input is-rounded" type="email" name="email" placeholder="student@example.com" required>
                                <span class="icon is-small is-left">
                                    <i class="mdi mdi-email"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-grid-column">
                        <div class="field">
                            <label class="label">Program</label>
                            <div class="control has-icons-left">
                                <div class="select is-fullwidth is-rounded">
                                    <select name="program" id="program_select" data-department="<?php echo htmlspecialchars($adminDepartment); ?>" required>
                                        <option value="">Select Program</option>
                                    </select>
                                </div>
                                <span class="icon is-small is-left">
                                    <i class="mdi mdi-book-open-variant"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="field">
                            <label class="label">Year & Section</label>
                            <div class="control has-icons-left">
                                <input class="input is-rounded year-section-input" type="text" name="year_section" id="year_section" placeholder="Program will appear here, add year-section (e.g., 4-1)" readonly required>
                                <span class="icon is-small is-left">
                                    <i class="mdi mdi-school"></i>
                                </span>
                            </div>
                            <p class="year-section-help">Select a program first, then add year-section (e.g., 4-1 for 4th year section 1)</p>
                        </div>
                        
                        <div class="field">
                            <label class="label">Department</label>
                            <div class="control has-icons-left">
                                <input class="input is-rounded" type="text" value="<?php echo htmlspecialchars($adminDepartment); ?>" disabled>
                                <span class="icon is-small is-left">
                                    <i class="mdi mdi-domain"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="divider">
                    <span>Security</span>
                </div>
                
                <div class="password-field-container">
                    <div class="field">
                        <label class="label">Password</label>
                        <div class="control has-icons-left has-icons-right">
                            <input class="input is-rounded" type="password" name="password" id="add_password" placeholder="Minimum 8 characters" minlength="8" required>
                            <span class="icon is-small is-left">
                                <i class="mdi mdi-lock"></i>
                            </span>
                            <span class="icon is-small is-right toggle-password" onclick="togglePasswordVisibility('add_password')">
                                <i class="mdi mdi-eye"></i>
                            </span>
                        </div>
                        <p class="help">Password must be at least 8 characters long</p>
                    </div>
                </div>
                
                <div class="form-actions-container">
                    <div class="form-actions">
                        <button type="submit" class="submit-button">
                            <i class="mdi mdi-account-plus"></i>
                            <span>Add Student</span>
                        </button>
                        <button type="reset" class="reset-button">
                            <i class="mdi mdi-refresh"></i>
                            <span>Reset</span>
                        </button>
                    </div>
                </div>
                
                <div class="divider">
                    <span>OR</span>
                </div>
                
                <div class="batch-upload-info">
                    <div class="info-card">
                        <h4><i class="mdi mdi-information"></i> Batch Upload Instructions</h4>
                        <p>Upload a CSV file with the following column format:</p>
                        <div class="csv-format">
                            <strong>FirstName, LastName, Email, Program, YearSection, Password</strong>
                        </div>
                        <p class="help-text">
                            <i class="mdi mdi-lightbulb"></i> 
                            Make sure your CSV file has a header row and follows the exact column order shown above.
                            All students will be added to the <strong><?php echo htmlspecialchars($adminDepartment); ?></strong> department.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editModal" class="modal">
    <div>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="mdi mdi-account-edit"></i> Edit Student
                </h5>
                <button type="button" class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <input type="hidden" name="edit_student" value="1">
                    <input type="hidden" id="edit_student_id" name="student_id">
                    
                    <div class="form-grid">
                        <!-- Left Column -->
                        <div class="form-grid-column">
                            <div class="field">
                                <label class="label">First Name</label>
                                <div class="control has-icons-left">
                                    <input class="input is-rounded" type="text" id="edit_first_name" name="first_name" pattern="[A-Za-z\s]+" required>
                                    <span class="icon is-small is-left">
                                        <i class="mdi mdi-account"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="field">
                                <label class="label">Last Name</label>
                                <div class="control has-icons-left">
                                    <input class="input is-rounded" type="text" id="edit_last_name" name="last_name" pattern="[A-Za-z\s]+" required>
                                    <span class="icon is-small is-left">
                                        <i class="mdi mdi-account-card-details"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="field">
                                <label class="label">Email Address</label>
                                <div class="control has-icons-left">
                                    <input class="input is-rounded" type="email" id="edit_email" name="email" required>
                                    <span class="icon is-small is-left">
                                        <i class="mdi mdi-email"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="form-grid-column">
                            <div class="field">
                                <label class="label">Department</label>
                                <div class="control has-icons-left">
                                    <input class="input is-rounded" type="text" id="edit_department" disabled>
                                    <span class="icon is-small is-left">
                                        <i class="mdi mdi-domain"></i>
                                    </span>
                                    <input type="hidden" id="hidden_department" name="department">
                                </div>
                            </div>
                            
                            <div class="field">
                                <label class="label">Program</label>
                                <div class="control has-icons-left">
                                    <div class="select is-fullwidth is-rounded">
                                        <select id="edit_program_select" name="program" data-department="<?php echo htmlspecialchars($adminDepartment); ?>" required>
                                            <option value="">Select Program</option>
                                        </select>
                                    </div>
                                    <span class="icon is-small is-left">
                                        <i class="mdi mdi-book-open-variant"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="field">
                                <label class="label">Year & Section</label>
                                <div class="control has-icons-left">
                                    <input class="input is-rounded year-section-input" type="text" id="edit_year_section" name="year_section" placeholder="Program will appear here, add year-section (e.g., 4-1)" readonly required>
                                    <span class="icon is-small is-left">
                                        <i class="mdi mdi-school"></i>
                                    </span>
                                </div>
                                <p class="year-section-help">Select a program first, then add year-section (e.g., 4-1 for 4th year section 1)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="divider">
                        <span>Security (Optional)</span>
                    </div>
                    
                    <div class="password-field-container">
                        <div class="field">
                            <label class="label">New Password (Leave blank to keep current password)</label>
                            <div class="control has-icons-left has-icons-right">
                                <input class="input is-rounded" type="password" id="edit_password" name="password" minlength="8">
                                <span class="icon is-small is-left">
                                    <i class="mdi mdi-lock"></i>
                                </span>
                                <span class="icon is-small is-right toggle-password" onclick="togglePasswordVisibility('edit_password')">
                                    <i class="mdi mdi-eye"></i>
                                </span>
                            </div>
                            <p class="help">Only fill this if you want to change the password</p>
                        </div>
                    </div>
                
                    <div class="modal-footer">
                        <button type="button" class="reset-button" onclick="closeModal('editModal')">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        <button type="submit" class="submit-button">
                            <i class="mdi mdi-check"></i> Update Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this student? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="reset-button" onclick="closeModal('deleteModal')">Cancel</button>
                <a href="#" id="confirmDelete" class="submit-button" style="background-color: #dc3545;">Delete</a>
            </div>
        </div>
    </div>
</div>