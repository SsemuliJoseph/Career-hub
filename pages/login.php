<?php
/**
 * pages/login.php - User Login Page
 * Teaching: This page handles user authentication (login).
 * - We include session.php (starts/resumes session) and db.php (database connection).
 * - The script checks $_SERVER['REQUEST_METHOD'] to determine if the form was submitted.
 * - It validates inputs, queries the database for matching credentials, and verifies passwords.
 * - On success, it creates a $_SESSION entry and redirects based on user role.
 * 
 * Key PHP concepts:
 * - $_SERVER['REQUEST_METHOD']: superglobal that holds the HTTP method (GET, POST, etc.).
 * - password_verify($plaintext, $hash): built-in function to check if a password matches a hash.
 * - mysqli_prepare(): prepares a SQL statement to prevent SQL injection.
 * - mysqli_stmt_bind_param(): binds variables to placeholders in the prepared statement.
 * - header('Location: ...'): sends an HTTP redirect to the browser.
 * - exit: stops script execution (important after redirects).
 */
// Include session management and database connection
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Initialize error message and timeout message variables
$error = '';
$timeout_message = '';

// Teaching: Check if the user was redirected due to session timeout.
// We read from $_GET, which contains URL query parameters (e.g., ?timeout=1).
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $timeout_message = "Your session has expired due to inactivity. Please login again.";
}

