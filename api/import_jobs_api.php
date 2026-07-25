<?php
/* Teaching: import_jobs_api.php
    Purpose: Long-running Server-Sent Events (SSE) endpoint that imports jobs
    from external APIs and streams progress back to the browser in real-time.
    Concepts demonstrated:
    - Server-Sent Events (SSE): streaming events with `text/event-stream`
    - Long-running PHP scripts (set_time_limit, careful output flushing)
    - External API integration with rate-limiting and deduplication
    - Use of helper services (ExternalAPIService) to keep controller code small
    - Security: keep API keys out of source control and load via environment/config
    Note: SSE endpoints must not emit extra whitespace or HTML; they must follow "data: ...\n\n" format.
*/
// Server-Sent Events for real-time import progress
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable nginx buffering

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display to avoid breaking SSE format
ini_set('log_errors', 1);

// Load environment variables from .env file
require_once __DIR__ . '/../includes/load_env.php';

require_once __DIR__ . '/../classes/autoload.php';
require_once __DIR__ . '/../includes/db.php';

set_time_limit(300); // 5 minutes

function sendSSE($type, $data) {
    echo "data: " . json_encode(array_merge(['type' => $type], $data)) . "\n\n";
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}

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

try {
    sendSSE('log', ['message' => '🔧 Starting import process...', 'level' => 'info']);
    
    // Debug: Check if config loader is working
    sendSSE('log', ['message' => '🔍 Checking config availability...', 'level' => 'info']);
    
    // Try to load config
    $configPath = __DIR__ . '/../config/config.php';
    $configExists = file_exists($configPath);
    sendSSE('log', ['message' => "Config file exists: " . ($configExists ? 'YES' : 'NO') . " at $configPath", 'level' => 'info']);
    
    // Check environment variables
    $rapidApiKey = getenv('RAPIDAPI_KEY') ?: $_ENV['RAPIDAPI_KEY'] ?? $_SERVER['RAPIDAPI_KEY'] ?? 'NOT SET';
    $adzunaAppId = getenv('ADZUNA_APP_ID') ?: $_ENV['ADZUNA_APP_ID'] ?? $_SERVER['ADZUNA_APP_ID'] ?? 'NOT SET';
    $adzunaAppKey = getenv('ADZUNA_APP_KEY') ?: $_ENV['ADZUNA_APP_KEY'] ?? $_SERVER['ADZUNA_APP_KEY'] ?? 'NOT SET';
    
    sendSSE('log', ['message' => "RAPIDAPI_KEY: " . (strlen($rapidApiKey) > 5 ? substr($rapidApiKey, 0, 8) . '...' : $rapidApiKey), 'level' => 'info']);
    sendSSE('log', ['message' => "ADZUNA_APP_ID: " . (strlen($adzunaAppId) > 5 ? substr($adzunaAppId, 0, 8) . '...' : $adzunaAppId), 'level' => 'info']);
    sendSSE('log', ['message' => "ADZUNA_APP_KEY: " . (strlen($adzunaAppKey) > 5 ? substr($adzunaAppKey, 0, 8) . '...' : $adzunaAppKey), 'level' => 'info']);
    
    sendSSE('log', ['message' => '🔄 Instantiating ExternalAPIService...', 'level' => 'info']);
    
    try {
        $apiService = new ExternalAPIService();
        sendSSE('log', ['message' => '✅ ExternalAPIService initialized', 'level' => 'success']);
    } catch (Exception $serviceException) {
        sendSSE('log', ['message' => '❌ Failed to create ExternalAPIService: ' . $serviceException->getMessage(), 'level' => 'error']);
        sendSSE('log', ['message' => 'Stack trace: ' . $serviceException->getTraceAsString(), 'level' => 'error']);
        throw $serviceException;
    }
    
    sendSSE('log', ['message' => '🚀 Initializing import service...', 'level' => 'info']);
    
    // Ensure we have an employer for external jobs
    $empCheck = $conn->query("SELECT id FROM employers WHERE company_name = 'External Job Board' LIMIT 1");
    if ($empCheck->num_rows == 0) {
        sendSSE('log', ['message' => '📝 Creating employer for external jobs...', 'level' => 'info']);
        $stmt = $conn->prepare("INSERT INTO employers (company_name, email, password, description, location, createdAt, updatedAt) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $company = "External Job Board";
        $email = "external@jobboard.com";
        $password = password_hash("external123", PASSWORD_DEFAULT);
        $desc = "International jobs sourced from external APIs";
        $location = "Global";
        
        $stmt->bind_param("sssss", $company, $email, $password, $desc, $location);
        $stmt->execute();
        $employerId = $conn->insert_id;
        $stmt->close();
        sendSSE('log', ['message' => "✅ Created employer ID: $employerId", 'level' => 'success']);
    } else {
        $employerId = $empCheck->fetch_assoc()['id'];
    }
    
    // Define comprehensive job categories
    $jobCategories = [
        'Software Developer', 'Web Developer', 'Mobile Developer', 'Data Analyst', 'Data Scientist',
        'IT Support', 'Network Engineer', 'Database Administrator', 'DevOps Engineer', 'UI/UX Designer',
        'Project Manager', 'Product Manager', 'Business Analyst', 'Operations Manager', 'Supply Chain Manager',
        'Human Resources Manager', 'Administrative Assistant', 'Marketing Manager', 'Digital Marketing Specialist',
        'Social Media Manager', 'Sales Representative', 'Account Manager', 'Content Writer',
        'Accountant', 'Financial Analyst', 'Auditor', 'Tax Consultant',
        'Nurse', 'Medical Officer', 'Teacher', 'Lecturer', 'Research Assistant',
        'Civil Engineer', 'Electrical Engineer', 'Mechanical Engineer',
        'Customer Service Representative', 'Call Center Agent',
        'Internship', 'Graduate Trainee', 'Entry Level'
    ];
    
    // Define locations
    $locations = [
        'Kampala, Uganda', 'Uganda', 'Nairobi, Kenya', 'Kenya',
        'Dar es Salaam, Tanzania', 'Tanzania', 'Kigali, Rwanda', 'Rwanda',
        'Lagos, Nigeria', 'Nigeria', 'Accra, Ghana', 'Ghana',
        'Cape Town, South Africa', 'South Africa', 'Cairo, Egypt', 'Egypt',
        'London, United Kingdom', 'Berlin, Germany', 'Amsterdam, Netherlands', 'Remote'
    ];
    
    $totalImported = 0;
    $totalSkipped = 0;
    
    // Randomly select FEWER categories and locations for faster import (5 categories x 3 locations = 15 queries max)
    $selectedCategories = array_rand(array_flip($jobCategories), min(5, count($jobCategories)));
    $selectedLocations = array_rand(array_flip($locations), min(3, count($locations)));
    
    $totalQueries = count($selectedCategories) * count($selectedLocations) * 2; // x2 for both APIs
    $currentQuery = 0;
    
    sendSSE('log', ['message' => "📊 Will search {$totalQueries} combinations from 2 APIs (JSearch + Adzuna)", 'level' => 'info']);
    sendSSE('progress', ['progress' => 0]);
    
    foreach ($selectedCategories as $category) {
        foreach ($selectedLocations as $location) {
            
            // ========== FETCH FROM JSEARCH ==========
            $currentQuery++;
            $progress = round(($currentQuery / $totalQueries) * 100);
            
            sendSSE('progress', ['progress' => $progress]);
            sendSSE('log', ['message' => "🔍 JSearch: '$category' in '$location' ({$currentQuery}/{$totalQueries})", 'level' => 'info']);
            
            try {
                // Fetch from JSearch
                $jSearchData = $apiService->fetchJSearchJobs($category, $location);
                
                if ($jSearchData && !empty($jSearchData['data'])) {
                    $jobs = $apiService->parseJSearchJobs($jSearchData);
                    sendSSE('log', ['message' => "✅ Found " . count($jobs) . " jobs", 'level' => 'success']);
                    
                    // Import jobs
                    $imported = 0;
                    $skipped = 0;
                    
                    foreach ($jobs as $job) {
                        // Auto-detect job type
                        $jobTitle = strtolower($job['title']);
                        $jobDesc = strtolower($job['description']);
                        
                        if (stripos($jobTitle, 'intern') !== false || stripos($jobDesc, 'internship') !== false) {
                            $jobType = 'Internship';
                        } elseif (stripos($jobTitle, 'part-time') !== false || stripos($jobDesc, 'part-time') !== false) {
                            $jobType = 'Part-time';
                        } elseif (stripos($jobTitle, 'contract') !== false || stripos($jobDesc, 'contract') !== false) {
                            $jobType = 'Contract';
                        } else {
                            $jobType = 'Full-time';
                        }
                        
                        // Extract industry from job title or description
                        $industry = extractIndustry($job['title'], $job['description']);
                        
                        // Check for duplicates
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
                        
                        // Check/create employer
                        $empStmt = $conn->prepare("SELECT id FROM employers WHERE company_name = ? LIMIT 1");
                        $empStmt->bind_param("s", $job['company']);
                        $empStmt->execute();
                        $empResult = $empStmt->get_result();
                        
                        if ($empResult->num_rows > 0) {
                            $jobEmployerId = $empResult->fetch_assoc()['id'];
                        } else {
                            $createEmpStmt = $conn->prepare("
                                INSERT INTO employers (company_name, email, password, description, location, createdAt, updatedAt) 
                                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
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
                        
                        // Insert job with all fields
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
                    
                    sendSSE('log', ['message' => "✅ JSearch: $imported imported | $skipped skipped", 'level' => 'success']);
                } else {
                    sendSSE('log', ['message' => "⚠️ No jobs found in JSearch", 'level' => 'info']);
                }
                
            } catch (Exception $e) {
                sendSSE('log', ['message' => "❌ JSearch Error: " . $e->getMessage(), 'level' => 'error']);
            }
            
            // Rate limiting after JSearch
            sleep(1);
            
            // ========== FETCH FROM ADZUNA ==========
            $currentQuery++;
            $progress = round(($currentQuery / $totalQueries) * 100);
            
            sendSSE('progress', ['progress' => $progress]);
            sendSSE('log', ['message' => "🔍 Adzuna: '$category' in '$location' ({$currentQuery}/{$totalQueries})", 'level' => 'info']);
            
            try {
                // Map location for Adzuna (Adzuna uses country codes)
                $adzunaLocation = 'us'; // Default to US
                if (stripos($location, 'Uganda') !== false || stripos($location, 'Kenya') !== false || 
                    stripos($location, 'Tanzania') !== false || stripos($location, 'Rwanda') !== false || 
                    stripos($location, 'Nairobi') !== false || stripos($location, 'Kampala') !== false) {
                    $adzunaLocation = 'gb'; // Use GB as proxy for international jobs
                }
                
                // Fetch from Adzuna
                $adzunaData = $apiService->fetchAdzunaJobs($category, $adzunaLocation);
                
                if ($adzunaData && !empty($adzunaData['results'])) {
                    $jobs = $apiService->parseAdzunaJobs($adzunaData);
                    sendSSE('log', ['message' => "✅ Found " . count($jobs) . " jobs", 'level' => 'success']);
                    
                    // Import jobs
                    $imported = 0;
                    $skipped = 0;
                    
                    foreach ($jobs as $job) {
                        // Auto-detect job type
                        $jobTitle = strtolower($job['title']);
                        $jobDesc = strtolower($job['description']);
                        
                        if (stripos($jobTitle, 'intern') !== false || stripos($jobDesc, 'internship') !== false) {
                            $jobType = 'Internship';
                        } elseif (stripos($jobTitle, 'part-time') !== false || stripos($jobDesc, 'part-time') !== false) {
                            $jobType = 'Part-time';
                        } elseif (stripos($jobTitle, 'contract') !== false || stripos($jobDesc, 'contract') !== false) {
                            $jobType = 'Contract';
                        } else {
                            $jobType = 'Full-time';
                        }
                        
                        // Extract industry from job title or description
                        $industry = extractIndustry($job['title'], $job['description']);
                        
                        // Check for duplicates
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
                        
                        // Check/create employer
                        $empStmt = $conn->prepare("SELECT id FROM employers WHERE company_name = ? LIMIT 1");
                        $empStmt->bind_param("s", $job['company']);
                        $empStmt->execute();
                        $empResult = $empStmt->get_result();
                        
                        if ($empResult->num_rows > 0) {
                            $jobEmployerId = $empResult->fetch_assoc()['id'];
                        } else {
                            $createEmpStmt = $conn->prepare("
                                INSERT INTO employers (company_name, email, password, description, location, createdAt, updatedAt) 
                                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
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
                        
                        // Insert job with all fields
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
                    
                    sendSSE('log', ['message' => "✅ Adzuna: $imported imported | $skipped skipped", 'level' => 'success']);
                } else {
                    sendSSE('log', ['message' => "⚠️ No jobs found in Adzuna", 'level' => 'info']);
                }
                
            } catch (Exception $e) {
                sendSSE('log', ['message' => "❌ Adzuna Error: " . $e->getMessage(), 'level' => 'error']);
            }
            
            // Rate limiting: reduced to 1 second pause every 3 queries for faster import
            if ($currentQuery % 3 == 0 && $currentQuery < $totalQueries) {
                sendSSE('log', ['message' => "⏸️ Pausing 1 second (rate limiting)...", 'level' => 'info']);
                sleep(1);
            }
        }
    }
    
    sendSSE('complete', [
        'message' => "Import complete! Total: $totalImported imported, $totalSkipped skipped",
        'imported' => $totalImported,
        'skipped' => $totalSkipped
    ]);
    
} catch (Exception $e) {
    sendSSE('log', ['message' => "❌ Fatal error: " . $e->getMessage(), 'level' => 'error']);
}
?>
