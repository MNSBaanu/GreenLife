<?php
require_once 'dbconnect.php';

// Function to get all articles
function getArticles($conn) {
    $sql = "SELECT * FROM articles WHERE is_active = 1 ORDER BY publish_date DESC";
    $result = $conn->query($sql);
    $articles = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
    }
    
    return $articles;
}

// Function to get all videos
function getVideos($conn) {
    $sql = "SELECT * FROM videos WHERE is_active = 1 ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $videos = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $videos[] = $row;
        }
    }
    
    return $videos;
}

// Function to get all health tips
function getHealthTips($conn) {
    $sql = "SELECT * FROM health_tips WHERE is_active = 1 ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $tips = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $tips[] = $row;
        }
    }
    
    return $tips;
}

// Function to search resources
function searchResources($conn, $searchTerm) {
    $searchTerm = "%$searchTerm%";
    
    // Search articles
    $sql = "SELECT 'article' as type, id, title, summary, category, image, read_time, publish_date 
            FROM articles 
            WHERE is_active = 1 AND (title LIKE ? OR summary LIKE ? OR category LIKE ?)
            ORDER BY publish_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Search videos
    $sql = "SELECT 'video' as type, id, title, description, category, youtube_id, youtube_url 
            FROM videos 
            WHERE is_active = 1 AND (title LIKE ? OR description LIKE ? OR category LIKE ?)
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $videos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    return array_merge($articles, $videos);
}

// Function to filter resources by category
function filterResourcesByCategory($conn, $category) {
    $category = "%$category%";
    
    // Filter articles
    $sql = "SELECT 'article' as type, id, title, summary, category, image, read_time, publish_date 
            FROM articles 
            WHERE is_active = 1 AND category LIKE ?
            ORDER BY publish_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Filter videos
    $sql = "SELECT 'video' as type, id, title, description, category, youtube_id, youtube_url 
            FROM videos 
            WHERE is_active = 1 AND category LIKE ?
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $videos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    return array_merge($articles, $videos);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'search':
            $searchTerm = $_POST['search_term'] ?? '';
            $results = searchResources($conn, $searchTerm);
            echo json_encode($results);
            break;
            
        case 'filter':
            $category = $_POST['category'] ?? '';
            $results = filterResourcesByCategory($conn, $category);
            echo json_encode($results);
            break;
            
        default:
            // Return all resources
            $data = [
                'articles' => getArticles($conn),
                'videos' => getVideos($conn),
                'health_tips' => getHealthTips($conn)
            ];
            echo json_encode($data);
    }
} else {
    // Return all resources for initial page load
    $data = [
        'articles' => getArticles($conn),
        'videos' => getVideos($conn),
        'health_tips' => getHealthTips($conn)
    ];
    echo json_encode($data);
}

$conn->close();
?> 