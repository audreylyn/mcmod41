# SendGrid API Key Setup Instructions

⚠️ **SECURITY NOTICE**: Never commit your actual SendGrid API key to version control!

## Setup Options

### Option 1: Environment Variables (Recommended for Production)

1. Set environment variables on your server:

   ```bash
   SENDGRID_API_KEY=your_actual_sendgrid_api_key_here
   SENDGRID_FROM_EMAIL=mcismartspace@gmail.com
   SENDGRID_FROM_NAME=MCiSmartSpace
   ```

2. The application will automatically use these environment variables.

### Option 2: Local Configuration (Development Only)

1. Open `department-admin/config/email_config.php`

2. Replace `YOUR_SENDGRID_API_KEY_HERE` with your actual API key:

   ```php
   $sendgrid_api_key = 'SG.your_actual_api_key_here';
   ```

3. **IMPORTANT**: This file is already in `.gitignore` to prevent accidental commits.

## Getting Your SendGrid API Key

1. Sign up for a SendGrid account at https://sendgrid.com/
2. Go to Settings > API Keys in your SendGrid dashboard
3. Create a new API key with "Mail Send" permissions
4. Copy the generated API key
5. Use it in your environment variables or local config

## Testing the Configuration

After setup, test the email functionality by:

1. Logging into the department admin panel
2. Try sending an email notification (e.g., reject a room request)
3. Check if the email is sent successfully

## Troubleshooting

- Ensure your sender email is verified in SendGrid
- Check SendGrid logs for delivery issues
- Verify API key permissions are set to "Mail Send"
- Make sure the API key is not expired

## Security Best Practices

- ✅ Use environment variables in production
- ✅ Keep API keys in `.gitignore` files
- ✅ Regularly rotate API keys
- ❌ Never commit API keys to version control
- ❌ Don't share API keys in chat/email
- ❌ Don't hardcode API keys in source code
