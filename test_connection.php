<?php
// Database Connection Test
// Delete this file after testing!

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Database Connection</h2>";

// Show server info
echo "<p><strong>Server:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Test if config.php exists
if (file_exists('php/config.php')) {
    echo "<p style='color: green;'>✓ config.php file exists</p>";
    
    // Manually test connection with credentials
    $host = "sql305.infinityfree.com";
    $user = "if0_41483792";
    $pass = "zsd6Ne19U3ScQfq";
    
    echo "<p><strong>Attempting connection to:</strong> $host</p>";
    echo "<p><strong>Username:</strong> $user</p>";
    
    // Try different database names
    $possible_dbs = [
        "if0_41483792_greenlife_wellness",
        "if0_41483792_greenlife",
        "if0_41483792_wellness",
        "if0_41483792_db"
    ];
    
    foreach ($possible_dbs as $dbname) {
        echo "<hr><p>Testing database: <strong>$dbname</strong></p>";
        
        $conn = new mysqli($host, $user, $pass, $dbname);
        
        if ($conn->connect_error) {
            echo "<p style='color: red;'>✗ Failed: " . $conn->connect_error . "</p>";
        } else {
            echo "<p style='color: green;'>✓ Connected successfully!</p>";
            
            // List all tables
            $result = $conn->query("SHOW TABLES");
            if ($result) {
                echo "<p><strong>Tables found:</strong></p><ul>";
                while ($row = $result->fetch_array()) {
                    echo "<li>" . $row[0] . "</li>";
                }
                echo "</ul>";
            }
            
            $conn->close();
            break;
        }
    }
    
} else {
    echo "<p style='color: red;'>✗ config.php file not found</p>";
    echo "<p>Current directory: " . getcwd() . "</p>";
}
?>
