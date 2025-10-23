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
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? '');

    // Validate required fields
    if (empty($title) || empty($category) || empty($summary) || empty($content) || empty($author)) {
        header('Location: admin_dashboard.php?error=missing_fields');
        exit();
    }

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['article_image']) && $_FILES['article_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/articles/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['article_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'article_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['article_image']['tmp_name'], $upload_path)) {
                $image_path = $new_filename;
            }
        }
    }

    // Insert new article
    $insert_query = "INSERT INTO articles (title, category, summary, content, image, publish_date, created_at) VALUES (?, ?, ?, ?, ?, CURDATE(), NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("sssss", $title, $category, $summary, $content, $image_path);

    if ($insert_stmt->execute()) {
        header('Location: admin_dashboard.php?success=article_added&article_name=' . urlencode($title));
        exit();
    } else {
        header('Location: admin_dashboard.php?error=database_error');
        exit();
    }
} else {
    // If not POST request, redirect to dashboard
    header('Location: admin_dashboard.php');
    exit();
}
?> 