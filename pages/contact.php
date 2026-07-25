<?php
// Contact page - requires user to be authenticated
// Teaching: The contact form posts to an API endpoint (/api/contact.php).
// - Client-side forms should validate input, but server-side validation is required.
// - Be careful with auto-submitted HTML: always sanitize and validate on the server.
require_once __DIR__ . '/../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/> <!-- Character encoding -->
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/> <!-- Responsive viewport -->
    <title>Career Connect Hub - Contact Us</title>
    <link rel="stylesheet" href="../css/global.css"> <!-- Global styles -->
    <link rel="stylesheet" href="../css/responsive.css"> <!-- Responsive styles -->
</head>
<body>
  <?php include_once __DIR__ . '/../includes/navbar.php'; ?> <!-- Include navigation bar -->


<main class="center-container">
<div class="card-form login-form"> <!-- Contact form card -->
        <!-- Contact form (submits to contact API) -->
        <form method="POST" action="/api/contact.php" class="contact-form">
            <!-- First and last name fields (side by side) -->
            <div style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label for="firstName">First Name</label>
                    <input id="firstName" name="firstName" placeholder="Your first name" type="text">
                </div>
                <div style="flex: 1;">
                    <label for="lastName">Last Name</label>
                    <input id="lastName" name="lastName" placeholder="Your last name" type="text">
                </div>
            </div>

            <!-- Email field -->
            <label for="email">Email Address</label>
            <input id="email" name="email" placeholder="you@example.com" type="email">

            <!-- Subject dropdown -->
            <label for="subject">Subject</label>
            <select id="subject" name="subject">
                <option disabled selected value="">Select a subject</option>
                <option value="General Inquiry">General Inquiry</option>
                <option value="Technical Support">Technical Support</option>
                <option value="Employer Partnership">Employer Partnership</option>
                <option value="Media Inquiry">Media Inquiry</option>
            </select>

            <!-- Message textarea -->
            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="Type your message here..." rows="5"></textarea>

            <!-- Submit button -->
            <button class="btn btn-primary" type="submit">Send Message</button>
        </form>

        <!-- Alternative contact method (phone) -->
        <div class="text-center margin-top-large">
            <p class="text-medium">Or call us at +256 779669429</p>
        </div>
    </div>
</main>
<!-- Footer section with social links -->
<footer class="footer">
<div class="page-content-wrapper footer-content">
<p>© 2025 CaReeR CoNNect HuB. All rights reserved.</p>
<div class="footer-social">
 <span> For more info </span>
  <!-- Instagram link -->
  <a href="https://www.instagram.com/my.preciouspabz" target="_blank" aria-label="Instagram" class="social-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
      <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
      <circle cx="17.5" cy="6.5" r="1.5"/>
    </svg>
  </a>

  <!-- GitHub link -->
  <a href="https://github.com/pabz123" target="_blank" aria-label="GitHub" class="social-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M9 19c-4.5 1.5-4.5-2.5-6-3m12 5v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 18 2.77 5.07 5.07 0 0 0 17.91 0S16.73.35 14 2.48a13.38 13.38 0 0 0-8 0C3.27.35 2.09 0 2.09 0A5.07 5.07 0 0 0 2 2.77 5.44 5.44 0 0 0 .5 8.5c0 5.42 3.3 6.61 6.44 7a3.37 3.37 0 0 0-.94 2.61V21"/>
    </svg>
  </a>

  <!-- LinkedIn link -->
  <a href="https://www.linkedin.com/in/pabz" target="_blank" aria-label="LinkedIn" class="social-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2h-1v9h-4v-9h-4v9H3V9h4v1.2a4.6 4.6 0 0 1 4-2.2z"/>
      <rect x="2" y="9" width="4" height="12"/>
      <circle cx="4" cy="4" r="2"/>
    </svg>
  </a>
</footer>

<script> // Theme toggle functionality
  // Get theme toggle button and body element
  const toggle = document.getElementById('theme-toggle');
  const body = document.body;

  // Load saved theme preference from localStorage
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'light') {
    body.classList.add('light-theme'); // Apply light theme
    toggle.textContent = '🌙 Dark Mode';
  }

  // Toggle theme on button click
  toggle.addEventListener('click', () => {
    const isLight = body.classList.toggle('light-theme'); // Toggle theme class
    toggle.textContent = isLight ? '🌙 Dark Mode' : '☀️ Light Mode'; // Update button text
    localStorage.setItem('theme', isLight ? 'light' : 'dark'); // Save preference
  });
</script>
</body>
</html>