# Botpress Quick Replies Setup Guide

## How to Add Predefined Questions in Botpress

### Step 1: Access Your Botpress Dashboard
1. Go to https://app.botpress.cloud/
2. Select your "MCiSmartSpace Support" bot
3. Go to the **Conversation Studio**

### Step 2: Create Quick Reply Buttons

#### Method 1: Welcome Message with Quick Replies
1. Go to **Flows** → **Main Flow**
2. Find or create the **Welcome** node
3. Add this content:

```
Welcome to MCiSmartSpace! I can help you with:

How can I assist you today?
```

4. Add **Quick Replies** (buttons):
   - 🏢 Reserve a Room
   - 📅 Check My Reservations  
   - 🔧 Report Equipment Issue
   - 👤 Account Help
   - ❓ Other Questions

#### Method 2: Create Intent-Based Responses

**Create these Intents in your bot:**

1. **room_reservation_help**
   - Training phrases: "how to reserve room", "book room", "room booking"
   - Response: Step-by-step room reservation guide with quick replies

2. **check_reservations**
   - Training phrases: "my reservations", "reservation status", "booking history"
   - Response: How to check reservation history

3. **equipment_issues**
   - Training phrases: "report problem", "broken equipment", "equipment issue"
   - Response: Equipment reporting process

4. **account_help**
   - Training phrases: "change password", "profile", "account"
   - Response: Account management options

### Step 3: Configure Specific Quick Reply Flows

#### Room Reservation Flow
When user clicks "Reserve a Room", show these options:
- 🔍 How to Find Rooms
- 📝 Reservation Process
- ⏰ Booking Rules
- ❌ Cancel Reservation
- 📧 Approval Status

#### Equipment Issues Flow
When user clicks "Report Equipment Issue":
- 📱 How to Report
- 📸 Adding Photos
- 🚨 Priority Levels
- 📊 Check Report Status

#### Account Help Flow
When user clicks "Account Help":
- 🔑 Change Password
- 👤 Update Profile
- 🔒 Account Security
- 📞 Contact Admin

### Step 4: Add Knowledge Base Content

In your Botpress **Knowledge Base**, add this information:

#### Room Reservation Knowledge
```
ROOM RESERVATION PROCESS:

Step 1: Go to "Browse Room" in the main menu
Step 2: Use filters to find suitable rooms (building, capacity, equipment)
Step 3: Click on a room to see details and availability
Step 4: Click "Reserve" and fill out the form
Step 5: Submit for admin approval
Step 6: Check email for approval notification

RESERVATION RULES:
- Book at least 2 hours in advance
- Maximum 4 hours per reservation
- Up to 3 active reservations allowed
- All reservations need admin approval
- Cancel at least 1 hour before start time

CHECKING STATUS:
Go to "Reservation History" to see:
- Pending: Waiting for approval
- Approved: Confirmed booking
- Rejected: Not approved (reason shown)
- Completed: Past reservation
- Cancelled: You cancelled it
```

#### Equipment Reporting Knowledge
```
EQUIPMENT ISSUE REPORTING:

Step 1: Go to "Equipment Reports" menu
Step 2: Click "Report New Issue"
Step 3: Select room and equipment
Step 4: Describe the problem
Step 5: Add photos (recommended)
Step 6: Choose priority level
Step 7: Submit report

PRIORITY LEVELS:
- Critical: Safety hazard, room unusable
- High: Major equipment failure
- Medium: Partially working
- Low: Minor cosmetic issues

TRACKING REPORTS:
Check "Equipment Reports" to see status and updates on your reports.
```

### Step 5: Create Conversation Flows

#### Example Flow Structure:
```
User: "How can I reserve a room?"
Bot: "I'll help you reserve a room! Here's the process:

1️⃣ Go to 'Browse Room' from the main menu
2️⃣ Use filters to find rooms by building, capacity, or equipment
3️⃣ Click on a room to see details and availability
4️⃣ Click 'Reserve' and fill out the reservation form
5️⃣ Submit and wait for admin approval

Would you like help with any specific step?"

Quick Replies:
- 🔍 Finding Rooms
- 📝 Filling the Form  
- ⏰ Booking Rules
- 📧 Approval Process
- ❌ Cancelling Reservations
```

### Step 6: Test Your Bot

1. Use the **Emulator** in Botpress to test responses
2. Try these test phrases:
   - "How do I reserve a room?"
   - "I need to report broken equipment"
   - "Check my reservations"
   - "Change my password"
   - "Help with QR scanning"

### Step 7: Publish Your Bot

1. Click **Publish** in your Botpress dashboard
2. The updated bot will be live on your website
3. Test on your actual MCiSmartSpace pages

## Sample Quick Reply Configurations

### Main Menu Quick Replies
```json
{
  "quick_replies": [
    {
      "content_type": "text",
      "title": "🏢 Reserve Room",
      "payload": "RESERVE_ROOM"
    },
    {
      "content_type": "text", 
      "title": "📅 My Reservations",
      "payload": "CHECK_RESERVATIONS"
    },
    {
      "content_type": "text",
      "title": "🔧 Report Issue", 
      "payload": "REPORT_EQUIPMENT"
    },
    {
      "content_type": "text",
      "title": "👤 Account Help",
      "payload": "ACCOUNT_HELP"
    }
  ]
}
```

### Room Reservation Sub-Menu
```json
{
  "quick_replies": [
    {
      "content_type": "text",
      "title": "🔍 Find Rooms",
      "payload": "FIND_ROOMS"
    },
    {
      "content_type": "text",
      "title": "📝 Booking Process", 
      "payload": "BOOKING_PROCESS"
    },
    {
      "content_type": "text",
      "title": "⏰ Rules & Policies",
      "payload": "BOOKING_RULES"
    },
    {
      "content_type": "text",
      "title": "❌ Cancel Booking",
      "payload": "CANCEL_BOOKING"
    }
  ]
}
```

This setup will give your users easy access to common questions and create a much better chatbot experience!
