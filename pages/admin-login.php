<?php
// admin-login.php
// Teaching: Lightweight admin authentication page. Students: notice we
// - check an admin secret key (extra layer)
// - validate credentials using prepared statements
// - auto-upgrade legacy plaintext passwords to hashed passwords

// Include session management and DB connection. These bring in secure
// session cookie settings and a $conn mysqli connection respectively.
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// If already logged in as admin, redirect to dashboard immediately.
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    header('Location: admin.php');
    exit;
}

// Error message shown to user (kept minimal to avoid leaking info)
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use trim() and null coalescing operator to avoid undefined index notices
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $secret_key = trim($_POST['secret_key'] ?? '');

    // IMPORTANT: This static secret is for demo/dev only. In real apps move
    // secrets to environment variables or a secrets manager and never commit them.
    $ADMIN_SECRET = 'CCH_ADMIN_2025';

    // First validate the secret key to provide an early rejection for casual access
    if ($secret_key !== $ADMIN_SECRET) {
        $error = 'Invalid secret key';
    } else {
        // Prepare statement to find admin by email OR username. Prepared
        // statements protect against SQL injection even if the input is tainted.
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? OR username = ?");
        $stmt->bind_param('ss', $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin) {
            // Validate password. Prefer password_verify() for hashed values.
            $passwordMatch = false;

            if (password_verify($password, $admin['password'])) {
                // Secure path: hashed password matched
                $passwordMatch = true;
            } elseif ($password === $admin['password']) {
                // Legacy fallback: plaintext password matched. Immediately upgrade.
                $passwordMatch = true;
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $updateStmt->bind_param('si', $hashedPassword, $admin['id']);
                $updateStmt->execute();
                $updateStmt->close();
            }

            if ($passwordMatch) {
                // Minimal session payload: store only what's necessary (don't store secrets)
                $_SESSION['user'] = [
                    'id' => $admin['id'],
                    'name' => $admin['username'] ?? $admin['email'] ?? 'Admin',
                    'email' => $admin['email'] ?? $admin['username'],
                    'role' => 'admin',
                    'profile_image' => '/uploads/profile/admin-avatar.png'
                ];
                header('Location: admin.php');
                exit;
            } else {
                // Be generic with error messages to avoid username enumeration
                $error = 'Invalid credentials';
            }
        } else {
            $error = 'Invalid credentials';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Character encoding for UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive viewport -->
    <title>Admin Access | Career Connect Hub</title> <!-- Browser tab title -->
    <link rel="stylesheet" href="../css/global.css"> <!-- Global site styles -->
    <link rel="stylesheet" href="../css/responsive.css"> <!-- Responsive layout styles -->
    <style> /* Page-specific styles */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .admin-login-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        /* Header area (icon, title, subtitle) */
        .admin-header {
            text-align: center; /* Center all header content */
            margin-bottom: 30px; /* Space below header */
        }
        /* Lock icon styling */
        .admin-icon {
            font-size: 4rem; /* Large icon */
            margin-bottom: 15px;
        }
        /* Main title styling */
        .admin-title {
            font-size: 1.8rem; /* Large title text */
            color: #333; /* Dark gray */
            margin-bottom: 5px;
        }
        /* Subtitle text */
        .admin-subtitle {
            color: #666; /* Medium gray */
            font-size: 0.9rem; /* Smaller text */
        }
        /* Form field wrapper */
        .form-group {
            margin-bottom: 20px; /* Space between fields */
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        /* Submit button styling */
        .admin-btn {
            width: 100%; /* Full-width button */
            padding: 14px; /* Button height */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Purple gradient */
            color: white; /* White text */
            border: none; /* No border */
            border-radius: 8px; /* Rounded corners */
            font-size: 1.1rem; /* Slightly larger text */
            font-weight: 600; /* Semi-bold */
            cursor: pointer; /* Pointer cursor on hover */
            transition: transform 0.2s; /* Smooth hover animation */
        }
        /* Button hover effect */
        .admin-btn:hover {
            transform: translateY(-2px); /* Lift button slightly on hover */
        }
        /* Back to main site link container */
        .back-link {
            text-align: center; /* Center link */
            margin-top: 20px; /* Space above link */
        }
        /* Back link styling */
        .back-link a {
            color: white; /* White text (contrasts with gradient) */
            text-decoration: none; /* No underline */
            font-size: 0.9rem; /* Smaller text */
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-card">
            <!-- Header section with icon, title, and subtitle -->
            <div class="admin-header">
                <div class="admin-icon">🔐</div> <!-- Lock emoji icon -->
                <h1 class="admin-title">Admin Access</h1>
                <p class="admin-subtitle">Authorized Personnel Only</p>
            </div>

            <!-- Display error message if set (XSS-safe with htmlspecialchars) -->
            <?php if ($error): ?>
                <div class="error-message">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Login form (submits to same page via POST) -->
            <form method="POST" action="">
                <!-- Email/Username field -->
                <div class="form-group">
                    <label for="email">Admin Email/Username</label>
                    <input type="text" id="email" name="email" required autocomplete="off" placeholder="Enter email or username">
                </div>

                <!-- Password field (masked input) -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <!-- Secret key field (extra security layer, masked) -->
                <div class="form-group">
                    <label for="secret_key">Secret Key</label>
                    <input type="password" id="secret_key" name="secret_key" required autocomplete="off">
                </div>

                <!-- Submit button -->
                <button type="submit" class="admin-btn">🔓 Access Dashboard</button>
            </form>
        </div>

        <!-- Link to return to main site -->
        <div class="back-link">
            <a href="../index.php">← Return to Main Site</a>
        </div>
    </div>

    <script>
        // Prevent right-click context menu (basic protection, easily bypassed)
        document.addEventListener('contextmenu', e => e.preventDefault());
        
        // Disable common dev tools keyboard shortcuts (F12, Ctrl+Shift+I, etc.)
        // Note: This is NOT real security - just discourages casual inspection
        document.addEventListener('keydown', e => {
            if (e.key === 'F12' ||  // Dev tools
                (e.ctrlKey && e.shiftKey && e.key === 'I') || // Inspect element
                (e.ctrlKey && e.shiftKey && e.key === 'J') || // Console
                (e.ctrlKey && e.key === 'U')) { // View source
                e.preventDefault(); // Block the shortcut
            }
        });
    </script>
</body>
</html>
