<?php
/**
 * External API Service
 * Handles integration with external job APIs
 */
class ExternalAPIService {
    private $adzunaAppId;
    private $adzunaAppKey;
    private $rapidApiKey;
    private $cacheDir;
    private $cacheTtlSeconds = 3600; // default 1 hour

    public function __construct() {
        // Try environment variables first
        $this->adzunaAppId = $this->getEnvVar('ADZUNA_APP_ID');
        $this->adzunaAppKey = $this->getEnvVar('ADZUNA_APP_KEY');
        $this->rapidApiKey = $this->getEnvVar('RAPIDAPI_KEY');
        
        // If env vars are empty, try loading from config file
        if (empty($this->adzunaAppId) || empty($this->rapidApiKey)) {
            $configPath = __DIR__ . '/../config/config.php';
            if (file_exists($configPath)) {
                $config = require $configPath;
                
                if (empty($this->adzunaAppId)) {
                    $this->adzunaAppId = $config['ADZUNA_APP_ID'] ?? '';
                }
                if (empty($this->adzunaAppKey)) {
                    $this->adzunaAppKey = $config['ADZUNA_APP_KEY'] ?? '';
                }
                if (empty($this->rapidApiKey)) {
                    $this->rapidApiKey = $config['RAPIDAPI_KEY'] ?? '';
                }
            }
        }
        
        $this->cacheDir = __DIR__ . '/../cache/api/';

        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

        // Optional TTL override via env
        $ttlEnv = $this->getEnvVar('CACHE_TTL');
        if ($ttlEnv !== '' && ctype_digit((string)$ttlEnv)) {
            $this->setCacheTtl((int)$ttlEnv);
        }

        // Ensure DB connection is available for imports
        if (!isset($GLOBALS['conn'])) {
            require_once __DIR__ . '/../includes/db.php';
        }
    }

    /**
     * Override cache TTL (in seconds)
     */
    public function setCacheTtl($seconds) {
        $seconds = (int)$seconds;
        if ($seconds > 0 && $seconds <= 86400) { // cap at 24h for safety
            $this->cacheTtlSeconds = $seconds;
        }
        return $this;
    }
    
    /**
     * Fetch jobs from Adzuna API
     * @param string $query Search query
     * @param string $location Location
     * @param int $page Page number
     * @return array|null
     */
    public function fetchAdzunaJobs($query = '', $location = 'us', $page = 1) {
        if (empty($this->adzunaAppId) || empty($this->adzunaAppKey)) {
            throw new Exception('Adzuna API credentials not configured. Set ADZUNA_APP_ID and ADZUNA_APP_KEY env vars.');
        }
        $cacheKey = md5("adzuna_{$query}_{$location}_{$page}");
        $cacheFile = $this->cacheDir . $cacheKey . '.json';
        
        // Check cache
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTtlSeconds) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        $url = "https://api.adzuna.com/v1/api/jobs/{$location}/search/{$page}";
        $params = [
            'app_id' => $this->adzunaAppId,
            'app_key' => $this->adzunaAppKey,
            'results_per_page' => 20,
            'what' => $query
        ];
        
        $fullUrl = $url . '?' . http_build_query($params);
        
