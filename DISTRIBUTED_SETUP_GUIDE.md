# Distributed Multi-Server Setup Guide
## Career Connect Hub - Group Project Configuration

---

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Work Division Strategy](#work-division-strategy)
3. [Network Configuration](#network-configuration)
4. [Server Setup](#server-setup)
5. [Database Synchronization](#database-synchronization)
6. [API Integration](#api-integration)
7. [Client Configuration](#client-configuration)
8. [Testing & Deployment](#testing--deployment)

---

## 1. Architecture Overview

### System Design
```
┌─────────────────────────────────────────────────────┐
│                   CLIENT DEVICE                      │
│              (Phone/PC - Browser)                    │
│                                                      │
│  ┌──────────────────────────────────────────────┐  │
│  │   Frontend (HTML/CSS/JS)                     │  │
│  │   - Makes API calls to both servers          │  │
│  │   - Load balancing logic                     │  │
│  └──────────────────────────────────────────────┘  │
└──────────────────┬──────────────┬───────────────────┘
                   │              │
        ┌──────────┴──────┐  ┌───┴──────────┐
        │                 │  │              │
┌───────▼────────┐ ┌──────▼──────────┐
│  SERVER 1      │ │   SERVER 2      │
│  (Laptop A)    │ │   (Laptop B)    │
│                │ │                 │
│  WAMP Server   │ │   WAMP Server   │
│  - Apache      │ │   - Apache      │
│  - MySQL       │ │   - MySQL       │
│  - PHP         │ │   - PHP         │
│                │ │                 │
│  Handles:      │ │   Handles:      │
│  - Students    │ │   - Employers   │
│  - Jobs        │ │   - Applications│
│  - Auth        │ │   - Admin       │
└────────────────┘ └─────────────────┘
```

---

## 2. Work Division Strategy

### 🎯 Recommended Division

#### **Server 1 (Laptop A) - Student & Jobs Module**
**Team Member(s): 1-2 people**

**Responsibilities:**
- Student registration and authentication
- Student profiles and dashboards
- Job listings and search
- Job posting by employers
- Career tips and resources
- Success stories

**Files to Focus On:**
```
pages/
├── student.php
├── student-profile.php
├── jobs.php
├── internship.php
├── my-applications.php
├── career tips.php
└── Success_stories.php

api/
├── auth/
│   ├── login.php
│   ├── signup.php
│   └── logout.php
├── jobs.php
├── search_jobs.php
└── student/

includes/
├── auth_check.php
└── session.php
```

**Database Tables:**
- `users` (role='student')
- `students`
- `student_profiles`
- `jobs`

---

#### **Server 2 (Laptop B) - Employer & Admin Module**
**Team Member(s): 1-2 people**

**Responsibilities:**
- Employer registration and authentication
- Employer profiles and dashboards
- Application management
- Admin panel and controls
- User management
- Application tracking

**Files to Focus On:**
```
pages/
├── employer.php
├── employer-profile.php
├── employer-applicants.php
├── admin.php
└── admin-login.php

api/
├── admin.php
├── admin_users.php
├── admin_jobs.php
├── admin_applications.php
├── employer_applicants.php
└── employer/

includes/
├── db.php
└── helpers.php
```

**Database Tables:**
- `users` (role='employer')
- `employers`
- `applications`
- `admins`

---

#### **Client (Phone/PC) - Integration & Testing**
**Team Member(s): 1 person**

**Responsibilities:**
- Frontend integration
- API routing logic
- Load balancing
- Testing all features
- UI/UX consistency
- Mobile responsiveness

**Files to Focus On:**
```
index.php
pages/
├── settings.php
├── contact.php
└── opportunities.php

css/
├── global.css
├── responsive.css
└── *.css

js/
├── app.js
└── *.js
```

---

## 3. Network Configuration

### Step 1: Connect All Devices to Same Network

**Option A: WiFi Network (Recommended)**
1. Connect all laptops and client device to the same WiFi
2. Ensure network is set to "Private" (not Public)
3. Disable firewalls temporarily for testing

**Option B: Hotspot**
1. One laptop creates a mobile hotspot
2. Other devices connect to it
3. More stable for development

### Step 2: Find IP Addresses

**On Each Server Laptop (Windows):**
```cmd
ipconfig
```
Look for: `IPv4 Address: 192.168.x.x`

**Example:**
- Server 1 (Laptop A): `192.168.1.100`
- Server 2 (Laptop B): `192.168.1.101`
- Client Device: `192.168.1.102`

### Step 3: Configure WAMP for Network Access

**On Both Server Laptops:**

1. **Allow Apache to accept external connections:**
   - Open: `C:\wamp64\bin\apache\apache2.x.x\conf\httpd.conf`
   - Find: `Listen 80`
   - Change to: `Listen 0.0.0.0:80`

2. **Allow directory access:**
   - Open: `C:\wamp64\bin\apache\apache2.x.x\conf\extra\httpd-vhosts.conf`
   - Add this configuration:
   ```apache
   <VirtualHost *:80>
       DocumentRoot "C:/wamp64/www"
       ServerName localhost
       <Directory "C:/wamp64/www">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. **Configure MySQL for remote access:**
   - Open phpMyAdmin
   - Go to User Accounts
   - Edit root user
   - Change Host from `localhost` to `%` (allows all IPs)
   - Or create specific user for remote access:
   ```sql
   CREATE USER 'remote_user'@'%' IDENTIFIED BY 'password123';
   GRANT ALL PRIVILEGES ON uniconnect_db.* TO 'remote_user'@'%';
   FLUSH PRIVILEGES;
   ```

4. **Restart WAMP services**

### Step 4: Test Connectivity

**From Client Device:**
```
http://192.168.1.100/
http://192.168.1.101/
```

**From Server 1 to Server 2:**
```cmd
ping 192.168.1.101
```

---

## 4. Server Setup

### Server 1 Configuration (Student & Jobs)

**File: `includes/config_server1.php`**
```php
<?php
// Server 1 Configuration - Student & Jobs Module

// Local Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'uniconnect_db');

// Server 2 Connection (for cross-server queries)
define('SERVER2_URL', 'http://192.168.1.101');
define('SERVER2_API', 'http://192.168.1.101/api');

// Remote Database (Server 2)
define('DB2_HOST', '192.168.1.101');
define('DB2_USER', 'remote_user');
define('DB2_PASS', 'password123');
define('DB2_NAME', 'uniconnect_db');

// This server's role
define('SERVER_ROLE', 'STUDENT_JOBS');
define('SERVER_ID', 1);

// API Key for server-to-server communication
define('API_SECRET_KEY', 'your_secure_secret_key_here');
?>
```

### Server 2 Configuration (Employer & Admin)

**File: `includes/config_server2.php`**
```php
<?php
// Server 2 Configuration - Employer & Admin Module

// Local Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'uniconnect_db');

// Server 1 Connection (for cross-server queries)
define('SERVER1_URL', 'http://192.168.1.100');
define('SERVER1_API', 'http://192.168.1.100/api');

// Remote Database (Server 1)
define('DB1_HOST', '192.168.1.100');
define('DB1_USER', 'remote_user');
define('DB1_PASS', 'password123');
define('DB1_NAME', 'uniconnect_db');

// This server's role
define('SERVER_ROLE', 'EMPLOYER_ADMIN');
define('SERVER_ID', 2);

// API Key for server-to-server communication
define('API_SECRET_KEY', 'your_secure_secret_key_here');
?>
```

---

## 5. Database Synchronization

### Strategy 1: Shared Tables (Recommended for Development)

**Both servers have the same database structure but handle different data:**

**Server 1 Primary Tables:**
- `students`
- `student_profiles`
- `jobs` (writes)

**Server 2 Primary Tables:**
- `employers`
- `applications`
- `admins`

**Shared Tables (Both servers need read access):**
- `users` (both read/write)
- `jobs` (Server 1 writes, Server 2 reads)

### Strategy 2: Database Replication

**Master-Master Replication Setup:**

1. **On Server 1 MySQL (my.ini):**
```ini
[mysqld]
server-id=1
log-bin=mysql-bin
binlog-do-db=uniconnect_db
relay-log=mysql-relay-bin
auto-increment-increment=2
auto-increment-offset=1
```

2. **On Server 2 MySQL (my.ini):**
```ini
[mysqld]
server-id=2
log-bin=mysql-bin
binlog-do-db=uniconnect_db
relay-log=mysql-relay-bin
auto-increment-increment=2
auto-increment-offset=2
```

3. **Create replication users on both servers:**
```sql
CREATE USER 'repl'@'%' IDENTIFIED BY 'repl_password';
GRANT REPLICATION SLAVE ON *.* TO 'repl'@'%';
FLUSH PRIVILEGES;
```

4. **Set up replication:**
```sql
-- On Server 1
CHANGE MASTER TO
  MASTER_HOST='192.168.1.101',
  MASTER_USER='repl',
  MASTER_PASSWORD='repl_password',
  MASTER_LOG_FILE='mysql-bin.000001',
  MASTER_LOG_POS=0;
START SLAVE;

-- On Server 2
CHANGE MASTER TO
  MASTER_HOST='192.168.1.100',
  MASTER_USER='repl',
  MASTER_PASSWORD='repl_password',
  MASTER_LOG_FILE='mysql-bin.000001',
  MASTER_LOG_POS=0;
START SLAVE;
```

### Strategy 3: API-Based Sync (Simpler Alternative)

**File: `api/sync_data.php`**
```php
<?php
require_once '../includes/db.php';
require_once '../includes/config_server1.php'; // or config_server2.php

header('Content-Type: application/json');

// Verify API key
$headers = getallheaders();
if (!isset($headers['X-API-Key']) || $headers['X-API-Key'] !== API_SECRET_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_jobs':
        // Server 1 provides jobs to Server 2
        $stmt = $conn->query("SELECT * FROM jobs WHERE status = 'Open' ORDER BY createdAt DESC LIMIT 50");
        $jobs = $stmt->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $jobs]);
        break;
        
    case 'get_applications':
        // Server 2 provides applications to Server 1
        $job_id = intval($_GET['job_id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM applications WHERE jobId = ?");
        $stmt->bind_param('i', $job_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $applications = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $applications]);
        break;
        
    case 'sync_user':
        // Sync user data between servers
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, createdAt, updatedAt) VALUES (?, ?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE updatedAt = NOW()");
        $stmt->bind_param('ssss', $data['username'], $data['email'], $data['password'], $data['role']);
        $success = $stmt->execute();
        echo json_encode(['success' => $success]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
```

---

## 6. API Integration

### Cross-Server Communication Helper

**File: `includes/server_api.php`**
```php
<?php
class ServerAPI {
    private $serverUrl;
    private $apiKey;
    
    public function __construct($serverUrl, $apiKey) {
        $this->serverUrl = $serverUrl;
        $this->apiKey = $apiKey;
    }
    
    /**
     * Make API request to another server
     */
    public function request($endpoint, $method = 'GET', $data = null) {
        $url = $this->serverUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return ['error' => 'Request failed', 'code' => $httpCode];
    }
    
    /**
     * Get jobs from Server 1
     */
    public function getJobs($limit = 50) {
        return $this->request("/api/sync_data.php?action=get_jobs&limit=$limit");
    }
    
    /**
     * Get applications from Server 2
     */
    public function getApplications($jobId) {
        return $this->request("/api/sync_data.php?action=get_applications&job_id=$jobId");
    }
    
    /**
     * Sync user data
     */
    public function syncUser($userData) {
        return $this->request("/api/sync_data.php?action=sync_user", 'POST', $userData);
    }
}

// Usage example:
// $server2 = new ServerAPI(SERVER2_URL, API_SECRET_KEY);
// $jobs = $server2->getJobs();
?>
```

### Example: Employer Viewing Applications (Server 2 needs data from Server 1)

**File: `pages/employer-applicants.php` (on Server 2)**
```php
<?php
require_once __DIR__ . '/../includes/config_server2.php';
require_once __DIR__ . '/../includes/server_api.php';
require_once __DIR__ . '/../includes/db.php';

// Get jobs from Server 1
$server1 = new ServerAPI(SERVER1_API, API_SECRET_KEY);
$jobsResponse = $server1->getJobs();
$jobs = $jobsResponse['data'] ?? [];

// Get applications from local database (Server 2)
$stmt = $conn->query("SELECT * FROM applications ORDER BY createdAt DESC");
$applications = $stmt->fetch_all(MYSQLI_ASSOC);

// Match applications with jobs
foreach ($applications as &$app) {
    foreach ($jobs as $job) {
        if ($job['id'] == $app['jobId']) {
            $app['job_title'] = $job['title'];
            $app['job_company'] = $job['company'];
            break;
        }
    }
}
?>
```

---

## 7. Client Configuration

### Load Balancer / Router

**File: `client/config.js`**
```javascript
// Client-side configuration
const ServerConfig = {
    // Server endpoints
    servers: {
        student: 'http://192.168.1.100',
        employer: 'http://192.168.1.101'
    },
    
    // API endpoints
    api: {
        student: 'http://192.168.1.100/api',
        employer: 'http://192.168.1.101/api'
    },
    
    // Route requests to appropriate server
    getServerUrl: function(module) {
        switch(module) {
            case 'student':
            case 'jobs':
            case 'search':
            case 'career-tips':
                return this.servers.student;
            case 'employer':
            case 'applications':
            case 'admin':
                return this.servers.employer;
            default:
                return this.servers.student; // Default
        }
    },
    
    // Make API request with automatic routing
    apiRequest: async function(module, endpoint, options = {}) {
        const baseUrl = module === 'employer' || module === 'admin' 
            ? this.api.employer 
            : this.api.student;
        
        const url = baseUrl + endpoint;
        
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });
            return await response.json();
        } catch (error) {
            console.error('API Request failed:', error);
            // Fallback to other server if primary fails
            return this.fallbackRequest(module, endpoint, options);
        }
    },
    
    // Fallback mechanism
    fallbackRequest: async function(module, endpoint, options) {
        const fallbackUrl = module === 'employer' 
            ? this.api.student 
            : this.api.employer;
        
        try {
            const response = await fetch(fallbackUrl + endpoint, options);
            return await response.json();
        } catch (error) {
            return { error: 'Both servers unavailable' };
        }
    }
};

// Usage examples:
// const jobs = await ServerConfig.apiRequest('student', '/jobs.php');
// const applications = await ServerConfig.apiRequest('employer', '/employer_applicants.php');
```

### Dynamic Page Loading

**File: `index.php` (on client)**
```php
<?php
// Client-side router
$module = $_GET['module'] ?? 'home';
$page = $_GET['page'] ?? 'index';

// Determine which server to proxy to
$serverUrl = '';
switch($module) {
    case 'student':
    case 'jobs':
        $serverUrl = 'http://192.168.1.100';
        break;
    case 'employer':
    case 'admin':
        $serverUrl = 'http://192.168.1.101';
        break;
    default:
        $serverUrl = 'http://192.168.1.100';
}

// Proxy the request
$targetUrl = $serverUrl . '/pages/' . $page . '.php';
$content = file_get_contents($targetUrl);

// Replace server-specific URLs with client URLs
$content = str_replace('http://localhost', 'http://' . $_SERVER['HTTP_HOST'], $content);
$content = str_replace($serverUrl, 'http://' . $_SERVER['HTTP_HOST'], $content);

echo $content;
?>
```

---

## 8. Testing & Deployment

### Testing Checklist

#### **Phase 1: Individual Server Testing**
- [ ] Server 1: Test student registration
- [ ] Server 1: Test job listings
- [ ] Server 1: Test job search
- [ ] Server 2: Test employer registration
- [ ] Server 2: Test application management
- [ ] Server 2: Test admin panel

#### **Phase 2: Cross-Server Communication**
- [ ] Server 1 can fetch data from Server 2
- [ ] Server 2 can fetch data from Server 1
- [ ] API authentication works
- [ ] Data synchronization works

#### **Phase 3: Client Integration**
- [ ] Client can access Server 1
- [ ] Client can access Server 2
- [ ] Routing works correctly
- [ ] Fallback mechanism works
- [ ] Mobile responsiveness

### Development Workflow

**Daily Workflow:**
1. **Morning Sync** (15 min)
   - Each team member pulls latest code
   - Sync database changes
   - Discuss blockers

2. **Development** (3-4 hours)
   - Work on assigned modules
   - Test locally
   - Commit frequently

3. **Integration Testing** (1 hour)
   - Connect all servers
   - Test cross-server features
   - Fix integration issues

4. **Evening Sync** (15 min)
   - Push code to shared repository
   - Document changes
   - Plan next day

### Git Workflow

```bash
# Create branches for each server
git checkout -b server1-student-module
git checkout -b server2-employer-module
git checkout -b client-integration

# Regular commits
git add .
git commit -m "feat: Add student profile page"
git push origin server1-student-module

# Merge when ready
git checkout main
git merge server1-student-module
```

### Combining Work

**Step 1: Code Integration**
```bash
# On main laptop
git clone <repository-url>
git checkout main
git merge server1-student-module
git merge server2-employer-module
git merge client-integration
```

**Step 2: Database Merge**
```sql
-- Export from Server 1
mysqldump -u root uniconnect_db students student_profiles jobs > server1_data.sql

-- Export from Server 2
mysqldump -u root uniconnect_db employers applications admins > server2_data.sql

-- Import to combined database
mysql -u root uniconnect_db < server1_data.sql
mysql -u root uniconnect_db < server2_data.sql
```

**Step 3: Configuration Update**
- Update all server URLs to single server
- Remove cross-server API calls
- Test thoroughly

---

## 9. Troubleshooting

### Common Issues

#### **Issue: Cannot connect to other server**
**Solution:**
```cmd
# Check firewall
netsh advfirewall firewall add rule name="Apache" dir=in action=allow protocol=TCP localport=80

# Check if Apache is listening
netstat -an | findstr :80

# Ping other server
ping 192.168.1.101
```

#### **Issue: Database connection refused**
**Solution:**
```sql
-- Check MySQL users
SELECT User, Host FROM mysql.user;

-- Grant remote access
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'password';
FLUSH PRIVILEGES;
```

#### **Issue: CORS errors in browser**
**Solution:**
Add to `.htaccess`:
```apache
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, Authorization"
```

---

## 10. Security Considerations

### For Development
- ✅ Use strong API keys
- ✅ Validate all inputs
- ✅ Use prepared statements
- ✅ Keep servers on private network

### For Production
- 🔒 Use HTTPS (SSL certificates)
- 🔒 Implement proper authentication
- 🔒 Use VPN for server communication
- 🔒 Regular security audits
- 🔒 Database encryption

---

## 11. Quick Start Commands

### Server 1 Setup
```bash
cd C:\wamp64\www\career-connect-hub
git checkout server1-student-module
# Start WAMP
# Import database: server1_tables.sql
```

### Server 2 Setup
```bash
cd C:\wamp64\www\career-connect-hub
git checkout server2-employer-module
# Start WAMP
# Import database: server2_tables.sql
```

### Client Setup
```bash
cd C:\wamp64\www\career-connect-hub
git checkout client-integration
# Update config.js with server IPs
# Open in browser
```

---

## 12. Contact & Support

**Team Communication:**
- Use WhatsApp/Telegram for quick updates
- Use Git issues for bug tracking
- Daily standup meetings (15 min)
- Weekly integration sessions

**Resources:**
- WAMP Documentation: http://www.wampserver.com/
- MySQL Replication: https://dev.mysql.com/doc/refman/8.0/en/replication.html
- PHP cURL: https://www.php.net/manual/en/book.curl.php

---

**Good luck with your distributed project! 🚀**