// Teaching: Handle login form submission (POST request).
// $_SERVER['REQUEST_METHOD'] tells us the HTTP method used to access this page.
// We only process login logic when the method is POST (form submission).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Teaching: Retrieve and sanitize form inputs from $_POST.
    // trim() removes leading/trailing whitespace to prevent accidental spaces.
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Teaching: Validate that all required fields are filled.
    // empty() returns true if a variable is '', 0, null, false, or an empty array.
    if (empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        // Teaching: Try admin login first.
        // mysqli_prepare() creates a prepared statement to safely execute queries.
        // The '?' placeholder will be replaced with the bound parameter.
        $stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email); // Bind $email as a string ('s')
        mysqli_stmt_execute($stmt); // Execute the prepared statement
        $result = mysqli_stmt_get_result($stmt); // Get the result set
        $admin = mysqli_fetch_assoc($result); // Fetch one row as an associative array

        // Teaching: Check if admin found and password matches.
        // password_verify() compares the plain-text password with the hashed one.
        // We also have a fallback for plain-text passwords (not recommended in production!).
        if ($admin && (password_verify($password, $admin['password']) || $password === $admin['password'])) {
            // Teaching: Create an admin session.
            // $_SESSION is a superglobal array that persists data across page requests.
            $_SESSION['user'] = [
                'id' => $admin['id'],
                'email' => $admin['email'],
                'role' => 'admin'
            ];
            
            // Teaching: Redirect to the requested page (if stored in session) or default.
            // The ?? operator provides a default value if the key doesn't exist.
            $redirect = $_SESSION['redirect_after_login'] ?? 'admin.php';
            unset($_SESSION['redirect_after_login']); // Clear the redirect session variable
            header("Location: " . $redirect); // Send HTTP redirect
            exit; // Stop script execution
        }

        // Teaching: If not admin, try normal user login.
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? AND role = ?");
        mysqli_stmt_bind_param($stmt, "ss", $email, $role); // Bind two strings ('ss')
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        // Teaching: Check if user found and password matches (hashed).
        if ($user && password_verify($password, $user['password'])) {
            // Teaching: Create user session with profile data.
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'] ?? $user['email'], // Use name or email as fallback
                'email' => $user['email'],
                'role' => $user['role'],
                'profile_image' => $user['profile_image'] ?? '/uploads/profile/default-avatar.png'
            ];

            // Teaching: Redirect based on role or to a previously requested page.
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirect);
            } else {
                // Default redirects based on role
                if ($role === 'student') {
                    header("Location: student.php");
                } else {
                    header("Location: employer.php");
                }
            }
            exit;
        } else {
            $error = "Invalid email or password."; // Login failed
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"> <!-- Character encoding -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive viewport -->
  <title>Login</title>
  <link rel="stylesheet" href="../css/global.css"> <!-- Global styles -->
  <link rel="stylesheet" href="../css/responsive.css"> <!-- Responsive styles -->
  <style>
    /* Timeout message styling (yellow warning banner) */
    .timeout-message {
      background-color: #fff3cd; /* Light yellow background */
      color: #856404; /* Dark yellow text */
      padding: 12px 20px;
      border-radius: 6px;
      margin-bottom: 15px;
      border-left: 4px solid #ffc107; /* Amber left border */
      text-align: center;
    }
  </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?> <!-- Include navigation bar -->
<main class="center-container">
  <div class="card-form login-form"> <!-- Login form card -->
    <h2>Welcome Back!</h2>
    <p>Sign in to continue your journey</p>
    <!-- Display timeout message if present -->
    <?php if ($timeout_message): ?>
      <div class="timeout-message"><?= htmlspecialchars($timeout_message) ?></div>
    <?php endif; ?>
    <!-- Display error message if present -->
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <!-- Login form (submits to same page) -->
    <form method="POST" action="">
      <input type="email" name="email" placeholder="Email" required> <!-- Email input -->
      <input type="password" name="password" placeholder="Password" required> <!-- Password input -->
      <!-- Role selection dropdown -->
      <select name="role" required>
        <option value="">Select Role</option>
        <option value="student">Student</option>
        <option value="employer">Employer</option>
      </select>
      <button type="submit">Login</button> <!-- Submit button -->
    </form>
    <!-- Link to signup page -->
    <p>Don't have an account? <a href="signup.php">Sign up</a></p>
  </div>
</main>
    

<!-- Footer section (hardcoded) -->
<footer class="footer">
<div class="page-content-wrapper footer-content">
<p>© 2025 CaReeR CoNNect HuB. All rights reserved.</p>
<div class="footer-social">
 <span> For more info </span>
  <!-- Instagram link with SVG icon -->
  <a href="https://www.instagram.com/YOUR_USERNAME" target="_blank" aria-label="Instagram" class="social-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
      <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
      <circle cx="17.5" cy="6.5" r="1.5"/>

    </svg>
  </a>

  <!-- GitHub link with SVG icon -->
  <a href="https://github.com/YOUR_USERNAME" target="_blank" aria-label="GitHub" class="social-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M9 19c-4.5 1.5-4.5-2.5-6-3m12 5v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 18 2.77 5.07 5.07 0 0 0 17.91 0S16.73.35 14 2.48a13.38 13.38 0 0 0-8 0C3.27.35 2.09 0 2.09 0A5.07 5.07 0 0 0 2 2.77 5.44 5.44 0 0 0 .5 8.5c0 5.42 3.3 6.61 6.44 7a3.37 3.37 0 0 0-.94 2.61V21"/>
    </svg>
  </a>

  <!-- LinkedIn link with SVG icon -->
  <a href="https://www.linkedin.com/in/YOUR_USERNAME" target="_blank" aria-label="LinkedIn" class="social-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2h-1v9h-4v-9h-4v9H3V9h4v1.2a4.6 4.6 0 0 1 4-2.2z"/>
      <rect x="2" y="9" width="4" height="12"/>
      <circle cx="4" cy="4" r="2"/>
    </svg>
  </a>
</div>

</div>
</footer>

<script> // Theme toggle functionality

 // Get theme toggle button and body element
 const toggle = document.getElementById('theme-toggle');
  const body = document.body;
  // Load saved theme from localStorage
  const savedTheme = localStorage.getItem('theme');
  // Apply light theme if saved
  if (savedTheme === 'light') {
    body.classList.add('light-theme');
    toggle.textContent = '🌙 Dark Mode';
  }
  // Toggle theme on button click
  toggle.addEventListener('click', () => {
    const isLight = body.classList.toggle('light-theme'); // Toggle class
    toggle.textContent = isLight ? '🌙 Dark Mode' : '☀️ Light Mode'; // Update button text
    localStorage.setItem('theme', isLight ? 'light' : 'dark'); // Save preference
  });

</script>

</body>

</html>