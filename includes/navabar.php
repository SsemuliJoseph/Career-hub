<?php
/**
 * Main Navigation Bar Component
 * TEACHING NOTES:
 * - Reusable navbar with PWA meta tags and theme support
 * - Dynamically applies light/dark theme from $_SESSION['user']['theme']
 * - Includes logo, navigation links, and user profile display
 * - Uses SVG graphics and responsive CSS for mobile-friendly navigation
 */
require_once __DIR__ . '/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Add PWA meta tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0a66c2">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="CareerConnect">
    
    <!-- Apple touch icons -->
    <link rel="apple-touch-icon" href="/assets/icons/icon-192.png">
    
    <!-- Preload critical assets -->
    <link rel="preload" href="/css/global.css" as="style">
    <link rel="preload" href="/js/theme.js" as="script">
    <link rel="preload" href="/js/navbar.js" as="script">
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="/css/global.css">
</head>
<body class="<?= ($_SESSION['user']['theme'] ?? 'dark') ?>-theme">

<nav class="page-content-wrapper header-nav">
  <a class="logo" href="../index.php">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2.2">
      <circle cx="32" cy="32" r="30" stroke="#0a66c2"/>
      <rect x="18" y="28" width="28" height="18" rx="3" ry="3" stroke="#0a66c2"/>
      <path d="M24 28v-4h16v4" stroke="#0a66c2"/>
      <path d="M32 12l14 6-14 6-14-6 14-6z" stroke="#0a66c2"/>
      <path d="M32 24v4" stroke="#0a66c2"/>
    </svg>
    <span class="logo-text">CaReeR CoNNect HuB</span>
  </a>

  <div class="nav-links">
    <a href="../index.php">Home</a>
    <a href="pages/Success_stories.php">Success Stories</a>
    <a href="pages/career tips.php">Career Tips</a>
    <a href="pages/contact.php">Contact</a>
</div>

    <div class="header-actions">
      <?php if (!empty($_SESSION['user'])): ?>
        <img id="user-avatar" src="<?= htmlspecialchars($_SESSION['user']['profile_image'] ?? '/career_hub/uploads/profile/default-avatar.png') ?>" alt="Profile" class="profile-avatar-small">
        
        <!-- Unified Hamburger Menu -->
        <button class="hamburger-menu" id="hamburger-menu" aria-label="Menu" aria-expanded="false">
          <span></span>
          <span></span>
          <span></span>
        </button>
        
        <!-- Unified Dropdown Menu (includes nav-links on mobile) -->
        <div class="dropdown-menu" id="dropdown-menu">
          <!-- Mobile Nav Links (hidden on desktop) -->
          <div class="mobile-nav-links">
            <a href="../index.php">🏠 Home</a>
            <a href="pages/opportunities.php">💼 Opportunities</a>
            <a href="pages/contact.php">📞 Contact</a>
            <hr>
          </div>
          
          <!-- User Menu -->
          <?php if (($_SESSION['user']['role'] ?? '') === 'employer'): ?>
            <a href="pages/employer-profile.php">🏢 Company Profile</a>
            <a href="pages/employer.php">📊 Dashboard</a>
            <a href="pages/employer-applicants.php">📋 Applications</a>
            <a href="pages/settings.php">⚙️ Settings</a>
          <?php else: ?>
            <a href="pages/student-profile.php">📝 My Profile</a>
            <a href="pages/my-applications.php">📋 My Applications</a>
            <a href="pages/jobs.php">💼 Browse Jobs</a>
            <a href="pages/settings.php">⚙️ Settings</a>
          <?php endif; ?>
          <hr>
          <a href="#" id="logout-link">🚪 Logout</a>
        </div>
      <?php else: ?>
        <a class="btn btn-secondary small-btn" href="pages/login.php">Login</a>
        <a class="btn btn-primary small-btn" href="pages/signup.php">Sign Up</a>
      <?php endif; ?>
    </div>
</nav>

<!-- Register service worker -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker registered');
            })
            .catch(err => {
                console.warn('ServiceWorker registration failed:', err);
            });
    });
}
</script>

<!-- Unified Hamburger dropdown menu -->
<script>
  (function(){
    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.body.classList.add(savedTheme + '-theme');
    
    // Unified hamburger menu toggle
    const hamburger = document.getElementById('hamburger-menu');
    const dropdown = document.getElementById('dropdown-menu');
    const logoutLink = document.getElementById('logout-link');
    
    if (hamburger && dropdown) {
      hamburger.addEventListener('click', (e) => {
        e.stopPropagation();
        const expanded = hamburger.getAttribute('aria-expanded') === 'true';
        hamburger.setAttribute('aria-expanded', String(!expanded));
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
    
    // Logout functionality
    if (logoutLink) {
      logoutLink.addEventListener('click', async (e) => {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
          try {
            await fetch('/api/auth/logout.php', { method: 'POST', credentials: 'include' });
          } catch (err) {
            console.error('Logout failed:', err);
          }
          localStorage.clear();
          window.location.href = '/pages/login.php';
        }
      });
    }
  })();
</script>
</body>
</html>
