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
    $service_id = $_POST['service_id'] ?? '';
    $session_date = $_POST['session_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $duration = $_POST['duration'] ?? 60;
    $notes = $_POST['notes'] ?? '';
    $therapist_id = $_SESSION['user_id'];
    
    if (empty($client_id) || empty($service_id) || empty($session_date) || empty($start_time)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: therapist_dashboard.php#client-management");
        exit();
    }
    
    // Calculate end time based on duration
    $start_datetime = new DateTime($start_time);
    $end_datetime = clone $start_datetime;
    $end_datetime->add(new DateInterval('PT' . $duration . 'M'));
    $end_time = $end_datetime->format('H:i:s');
    
    try {
        // Check if therapist is available at this time
        $day_of_week = date('l', strtotime($session_date));
        $availability_query = "SELECT * FROM therapist_availability WHERE therapist_id = ? AND day_of_week = ? AND start_time <= ? AND end_time >= ? AND is_available = 1";
        $availability_stmt = $conn->prepare($availability_query);
        $availability_stmt->bind_param("isss", $therapist_id, $day_of_week, $start_time, $end_time);
        $availability_stmt->execute();
        $availability_result = $availability_stmt->get_result();
        
        if ($availability_result->num_rows === 0) {
            $_SESSION['error'] = "You are not available at the selected time. Please choose another time.";
            header("Location: therapist_dashboard.php#client-management");
            exit();
        }
        
        // Check for conflicts with existing appointments
        $conflict_query = "SELECT * FROM appointments WHERE therapist_id = ? AND appointment_date = ? AND 
                          ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))";
        $conflict_stmt = $conn->prepare($conflict_query);
        $conflict_stmt->bind_param("isssssss", $therapist_id, $session_date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time);
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();
        
        if ($conflict_result->num_rows > 0) {
            $_SESSION['error'] = "There is a scheduling conflict at the selected time. Please choose another time.";
            header("Location: therapist_dashboard.php#client-management");
            exit();
        }
        
        // Create the appointment
        $appointment_query = "INSERT INTO appointments (client_id, therapist_id, service_id, appointment_date, start_time, end_time, duration, status, notes) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', ?)";
        $appointment_stmt = $conn->prepare($appointment_query);
        $appointment_stmt->bind_param("iiisssis", $client_id, $therapist_id, $service_id, $session_date, $start_time, $end_time, $duration, $notes);
        
        if ($appointment_stmt->execute()) {
            $appointment_id = $conn->insert_id;
            
            // Create session record
            $session_query = "INSERT INTO sessions (appointment_id, therapist_id, client_id, service_id, session_date, start_time, end_time, duration, status) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')";
            $session_stmt = $conn->prepare($session_query);
            $session_stmt->bind_param("iiiisssi", $appointment_id, $therapist_id, $client_id, $service_id, $session_date, $start_time, $end_time, $duration);
            $session_stmt->execute();
            
            // Create notification for client
            $notification_query = "INSERT INTO notifications (user_id, title, message, type, related_id, related_type) VALUES (?, ?, ?, 'appointment', ?, 'appointment')";
            $notification_stmt = $conn->prepare($notification_query);
            $title = "New Session Scheduled";
            $message = "A new therapy session has been scheduled for you.";
            $notification_stmt->bind_param("issi", $client_id, $title, $message, $appointment_id);
            $notification_stmt->execute();
            
            $_SESSION['success'] = "Session scheduled successfully!";
        } else {
            $_SESSION['error'] = "Failed to schedule session. Please try again.";
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