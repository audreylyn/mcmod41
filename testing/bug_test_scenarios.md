# 🧪 Bug Test Scenarios - Proof of Concept

## 📋 **Test Environment Setup**
- Ensure you have a clean database with test data
- Use different browsers/incognito mode for role testing
- Have database access to verify data changes
- Test with multiple user accounts per role

---

## 🔴 **CRITICAL SEVERITY TESTS**

### **Test C1: Registrar Plain Text Password Storage**
**Bug:** Registrar passwords stored in plain text (FIXED but reverting shows the issue)

**Steps:**
1. Check database: `SELECT Reg_Password FROM registrar WHERE regid = 1;`
2. If you see plain text like '1234', the bug exists
3. Try logging in with that exact password
4. **Expected:** Login works (proving plain text storage)
5. **Should Be:** Hashed password that requires password_verify()

**Proof:** Database shows readable password instead of hash starting with `$2y$`

---

### **Test H3: RETRACTED - Cross-Department Penalty Access**
**Status:** ❌ **FALSE POSITIVE** - Code correctly filters by department
**Reality:** `WHERE s.Department = ?` properly restricts access to same department students
**Your Testing:** Confirmed - only shows students in admin's department

### **Test H4: RETRACTED - Ban Status Race Condition**  
**Status:** ❌ **FALSE POSITIVE** - Ban check works correctly
**Reality:** Ban status checked early in reservation process and blocks properly
**Your Testing:** Confirmed - banned students are restricted from reservations

### **Test H5: Timezone Inconsistency**
**Bug:** Database connection uses UTC, other files use Asia/Manila

**Steps:**
1. Set a room to maintenance with end time "tomorrow 10:00 AM"
2. Check database: `SELECT end_date FROM room_maintenance ORDER BY id DESC LIMIT 1;`
3. Compare with what's displayed in UI
4. **Expected:** 8-hour difference between stored and displayed times

**Proof:** Time discrepancy between database and UI display

---

## 🔴 **MEDIUM SEVERITY TESTS**

### **Test M1: Registrar Email Validation Bypass (AJAX)**
**Bug:** AJAX admin creation doesn't check all user tables

**Steps:**
1. Login as Registrar
2. Create a teacher with email "test@example.com"
3. Use AJAX interface to create department admin with same email
4. **Expected:** Admin creation succeeds (BUG - should fail)

**Proof:** Two users with same email in different tables

### **Test M2: Teacher Cross-Department Room Booking**
**Bug:** Teachers can book rooms outside their department

**Steps:**
1. Login as Teacher from "Business Administration" department
2. Try to book a room from "Accountancy" building
3. **Expected:** Booking succeeds (BUG - should be restricted)

**Proof:** Teacher has reservation for room outside their department

### **Test M3: Equipment Report Duplicate Logic Flaw**
**Bug:** Complex condition allows duplicate reports

**Steps:**
1. Login as Student
2. Report an equipment issue for a specific unit
3. Before admin responds, report the same equipment again
4. Use different browsers or clear session between attempts
5. **Expected:** Second report succeeds (BUG)

**Proof:** Multiple open reports for same equipment unit

### **Test M4: Expired Penalty Still Blocking**
**Bug:** Expired penalties still block equipment reporting

**Steps:**
1. Create student penalty with past expiry date in database:
   ```sql
   UPDATE student SET PenaltyStatus = 'banned', PenaltyExpiresAt = '2025-01-01' WHERE StudentID = X;
   ```
2. Login as that student
3. Try to report equipment issue
4. **Expected:** Blocked despite expired penalty (BUG)

**Proof:** Student can't report despite penalty being expired

### **Test M5: Session Regeneration Issue**
**Bug:** Session data not properly updated during regeneration

**Steps:**
1. Login and note session data
2. Wait for session regeneration interval (or trigger manually)
3. Check if all session variables are preserved
4. **Expected:** Some session data lost (BUG)

**Proof:** User gets logged out or loses role information

### **Test M6: Database Connection Silent Failures**
**Bug:** Connection reuse with suppressed errors

**Steps:**
1. Simulate database connection drop during session
2. Try to perform database operations
3. **Expected:** Operations fail silently without proper error handling

**Proof:** No error messages despite database being unavailable

---

## 🔴 **ROLE-SPECIFIC TEST SCENARIOS**

### **Registrar Role Tests**
```
Test R1: Hardcoded Department Fallback
1. Login as Registrar
2. Check dashboard statistics
3. Look for "Business Administration" data even if registrar has no department
Expected: Incorrect department data displayed
```

### **Department Admin Role Tests**
```
Test DA1: Penalty Race Condition
1. Two admins try to ban same student simultaneously
2. Expected: Duplicate penalty records created

Test DA2: Missing Department Validation
1. Admin tries to manage users from other departments
2. Expected: Cross-department access allowed (BUG)
```

### **Teacher Role Tests**
```
Test T1: Inconsistent User ID Field
1. Login as Teacher
2. Check reservation history
3. Expected: Wrong data if system assumes TeacherID for all non-students
```

### **Student Role Tests**
```
Test S1: SessionStorage Dependency
1. Make equipment report
2. Clear browser sessionStorage during process
3. Expected: Data loss and broken functionality

Test S2: Equipment API Vulnerability
1. Intercept equipment details API call
2. Modify unit_id parameter
3. Expected: Access to unauthorized equipment data
```

---

## 📊 **Test Results Template**

Create a file `test_results.md` and document findings:

```markdown
# Test Results

## Critical Tests
- [ ] C1: Registrar Plain Text - RESULT: ___________
- [ ] H1: Student Update Params - RESULT: ___________
- [ ] H2: Teacher Update Params - RESULT: ___________

## High Priority Tests  
- [ ] H3: Cross-Dept Penalty - RESULT: ___________
- [ ] H4: Ban Race Condition - RESULT: ___________
- [ ] H5: Timezone Issues - RESULT: ___________

## Medium Priority Tests
- [ ] M1: Email Validation - RESULT: ___________
- [ ] M2: Cross-Dept Booking - RESULT: ___________
- [ ] M3: Duplicate Reports - RESULT: ___________
- [ ] M4: Expired Penalties - RESULT: ___________
- [ ] M5: Session Regeneration - RESULT: ___________
- [ ] M6: Connection Failures - RESULT: ___________
```

---

## 🎯 **Quick Verification Commands**

**Check for plain text passwords:**
```sql
SELECT regid, Reg_Email, Reg_Password FROM registrar;
```

**Check for duplicate emails:**
```sql
SELECT email, COUNT(*) FROM (
    SELECT Email as email FROM teacher
    UNION ALL SELECT Email as email FROM student  
    UNION ALL SELECT Email as email FROM dept_admin
    UNION ALL SELECT Reg_Email as email FROM registrar
) combined GROUP BY email HAVING COUNT(*) > 1;
```

**Check timezone inconsistencies:**
```sql
SELECT NOW() as db_time, 
       CONVERT_TZ(NOW(), 'UTC', 'Asia/Manila') as manila_time;
```

**Check cross-department penalties:**
```sql
SELECT s.Department as student_dept, 
       da.Department as admin_dept,
       p.reason
FROM penalty p
JOIN student s ON p.student_id = s.StudentID  
JOIN dept_admin da ON p.issued_by = da.AdminID
WHERE s.Department != da.Department;
```

Run these tests systematically and document the results. Each failed test proves the existence of the bug I identified in my analysis.
