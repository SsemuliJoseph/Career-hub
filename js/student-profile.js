/* Teaching: student-profile.js — Profile form behaviors & UI helpers
  Purpose: Handle profile image previews, live completion percentage,
  form submission, initial data loading, theme toggle and logout.
  Concepts shown: DOM selection, FormData, fetch, event delegation,
  localStorage usage, defensive checks for missing elements, and
  graceful handling of offline vs online workflows.
*/
const form = document.getElementById('profileForm');
const completionText = document.getElementById('completion-text');
const completionFill = document.getElementById('completion-fill');
const preview = document.getElementById('profilePreview');
const smallPic = document.getElementById('profile-pic-small');

document.getElementById('profilePic').addEventListener('change', e => {
  const file = e.target.files[0];
  if (file) {
    preview.src = URL.createObjectURL(file);
    smallPic.src = URL.createObjectURL(file);
  }
});

// Calculate completion %
function updateCompletion() {
  const inputs = Array.from(form.querySelectorAll('input, select, textarea'));
  const filled = inputs.filter(i => i.value.trim() !== '').length;
  const percent = Math.round((filled / inputs.length) * 100);
  completionText.textContent = `${percent}%`;
  completionFill.style.width = `${percent}%`;
}

form.addEventListener('input', updateCompletion);

// Save data
form.addEventListener('submit', async e => {
  e.preventDefault();
  const formData = new FormData(form);

  try {
    const res = await fetch('http://localhost:5000/api/student/profile', {
      method: 'POST',
      body: formData,
    });
    const result = await res.json();
    alert(result.message || 'Profile saved successfully!');
  } catch (err) {
    console.error(err);
    alert('Error saving profile');
  }
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
  const logoutBtn = document.getElementById('logout-btn');

  if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
      try {
        // Optionally tell your server to destroy the session
        await fetch('http://localhost:5000/auth/logout', {
          method: 'POST',
          credentials: 'include'
        });

        // Clear localStorage or tokens
        localStorage.removeItem('token');
        localStorage.removeItem('theme');

        alert('You have been logged out.');
        window.location.href = 'login.html';
      } catch (err) {
        console.error('Logout error:', err);
        alert('Could not log out. Please try again.');
      }
  });}
  document.addEventListener("DOMContentLoaded", async () => {
  const user = JSON.parse(localStorage.getItem("user"));
  if (!user) {
    window.location.href = "login.html";
    return;
  }

  // Load current data
  const res = await fetch(`/api/student/${user.id}`);
  const data = await res.json();

  document.getElementById("name").value = data.name || "";
  document.getElementById("email").value = data.email || "";
  document.getElementById("phone").value = data.phone || "";
  document.getElementById("education").value = data.education || "";
  document.getElementById("skills").value = data.skills || "";
  if (data.profile_image) document.getElementById("profilePic").src = data.profile_image;

  // Show progress
  document.getElementById("progress-fill").style.width = data.profile_completion + "%";
  document.getElementById("progress-text").textContent = `${data.profile_completion}% complete`;
});
document.addEventListener("DOMContentLoaded", async () => {
  try {
    const res = await fetch("/auth/session");
    const data = await res.json();

    // If not logged in, go back to login page
    if (!data.loggedIn) {
      window.location.href = "login.html";
      return;
    }

    const user = data.user;

    // ✅ Inject student info into navbar
    const navArea = document.querySelector(".header-actions");
    if (navArea) {
      navArea.innerHTML = `
        <div class="user-info">
          <img src="${user.profile_image || '/assets/images/default-avatar.png'}" 
               alt="Profile" 
               class="profile-thumb"
               style="width:35px;height:35px;border-radius:50%;object-fit:cover;margin-right:8px;">
          <span>Hi, ${user.name}</span>
          <button id="logout-btn" class="btn btn-secondary small-btn">Logout</button>
        </div>
      `;
    }

    // ✅ Handle logout
    document.getElementById("logout-btn")?.addEventListener("click", async () => {
      await fetch("/auth/logout", { method: "POST" });
      localStorage.clear();
      window.location.href = "login.html";
    });

    // ✅ Optionally load more student-specific info
    // (example: profile completion)
    const profileRes = await fetch(`/api/student/${user.id}`);
    if (profileRes.ok) {
      const student = await profileRes.json();
      document.getElementById("profileCompletion")?.innerText = `${student.profile_completion || 0}% Complete`;
    }

  } catch (err) {
    console.error("Session check failed:", err);
    window.location.href = "login.html";
  }
});