<?php
// Auto-import jobs from multiple locations and categories
require_once __DIR__ . '/classes/autoload.php';
require_once __DIR__ . '/includes/db.php';

set_time_limit(300); // Allow 5 minutes for import

// Helper function to extract industry
function extractIndustry($title, $description) {
    $title = strtolower($title);
    $description = strtolower($description);
    $combined = $title . ' ' . $description;
    
    // Industry keywords mapping
    $industries = [
        'Technology' => ['software', 'developer', 'engineer', 'it ', 'data', 'tech', 'programming', 'coding', 'web', 'mobile', 'devops', 'cloud', 'database', 'network'],
        'Healthcare' => ['nurse', 'medical', 'health', 'doctor', 'clinical', 'patient', 'hospital', 'pharmacy', 'healthcare'],
        'Finance' => ['accountant', 'financial', 'finance', 'banking', 'investment', 'auditor', 'tax', 'treasury'],
        'Marketing' => ['marketing', 'social media', 'content', 'seo', 'brand', 'advertising', 'digital marketing'],
        'Sales' => ['sales', 'account manager', 'business development', 'account executive'],
        'Education' => ['teacher', 'lecturer', 'professor', 'instructor', 'education', 'training', 'tutor'],
        'Engineering' => ['civil engineer', 'mechanical engineer', 'electrical engineer', 'structural', 'construction'],
        'Customer Service' => ['customer service', 'customer support', 'call center', 'support representative'],
        'Human Resources' => ['hr ', 'human resource', 'recruitment', 'recruiter', 'talent acquisition'],
        'Operations' => ['operations', 'supply chain', 'logistics', 'warehouse', 'inventory'],
        'Design' => ['designer', 'ui/ux', 'graphic', 'creative', 'design'],
        'Management' => ['manager', 'director', 'supervisor', 'lead', 'head of']
    ];
    
    foreach ($industries as $industry => $keywords) {
        foreach ($keywords as $keyword) {
            if (stripos($combined, $keyword) !== false) {
                return $industry;
            }
        }
    }
    
    return 'General';
}

echo "<h1>🌍 Auto-Import Jobs from Multiple Locations</h1>";
echo "<p>Fetching jobs from Uganda, East Africa, Europe, and Africa...</p>";
echo "<hr>";

