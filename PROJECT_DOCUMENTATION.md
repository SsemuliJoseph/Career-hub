# Career Connect Hub - Student Learning Documentation

## 🎓 Project Overview
This project is a comprehensive job portal web application built with PHP (backend) and vanilla JavaScript (frontend). Every file has been annotated with **line-by-line teaching comments** to help students understand how web applications work.

---

## 📚 Learning Resources by Technology

### PHP Concepts Covered
- **Session Management**: `session_start()`, `$_SESSION`, session security
- **Database Operations**: mysqli prepared statements, `bind_param()`, `get_result()`
- **Security**: Password hashing (`password_hash`, `password_verify`), XSS prevention (`htmlspecialchars`)
- **File Uploads**: `move_uploaded_file()`, validation, storage
- **Authentication**: Login/logout flows, role-based access control
- **HTTP**: `header()` redirects, `$_SERVER`, `$_POST`, `$_GET` superglobals
- **Database Transactions**: `begin_transaction()`, `commit()`, `rollback()`

### JavaScript Concepts Covered
- **DOM Manipulation**: `querySelector()`, `getElementById()`, `addEventListener()`
- **Async Programming**: Promises, `async`/`await`, `fetch()` API
- **ES6+ Features**: Arrow functions, template literals, destructuring, modules
- **Local Storage**: Persistent client-side data storage
- **Form Handling**: `FormData`, `preventDefault()`, validation
- **WebSockets**: Real-time bidirectional communication
- **PWA**: Service Workers, offline support, app installation

---

## 🗂️ File Structure with Learning Notes

### `/pages/` - Server-Side Rendered Pages
Each page demonstrates specific patterns:

**Authentication Pages:**
- `login.php` - Form handling, password verification, session creation
- `signup.php` - User registration, password hashing, duplicate checking
- `logout.php` - Session destruction, secure logout

**User Dashboards:**
- `student.php` - Role-based dashboard, prepared statements, profile completion calculation
- `employer.php` - Employer dashboard, job posting management
- `admin.php` - Admin interface, role-based access, real-time WebSocket integration

**Profile Management:**
- `student-profile.php` - Profile editing, file uploads (CV, profile pic)
- `employer-profile.php` - Company profile, logo upload, transaction usage
- `settings.php` - Theme management, PWA installation, notification preferences

**Job Features:**
- `jobs.php` - Job listings, search with prepared statements, pagination
- `apply.php` - Application submission, file uploads, FormData
- `my-applications.php` - Application tracking with JOINs
- `opportunities.php` - Career opportunities listing
- `internship.php` - Internship-specific listings

**Content Pages:**
- `Success_stories.php` - Static content page example
- `career tips.php` - Educational content
- `interview.php` - Interview prep guide with embedded video
- `contact.php` - Contact form handling

### `/js/` - Client-Side JavaScript
**Core Modules:**
- `api.js` - Centralized fetch wrapper, error handling, token management
- `app.js` - PWA functionality, Service Worker registration, install prompts
- `theme.js` - Theme toggling, localStorage persistence, system theme detection

**Feature Modules:**
- `auth.js` - Login/signup AJAX handlers
- `jobs.js` - Job listing dynamic loading
- `apply.js` - Application submission with FormData
- `dashboard.js` - Dashboard data fetching
- `profile.js` - Profile editing
- `post-job.js` - Job posting form
- `student-profile.js` - Student profile management
- `navbar.js` - Dynamic navbar, user info, theme toggle

**Real-Time:**
- `websocket-client.js` - WebSocket client class, event system, notifications

### `/classes/` - PHP Object-Oriented Classes
- `Database.php` - Singleton database connection wrapper
- `Model.php` - Base model class with CRUD helpers
- `User.php` - User management class
- `Job.php` - Job model
- `Application.php` - Application model
- `WebSocketServer.php` - WebSocket server implementation

### `/includes/` - Shared PHP Components
- `session.php` - Session initialization with security settings
- `db.php` - Database connection setup
- `auth_check.php` - Authentication guard for protected pages
- `helpers.php` - Utility functions
- `navbar.php` - Reusable navigation bar
- `footer.php` - Reusable footer

---

## 🔒 Security Patterns Demonstrated

1. **SQL Injection Prevention**
   ```php
   // ALWAYS use prepared statements for user input
   $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
   $stmt->bind_param("s", $email);
   ```

2. **XSS Prevention**
   ```php
   // ALWAYS escape output
   echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
   ```

3. **Password Security**
   ```php
   // Hash passwords with bcrypt
   $hash = password_hash($password, PASSWORD_BCRYPT);
   // Verify with constant-time comparison
   password_verify($input, $hash);
   ```

4. **File Upload Validation**
   ```php
   // Validate file type, size, and use safe file names
   $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
   $maxSize = 5 * 1024 * 1024; // 5MB
   ```

5. **Session Security**
   ```php
   session_start([
       'cookie_httponly' => true,  // Prevent JavaScript access
       'cookie_secure' => true,    // HTTPS only
       'cookie_samesite' => 'Lax'  // CSRF protection
   ]);
   ```

---

## 🌐 API Patterns

### RESTful Endpoints
- **GET** `/api/jobs` - Fetch job listings
- **POST** `/api/applications` - Submit application
- **GET** `/api/student/profile` - Get profile data
- **POST** `/api/user/save_theme.php` - Save user preference

