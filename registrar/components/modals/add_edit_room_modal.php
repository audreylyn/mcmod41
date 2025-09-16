<!-- Add/Edit Modal -->
<div id="roomModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add Room</h2>
            <button class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="modal-body">
            <form id="roomForm" method="POST">
                <input type="hidden" name="room_id" id="room_id">
                
                <div class="field">
                    <label class="label">Room Name</label>
                    <input class="input" type="text" name="room_name" id="room_name" required>
                </div>

                <div class="field">
                    <label class="label">Room Type</label>
                    <div class="select is-fullwidth">
                        <select name="room_type" id="room_type" required>
                            <option value="">Select Room Type</option>
                            <?php
                            $room_types = ['Classroom', 'Gymnasium', 'Auditorium', 'Lecture Hall'];
                            foreach ($room_types as $type) {
                                echo '<option value="' . htmlspecialchars($type) . '">' . htmlspecialchars($type) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label class="label" id="capacityLabel">Capacity (max 500)</label>
                    <input class="input" type="number" name="capacity" id="capacity" min="1" max="500" required>
                </div>

                <div class="field">
                    <label class="label">Building</label>
                    <div class="select is-fullwidth">
                        <select name="building_id" id="building_id" required>
                            <option value="">Select Building</option>
                            <?php
                            $building_sql = "SELECT id, building_name FROM buildings ORDER BY building_name ASC";
                            $building_result_modal = $conn->query($building_sql);
                            while ($building = $building_result_modal->fetch_assoc()) {
                                echo "<option value=\"" . htmlspecialchars($building['id']) . "\">" . htmlspecialchars($building['building_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer" style="text-align: right;">
                    <button type="button" class="button is-cancel" id="modalCancel">Cancel</button>
                    <button type="submit" class="button is-save" id="formSubmitBtn" name="add_room">Save Changes</button>
                </div>
            </form>
        </div>
        
    </div>
</div>