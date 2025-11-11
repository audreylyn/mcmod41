# Room Reservation System - Testing Scenarios

## Overview
This document provides detailed testing scenarios for the room reservation system, including priority handling, conflict detection, and approval workflows.

---

## Priority Rules

### 🎯 **Booking Priority Hierarchy**
1. **Teacher** - Highest Priority (Academic/Educational activities)
2. **Student** - Lower Priority (Student organization activities)

### 📋 **Priority Conflict Resolution**
- When Teacher and Student book the same room/time:
  - Department Admin should approve Teacher request first
  - System should alert when attempting to approve Student request if Teacher request exists for same slot
  - If Student is approved first (by mistake), system should warn when Teacher request is being approved

---

## Test Scenarios

### **Scenario 1: Basic Reservation (No Conflicts)**

#### Test 1.1: Student Books Available Room
**Steps:**
1. Login as Student
2. Browse rooms and select available room
3. Fill reservation form:
   - Activity Name: "Club Meeting"
   - Purpose: "Monthly general assembly for Computer Club members"
   - Date: November 15, 2025
   - Time: 2:00 PM - 4:00 PM
   - Participants: 25
4. Submit request

**Expected Result:**
- ✅ Success message: "Your room reservation request has been submitted successfully"
- ✅ Request status: "Pending"
- ✅ Request appears in reservation history

---

#### Test 1.2: Teacher Books Available Room
**Steps:**
1. Login as Teacher
2. Browse rooms and select available room
3. Fill reservation form:
   - Activity Name: "Programming Workshop"
   - Purpose: "Hands-on Java programming workshop for 2nd year students"
   - Date: November 16, 2025
   - Time: 9:00 AM - 12:00 PM
   - Participants: 30
4. Submit request

**Expected Result:**
- ✅ Success message: "Your room reservation request has been submitted successfully"
- ✅ Request status: "Pending"
- ✅ Request appears in reservation history

---

### **Scenario 2: Time Overlap Detection**

#### Test 2.1: Exact Same Time Slot
**Steps:**
1. Student A books Room 101: Nov 15, 2:00 PM - 4:00 PM (Status: Approved)
2. Student B tries to book Room 101: Nov 15, 2:00 PM - 4:00 PM

**Expected Result:**
- ❌ Error: "This room is already booked for the selected time. Please choose another time or room."

---

#### Test 2.2: Partial Overlap (Start Time)
**Steps:**
1. Student A books Room 102: Nov 15, 2:00 PM - 4:00 PM (Status: Approved)
2. Student B tries to book Room 102: Nov 15, 3:00 PM - 5:00 PM

**Expected Result:**
- ❌ Error: "This room is already booked for the selected time. Please choose another time or room."

---

#### Test 2.3: Partial Overlap (End Time)
**Steps:**
1. Student A books Room 103: Nov 15, 3:00 PM - 5:00 PM (Status: Approved)
2. Student B tries to book Room 103: Nov 15, 1:00 PM - 4:00 PM

**Expected Result:**
- ❌ Error: "This room is already booked for the selected time. Please choose another time or room."

---

#### Test 2.4: Complete Overlap (Contained Within)
**Steps:**
1. Student A books Room 104: Nov 15, 1:00 PM - 6:00 PM (Status: Approved)
2. Student B tries to book Room 104: Nov 15, 2:00 PM - 4:00 PM

**Expected Result:**
- ❌ Error: "This room is already booked for the selected time. Please choose another time or room."

---

#### Test 2.5: Complete Overlap (Contains Existing)
**Steps:**
1. Student A books Room 105: Nov 15, 2:00 PM - 4:00 PM (Status: Approved)
2. Student B tries to book Room 105: Nov 15, 1:00 PM - 6:00 PM

**Expected Result:**
- ❌ Error: "This room is already booked for the selected time. Please choose another time or room."

---

