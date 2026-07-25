<?php
/**
 * WebSocket Server Class
 * Handles real-time communication for job notifications
 * 
 * To run this server, execute: php websocket_server.php
 * 
 * This class implements a custom WebSocket server that enables:
 * - Real-time push notifications to connected clients
 * - User-specific message targeting
 * - Channel-based pub/sub messaging
 * - Rate limiting for spam prevention
 * - Offline notification queuing
 */
class WebSocketServer {
    // Server configuration
    private $address;  // IP address to bind to (e.g., '0.0.0.0' = all interfaces)
    private $port;     // Port number to listen on (e.g., 8080)
    
    // Core socket management
    private $socket;   // Main server socket resource
    private $clients = [];  // Array of all connected client socket resources
    
    // User and channel tracking
    private $userConnections = [];  // Maps userId (int) => client socket resource
    private $channels = [];         // Maps channel name => array of subscribed client sockets
    
    // Client metadata and rate limiting
    private $clientMeta = [];   // Maps (int)client_socket => ['userId' => X]
    private $rateLimits = [];   // Maps (int)client_socket => ['start' => timestamp, 'count' => N]
    
    // Notification queue settings
    private $notificationQueue;       // File path for persistent notification queue (JSON)
    private $lastQueueCheck = 0;      // Last time queue was processed (milliseconds)
    private $queueCheckIntervalMs = 500; // Process queue every 0.5 seconds
    
    /**
     * Constructor
     * 
     * @param string $address IP to bind server to ('0.0.0.0' for all interfaces)
     * @param int $port Port number to listen on
     */
    public function __construct($address = '0.0.0.0', $port = 8080) {
        // Save network configuration
        $this->address = $address;
        $this->port = $port;
        
        // Set path for notification queue file (persists messages for offline users)
        $this->notificationQueue = __DIR__ . '/../cache/notifications.json';
    }
    
