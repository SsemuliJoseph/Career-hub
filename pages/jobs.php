<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

// Read query parameters using the $_GET superglobal. Always validate or
// sanitize external input — we'll demonstrate safe usage below.
$search = $_GET['search'] ?? '';
$jobId = $_GET['jobId'] ?? '';
$highlightJobId = null;

// Base query selects jobs with the employer company name via LEFT JOIN.
$baseSql = "SELECT jobs.*, employers.company_name AS company FROM jobs LEFT JOIN employers ON jobs.employer_id = employers.id";

// If specific jobId is provided, cast to int to avoid injection and allow
// numeric comparisons later when highlighting a job in the UI.
if (!empty($jobId)) {
  $highlightJobId = (int)$jobId;
}

// We'll use a prepared statement when a search term is provided. This is
// safer than interpolating sanitized strings into SQL, especially with
// LIKE patterns. Prepared statements separate the query from the data.
$jobs = [];
if (!empty($search)) {
  // Build SQL with placeholders for LIKE parameters.
  $sql = $baseSql . " WHERE jobs.title LIKE ? OR employers.company_name LIKE ? OR jobs.location LIKE ? ORDER BY jobs.createdAt DESC";
  $stmt = $conn->prepare($sql);
  if ($stmt) {
    // Use wildcarded parameters for LIKE. bind_param needs actual
    // variables; we build them here.
    $like = "%{$search}%";
    // 'sss' indicates three string parameters.
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $jobs[] = $row;
    }
    $stmt->close();
  } else {
    // Fallback: if prepare fails, log and run a safe escaped query.
    // real_escape_string reduces risk but prepared statements are
    // preferred.
    $safeSearch = $conn->real_escape_string($search);
    $fallbackQuery = $baseSql . " WHERE jobs.title LIKE '%$safeSearch%' OR employers.company_name LIKE '%$safeSearch%' OR jobs.location LIKE '%$safeSearch%' ORDER BY jobs.createdAt DESC";
    $result = $conn->query($fallbackQuery);
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
      }
    }
  }
} else {
  // No search term: fetch recent jobs safely.
  $sql = $baseSql . " ORDER BY jobs.createdAt DESC";
  $result = $conn->query($sql);
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $jobs[] = $row;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Career Connect Hub - Jobs</title>
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/responsive.css">
 <style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: #2f343d;
    color: #fff;
    margin: 0;
    transition: background 0.3s ease, color 0.3s ease;
  }

  header {
    background: #2f343d;
    color: #fff;
    padding: 1rem;
    transition: background 0.3s ease, color 0.3s ease;
  }

  .logo-text {
    font-weight: bold;
    margin-left: .5rem;
  }

  .page-content-wrapper {
    max-width: 1000px;
    margin: auto;
    padding: 1rem;
  }

  .search-row {
    display: flex;
    gap: .5rem;
    margin: 1rem 0;
  }

  .search-row input {
    flex: 1;
    padding: .6rem .8rem;
    border-radius: 8px;
    border: 1px solid #ccc;
  }

  .btn-primary {
    background: #0a66c2;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: .6rem 1rem;
    cursor: pointer;
    transition: background 0.3s ease;
  }

  .btn-primary:hover {
    background: #084b92;
  }

  .job-card {
    background: #3a3f48;
    color: #fff;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.1);
    cursor: pointer;
    transition: background 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
  }

  .job-card:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,.15);
  }

  .job-details {
    display: none;
    margin-top: .5rem;
    border-top: 1px solid #e5e7eb;
    padding-top: .5rem;
  }

  .job-card.active .job-details {
    display: block;
  }

  .small-muted {
    color: #9ca3af;
  }

  /* 🌞 Light Theme */
  .light-theme {
    background: #ffffff;
    color: #111;
  }

  .light-theme header {
    background: #ffffff;  /* Header now white */
    color: #111;          /* Text becomes dark */
    border-bottom: 1px solid #e5e7eb; /* Adds subtle separator */
  }

  .light-theme .nav-links a {
    color: #111;          /* Make links visible on white background */
  }

  .light-theme .logo-text {
    color: #111;
  }

  .light-theme .job-card {
    background: #ffffff;
    color: #111;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .light-theme .small-muted {
    color: #555;
  }

  .light-theme .btn-primary {
    background: #0a66c2;
    color: #fff;
  }

  .light-theme .search-row input {
    background: #fff;
    color: #111;
    border: 1px solid #ccc;
  }

  /* Optional: nav-links styling consistency */
  .nav-links a {
    color: #fff;
    text-decoration: none;
    margin-right: 1rem;
    font-weight: 500;
    transition: color 0.3s ease;
  }

  .nav-links a:hover {
    color: #0a66c2;
  }
</style>


</head>
<body>
  <?php include_once __DIR__ . '/../includes/navbar.php'; ?>

