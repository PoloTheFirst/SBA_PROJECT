<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
// Set content type at the very beginning
header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users

require 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Security token invalid']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

$user_id = $_SESSION['user_id'];
$file = $_FILES['avatar'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$file_type = mime_content_type($file['tmp_name']);

if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Only JPEG, PNG, GIF, and WebP images are allowed']);
    exit();
}

// Validate file size (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
    exit();
}

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/avatars/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Check if this is a client-edited image
$is_edited = isset($_POST['edited']) && $_POST['edited'] === 'true';

if ($is_edited) {
    // For client-edited images, use JPEG format and trust the client-side cropping
    $filename = 'avatar_' . $user_id . '_' . time() . '.jpg';
    $file_path = $upload_dir . $filename;

    // Simply move the uploaded file (it's already cropped and resized by client)
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save edited image']);
        exit();
    }

    // Optional: Verify the image dimensions are correct (150x150)
    list($width, $height) = getimagesize($file_path);
    if ($width !== 150 || $height !== 150) {
        // If dimensions are wrong, we'll resize it server-side as fallback
        resizeImageServerSide($file_path, $file_type);
    }
} else {
    // For non-edited images, use original server-side resizing
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;

    // Original server-side resizing code
    try {
        list($width, $height) = getimagesize($file['tmp_name']);
        $src_image = null;

        switch ($file_type) {
            case 'image/jpeg':
            case 'image/jpg':
                $src_image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $src_image = imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/gif':
                $src_image = imagecreatefromgif($file['tmp_name']);
                break;
            case 'image/webp':
                $src_image = imagecreatefromwebp($file['tmp_name']);
                break;
            default:
                throw new Exception('Unsupported image format');
        }

        // Create square canvas
        $dst_image = imagecreatetruecolor(150, 150);

        // Preserve transparency for PNG and GIF
        if ($file_type == 'image/png' || $file_type == 'image/gif') {
            imagecolortransparent($dst_image, imagecolorallocatealpha($dst_image, 0, 0, 0, 127));
            imagealphablending($dst_image, false);
            imagesavealpha($dst_image, true);
        }

        // Calculate aspect ratio and crop
        $aspect_ratio = $width / $height;
        if ($aspect_ratio > 1) {
            // Landscape - crop width
            $new_width = $height;
            $new_height = $height;
            $src_x = ($width - $new_width) / 2;
            $src_y = 0;
        } else {
            // Portrait - crop height
            $new_width = $width;
            $new_height = $width;
            $src_x = 0;
            $src_y = ($height - $new_height) / 2;
        }

        // Resize and crop
        imagecopyresampled($dst_image, $src_image, 0, 0, $src_x, $src_y, 150, 150, $new_width, $new_height);

        // Save resized image
        switch ($file_type) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($dst_image, $file_path, 90);
                break;
            case 'image/png':
                imagepng($dst_image, $file_path, 9);
                break;
            case 'image/gif':
                imagegif($dst_image, $file_path);
                break;
            case 'image/webp':
                imagewebp($dst_image, $file_path, 90);
                break;
        }

        imagedestroy($src_image);
        imagedestroy($dst_image);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error processing image: ' . $e->getMessage()]);
        exit();
    }
}

/**
 * Fallback server-side resizing for client-edited images that have wrong dimensions
 */
function resizeImageServerSide($file_path, $file_type)
{
    try {
        list($width, $height) = getimagesize($file_path);
        $src_image = null;

        switch ($file_type) {
            case 'image/jpeg':
            case 'image/jpg':
                $src_image = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $src_image = imagecreatefrompng($file_path);
                break;
            case 'image/git':
                $src_image = imagecreatefromgif($file_path);
                break;
            case 'image/webp':
                $src_image = imagecreatefromwebp($file_path);
                break;
            default:
                return false;
        }

        // Create square canvas
        $dst_image = imagecreatetruecolor(150, 150);

        // Preserve transparency for PNG and GIF
        if ($file_type == 'image/png' || $file_type == 'image/gif') {
            imagecolortransparent($dst_image, imagecolorallocatealpha($dst_image, 0, 0, 0, 127));
            imagealphablending($dst_image, false);
            imagesavealpha($dst_image, true);
        }

        // Resize to 150x150
        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, 150, 150, $width, $height);

        // Save back to the same file as JPEG
        imagejpeg($dst_image, $file_path, 90);

        imagedestroy($src_image);
        imagedestroy($dst_image);

        return true;
    } catch (Exception $e) {
        error_log("Fallback resizing failed: " . $e->getMessage());
        return false;
    }
}

// Update database immediately with new avatar
try {
    // Delete old avatar file if it exists
    $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $old_avatar = $stmt->fetchColumn();
    
    if ($old_avatar && file_exists($old_avatar) && $old_avatar !== $file_path) {
        unlink($old_avatar);
    }

    // Update database with new avatar path
    $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->execute([$file_path, $user_id]);
    
    // Update session
    $_SESSION['profile_picture'] = $file_path;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Avatar updated successfully!',
        'file_path' => $file_path . '?t=' . time() // Cache busting
    ]);
    
} catch (Exception $e) {
    // Delete the uploaded file if database update fails
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    echo json_encode(['success' => false, 'message' => 'Failed to update avatar in database: ' . $e->getMessage()]);
}
?>