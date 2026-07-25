<?php
/**
 * Application Model
 * Handles job application operations
 *
 * Teaching notes:
 * - This class extends Model (which provides base CRUD methods) to specialize in
 *   job application logic. It demonstrates typical patterns for relationships (JOINs),
 *   aggregate queries (statistics), and soft validation (hasApplied).
 * - Methods use prepared statements to prevent SQL injection.
 * - This class shows how to structure business logic around a single database entity.
 */
class Application extends Model {
    /**
     * protected $table identifies the DB table for base Model methods like create/update.
     * Child classes override this property to target different tables.
     */
    protected $table = 'applications';
    
    /**
     * Get applications by user
     * - Uses a LEFT JOIN to include job details (title, company, location).
     * - LEFT JOIN means include the application even if the job record is missing (defensive)
     * - bind_param("i", $userId): "i" means integer parameter type for security.
     * @param int $userId
     * @return array
     */
    public function getByUser($userId) {
        $sql = "SELECT a.*, j.title, j.company, j.location 
                FROM {$this->table} a
                LEFT JOIN jobs j ON a.job_id = j.id
                WHERE a.user_id = ?
                ORDER BY a.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
    
    /**
     * Get applications by job
     * - Returns all applicants for a given job, with user details attached.
     * - LEFT JOIN ensures we get application data even if a user record is somehow missing.
     * @param int $jobId
     * @return array
     */
    public function getByJob($jobId) {
        $sql = "SELECT a.*, u.name, u.email, u.phone 
                FROM {$this->table} a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.job_id = ?
                ORDER BY a.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        // Bind integer parameter
        $stmt->bind_param("i", $jobId);
        $stmt->execute();
        $result = $stmt->get_result();
        // fetch_all(MYSQLI_ASSOC) returns an array of associative arrays
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
    
    /**
     * Check if user has already applied to job
     * - Prevents duplicate applications. Use this before create() to enforce uniqueness.
     * - COUNT(*) aggregates rows. We check if count > 0.
     * - bind_param("ii", ...): two integer parameters (userId, jobId)
     * @param int $userId
     * @param int $jobId
     * @return bool
     */
    public function hasApplied($userId, $jobId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ? AND job_id = ?");
        $stmt->bind_param("ii", $userId, $jobId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        // Cast to bool: any count > 0 becomes true
        return $row['count'] > 0;
    }
    
    /**
     * Update application status
     * - Wrapper around base update() method to set status and timestamp.
     * - This is a convenience method: calling code doesn't need to remember to set updated_at.
     * @param int $id Application ID
     * @param string $status New status value
     * @return bool Success flag
     */
    public function updateStatus($id, $status) {
        // Delegate to base Model's update() which uses prepared statements internally
        return $this->update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get application statistics
     * - Aggregates data with COUNT and CASE WHEN for counting by status.
     * - This query has no user input, so direct $this->conn->query is acceptable.
     * - CASE WHEN status = 'X' THEN 1 END returns 1 for matching rows, null otherwise.
     *   COUNT ignores nulls, so we effectively count rows where status matches.
     * @return array Associative array with keys: total_applications, pending_count, etc.
     */
    public function getStatistics() {
        $sql = "SELECT 
                COUNT(*) as total_applications,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'reviewed' THEN 1 END) as reviewed_count,
                COUNT(CASE WHEN status = 'accepted' THEN 1 END) as accepted_count,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count
                FROM {$this->table}";
        
        // Execute without parameters
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row;
    }
}
