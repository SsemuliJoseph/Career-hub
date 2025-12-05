/**
 * js/navbar.js - Navigation Bar Dynamic Behavior
 * Teaching: This file manages the navbar's dynamic features (user info, theme toggle, logout).
 * - It uses async/await to fetch user data from the server.
 * - It interacts with localStorage for client-side caching and theme persistence.
 * - It demonstrates defensive coding (check if elements exist before using them).
 * 
 * Key concepts:
 * - DOMContentLoaded: wait for DOM to be ready before accessing elements.
 * - fetch API: modern way to make HTTP requests (replaces XMLHttpRequest).
 * - async/await: clean syntax for handling Promises.
 * - localStorage: persistent browser storage (survives page reload and tab close).
 * - classList: modern API for adding/removing CSS classes.
 */

// public/js/navbar.js
// Teaching: async function inside DOMContentLoaded ensures DOM is ready before running
document.addEventListener("DOMContentLoaded", async () => {
  // Teaching: getElementById is the fastest way to get an element by its id attribute
  const userNameEl = document.getElementById('user-name');
  const userAvatarEl = document.getElementById('user-avatar');
  const navUser = document.getElementById('nav-user');
  const themeToggle = document.getElementById('theme-toggle');

  /**
   * Helper function to apply theme to document body
   * Teaching: Functions can be defined inside other functions (closures).
   * This function is only accessible within this DOMContentLoaded handler.
   */
  function applyTheme(theme) {
    if (theme === 'light') {
      // Add 'light-theme' class to body element for light mode styles
      document.body.classList.add('light-theme');
      // Update theme toggle button text (defensive check with &&)
      themeToggle && (themeToggle.textContent = '🌤️ Light');
    } else {
      // Remove 'light-theme' class for dark mode (default)
      document.body.classList.remove('light-theme');
      themeToggle && (themeToggle.textContent = '🌙 Dark');
    }
    // Teaching: try/catch for localStorage access (may fail in private browsing)
    try { localStorage.setItem('theme', theme); } catch (e) {}
  }

  // Load user from localStorage cache or fetch from session endpoint
  let user = null;
  // Teaching: try/catch for JSON.parse (invalid JSON throws error)
  try { user = JSON.parse(localStorage.getItem('user')); } catch (e) { user = null; }

  // If no cached user, fetch from server
  if (!user) {
    try {
      // Teaching: fetch() returns a Promise that resolves to a Response object
      const res = await fetch('/api/session/me');
      // Teaching: res.ok is true if status code is 200-299 (success)
      if (res.ok) {
        // Teaching: res.json() parses response body as JSON (returns Promise)
        const payload = await res.json();
        // Extract user object (handle different response formats)
        user = payload?.user || payload || null;
        if (user) {
          // Cache user in localStorage for faster subsequent page loads
          try { localStorage.setItem('user', JSON.stringify(user)); } catch (e) {}
        }
      }
    } catch (err) { 
      // Network error or server unreachable
      console.warn(err); 
    }
  }

  // Teaching: Determine theme preference from multiple sources (priority order)
  // 1. User's saved preference (from session)
  // 2. localStorage (client-side cache)
  // 3. Default to 'dark'
  let preferredTheme = (user && user.theme) || localStorage.getItem('theme') || 'dark';
  applyTheme(preferredTheme);

  // Populate navbar user info (name and avatar)
  if (user && (user.email || user.name)) {
    // Teaching: && operator short-circuits: only execute right side if left is truthy
    // This is a compact way to write: if (userNameEl) { userNameEl.textContent = ... }
    userNameEl && (userNameEl.textContent = user.name || user.fullName || (user.email ? user.email.split('@')[0] : 'User'));
    if (userAvatarEl) {
      // Set avatar image source (fallback to default if none provided)
      userAvatarEl.src = user.profile_image || user.profilePic || user.profileImage || '/assets/images/default-avatar.png';
    }
  } else {
    // User not logged in - show default guest text and avatar
    userNameEl && (userNameEl.textContent = 'Guest');
    if (userAvatarEl) userAvatarEl.src = '/assets/images/default-avatar.png';
  }

  // Teaching: Dropdown toggle (show/hide user menu on click)
  navUser && navUser.addEventListener('click', (e) => {
    // classList.toggle adds class if not present, removes if present
    navUser.classList.toggle('active');
    // Teaching: stopPropagation prevents event from bubbling up to parent elements
    e.stopPropagation();
  });
  
  // Close dropdown when clicking anywhere else on page
  document.addEventListener('click', () => { 
    navUser && navUser.classList.remove('active'); 
  });

  // Theme toggle handler: persist to server if logged in, otherwise to localStorage
  if (themeToggle) {
    themeToggle.addEventListener('click', async () => {
      const newTheme = document.body.classList.contains('light-theme') ? 'dark' : 'light';
      applyTheme(newTheme);

      // If user is logged in, persist preference to DB via API
      if (user && user.id) {
        try {
          await fetch('/api/user/save_theme.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ theme: newTheme })
          });
          // update local session cache
          user.theme = newTheme;
          try { localStorage.setItem('user', JSON.stringify(user)); } catch (e) {}
        } catch (err) { console.warn('Failed to save theme:', err); }
      }
    });
  }

  // logout button (if present in dropdown)
  const logoutBtn = document.getElementById('logout-btn');
  logoutBtn && logoutBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    try { await fetch('/api/auth/logout.php', { method: 'POST' }); } catch (e) {}
    localStorage.removeItem('user');
    window.location.href = '/pages/login.php';
  });
});
