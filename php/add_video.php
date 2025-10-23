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
    $video_url = trim($_POST['video_url'] ?? '');
    $duration = intval($_POST['duration'] ?? 0);
    $author = trim($_POST['author'] ?? '');

    // Validate required fields
    if (empty($title) || empty($category) || empty($summary) || empty($video_url) || empty($author) || $duration <= 0) {
        header('Location: admin_dashboard.php?error=missing_fields');
        exit();
    }

    // Validate URL format
    if (!filter_var($video_url, FILTER_VALIDATE_URL)) {
        header('Location: admin_dashboard.php?error=invalid_url');
        exit();
    }

    // Handle thumbnail upload
    $thumbnail_path = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/videos/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'video_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
                $thumbnail_path = $new_filename;
            }
        }
    }

    // Insert new video
    $insert_query = "INSERT INTO videos (title, description, youtube_url, category, created_at) VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ssss", $title, $summary, $video_url, $category);

    if ($insert_stmt->execute()) {
        header('Location: admin_dashboard.php?success=video_added&video_name=' . urlencode($title));
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