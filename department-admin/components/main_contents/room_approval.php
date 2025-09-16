<div class="card-content">
    <!-- Display success/error messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <?php
        // Determine if this is a rejection success message
        $alertClass = (strpos($_SESSION['success_message'], 'rejected') !== false) ? 'alert-reject' : 'alert-success';
        ?>
        <div class="alert <?php echo $alertClass; ?> fade-alert">
            <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger fade-alert">
            <?php
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Search and Filters -->
    <div class="search-container">
        <input type="text" id="searchInput" class="search-input" placeholder="Search by activity, room, or requester...">
        <i class="mdi mdi-magnify search-icon"></i>
    </div>

    <div class="filters">
        <div class="filter-item">
            <label class="filter-label">Status</label>
            <select id="statusFilter" class="filter-control">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="filter-item">
            <label class="filter-label">Date</label>
            <select id="dateFilter" class="filter-control">
                <option value="">All Dates</option>
                <option value="today">Today</option>
                <option value="tomorrow">Tomorrow</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>
        <div class="filter-item">
            <label class="filter-label">Priority</label>
            <select id="priorityFilter" class="filter-control">
                <option value="">All Priorities</option>
                <option value="teacher">Teacher Requests</option>
                <option value="urgent">Urgent (Today/Tomorrow)</option>
                <option value="week">This Week</option>
            </select>
        </div>
        <button id="clearFilters">Clear Filters</button>
    </div>

    <div class="results-count" id="requestCount"></div>

    <!-- Room Request Cards -->
    <div id="requestsContainer">
        <?php
        // Get the department of the logged-in admin
        $admin_department = $_SESSION['department'] ?? '';

        if (empty($admin_department)) {
            // Optional: Handle cases where department is not set for the admin
            echo "<div class='no-results'>Department not configured for this admin.</div>";
            $requests = [];
            $requestCount = 0;
        } else {
            // Query to get room requests with room and user info, filtered by department
            $sql = "SELECT rr.*, r.room_name, r.capacity, r.room_type, b.building_name,
                    CASE 
                        WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                        WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
                    END as RequesterName,
                    CASE 
                        WHEN rr.StudentID IS NOT NULL THEN 'Student'
                        WHEN rr.TeacherID IS NOT NULL THEN 'Teacher'
                    END as RequesterType,
                    s.Department as StudentDepartment,
                    t.Department as TeacherDepartment,
                    DATEDIFF(rr.ReservationDate, CURDATE()) as DaysUntilReservation,
                    rr.RequestDate as RequestDate
                    FROM room_requests rr
                    LEFT JOIN rooms r ON rr.RoomID = r.id
                    LEFT JOIN buildings b ON r.building_id = b.id
                    LEFT JOIN student s ON rr.StudentID = s.StudentID
                    LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
                    HAVING StudentDepartment = ? OR TeacherDepartment = ?
                    ORDER BY 
                        CASE WHEN rr.Status = 'pending' THEN 0
                                WHEN rr.Status = 'approved' THEN 1
                                WHEN rr.Status = 'rejected' THEN 2
                        END ASC,
                        CASE WHEN rr.TeacherID IS NOT NULL THEN 0 ELSE 1 END, /* Teachers first */
                        DATEDIFF(DATE(rr.StartTime), CURDATE()) ASC, /* Prioritize by how soon the date is */
                        rr.RequestDate ASC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $admin_department, $admin_department);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $requests = [];
            while ($row = $result->fetch_assoc()) {
                // Calculate priority score (lower = higher priority)
                $priorityScore = 0;

                // Teacher requests get higher priority (subtract 1000 to ensure they're always first)
                if ($row['RequesterType'] == 'Teacher') {
                    $priorityScore -= 1000;
                }

                // Urgent requests (within next 3 days) get higher priority
                $daysUntil = $row['DaysUntilReservation'];
                if ($daysUntil <= 0) {
                    $priorityScore -= 500;
                    $priorityLabel = "Today";
                    $priorityClass = "priority-urgent today-badge";
                } else if ($daysUntil <= 1) {
                    $priorityScore -= 400;
                    $priorityLabel = "Tomorrow";
                    $priorityClass = "priority-high";
                } else if ($daysUntil <= 3) {
                    $priorityScore -= 300;
                    $priorityLabel = "Soon";
                    $priorityClass = "priority-medium";
                } else if ($daysUntil <= 7) {
                    $priorityScore -= 200;
                    $priorityLabel = "This Week";
                    $priorityClass = "priority-normal";
                } else {
                    $priorityLabel = "Scheduled";
                    $priorityClass = "priority-low";
                }

                // Store priority information in the row
                $row['PriorityScore'] = $priorityScore;
                $row['PriorityLabel'] = $priorityLabel;
                $row['PriorityClass'] = $priorityClass;
                $row['DaysUntil'] = $daysUntil;

                $requests[] = $row;
            }

            // Sort requests by priority score
            usort($requests, function ($a, $b) {
                $statusA = $a['Status'] == 'pending' ? 0 : ($a['Status'] == 'approved' ? 1 : 2);
                $statusB = $b['Status'] == 'pending' ? 0 : ($b['Status'] == 'approved' ? 1 : 2);
                if ($statusA != $statusB) {
                    return $statusA - $statusB;
                }
                return $a['PriorityScore'] - $b['PriorityScore'];
            });
        }

        // Display the count
        $requestCount = count($requests);

        if ($requestCount > 0):
            foreach ($requests as $row):
                $requestId = $row['RequestID'];
                $activityName = htmlspecialchars($row['ActivityName']);
                $roomName = htmlspecialchars($row['room_name']);
                $buildingName = htmlspecialchars($row['building_name']);
                // Use ReservationDate (DATE) for the reservation day
                $reservationDate = date('M j, Y', strtotime($row['ReservationDate']));
                $startTime = date('g:i A', strtotime($row['StartTime']));
                $endTime = date('g:i A', strtotime($row['EndTime']));
                $timeRange = "$startTime - $endTime";
                $participants = $row['NumberOfParticipants'];
                $requesterName = htmlspecialchars($row['RequesterName']);
                $requesterType = $row['RequesterType'];
                $status = $row['Status'];
                $priorityLabel = $row['PriorityLabel'];
                $priorityClass = $row['PriorityClass'];
                $daysUntil = $row['DaysUntil'];

                // Set info icon class based on status
                $iconClass = 'info-icon-' . $status;
        ?>
                <div class="request-card"
                    data-status="<?php echo $status; ?>"
                    data-reservation-date="<?php echo date('Y-m-d', strtotime($row['ReservationDate'])); ?>"
                    data-building="<?php echo htmlspecialchars($buildingName); ?>"
                    data-requester-type="<?php echo $requesterType; ?>"
                    data-days-until="<?php echo $daysUntil; ?>"
                    data-priority-score="<?php echo $row['PriorityScore']; ?>">

                    <div class="request-title">
                        <h3><?php echo $activityName; ?></h3>
                        <div class="card-indicators">
                            <i class="mdi mdi-information-outline info-icon <?php echo $iconClass; ?>" onclick="showRequestDetails(<?php echo $requestId; ?>)"></i>
                        </div>
                    </div>

                    <div class="request-details">
                        <div class="request-detail-item"><i class="mdi mdi-domain"></i><?php echo $roomName . ', ' . $buildingName; ?></div>
                        <div class="request-detail-item"><i class="mdi mdi-calendar"></i><?php echo $reservationDate; ?></div>
                        <div class="request-detail-item"><i class="mdi mdi-clock-outline"></i><?php echo $timeRange; ?></div>
                        <div class="request-detail-item">
                            <i class="mdi mdi-account"></i>
                            <?php echo $requesterName; ?>
                            <span class="requester-badge <?php echo strtolower($requesterType); ?>-badge"><?php echo $requesterType; ?></span>
                        </div>
                        <div class="request-detail-item"><i class="mdi mdi-account-group"></i><?php echo $participants; ?> participants</div>

                        <?php if ($status == 'pending'): ?>
                            <div class="request-detail-item priority-item">
                                <i class="mdi mdi-clock-alert-outline"></i>
                                <span class="priority-badge <?php echo $priorityClass; ?>"><?php echo $priorityLabel; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($status == 'approved' && (!empty($row['ApproverFirstName']) || !empty($row['ApproverLastName']))): ?>
                            <div class="request-detail-item">
                                <i class="mdi mdi-check-circle" style="color: var(--success-color);"></i>
                                <span>Approved by: <?php echo htmlspecialchars($row['ApproverFirstName'] . ' ' . $row['ApproverLastName']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($status == 'rejected' && (!empty($row['RejecterFirstName']) || !empty($row['RejecterLastName']))): ?>
                            <div class="request-detail-item">
                                <i class="mdi mdi-cancel" style="color: var(--danger-color);"></i>
                                <span>Rejected by: <?php echo htmlspecialchars($row['RejecterFirstName'] . ' ' . $row['RejecterLastName']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($status == 'pending'): ?>
                        <div class="action-buttons">
                            <form method="POST" style="flex: 1;">
                                <input type="hidden" name="request_id" value="<?php echo $requestId; ?>">
                                <button type="submit" name="approve_request" class="btn-approve">Approve</button>
                            </form>
                            <button type="button" class="btn-reject" onclick="showRejectModal(<?php echo $requestId; ?>)">Reject</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">No room requests found.</div>
        <?php endif; ?>
    </div>
</div>