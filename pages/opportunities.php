<?php
/**
 * pages/opportunities.php (career-opportunities.php)
 * Teaching: This page displays all open job opportunities.
 * - We use auth_check.php to ensure the user is logged in.
 * - We fetch jobs with a prepared statement using an INNER JOIN to include employer info.
 * - The WHERE clause filters for jobs with status = 'Open' to show only active listings.
 * - DATE_FORMAT() formats the createdAt timestamp into a readable format.
 * - LIMIT 50 restricts the result to 50 rows for performance (pagination would be better for large datasets).
 */
// Path: pages/career-opportunities.php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

// Debug mode (optional; remove in production)
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Teaching: Fetch all open jobs with employer information.
// INNER JOIN links the jobs table to the employers table via employer_id.
// This allows us to show the company name alongside job details.
// WHERE jobs.status = 'Open' filters out closed/archived jobs.
// ORDER BY jobs.createdAt DESC shows newest jobs first.
// LIMIT 50 restricts the query to 50 rows (for performance).
$stmt = $conn->prepare("
    SELECT 
        jobs.id,
        jobs.title,
        jobs.type AS job_type,
        jobs.location,
        jobs.industry,
        jobs.application_deadline,
        jobs.status,
        employers.company_name AS company,
        DATE_FORMAT(jobs.createdAt, '%M %d, %Y') AS posted_date
    FROM jobs
    INNER JOIN employers ON jobs.employer_id = employers.id
    WHERE jobs.status = 'Open'
    ORDER BY jobs.createdAt DESC
    LIMIT 50
");
$stmt->execute(); // Execute the query
$result = $stmt->get_result(); // Get result set

// Teaching: Store all rows in an array for template iteration.
$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}
$stmt->close(); // Close statement to free resources
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>Career Opportunities | Career Connect Hub</title>
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/responsive.css">
  <style>
    .opportunities-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 25px;
      margin-top: 30px;
    }
    .opportunity-card {
      background: var(--card-bg);
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s, box-shadow 0.2s;
      cursor: pointer;
    }
    .opportunity-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    .job-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      margin-right: 8px;
    }
    .badge-fulltime { background: #dbeafe; color: #1e40af; }
    .badge-internship { background: #d1fae5; color: #065f46; }
    .badge-parttime { background: #fef3c7; color: #92400e; }
    .badge-contract { background: #e0e7ff; color: #4338ca; }
  </style>
</head>
<body>
  <?php include_once __DIR__ . '/../includes/navbar.php'; ?>

  <main class="page-content-wrapper" style="padding: 40px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
      <button onclick="window.history.back()" class="btn btn-secondary" style="margin-bottom: 20px;">
        ← Back
      </button>
      
      <h1 style="font-size: 2.5rem; margin-bottom: 10px;">💼 Career Opportunities</h1>
      <p style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 30px;">
        Explore all available job openings and career advancement opportunities
      </p>

      <?php if (empty($jobs)): ?>
        <div style="text-align: center; padding: 60px 20px;">
          <div style="font-size: 4rem; margin-bottom: 20px;">💼</div>
          <h2>No Opportunities Available</h2>
          <p style="color: var(--text-secondary);">Check back soon for new job postings!</p>
        </div>
      <?php else: ?>
        <div class="opportunities-grid">
          <?php foreach ($jobs as $job): ?>
            <div class="opportunity-card" onclick="window.location.href='jobs.php?id=<?= $job['id'] ?>'">
              <?php 
                $jobType = strtolower($job['job_type'] ?? '');
                $badgeClass = 'badge-fulltime';
                if (strpos($jobType, 'intern') !== false) $badgeClass = 'badge-internship';
                elseif (strpos($jobType, 'part') !== false) $badgeClass = 'badge-parttime';
                elseif (strpos($jobType, 'contract') !== false) $badgeClass = 'badge-contract';
              ?>
              <span class="job-badge <?= $badgeClass ?>">
                <?= htmlspecialchars($job['job_type'] ?? 'Full-time') ?>
              </span>

              <h3 style="font-size: 1.4rem; margin-bottom: 10px; color: var(--text-primary);">
                <?= htmlspecialchars($job['title']) ?>
              </h3>

              <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 8px; color: var(--linkedin-blue);">
                <?= htmlspecialchars($job['company'] ?? 'Company') ?>
              </p>

              <p style="color: var(--text-secondary); margin-bottom: 10px;">
                📍 <?= htmlspecialchars($job['location'] ?? 'Location') ?>
              </p>

              <?php if (!empty($job['industry'])): ?>
                <p style="color: var(--text-secondary); margin-bottom: 10px;">
                  🏢 <?= htmlspecialchars($job['industry']) ?>
                </p>
              <?php endif; ?>

              <p style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 15px;">
                Posted: <?= htmlspecialchars($job['posted_date'] ?? '') ?>
              </p>

              <?php if (!empty($job['application_deadline'])): ?>
                <p style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 5px;">
                  ⏰ Deadline: <?= date('M d, Y', strtotime($job['application_deadline'])) ?>
                </p>
              <?php endif; ?>

              <button class="btn btn-primary" style="width: 100%; margin-top: 15px;"
                onclick="event.stopPropagation(); window.location.href='apply.php?jobId=<?= $job['id'] ?>'">
                Apply Now
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <?php include_once __DIR__ . '/../includes/footer.php'; ?>

  <script>
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.body.classList.add(savedTheme + '-theme');
  </script>
</body>
</html>