#### Test 2.6: Adjacent Time Slots (Should Work)
**Steps:**
1. Student A books Room 106: Nov 15, 2:00 PM - 4:00 PM (Status: Approved)
2. Student B tries to book Room 106: Nov 15, 4:00 PM - 6:00 PM

**Expected Result:**
- ✅ Success: Reservation submitted successfully
- ✅ No overlap (end time of first = start time of second is allowed)

---

#### Test 2.7: Same Time, Different Dates (Should Work)
**Steps:**
1. Student A books Room 107: Nov 15, 2:00 PM - 4:00 PM (Status: Approved)
2. Student B tries to book Room 107: Nov 16, 2:00 PM - 4:00 PM

**Expected Result:**
- ✅ Success: Reservation submitted successfully
- ✅ Different dates don't conflict

---

#### Test 2.8: Same Time, Different Rooms (Should Work)
**Steps:**
1. Student A books Room 108: Nov 15, 2:00 PM - 4:00 PM (Status: Approved)
2. Student B tries to book Room 109: Nov 15, 2:00 PM - 4:00 PM

**Expected Result:**
- ✅ Success: Reservation submitted successfully
- ✅ Different rooms don't conflict

---

### **Scenario 3: Priority Conflicts (Teacher vs Student)**

#### Test 3.1: Teacher and Student Request Same Slot - Approve Teacher First ⭐
**Steps:**
1. **Student** requests Room 201: Nov 20, 10:00 AM - 12:00 PM (Status: Pending)
   - Activity: "Student Council Meeting"
2. **Teacher** requests Room 201: Nov 20, 10:00 AM - 12:00 PM (Status: Pending)
   - Activity: "Data Structures Lecture"
3. **Department Admin** views pending requests
4. Admin clicks **Approve** on Teacher's request

**Expected Result:**
- ✅ Teacher request approved successfully
- ✅ System checks for conflicting requests
- ✅ Student request should be **automatically rejected** with reason:
  - "Your request conflicts with an approved Teacher reservation. Teacher requests have priority for academic purposes."

---

#### Test 3.2: Teacher and Student Request Same Slot - Try Approve Student First ⚠️
**Steps:**
1. **Teacher** requests Room 202: Nov 21, 2:00 PM - 4:00 PM (Status: Pending)
   - Activity: "Database Workshop"
2. **Student** requests Room 202: Nov 21, 2:00 PM - 4:00 PM (Status: Pending)
   - Activity: "Club Photography Session"
3. **Department Admin** views pending requests
4. Admin clicks **Approve** on Student's request

**Expected Result:**
- ⚠️ **Warning Alert** appears:
  ```
  ⚠️ Priority Conflict Detected!
  
  A Teacher has also requested this room for the same time:
  - Teacher: Prof. Juan Dela Cruz
  - Activity: Database Workshop
  - Date: November 21, 2025
  - Time: 2:00 PM - 4:00 PM
  
  Teachers have priority for room reservations. 
  
  Do you want to:
  • Approve Teacher request and reject Student request (Recommended)
  • Approve Student request anyway and reject Teacher request
  • Keep both as pending for manual review
  ```
- ❌ If admin clicks "Approve anyway": Show confirmation dialog
- ✅ If admin clicks "Approve Teacher": Approve teacher, reject student with priority reason

---

#### Test 3.3: Multiple Students Request Same Slot (No Teacher Conflict)
**Steps:**
1. **Student A** requests Room 203: Nov 22, 9:00 AM - 11:00 AM (Status: Pending)
2. **Student B** requests Room 203: Nov 22, 9:00 AM - 11:00 AM (Status: Pending)
3. **Department Admin** approves Student A's request

**Expected Result:**
- ✅ Student A approved successfully
- ✅ Student B automatically rejected with reason:
  - "This room is already booked for the selected time. The reservation was approved for another student."

---

