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
    $_SESSION['error'] = 'Invalid video ID provided.';
    header('Location: admin_dashboard.php');
    exit();
}

$video_id = (int)$_GET['id'];

try {
    // Get video details before deletion
    $stmt = $conn->prepare("SELECT title FROM videos WHERE id = ?");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Video not found.';
        header('Location: admin_dashboard.php');
        exit();
    }
    
    $video = $result->fetch_assoc();
    
    // Delete the video from database
    $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->bind_param("i", $video_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Video "' . htmlspecialchars($video['title']) . '" has been deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete video. Please try again.';
    }
    
} catch (Exception $e) {
    error_log("Error deleting video: " . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while deleting the video.';
}

$stmt->close();
$conn->close();

header('Location: admin_dashboard.php');
exit();
?> 