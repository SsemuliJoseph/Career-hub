<?php
/**
 * pages/student-profile.php - Student Profile Viewing/Editing
 * Teaching: This page allows students to view and update their profile information.
 * - session_start() is called at the top to resume the session (enables $_SESSION access).
 * - We require three includes:
 *   - db.php: provides $conn (mysqli connection object).
 *   - auth_check.php: ensures only logged-in users can access this page.
 *   - helpers.php: provides utility functions (if any).
 * - We fetch fresh data from the database to avoid relying on stale session data.
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php'; // ensures logged-in
require_once __DIR__ . '/../includes/helpers.php';

// Teaching: Extract user data from the session and cast the ID to an integer.
// The null coalescing operator (??) provides a fallback empty array if 'user' isn't set.
// Casting with (int) ensures $userId is always an integer, even if session data is missing.
$user = $_SESSION['user'] ?? [];
$userId = (int)($user['id'] ?? 0);

// Teaching: Initialize an associative array with default profile values.
// This ensures that even if the database returns no rows, we have sensible defaults.
// Using an array structure like this makes the template code cleaner (no need for isset checks).
$profileData = [
    'id' => $userId,
    'name' => '',
    'fullName' => '',
    'email' => '',
    'phone' => '',
    'education' => '',
    'skills' => '',
    'profile_image' => '/uploads/profile/default-avatar.png', // Default avatar
    'cv' => ''
];

if ($userId) {
    // Teaching: Fetch basic user info from the users table.
    // We use a prepared statement with bind_param to safely inject $userId.
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId); // 'i' = integer type
    $stmt->execute(); // Execute the query
    $res = $stmt->get_result(); // Get result set (requires mysqlnd driver)
    $u = $res->fetch_assoc(); // Fetch one row as associative array
    
    if ($u) {
        // Merge fetched data into $profileData, casting id to int for clarity.
        $profileData['id'] = (int)$u['id'];
        $profileData['name'] = $u['username'] ?? '';
        $profileData['email'] = $u['email'] ?? '';
    }
    $stmt->close(); // Close statement to free resources
    
    // Teaching: Fetch additional profile fields from student_profiles table.
    // We search by email (which links the two tables). This is a second query because
    // the schema stores extended profile info in a separate table.
    $stmt2 = $conn->prepare("SELECT fullName, phone, education, skills, profilePic, cvFile FROM student_profiles WHERE email = ?");
    $stmt2->bind_param("s", $profileData['email']); // 's' = string type
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $sp = $res2->fetch_assoc();
    
    if ($sp) {
        // Teaching: The ?: operator is the ternary operator (shorthand if-else).
        // Example: $sp['fullName'] ?: $profileData['fullName'] means:
        // "use $sp['fullName'] if truthy, otherwise use the existing value".
        $profileData['fullName'] = $sp['fullName'] ?: $profileData['fullName'];
        $profileData['phone'] = $sp['phone'] ?: $profileData['phone'];
        $profileData['education'] = $sp['education'] ?? '';
        $profileData['skills'] = $sp['skills'] ?? '';
        $profileData['profile_image'] = $sp['profilePic'] ?: $profileData['profile_image'];
        $profileData['cv'] = $sp['cvFile'] ?? '';
    }
    $stmt2->close();
    
    // Teaching: Update the session with merged profile data.
    // array_merge() combines two arrays; the second overwrites keys in the first.
    $_SESSION['user'] = array_merge($_SESSION['user'] ?? [], $profileData);
}

$user = $profileData;
$profilePic = $user['profile_image'];

// Handle success/error messages
$successMsg = '';
$errorMsg = '';
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $successMsg = $_SESSION['profile_success'] ?? 'Profile updated successfully!';
    unset($_SESSION['profile_success']);
}
if (isset($_GET['error']) && $_GET['error'] == '1') {
    $errorMsg = $_SESSION['profile_error'] ?? 'An error occurred. Please try again.';
    unset($_SESSION['profile_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Profile</title>
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/responsive.css">
  <link rel="stylesheet" href="../css/student-profile.css">
</head>

<body class="student-page">
  <?php include_once __DIR__ . '/../includes/navbar.php'; ?>

  <main class="profile-container">
    <h1>My Profile</h1>
    
    <?php if ($successMsg): ?>
    <div class="message success" style="padding: 12px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 6px; margin-bottom: 20px;">
      ✓ <?= htmlspecialchars($successMsg) ?>
    </div>
    <?php endif; ?>
    
    <?php if ($errorMsg): ?>
    <div class="message error" style="padding: 12px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 6px; margin-bottom: 20px;">
      ✗ <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <!-- Profile Completion -->
    <div class="progress-section">
      <label>Profile Completion:</label>
      <div class="progress-bar">
        <div id="completion-fill" class="progress-fill" style="width: 0%;"></div>
      </div>
      <span id="completion-text">0%</span>
    </div>

    <!-- Profile Form -->
    <div class="profile">
      <form id="profileForm" enctype="multipart/form-data" method="POST" action="../includes/upload_helpers.php">
        <div class="form-grid">
          <div>
            <label>Full Name:</label>
            <input type="text" name="fullName" id="fullName" value="<?= htmlspecialchars($user['fullName'] ?: $user['name']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>

            <label>Phone:</label>
            <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">

            <label>Education Level:</label>
            <select id="education" name="education">
              <option value="">Select</option>
              <option value="Diploma" <?= (isset($user['education']) && $user['education'] == 'Diploma') ? 'selected' : '' ?>>Diploma</option>
              <option value="Undergraduate" <?= (isset($user['education']) && $user['education'] == 'Undergraduate') ? 'selected' : '' ?>>Undergraduate</option>
              <option value="Postgraduate" <?= (isset($user['education']) && $user['education'] == 'Postgraduate') ? 'selected' : '' ?>>Postgraduate</option>
            </select>

            <label>Skills:</label>
            <textarea id="skills" name="skills" placeholder="e.g., public speaking, JavaScript, Python, UI/UX Design"><?= htmlspecialchars($user['skills'] ?? '') ?></textarea>
          </div>

          <!-- Upload Area -->
          <div class="upload-section">
            <div class="profile-pic-wrapper">
              <img src="<?= $profilePic ?>" alt="avatar" style="width:120px;height:120px;border-radius:50%;">
              <input type="file" id="profilePic" name="profilePic" accept="image/*">
            </div>

            <label>Upload CV (PDF):</label>
            <input type="file" id="cvFile" name="cvFile" accept=".pdf">

            <button type="submit" class="btn btn-primary">Save Profile</button>
          </div>
        </div>
      </form>
    </div>
  </main>

  <footer class="footer">
    <div class="page-content-wrapper footer-content">
      <p>© 2025 CaReeR CoNNect HuB. All rights reserved.</p>
      <div class="footer-social">
        <span>For more info</span>
        <a href="https://www.instagram.com/my.preciouspabz" target="_blank" aria-label="Instagram" class="social-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
            <circle cx="17.5" cy="6.5" r="1.5"/>
          </svg>
        </a>
        <a href="https://github.com/pabz123" target="_blank" aria-label="GitHub" class="social-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 19c-4.5 1.5-4.5-2.5-6-3m12 5v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 18 2.77 5.07 5.07 0 0 0 17.91 0S16.73.35 14 2.48a13.38 13.38 0 0 0-8 0C3.27.35 2.09 0 2.09 0A5.07 5.07 0 0 0 2 2.77 5.44 5.44 0 0 0 .5 8.5c0 5.42 3.3 6.61 6.44 7a3.37 3.37 0 0 0-.94 2.61V21"/>
          </svg>
        </a>
        <a href="https://www.linkedin.com/in/pabz" target="_blank" aria-label="LinkedIn" class="social-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2h-1v9h-4v-9h-4v9H3V9h4v1.2a4.6 4.6 0 0 1 4-2.2z"/>
            <rect x="2" y="9" width="4" height="12"/>
            <circle cx="4" cy="4" r="2"/>
          </svg>
        </a>
      </div>
    </div>
  </footer>

  <script>
  document.addEventListener("DOMContentLoaded", () => {
    const body = document.body;
    const form = document.getElementById('profileForm');
    const completionText = document.getElementById('completion-text');
    const completionFill = document.getElementById('completion-fill');
    const hamburger = document.getElementById('hamburger-menu');
    const dropdown = document.getElementById('dropdown-menu');
    const logoutLink = document.getElementById('logout-link');

    // === Load saved theme ===
    const savedTheme = localStorage.getItem('theme') || 'dark';
    if (savedTheme === 'light') {
      body.classList.add('light-theme');
    } else {
      body.classList.add('dark-theme');
    }

    // === Hamburger menu toggle ===
    if (hamburger && dropdown) {
      hamburger.addEventListener('click', (e) => {
        e.stopPropagation();
        hamburger.classList.toggle('active');
        dropdown.classList.toggle('active');
      });

      // Close dropdown when clicking outside
      document.addEventListener('click', (e) => {
        if (!hamburger.contains(e.target) && !dropdown.contains(e.target)) {
          hamburger.classList.remove('active');
          dropdown.classList.remove('active');
        }
      });
    }

    // === Profile completion calculation ===
    function updateCompletion() {
      const inputs = Array.from(form.querySelectorAll('input[type="text"], input[type="email"], select, textarea'));
      const filled = inputs.filter(i => i.value.trim() !== '').length;
      const percent = Math.round((filled / inputs.length) * 100);
      completionText.textContent = `${percent}%`;
      completionFill.style.width = `${percent}%`;
    }

    // Update completion on input
    form.addEventListener('input', updateCompletion);
    updateCompletion(); // Initial calculation

    // === Profile picture preview ===
    const profilePicInput = document.getElementById('profilePic');
    const profilePreview = document.querySelector('.profile-pic-wrapper img');
    const profileSmall = document.getElementById('profile-pic-small');

    if (profilePicInput) {
      profilePicInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = (e) => {
            if (profilePreview) profilePreview.src = e.target.result;
            if (profileSmall) profileSmall.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
    }

    // === Logout ===
    if (logoutLink) {
      logoutLink.addEventListener('click', async (e) => {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
          try {
            await fetch('../api/auth/logout.php', { method: 'POST', credentials: 'include' });
          } catch (err) {
            console.error('Logout failed:', err);
          }
          localStorage.clear();
          window.location.href = 'login.php';
        }
      });
    }
  });
  </script>
</body>
</html>
