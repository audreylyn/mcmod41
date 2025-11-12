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

    <!-- Search and Bulk Actions -->
    <div class="search-and-actions-container">
        <div class="search-container">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by activity, room, or requester...">
            <i class="mdi mdi-magnify search-icon"></i>
        </div>
        
        <div class="bulk-actions-container">
            <button type="button" class="bulk-delete-btn" id="bulkDeleteBtn">
                <i class="mdi mdi-delete-sweep"></i>
                Bulk Delete
            </button>
        </div>
    </div>

    <div class="filters">
        <div class="filter-item">
            <label class="filter-label">Status</label>
            <select id="statusFilter" class="filter-control">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="completed">Completed</option>
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
                <option value="expired">Expired Requests</option>
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
        // Set timezone to Philippines
        date_default_timezone_set('Asia/Manila');
        
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
                                WHEN rr.Status = 'auto_expired' THEN 3
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
                if ($daysUntil < 0) {
                    $priorityScore -= 600; // Expired gets highest priority for cleanup
                    $priorityLabel = "Expired";
                    $priorityClass = "priority-expired expired-badge";
                } else if ($daysUntil == 0) {
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
                $statusA = $a['Status'] == 'pending' ? 0 : ($a['Status'] == 'approved' ? 1 : ($a['Status'] == 'rejected' ? 2 : 3));
                $statusB = $b['Status'] == 'pending' ? 0 : ($b['Status'] == 'approved' ? 1 : ($b['Status'] == 'rejected' ? 2 : 3));
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

                // Check if request is expired (date in past OR time has passed today)
                $reservationDateTime = new DateTime($row['ReservationDate'] . ' ' . $row['EndTime']);
                $now = new DateTime();
                $isExpired = ($reservationDateTime <= $now);
                
                // Update priority for expired requests
                if ($isExpired && $priorityLabel !== 'Expired') {
                    $priorityLabel = 'Expired';
                    $priorityClass = 'priority-expired expired-badge';
                }

                // Set info icon class based on status
                $iconClass = 'info-icon-' . $status;
        ?>
                <div class="request-card"
                    data-status="<?php echo $status; ?>"
                    data-reservation-date="<?php echo date('Y-m-d', strtotime($row['ReservationDate'])); ?>"
                    data-building="<?php echo htmlspecialchars($buildingName); ?>"
                    data-requester-type="<?php echo $requesterType; ?>"
                    data-days-until="<?php echo $daysUntil; ?>"
                    data-is-expired="<?php echo $isExpired ? 'true' : 'false'; ?>"
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
                        
                        <?php if ($status == 'auto_expired'): ?>
                            <div class="request-detail-item">
                                <i class="mdi mdi-clock-remove" style="color: #666666;"></i>
                                <span style="color: #666666;">Auto-expired due to late notice</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($status == 'pending'): ?>
                        <div class="action-buttons">
                            <form method="POST" style="flex: 1;">
                                <input type="hidden" name="request_id" value="<?php echo $requestId; ?>">
                                <button type="submit" name="approve_request" class="btn-approve" <?php echo ($isExpired) ? 'disabled' : ''; ?>>Approve</button>
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

<!-- Bulk Delete Modal -->
<div id="bulkDeleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Bulk Delete Room Reservations</h3>
            <span class="close" onclick="closeBulkDeleteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Select a date range to delete room reservations:</p>
            <div class="date-range-inputs">
                <div class="date-input-group">
                    <label for="bulkStartDate">Start Date:</label>
                    <input type="date" id="bulkStartDate" class="date-input">
                </div>
                <div class="date-input-group">
                    <label for="bulkEndDate">End Date:</label>
                    <input type="date" id="bulkEndDate" class="date-input">
                </div>
            </div>
            <div class="preview-section">
                <button type="button" id="previewDeleteBtn" class="btn-preview" disabled>
                    <i class="mdi mdi-eye"></i> Preview Delete
                </button>
                <div id="previewResult" class="preview-result">
                    <div class="preview-info info">
                        <i class="mdi mdi-information"></i>
                        Select both start and end dates to enable preview
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeBulkDeleteModal()">Cancel</button>
            <button type="button" id="confirmDeleteBtn" class="btn-delete" disabled>
                <i class="mdi mdi-delete"></i> Delete Records
            </button>
        </div>
    </div>
</div>


<script>
// Bulk Delete Functionality
document.addEventListener('DOMContentLoaded', function() {
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteModal = document.getElementById('bulkDeleteModal');
    const previewDeleteBtn = document.getElementById('previewDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const bulkStartDate = document.getElementById('bulkStartDate');
    const bulkEndDate = document.getElementById('bulkEndDate');
    const previewResult = document.getElementById('previewResult');

    // Open bulk delete modal
    bulkDeleteBtn.addEventListener('click', function() {
        bulkDeleteModal.style.display = 'block';
        // Clear previous values and reset state
        bulkStartDate.value = '';
        bulkEndDate.value = '';
        previewDeleteBtn.disabled = true;
        confirmDeleteBtn.disabled = true;
        previewResult.innerHTML = `
            <div class="preview-info info">
                <i class="mdi mdi-information"></i>
                Select both start and end dates to enable preview
            </div>
        `;
    });

    // Date validation function
    function validateDateRange() {
        const startDate = bulkStartDate.value;
        const endDate = bulkEndDate.value;
        
        if (startDate && endDate) {
            if (new Date(startDate) > new Date(endDate)) {
                previewResult.innerHTML = `
                    <div class="preview-info error">
                        <i class="mdi mdi-alert"></i>
                        Start date cannot be after end date
                    </div>
                `;
                previewDeleteBtn.disabled = true;
                confirmDeleteBtn.disabled = true;
                return false;
            } else {
                previewResult.innerHTML = `
                    <div class="preview-info info">
                        <i class="mdi mdi-information"></i>
                        Click "Preview Delete" to see how many records will be deleted
                    </div>
                `;
                previewDeleteBtn.disabled = false;
                confirmDeleteBtn.disabled = true;
                return true;
            }
        } else {
            previewResult.innerHTML = `
                <div class="preview-info info">
                    <i class="mdi mdi-information"></i>
                    Select both start and end dates to enable preview
                </div>
            `;
            previewDeleteBtn.disabled = true;
            confirmDeleteBtn.disabled = true;
            return false;
        }
    }

    // Add event listeners for date inputs
    bulkStartDate.addEventListener('change', validateDateRange);
    bulkEndDate.addEventListener('change', validateDateRange);

    // Preview delete
    previewDeleteBtn.addEventListener('click', function() {
        const startDate = bulkStartDate.value;
        const endDate = bulkEndDate.value;

        if (!startDate || !endDate) {
            showAlert('Please select both start and end dates', 'error');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            showAlert('Start date cannot be after end date', 'error');
            return;
        }

        previewDeleteBtn.disabled = true;
        previewDeleteBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Loading...';

        fetch('includes/cleanup_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=preview_bulk_delete&start_date=${startDate}&end_date=${endDate}`
        })
        .then(response => {
            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            // Get response text first to debug
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text); // Debug log
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('Invalid JSON response from server');
            }
        })
        .then(data => {
            if (data.success) {
                previewResult.innerHTML = `
                    <div class="preview-info ${data.count > 0 ? 'warning' : 'info'}">
                        <i class="mdi mdi-information"></i>
                        ${data.count} room reservation(s) will be deleted.
                        <br><small>Date range: ${startDate} to ${endDate}</small>
                    </div>
                    <div class="caution-message">
                        <i class="mdi mdi-alert"></i>
                        <strong>Caution:</strong> This action cannot be undone. All selected reservations will be permanently deleted.
                    </div>
                `;
                confirmDeleteBtn.disabled = data.count === 0;
            } else {
                previewResult.innerHTML = `
                    <div class="preview-info error">
                        <i class="mdi mdi-alert"></i>
                        Error: ${data.message}
                    </div>
                `;
                confirmDeleteBtn.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            previewResult.innerHTML = `
                <div class="preview-info error">
                    <i class="mdi mdi-alert"></i>
                    Error occurred while previewing delete
                </div>
            `;
            confirmDeleteBtn.disabled = true;
        })
        .finally(() => {
            previewDeleteBtn.disabled = false;
            previewDeleteBtn.innerHTML = '<i class="mdi mdi-eye"></i> Preview Delete';
        });
    });

    // Confirm delete
    confirmDeleteBtn.addEventListener('click', function() {
        const startDate = bulkStartDate.value;
        const endDate = bulkEndDate.value;

        confirmDeleteBtn.disabled = true;
        confirmDeleteBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Deleting...';

        fetch('includes/cleanup_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=bulk_delete&start_date=${startDate}&end_date=${endDate}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Bulk delete response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('Invalid JSON response from server');
            }
        })
        .then(data => {
            if (data.success) {
                showAlert(`Successfully deleted ${data.deleted_count} room reservation(s)`, 'success');
                closeBulkDeleteModal();
                // Refresh the page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert(`Error: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error occurred while deleting records', 'error');
        })
        .finally(() => {
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.innerHTML = '<i class="mdi mdi-delete"></i> Delete Records';
        });
    });

});

function closeBulkDeleteModal() {
    const modal = document.getElementById('bulkDeleteModal');
    modal.style.display = 'none';
    
    // Reset form state
    document.getElementById('bulkStartDate').value = '';
    document.getElementById('bulkEndDate').value = '';
    document.getElementById('previewDeleteBtn').disabled = true;
    document.getElementById('confirmDeleteBtn').disabled = true;
    document.getElementById('previewResult').innerHTML = `
        <div class="preview-info info">
            <i class="mdi mdi-information"></i>
            Select both start and end dates to enable preview
        </div>
    `;
}


function showAlert(message, type) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} fade-alert`;
    alert.innerHTML = message;
    
    // Insert at the top of card-content
    const cardContent = document.querySelector('.card-content');
    cardContent.insertBefore(alert, cardContent.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 5000);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const bulkModal = document.getElementById('bulkDeleteModal');
    
    if (event.target == bulkModal) {
        closeBulkDeleteModal();
    }
}
</script>