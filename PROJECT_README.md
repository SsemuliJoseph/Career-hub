# Career Hub - Complete Project Guide
**Full-Stack Job Application Platform for Students and Employers**

---

## Table of Contents
1. [Project Overview](#project-overview)
2. [Technology Stack](#technology-stack)
3. [Architecture & Design Patterns](#architecture--design-patterns)
4. [Project Structure](#project-structure)
5. [Database Schema](#database-schema)
6. [Core Features](#core-features)
7. [Authentication & Security](#authentication--security)
8. [API Architecture](#api-architecture)
9. [Real-Time Features (WebSockets)](#real-time-features-websockets)
10. [Frontend JavaScript Modules](#frontend-javascript-modules)
11. [Styling & Responsive Design](#styling--responsive-design)
12. [Development Workflow](#development-workflow)
13. [Deployment Guide](#deployment-guide)
14. [Common Tasks & Examples](#common-tasks--examples)
15. [Troubleshooting](#troubleshooting)

---

## Project Overview

**Career Hub** is a comprehensive job application platform connecting students with employers. It enables:
- **Students**: Browse jobs, apply online/externally, track applications, build profiles
- **Employers**: Post jobs, review applicants, manage hiring pipeline
- **Admins**: Manage users, jobs, import external job listings

### Key Highlights
- 🔐 **Secure authentication** with session management and CSRF protection
- 📱 **Progressive Web App (PWA)** with offline support
- 🔄 **Real-time notifications** via WebSockets
- 🌐 **External job integration** from JSearch and Adzuna APIs
- 📊 **Dashboard analytics** for tracking applications and job stats
- 🎨 **Responsive design** with dark/light theme support

---

## Technology Stack

### Backend
- **PHP 8.x** - Server-side logic and API endpoints
- **MySQL/MariaDB** - Relational database (via mysqli)
- **Sessions** - Server-side authentication (no JWT in current version)
- **WebSocket Server** - Real-time notifications (PHP socket programming)

### Frontend
- **Vanilla JavaScript (ES6 modules)** - No frameworks (React/Vue/Angular)
- **HTML5** - Semantic markup
- **CSS3** - Custom properties, Flexbox, Grid, responsive design
- **Service Worker** - PWA functionality, offline caching

### Tools & Libraries
- **Composer** - PHP dependency management (if used)
- **npm** - Frontend tooling (optional)
- **Environment Variables** - Config management (.env files)

### External APIs
- **JSearch (RapidAPI)** - Job listings
- **Adzuna API** - Additional job data

---

## Architecture & Design Patterns

### MVC-Like Structure
The project follows a **Model-View-Controller-inspired** architecture:

```
┌─────────────────────────────────────────────────┐
│                   CLIENT SIDE                    │
│  (HTML Pages + JavaScript Modules + CSS)        │
└─────────────────┬───────────────────────────────┘
                  │ HTTP/AJAX Requests
                  ▼
┌─────────────────────────────────────────────────┐
│              ROUTING LAYER (Apache)              │
│  .htaccess rules → pages/*.php or api/*.php     │
└─────────────────┬───────────────────────────────┘
                  │
      ┌───────────┴──────────┐
      ▼                      ▼
┌──────────────┐      ┌──────────────┐
│   PAGES      │      │     API      │
│  (Views)     │      │ (Controllers)│
│ pages/*.php  │      │  api/*.php   │
└──────┬───────┘      └──────┬───────┘
       │                     │
       │ uses                │ uses
       ▼                     ▼
┌─────────────────────────────────────┐
│          BUSINESS LOGIC              │
│  classes/ (Models: User, Job, etc.) │
│  includes/ (Helpers, CSRF, Upload)  │
└──────────────┬──────────────────────┘
               │ queries
               ▼
┌─────────────────────────────────────┐
│          DATABASE (MySQL)            │
│  Tables: users, jobs, applications  │
└─────────────────────────────────────┘
```

### Key Design Patterns

1. **Separation of Concerns**
   - `pages/` = Views (HTML + embedded PHP for UI)
   - `api/` = RESTful JSON endpoints (no HTML output)
   - `classes/` = Business logic and data models
   - `includes/` = Shared utilities (auth, CSRF, DB, helpers)

2. **Session-Based Authentication**
   - Server maintains `$_SESSION['user']` with user data
   - CSRF tokens protect state-changing requests
   - No JWT/token auth (purely server-side sessions)

3. **Prepared Statements**
   - All database queries use `mysqli::prepare()` with parameter binding
   - Prevents SQL injection attacks

4. **Progressive Enhancement**
   - Core functionality works without JavaScript
   - JavaScript adds real-time updates, offline support, smooth UX

---

## Project Structure

```
career_hub/
│
├── index.php                  # Landing page / home
├── composer.json              # PHP dependencies (if any)
├── .env                       # Environment config (API keys, DB credentials) - NOT in git
├── .htaccess                  # Apache rewrite rules
│
├── pages/                     # HTML Views (user-facing pages)
│   ├── login.php             # Student/Employer login
│   ├── register.php          # New user registration
│   ├── student.php           # Student dashboard
│   ├── employer.php          # Employer dashboard
│   ├── admin.php             # Admin panel
│   ├── jobs.php              # Job listings
│   ├── apply.php             # Application form
│   ├── applications.php      # Student: view my applications
│   ├── employer-applicants.php  # Employer: view applicants
│   ├── post-job.php          # Employer: create job posting
│   ├── import-jobs.php       # Admin: import external jobs
│   └── ...                   # (22 total page files)
│
├── api/                       # RESTful JSON Endpoints
│   ├── auth/                 # Authentication endpoints
│   │   ├── login.php
│   │   ├── register.php
│   │   └── logout.php
│   ├── session/              # Session management
│   │   └── me.php           # Get current user
│   ├── user/                 # User operations
│   │   └── save_theme.php
│   ├── v1/                   # Versioned API (future)
│   │
│   ├── jobs.php              # GET/POST jobs
│   ├── get_jobs.php          # Simple job list
│   ├── get_all_jobs.php      # Jobs with employer info
│   ├── search_jobs.php       # Search with LIKE queries
│   ├── create_job.php        # Create job (employer)
│   ├── delete_job.php        # Delete job (admin)
│   ├── get_job_stats.php     # Dashboard metrics
│   │
│   ├── applications.php      # POST application
│   ├── get_applications.php  # Employer: get applicants
│   ├── get_applicants.php    # Similar to above
│   ├── student_applications.php  # Student: my applications
│   ├── update_application_status.php  # Update status
│   ├── track_external_application.php # Track external apply
│   │
│   ├── student_profile.php   # GET/POST profile
│   ├── employer_applicants.php  # Employer applicants view
│   │
│   ├── admin.php             # Admin operations
│   ├── admin_jobs.php        # Admin job management
│   ├── admin_users.php       # Admin user management
│   │
│   ├── import_jobs_api.php   # SSE endpoint for job import
│   ├── sync_data.php         # Cross-server sync (API key auth)
│   └── ...                   # (44 total API files)
│
├── classes/                   # Business Logic (Models)
│   ├── Database.php          # Singleton DB connection
│   ├── User.php              # User model (auth, CRUD)
│   ├── Job.php               # Job model (search, filters)
│   ├── Application.php       # Application tracking
│   ├── Model.php             # Base model (CRUD helpers)
│   ├── ExternalAPIService.php  # JSearch & Adzuna integration
│   ├── WebSocketServer.php   # WebSocket server class
│   └── autoload.php          # Class autoloader
│
├── includes/                  # Shared Utilities
│   ├── db.php                # Database connection ($conn)
│   ├── session.php           # Session init & timeout handling
│   ├── auth_check.php        # Require login (include in pages)
│   ├── csrf.php              # CSRF token generation & validation
│   ├── helpers.php           # Utility functions
│   ├── upload_helpers.php    # File upload validation
│   ├── navbar.php            # Reusable navigation bar
│   ├── footer.php            # Reusable footer
│   ├── load_env.php          # Load .env variables
│   ├── config_loader.php     # Multi-server config loader
│   └── ...
│
├── js/                        # Frontend JavaScript (ES6 Modules)
│   ├── app.js                # PWA: service worker, install prompt, offline queue
│   ├── api.js                # Fetch wrapper with auth headers
│   ├── auth.js               # Login/signup forms
│   ├── jobs.js               # Job listings display
│   ├── apply.js              # Application form submission
│   ├── dashboard.js          # Dashboard API helpers
│   ├── navbar.js             # Dynamic navbar (user info, theme, logout)
│   ├── theme.js              # Theme management (dark/light)
│   ├── websocket-client.js   # WebSocket client (real-time notifications)
│   ├── student-profile.js    # Profile editing
│   ├── post-job.js           # Job posting form
│   ├── profile.js            # Profile helpers
│   ├── scripts.js            # General UI utilities
│   └── JS_LEARNING_GUIDE.md  # JavaScript learning resource
│
├── css/                       # Stylesheets
│   ├── global.css            # CSS variables, base styles, layout
│   ├── base.css              # Utility classes (spacing, display)
│   ├── styles.css            # Alternative theme
│   ├── dashboard.css         # Dashboard-specific
│   ├── responsive.css        # Media queries
│   ├── jobs.css              # Job listings
│   ├── applications.css      # Applications page
│   ├── forms.css             # Form styling
│   ├── dark-themed.css       # Dark mode overrides
│   ├── landing.css           # Landing page
│   └── CSS_LEARNING_GUIDE.md # CSS learning resource
│
├── assets/                    # Static Assets
│   ├── images/               # Images, logos
│   └── icons/                # PWA icons
│
├── uploads/                   # User-uploaded files (CVs, profile pics)
│   ├── cvs/
│   └── profiles/
│
├── cache/                     # Cached data
│   ├── api/
│   └── notifications/
│
├── sql/                       # Database Schema & Migrations
│   └── uniconnect_db.sql     # Initial database structure
│
├── vendor/                    # Composer dependencies (if any)
│
├── service-worker.js          # PWA service worker (caching)
├── sw.js                      # Alternative service worker
├── manifest.json              # PWA manifest (icons, name, theme)
├── offline.html               # Offline fallback page
│
├── websocket_server.php       # WebSocket server entrypoint
├── start_websocket.bat        # Windows: start WebSocket server
│
├── test_*.php                 # Test/debug files
└── PROJECT_README.md          # This file
```

---

## Database Schema

### Core Tables

#### `users`
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- bcrypt hash
    role ENUM('student', 'employer', 'admin') DEFAULT 'student',
    profile_image VARCHAR(255),
    theme VARCHAR(20) DEFAULT 'dark',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `students`
```sql
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,  -- FK to users.id
    full_name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    education TEXT,
    skills TEXT,
    profile_pic VARCHAR(255),
    cv_file VARCHAR(255),
    profile_completion INT DEFAULT 0,  -- percentage
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `employers`
```sql
CREATE TABLE employers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    logo VARCHAR(255),
    description TEXT,
    location VARCHAR(255),
    industry VARCHAR(100),
    website VARCHAR(255),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `jobs`
```sql
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    type ENUM('Full-time', 'Part-time', 'Contract', 'Internship') DEFAULT 'Full-time',
    industry VARCHAR(100),
    requirements TEXT,
    responsibilities TEXT,
    status ENUM('Open', 'Closed', 'Draft') DEFAULT 'Open',
    application_method ENUM('Direct', 'External') DEFAULT 'Direct',
    external_link VARCHAR(500),  -- For external jobs
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_industry (industry)
);
```

#### `applications`
```sql
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    studentId INT NOT NULL,
    jobId INT NOT NULL,
    status ENUM('Applied', 'Reviewed', 'Interview', 'Hired', 'Rejected') DEFAULT 'Applied',
    coverLetter TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (studentId) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (jobId) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (studentId, jobId)  -- Prevent duplicate applications
);
```

### Relationships
- **One-to-Many**: `employers` → `jobs` (one employer posts many jobs)
- **One-to-Many**: `users` → `applications` (one student has many applications)
- **One-to-Many**: `jobs` → `applications` (one job receives many applications)
- **Many-to-Many**: `users` ↔ `jobs` (via `applications` junction table)

---

## Core Features

### 1. User Registration & Authentication
**Files**: `pages/login.php`, `pages/register.php`, `api/auth/`

- **Registration**: Students and employers sign up with email/password
- **Login**: Email + password verified against bcrypt hash in database
- **Session Management**: PHP sessions store user data server-side
- **Role-Based Access**: Different dashboards for student/employer/admin
- **CSRF Protection**: All forms include CSRF tokens

**Flow**:
```
User fills form → POST to /api/auth/register.php
  ↓
Validate inputs (email format, password strength)
  ↓
Hash password with bcrypt
  ↓
INSERT into users table
  ↓
Return success JSON
  ↓
Redirect to login page
```

### 2. Job Listings & Search
**Files**: `pages/jobs.php`, `js/jobs.js`, `api/jobs.php`, `api/search_jobs.php`

- **Browse Jobs**: Paginated list of all open positions
- **Search**: LIKE queries across title, location, industry
- **Filters**: Job type (Full-time, Internship, etc.), location, industry
- **External Jobs**: Jobs from JSearch/Adzuna APIs with "Apply Externally" link

**Search Implementation**:
```sql
-- api/search_jobs.php
SELECT * FROM jobs
WHERE title LIKE CONCAT('%', ?, '%')
   OR location LIKE CONCAT('%', ?, '%')
   OR industry LIKE CONCAT('%', ?, '%')
AND status = 'Open'
ORDER BY createdAt DESC
LIMIT 10
```

### 3. Job Applications
**Files**: `pages/apply.php`, `js/apply.js`, `api/applications.php`

**Direct Application** (Internal Jobs):
```
Student clicks "Apply" → Form with cover letter
  ↓
POST to /api/applications.php with jobId, coverLetter
  ↓
Validate authentication & CSRF token
  ↓
INSERT into applications table
  ↓
Return success → Redirect to applications page
```

**External Application** (External Jobs):
```
Student clicks "Apply Externally" → Opens external link in new tab
  ↓
POST to /api/track_external_application.php to log tracking
  ↓
INSERT into applications with external flag
```

### 4. Application Tracking
**Files**: `pages/applications.php`, `api/student_applications.php`

Students see all their applications with:
- Job title, company, location
- Application status (Applied → Reviewed → Interview → Hired/Rejected)
- Application date
- Direct vs External indicator

**Status Updates**:
- Students can update their own application status
- Employers can update status for applications to their jobs (future feature)

### 5. Employer Features
**Files**: `pages/employer.php`, `pages/post-job.php`, `pages/employer-applicants.php`

- **Post Jobs**: Create job listings with title, description, requirements
- **View Applicants**: See all applications for their jobs
- **Review Applications**: View student profiles, CVs, cover letters
- **Manage Jobs**: Edit/delete job postings

### 6. Admin Panel
**Files**: `pages/admin.php`, `api/admin_*.php`

- **User Management**: View/edit/delete users
- **Job Management**: Moderate job postings
- **Import External Jobs**: Trigger job imports from APIs
- **Analytics**: Dashboard with job counts, application stats

### 7. Real-Time Notifications (WebSockets)
**Files**: `websocket_server.php`, `js/websocket-client.js`, `classes/WebSocketServer.php`

**Server** (PHP Socket Programming):
```php
// Start WebSocket server on port 8080
php websocket_server.php
```

**Client** (JavaScript WebSocket API):
```javascript
const ws = new WebSocket('ws://localhost:8080');
ws.addEventListener('message', (e) => {
  const data = JSON.parse(e.data);
  if (data.type === 'job_notification') {
    showNotification('New Job Posted!', data.title);
  }
});
```

**Notification Types**:
- New job postings
- Application status changes
- System announcements

### 8. Progressive Web App (PWA)
**Files**: `service-worker.js`, `manifest.json`, `js/app.js`

- **Offline Support**: Service worker caches pages and assets
- **Install Prompt**: "Add to Home Screen" button
- **Offline Queue**: Form submissions queued when offline, sync when online
- **App-like Experience**: Runs in standalone mode on mobile

**Service Worker Strategy**:
```javascript
// Cache-first for static assets
self.addEventListener('fetch', (e) => {
  e.respondWith(
    caches.match(e.request).then(response => 
      response || fetch(e.request)
    )
  );
});
```

---

## Authentication & Security

### 1. Password Security
- **Hashing**: `password_hash($password, PASSWORD_DEFAULT)` uses bcrypt
- **Verification**: `password_verify($input, $hash)` for login
- **Never store plain text passwords**

### 2. SQL Injection Prevention
```php
// ❌ UNSAFE (vulnerable to SQL injection)
$query = "SELECT * FROM users WHERE email = '$email'";

// ✅ SAFE (prepared statement with parameter binding)
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
```

### 3. Cross-Site Scripting (XSS) Prevention
```php
// ❌ UNSAFE (HTML in user input renders as code)
echo "<p>Welcome, {$_POST['name']}</p>";

// ✅ SAFE (escape HTML entities)
echo "<p>Welcome, " . htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') . "</p>";
```

### 4. Cross-Site Request Forgery (CSRF) Protection
```php
// Generate token on page load
$csrf_token = generate_csrf_token();  // Stored in $_SESSION

// Include in form
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

// Verify on submission
if (!verify_csrf_token($_POST['csrf_token'])) {
    die('Invalid CSRF token');
}
```

### 5. Session Security
```php
// includes/session.php
session_start([
    'cookie_httponly' => true,      // Prevent JavaScript access
    'cookie_secure' => true,        // HTTPS only (production)
    'cookie_samesite' => 'Strict',  // CSRF protection
]);

// Session timeout (30 minutes inactivity)
if (isset($_SESSION['LAST_ACTIVITY']) && 
    (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_destroy();
    header('Location: /pages/login.php');
    exit;
}
```

### 6. File Upload Security
```php
// includes/upload_helpers.php
function validateUpload($file) {
    // Check file size (max 5MB)
    if ($file['size'] > 5242880) return false;
    
    // Check MIME type
    $allowed = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($file['type'], $allowed)) return false;
    
    // Check file extension
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'pdf'])) {
        return false;
    }
    
    return true;
}
```

---

## API Architecture

### RESTful Design Principles

#### HTTP Methods
- **GET**: Retrieve data (idempotent, safe, cacheable)
- **POST**: Create new resources or submit forms
- **PUT/PATCH**: Update existing resources (not heavily used in this project)
- **DELETE**: Remove resources

#### Standard Response Format
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}

// Or on error:
{
  "success": false,
  "error": "Validation failed: Missing email",
  "code": 400
}
```

#### HTTP Status Codes
- **200 OK**: Success
- **201 Created**: Resource created
- **400 Bad Request**: Invalid input
- **401 Unauthorized**: Not logged in
- **403 Forbidden**: Logged in but insufficient permissions
- **404 Not Found**: Resource doesn't exist
- **405 Method Not Allowed**: Wrong HTTP verb
- **500 Internal Server Error**: Server-side error

### Example API Endpoint
```php
// api/jobs.php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Retrieve jobs
    $id = $_GET['id'] ?? null;
    
    if ($id) {
        // Single job
        $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $job = $stmt->get_result()->fetch_assoc();
        
        if (!$job) {
            http_response_code(404);
            echo json_encode(['error' => 'Job not found']);
            exit;
        }
        
        echo json_encode(['job' => $job]);
    } else {
        // All jobs
        $result = $conn->query("SELECT * FROM jobs ORDER BY createdAt DESC");
        $jobs = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['jobs' => $jobs]);
    }
    exit;
}

if ($method === 'POST') {
    // Create job (requires authentication)
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    // ... validation and INSERT logic
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
```

---

## Real-Time Features (WebSockets)

### Architecture

```
┌─────────────┐                  ┌─────────────┐
│   Browser   │  WebSocket       │  WebSocket  │
│   Client    │ ←─────────────→  │   Server    │
│ (JS client) │  ws://host:8080  │  (PHP CLI)  │
└─────────────┘                  └──────┬──────┘
                                        │
                                   Publishes to
                                   registered
                                   clients
```

### Server Setup
```bash
# Start WebSocket server (Windows)
start_websocket.bat

# Or manually
php websocket_server.php

# Server listens on ws://localhost:8080
```

### Client Implementation
```javascript
// js/websocket-client.js
const ws = new CareerHubWebSocket('ws://localhost:8080');
ws.connect();

// Subscribe to channels
ws.subscribe('job_updates');
ws.subscribe('notifications:user:' + userId);

// Listen for events
ws.on('jobNotification', (data) => {
    console.log('New job:', data.title);
    showBrowserNotification(data);
});

ws.on('applicationUpdate', (data) => {
    console.log('Application status changed:', data.status);
    updateApplicationUI(data);
});
```

### Message Protocol
```javascript
// Client → Server
{
  "type": "register",
  "userId": 123
}

{
  "type": "subscribe",
  "channel": "job_updates"
}

// Server → Client
{
  "type": "job_notification",
  "title": "Software Engineer",
  "company": "Tech Corp",
  "jobId": 456
}

{
  "type": "application_update",
  "status": "Interview",
  "jobTitle": "Data Analyst"
}
```

### Reconnection Strategy
- Automatic reconnection with exponential backoff
- Max 5 reconnection attempts
- Resubscribes to channels after reconnect
- Visual indicator shows connection status

---

## Frontend JavaScript Modules

### Module System (ES6)
```javascript
// Export (api.js)
export async function api(path, options) {
    // fetch wrapper
}

// Import (jobs.js)
import { api } from './api.js';

// Use
const jobs = await api('/jobs');
```

### Key Modules

#### `api.js` - Fetch Wrapper
```javascript
export async function api(path, opts = {}) {
    const token = localStorage.getItem('token');
    const headers = {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        ...opts.headers
    };
    
    const res = await fetch(path, { ...opts, headers });
    if (!res.ok) throw new Error(await res.text());
    return res.json();
}
```

#### `auth.js` - Login/Signup
```javascript
loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = form.querySelector('[name="email"]').value;
    const password = form.querySelector('[name="password"]').value;
    
    const data = await api('/api/auth/login', {
        method: 'POST',
        body: JSON.stringify({ email, password })
    });
    
    localStorage.setItem('token', data.token);
    window.location.href = '/dashboard';
});
```

#### `jobs.js` - Display Jobs
```javascript
async function loadJobs() {
    const jobs = await api('/api/jobs');
    const container = document.getElementById('jobsList');
    
    jobs.forEach(job => {
        const card = createJobCard(job);
        container.appendChild(card);
    });
}
```

#### `app.js` - PWA Features
```javascript
// Service worker registration
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js');
}

// Install prompt
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    showInstallButton();
});

// Offline queue
function queueOfflineSubmission(formData, endpoint) {
    const queue = JSON.parse(localStorage.getItem('offline-queue') || '[]');
    queue.push({ endpoint, data: formData, timestamp: Date.now() });
    localStorage.setItem('offline-queue', JSON.stringify(queue));
}
```

---

## Styling & Responsive Design

### CSS Architecture

```
global.css          → CSS variables, reset, base styles
base.css            → Utility classes (.mt-2, .flex-center)
responsive.css      → Media queries for mobile/tablet/desktop
[feature].css       → Feature-specific styles (jobs.css, dashboard.css)
dark-themed.css     → Dark mode overrides
```

### CSS Variables (Design Tokens)
```css
:root {
    /* Colors */
    --primary-color: #0066cc;
    --secondary-color: #ff6600;
    --text-color: #333333;
    --bg-color: #ffffff;
    --border-color: #cccccc;
    
    /* Spacing (8px scale) */
    --spacing-xs: 4px;
    --spacing-sm: 8px;
    --spacing-md: 16px;
    --spacing-lg: 24px;
    --spacing-xl: 32px;
    
    /* Typography */
    --font-main: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --font-size-sm: 14px;
    --font-size-base: 16px;
    --font-size-lg: 18px;
    
    /* Border Radius */
    --radius-sm: 4px;
    --radius-md: 8px;
    --radius-lg: 12px;
}

/* Dark theme overrides */
.dark-theme {
    --text-color: #ffffff;
    --bg-color: #1a1a1a;
    --border-color: #444444;
}
```

### Responsive Breakpoints
```css
/* Mobile first approach */

/* Base styles (mobile 320px+) */
.container {
    padding: 10px;
}

/* Tablets (768px+) */
@media (min-width: 768px) {
    .container {
        padding: 20px;
        max-width: 720px;
        margin: 0 auto;
    }
}

/* Desktops (1024px+) */
@media (min-width: 1024px) {
    .container {
        max-width: 960px;
    }
}

/* Large desktops (1200px+) */
@media (min-width: 1200px) {
    .container {
        max-width: 1140px;
    }
}
```

### Common Patterns
```css
/* Card component */
.card {
    background: white;
    border-radius: var(--radius-md);
    padding: var(--spacing-lg);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

/* Flexbox centering */
.flex-center {
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Grid layout */
.grid-3-col {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-lg);
}
```

---

## Development Workflow

### Setup

1. **Clone Repository**
```bash
git clone https://github.com/yourrepo/career_hub.git
cd career_hub
```

2. **Configure Environment**
```bash
# Copy example env file
cp .env.example .env

# Edit .env with your credentials
DB_HOST=localhost
DB_NAME=career_hub
DB_USER=root
DB_PASS=your_password
RAPIDAPI_KEY=your_key
ADZUNA_APP_ID=your_id
ADZUNA_APP_KEY=your_key
```

3. **Database Setup**
```bash
# Import SQL schema
mysql -u root -p career_hub < sql/uniconnect_db.sql

# Or via phpMyAdmin
# Import → Browse → uniconnect_db.sql → Go
```

4. **Start Servers**
```bash
# Apache/WAMP (Windows)
Start WAMP → localhost/career_hub

# Apache (Linux/Mac)
sudo apachectl start

# PHP built-in server (development only)
php -S localhost:8000

# WebSocket server (separate terminal)
php websocket_server.php
```

### Development Tools

**Browser DevTools**:
- **Console**: View JS errors, logs
- **Network**: Inspect API requests/responses
- **Application**: View localStorage, Service Worker, cache
- **Sources**: Debug JavaScript with breakpoints

**VS Code Extensions** (recommended):
- PHP Intelephense (PHP autocomplete)
- ESLint (JavaScript linting)
- Prettier (code formatting)
- Live Server (auto-reload)

### Testing

```php
// Test database connection
include 'includes/db.php';
echo $conn ? "✅ Connected" : "❌ Failed";

// Test API endpoint
curl http://localhost/career_hub/api/jobs.php

// Test with authentication
curl -H "Cookie: PHPSESSID=abc123" \
     http://localhost/career_hub/api/student_applications.php
```

```javascript
// Test JavaScript module
import { api } from './js/api.js';
const jobs = await api('/api/jobs');
console.log(jobs);
```

---

## Deployment Guide

### Production Checklist

1. **Security Hardening**
```php
// ✅ Enable HTTPS only
session_start(['cookie_secure' => true]);

// ✅ Remove debug code
error_reporting(0);
ini_set('display_errors', 0);

// ✅ Secure file permissions
chmod 644 *.php
chmod 755 directories
chmod 600 .env

// ✅ Move .env outside web root
```

2. **Database Optimization**
```sql
-- Add indexes for common queries
CREATE INDEX idx_jobs_status ON jobs(status);
CREATE INDEX idx_jobs_created ON jobs(createdAt);
CREATE INDEX idx_applications_student ON applications(studentId);
```

3. **Caching**
```php
// Enable OPcache (php.ini)
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000

// Browser caching (.htaccess)
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

4. **Environment Variables**
```bash
# Use server environment variables instead of .env file
export DB_HOST=prod-db.example.com
export DB_NAME=career_hub_prod
export DB_USER=prod_user
export DB_PASS=strong_password
```

5. **Backup Strategy**
```bash
# Daily database backups
0 2 * * * mysqldump -u user -p career_hub > backup_$(date +\%Y\%m\%d).sql

# Weekly file backups
tar -czf files_$(date +\%Y\%m\%d).tar.gz uploads/
```

---

## Common Tasks & Examples

### Task 1: Add a New API Endpoint

**Goal**: Create `/api/get_profile.php` to return user profile

```php
<?php
// api/get_profile.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Check authentication
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = (int) $_SESSION['user']['id'];

// Fetch profile
$stmt = $conn->prepare("
    SELECT id, name, email, profile_image, created_at 
    FROM users 
    WHERE id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

if (!$profile) {
    http_response_code(404);
    echo json_encode(['error' => 'Profile not found']);
    exit;
}

echo json_encode(['profile' => $profile]);
?>
```

**Frontend Usage**:
```javascript
// js/profile.js
import { api } from './api.js';

async function loadProfile() {
    const data = await api('/api/get_profile.php');
    document.getElementById('userName').textContent = data.profile.name;
}
```

### Task 2: Add a New Page

**Goal**: Create `/pages/about.php`

```php
<?php
// pages/about.php
require_once __DIR__ . '/../includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - Career Hub</title>
    <link rel="stylesheet" href="/css/global.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <main class="container">
        <h1>About Career Hub</h1>
        <p>Career Hub connects students with employers...</p>
    </main>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
```

### Task 3: Add Database Migration

**Goal**: Add `phone` column to `users` table

```sql
-- sql/migrations/001_add_phone_to_users.sql
ALTER TABLE users 
ADD COLUMN phone VARCHAR(20) AFTER email;

-- Verify
DESCRIBE users;
```

```bash
# Run migration
mysql -u root -p career_hub < sql/migrations/001_add_phone_to_users.sql
```

### Task 4: Debug API Request

**Problem**: API returns 500 error

```bash
# Check PHP error log
tail -f /var/log/apache2/error.log

# Or in WAMP
# C:\wamp64\logs\php_error.log

# Enable error display temporarily
ini_set('display_errors', 1);
error_reporting(E_ALL);

# Add debug logging
error_log("API called with: " . json_encode($_POST));
```

---

## Troubleshooting

### Issue 1: Database Connection Failed

**Symptoms**: "Connection refused" or "Access denied"

**Solutions**:
```php
// Check credentials in includes/db.php
$host = 'localhost';  // Try 127.0.0.1 instead
$user = 'root';       // Correct username?
$pass = '';           // Correct password?
$db   = 'career_hub'; // Database exists?

// Test connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Failed: " . $conn->connect_error);
}
echo "✅ Connected successfully";
```

### Issue 2: Session Not Working

**Symptoms**: User logged out immediately, session data lost

**Solutions**:
```php
// Ensure session_start() is called
session_start();  // Must be BEFORE any output

// Check session directory permissions
echo session_save_path();  // Should be writable

// Verify session ID
echo session_id();  // Should persist across requests

// Check session lifetime
ini_set('session.gc_maxlifetime', 3600);  // 1 hour
```

### Issue 3: CSRF Token Invalid

**Symptoms**: "Invalid CSRF token" on form submission

**Solutions**:
```php
// Generate token AFTER session_start()
session_start();
$token = generate_csrf_token();

// Ensure form includes token
<input type="hidden" name="csrf_token" value="<?= $token ?>">

// Check token from multiple sources
$token = $_POST['csrf_token'] ?? 
         $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 
         null;
```

### Issue 4: File Upload Failing

**Symptoms**: File not appearing in uploads/ directory

**Solutions**:
```php
// Check file size limits (php.ini)
upload_max_filesize = 10M
post_max_size = 10M

// Check directory permissions
chmod 755 uploads/
chmod 755 uploads/cvs/

// Check for upload errors
if ($_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
    echo "Upload error: " . $_FILES['cv']['error'];
}
```

### Issue 5: WebSocket Not Connecting

**Symptoms**: "WebSocket connection failed" in browser console

**Solutions**:
```bash
# Check if server is running
netstat -an | grep 8080

# Start server manually
php websocket_server.php

# Check firewall
# Allow port 8080 in Windows Firewall

# Verify URL
ws://localhost:8080  # Not wss:// for local development
```

### Issue 6: PWA Not Installing

**Symptoms**: "Add to Home Screen" not appearing

**Solutions**:
```javascript
// Check manifest.json is valid
// Visit: chrome://inspect/#service-workers

// Verify HTTPS (required for PWA)
// Use localhost for development (HTTPS not required)

// Check service worker registration
navigator.serviceWorker.getRegistrations()
    .then(regs => console.log(regs));

// Unregister and re-register
navigator.serviceWorker.getRegistrations()
    .then(regs => regs.forEach(reg => reg.unregister()));
```

---

## Learning Resources

### Included Guides
- `js/JS_LEARNING_GUIDE.md` - Complete JavaScript tutorial
- `css/CSS_LEARNING_GUIDE.md` - Complete CSS tutorial
- This file (`PROJECT_README.md`) - Project overview

### External Resources
- **PHP**: [PHP Manual](https://www.php.net/manual/en/)
- **MySQL**: [MySQL Documentation](https://dev.mysql.com/doc/)
- **JavaScript**: [MDN Web Docs](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
- **WebSockets**: [WebSocket API](https://developer.mozilla.org/en-US/docs/Web/API/WebSocket)
- **PWA**: [Progressive Web Apps](https://web.dev/progressive-web-apps/)

---

## Project Roadmap

### Completed ✅
- User authentication (session-based)
- Job listings and search
- Application tracking
- Real-time notifications (WebSocket)
- PWA support
- External job import
- Dark/light theme
- Responsive design

### In Progress 🚧
- Email notifications
- Advanced search filters
- Employer application management
- Chat between students and employers

### Planned 📋
- Video interviews
- Skills assessment tests
- Resume builder
- Company profiles with reviews
- Mobile apps (React Native)
- Analytics dashboard
- Multi-language support

---

## Contributing

### Code Style
- **PHP**: PSR-12 coding standard
- **JavaScript**: ES6+, 2-space indentation
- **CSS**: BEM naming convention for components
- **Comments**: Explain "why", not "what"

### Git Workflow
```bash
# Create feature branch
git checkout -b feature/add-chat

# Make changes
git add .
git commit -m "feat: add real-time chat feature"

# Push and create PR
git push origin feature/add-chat
```

### Commit Message Format
```
type(scope): subject

Types: feat, fix, docs, style, refactor, test, chore
Example: feat(api): add endpoint for chat messages
```

---

## License

This project is for educational purposes. All rights reserved.

---

## Support

**Issues**: Open an issue on GitHub  
**Email**: support@careerhub.com  
**Documentation**: This README and included learning guides

---

**🎉 You're now ready to understand and work with the Career Hub project!**

**Next Steps**:
1. Read through this entire README
2. Set up your development environment
3. Explore the code with the teaching comments
4. Build a small feature to practice
5. Refer to JS_LEARNING_GUIDE.md and CSS_LEARNING_GUIDE.md as needed

**Happy Coding! 🚀**
