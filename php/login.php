<?php
session_start();
include("dbconnect.php");
error_reporting(0);

if (isset($_POST["email"], $_POST["password"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Fetch user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Check if user exists and password matches
    if ($row && $password === $row['password']) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'] ?? $row['fullname'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['fullname'] = $row['name'];

        // Redirect based on role from DB
        if ($row['role'] === 'client') {
            header("Location: client_dashboard.php");
        } elseif ($row['role'] === 'therapist') {
            header("Location: therapist_dashboard.php");
        } elseif ($row['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            $_SESSION['login_error'] = "Unknown role.";
            header("Location: ../html/login.html");
        }
        exit();
    } else {
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: ../html/login.html");
        exit();
    }

    $stmt->close();
} else {
    $_SESSION['login_error'] = "Please enter email and password.";
    header("Location: ../html/login.html");
    exit();
}

$conn->close();
?>
