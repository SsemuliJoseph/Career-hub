# Career Connect Hub — Comprehensive Project Documentation

## Table of Contents
1. [Introduction & Features](#introduction--features)
2. [System Architecture](#system-architecture)
3. [Technology Stack & Interrelations](#technology-stack--interrelations)
4. [Data Flow & Processing](#data-flow--processing)
5. [Authentication & Session Management](#authentication--session-management)
6. [Database Schema & Relationships](#database-schema--relationships)
7. [API Design & Endpoints](#api-design--endpoints)
8. [Real-Time Features (WebSocket)](#real-time-features-websocket)
9. [Responsive Design & Frontend](#responsive-design--frontend)
10. [Object-Oriented Patterns & Module Sharing](#object-oriented-patterns--module-sharing)
11. [Client-Server Architecture](#client-server-architecture)
12. [File Upload & Security](#file-upload--security)
13. [Deployment & Setup](#deployment--setup)
14. [Future Enhancements](#future-enhancements)

---

## 1. Introduction & Features

*Career Connect Hub* is a full-stack web platform connecting students and graduates with job and internship opportunities. The system provides role-based access for students, employers, and administrators.

### Core Features

#### For Students:
- ✅ *Profile Management*: Create/edit profile with personal info, education, skills, profile picture, and CV upload
- 📄 *CV Review Service*: Upload CV, preview in browser, request professional feedback
- 💼 *Job Search & Applications*: Browse jobs/internships, apply with cover letter and CV
- 📊 *Dashboard*: View profile completion, applied jobs count, recommended opportunities
- 🔔 *Real-Time Notifications*: WebSocket-powered live updates for new jobs and application status changes
- 📱 *Responsive Design*: Mobile-first UI with hamburger menus and adaptive layouts

#### For Employers:
- 📝 *Job Posting*: Create and manage job/internship listings
- 👥 *Applicant Management*: View applications, download CVs, update application status
- 📧 *Notifications*: Real-time alerts when students apply

#### For Administrators:
- 🛠 *User Management*: View/manage all users, moderate content
- 📋 *CV Review Queue*: Review student CVs and provide feedback
- 📈 *Analytics*: Track platform usage and engagement

#### Platform-Wide:
- 🔐 *Secure Authentication*: Password hashing, session management, role-based access control
- 🔍 *Smart Search*: Autocomplete job search with AJAX suggestions
- 🌓 *Theme Toggle*: Dark/light mode with localStorage persistence
- 🎨 *Modern UI*: LinkedIn-inspired design with smooth transitions

---

## 2. System Architecture

### System Collaboration Diagram

mermaid
graph LR
    Browser[":Browser<br/>(Client)"]
    WebServer[":WebServer<br/>(Apache/Nginx)"]
    PHP[":PHPRuntime<br/>(Server)"]
    MySQL[":MySQL<br/>(Database)"]
    FileSystem[":FileSystem<br/>(Storage)"]
    WSServer[":WebSocketServer<br/>(Real-time)"]
    
    Browser -->|"1: HTTP Request"| WebServer
    WebServer -->|"2: Execute PHP"| PHP
    PHP -->|"3: Query"| MySQL
    MySQL -->|"4: ResultSet"| PHP
    PHP -->|"5: Read/Write Files"| FileSystem
    FileSystem -->|"6: File Path"| PHP
    PHP -->|"7: HTML Response"| WebServer
    WebServer -->|"8: HTTP Response"| Browser
    Browser <-->|"9: WebSocket Messages"| WSServer
    PHP -.->|"10: Trigger Event"| WSServer


### Component Collaboration Diagram

mermaid
graph TB
    subgraph ":ClientLayer"
        HTML[":HTMLPage"]
        CSS[":Stylesheet"]
        JS[":JavaScript"]
        WSClient[":WebSocketClient"]
    end
    
    subgraph ":ServerLayer"
        PHPPage[":PHPPage"]
        Includes[":SharedModules"]
        API[":APIEndpoint"]
        Session[":SessionManager"]
    end
    
    subgraph ":DataLayer"
        DB[":Database"]
        Files[":FileStorage"]
    end
    
    HTML -->|"includes"| CSS
    HTML -->|"contains"| JS
    JS -->|"1: fetch()"| API
    JS -->|"2: manipulate"| HTML
    WSClient <-->|"3: connect/subscribe"| WSServer[":WSServer"]
    
    PHPPage -->|"4: require"| Includes
    PHPPage -->|"5: read/write"| Session
    API -->|"6: require"| Includes
    Includes -->|"7: query"| DB
    API -->|"8: INSERT/UPDATE"| DB
    PHPPage -->|"9: upload/read"| Files
    API -->|"10: save file"| Files


### Directory Structure


career_hub/
├── index.php                  # Public landing page
├── README.md                  # This documentation
├── pages/                     # Application pages
│   ├── login.php              # User authentication
│   ├── signup.php             # User registration
│   ├── student.php            # Student dashboard
│   ├── student-profile.php    # Profile edit/upload
│   ├── cv.php                 # CV review page
│   ├── jobs.php               # Job listings
│   ├── apply.php              # Job application form
│   ├── my-applications.php    # Application tracker
│   ├── employer.php           # Employer dashboard
│   └── admin.php              # Admin panel
├── includes/                  # Shared PHP modules
│   ├── db.php                 # Database connection
│   ├── session.php            # Session initialization
│   ├── auth_check.php         # Authentication guard
│   ├── helpers.php            # Utility functions
│   ├── navbar.php             # Navigation component
│   └── footer.php             # Footer component
├── api/                       # REST-like API endpoints
│   ├── auth/
│   │   ├── login.php          # Login endpoint
│   │   └── logout.php         # Logout endpoint
│   ├── jobs.php               # Job CRUD operations
│   ├── search_jobs.php        # Search autocomplete
│   ├── applications.php       # Application submission
│   └── request_cv_review.php  # CV review request
├── css/                       # Stylesheets
│   ├── global.css             # Global styles & variables
│   ├── responsive.css         # Media queries
│   └── student-profile.css    # Page-specific styles
├── js/                        # Client-side JavaScript
│   └── websocket-client.js    # WebSocket client class
├── ws/                        # WebSocket server
│   └── websocket.php          # Ratchet WS server
├── uploads/                   # User-uploaded files
│   ├── profile/               # Profile images
│   ├── cv/                    # Student CVs
│   └── applications/          # Application CVs
└── docs/                      # Additional documentation
    └── project_overview.md    # Presentation summary


---

## 3. Technology Stack & Interrelations

### Technology Overview

| Layer | Technology | Purpose |
|-------|-----------|---------|
| *Frontend (Structure)* | HTML5 | Semantic markup, forms, accessibility |
| *Frontend (Style)* | CSS3 | Responsive design, themes, animations |
| *Frontend (Logic)* | JavaScript (ES6+) | DOM manipulation, AJAX, WebSocket client |
| *Backend (Server)* | PHP 8.x | Server-side logic, session, database queries |
| *Database* | MySQL 8.0 | Relational data storage |
| *Real-Time* | WebSocket (Ratchet/Node) | Live notifications |
| *File Storage* | Local filesystem | Profile images, CVs |

### Technology Collaboration Diagram

mermaid
graph LR
    HTML[":HTML<br/>(Structure)"]
    CSS[":CSS<br/>(Presentation)"]
    JS[":JavaScript<br/>(Behavior)"]
    PHP[":PHP<br/>(Server Logic)"]
    MySQL[":MySQL<br/>(Data)"]
    Session[":Session<br/>(State)"]
    WS[":WebSocket<br/>(Real-time)"]
    
    HTML -->|"1: styled by"| CSS
    HTML -->|"2: manipulated by"| JS
    JS -->|"3: fetch() request"| PHP
    PHP -->|"4: renders"| HTML
    PHP -->|"5: queries"| MySQL
    PHP -->|"6: read/write"| Session
    JS <-->|"7: bidirectional"| WS
    PHP -.->|"8: triggers"| WS
    
    style HTML fill:#e1f5ff
    style CSS fill:#ffe1f5
    style JS fill:#fff5e1
    style PHP fill:#e1ffe1
    style MySQL fill:#f5e1ff


---

## 4. Data Flow & Processing

### User Registration Activity Diagram

mermaid
flowchart TD
    Start([User Opens Signup Page]) --> FillForm[User Fills Registration Form]
    FillForm --> Submit[User Clicks Submit]
    Submit --> Validate{Validate Input}
    Validate -->|Invalid| ShowError[Display Error Message]
    ShowError --> FillForm
    Validate -->|Valid| CheckEmail{Email Exists?}
    CheckEmail -->|Yes| ShowError2[Display 'Email Already Registered']
    ShowError2 --> FillForm
    CheckEmail -->|No| HashPassword[Hash Password with password_hash]
    HashPassword --> InsertDB[INSERT INTO users Table]
    InsertDB --> CreateSession[Create $_SESSION with User Data]
    CreateSession --> Redirect[Redirect to Dashboard]
    Redirect --> End([Show Dashboard Page])


### Job Application Activity Diagram

mermaid
flowchart TD
    Start([Student Opens Apply Page]) --> LoadJob[Load Job Details from DB]
    LoadJob --> ShowForm[Display Application Form]
    ShowForm --> FillForm[Student Fills Form & Uploads CV]
    FillForm --> Submit[Student Clicks Submit]
    Submit --> ValidateForm{Validate Form Data}
    ValidateForm -->|Invalid| ShowError[Display Error Message]
    ShowError --> FillForm
    ValidateForm -->|Valid| CheckDuplicate{Already Applied?}
    CheckDuplicate -->|Yes| ShowError2[Display 'Already Applied']
    ShowError2 --> End1([Return to Jobs Page])
    CheckDuplicate -->|No| ValidateFile{Validate CV File}
    ValidateFile -->|Invalid| ShowError3[Display 'Invalid File Type/Size']
    ShowError3 --> FillForm
    ValidateFile -->|Valid| SaveFile[Save CV to /uploads/applications/]
    SaveFile --> InsertDB[INSERT INTO applications Table]
    InsertDB --> TriggerWS[Trigger WebSocket Notification]
    TriggerWS --> SendResponse[Send JSON Success Response]
    SendResponse --> ShowSuccess[Display Success Message]
    ShowSuccess --> Redirect[Redirect to My Applications]
    Redirect --> End2([Show Applications List])


### Profile Update Activity Diagram

mermaid
flowchart TD
    Start([Student Opens Profile Page]) --> LoadProfile[Fetch Profile Data from DB & Session]
    LoadProfile --> ShowForm[Display Profile Form with Current Data]
    ShowForm --> EditForm[Student Edits Form & Uploads Image]
    EditForm --> Submit[Student Clicks Save]
    Submit --> ValidateInput{Validate Input}
    ValidateInput -->|Invalid| ShowError[Display Error Message]
    ShowError --> EditForm
    ValidateInput -->|Valid| CheckImage{Image Uploaded?}
    CheckImage -->|Yes| ValidateImage{Validate Image File}
    ValidateImage -->|Invalid| ShowError2[Display 'Invalid Image']
    ShowError2 --> EditForm
    ValidateImage -->|Valid| SaveImage[Save Image to /uploads/profile/]
    SaveImage --> UpdateDB
    CheckImage -->|No| UpdateDB[UPDATE student_profiles Table]
    UpdateDB --> RefreshSession[Update $_SESSION with New Data]
    RefreshSession --> SetTimestamp[Set profile_image_ts for Cache-Bust]
    SetTimestamp --> Redirect[Redirect to Dashboard]
    Redirect --> End([Show Updated Dashboard])


### Authentication Flow Activity Diagram

mermaid
flowchart TD
    Start([User Visits Protected Page]) --> CheckSession{Session Exists?}
    CheckSession -->|No| RedirectLogin[Redirect to login.php]
    RedirectLogin --> ShowLogin[Display Login Form]
    ShowLogin --> EnterCreds[User Enters Email, Password, Role]
    EnterCreds --> Submit[User Clicks Login]
    Submit --> CheckAdmin{Check Admins Table}
    CheckAdmin -->|Found| VerifyAdminPass{Verify Password}
    VerifyAdminPass -->|Invalid| ShowError[Display Error]
    ShowError --> ShowLogin
    VerifyAdminPass -->|Valid| CreateAdminSession[Create Admin Session]
    CreateAdminSession --> RedirectAdmin[Redirect to admin.php]
    RedirectAdmin --> EndAdmin([Admin Dashboard])
    CheckAdmin -->|Not Found| CheckUser{Check Users Table}
    CheckUser -->|Not Found| ShowError
    CheckUser -->|Found| VerifyUserPass{Verify Password}
    VerifyUserPass -->|Invalid| ShowError
    VerifyUserPass -->|Valid| CreateUserSession[Create User Session]
    CreateUserSession --> CheckRole{Check Role}
    CheckRole -->|Student| RedirectStudent[Redirect to student.php]
    CheckRole -->|Employer| RedirectEmployer[Redirect to employer.php]
    RedirectStudent --> EndStudent([Student Dashboard])
    RedirectEmployer --> EndEmployer([Employer Dashboard])
    CheckSession -->|Yes| VerifyRole{Correct Role?}
    VerifyRole -->|No| Show403[Display 403 Access Denied]
    Show403 --> End403([Error Page])
    VerifyRole -->|Yes| RenderPage[Render Protected Page]
    RenderPage --> EndProtected([Protected Page Displayed])


---

## 5. Authentication & Session Management

### Authentication Collaboration Diagram

mermaid
graph TB
    User[":User"] -->|"1: visits"| Browser[":Browser"]
    Browser -->|"2: HTTP GET"| Page[":ProtectedPage"]
    Page -->|"3: require"| AuthCheck[":auth_check.php"]
    AuthCheck -->|"4: check"| Session[":SessionManager"]
    Session -->|"5: exists?"| AuthCheck
    AuthCheck -->|"6a: no session"| Page
    Page -->|"7a: redirect"| Browser
    Browser -->|"8a: show"| LoginPage[":login.php"]
    
    AuthCheck -->|"6b: session valid"| Page
    Page -->|"7b: render"| Browser
    Browser -->|"8b: display"| User
    
    LoginPage -->|"9: POST credentials"| LoginScript[":LoginHandler"]
    LoginScript -->|"10: query"| DB[":Database"]
    DB -->|"11: user data"| LoginScript
    LoginScript -->|"12: verify password"| LoginScript
    LoginScript -->|"13: create session"| Session
    Session -->|"14: redirect"| Browser


### Session Data Structure

php
$_SESSION['user'] = [
    'id' => 123,                    // User ID from database
    'email' => 'student@example.com',
    'role' => 'student',            // student|employer|admin
    'name' => 'John Doe',           // Username
    'fullName' => 'John Doe',       // Full name (students)
    'profile_image' => '/uploads/profile/profile_123_1234567890.jpg',
    'profile_image_ts' => 1234567890, // Cache-bust timestamp
    'cv' => '/uploads/cv/cv_123_1234567890.pdf',
    'phone' => '0700123456',
    'education' => 'Undergraduate',
    'skills' => 'PHP, JavaScript, MySQL'
];


### Login Process (login.php)

1. *Form Submission*: User enters email, password, role
2. *Admin Check*: Query admins table first for admin login
3. *User Check*: Query users table with role filter
4. *Password Verification*: password_verify() compares hashed password
5. *Session Creation*: Store user data in $_SESSION['user']
6. *Role-Based Redirect*:
   - Admin → admin.php
   - Student → student.php
   - Employer → employer.php

### Protection Mechanism (auth_check.php)

php
// Included at top of protected pages
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: /pages/login.php');
    exit;
}
// Optional: role check
if ($_SESSION['user']['role'] !== 'student') {
    http_response_code(403);
    die('Access denied');
}


---

## 6. Database Schema & Relationships

### Entity Collaboration Diagram

mermaid
graph LR
    Users[":users<br/>(Entity)"]
    StudentProfiles[":student_profiles<br/>(Entity)"]
    Jobs[":jobs<br/>(Entity)"]
    Applications[":applications<br/>(Entity)"]
    CVReviews[":cv_reviews<br/>(Entity)"]
    
    Users -->|"1: has profile<br/>(1:1 by email)"| StudentProfiles
    Users -->|"2: submits<br/>(1:N)"| Applications
    Users -->|"3: requests<br/>(1:N)"| CVReviews
    Jobs -->|"4: receives<br/>(1:N)"| Applications
    
    style Users fill:#e1f5ff
    style StudentProfiles fill:#ffe1f5
    style Jobs fill:#fff5e1
    style Applications fill:#e1ffe1
    style CVReviews fill:#f5e1ff


### Entity-Relationship Diagram

mermaid
erDiagram
    users ||--o{ student_profiles : has
    users ||--o{ applications : submits
    users ||--o{ cv_reviews : requests
    jobs ||--o{ applications : receives
    
    users {
        int id PK
        string username
        string email UK
        string password
        enum role
        datetime createdAt
        datetime updatedAt
    }
    
    student_profiles {
        int id PK
        string email FK
        string fullName
        string phone
        string education
        text skills
        string profilePic
        string cvFile
        datetime created_at
    }
    
    jobs {
        int id PK
        int employer_id FK
        string title
        string company
        text description
        string location
        string type
        datetime created_at
    }
    
    applications {
        int id PK
        int studentId FK
        int jobId FK
        string fullName
        string email
        string phone
        text coverLetter
        string cvPath
        enum status
        datetime appliedAt
    }
    
    cv_reviews {
        int id PK
        int studentId FK
        string cvPath
        enum status
        text feedback
        datetime createdAt
        datetime reviewedAt
    }


### Key Relationships

- *users → student_profiles*: One-to-one (linked by email)
- *users → applications*: One-to-many (student submits multiple applications)
- *jobs → applications*: One-to-many (job receives multiple applications)
- *users → cv_reviews*: One-to-many (student can request multiple reviews over time)

### Data Integrity

- *Foreign Keys*: Enforce referential integrity (CASCADE on delete)
- *Unique Constraints*: Prevent duplicate emails
- *ENUM Types*: Restrict role/status values to valid options
- *Prepared Statements*: All queries use parameterized inputs to prevent SQL injection

---

## 7. API Design & Endpoints

### API Request-Response Collaboration Diagram

mermaid
graph LR
    Client[":Client<br/>(Browser)"]
    API[":APIEndpoint<br/>(PHP)"]
    Validator[":InputValidator"]
    DB[":Database"]
    FileHandler[":FileHandler"]
    Session[":Session"]
    Response[":JSONResponse"]
    
    Client -->|"1: POST/GET request"| API
    API -->|"2: validate"| Validator
    Validator -->|"3: validation result"| API
    API -->|"4: query/insert"| DB
    DB -->|"5: result set"| API
    API -->|"6: save file"| FileHandler
    FileHandler -->|"7: file path"| API
    API -->|"8: update"| Session
    API -->|"9: build response"| Response
    Response -->|"10: JSON"| Client


### API Endpoints Overview

| Endpoint | Method | Purpose | Authentication | Request | Response |
|----------|--------|---------|----------------|---------|----------|
| /api/auth/login.php | POST | User login | No | {email, password, role} | {success, user} |
| /api/auth/logout.php | POST | End session | Yes | None | {success} |
| /api/jobs.php | GET | List jobs | Optional | ?category=&page= | {jobs: [...]} |
| /api/search_jobs.php | GET | Autocomplete | Yes | ?q=keyword | [{id, title, company}] |
| /api/applications.php | POST | Submit application | Yes | FormData (fields + CV file) | {success, message} |
| /api/request_cv_review.php | POST | Request CV review | Yes | {cvPath} | {success, message} |

### Example: Job Search API Flow

mermaid
sequenceDiagram
    participant User
    participant JS
    participant API
    participant DB
    
    User->>JS: Type "software" in search
    JS->>API: GET /api/search_jobs.php?q=software
    API->>DB: SELECT * FROM jobs WHERE title LIKE '%software%'
    DB-->>API: Rows [{id:1, title:"Software Engineer"}, ...]
    API-->>JS: JSON [{id:1, title:"Software Engineer"}, ...]
    JS->>JS: Render suggestions dropdown
    JS-->>User: Show suggestions
    User->>JS: Click suggestion
    JS-->>User: Navigate to jobs.php?jobId=1


### API Security Measures

1. *Authentication Check*: Most endpoints include auth_check.php
2. *Input Validation*: Sanitize and validate all user inputs
3. *SQL Injection Prevention*: Use prepared statements exclusively
4. *File Upload Validation*: Check MIME type, file extension, size limits
5. *CSRF Protection*: (Future enhancement: token-based validation)

---

## 8. Real-Time Features (WebSocket)

### WebSocket Communication Collaboration Diagram

mermaid
graph TB
    Student[":StudentBrowser"]
    WSClient[":WebSocketClient<br/>(JS)"]
    WSServer[":WebSocketServer<br/>(Ratchet/Node)"]
    API[":ApplicationAPI<br/>(PHP)"]
    Employer[":EmployerBrowser"]
    
    Student -->|"1: load page"| WSClient
    WSClient -->|"2: connect()"| WSServer
    WSServer -->|"3: onOpen, send welcome"| WSClient
    WSClient -->|"4: subscribe('jobs')"| WSServer
    WSServer -->|"5: confirm subscription"| WSClient
    
    Student -->|"6: submit application"| API
    API -->|"7: insert to DB"| DB[":Database"]
    API -.->|"8: trigger event"| WSServer
    WSServer -->|"9: broadcast notification"| Employer
    WSServer -->|"10: update UI"| WSClient
    
    style WSServer fill:#ffe1e1


### WebSocket Connection Activity Diagram

mermaid
flowchart TD
    Start([Page Loads]) --> InitWS[Initialize WebSocketClient]
    InitWS --> Connect[Call connect() Method]
    Connect --> CreateSocket[Create WebSocket Instance]
    CreateSocket --> WaitOpen{Wait for Connection}
    WaitOpen -->|Error| HandleError[Trigger onError Handler]
    HandleError --> ScheduleReconnect[Schedule Reconnect with Backoff]
    ScheduleReconnect --> Wait[Wait Delay Period]
    Wait --> Connect
    WaitOpen -->|Success| OnOpen[Trigger onOpen Handler]
    OnOpen --> SendWelcome[Server Sends Welcome Message]
    SendWelcome --> EmitConnected[Emit 'connected' Event]
    EmitConnected --> Subscribe1[Subscribe to 'jobs' Channel]
    Subscribe1 --> Subscribe2[Subscribe to 'applications' Channel]
    Subscribe2 --> Subscribe3[Subscribe to 'notifications' Channel]
    Subscribe3 --> StartHeartbeat[Start Heartbeat Interval]
    StartHeartbeat --> Listen([Listen for Messages])
    Listen --> ReceiveMsg[Receive WebSocket Message]
    ReceiveMsg --> ParseJSON{Parse JSON}
    ParseJSON -->|Invalid| LogError[Log Parse Error]
    LogError --> Listen
    ParseJSON -->|Valid| CheckType{Check Message Type}
    CheckType -->|notification| EmitNotification[Emit 'notification' Event]
    EmitNotification --> ShowToast[Show Notification Toast]
    ShowToast --> UpdateUI[Update UI Elements]
    UpdateUI --> Listen
    CheckType -->|other| EmitGeneric[Emit 'message' Event]
    EmitGeneric --> Listen


### Notification Broadcasting Activity Diagram

mermaid
flowchart TD
    Start([Application Submitted]) --> InsertDB[Insert into applications Table]
    InsertDB --> GetJobDetails[Fetch Job & Employer Info]
    GetJobDetails --> BuildPayload[Build Notification Payload]
    BuildPayload --> TriggerWS[Send Event to WebSocket Server]
    TriggerWS --> WSReceive[WS Server Receives Event]
    WSReceive --> ParseEvent[Parse Event Data]
    ParseEvent --> GetChannel{Determine Target Channel}
    GetChannel --> IterateClients[Iterate Connected Clients]
    IterateClients --> CheckSubscription{Client Subscribed?}
    CheckSubscription -->|No| NextClient[Move to Next Client]
    NextClient --> MoreClients{More Clients?}
    MoreClients -->|Yes| CheckSubscription
    MoreClients -->|No| End1([End])
    CheckSubscription -->|Yes| SendMessage[Send JSON Message to Client]
    SendMessage --> ClientReceive[Client Receives Message]
    ClientReceive --> DisplayToast[Display Toast Notification]
    DisplayToast --> PlaySound[Play Notification Sound]
    PlaySound --> UpdateBadge[Update Badge Count]
    UpdateBadge --> NextClient


---

## 9. Responsive Design & Frontend

### Responsive Breakpoints

css
/* Mobile First Approach */
/* Base styles: 320px - 767px */

/* Tablet: 768px - 1023px */
@media (min-width: 768px) { ... }

/* Desktop: 1024px+ */
@media (min-width: 1024px) { ... }

/* Large Desktop: 1440px+ */
@media (min-width: 1440px) { ... }


### Adaptive Components

*Navbar*:
- Desktop: Horizontal links + user menu dropdown
- Mobile: Hamburger menu → slide-out navigation

*Grid Layouts*:
- Mobile: 1 column stack
- Tablet: 2 columns
- Desktop: 3 columns

css
.grid-3-col {
    display: grid;
    grid-template-columns: 1fr; /* Mobile */
    gap: 20px;
}

@media (min-width: 768px) {
    .grid-3-col {
        grid-template-columns: repeat(2, 1fr); /* Tablet */
    }
}

@media (min-width: 1024px) {
    .grid-3-col {
        grid-template-columns: repeat(3, 1fr); /* Desktop */
    }
}


### Theme System

*CSS Variables* (global.css):
css
:root {
    --bg-primary: #0a0f1e;
    --text-primary: #e8eaed;
    --linkedin-blue: #0a66c2;
    /* ...more variables */
}

body.light-theme {
    --bg-primary: #ffffff;
    --text-primary: #1f1f1f;
    /* ...override variables */
}


*Theme Toggle* (JavaScript):
javascript
const toggle = document.getElementById('theme-toggle');
const savedTheme = localStorage.getItem('theme') || 'dark';
document.body.classList.add(savedTheme + '-theme');

toggle.addEventListener('click', () => {
    const isLight = document.body.classList.toggle('light-theme');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
});


---

## 10. Object-Oriented Patterns & Module Sharing

### Module Sharing Collaboration Diagram

mermaid
graph TB
    Login[":login.php"]
    Student[":student.php"]
    Profile[":student-profile.php"]
    API[":applications.php"]
    
    DB[":db.php<br/>(Shared)"]
    Auth[":auth_check.php<br/>(Shared)"]
    Helpers[":helpers.php<br/>(Shared)"]
    Navbar[":navbar.php<br/>(Shared)"]
    
    Login -->|"1: require"| DB
    Login -->|"2: require"| Helpers
    Login -->|"3: include"| Navbar
    
    Student -->|"4: require"| DB
    Student -->|"5: require"| Auth
    Student -->|"6: require"| Helpers
    Student -->|"7: include"| Navbar
    
    Profile -->|"8: require"| DB
    Profile -->|"9: require"| Auth
    Profile -->|"10: require"| Helpers
    Profile -->|"11: include"| Navbar
    
    API -->|"12: require"| DB
    API -->|"13: require"| Auth
    API -->|"14: require"| Helpers
    
    style DB fill:#e1f5ff
    style Auth fill:#ffe1f5
    style Helpers fill:#fff5e1
    style Navbar fill:#e1ffe1


### OOP in JavaScript: WebSocketClient Class

javascript
class WebSocketClient {
    constructor(url) {
        this.url = url;
        this.ws = null;
        this.handlers = {};
        this.reconnectDelay = 3000;
    }
    
    // Public methods
    connect() { /* ... */ }
    on(event, callback) { /* ... */ }
    emit(event, data) { /* ... */ }
    send(data) { /* ... */ }
    
    // Private method (convention: prefix with _)
    _scheduleReconnect() { /* ... */ }
}

// Usage: Instantiate and use
const ws = new WebSocketClient('ws://localhost:8080');
ws.connect();
ws.on('notification', handleNotification);


*Benefits*:
- Encapsulation: Internal state (handlers, reconnectDelay) hidden
- Reusability: Single class used across multiple pages
- Maintainability: Changes in one place affect all instances

### Module Sharing in PHP: Includes Pattern

*Shared Modules* (includes/):
- db.php: Database connection (singleton pattern)
- auth_check.php: Reusable authentication guard
- helpers.php: Utility functions (getUserName, sanitize, etc.)
- navbar.php: Shared navigation component
- footer.php: Shared footer component

*Usage Pattern*:
php
// Every page includes necessary modules
require_once __DIR__ . '/../includes/db.php';         // Database
require_once __DIR__ . '/../includes/auth_check.php'; // Auth
require_once __DIR__ . '/../includes/helpers.php';    // Utils

// Access shared resources
$conn; // Database connection from db.php
getUserName(); // Helper function from helpers.php


*Benefits*:
- DRY Principle: No code duplication
- Consistency: Database connection logic centralized
- Security: Auth check applied consistently
- Maintainability: Update footer once, reflects everywhere

### Data Passing Between Modules

*Session as Shared State*:
php
// login.php sets session
$_SESSION['user'] = ['id' => 1, 'email' => '...'];

// student.php reads session
$userId = $_SESSION['user']['id'];

// student-profile.php updates session
$_SESSION['user']['profile_image'] = '/uploads/profile/new.jpg';
$_SESSION['user']['profile_image_ts'] = time();

// navbar.php renders from session
echo $_SESSION['user']['profile_image'];


*Database as Persistent State*:
- Write operations update both database and session
- Read operations prefer session (fast) with occasional DB refresh

---

## 11. Client-Server Architecture

### Request-Response Activity Diagram

mermaid
flowchart TD
    Start([User Clicks Link]) --> SendHTTP[Browser Sends HTTP GET Request]
    SendHTTP --> WebServer[Web Server Receives Request]
    WebServer --> RouteRequest[Route to PHP File]
    RouteRequest --> LoadPHP[Load & Execute PHP Script]
    LoadPHP --> IncludeModules[Include Required Modules]
    IncludeModules --> CheckAuth{Authentication Required?}
    CheckAuth -->|Yes| ValidateSession{Session Valid?}
    ValidateSession -->|No| Redirect[Redirect to Login]
    Redirect --> End1([Show Login Page])
    ValidateSession -->|Yes| ProcessLogic[Execute Business Logic]
    CheckAuth -->|No| ProcessLogic
    ProcessLogic --> QueryDB[Query Database]
    QueryDB --> FetchData[Fetch Result Set]
    FetchData --> RenderHTML[Render HTML with PHP]
    RenderHTML --> SendResponse[Send HTTP Response]
    SendResponse --> BrowserReceive[Browser Receives HTML]
    BrowserReceive --> ParseHTML[Parse & Render DOM]
    ParseHTML --> ExecuteJS[Execute Inline JavaScript]
    ExecuteJS --> UserInteraction([User Sees Page])
    UserInteraction --> AJAX{User Triggers AJAX?}
    AJAX -->|No| End2([End])
    AJAX -->|Yes| FetchAPI[JavaScript fetch() API Call]
    FetchAPI --> PHPEndpoint[PHP API Endpoint]
    PHPEndpoint --> ValidateAPI[Validate Input]
    ValidateAPI --> QueryDB2[Query Database]
    QueryDB2 --> FormatJSON[Format Response as JSON]
    FormatJSON --> SendJSON[Send JSON Response]
    SendJSON --> ParseJSON[JavaScript Parses JSON]
    ParseJSON --> UpdateDOM[Update DOM Elements]
    UpdateDOM --> End3([User Sees Updated Content])


---

## 12. File Upload & Security

### File Upload Activity Diagram

mermaid
flowchart TD
    Start([User Selects File]) --> ValidateClient{Client-side Validation}
    ValidateClient -->|Invalid| ShowClientError[Show Error Message]
    ShowClientError --> End1([User Retries])
    ValidateClient -->|Valid| Preview[Show File Preview]
    Preview --> Submit[User Submits Form]
    Submit --> SendMultipart[Send multipart/form-data]
    SendMultipart --> PHPReceive[PHP Receives $_FILES]
    PHPReceive --> CheckError{Upload Error?}
    CheckError -->|Yes| ReturnError[Return Error Response]
    ReturnError --> End2([Show Error to User])
    CheckError -->|No| CheckMIME{Valid MIME Type?}
    CheckMIME -->|No| ReturnError
    CheckMIME -->|Yes| CheckSize{Within Size Limit?}
    CheckSize -->|No| ReturnError
    CheckSize -->|Yes| CheckDir{Upload Dir Exists?}
    CheckDir -->|No| CreateDir[Create Directory with 0755]
    CreateDir --> GenerateFilename
    CheckDir -->|Yes| GenerateFilename[Generate Unique Filename]
    GenerateFilename --> MoveFile[move_uploaded_file()]
    MoveFile --> CheckMove{Move Successful?}
    CheckMove -->|No| ReturnError
    CheckMove -->|Yes| SavePath[Save File Path to Database]
    SavePath --> UpdateSession[Update $_SESSION with Path]
    UpdateSession --> SetTimestamp[Set Cache-Bust Timestamp]
    SetTimestamp --> ReturnSuccess[Return Success Response]
    ReturnSuccess --> ClientUpdate[Client Updates UI]
    ClientUpdate --> End3([File Uploaded Successfully])


### Security Measures

#### 1. File Type Validation
php
$allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
$mimeType = mime_content_type($_FILES['file']['tmp_name']);
if (!in_array($mimeType, $allowedTypes)) {
    die('Invalid file type');
}


#### 2. File Size Limits
php
$maxSize = 5 * 1024 * 1024; // 5MB
if ($_FILES['file']['size'] > $maxSize) {
    die('File too large');
}


#### 3. Unique Filenames (Prevent Overwrite)
php
$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$filename = 'profile_' . $userId . '_' . time() . '.' . $ext;


#### 4. Secure File Permissions
php
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true); // Read/write owner, read-only others
}


#### 5. Path Validation (Prevent Directory Traversal)
php
$uploadDir = realpath(__DIR__ . '/../uploads/profile');
$destination = $uploadDir . '/' . basename($filename);
// basename() strips path traversal attempts (../)


### Cache-Busting for Updated Files

*Problem*: Browser caches old profile image after user uploads new one

*Solution*: Append version query parameter based on file modification time
php
$imagePath = '/uploads/profile/profile_123.jpg';
$localPath = __DIR__ . $imagePath;
$version = file_exists($localPath) ? filemtime($localPath) : time();
$imageUrl = $imagePath . '?v=' . $version;
// Result: /uploads/profile/profile_123.jpg?v=1234567890


Browser treats different query strings as different URLs → fetches new file

---

## 13. Deployment & Setup

### Prerequisites

- PHP 8.0+
- MySQL 8.0+
- Apache/Nginx web server
- Composer (for WebSocket dependencies)
- Node.js (optional, for alternative WS server)

### Installation Steps

1. *Clone Repository*:
bash
git clone https://github.com/yourusername/career_hub.git
cd career_hub


2. *Database Setup*:
bash
mysql -u root -p
CREATE DATABASE career_hub;
USE career_hub;
SOURCE schema.sql; # Import database schema


3. *Configure Database Connection* (includes/db.php):
php
$host = 'localhost';
$user = 'root';
$pass = 'your_password';
$db = 'career_hub';


4. *Set File Permissions*:
bash
chmod 755 uploads/
chmod 755 uploads/profile/ uploads/cv/ uploads/applications/


5. *Install WebSocket Dependencies* (if using Ratchet):
bash
composer require cboden/ratchet


6. *Start WebSocket Server*:
bash
php ws/websocket.php
# Runs on ws://localhost:8080


7. *Configure Virtual Host* (Apache example):
apache
<VirtualHost *:80>
    ServerName careerhub.local
    DocumentRoot "C:/wamp64/www/career_hub"
    <Directory "C:/wamp64/www/career_hub">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>


8. *Update Hosts File* (Windows: C:\Windows\System32\drivers\etc\hosts):

127.0.0.1    careerhub.local


9. *Access Application*:

http://careerhub.local


### Production Deployment Checklist

- [ ] Change database credentials
- [ ] Enable HTTPS (Let's Encrypt)
- [ ] Use wss:// for WebSocket in production
- [ ] Disable display_errors in php.ini
- [ ] Set up automated backups (database + uploads)
- [ ] Configure firewall rules
- [ ] Set up process manager for WebSocket (Supervisor/systemd)
- [ ] Enable rate limiting on API endpoints
- [ ] Add CSRF token validation
- [ ] Implement email verification
- [ ] Set up monitoring (server health, error logs)

---

## 14. Future Enhancements

### Planned Features

1. *Email Notifications*:
   - Application confirmation emails
   - Job recommendations based on skills
   - Interview invites

2. *Advanced Search & Filters*:
   - Filter by location, salary, job type
   - Saved searches
   - Job alerts

3. *Employer Dashboard Enhancements*:
   - Applicant shortlisting
   - Interview scheduling
   - Bulk actions on applications

4. *Admin Panel Features*:
   - User analytics dashboard
   - Content moderation tools
   - System health monitoring

5. *Student Profile Enhancements*:
   - Portfolio/project showcase
   - Skill endorsements
   - Recommendations from mentors

6. *AI-Powered Features*:
   - CV parsing (extract skills automatically)
   - Job recommendations (ML-based matching)
   - Interview question generator

7. *Mobile App*:
   - React Native or Flutter app
   - Push notifications
   - Offline mode for saved jobs

8. *Testing & Quality*:
   - Unit tests (PHPUnit)
   - Integration tests (Selenium)
   - CI/CD pipeline (GitHub Actions)

---

## Summary

*Career Connect Hub* demonstrates a full-stack web application with:

✅ *Clean Architecture*: Separation of concerns (presentation, logic, data)  
✅ *Secure by Design*: Password hashing, prepared statements, file validation  
✅ *Real-Time Capabilities*: WebSocket notifications  
✅ *Responsive UI*: Mobile-first design with theme support  
✅ *Modular Codebase*: Reusable components and shared utilities  
✅ *Scalable Foundation*: Ready for enhancement with APIs, admin tools, and advanced features  

---

## Contact & Support

- *Project Repository*: c:\wamp64\www\career_hub
- *Documentation*: This file (README.md)
- *Presentation Summary*: docs/project_overview.md

For questions or contributions, contact the development team or open an issue in the repository.

---

*© 2025 Career Connect Hub. All rights reserved.*