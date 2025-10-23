<?php
session_start();
require_once 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid article ID provided.';
    header('Location: admin_dashboard.php');
    exit();
}

$article_id = (int)$_GET['id'];

try {
    // Get article details before deletion for image cleanup
    $stmt = $conn->prepare("SELECT title, image FROM articles WHERE id = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Article not found.';
        header('Location: admin_dashboard.php');
        exit();
    }
    
    $article = $result->fetch_assoc();
    $image_path = $article['image'];
    
    // Delete the article from database
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->bind_param("i", $article_id);
    
    if ($stmt->execute()) {
        // Delete the image file if it exists and is not a default image
        if ($image_path && $image_path !== 'default-article.jpg' && file_exists("../images/articles/" . $image_path)) {
            unlink("../images/articles/" . $image_path);
        }
        
        $_SESSION['success'] = 'Article "' . htmlspecialchars($article['title']) . '" has been deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete article. Please try again.';
    }
    
} catch (Exception $e) {
    error_log("Error deleting article: " . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while deleting the article.';
}

$stmt->close();
$conn->close();

header('Location: admin_dashboard.php');
exit();
?> 