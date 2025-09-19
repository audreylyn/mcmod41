<!-- Page content -->
<div class="right_col" role="main">
    <div>
        <div class="row">
            <div class="col-md-12">
                <!-- Display banned account alert for students with banned status -->
                <?php if ($userRole === 'Student' && $isStudentBanned): ?>
                <div class="banned-account-alert">
                    <div class="alert-content">
                        <h4>Account Restricted</h4>
                        <p>Your account has been temporarily suspended. You can browse rooms but cannot make reservations.</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Updated Search and Filter Section based on the provided image -->
                <div class="search-filter-wrapper">
                    <div class="search-bar-container">
                        <div class="search-input-wrapper">
                            <i class="fa fa-search search-icon"></i>
                            <input type="text" id="searchRooms" class="form-control" placeholder="Search rooms...">
                            <i class="fa fa-times search-clear-icon" id="clearSearch" style="display: none;"></i>
                        </div>

                        <div class="view-toggle-container">
                            <button class="filter-button" id="filterToggleBtn" type="button" onclick="toggleFilterDropdown(event)">
                                <i class="fa fa-filter"></i> Filters <span class="filter-count-bubble" id="filterCountBubble">0</span> <i class="fa fa-chevron-down filter-chevron"></i>
                            </button>

                            <div class="view-toggle btn-group" role="group">
                                <button type="button" class="btn active" id="gridView">
                                    <i class="fa fa-th-large"></i>
                                </button>
                                <button type="button" class="btn" id="listView">
                                    <i class="fa fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Container for active filter tags -->
                    <div class="applied-filters" id="appliedFilters">
                        <!-- Filter tags will be added here dynamically -->
                    </div>

                    <div class="filter-dropdown" id="filterDropdown" style="display:none;">
                        <div class="filter-section">
                            <h3 class="filter-heading">Building</h3>
                            <div class="filter-options">
                                <?php
                                // Query to get all buildings (mark or limit based on user department)
                                // $conn is either already available above for restricted users, otherwise create it now
                                if (!isset($conn)) {
                                    require_once '../auth/dbh.inc.php';
                                    $conn = db();
                                }

                                $sql = "SELECT id, building_name, department FROM buildings ORDER BY building_name";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $isUserDept = false;
                                        if (isset($user_department) && $user_department !== '' && $row['department'] === $user_department) {
                                            $isUserDept = true;
                                        }

                                        // If restricted user, only show their department buildings (hide others)
                                        if ($isRestrictedUser && !$isUserDept) {
                                            continue;
                                        }

                                        echo '<div class="filter-checkbox-item' . ($isUserDept ? ' user-department' : '') . '>'; 
                                        echo '<label>';
                                        $checked = ($isUserDept) ? ' checked' : '';
                                        echo '<input type="checkbox" name="building" value="' . $row['id'] . '" class="building-checkbox"' . $checked . '>'; 
                                        echo '<span class="checkbox-label">' . htmlspecialchars($row['building_name']);
                                        if ($isUserDept) echo ' <i class="fa fa-home" title="Your department building"></i>';
                                        echo '</span>';
                                        echo '</label>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <div class="filter-section">
                            <h3 class="filter-heading">Room Types</h3>
                            <div class="filter-count"><span id="roomTypeCount">0 selected</span></div>
                            <div class="filter-options">
                                <?php
                                // Query to get distinct room types
                                $sql = "SELECT DISTINCT room_type FROM rooms";
                                $result = $conn->query($sql);

                                $types = [];
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $types[] = $row['room_type'];
                                    }
                                }

                                // Ensure 'Gymnasium' is always available
                                if (!in_array('Gymnasium', $types)) {
                                    array_unshift($types, 'Gymnasium');
                                }

                                foreach ($types as $type) {
                                    echo '<div class="filter-checkbox-item">';
                                    echo '<label>';
                                    echo '<input type="checkbox" name="roomType" value="' . htmlspecialchars($type) . '" class="roomtype-checkbox">';
                                    echo '<span class="checkbox-label">' . htmlspecialchars($type) . '</span>';
                                    echo '</label>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="filter-section">
                            <h3 class="filter-heading">Minimum Capacity</h3>
                            <div class="filter-count"><span id="capacityValue">Any</span></div>
                            <div class="filter-options capacity-filter">
                                <input type="range" id="capacitySlider" min="0" max="100" value="0" class="capacity-slider">
                            </div>
                        </div>

                        <div class="filter-section">
                            <?php
                            // Determine if any rooms have zero equipment units; if none, hide the Has Equipment filter
                            $rooms_without_equipment = 0;
                            $eq_sql = "SELECT COUNT(*) as cnt FROM rooms r LEFT JOIN (SELECT room_id, COUNT(*) as eqc FROM equipment_units GROUP BY room_id) eu ON r.id = eu.room_id WHERE COALESCE(eu.eqc,0) = 0";
                            $eq_res = $conn->query($eq_sql);
                            if ($eq_res) {
                                $erow = $eq_res->fetch_assoc();
                                $rooms_without_equipment = intval($erow['cnt']);
                            }

                            if ($rooms_without_equipment > 0) {
                                // Render Has Equipment toggle only when some rooms lack equipment
                                echo '<div class="filter-toggle-item">';
                                echo '<div class="toggle-icon"><i class="fa fa-desktop"></i></div>';
                                echo '<span class="toggle-label">Has Equipment</span>';
                                echo '<label class="switch">';
                                echo '<input type="checkbox" id="hasEquipment">';
                                echo '<span class="slider round"></span>';
                                echo '</label>';
                                echo '</div>';
                            }
                            ?>

                            <div class="filter-toggle-item">
                                <div class="toggle-icon"><i class="fa fa-check-circle"></i></div>
                                <span class="toggle-label">Only Available</span>
                                <label class="switch">
                                    <input type="checkbox" id="onlyAvailable" checked>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="filter-actions">
                            <button id="applyFilters" class="apply-button">
                                <i class="fa fa-check"></i> Apply Filters
                            </button>
                            <button id="resetFilters" class="reset-button">
                                <i class="fa fa-refresh"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div id="roomCount" class="room-count-display">6 rooms found</div>

                <!-- No results message container -->
                <div id="noResultsMessage" class="no-results-container" style="display: none;">
                    <div class="no-results-content">
                        <div class="no-results-icon">
                            <i class="fa fa-book"></i>
                        </div>
                        <h3>No rooms found</h3>
                        <p>We couldn't find any rooms matching your filter criteria. Try adjusting your filters or search terms.</p>
                    </div>
                </div>

                <!-- Room Display Section Stays the Same -->
                <div class="row" id="roomsGrid">
                    <?php
                    // Check for user role and banned status for JavaScript
                    $jsUserRole = json_encode($userRole);
                    $jsIsStudentBanned = json_encode($isStudentBanned);
                    echo "<script>
                        const userRole = {$jsUserRole};
                        const isStudentBanned = {$jsIsStudentBanned};
                    </script>";
                    
                    // Start building the query with basic structure
                    $base_sql = "SELECT r.id, r.room_name, r.room_type, r.capacity, r.RoomStatus, b.id as building_id, b.building_name, 
                (SELECT COUNT(*) FROM equipment_units eu WHERE eu.room_id = r.id) as equipment_count
                FROM rooms r 
                JOIN buildings b ON r.building_id = b.id";

                    // Initialize where clauses array and parameter array
                    $where_clauses = [];
                    $params = [];
                    $param_types = "";

                    // Get filter parameters
                    $building_ids = isset($_GET['building_ids']) ? $_GET['building_ids'] : [];
                    // If user is restricted (student/teacher) and no building filter is provided,
                    // default to buildings for the user's department
                    if ($isRestrictedUser) {
                        if (empty($building_ids) && !empty($user_department_buildings)) {
                            $building_ids = $user_department_buildings;
                        }
                    }
                    $room_types = isset($_GET['room_types']) ? $_GET['room_types'] : [];
                    $min_capacity = isset($_GET['min_capacity']) ? intval($_GET['min_capacity']) : 0;
                    $has_equipment = isset($_GET['has_equipment']) && $_GET['has_equipment'] === 'true';
                    $only_available = isset($_GET['only_available']) && $_GET['only_available'] === 'true';
                    $search_term = isset($_GET['search']) ? $_GET['search'] : '';

                    // Add where clauses based on filters
                    if (!empty($building_ids)) {
                        $placeholders = str_repeat('?,', count($building_ids) - 1) . '?';
                        if ($isRestrictedUser) {
                            // Restricted users: show department buildings OR any Gymnasium regardless of building
                            $where_clauses[] = "(b.id IN ($placeholders) OR r.room_type = 'Gymnasium')";
                        } else {
                            $where_clauses[] = "b.id IN ($placeholders)";
                        }
                        foreach ($building_ids as $id) {
                            $params[] = $id;
                            $param_types .= "i";
                        }
                    } elseif ($isRestrictedUser) {
                        // No explicit building_ids provided. Try to restrict by department if available,
                        // otherwise at minimum show Gymnasium rooms only to restricted users.
                        if (!empty($user_department)) {
                            $where_clauses[] = "(b.department = ? OR r.room_type = 'Gymnasium')";
                            $params[] = $user_department;
                            $param_types .= "s";
                        } else {
                            // Fallback: only show gymnasiums
                            $where_clauses[] = "r.room_type = 'Gymnasium'";
                        }
                    }

                    if (!empty($room_types)) {
                        $placeholders = str_repeat('?,', count($room_types) - 1) . '?';
                        $where_clauses[] = "r.room_type IN ($placeholders)";
                        foreach ($room_types as $type) {
                            $params[] = $type;
                            $param_types .= "s";
                        }
                    }

                    if ($min_capacity > 0) {
                        $where_clauses[] = "r.capacity >= ?";
                        $params[] = $min_capacity;
                        $param_types .= "i";
                    }

                    if ($has_equipment) {
                        $where_clauses[] = "(SELECT COUNT(*) FROM equipment_units eu WHERE eu.room_id = r.id) > 0";
                    }

                    if ($only_available) {
                        $where_clauses[] = "r.RoomStatus = 'available'";
                    }

                    if (!empty($search_term)) {
                        $where_clauses[] = "(r.room_name LIKE ? OR b.building_name LIKE ? OR r.room_type LIKE ?)";
                        $search_param = "%$search_term%";
                        $params[] = $search_param;
                        $params[] = $search_param;
                        $params[] = $search_param;
                        $param_types .= "sss";
                    }

                    // Combine all where clauses
                    if (!empty($where_clauses)) {
                        $base_sql .= " WHERE " . implode(" AND ", $where_clauses);
                    }

                    // Add ordering
                    $base_sql .= " ORDER BY r.room_name";

                    // Prepare and execute the query
                    $stmt = $conn->prepare($base_sql);

                    // Bind parameters if we have any
                    if (!empty($params)) {
                        $stmt->bind_param($param_types, ...$params);
                    }

                    $stmt->execute();
                    $result = $stmt->get_result();

                    // Display room count
                    $room_count = $result->num_rows;
                    echo "<script>document.getElementById('roomCount').innerText = '$room_count " . ($room_count === 1 ? "room" : "rooms") . " found';</script>";

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $roomId = $row['id'];
                            $roomName = $row['room_name'];
                            $roomType = $row['room_type'];
                            $capacity = $row['capacity'];
                            $buildingId = $row['building_id'];
                            $buildingName = $row['building_name'];
                            $status = strtolower($row['RoomStatus']);
                            $hasEquipment = $row['equipment_count'] > 0;

                            // Set status class and text
                            $statusClass = "";
                            $statusText = "";

                            switch ($status) {
                                case 'available':
                                    $statusClass = "label-success";
                                    $statusText = "Available";
                                    break;
                                case 'occupied':
                                    $statusClass = "label-warning";
                                    $statusText = "Occupied";
                                    break;
                                case 'maintenance':
                                    $statusClass = "label-lightblue";
                                    $statusText = "Maintenance";
                                    break;
                                default:
                                    $statusClass = "label-default";
                                    $statusText = "Unknown";
                            }
                    ?>
                            <div class="col-md-4 room-card <?php echo ($userRole === 'Student' && $isStudentBanned) ? 'room-card-banned' : ''; ?>"
                                data-room-id="<?php echo $roomId; ?>"
                                data-building-id="<?php echo $buildingId; ?>"
                                data-building-name="<?php echo htmlspecialchars($buildingName); ?>"
                                data-room-name="<?php echo htmlspecialchars($roomName); ?>"
                                data-room-type="<?php echo htmlspecialchars($roomType); ?>"
                                data-capacity="<?php echo $capacity; ?>"
                                data-status="<?php echo $status; ?>"
                                data-status-text="<?php echo $statusText; ?>"
                                data-status-class="<?php echo $statusClass; ?>"
                                data-has-equipment="<?php echo $hasEquipment ? 'true' : 'false'; ?>">
                                <div class="x_panel">
                                    <div class="x_title bg-header">
                                        <div class="room-header">
                                            <div class="room-chip">
                                                <h2 class="room-name"><?php echo $roomName; ?></h2>
                                                <span class="label <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            </div>
                                            <p class="building-name"><?php echo $buildingName; ?></p>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">
                                        <div class="room-info">
                                            <i class="fa fa-users"></i> Capacity: <?php echo $capacity; ?>
                                        </div>
                                        <div class="room-info">
                                            <i class="fa fa-th-large"></i> Type: <?php echo $roomType; ?>
                                        </div>
                                        <?php if ($hasEquipment) { ?>
                                            <div class="room-info">
                                                <i class="fa fa-desktop"></i> Has Equipment
                                            </div>
                                        <?php } ?>
                                        <div class="action-buttons">
                                            <button type="button" class="btn-view" onclick="showRoomDetailsModal(this.parentNode.parentNode.parentNode.parentNode)">
                                                <i class="fa fa-info-circle"></i> View Details
                                            </button>
                                            <?php if ($status == 'available') { ?>
                                                <?php if ($userRole === 'Student' && $isStudentBanned) { ?>
                                                <button type="button" class="btn-reserve" disabled title="Your account is restricted">
                                                    <i class="fa fa-ban"></i> Restricted
                                                </button>
                                                <?php } else { ?>
                                                <button type="button" class="btn-reserve" onclick="showReservationModal(<?php echo $roomId; ?>)">
                                                    <i class="fa fa-calendar-plus-o"></i> Reserve
                                                </button>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <button type="button" class="btn-unavailable" disabled>
                                                    <i class="fa fa-calendar-times-o"></i> Unavailable
                                                </button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        // Show the styled no results message
                        echo "<script>document.getElementById('noResultsMessage').style.display = 'flex';</script>";
                    }

                    // Close the database connection
                    $conn->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>