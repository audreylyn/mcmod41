<div class="qr-layout">
    <div class="qr-panel">
        <div class="qr-panel-header">Generate QR Code</div>
        <div class="qr-panel-bodys">
            <form id="qr-form">
                <div class="form-group">
                    <label for="equipment" class="form-label">Select Equipment</label>
                    <select class="form-select" id="equipment" name="equipment">
                        <option value="">-- Select Equipment --</option>
                        <?php
                        // Get department from session
                        $department = isset($_SESSION['department']) ? $_SESSION['department'] : null;

                        if ($department) {
                            // Map session department to database department for special cases
                            $map = [
                                'education and arts' => 'Education and Arts',
                                'criminal justice' => 'Criminal Justice'
                            ];
                            $likeDepartment = '%' . $department . '%';
                            $deptLower = strtolower($department);
                            if (isset($map[$deptLower])) {
                                $likeDepartment = '%' . $map[$deptLower] . '%';
                            }
                            $sql = "SELECT eu.unit_id, e.name, r.room_name, b.building_name, eu.serial_number
                                    FROM equipment_units eu
                                    JOIN equipment e ON eu.equipment_id = e.id
                                    JOIN rooms r ON eu.room_id = r.id
                                    JOIN buildings b ON r.building_id = b.id
                                    WHERE b.department LIKE ?
                                    ORDER BY b.building_name, r.room_name, e.name, eu.serial_number";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('s', $likeDepartment);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $value = json_encode([
                                        'id' => $row['unit_id'],
                                        'name' => $row['name'],
                                        'room' => $row['room_name'],
                                        'building' => $row['building_name'],
                                        'serial' => $row['serial_number']
                                    ]);
                                    $serial_display = $row['serial_number'] ? ' (SN: ' . $row['serial_number'] . ')' : '';
                                    echo '<option value=\'' . htmlspecialchars($value) . '\'>' .
                                        htmlspecialchars($row['name'] . $serial_display . ' - ' . $row['room_name'] . ', ' . $row['building_name']) .
                                        '</option>';
                                }
                            }
                        }
                        // If department is not set, show no equipment
                        ?>
                    </select>
                </div>

                <div class="custom-fields">
                    <div class="form-group">
                        <input type="text" class="form-input" id="custom-id" placeholder="Equipment ID" disabled style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-input" id="custom-name" placeholder="Equipment Name" disabled style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-input" id="custom-room" placeholder="Room" disabled style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-input" id="custom-building" placeholder="Building" disabled style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>
                </div>

                <div class="field">
                    <p class="help" style="color: #64748b; font-size: 0.875rem; text-align:left;">
                        <i class="mdi mdi-information"></i> Please select from the equipment list above.
                    </p>
                </div>

                <button type="submit" class="genQrbtn">Generate QR Code</button>
            </form>
        </div>
    </div>

    <div class="qr-panel">
        <div class="qr-panel-header">QR Code</div>
        <div class="qr-panel-body">
            <div id="qrcode" class="text-center"></div>
            <div id="qr-info" class="mb-3"></div>
            <button id="downloadBtn" style="display: none;" class="btn btn-primary mt-2">Download QR Code</button>
        </div>
    </div>
</div>