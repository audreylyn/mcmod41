<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

include 'includes/dashboard_data.php'
// No need to close the connection, it's managed by the db() function.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Admin Dashboard</title>
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

                <!-- Full Width Chart -->
                <div class="chart-card chart-card-full">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-chart-line"></i></span>
                            Monthly Room Request Trends
                        </h3>
                    </div>
                    <div class="card-content">
                        <canvas id="monthlyTrendsChart" height="100"></canvas>
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
                                <div class="issue-item">
                                    <div class="issue-title"><?php echo htmlspecialchars($issue['equipment_name']); ?> - <?php echo htmlspecialchars($issue['issue_type']); ?></div>
                                    <div class="issue-meta">
                                        <span>Reported: <?php echo date('M j, Y g:i A', strtotime($issue['reported_at'])); ?></span>
                                        <span class="badge badge-<?php echo strtolower($issue['status']); ?>"><?php echo $issue['status']; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <a href="dept_equipment_report.php" class="action-link">View All Issues</a>
                        <?php else: ?>
                            <p class="text-center py-4">No recent equipment issues reported.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
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
                            <div class="issue-item">
                                <div class="issue-title">
                                    <?php echo htmlspecialchars($usage['room_name']); ?>, 
                                    <?php echo htmlspecialchars($usage['building_name']); ?> - 
                                    <?php echo htmlspecialchars($usage['ActivityName']); ?>
                                </div>
                                <div class="issue-meta">
                                    <span>User: <?php echo htmlspecialchars($usage['user_name']); ?> (<?php echo $usage['user_role']; ?>)</span>
                                    <span>Time: <?php echo date('M j, Y g:i A', strtotime($usage['StartTime'])); ?></span>
                                    <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $usage['usage_status'])); ?>"><?php echo $usage['usage_status']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <a href="dept_room_usage_logs.php" class="action-link">View All Usage</a>
                    <?php else: ?>
                        <p class="text-center py-4">No recent room usage data found.</p>
                    <?php endif; ?>
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
            rejected: <?php echo isset($room_stats['rejected']) ? $room_stats['rejected'] : 0; ?>
        };
        window.issueStats = {
            pending: <?php echo isset($issue_stats['pending']) ? $issue_stats['pending'] : 0; ?>,
            in_progress: <?php echo isset($issue_stats['in_progress']) ? $issue_stats['in_progress'] : 0; ?>,
            resolved: <?php echo isset($issue_stats['resolved']) ? $issue_stats['resolved'] : 0; ?>,
            rejected: <?php echo isset($issue_stats['rejected']) ? $issue_stats['rejected'] : 0; ?>
        };
        window.monthlyLabels = <?php echo json_encode(array_keys($monthly_stats)); ?>;
        window.monthlyData = <?php echo json_encode(array_values($monthly_stats)); ?>;
    </script>

    <script type="text/javascript" src="../public/js/admin_scripts/dept_admin_dashboard.js"></script>
</body>

</html>