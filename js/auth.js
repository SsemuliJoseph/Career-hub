/**
 * auth.js - Authentication module (login/signup/logout)
 *
 * Teaching notes: This module handles user authentication flows.
 * Key JavaScript concepts:
 * - ES6 modules: import/export for code organization
 * - Event listeners: addEventListener for form submissions
 * - async/await: Modern promise handling for API calls
 * - localStorage: Browser storage for auth tokens (persistent across sessions)
 * - FormData API: Extract form values (alternative to manual querySelector)
 * - Object.fromEntries(): Convert FormData to plain object
 * - JSON.stringify(): Convert JS object to JSON string for fetch body
 * - preventDefault(): Stop default form submission (page reload)
 * - window.location.href: Client-side navigation (redirect after auth)
 *
 * Authentication flow:
 * 1. User fills form (email, password)
 * 2. JS captures submit event, prevents default
 * 3. Send credentials to API endpoint (POST /auth/login or /auth/register)
 * 4. Server validates, returns token or error
 * 5. Store token in localStorage (used for subsequent API requests)
 * 6. Redirect to appropriate dashboard
 *
 * Security notes:
 * - Tokens should be HTTP-only cookies for XSS protection (this uses localStorage for simplicity)
 * - Always use HTTPS in production to encrypt token transmission
 * - Consider JWT refresh tokens for long-lived sessions
 */

// Import centralized fetch wrapper with auth headers
import { api } from './api.js';

/**
 * Login form handler
 * - getElementById: retrieves element by id attribute
 * - querySelector: CSS selector for finding input elements
 * - addEventListener: attaches handler to form submit event
 */
const loginForm = document.getElementById('loginForm');
if (loginForm) {
  loginForm.addEventListener('submit', async (e) => {
    /**
     * preventDefault: stops form's default behavior (page reload on submit)
     * - Without this, form would submit to action attribute or current URL
     * - Allows JS to handle submission via fetch instead
     */
    e.preventDefault();
    
    /**
     * Extract form values from input elements
     * - querySelector: finds first element matching CSS selector
     * - input[name="email"]: finds <input name="email">
     * - .value: gets current input value
     */
    const email = loginForm.querySelector('input[name="email"]').value;
    const password = loginForm.querySelector('input[name="password"]').value;
    
    try {
      /**
       * API call: POST to /auth/login with credentials
       * - JSON.stringify: converts JS object to JSON string for request body
       * - await: pauses execution until promise resolves (async function required)
       * - api() wrapper handles fetch, error checking, JSON parsing
       */
      const data = await api('/auth/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
      });
      
      /**
       * Store auth token in localStorage
       * - localStorage: browser storage (persists across sessions)
       * - setItem(key, value): stores string value with given key
       * - Token will be sent in Authorization header for subsequent requests
       */
      if (data.token) localStorage.setItem('token', data.token);
      
      /**
       * Redirect to dashboard or home page
       * - window.location.href: sets current URL (triggers navigation)
       * - || operator: provides fallback if data.redirect is undefined
       */
      window.location.href = '/student.html' || '/';
    } catch (err) {
      // Log error to console for debugging
      console.error(err);
      
      /**
       * Display user-friendly error message
       * - alert(): browser popup (not ideal UX, consider toast/banner instead)
       * - err.message: error message from API or fetch
       */
      alert(err.message || 'Login failed');
    }
  });
}

/**
 * Signup form handler
 * - Similar pattern to login but uses FormData for multi-field forms
 */
const signupForm = document.getElementById('signupForm');
if (signupForm) {
  signupForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    /**
     * FormData API: Extracts all form fields automatically
     * - new FormData(form): creates FormData object from form element
     * - Handles text inputs, textareas, selects, checkboxes, radio buttons
     * - entries(): returns iterator of [name, value] pairs
     * - Object.fromEntries(): converts entries to plain object
     */
    const formData = new FormData(signupForm);
    const payload = Object.fromEntries(formData.entries());
    
    try {
      // POST to registration endpoint with all form fields
      const data = await api('/auth/register', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
      
      // Show success message and redirect to login
      alert('Registration successful. Please log in.');
      window.location.href = '/login.html';
    } catch (err) {
      console.error(err);
      alert(err.message || 'Registration failed');
    }
  });
}

/**
 * Logout helper
 * - Clears auth token from localStorage
 * - Redirects to home page (unauthenticated state)
 */
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
  logoutBtn.addEventListener('click', () => {
    /**
     * removeItem: deletes key from localStorage
     * - Token is removed, making user unauthenticated
     * - Next API call will fail auth check, forcing re-login
     */
    localStorage.removeItem('token');
    
    // Redirect to home page
    window.location.href = '/';
  });
}