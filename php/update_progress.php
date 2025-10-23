<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is a therapist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? '';
    $progress_percentage = $_POST['progress_percentage'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $goals_achieved = $_POST['goals_achieved'] ?? '';
    $next_goals = $_POST['next_goals'] ?? '';
    $therapist_id = $_SESSION['user_id'];
    
    if (empty($client_id) || empty($progress_percentage)) {
        $_SESSION['error'] = "Please provide client ID and progress percentage.";
        header("Location: therapist_dashboard.php#client-management");
        exit();
    }
    
    // Validate progress percentage
    if (!is_numeric($progress_percentage) || $progress_percentage < 0 || $progress_percentage > 100) {
        $_SESSION['error'] = "Progress percentage must be between 0 and 100.";
        header("Location: therapist_dashboard.php#client-management");
        exit();
    }
    
    try {
        // Insert progress record
        $progress_query = "INSERT INTO client_progress (client_id, therapist_id, progress_date, progress_percentage, notes, goals_achieved, next_goals) 
                          VALUES (?, ?, CURDATE(), ?, ?, ?, ?)";
        $progress_stmt = $conn->prepare($progress_query);
        $progress_stmt->bind_param("iiisss", $client_id, $therapist_id, $progress_percentage, $notes, $goals_achieved, $next_goals);
        
        if ($progress_stmt->execute()) {
            // Update client's overall progress in users table
            $update_client_query = "UPDATE users SET progress = ?, last_session = CURDATE() WHERE id = ?";
            $update_client_stmt = $conn->prepare($update_client_query);
            $update_client_stmt->bind_param("ii", $progress_percentage, $client_id);
            $update_client_stmt->execute();
            
            // Create notification for client
            $notification_query = "INSERT INTO notifications (user_id, title, message, type, related_id, related_type) VALUES (?, ?, ?, 'progress', ?, 'progress')";
            $notification_stmt = $conn->prepare($notification_query);
            $title = "Progress Update";
            $message = "Your therapist has updated your progress. Current progress: " . $progress_percentage . "%";
            $notification_stmt->bind_param("issi", $client_id, $title, $message, $client_id);
            $notification_stmt->execute();
            
            $_SESSION['success'] = "Client progress updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update progress. Please try again.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    }
    
    header("Location: therapist_dashboard.php#client-management");
    exit();
}

// If not POST request, redirect back
header("Location: therapist_dashboard.php#client-management");
exit();
?> 