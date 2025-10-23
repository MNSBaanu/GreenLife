<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Basic validation
    $errors = [];
    
    if (empty($fullname)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if username already exists (excluding current user)
    if (!empty($username)) {
        $check_username = "SELECT id FROM users WHERE username = ? AND id != ?";
        $stmt_check = $conn->prepare($check_username);
        $stmt_check->bind_param("si", $username, $user_id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $errors[] = "Username already exists";
        }
    }
    
    // Check if email already exists (excluding current user)
    if (!empty($email)) {
        $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt_check = $conn->prepare($check_email);
        $stmt_check->bind_param("si", $email, $user_id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    // If no errors, update the database
    if (empty($errors)) {
        try {
            $update_query = "UPDATE users SET fullname = ?, username = ?, email = ?, phone = ? WHERE id = ?";
            $stmt_update = $conn->prepare($update_query);
            
            if (!$stmt_update) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt_update->bind_param("ssssi", $fullname, $username, $email, $phone, $user_id);
            
            if ($stmt_update->execute()) {
                if ($stmt_update->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No changes were made to your profile.']);
                }
            } else {
                throw new Exception("Execute failed: " . $stmt_update->error);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => implode(", ", $errors)]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?> 