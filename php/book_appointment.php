<?php
session_start();
require_once 'dbconnect.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_SESSION['user_id'];
    $therapist_id = $_POST['therapist_id'] ?? '';
    $service_id = $_POST['appointment_type'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $session_duration = $_POST['session_duration'] ?? 60;
    $notes = $_POST['appointment_notes'] ?? '';
    
    // Validate required fields
    if (empty($therapist_id) || empty($service_id) || empty($appointment_date) || empty($appointment_time)) {
        echo json_encode(['error' => 'Please fill in all required fields.']);
        exit();
    }
    
    // Validate date is not in the past
    if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
        echo json_encode(['error' => 'Appointment date cannot be in the past.']);
        exit();
    }
    
    // Calculate end time based on duration
    $start_datetime = new DateTime($appointment_time);
    $end_datetime = clone $start_datetime;
    $end_datetime->add(new DateInterval('PT' . $session_duration . 'M'));
    $end_time = $end_datetime->format('H:i:s');
    
    try {
        // Check if therapist is available at this time
        $day_of_week = date('l', strtotime($appointment_date));
        $availability_query = "SELECT * FROM therapist_availability 
                              WHERE therapist_id = ? AND day_of_week = ? 
                              AND start_time <= ? AND end_time >= ? 
                              AND is_available = 1";
        $availability_stmt = $conn->prepare($availability_query);
        $availability_stmt->bind_param("isss", $therapist_id, $day_of_week, $appointment_time, $end_time);
        $availability_stmt->execute();
        $availability_result = $availability_stmt->get_result();
        
        if ($availability_result->num_rows === 0) {
            echo json_encode(['error' => 'Therapist is not available at the selected time. Please choose another time.']);
            exit();
        }
        
        // Check for conflicts with existing appointments
        $conflict_query = "SELECT * FROM appointments 
                          WHERE therapist_id = ? AND appointment_date = ? 
                          AND status IN ('pending', 'confirmed')
                          AND ((start_time <= ? AND end_time > ?) 
                               OR (start_time < ? AND end_time >= ?) 
                               OR (start_time >= ? AND end_time <= ?))";
        $conflict_stmt = $conn->prepare($conflict_query);
        $conflict_stmt->bind_param("isssssss", $therapist_id, $appointment_date, $appointment_time, $appointment_time, $end_time, $end_time, $appointment_time, $end_time);
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();
        
        if ($conflict_result->num_rows > 0) {
            echo json_encode(['error' => 'There is a scheduling conflict at the selected time. Please choose another time.']);
            exit();
        }
        
        // Create the appointment
        $appointment_query = "INSERT INTO appointments (client_id, therapist_id, service_id, appointment_date, start_time, end_time, duration, status, notes) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
        $appointment_stmt = $conn->prepare($appointment_query);
        $appointment_stmt->bind_param("iiisssis", $client_id, $therapist_id, $service_id, $appointment_date, $appointment_time, $end_time, $session_duration, $notes);
        
        if ($appointment_stmt->execute()) {
            $appointment_id = $conn->insert_id;
            
            // Get therapist and service names for notification
            $therapist_query = "SELECT fullname FROM users WHERE id = ?";
            $therapist_stmt = $conn->prepare($therapist_query);
            $therapist_stmt->bind_param("i", $therapist_id);
            $therapist_stmt->execute();
            $therapist_result = $therapist_stmt->get_result();
            $therapist_name = $therapist_result->fetch_assoc()['fullname'];
            
            $service_query = "SELECT name FROM services WHERE id = ?";
            $service_stmt = $conn->prepare($service_query);
            $service_stmt->bind_param("i", $service_id);
            $service_stmt->execute();
            $service_result = $service_stmt->get_result();
            $service_name = $service_result->fetch_assoc()['name'];
            
            // Create notification for therapist
            $notification_query = "INSERT INTO notifications (user_id, title, message, type, related_id, related_type) VALUES (?, ?, ?, 'appointment', ?, 'appointment')";
            $notification_stmt = $conn->prepare($notification_query);
            $title = "New Appointment Request";
            $message = "Client has requested an appointment for " . $service_name . " on " . date('M d, Y', strtotime($appointment_date)) . " at " . date('g:i A', strtotime($appointment_time));
            $notification_stmt->bind_param("issi", $therapist_id, $title, $message, $appointment_id);
            $notification_stmt->execute();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Appointment request submitted successfully! We will confirm your appointment shortly.',
                'appointment_id' => $appointment_id
            ]);
        } else {
            echo json_encode(['error' => 'Failed to book appointment. Please try again.']);
        }
    } catch (Exception $e) {
        error_log("Error booking appointment: " . $e->getMessage());
        echo json_encode(['error' => 'An error occurred while booking your appointment. Please try again.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}

$conn->close();
?> 