<?php
session_start();
require_once 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $therapist_id = intval($_GET['id']);
    
    // Get therapist name before deletion for success message
    $get_therapist_query = "SELECT fullname FROM users WHERE id = ? AND role = 'therapist'";
    $get_therapist_stmt = $conn->prepare($get_therapist_query);
    $get_therapist_stmt->bind_param("i", $therapist_id);
    $get_therapist_stmt->execute();
    $therapist_result = $get_therapist_stmt->get_result();
    
    if ($therapist_result->num_rows > 0) {
        $therapist_data = $therapist_result->fetch_assoc();
        $therapist_name = $therapist_data['fullname'];
        
        // Delete related records first (due to foreign key constraints)
        
        // Delete therapist from therapists table
        $delete_therapist_details_query = "DELETE FROM therapists WHERE user_id = ?";
        $delete_therapist_details_stmt = $conn->prepare($delete_therapist_details_query);
        $delete_therapist_details_stmt->bind_param("i", $therapist_id);
        $delete_therapist_details_stmt->execute();
        
        // Update client progress records to remove therapist reference
        $update_progress_query = "UPDATE client_progress SET therapist_id = NULL WHERE therapist_id = ?";
        $update_progress_stmt = $conn->prepare($update_progress_query);
        $update_progress_stmt->bind_param("i", $therapist_id);
        $update_progress_stmt->execute();
        
        // Update sessions to remove therapist reference
        $update_sessions_query = "UPDATE sessions SET therapist_id = NULL WHERE therapist_id = ?";
        $update_sessions_stmt = $conn->prepare($update_sessions_query);
        $update_sessions_stmt->bind_param("i", $therapist_id);
        $update_sessions_stmt->execute();
        
        // Update appointments to remove therapist reference
        $update_appointments_query = "UPDATE appointments SET therapist_id = NULL WHERE therapist_id = ?";
        $update_appointments_stmt = $conn->prepare($update_appointments_query);
        $update_appointments_stmt->bind_param("i", $therapist_id);
        $update_appointments_stmt->execute();
        
        // Update inquiries to remove therapist assignment
        $update_inquiries_query = "UPDATE inquiries SET assigned_therapist_id = NULL WHERE assigned_therapist_id = ?";
        $update_inquiries_stmt = $conn->prepare($update_inquiries_query);
        $update_inquiries_stmt->bind_param("i", $therapist_id);
        $update_inquiries_stmt->execute();
        
        // Update clients to remove therapist assignment
        $update_clients_query = "UPDATE users SET assigned_therapist_id = NULL WHERE assigned_therapist_id = ?";
        $update_clients_stmt = $conn->prepare($update_clients_query);
        $update_clients_stmt->bind_param("i", $therapist_id);
        $update_clients_stmt->execute();
        
        // Delete notifications for this therapist
        $delete_notifications_query = "DELETE FROM notifications WHERE user_id = ?";
        $delete_notifications_stmt = $conn->prepare($delete_notifications_query);
        $delete_notifications_stmt->bind_param("i", $therapist_id);
        $delete_notifications_stmt->execute();
        
        // Finally, delete the therapist from users table
        $delete_therapist_query = "DELETE FROM users WHERE id = ? AND role = 'therapist'";
        $delete_therapist_stmt = $conn->prepare($delete_therapist_query);
        $delete_therapist_stmt->bind_param("i", $therapist_id);
        
        if ($delete_therapist_stmt->execute()) {
            header('Location: admin_dashboard.php?success=deleted&deleted_name=' . urlencode($therapist_name) . '&deleted_type=Therapist');
            exit();
        } else {
            header('Location: admin_dashboard.php?error=delete_failed');
            exit();
        }
    } else {
        header('Location: admin_dashboard.php?error=therapist_not_found');
        exit();
    }
} else {
    header('Location: admin_dashboard.php?error=invalid_request');
    exit();
}
?> 