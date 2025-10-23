<?php
session_start();
require_once 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $client_id = intval($_GET['id']);
    
    // Debug: Log the client ID being deleted
    error_log("Attempting to delete client ID: " . $client_id);
    
    // Get client name before deletion for success message
    $get_client_query = "SELECT fullname FROM users WHERE id = ? AND role = 'client'";
    $get_client_stmt = $conn->prepare($get_client_query);
    $get_client_stmt->bind_param("i", $client_id);
    $get_client_stmt->execute();
    $client_result = $get_client_stmt->get_result();
    
    if ($client_result->num_rows > 0) {
        $client_data = $client_result->fetch_assoc();
        $client_name = $client_data['fullname'];
        
        // Debug: Log the client name
        error_log("Deleting client: " . $client_name);
        
        // Delete related records first (due to foreign key constraints)
        
        // Delete client progress records
        $delete_progress_query = "DELETE FROM client_progress WHERE client_id = ?";
        $delete_progress_stmt = $conn->prepare($delete_progress_query);
        $delete_progress_stmt->bind_param("i", $client_id);
        $delete_progress_stmt->execute();
        
        // Delete sessions where client is involved
        $delete_sessions_query = "DELETE FROM sessions WHERE client_id = ?";
        $delete_sessions_stmt = $conn->prepare($delete_sessions_query);
        $delete_sessions_stmt->bind_param("i", $client_id);
        $delete_sessions_stmt->execute();
        
        // Delete appointments where client is involved
        $delete_appointments_query = "DELETE FROM appointments WHERE client_id = ?";
        $delete_appointments_stmt = $conn->prepare($delete_appointments_query);
        $delete_appointments_stmt->bind_param("i", $client_id);
        $delete_appointments_stmt->execute();
        
        // Delete inquiries from this client
        $delete_inquiries_query = "DELETE FROM inquiries WHERE client_id = ?";
        $delete_inquiries_stmt = $conn->prepare($delete_inquiries_query);
        $delete_inquiries_stmt->bind_param("i", $client_id);
        $delete_inquiries_stmt->execute();
        
        // Delete notifications for this client
        $delete_notifications_query = "DELETE FROM notifications WHERE user_id = ?";
        $delete_notifications_stmt = $conn->prepare($delete_notifications_query);
        $delete_notifications_stmt->bind_param("i", $client_id);
        $delete_notifications_stmt->execute();
        
        // Finally, delete the client from users table
        $delete_client_query = "DELETE FROM users WHERE id = ? AND role = 'client'";
        $delete_client_stmt = $conn->prepare($delete_client_query);
        $delete_client_stmt->bind_param("i", $client_id);
        
        if ($delete_client_stmt->execute()) {
            // Debug: Log successful deletion
            error_log("Successfully deleted client: " . $client_name);
            header('Location: admin_dashboard.php?success=deleted&deleted_name=' . urlencode($client_name) . '&deleted_type=Client');
            exit();
        } else {
            // Debug: Log deletion failure
            error_log("Failed to delete client: " . $client_name . " - " . $conn->error);
            header('Location: admin_dashboard.php?error=delete_failed');
            exit();
        }
    } else {
        // Debug: Log client not found
        error_log("Client not found with ID: " . $client_id);
        header('Location: admin_dashboard.php?error=client_not_found');
        exit();
    }
} else {
    // Debug: Log invalid request
    error_log("Invalid request - no client ID provided");
    header('Location: admin_dashboard.php?error=invalid_request');
    exit();
}
?> 