<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get available therapists for a specific service
if (isset($_GET['action']) && $_GET['action'] === 'get_therapists' && isset($_GET['service_id'])) {
    $service_id = intval($_GET['service_id']);
    
    $query = "SELECT DISTINCT u.id, u.fullname, u.profile_image 
              FROM users u 
              INNER JOIN therapist_services ts ON u.id = ts.therapist_id 
              WHERE u.role = 'therapist' 
              AND u.is_active = 1 
              AND ts.service_id = ? 
              AND ts.is_active = 1 
              ORDER BY LOWER(TRIM(u.fullname)), u.id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $therapists = [];
    while ($row = $result->fetch_assoc()) {
        $therapists[] = $row;
    }
    
    echo json_encode(['therapists' => $therapists]);
    exit();
}

// Get available time slots for a specific therapist and date
if (isset($_GET['action']) && $_GET['action'] === 'get_availability' && isset($_GET['therapist_id']) && isset($_GET['date'])) {
    $therapist_id = intval($_GET['therapist_id']);
    $date = $_GET['date'];
    
    // Get day of week
    $day_of_week = date('l', strtotime($date));
    
    // Get therapist availability for this day
    $availability_query = "SELECT start_time, end_time 
                          FROM therapist_availability 
                          WHERE therapist_id = ? 
                          AND day_of_week = ? 
                          AND is_available = 1";
    
    $stmt = $conn->prepare($availability_query);
    $stmt->bind_param("is", $therapist_id, $day_of_week);
    $stmt->execute();
    $availability_result = $stmt->get_result();
    
    if ($availability_result->num_rows === 0) {
        echo json_encode(['available_slots' => []]);
        exit();
    }
    
    $availability = $availability_result->fetch_assoc();
    $start_time = $availability['start_time'];
    $end_time = $availability['end_time'];
    
    // Get existing appointments for this therapist on this date
    $appointments_query = "SELECT start_time, end_time 
                          FROM appointments 
                          WHERE therapist_id = ? 
                          AND appointment_date = ? 
                          AND status IN ('pending', 'confirmed')";
    
    $stmt = $conn->prepare($appointments_query);
    $stmt->bind_param("is", $therapist_id, $date);
    $stmt->execute();
    $appointments_result = $stmt->get_result();
    
    $booked_slots = [];
    while ($appointment = $appointments_result->fetch_assoc()) {
        $booked_slots[] = [
            'start' => $appointment['start_time'],
            'end' => $appointment['end_time']
        ];
    }
    
    // Generate available time slots (60-minute intervals)
    $available_slots = [];
    $current_time = strtotime($start_time);
    $end_timestamp = strtotime($end_time);
    
    while ($current_time < $end_timestamp) {
        $slot_start = date('H:i', $current_time);
        $slot_end = date('H:i', $current_time + 3600); // 1 hour later
        
        // Check if this slot conflicts with any booked appointments
        $is_available = true;
        foreach ($booked_slots as $booked) {
            $booked_start = strtotime($booked['start']);
            $booked_end = strtotime($booked['end']);
            $slot_start_time = strtotime($slot_start);
            $slot_end_time = strtotime($slot_end);
            
            // Check for overlap
            if (($slot_start_time < $booked_end) && ($slot_end_time > $booked_start)) {
                $is_available = false;
                break;
            }
        }
        
        if ($is_available) {
            $available_slots[] = [
                'time' => $slot_start,
                'display' => date('g:i A', strtotime($slot_start))
            ];
        }
        
        $current_time += 3600; // Move to next hour
    }
    
    echo json_encode(['available_slots' => $available_slots]);
    exit();
}

// Invalid request
http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
?> 