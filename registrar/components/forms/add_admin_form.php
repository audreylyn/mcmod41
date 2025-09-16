<?php
$departments = ['Accountancy', 'Business Administration', 'Hospitality Management', 'Education and Arts', 'Criminal Justice'];
?>
<form id="adminForm" action="includes/add_admin_ajax.php" method="POST">
    <input type="hidden" name="action" value="add">
    <div class="field is-inline">
        <div class="control">
            <label class="label">First Name:</label>
            <input class="input" type="text" name="first_name" pattern="[A-Za-z\s]+" required>
        </div>
        <div class="control">
            <label class="label">Last Name:</label>
            <input class="input" type="text" name="last_name" pattern="[A-Za-z\s]+" required>
        </div>
    </div>

    <div class="field is-inline">
        <div class="control">
            <label class="label">Department:</label>
            <div class="select">
                <select name="department" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="field is-inline">
        <div class="control">
            <label class="label">Email:</label>
            <input class="input" type="email" name="email" required>
        </div>
    </div>

    <div class="field is-inline">
        <div class="control">
            <label class="label">Password:</label>
            <input class="input" type="password" name="password" minlength="8" required>
        </div>
    </div>

    <div class="field is-inline">
        <div class="control">
            <button type="submit" class="styled-button">Register</button>
        </div>
    </div>
</form>
