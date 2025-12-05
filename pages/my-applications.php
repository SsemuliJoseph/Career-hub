<?php
/**
 * pages/my-applications.php - Student Application History
 * Teaching: This page shows all job applications submitted by the current student.
 * - We use auth_check.php to ensure only logged-in users can access it.
 * - We fetch the user's ID from $_SESSION (populated at login).
 * - We use a prepared statement with INNER JOINs to fetch application details
 *   along with job and employer information in a single query.
 * - DATE_FORMAT() is a MySQL function that formats dates (e.g., 'January 01, 2025').
 */
// Path: pages/my-applications.php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

// Teaching: Get the current user's ID from the session.
// This ID is used to filter applications (only show this user's applications).
$userId = $_SESSION['user']['id'];

// Teaching: Fetch all applications with related job and employer details using JOINs.
// INNER JOIN ensures only applications with matching jobs and employers are returned.
// DATE_FORMAT(..., '%M %d, %Y') formats the date as "January 01, 2025" (MySQL function).
// ORDER BY ... DESC sorts newest applications first.
$stmt = $conn->prepare("
    SELECT 
        applications.*, 
        jobs.title AS job_title,
        jobs.location,
        jobs.description,
        employers.company_name AS company,
        DATE_FORMAT(applications.createdAt, '%M %d, %Y') AS applied_date
    FROM applications
    INNER JOIN jobs ON applications.jobId = jobs.id
    INNER JOIN employers ON jobs.employer_id = employers.id
    WHERE applications.studentId = ?
    ORDER BY applications.createdAt DESC
");
$stmt->bind_param('i', $userId); // Bind the user ID as an integer ('i')
$stmt->execute(); // Execute the prepared statement
$result = $stmt->get_result(); // Get the result set

// Teaching: Loop through the result set and store each row in an array.
// This allows us to iterate over applications in the HTML template below.
$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
$stmt->close(); // Close the statement to free resources
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Applications</title>
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/responsive.css">
  <link rel="stylesheet" href="../css/student.css">
</head>

<body>
  <?php include_once __DIR__ . '/../includes/navbar.php'; ?>

  <main class="profile-container">
    <h1>My Job & Internship Applications</h1>
    <p class="text-medium" style="margin-bottom: 30px;">Track all your job applications in one place</p>
    
    <?php if (count($applications) > 0): ?>
      <div class="applications-grid">
        <?php foreach ($applications as $app): ?>
          <div class="application-card">
            <div class="app-header">
              <h3><?= htmlspecialchars($app['job_title']) ?></h3>
              <span class="status-badge status-<?= strtolower($app['status'] ?? 'Applied') ?>">
                <?= htmlspecialchars(ucfirst($app['status'] ?? 'Applied')) ?>
              </span>
            </div>
            <div class="app-details">
              <p class="company">🏢 <?= htmlspecialchars($app['company'] ?? 'Company') ?></p>
              <p class="location">📍 <?= htmlspecialchars($app['location'] ?? 'Location') ?></p>
              <p class="date">📅 Applied on <?= $app['applied_date'] ?></p>
            </div>
            <?php if (!empty($app['coverLetter'])): ?>
              <div class="cover-letter">
                <strong>Cover Letter:</strong>
                <p><?= nl2br(htmlspecialchars(substr($app['coverLetter'], 0, 150))) ?><?= strlen($app['coverLetter']) > 150 ? '...' : '' ?></p>
              </div>
            <?php endif; ?>
            <div class="app-actions">
              <a href="jobs.php?jobId=<?= $app['jobId'] ?>" class="btn btn-secondary btn-sm">View Job</a>
              <?php if (!empty($app['cv_file'])): ?>
                <a href="../<?= htmlspecialchars($app['cv_file']) ?>" target="_blank" class="btn btn-secondary btn-sm">View CV</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        <h2>No Applications Yet</h2>
        <p>You haven't applied for any jobs yet. Start exploring opportunities!</p>
        <a href="jobs.php" class="btn btn-primary">Browse Jobs</a>
      </div>
    <?php endif; ?>
  </main>

  <?php include_once __DIR__ . '/../includes/footer.php'; ?>

  <style>
    .applications-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 24px;
      margin-top: 20px;
    }
    .application-card {
      background: var(--card-background);
      border-radius: 12px;
      padding: 24px;
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
    }
    .application-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(10, 102, 194, 0.2);
      border-color: var(--linkedin-blue);
    }
    .app-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 16px;
      gap: 12px;
    }
    .app-header h3 {
      margin: 0;
      font-size: 1.3rem;
      color: var(--text-dark);
      flex: 1;
    }
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      white-space: nowrap;
    }
    .status-applied {
      background: rgba(255, 193, 7, 0.2);
      color: #ffc107;
    }
    .status-reviewed {
      background: rgba(33, 150, 243, 0.2);
      color: #2196f3;
    }
    .status-hired {
      background: rgba(76, 175, 80, 0.2);
      color: #4caf50;
    }
    .status-rejected {
      background: rgba(244, 67, 54, 0.2);
      color: #f44336;
    }
    .app-details p {
      margin: 8px 0;
      color: var(--text-medium);
      font-size: 0.95rem;
    }
    .cover-letter {
      margin: 16px 0;
      padding: 12px;
      background: rgba(10, 102, 194, 0.05);
      border-left: 3px solid var(--linkedin-blue);
      border-radius: 4px;
    }
    .cover-letter p {
      margin: 8px 0 0;
      font-size: 0.9rem;
      color: var(--text-medium);
    }
    .app-actions {
      display: flex;
      gap: 12px;
      margin-top: 16px;
    }
    .btn-sm {
      padding: 8px 16px;
      font-size: 0.9rem;
    }
    .empty-state {
      text-align: center;
      padding: 60px 20px;
    }
    .empty-state svg {
      color: var(--text-medium);
      margin-bottom: 20px;
    }
    .empty-state h2 {
      margin: 20px 0 10px;
      color: var(--text-dark);
    }
    .empty-state p {
      color: var(--text-medium);
      margin-bottom: 30px;
    }
    @media (max-width: 768px) {
      .applications-grid {
        grid-template-columns: 1fr;
      }
      .app-actions {
        flex-direction: column;
      }
      .btn-sm {
        width: 100%;
      }
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Load saved theme
      const savedTheme = localStorage.getItem('theme') || 'dark';
      document.body.classList.add(savedTheme + '-theme');
    });
  </script>
</body>
</html>
