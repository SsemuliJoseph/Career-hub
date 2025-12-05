<?php
require_once 'includes/db.php';

echo "<h2>Jobs Database Test</h2>";

// Check if jobs table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'jobs'");
if ($tableCheck->num_rows > 0) {
    echo "<p>✅ Jobs table exists</p>";
} else {
    echo "<p>❌ Jobs table does NOT exist</p>";
    exit;
}

// Count jobs
$result = $conn->query("SELECT COUNT(*) as count FROM jobs");
$row = $result->fetch_assoc();
echo "<p>Total jobs in database: <strong>" . $row['count'] . "</strong></p>";

// Show sample jobs
echo "<h3>Sample Jobs (first 5):</h3>";
$jobs = $conn->query("SELECT jobs.*, employers.company_name FROM jobs LEFT JOIN employers ON jobs.employer_id = employers.id LIMIT 5");

if ($jobs->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Title</th><th>Company</th><th>Location</th><th>Created</th></tr>";
    while ($job = $jobs->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $job['id'] . "</td>";
        echo "<td>" . htmlspecialchars($job['title']) . "</td>";
        echo "<td>" . htmlspecialchars($job['company_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($job['location'] ?? 'N/A') . "</td>";
        echo "<td>" . $job['createdAt'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No jobs found in database</p>";
}

// Check employers table
echo "<h3>Employers Check:</h3>";
$empResult = $conn->query("SELECT COUNT(*) as count FROM employers");
$empRow = $empResult->fetch_assoc();
echo "<p>Total employers: <strong>" . $empRow['count'] . "</strong></p>";
?>
