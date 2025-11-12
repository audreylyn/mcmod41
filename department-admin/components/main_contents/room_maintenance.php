<!-- Page Header -->
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 class="page-title">
            <i class="mdi mdi-wrench"></i>
            Room Maintenance Management
        </h1>
        <p class="page-subtitle">
            Manage room status for <?php echo htmlspecialchars($department); ?> department and gymnasium
        </p>
    </div>
    <button type="button" class="btn btn-info btn-sm" onclick="checkExpiredMaintenance()" id="checkExpiredMaintenanceBtn" 
            title="Manually check for expired maintenance periods and update room status. Note: System automatically checks every 10 page loads.">
        <i class="mdi mdi-clock-check"></i> Manual Expiry Check
    </button>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $room_stats['available'] ?? 0; ?></div>
        <div class="stat-label">Available Rooms</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $room_stats['occupied'] ?? 0; ?></div>
        <div class="stat-label">Occupied Rooms</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $room_stats['maintenance'] ?? 0; ?></div>
        <div class="stat-label">Under Maintenance</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo count($rooms); ?></div>
        <div class="stat-label">Total Rooms</div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="content-grid">
    <!-- Rooms Table -->
    <div class="main-content">
        <div class="section-header">
            <h2 class="section-title">
                <i class="mdi mdi-door icon"></i>
                Room Status Management
            </h2>
        </div>
        
        <div class="table-container">
            <table class="maintenance-table">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Type & Capacity</th>
                        <th>Current Status</th>
                        <th>Maintenance Info</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr data-room-id="<?php echo $room['id']; ?>">
                            <td>
                                <div class="room-info">
                                    <span class="room-name">
                                        <?php echo htmlspecialchars($room['room_name']); ?>
                                        <?php if ($room['room_type'] === 'Gymnasium'): ?>
                                            <span class="gym-indicator">ALL DEPTS</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="room-details">
                                        <?php echo htmlspecialchars($room['building_name']); ?>
                                        <?php if ($room['department'] !== $department && $room['room_type'] !== 'Gymnasium'): ?>
                                            (<?php echo htmlspecialchars($room['department']); ?>)
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($room['room_type']); ?></strong><br>
                                    <small>Capacity: <?php echo $room['capacity']; ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $room['RoomStatus']; ?><?php echo $room['expiry_warning'] ? ' ' . $room['expiry_warning'] : ''; ?>"
                                    title="<?php
                                        if ($room['RoomStatus'] === 'available') {
                                            echo 'Room is available for reservations';
                                        } elseif ($room['RoomStatus'] === 'occupied') {
                                            echo 'Room is currently occupied by a reservation';
                                        } elseif ($room['RoomStatus'] === 'maintenance') {
                                            if ($room['expiry_warning'] === 'expiring_soon') {
                                                echo 'Room under maintenance - expires within 24 hours (URGENT)';
                                            } elseif ($room['expiry_warning'] === 'expiring_3days') {
                                                echo 'Room under maintenance - expires within 3 days';
                                            } else {
                                                echo 'Room is under maintenance';
                                            }
                                        }
                                    ?>">
                                    <?php echo ucfirst($room['RoomStatus']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($room['RoomStatus'] === 'maintenance' && $room['maintenance_reason']): ?>
                                    <div class="maintenance-info">
                                        <strong>Reason:</strong> <?php echo htmlspecialchars($room['maintenance_reason']); ?><br>
                                        <strong>Period:</strong> <?php echo date('M j, Y', strtotime($room['maintenance_start'])); ?> - 
                                        <?php echo isset($room['maintenance_end']) ? date('M j, Y', strtotime($room['maintenance_end'])) : 'Ongoing'; ?><br>
                                        <strong>By:</strong> <?php echo htmlspecialchars($room['maintenance_admin']); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($room['RoomStatus'] === 'occupied'): ?>
                                    <span class="text-muted">Auto-managed</span>
                                <?php else: ?>
                                    <select class="action-select" onchange="updateRoomStatus(<?php echo $room['id']; ?>, this.value, '<?php echo htmlspecialchars($room['room_name']); ?>')">
                                        <option value="">Change Status</option>
                                        <?php if ($room['RoomStatus'] !== 'available'): ?>
                                            <option value="available">Set Available</option>
                                        <?php endif; ?>
                                        <?php if ($room['RoomStatus'] !== 'maintenance'): ?>
                                            <option value="maintenance">Set Maintenance</option>
                                        <?php endif; ?>
                                    </select>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sidebar with Maintenance History -->
    <div class="sidebar-content">
        <h3 class="section-title">
            <i class="mdi mdi-history icon"></i>
            Recent Maintenance History
        </h3>
        
        <div class="history-list">
            <?php if (count($maintenance_history) > 0): ?>
                <?php foreach ($maintenance_history as $history): ?>
                    <div class="history-item">
                        <div class="history-room">
                            <?php echo htmlspecialchars($history['room_name']); ?>
                            <small>(<?php echo htmlspecialchars($history['building_name']); ?>)</small>
                        </div>
                        <div class="history-reason">
                            <?php echo htmlspecialchars($history['reason']); ?>
                        </div>
                        <div class="history-meta">
                            <span>By: <?php echo htmlspecialchars($history['admin_name']); ?></span>
                            <?php if ($history['end_date']): ?>
                                <?php
                                $start = new DateTime($history['start_date']);
                                $end = new DateTime($history['end_date']);
                                $duration = $start->diff($end);
                                $duration_text = '';
                                if ($duration->days > 0) {
                                    $duration_text = $duration->days . 'd ';
                                }
                                $duration_text .= $duration->h . 'h ' . $duration->i . 'm';
                                ?>
                                <span class="duration-badge"><?php echo $duration_text; ?></span>
                            <?php else: ?>
                                <span class="duration-badge">Ongoing</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No maintenance history found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>