#### Test 3.4: Multiple Teachers Request Same Slot
**Steps:**
1. **Teacher A** requests Room 204: Nov 23, 1:00 PM - 3:00 PM (Status: Pending)
2. **Teacher B** requests Room 204: Nov 23, 1:00 PM - 3:00 PM (Status: Pending)
3. **Department Admin** approves Teacher A's request

**Expected Result:**
- ✅ Teacher A approved successfully
- ⚠️ **Alert** appears for Teacher B's conflicting request:
  ```
  Another Teacher's reservation was approved for this slot.
  Please contact Teacher [Name] to coordinate or select different time.
  ```
- ✅ Teacher B automatically rejected with reason:
  - "This room is already booked by another teacher. Please coordinate with [Teacher A Name] or choose a different time slot."

---

### **Scenario 4: Room Maintenance Conflicts**

#### Test 4.1: Try to Reserve Room Under Maintenance
**Steps:**
1. Department Admin sets Room 301 to "Maintenance" status
   - Reason: "Air conditioning repair"
   - End Date: Nov 25, 2025
2. Student tries to book Room 301: Nov 18, 2:00 PM - 4:00 PM

**Expected Result:**
- ❌ Error: "This room is currently under maintenance and cannot be reserved. Please choose another room."

---

#### Test 4.2: Try to Approve Request for Room That Went Into Maintenance
**Steps:**
1. Student submits request for Room 302: Nov 24, 10:00 AM - 12:00 PM (Status: Pending)
2. Department Admin sets Room 302 to "Maintenance" status (Nov 23-25)
3. Admin tries to approve the pending request

**Expected Result:**
- ⚠️ **Warning Alert**:
  ```
  ⚠️ Room Under Maintenance
  
  This room is currently scheduled for maintenance:
  - Start: November 23, 2025
  - End: November 25, 2025
  - Reason: Air conditioning repair
  
  The requested date (November 24) falls within the maintenance period.
  
  Actions:
  • Reject request and notify student to choose another room
  • Remove maintenance schedule if repair is completed
  • Keep as pending for later review
  ```

---

### **Scenario 5: Capacity Validation**

#### Test 5.1: Participants Exceed Room Capacity
**Steps:**
1. Select Room 401 (Capacity: 30 persons)
2. Fill reservation form with 45 participants
3. Submit request

**Expected Result:**
- ❌ Error: "The number of participants exceeds the room capacity of 30"

---

#### Test 5.2: Participants Within Capacity
**Steps:**
1. Select Room 402 (Capacity: 50 persons)
2. Fill reservation form with 40 participants
3. Submit request

**Expected Result:**
- ✅ Success: Reservation submitted successfully

---

### **Scenario 6: Banned Account Restrictions**

#### Test 6.1: Banned Student Tries to Reserve Room
**Steps:**
1. Department Admin bans Student account (Penalty active until Nov 30, 2025)
2. Login as banned Student
3. Try to submit room reservation

**Expected Result:**
- ❌ Error: "Your account has been banned and you cannot make reservations. Please contact your department administrator."
- 🔒 Reserve buttons appear grayed out and disabled
- ⚠️ Banner alert displays ban status and expiry date

---

#### Test 6.2: Banned Student Request Gets Approved Before Ban
**Steps:**
1. Student submits request: Nov 25, 2:00 PM - 4:00 PM (Status: Pending)
2. Department Admin bans student (effective Nov 15)
3. Admin tries to approve the pending request from Nov 10

**Expected Result:**
- ⚠️ **Warning Alert**:
  ```
  ⚠️ Student Account Banned
  
  This student's account is currently banned:
  - Ban Date: November 15, 2025
  - Expires: November 30, 2025
  - Reason: Violation of room usage policy
  
  Do you want to:
  • Reject this request due to account ban
  • Approve anyway (if ban was issued after this request)
  ```

---

### **Scenario 7: Input Validation**

#### Test 7.1: Invalid Time Range (End Before Start)
**Steps:**
1. Fill reservation form:
   - Start Time: 4:00 PM
   - End Time: 2:00 PM
