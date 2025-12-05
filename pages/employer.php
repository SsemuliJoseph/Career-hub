<?php
// Path: pages/employer.php
// This file renders the Employer Dashboard. Below are short, focused
// inline explanations for each PHP block so you can learn what the code
// is doing and why certain built-ins are used.

// Start or resume the PHP session. PHP sessions store per-user data on the
// server and a small session id cookie is sent to the browser. Calling
// session_start() is required before reading/writing the $_SESSION array.
session_start();

// Include an authentication helper that ensures the user is logged in and
// may provide convenience functions. require_once stops execution if the
// file cannot be loaded, which is useful for security-critical includes.
require_once __DIR__ . '/../includes/auth_check.php';

// Include the database connection. Typically this file creates a $conn
// mysqli object that's reused below. Using a single shared connection is
// common in simple apps but for testable code you'd inject dependencies.
require_once __DIR__ . '/../includes/db.php';

// --- Authorization check ---
// Use the $_SESSION superglobal to read authenticated user info. The null
// coalescing operator (??) returns the right side if the left is not set.
$role = $_SESSION['user']['role'] ?? '';
// Only employers and admins may access this dashboard. header() sends an
// HTTP redirect. Always call exit immediately after header('Location: ...')
// to avoid running further code on the page.
if ($role !== 'employer' && $role !== 'admin') {
  header('Location: /pages/home.php');
  exit;
}

// Extract commonly used session values. Remember to escape values that will
// be echoed into HTML to prevent XSS (we use htmlspecialchars below).
$userId = $_SESSION['user']['id'];
$userName = htmlspecialchars($_SESSION['user']['name'] ?? 'Employer', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

// --- Fetch employer profile ---
// We use prepared statements to safely run queries with user-supplied data.
// prepare() returns false on error; always check the return value.
$employerProfile = null;
try {
  $stmt = $conn->prepare("SELECT company_name, company_logo, company_description, industry FROM employers WHERE user_id = ?");
  if ($stmt) {
    // bind_param requires a types string. 'i' means integer.
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    // get_result() requires the mysqlnd driver; it returns a mysqli_result
    $result = $stmt->get_result();
    $employerProfile = $result->fetch_assoc(); // associative array or null
    $stmt->close();
  }
} catch (Exception $e) {
  // If the table doesn't exist or another DB error happens, we fallback
  // to null. In production, log $e->getMessage() to an error log.
  $employerProfile = null;
}

// --- Profile completion calculation ---
// We build a small array of fields and count how many are non-empty. This
// demonstrates array handling, loops, and the empty() function.
$fields = [
  $employerProfile['company_name'] ?? '',
  $employerProfile['company_logo'] ?? '',
  $employerProfile['company_description'] ?? '',
  $employerProfile['industry'] ?? ''
];
$filledFields = 0;
foreach ($fields as $field) {
  if (!empty($field)) {
    $filledFields++;
  }
}
// round() converts the computed fraction to an integer percent.
$profileCompletion = round(($filledFields / max(1, count($fields))) * 100);

// --- Count posted jobs ---
$jobCount = 0;
try {
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM jobs WHERE employer_id = ?");
  if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $jobCount = (int) ($result->fetch_assoc()['count'] ?? 0);
    $stmt->close();
  }
} catch (Exception $e) {
  $jobCount = 0; // fallback on error
}

