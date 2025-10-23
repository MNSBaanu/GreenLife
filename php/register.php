<?php
session_start();
session_regenerate_id(true);
include('dbconnect.php');
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $role = trim($_POST['role']);

    // Validation
    if (empty($fullname) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)) {
        $_SESSION['register_error'] = "Please fill in all fields";
        header("Location: ../html/login.html?tab=register");
        exit();
    }

    if ($password !== $confirmPassword) {
        $_SESSION['register_error'] = "Passwords do not match";
        header("Location: ../html/login.html?tab=register");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['register_error'] = "Password must be at least 6 characters";
        header("Location: ../html/login.html?tab=register");
        exit();
    }

    // Check if email exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['register_error'] = "Email already registered";
        header("Location: ../html/login.html?tab=register");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $username = strtolower(str_replace(' ', '', $fullname));

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $username, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        $_SESSION['register_success'] = "Registration successful! Please login.";
        header("Location: ../html/login.html");
        exit();
    } else {
        $_SESSION['register_error'] = "Registration failed: " . $stmt->error;
        header("Location: ../html/login.html?tab=register");
        exit();
    }

    $stmt->close();
    $check->close();
    $conn->close();
}