    /**
     * Start the WebSocket server
     * 
     * Creates server socket, binds to address:port, and enters event loop
     */
    public function start() {
        // Create TCP socket using IPv4 (AF_INET), Stream socket (SOCK_STREAM), TCP protocol (SOL_TCP)
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        // Check if socket creation succeeded
        if ($this->socket === false) {
            // Display error message with socket error code and exit
            die("socket_create() failed: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        // Set socket option to allow address reuse (prevents "Address already in use" errors on restart)
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        // Bind socket to IP address and port
        // Makes server listen on specific address:port combination
        if (socket_bind($this->socket, $this->address, $this->port) === false) {
            // Display bind error and exit
            die("socket_bind() failed: " . socket_strerror(socket_last_error($this->socket)) . "\n");
        }
        
        // Start listening for incoming connections
        // Backlog of 5 means up to 5 pending connections can queue while handling current ones
        if (socket_listen($this->socket, 5) === false) {
            // Display listen error and exit
            die("socket_listen() failed: " . socket_strerror(socket_last_error($this->socket)) . "\n");
        }
        
        // Log successful server start to console
        echo "WebSocket server started on {$this->address}:{$this->port}\n";
        
        // Main server event loop - runs continuously until server is stopped
        while (true) {
            // Prepare array of sockets to monitor for read activity
            // Merge server socket (for new connections) with all client sockets (for messages)
            $read = array_merge([$this->socket], $this->clients);
            $write = null;  // Not monitoring write sockets
            $except = null; // Not monitoring exceptional conditions
            
            // Wait for activity on any socket with 100ms (100,000 microseconds) timeout
            // socket_select() blocks execution until socket has data or timeout expires
            // Returns number of sockets with activity, or false on error
            if (socket_select($read, $write, $except, 0, 100000) === false) { // 100ms
                // If select() fails, break out of main loop (critical error)
                break;
            }
            
            // Check if server socket has activity (indicates new incoming connection)
            if (in_array($this->socket, $read)) {
                // Accept the new client connection request
                $newClient = socket_accept($this->socket);
                
                // Add new client socket to clients array immediately
                $this->clients[] = $newClient;
                
                // Read HTTP upgrade request headers from client (max 2048 bytes)
                $header = socket_read($newClient, 2048);
                
                // Perform WebSocket handshake to upgrade HTTP connection to WebSocket
                // Returns true if handshake succeeds, false if unauthorized or malformed
                if (!$this->performHandshake($header, $newClient)) {
                    // Handshake failed or unauthorized - close connection
                    @socket_close($newClient);
                    
                    // Remove failed client from clients array
                    $key = array_search($newClient, $this->clients);
                    if ($key !== false) unset($this->clients[$key]);
                    continue;
                }
                
                // Handshake successful - send welcome message to client
                $this->sendMessage($newClient, json_encode([
                    'type' => 'connection',
                    'message' => 'Connected to Career Hub WebSocket'
                ]));
                
                // Remove server socket from $read array (already processed)
                $key = array_search($this->socket, $read);
                unset($read[$key]);
            }
            
            // Process messages from all connected clients
            foreach ($read as $client) {
                // Read data from client socket (max 1024 bytes, binary mode)
                $data = @socket_read($client, 1024, PHP_BINARY_READ);
                
                // Check if read failed or connection closed (empty data)
                if ($data === false || $data === '') {
                    // Client disconnected - clean up and remove from arrays
                    $this->disconnect($client);
                    continue;
                }
                
                // Unmask WebSocket frame to extract actual message payload
                // WebSocket protocol requires client-to-server messages to be masked
                $message = $this->unmask($data);
                
                // Check if client is within rate limit (prevents spam/DoS)
                if ($this->checkRateLimit($client) === false) {
                    // Rate limit exceeded - send error and disconnect abusive client
                    $this->sendMessage($client, json_encode(['type' => 'error', 'message' => 'Rate limit exceeded']));
                    $this->disconnect($client);
                    continue;
                }
                
                // Process the message (register, subscribe, broadcast, etc.)
                $this->handleMessage($client, $message);
            }

            // Periodically process notification queue for offline/failed message delivery
            // Get current timestamp in milliseconds
            $now = (int)(microtime(true) * 1000);
            
            // Check if enough time has elapsed since last queue processing
            if ($now - $this->lastQueueCheck >= $this->queueCheckIntervalMs) {
                // Process any queued notifications (send to newly connected users)
                $this->processNotificationQueue();
                
                // Update last check timestamp
                $this->lastQueueCheck = $now;
            }
        }
    }
    
    /**
     * Perform WebSocket handshake
     * 
     * Upgrades HTTP connection to WebSocket by parsing headers and sending upgrade response.
     * Also handles optional token-based authentication via query string.
     * 
     * @param string $rawHeaders Raw HTTP request headers from client
     * @param resource $client Client socket resource
     * @return bool True if handshake successful, false if rejected
     */
    private function performHandshake($rawHeaders, $client) {
        // Split raw headers into individual lines
        $lines = preg_split("/\r\n/", $rawHeaders);
        $headers = [];  // Will store parsed HTTP headers
        $path = '/';    // Will store request path (may contain query string)
        
        // Parse each header line
        foreach ($lines as $line) {
            // Remove trailing whitespace
            $line = rtrim($line);
            
            // Check if line is the GET request line (e.g., "GET /?token=abc HTTP/1.1")
            if (preg_match('/^GET\s+(\S+)\s+HTTP\//i', $line, $m)) {
                $path = $m[1];  // Extract path/query string
                
            // Check if line is a header (e.g., "Host: localhost:8080")
            } elseif (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                // Store header as key-value pair
                $headers[$matches[1]] = $matches[2];
            }
        }
        
        // Verify required WebSocket header is present
        if (!isset($headers['Sec-WebSocket-Key'])) {
            // Missing required header - reject handshake
            return false;
        }
        
        // Optional token-based authentication via query string: ws://host:port/?token=xyz
        $authorized = true;  // Default to authorized (if no token required)
        $userId = null;      // Will store user ID if authenticated
        
        // Parse URL to check for query string parameters
        $urlParts = parse_url($path);
        
        // If query string exists
        if (!empty($urlParts['query'])) {
            // Parse query string into associative array
            parse_str($urlParts['query'], $qs);
            
            // If token parameter provided
            if (!empty($qs['token'])) {
                // Validate the WebSocket token (checks cache/ws_tokens/)
                $meta = $this->validateWsToken($qs['token']);
                
                if ($meta) {
                    // Token valid - extract user ID and register connection
                    $userId = (int)$meta['userId'];
                    $this->userConnections[$userId] = $client;  // Map userId to socket
                    $this->clientMeta[(int)$client] = ['userId' => $userId];  // Store client metadata
                } else {
                    // Token invalid or expired - reject connection
                    $authorized = false;
                }
            }
        }

        // If authorization failed
        if ($authorized === false) {
            // Send HTTP 401 Unauthorized response
            $response = "HTTP/1.1 401 Unauthorized\r\nConnection: close\r\n\r\n";
            @socket_write($client, $response, strlen($response));
            return false;
        }

        // Generate WebSocket accept key using client's key
        $secKey = $headers['Sec-WebSocket-Key'];
        
        // WebSocket protocol requires concatenating client key with magic GUID
        // Then SHA1 hash and base64 encode the result
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        
        // Build HTTP 101 Switching Protocols response
        $response = "HTTP/1.1 101 Switching Protocols\r\n";
        $response .= "Upgrade: websocket\r\n";       // Upgrade to WebSocket protocol
        $response .= "Connection: Upgrade\r\n";      // Connection must upgrade
        $response .= "Sec-WebSocket-Accept: $secAccept\r\n\r\n";  // Computed accept key
        
        // Send handshake response to client
        socket_write($client, $response, strlen($response));
        
        // Handshake complete - connection is now upgraded to WebSocket
        return true;
    }
    
    /**
     * Handle incoming message from client
     * 
     * Processes different message types:
     * - register: Associate userId with connection for targeted messaging
     * - subscribe: Subscribe client to a channel for pub/sub
     * - unsubscribe: Unsubscribe client from a channel
     * - ping: Keepalive check (responds with pong)
     * - default: Broadcast to channel or all clients
     * 
     * @param resource $client Client socket that sent the message
     * @param string $message JSON-encoded message string
     */
    private function handleMessage($client, $message) {
        // Decode JSON message to associative array
        $data = json_decode($message, true);
        
        // If message is not valid JSON, ignore it
        if (!$data) {
            return;
        }
        
        // Handle message based on type field
        switch ($data['type'] ?? '') {
            case 'register':
                // Register user with their connection for targeted messaging
                $userId = $data['userId'] ?? null;
                if ($userId) {
                    // Map userId to client socket for sendToUser() functionality
                    $this->userConnections[$userId] = $client;
                    
                    // Store userId in client metadata
                    $this->clientMeta[(int)$client]['userId'] = (int)$userId;
                }
                break;
                
            case 'subscribe':
                // Subscribe client to a channel (e.g., "admin_notifications", "job_updates")
                $channel = $data['channel'] ?? null;
                if ($channel) {
                    $this->subscribe($client, $channel);
                }
                break;
                
            case 'unsubscribe':
                // Unsubscribe client from a channel
                $channel = $data['channel'] ?? null;
                if ($channel) {
                    $this->unsubscribe($client, $channel);
                }
                break;
                
            case 'ping':
                // Respond to keepalive ping with pong
                $this->sendMessage($client, json_encode(['type' => 'pong']));
                break;
                
            default:
                // If no recognized type, check if message should go to a channel
                if (!empty($data['channel'])) {
                    // Broadcast to all subscribers of the specified channel
                    $this->broadcastToChannel($data['channel'], json_encode($data));
                } else {
                    // Broadcast to all connected clients
                    $this->broadcast($message);
                }
                break;
        }
    }
    
    /**
     * Send message to specific client
     * 
     * @param resource $client Client socket to send to
     * @param string $message Message string (will be masked)
     */
    private function sendMessage($client, $message) {
        // Mask message according to WebSocket protocol
        $message = $this->mask($message);
        
        // Write masked message to client socket
        @socket_write($client, $message, strlen($message));
    }
    
    /**
     * Broadcast message to all connected clients
     * 
     * @param string $message Message to broadcast
     */
    public function broadcast($message) {
        // Mask message once (more efficient than masking for each client)
        $message = $this->mask($message);
        
        // Send to every connected client
        foreach ($this->clients as $client) {
            @socket_write($client, $message, strlen($message));
        }
    }

    /**
     * Broadcast message to all subscribers of a specific channel
     * 
     * Implements pub/sub pattern for targeted notifications
     * 
     * @param string $channel Channel name (e.g., "admin_notifications")
     * @param string $message Message to broadcast
     */
    public function broadcastToChannel($channel, $message) {
        // Mask message once
        $message = $this->mask($message);
        
        // Check if channel exists and has subscribers
        if (!isset($this->channels[$channel])) return;
        
        // Send to all clients subscribed to this channel
        foreach ($this->channels[$channel] as $client) {
            @socket_write($client, $message, strlen($message));
        }
    }
    
    /**
     * Send message to specific user by their userId
     * 
     * Requires user to have registered their connection via 'register' message type
     * 
     * @param int $userId User ID from database
     * @param string $message Message to send
     */
    public function sendToUser($userId, $message) {
        // Check if user has a registered connection
        if (isset($this->userConnections[$userId])) {
            // Send message to user's socket
            $this->sendMessage($this->userConnections[$userId], $message);
        }
    }
    
    /**
     * Disconnect client and clean up all references
     * 
     * Removes client from:
     * - Main clients array
     * - User connections mapping
     * - All channel subscriptions
     * - Client metadata storage
     * 
     * @param resource $client Client socket to disconnect
     */
    private function disconnect($client) {
        // Remove from main clients array
        $key = array_search($client, $this->clients);
        if ($key !== false) {
            unset($this->clients[$key]);
        }
        
        // Remove from user connections mapping
        foreach ($this->userConnections as $userId => $conn) {
            if ($conn === $client) {
                unset($this->userConnections[$userId]);
                break;
            }
        }
        // Remove from all channel subscriptions
        foreach ($this->channels as $channel => $subs) {
            // Find client in channel's subscriber array
            $idx = array_search($client, $subs, true);
            if ($idx !== false) {
                // Remove from this channel
                unset($this->channels[$channel][$idx]);
            }
        }
        
        // Remove client metadata
        unset($this->clientMeta[(int)$client]);
        
        // Close the socket connection
        @socket_close($client);
    }

    /**
     * Subscribe client to a channel
     * 
     * Channels enable pub/sub messaging pattern (e.g., "admin_notifications", "job_updates")
     * 
     * @param resource $client Client socket to subscribe
     * @param string $channel Channel name
     */
    private function subscribe($client, $channel) {
        // Create channel array if it doesn't exist
        if (!isset($this->channels[$channel])) {
            $this->channels[$channel] = [];
        }
        
        // Add client to channel if not already subscribed (prevent duplicates)
        if (!in_array($client, $this->channels[$channel], true)) {
            $this->channels[$channel][] = $client;
        }
    }

    /**
     * Unsubscribe client from a channel
     * 
     * @param resource $client Client socket to unsubscribe
     * @param string $channel Channel name
     */
    private function unsubscribe($client, $channel) {
        // If channel doesn't exist, nothing to do
        if (!isset($this->channels[$channel])) return;
        
        // Find client in channel's subscriber array
        $idx = array_search($client, $this->channels[$channel], true);
        if ($idx !== false) {
            // Remove client from channel
            unset($this->channels[$channel][$idx]);
        }
    }

    /**
     * Validate WebSocket authentication token
     * 
     * Tokens are stored in cache/ws_tokens/ directory with expiration times.
     * Used for authenticating WebSocket connections.
     * 
     * @param string $token Token string from query parameter
     * @return array|null Token metadata if valid, null if invalid/expired
     */
    private function validateWsToken($token) {
        // Build path to token file
        $dir = __DIR__ . '/../cache/ws_tokens';
        $file = $dir . '/' . basename($token) . '.json';
        
        // Check if token file exists
        if (!file_exists($file)) return null;
        
        // Read and decode token metadata
        $meta = json_decode(@file_get_contents($file), true);
        if (!$meta) return null;
        
        // Check if token has expired
        if (empty($meta['expiresAt']) || time() > (int)$meta['expiresAt']) return null;
        
        // Token valid - return metadata (includes userId)
        return $meta;
    }

    /**
     * Process notification queue
     * 
     * Handles queued notifications for users who were offline or delivery failed.
     * Attempts to deliver messages to now-connected users.
     */
    private function processNotificationQueue() {
        // Check if queue file exists
        if (!file_exists($this->notificationQueue)) return;
        
        // Read queue file
        $json = @file_get_contents($this->notificationQueue);
        if ($json === false) return;
        
        // Parse JSON array of queued messages
        $items = json_decode($json, true);
        if (!is_array($items) || empty($items)) return;

        // Track messages that still can't be delivered (for re-queuing)
        $remaining = [];
        
        // Process each queued notification
        foreach ($items as $item) {
            // Skip if already marked as sent
            if (!empty($item['sent'])) { continue; }
            
            // Build message payload
            $payloadArr = [
                'type' => $item['type'] ?? 'message',
                'data' => $item['data'] ?? []
            ];
            $payload = json_encode($payloadArr);
            
            // Determine delivery method and attempt to send
            if (!empty($item['userId'])) {
                // User-specific notification
                $this->sendToUser((int)$item['userId'], $payload);
            } elseif (!empty($item['channel'])) {
                // Channel broadcast
                $this->broadcastToChannel($item['channel'], json_encode($payloadArr));
            } else {
                // Broadcast to all clients
                $this->broadcast($payload);
            }
            // Note: Items are not re-queued (mark sent by not adding to $remaining)
        }
        
        // Write back any remaining undeliverable messages
        @file_put_contents($this->notificationQueue, json_encode($remaining, JSON_PRETTY_PRINT));
    }

    /**
     * Check if client is within rate limit
     * 
     * Prevents spam/DoS attacks by limiting messages per time window.
     * Limit: 30 messages per 10 seconds per client.
     * 
     * @param resource $client Client socket to check
     * @return bool True if within limit, false if exceeded
     */
    private function checkRateLimit($client) {
        // Use socket integer as key
        $key = (int)$client;
        $now = microtime(true);
        
        // Rate limit settings
        $window = 10.0; // 10-second sliding window
        $limit = 30;    // Max 30 messages in window
        
        // Initialize tracking if first message from this client
        if (!isset($this->rateLimits[$key])) {
            $this->rateLimits[$key] = ['start' => $now, 'count' => 0];
        }
        
        // Get reference to rate limit entry for this client
        $entry = &$this->rateLimits[$key];
        
        // If window has expired, reset counter
        if ($now - $entry['start'] > $window) {
            $entry['start'] = $now;
            $entry['count'] = 0;
        }
        
        // Increment message count
        $entry['count']++;
        
        // Return true if still within limit, false if exceeded
        return $entry['count'] <= $limit;
    }
    
    /**
     * Mask message for WebSocket protocol
     * 
     * WebSocket protocol requires server messages to have specific frame format.
     * Creates proper WebSocket frame with opcode (0x1 = text frame) and length encoding.
     * 
     * @param string $text Message text to mask
     * @return string Masked WebSocket frame
     */
    private function mask($text) {
        // FIN bit (0x80) + Opcode (0x1 for text frame)
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);
        
        // Encode length based on message size
        if ($length <= 125) {
            // Short message - length fits in 7 bits
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            // Medium message - use extended 16-bit length
            $header = pack('CCn', $b1, 126, $length);
        } else {
            // Large message - use extended 64-bit length
            $header = pack('CCNN', $b1, 127, $length);
        }
        
        // Return header + message payload
        return $header . $text;
    }
    
    /**
     * Unmask message from client
     * 
     * WebSocket protocol requires client messages to be masked with 4-byte key.
     * This method extracts the masking key and unmasks the payload.
     * 
     * @param string $text Masked WebSocket frame from client
     * @return string Unmasked message payload
     */
    private function unmask($text) {
        // Extract payload length from second byte (bits 0-6)
        $length = ord($text[1]) & 127;
        
        // Determine where masking key and data start based on length encoding
        if ($length == 126) {
            // Extended 16-bit length
            $masks = substr($text, 4, 4);  // Masking key at bytes 4-7
            $data = substr($text, 8);       // Payload starts at byte 8
        } elseif ($length == 127) {
            // Extended 64-bit length
            $masks = substr($text, 10, 4);  // Masking key at bytes 10-13
            $data = substr($text, 14);       // Payload starts at byte 14
        } else {
            // Standard length (0-125)
            $masks = substr($text, 2, 4);   // Masking key at bytes 2-5
            $data = substr($text, 6);        // Payload starts at byte 6
        }
        
        // Unmask the data by XOR-ing each byte with corresponding mask byte
        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            // XOR data byte with mask byte (cycling through 4-byte mask)
            $text .= $data[$i] ^ $masks[$i % 4];
        }
        
        // Return unmasked message payload
        return $text;
    }
}

