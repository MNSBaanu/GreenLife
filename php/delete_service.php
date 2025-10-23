<?php
session_start();
require_once 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $service_id = intval($_GET['id']);
    
    // Get service name before deletion for success message
    $get_service_query = "SELECT name FROM services WHERE id = ?";
    $get_service_stmt = $conn->prepare($get_service_query);
    $get_service_stmt->bind_param("i", $service_id);
    $get_service_stmt->execute();
    $service_result = $get_service_stmt->get_result();
    
    if ($service_result->num_rows > 0) {
        $service_data = $service_result->fetch_assoc();
        $service_name = $service_data['name'];
        
        // Delete related records first (due to foreign key constraints)
        
        // Delete sessions that use this service
        $delete_sessions_query = "DELETE FROM sessions WHERE service_id = ?";
        $delete_sessions_stmt = $conn->prepare($delete_sessions_query);
        $delete_sessions_stmt->bind_param("i", $service_id);
        $delete_sessions_stmt->execute();
        
        // Delete appointments that use this service
        $delete_appointments_query = "DELETE FROM appointments WHERE service_id = ?";
        $delete_appointments_stmt = $conn->prepare($delete_appointments_query);
        $delete_appointments_stmt->bind_param("i", $service_id);
        $delete_appointments_stmt->execute();
        
        // Finally, delete the service from services table
        $delete_service_query = "DELETE FROM services WHERE id = ?";
        $delete_service_stmt = $conn->prepare($delete_service_query);
        $delete_service_stmt->bind_param("i", $service_id);
        
        if ($delete_service_stmt->execute()) {
            header('Location: admin_dashboard.php?success=deleted&deleted_name=' . urlencode($service_name) . '&deleted_type=Service');
            exit();
        } else {
            header('Location: admin_dashboard.php?error=delete_failed');
            exit();
        }
    } else {
        header('Location: admin_dashboard.php?error=service_not_found');
        exit();
    }
} else {
    header('Location: admin_dashboard.php?error=invalid_request');
    exit();
}
?> 