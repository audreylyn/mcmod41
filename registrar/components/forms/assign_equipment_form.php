<div class="card mt-6">
    <header class="card-header">
        <div class="new-title-container">
            <p class="new-title">Assign Equipment to Room</p>
        </div>
    </header>
    <div class="card-content">
        <form method="POST">
            <div class="field">
                <div class="control" style="width: 100%; margin-bottom: 1rem;">
                    <label class="label">Select Room:</label>
                    <select class="input" name="room_id" required style="width: 100%;">
                        <option value="">Select a room</option>
                        <?php foreach ($rooms_list as $room): ?>
                            <option value="<?= htmlspecialchars($room['id']) ?>">
                                <?= htmlspecialchars($room['room_name']) ?> (<?= htmlspecialchars($room['building_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="control" style="width: 100%; margin-bottom: 1rem;">
                    <label class="label">Select Equipment:</label>
                    <select class="input" name="equipment_id" required style="width: 100%;">
                        <option value="">Select equipment</option>
                        <?php foreach ($equipment_dropdown as $equipment): ?>
                            <option value="<?= htmlspecialchars($equipment['id']) ?>">
                                <?= htmlspecialchars($equipment['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="control" style="width: 100%; margin-bottom: 1rem;">
                    <label class="label">Serial Number (optional):</label>
                    <input class="input" type="text" name="serial_number" placeholder="e.g. SN12345">
                </div>
                <div class="control" style="width: 100%; margin-bottom: 1rem;">
                    <label class="label">Quantity:</label>
                    <input class="input" type="number" name="quantity" value="1" min="1" required style="width: 100%;">
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <button type="submit" name="assign_equipment" class="styled-button">Assign Equipment</button>
                </div>
            </div>
        </form>
    </div>
</div>