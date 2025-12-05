<?php
// Path: includes/upload_helper.php
// Centralized file upload validation and handling

class UploadHelper {
    
    // Allowed file types
    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    const ALLOWED_DOCUMENT_TYPES = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    // File size limits (in bytes)
    const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2MB
    const MAX_DOCUMENT_SIZE = 5 * 1024 * 1024; // 5MB
    
    /**
     * Upload profile image
     * @param array $file - $_FILES['fieldname']
     * @param int $userId - User ID for folder organization
     * @return array - ['success' => bool, 'path' => string, 'error' => string]
     */
    public static function uploadProfileImage($file, $userId) {
        // Validate file exists
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
        }
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed.'];
        }
        
        // Validate file size
        if ($file['size'] > self::MAX_IMAGE_SIZE) {
            return ['success' => false, 'error' => 'File too large. Maximum size is 2MB.'];
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../uploads/profiles/' . $userId . '/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ['success' => false, 'error' => 'Failed to create upload directory'];
            }
        }
        
        // Generate safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'error' => 'Failed to move uploaded file'];
        }
        
        // Return relative path for database storage
        $relativePath = '/uploads/profiles/' . $userId . '/' . $filename;
        
        return [
            'success' => true,
            'path' => $relativePath,
            'filename' => $filename
        ];
    }
    
    /**
     * Upload company logo
     * @param array $file - $_FILES['fieldname']
     * @param int $userId - User ID for folder organization
     * @return array - ['success' => bool, 'path' => string, 'error' => string]
     */
    public static function uploadCompanyLogo($file, $userId) {
        // Validate file exists
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
        }
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed.'];
        }
        
        // Validate file size
        if ($file['size'] > self::MAX_IMAGE_SIZE) {
            return ['success' => false, 'error' => 'File too large. Maximum size is 2MB.'];
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../uploads/company_logos/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ['success' => false, 'error' => 'Failed to create upload directory'];
            }
        }
        
        // Generate safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . $userId . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        // Delete old logo if exists
        $pattern = $uploadDir . 'logo_' . $userId . '_*';
        foreach (glob($pattern) as $oldFile) {
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'error' => 'Failed to move uploaded file'];
        }
        
        // Return relative path for database storage
        $relativePath = '/uploads/company_logos/' . $filename;
        
        return [
            'success' => true,
            'path' => $relativePath,
            'filename' => $filename
        ];
    }
    
    /**
     * Upload CV/Resume
     * @param array $file - $_FILES['fieldname']
     * @param int $applicationId - Application ID for folder organization
     * @return array - ['success' => bool, 'path' => string, 'error' => string]
     */
    public static function uploadCV($file, $applicationId) {
        // Validate file exists
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
        }
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, self::ALLOWED_DOCUMENT_TYPES)) {
            return ['success' => false, 'error' => 'Invalid file type. Only PDF, DOC, and DOCX allowed.'];
        }
        
        // Validate file size
        if ($file['size'] > self::MAX_DOCUMENT_SIZE) {
            return ['success' => false, 'error' => 'File too large. Maximum size is 5MB.'];
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../uploads/cvs/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ['success' => false, 'error' => 'Failed to create upload directory'];
            }
        }
        
        // Generate safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'cv_' . $applicationId . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'error' => 'Failed to move uploaded file'];
        }
        
        // Return relative path for database storage
        $relativePath = '/uploads/cvs/' . $filename;
        
        return [
            'success' => true,
            'path' => $relativePath,
            'filename' => $filename
        ];
    }
    
    /**
     * Delete file
     * @param string $path - Relative path to file
     * @return bool
     */
    public static function deleteFile($path) {
        $fullPath = __DIR__ . '/..' . $path;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
    
    /**
     * Validate image dimensions (optional)
     * @param string $tmpPath - Temporary file path
     * @param int $maxWidth - Maximum width
     * @param int $maxHeight - Maximum height
     * @return bool
     */
    public static function validateImageDimensions($tmpPath, $maxWidth = 2000, $maxHeight = 2000) {
        list($width, $height) = getimagesize($tmpPath);
        return ($width <= $maxWidth && $height <= $maxHeight);
    }
}
