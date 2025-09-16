# MCiSmartSpace Chatbot Integration

## Overview
This directory contains the complete Botpress chatbot integration for the MCiSmartSpace system. The chatbot provides customer support for room reservations, equipment reporting, and general system assistance.

## Files Structure

### Core Integration Files
- `botpress-widget.php` - Main chatbot widget with Botpress integration
- `chatbot-config.php` - Configuration class with system context
- `chatbot-layout.php` - Layout component for easy integration
- `quick-integration.php` - Helper for quick chatbot addition to pages

### Documentation
- `botpress-setup-guide.md` - Complete setup and configuration guide
- `README.md` - This file

## Quick Start

### 1. Get Your Botpress Credentials
1. Sign up/login at https://app.botpress.cloud/
2. Create a new bot named "MCiSmartSpace Support"
3. Get your Bot ID and Client ID from the dashboard

### 2. Update Configuration
Edit `botpress-widget.php` and replace:
```javascript
botId: 'YOUR_BOT_ID', // Replace with your actual Bot ID
clientId: 'YOUR_CLIENT_ID', // Replace with your actual Client ID
```

### 3. Integration Status
The chatbot has been integrated into these user pages:
- ✅ `users_browse_room.php`
- ✅ `users_reservation_history.php` 
- ✅ `equipment_report_status.php`
- ✅ `edit_profile.php`

### 4. Features Included

#### Context-Aware Support
- Automatically detects current page and provides relevant help
- Passes user session data (name, role, ID) to chatbot
- Page-specific help buttons and guidance

#### System Integration
- Matches your system's green theme (#0f4228)
- Mobile responsive design
- Integrates with existing UI components
- Proper session handling

#### Support Topics Covered
- Room reservation process
- Equipment issue reporting
- Account management
- Navigation assistance
- Technical troubleshooting

## Usage

### For Developers
Include the chatbot in any user page by adding:
```php
<?php include "layout/chatbot-layout.php"; ?>
```

### For Quick Integration
Use the helper function:
```php
<?php 
include "components/chatbot/quick-integration.php";
addChatbotToPage();
?>
```

## Customization

### Theme Colors
Update colors in the widget configuration:
```javascript
themeColor: '#0f4228', // Your primary color
```

### Bot Personality
Configure in your Botpress dashboard:
- Bot name: "MCiSmartSpace Support"
- Avatar: Uses your system logo
- Tone: Professional and helpful

### Context Data
The system automatically provides:
- User information (if logged in)
- Current page context
- System features and capabilities
- Session data

## Testing

### Test Scenarios
1. **New User**: Ask about room reservations
2. **Equipment Issues**: Report broken equipment
3. **Account Help**: Password change assistance
4. **Navigation**: Help finding features
5. **Mobile**: Test on mobile devices

### Verification Checklist
- [ ] Widget appears on all user pages
- [ ] User context is passed correctly
- [ ] Theme matches system design
- [ ] Mobile responsiveness works
- [ ] Help buttons function properly
- [ ] Bot responds appropriately

## Troubleshooting

### Common Issues
1. **Widget not showing**: Check Bot ID and Client ID
2. **No user context**: Verify PHP session is active
3. **Styling problems**: Check CSS conflicts
4. **Mobile issues**: Verify responsive CSS rules

### Debug Mode
Add to widget configuration for debugging:
```javascript
debug: true
```

## Security Notes
- User data is only passed if session is active
- No sensitive information is logged
- All communications use HTTPS
- Session validation is maintained

## Support
- Botpress Documentation: https://botpress.com/docs
- System Admin: Contact your IT department
- Integration Issues: Check the setup guide

---

**Next Steps:**
1. Configure your Botpress bot with the provided knowledge base
2. Test the integration thoroughly
3. Train the bot with common user questions
4. Monitor usage and improve responses

The chatbot is now ready to provide 24/7 customer support for your MCiSmartSpace users!
