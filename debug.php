<?php
// Debug script to identify PHP errors
// This will help us find what's causing HTML output instead of JSON

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP Debug Information</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    .test { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
</style>";

// Test 1: Check if config files exist
echo "<div class='test'>";
echo "<h3>1. Checking Config Files...</h3>";
$configFiles = [
    'config/database.php',
    'config/api_keys.php'
];

foreach ($configFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✓ $file exists</p>";
    } else {
        echo "<p class='error'>✗ $file missing</p>";
    }
}
echo "</div>";

// Test 2: Check database connection
echo "<div class='test'>";
echo "<h3>2. Testing Database Connection...</h3>";
try {
    require_once 'config/database.php';
    echo "<p class='info'>✓ Config loaded successfully</p>";
    
    $pdo = getDBConnection();
    echo "<p class='success'>✓ Database connection successful</p>";
    
    // Test if database exists
    $stmt = $pdo->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    echo "<p class='info'>Current database: $dbName</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p class='error'>Error details: " . print_r($e, true) . "</p>";
}
echo "</div>";

// Test 3: Check if tables exist
echo "<div class='test'>";
echo "<h3>3. Checking Database Tables...</h3>";
try {
    $pdo = getDBConnection();
    $tables = ['users', 'challenges', 'user_progress'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✓ Table '$table' exists</p>";
            
            // Count rows
            $countStmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $countStmt->fetchColumn();
            echo "<p class='info'>  - Rows in $table: $count</p>";
        } else {
            echo "<p class='error'>✗ Table '$table' does not exist</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error checking tables: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 4: Check API files
echo "<div class='test'>";
echo "<h3>4. Checking API Files...</h3>";
$apiFiles = [
    'api/challenges.php',
    'api/weather_simple.php',
    'api/pokemon.php',
    'api/news.php',
    'api/giphy.php',
    'api/joke.php',
    'api/coingecko.php'
];

foreach ($apiFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✓ $file exists</p>";
        
        // Check for syntax errors
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<p class='success'>  - Syntax OK</p>";
        } else {
            echo "<p class='error'>  - Syntax error: $output</p>";
        }
    } else {
        echo "<p class='error'>✗ $file missing</p>";
    }
}
echo "</div>";

// Test 5: Test individual API endpoints
echo "<div class='test'>";
echo "<h3>5. Testing API Endpoints...</h3>";

$endpoints = [
    'challenges' => 'api/challenges.php?action=all',
    'weather_simple' => 'api/weather_simple.php?action=get_weather&lat=40.7128&lon=-74.0060',
    'pokemon' => 'api/pokemon.php?action=random'
];

foreach ($endpoints as $name => $endpoint) {
    echo "<h4>Testing $name...</h4>";
    
    // Capture output
    ob_start();
    try {
        include $endpoint;
        $output = ob_get_clean();
        
        // Check if output is valid JSON
        $json = json_decode($output, true);
        if ($json !== null) {
            echo "<p class='success'>✓ $name returns valid JSON</p>";
            if (isset($json['success'])) {
                echo "<p class='info'>  - Success: " . ($json['success'] ? 'true' : 'false') . "</p>";
                if (!$json['success'] && isset($json['error'])) {
                    echo "<p class='error'>  - Error: " . $json['error'] . "</p>";
                }
            }
        } else {
            echo "<p class='error'>✗ $name returns invalid JSON</p>";
            echo "<p class='error'>Raw output: " . htmlspecialchars(substr($output, 0, 200)) . "...</p>";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p class='error'>✗ $name threw exception: " . $e->getMessage() . "</p>";
    }
}
echo "</div>";

// Test 6: Check PHP configuration
echo "<div class='test'>";
echo "<h3>6. PHP Configuration...</h3>";
echo "<p class='info'>PHP Version: " . phpversion() . "</p>";
echo "<p class='info'>Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "</p>";
echo "<p class='info'>Error Reporting: " . ini_get('error_reporting') . "</p>";
echo "<p class='info'>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p class='info'>Max Execution Time: " . ini_get('max_execution_time') . "</p>";
echo "</div>";

echo "<h2>Next Steps</h2>";
echo "<p>Based on the results above:</p>";
echo "<ol>";
echo "<li>If database connection fails: Check your database credentials in config/database.php</li>";
echo "<li>If tables don't exist: Import the database schema from database/schema.sql</li>";
echo "<li>If API files have syntax errors: Fix the PHP syntax issues</li>";
echo "<li>If JSON is invalid: Check for PHP warnings/errors in the API files</li>";
echo "</ol>";

echo "<p><strong>Quick Fix:</strong></p>";
echo "<ol>";
echo "<li><a href='setup.php'>Run the setup script</a></li>";
echo "<li><a href='test_api.php'>Run the API test</a></li>";
echo "<li>Check the browser console (F12) for JavaScript errors</li>";
echo "</ol>";
?> 