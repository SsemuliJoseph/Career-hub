<?php
/**
 * Notification Center Component
 * Include this file in your pages to add notification history panel
 * Usage: <?php include_once __DIR__ . '/../includes/notification_center.php'; ?>
 */
?>
<style>
.notification-center {
    position: fixed;
    top: 70px;
    right: -400px;
    width: 380px;
    height: calc(100vh - 90px);
    background: white;
    box-shadow: -2px 0 10px rgba(0,0,0,0.1);
    transition: right 0.3s ease;
    z-index: 999;
    display: flex;
    flex-direction: column;
}
.notification-center.open { right: 0; }
.notification-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.notification-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}
.notif-close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
}
.notification-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}
.notification-item {
    background: #f9f9f9;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 8px;
    border-left: 4px solid #4CAF50;
    cursor: pointer;
    transition: all 0.2s;
}
.notification-item:hover {
    background: #f0f0f0;
    transform: translateX(-5px);
}
.notification-item.unread {
    background: #e3f2fd;
    border-left-color: #2196F3;
}
.notification-item.warning { border-left-color: #ff9800; }
.notification-item.error { border-left-color: #f44336; }
.notification-item.success { border-left-color: #4CAF50; }
.notification-item h4 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #333;
}
.notification-item p {
    margin: 0 0 8px 0;
    font-size: 13px;
    color: #666;
}
.notification-time {
    font-size: 11px;
    color: #999;
}
.notification-actions {
    padding: 15px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    gap: 10px;
}
.notification-actions button {
    flex: 1;
    padding: 8px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 13px;
}
.mark-read-btn {
    background: #4CAF50;
    color: white;
}
.clear-all-btn {
    background: #f44336;
    color: white;
}
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}
.notification-bell {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    font-size: 20px;
    color: #666;
}
.notification-bell:hover { color: #333; }
.notification-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: #f44336;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
}
.notification-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.3);
    z-index: 998;
    display: none;
}
.notification-overlay.show { display: block; }
</style>

<!-- Notification Bell Button (add to navbar) -->
<button class="notification-bell" onclick="toggleNotificationCenter()" title="Notifications">
    🔔
    <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
</button>

<!-- Notification Overlay -->
<div class="notification-overlay" id="notificationOverlay" onclick="closeNotificationCenter()"></div>

<!-- Notification Center Panel -->
<div class="notification-center" id="notificationCenter">
    <div class="notification-header">
        <h3>📬 Notifications</h3>
        <button class="notif-close-btn" onclick="closeNotificationCenter()">×</button>
    </div>
    
    <div class="notification-list" id="notificationList">
        <div class="empty-state">
            <p>No notifications yet</p>
        </div>
    </div>
    
    <div class="notification-actions">
        <button class="mark-read-btn" onclick="markAllAsRead()">Mark all read</button>
        <button class="clear-all-btn" onclick="clearAllNotifications()">Clear all</button>
    </div>
</div>

<script>
// Notification Center Manager
(function() {
    const MAX_NOTIFICATIONS = 50;
    let notifications = [];
    let unreadCount = 0;

    function loadNotifications() {
        const saved = localStorage.getItem('notification_history');
        if (saved) {
            try {
                notifications = JSON.parse(saved);
                updateUnreadCount();
                renderNotifications();
            } catch (e) {
                console.error('Failed to load notifications:', e);
            }
        }
    }

    function saveNotifications() {
        try {
            localStorage.setItem('notification_history', JSON.stringify(notifications));
        } catch (e) {
            console.error('Failed to save notifications:', e);
        }
    }

    function addNotification(data) {
        const notification = {
            id: Date.now(),
            title: data.title || 'Notification',
            message: data.message || '',
            type: data.type || 'info',
            timestamp: new Date().toISOString(),
            read: false
        };
        
        notifications.unshift(notification);
        
        if (notifications.length > MAX_NOTIFICATIONS) {
            notifications = notifications.slice(0, MAX_NOTIFICATIONS);
        }
        
        saveNotifications();
        updateUnreadCount();
        renderNotifications();
    }

    function renderNotifications() {
        const listEl = document.getElementById('notificationList');
        
        if (notifications.length === 0) {
            listEl.innerHTML = '<div class="empty-state"><p>No notifications yet</p></div>';
            return;
        }
        
        listEl.innerHTML = notifications.map(notif => {
            const date = new Date(notif.timestamp);
            const timeAgo = getTimeAgo(date);
            const unreadClass = notif.read ? '' : 'unread';
            const typeClass = notif.type || 'info';
            
            return `
                <div class="notification-item ${unreadClass} ${typeClass}" onclick="markAsRead(${notif.id})">
                    <h4>${escapeHtml(notif.title)}</h4>
                    <p>${escapeHtml(notif.message)}</p>
                    <div class="notification-time">${timeAgo}</div>
                </div>
            `;
        }).join('');
    }

    function updateUnreadCount() {
        unreadCount = notifications.filter(n => !n.read).length;
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    window.markAsRead = function(id) {
        const notif = notifications.find(n => n.id === id);
        if (notif && !notif.read) {
            notif.read = true;
            saveNotifications();
            updateUnreadCount();
            renderNotifications();
        }
    };

    window.markAllAsRead = function() {
        notifications.forEach(n => n.read = true);
        saveNotifications();
        updateUnreadCount();
        renderNotifications();
    };

    window.clearAllNotifications = function() {
        if (confirm('Clear all notifications? This cannot be undone.')) {
            notifications = [];
            saveNotifications();
            updateUnreadCount();
            renderNotifications();
        }
    };

    window.toggleNotificationCenter = function() {
        const center = document.getElementById('notificationCenter');
        const overlay = document.getElementById('notificationOverlay');
        
        if (center.classList.contains('open')) {
            closeNotificationCenter();
        } else {
            center.classList.add('open');
            overlay.classList.add('show');
        }
    };

    window.closeNotificationCenter = function() {
        const center = document.getElementById('notificationCenter');
        const overlay = document.getElementById('notificationOverlay');
        center.classList.remove('open');
        overlay.classList.remove('show');
    };

    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
        if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';
        
        return date.toLocaleDateString();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize
    loadNotifications();

    // Expose API
    window.NotificationCenter = {
        add: addNotification,
        markAsRead: markAsRead,
        markAllAsRead: markAllAsRead,
        clear: clearAllNotifications
    };
})();
</script>