2. Submit request

**Expected Result:**
- ❌ Error: "End time must be after start time"

---

#### Test 7.2: Past Date Selection
**Steps:**
1. Fill reservation form with date: Nov 5, 2025 (past date)
2. Submit request

**Expected Result:**
- ❌ Error: "Cannot book reservations for past dates"
- 📅 Date picker should disable past dates

---

#### Test 7.3: Short Activity Name
**Steps:**
1. Fill reservation form:
   - Activity Name: "AB"
2. Submit request

**Expected Result:**
- ❌ Error: "Activity name must be at least 3 characters"

---

#### Test 7.4: Short Purpose Description
**Steps:**
1. Fill reservation form:
   - Purpose: "Meeting"
2. Submit request

**Expected Result:**
- ❌ Error: "Purpose must be at least 10 characters"

---

#### Test 7.5: Zero or Negative Participants
**Steps:**
1. Fill reservation form:
   - Participants: 0
2. Submit request

**Expected Result:**
- ❌ Error: "Number of participants must be at least 1"

---

### **Scenario 8: Approval/Rejection Workflow**

#### Test 8.1: Approve Request Successfully
**Steps:**
1. Department Admin views pending requests
2. Reviews Student/Teacher request details
3. Clicks "Approve" button
4. Confirms approval

**Expected Result:**
- ✅ Request status changes to "Approved"
- ✅ Room status updates to "Occupied" for that time slot
- 📧 Email notification sent to requester:
  ```
  Subject: Room Reservation Approved - [Room Name]
  
  Your room reservation has been approved!
  
  Details:
  - Room: Room 101
  - Date: November 15, 2025
  - Time: 2:00 PM - 4:00 PM
  - Activity: Club Meeting
  
  Please arrive on time and follow room usage guidelines.
  ```

---

#### Test 8.2: Reject Request with Reason
**Steps:**
1. Department Admin views pending requests
2. Clicks "Reject" button
3. Enters rejection reason: "Room needed for emergency faculty meeting"
4. Confirms rejection

**Expected Result:**
- ❌ Request status changes to "Rejected"
- ✅ Rejection reason stored in database
- 📧 Email notification sent to requester:
  ```
  Subject: Room Reservation Rejected - [Room Name]
  
  Unfortunately, your room reservation has been rejected.
  
  Details:
  - Room: Room 102
  - Date: November 16, 2025
  - Time: 9:00 AM - 11:00 AM
  
  Reason: Room needed for emergency faculty meeting
  
  Please submit a new request for a different time or room.
  ```

---

### **Scenario 9: Cancellation Workflow**

#### Test 9.1: Student Cancels Pending Request
**Steps:**
1. Student views reservation history
2. Finds pending request
3. Clicks "Cancel Request" button
4. Confirms cancellation

**Expected Result:**
- ✅ Request removed from pending list
- ✅ Cancellation recorded in system
- 💬 Success message: "Your reservation request has been cancelled"

---

#### Test 9.2: Cannot Cancel Approved Request (Past Date)
**Steps:**
1. Student has approved request for Nov 12, 2025 (past date)
2. Student tries to cancel on Nov 14, 2025
3. Clicks "Cancel Request" button

**Expected Result:**
- ⚠️ Warning: "Cannot cancel reservations for past dates. Please contact the department administrator if needed."

---

### **Scenario 10: Edge Cases**

#### Test 10.1: Same User Multiple Bookings Same Room
**Steps:**
1. Student A books Room 501: Nov 28, 9:00 AM - 11:00 AM (Approved)
2. Same Student A tries to book Room 501: Nov 28, 2:00 PM - 4:00 PM (Different time)

**Expected Result:**
- ✅ Success: User can book same room multiple times on different time slots

---

#### Test 10.2: Back-to-Back Bookings Different Users
**Steps:**
1. Student A books Room 502: Nov 29, 9:00 AM - 11:00 AM (Approved)
2. Student B books Room 502: Nov 29, 11:00 AM - 1:00 PM

