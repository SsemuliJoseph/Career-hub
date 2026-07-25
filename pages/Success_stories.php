<?php
/**
 * pages/Success_stories.php
 * Teaching: This is a static content page that displays success stories.
 * - It includes auth_check.php to ensure the user is logged in before viewing.
 * - __DIR__ is a magic constant that returns the directory of this file.
 * - require_once ensures the file is included only once (prevents duplicate inclusion).
 * - The page is mostly HTML with embedded PHP for includes and theme toggling.
 */
require_once __DIR__ . '/../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>Success Stories | Career Connect Hub</title>
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
<?php include_once __DIR__ . '/../includes/navbar.php'; ?>
<main class="page-content-wrapper">
  <section class="hero-section text-center" style="padding: 60px 20px;">
    <button onclick="window.history.back()" class="btn btn-secondary" style="margin-bottom: 20px;">
      ← Back
    </button>
    <h1 style="font-size: 2.5rem; margin-bottom: 20px;">🌟 Success Stories</h1>
    <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto;">Real stories from students and graduates who found their dream careers through Career Connect Hub</p>
  </section>

  <section class="success-stories-grid" style="padding: 40px 20px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto;">
      <div class="card-form" style="padding: 30px;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
          <img alt="Alex Mukulu" class="profile-pic" src="../assets/images/alex.jpg" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;"/>
          <div>
            <h3 style="margin: 0; font-size: 1.2rem;">Alex Mukulu</h3>
            <p class="text-medium" style="margin: 5px 0 0 0; color: var(--text-secondary);">Financial Analyst at Global Corp</p>
          </div>
        </div>
        <p style="font-style: italic; line-height: 1.6;">"I found my first role in Finance. The platform's advice section was key to nailing the interview!"</p>
      </div>
      <div class="card-form" style="padding: 30px;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
          <img alt="David K." class="profile-pic" src="../assets/images/bosco.jpg" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;"/>
          <div>
            <h3 style="margin: 0; font-size: 1.2rem;">David K.</h3>
            <p class="text-medium" style="margin: 5px 0 0 0; color: var(--text-secondary);">Software Engineering Intern at Tech Innovate</p>
          </div>
        </div>
        <p style="font-style: italic; line-height: 1.6;">"The resources here prepared me for my technical interviews better than anything else. Got an internship at a major tech company."</p>
      </div>
      <div class="card-form" style="padding: 30px;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
          <img alt="Maria M." class="profile-pic" src="../assets/images/maria.jpg" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;"/>
          <div>
            <h3 style="margin: 0; font-size: 1.2rem;">Maria M.</h3>
            <p class="text-medium" style="margin: 5px 0 0 0; color: var(--text-secondary);">Project Coordinator at Green Future NGO</p>
          </div>
        </div>
        <p style="font-style: italic; line-height: 1.6;">"As a non-traditional student, I wasn't sure where to start. The Hub simplified everything and connected me to an NGO."</p>
      </div>
      <div class="card-form" style="padding: 30px;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
          <img alt="Bosco Mathew" class="profile-pic" src="../assets/images/bosco.jpg" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;"/>
          <div>
            <h3 style="margin: 0; font-size: 1.2rem;">Bosco Mathew</h3>
            <p class="text-medium" style="margin: 5px 0 0 0; color: var(--text-secondary);">Marketing Associate at Media King</p>
          </div>
        </div>
        <p style="font-style: italic; line-height: 1.6;">"A seamless experience from finding the job to uploading my CV. Highly recommend this for any university student."</p>
      </div>
    </div>
  </section>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<script>
  /**
   * Teaching (JS): Theme loading from localStorage
   * - localStorage.getItem('theme') retrieves the saved theme preference.
   * - The || 'dark' provides a default value if no theme is saved.
   * - classList.add() adds a CSS class to the body element to apply the theme.
   * - This pattern ensures the user's theme preference persists across page loads.
   */
  const savedTheme = localStorage.getItem('theme') || 'dark';
  document.body.classList.add(savedTheme + '-theme');
</script>

</body>
</html>