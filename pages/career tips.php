<?php
// Career tips page
// Teaching: This is a primarily static content page that requires the user
// to be authenticated (includes/auth_check.php). Static pages are a good place
// to link to external resources and provide curated content for students.
require_once __DIR__ . '/../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Career Tips & Resources | Career Connect Hub</title>
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
        <h1 style="font-size: 2.5rem; margin-bottom: 15px;">💡 Career Tips & Resources</h1>
        <p style="font-size: 1.2rem; color: var(--text-secondary);">Expert advice, guides, and resources to help you succeed in your career journey</p>
    </section>

    <div style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">

        <section style="margin-bottom: 50px;">
            <h2 style="font-size: 2rem; margin-bottom: 25px;">📝 Featured Articles</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px;"> 
                <a href="https://www.indeed.com/career-advice/resumes-cover-letters/how-to-make-a-resume-with-examples" target="_blank" class="card-form" style="padding: 25px; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <span style="display: inline-block; padding: 5px 12px; background: #0a66c2; color: white; border-radius: 20px; font-size: 0.85rem; margin-bottom: 15px;">Career Advice</span>
                    <h3 style="font-size: 1.3rem; margin: 10px 0; color: var(--text-primary);">💼 Crafting a Standout Resume</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">Learn how to create a resume that highlights your skills and experiences, even with limited work history.</p>
                    <span style="color: var(--linkedin-blue); margin-top: 15px; display: inline-block;">Read More →</span>
                </a>
                <a href="interview.php" class="card-form" style="padding: 25px; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <span style="display: inline-block; padding: 5px 12px; background: #10b981; color: white; border-radius: 20px; font-size: 0.85rem; margin-bottom: 15px;">Interview Prep</span>
                    <h3 style="font-size: 1.3rem; margin: 10px 0; color: var(--text-primary);">🎥 Ace Your Interview</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">Prepare for your next interview with our guide to frequently asked questions and strategies for providing strong responses.</p>
                    <span style="color: var(--linkedin-blue); margin-top: 15px; display: inline-block;">Read More →</span>
                </a>
                <a href="https://www.linkedin.com/pulse/networking-tips-students-career-center/" target="_blank" class="card-form" style="padding: 25px; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <span style="display: inline-block; padding: 5px 12px; background: #f59e0b; color: white; border-radius: 20px; font-size: 0.85rem; margin-bottom: 15px;">Networking</span>
                    <h3 style="font-size: 1.3rem; margin: 10px 0; color: var(--text-primary);">🤝 Building Your Professional Network</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">Discover effective networking techniques to connect with industry professionals and expand your career opportunities.</p>
                    <span style="color: var(--linkedin-blue); margin-top: 15px; display: inline-block;">Read More →</span>
                </a>
            </div>
        </section>

        <section style="margin-bottom: 50px;">
            <h2 style="font-size: 2rem; margin-bottom: 25px;">🎯 Interactive Guides</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px;">
                <a href="https://www.16personalities.com/free-personality-test" target="_blank" class="card-form" style="padding: 25px; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <span style="display: inline-block; padding: 5px 12px; background: #8b5cf6; color: white; border-radius: 20px; font-size: 0.85rem; margin-bottom: 15px;">Career Assessment</span>
                    <h3 style="font-size: 1.3rem; margin: 10px 0; color: var(--text-primary);">🧠 Find Your Ideal Career Path</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">Take this personality test to explore potential career paths that align with your interests and skills.</p>
                    <span style="color: var(--linkedin-blue); margin-top: 15px; display: inline-block;">Take Quiz →</span>
                </a>
                <a href="https://www.themuse.com/advice/job-search-checklist" target="_blank" class="card-form" style="padding: 25px; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <span style="display: inline-block; padding: 5px 12px; background: #ec4899; color: white; border-radius: 20px; font-size: 0.85rem; margin-bottom: 15px;">Job Search</span>
                    <h3 style="font-size: 1.3rem; margin: 10px 0; color: var(--text-primary);">✅ Job Search Checklist</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">Use this comprehensive checklist to ensure you're covering all the essential steps in your job search process.</p>
                    <span style="color: var(--linkedin-blue); margin-top: 15px; display: inline-block;">View Checklist →</span>
                </a>
                <a href="https://www.pramp.com/" target="_blank" class="card-form" style="padding: 25px; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <span style="display: inline-block; padding: 5px 12px; background: #06b6d4; color: white; border-radius: 20px; font-size: 0.85rem; margin-bottom: 15px;">Practice</span>
                    <h3 style="font-size: 1.3rem; margin: 10px 0; color: var(--text-primary);">🎙️ Mock Interview Practice</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">Practice your interview skills with peer-to-peer mock interviews and get real-time feedback.</p>
                    <span style="color: var(--linkedin-blue); margin-top: 15px; display: inline-block;">Start Practicing →</span>
                </a>
            </div>
        </section>

        <section style="margin-bottom: 50px;">
            <h2 style="font-size: 2rem; margin-bottom: 25px;">🎥 Video Resources</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px;">
                <a href="https://www.youtube.com/watch?v=2_Ol7As_i18" target="_blank" class="card-form" style="padding: 25px; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <span style="display: inline-block; padding: 5px 12px; background: #ef4444; color: white; border-radius: 20px; font-size: 0.85rem; margin-bottom: 15px;">Expert Advice</span>
                    <h3 style="font-size: 1.3rem; margin: 10px 0; color: var(--text-primary);">🎬 Career Advice from Industry Experts</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">Watch panel discussions featuring career experts sharing valuable advice and insights for job seekers.</p>
                    <span style="color: var(--linkedin-blue); margin-top: 15px; display: inline-block;">Watch Video →</span>
                </a>
                <a href="https://www.coursera.org/learn/wharton-communication-skills" target="_blank" class="card-form" style="padding: 25px; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <span style="display: inline-block; padding: 5px 12px; background: #3b82f6; color: white; border-radius: 20px; font-size: 0.85rem; margin-bottom: 15px;">Skill Development</span>
                    <h3 style="font-size: 1.3rem; margin: 10px 0; color: var(--text-primary);">🗣️ Communication Skills Course</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">Free course to enhance your communication and collaboration skills, crucial for workplace success.</p>
                    <span style="color: var(--linkedin-blue); margin-top: 15px; display: inline-block;">Start Course →</span>
                </a>
                <a href="https://www.youtube.com/watch?v=WChywVnwJHU" target="_blank" class="card-form" style="padding: 25px; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <span style="display: inline-block; padding: 5px 12px; background: #10b981; color: white; border-radius: 20px; font-size: 0.85rem; margin-bottom: 15px;">Finance</span>
                    <h3 style="font-size: 1.3rem; margin: 10px 0; color: var(--text-primary);">💰 How to Negotiate Your Salary</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">A step-by-step guide to confidently negotiate your salary offer for your first job.</p>
                    <span style="color: var(--linkedin-blue); margin-top: 15px; display: inline-block;">Watch Guide →</span>
                </a>
            </div>
        </section>
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