try {
    $apiService = new ExternalAPIService();
    
    // Ensure we have an employer for external jobs
    $empCheck = $conn->query("SELECT id FROM employers WHERE company_name = 'External Job Board' LIMIT 1");
    if ($empCheck->num_rows == 0) {
        echo "<p>Creating employer for external jobs...</p>";
        $stmt = $conn->prepare("INSERT INTO employers (company_name, email, password, description, location, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $company = "External Job Board";
        $email = "external@jobboard.com";
        $password = password_hash("external123", PASSWORD_DEFAULT);
        $desc = "International jobs sourced from external APIs";
        $location = "Global";
        
        $stmt->bind_param("sssss", $company, $email, $password, $desc, $location);
        $stmt->execute();
        $employerId = $conn->insert_id;
        $stmt->close();
        echo "<p>✅ Created employer ID: $employerId</p>";
    } else {
        $employerId = $empCheck->fetch_assoc()['id'];
    }
    
    echo "<hr>";
    
    // Define comprehensive job categories
    $jobCategories = [
        // Tech & IT
        'Software Developer',
        'Web Developer',
        'Mobile Developer',
        'Data Analyst',
        'Data Scientist',
        'IT Support',
        'Network Engineer',
        'Database Administrator',
        'DevOps Engineer',
        'UI/UX Designer',
        
        // Business & Management
        'Project Manager',
        'Product Manager',
        'Business Analyst',
        'Operations Manager',
        'Supply Chain Manager',
        'Human Resources Manager',
        'Administrative Assistant',
        
        // Marketing & Sales
        'Marketing Manager',
        'Digital Marketing Specialist',
        'Social Media Manager',
        'Sales Representative',
        'Account Manager',
        'Content Writer',
        
        // Finance & Accounting
        'Accountant',
        'Financial Analyst',
        'Auditor',
        'Tax Consultant',
        
        // Healthcare & Education
        'Nurse',
        'Medical Officer',
        'Teacher',
        'Lecturer',
        'Research Assistant',
        
        // Engineering
        'Civil Engineer',
        'Electrical Engineer',
        'Mechanical Engineer',
        
        // Customer Service
        'Customer Service Representative',
        'Call Center Agent',
        
        // Internships & Entry Level
        'Internship',
        'Graduate Trainee',
        'Entry Level'
    ];
    
    // Define locations with focus on Uganda, East Africa, Europe, and Africa
    $locations = [
        // Uganda
        'Kampala, Uganda',
        'Uganda',
        
        // East Africa
        'Nairobi, Kenya',
        'Kenya',
        'Dar es Salaam, Tanzania',
        'Tanzania',
        'Kigali, Rwanda',
        'Rwanda',
        
        // Other African Countries
        'Lagos, Nigeria',
        'Nigeria',
        'Accra, Ghana',
        'Ghana',
        'Cape Town, South Africa',
        'South Africa',
        'Cairo, Egypt',
        'Egypt',
        
        // Europe (for remote/international opportunities)
        'London, United Kingdom',
        'Berlin, Germany',
        'Amsterdam, Netherlands',
        'Remote'
    ];
    
    $totalImported = 0;
    $totalSkipped = 0;
    $totalAttempts = 0;
    
    // Randomly select categories and locations to avoid hitting API limits
    $selectedCategories = array_rand(array_flip($jobCategories), min(15, count($jobCategories)));
    $selectedLocations = array_rand(array_flip($locations), min(8, count($locations)));
    
    echo "<h3>📊 Search Strategy</h3>";
    echo "<p>Categories to search: <strong>" . count($selectedCategories) . "</strong></p>";
    echo "<p>Locations to search: <strong>" . count($selectedLocations) . "</strong></p>";
    echo "<hr>";
    
    foreach ($selectedCategories as $category) {
        foreach ($selectedLocations as $location) {
            $totalAttempts++;
            echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #667eea; border-radius: 5px;'>";
            echo "<h4>🔍 Query: '$category' in '$location'</h4>";
            
            try {
                // Fetch from JSearch
                $jSearchData = $apiService->fetchJSearchJobs($category, $location);
                
                if ($jSearchData && !empty($jSearchData['data'])) {
                    $jobs = $apiService->parseJSearchJobs($jSearchData);
                    echo "<p>✅ Found " . count($jobs) . " jobs from JSearch API</p>";
                    
                    // Import jobs into database
                    $imported = 0;
                    $skipped = 0;
                    
                    foreach ($jobs as $job) {
                        // Auto-detect job type from title and description
                        $jobTitle = strtolower($job['title']);
                        $jobDesc = strtolower($job['description']);
                        
                        if (stripos($jobTitle, 'intern') !== false || stripos($jobDesc, 'internship') !== false) {
                            $jobType = 'Internship';
                        } elseif (stripos($jobTitle, 'part-time') !== false || stripos($jobDesc, 'part-time') !== false || stripos($jobDesc, 'part time') !== false) {
                            $jobType = 'Part-time';
                        } elseif (stripos($jobTitle, 'contract') !== false || stripos($jobDesc, 'contract') !== false) {
                            $jobType = 'Contract';
                        } else {
                            $jobType = 'Full-time';
                        }
                        
                        // Extract industry
                        $industry = extractIndustry($job['title'], $job['description']);
                        
                        // Enhanced duplicate check: title + location + company
                        $checkStmt = $conn->prepare("
                            SELECT id FROM jobs 
                            WHERE title = ? AND location = ? 
                            AND employer_id = (SELECT id FROM employers WHERE company_name = ?)
                            LIMIT 1
                        ");
                        $checkStmt->bind_param("sss", $job['title'], $job['location'], $job['company']);
                        $checkStmt->execute();
                        $checkResult = $checkStmt->get_result();
                        
                        if ($checkResult->num_rows > 0) {
                            $skipped++;
                            $checkStmt->close();
                            continue;
                        }
                        $checkStmt->close();
                        
                        // Check if company employer exists, create if not
                        $empStmt = $conn->prepare("SELECT id FROM employers WHERE company_name = ? LIMIT 1");
                        $empStmt->bind_param("s", $job['company']);
                        $empStmt->execute();
                        $empResult = $empStmt->get_result();
                        
                        if ($empResult->num_rows > 0) {
                            $jobEmployerId = $empResult->fetch_assoc()['id'];
                        } else {
                            // Create employer for this company
                            $createEmpStmt = $conn->prepare("
                                INSERT INTO employers (company_name, email, password, description, location, created_at) 
                                VALUES (?, ?, ?, ?, ?, NOW())
                            ");
                            $companyEmail = strtolower(str_replace(' ', '', $job['company'])) . '@external.com';
                            $defaultPass = password_hash('external123', PASSWORD_DEFAULT);
                            $companyDesc = "Employer sourced from external job board";
                            
                            $createEmpStmt->bind_param("sssss", $job['company'], $companyEmail, $defaultPass, $companyDesc, $job['location']);
                            $createEmpStmt->execute();
                            $jobEmployerId = $conn->insert_id;
                            $createEmpStmt->close();
                        }
                        $empStmt->close();
                        
                        // Insert job with all fields properly
                        $insertStmt = $conn->prepare("
                            INSERT INTO jobs (
                                title, description, location, type, employer_id, 
                                status, requirements, responsibilities, external_link, 
                                application_method, industry, createdAt, updatedAt
                            ) 
                            VALUES (?, ?, ?, ?, ?, 'Open', ?, ?, ?, 'External', ?, NOW(), NOW())
                        ");
                        
                        $insertStmt->bind_param("ssssissss", 
                            $job['title'], 
                            $job['description'], 
                            $job['location'], 
                            $jobType, 
                            $jobEmployerId,
                            $job['requirements'],
                            $job['responsibilities'],
                            $job['url'],
                            $industry
                        );
                        
                        if ($insertStmt->execute()) {
                            $imported++;
                        }
                        $insertStmt->close();
                    }
                    
                    $totalImported += $imported;
                    $totalSkipped += $skipped;
                    
                    echo "<p style='color: green;'>✅ Imported: <strong>$imported</strong> | Skipped (duplicates): <strong>$skipped</strong></p>";
                } else {
                    echo "<p style='color: orange;'>⚠️ No jobs found from JSearch API</p>";
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            echo "</div>";
            
            // Rate limiting: pause every 5 queries to avoid hitting API limits
            if ($totalAttempts % 5 == 0) {
                echo "<p style='color: blue; text-align: center;'>⏸️ Pausing for 2 seconds to respect API rate limits...</p>";
                sleep(2);
            }
        }
    }
    
    echo "<hr>";
    echo "<div style='background: #667eea; color: white; padding: 20px; border-radius: 10px; text-align: center;'>";
    echo "<h2>📊 Import Summary</h2>";
    echo "<p style='font-size: 24px;'><strong>Total Jobs Imported: $totalImported</strong></p>";
    echo "<p style='font-size: 18px;'>Total Skipped (duplicates): $totalSkipped</p>";
    echo "<p style='font-size: 18px;'>Total API Queries: $totalAttempts</p>";
    echo "</div>";
    
    echo "<br><br>";
    echo "<div style='text-align: center;'>";
    echo "<a href='/career_hub/pages/jobs.php' style='display: inline-block; background: #48bb78; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 18px;'>📋 View All Jobs</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fc8181; color: white; padding: 20px; border-radius: 10px;'>";
    echo "<h2>❌ Fatal Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Import Complete</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        h1, h2, h3, h4 {
            color: #2d3748;
        }
        hr {
            border: none;
            border-top: 2px solid #e2e8f0;
            margin: 20px 0;
        }
    </style>
</head>
<body>
</body>
</html>
            
            if ($jSearchData && isset($jSearchData['data'])) {
                $jobs = $apiService->parseJSearchJobs($jSearchData);
                echo "<p>Found: " . count($jobs) . " jobs</p>";
                
                // Import each job
                $imported = 0;
                $skipped = 0;
                
                foreach ($jobs as $job) {
                    // Check if job already exists (by title and company)
                    $checkStmt = $conn->prepare("SELECT id FROM jobs WHERE title = ? AND location = ? LIMIT 1");
                    $checkStmt->bind_param("ss", $job['title'], $job['location']);
                    $checkStmt->execute();
                    $exists = $checkStmt->get_result()->num_rows > 0;
                    $checkStmt->close();
                    
                    if ($exists) {
                        $skipped++;
                        continue;
                    }
                    
                    // Insert job
                    $stmt = $conn->prepare("INSERT INTO jobs (
                        title, description, location, type, employer_id, 
                        status, createdAt, updatedAt, requirements, external_link
                    ) VALUES (?, ?, ?, 'Full-time', ?, 'Open', NOW(), NOW(), ?, ?)");
                    
                    $requirements = "Check job listing for detailed requirements";
                    
                    $stmt->bind_param("sssiss", 
                        $job['title'],
                        $job['description'],
                        $job['location'],
                        $employerId,
                        $requirements,
                        $job['url']
                    );
                    
                    if ($stmt->execute()) {
                        $imported++;
                    }
                    $stmt->close();
                }
                
                echo "<p>✅ Imported: <strong>$imported</strong> | Skipped (duplicates): <strong>$skipped</strong></p>";
                $totalImported += $imported;
                $totalSkipped += $skipped;
                
            } else {
                echo "<p>⚠️ No jobs found for this query</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo "<hr>";
    }
    
    echo "<h2>Summary</h2>";
    echo "<p><strong>Total Imported:</strong> $totalImported jobs</p>";
    echo "<p><strong>Total Skipped:</strong> $totalSkipped jobs (duplicates)</p>";
    
    // Show total jobs in database
    $result = $conn->query("SELECT COUNT(*) as count FROM jobs");
    $totalJobs = $result->fetch_assoc()['count'];
    echo "<p><strong>Total Jobs in Database:</strong> $totalJobs</p>";
    
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<p><a href='pages/jobs.php' style='padding: 10px 20px; background: #0a66c2; color: white; text-decoration: none; border-radius: 5px;'>View Jobs Page</a></p>";
    echo "<p><a href='test_jobs.php'>Check Database</a></p>";
    echo "<p><a href='import_external_jobs.php'>Import More Jobs</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
