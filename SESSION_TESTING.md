# Session Security Testing Guide

## 🧪 Quick Tests

### Test 1: Authentication Required (2 minutes)

**Steps:**
1. Make sure you're logged out
2. Visit: `http://localhost/career_hub/pages/student.php`
3. **Expected:** Redirected to login page
4. **Expected:** URL shows `login.php` (not `student.php`)

**Test Other Protected Pages:**
- `http://localhost/career_hub/pages/jobs.php`
- `http://localhost/career_hub/pages/employer.php`
- `http://localhost/career_hub/pages/opportunities.php`

All should redirect to login!

---

### Test 2: Public Pages Accessible (1 minute)

**Steps:**
1. Make sure you're logged out
2. Visit: `http://localhost/career_hub/pages/index.php`
3. **Expected:** Page loads normally (no redirect)

**Test Other Public Pages:**
- `http://localhost/career_hub/pages/login.php` ✅ Should load
- `http://localhost/career_hub/pages/signup.php` ✅ Should load
- `http://localhost/career_hub/pages/admin-login.php` ✅ Should load

---

### Test 3: Login and Auto-Redirect (2 minutes)

**Steps:**
1. Logout (if logged in)
2. Try to visit: `http://localhost/career_hub/pages/jobs.php`
3. **Expected:** Redirected to login page
4. Login with your credentials
5. **Expected:** After login, automatically redirected to `jobs.php`

**Why it works:**
- System remembers which page you tried to access
- After login, sends you there automatically

---

### Test 4: Session Timeout - Quick Test (2 minutes)

**For Quick Testing, temporarily reduce timeout:**

1. Open `includes/session.php`
2. Find line: `define('SESSION_TIMEOUT', 1800);`
3. Change to: `define('SESSION_TIMEOUT', 60);` (1 minute)
4. Save file

