<?php
// Path: pages/employer-applicants.php
// Purpose: Show applicants for jobs owned by the logged-in employer.
// Teaching notes are added inline to explain PHP/JS interactions.
require_once __DIR__ . '/../includes/auth_check.php';
// Ensure only employers can reach this page. We use the $_SESSION array
// populated at login to check the user's role.
if (($_SESSION['user']['role'] ?? '') !== 'employer') {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Job Applicants | Career Connect Hub</title>
  <link rel="stylesheet" href="../css/global.css" />
  <link rel="stylesheet" href="../css/responsive.css" />
  <style>
    .applicant-card {
      background: var(--card-bg);
      padding: 25px;
      border-radius: 12px;
      margin: 15px 0;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .applicant-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    .applicant-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }
    .applicant-header {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 15px;
    }
    .applicant-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
    }
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.7);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }
    .modal-content {
      width: 95%;
      max-width: 800px;
      max-height: 90vh;
      overflow: auto;
      background: var(--card-bg);
      padding: 30px;
      border-radius: 12px;
      position: relative;
    }
    .close-btn {
      position: absolute;
      top: 15px;
      right: 20px;
      font-size: 28px;
      cursor: pointer;
      background: none;
      border: none;
      color: var(--text-primary);
    }
    .no-applicants {
      text-align: center;
      padding: 60px 20px;
      color: var(--text-secondary);
      font-size: 1.1rem;
    }
    .status-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-reviewed { background: #dbeafe; color: #1e40af; }
  </style>
</head>
<body>
  <?php include_once __DIR__ . '/../includes/navbar.php'; ?>

  <main class="page-content-wrapper">
    <section style="padding: 40px 20px;">
      <div style="max-width: 1200px; margin: 0 auto;">
        <button onclick="window.history.back()" class="btn btn-secondary" style="margin-bottom: 20px;">
          ← Back
        </button>
        <h1 style="font-size: 2rem; margin-bottom: 10px;">📋 Job Applicants</h1>
        <p style="color: var(--text-secondary); margin-bottom: 30px;">Review and manage applications for your posted jobs</p>
        
        <!-- The applicants list is populated by client-side JS which calls
             `../api/get_applicants.php`. This keeps the page responsive and
             separates rendering (HTML) from data retrieval (API). -->
        <div id="applicants-list" class="applicant-grid"></div>
      </div>
    </section>

    <!-- Modal -->
    <div id="applicant-modal" class="modal-overlay">
      <div class="modal-content">
        <button id="close-modal" class="close-btn" aria-label="Close">&times;</button>
        <div id="applicant-details"></div>
      </div>
    </div>
  </main>

  <?php include_once __DIR__ . '/../includes/footer.php'; ?>

  <script>
  (function () {
    const listEl = document.getElementById('applicants-list');
    const modal = document.getElementById('applicant-modal');
    const modalDetails = document.getElementById('applicant-details');
    const closeBtn = document.getElementById('close-modal');

    // Load theme
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.body.classList.add(savedTheme + '-theme');

    // Fetch applicants
    async function loadApplicants() {
      // Guard: make sure the DOM element exists before doing work.
      if (!listEl) return;

      // Show a temporary loading state while we fetch from the API.
      listEl.innerHTML = '<div class="no-applicants">🔄 Loading applicants...</div>';
      try {
        // fetch() is the modern way to do AJAX in browser JS. We include
        // credentials so cookies (session id) are sent with the request.
        const res = await fetch('../api/get_applicants.php', { credentials:'include' });
        if (!res.ok) throw new Error('Server error');

        // Parse JSON response. The API should return a JSON object with
        // an `applicants` array. Always validate/normalize server data.
        const payload = await res.json();
        const applicants = payload.applicants || [];

        if (!applicants.length) {
          // No applicants -> show a friendly empty state.
          listEl.innerHTML = `<div class="no-applicants">
            <div style="font-size: 3rem; margin-bottom: 20px;">💼</div>
            <h3>No Applicants Yet</h3>
            <p>Once students apply to your jobs, they will appear here.</p>
          </div>`;
          return;
        }

        // Build HTML for each applicant. Note: we sanitize the JSON string
        // to avoid injecting closing tags. For robust XSS prevention use a
        // templating library or set textContent on elements rather than
        // concatenating strings.
        listEl.innerHTML = applicants.map(a => {
          const name = String(a.full_name || 'Unnamed');
          const title = String(a.title || 'Applied Role');
          const email = String(a.email || '');
          const appliedDate = a.applied_at ? new Date(a.applied_at).toLocaleDateString() : 'Recently';
          const profileImg = a.profile_image || '/uploads/profile/default-avatar.png';
          // Escape '<' to prevent breaking out of the attribute in the
          // generated HTML. We also escape single quotes used in the data-attr.
          const json = JSON.stringify(a).replace(/</g,'\\u003c').replace(/'/g, "\\'");
          
          return `
            <div class="applicant-card">
              <div class="applicant-header">
                <img src="${profileImg}" alt="${name}" class="applicant-avatar">
                <div>
                  <h3 style="margin: 0; font-size: 1.2rem;">${name}</h3>
                  <p style="margin: 5px 0; color: var(--text-secondary); font-size: 0.9rem;">${email}</p>
                </div>
              </div>
              <p style="margin: 10px 0;"><strong>💼 Position:</strong> ${title}</p>
              <p style="margin: 10px 0; color: var(--text-secondary); font-size: 0.9rem;">📅 Applied: ${appliedDate}</p>
              <button class="btn btn-primary view-btn" data-json='${json}' style="width: 100%; margin-top: 15px;">View Full Application</button>
            </div>
          `;
        }).join('');
        // After injecting HTML, attach event handlers to the buttons.
        attachViewHandlers();
      } catch (err) {
        // Show an error state and log the error for debugging.
        console.error('Failed to load applicants', err);
        listEl.innerHTML = `<div class="no-applicants">
          <div style="font-size: 3rem; margin-bottom: 20px;">⚠️</div>
          <h3>Could Not Load Applicants</h3>
          <p>Please try again later or contact support.</p>
        </div>`;
      }
    }

    function attachViewHandlers() {
      document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const a = JSON.parse(btn.dataset.json);
          const name = a.full_name || 'Unnamed';
          const email = a.email || 'N/A';
          const phone = a.phone || 'Not provided';
          const skills = a.skills || 'Not provided';
          const education = a.education || 'Not specified';
          const experience = a.experience || 'Not specified';
          const coverLetter = a.cover_letter || 'No cover letter provided';
          const profilePic = a.profile_image || '/uploads/profile/default-avatar.png';
          const cvFile = a.cv_file ? `../uploads/cvs/${a.cv_file}` : null;

          modalDetails.innerHTML = `
            <div style="text-align: center; margin-bottom: 30px;">
              <img src="${profilePic}" alt="Profile" style="width:100px;height:100px;border-radius:50%;margin-bottom:15px;">
              <h2 style="margin: 10px 0;">${name}</h2>
              <p style="color: var(--text-secondary);">${email}</p>
            </div>
            
            <div style="margin-bottom: 25px;">
              <h3 style="font-size: 1.3rem; margin-bottom: 10px; color: var(--linkedin-blue);">📞 Contact Information</h3>
              <p><strong>Email:</strong> ${email}</p>
              <p><strong>Phone:</strong> ${phone}</p>
            </div>
            
            <div style="margin-bottom: 25px;">
              <h3 style="font-size: 1.3rem; margin-bottom: 10px; color: var(--linkedin-blue);">🎓 Education</h3>
              <p>${education}</p>
            </div>
            
            <div style="margin-bottom: 25px;">
              <h3 style="font-size: 1.3rem; margin-bottom: 10px; color: var(--linkedin-blue);">💼 Experience</h3>
              <p>${experience}</p>
            </div>
            
            <div style="margin-bottom: 25px;">
              <h3 style="font-size: 1.3rem; margin-bottom: 10px; color: var(--linkedin-blue);">✨ Skills</h3>
              <p>${skills}</p>
            </div>
            
            <div style="margin-bottom: 25px;">
              <h3 style="font-size: 1.3rem; margin-bottom: 10px; color: var(--linkedin-blue);">📝 Cover Letter</h3>
              <p style="white-space: pre-wrap; line-height: 1.6;">${coverLetter}</p>
            </div>
            
            <div>
              <h3 style="font-size: 1.3rem; margin-bottom: 10px; color: var(--linkedin-blue);">📄 Resume/CV</h3>
              ${cvFile ? `<iframe src="${cvFile}" width="100%" height="600" style="border:1px solid var(--border-color); border-radius:8px;"></iframe>` : '<p style="color: var(--text-secondary);">No CV uploaded</p>'}
            </div>
          `;
          modal.style.display = 'flex';
        });
      });
    }

    // Close modal
    closeBtn && closeBtn.addEventListener('click', () => {
      modal.style.display = 'none';
    });
    
    modal && modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });

    // Initial load
    loadApplicants();
  })();
  </script>
</body>
</html>
