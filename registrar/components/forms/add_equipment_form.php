<div class="card">
    <header class="card-header">
        <div class="new-title-container">
            <p class="new-title">Add Equipment</p>
        </div>
    </header>
    <div class="card-content">
        <form method="POST">
            <div class="field is-inline">
                <div class="field" style="width: 100%;">
                    <label class="label">Equipment Name:</label>
                    <div class="control">
                        <input class="input" type="text" name="name" required style="width: 100%;">
                    </div>
                </div>
            </div>

            <div class="field is-inline">
                <div class="field" style="width: 100%;">
                    <label class="label">Category:</label>
                    <div class="control">
                        <div class="select" style="width: 100%;">
                            <select name="category" required style="width: 100%;">
                                <option value="">Select Category</option>
                                <?php
                                $categories = ['Furniture', 'Electronics', 'Teaching Materials', 'Office Supplies', 'Laboratory Equipment'];
                                foreach ($categories as $cat) {
                                    echo '<option value="' . htmlspecialchars($cat) . '">' . htmlspecialchars($cat) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field is-inline">
                <div class="control">
                    <label class="label">Description:</label>
                    <textarea class="input" name="description" required></textarea>
                </div>
            </div>

            <div class="field is-inline">
                <div class="control">
                    <button type="submit" name="add_equipment" class="styled-button">Add Equipment</button>
                </div>
            </div>
        </form>
    </div>
</div>