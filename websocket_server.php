<?php
/**
 * WebSocket Server Runner
 * 
 * To run this server, execute in terminal:
 * php websocket_server.php
 * 
 * The server will listen on port 8080 for WebSocket connections
 */

// Include the autoloader to access all classes (WebSocketServer, etc.)
require_once __DIR__ . '/classes/autoload.php';

// Display banner header to console when server starts
echo "===========================================\n";
echo "Career Hub WebSocket Server\n";
echo "===========================================\n\n";

try {
    // Create WebSocket server instance
    // '0.0.0.0' = Bind to all available network interfaces (allow external connections)
    // 8080 = Port number for WebSocket connections (ws://hostname:8080)
    $server = new WebSocketServer('0.0.0.0', 8080);
    
    // Start the server - this enters an infinite loop handling connections
    // Server will continue running until manually stopped (Ctrl+C)
    $server->start();
    
} catch (Exception $e) {
    // If server startup fails (port already in use, insufficient permissions, etc.)
    // Display the error message and exit with error code
    echo "Error starting WebSocket server: " . $e->getMessage() . "\n";
    exit(1);
}

