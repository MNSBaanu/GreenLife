<?php
echo "<h2>Debug Information</h2>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script Filename:</strong> " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";

echo "<h3>File Checks:</h3>";
$files = [
    'html/login.php',
    'php/login.php',
    'php/config.php',
    'php/dbconnect.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file exists</p>";
    } else {
        echo "<p style='color: red;'>✗ $file NOT found</p>";
    }
}

echo "<h3>Test Links:</h3>";
echo "<p><a href='html/index.html'>Go to html/index.html</a></p>";
echo "<p><a href='html/login.php'>Go to html/login.php</a></p>";
echo "<p><a href='php/login.php'>Go to php/login.php (should not work - it's a processor)</a></p>";
?>