        try {
            $response = $this->makeRequest($fullUrl);
            
            if ($response) {
                // Cache the response
                file_put_contents($cacheFile, json_encode($response));
                return $response;
            }
        } catch (Exception $e) {
            error_log("Adzuna API Error: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Parse Adzuna jobs to our format
     * @param array $adzunaData
     * @return array
     */
    public function parseAdzunaJobs($adzunaData) {
        if (!isset($adzunaData['results'])) {
            return [];
        }
        
        $jobs = [];
        foreach ($adzunaData['results'] as $job) {
            $jobs[] = [
                'title' => $job['title'] ?? '',
                'company' => $job['company']['display_name'] ?? 'Unknown',
                'description' => $job['description'] ?? '',
                'location' => $job['location']['display_name'] ?? '',
                'salary_min' => $job['salary_min'] ?? null,
                'salary_max' => $job['salary_max'] ?? null,
                'url' => $job['redirect_url'] ?? '',
                'source' => 'adzuna',
                'created_at' => date('Y-m-d H:i:s', strtotime($job['created'] ?? 'now'))
            ];
        }
        
        return $jobs;
    }
    
    /**
     * Fetch jobs from JSearch API (RapidAPI alternative)
     * Note: This requires RapidAPI key
     * @param string $query
     * @return array|null
     */
    public function fetchJSearchJobs($query = 'software developer', $location = 'United States') {
        if (empty($this->rapidApiKey)) {
            throw new Exception('RapidAPI key not configured. Set RAPIDAPI_KEY env var.');
        }
        $cacheKey = md5("jsearch_{$query}_{$location}");
        $cacheFile = $this->cacheDir . $cacheKey . '.json';
        
        // Check cache
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTtlSeconds) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        $url = "https://jsearch.p.rapidapi.com/search";
        $params = [
            'query' => $query,
            'page' => 1,
            'num_pages' => 1
        ];
        
        $fullUrl = $url . '?' . http_build_query($params);
        
        $headers = [
            'X-RapidAPI-Key: ' . $this->rapidApiKey,
            'X-RapidAPI-Host: jsearch.p.rapidapi.com'
        ];
        
        try {
            $response = $this->makeRequest($fullUrl, $headers);
            
            if ($response) {
                file_put_contents($cacheFile, json_encode($response));
                return $response;
            }
        } catch (Exception $e) {
            error_log("JSearch API Error: " . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Parse JSearch API response into normalized job format
     */
    public function parseJSearchJobs($jData) {
        $jobs = [];
        
        if (!isset($jData['data']) || empty($jData['data'])) {
            return $jobs;
        }
        
        foreach ($jData['data'] as $job) {
            // Get the full description
            $fullDescription = $job['job_description'] ?? 'No description available';
            
            // Summarize description to 500-1000 characters
            $summarizedDescription = $this->summarizeDescription($fullDescription);
            
            // Extract requirements and responsibilities
            $extracted = $this->extractJobDetails($fullDescription);
            
            $jobs[] = [
                'title' => $this->cleanText($job['job_title'] ?? 'No Title', 255),
                'company' => $this->cleanText($job['employer_name'] ?? 'Unknown Company', 255),
                'description' => $summarizedDescription,
                'location' => $this->formatLocation($job),
                'url' => $job['job_apply_link'] ?? $job['job_google_link'] ?? '#',
                'source' => 'jsearch',
                'created_at' => isset($job['job_posted_at_datetime_utc']) 
                    ? date('Y-m-d H:i:s', strtotime($job['job_posted_at_datetime_utc']))
                    : date('Y-m-d H:i:s'),
                'requirements' => $extracted['requirements'],
                'responsibilities' => $extracted['responsibilities']
            ];
        }
        
        return $jobs;
    }
    
    /**
     * Summarize long description to 500-1000 characters
     */
    private function summarizeDescription($description, $maxLength = 800) {
        // Clean up the description
        $description = strip_tags($description);
        $description = preg_replace('/\s+/', ' ', $description);
        $description = trim($description);
        
        // If already short enough, return as is
        if (strlen($description) <= $maxLength) {
            return $description;
        }
        
        // Try to find a good break point (end of sentence)
        $breakPoint = $maxLength;
        $sentences = preg_split('/([.!?]\s+)/', substr($description, 0, $maxLength * 1.5), -1, PREG_SPLIT_DELIM_CAPTURE);
        
        $summary = '';
        foreach ($sentences as $sentence) {
            if (strlen($summary . $sentence) <= $maxLength) {
                $summary .= $sentence;
            } else {
                break;
            }
        }
        
        // If we got a good summary, use it
        if (strlen($summary) >= $maxLength * 0.7) {
            return trim($summary);
        }
        
        // Otherwise, cut at word boundary
        $summary = substr($description, 0, $maxLength);
        $lastSpace = strrpos($summary, ' ');
        if ($lastSpace !== false) {
            $summary = substr($summary, 0, $lastSpace);
        }
        
        return trim($summary) . '...';
    }
    
    /**
     * Extract requirements and responsibilities from job description
     */
    private function extractJobDetails($description) {
        $requirements = '';
        $responsibilities = '';
        
        // Clean the description
        $description = strip_tags($description);
        
        // Try to extract requirements section
        $reqPatterns = [
            '/(?:Requirements?|Qualifications?|What you need|What we are looking for)[:\s]+(.*?)(?=Responsibilities?|Duties|What you will do|Benefits?|About|$)/is',
            '/(?:Required?|Must have)[:\s]+(.*?)(?=Preferred?|Responsibilities?|$)/is'
        ];
        
        foreach ($reqPatterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                $requirements = $this->cleanExtractedText($matches[1], 500);
                break;
            }
        }
        
        // Try to extract responsibilities section
        $respPatterns = [
            '/(?:Responsibilities?|Duties|What you will do|Your role)[:\s]+(.*?)(?=Requirements?|Qualifications?|Benefits?|About|$)/is',
            '/(?:You will|The role includes)[:\s]+(.*?)(?=Requirements?|Qualifications?|$)/is'
        ];
        
        foreach ($respPatterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                $responsibilities = $this->cleanExtractedText($matches[1], 500);
                break;
            }
        }
        
        // If we didn't find specific sections, create generic ones from the description
        if (empty($requirements)) {
            $requirements = 'See job description for details. Apply through the external link provided.';
        }
        
        if (empty($responsibilities)) {
            $responsibilities = 'Please refer to the full job description available through the external application link.';
        }
        
        return [
            'requirements' => $requirements,
            'responsibilities' => $responsibilities
        ];
    }
    
    /**
     * Clean extracted text to specified length
     */
    private function cleanExtractedText($text, $maxLength = 500) {
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Truncate if too long
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength);
            $lastSpace = strrpos($text, ' ');
            if ($lastSpace !== false) {
                $text = substr($text, 0, $lastSpace);
            }
            $text .= '...';
        }
        
        return $text;
    }
    
