# Session Security & Authentication System

## 🔒 Overview

The Career Hub platform now has comprehensive session security with:
- **Authentication required** for all pages except public pages (index, login, signup, admin-login)
- **30-minute inactivity timeout** - automatic logout after 30 minutes of no activity
- **Secure session cookies** with HttpOnly, SameSite protection
- **Session hijacking protection** with proper token validation
- **Automatic redirect** back to requested page after login

---

## ⏱️ Session Timeout

### How It Works
- Every page load updates the last activity timestamp
- If 30 minutes pass without activity, session is destroyed
- User is redirected to login page with timeout message
- Can customize timeout duration in `includes/session.php`

### Configuration
```php
// In includes/session.php
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds

// To change timeout (e.g., 1 hour):
define('SESSION_TIMEOUT', 3600); // 60 minutes
```

### Timeout Behavior
1. User is inactive for 30 minutes
2. On next page request, session is destroyed
3. Redirect to login page: `login.php?timeout=1`
4. Yellow warning message appears: "Your session has expired due to inactivity"
5. After re-login, user is redirected back to the page they were trying to access

---

## 🛡️ Protected Pages

### Pages Requiring Authentication
All pages require login **EXCEPT** these public pages:
- `pages/index.php` - Landing page
- `pages/login.php` - User login
- `pages/signup.php` - User registration
- `pages/admin-login.php` - Admin login

### Protected Pages (require login):
- **Student Pages:**
  - `student.php` - Student dashboard
  - `student-profile.php` - Profile management
  - `my-applications.php` - Application history
  - `apply.php` - Job application form

- **Employer Pages:**
  - `employer.php` - Employer dashboard
  - `employer-profile.php` - Company profile
  - `employer-applicants.php` - View applicants

- **Admin Pages:**
  - `admin.php` - Admin dashboard

- **General Pages:**
  - `jobs.php` - Job listings
  - `opportunities.php` - Career opportunities
  - `contact.php` - Contact page
  - `career tips.php` - Career tips
  - `internship.php` - Internship info
  - `interview.php` - Interview prep
  - `Success_stories.php` - Success stories
  - `settings.php` - User settings

---

## 🔐 Authentication Flow

### Login Process
1. User submits email, password, and role
2. System validates credentials against database
3. Creates session with user data
4. Stores requested page (if any) for post-login redirect
5. Redirects to appropriate dashboard or requested page

### Auto-Redirect After Login
If user tries to access a protected page while logged out:
```
User -> jobs.php (protected)
  ↓
Redirected to login.php
  ↓
Session stores: redirect_after_login = "/career_hub/pages/jobs.php"
  ↓
User logs in
  ↓
Redirected back to jobs.php (original page)
```

### Role-Based Access
- **Students:** Access student dashboard, jobs, applications
- **Employers:** Access employer dashboard, post jobs, view applicants
- **Admins:** Access admin dashboard, manage all users/jobs

---

## 🔧 Implementation Details

### Session Configuration (`includes/session.php`)
```php
// Secure cookie parameters
session_set_cookie_params([
    'lifetime' => 0,          // Session cookie (expires on browser close)
    'path' => '/',            // Available throughout site
    'domain' => '',           // Current domain
    'secure' => true/false,   // True if HTTPS
    'httponly' => true,       // Prevent JavaScript access
    'samesite' => 'Lax'       // CSRF protection
]);
```

### Authentication Check (`includes/auth_check.php`)
```php
// Includes session.php (with timeout handling)
require_once __DIR__ . '/session.php';

// Check if user logged in
if (!isset($_SESSION['user'])) {
    // Store requested page
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to login
    header("Location: /career_hub/pages/login.php");
    exit;
}
```

### Activity Tracking
Every page that includes `session.php` automatically:
1. Checks last activity time
2. Calculates inactivity duration
3. Destroys session if > 30 minutes
4. Updates last activity timestamp

---

## 📱 User Experience

### First-Time Visit
```
1. User visits career_hub/pages/jobs.php
2. Not logged in → redirected to login.php
3. User sees login form
4. After login → automatically goes to jobs.php
```

