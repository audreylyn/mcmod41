<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

include 'includes/dashboard_data.php';
// No need to close the connection, it's managed by the db() function.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
        <!-- Icons and Manifest -->
    <link rel="icon" href="../public/assets/logo.webp" type="image/webp" />
    <link rel="apple-touch-icon" href="../public/assets/logo.webp">
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/dept_admin_dashboard.css">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
    <div id="app">
        <?php include 'layout/topnav.php'; ?>
        <?php include 'layout/sidebar.php'; ?>

        <section class="section main-section">
            <div class="dashboard-container">

                <!-- Statistics Cards -->
                <div class="stat-card">
                    <i class="mdi mdi-account-tie stat-icon"></i>
                    <div class="stat-value"><?php echo $teacher_count; ?></div>
                    <div class="stat-label">Teachers</div>
                </div>

                <div class="stat-card">
                    <i class="mdi mdi-account-group stat-icon"></i>
                    <div class="stat-value"><?php echo $student_count; ?></div>
                    <div class="stat-label">Students</div>
                </div>

                <div class="stat-card">
                    <i class="mdi mdi-clock-alert stat-icon"></i>
                    <div class="stat-value"><?php echo $pending_requests; ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>

                <div class="stat-card">
                    <i class="mdi mdi-alert-circle stat-icon"></i>
                    <div class="stat-value"><?php echo $unresolved_issues; ?></div>
                    <div class="stat-label">Unresolved Issues</div>
                </div>

                <!-- First Row Charts -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-chart-pie"></i></span>
                            Room Request Status
                        </h3>
                    </div>
                    <div class="card-content">
                        <canvas id="roomStatusChart" height="220"></canvas>
                    </div>
                </div>

                <!-- Second Row Charts -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-chart-bar"></i></span>
                            Equipment Issues by Status
                        </h3>
                    </div>
                    <div class="card-content">
                        <canvas id="issuesStatusChart" height="220"></canvas>
                    </div>
                </div>

                <!-- Recent Issues Section -->
                <div class="chart-card issues-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-clipboard-alert"></i></span>
                            Recent Equipment Issues
                        </h3>
                    </div>
                    <div class="card-content">
                        <?php if (count($recent_issues) > 0): ?>
                            <?php foreach ($recent_issues as $issue): ?>
                                <div class="list-item">
                                    <div class="item-main">
                                        <div class="item-title">
                                            <?php echo htmlspecialchars($issue['equipment_name']); ?> (<?php echo htmlspecialchars($issue['issue_type']); ?>)
                                        </div>
                                        <div class="item-subtitle">
                                            <strong>Issue ID:</strong> ISS-<?php echo $issue['id']; ?>
                                        </div>
                                        <div class="item-details">
                                            <span class="detail-item">
                                                <i class="mdi mdi-account"></i> Reporter: <?php echo htmlspecialchars($issue['first_name'] . ' ' . $issue['last_name']); ?> (<?php echo $issue['user_type']; ?>)
                                            </span>
                                            <span class="detail-item">
                                                <i class="mdi mdi-calendar"></i> Reported: <?php 
                                                    $utcTimestamp = strtotime($issue['reported_at']);
                                                    $phTimestamp = $utcTimestamp + (8 * 3600);
                                                    echo date('M j, Y g:i A', $phTimestamp);
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="item-status">
                                        <span class="simple-status status-<?php echo strtolower(str_replace('_', '-', $issue['status'])); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $issue['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="section-footer">
                                <a href="dept_equipment_report.php" class="simple-link">
                                    <i class="mdi mdi-arrow-right"></i> View All Equipment Issues
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No recent equipment issues reported.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-container">
            <!-- Recent Room Usage -->
            <div class="chart-card issues-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="icon"><i class="mdi mdi-door"></i></span>
                        Recent Room Usage
                    </h3>
                </div>
                <div class="card-content">
                    <?php if (count($recent_room_usage) > 0): ?>
                        <?php foreach ($recent_room_usage as $usage): ?>
                            <div class="list-item">
                                <div class="item-main">
                                    <div class="item-title">
                                        <?php echo htmlspecialchars($usage['room_name']); ?> 
                                        <?php if ($usage['room_type']): ?>
                                            (<?php echo htmlspecialchars($usage['room_type']); ?>)
                                        <?php endif; ?>
                                        - <?php echo htmlspecialchars($usage['building_name']); ?>
                                    </div>
                                    <div class="item-subtitle">
                                        <strong><?php echo htmlspecialchars($usage['ActivityName']); ?></strong>
                                    </div>
                                    <div class="item-details">
                                        <span class="detail-item">
                                            <i class="mdi mdi-account"></i> User: <?php echo htmlspecialchars($usage['user_name']); ?> (<?php echo $usage['user_role']; ?>)
                                        </span>
                                        <span class="detail-item">
                                            <i class="mdi mdi-calendar"></i> Date: <?php 
                                                $reservation_date = new DateTime($usage['ReservationDate']);
                                                echo $reservation_date->format('M j, Y');
                                                
                                                if ($usage['StartTime'] && $usage['EndTime']) {
                                                    $start_time = new DateTime($usage['StartTime']);
                                                    $end_time = new DateTime($usage['EndTime']);
                                                    echo ' (' . $start_time->format('g:i A') . ' - ' . $end_time->format('g:i A') . ')';
                                                }
                                            ?>
                                        </span>
                                        <span class="detail-item">
                                            <i class="mdi mdi-clock"></i> Requested: <?php 
                                                $utcTimestamp = strtotime($usage['RequestDate']);
                                                $phTimestamp = $utcTimestamp + (8 * 3600);
                                                echo date('M j, Y g:i A', $phTimestamp);
                                            ?>
                                        </span>
                                        <?php if ($usage['admin_first_name']): ?>
                                            <span class="detail-item">
                                                <i class="mdi mdi-account-check"></i> Approved by: <?php echo htmlspecialchars($usage['admin_first_name'] . ' ' . $usage['admin_last_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="item-status">
                                    <span class="simple-status status-<?php echo strtolower(str_replace(' ', '-', $usage['usage_status'])); ?>">
                                        <?php echo $usage['usage_status']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="section-footer">
                            <a href="dept_room_activity_logs.php" class="simple-link">
                                <i class="mdi mdi-arrow-right"></i> View All Activity Logs
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No recent room usage data found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </div>

            
            <!-- Two column layout for Rooms with Most Issues and Most Requested Rooms -->
            <div class="dashboard-container">
                <!-- Rooms with Most Issues -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-alert-circle"></i></span>
                            Rooms with Most Issues
                        </h3>
                    </div>
                    <div class="card-content table-responsive">
                        <table class="dashboard-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Building</th>
                                    <th class="has-text-right">Issue Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($rooms_with_most_issues) > 0): ?>
                                    <?php foreach ($rooms_with_most_issues as $room): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($room['room_name']); ?></td>
                                            <td><?php echo htmlspecialchars($room['building_name']); ?></td>
                                            <td class="has-text-right">
                                                <span class="badge badge-danger"><?php echo $room['issue_count']; ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="has-text-centered">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Most Requested Rooms -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-bookmark-check"></i></span>
                            Most Requested Rooms
                        </h3>
                    </div>
                    <div class="card-content table-responsive">
                        <table class="dashboard-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Building</th>
                                    <th class="has-text-right">Requests</th>
                                    <th class="has-text-right">Approval Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($most_requested_rooms) > 0): ?>
                                    <?php foreach ($most_requested_rooms as $room): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($room['room_name']); ?></td>
                                            <td><?php echo htmlspecialchars($room['building_name']); ?></td>
                                            <td class="has-text-right"><?php echo $room['request_count']; ?></td>
                                            <td class="has-text-right">
                                                <span class="badge badge-<?php echo ($room['approval_rate'] >= 70) ? 'success' : (($room['approval_rate'] >= 40) ? 'warning' : 'danger'); ?>">
                                                    <?php echo $room['approval_rate']; ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="has-text-centered">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- Reports Section -->
                <div class="chart-card chart-card-full" id="reports-section">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-file-chart"></i></span>
                            Generate Reports
                        </h3>
                    </div>
                    <div class="card-content">
                        <div class="reports-container">
                            <!-- Date Range Picker -->
                            <div class="date-range-section">
                                <h4>Step 1: Select Date Range</h4>
                                <p class="instruction-text">Please select a date range first to enable report options.</p>
                                <div class="date-inputs">
                                    <div class="date-input-group">
                                        <label for="startDate">Start Date:</label>
                                        <input type="date" id="startDate" class="date-input">
                                    </div>
                                    <div class="date-input-group">
                                        <label for="endDate">End Date:</label>
                                        <input type="date" id="endDate" class="date-input">
                                    </div>
                                </div>
                            </div>

                            <!-- Report Options -->
                            <div class="report-options">
                                <h4>Step 2: Choose Report Type</h4>
                                <div class="report-status-message" id="reportStatusMessage">
                                    <i class="mdi mdi-information-outline"></i>
                                    <span>Select a date range above to enable report options</span>
                                </div>
                                <div class="report-buttons">
                                    <button class="report-btn" data-report="booking-requests" disabled>
                                        <i class="mdi mdi-clipboard-check"></i>
                                        <span>Room Reservation Requests</span>
                                        <small>Detailed list of approved and rejected requests</small>
                                    </button>
                                    <button class="report-btn" data-report="equipment-status" disabled>
                                        <i class="mdi mdi-tools"></i>
                                        <span>Equipment Issues Report</span>
                                        <small>Detailed equipment problems and maintenance issues</small>
                                    </button>
                                </div>
                            </div>

                            <!-- Export Options -->
                            <div class="export-options">
                                <h4>Step 3: Generate Report</h4>
                                <div class="export-buttons">
                                    <button class="export-btn" data-format="csv" disabled>
                                        <i class="mdi mdi-file-excel"></i>
                                        Export as CSV
                                    </button>
                                    <button class="export-btn" data-format="preview" disabled>
                                        <i class="mdi mdi-eye"></i>
                                        Preview Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/topnav-dropdown.js"></script>

    <!-- Pass PHP data to JavaScript -->
    <script>
        window.roomStats = {
            pending: <?php echo isset($room_stats['pending']) ? $room_stats['pending'] : 0; ?>,
            approved: <?php echo isset($room_stats['approved']) ? $room_stats['approved'] : 0; ?>,
            completed: <?php echo isset($room_stats['completed']) ? $room_stats['completed'] : 0; ?>,
            rejected: <?php echo isset($room_stats['rejected']) ? $room_stats['rejected'] : 0; ?>
        };
        window.issueStats = {
            pending: <?php echo isset($issue_stats['pending']) ? $issue_stats['pending'] : 0; ?>,
            in_progress: <?php echo isset($issue_stats['in_progress']) ? $issue_stats['in_progress'] : 0; ?>,
            resolved: <?php echo isset($issue_stats['resolved']) ? $issue_stats['resolved'] : 0; ?>,
            rejected: <?php echo isset($issue_stats['rejected']) ? $issue_stats['rejected'] : 0; ?>
        };
        // Monthly chart data removed - chart no longer used
    </script>

    <script type="text/javascript" src="../public/js/admin_scripts/dept_admin_dashboard.js"></script>
</body>

</html>