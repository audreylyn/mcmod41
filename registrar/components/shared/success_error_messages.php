<?php if ($success_message): ?>
    <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>