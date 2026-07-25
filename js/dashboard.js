/**
 * js/dashboard.js - Dashboard API Helper Functions
 * Teaching: This module provides functions for fetching dashboard data.
 * - It uses ES6 'import' to load the shared API helper from api.js.
 * - ES6 'export async function' allows other modules to import these functions.
 * - async/await is modern JavaScript syntax for handling asynchronous operations.
 * - The apiFetch function is expected to return a Promise that resolves to data.
 * 
 * Key JavaScript concepts:
 * - import/export: ES6 module system for organizing code.
 * - async: marks a function as asynchronous (returns a Promise).
 * - await: pauses execution until the Promise resolves (must be inside async function).
 */
import { api } from './api.js';

/**
 * Get current user's job applications
 * Teaching: async function returns a Promise that resolves to the application list.
 * @return {Promise<Array>} Array of application objects
 */
export async function getMyApplications() {
  // apiFetch will make a GET request to /applications/my
  return apiFetch("/applications/my");
}

/**
 * Get all applicants for a specific job (employer view)
 * Teaching: Template literals (backticks) allow string interpolation with ${variable}.
 * @param {number} jobId ID of the job
 * @return {Promise<Array>} Array of applicant objects
 */
export async function getApplicantsForJob(jobId) {
  // Use template literal to insert jobId into the URL path
  return apiFetch(`/applications/for-job/${jobId}`);
}