### Active Session
```
1. User logged in
2. Browsing pages normally
3. Each click/page load resets the 30-minute timer
4. No interruption as long as active
```

### Session Expiry
```
1. User logged in
2. Opens jobs.php
3. Gets coffee, leaves browser open for 35 minutes
4. Clicks a link or refreshes page
5. Yellow warning: "Your session has expired..."
6. Must login again
7. After login → back to the page they tried to access
```

---

## 🛠️ Testing

### Test Authentication
1. **Logout** (if logged in)
2. Try accessing: `http://localhost/career_hub/pages/student.php`
3. Should redirect to login page
4. After login, should return to student.php

### Test Timeout
1. **Login** as any user
2. Note the current time
3. **Wait 31 minutes** (or change timeout to 60 seconds for testing)
4. Try to navigate to any page
5. Should see timeout message and be logged out

### Quick Timeout Test (for development)
In `includes/session.php`, temporarily change:
```php
// Original
define('SESSION_TIMEOUT', 1800); // 30 minutes

// For testing
define('SESSION_TIMEOUT', 60); // 1 minute
```

Wait 61 seconds, then refresh any page.

---

## 🔍 Troubleshooting

### Users Keep Getting Logged Out
- Check `SESSION_TIMEOUT` value in `includes/session.php`
- Increase timeout if legitimate use case requires longer sessions
- Verify server time is correct

### Session Not Timing Out
- Clear browser cookies
- Check if `$_SESSION['LAST_ACTIVITY']` is being updated
- Verify `session.php` is included on all protected pages

### Redirect Loop
- Ensure `index.php`, `login.php`, `signup.php` do NOT include `auth_check.php`
- Check that public pages array in `session.php` matches actual public pages

### "Session Expired" on Every Login
- Clear browser cache and cookies
- Check PHP session settings (`php.ini`)
- Verify session directory is writable

---

## 🚀 Production Recommendations

### Security Enhancements
1. **Enable HTTPS** - Set `'secure' => true` in session config
2. **Regenerate Session ID** - After login to prevent fixation attacks
3. **Use Secure Database** - Store sessions in DB instead of files
4. **Add CSRF Tokens** - Already implemented in forms
5. **Rate Limiting** - Prevent brute force login attempts
6. **Password Policies** - Enforce strong passwords

### Performance
1. **Session Cleanup** - Old sessions cleaned automatically by PHP
2. **Cache Headers** - Prevent caching of authenticated pages
3. **CDN** - For static assets only

### Monitoring
1. **Login Logs** - Track successful/failed logins
2. **Session Metrics** - Monitor active sessions
3. **Timeout Alerts** - Track frequent timeout issues

---

## 📋 Quick Reference

### Session Variables
```php
$_SESSION['user'] = [
    'id' => 123,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'role' => 'student', // or 'employer', 'admin'
    'profile_image' => '/uploads/profile/john.jpg'
];

$_SESSION['LAST_ACTIVITY'] = time(); // Unix timestamp
$_SESSION['redirect_after_login'] = '/career_hub/pages/jobs.php';
```

### Key Files
- `includes/session.php` - Session initialization & timeout
- `includes/auth_check.php` - Authentication verification
- `pages/login.php` - Login form with timeout handling
- `pages/logout.php` - Session destruction

### Common Tasks

**Change Timeout Duration:**
```php
// includes/session.php
define('SESSION_TIMEOUT', 3600); // 1 hour
```

**Add New Protected Page:**
```php
// At top of your-new-page.php
<?php
require_once __DIR__ . '/../includes/auth_check.php';
// Rest of your code...
```

**Add New Public Page:**
```php
// Just use session.php, NOT auth_check.php
<?php
require_once __DIR__ . '/../includes/session.php';
// Rest of your code...
```

**Manual Logout:**
```php
session_start();
session_unset();
session_destroy();
header('Location: login.php');
exit;
```

---

## ✅ Implementation Status

- ✅ Session timeout (30 minutes)
- ✅ Secure session cookies
- ✅ Authentication required on all protected pages
- ✅ Timeout warning message
- ✅ Auto-redirect after login
- ✅ Role-based access control
- ✅ Public pages accessible without login

---

Made with 🔒 for Career Hub Security
