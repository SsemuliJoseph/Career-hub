<?php
/**
 * pages/internship.php - Internship Listings
 * Teaching: This page displays internship opportunities (similar to jobs.php).
 * - It's a static/mock page with hardcoded example internships (no database query yet).
 * - In a real app, you'd query the jobs table filtering by type = 'Internship'.
 * - The page demonstrates client-side search (redirects to jobs.php with search params).
 */
// Path: pages/internship.php
require_once __DIR__ . '/../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>Internship Opportunities | Career Connect Hub</title>
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
<?php include_once __DIR__ . '/../includes/navbar.php'; ?>
<main class="page-content-wrapper">
<section class="hero-section" style="padding: 60px 20px; text-align: center;">
<button onclick="window.history.back()" class="btn btn-secondary" style="margin-bottom: 20px;">
  ← Back
</button>
<h1 style="font-size: 2.5rem; margin-bottom: 15px;">🎓 Internship Opportunities</h1>
<p style="font-size: 1.2rem; margin-bottom: 30px; color: var(--text-secondary);">Browse the latest summer and year-round internships.</p>
<div class="search-bar" style="max-width: 600px; margin: 0 auto; display: flex; gap: 10px;">
<input id="search-input" placeholder="Search internships by company, title, or location..." type="text" style="flex: 1; padding: 12px 20px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--card-bg); color: var(--text-primary);"/>
<button class="btn btn-primary" id="search-button">Search</button>
</div>
</section>
<section style="padding: 40px 20px;">
<div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 250px 1fr; gap: 30px;">
<div class="card-form" style="padding: 25px; height: fit-content;">
<h2 style="font-size: 1.5rem; margin-bottom: 20px;">📊 Filters</h2>
<div style="margin-bottom: 25px;">
<h3 style="font-size: 1.1rem; margin-bottom: 12px; color: var(--linkedin-blue);">Industry</h3>
<label style="display: block; padding: 8px 0; cursor: pointer;"><input type="checkbox" style="margin-right: 8px;"/> Technology</label>
<label style="display: block; padding: 8px 0; cursor: pointer;"><input type="checkbox" style="margin-right: 8px;"/> Finance</label>
<label style="display: block; padding: 8px 0; cursor: pointer;"><input type="checkbox" style="margin-right: 8px;"/> Marketing</label>
<label style="display: block; padding: 8px 0; cursor: pointer;"><input type="checkbox" style="margin-right: 8px;"/> Other</label>
</div>
<div style="margin-bottom: 25px;">
<h3 style="font-size: 1.1rem; margin-bottom: 12px; color: var(--linkedin-blue);">Duration</h3>
<label style="display: block; padding: 8px 0; cursor: pointer;"><input type="checkbox" style="margin-right: 8px;"/> Summer (3 months)</label>
<label style="display: block; padding: 8px 0; cursor: pointer;"><input type="checkbox" style="margin-right: 8px;"/> Year-round</label>
<label style="display: block; padding: 8px 0; cursor: pointer;"><input type="checkbox" style="margin-right: 8px;"/> Part-time</label>
</div>
<button class="btn btn-secondary" style="width: 100%;">Apply Filters</button>
</div>
<div style="display: grid; gap: 20px;">
<div class="card-form" style="padding: 25px; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
<h3 style="font-size: 1.3rem; margin-bottom: 8px; color: var(--linkedin-blue);">💻 Software Developer Intern</h3>
<p class="text-medium" style="margin: 5px 0; color: var(--text-secondary);">Acme Tech Solutions | Remote</p>
<p style="margin: 10px 0 0 0;">Python, AWS. Summer 2025.</p>
<a href="jobs.php" class="btn btn-primary" style="margin-top: 15px; display: inline-block;">View Details</a>
</div>
<div class="card-form" style="padding: 25px; cursor: pointer; transition: transform 0.2s; border: 2px solid var(--linkedin-blue);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
<h3 style="font-size: 1.3rem; margin-bottom: 8px; color: var(--linkedin-blue);">📊 Financial Analyst Intern</h3>
<p class="text-medium" style="margin: 5px 0; color: var(--text-secondary);">Global Finance Group | New York</p>
<p style="margin: 10px 0 0 0;">3-month paid internship, starting June.</p>
<a href="jobs.php" class="btn btn-primary" style="margin-top: 15px; display: inline-block;">View Details</a>
</div>
<div class="card-form" style="padding: 25px; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
<h3 style="font-size: 1.3rem; margin-bottom: 8px; color: var(--linkedin-blue);">📣 Digital Marketing Intern</h3>
<p class="text-medium" style="margin: 5px 0; color: var(--text-secondary);">Creative Agency X | London</p>
<p style="margin: 10px 0 0 0;">Social media focus. Part-time available.</p>
<a href="jobs.php" class="btn btn-primary" style="margin-top: 15px; display: inline-block;">View Details</a>
</div>
</div>
</div>
</section>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<script>
  // Load theme
  const savedTheme = localStorage.getItem('theme') || 'dark';
  document.body.classList.add(savedTheme + '-theme');
  
  // Search functionality
  const searchBtn = document.getElementById('search-button');
  const searchInput = document.getElementById('search-input');
  
  if (searchBtn && searchInput) {
    searchBtn.addEventListener('click', () => {
      const query = searchInput.value.trim();
      if (query) {
        window.location.href = `jobs.php?search=${encodeURIComponent(query)}&type=internship`;
      }
    });
    
    searchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        searchBtn.click();
      }
    });
  }
</script>

</body>
</html>