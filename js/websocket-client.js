/**
 * WebSocket Client for Career Hub
 * Handles real-time notifications and updates
 * 
 * Features:
 * - Automatic reconnection with exponential backoff
 * - Channel-based subscriptions (pub/sub pattern)
 * - User registration for targeted messaging
 * - Browser notifications for job updates
 * - Event listener system for custom handling
 */

class CareerHubWebSocket {
    /**
     * Constructor
     * @param {string} url WebSocket server URL (e.g., 'ws://localhost:8080')
     */
    constructor(url = 'ws://localhost:8080') {
        this.url = url;                    // WebSocket server URL
        this.socket = null;                // WebSocket instance (set when connected)
        this.reconnectAttempts = 0;        // Number of reconnection attempts made
        this.maxReconnectAttempts = 5;     // Max reconnections before giving up
        this.reconnectDelay = 3000;        // Delay between reconnection attempts (3 seconds)
        this.isConnected = false;          // Connection status flag
        this.listeners = {};               // Event listeners: { eventName: [callbacks] }
        this.subscriptions = new Set();    // Set of channel names to auto-resubscribe on reconnect
        this.authToken = this.getAuthToken();  // Get authentication token from storage
    }

    /**
     * Connect to WebSocket server
     * 
     * Establishes WebSocket connection and sets up event handlers for:
     * - open: Connection established
     * - message: Incoming messages from server
     * - error: Connection errors
     * - close: Connection closed/lost
     */
    connect() {
        try {
            // Append authentication token to URL if available (ws://host:port/?token=xyz)
            const fullUrl = this.appendToken(this.url, this.authToken);
            
            // Create new WebSocket connection
            this.socket = new WebSocket(fullUrl);
            
            // Handle connection opened event
            this.socket.onopen = (event) => {
                console.log('WebSocket connected');
                this.isConnected = true;              // Update connection status
                this.reconnectAttempts = 0;           // Reset reconnection counter
                this.trigger('connected', event);     // Trigger connected event for listeners
                
                // Register user if logged in (fallback after token handshake)
                const userId = this.getUserId();
                if (userId) {
                    this.register(userId);  // Send register message to server
                }
                
                // Resubscribe to all channels after reconnection
                // Important for maintaining subscriptions after connection loss
                this.subscriptions.forEach(ch => this.subscribe(ch));
            };
            
            // Handle incoming messages from server
            this.socket.onmessage = (event) => {
                try {
                    // Parse JSON message from server
                    const data = JSON.parse(event.data);
                    console.log('WebSocket message:', data);
                    
                    // Route message to appropriate handler based on type
                    switch (data.type) {
                        case 'connection':
                            // Welcome message from server
                            this.trigger('connection', data);
                            break;
                            
                        case 'job_notification':
                            // New job posting notification
                            this.handleJobNotification(data);
                            break;
                            
                        case 'application_update':
                            // Application status changed
                            this.handleApplicationUpdate(data);
                            break;
                            
                        case 'pong':
                            // Response to keepalive ping (no action needed)
                            break;
                            
                        default:
                            // Generic message - trigger for custom listeners
                            this.trigger('message', data);
                            break;
                    }
                } catch (e) {
                    // JSON parsing or handler errors
                    console.error('Error parsing WebSocket message:', e);
                }
            };
            
            // Handle connection errors
            this.socket.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.trigger('error', error);  // Trigger error event for listeners
            };
            
            // Handle connection closed
            this.socket.onclose = (event) => {
                console.log('WebSocket disconnected');
                this.isConnected = false;            // Update connection status
                this.trigger('disconnected', event); // Trigger disconnected event
                this.attemptReconnect();             // Try to reconnect automatically
            };
            
        } catch (error) {
            // Exception during WebSocket creation (invalid URL, browser doesn't support WebSocket, etc.)
            console.error('Failed to create WebSocket connection:', error);
            this.attemptReconnect();  // Try to reconnect
        }
    }

    /**
     * Attempt to reconnect to WebSocket server
     * 
     * Uses exponential backoff strategy:
     * - Limits reconnection attempts to maxReconnectAttempts
     * - Waits reconnectDelay milliseconds between attempts
     * - Triggers event if max attempts reached
     */
    attemptReconnect() {
        // Check if we haven't exceeded max reconnection attempts
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;  // Increment counter
            console.log(`Attempting to reconnect... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
            
            // Schedule reconnection after delay
            setTimeout(() => {
                this.connect();  // Retry connection
            }, this.reconnectDelay);
        } else {
            // Max attempts reached - give up
            console.error('Max reconnection attempts reached');
            this.trigger('maxReconnectAttemptsReached');  // Trigger event for UI notification
        }
    }

    /**
     * Register user with WebSocket server
     * 
     * Associates this WebSocket connection with a specific user ID.
     * Enables server to send targeted notifications to this user.
     * 
     * @param {number} userId User's database ID
     */
    register(userId) {
        this.send({
            type: 'register',  // Message type to trigger registration handler
            userId: userId     // User ID from database
        });
    }

    /**
     * Subscribe to a channel for pub/sub notifications
     * 
     * Channels allow filtering messages by topic (e.g., "admin_notifications", "job_updates").
     * Subscription is automatically restored after reconnection.
     * 
     * @param {string} channel Channel name to subscribe to
     */
    subscribe(channel) {
        if (!channel) return;  // Ignore empty channel names
        
        // Add to subscriptions set for auto-resubscribe on reconnect
        this.subscriptions.add(channel);
        
        // Send subscribe message to server
        this.send({ type: 'subscribe', channel });
    }

    /**
     * Unsubscribe from a channel
     * 
     * Stops receiving messages from specified channel
     * 
     * @param {string} channel Channel name to unsubscribe from
     */
    unsubscribe(channel) {
        if (!channel) return;  // Ignore empty channel names
        
        // Remove from subscriptions set
        this.subscriptions.delete(channel);
        
        // Send unsubscribe message to server
        this.send({ type: 'unsubscribe', channel });
    }

    /**
     * Send message to WebSocket server
     * 
     * Converts data object to JSON and sends over WebSocket connection.
     * Only sends if connection is open.
     * 
     * @param {object} data Message data object (will be JSON-stringified)
     */
    send(data) {
        // Check if WebSocket is connected and ready
        if (this.isConnected && this.socket.readyState === WebSocket.OPEN) {
            // Convert data to JSON string and send
            this.socket.send(JSON.stringify(data));
        } else {
            // Connection not ready - log warning
            console.warn('WebSocket is not connected');
        }
    }

    /**
     * Append authentication token to WebSocket URL
     * 
     * Adds token as query parameter for server-side authentication during handshake.
     * Format: ws://host:port/?token=xyz
     * 
     * @param {string} baseUrl Base WebSocket URL
     * @param {string} token Authentication token
     * @return {string} URL with token appended
     */
    appendToken(baseUrl, token) {
        // If no token provided, return URL unchanged
        if (!token) return baseUrl;
        
        try {
            // Use URL API for proper query string handling
            const u = new URL(baseUrl);
            u.searchParams.set('token', token);  // Add/update token parameter
            return u.toString();
        } catch (e) {
            // Fallback if URL API fails (older browsers)
            // Simple string concatenation with proper ? or & separator
            return baseUrl + (baseUrl.includes('?') ? '&' : '?') + 'token=' + encodeURIComponent(token);
        }
    }

    /**
     * Get authentication token from storage
     * 
     * Checks multiple sources in order of preference:
     * 1. window.WS_AUTH_TOKEN (server-side template injection)
     * 2. localStorage 'wsAuthToken' key
     * 
     * @return {string|null} Authentication token or null if not found
     */
    getAuthToken() {
        // Prefer in-memory global provided by server-side PHP (more secure, doesn't persist)
        if (window.WS_AUTH_TOKEN) return window.WS_AUTH_TOKEN;
        
        // Fallback to localStorage (persists across page reloads)
        return localStorage.getItem('wsAuthToken') || null;
    }

    /**
     * Handle job notification message
     * 
     * Triggered when server sends a new job posting notification.
     * Shows browser notification and triggers custom event listeners.
     * 
     * @param {object} data Notification data {title, company, jobId, etc.}
     */
    handleJobNotification(data) {
        // Trigger custom event for listeners (e.g., update UI)
        this.trigger('jobNotification', data);
        
        // Show browser notification to user (if permission granted)
        this.showNotification(
            'New Job Posted!',                        // Notification title
            `${data.title} at ${data.company}`,      // Notification body
            '/pages/jobs.php?id=' + data.jobId       // URL to open on click
        );
    }

    /**
     * Handle application status update message
     * 
     * Triggered when employer changes status of user's job application
     * (e.g., Pending → Reviewed → Interview → Accepted/Rejected)
     * 
     * @param {object} data Application update data {status, jobTitle, etc.}
     */
    handleApplicationUpdate(data) {
        // Trigger custom event for listeners (e.g., refresh applications list)
        this.trigger('applicationUpdate', data);
        
        // Show browser notification to user
        this.showNotification(
            'Application Update',                     // Notification title
            `Your application status: ${data.status}`, // Status change message
            '/pages/applications.php'                 // URL to applications page
        );
    }

    /**
     * Show browser notification
     * 
     * Uses Web Notifications API to display native system notification.
     * Requires user to have granted notification permission.
     * 
     * @param {string} title Notification title
     * @param {string} body Notification body text
     * @param {string|null} url Optional URL to open when notification is clicked
     */
    showNotification(title, body, url = null) {
        // Check if browser supports notifications and permission is granted
        if ('Notification' in window && Notification.permission === 'granted') {
            // Create new browser notification
            const notification = new Notification(title, {
                body: body,                      // Main notification text
                icon: '/assets/logo.png',        // Icon image
                badge: '/assets/badge.png'       // Badge image (Android)
            });
            
            // If URL provided, open it when notification is clicked
            if (url) {
                notification.onclick = function() {
                    window.open(url, '_blank');  // Open URL in new tab
                    notification.close();         // Close notification
                };
            }
        }
    }

    /**
     * Request notification permission from user
     * 
     * Prompts browser notification permission dialog.
     * Should be called in response to user action (button click) for best UX.
     */
    requestNotificationPermission() {
        // Check if browser supports notifications and permission not yet decided
        if ('Notification' in window && Notification.permission === 'default') {
            // Request permission from user (shows browser prompt)
            Notification.requestPermission().then(permission => {
                console.log('Notification permission:', permission);
                // permission can be: 'granted', 'denied', or 'default'
            });
        }
    }

    /**
     * Get user ID from storage
     * 
     * Retrieves user's database ID from multiple possible sources.
     * Used for registering WebSocket connection with server.
     * 
     * @return {number|null} User ID or null if not logged in
     */
    getUserId() {
        // Try to get from localStorage (persists across sessions)
        // Or from a global variable set by PHP (window.userId)
        return localStorage.getItem('userId') || window.userId || null;
    }

    /**
     * Add event listener
     * 
     * Registers callback function to be called when specific event is triggered.
     * Supports multiple listeners per event.
     * 
     * @param {string} event Event name (e.g., 'connected', 'jobNotification')
     * @param {function} callback Function to call when event triggers
     */
    on(event, callback) {
        // Initialize listeners array for this event if it doesn't exist
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        
        // Add callback to listeners array
        this.listeners[event].push(callback);
    }

    /**
     * Trigger event and call all registered listeners
     * 
     * Executes all callback functions registered for specified event.
     * Passes data parameter to each callback.
     * 
     * @param {string} event Event name to trigger
     * @param {any} data Data to pass to callback functions
     */
    trigger(event, data) {
        // Check if any listeners are registered for this event
        if (this.listeners[event]) {
            // Call each registered callback with the data
            this.listeners[event].forEach(callback => callback(data));
        }
    }

    /**
     * Send ping message to keep connection alive
     * 
     * Keepalive pings prevent idle connection timeout and verify connection health.
     * Server responds with 'pong' message.
     */
    ping() {
        this.send({ type: 'ping' });
    }

    /**
     * Start automatic keep-alive ping
     * 
     * Sends periodic ping messages to prevent connection timeout.
     * Default interval is 30 seconds.
     * 
     * @param {number} interval Milliseconds between pings (default: 30000 = 30 seconds)
     */
    startKeepAlive(interval = 30000) {
        // Set up interval to send ping if connected
        setInterval(() => {
            if (this.isConnected) {
                this.ping();  // Send keepalive ping
            }
        }, interval);
    }

    /**
     * Disconnect from WebSocket server
     * 
     * Closes WebSocket connection gracefully.
     * Triggers onclose event handler.
     */
    disconnect() {
        if (this.socket) {
            this.socket.close();  // Close WebSocket connection
        }
    }
}

// === Auto-initialization and global event handlers ===

// Global WebSocket client instance
let wsClient = null;

// Initialize WebSocket when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Create and connect WebSocket client instance
    wsClient = new CareerHubWebSocket('ws://localhost:8080');
    wsClient.connect();                      // Establish connection
    wsClient.startKeepAlive();               // Start keepalive pings (30s interval)
    wsClient.requestNotificationPermission(); // Request browser notification permission
    
    // Example dynamic channel subscriptions (uncomment/customize as needed)
    // wsClient.subscribe('jobs');  // Subscribe to all job notifications
    // User-specific channel subscription (if logged in)
    // if (wsClient.getUserId()) wsClient.subscribe('notifications:user:' + wsClient.getUserId());
    
    // === Event handlers for different notification types ===
    
    // Listen for new job posting notifications
    wsClient.on('jobNotification', function(data) {
        console.log('New job notification:', data);
        
        // If user is on jobs page, refresh to show new job
        if (window.location.pathname.includes('jobs.php')) {
            // Option 1: Full page reload
            location.reload();
            
            // Option 2 (better): Dynamically add job card without reload
            // addJobCard(data);
        }
    });
    
    // Listen for application status updates
    wsClient.on('applicationUpdate', function(data) {
        console.log('Application update:', data);
        
        // If user is on applications page, refresh to show updated status
        if (window.location.pathname.includes('applications.php')) {
            location.reload();
        }
    });
    
    // Handle connection established
    wsClient.on('connected', function() {
        console.log('Connected to real-time notifications');
        showConnectionStatus('connected');  // Show green indicator
    });
    
    // Handle connection lost
    wsClient.on('disconnected', function() {
        console.log('Disconnected from real-time notifications');
        showConnectionStatus('disconnected');  // Show red indicator
    });
});

/**
 * Show visual connection status indicator
 * 
 * Displays a small colored badge in top-right corner showing WebSocket connection status.
 * - Green "Connected" badge (fades after 3 seconds)
 * - Red "Disconnected" badge (stays visible)
 * 
 * @param {string} status Connection status: 'connected' or 'disconnected'
 */
function showConnectionStatus(status) {
    // Try to find existing indicator element
    let indicator = document.getElementById('ws-status-indicator');
    
    // If indicator doesn't exist, create it
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'ws-status-indicator';
        
        // Style as fixed position badge in top-right corner
        indicator.style.cssText = `
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 10000;
            transition: opacity 0.3s;
        `;
        
        // Add to page
        document.body.appendChild(indicator);
    }
    
    if (status === 'connected') {
        // Show green connected badge
        indicator.textContent = '● Connected';
        indicator.style.backgroundColor = '#4caf50';  // Green
        indicator.style.color = 'white';
        
        // Hide badge after 3 seconds (user knows connection is established)
        setTimeout(() => {
            indicator.style.opacity = '0';
        }, 3000);
    } else {
        // Show red disconnected badge (stays visible)
        indicator.textContent = '● Disconnected';
        indicator.style.backgroundColor = '#f44336';  // Red
        indicator.style.color = 'white';
        indicator.style.opacity = '1';  // Ensure visible
    }
}

// Export class for use in other scripts or modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CareerHubWebSocket;
}

