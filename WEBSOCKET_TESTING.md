# WebSocket Testing Guide

## Quick Start

### 1. Start the WebSocket Server

**Option A: Using the batch file (Windows)**
```powershell
cd C:\wamp64\www\career_hub
.\start_websocket.bat
```

**Option B: Using PHP directly**
```powershell
cd C:\wamp64\www\career_hub
php websocket_server.php
```

You should see:
```
===========================================
Career Hub WebSocket Server
===========================================

WebSocket Server started on 0.0.0.0:8080
Waiting for connections...
```

### 2. Open the Test Page

While the server is running, open in your browser:
```
http://localhost/career_hub/test_websocket.html
```

**Important**: You must be logged in as an admin or student in another tab first (the test page fetches an auth token from your session).

### 3. Test Connection

1. Click **"Connect to WebSocket"** button
2. Watch the log panel for:
   - ✓ Auth token obtained
   - ✓ WebSocket connection established
   - Connection status changes to 🟢 Connected

### 4. Test Features

**Channel Subscriptions:**
- Click "Subscribe: jobs" → should see confirmation in log
- Click "Subscribe: notifications"
- Click "Unsubscribe: jobs" → should see unsubscribe confirmation

**Send Messages:**
- Click "Send Ping" → should receive pong response
- Click "Send Test Message" → sends JSON with timestamp

**Custom Messages:**
- Enter JSON in the textarea, e.g.:
  ```json
  {"type":"message","content":"hello from client"}
  ```
- Click "Send Raw JSON"

### 5. Test Notification Queue

Open a new terminal (keep the WebSocket server running) and send a notification via API:

```powershell
# You need to be logged in as admin in your browser first, then use session cookie
# Or use curl/Invoke-RestMethod with your PHPSESSID cookie

# Example using PowerShell (replace YOUR_SESSION_ID):
$headers = @{
    "Cookie" = "PHPSESSID=YOUR_SESSION_ID"
    "Content-Type" = "application/x-www-form-urlencoded"
}
$body = @{
    csrf_token = "YOUR_CSRF_TOKEN"
    message = "Test notification from API"
    userId = 1
    channel = "jobs"
}
Invoke-RestMethod -Uri "http://localhost/career_hub/api/v1/notify.php" -Method POST -Headers $headers -Body $body
```

Or test directly in the browser console on a logged-in page:
```javascript
fetch('/career_hub/api/v1/notify.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
        csrf_token: document.querySelector('[name=csrf_token]')?.value || '',
        message: 'Test notification',
        channel: 'jobs'
    })
}).then(r => r.json()).then(console.log);
```

The WebSocket client should receive the notification in the message log.

## Expected Server Console Output

When clients connect:
```
New connection from 127.0.0.1:xxxxx
Handshake successful
Client authenticated: user_id=1
```

When messages are sent:
```
Received: {"type":"subscribe","channel":"jobs"}
Client subscribed to channel: jobs
```

When notification queue is processed:
```
Processing notification queue...
Broadcasting to channel: jobs
Sent to 1 client(s)
```

## Troubleshooting

### Connection fails with "Failed to get auth token"
- Ensure you're logged in as admin or student in the same browser
- Check `/api/auth/ws_token.php` is accessible
- Verify session is active

### Connection closes immediately
- Check server console for errors
- Verify port 8080 is not blocked by firewall
- Ensure WebSocket server is running

### No notifications received
- Verify the notification was queued: check `cache/notifications.json`
- Ensure you're subscribed to the correct channel
- Check server is processing the queue (runs every 5 seconds)

### Rate limiting triggered
- Server limits 10 messages per 60 seconds per client
- Wait 60 seconds or restart server to reset

## Monitoring

**Server logs**: Watch the WebSocket server console for real-time activity

**Cache files**:
- `cache/ws_tokens/` — Auth tokens (15 min expiry)
- `cache/notifications.json` — Notification queue

**Client stats**: The test page shows:
- Messages Received counter
- Messages Sent counter
- Real-time message log with timestamps

## Production Checklist

- [ ] WebSocket server runs as a background service (not just console)
- [ ] Configure firewall to allow port 8080
- [ ] Use wss:// (secure WebSocket) with SSL certificate
- [ ] Monitor server uptime and auto-restart on crash
- [ ] Implement logging to files (not just console)
- [ ] Consider using a process manager (PM2, supervisord)

## Advanced Testing

**Stress test** (multiple clients):
- Open test_websocket.html in multiple browser tabs
- Each should connect independently
- Send messages from different tabs
- Verify all receive channel broadcasts

**Token expiration** (15 min default):
- Connect and wait 16 minutes
- Try sending a message → should be rejected or disconnected

**Reconnection**:
- Disconnect client
- Stop server
- Restart server
- Reconnect client → should get new token and reconnect

---

For integration with your app, use the client script in `js/websocket-client.js` and follow the patterns in the test page.
