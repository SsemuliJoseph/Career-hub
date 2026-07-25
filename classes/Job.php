<?php
/**
 * Job Model
 * Handles job-related database operations
 * Teaching notes:
 * - Examples of search, filtering, and aggregate queries.
 * - Be aware of performance: LIKE '%term%' scans can be slow on large tables.
 *   Consider full-text indexes or external search services for production.
 */
class Job extends Model {
    protected $table = 'jobs';
    
    /**
     * Search jobs by query
     * - Uses wildcard LIKE queries. Bind parameters to avoid SQL injection.
     * @param string $query
     * @return array
     */
    public function search($query) {
        $searchTerm = "%$query%";
        $sql = "SELECT * FROM {$this->table} 
                WHERE title LIKE ? 
                OR company LIKE ? 
                OR description LIKE ? 
                OR location LIKE ?
                ORDER BY created_at DESC
                LIMIT 50";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
    
    /**
     * Get jobs by type
     * @param string $type
     * @return array
     */
    public function getByType($type) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE type = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $type);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
    
    /**
     * Get jobs by company
     * @param string $company
     * @return array
     */
    public function getByCompany($company) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE company = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $company);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
    
    /**
     * Get recent jobs
     * - Demonstrates binding an integer LIMIT parameter.
     * @param int $limit
     * @return array
     */
    public function getRecent($limit = 10) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
    
    /**
     * Get job statistics
     * - Uses a direct query with aggregates. This method does not accept user input,
     *   so a direct $conn->query is acceptable here. If user input is used, always
     *   parameterize queries.
     * @return array
     */
    public function getStatistics() {
        $sql = "SELECT 
                COUNT(*) as total_jobs,
                COUNT(CASE WHEN type = 'Full-time' THEN 1 END) as full_time_count,
                COUNT(CASE WHEN type = 'Internship' THEN 1 END) as internship_count,
                COUNT(CASE WHEN type = 'Part-time' THEN 1 END) as part_time_count,
                COUNT(DISTINCT j.employer_id) as total_companies
                FROM {$this->table} j";
        
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row;
    }
}
