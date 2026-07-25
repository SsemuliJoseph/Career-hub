<?php
/**
 * upload_helpers.php
 * ------------------
 * Profile and file upload helpers with transactional persistence.
 * Teaching focus:
 * - Validate and sanitize all file metadata before storing on disk.
 * - Use move_uploaded_file() to avoid accepting attacker-controlled paths.
 * - Use DB transactions to keep multiple table updates atomic.
 */

session_start();
require_once __DIR__ . '/db.php'; // mysqli connection ($conn)

// Ensure upload directories exist with safe permissions
$uploadDirs = [
    __DIR__ . '/../uploads/profile',
    __DIR__ . '/../uploads/cv'
];

foreach ($uploadDirs as $dir) {
    if (!file_exists($dir)) {
        // 0755 is a reasonable default for web-accessible upload folders
        mkdir($dir, 0755, true);
    }
}

/**
 * Validate and upload a single file
 * Security notes:
 * - We validate file extension and size here but do NOT trust extension alone.
 *   For stronger security, also validate MIME type and, for images, consider
 *   re-encoding/resizing to strip harmful metadata.
 * - Filenames are generated server-side to avoid conflicts and path traversal.
 *
 * @param array $file - element from $_FILES
 * @param string $type - 'profile' or 'cv'
 * @param int $userId - User ID used in filename
 * @return string|null - Public path (e.g. '/uploads/profile/..') on success, null on failure
 */
function uploadFile($file, $type, $userId) {
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedTypes = [
        'profile' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'cv' => ['pdf', 'doc', 'docx']
    ];

    $maxSizes = [
        'profile' => 5 * 1024 * 1024, // 5MB
        'cv' => 10 * 1024 * 1024 // 10MB
    ];

    // Validate file size
    if ($file['size'] > $maxSizes[$type]) {
        return null;
    }

    // Validate file extension (not a perfect check, but useful)
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes[$type])) {
        return null;
    }

    // Generate a server-controlled filename to avoid collisions and traversal
    $filename = $type . '-' . $userId . '-' . time() . '.' . $ext;
    $targetPath = __DIR__ . '/../uploads/' . $type . '/' . $filename;

    // move_uploaded_file is safer than copy() because it verifies the upload origin
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Return a web-accessible path (relative to app root)
        return '/uploads/' . $type . '/' . $filename;
    }

    return null;
}

/**
 * Handle complete profile update with persistent storage
 * - Validates input, handles uploads, updates several DB tables inside a transaction
 * - Returns an array with 'success' or 'error' keys to standardize responses
 */
function handleProfileUpdate($userId) {
    global $conn;
    
    // Sanitize and validate inputs coming from POST
    $fullName = trim($_POST['fullName'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $education = trim($_POST['education'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    
    if (!$fullName || !$email) {
        return ['error' => 'Name and email are required'];
    }

    // Current stored assets (keep defaults when none exist)
    $currentProfileImage = $_SESSION['user']['profile_image'] ?? '/uploads/profile/default-avatar.png';
    $currentCvFile = $_SESSION['user']['cv'] ?? null;

    // Handle profile picture upload (if provided)
    if (!empty($_FILES['profilePic']['tmp_name'])) {
        $uploadedImage = uploadFile($_FILES['profilePic'], 'profile', $userId);
        if ($uploadedImage) {
            $currentProfileImage = $uploadedImage;
        } else {
            return ['error' => 'Invalid profile image. Use JPG, PNG, or GIF (max 5MB)'];
        }
    }

    // Handle CV upload (if provided)
    if (!empty($_FILES['cvFile']['tmp_name'])) {
        $uploadedCv = uploadFile($_FILES['cvFile'], 'cv', $userId);
        if ($uploadedCv) {
            $currentCvFile = $uploadedCv;
        } else {
            return ['error' => 'Invalid CV file. Use PDF, DOC, or DOCX (max 10MB)'];
        }
    }

    // Start transaction to ensure all related tables are updated atomically
    $conn->begin_transaction();
    
    try {
        // 1. Update users table (primary authentication table)
        $stmt1 = $conn->prepare(
            "UPDATE users SET name = ?, email = ?, phone = ?, profile_image = ?, updatedAt = NOW() WHERE id = ?"
        );
        $stmt1->bind_param('ssssi', $fullName, $email, $phone, $currentProfileImage, $userId);
        $stmt1->execute();
        $stmt1->close();

        // 2. Update or insert into students table (optional table used for student-specific info)
        $checkStudent = $conn->prepare("SELECT id FROM students WHERE id = ?");
        $checkStudent->bind_param('i', $userId);
        $checkStudent->execute();
        $studentExists = $checkStudent->get_result()->num_rows > 0;
        $checkStudent->close();

        if ($studentExists) {
            $stmt2 = $conn->prepare(
                "UPDATE students SET name = ?, full_name = ?, email = ?, contact = ?, skills = ?, profile_image = ?, profilePic = ?, cv_file = ?, updatedAt = NOW() WHERE id = ?"
            );
            $stmt2->bind_param('ssssssssi', $fullName, $fullName, $email, $phone, $skills, $currentProfileImage, $currentProfileImage, $currentCvFile, $userId);
            $stmt2->execute();
            $stmt2->close();
        }

        // 3. Update or insert into student_profiles table (indexed by email)
        $checkProfile = $conn->prepare("SELECT id FROM student_profiles WHERE email = ?");
        $checkProfile->bind_param('s', $email);
        $checkProfile->execute();
        $profileExists = $checkProfile->get_result()->num_rows > 0;
        $checkProfile->close();

        if ($profileExists) {
            $stmt3 = $conn->prepare(
                "UPDATE student_profiles SET fullName = ?, phone = ?, education = ?, skills = ?, profilePic = ?, cvFile = ? WHERE email = ?"
            );
            $stmt3->bind_param('sssssss', $fullName, $phone, $education, $skills, $currentProfileImage, $currentCvFile, $email);
        } else {
            $stmt3 = $conn->prepare(
                "INSERT INTO student_profiles (email, fullName, phone, education, skills, profilePic, cvFile) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt3->bind_param('sssssss', $email, $fullName, $phone, $education, $skills, $currentProfileImage, $currentCvFile);
        }
        $stmt3->execute();
        $stmt3->close();

        // Commit transaction
        $conn->commit();

        // Update session with fresh data so UI reflects the changes immediately
        $_SESSION['user'] = array_merge($_SESSION['user'], [
            'name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'profile_image' => $currentProfileImage,
            'cv' => $currentCvFile,
            'education' => $education,
            'skills' => $skills
        ]);

        return ['success' => true, 'message' => 'Profile updated successfully!'];
        
    } catch (Exception $e) {
        // Rollback on any error to keep DB consistent
        $conn->rollback();
        error_log("Profile update error: " . $e->getMessage());
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

// ============================================
// MAIN EXECUTION
// ============================================

// Require logged-in user
$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    http_response_code(401);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['error' => 'Unauthorized. Please log in.']);
    } else {
        header('Location: ../pages/login.php');
    }
    exit;
}

// Handle POST request for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = handleProfileUpdate($userId);
    
    // Return JSON for AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    // Redirect for form submissions with status stored in session
    if (isset($result['error'])) {
        $_SESSION['profile_error'] = $result['error'];
        header('Location: ../pages/student-profile.php?error=1');
    } else {
        $_SESSION['profile_success'] = $result['message'];
        header('Location: ../pages/student-profile.php?updated=1');
    }
    exit;
}
?>
