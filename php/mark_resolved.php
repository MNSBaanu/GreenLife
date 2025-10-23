<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is a therapist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inquiry_id = $_POST['inquiry_id'] ?? '';
    $therapist_id = $_SESSION['user_id'];
    
    if (empty($inquiry_id)) {
        $_SESSION['error'] = "Please provide inquiry ID.";
        header("Location: therapist_dashboard.php#inquiries");
        exit();
    }
    
    try {
        // Update the inquiry status to resolved
        $update_query = "UPDATE inquiries SET status = 'resolved' WHERE id = ? AND assigned_therapist_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $inquiry_id, $therapist_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Inquiry marked as resolved successfully!";
        } else {
            $_SESSION['error'] = "Failed to mark inquiry as resolved. Please try again.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    }
    
    header("Location: therapist_dashboard.php#inquiries");
    exit();
}

// If not POST request, redirect back
header("Location: therapist_dashboard.php#inquiries");
exit();
?> 