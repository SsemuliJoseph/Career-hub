<?php
/**
 * index.php - Application landing page
 *
 * Teaching notes:
 * - This is the public landing page shown to visitors. It demonstrates a typical PHP pattern:
 *   1) Perform backend bootstrapping (error reporting for dev, session start)
 *   2) Include shared helpers and templates
 *   3) Output HTML with small inline PHP to render dynamic parts (e.g. username)
 *
 * - For larger apps you separate logic (controllers) and views (templates). This project keeps
 *   simple pages as single PHP files for teaching clarity.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and apply global helpers. These files set up secure cookie params and helper
// functions (e.g. sanitization, URL helpers). Including them at the top ensures $_SESSION
// is available when rendering the page.
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Career Connect Hub</title>
  <link rel="stylesheet" href="./css/global.css">
  <link rel="stylesheet" href="./css/responsive.css">
</head>
<body>
    <?php // The navbar is a shared partial. It reads session state to show login/logout links.
      include_once __DIR__ . '/includes/navabar.php'; ?>


      

<main class="page-content-wrapper">
<section class="hero-section">
<?php if (!empty($_SESSION['user'])): ?>
  <p class="welcome-text">Hello, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'User'); ?>!</p>
<?php else: ?>
  <p class="welcome-text">Welcome!</p>
<?php endif; ?>

<h1 class="hero-title">Your Future Starts Now</h1>
<p class="hero-subtitle">Connecting students and graduates with their ideal career opportunities.</p>

<?php if (empty($_SESSION['user'])): ?>
  <p class="cta-text"><a href="pages/login.php" class="link-primary">Login</a> or <a href="pages/signup.php" class="link-primary">Register</a> to get started.</p>
<?php endif; ?>

<!-- Search Bar -->
<div class="search-bar-container">
  <!--
    Teaching: The search box below is interactive via JavaScript at the bottom of this file.
    - The frontend checks if user is logged-in (via a small PHP boolean) before allowing searches.
    - In a production app, the search box would call a paginated API and handle errors/edge cases.
  -->
  <input type="text" id="jobSearch" class="search-input" placeholder="Search for jobs, companies, or skills..." autocomplete="off">
  <button id="searchBtn" class="btn btn-search">Search</button>
</div>

<ul id="suggestions" class="suggestions-list"></ul>




</section>
<section class="feature-section">
<h2>Explore Opportunities</h2>
<div class="grid-3-col">
<div class="feature-card">
  <div class="icon-wrapper">
    <!-- Job icon -->
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="36" height="36">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
            d="M4 7a2 2 0 012-2h12a2 2 0 012 2v2H4V7zm0 4h16v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6"/>
    </svg>
  </div>
  <h3>Jobs</h3>
  <p>Browse full-time and part-time positions.</p>
  <a href="pages/jobs.php">Find Jobs</a>
</div>

<div class="feature-card">
 <div class="icon-wrapper">
 <!-- Internship Icon -->
<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 64 64" width="48" height="48">
  <circle cx="32" cy="18" r="6" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M20 38v-4c0-4 3-8 12-8s12 4 12 8v4" stroke-linecap="round" stroke-linejoin="round"/>
  <rect x="18" y="38" width="28" height="14" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M26 38v-4h12v4" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

 </div>
<h3>Internships</h3>
<p>Gain vital experience with top companies.</p>
<a href="pages/internship.php">View Internships</a>
</div>
<div class="feature-card">
<div class="icon-wrapper">
<!-- Career Tips / Ideas Icon -->
<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 64 64" width="48" height="48">
  <path d="M32 8c-8 0-14 6-14 14 0 6 4 10 7 13v5h14v-5c3-3 7-7 7-13 0-8-6-14-14-14z" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M26 48h12m-10 4h8m-6 4h4" stroke-linecap="round"/>
</svg>

</div>
<h3>Career Tips</h3>
<p>Get advice on CVs, interviews, and networking.</p>
<a href="pages/career tips.php">Get Tips</a>
</div>
</div>
</section>
<section class="testimonial-section">
<h2>Success Stories</h2>
<div class="grid-3-col">
<div class="testimonial-card">
<p>"I landed my dream job thanks to the resources here!"</p>
<div class="testimonial-author">
<img alt="User profile picture" class="profile-pic" src="./assets/images/alex.jpg" width="640px"/>
<span>Alex Mukulu, Class of '24</span>
</div>
</div>
<div class="testimonial-card">
<p>"The internship filter helped me find a role perfectly aligned with my studies."</p>
<div class="testimonial-author">
<img alt="User profile picture" class="profile-pic" src="./assets/images/maria.jpg" width="640px"/>
<span>Maria Jovia Namirembe, Engineering Student</span>
</div>
</div>

</div>
</div>
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
</div>
<script  >
 fetch('/api/jobs.php')
        .then(r=>r.json())
        .then(data=>{
          const cont = document.getElementById('jobsList');
          if(!data.jobs || !data.jobs.length){ cont.innerText = 'No jobs yet.'; return; }
          cont.innerHTML = data.jobs.map(j=>`<article><h3>${j.title}</h3><p>${j.company}</p><a href="/pages/jobs.php?id=${j.id}">View</a></article>`).join('');
        });
  const toggle = document.getElementById('theme-toggle');
  const body = document.body;

  // Load saved theme from localStorage
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'light') {
    body.classList.add('light-theme');
    toggle.textContent = '🌙 Dark Mode';
  }

  // Toggle theme on button click
  toggle.addEventListener('click', () => {
    const isLight = body.classList.toggle('light-theme');
    toggle.textContent = isLight ? '🌙 Dark Mode' : '☀️ Light Mode';
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
  });
  document.getElementById('job-search-btn').addEventListener('click', async () => {
  const q = document.getElementById('job-search-input').value.trim();
  const res = await fetch(`/api/jobs/search?q=${encodeURIComponent(q)}`);
  const jobs = await res.json();
  const container = document.getElementById('search-results');
  container.innerHTML = jobs.map(j => `
    <div class="job-card">
      <h3>${j.title}</h3>
      <p>${j.company} — ${j.location || ''}</p>
      <a href="/jobs/${j.id}" class="btn btn-secondary">View</a>
      <button class="btn btn-primary apply-btn" data-jobid="${j.id}" data-title="${j.title}" data-company="${j.company}">Apply</button>
    </div>
  `).join('');

  // attach apply listeners
  document.querySelectorAll('.apply-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const jobId = btn.dataset.jobid;
      const title = btn.dataset.title;
      const company = btn.dataset.company;
      const studentEmail = localStorage.getItem('studentEmail');
      await fetch('/api/student/apply', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ jobId, studentEmail, title, company })
      });
      alert('Applied');
    });
  });
});
// Load saved theme
const savedTheme = localStorage.getItem('theme') || 'dark';
document.body.classList.add(savedTheme + '-theme');

// Check if user is logged in
const isLoggedIn = <?= !empty($_SESSION['user']) ? 'true' : 'false' ?>;

// Search functionality
const searchInput = document.getElementById('jobSearch');
const searchBtn = document.getElementById('searchBtn');
const suggestionsBox = document.getElementById('suggestions');

// Handle search button click
searchBtn.addEventListener('click', () => {
  const query = searchInput.value.trim();
  
  if (!query) {
    return;
  }
  
  // Require login to search
  if (!isLoggedIn) {
    alert('Please login to search for jobs');
    window.location.href = 'pages/login.php';
    return;
  }
  
  // Redirect to jobs page with search query
  window.location.href = `pages/jobs.php?search=${encodeURIComponent(query)}`;
});

// Handle Enter key
searchInput.addEventListener('keypress', (e) => {
  if (e.key === 'Enter') {
    searchBtn.click();
  }
});

// Autocomplete suggestions
searchInput.addEventListener('input', () => {
  const query = searchInput.value.trim();
  
  if (query.length < 2) {
    suggestionsBox.innerHTML = '';
    suggestionsBox.style.display = 'none';
    return;
  }
  
  // Only show suggestions if logged in
  if (!isLoggedIn) {
    return;
  }
  
  fetch(`api/search_jobs.php?q=${encodeURIComponent(query)}`)
    .then(res => res.json())
    .then(data => {
      suggestionsBox.innerHTML = '';
      if (data.length > 0) {
        suggestionsBox.style.display = 'block';
        data.forEach(job => {
          const li = document.createElement('li');
          li.innerHTML = `<strong>${job.title}</strong><br><small>${job.company || ''} \u00b7 ${job.location || ''}</small>`;
          li.addEventListener('click', () => {
            // Redirect to jobs page with this specific job
            window.location.href = `pages/jobs.php?jobId=${job.id}`;
          });
          suggestionsBox.appendChild(li);
        });
      } else {
        suggestionsBox.style.display = 'block';
        suggestionsBox.innerHTML = '<li class="no-result">No matches found</li>';
      }
    })
    .catch(() => {
      suggestionsBox.innerHTML = '<li class="no-result">Error fetching results</li>';
    });
});

// Close suggestions when clicking outside
document.addEventListener('click', (e) => {
  if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
    suggestionsBox.style.display = 'none';
  }
});

// Hamburger menu for logged-in users
const hamburger = document.getElementById('hamburger-menu');
const dropdown = document.getElementById('dropdown-menu');
const logoutLink = document.getElementById('logout-link');

if (hamburger && dropdown) {
  hamburger.addEventListener('click', (e) => {
    e.stopPropagation();
    hamburger.classList.toggle('active');
    dropdown.classList.toggle('active');
  });
  
  // Close dropdown when clicking outside
  document.addEventListener('click', (e) => {
    if (!hamburger.contains(e.target) && !dropdown.contains(e.target)) {
      hamburger.classList.remove('active');
      dropdown.classList.remove('active');
    }
  });
}

// Logout functionality
if (logoutLink) {
  logoutLink.addEventListener('click', async (e) => {
    e.preventDefault();
    if (confirm('Are you sure you want to logout?')) {
      try {
        await fetch('api/auth/logout.php', { method: 'POST', credentials: 'include' });
      } catch (err) {
        console.error('Logout failed:', err);
      }
      localStorage.clear();
      window.location.href = 'pages/login.php';
    }
  });
}

// Mobile nav toggle
const navHamburger = document.getElementById('nav-hamburger');
const navLinks = document.getElementById('nav-links');

if (navHamburger && navLinks) {
  navHamburger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
  });
}
</script>

</body>
</html>