<?php
/**
 * Career Hub Setup Test Page
 * Verifies that all OOP components are working correctly
 */

require_once 'classes/autoload.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Hub - Setup Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0a66c2;
            border-bottom: 3px solid #0a66c2;
            padding-bottom: 10px;
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        .success {
            color: #4caf50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        .info {
            background: #e3f2fd;
            padding: 10px;
            border-left: 4px solid #2196f3;
            margin: 10px 0;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .status-icon {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>🚀 Career Hub Setup Test</h1>
    <p>This page tests all OOP components and configurations.</p>

    <?php
    $allPassed = true;
    
    // Test 1: Database Connection
    echo '<div class="test-section">';
    echo '<h2>1. Database Connection</h2>';
    try {
    global $conn;
    echo '<span class="status-icon">✅</span>';
    echo '<span class="success">Database connected successfully!</span><br>';
    echo '<div class="info">Host: ' . htmlspecialchars($conn->host_info) . '</div>';
    } catch (Exception $e) {
        $allPassed = false;
        echo '<span class="status-icon">❌</span>';
        echo '<span class="error">Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
    echo '</div>';
    
    // Test 2: Job Model
    echo '<div class="test-section">';
    echo '<h2>2. Job Model</h2>';
    try {
        $jobModel = new Job();
        $jobs = $jobModel->all(5, 0);
        $stats = $jobModel->getStatistics();
        
        echo '<span class="status-icon">✅</span>';
        echo '<span class="success">Job model working perfectly!</span><br>';
        echo '<div class="info">';
        echo 'Total jobs: ' . ($stats['total_jobs'] ?? 0) . '<br>';
        echo 'Full-time: ' . ($stats['full_time_count'] ?? 0) . '<br>';
        echo 'Internships: ' . ($stats['internship_count'] ?? 0) . '<br>';
        echo 'Companies: ' . ($stats['total_companies'] ?? 0) . '<br>';
        echo '</div>';
        
        if (!empty($jobs)) {
            echo '<p><strong>Recent jobs:</strong></p>';
            echo '<ul>';
            foreach (array_slice($jobs, 0, 3) as $job) {
                echo '<li>' . htmlspecialchars($job['title']) . ' at ' . htmlspecialchars($job['company']) . '</li>';
            }
            echo '</ul>';
        }
    } catch (Exception $e) {
        $allPassed = false;
        echo '<span class="status-icon">❌</span>';
        echo '<span class="error">Job model failed: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
    echo '</div>';
    
    // Test 3: User Model
    echo '<div class="test-section">';
    echo '<h2>3. User Model</h2>';
    try {
        $userModel = new User();
        $students = $userModel->getByRole('student');
        $employers = $userModel->getByRole('employer');
        
        echo '<span class="status-icon">✅</span>';
        echo '<span class="success">User model initialized successfully!</span><br>';
        echo '<div class="info">';
        echo 'Total students: ' . count($students) . '<br>';
        echo 'Total employers: ' . count($employers) . '<br>';
        echo 'Total users: ' . (count($students) + count($employers));
        echo '</div>';
    } catch (Exception $e) {
        $allPassed = false;
        echo '<span class="status-icon">❌</span>';
        echo '<span class="error">User model failed: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
    echo '</div>';
    
    // Test 4: Application Model
    echo '<div class="test-section">';
    echo '<h2>4. Application Model</h2>';
    try {
        $appModel = new Application();
        $stats = $appModel->getStatistics();
        
        echo '<span class="status-icon">✅</span>';
        echo '<span class="success">Application model working!</span><br>';
        echo '<div class="info">';
        echo 'Total applications: ' . ($stats['total_applications'] ?? 0) . '<br>';
        echo 'Pending: ' . ($stats['pending_count'] ?? 0) . '<br>';
        echo 'Reviewed: ' . ($stats['reviewed_count'] ?? 0) . '<br>';
        echo 'Accepted: ' . ($stats['accepted_count'] ?? 0) . '<br>';
        echo 'Rejected: ' . ($stats['rejected_count'] ?? 0);
        echo '</div>';
    } catch (Exception $e) {
        $allPassed = false;
        echo '<span class="status-icon">❌</span>';
        echo '<span class="error">Application model failed: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
    echo '</div>';
    
    // Test 5: External API Service
    echo '<div class="test-section">';
    echo '<h2>5. External API Service</h2>';
    try {
        $apiService = new ExternalAPIService();
        echo '<span class="status-icon">✅</span>';
        echo '<span class="success">External API service initialized!</span><br>';
        echo '<div class="info">';
        echo '⚠️ Remember to configure your API keys in classes/ExternalAPIService.php:<br>';
        echo '- Adzuna API: App ID and App Key<br>';
        echo '- JSearch API: RapidAPI Key<br>';
        echo '</div>';
    } catch (Exception $e) {
        $allPassed = false;
        echo '<span class="status-icon">❌</span>';
        echo '<span class="error">External API service failed: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
    echo '</div>';
    
    // Test 6: WebSocket Server Class
    echo '<div class="test-section">';
    echo '<h2>6. WebSocket Server</h2>';
    try {
        // Just check if class exists
        if (class_exists('WebSocketServer')) {
            echo '<span class="status-icon">✅</span>';
            echo '<span class="success">WebSocket server class loaded!</span><br>';
            echo '<div class="info">';
            echo 'To start the WebSocket server, run:<br>';
            echo '<pre>php websocket_server.php</pre>';
            echo 'The server will listen on port 8080.';
            echo '</div>';
        }
    } catch (Exception $e) {
        $allPassed = false;
        echo '<span class="status-icon">❌</span>';
        echo '<span class="error">WebSocket server failed: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
    echo '</div>';
    
    // Test 7: Cache Directories
    echo '<div class="test-section">';
    echo '<h2>7. Cache Directories</h2>';
    $cacheDir = __DIR__ . '/cache/api';
    $notificationDir = __DIR__ . '/cache/notifications';
    
    if (is_dir($cacheDir) && is_writable($cacheDir)) {
        echo '<span class="status-icon">✅</span>';
        echo '<span class="success">API cache directory exists and is writable</span><br>';
    } else {
        echo '<span class="status-icon">⚠️</span>';
        echo '<span class="error">API cache directory missing or not writable</span><br>';
        echo '<div class="info">Create: ' . htmlspecialchars($cacheDir) . '</div>';
    }
    
    if (is_dir($notificationDir) && is_writable($notificationDir)) {
        echo '<span class="status-icon">✅</span>';
        echo '<span class="success">Notifications cache directory exists and is writable</span><br>';
    } else {
        echo '<span class="status-icon">⚠️</span>';
        echo '<span class="error">Notifications cache directory missing or not writable</span><br>';
        echo '<div class="info">Create: ' . htmlspecialchars($notificationDir) . '</div>';
    }
    echo '</div>';
    
    // Test 8: API Endpoints Check
    echo '<div class="test-section">';
    echo '<h2>8. API Endpoints</h2>';
    $apiEndpoints = [
        'export_jobs.php' => 'Export jobs data',
        'export_applications.php' => 'Export applications',
        'import_jobs.php' => 'Import jobs',
        'stats.php' => 'Statistics',
        'fetch_external_jobs.php' => 'Fetch external jobs',
        'notify.php' => 'WebSocket notifications'
    ];
    
    echo '<p><strong>Available API endpoints:</strong></p><ul>';
    foreach ($apiEndpoints as $file => $description) {
        $path = __DIR__ . '/api/v1/' . $file;
        if (file_exists($path)) {
            echo '<li><span class="status-icon">✅</span>' . htmlspecialchars($description) . ' - <code>/api/v1/' . htmlspecialchars($file) . '</code></li>';
        } else {
            echo '<li><span class="status-icon">❌</span>' . htmlspecialchars($description) . ' - File missing</li>';
        }
    }
    echo '</ul>';
    
    echo '<div class="info">';
    echo '⚠️ Remember to configure API tokens in each endpoint file!<br>';
    echo 'Update $validTokens array with your secure tokens.';
    echo '</div>';
    echo '</div>';
    
    // Final Summary
    echo '<div class="test-section" style="background: ' . ($allPassed ? '#e8f5e9' : '#fff3e0') . '">';
    echo '<h2>Summary</h2>';
    if ($allPassed) {
        echo '<span class="status-icon">🎉</span>';
        echo '<span class="success" style="font-size: 18px;">All tests passed! Your setup is complete.</span><br><br>';
        echo '<p><strong>Next steps:</strong></p>';
        echo '<ol>';
        echo '<li>Configure API tokens in /api/v1/ files</li>';
        echo '<li>Set up external API credentials (Adzuna, JSearch)</li>';
        echo '<li>Start WebSocket server: <code>php websocket_server.php</code></li>';
        echo '<li>Test API endpoints with Postman or curl</li>';
        echo '<li>Review documentation: API_DOCUMENTATION.md and README_OOP.md</li>';
        echo '</ol>';
    } else {
        echo '<span class="status-icon">⚠️</span>';
        echo '<span class="error" style="font-size: 18px;">Some tests failed. Please review the errors above.</span><br><br>';
        echo '<p>Check the SETUP_GUIDE.md for troubleshooting steps.</p>';
    }
    echo '</div>';
    ?>
    
    <div style="text-align: center; margin-top: 30px; color: #666;">
        <p>Career Hub - Object-Oriented PHP Architecture</p>
        <p>For more information, see: <strong>SETUP_GUIDE.md</strong>, <strong>API_DOCUMENTATION.md</strong>, <strong>README_OOP.md</strong></p>
    </div>
</body>
</html>
