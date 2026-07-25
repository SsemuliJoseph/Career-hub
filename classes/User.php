<?php
/**
 * User Model
 * Handles user-related database operations
 *
 * Teaching notes:
 * - This model extends a base Model class which provides common CRUD helpers.
 * - Methods below demonstrate safe DB usage via prepared statements and proper
 *   password handling using PHP's password_hash/password_verify functions.
 */
class User extends Model {
    protected $table = 'users';
    
    /**
     * Find user by email
     * - Uses prepared statements to avoid SQL injection.
     * @param string $email
     * @return array|null
     */
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        // bind_param expects types: s = string
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        // Return associative array or null if not found
        return $row ?: null;
    }
    
    /**
     * Find user by username
     * @param string $username
     * @return array|null
     */
    public function findByUsername($username) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
    
    /**
     * Get users by role
     * - Example of returning multiple rows with fetch_all.
     * @param string $role
     * @return array
     */
    public function getByRole($role) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE role = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        // MYSQLI_ASSOC returns an array of associative arrays
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
    
    /**
     * Verify user password
     * - Demonstrates authentication by comparing a plain password to the
     *   hashed password stored in the DB using password_verify.
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            // No user with that email
            return false;
        }
        
        // password_verify handles timing-safe comparisons and supports different algorithms
        if (password_verify($password, $user['password'])) {
            // Remove password from returned data for safety before returning
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Register new user
     * - Hash the password, set timestamps, and delegate to base create() method.
     * - Always use password_hash with PASSWORD_DEFAULT so PHP can evolve the algorithm.
     * @param array $data
     * @return int|false
     */
    public function register(array $data) {
        // Hash password before storing
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Add timestamp for record keeping
        $data['created_at'] = date('Y-m-d H:i:s');
        
        try {
            // create() comes from Model and handles building the INSERT statement
            return $this->create($data);
        } catch (Exception $e) {
            // Log and return false on failure; higher-level code should surface user-friendly messages.
            error_log("User registration error: " . $e->getMessage());
            return false;
        }
    }
}