    /**
     * Clean text to fit database field limits
     */
    private function cleanText($text, $maxLength = 255) {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength - 3) . '...';
        }
        
        return $text;
    }
    
    /**
     * Format location from JSearch job data
     * @param array $job
     * @return string
     */
    private function formatLocation($job) {
        // Check for remote work
        if (isset($job['job_is_remote']) && $job['job_is_remote']) {
            return 'Remote';
        }
        
        // Extract location components
        $city = isset($job['job_city']) ? trim($job['job_city']) : '';
        $state = isset($job['job_state']) ? trim($job['job_state']) : '';
        $country = isset($job['job_country']) ? trim($job['job_country']) : '';
        
        // Build location string
        $locationParts = [];
        
        if (!empty($city)) {
            $locationParts[] = $city;
        }
        
        // Add state only if it's different from city and not empty
        if (!empty($state) && $state !== $city) {
            $locationParts[] = $state;
        }
        
        // Add country if available
        if (!empty($country)) {
            $locationParts[] = $country;
        }
        
        // Join with commas
        $location = implode(', ', $locationParts);
        
        // If we couldn't build a location, use a default
        if (empty($location)) {
            $location = 'Location Not Specified';
        }
        
        // Limit to 255 characters to fit database schema
        if (strlen($location) > 255) {
            $location = substr($location, 0, 252) . '...';
        }
        
        return $location;
    }
    
    /**
     * Make HTTP request
     * @param string $url
     * @param array $headers
     * @return array|null
     */
    private function makeRequest($url, $headers = []) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15, // Reduced from 30 to 15 seconds for faster import
            CURLOPT_CONNECTTIMEOUT => 10, // Add connection timeout
            CURLOPT_SSL_VERIFYPEER => false, // For local development - enable in production
            CURLOPT_SSL_VERIFYHOST => false, // For local development - enable in production
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_USERAGENT => 'CareerHub/1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Log errors for debugging
        if ($curlError) {
            error_log("cURL Error: $curlError for URL: $url");
            throw new Exception("API Request failed: $curlError");
        }
        
        if ($httpCode !== 200) {
            error_log("HTTP $httpCode from URL: $url");
            error_log("Response: " . substr($response, 0, 500));
            throw new Exception("API returned HTTP $httpCode");
        }
        
        if ($response) {
            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error: " . json_last_error_msg());
                throw new Exception("Invalid JSON response from API");
            }
            return $decoded;
        }
        
        return null;
    }
    
    /**
     * Import external jobs into database
     * @param array $jobs
     * @return int Number of jobs imported
     */
    public function importJobs($jobs) {
        $jobModel = new Job();
        $imported = 0;
        $skipped = 0;

        // Cache table columns for filtering/mapping
        $columns = $this->describeJobsTable();
        $hasCompany = isset($columns['company']);
        $hasCreatedAt = isset($columns['created_at']) || isset($columns['createdAt']);
        $hasExternalLink = isset($columns['external_link']);
        $hasUrl = isset($columns['url']);

        foreach ($jobs as $jobData) {
            try {
                // Minimal validation
                $title = trim($jobData['title'] ?? '');
                $company = trim($jobData['company'] ?? '');
                $description = trim($jobData['description'] ?? '');
                if ($title === '' || $description === '') {
                    $skipped++; // insufficient data
                    continue;
                }

                // Deduplication (by title + company (if exists) + description prefix)
                if ($this->jobExists($title, $company, $description, $hasCompany)) {
                    $skipped++;
                    continue;
                }

                // Prepare insert payload based on actual table columns
                $data = [];
                $data['title'] = $title;
                if ($hasCompany && $company !== '') { $data['company'] = $company; }
                $data['description'] = $description;

                // created_at vs createdAt
                $createdVal = $jobData['created_at'] ?? $jobData['createdAt'] ?? date('Y-m-d H:i:s');
                if (isset($columns['created_at'])) {
                    $data['created_at'] = $createdVal;
                } elseif (isset($columns['createdAt'])) {
                    $data['createdAt'] = $createdVal;
                }

                // Optional mapping for external links
                $link = $jobData['url'] ?? $jobData['external_link'] ?? null;
                if ($link) {
                    if ($hasUrl) { $data['url'] = $link; }
                    elseif ($hasExternalLink) { $data['external_link'] = $link; }
                }

                // Best-effort optional fields if present in schema
                if (isset($columns['location']) && !empty($jobData['location'])) {
                    $data['location'] = $jobData['location'];
                }
                if (isset($columns['application_method'])) {
                    $data['application_method'] = $data['application_method'] ?? 'External';
                }
                if (isset($columns['status'])) { $data['status'] = $data['status'] ?? 'Open'; }
                if (isset($columns['type']) && !isset($data['type'])) { $data['type'] = 'Full-time'; }

                $jobModel->create($data);
                $imported++;
            } catch (Exception $e) {
                // On error, skip and continue
                error_log("Failed to import job: " . $e->getMessage());
                $skipped++;
            }
        }

        // Return both counts for transparency
        return [
            'imported' => $imported,
            'skipped' => $skipped
        ];
    }

    /**
     * Check if a similar job already exists to avoid duplicates
     */
    private function jobExists($title, $company, $description, $hasCompany) {
        $conn = $GLOBALS['conn'];
        $descPrefix = mb_substr($description, 0, 120);
        if ($hasCompany && $company !== '') {
            $sql = "SELECT id FROM jobs WHERE title = ? AND company = ? AND LEFT(description, 120) = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sss', $title, $company, $descPrefix);
        } else {
            $sql = "SELECT id FROM jobs WHERE title = ? AND LEFT(description, 120) = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $title, $descPrefix);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = (bool)$res->fetch_assoc();
        $stmt->close();
        return $exists;
    }

    /**
     * Return jobs table columns (name => true)
     */
    private function describeJobsTable() {
        static $cache = null;
        if ($cache !== null) { return $cache; }
        $conn = $GLOBALS['conn'];
        $result = $conn->query('DESCRIBE jobs');
        $cols = [];
        while ($row = $result->fetch_assoc()) {
            $cols[$row['Field']] = $row; // keep full row for potential future use
        }
        $result->free();
        $cache = $cols;
        return $cache;
    }

    /**
     * Helper to get environment variable from multiple sources
     * @param string $key
     * @return string
     */
    private function getEnvVar($key) {
        // Try getenv first
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return $val;
        }
        // Fallback to $_ENV
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        // Fallback to $_SERVER
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        return '';
    }
}
