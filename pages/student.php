<?php
/**
 * pages/student.php - Student Dashboard
 * Teaching: This page demonstrates a role-based dashboard pattern.
 * - ini_set('display_errors', 1) and error_reporting(E_ALL) enable error reporting for debugging.
 *   In production, these should be disabled for security (errors can leak sensitive info).
 * - We include three essential files:
 *   - auth_check.php: ensures the user is authenticated (logged in).
 *   - helpers.php: provides helper functions like getUserName().
 *   - db.php: provides the global $conn mysqli database connection.
 * - We read the user's ID from $_SESSION, which is a superglobal populated after login.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

// Teaching: Extract the current user's ID from the session superglobal.
// The session was populated when the user logged in, and it persists across page requests.
$userId = $_SESSION['user']['id'];

// Teaching: Use a prepared statement to safely fetch user data.
// LEFT JOIN is used here because a user may exist without a student profile row.
// The '?' placeholder will be replaced with $userId (bound below).
$stmt = $conn->prepare("
    SELECT 
        u.username,
        u.email,
        sp.phone,
        sp.profilePic,
        sp.education,
        sp.skills
    FROM users AS u
    LEFT JOIN student_profiles AS sp ON u.id = sp.id
    WHERE u.id = ?
");
// bind_param('i', $userId) binds $userId as an integer ('i').
// This is crucial for SQL injection prevention: the value is escaped by the driver.
$stmt->bind_param('i', $userId);
$stmt->execute(); // Execute the prepared statement
$result = $stmt->get_result(); // Get the result set (requires mysqlnd)
$userData = $result->fetch_assoc(); // Fetch one row as an associative array
$stmt->close(); // Close the statement to free resources

// Teaching: Calculate profile completion percentage.
// We define an array of field names we consider "complete" when filled.
// We iterate (foreach) over this array and count how many are non-empty.
// The !== check for the default avatar ensures we don't count it as "filled".
// Finally, we compute a percentage: (filled / total) * 100 and round it.
$fields = ['name', 'email', 'phone', 'profile_image', 'education', 'skills'];
$filledFields = 0;
foreach ($fields as $field) {
    if (!empty($userData[$field]) && $userData[$field] !== '/uploads/profile/default-avatar.png') {
        $filledFields++;
    }
}
// round() is a built-in PHP function that rounds a float to the nearest integer.
$profileCompletion = round(($filledFields / count($fields)) * 100);

// Teaching: Count the number of applications this student has submitted.
// We use a prepared statement again for safe parameter binding.
// COUNT(*) is a SQL aggregate function that returns the number of rows.
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM applications WHERE studentId = ?");
$stmt->bind_param('i', $userId); // Bind the student ID as an integer
$stmt->execute();
$result = $stmt->get_result();
// Fetch the result row and immediately access the 'count' column.
$appCount = $result->fetch_assoc()['count'];
$stmt->close(); // Always close statements to free resources
?>

<!DOCTYPE html> 
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="../css/student.css">
    <style>
      /* WebSocket notification toast */
      .notification-toast {
        position: fixed;
        top: 80px;
        right: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
        max-width: 400px;
        display: none;
      }
      .notification-toast.show { display: block; }
      .notification-toast.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
      .notification-toast.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
      .notification-toast.warning { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
      .notification-toast h4 {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 600;
      }
      .notification-toast p {
        margin: 0;
        font-size: 14px;
        opacity: 0.95;
      }
      @keyframes slideIn {
        from {
          transform: translateX(400px);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
      
      /* WebSocket status indicator */
      .ws-status {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 999;
      }
      .ws-status .indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ccc;
      }
      .ws-status.connected .indicator { background: #4CAF50; animation: pulse 2s infinite; }
      .ws-status.connecting .indicator { background: #FFC107; }
      @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
      }
    </style>

  </head>

  <body class="student-page">

  
    <?php include_once __DIR__ . '/../includes/navbar.php'; ?>


        <main class="centre-container">
    <p>Welcome, <?= getUserName() ?>.</p>




          <section class="dashboard-grid">
            <div class="grid-layout">
              <div class="card-form">
                <h2>Applied Jobs (<?= $appCount ?>)</h2>
                <p class="text-medium">You have <?= $appCount ?> active job application<?= $appCount != 1 ? 's' : '' ?>. Keep track of their status.</p>
                <a href="my-applications.php" class="btn btn-secondary">View Applications</a>
              </div>

              <div class="card-form">
                <h2>Interview Invites (2)</h2>
                <p class="text-medium">Prepare for your interviews with our resources.</p>
                <a href="interview.php" class="btn btn-primary">Prep Now</a>
              </div>

              <div class="card-form" id="profile-progress-card">
  <h2>Profile Completion (<span id="profile-percent"><?= $profileCompletion ?>%</span>)</h2>
  <div class="progress-bar">
    <div id="progress-bar-fill" style="width:<?= $profileCompletion ?>%; background: linear-gradient(90deg, #0a66c2 0%, #5ba3d0 100%); height: 100%; border-radius: 10px; transition: width 0.5s ease;"></div>
  </div>
  <p class="text-medium"><?= $profileCompletion < 100 ? 'Update your profile to unlock more opportunities.' : 'Your profile is complete! 🎉' ?></p>
  <a href="student-profile.php" class="btn btn-secondary">Update Profile</a>
</div>

            </div>
          </section>

          <section class="recommendations">
            <h2>Recommended Opportunities</h2>
            <div class="grid-3-col">
              <div class="article-card">
                <div class="card-content">
                  <h3>Junior Software Engineer</h3>
                  <p class="text-medium">Acme Tech Solutions</p>
                  <p>Full-time job opening for final year students or new graduates.</p>
                  <a href="jobs.php" class="btn btn-secondary">View Job</a>
                </div>
              </div>

              <div class="article-card">
                <div class="card-content">
                  <h3>Marketing Intern</h3>
                  <p class="text-medium">Global Brands Inc.</p>
                  <p>Summer internship for marketing and business students.</p>
                  <a href="internship.php" class="btn btn-secondary">View Internship</a>
                </div>
              </div>

              <div class="article-card">
                <div class="card-content">
                  <h3>Financial Analyst Trainee</h3>
                  <p class="text-medium">Wall Street Bank</p>
                  <p>One-year trainee program for finance majors.</p>
                  <a href="jobs.php" class="btn btn-secondary">View Trainee Program</a>
                </div>
              </div>
            </div>
          </section>

          <section class="guidance-resources">
            <h2>Career Guidance</h2>
            <div class="grid-layout">
              <div class="card-form">
                <h3>CV Review Service</h3>
                <p class="text-medium">Upload your CV and get professional feedback within 48 hours.</p>
                <a href="cv.php" class="btn btn-primary">Review CV</a>
              </div>

              <div class="card-form">
                <h3>Interview Simulations</h3>
                <p class="text-medium">Practice common interview scenarios with our AI-powered tool.</p>
                <a href="interview.php" class="btn btn-primary">Start Practice</a>
              </div>
            </div>
          </section>
        </main>

        
    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
 
    
    <!-- WebSocket notification container -->
    <div id="notificationToast" class="notification-toast"></div>
    
    <!-- WebSocket status indicator -->
    <div id="wsStatus" class="ws-status">
      <span class="indicator"></span>
      <span class="status-text">Connecting...</span>
    </div>
    
    <!-- Notification History Panel -->
    <?php include_once __DIR__ . '/../includes/notification_center.php'; ?>
    
    <!-- WebSocket Client -->
    <script src="../js/websocket-client.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Load saved theme
      const savedTheme = localStorage.getItem('theme') || 'dark';
      document.body.classList.add(savedTheme + '-theme');
      
      // Profile completion animation
      const progressBar = document.getElementById('progress-bar-fill');
      if (progressBar) {
        const targetWidth = progressBar.style.width;
        progressBar.style.width = '0%';
        setTimeout(() => {
          progressBar.style.width = targetWidth;
        }, 300);
      }
      
      // Initialize WebSocket for real-time notifications
      const wsClient = new WebSocketClient('ws://localhost:8080');
      const statusDiv = document.getElementById('wsStatus');
      const statusText = statusDiv.querySelector('.status-text');
      
      // Connection status handlers
      wsClient.on('connected', () => {
        statusDiv.className = 'ws-status connected';
        statusText.textContent = 'Live';
        console.log('✓ WebSocket connected');
        
        // Subscribe to relevant channels for students
        wsClient.subscribe('jobs');
        wsClient.subscribe('applications');
        wsClient.subscribe('notifications');
      });
      
      wsClient.on('disconnected', () => {
        statusDiv.className = 'ws-status';
        statusText.textContent = 'Offline';
        console.log('✗ WebSocket disconnected');
      });
      
      wsClient.on('error', (error) => {
        statusDiv.className = 'ws-status';
        statusText.textContent = 'Error';
        console.error('WebSocket error:', error);
      });
      
      // Notification handler
      wsClient.on('notification', (data) => {
        console.log('Received notification:', data);
        showNotification(data);
        
        // Add to notification history if NotificationCenter is available
        if (window.NotificationCenter) {
          window.NotificationCenter.add(data);
        }
        
        // Update UI based on notification type
        if (data.type === 'job_posted') {
          // Could refresh recommended jobs section
          console.log('New job posted:', data.job);
        } else if (data.type === 'application_status') {
          // Could update application count
          console.log('Application status changed:', data);
        }
      });
      
      // Message handler (catch-all)
      wsClient.on('message', (data) => {
        console.log('WebSocket message:', data);
      });
      
      // Connect to WebSocket server
      wsClient.connect();
      
      // Show notification toast
      function showNotification(data) {
        const toast = document.getElementById('notificationToast');
        const message = data.message || data.data?.message || 'New notification';
        const title = data.title || data.data?.title || 'Notification';
        const type = data.type || 'info';
        
        // Set content
        toast.innerHTML = `
          <h4>${escapeHtml(title)}</h4>
          <p>${escapeHtml(message)}</p>
        `;
        
        // Set type class
        toast.className = `notification-toast show ${type}`;
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
          toast.classList.remove('show');
        }, 5000);
        
        // Play sound (optional)
        // new Audio('../assets/notification.mp3').play().catch(() => {});
      }
      
      function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }
      
      // Test notification button (remove in production)
      if (window.location.search.includes('test_ws')) {
        setTimeout(() => {
          showNotification({
            title: 'Test Notification',
            message: 'WebSocket is working! 🎉',
            type: 'success'
          });
        }, 2000);
      }
    });
    </script>
    
  </body>
</html>
