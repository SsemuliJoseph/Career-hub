/**
 * js/profile.js - Profile Editing Functions
 * Teaching: This file demonstrates client-side profile editing with localStorage.
 * - prompt() is a built-in browser function that shows a dialog and returns user input.
 * - querySelector() selects DOM elements by CSS selector.
 * - textContent is a property for reading/writing text content of an element.
 * - JSON.parse() / JSON.stringify() convert between JSON strings and JS objects.
 * - localStorage is a browser API for persistent key-value storage (survives page reload).
 * 
 * Key concepts:
 * - Optimistic UI: update UI immediately, assume server call will succeed.
 * - localStorage: simple client-side storage (not secure, avoid sensitive data).
 * - JSON serialization: convert objects to strings for storage.
 */

/**
 * Handle profile name editing
 * Teaching: This function demonstrates a simple inline editing pattern.
 * In production, you'd send the update to the server and handle errors.
 */
function handleEditProfile() {
  // Get current displayed name from DOM element with id 'profile-name'
  const currentName = document.querySelector('#profile-name').textContent;
  
  // Teaching: prompt(message, defaultValue) shows a browser dialog for text input.
  // Returns the entered text or null if user cancelled.
  const newName = prompt('Enter your new name:', currentName);

  // Check if user entered a non-empty name (trim removes whitespace)
  if (newName && newName.trim() !== '') {
    // Update DOM immediately (optimistic UI pattern)
    document.querySelector('#profile-name').textContent = newName.trim();

    // Teaching: Update localStorage to persist the change across page reloads.
    // localStorage.getItem() retrieves a stored string (or null if not found).
    // JSON.parse() converts the JSON string back to a JavaScript object.
    // The || {} provides a fallback empty object if no data exists.
    const userData = JSON.parse(localStorage.getItem('userProfile')) || {};
    
    // Update the name property
    userData.name = newName.trim();
    
    // Teaching: JSON.stringify() converts the object to a JSON string.
    // localStorage.setItem(key, value) stores the string under the key 'userProfile'.
    localStorage.setItem('userProfile', JSON.stringify(userData));

    // Show success notification (assumes showNotification function exists elsewhere)
    showNotification('Profile updated successfully!');
  }
}
