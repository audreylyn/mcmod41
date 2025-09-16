<?php
if (isset($_SESSION['success_message'])) {
    echo 'showModal("Success", "' . addslashes($_SESSION['success_message']) . '", "success");';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo 'showModal("Error", "' . addslashes($_SESSION['error_message']) . '", "error");';
    unset($_SESSION['error_message']);
}