// --- Count applications ---
$appCount = 0;
try {
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM applications INNER JOIN jobs ON applications.job_id = jobs.id WHERE jobs.employer_id = ?");
  if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appCount = (int) ($result->fetch_assoc()['count'] ?? 0);
    $stmt->close();
  }
} catch (Exception $e) {
  $appCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>Career Connect Hub - Employer Dashboard</title>
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/responsive.css">
  <link rel="stylesheet" href="../css/employerdashboard.css">
  <style>
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

<body>
  <?php include_once __DIR__ . '/../includes/navbar.php'; ?>

  <main class="page-content-wrapper">
    <h1>Employer Dashboard</h1>
    <p>Welcome, <?= $userName ?>.</p>

    <!-- Dashboard Cards -->
    <section class="dashboard-grid" style="margin-bottom: 40px;">
      <div class="grid-layout" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        
        <!-- Profile Completion Card -->
        <div class="card-form">
          <h2>Company Profile (<span><?= $profileCompletion ?>%</span>)</h2>
          <div class="progress-bar" style="background: var(--border-color); height: 10px; border-radius: 10px; overflow: hidden; margin: 15px 0;">
            <div style="width: <?= $profileCompletion ?>%; background: linear-gradient(90deg, #0a66c2 0%, #5ba3d0 100%); height: 100%; transition: width 0.5s ease;"></div>
          </div>
          <p class="text-medium"><?= $profileCompletion < 100 ? 'Complete your company profile to attract more candidates.' : 'Your company profile is complete! 🎉' ?></p>
          <a href="employer-profile.php" class="btn btn-secondary">Manage Profile</a>
        </div>

        <!-- Posted Jobs Card -->
        <div class="card-form">
          <h2>Posted Jobs (<?= $jobCount ?>)</h2>
          <p class="text-medium">You have <?= $jobCount ?> active job posting<?= $jobCount != 1 ? 's' : '' ?>.</p>
          <a href="#create-job" class="btn btn-primary">Post New Job</a>
        </div>

        <!-- Applications Card -->
        <div class="card-form">
          <h2>Applications (<?= $appCount ?>)</h2>
          <p class="text-medium">You have received <?= $appCount ?> application<?= $appCount != 1 ? 's' : '' ?>.</p>
          <a href="employer-applicants.php" class="btn btn-secondary">View Applications</a>
        </div>
      </div>
    </section>

    <section id="create-job">
      <h2>Create a New Job</h2>
    <form id="createJobForm">
      <input type="text" name="title" placeholder="Job Title" required><br>
      <input type="text" name="company" placeholder="Company" required><br>
      <input type="text" name="location" placeholder="Location" required><br>
      <textarea name="description" placeholder="Job Description" required></textarea><br>
      <textarea name="requirements" placeholder="Requirements" required></textarea><br>
      <button type="submit" class="btn btn-primary">Create Job</button>
    </form>
    <p id="statusMsg"></p>
    </section>

    <section>
      <h2>Candidate Applications</h2>
      <p class="text-medium">Showing 12 candidates for "Senior Software Engineer"</p>

      <div class="candidate-card" data-candidate-id="1">
        <div class="candidate-avatar">JD</div>
        <div class="candidate-info">
          <h3>Mariam Ampaire</h3>
          <p class="text-medium">Applied for: Frontend Developer</p>
          <p class="text-medium">Skills: React, JavaScript, UI/UX</p>
        </div>
        <div class="card-actions">
          <button class="btn btn-secondary download-btn" data-filename="Jane_Doe_CV.pdf">View CV</button>
          <button class="btn btn-primary">Schedule Interview</button>
        </div>
      </div>

      <div class="candidate-card" data-candidate-id="2">
        <div class="candidate-avatar">MS</div>
        <div class="candidate-info">
          <h3>Michael Walukaga</h3>
          <p class="text-medium">Applied for: Project Manager</p>
          <p class="text-medium">Skills: SCRUM, Budgeting, Leadership</p>
        </div>
        <div class="card-actions">
          <button class="btn btn-secondary download-btn" data-filename="Michael_Scott_CV.pdf">View CV</button>
          <button class="btn btn-primary">Schedule Interview</button>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="page-content-wrapper footer-content">
      <p>© 2025 CaReeR CoNNect HuB. All rights reserved.</p>
      <div class="footer-social">
        <span> For more info </span>
        <a href="https://www.instagram.com/my.preciouspabz" target="_blank" aria-label="Instagram" class="social-link">
          <!-- Instagram icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
            <circle cx="17.5" cy="6.5" r="1.5"/>
          </svg>
        </a>
        <a href="https://github.com/pabz123" target="_blank" aria-label="GitHub" class="social-link">
          <!-- GitHub icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 19c-4.5 1.5-4.5-2.5-6-3m12 5v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 18 2.77 5.07 5.07 0 0 0 17.91 0S16.73.35 14 2.48a13.38 13.38 0 0 0-8 0C3.27.35 2.09 0 2.09 0A5.07 5.07 0 0 0 2 2.77 5.44 5.44 0 0 0 .5 8.5c0 5.42 3.3 6.61 6.44 7a3.37 3.37 0 0 0-.94 2.61V21"/>
          </svg>
        </a>
        <a href="https://www.linkedin.com/in/pabz" target="_blank" aria-label="LinkedIn" class="social-link">
          <!-- LinkedIn icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2h-1v9h-4v-9h-4v9H3V9h4v1.2a4.6 4.6 0 0 1 4-2.2z"/>
            <rect x="2" y="9" width="4" height="12"/>
            <circle cx="4" cy="4" r="2"/>
          </svg>
        </a>
      </div>
    </div>
  </footer>

  <!-- ✅ Include common.js for theme + logout -->
  <script src="/js/common.js"></script>

  <!-- Optional: Page-specific script -->
  <script>
    document.querySelectorAll('.download-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const filename = btn.getAttribute('data-filename');
        const card = btn.closest('.candidate-card');
        const name = (card && card.querySelector('h3')) ? card.querySelector('h3').textContent : 'Candidate';
        const content = `${name}\n\nThis is a simulated CV for ${name}. Replace with a real file in production.`;
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
      });
    });
	// Path: /js/common.js
