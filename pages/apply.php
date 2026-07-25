<?php
// apply.php - Job application page
// Teaching: This page shows how to safely accept a jobId from the URL and
// fetch the corresponding job using a prepared statement. Key lessons:
// - Cast and validate all external input (here we cast to int and check >0).
// - Use prepared statements for queries that include user-provided values.

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/auth_check.php'; // ensures user is logged in
require_once __DIR__ . '/../includes/db.php';

// Read jobId from query string and cast to int to avoid injection via malformed values
$jobId = isset($_GET['jobId']) ? (int)$_GET['jobId'] : 0;
if ($jobId === 0) {
  // Fail fast with a minimal message. In production consider a friendly 404 page.
  die('Invalid Job ID');
}

// Prepared SELECT to fetch job details and employer info. Prepared statements
// are essential when inserting user data into queries.
$stmt = $conn->prepare("
    SELECT 
        jobs.id,
        jobs.title,
        jobs.location,
        jobs.description,
        employers.company_name AS company
    FROM jobs
    INNER JOIN employers ON jobs.employer_id = employers.id
    WHERE jobs.id = ?
");
$stmt->bind_param('i', $jobId);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();
$stmt->close();

if (!$job) {
  die('Job not found');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"> <!-- Character encoding -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive viewport -->
  <title>Apply for <?= htmlspecialchars($job['title']) ?> | Career Connect Hub</title> <!-- Dynamic title with XSS protection -->
  <link rel="stylesheet" href="../css/global.css"> <!-- Global styles -->
  <link rel="stylesheet" href="../css/responsive.css"> <!-- Responsive styles -->
  <style> /* Page-specific styles */
    /* Main container for application form (centered, limited width) */
    .apply-container {
      max-width: 700px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    /* Job details header card */
    .job-header {
      background: var(--card-bg); /* Theme-aware background */
      padding: 30px;
      border-radius: 12px;
      margin-bottom: 30px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    /* File upload drop area styling */
    .file-upload-area {
      border: 2px dashed var(--border-color); /* Dashed border */
      border-radius: 12px;
      padding: 40px;
      text-align: center;
      background: var(--card-bg);
      cursor: pointer; /* Show clickable cursor */
      transition: all 0.3s; /* Smooth hover transition */
    }
    /* Hover state for file upload area */
    .file-upload-area:hover {
      border-color: var(--linkedin-blue); /* Blue border on hover */
      background: rgba(10, 102, 194, 0.05); /* Light blue tint */
    }
    /* Hide the actual file input (custom styling via label) */
    .file-upload-area input[type="file"] {
      display: none;
    }
    /* Display selected filename */
    .file-name {
      margin-top: 10px;
      color: var(--linkedin-blue);
      font-weight: 600;
    }
  </style>
</head>
<body>
  <?php include_once __DIR__ . '/../includes/navbar.php'; ?> <!-- Include navigation bar -->

<main class="page-content-wrapper">
  <div class="apply-container">
    <!-- Back button (uses browser history) -->
    <button onclick="window.history.back()" class="btn btn-secondary" style="margin-bottom: 20px;">
      ← Back
    </button>
    
    <!-- Job information header card -->
    <div class="job-header">
      <!-- Job title (XSS-safe) -->
      <h1 style="font-size: 2rem; margin-bottom: 10px;"><?= htmlspecialchars($job['title']) ?></h1>
      <!-- Company name -->
      <p style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 5px;">
        <strong><?= htmlspecialchars($job['company'] ?? 'Company') ?></strong>
      </p>
      <!-- Job location -->
      <p style="color: var(--text-secondary);">
        📍 <?= htmlspecialchars($job['location'] ?? 'Location') ?>
      </p>
    </div>
    
    <!-- Application form card -->
    <div class="card-form">
      <h2 style="margin-bottom: 25px;">📝 Application Form</h2>
      
      <!-- Application form (submits to applications API with file upload support) -->
      <form id="applyForm" method="post" action="../api/applications.php" enctype="multipart/form-data">
        <!-- Hidden field: job ID for server-side processing -->
        <input type="hidden" name="jobId" id="jobId" value="<?= $jobId ?>"/>

        <!-- Full name field -->
        <div style="margin-bottom: 20px;">
          <label for="fullName" style="display: block; margin-bottom: 8px; font-weight: 600;">Full Name *</label>
          <input id="fullName" name="fullName" type="text" placeholder="" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--card-bg); color: var(--text-primary);" />
        </div>

        <!-- Email field -->
        <div style="margin-bottom: 20px;">
          <label for="email" style="display: block; margin-bottom: 8px; font-weight: 600;">Email Address *</label>
          <input id="email" name="email" type="email" placeholder="" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--card-bg); color: var(--text-primary);" />
        </div>

        <!-- Phone number field -->
        <div style="margin-bottom: 20px;">
          <label for="phone" style="display: block; margin-bottom: 8px; font-weight: 600;">Phone Number *</label>
          <input id="phone" name="phone" type="tel" placeholder="" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--card-bg); color: var(--text-primary);" />
        </div>

        <!-- Cover letter textarea (optional) -->
        <div style="margin-bottom: 20px;">
          <label for="coverLetter" style="display: block; margin-bottom: 8px; font-weight: 600;">Cover Letter (Optional)</label>
          <textarea id="coverLetter" name="coverLetter" placeholder="Tell us why you're a great fit for this role..." rows="6" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--card-bg); color: var(--text-primary); resize: vertical;"></textarea>
        </div>

        <!-- Resume/CV file upload section -->
        <div style="margin-bottom: 25px;">
          <label style="display: block; margin-bottom: 8px; font-weight: 600;">Resume/CV Upload *</label>
          <!-- Clickable drop area for file upload -->
          <div class="file-upload-area" onclick="document.getElementById('cvFile').click()">
            <!-- Hidden file input (triggered by clicking drop area) -->
            <input id="cvFile" name="cvFile" type="file" accept=".pdf,.doc,.docx" required />
            <div style="font-size: 3rem; margin-bottom: 10px;">📄</div> <!-- File icon -->
            <p style="font-size: 1.1rem; margin-bottom: 5px;">Click to upload or drag and drop</p>
            <p style="font-size: 0.9rem; color: var(--text-secondary);">PDF, DOC, or DOCX (Max 5MB)</p>
            <!-- Filename display (shown after file selection) -->
            <div id="fileName" class="file-name" style="display: none;"></div>
          </div>
        </div>

        <!-- Submit button -->
        <button class="btn btn-primary" type="submit" style="width: 100%; padding: 14px; font-size: 1.1rem;">
          🚀 Submit Application
        </button>
      </form>
    </div>
  </div>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?> <!-- Include site footer -->
<script> // JavaScript for form functionality
// Load theme
const savedTheme = localStorage.getItem('theme') || 'dark';
document.body.classList.add(savedTheme + '-theme');

// File upload display
const fileInput = document.getElementById('cvFile');
const fileName = document.getElementById('fileName');

fileInput.addEventListener('change', (e) => {
  if (e.target.files.length > 0) {
    fileName.textContent = '✓ ' + e.target.files[0].name;
    fileName.style.display = 'block';
  }
});

// Form submission
document.getElementById('applyForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const submitBtn = e.target.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;
  submitBtn.textContent = '⏳ Submitting...';
  submitBtn.disabled = true;

  const form = e.target;
  const formData = new FormData(form);

  try {
    const response = await fetch(form.action, {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      alert('✅ Application submitted successfully!\n\nWe\'ll review your application and get back to you soon.');
      window.location.href = 'my-applications.php';
    } else {
      alert('❌ Error: ' + (result.error || 'Failed to submit application'));
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    }
  } catch (error) {
    alert('❌ Network error. Please check your connection and try again.');
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  }
});
</script>


</body>
</html>
