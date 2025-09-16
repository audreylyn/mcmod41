<?php
/**
 * Email Configuration for SendGrid
 * 
 * SECURITY NOTICE: This file contains sensitive API keys.
 * Make sure this file is added to .gitignore and never committed to version control.
 */

// Try to load from environment variables first, then fallback to manual configuration
$sendgrid_api_key = getenv('SENDGRID_API_KEY') ?: null;
$from_email = getenv('SENDGRID_FROM_EMAIL') ?: 'mcismartspace@gmail.com';
$from_name = getenv('SENDGRID_FROM_NAME') ?: 'MCiSmartSpace';

// If no environment variable is set, you can manually set it here for local development
// IMPORTANT: DO NOT COMMIT THE ACTUAL API KEY TO VERSION CONTROL
if (!$sendgrid_api_key) {
    // For local development only - replace with your actual key
    $sendgrid_api_key = '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';
}

// SendGrid Configuration
define('SENDGRID_API_KEY', $sendgrid_api_key);
define('FROM_EMAIL', $from_email);
define('FROM_NAME', $from_name);

// Email Settings
define('ENABLE_EMAIL_NOTIFICATIONS', true); // Set to false to disable email notifications

/**
 * Instructions for setting up SendGrid:
 * 
 * RECOMMENDED APPROACH (Environment Variables):
 * 1. Set environment variables on your server:
 *    - SENDGRID_API_KEY=your_actual_api_key_here
 *    - SENDGRID_FROM_EMAIL=your_verified_email@domain.com
 *    - SENDGRID_FROM_NAME=Your App Name
 * 
 * ALTERNATIVE APPROACH (Local Development):
 * 1. Replace 'YOUR_SENDGRID_API_KEY_HERE' above with your actual API key
 * 2. Make sure this file is in .gitignore
 * 3. Never commit the actual API key to version control
 * 
 * SendGrid Setup:
 * 1. Sign up for a SendGrid account at https://sendgrid.com/
 * 2. Go to Settings > API Keys in your SendGrid dashboard
 * 3. Create a new API key with "Mail Send" permissions
 * 4. Use the API key in environment variables or local config
 * 5. Verify your sender email in SendGrid
 */
?>
