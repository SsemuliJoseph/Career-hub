<?php
/**
 * Data Synchronization API
 * Handles cross-server data requests
 */

/* Teaching: sync_data.php — cross-server synchronization endpoint
    Purpose: Provide a secure API for two servers to exchange data (jobs, applications, users).
    Key concepts:
    - API key authentication (X-API-Key header) to protect machine-to-machine endpoints
    - Multiple actions handled by a single entrypoint (action=get_jobs, sync_user, ping, etc.)
    - Use PDO or mysqli prepared statements to avoid SQL injection when building queries
    - Return consistent JSON shapes: { success: bool, data: ..., message/error }
    - Keep sync endpoints idempotent when possible (safe to retry)
*/

require_once __DIR__ . '/../includes/db.php';

// Load appropriate config based on server
if (file_exists(__DIR__ . '/../includes/config_server1.php')) {
    require_once __DIR__ . '/../includes/config_server1.php';
} elseif (file_exists(__DIR__ . '/../includes/config_server2.php')) {
    require_once __DIR__ . '/../includes/config_server2.php';
}

header('Content-Type: application/json');

// Verify API key for security
$headers = getallheaders();
$apiKey = $headers['X-API-Key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';

if (defined('API_SECRET_KEY') && $apiKey !== API_SECRET_KEY) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized - Invalid API Key']);
    exit;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        case 'get_jobs':
            // Server 1 provides jobs to Server 2
            $limit = intval($_GET['limit'] ?? 50);
            $status = $_GET['status'] ?? 'Open';
            
            $stmt = $conn->prepare("SELECT * FROM jobs WHERE status = ? ORDER BY createdAt DESC LIMIT ?");
            $stmt->bind_param('si', $status, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $jobs = $result->fetch_all(MYSQLI_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $jobs, 'count' => count($jobs)]);
            break;
            
        case 'get_applications':
            // Server 2 provides applications to Server 1
            $job_id = intval($_GET['job_id'] ?? 0);
            
            if ($job_id > 0) {
                $stmt = $conn->prepare("SELECT * FROM applications WHERE jobId = ? ORDER BY createdAt DESC");
                $stmt->bind_param('i', $job_id);
            } else {
                $stmt = $conn->prepare("SELECT * FROM applications ORDER BY createdAt DESC LIMIT 100");
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $applications = $result->fetch_all(MYSQLI_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $applications, 'count' => count($applications)]);
            break;
            
        case 'get_student':
            // Server 1 provides student data
            $student_id = intval($_GET['id'] ?? 0);
            
            if ($student_id === 0) {
                throw new Exception('Student ID required');
            }
            
            $stmt = $conn->prepare("SELECT s.*, sp.* FROM students s LEFT JOIN student_profiles sp ON s.email = sp.email WHERE s.id = ?");
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
            
            if ($student) {
                echo json_encode(['success' => true, 'data' => $student]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Student not found']);
            }
            break;
            
        case 'get_employer':
            // Server 2 provides employer data
            $employer_id = intval($_GET['id'] ?? 0);
            
            if ($employer_id === 0) {
                throw new Exception('Employer ID required');
            }
            
            $stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
            $stmt->bind_param('i', $employer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $employer = $result->fetch_assoc();
            
            if ($employer) {
                // Remove sensitive data
                unset($employer['password']);
                echo json_encode(['success' => true, 'data' => $employer]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Employer not found']);
            }
            break;
            
        case 'sync_user':
            // Sync user data between servers
            if ($method !== 'POST') {
                throw new Exception('POST method required');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['email'])) {
                throw new Exception('Invalid user data');
            }
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param('s', $data['email']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing user
                $stmt = $conn->prepare("UPDATE users SET username = ?, role = ?, updatedAt = NOW() WHERE email = ?");
                $stmt->bind_param('sss', $data['username'], $data['role'], $data['email']);
                $success = $stmt->execute();
                $message = 'User updated';
            } else {
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, createdAt, updatedAt) VALUES (?, ?, ?, ?, NOW(), NOW())");
                $stmt->bind_param('ssss', $data['username'], $data['email'], $data['password'], $data['role']);
                $success = $stmt->execute();
                $message = 'User created';
            }
            
            echo json_encode(['success' => $success, 'message' => $message]);
            break;
            
        case 'create_application':
            // Server 1 sends application to Server 2
            if ($method !== 'POST') {
                throw new Exception('POST method required');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $conn->prepare("INSERT INTO applications (studentId, jobId, status, coverLetter, createdAt, updatedAt) VALUES (?, ?, 'Applied', ?, NOW(), NOW())");
            $stmt->bind_param('iis', $data['studentId'], $data['jobId'], $data['coverLetter']);
            $success = $stmt->execute();
            $applicationId = $conn->insert_id;
            
            echo json_encode(['success' => $success, 'applicationId' => $applicationId]);
            break;
            
        case 'update_application_status':
            // Server 2 updates application status
            if ($method !== 'POST') {
                throw new Exception('POST method required');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $conn->prepare("UPDATE applications SET status = ?, updatedAt = NOW() WHERE id = ?");
            $stmt->bind_param('si', $data['status'], $data['applicationId']);
            $success = $stmt->execute();
            
            echo json_encode(['success' => $success]);
            break;
            
        case 'ping':
            // Health check
            echo json_encode([
                'success' => true,
                'server' => defined('SERVER_ROLE') ? SERVER_ROLE : 'UNKNOWN',
                'timestamp' => time(),
                'message' => 'Server is online'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
