<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['profile_picture'];
    
    // Validation
    $errors = [];
    
    // Check if file was uploaded
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $errors[] = "File is too large (exceeds server limit)";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = "File is too large (exceeds form limit)";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = "File was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = "No file was uploaded";
                break;
            default:
                $errors[] = "Upload error occurred";
        }
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = "File size must be less than 5MB";
    }
    
    // Check file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        $errors[] = "Only JPG, PNG, and GIF files are allowed";
    }
    
    // If no errors, process the upload
    if (empty($errors)) {
        try {
            // Create uploads directory if it doesn't exist
            $upload_dir = '../images/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Get current profile image to delete old one
                $current_image_query = "SELECT profile_image FROM users WHERE id = ?";
                $stmt_current = $conn->prepare($current_image_query);
                $stmt_current->bind_param("i", $user_id);
                $stmt_current->execute();
                $result = $stmt_current->get_result();
                $current_data = $result->fetch_assoc();
                
                // Delete old profile image if it exists and is not default
                if ($current_data && $current_data['profile_image'] && 
                    $current_data['profile_image'] !== 'default_avatar.png' &&
                    file_exists('../images/' . $current_data['profile_image'])) {
                    unlink('../images/' . $current_data['profile_image']);
                }
                
                // Update database with new profile image
                $update_query = "UPDATE users SET profile_image = ? WHERE id = ?";
                $stmt_update = $conn->prepare($update_query);
                $stmt_update->bind_param("si", $filename, $user_id);
                
                if ($stmt_update->execute()) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Profile picture updated successfully!',
                        'filename' => $filename
                    ]);
                } else {
                    throw new Exception("Failed to update database");
                }
            } else {
                throw new Exception("Failed to move uploaded file");
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => implode(", ", $errors)]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
}
?> 