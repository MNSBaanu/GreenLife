<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is a therapist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $therapist_id = $_SESSION['user_id'];
    
    if (empty($appointment_id) || empty($appointment_date) || empty($start_time)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: therapist_dashboard.php#appointment-management");
        exit();
    }
    
    try {
        // Get current appointment details
        $current_query = "SELECT end_time, duration FROM appointments WHERE id = ? AND therapist_id = ?";
        $current_stmt = $conn->prepare($current_query);
        $current_stmt->bind_param("ii", $appointment_id, $therapist_id);
        $current_stmt->execute();
        $current_result = $current_stmt->get_result();
        
        if ($current_result->num_rows === 0) {
            $_SESSION['error'] = "Appointment not found or you don't have permission to edit it.";
            header("Location: therapist_dashboard.php#appointment-management");
            exit();
        }
        
        $current_data = $current_result->fetch_assoc();
        $duration = $current_data['duration'];
        
        // Calculate new end time based on duration
        $start_datetime = new DateTime($start_time);
        $end_datetime = clone $start_datetime;
        $end_datetime->add(new DateInterval('PT' . $duration . 'M'));
        $end_time = $end_datetime->format('H:i:s');
        
        // Check for conflicts with existing appointments (excluding current appointment)
        $conflict_query = "SELECT * FROM appointments WHERE therapist_id = ? AND appointment_date = ? AND id != ? AND 
                          ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))";
        $conflict_stmt = $conn->prepare($conflict_query);
        $conflict_stmt->bind_param("issssssss", $therapist_id, $appointment_date, $appointment_id, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time);
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();
        
        if ($conflict_result->num_rows > 0) {
            $_SESSION['error'] = "There is a scheduling conflict at the selected time. Please choose another time.";
            header("Location: therapist_dashboard.php#appointment-management");
            exit();
        }
        
        // Update the appointment
        $update_query = "UPDATE appointments SET appointment_date = ?, start_time = ?, end_time = ?, notes = ? WHERE id = ? AND therapist_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssssii", $appointment_date, $start_time, $end_time, $notes, $appointment_id, $therapist_id);
        
        if ($update_stmt->execute()) {
            // Update corresponding session record
            $session_update_query = "UPDATE sessions SET session_date = ?, start_time = ?, end_time = ? WHERE appointment_id = ?";
            $session_update_stmt = $conn->prepare($session_update_query);
            $session_update_stmt->bind_param("sssi", $appointment_date, $start_time, $end_time, $appointment_id);
            $session_update_stmt->execute();
            
            // Create notification for client
            $client_query = "SELECT client_id FROM appointments WHERE id = ?";
            $client_stmt = $conn->prepare($client_query);
            $client_stmt->bind_param("i", $appointment_id);
            $client_stmt->execute();
            $client_result = $client_stmt->get_result();
            $client_data = $client_result->fetch_assoc();
            
            if ($client_data) {
                $notification_query = "INSERT INTO notifications (user_id, title, message, type, related_id, related_type) VALUES (?, ?, ?, 'appointment', ?, 'appointment')";
                $notification_stmt = $conn->prepare($notification_query);
                $title = "Appointment Updated";
                $message = "Your appointment has been updated by your therapist.";
                $notification_stmt->bind_param("issi", $client_data['client_id'], $title, $message, $appointment_id);
                $notification_stmt->execute();
            }
            
            $_SESSION['success'] = "Appointment updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update appointment. Please try again.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    }
    
    header("Location: therapist_dashboard.php#appointment-management");
    exit();
}

// If not POST request, redirect back
header("Location: therapist_dashboard.php#appointment-management");
exit();
?> 