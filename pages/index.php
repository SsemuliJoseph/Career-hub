<?php
// Path: pages/home.php (aka index)
// Teaching notes:
// - This page is a simple PHP-rendered entry point. It includes session
//   handling and a small helpers file which provides convenience functions
//   like `getUserName()` used below.
// - We prefer require_once for these includes so missing critical files stop
//   execution (fail-fast), which is helpful during development.
require_once __DIR__ . '/../includes/session.php'; // starts/resumes session
require_once __DIR__ . '/../includes/helpers.php'; // small helper functions
?>

<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Career Connect Hub</title>
<link rel="stylesheet" href="../css/global.css">
</head>
<body >

<div class="site-wrapper">
<header>
  <nav class="page-content-wrapper header-nav">
    <a class="logo" href="index.php">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2.2">
        <circle cx="32" cy="32" r="30" stroke="#0a66c2"/>
        <rect x="18" y="28" width="28" height="18" rx="3" ry="3" stroke="#0a66c2"/>
        <path d="M24 28v-4h16v4" stroke="#0a66c2"/>
        <path d="M32 12l14 6-14 6-14-6 14-6z" stroke="#0a66c2"/>
        <path d="M32 24v4" stroke="#0a66c2"/>
      </svg>
      <span class="logo-text">CaReeR CoNNect HuB</span>
    </a>

    <!-- Duplicate/old inline script removed. Modernized script lives above near the
         feature section and includes defensive checks and teaching comments. -->
      if (!jobsArray.length) { cont.textContent = 'No jobs yet.'; return; }

      // When injecting HTML from server data prefer building elements and
      // setting textContent to avoid XSS. For brevity we use template
      // strings but ensure values are escaped or trusted.
      cont.innerHTML = jobsArray.map(j => `
        <article>
          <h3>${String(j.title)}</h3>
          <p>${String(j.company)}</p>
          <a href="/pages/jobs.php?id=${encodeURIComponent(j.id)}">View</a>
        </article>
      `).join('');
    } catch (err) {
      console.error('Error loading featured jobs:', err);
    }
  })();

  // Theme toggle: get the button; guard in case the button isn't present.
  const toggle = document.getElementById('theme-toggle');
  const body = document.body;

  // Load saved theme from localStorage. Use 'light' as an explicit state.
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'light') {
    body.classList.add('light-theme');
    if (toggle) toggle.textContent = '🌙 Dark Mode';
  }

  if (toggle) {
    // Toggle theme on button click. classList.toggle returns a boolean
    // indicating whether the class is now present.
    toggle.addEventListener('click', () => {
      const isLight = body.classList.toggle('light-theme');
      toggle.textContent = isLight ? '🌙 Dark Mode' : '☀️ Light Mode';
      localStorage.setItem('theme', isLight ? 'light' : 'dark');
    });
  }

  // Job search: attach click handler only if the search button exists.
  const searchBtn = document.getElementById('job-search-btn');
  if (searchBtn) {
    searchBtn.addEventListener('click', async () => {
      const q = (document.getElementById('job-search-input') || {}).value?.trim() || '';
      // Use encodeURIComponent for query parameter values to avoid
      // malformed URLs when user input contains special characters.
      const res = await fetch(`/api/jobs/search?q=${encodeURIComponent(q)}`);
      if (!res.ok) {
        console.error('Search failed:', res.status);
        return;
      }
      const jobs = await res.json();
      const container = document.getElementById('search-results');
      if (!container) return;

      // jobs may be an array or an object; normalize to an array here.
      const list = Array.isArray(jobs) ? jobs : (jobs.jobs || []);

      container.innerHTML = list.map(j => `
        <div class="job-card">
          <h3>${String(j.title)}</h3>
          <p>${String(j.company)} — ${String(j.location || '')}</p>
          <a href="/jobs/${encodeURIComponent(j.id)}" class="btn btn-secondary">View</a>
          <button class="btn btn-primary apply-btn" data-jobid="${encodeURIComponent(j.id)}" data-title="${String(j.title)}" data-company="${String(j.company)}">Apply</button>
        </div>
      `).join('');

      // attach apply listeners
      document.querySelectorAll('.apply-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
          const jobId = btn.dataset.jobid;
          const title = btn.dataset.title;
          const company = btn.dataset.company;
          const studentEmail = localStorage.getItem('studentEmail');

          // POST JSON to the API. Use try/catch to handle network errors.
          try {
            await fetch('/api/student/apply', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ jobId, studentEmail, title, company }),
              credentials: 'include' // send cookies if your API uses sessions
            });
            alert('Applied');
          } catch (err) {
            console.error('Apply failed', err);
            alert('Failed to apply. Please try again.');
          }
        });
      });
    });
  }

</script>
<div class="testimonial-author">
<img alt="User profile picture" class="profile-pic" src="../assets/images/maria.jpg" width="640px"/>
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

</script>

</body></html>