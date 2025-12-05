<?php
// api/clear_settings_referrer.php
// Teaching: small utility endpoint used to clear a transient session value
// Purpose: demonstrates a minimal JSON API that manipulates session state.
// Key concepts shown:
// - session_start(): PHP session must be started before reading/writing $_SESSION
// - Returning JSON and appropriate HTTP status codes for AJAX/Fetch clients
// - Minimal endpoints are useful for one-off client actions (e.g., UI settings)
session_start();

// Clear the settings referrer from session
if (isset($_SESSION['settings_referrer'])) {
    unset($_SESSION['settings_referrer']);
}

http_response_code(200);
echo json_encode(['success' => true]);
