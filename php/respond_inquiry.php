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
    $response = $_POST['response'] ?? '';
    $therapist_id = $_SESSION['user_id'];
    
    if (empty($inquiry_id) || empty($response)) {
        $_SESSION['error'] = "Please provide both inquiry ID and response.";
        header("Location: therapist_dashboard.php#inquiries");
        exit();
    }
    
    try {
        // Update the inquiry with the response
        $update_query = "UPDATE inquiries SET response = ?, response_date = NOW(), status = 'resolved' WHERE id = ? AND assigned_therapist_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sii", $response, $inquiry_id, $therapist_id);
        
        if ($stmt->execute()) {
            // Create notification for the client if they have an account
            $client_query = "SELECT client_id, client_name FROM inquiries WHERE id = ?";
            $client_stmt = $conn->prepare($client_query);
            $client_stmt->bind_param("i", $inquiry_id);
            $client_stmt->execute();
            $client_result = $client_stmt->get_result();
            $client_data = $client_result->fetch_assoc();
            
            if ($client_data && $client_data['client_id']) {
                $notification_query = "INSERT INTO notifications (user_id, title, message, type, related_id, related_type) VALUES (?, ?, ?, 'inquiry', ?, 'inquiry')";
                $notification_stmt = $conn->prepare($notification_query);
                $title = "Inquiry Response";
                $message = "Your inquiry has been responded to by your therapist.";
                $notification_stmt->bind_param("issi", $client_data['client_id'], $title, $message, $inquiry_id);
                $notification_stmt->execute();
            }
            
            $_SESSION['success'] = "Response sent successfully!";
        } else {
            $_SESSION['error'] = "Failed to send response. Please try again.";
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