### Response Format
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

---

## 📡 WebSocket Real-Time Features

### Client-Side Usage
```javascript
// Connect to WebSocket server
const ws = new CareerHubWebSocket('ws://localhost:8080');
ws.connect();

// Subscribe to channels
ws.subscribe('jobs');
ws.subscribe('notifications');

// Listen for events
ws.on('jobNotification', (data) => {
  showNotification('New job posted!', data);
});
```

### Server-Side Implementation
- PHP WebSocket server using Ratchet library
- Channel-based pub/sub system
- User registration for targeted messaging
- Automatic reconnection with exponential backoff

---

## 🎨 Frontend Patterns

### Theme Management
```javascript
// Detect system preference
const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

// Save to localStorage and server
localStorage.setItem('theme', 'dark');
await fetch('/api/user/save_theme.php', {
  method: 'POST',
  body: JSON.stringify({ theme: 'dark' })
});
```

### Form Handling
```javascript
// Prevent default and use fetch
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(form);
  const res = await fetch('/api/endpoint', {
    method: 'POST',
    body: formData
  });
});
```

---

## 🚀 Progressive Web App (PWA)

### Features Implemented
1. **Service Worker** - Caches assets for offline access
2. **Install Prompt** - Add to home screen functionality
3. **Offline Support** - Queue submissions when offline
4. **Background Sync** - Sync data when connection restored
5. **App Manifest** - Define app name, icons, theme color

### Installation Flow
```javascript
// Detect install prompt
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  showInstallButton();
});

// Trigger installation
await deferredPrompt.prompt();
const { outcome } = await deferredPrompt.userChoice;
```

---

## 📊 Database Schema (Key Tables)

### Users
- `id`, `email`, `password`, `role`, `createdAt`, `updatedAt`

### Jobs
- `id`, `employer_id`, `title`, `description`, `location`, `type`, `status`

### Applications
- `id`, `studentId`, `jobId`, `coverLetter`, `cv_file`, `status`, `createdAt`

### Student_Profiles
- `id`, `fullName`, `phone`, `education`, `skills`, `profilePic`, `cvFile`

### Employers
- `id`, `company_name`, `logo`, `industry`, `website`

---

## 🎯 Learning Path Recommendations

### Beginner (Start Here)
1. Read `pages/login.php` - Basic form handling
2. Read `pages/signup.php` - User registration
3. Read `js/scripts.js` - Simple DOM manipulation
4. Read `includes/db.php` - Database connection

### Intermediate
1. Read `pages/student-profile.php` - File uploads
2. Read `classes/Model.php` - OOP patterns
3. Read `js/api.js` - Fetch API wrapper
4. Read `js/theme.js` - Client-side state management

### Advanced
1. Read `websocket-client.js` - Real-time communication
2. Read `pages/admin.php` - Complex dashboard with WebSocket
3. Read `js/app.js` - PWA implementation
4. Read `classes/WebSocketServer.php` - Server-side WebSocket

---

## 🛠️ Development Tools

### Prerequisites
- **PHP 7.4+** - Server-side language
- **MySQL/MariaDB** - Database
- **Web Server** - Apache (WAMP/XAMPP) or Nginx
- **Modern Browser** - Chrome, Firefox, Edge (for PWA features)

### Recommended Extensions
- **VS Code Extensions**: PHP Intelephense, ESLint, Prettier
- **Browser Extensions**: JSON Formatter, Redux DevTools

---

## 🔧 Common Tasks

### Start WebSocket Server
```bash
cd c:\wamp64\www\career_hub
php websocket_server.php
```

### Import Database
```bash
mysql -u root -p career_hub < uniconnect_db.sql
```

### Install Dependencies
```bash
composer install
```

---

## 📝 Code Quality Checklist

- [ ] All user inputs validated server-side
- [ ] Prepared statements used for all SQL queries
- [ ] Output escaped with `htmlspecialchars()`
- [ ] Passwords hashed with `password_hash()`
- [ ] Files validated before upload
- [ ] Session security headers set
- [ ] Error handling with try/catch
- [ ] CSRF tokens where needed
- [ ] HTTPS enforced in production
- [ ] SQL injection tests passed

---

## 🎓 Additional Resources

### Official Documentation
- [PHP Manual](https://www.php.net/manual/en/)
- [MDN Web Docs (JavaScript)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
- [MySQLi Documentation](https://www.php.net/manual/en/book.mysqli.php)

### Video Tutorials
- Embedded in `pages/interview.php` - Video interview tips

### Best Practices
- [OWASP Top 10](https://owasp.org/www-project-top-ten/) - Security vulnerabilities
- [PHP The Right Way](https://phptherightway.com/) - Modern PHP practices
- [JavaScript.info](https://javascript.info/) - Modern JavaScript tutorial

---

## 📞 Support

For questions or clarifications about the code annotations:
1. Review the inline comments in each file
2. Check this documentation for conceptual overview
3. Refer to official language/framework documentation
4. Practice by modifying and extending the codebase

---

**Happy Learning! 🚀**

*Every line of code in this project has been annotated to help you understand web development fundamentals. Take your time, experiment, and learn by doing!*
