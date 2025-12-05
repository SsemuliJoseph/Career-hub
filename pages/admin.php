<?php
// admin.php - Admin Dashboard
// Teaching: This page is an example of server-rendered admin views.
// It performs authorization checks, executes aggregate queries for
// dashboard metrics, and pre-renders recent activity. For student learning:
// - Note where we use direct $conn->query() for internal-only queries (no user input).
// - Be cautious: if any query used user input, use prepared statements to avoid SQL injection.

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Authorization guard: only users with role 'admin' may continue.
// Explanation: We read from the $_SESSION superglobal which is populated
// after session_start() ran in the included session.php. The null coalescing
// operator (??) is used to avoid "undefined index" notices if 'user' or
// 'role' are not present. This is a defensive pattern; it returns an empty
// string if the value isn't set. We then compare strictly to 'admin'.
// If the current visitor is not an admin, we issue an HTTP redirect
// (header('Location: ...')) and immediately exit to prevent the rest of
// the page from rendering to unauthorized users.
if (($_SESSION['user']['role'] ?? '') !== 'admin') {
  header('Location: admin-login.php');
  exit;
}

// Gather statistics for dashboard cards. These queries do not accept user input,
// so using $conn->query() is acceptable here. For any query that used external data,
// prefer prepared statements.
$stats = [];
// Execute a series of internal aggregate queries to build dashboard metrics.
// Note: these queries do not use user-supplied input, so using ->query() is
// acceptable. If any query concatenated user input, you'd instead use
// prepared statements (prepare() + bind_param()).

// Example pattern:
// $result = $conn->query("SELECT COUNT(*) as count FROM users");
// $row = $result->fetch_assoc();
// $stats['total_users'] = (int) ($row['count'] ?? 0);
// Casting to (int) documents our intent: the final value is an integer.

$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = (int) ($result->fetch_assoc()['count'] ?? 0);

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
$stats['total_students'] = (int) ($result->fetch_assoc()['count'] ?? 0);

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'employer'");
$stats['total_employers'] = (int) ($result->fetch_assoc()['count'] ?? 0);

$result = $conn->query("SELECT COUNT(*) as count FROM jobs");
$stats['total_jobs'] = (int) ($result->fetch_assoc()['count'] ?? 0);

$result = $conn->query("SELECT COUNT(*) as count FROM applications");
$stats['total_applications'] = (int) ($result->fetch_assoc()['count'] ?? 0);

// Fetch recent users and jobs to show quick activity. These are small LIMITed queries
// suitable for dashboard previews. For larger datasets use pagination or async loading.
$recent_users = [];
// Fetch a small set of recent users for the dashboard preview. We store the
// results in a PHP array so the template can iterate over them and render
// HTML server-side. This avoids an extra XHR for the initial page load.
$result = $conn->query("SELECT id, username, email, role, createdAt FROM users ORDER BY createdAt DESC LIMIT 10");
while ($row = $result->fetch_assoc()) {
  // Each $row is an associative array keyed by column name. We push it into
  // the $recent_users array to be used below when rendering the table.
  $recent_users[] = $row;
}

