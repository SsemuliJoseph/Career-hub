<?php
/**
 * pages/interview.php - Interview Preparation Guide
 * Teaching: This is a static content page with career tips for video interviews.
 * - We include auth_check.php to ensure only logged-in users can access it.
 * - The page is mostly HTML with embedded PHP for includes and theme handling.
 * - It demonstrates how to structure educational/informational pages in a web app.
 */
// Path: pages/interview.php
require_once __DIR__ . '/../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Interview Prep Guide | Career Connect Hub</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
<?php include_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="page-content-wrapper">
        <div class="article-container" style="max-width: 900px; margin: 0 auto; padding: 40px 20px;">
        <button onclick="window.history.back()" class="btn btn-secondary" style="margin-bottom: 20px;">
          ← Back
        </button>
		<section>
            <h1 style="font-size: 2.5rem; margin-bottom: 10px;">🎥 Mastering the Video Interview</h1>
            <p class="text-medium" style="font-size: 1.1rem; margin-bottom: 30px; color: var(--text-secondary);">A comprehensive guide to standing out in virtual job interviews.</p>
            
           <div class="article-video" style="margin: 30px 0;">
  <div class="video-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px;">
    <iframe 
      src="https://www.youtube.com/embed/ZU9x1vFx5lI"
      title="Interview Preparation Tips"
      frameborder="0"
      allow="encrypted-media; picture-in-picture"
      allowfullscreen
      style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
    </iframe>
  </div>
</div>


</section>
            <div class="article-content" style="margin-top: 40px;">
			
                <section style="margin-bottom: 40px;">
                    <h2 style="font-size: 1.8rem; margin-bottom: 15px;">💻 Prepare Your Environment</h2>
                    <p class="text-medium" style="line-height: 1.8;">The video interview starts long before you answer the first question. Your background, lighting, and sound quality create your first impression.</p>

                    <h3 style="margin-top: 25px; font-size: 1.3rem; color: var(--linkedin-blue);">Technical Checklist</h3>
                    <ul class="check-list" style="list-style: none; padding: 0;">
                        <li style="padding: 12px 0; display: flex; align-items: start; gap: 10px;">
                            <span style="color: #10b981; font-size: 1.2rem;">✓</span>
                            <p style="margin: 0;">Test your camera and microphone audio levels.</p>
                        </li>
                        <li style="padding: 12px 0; display: flex; align-items: start; gap: 10px;">
                            <span style="color: #10b981; font-size: 1.2rem;">✓</span>
                            <p style="margin: 0;">Ensure your internet connection is stable and fast.</p>
                        </li>
                        <li style="padding: 12px 0; display: flex; align-items: start; gap: 10px;">
                            <span style="color: #10b981; font-size: 1.2rem;">✓</span>
                            <p style="margin: 0;">Charge your computer and have a charger nearby.</p>
                        </li>
                    </ul>
                </section>

                <section style="margin-bottom: 40px;">
                    <h2 style="font-size: 1.8rem; margin-bottom: 15px;">👔 Presentation Tips</h2>
                    <p class="text-medium" style="line-height: 1.8;">Just as in-person, visual presence matters. Pay attention to your frame and posture.</p>

                    <h3 style="margin-top: 25px; font-size: 1.3rem; color: var(--linkedin-blue);">Visual Guidelines</h3>
                    <ul class="check-list" style="list-style: none; padding: 0;">
                        <li style="padding: 12px 0; display: flex; align-items: start; gap: 10px;">
                            <span style="color: #10b981; font-size: 1.2rem;">✓</span>
                            <p style="margin: 0;">Choose a quiet space with a clean, uncluttered background.</p>
                        </li>
                        <li style="padding: 12px 0; display: flex; align-items: start; gap: 10px;">
                            <span style="color: #10b981; font-size: 1.2rem;">✓</span>
                            <p style="margin: 0;">Ensure your face is well-lit, preferably with light coming from in front of you.</p>
                        </li>
                        <li style="padding: 12px 0; display: flex; align-items: start; gap: 10px;">
                            <span style="color: #10b981; font-size: 1.2rem;">✓</span>
                            <p style="margin: 0;">Maintain eye contact with the camera (not the screen).</p>
                        </li>
                        <li style="padding: 12px 0; display: flex; align-items: start; gap: 10px;">
                            <span style="color: #10b981; font-size: 1.2rem;">✓</span>
                            <p style="margin: 0;">Minimize distractions.</p>
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </main>
    
<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<script>
  // Load theme
  const savedTheme = localStorage.getItem('theme') || 'dark';
  document.body.classList.add(savedTheme + '-theme');
</script>

</body>
</html>