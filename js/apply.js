/**
 * apply.js - Job application submission module
 *
 * Teaching notes: This module handles job application form submission with file uploads.
 * Key JavaScript concepts:
 * - FormData API: Handles multipart/form-data including file uploads
 * - fetch API: Direct fetch call (not using api.js wrapper due to FormData)
 * - Authorization header: Bearer token authentication pattern
 * - Content-Type: NOT set manually for FormData (browser sets multipart boundary)
 * - async/await: Modern promise handling
 * - Error handling: try/catch with user feedback
 *
 * FormData vs JSON:
 * - FormData: Used when form includes <input type="file"> (multipart/form-data)
 * - JSON: Used for simple text data (application/json)
 * - Browser automatically sets Content-Type boundary for FormData
 * - Do NOT manually set Content-Type header when sending FormData
 *
 * File upload flow:
 * 1. User fills form (cover letter, CV file, etc.)
 * 2. JS creates FormData object from form element
 * 3. FormData automatically encodes files as binary data
 * 4. POST to /applications endpoint with multipart/form-data
 * 5. Server saves file to disk and creates application record
 * 6. Redirect to jobs list on success
 *
 * Security notes:
 * - Server MUST validate file types, sizes, and sanitize filenames
 * - Never trust client-side validation (always validate server-side)
 * - Use Authorization header to identify user (prevents unauthorized applications)
 */

// Import api helper (not used here due to FormData handling)
import { api } from './api.js';

/**
 * Application form handler
 * - Assumes form id="applyForm" exists in DOM
 * - Handles submit event, prevents default, sends multipart data
 */
const applyForm = document.getElementById('applyForm');
if (applyForm) {
  applyForm.addEventListener('submit', async (e) => {
    /**
     * preventDefault: stops default form submission behavior
     * - Prevents page reload
     * - Allows JS to handle submission via fetch
     */
    e.preventDefault();
    
    /**
     * FormData: automatically extracts all form fields and files
     * - new FormData(form): creates FormData object from form element
     * - Handles <input type="text">, <textarea>, <select>, <input type="file">
     * - Encodes files as binary data (multipart/form-data format)
     */
    const formData = new FormData(applyForm);
    
    /**
     * IMPORTANT: When sending FormData, do NOT set Content-Type header
     * - Browser automatically sets Content-Type: multipart/form-data; boundary=...
     * - Boundary is a random string separating form parts
     * - Manual Content-Type will break file uploads (missing boundary)
     */
    try {
      /**
       * fetch: direct fetch call (not using api.js wrapper)
       * - api.js may set Content-Type: application/json, which breaks FormData
       * - Authorization header: Bearer token pattern for authentication
       * - localStorage.getItem('token'): retrieves auth token from browser storage
       * - || '': provides empty string fallback if token doesn't exist
       * - body: formData: sends FormData object as request body
       */
      const res = await fetch('/applications', {
        method: 'POST',
        headers: {
          /**
           * Authorization header: Bearer <token>
           * - Bearer: auth scheme indicating token-based auth
           * - Token was stored in localStorage during login
           * - Server validates token to identify user
           */
          'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
          // DO NOT add Content-Type here when sending FormData
          // Browser auto-sets: Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryXYZ
        },
        body: formData, // FormData object (includes files)
      });
      
      /**
       * Check HTTP status code
       * - res.ok: true if status is 200-299 (success)
       * - false for 400, 401, 500, etc. (error responses)
       */
      if (!res.ok) {
        /**
         * Parse error response as JSON
         * - await res.json(): parses response body as JSON
         * - .catch(() => ({...})): fallback if response isn't JSON (e.g., HTML error page)
         * - || operator: provides default error message
         */
        const err = await res.json().catch(() => ({ message: res.statusText }));
        throw new Error(err.message || 'Application failed');
      }
      
      // Success: show message and redirect to jobs list
      alert('Application submitted successfully');
      window.location.href = '/jobs.html';
    } catch (err) {
      // Log error and display user-friendly message
      console.error(err);
      alert(err.message || 'Failed to submit application');
    }
  });
}