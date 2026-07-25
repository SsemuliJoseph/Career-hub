/**
 * jobs.js - Job listing display module
 *
 * Teaching notes: This module fetches and renders job listings on the jobs page.
 * Key JavaScript concepts:
 * - ES6 import: Modular code organization (requires type="module" in <script> tag)
 * - async/await: Modern asynchronous programming (cleaner than callbacks/promises)
 * - try/catch: Error handling for async operations
 * - Template literals: ${} syntax for dynamic HTML generation
 * - DOM manipulation: createElement, appendChild, innerHTML
 * - DOMContentLoaded: Event fired when DOM is ready (safe to manipulate elements)
 *
 * API interaction:
 * - Calls /jobs endpoint (see api/jobs.php or api/get_jobs.php)
 * - Expects JSON response: { jobs: [...] }
 * - Each job has: id, title, company, location, description
 *
 * Security notes:
 * - Always validate/sanitize data from server before rendering
 * - Consider escaping HTML in job.title, job.description to prevent XSS
 * - In production, use textContent instead of innerHTML for user data
 */

// Import api helper function (centralized fetch wrapper)
import { api } from './api.js';

/**
 * loadJobs - Fetch jobs from API and render to DOM
 * - async function: allows await keyword for cleaner async code
 * - try/catch: handles errors from fetch/api call
 */
async function loadJobs() {
  try {
    // api() is a wrapper around fetch() with auth headers, error handling
    // GET request to /jobs endpoint returns array of job objects
    const jobs = await api('/jobs', { method: 'GET' });
    
    // Find container element in DOM (assumes <div id="jobsList"></div> exists)
    const container = document.getElementById('jobsList');
    
    // Guard clause: exit early if container doesn't exist (defensive)
    if (!container) return;
    
    // Clear existing content (innerHTML = '' removes all child nodes)
    container.innerHTML = '';
    
    /**
     * forEach loop: iterate over jobs array
     * - arrow function: (job => {...}) is shorthand for function(job) {...}
     * - job parameter represents each job object in the array
     */
    jobs.forEach(job => {
      // createElement: creates a new DOM element (not yet in document)
      const el = document.createElement('div');
      
      // Set CSS class for styling
      el.className = 'job-item';
      
      /**
       * Template literal: backticks allow multi-line strings with ${} interpolation
       * - ${job.title}: inserts job title into HTML
       * - || '': provides empty string fallback if property is undefined/null
       * - substring(0, 200): truncates description to 200 chars
       * - data-job-id attribute: stores job ID for click handlers (data-* attributes)
       */
      el.innerHTML = `
        <h3>${job.title}</h3>
        <p>${job.company || ''} — ${job.location || ''}</p>
        <p>${job.description ? job.description.substring(0, 200) + '...' : ''}</p>
        <a href="/cv.html" class="apply-link" data-job-id="${job.id}">Apply</a>
      `;
      
      // appendChild: adds element to the end of container's children
      container.appendChild(el);
    });
  } catch (err) {
    // Log error to browser console for debugging
    console.error('Failed to load jobs', err);
    
    // In production, display user-friendly error message in UI
    // Example: container.innerHTML = '<p>Failed to load jobs. Please try again.</p>';
  }
}

/**
 * DOMContentLoaded event: fires when HTML is parsed and DOM is ready
 * - Safer than running code at top of file (elements may not exist yet)
 * - addEventListener: attaches event handler without overwriting existing handlers
 * - loadJobs: function called when event fires (no parentheses = pass function reference)
 */
document.addEventListener('DOMContentLoaded', loadJobs);