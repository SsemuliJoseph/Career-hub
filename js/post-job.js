/**
 * js/post-job.js - Job Posting Form Handler
 * Teaching: This module handles the "Post a Job" form submission via AJAX.
 * - It uses ES6 imports to load the shared API helper.
 * - FormData is a browser API for handling form data (including file uploads).
 * - Object.fromEntries() converts FormData to a plain object (ES2019 feature).
 * - async/await simplifies Promise handling (no .then() chains).
 * 
 * Key concepts:
 * - preventDefault(): stop form's default submit behavior (no page reload).
 * - FormData API: extract form values easily, supports file inputs.
 * - JSON.stringify(): convert JS object to JSON string for server.
 * - window.location.href: navigate to a different page programmatically.
 */
import { api } from './api.js';

// Teaching: getElementById returns the element with specified id (or null if not found).
// Store reference in a constant for reuse.
const postJobForm = document.getElementById('postJobForm');

// Check if form exists on current page (defensive programming)
if (postJobForm) {
  // Teaching: addEventListener attaches a submit handler to the form.
  // async keyword makes the handler function asynchronous (can use await inside).
  postJobForm.addEventListener('submit', async (e) => {
    // Teaching: e.preventDefault() stops the default form submission behavior.
    // Without this, the browser would navigate away and reload the page.
    e.preventDefault();
    
    // Teaching: FormData(form) extracts all input values from the form.
    // It handles text inputs, checkboxes, file uploads, etc. automatically.
    const formData = new FormData(postJobForm);
    
    // Teaching: Object.fromEntries() converts FormData to a plain object.
    // Example: { title: 'Software Engineer', location: 'Remote', ... }
    // This is needed because JSON.stringify doesn't work directly with FormData.
    const payload = Object.fromEntries(formData.entries());

    try {
      // Teaching: await pauses execution until the api() call resolves.
      // The api() function sends a POST request with the job data as JSON.
      const data = await api('/jobs', {
        method: 'POST',  // HTTP method for creating resources
        body: JSON.stringify(payload),  // Convert object to JSON string
      });
      
      // If successful, show alert and redirect to jobs page
      alert('Job posted successfully');
      // Teaching: window.location.href navigates to a new page (like clicking a link).
      window.location.href = '/jobs.html';
    } catch (err) {
      // Teaching: catch block handles errors (network failure, server error, etc.)
      console.error(err);  // Log error to browser console for debugging
      // Show user-friendly error message (err.message may contain server response)
      alert(err.message || 'Failed to post job');
    }
  });
}