$recent_jobs = [];
// Recent jobs with a LEFT JOIN to the employers table so we can show the
// company name when available. LEFT JOIN ensures jobs without a matching
// employer row won't be dropped.
$result = $conn->query(
  "SELECT j.id, j.title, e.company_name as company, j.location, j.createdAt as posted_date 
  FROM jobs j 
  LEFT JOIN employers e ON j.employer_id = e.id 
  ORDER BY j.createdAt DESC 
  LIMIT 10"
);
while ($row = $result->fetch_assoc()) {
  $recent_jobs[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /> <!-- Character encoding -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <!-- Responsive viewport -->
  <title>Admin Dashboard | Career Connect Hub</title>
  <link rel="stylesheet" href="../css/global.css" /> <!-- Global site styles -->
  <link rel="stylesheet" href="../css/responsive.css" /> <!-- Responsive layout -->
  <style> /* Admin dashboard specific styles */
    .admin-dashboard {
      padding: 40px 20px;
      max-width: 1400px;
      margin: 0 auto;
    }
    .admin-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    .stat-card {
      background: var(--card-bg);
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-left: 4px solid;
    }
    /* Colored left borders for different stat types */
    .stat-card.users { border-left-color: #3b82f6; } /* Blue for users */
    .stat-card.students { border-left-color: #10b981; } /* Green for students */
    .stat-card.employers { border-left-color: #f59e0b; } /* Orange for employers */
    .stat-card.jobs { border-left-color: #8b5cf6; } /* Purple for jobs */
    .stat-card.applications { border-left-color: #ec4899; } /* Pink for applications */
    /* Large number display in stat cards */
    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      margin: 10px 0;
    }
    /* Label text in stat cards */
    .stat-label {
      color: var(--text-secondary);
      font-size: 0.9rem;
    }
    /* Tab navigation bar */
    .admin-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      border-bottom: 2px solid var(--border-color);
    }
    /* Individual tab button styling */
    .admin-tab {
      padding: 12px 24px;
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1rem;
      color: var(--text-secondary);
      border-bottom: 3px solid transparent; /* Hidden by default */
      transition: all 0.3s; /* Smooth hover/active transitions */
    }
    /* Active tab styling (blue underline) */
    .admin-tab.active {
      color: var(--linkedin-blue);
      border-bottom-color: var(--linkedin-blue);
    }
    /* Hover state for tabs */
    .admin-tab:hover {
      color: var(--text-primary);
    }
    /* Tab content containers (hidden by default) */
    .tab-content {
      display: none;
    }
    /* Show active tab content */
    .tab-content.active {
      display: block;
    }
    /* Data table container styling */
    .data-table {
      background: var(--card-bg);
      border-radius: 12px;
      overflow: hidden; /* Clip rounded corners */
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    /* Full-width table with collapsed borders */
    .data-table table {
      width: 100%;
      border-collapse: collapse;
    }
    /* Table header styling (blue background) */
    .data-table th {
      background: var(--linkedin-blue);
      color: white;
      padding: 15px;
      text-align: left;
      font-weight: 600;
    }
    /* Table cell styling */
    .data-table td {
      padding: 12px 15px;
      border-bottom: 1px solid var(--border-color);
    }
    /* Hover effect on table rows */
    .data-table tr:hover {
      background: rgba(10, 102, 194, 0.05); /* Subtle blue highlight */
    }
    .action-btn {
      padding: 6px 12px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      font-size: 0.85rem;
      margin-right: 5px;
    }
    /* Color variants for action buttons */
    .btn-view { background: #3b82f6; color: white; } /* Blue for view */
    .btn-delete { background: #ef4444; color: white; } /* Red for delete */
    .btn-edit { background: #10b981; color: white; } /* Green for edit */
    
    /* WebSocket notification toast (real-time alerts) */
    .notification-toast {
      position: fixed;
      top: 80px;
      right: 20px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 16px 24px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 1000; /* Above other content */
      animation: slideIn 0.3s ease-out; /* Slide in animation */
      max-width: 400px;
      display: none; /* Hidden until triggered */
    }
    .notification-toast.show { display: block; } /* Show when triggered */
    /* Notification type variants */
    .notification-toast.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); } /* Green gradient */
    .notification-toast.warning { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); } /* Pink/yellow gradient */
    /* Notification title styling */
    .notification-toast h4 {
      margin: 0 0 8px 0;
      font-size: 16px;
      font-weight: 600;
    }
    /* Notification message text */
    .notification-toast p {
      margin: 0;
      font-size: 14px;
      opacity: 0.95;
    }
    /* Keyframe animation for toast sliding in from right */
    @keyframes slideIn {
      from { transform: translateX(400px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    /* WebSocket connection status indicator (bottom-right corner) */
    .ws-status {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: rgba(0,0,0,0.8); /* Dark semi-transparent background */
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 12px;
      display: flex;
      align-items: center;
      gap: 8px;
      z-index: 999; /* Below notifications */
    }
    /* Status indicator dot */
    .ws-status .indicator {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #ccc; /* Gray when disconnected */
    }
    /* Green pulsing dot when connected */
    .ws-status.connected .indicator { background: #4CAF50; animation: pulse 2s infinite; }
    /* Pulsing animation for connection indicator */
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }
  </style>
</head>
<body>
  <?php include_once __DIR__ . '/../includes/navibar.php'; ?> <!-- Include navigation bar -->

<main class="admin-dashboard">
  <!-- Dashboard header: title and logged-in user -->
  <div class="admin-header">
    <div>
      <h1 style="font-size: 2.5rem; margin-bottom: 5px;">🛡️ Admin Dashboard</h1>
      <p style="color: var(--text-secondary);">Monitor and manage the entire platform</p>
    </div>
    <div>
      <!-- Display current admin name (XSS-safe) -->
      <span style="color: var(--text-secondary);">Logged in as: <strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong></span>
    </div>
  </div>

  <!-- Statistics Cards Grid -->
  <div class="stats-grid">
    <!-- Total users card (blue) -->
    <div class="stat-card users">
      <div class="stat-label">👥 Total Users</div>
      <div class="stat-number"><?= $stats['total_users'] ?></div>
    </div>
    <!-- Students card (green) -->
    <div class="stat-card students">
      <div class="stat-label">🎓 Students</div>
      <div class="stat-number"><?= $stats['total_students'] ?></div>
    </div>
    <!-- Employers card (orange) -->
    <div class="stat-card employers">
      <div class="stat-label">🏢 Employers</div>
      <div class="stat-number"><?= $stats['total_employers'] ?></div>
    </div>
    <!-- Jobs card (purple) -->
    <div class="stat-card jobs">
      <div class="stat-label">💼 Active Jobs</div>
      <div class="stat-number"><?= $stats['total_jobs'] ?></div>
    </div>
    <!-- Applications card (pink) -->
    <div class="stat-card applications">
      <div class="stat-label">📋 Applications</div>
      <div class="stat-number"><?= $stats['total_applications'] ?></div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div style="margin: 20px 0; display: flex; gap: 15px; flex-wrap: wrap;">
    <!-- Link to import external jobs from API -->
    <a href="/career_hub/pages/import-jobs.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); transition: transform 0.2s;">
      <span>🌍</span>
      <span>Import Jobs from API</span>
    </a>
    <!-- Link to clean up old/invalid jobs -->
    <a href="/career_hub/cleanup_jobs.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #fc8181 0%, #f56565 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 15px rgba(252, 129, 129, 0.3); transition: transform 0.2s;">
      <span>🧹</span>
      <span>Clean Up Jobs</span>
    </a>
  </div>

  <!-- Tab Navigation Buttons -->
  <div class="admin-tabs">
    <!-- Tab buttons to switch between views -->
    <button class="admin-tab active" onclick="showTab('users', event)">👥 All Users</button>
    <button class="admin-tab" onclick="showTab('jobs', event)">💼 All Jobs</button>
    <button class="admin-tab" onclick="showTab('applications', event)">📋 Applications</button>
    <button class="admin-tab" onclick="showTab('recent')">🕒 Recent Activity</button>
  </div>

  <!-- Tab Contents (only one visible at a time) -->
  <!-- Users tab: dynamically loaded via JavaScript -->
  <div id="users-tab" class="tab-content active">
    <div class="data-table">
      <div id="users-data">Loading users...</div>
    </div>
  </div>

  <!-- Jobs tab: dynamically loaded via JavaScript -->
  <div id="jobs-tab" class="tab-content">
    <div class="data-table">
      <div id="jobs-data">Loading jobs...</div>
    </div>
  </div>

  <!-- Applications tab: dynamically loaded via JavaScript -->
  <div id="applications-tab" class="tab-content">
    <div class="data-table">
      <div id="applications-data">Loading applications...</div>
    </div>
  </div>

  <!-- Recent activity tab: pre-rendered from PHP -->
  <div id="recent-tab" class="tab-content">
    <!-- Recent users section -->
    <h3>Recent Users</h3>
    <div class="data-table" style="margin-bottom: 30px;">
      <table>
        <thead>
          <tr> <!-- Table headers -->
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_users as $user): ?> <!-- Loop through recent users array -->
            <tr>
              <td><?= $user['id'] ?></td>
              <td><?= htmlspecialchars($user['username']) ?></td> <!-- XSS-safe username -->
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><span style="padding: 4px 8px; background: #dbeafe; color: #1e40af; border-radius: 4px; font-size: 0.85rem;"><?= htmlspecialchars($user['role']) ?></span></td>
              <td><?= date('M d, Y', strtotime($user['createdAt'])) ?></td> <!-- Format date -->
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Recent jobs section -->
    <h3>Recent Jobs</h3>
    <div class="data-table">
      <table>
        <thead>
          <tr> <!-- Table headers -->
            <th>ID</th>
            <th>Title</th>
            <th>Company</th>
            <th>Location</th>
            <th>Posted</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_jobs as $job): ?> <!-- Loop through recent jobs array -->
            <tr>
              <td><?= $job['id'] ?></td>
              <td><?= htmlspecialchars($job['title']) ?></td>
              <td><?= htmlspecialchars($job['company']) ?></td>
              <td><?= htmlspecialchars($job['location']) ?></td>
              <td><?= date('M d, Y', strtotime($job['posted_date'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include_once __DIR__ . '/../includes/footer.php'; ?> <!-- Include site footer -->

<script> // JavaScript for dynamic functionality
// Load theme
const savedTheme = localStorage.getItem('theme') || 'dark';
document.body.classList.add(savedTheme + '-theme');

// Tab switching
// We accept the DOM event as the second parameter for two reasons:
// 1) inline onclick handlers can provide the event object; passing it
//    explicitly makes the function deterministic across environments.
// 2) it allows us to read event.target to style the clicked button.
function showTab(tabName, evt) {
  // Hide all tab content containers by removing the "active" class.
  document.querySelectorAll('.tab-content').forEach(tab => {
    tab.classList.remove('active');
  });

  // Remove the active state from each tab button so we can set it only on the
  // clicked button. We do this before adding the new active class to ensure
  // only one tab is visually active at a time.
  document.querySelectorAll('.admin-tab').forEach(btn => {
    btn.classList.remove('active');
  });

  // Show the selected tab content by id. Using getElementById is faster than
  // querySelector for simple id lookups.
  const content = document.getElementById(tabName + '-tab');
  if (content) content.classList.add('active');

  // Safely mark the clicked button as active. evt may be undefined if the
  // function is called programmatically, so check before using it.
  if (evt && evt.target) evt.target.classList.add('active');

  // Load data for the selected view. We keep the loading functions idempotent
  // (safe to call multiple times) and they each handle rendering empty states.
  if (tabName === 'users') loadUsers();
  if (tabName === 'jobs') loadJobs();
  if (tabName === 'applications') loadApplications();
}

// Load all users
async function loadUsers() {
  const container = document.getElementById('users-data');
  container.innerHTML = 'Loading...';
  
  try {
  // Use fetch() to call our admin API endpoint. fetch() returns a Promise
  // that resolves to a Response object. We should always check res.ok to
  // ensure the HTTP status code is in the 200-299 range before parsing JSON.
  const res = await fetch('../api/admin_users.php', { credentials: 'same-origin' });
  if (!res.ok) throw new Error('Network response was not ok: ' + res.status);
  // res.json() also returns a Promise that resolves to the parsed object.
  const data = await res.json();
    
    if (!data.users || data.users.length === 0) {
      container.innerHTML = '<p style="padding: 20px; text-align: center;">No users found</p>';
      return;
    }
    
    container.innerHTML = `
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          ${data.users.map(user => `
            <tr>
              <td>${user.id}</td>
              <td>${user.username || 'N/A'}</td>
              <td>${user.email}</td>
              <td><span style="padding: 4px 8px; background: ${user.role === 'employer' ? '#fef3c7' : '#dbeafe'}; color: ${user.role === 'employer' ? '#92400e' : '#1e40af'}; border-radius: 4px; font-size: 0.85rem;">${user.role}</span></td>
              <td>${new Date(user.createdAt).toLocaleDateString()}</td>
              <td>
                <button class="action-btn btn-view" onclick="viewUser(${user.id})">View</button>
                <button class="action-btn btn-delete" onclick="deleteUser(${user.id})">Delete</button>
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;
  } catch (error) {
    container.innerHTML = '<p style="padding: 20px; color: red;">Error loading users</p>';
  }
}

// Load all jobs
async function loadJobs() {
  const container = document.getElementById('jobs-data');
  container.innerHTML = 'Loading...'; // Show loading message
  
  try {
  // A real-world app would include proper authorization checks on the API.
  // We pass credentials: 'same-origin' to ensure cookies (session) are sent.
  const res = await fetch('../api/admin_jobs.php', { credentials: 'same-origin' }); // Call admin jobs API
  if (!res.ok) throw new Error('Network response was not ok: ' + res.status);
  const data = await res.json(); // Parse response
    
    // Handle empty results
    if (!data.jobs || data.jobs.length === 0) {
      container.innerHTML = '<p style="padding: 20px; text-align: center;">No jobs found</p>';
      return;
    }
    
    // Build HTML table from job data
    container.innerHTML = `
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Company</th>
            <th>Location</th>
            <th>Type</th>
            <th>Posted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          ${data.jobs.map(job => `
            <tr>
              <td>${job.id}</td>
              <td>${job.title}</td>
              <td>${job.company}</td>
              <td>${job.location}</td>
              <td>${job.type || 'N/A'}</td>
              <td>${new Date(job.posted_date).toLocaleDateString()}</td>
              <td>
                <button class="action-btn btn-view" onclick="window.location.href='jobs.php?id=${job.id}'">View</button>
                <button class="action-btn btn-delete" onclick="deleteJob(${job.id})">Delete</button>
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;
  } catch (error) {
    // Show error if API call fails
    container.innerHTML = '<p style="padding: 20px; color: red;">Error loading jobs</p>';
  }
}

// Load all applications
async function loadApplications() {
  const container = document.getElementById('applications-data');
  container.innerHTML = 'Loading...'; // Show loading message
  
  try {
  const res = await fetch('../api/admin_applications.php', { credentials: 'same-origin' }); // Call admin applications API
  if (!res.ok) throw new Error('Network response was not ok: ' + res.status);
  const data = await res.json(); // Parse response
    
    // Handle empty results
    if (!data.applications || data.applications.length === 0) {
      container.innerHTML = '<p style="padding: 20px; text-align: center;">No applications found</p>';
      return;
    }
    
    // Build HTML table from application data
    container.innerHTML = `
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Applicant</th>
            <th>Job Title</th>
            <th>Company</th>
            <th>Applied</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          ${data.applications.map(app => `
            <tr>
              <td>${app.id}</td>
              <td>${app.applicant_name || 'N/A'}</td>
              <td>${app.job_title || 'N/A'}</td>
              <td>${app.company || 'N/A'}</td>
              <td>${new Date(app.applied_at).toLocaleDateString()}</td>
              <td><span style="padding: 4px 8px; background: #d1fae5; color: #065f46; border-radius: 4px; font-size: 0.85rem;">${app.status || 'Pending'}</span></td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;
  } catch (error) {
    // Show error if API call fails
    container.innerHTML = '<p style="padding: 20px; color: red;">Error loading applications</p>';
  }
}

// View user details (placeholder function)
function viewUser(id) {
  alert('View user details for ID: ' + id); // TODO: implement proper user detail view
}

// Delete user (placeholder function)
function deleteUser(id) {
  if (confirm('Are you sure you want to delete this user?')) {
    // TODO: Implement delete functionality via API
    alert('Delete user ID: ' + id);
  }
}

// Delete job (placeholder function)
function deleteJob(id) {
  if (confirm('Are you sure you want to delete this job?')) {
    // TODO: Implement delete functionality via API
    alert('Delete job ID: ' + id);
  }
}

// Load initial data (users tab is active by default)
loadUsers();

// WebSocket Integration for Real-Time Admin Notifications
const wsClient = new WebSocketClient('ws://localhost:8080'); // Create WebSocket client connection
const statusDiv = document.getElementById('wsStatus'); // Get status indicator element
const statusText = statusDiv?.querySelector('.status-text'); // Get status text element

// Handle WebSocket connection established
wsClient.on('connected', () => {
  if (statusDiv) statusDiv.className = 'ws-status connected'; // Update UI to show connected state
  if (statusText) statusText.textContent = 'Live'; // Update text to "Live"
  console.log('✓ Admin WebSocket connected'); // Log connection success
  
  // Subscribe to admin-specific notification channels for real-time updates
  wsClient.subscribe('admin_notifications'); // General admin alerts
  wsClient.subscribe('new_users'); // New user registrations
  wsClient.subscribe('new_jobs'); // New job postings
  wsClient.subscribe('applications'); // Job application activity
  wsClient.subscribe('reports'); // System reports
});

// Handle WebSocket disconnection
wsClient.on('disconnected', () => {
  if (statusDiv) statusDiv.className = 'ws-status'; // Remove connected styling
  if (statusText) statusText.textContent = 'Offline'; // Update text to "Offline"
});

// Handle incoming notifications from WebSocket server
wsClient.on('notification', (data) => {
  console.log('Admin notification:', data); // Log notification details
  showNotification(data); // Display toast notification
  
  // Auto-refresh data tables based on notification type
  if (data.type === 'new_user') {
    loadUsers(); // Refresh users list
  } else if (data.type === 'new_job') {
    loadJobs(); // Refresh jobs list
  } else if (data.type === 'new_application') {
    // Refresh applications if needed (placeholder)
  }
});

// Establish WebSocket connection
wsClient.connect();

// Display notification toast to user
function showNotification(data) {
  const toast = document.getElementById('notificationToast'); // Get toast element
  if (!toast) return; // Exit if toast element not found
  
  // Extract message data with fallback defaults
  const message = data.message || data.data?.message || 'New notification';
  const title = data.title || data.data?.title || 'Admin Alert';
  const type = data.type || 'info'; // Notification type (success, warning, info)
  
  // Build toast HTML (using XSS-safe escapeHtml function)
  toast.innerHTML = `
    <h4>${escapeHtml(title)}</h4>
    <p>${escapeHtml(message)}</p>
  `;
  
  // Show toast with appropriate styling
  toast.className = `notification-toast show ${type}`;
  // Auto-hide toast after 6 seconds
  setTimeout(() => toast.classList.remove('show'), 6000);
}

// Escape HTML to prevent XSS attacks in notifications
function escapeHtml(text) {
  const div = document.createElement('div'); // Create temporary div
  div.textContent = text; // Set as text (auto-escapes HTML)
  return div.innerHTML; // Return escaped HTML
}
</script>

<!-- WebSocket notification elements (UI components for real-time alerts) -->
<div id="notificationToast" class="notification-toast"></div> <!-- Toast container for notifications -->
<div id="wsStatus" class="ws-status"> <!-- WebSocket connection status indicator -->
  <span class="indicator"></span> <!-- Colored dot (green when connected) -->
  <span class="status-text">Connecting...</span> <!-- Status text -->
</div>
<!-- Include WebSocket client library -->
<script src="../js/websocket-client.js"></script>

</body>
</html>
