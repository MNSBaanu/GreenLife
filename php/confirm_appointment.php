<?php
session_start();
require_once 'dbconnect.php';

// Check if user is logged in and is a therapist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'] ?? '';
    $therapist_id = $_SESSION['user_id'];
    
    if (empty($appointment_id) || !is_numeric($appointment_id)) {
        echo json_encode(['error' => 'Invalid appointment ID']);
        exit();
    }
    
    try {
        // Check if appointment exists and belongs to this therapist
        $check_query = "SELECT a.*, c.fullname as client_name, s.name as service_name 
                       FROM appointments a 
                       INNER JOIN users c ON a.client_id = c.id 
                       INNER JOIN services s ON a.service_id = s.id 
                       WHERE a.id = ? AND a.therapist_id = ? AND (a.status = 'pending' OR a.status = 'open')";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $appointment_id, $therapist_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['error' => 'Appointment not found or already confirmed']);
            exit();
        }
        
        $appointment = $check_result->fetch_assoc();
        $client_id = $appointment['client_id'];
        
        // Update appointment status to confirmed
        $update_query = "UPDATE appointments SET status = 'confirmed' WHERE id = ? AND therapist_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $appointment_id, $therapist_id);
        
        if ($update_stmt->execute()) {
            // Create notification for client
            $notification_query = "INSERT INTO notifications (user_id, title, message, type, related_id, related_type) VALUES (?, ?, ?, 'appointment', ?, 'appointment')";
            $notification_stmt = $conn->prepare($notification_query);
            $title = "Appointment Confirmed";
            $message = "Your appointment for " . $appointment['service_name'] . " on " . date('M d, Y', strtotime($appointment['appointment_date'])) . " at " . date('g:i A', strtotime($appointment['start_time'])) . " has been confirmed.";
            $notification_stmt->bind_param("issi", $client_id, $title, $message, $appointment_id);
            $notification_stmt->execute();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Appointment confirmed successfully!'
            ]);
        } else {
            echo json_encode(['error' => 'Failed to confirm appointment']);
        }
        
    } catch (Exception $e) {
        error_log("Error confirming appointment: " . $e->getMessage());
        echo json_encode(['error' => 'An error occurred while confirming the appointment']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?> 