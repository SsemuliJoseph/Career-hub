<!-- Session Timeout Indicator Component -->
<?php
/**
 * Session Timeout Visual Indicator
 * TEACHING NOTES:
 * - Displays countdown timer for session expiration (bottom-left corner)
 * - Uses CSS animations (pulse) when session is about to expire
 * - JavaScript-driven (see related JS in page for functionality)
 * - Helps users stay aware of session timeout to avoid data loss
 */
?>
<!-- Add this to any page to show session timeout countdown -->

<style>
.session-indicator {
    position: fixed;
    bottom: 20px;
    left: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 999;
    display: none;
    opacity: 0.9;
    transition: all 0.3s ease;
}

.session-indicator:hover {
    opacity: 1;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}

.session-indicator.warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    animation: pulse 2s infinite;
}

.session-indicator i {
    margin-right: 8px;
}

@keyframes pulse {
    0%, 100% { opacity: 0.9; }
    50% { opacity: 1; }
}
</style>

<div class="session-indicator" id="sessionIndicator">
    <i>⏱️</i>
    <span id="sessionTimer">Session: --:--</span>
</div>

<script>
// Session Timeout Indicator
(function() {
    const SESSION_TIMEOUT = <?php echo defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 1800; ?>; // 30 minutes default
    const LAST_ACTIVITY = <?php echo $_SESSION['LAST_ACTIVITY'] ?? time(); ?>;
    const WARNING_THRESHOLD = 300; // Show warning when 5 minutes left
    
    const indicator = document.getElementById('sessionIndicator');
    const timer = document.getElementById('sessionTimer');
    
    // Show indicator
    indicator.style.display = 'block';
    
    function updateTimer() {
        const now = Math.floor(Date.now() / 1000);
        const elapsed = now - LAST_ACTIVITY;
        const remaining = SESSION_TIMEOUT - elapsed;
        
        if (remaining <= 0) {
            timer.textContent = 'Session Expired';
            indicator.classList.add('warning');
            // Redirect to login with timeout message
            setTimeout(() => {
                window.location.href = '/career_hub/pages/login.php?timeout=1';
            }, 2000);
            return;
        }
        
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        
        timer.textContent = `Session: ${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        // Add warning class when less than 5 minutes remaining
        if (remaining <= WARNING_THRESHOLD) {
            indicator.classList.add('warning');
        } else {
            indicator.classList.remove('warning');
        }
    }
    
    // Update every second
    updateTimer();
    setInterval(updateTimer, 1000);
    
    // Reset timer on user activity
    let activityTimeout;
    function resetActivityTimer() {
        clearTimeout(activityTimeout);
        activityTimeout = setTimeout(() => {
            // Ping server to keep session alive
            fetch('/career_hub/api/session/ping.php', {
                method: 'POST',
                credentials: 'same-origin'
            }).catch(() => {});
        }, 5000); // Wait 5 seconds after activity before pinging
    }
    
    // Track user activity
    ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, resetActivityTimer, { passive: true });
    });
})();
</script>
