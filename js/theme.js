/* Teaching: theme.js — ThemeManager class
  Purpose: Centralized theme handling for the app (apply, toggle, persist).
  Demonstrates: ES6 classes, localStorage for persistence, DOM updates,
  updating meta theme-color for PWAs, listening to system preference
  changes via matchMedia, and optional server-side persistence.
  Keep theme logic isolated so other files can call `window.themeManager`.
*/
class ThemeManager {
  constructor() {
    this.THEME_KEY = 'theme';
    this.themeToggle = document.getElementById('theme-toggle');
    this.init();
  }

  init() {
    // Load saved theme
    const savedTheme = localStorage.getItem(this.THEME_KEY) || 
                      document.body.classList.contains('light-theme') ? 'light' : 'dark';
    this.applyTheme(savedTheme);

    // Set up theme toggle
    if (this.themeToggle) {
      this.themeToggle.addEventListener('click', () => this.toggleTheme());
    }

    // Watch for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)')
      .addEventListener('change', e => {
        if (!localStorage.getItem(this.THEME_KEY)) {
          this.applyTheme(e.matches ? 'dark' : 'light');
        }
      });
  }

  async applyTheme(theme) {
    document.body.classList.remove('light-theme', 'dark-theme');
    document.body.classList.add(`${theme}-theme`);
    
    if (this.themeToggle) {
      this.themeToggle.textContent = theme === 'light' ? '🌙 Dark Mode' : '☀️ Light Mode';
    }
    
    // Update meta theme-color
    document.querySelector('meta[name="theme-color"]')?.setAttribute(
      'content', 
      theme === 'light' ? '#ffffff' : '#0a66c2'
    );

    // Save to localStorage
    localStorage.setItem(this.THEME_KEY, theme);

    // If logged in, save to server
    const user = JSON.parse(localStorage.getItem('user'));
    if (user?.id) {
      try {
        await fetch('/api/user/save_theme.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ theme })
        });
      } catch (err) {
        console.warn('Failed to save theme to server:', err);
      }
    }
  }

  toggleTheme() {
    const newTheme = document.body.classList.contains('light-theme') ? 'dark' : 'light';
    this.applyTheme(newTheme);
  }
}

// Initialize theme management when DOM loads
document.addEventListener('DOMContentLoaded', () => {
  window.themeManager = new ThemeManager();
});