<?php
/**
 * pages/import-jobs.php
 * Teaching notes (top-of-file):
 * - This page is an admin-only UI for importing jobs from external sources.
 * - We include three helper files:
 *   - includes/session.php: ensures session_start() ran and session data is available.
 *   - includes/auth_check.php: small helper that may centralize auth logic; including
 *     it keeps authorization logic consistent across pages.
 *   - includes/db.php: exposes the $conn mysqli connection used for DB operations.
 *
 * Key PHP concepts demonstrated here for students:
 * - include/require once and __DIR__ usage to build filesystem-safe paths.
 * - Reading $_SESSION to determine the current user and their role.
 * - Using header('Location: ...') and exit to perform server-side redirects.
 * - Keeping authorization checks near the top of the script (fail fast) to avoid
 *   accidental rendering of protected content.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

// Authorization check (defensive): verify that a user is in session and is an admin.
// We read from the $_SESSION superglobal which was made available by session.php.
// The check is intentionally strict: we require the role to equal the string 'admin'.
// If the check fails we redirect the user based on their role (or to login)
// and immediately exit so no protected HTML is shown.
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    // If there is a logged-in user but they are not an admin, we redirect them
    // to the appropriate dashboard. This is nicer than always sending to login.
    if (isset($_SESSION['user'])) {
        $role = $_SESSION['user']['role'];
        if ($role === 'student') {
            header('Location: /career_hub/pages/student.php');
        } elseif ($role === 'employer') {
            header('Location: /career_hub/pages/employer.php');
        } else {
            // Unknown role -> treat as unauthorized
            header('Location: /career_hub/pages/login.php?error=unauthorized');
        }
    } else {
        // No session available -> redirect to login with an error query param.
        // Note: query params are visible in the URL and can be used to display
        // user-friendly messages on the login page.
        header('Location: /career_hub/pages/login.php?error=unauthorized');
    }
    exit(); // Stop script execution after sending redirect header.
}

$pageTitle = "Job Import Manager"; // Used later in the <title> and header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Career Hub</title>
    <link rel="stylesheet" href="/career_hub/css/global.css">
    <link rel="stylesheet" href="/career_hub/css/responsive.css">
    <link rel="stylesheet" href="/career_hub/css/jobs.css">
    <style>
        .import-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .import-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .import-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
            color: white;
        }

        .import-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1em;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #64748b;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .number {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin: 0;
        }

        .import-actions {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .import-btn {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.1em;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .import-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
        }

        .import-btn:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
            box-shadow: none;
        }

        .import-btn .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .import-btn.loading .spinner {
            display: block;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .progress-container {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .progress-container.active {
            display: block;
        }

        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e2e8f0;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9em;
        }

        .progress-log {
            max-height: 300px;
            overflow-y: auto;
            background: white;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }

        .progress-log p {
            margin: 5px 0;
            padding: 5px;
            border-left: 3px solid transparent;
        }

        .progress-log .success {
            color: #38a169;
            border-left-color: #38a169;
        }

        .progress-log .error {
            color: #e53e3e;
            border-left-color: #e53e3e;
        }

        .progress-log .info {
            color: #3182ce;
            border-left-color: #3182ce;
        }

        .jobs-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #4a5568;
            font-size: 0.9em;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px;
            border: 1px solid #cbd5e0;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .jobs-grid {
            display: grid;
            gap: 20px;
        }

        .job-card {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .job-card:hover {
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateX(5px);
        }

        .job-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .job-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #2d3748;
            margin: 0 0 5px 0;
        }

        .job-company {
            color: #667eea;
            font-weight: 600;
            font-size: 1em;
        }

        .job-type-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .job-type-Full-time {
            background: #c6f6d5;
            color: #22543d;
        }

        .job-type-Part-time {
            background: #bee3f8;
            color: #2c5282;
        }

        .job-type-Internship {
            background: #feebc8;
            color: #7c2d12;
        }

        .job-type-Contract {
            background: #e9d8fd;
            color: #44337a;
        }

        .job-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .job-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #64748b;
            font-size: 0.9em;
        }

        .job-description {
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .job-actions {
            display: flex;
            gap: 10px;
        }

        .job-action-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .view-btn {
            background: #667eea;
            color: white;
        }

        .view-btn:hover {
            background: #5568d3;
        }

        .delete-btn {
            background: #fc8181;
            color: white;
        }

        .delete-btn:hover {
            background: #f56565;
        }

        .no-jobs {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .no-jobs-icon {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination button {
            padding: 10px 20px;
            border: 1px solid #cbd5e0;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination button:hover:not(:disabled) {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination button.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #38a169;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #e53e3e;
        }

        .alert-info {
            background: #bee3f8;
            color: #2c5282;
            border-left: 4px solid #3182ce;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navibar.php'; ?>

    <div class="import-container">
        <!-- Header -->
        <div class="import-header">
            <h1>🌍 Job Import Manager</h1>
            <p>Import jobs from external APIs across Uganda, East Africa, Europe, and Africa</p>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>📊 Total Jobs</h3>
                <p class="number" id="totalJobs">0</p>
            </div>
            <div class="stat-card">
                <h3>🆕 Today's Imports</h3>
                <p class="number" id="todayImports">0</p>
            </div>
            <div class="stat-card">
                <h3>🏢 Companies</h3>
                <p class="number" id="totalCompanies">0</p>
            </div>
            <div class="stat-card">
                <h3>🌐 External Jobs</h3>
                <p class="number" id="externalJobs">0</p>
            </div>
        </div>

        <!-- Import Actions -->
        <div class="import-actions">
            <h2>🚀 Import Jobs from External APIs</h2>
            <p style="color: #64748b; margin-bottom: 20px;">
                Click the button below to automatically import jobs from JSearch API. 
                The system will search across <strong>5 random job categories</strong> and <strong>3 locations</strong> 
                including Uganda, Kenya, Tanzania, Rwanda, and more. Import takes approximately <strong>30-60 seconds</strong>.
            </p>
            <button id="importBtn" class="import-btn">
                <span class="spinner"></span>
                <span class="btn-text">🌍 Start Import</span>
            </button>

            <!-- Progress Container -->
            <div id="progressContainer" class="progress-container">
                <h3>Import Progress</h3>
                <div class="progress-bar">
                    <div id="progressFill" class="progress-fill">0%</div>
                </div>
                <div id="progressLog" class="progress-log"></div>
            </div>
        </div>

        <!-- Jobs Section -->
        <div class="jobs-section">
            <h2>📋 Available Jobs</h2>
            
            <!-- Filters -->
            <div class="filters">
                <div class="filter-group">
                    <label for="searchInput">🔍 Search</label>
                    <input type="text" id="searchInput" placeholder="Search jobs...">
                </div>
                <div class="filter-group">
                    <label for="typeFilter">📑 Job Type</label>
                    <select id="typeFilter">
                        <option value="">All Types</option>
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                        <option value="Internship">Internship</option>
                        <option value="Contract">Contract</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="locationFilter">📍 Location</label>
                    <input type="text" id="locationFilter" placeholder="Filter by location...">
                </div>
                <div class="filter-group">
                    <label for="sourceFilter">🌐 Source</label>
                    <select id="sourceFilter">
                        <option value="">All Sources</option>
                        <option value="external">External API</option>
                        <option value="internal">Internal</option>
                    </select>
                </div>
            </div>

            <!-- Jobs Grid -->
            <div id="jobsGrid" class="jobs-grid">
                <div class="no-jobs">
                    <div class="no-jobs-icon">📭</div>
                    <h3>Loading jobs...</h3>
                </div>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="pagination"></div>
        </div>
    </div>

    <script>
        let allJobs = [];
        let filteredJobs = [];
        let currentPage = 1;
        const jobsPerPage = 10;

        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadJobs();

            // Set up event listeners
            document.getElementById('importBtn').addEventListener('click', startImport);
            document.getElementById('searchInput').addEventListener('input', applyFilters);
            document.getElementById('typeFilter').addEventListener('change', applyFilters);
            document.getElementById('locationFilter').addEventListener('input', applyFilters);
            document.getElementById('sourceFilter').addEventListener('change', applyFilters);
        });

        // Load statistics
        async function loadStats() {
            try {
                const response = await fetch('/career_hub/api/get_job_stats.php');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('totalJobs').textContent = data.stats.total_jobs;
                    document.getElementById('todayImports').textContent = data.stats.today_imports;
                    document.getElementById('totalCompanies').textContent = data.stats.total_companies;
                    document.getElementById('externalJobs').textContent = data.stats.external_jobs;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Load jobs
        async function loadJobs() {
            try {
                const response = await fetch('/career_hub/api/get_all_jobs.php');
                const data = await response.json();
                
                if (data.success) {
                    allJobs = data.jobs;
                    filteredJobs = allJobs;
                    displayJobs();
                }
            } catch (error) {
                console.error('Error loading jobs:', error);
                showAlert('Failed to load jobs', 'error');
            }
        }

        // Apply filters
        function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const typeFilter = document.getElementById('typeFilter').value;
            const locationFilter = document.getElementById('locationFilter').value.toLowerCase();
            const sourceFilter = document.getElementById('sourceFilter').value;

            filteredJobs = allJobs.filter(job => {
                const matchesSearch = job.title.toLowerCase().includes(searchTerm) || 
                                     job.description.toLowerCase().includes(searchTerm) ||
                                     job.company.toLowerCase().includes(searchTerm);
                const matchesType = !typeFilter || job.type === typeFilter;
                const matchesLocation = !locationFilter || job.location.toLowerCase().includes(locationFilter);
                const matchesSource = !sourceFilter || 
                                     (sourceFilter === 'external' && job.external_link) ||
                                     (sourceFilter === 'internal' && !job.external_link);

                return matchesSearch && matchesType && matchesLocation && matchesSource;
            });

            currentPage = 1;
            displayJobs();
        }

        // Display jobs
        function displayJobs() {
            const jobsGrid = document.getElementById('jobsGrid');
            const startIndex = (currentPage - 1) * jobsPerPage;
            const endIndex = startIndex + jobsPerPage;
            const jobsToDisplay = filteredJobs.slice(startIndex, endIndex);

            if (jobsToDisplay.length === 0) {
                jobsGrid.innerHTML = `
                    <div class="no-jobs">
                        <div class="no-jobs-icon">📭</div>
                        <h3>No jobs found</h3>
                        <p>Try adjusting your filters or import more jobs</p>
                    </div>
                `;
                document.getElementById('pagination').innerHTML = '';
                return;
            }

            jobsGrid.innerHTML = jobsToDisplay.map(job => `
                <div class="job-card" data-job-id="${job.id}">
                    <div class="job-card-header">
                        <div>
                            <h3 class="job-title">${escapeHtml(job.title)}</h3>
                            <p class="job-company">${escapeHtml(job.company)}</p>
                        </div>
                        <span class="job-type-badge job-type-${job.type}">${job.type}</span>
                    </div>
                    <div class="job-meta">
                        <span class="job-meta-item">📍 ${escapeHtml(job.location)}</span>
                        <span class="job-meta-item">📅 ${formatDate(job.createdAt)}</span>
                        ${job.external_link ? '<span class="job-meta-item">🌐 External</span>' : ''}
                    </div>
                    <div class="job-description">${escapeHtml(job.description)}</div>
                    <div class="job-actions">
                        ${job.external_link ? 
                            `<button class="job-action-btn view-btn" onclick="window.open('${escapeHtml(job.external_link)}', '_blank')">🔗 View External</button>` :
                            `<button class="job-action-btn view-btn" onclick="viewJob(${job.id})">👁️ View Details</button>`
                        }
                        <button class="job-action-btn delete-btn" onclick="deleteJob(${job.id})">🗑️ Delete</button>
                    </div>
                </div>
            `).join('');

            displayPagination();
        }

        // Display pagination
        function displayPagination() {
            const totalPages = Math.ceil(filteredJobs.length / jobsPerPage);
            const pagination = document.getElementById('pagination');

            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let paginationHTML = '';
            
            // Previous button
            paginationHTML += `<button ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">← Previous</button>`;
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    paginationHTML += `<button class="${currentPage === i ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    paginationHTML += `<button disabled>...</button>`;
                }
            }
            
            // Next button
            paginationHTML += `<button ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">Next →</button>`;
            
            pagination.innerHTML = paginationHTML;
        }

        // Change page
        function changePage(page) {
            currentPage = page;
            displayJobs();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Start import
        async function startImport() {
            const btn = document.getElementById('importBtn');
            const progressContainer = document.getElementById('progressContainer');
            const progressFill = document.getElementById('progressFill');
            const progressLog = document.getElementById('progressLog');

            btn.disabled = true;
            btn.classList.add('loading');
            btn.querySelector('.btn-text').textContent = 'Importing Jobs...';
            progressContainer.classList.add('active');
            progressLog.innerHTML = '<p class="info">🚀 Starting import process...</p>';
            progressFill.style.width = '0%';
            progressFill.textContent = '0%';

            try {
                const response = await fetch('/career_hub/api/import_jobs_api.php', {
                    method: 'POST'
                });

                const reader = response.body.getReader();
                const decoder = new TextDecoder();

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    const chunk = decoder.decode(value);
                    const lines = chunk.split('\n');

                    for (const line of lines) {
                        if (line.trim().startsWith('data: ')) {
                            const data = JSON.parse(line.substring(6));
                            
                            if (data.type === 'progress') {
                                progressFill.style.width = data.progress + '%';
                                progressFill.textContent = data.progress + '%';
                            } else if (data.type === 'log') {
                                const logClass = data.level || 'info';
                                progressLog.innerHTML += `<p class="${logClass}">${data.message}</p>`;
                                progressLog.scrollTop = progressLog.scrollHeight;
                            } else if (data.type === 'complete') {
                                progressFill.style.width = '100%';
                                progressFill.textContent = '100%';
                                progressLog.innerHTML += `<p class="success">✅ ${data.message}</p>`;
                                showAlert(`Import complete! Imported: ${data.imported}, Skipped: ${data.skipped}`, 'success');
                                
                                // Reload stats and jobs
                                setTimeout(() => {
                                    loadStats();
                                    loadJobs();
                                }, 1000);
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Import error:', error);
                progressLog.innerHTML += `<p class="error">❌ Error: ${error.message}</p>`;
                showAlert('Import failed: ' + error.message, 'error');
            } finally {
                btn.disabled = false;
                btn.classList.remove('loading');
                btn.querySelector('.btn-text').textContent = '🌍 Start Import';
            }
        }

        // Delete job
        async function deleteJob(jobId) {
            if (!confirm('Are you sure you want to delete this job?')) return;

            try {
                const response = await fetch('/career_hub/api/delete_job.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ job_id: jobId })
                });

                const data = await response.json();
                
                if (data.success) {
                    showAlert('Job deleted successfully', 'success');
                    loadStats();
                    loadJobs();
                } else {
                    showAlert('Failed to delete job: ' + data.message, 'error');
                }
            } catch (error) {
                showAlert('Error deleting job: ' + error.message, 'error');
            }
        }

        // View job
        function viewJob(jobId) {
            window.location.href = `/career_hub/pages/jobs.php?id=${jobId}`;
        }

        // Show alert
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} show`;
            alert.textContent = message;
            alertContainer.appendChild(alert);

            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }

        // Utility functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays === 0) return 'Today';
            if (diffDays === 1) return 'Yesterday';
            if (diffDays < 7) return `${diffDays} days ago`;
            if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
            return date.toLocaleDateString();
        }
    </script>
</body>
</html>
