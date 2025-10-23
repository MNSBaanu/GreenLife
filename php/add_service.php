<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $service_name = trim($_POST['service_name']);
    $category = trim($_POST['category']);
    $summary = trim($_POST['summary']);
    $benefits = trim($_POST['benefits']);
    $booking_text = trim($_POST['booking_text'] ?? 'Book Session');

    // Simple validation
    if (empty($service_name) || empty($category) || empty($summary) || empty($benefits)) {
        header("Location: admin_dashboard.php?error=missing_fields");
        exit();
    }

    // Handle image upload
    $image_name = 'default-service.jpg'; // Default image
    if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/services/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_info = pathinfo($_FILES['service_image']['name']);
        $extension = strtolower($file_info['extension']);
        
        // Check if file is an image
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($extension, $allowed_extensions)) {
            $new_filename = 'service_' . time() . '_' . str_replace(' ', '_', $service_name) . '.' . $extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['service_image']['tmp_name'], $upload_path)) {
                $image_name = $new_filename;
            }
        }
    }

    // Insert new service
    $insert_query = "INSERT INTO services (name, category, summary, benefits, image, booking_text, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";
    
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssssss", $service_name, $category, $summary, $benefits, $image_name, $booking_text);
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=service_added&service_name=" . urlencode($service_name));
        exit();
    } else {
        header("Location: admin_dashboard.php?error=creation_failed");
        exit();
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
?> 