<?php
/**
 * Server API Communication Class
 * Handles cross-server communication for distributed setup
 */

class ServerAPI {
    private $serverUrl;
    private $apiKey;
    private $timeout = 10; // seconds
    
    public function __construct($serverUrl, $apiKey) {
        $this->serverUrl = rtrim($serverUrl, '/');
        $this->apiKey = $apiKey;
    }
    
    /**
     * Make API request to another server
     * 
     * @param string $endpoint API endpoint (e.g., '/api/sync_data.php')
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param array|null $data Request data
     * @return array Response data
     */
    public function request($endpoint, $method = 'GET', $data = null) {
        $url = $this->serverUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'Connection error: ' . $error
            ];
        }
        
        if ($httpCode === 200) {
            return json_decode($response, true) ?? ['success' => false, 'error' => 'Invalid JSON response'];
        }
        
        return [
            'success' => false,
            'error' => 'Request failed',
            'code' => $httpCode
        ];
    }
    
    /**
     * Get jobs from Server 1
     */
    public function getJobs($limit = 50, $filters = []) {
        $query = http_build_query(array_merge(['action' => 'get_jobs', 'limit' => $limit], $filters));
        return $this->request("/api/sync_data.php?$query");
    }
    
    /**
     * Get applications from Server 2
     */
    public function getApplications($jobId = null) {
        $query = 'action=get_applications';
        if ($jobId) {
            $query .= '&job_id=' . intval($jobId);
        }
        return $this->request("/api/sync_data.php?$query");
    }
    
    /**
     * Sync user data between servers
     */
    public function syncUser($userData) {
        return $this->request("/api/sync_data.php?action=sync_user", 'POST', $userData);
    }
    
    /**
     * Get student profile from Server 1
     */
    public function getStudentProfile($studentId) {
        return $this->request("/api/sync_data.php?action=get_student&id=$studentId");
    }
    
    /**
     * Get employer profile from Server 2
     */
    public function getEmployerProfile($employerId) {
        return $this->request("/api/sync_data.php?action=get_employer&id=$employerId");
    }
    
    /**
     * Check if server is online
     */
    public function ping() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->serverUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
}

// Example usage:
/*
require_once 'config_server1.php'; // or config_server2.php
$server2 = new ServerAPI(SERVER2_URL, API_SECRET_KEY);

// Check if server is online
if ($server2->ping()) {
    // Get jobs
    $jobs = $server2->getJobs(20);
    if ($jobs['success']) {
        foreach ($jobs['data'] as $job) {
            echo $job['title'] . "\n";
        }
    }
}
*/
?>
