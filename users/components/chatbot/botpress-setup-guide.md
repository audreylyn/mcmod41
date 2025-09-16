# MCiSmartSpace Botpress Chatbot Setup Guide

## Overview
This guide will help you configure your Botpress chatbot for the MCiSmartSpace room reservation and equipment management system.

## Prerequisites
- Active Botpress Cloud account at https://app.botpress.cloud/
- Access to your MCiSmartSpace system files

## Step 1: Botpress Bot Configuration

### 1.1 Create Your Bot
1. Log into your Botpress Cloud dashboard
2. Click "Create Bot" 
3. Choose "Blank Bot" template
4. Name your bot: "MCiSmartSpace Support"
5. Add description: "Customer support chatbot for MCiSmartSpace room reservation system"

### 1.2 Get Your Bot Credentials
After creating your bot, you'll need these values:
- **Bot ID**: Found in your bot's settings
- **Client ID**: Found in the Integration settings
- **Webhook URL**: For advanced integrations (optional)

### 1.3 Update Configuration Files
Replace the placeholder values in `/users/components/chatbot/botpress-widget.php`:

```javascript
botId: 'YOUR_ACTUAL_BOT_ID_HERE',
clientId: 'YOUR_ACTUAL_CLIENT_ID_HERE',
```

## Step 2: Bot Knowledge Base Configuration

### 2.1 System Context
Add this information to your bot's knowledge base in Botpress:

**System Information:**
- System Name: MCiSmartSpace
- Purpose: Room reservation and equipment management system
- Users: Students and Teachers at Meycauayan College Incorporated
- Main Features: Room browsing, reservations, equipment reporting, profile management

**User Roles:**
- Student: Can browse rooms, make reservations, report equipment issues
- Teacher: Same as student with additional privileges

### 2.2 Common Support Topics

#### Room Reservations
- How to browse available rooms
- Making a room reservation
- Checking reservation status
- Canceling reservations
- Understanding approval process
- Reservation time limits and policies

#### Equipment Management
- How to report equipment issues
- Adding photos to equipment reports
- Tracking report status
- Understanding priority levels
- Equipment validation via QR codes

#### Account Management
- Editing profile information
- Changing passwords
- Account security best practices
- Session management

#### Technical Support
- Browser compatibility issues
- Mobile device usage
- QR code scanning problems
- Login/logout issues
- Navigation help

### 2.3 Sample Conversations

**Room Reservation Help:**
```
User: How do I reserve a room?
Bot: I'll help you reserve a room! Here's how:

1. Go to "Browse Room" from the main menu
2. Use filters to find rooms by building, capacity, or equipment
3. Click on a room to see details and availability
4. Click "Reserve" and fill out the reservation form
5. Submit and wait for approval

Would you like help with any specific step?
```

**Equipment Issue Reporting:**
```
User: I found broken equipment in a room
Bot: I'll help you report that equipment issue:

1. Go to "Equipment Reports" from the menu
2. Click "Report New Issue"
3. Select the room and equipment
4. Describe the problem
5. Add photos if possible
6. Submit the report

Your report will be tracked and you'll get updates on the repair status. Need help with any of these steps?
```

## Step 3: Advanced Configuration

### 3.1 User Context Integration
The chatbot automatically receives user information when they're logged in:
- User ID and name
- Role (Student/Teacher)
- Email address
- Current page context

### 3.2 Page-Specific Help
The system provides context-aware help based on the current page:
- Room browsing assistance
- Reservation history explanations
- Equipment reporting guidance
- Profile management help

### 3.3 Custom Styling
Update the stylesheet URL in the widget configuration to match your system's theme:
```javascript
stylesheet: 'https://webchat-styler-css.botpress.app/prod/code/YOUR_STYLE_ID/style.css'
```

## Step 4: Bot Training

### 4.1 Intent Training
Train your bot to recognize these intents:
- room_reservation_help
- equipment_issue_help
- account_management_help
- navigation_help
- technical_support
- general_inquiry

### 4.2 Entity Recognition
Configure entities for:
- Room names/numbers
- Building names
- Equipment types
- Time periods
- Issue priorities

### 4.3 Conversation Flows
Create flows for:
1. Welcome message and system introduction
2. Room reservation step-by-step guidance
3. Equipment issue reporting process
4. Account management assistance
5. Technical troubleshooting
6. Escalation to human support

## Step 5: Testing and Deployment

### 5.1 Test Scenarios
Test these common scenarios:
- New user asking about room reservations
- Student reporting equipment issues
- Teacher needing help with profile updates
- User having login problems
- Navigation assistance requests

### 5.2 Integration Testing
Verify:
- Widget appears on all user pages
- User context is properly passed
- Page-specific help works
- Mobile responsiveness
- Theme consistency

### 5.3 Performance Monitoring
Monitor:
- Response times
- User satisfaction
- Common unresolved queries
- Escalation rates

## Step 6: Maintenance

### 6.1 Regular Updates
- Update knowledge base with new features
- Add new common questions and answers
- Refine conversation flows based on usage
- Update system information as needed

### 6.2 Analytics Review
- Review conversation logs
- Identify improvement opportunities
- Track user satisfaction metrics
- Monitor technical performance

## Troubleshooting

### Common Issues:
1. **Widget not appearing**: Check Bot ID and Client ID configuration
2. **User context not working**: Verify session data is available
3. **Styling issues**: Update stylesheet URL or CSS overrides
4. **Mobile problems**: Check responsive CSS rules
5. **Performance issues**: Review script loading and initialization

### Support Resources:
- Botpress Documentation: https://botpress.com/docs
- MCiSmartSpace System Documentation
- Technical Support Contact Information

## Security Considerations

- Never expose sensitive user data in bot conversations
- Implement proper session validation
- Use HTTPS for all communications
- Regular security updates for Botpress integration
- Monitor for potential security vulnerabilities

---

For additional support with this integration, contact your system administrator or refer to the Botpress Cloud documentation.
