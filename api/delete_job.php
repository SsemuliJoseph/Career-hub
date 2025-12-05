<?php
/* Teaching: delete_job.php
   Purpose: Admin-only endpoint to remove a job posting.
   Important security and operational notes:
   - Restrict to admin users via session role check
   - Use prepared statements for destructive actions to prevent SQL injection
   - Consider soft-delete (mark as inactive) instead of hard DELETE to preserve history
   - Return clear JSON responses for client-side handling
*/
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $jobId = $input['job_id'] ?? null;

    if (!$jobId) {
        throw new Exception('Job ID is required');
    }

    // Delete the job
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $jobId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Job deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete job');
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