// Shared script: theme toggle + optional logout handling.
// Safe to include on every page (idempotent).

(function () {
  // run only once even if script accidentally included twice
  if (window.__careerConnectCommonLoaded) return;
  window.__careerConnectCommonLoaded = true;

  document.addEventListener("DOMContentLoaded", () => {
    const body = document.body;
    const themeToggle = document.getElementById("theme-toggle");
    const logoutBtn = document.getElementById("logout-btn");

    // --- Theme initialization ---
    // We support three states:
    //  - explicit "light" in localStorage -> light theme
    //  - explicit "dark" in localStorage -> dark theme
    //  - nothing -> respect prefers-color-scheme or default to dark
    const stored = localStorage.getItem("theme");
    const prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;

    function applyTheme(t) {
      body.classList.remove("light-theme", "dark-theme");
      if (t === "light") body.classList.add("light-theme");
      else body.classList.add("dark-theme");
      // update button text if present
      if (themeToggle) themeToggle.textContent = t === "light" ? "🌙 Dark Mode" : "☀️ Light Mode";
    }

    if (stored === "light" || stored === "dark") {
      applyTheme(stored);
    } else {
      applyTheme(prefersDark ? "dark" : "light");
    }

    // --- Theme toggle click handler ---
    if (themeToggle) {
      themeToggle.addEventListener("click", () => {
        const isLight = body.classList.contains("light-theme");
        const newTheme = isLight ? "dark" : "light";
        applyTheme(newTheme);
        localStorage.setItem("theme", newTheme);
      });
    }

    // --- Optional: logout button (best-effort, non-blocking) ---
    if (logoutBtn) {
      logoutBtn.addEventListener("click", async (e) => {
        e && e.preventDefault && e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
          try {
            await fetch("/career_hub/api/auth/logout.php", { method: "POST", credentials: "include" });
          } catch (err) {
            console.error('Logout failed:', err);
          }
          // cleanup local-only state
          localStorage.clear();
          localStorage.removeItem("token");
          // preserve theme preference — do not clear theme here
          window.location.href = "/career_hub/pages/login.php";
        }
      });
    }
  });
})();
document.getElementById("createJobForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const res = await fetch("../api/create_job.php", { method: "POST", body: formData });
  const data = await res.json();
  document.getElementById("statusMsg").textContent = data.message;
  if (data.success) e.target.reset();
});

// WebSocket Integration
const wsClient = new WebSocketClient('ws://localhost:8080');
const statusDiv = document.getElementById('wsStatus');
const statusText = statusDiv?.querySelector('.status-text');

wsClient.on('connected', () => {
  if (statusDiv) statusDiv.className = 'ws-status connected';
  if (statusText) statusText.textContent = 'Live';
  console.log('✓ WebSocket connected');
  
  // Subscribe to employer-relevant channels
  wsClient.subscribe('applications');
  wsClient.subscribe('jobs');
  wsClient.subscribe('employer_notifications');
});

wsClient.on('disconnected', () => {
  if (statusDiv) statusDiv.className = 'ws-status';
  if (statusText) statusText.textContent = 'Offline';
});

wsClient.on('notification', (data) => {
  console.log('Notification received:', data);
  showNotification(data);
  
  // Refresh application count if it's an application notification
  if (data.type === 'new_application') {
    const appCountEl = document.querySelector('.dashboard-grid .card-form h2');
    if (appCountEl && appCountEl.textContent.includes('Applications')) {
      const currentCount = parseInt(appCountEl.textContent.match(/\d+/)?.[0] || 0);
      appCountEl.textContent = `Applications (${currentCount + 1})`;
    }
  }
});

wsClient.connect();

function showNotification(data) {
  const toast = document.getElementById('notificationToast');
  if (!toast) return;
  
  const message = data.message || data.data?.message || 'New notification';
  const title = data.title || data.data?.title || 'Notification';
  const type = data.type || 'info';
  
  toast.innerHTML = `
    <h4>${escapeHtml(title)}</h4>
    <p>${escapeHtml(message)}</p>
  `;
  
  toast.className = `notification-toast show ${type}`;
  setTimeout(() => toast.classList.remove('show'), 5000);
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

  </script>
  
  <!-- WebSocket notification elements -->
  <div id="notificationToast" class="notification-toast"></div>
  <div id="wsStatus" class="ws-status">
    <span class="indicator"></span>
    <span class="status-text">Connecting...</span>
  </div>
  <script src="../js/websocket-client.js"></script>
  
</body>
</html>
