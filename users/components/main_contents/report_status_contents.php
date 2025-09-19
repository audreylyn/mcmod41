<!-- Page content -->
<div class="right_col" role="main">
    <div class="main-content">
        <h1 class="page-title-report">My Equipment Reports</h1>
        <p class="page-subtitle">Track the status of your equipment issue reports</p>

        <div class="wrap-report">
            <div class="search-wrapper">
                <div class="search-wrapper-inner">
                    <div class="search-box">
                        <i class="fa fa-search search-icon"></i>
                        <input type="text" id="searchInput" placeholder="Search equipment, location...">
                    </div>
                    <div class="status-filter">
                        <select id="statusFilter" class="filter-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="status-filter">
                        <select id="conditionFilter" class="filter-select">
                            <option value="">All Conditions</option>
                            <option value="working">Working</option>
                            <option value="needs_repair">Needs Repair</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="missing">Missing</option>
                        </select>
                    </div>
                </div>
                <a href="qr-scan.php" class="btn-primary">Report an Issue</a>
            </div>
        </div>

        <?php
        // If redirected here due to banned status when attempting to report equipment,
        // show a clear error message. The redirect includes ?banned=1.
        if (isset($_GET['banned']) && $_GET['banned'] == '1') {
            echo '<div class="alert alert-danger" role="alert">Your account has been banned and you cannot report equipment issues. Please contact your department administrator.</div>';
        }
        ?>


        <?php if (empty($reports)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fa fa-clipboard-list"></i>
                </div>
                <h4 class="empty-title">No Reports Found</h4>
                <p class="empty-text">You haven't submitted any equipment issue reports yet.</p>
            </div>
        <?php else: ?>
            <div class="reports-list">
                <?php foreach ($reports as $report): ?>
                    <div class="report-card" data-status="<?php echo $report['status']; ?>" data-condition="<?php echo $report['statusCondition']; ?>">
                        <div class="report-card-header">
                            <div class="report-id">
                                <?php 
                                    // Display reference number if it exists, otherwise generate one from ID
                                    $refNumber = !empty($report['reference_number']) ? 
                                        $report['reference_number'] : 
                                        'EQ' . str_pad($report['id'], 6, '0', STR_PAD_LEFT);
                                    echo $refNumber;
                                ?>
                            </div>
                            <div class="status-badges">
                                <?php echo getStatusBadge($report['status']); ?>
                            </div>
                        </div>
                        <div class="report-card-body">
                            <div class="report-info-grid">
                                <div class="info-item">
                                    <div class="info-label">Equipment:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($report['equipment_name']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Location:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($report['room_name'] . ', ' . $report['building_name']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Issue Type:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($report['issue_type']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Condition:</div>
                                    <div class="info-value"><?php echo ucfirst(str_replace('_', ' ', $report['statusCondition'])); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Reported:</div>
                                    <div class="info-value"><?php echo date('M d, Y h:i A', strtotime($report['reported_at'])); ?></div>
                                </div>
                            </div>
                            <a href="javascript:void(0)" class="view-details-btn" onclick="toggleDetails(this, <?php echo $report['id']; ?>)">
                                View Details <i class="fa fa-chevron-right"></i>
                            </a>
                        </div>
                        <div id="details-<?php echo $report['id']; ?>" class="report-details">
                            <div class="details-section">
                                <h3 class="details-title">Issue Description</h3>
                                <p class="details-content"><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>
                            </div>

                            <?php if (!empty($report['image_path'])): ?>
                                <div class="details-section">
                                    <h3 class="details-title">Attached Image</h3>
                                    <div class="image-container">
                                        <img src="<?php echo htmlspecialchars($report['image_path']); ?>" alt="Issue Image" class="report-image">
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($report['admin_response'])): ?>
                                <div class="details-section">
                                    <h3 class="details-title">Administrator Response</h3>
                                    <div class="admin-response">
                                        <p><?php echo nl2br(htmlspecialchars($report['admin_response'])); ?></p>
                                        <?php if (!empty($report['resolved_at'])): ?>
                                            <div class="response-date">Responded on <?php echo date('M d, Y h:i A', strtotime($report['resolved_at'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($report['status'] === 'rejected' && !empty($report['rejection_reason'])): ?>
                                <div class="details-section">
                                    <h3 class="details-title">Rejection Reason</h3>
                                            <div class="rejection-reason">
                                        <p><?php echo nl2br(htmlspecialchars($report['rejection_reason'])); ?></p>
                                        <?php if (!empty($report['resolved_at'])): ?>
                                            <div class="response-date">Rejected on <?php echo date('M d, Y h:i A', strtotime($report['resolved_at'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>