**Expected Result:**
- ✅ Success: Back-to-back bookings allowed (end time = start time is valid)

---

#### Test 10.3: Midnight Crossing Reservations
**Steps:**
1. Teacher books Room 503: Nov 30, 10:00 PM - 11:59 PM (Approved)
2. Student tries to book Room 503: Dec 1, 12:00 AM - 2:00 AM

**Expected Result:**
- ✅ Success: Different dates, no conflict

---

#### Test 10.4: All-Day Event
**Steps:**
1. Teacher books Room 504: Dec 5, 8:00 AM - 6:00 PM
2. Student tries to book Room 504: Dec 5, 1:00 PM - 3:00 PM

**Expected Result:**
- ❌ Error: "This room is already booked for the selected time"

---

## Summary Checklist

### ✅ **Core Functionality**
- [ ] Students can submit reservation requests
- [ ] Teachers can submit reservation requests
- [ ] Requests appear in admin approval queue
- [ ] Admin can approve/reject requests
- [ ] Email notifications sent on approval/rejection

### ✅ **Conflict Detection**
- [ ] Exact time overlap detected
- [ ] Partial time overlap detected
- [ ] Complete time overlap detected
- [ ] Adjacent bookings allowed
- [ ] Different dates allowed
- [ ] Different rooms allowed

### ✅ **Priority System**
- [ ] Teacher priority over Student enforced
- [ ] Warning shown when approving Student over Teacher
- [ ] Automatic rejection of conflicting requests
- [ ] Clear priority messages in notifications

### ✅ **Maintenance Handling**
- [ ] Cannot book rooms under maintenance
- [ ] Warning when approving requests for maintenance rooms
- [ ] Maintenance dates properly validated

### ✅ **Validation**
- [ ] Room capacity checked
- [ ] Time range validated (end > start)
- [ ] Past dates prevented
- [ ] Input length requirements enforced
- [ ] Banned accounts restricted

### ✅ **User Experience**
- [ ] Clear error messages
- [ ] Helpful validation feedback
- [ ] Priority conflict warnings
- [ ] Email notifications working
- [ ] Reservation history accurate

---

## Test Data Setup

### Sample Users
```sql
-- Teacher Account
Email: juan.delacruz@mc.edu.ph
Password: Teacher123!
Department: Computer Science

-- Student Account 1
Email: maria.santos@mc.edu.ph
Password: Student123!
Department: Computer Science

-- Student Account 2
Email: pedro.reyes@mc.edu.ph
Password: Student123!
Department: Computer Science

-- Department Admin
Email: admin.cs@mc.edu.ph
Password: Admin123!
Department: Computer Science
```

### Sample Rooms
```sql
Room 101 - Computer Lab - Capacity: 40
Room 102 - Lecture Hall - Capacity: 60
Room 103 - Conference Room - Capacity: 20
Room 104 - Multimedia Room - Capacity: 30
```

---

## Bug Tracking Template

When bugs are found during testing:

```
Bug ID: BUG-001
Severity: High/Medium/Low
Title: [Brief description]
Steps to Reproduce:
1. 
2. 
3. 

Expected Result:
[What should happen]

Actual Result:
[What actually happened]

Screenshots: [If applicable]
Environment: Browser, OS, Database version
Date Found: [Date]
Status: Open/In Progress/Fixed/Closed
```

---

## Automated Test Script Ideas

For future implementation:
1. **Unit Tests**: Test individual validation functions
2. **Integration Tests**: Test database queries and conflict detection
3. **E2E Tests**: Simulate full user workflows
4. **Performance Tests**: Test with 100+ concurrent booking requests
5. **Security Tests**: SQL injection, XSS attempts, session hijacking

---

**Document Version:** 1.0  
**Last Updated:** November 11, 2025  
**Prepared By:** SmartSpace Development Team
