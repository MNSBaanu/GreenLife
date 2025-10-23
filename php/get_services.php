<?php
// Temporarily comment out session check for testing
// session_start();
require_once 'dbconnect.php';

// Temporarily comment out authentication check
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     http_response_code(401);
//     echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
//     exit;
// }

try {
    // Fetch all active services from database
    $query = "SELECT id, name, category, summary, image, is_active, created_at FROM services WHERE is_active = 1 ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        // Get client count for this service (you might want to add a relationship table later)
        $clientCount = 0; // Placeholder for now
        
        $services[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'category' => $row['category'],
            'summary' => $row['summary'],
            'image' => $row['image'],
            'is_active' => $row['is_active'],
            'client_count' => $clientCount,
            'price' => getServicePrice($row['name']), // Helper function to get price
            'icon' => getServiceIcon($row['name']) // Helper function to get icon
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'services' => $services
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();

// Helper function to get service price (you can modify this based on your pricing structure)
function getServicePrice($serviceName) {
    $prices = [
        'Ayurvedic Therapy' => '$80/session',
        'Yoga & Meditation' => '$60/session',
        'Nutrition & Diet' => '$100/consultation',
        'Massage Therapy' => '$70/session',
        'Aromatic Therapy' => '$50/session',
        'Physiotherapy' => '$90/session'
    ];
    
    return $prices[$serviceName] ?? '$75/session';
}

// Helper function to get service icon
function getServiceIcon($serviceName) {
    $icons = [
        'Ayurvedic Therapy' => 'fas fa-leaf',
        'Yoga & Meditation' => 'fas fa-om',
        'Nutrition & Diet' => 'fas fa-apple-alt',
        'Massage Therapy' => 'fas fa-hands',
        'Aromatic Therapy' => 'fas fa-spa',
        'Physiotherapy' => 'fas fa-walking'
    ];
    
    return $icons[$serviceName] ?? 'fas fa-heart';
}
?> 