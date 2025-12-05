<?php
/**
 * pages/signup.php - User Registration
 * Teaching: This page handles new user sign-ups (students and employers).
 * - We include session.php (to start/resume the session) and db.php (for database access).
 * - The script checks if the request method is POST (form submission).
 * - It validates inputs, checks for duplicate emails, and hashes passwords before storing.
 * Key PHP concepts:
 * - trim() removes whitespace from the start and end of a string.
 * - password_hash() creates a secure hash using the bcrypt algorithm (PASSWORD_BCRYPT).
 * - mysqli_prepare() prepares a SQL statement for safe execution (prevents SQL injection).
 */
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

$error = ''; // Variable to hold error messages
$success = ''; // Variable to hold success messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Teaching: Retrieve and sanitize user inputs from the $_POST superglobal.
    // trim() removes leading/trailing whitespace to prevent accidental spaces.
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Teaching: Validate that all required fields are filled.
    // empty() returns true if a variable is an empty string, 0, null, or false.
    if (empty($email) || empty($password) || empty($role)) {
        $error = "All required fields must be filled.";
    } else {
        // Teaching: Check for duplicate email in the students table.
        // We use a prepared statement (mysqli_prepare) to safely query the database.
        $check = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email); // Bind $email as a string ('s')
        mysqli_stmt_execute($check); // Execute the prepared statement
        mysqli_stmt_store_result($check); // Store the result to check row count

        if (mysqli_stmt_num_rows($check) > 0) {
            // Teaching: If a row is found, the email is already registered.
            // We use JavaScript's alert() function to notify the user (not best UX, but simple).
            echo "<script>alert('Email already registered. Please use a different email or login.');</script>";
        } else {
            // Teaching: Hash the password using PHP's password_hash() function.
            // PASSWORD_BCRYPT is a secure hashing algorithm that includes salting.
            // Never store plain-text passwords in a database!
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            // Teaching: Insert the new user into the users table.
            // The placeholders (?) are replaced with bound parameters for security.
            $stmt = mysqli_prepare($conn, "INSERT INTO users (email, password, role, createdAt, updatedAt) VALUES (?, ?, ?, NOW(), NOW())");
            mysqli_stmt_bind_param($stmt, "sss", $email, $hashed, $role); // Bind three strings
            mysqli_stmt_execute($stmt); // Execute the INSERT

            // Teaching: If the role is 'student', also insert into the students table.
            // This demonstrates a multi-table insert pattern for role-based data.
            if ($role === 'student') {
                $stmt2 = mysqli_prepare($conn, "INSERT INTO students (email, password, name, createdAt, updatedAt) VALUES (?, ?, ?, NOW(), NOW())");
                $name = $username; // Use username as name
                mysqli_stmt_bind_param($stmt2, "sss", $email, $hashed, $name);
                mysqli_stmt_execute($stmt2);
            }

            $success = "Registration successful! You can now log in.";
        }
        mysqli_stmt_close($check); // Always close statements to free resources
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<main class="center-container">
  <div class="card-form login-form">
    <h2>Create Your Account</h2>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="POST" action="">
      <input type="text" name="username" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <select name="role" required>
        <option value="">Select Role</option>
        <option value="student">Student</option>
        <option value="employer">Employer</option>
      </select>
      <button type="submit">Sign Up</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
  </div>
</main>

<footer class="footer">
<div class="page-content-wrapper footer-content">
<p>© 2025 CaReeR CoNNect HuB. All rights reserved.</p>
<div class="footer-social">
 <span> For more info </span>
  <!-- Instagram -->
  <a href="https://www.instagram.com/YOUR_USERNAME" target="_blank" aria-label="Instagram" class="social-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
      <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
      <circle cx="17.5" cy="6.5" r="1.5"/>
    </svg>
  </a>

  <!-- GitHub -->
  <a href="https://github.com/YOUR_USERNAME" target="_blank" aria-label="GitHub" class="social-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M9 19c-4.5 1.5-4.5-2.5-6-3m12 5v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 18 2.77 5.07 5.07 0 0 0 17.91 0S16.73.35 14 2.48a13.38 13.38 0 0 0-8 0C3.27.35 2.09 0 2.09 0A5.07 5.07 0 0 0 2 2.77 5.44 5.44 0 0 0 .5 8.5c0 5.42 3.3 6.61 6.44 7a3.37 3.37 0 0 0-.94 2.61V21"/>
    </svg>
  </a>

  <!-- LinkedIn -->
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
  <script>
   
	// Theme toggle
  const toggle = document.getElementById('theme-toggle');
  const body = document.body;
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'light') {
    body.classList.add('light-theme');
    toggle.textContent = '🌙 Dark Mode';
  }
  toggle.addEventListener('click', () => {
    const isLight = body.classList.toggle('light-theme');
    toggle.textContent = isLight ? '🌙 Dark Mode' : '☀️ Light Mode';
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
  });
  </script>
</body>
</html>
