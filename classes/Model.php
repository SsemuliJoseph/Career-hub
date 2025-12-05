<?php
/**
 * Base Model Class (teaching notes)
 * Parent class for all models using mysqli
 *
 * Purpose:
 * - Provide common CRUD operations and helpers so concrete models (User, Job, etc.)
 *   can focus on domain-specific queries.
 * - Demonstrates safe usage of prepared statements and dynamic parameter binding.
 */
abstract class Model {
    protected $conn;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        // Use the globally available mysqli connection created in includes/db.php.
        // Explanation:
        // - $GLOBALS is a PHP superglobal array that stores all global variables.
        // - This project places the mysqli object into $GLOBALS['conn'] so it can
        //   be reused across models without passing it explicitly.
        // Note: For testable & modular code, prefer dependency injection instead
        // of relying on globals.
        if (!isset($GLOBALS['conn'])) {
            // require_once will include the file only once. __DIR__ is the
            // directory of the current file; we use it to build a relative path.
            require_once __DIR__ . '/../includes/db.php';
        }

        // Assign the mysqli connection object to $this->conn for use in methods.
        $this->conn = $GLOBALS['conn'];
    }
    
    /**
     * Find record by ID
     * - Example of a simple SELECT with a bound integer parameter.
     * @param int $id
     * @return array|null
     */
    public function find($id) {
        // Prepare a parameterized SQL statement. The ? is a placeholder.
        // prepare() returns a mysqli_stmt object on success or false on failure.
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1");

        // bind_param binds PHP variables to the parameter markers (?) in the
        // SQL statement. The first argument is a string describing the types:
        //  - 'i' for integer
        //  - 'd' for double (float)
        //  - 's' for string
        //  - 'b' for blob
        // Here we bind $id as an integer.
        $stmt->bind_param("i", $id);

        // Execute the prepared statement. This runs the query with the bound
        // parameters in a safe way (prevents SQL injection for those params).
        $stmt->execute();

        // get_result() returns a mysqli_result object which provides convenient
        // fetch methods (fetch_assoc, fetch_all). Note: get_result requires the
        // mysqlnd driver. If not available you'd use bind_result/fetch instead.
        $result = $stmt->get_result();

        // Fetch a single row as an associative array (column => value).
        $row = $result->fetch_assoc();

        // Close the statement to free resources (network buffers, prepared
        // statement objects on the server, etc.).
        $stmt->close();

        // Return the row if found, otherwise null. The ?: operator returns the
        // left-hand value if truthy, otherwise the right-hand value.
        return $row ?: null;
    }
    
    /**
     * Get all records with optional pagination
     * - Note: Using LIMIT and OFFSET via bound parameters is supported by mysqli.
     *   Some DB engines require integers, so ensure values are typed.
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function all($limit = 100, $offset = 0) {
        // Parameterized LIMIT/OFFSET query. Note: LIMIT and OFFSET expect
        // integers; binding them as 'i' ensures they are treated as integers.
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ? OFFSET ?");

        // Bind two integer parameters. bind_param takes the types string then
        // the variables to bind in order.
        $stmt->bind_param("ii", $limit, $offset);

        // Execute and fetch all results as an array of associative arrays.
        $stmt->execute();
        $result = $stmt->get_result();

        // fetch_all(MYSQLI_ASSOC) returns all rows as associative arrays.
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
    
    /**
     * Create new record
     * - Dynamically builds the INSERT statement and binds parameters.
     * - Teaching: this method infers parameter types (i, d, s). Be careful when
     *   mixing NULL values or when you need more precise control over types.
     * @param array $data
     * @return int Last insert ID
     */
    public function create(array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);
        // Build the types string and values array for bind_param.
        // bind_param requires a types string followed by variables. We infer
        // types from PHP variable types here. This is convenient but not
        // perfect for every situation (NULL handling, binary data, etc.).
        $types = '';
        $values = [];
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                // Default to string for everything else (including NULL).
                $types .= 's';
            }
            // Collect the value in the same order as the placeholders.
            $values[] = $value;
        }

        // The spread operator (...) unpacks the $values array into individual
        // arguments. So bind_param($types, ...$values) is equivalent to
        // bind_param($types, $values[0], $values[1], ...).
        $stmt->bind_param($types, ...$values);

        // Execute the INSERT. On success, mysqli will set insert_id on the
        // connection object which we can return.
        $stmt->execute();
        $insertId = $this->conn->insert_id;
        $stmt->close();

        return $insertId;
    }
    
    /**
     * Update record
     * - Builds SET clauses dynamically and appends the ID parameter.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data) {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "$key = ?";
        }
        $setClause = implode(', ', $set);
        
        $sql = "UPDATE {$this->table} SET $setClause WHERE {$this->primaryKey} = ?";
        $stmt = $this->conn->prepare($sql);
        
        // Bind parameters (data values first, then ID)
        $types = '';
        $values = [];
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        // Append the type for the ID (usually integer) and append the ID
        // value to the parameters list. Order matters: bound params follow the
        // order of the placeholders in the SQL statement.
        $types .= 'i'; // for the ID
        $values[] = $id;

        // Bind and execute. If bind_param fails it returns false; runtime
        // errors can be inspected via $stmt->error or $this->conn->error.
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();

        // Return boolean result of the execute call (true on success).
        return $result;
    }
    
    /**
     * Delete record by primary key
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // Simple DELETE by primary key. Always prefer parameterized queries
        // over interpolating variables directly into SQL to avoid injection.
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    /**
     * Execute custom query with parameters and return rows
     * - Useful for more complex queries. Caller is responsible for providing
     *   the correct types string and parameter order.
     * @param string $query
     * @param string $types Types string (e.g., "iss" for int, string, string)
     * @param array $params Parameters array
     * @return array
     */
    protected function query($query, $types = '', array $params = []) {
        // General purpose query helper. The caller provides the SQL, the
        // types string and the parameters in order. This keeps calling code
        // concise for custom queries.
        $stmt = $this->conn->prepare($query);

        // Only bind if types and params were provided. bind_param expects
        // variables by reference; passing literals will not work properly in
        // older PHP versions. Here we pass $params which are variables.
        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        // Return all rows as an array of associative arrays.
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
    
    /**
     * Execute query and return single row
     * @param string $query
     * @param string $types
     * @param array $params
     * @return array|null
     */
    protected function queryOne($query, $types = '', array $params = []) {
        // Like query() but returns a single row or null. Useful for queries
        // where you expect only one result (e.g., find by unique key).
        $stmt = $this->conn->prepare($query);

        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }
}
