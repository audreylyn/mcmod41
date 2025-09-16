# SendGrid Email Notifications Setup Guide

## Overview
Your room reservation system now includes automatic email notifications using SendGrid API. When department admins approve or reject room reservation requests, students and teachers will automatically receive professional email notifications.

## Features Implemented
- ✅ **Approval Notifications**: Beautiful HTML emails sent when requests are approved
- ✅ **Rejection Notifications**: Professional emails with rejection reasons
- ✅ **Automatic User Detection**: System automatically identifies if requester is student or teacher
- ✅ **Email Templates**: Professional, responsive email templates with school branding
- ✅ **Error Handling**: Robust error handling with logging for troubleshooting

## Setup Instructions

### Step 1: Create SendGrid Account
1. Go to [https://sendgrid.com/](https://sendgrid.com/)
2. Sign up for a free account (allows 100 emails/day)
3. Verify your email address

### Step 2: Create API Key
1. Log into your SendGrid dashboard
2. Go to **Settings** → **API Keys**
3. Click **Create API Key**
4. Choose **Full Access** or **Mail Send** permissions
5. Copy the generated API key (you'll only see it once!)

### Step 3: Configure Email Settings
1. Open `department-admin/config/email_config.php`
2. Replace `'YOUR_SENDGRID_API_KEY'` with your actual API key
3. Update `FROM_EMAIL` with your school's email address
4. Update `FROM_NAME` with your preferred sender name

```php
// Example configuration:
define('SENDGRID_API_KEY', 'SG.your-actual-api-key-here');
define('FROM_EMAIL', 'noreply@yourschool.edu');
define('FROM_NAME', 'Your School Room Reservation System');
```

### Step 4: Verify Sender Email (Important!)
1. In SendGrid dashboard, go to **Settings** → **Sender Authentication**
2. Click **Verify a Single Sender**
3. Enter the email address you used in `FROM_EMAIL`
4. Complete the verification process

### Step 5: Test the System
1. Create a test room reservation request
2. Approve or reject it through the department admin panel
3. Check if the email was received
4. Check server error logs if emails aren't being sent

## Email Templates

### Approval Email Features:
- Professional HTML design with school colors
- Complete reservation details (room, date, time, etc.)
- Approver information
- Important reminders and next steps
- Mobile-responsive design

### Rejection Email Features:
- Respectful and professional tone
- Clear rejection reason
- Guidance for next steps
- Alternative suggestions

## Troubleshooting

### Emails Not Being Sent?
1. **Check API Key**: Ensure it's correctly set in `email_config.php`
2. **Verify Sender**: Make sure your FROM_EMAIL is verified in SendGrid
3. **Check Logs**: Look at server error logs for detailed error messages
4. **Test API Key**: Try sending a test email through SendGrid dashboard

### Common Issues:
- **403 Forbidden**: API key doesn't have proper permissions
- **401 Unauthorized**: Invalid API key
- **400 Bad Request**: Usually sender email not verified

### Enable/Disable Notifications
To temporarily disable email notifications, set:
```php
define('ENABLE_EMAIL_NOTIFICATIONS', false);
```

## Security Best Practices
- Never commit your API key to version control
- Use environment variables for production
- Regularly rotate your API keys
- Monitor your SendGrid usage and quotas

## File Structure
```
department-admin/
├── config/
│   └── email_config.php          # Email configuration
├── includes/
│   ├── sendgrid_email_service.php # Email service class
│   └── approve-reject.php         # Modified to send emails
└── SENDGRID_SETUP_INSTRUCTIONS.md # This file
```

## Support
- SendGrid Documentation: [https://docs.sendgrid.com/](https://docs.sendgrid.com/)
- SendGrid Support: Available through your dashboard
- Check server error logs for detailed debugging information

## Email Preview
Students and teachers will receive professionally formatted emails with:
- School branding and colors
- Complete reservation details
- Clear status (approved/rejected)
- Next steps and important reminders
- Contact information for questions

The system is now ready to automatically notify users about their room reservation status changes!