<main class="page-content-wrapper">
  <h1>Explore Jobs</h1>
  <p class="small-muted">Search by title, company or location.</p>

  <div class="search-row">
    <input id="search-input" placeholder="Search jobs..." autocomplete="off">
    <button id="search-button" class="btn-primary">Search</button>
  </div>

  <section id="job-list">
    <?php if (count($jobs)): ?>
      <?php foreach ($jobs as $j): ?>
        <article class="job-card <?= ($highlightJobId && $highlightJobId == $j['id']) ? 'active highlight' : '' ?>" data-id="<?= htmlspecialchars($j['id']) ?>">
          <h3><?= htmlspecialchars($j['title']) ?></h3>
          <div class="small-muted"><?= htmlspecialchars($j['company'] ?? '—') ?> · <?= htmlspecialchars($j['location'] ?? '—') ?></div>
          <p><?= htmlspecialchars(mb_substr($j['description'] ?? '', 0, 120)) ?><?= strlen($j['description'] ?? '')>120 ? '...' : '' ?></p>
          <div class="job-details">
            <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($j['description'] ?? 'No description provided.')) ?></p>
            <p><strong>Requirements:</strong><br><?= nl2br(htmlspecialchars($j['requirements'] ?? 'Not specified.')) ?></p>
            <a href="apply.php?jobId=<?= $j['id'] ?>" class="btn btn-primary">Apply Job</a>

          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="small-muted">No jobs found. Try searching for a position.</p>
    <?php endif; ?>
  </section>
</main>
<footer class="footer">
<div class="page-content-wrapper footer-content">
<p>© 2025 CaReeR CoNNect HuB. All rights reserved.</p>
<div class="footer-social">
 <span> For more info </span>
  <!-- Instagram -->
  <a href="https://www.instagram.com/my.preciouspabz" target="_blank" aria-label="Instagram" class="social-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
      <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
      <circle cx="17.5" cy="6.5" r="1.5"/>
    </svg>
  </a>

  <!-- GitHub -->
  <a href="https://github.com/pabz123" target="_blank" aria-label="GitHub" class="social-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M9 19c-4.5 1.5-4.5-2.5-6-3m12 5v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 18 2.77 5.07 5.07 0 0 0 17.91 0S16.73.35 14 2.48a13.38 13.38 0 0 0-8 0C3.27.35 2.09 0 2.09 0A5.07 5.07 0 0 0 2 2.77 5.44 5.44 0 0 0 .5 8.5c0 5.42 3.3 6.61 6.44 7a3.37 3.37 0 0 0-.94 2.61V21"/>
    </svg>
  </a>

  <!-- LinkedIn -->
  <a href="https://www.linkedin.com/in/pabz" target="_blank" aria-label="LinkedIn" class="social-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2h-1v9h-4v-9h-4v9H3V9h4v1.2a4.6 4.6 0 0 1 4-2.2z"/>
      <rect x="2" y="9" width="4" height="12"/>
      <circle cx="4" cy="4" r="2"/>
    </svg>
  </a>
</div>

</div>
</footer>
<style>
.job-card.highlight {
  border: 2px solid var(--linkedin-blue);
  box-shadow: 0 0 20px rgba(10, 102, 194, 0.3);
  animation: highlightPulse 2s ease-in-out;
}

@keyframes highlightPulse {
  0%, 100% {
    box-shadow: 0 0 20px rgba(10, 102, 194, 0.3);
  }
  50% {
    box-shadow: 0 0 30px rgba(10, 102, 194, 0.6);
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  
  // Load saved theme preference
  const savedTheme = localStorage.getItem('theme') || 'dark';
  body.classList.add(savedTheme + '-theme');

  // Job card dropdown toggle
  const jobCards = document.querySelectorAll('.job-card');
  jobCards.forEach(card => {
    card.addEventListener('click', (e) => {
      // Don't toggle if clicking on Apply button
      if (e.target.tagName === 'A' || e.target.closest('a')) {
        return;
      }
      card.classList.toggle('active');
    });
  });

  // Scroll to highlighted job if exists
  const highlightedJob = document.querySelector('.job-card.highlight');
  if (highlightedJob) {
    setTimeout(() => {
      highlightedJob.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 300);
  }

  // Search functionality
  const searchBtn = document.getElementById('search-button');
  const searchInput = document.getElementById('search-input');
  
  if (searchBtn && searchInput) {
    searchBtn.addEventListener('click', () => {
      const q = searchInput.value.trim();
      if (!q) return location.href = 'jobs.php';
      location.href = `jobs.php?search=${encodeURIComponent(q)}`;
    });
    
    // Allow Enter key to search
    searchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        searchBtn.click();
      }
    });
  }
});
</script>

    

</body>
</html>