**Test Steps:**
1. Login as any user
2. Navigate to any page (e.g., student dashboard)
3. **Wait 61 seconds** (do nothing, don't click anything)
4. Click any link or refresh the page
5. **Expected:** Yellow warning: "Your session has expired..."
6. **Expected:** Redirected to login page

**Don't forget to change it back to 1800 after testing!**

---

### Test 5: Session Timeout - Full Test (35 minutes)

**Steps:**
1. Make sure `SESSION_TIMEOUT` is set to `1800` (30 minutes)
2. Login as any user
3. Open student/employer dashboard
4. **Wait exactly 30 minutes** without clicking anything
5. After 30 minutes, try to click a link or refresh
6. **Expected:** Timeout message and redirect to login

**Activity Resets Timer:**
- Click anything: timer resets
- Scroll page: timer resets  
- Type in a form: timer resets
- Refresh page: timer resets

---

### Test 6: Session Indicator (Optional - 5 minutes)

**Add Session Timer to a Page:**

1. Open `pages/student.php` (or any page)
2. Before `</body>` tag, add:
```php
<?php include_once __DIR__ . '/../includes/session_indicator.php'; ?>
```
3. Save and refresh the page
4. **Expected:** Bottom-left corner shows countdown timer
5. **Expected:** Timer turns red when < 5 minutes remaining
6. **Expected:** Timer counts down in real-time

**Timer Features:**
- Shows remaining session time
- Updates every second
- Turns red/pulsing when < 5 minutes left
- Automatically redirects when expired

---

## 🔍 Detailed Testing Scenarios

### Scenario 1: Normal User Flow

```
1. User visits index.php (public) ✅ Works
2. Clicks "Jobs" link → redirected to login
3. Logs in with email/password
4. Automatically sent to Jobs page
5. Browses for 10 minutes (active)
6. Session stays alive ✅
7. Logs out manually
8. Session destroyed ✅
```

### Scenario 2: Inactive User

```
1. User logs in
2. Opens dashboard
3. Goes to lunch (35 minutes)
4. Comes back, clicks anything
5. Yellow timeout warning appears
6. Redirected to login
7. Logs in again
8. Returns to the page they tried to access
```

### Scenario 3: Different User Roles

**Student:**
```
Login → student.php dashboard
Try employer.php → Access denied (role check)
```

**Employer:**
```
Login → employer.php dashboard
Try admin.php → Redirected to login
```

**Admin:**
```
Login via admin-login.php
Access admin.php ✅
Special admin secret key required
```

---

## 📊 Expected Behaviors

### ✅ Correct Behaviors

| Action | Expected Result |
|--------|----------------|
| Visit protected page when logged out | Redirect to login |
| Login successfully | Go to dashboard or requested page |
| Inactive for 30 minutes | Session expires, show timeout message |
| Click/scroll/type while logged in | Session timer resets |
| Logout manually | Session destroyed, redirect to login |
| Visit public page (index, login, signup) | Page loads normally |
| Try accessing admin page as student | Access denied |

### ❌ Incorrect Behaviors (Report if you see these)

| Problem | What It Means |
|---------|--------------|
| Can access student.php without login | Auth check not working |
| Session never expires | Timeout not configured |
| Timeout happens too quickly | SESSION_TIMEOUT too low |
| Login doesn't redirect properly | Redirect logic broken |
| Can't access any pages | All pages have auth_check (even public) |

---

## 🛠️ Debugging Commands

### Check Session Files (Windows)
```powershell
# Find PHP session directory
php -i | Select-String "session.save_path"

# List active sessions
ls "C:\wamp64\tmp" | Where-Object {$_.Name -like "sess_*"}
```

### Check PHP Settings
```powershell
# View session settings
php -i | Select-String "session"
```

### Manual Session Check
Create `test_session.php`:
```php
<?php
session_start();
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Last Activity: " . ($_SESSION['LAST_ACTIVITY'] ?? 'Not set') . "\n";
echo "Current Time: " . time() . "\n";
if (isset($_SESSION['LAST_ACTIVITY'])) {
    $elapsed = time() - $_SESSION['LAST_ACTIVITY'];
    echo "Elapsed: " . $elapsed . " seconds\n";
    echo "Remaining: " . (1800 - $elapsed) . " seconds\n";
}
echo "\nSession Data:\n";
print_r($_SESSION);
echo "</pre>";
```

Visit: `http://localhost/career_hub/test_session.php`

---

## 📝 Test Checklist

Use this checklist to verify everything works:

- [ ] Public pages accessible without login (index, login, signup)
- [ ] Protected pages require login (student, employer, jobs, etc.)
- [ ] Login redirects to originally requested page
- [ ] Session expires after 30 minutes of inactivity
- [ ] Timeout message appears when session expires
- [ ] Activity (clicks, scrolls) resets timeout timer
- [ ] Manual logout destroys session
- [ ] Role-based access works (students can't access employer pages)
- [ ] Admin requires special login and secret key
- [ ] Session indicator shows countdown (if added)

---

## 🚨 Common Issues & Fixes

### Issue: "Session expired" appears immediately after login

**Fix:**
```php
// In includes/session.php, make sure this line exists:
$_SESSION['LAST_ACTIVITY'] = time();
```

### Issue: Can access protected pages without login

**Fix:**
```php
// Make sure protected pages have this at the top:
<?php
require_once __DIR__ . '/../includes/auth_check.php';
```

### Issue: Infinite redirect loop

**Fix:**
```php
// Check that login.php uses session.php, NOT auth_check.php
// includes/session.php should have public pages check
```

### Issue: Session timeout doesn't work

**Fix:**
```php
// Verify in includes/session.php:
define('SESSION_TIMEOUT', 1800);

// Check that timeout logic exists after this line
```

---

## 💡 Advanced Testing

### Test with Multiple Browsers
1. Chrome: Login as student
2. Firefox: Login as employer  
3. Edge: Login as admin
4. Each should maintain separate sessions

### Test Concurrent Sessions
1. Login on Computer A
2. Login same user on Computer B
3. Both should work (unless you implement single-session per user)

### Test Mobile
1. Access on mobile device
2. Session should work identically
3. Timeout should work on mobile too

---

## 📞 Support

If tests fail, check:
1. `includes/session.php` - Timeout configuration
2. `includes/auth_check.php` - Authentication logic
3. `pages/login.php` - Login and redirect handling
4. PHP error logs (usually in `C:\wamp64\logs\php_error.log`)

**Files Modified:**
- ✅ `includes/session.php` - Added timeout logic
- ✅ `includes/auth_check.php` - Added redirect handling
- ✅ `pages/login.php` - Added timeout message
- ✅ All protected pages - Added `auth_check.php` include
- ✅ `api/session/ping.php` - Keep-alive endpoint
- ✅ `includes/session_indicator.php` - Visual timer (optional)

---

Happy Testing! 🎉
