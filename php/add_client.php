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
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    // Simple validation
    if (empty($fullname) || empty($username) || empty($email) || empty($phone) || empty($password)) {
        header("Location: admin_dashboard.php?error=missing_fields");
        exit();
    }

    // Check if username or email already exists
    $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: admin_dashboard.php?error=user_exists");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new client
    $insert_query = "INSERT INTO users (fullname, username, email, phone, password, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, 'client', 1, NOW())";
    
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sssss", $fullname, $username, $email, $phone, $hashed_password);
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=client_added&client_name=" . urlencode($fullname));
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