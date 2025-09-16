<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
    <script>
    // Clear Botpress chat data from localStorage before logout
    if (typeof localStorage !== 'undefined') {
        // Clear specific Botpress-related items
        localStorage.removeItem('bp-user-id');
        localStorage.removeItem('bp-conversation');
        
        // Also clear any items that start with 'bp-' to be thorough
        Object.keys(localStorage).forEach(key => {
            if (key.startsWith('bp-')) {
                localStorage.removeItem(key);
            }
        });
    }
    
    // Redirect after clearing
    window.location.href = "../index.php";
    </script>
</head>
<body>
    <p>Logging out...</p>
</body>
</html>

<?php
session_destroy();
exit();