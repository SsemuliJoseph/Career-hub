<?php
// Path: pages/employer-profile.php
// This page allows an employer to view and edit their company profile.
// The comments below explain each PHP construct and best practices.

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

// Authorization: ensure the current session user has the employer or admin
// role. We read the user's role from the $_SESSION superglobal. Using the
// null coalescing operator (??) avoids warnings if the array key doesn't
// exist.
$role = $_SESSION['user']['role'] ?? '';
if ($role !== 'employer' && $role !== 'admin') {
    header('Location: /pages/home.php');
    exit;
}

// Common variables and messages used to show success/error to the user.
$userId = $_SESSION['user']['id'];
$successMsg = '';
$errorMsg = '';

// --- Fetch current employer profile ---
// We build a LEFT JOIN to combine user information with employer details.
// Note: the original LEFT JOIN used `users.id = employers.id` which is
// unusual — typically employers.user_id references users.id. Here we
// preserve the original query but be mindful of your DB schema.
$stmt = $conn->prepare("SELECT users.username, users.email, employers.company_name, employers.logo, employers.description, employers.website_url, employers.location, employers.industry FROM users LEFT JOIN employers ON users.id = employers.id WHERE users.id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

// --- Handle form submission ---
// PHP uses $_SERVER['REQUEST_METHOD'] to detect the HTTP method used by the
// request. For POSTed form data we read $_POST and $_FILES. We trim input to
// remove accidental whitespace.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = trim($_POST['company_name'] ?? '');
    $companyDescription = trim($_POST['company_description'] ?? '');
    $companyWebsite = trim($_POST['company_website'] ?? '');
    $companyAddress = trim($_POST['company_address'] ?? '');
    $companySize = trim($_POST['company_size'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $contactName = trim($_POST['contact_name'] ?? '');
    $contactEmail = trim($_POST['contact_email'] ?? '');
    $contactPhone = trim($_POST['contact_phone'] ?? '');
    
    // --- Handle company logo upload ---
    // $_FILES contains uploaded files. Check for UPLOAD_ERR_OK to confirm
    // the upload succeeded. Always validate file extension and size.
    $companyLogo = $profile['logo'] ?? '';
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/company_logos/';
        if (!is_dir($uploadDir)) {
            // mkdir with recursive=true ensures the directory is created.
            mkdir($uploadDir, 0755, true);
        }
        
        // Use pathinfo to safely extract extension and normalize it.
        $fileExt = strtolower(pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // Validate extension and max size (5MB here).
        if (in_array($fileExt, $allowedExts) && $_FILES['company_logo']['size'] <= 5000000) {
            $fileName = 'logo-' . $userId . '-' . time() . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;
            
            // move_uploaded_file securely moves the file from PHP's tmp
            // directory to your uploads directory.
            if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $targetPath)) {
                // Store the public path for the DB; note this assumes the
                // server serves /uploads from the repo root.
                $companyLogo = '/uploads/company_logos/' . $fileName;
                
                // Clean up old logo if present to avoid orphaned files.
                if (!empty($profile['logo']) && file_exists(__DIR__ . '/..' . $profile['logo'])) {
                    unlink(__DIR__ . '/..' . $profile['logo']);
                }
            }
        }
    }
    
    // --- Check if employer record exists ---
    // Note: the original code had two SELECTs with differing WHERE clauses.
    // Here we check properly using user_id.
    $stmt = $conn->prepare("SELECT id FROM employers WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $employerExists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    
    // Use transactions to ensure insert/update operations are atomic.
    $conn->begin_transaction();
    
    try {
        if ($employerExists) {
            // UPDATE: use parameterized query to prevent injection.
            $stmt = $conn->prepare("UPDATE employers SET company_name = ?, logo = ?, description = ?, website_url = ?, location = ?, industry = ?, updatedAt = NOW() WHERE user_id = ?");
            $stmt->bind_param('ssssssi', $companyName, $companyLogo, $companyDescription, $companyWebsite, $companyAddress, $industry, $userId);
        } else {
            // INSERT: create a new employer record. We hash a default
            // password here to satisfy a required column; in real apps you
            // might design the schema differently.
            $defaultPassword = password_hash('changeme123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO employers (user_id, company_name, email, password, logo, description, website_url, location, industry, createdAt, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param('issssssss', $userId, $companyName, $contactEmail, $defaultPassword, $companyLogo, $companyDescription, $companyWebsite, $companyAddress, $industry);
        }
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        
        // Update the session to reflect any changed contact details.
        $_SESSION['user']['name'] = $contactName;
        $_SESSION['user']['email'] = $contactEmail;
        $_SESSION['user']['phone'] = $contactPhone;
        
        $successMsg = 'Company profile updated successfully!';
        
        // Redirect to avoid form re-submission on refresh (Post/Redirect/Get).
        header('Location: employer-profile.php?success=1');
        exit;
        
    } catch (Exception $e) {
        // Roll back on any exception to keep DB consistent.
        $conn->rollback();
        $errorMsg = 'Error updating profile: ' . $e->getMessage();
    }
}

// --- Calculate profile completion (UI helper) ---
$fields = [
    $profile['company_name'] ?? '',
    $profile['company_logo'] ?? '',
    $profile['company_description'] ?? '',
    $profile['company_website'] ?? '',
    $profile['company_address'] ?? '',
    $profile['company_size'] ?? '',
    $profile['industry'] ?? '',
    $profile['name'] ?? '',
    $profile['email'] ?? '',
    $profile['phone'] ?? ''
];
$filledFields = 0;
foreach ($fields as $field) {
    if (!empty($field)) {
        $filledFields++;
    }
}
$profileCompletion = round(($filledFields / max(1, count($fields))) * 100);

if (isset($_GET['success'])) {
    $successMsg = 'Company profile updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Profile | Career Connect Hub</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="../css/student-profile.css">
</head>
<body>
    <?php include_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="profile-container">
        <button onclick="window.history.back()" class="btn btn-secondary" style="margin-bottom: 20px;">
          ← Back
        </button>
        <h1>Company Profile</h1>
        
        <!-- Profile Completion -->
        <div class="completion-banner">
            <div class="completion-info">
                <span>Profile Completion: <strong id="completion-text"><?= $profileCompletion ?>%</strong></span>
            </div>
            <div class="progress-bar">
                <div id="completion-fill" style="width: <?= $profileCompletion ?>%; background: linear-gradient(90deg, #0a66c2 0%, #5ba3d0 100%); height: 100%; border-radius: 10px;"></div>
            </div>
        </div>

        <?php if ($successMsg): ?>
            <div class="message success"><?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>
        
        <?php if ($errorMsg): ?>
            <div class="message error"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="profile-form">
            
            <!-- Company Logo -->
            <div class="profile-picture-section">
                <h2>Company Logo</h2>
                <div class="picture-upload-container">
                    <img id="logo-preview" src="<?= htmlspecialchars($profile['logo'] ?? '/career_hub/uploads/profile/default-avatar.png') ?>" alt="Company Logo" class="profile-picture-preview">
                    <div class="upload-controls">
                        <label for="company_logo" class="btn btn-secondary">Choose Logo</label>
                        <input type="file" id="company_logo" name="company_logo" accept="image/*" style="display: none;">
                        <p class="text-medium">Recommended: Square image, max 5MB</p>
                    </div>
                </div>
            </div>

            <!-- Company Information -->
            <div class="form-section">
                <h2>Company Information</h2>
                
                <div class="form-group">
                    <label for="company_name">Company Name *</label>
                    <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($profile['company_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="company_description">Company Description *</label>
                    <textarea id="company_description" name="company_description" rows="5" required><?= htmlspecialchars($profile['company_description'] ?? '') ?></textarea>
                    <small>Tell candidates about your company, mission, and culture</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="industry">Industry *</label>
                        <select id="industry" name="industry" required>
                            <option value="">Select Industry</option>
                            <option value="Technology" <?= ($profile['industry'] ?? '') === 'Technology' ? 'selected' : '' ?>>Technology</option>
                            <option value="Finance" <?= ($profile['industry'] ?? '') === 'Finance' ? 'selected' : '' ?>>Finance</option>
                            <option value="Healthcare" <?= ($profile['industry'] ?? '') === 'Healthcare' ? 'selected' : '' ?>>Healthcare</option>
                            <option value="Education" <?= ($profile['industry'] ?? '') === 'Education' ? 'selected' : '' ?>>Education</option>
                            <option value="Retail" <?= ($profile['industry'] ?? '') === 'Retail' ? 'selected' : '' ?>>Retail</option>
                            <option value="Manufacturing" <?= ($profile['industry'] ?? '') === 'Manufacturing' ? 'selected' : '' ?>>Manufacturing</option>
                            <option value="Hospitality" <?= ($profile['industry'] ?? '') === 'Hospitality' ? 'selected' : '' ?>>Hospitality</option>
                            <option value="Construction" <?= ($profile['industry'] ?? '') === 'Construction' ? 'selected' : '' ?>>Construction</option>
                            <option value="Other" <?= ($profile['industry'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="company_size">Company Size *</label>
                        <select id="company_size" name="company_size" required>
                            <option value="">Select Size</option>
                            <option value="1-10" <?= ($profile['company_size'] ?? '') === '1-10' ? 'selected' : '' ?>>1-10 employees</option>
                            <option value="11-50" <?= ($profile['company_size'] ?? '') === '11-50' ? 'selected' : '' ?>>11-50 employees</option>
                            <option value="51-200" <?= ($profile['company_size'] ?? '') === '51-200' ? 'selected' : '' ?>>51-200 employees</option>
                            <option value="201-500" <?= ($profile['company_size'] ?? '') === '201-500' ? 'selected' : '' ?>>201-500 employees</option>
                            <option value="501-1000" <?= ($profile['company_size'] ?? '') === '501-1000' ? 'selected' : '' ?>>501-1000 employees</option>
                            <option value="1000+" <?= ($profile['company_size'] ?? '') === '1000+' ? 'selected' : '' ?>>1000+ employees</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="company_website">Company Website</label>
                    <input type="url" id="company_website" name="company_website" value="<?= htmlspecialchars($profile['company_website'] ?? '') ?>" placeholder="https://www.example.com">
                </div>

                <div class="form-group">
                    <label for="company_address">Company Address *</label>
                    <textarea id="company_address" name="company_address" rows="3" required><?= htmlspecialchars($profile['company_address'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="form-section">
                <h2>Contact Information</h2>
                
                <div class="form-group">
                    <label for="contact_name">Contact Person Name *</label>
                    <input type="text" id="contact_name" name="contact_name" value="<?= htmlspecialchars($profile['name'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_email">Contact Email *</label>
                        <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($profile['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_phone">Contact Phone *</label>
                        <input type="tel" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Company Profile</button>
                <a href="employer.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </main>

    <?php include_once __DIR__ . '/../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Load theme
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.body.classList.add(savedTheme + '-theme');

            // Logo preview
            const logoInput = document.getElementById('company_logo');
            const logoPreview = document.getElementById('logo-preview');

            logoInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        logoPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Profile completion animation
            const completionFill = document.getElementById('completion-fill');
            if (completionFill) {
                const targetWidth = completionFill.style.width;
                completionFill.style.width = '0%';
                setTimeout(() => {
                    completionFill.style.width = targetWidth;
                }, 300);
            }
        });
    </script>
</body>
</html>
