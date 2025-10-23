<?php
session_start();
require_once 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../html/login.html');
    exit();
}

if (isset($_GET['id'])) {
    $tip_id = intval($_GET['id']);
    
    // Get tip name before deletion for success message
    $get_tip_query = "SELECT title FROM health_tips WHERE id = ?";
    $get_tip_stmt = $conn->prepare($get_tip_query);
    $get_tip_stmt->bind_param("i", $tip_id);
    $get_tip_stmt->execute();
    $tip_result = $get_tip_stmt->get_result();
    
    if ($tip_result->num_rows > 0) {
        $tip_data = $tip_result->fetch_assoc();
        $tip_name = $tip_data['title'];
        
        // Delete the tip
        $delete_tip_query = "DELETE FROM health_tips WHERE id = ?";
        $delete_tip_stmt = $conn->prepare($delete_tip_query);
        $delete_tip_stmt->bind_param("i", $tip_id);
        
        if ($delete_tip_stmt->execute()) {
            header('Location: admin_dashboard.php?success=deleted&deleted_name=' . urlencode($tip_name) . '&deleted_type=Tip&section=tips');
            exit();
        } else {
            header('Location: admin_dashboard.php?error=delete_failed&section=tips');
            exit();
        }
    } else {
        header('Location: admin_dashboard.php?error=tip_not_found&section=tips');
        exit();
    }
} else {
    header('Location: admin_dashboard.php?error=invalid_request&section=tips');
    exit();
}
?> 