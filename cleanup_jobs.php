<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';

// Check if user is admin - redirect if not
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Redirect to appropriate dashboard based on role
    if (isset($_SESSION['user'])) {
        $role = $_SESSION['user']['role'];
        if ($role === 'student') {
            header('Location: /career_hub/pages/student.php');
        } elseif ($role === 'employer') {
            header('Location: /career_hub/pages/employer.php');
        } else {
            header('Location: /career_hub/pages/login.php?error=unauthorized');
        }
    } else {
        header('Location: /career_hub/pages/login.php?error=unauthorized');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleanup Jobs - Admin Panel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f0f2f5;
        }
        .cleanup-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
        }
        .cleanup-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navibar.php'; ?>
    
    <div class="cleanup-container">
        <div class="cleanup-content">
            <h1>🧹 Clean Up Jobs Database</h1>
            <p>This will remove jobs with overly long descriptions (external API imports with issues)</p>
            <hr>
<?php

try {
    // First, check if there are any applications to delete
    $checkApps = $conn->query("
        SELECT COUNT(*) as count FROM applications a
        INNER JOIN jobs j ON a.jobId = j.id
        WHERE j.external_link IS NOT NULL AND j.external_link != ''
    ");
    $appsToDelete = $checkApps->fetch_assoc()['count'];
    
    if ($appsToDelete > 0) {
        // Delete applications for jobs with external links (imported jobs)
        $deleteApps = $conn->query("
            DELETE a FROM applications a
            INNER JOIN jobs j ON a.jobId = j.id
            WHERE j.external_link IS NOT NULL AND j.external_link != ''
        ");
        
        $appsDeleted = $conn->affected_rows;
        echo "<p style='color: orange;'>🗑️ Deleted $appsDeleted applications linked to external jobs</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ No applications found for external jobs</p>";
    }
    
    // Now delete the jobs with external links (these are the problematic imports)
    $deleteJobs = $conn->query("
        DELETE FROM jobs 
        WHERE external_link IS NOT NULL AND external_link != ''
    ");
    
    $jobsDeleted = $conn->affected_rows;
    echo "<p style='color: green;'>✅ Deleted $jobsDeleted external jobs with long descriptions</p>";
    
    // Keep the manually created jobs (jobs 1-5)
    $remainingQuery = $conn->query("SELECT COUNT(*) as count FROM jobs");
    $remaining = $remainingQuery->fetch_assoc()['count'];
    
    echo "<p style='color: blue;'>📊 Remaining jobs in database: $remaining</p>";
    
    echo "<hr>";
    echo "<h2>✨ Database cleaned successfully!</h2>";
    echo "<p>You can now import jobs with the new improved system that creates:</p>";
    echo "<ul>";
    echo "<li>✅ Summarized descriptions (500-1000 characters)</li>";
    echo "<li>✅ Extracted requirements and responsibilities</li>";
    echo "<li>✅ Proper application_method set to 'External'</li>";
    echo "<li>✅ Industry classification</li>";
    echo "<li>✅ Clean, readable job data</li>";
    echo "</ul>";
    
    echo "<br>";
    echo "<div style='text-align: center;'>";
    echo "<a href='/career_hub/pages/import-jobs.php' style='display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 18px; margin: 10px;'>🌍 Go to Import Jobs Page</a>";
    echo "<a href='/career_hub/pages/admin.php' style='display: inline-block; background: #48bb78; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 18px; margin: 10px;'>� Admin Dashboard</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fc8181; color: white; padding: 20px; border-radius: 10px;'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
        </div>
    </div>
</body>
</html>
<html>
<head>
    <title>Cleanup Complete</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        h1, h2 {
            color: white;
        }
        p, ul {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        hr {
            border: none;
            border-top: 2px solid rgba(255,255,255,0.3);
            margin: 20px 0;
        }
    </style>
</head>
<body>
</body>
</html>
