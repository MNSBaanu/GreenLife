<?php
session_start();
require_once 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? '');

    // Validate required fields
    if (empty($title) || empty($category) || empty($content) || empty($author)) {
        header('Location: admin_dashboard.php?error=missing_fields&section=tips');
        exit();
    }

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['tip_image']) && $_FILES['tip_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/tips/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['tip_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'tip_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['tip_image']['tmp_name'], $upload_path)) {
                $image_path = $new_filename;
            }
        }
    }

    // Insert new tip
    $insert_query = "INSERT INTO health_tips (title, description, category, image, created_at) VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ssss", $title, $content, $category, $image_path);

    if ($insert_stmt->execute()) {
        header('Location: admin_dashboard.php?success=tip_added&tip_name=' . urlencode($title) . '&section=tips');
        exit();
    } else {
        header('Location: admin_dashboard.php?error=database_error&section=tips');
        exit();
    }
} else {
    // If not POST request, redirect to dashboard
    header('Location: admin_dashboard.php?section=tips');
    exit();
}
?> 