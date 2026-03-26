<?php
// Database Connection Test
// Delete this file after testing!

echo "<h2>Testing Database Connection</h2>";

// Show server info
echo "<p><strong>Server:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Test if config.php exists
if (file_exists('php/config.php')) {
    echo "<p style='color: green;'>✓ config.php file exists</p>";
    
    // Include and test connection
    require_once 'php/config.php';
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>✗ Connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Database connected successfully!</p>";
        echo "<p><strong>Database:</strong> " . $dbname . "</p>";
        
        // Test if users table exists
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        if ($result->num_rows > 0) {
            echo "<p style='color: green;'>✓ 'users' table exists</p>";
            
            // Count users
            $count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc();
            echo "<p><strong>Total users:</strong> " . $count['total'] . "</p>";
        } else {
            echo "<p style='color: red;'>✗ 'users' table not found</p>";
        }
    }
} else {
    echo "<p style='color: red;'>✗ config.php file not found</p>";
    echo "<p>Current directory: " . getcwd() . "</p>";
}
?>
