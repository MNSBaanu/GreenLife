<?php
session_start();
require_once 'dbconnect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $experience = intval($_POST['experience'] ?? 0);
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($fullname) || empty($username) || empty($email) || empty($phone) || empty($specialization) || empty($password)) {
        header('Location: admin_dashboard.php?error=missing_fields');
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: admin_dashboard.php?error=invalid_email');
        exit();
    }

    // Check if username or email already exists
    $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        header('Location: admin_dashboard.php?error=user_exists');
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new therapist into users table first
    $insert_query = "INSERT INTO users (fullname, username, email, phone, password, role, created_at) VALUES (?, ?, ?, ?, ?, 'therapist', NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("sssss", $fullname, $username, $email, $phone, $hashed_password);

    if ($insert_stmt->execute()) {
        $therapist_id = $conn->insert_id;
        
        // Insert detailed therapist info into therapists table
        $therapist_query = "INSERT INTO therapists (user_id, fullname, email, phone, specialization, experience_years, availability, working_days, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, 'Available for consultations', 'Monday - Friday', '09:00:00', '17:00:00')";
        $therapist_stmt = $conn->prepare($therapist_query);
        $therapist_stmt->bind_param("issssi", $therapist_id, $fullname, $email, $phone, $specialization, $experience);
        $therapist_stmt->execute();

        header('Location: admin_dashboard.php?success=therapist_added&therapist_name=' . urlencode($fullname));
        exit();
    } else {
        header('Location: admin_dashboard.php?error=database_error');
        exit();
    }
} else {
    // If not POST request, redirect to dashboard
    header('Location: admin_dashboard.php');
    exit();
}
?> 