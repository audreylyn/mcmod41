<?php
// Brevo Email Service Configuration
// IMPORTANT: Never commit your actual API key to version control!

// Get API key from environment variable (recommended for production)
$brevo_api_key = getenv('BREVO_API_KEY') ?: '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';

define('BREVO_API_KEY', $brevo_api_key);
define('FROM_EMAIL', 'mcismartspace@gmail.com');
define('FROM_NAME', 'MCiSmartSpace');
define('ENABLE_EMAIL_NOTIFICATIONS', true);