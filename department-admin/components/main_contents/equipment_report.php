<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-wrench"></i></span>
            Equipment Issue Reports
        </p>
    </header>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (!$viewReportId): ?>
        <!-- Dashboard Summary -->
        <div class="card-content">
            <div class="dashboard-tiles">
                <div class="tile tile-pending">
                    <div class="tile-count"><?php echo $countData['pending_count']; ?></div>
                    <div class="tile-label">Pending</div>
                </div>
                <div class="tile tile-in-progress">
                    <div class="tile-count"><?php echo $countData['in_progress_count']; ?></div>
                    <div class="tile-label">In Progress</div>
                </div>
                <div class="tile tile-resolved">
                    <div class="tile-count"><?php echo $countData['resolved_count']; ?></div>
                    <div class="tile-label">Resolved</div>
                </div>
                <div class="tile tile-rejected">
                    <div class="tile-count"><?php echo $countData['rejected_count']; ?></div>
                    <div class="tile-label">Rejected</div>
                </div>
                <div class="tile tile-total">
                    <div class="tile-count"><?php echo $countData['total_count']; ?></div>
                    <div class="tile-label">Total Reports</div>
                </div>
            </div>

            <!-- Reports List with DataTables -->
            <div class="table-container">
                <!-- Custom filters above the table -->
                <div style="background-color: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1rem;">

                        <div>
                            <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Date Range</label>
                            <select id="date-filter" class="form-select" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;">
                                <option value="" selected>All Time</option>
                                <option value="7">Last 7 days</option>
                                <option value="30">Last 30 days</option>
                                <option value="90">Last 90 days</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Location</label>
                            <select id="location-filter" class="form-select" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;">
                                <option value="">All Locations</option>
                                <?php
                                // Generate unique locations from dataset
                                $locations = [];
                                mysqli_data_seek($reportsResult, 0);
                                while ($row = $reportsResult->fetch_assoc()) {
                                    $location = $row['building_name'] . ' - ' . $row['room_name'];
                                    if (!empty($row['building_name']) && !empty($row['room_name']) && !in_array($location, $locations)) {
                                        $locations[] = $location;
                                        echo '<option value="' . htmlspecialchars($location) . '">' . htmlspecialchars($location) . '</option>';
                                    }
                                }
                                // Reset the result pointer to beginning
                                mysqli_data_seek($reportsResult, 0);
                                ?>
                            </select>
                        </div>
                        <div style="display: flex; align-items: flex-end;">
                            <button id="reset-filters" class="back-btn" style="width: 100%; justify-content: center; border: none; background-color: #f1f5f9; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500; cursor: pointer;">Reset</button>
                        </div>
                    </div>


                </div>

                <!-- Show entries dropdown -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center;">
                        <span style="margin-right: 0.5rem; font-weight: 500;">Show</span>
                        <select id="entries-filter" class="form-select" style="width: auto; min-width: 70px; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="-1">All</option>
                        </select>
                        <span style="margin-left: 0.5rem; font-weight: 500;">entries</span>
                    </div>

                    <div>
                        <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Status</label>
                        <select id="status-filter" class="form-select" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <!-- Standalone search bar -->
                    <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
                        <div>
                            <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Search</label>
                            <input type="text" id="customSearch" class="form-select" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;" placeholder="Search by equipment, room, student...">
                        </div>
                    </div>
                </div>

                <table id="equipmentReportTable" class="table is-fullwidth is-striped">
                    <thead>
                        <tr class="titles">
                            <th>Reference Number</th>
                            <th>Equipment</th>
                            <th>Location</th>
                            <th>Issue Type</th>
                            <th>Reported By</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Condition</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($reportsResult->num_rows > 0): ?>
                            <?php while ($row = $reportsResult->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="Reference Number"><span class="reference-number"><?php echo !empty($row['reference_number']) ? htmlspecialchars($row['reference_number']) : 'EQ' . str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></span></td>
                                    <td data-label="Equipment">
                                        <?php echo htmlspecialchars($row['equipment_name'] ?? 'N/A'); ?>
                                    </td>
                                    <td data-label="Location"><?php echo htmlspecialchars($row['room_name'] ?? 'N/A') . ' (' . htmlspecialchars($row['building_name'] ?? 'N/A') . ')'; ?></td>
                                    <td data-label="Issue Type"><?php echo htmlspecialchars($row['issue_type']); ?></td>
                                    <td data-label="Reported By"><?php echo htmlspecialchars($row['reporter_name'] ?? 'Unknown'); ?></td>
                                    <td data-label="Date"><?php echo date('M d, Y', strtotime($row['reported_at'])); ?></td>
                                    <td data-label="Status">
                                        <span class="status-badge status-<?php echo $row['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                        </span>
                                    </td>
                                    <td data-label="Condition">
                                        <span class="condition-badge condition-<?php echo $row['statusCondition'] ?? 'unknown'; ?>">
                                            <?php echo $row['statusCondition'] ? ucfirst(str_replace('_', ' ', $row['statusCondition'])) : 'Unknown'; ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="?view=<?php echo $row['id']; ?>" class="view-btn" title="View Details for <?php echo htmlspecialchars($row['reference_number']); ?>">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="has-text-centered">No reports found matching your criteria.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- Report Detail View -->
        <?php if ($reportDetail): ?>
            <div class="card-content">
                <div class="detail-header">
                    <a href="dept_equipment_report.php" class="back-btn">
                        <i class="mdi mdi-arrow-left"></i> Back to List
                    </a>
                    <span class="status-badge status-<?php echo $reportDetail['status']; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $reportDetail['status'])); ?>
                    </span>
                </div>

                <div class="detail-grid">
                    <div>
                        <div class="detail-section">
                            <h3 class="section-title">Equipment Information</h3>
                            <div class="info-group">
                                <div class="info-label">Serial Number</div>
                                <div class="info-value"><?php echo htmlspecialchars($reportDetail['serial_number'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($reportDetail['reporter_name'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Email</div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($reportDetail['reporter_type'] == 'Student' ?
                                        $reportDetail['student_email'] :
                                        $reportDetail['teacher_email']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="detail-section">
                            <h3 class="section-title">Issue Information</h3>
                            <div class="info-group">
                                <div class="info-label">Reference Number</div>
                                <div class="info-value"><span class="reference-number"><?php echo htmlspecialchars($reportDetail['reference_number'] ?? 'N/A'); ?></span></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Issue Type</div>
                                <div class="info-value"><?php echo htmlspecialchars($reportDetail['issue_type']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Description</div>
                                <div class="info-value"><?php echo nl2br(htmlspecialchars($reportDetail['description'])); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Equipment Condition</div>
                                <div class="info-value">
                                    <span class="condition-badge condition-<?php echo $reportDetail['statusCondition'] ?? 'unknown'; ?>">
                                        <?php echo $reportDetail['statusCondition'] ? ucfirst(str_replace('_', ' ', $reportDetail['statusCondition'])) : 'Unknown'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Reported Date</div>
                                <div class="info-value"><?php echo date('F d, Y - h:i A', strtotime($reportDetail['reported_at'])); ?></div>
                            </div>
                            <?php if ($reportDetail['resolved_at']): ?>
                                <div class="info-group">
                                    <div class="info-label">Resolved Date</div>
                                    <div class="info-value"><?php echo date('F d, Y - h:i A', strtotime($reportDetail['resolved_at'])); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($reportDetail['admin_response']): ?>
                            <div class="detail-section">
                                <h3 class="section-title">Admin Response</h3>
                                <div class="info-value"><?php echo nl2br(htmlspecialchars($reportDetail['admin_response'])); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($reportDetail['image_path']): ?>
                    <div class="image-container">
                        <h3 class="section-title">Attached Image</h3>
                        <img src="<?php echo htmlspecialchars($reportDetail['image_path']); ?>" alt="Issue Image" class="issue-image">
                    </div>
                <?php endif; ?>

                <form class="status-form" method="POST" action="">
                    <input type="hidden" name="report_id" value="<?php echo $reportDetail['id']; ?>">
                    <div class="form-group">
                        <label class="form-label">Update Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" <?php echo $reportDetail['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo $reportDetail['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $reportDetail['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="rejected" <?php echo $reportDetail['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Equipment Condition</label>
                        <select name="statusCondition" class="form-select">
                            <option value="working" <?php echo ($reportDetail['statusCondition'] ?? '') == 'working' ? 'selected' : ''; ?>>Working</option>
                            <option value="needs_repair" <?php echo ($reportDetail['statusCondition'] ?? '') == 'needs_repair' ? 'selected' : ''; ?>>Needs Repair</option>
                            <option value="maintenance" <?php echo ($reportDetail['statusCondition'] ?? '') == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="missing" <?php echo ($reportDetail['statusCondition'] ?? '') == 'missing' ? 'selected' : ''; ?>>Missing</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Admin Response</label>
                        <textarea name="admin_response" class="form-textarea" placeholder="Provide details about resolution, next steps, or rejection reason..."><?php echo htmlspecialchars($reportDetail['admin_response'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="update_status" class="submit-btn">Update Report</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="card-content">
                <div class="notification is-danger">
                    Report not found. <a href="dept_equipment_report.php">Return to report list</a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>