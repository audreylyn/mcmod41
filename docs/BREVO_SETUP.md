# Brevo (Sendinblue) Email Setup Guide

This guide will help you set up Brevo email notifications for room reservation approvals and rejections in the MCiSmartSpace system.

## 📧 What Brevo Does

Brevo sends automated email notifications when:
- ✅ Room reservations are **approved** by department admins
- ❌ Room reservations are **rejected** by department admins

## 🚀 Quick Setup Steps

### 1. Create a Brevo Account

1. Go to [Brevo.com](https://www.brevo.com/) (formerly Sendinblue)
2. Click **"Sign up free"**
3. Fill out the registration form
4. Verify your email address
5. Complete the onboarding process

**Free Tier:** Brevo offers 300 emails/day for free, which is more generous than SendGrid!

---

### 2. Create an API Key

1. Log in to your [Brevo Dashboard](https://app.brevo.com/)
2. Click on your profile name (top right) → **"SMTP & API"**
3. Navigate to **"API Keys"** tab
4. Click **"Generate a new API key"** or **"Create a new API key"**
5. Configure your API key:
   - **Name:** `MCiSmartSpace-Production` (or any descriptive name)
6. Click **"Generate"** or **"Create"**
7. **IMPORTANT:** Copy the API key immediately - you won't be able to see it again!
   - Format: `xkeysib-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-xxxxxxxxxxxxxxxxx`

---

### 3. Verify Your Sender Email (Required)

Brevo requires you to verify the email address you'll send from.

#### Verify Sender Email

1. Go to **"Senders, Domains & Dedicated IPs"** in the main menu
2. Click **"Add a sender"**
3. Fill in the form:
   - **Email Address:** Your email (e.g., `mcismartspace@gmail.com`)
   - **Sender Name:** `MCiSmartSpace` (or your preferred name)
4. Click **"Add"** or **"Create"**
5. Check your email inbox and click the verification link
6. Wait for verification confirmation (usually instant)

#### Optional: Domain Authentication (Advanced)

For production environments with custom domains:

1. Go to **"Senders, Domains & Dedicated IPs"**
2. Click **"Domains"** tab
3. Click **"Authenticate a new domain"**
4. Follow the wizard to add DNS records (SPF, DKIM, DMARC)
5. This allows you to send from any `@yourdomain.com` email address

---

### 4. Configure the Application

#### Method 1: Environment Variables (Recommended for Production)

Set these environment variables on your server:

**Windows (PowerShell):**
```powershell
[System.Environment]::SetEnvironmentVariable('BREVO_API_KEY', 'xkeysib-your_actual_api_key_here', 'User')
[System.Environment]::SetEnvironmentVariable('BREVO_FROM_EMAIL', 'mcismartspace@gmail.com', 'User')
[System.Environment]::SetEnvironmentVariable('BREVO_FROM_NAME', 'MCiSmartSpace', 'User')
```

**Linux/Mac (Terminal):**
```bash
export BREVO_API_KEY="xkeysib-your_actual_api_key_here"
export BREVO_FROM_EMAIL="mcismartspace@gmail.com"
export BREVO_FROM_NAME="MCiSmartSpace"
```

**For permanent setup**, add these to:
- Windows: System Environment Variables
- Linux/Mac: `~/.bashrc` or `~/.zshrc`
- Docker: `.env` file or docker-compose.yml
- Azure/AWS: Application Settings

#### Method 2: Configuration File (For Local Development)

1. Navigate to `department-admin/config/`
2. Copy `email_config_brevo.php.template` to `email_config.php`:
   ```bash
   cp department-admin/config/email_config_brevo.php.template department-admin/config/email_config.php
   ```
3. Open `email_config.php` and replace:
   ```php
   $brevo_api_key = 'YOUR_BREVO_API_KEY_HERE';
   ```
   with your actual API key:
   ```php
   $brevo_api_key = 'xkeysib-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-xxxxxxxxxx';
   ```
4. Update the sender email if different:
   ```php
   $from_email = 'mcismartspace@gmail.com'; // Must be verified in Brevo
   $from_name = 'MCiSmartSpace';
   ```

**⚠️ SECURITY WARNING:**
- **NEVER commit `email_config.php` to Git**
- Ensure `email_config.php` is in `.gitignore`
- Only share API keys through secure channels

---

### 5. Enable Email Notifications

In `department-admin/config/email_config.php`:

```php
// Email Settings
define('ENABLE_EMAIL_NOTIFICATIONS', true); // Set to true to enable
```

---

## 📝 Email Templates

The system sends two types of emails:

### Approval Email
- **Subject:** "Room Reservation Approved - [Activity Name]"
- **Content:** Includes reservation details, room information, date/time, and next steps
- **Sent to:** Student or Teacher who made the reservation

### Rejection Email
- **Subject:** "Room Reservation Update - [Activity Name]"
- **Content:** Includes rejection reason and reservation details
- **Sent to:** Student or Teacher who made the reservation

---

## 🧪 Testing Email Functionality

### Test 1: Approve a Reservation

1. Log in as a **Department Admin**
2. Go to **Room Approval** page
3. Click **Approve** on a pending reservation
4. Check the recipient's email inbox (including spam folder)

### Test 2: Reject a Reservation

1. Log in as a **Department Admin**
2. Go to **Room Approval** page
3. Click **Reject** on a pending reservation
4. Enter a rejection reason
5. Check the recipient's email inbox

### Troubleshooting Tests

If emails aren't being sent, check:

1. **Application Logs:**
   - Look for error messages in PHP error logs
   - Check for "Failed to send approval/rejection email" messages

2. **Brevo Dashboard:**
   - Go to Brevo Dashboard → **"Statistics"** → **"Email"**
   - Look for sent emails, bounces, or blocks
   - Check **"Logs"** section for detailed activity

3. **Common Issues:**
   - API key is incorrect or not set
   - Sender email is not verified in Brevo
   - `ENABLE_EMAIL_NOTIFICATIONS` is set to `false`
   - Firewall blocking outbound connections

---

## 🔍 How It Works

### Code Flow

1. **Department Admin** approves/rejects a room reservation in `dept_room_approval.php`
2. `approve-reject.php` processes the action:
   - Updates database status
   - Checks if email is enabled and configured
   - Fetches reservation and requester details
   - Calls `BrevoEmailService` class
3. `brevo_email_service.php` handles:
   - Building HTML and text email templates
   - Sending via Brevo API v3 using cURL
   - Returning success/failure status

### API Endpoint

Brevo uses: `https://api.brevo.com/v3/smtp/email`

### Key Files

```
department-admin/
├── config/
│   ├── email_config_brevo.php.template  # Template configuration file
│   └── email_config.php                 # Your actual config (create from template)
├── includes/
│   ├── approve-reject.php               # Processes approvals/rejections
│   └── brevo_email_service.php          # Brevo API integration
└── dept_room_approval.php               # Admin approval interface
```

---

## 🔐 Security Best Practices

1. **Never commit API keys to version control**
   - Use environment variables for production
   - Keep `email_config.php` in `.gitignore`

2. **Rotate API keys regularly**
   - Create new keys every 90 days
   - Delete old keys after rotation

3. **Monitor usage**
   - Check Brevo dashboard for unusual activity
   - Set up alerts for quota limits

4. **Verify sender domains**
   - Use domain authentication for production
   - Avoid using personal email addresses for production

5. **Use HTTPS**
   - Ensure your server uses HTTPS
   - API calls are encrypted

---

## 📊 Brevo Dashboard

Monitor your emails at: [Brevo Dashboard](https://app.brevo.com/)

View:
- **Statistics:** Delivery rates, open rates, click rates
- **Logs:** Detailed email activity and errors
- **Contacts:** Manage recipients and lists
- **API Keys:** View and manage keys

---

## 🚨 Troubleshooting

### Emails Not Being Sent

**Check 1: Verify API Key**
```php
// Add this temporarily to test
var_dump(defined('BREVO_API_KEY'));
var_dump(BREVO_API_KEY);
```

**Check 2: Test Brevo Connection**
```php
// Test code (remove after testing)
$test = new BrevoEmailService(BREVO_API_KEY, FROM_EMAIL, FROM_NAME);
$result = $test->sendEmail(
    'your-test-email@gmail.com',
    'Test User',
    'Test Subject',
    '<h1>Test HTML</h1>',
    'Test Text'
);
var_dump($result);
```

**Check 3: Review Error Logs**
- Check PHP error logs for cURL errors
- Look for Brevo API error responses
- Verify SSL certificates are working

### Common Error Messages

| Error | Cause | Solution |
|-------|-------|----------|
| `401 Unauthorized` | Invalid API key | Verify API key is correct |
| `400 Bad Request - Sender not found` | Sender not verified | Verify sender email in Brevo |
| `403 Forbidden` | Insufficient permissions | Check API key permissions |
| `cURL error` | Network/SSL issue | Check firewall, SSL certificates |
| `No email sent` | Config not loaded | Ensure `email_config.php` exists |

---

## 💡 Additional Tips

1. **Development Mode:** Set `ENABLE_EMAIL_NOTIFICATIONS = false` during development to avoid sending test emails

2. **Email Customization:** Edit templates in `brevo_email_service.php` to customize email design

3. **Multiple Environments:** Use different API keys for development, staging, and production

4. **Cost Management:** Monitor Brevo usage to stay within free tier (300 emails/day)

5. **Email Testing Tools:** Use [Mailtrap](https://mailtrap.io/) or similar for development email testing

6. **Brevo vs SendGrid:** Brevo offers more free emails per day (300 vs 100) and simpler pricing

---

## 🔄 Migration from SendGrid

If you're migrating from SendGrid:

1. Create Brevo account and get API key
2. Update `email_config.php` with Brevo credentials
3. The code has been updated to use `BrevoEmailService`
4. Test thoroughly before deploying to production
5. Monitor both services during transition

---

## 📞 Support

- **Brevo Documentation:** [Brevo API Docs](https://developers.brevo.com/)
- **Brevo Support:** [Brevo Help Center](https://help.brevo.com/)
- **Application Issues:** Contact your development team

---

## ✅ Checklist

- [ ] Brevo account created
- [ ] API key generated and saved securely
- [ ] Sender email verified in Brevo
- [ ] Environment variables configured OR `email_config.php` created
- [ ] `ENABLE_EMAIL_NOTIFICATIONS` set to `true`
- [ ] Test approval email sent successfully
- [ ] Test rejection email sent successfully
- [ ] Error logging reviewed
- [ ] API key added to `.gitignore` (if using config file)

---

**Last Updated:** November 11, 2025
**Version:** 1.0
**Migration:** Replaced SendGrid with Brevo for better free tier
