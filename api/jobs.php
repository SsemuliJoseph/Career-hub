<?php
/**
 * api/jobs.php - Jobs REST API endpoint
 *
 * Teaching notes: This is a RESTful API endpoint that handles multiple HTTP methods:
 * - GET: Retrieve jobs (all or single by id)
 * - POST: Create new job (requires authentication and CSRF token)
 *
 * Key PHP concepts demonstrated:
 * - $_SERVER['REQUEST_METHOD']: Identifies HTTP verb (GET, POST, PUT, DELETE)
 * - header('Content-Type: ...'): Sends HTTP response header for JSON APIs
 * - http_response_code(xxx): Sets HTTP status code (200 OK, 401 Unauthorized, etc.)
 * - json_encode(): Converts PHP array to JSON string for API responses
 * - exit: Terminates script after sending response (critical for APIs)
 *
 * Security patterns:
 * - Authentication check: verify $_SESSION['user'] exists before mutations
 * - CSRF protection: validate token from POST/header to prevent cross-site attacks
 * - Prepared statements: bind parameters to prevent SQL injection
 * - Input validation: trim() and type cast user inputs
 */

// Set JSON content type for all responses (RESTful API convention)
header('Content-Type: application/json; charset=utf-8');

// Load dependencies: session for auth, db for mysqli connection, csrf for token validation
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php'; // provides $conn (mysqli object)
require_once __DIR__ . '/../includes/csrf.php';

/**
 * Determine which HTTP method was used: GET, POST, PUT, DELETE, etc.
 * $_SERVER is a PHP superglobal containing server/request info.
 */
$method = $_SERVER['REQUEST_METHOD'];

// ===== GET: Retrieve jobs =====
if ($method === 'GET') {
    // Check if client requested a specific job by id query parameter
    // Use (int) cast to safely convert string to integer (prevents injection)
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($id) {
        // Single job retrieval with prepared statement (secure parameter binding)
        $stmt = $conn->prepare("SELECT id, title, company, description, created_at FROM jobs WHERE id = ? LIMIT 1");
        // bind_param("i", $id): "i" = integer type hint for mysqli
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        // fetch_assoc() returns one row as associative array or null if not found
        $job = $res->fetch_assoc();
        $stmt->close();
        
        // Return 404 if job doesn't exist
        if (!$job) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Not found']); 
            exit; 
        }
        
        // Success: return job object wrapped in JSON
        echo json_encode(['job' => $job]); 
        exit;
    } else {
        // Return all jobs with truncated descriptions (performance optimization)
        // LEFT(description, 400) returns first 400 chars of description column
        $res = $conn->query("SELECT id, title, company, LEFT(description, 400) as description, created_at FROM jobs ORDER BY created_at DESC");
        // fetch_all(MYSQLI_ASSOC) returns array of associative arrays
        $jobs = $res->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['jobs' => $jobs]); 
        exit;
    }
}

// ===== POST: Create new job =====
if ($method === 'POST') {
    // Authentication guard: only logged-in users can create jobs
    if (empty($_SESSION['user'])) { 
        http_response_code(401); 
        echo json_encode(['error'=>'Unauthorized']); 
        exit; 
    }
    
    // CSRF token validation: accept from POST body or HTTP header
    // ?? is null coalescing operator: returns first defined (non-null) value
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!verify_csrf_token($token)) { 
        http_response_code(403); 
        echo json_encode(['error'=>'Invalid CSRF token']); 
        exit; 
    }

    // Sanitize inputs with trim() to remove leading/trailing whitespace
    $title = trim($_POST['title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validate required fields: empty string evaluates to false
    if (!$title || !$company || !$description) { 
        http_response_code(400); 
        echo json_encode(['error'=>'Missing fields']); 
        exit; 
    }

    // Generate UTC timestamp using DateTime class (OOP approach)
    $createdAt = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    
    // Insert with prepared statement: 4 string parameters (ssss)
    $stmt = $conn->prepare("INSERT INTO jobs (title, company, description, created_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $company, $description, $createdAt);
    $stmt->execute();
    
    // insert_id is a mysqli property returning the auto-increment ID from last INSERT
    $newId = $conn->insert_id;
    $stmt->close();
    
    // Return success response with the new job's ID
    echo json_encode(['success' => true, 'job_id' => $newId]); 
    exit;
}

// ===== Fallback: Method not allowed =====
// If request method is not GET or POST, return 405 Method Not Allowed
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
