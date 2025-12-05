<!-- Admin Navigation Bar -->
<?php
/**
 * Admin Navigation Bar Component
 * TEACHING NOTES:
 * - Admin-only navigation with dashboard links (Dashboard, Import Jobs, Cleanup, View Site)
 * - Shows admin profile image and name from $_SESSION['user']
 * - Includes logout functionality via adminLogout() JavaScript function
 * - Styled with emoji icons for visual navigation cues
 */
?>
<nav class="admin-navbar">
  <div class="admin-nav-container">
    <div class="admin-nav-brand">
      <a href="/career_hub/pages/admin.php" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 1.5em;">🛡️</span>
        <span style="font-weight: bold; font-size: 1.2em;">Admin Panel</span>
      </a>
    </div>
    
    <div class="admin-nav-links">
      <a href="/career_hub/pages/admin.php" class="admin-nav-link">
        <span>📊</span>
        <span>Dashboard</span>
      </a>
      <a href="/career_hub/pages/import-jobs.php" class="admin-nav-link">
        <span>🌍</span>
        <span>Import Jobs</span>
      </a>
      <a href="/career_hub/cleanup_jobs.php" class="admin-nav-link">
        <span>🧹</span>
        <span>Cleanup</span>
      </a>
      <a href="/career_hub/index.php" class="admin-nav-link" target="_blank">
        <span>🏠</span>
        <span>View Site</span>
      </a>
    </div>
    
    <div class="admin-nav-user">
      <img src="<?= htmlspecialchars($_SESSION['user']['profile_image'] ?? '/career_hub/uploads/profile/default-avatar.png') ?>" 
           alt="Profile" 
           style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);">
      <span class="admin-user-info">
        <span style="opacity: 0.8;">Admin:</span>
        <strong><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?></strong>
      </span>
      <button onclick="adminLogout()" class="admin-logout-btn">
        <span>🚪</span>
        <span>Logout</span>
      </button>
    </div>
  </div>
</nav>

<style>
  /* Reset any conflicting styles */
  .admin-navbar * {
    box-sizing: border-box;
  }
  
  /* Admin Navbar Styles */
  .admin-navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    position: sticky;
    top: 0;
    z-index: 1000;
    border-bottom: 3px solid rgba(255, 255, 255, 0.2);
  }
  
  .admin-nav-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 15px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
  }
  
  .admin-nav-brand {
    flex-shrink: 0;
  }
  
  .admin-nav-links {
    display: flex;
    gap: 10px;
    flex: 1;
    justify-content: center;
  }
  
  .admin-nav-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    color: white !important;
    text-decoration: none !important;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
  
  .admin-nav-link:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }
  
  .admin-nav-link span:first-child {
    font-size: 1.2em;
  }
  
  .admin-nav-user {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-shrink: 0;
  }
  
  .admin-user-info {
    color: white;
    font-size: 0.95em;
    display: flex;
    gap: 5px;
    align-items: center;
  }
  
  .admin-logout-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: rgba(252, 129, 129, 0.9);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95em;
  }
  
  .admin-logout-btn:hover {
    background: #fc8181;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(252, 129, 129, 0.4);
  }
  
  /* Responsive Design */
  @media (max-width: 768px) {
    .admin-nav-container {
      flex-direction: column;
      gap: 15px;
      padding: 15px;
    }
    
    .admin-nav-links {
      flex-wrap: wrap;
      justify-content: center;
      width: 100%;
    }
    
    .admin-nav-link {
      padding: 8px 15px;
      font-size: 0.9em;
    }
    
    .admin-nav-user {
      flex-direction: column;
      gap: 10px;
    }
  }
</style>

<script>
  function adminLogout() {
    if (confirm('Are you sure you want to logout?')) {
      fetch('/career_hub/api/auth/logout.php', {
        method: 'POST'
      })
      .then(() => {
        localStorage.clear();
        window.location.href = '/career_hub/pages/login.php';
      })
      .catch(error => {
        console.error('Logout error:', error);
        window.location.href = '/career_hub/pages/login.php';
      });
    }
  }
</script>
     
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
            await fetch('/career_hub/api/auth/logout.php', { method: 'POST', credentials: 'include' });
          } catch (err) {
            console.error('Logout failed:', err);
          }
          localStorage.clear();
          window.location.href = '/career_hub/pages/login.php';
        }
      });
    }
    
    // Set user ID for WebSocket if logged in
    <?php if (!empty($_SESSION['user']['id'])): ?>
    localStorage.setItem('userId', '<?= $_SESSION['user']['id'] ?>');
    <?php endif; ?>
  })();
</script>
</body>
</html>
