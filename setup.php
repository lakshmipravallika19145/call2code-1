<?php
// Setup script for Adventure Hunt Scavenger Hunt App
// Run this script once to configure your environment

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Adventure Hunt - Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: green; background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffe8e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: orange; background: #fff8e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e8f0ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .step { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .api-test { border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🚀 Adventure Hunt Setup</h1>
    <p>This script will help you configure your Adventure Hunt application.</p>";

// Step 1: Check PHP version
echo "<div class='step'>
    <h3>Step 1: PHP Version Check</h3>";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "<div class='success'>✅ PHP version " . PHP_VERSION . " is compatible</div>";
} else {
    echo "<div class='error'>❌ PHP version " . PHP_VERSION . " is too old. Please upgrade to PHP 7.4 or higher.</div>";
}
echo "</div>";

// Step 2: Check required extensions
echo "<div class='step'>
    <h3>Step 2: Required Extensions</h3>";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'curl'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='success'>✅ $ext extension is loaded</div>";
    } else {
        echo "<div class='error'>❌ $ext extension is missing</div>";
        $missing_extensions[] = $ext;
    }
}
echo "</div>";

// Step 3: Database connection test
echo "<div class='step'>
    <h3>Step 3: Database Connection Test</h3>";

if (file_exists('config/database.php')) {
    require_once 'config/database.php';
    
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<div class='success'>✅ Database connection successful</div>";
        
        // Check if tables exist
        $tables = ['users', 'challenges', 'user_progress', 'game_sessions', 'offline_queue'];
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<div class='success'>✅ Table '$table' exists</div>";
            } else {
                echo "<div class='error'>❌ Table '$table' is missing</div>";
                $missing_tables[] = $table;
            }
        }
        
        if (!empty($missing_tables)) {
            echo "<div class='warning'>⚠️ Some tables are missing. Please import the database schema from database/schema.sql</div>";
        }
        
    } catch(PDOException $e) {
        echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
        echo "<div class='info'>💡 Please check your database configuration in config/database.php</div>";
    }
} else {
    echo "<div class='error'>❌ config/database.php file not found</div>";
}
echo "</div>";

// Step 4: API Configuration Check
echo "<div class='step'>
    <h3>Step 4: API Configuration Check</h3>";

if (defined('OPENWEATHER_API_KEY') && OPENWEATHER_API_KEY !== 'YOUR_OPENWEATHER_API_KEY') {
    echo "<div class='success'>✅ OpenWeatherMap API key is configured</div>";
} else {
    echo "<div class='warning'>⚠️ OpenWeatherMap API key needs to be configured</div>";
}

if (defined('GOOGLE_MAPS_API_KEY') && GOOGLE_MAPS_API_KEY !== 'YOUR_GOOGLE_MAPS_API_KEY') {
    echo "<div class='success'>✅ Google Maps API key is configured</div>";
} else {
    echo "<div class='warning'>⚠️ Google Maps API key needs to be configured</div>";
}

if (defined('NEWS_API_KEY') && NEWS_API_KEY !== 'YOUR_NEWS_API_KEY') {
    echo "<div class='success'>✅ NewsAPI key is configured</div>";
} else {
    echo "<div class='warning'>⚠️ NewsAPI key needs to be configured</div>";
}

if (defined('GIPHY_API_KEY') && GIPHY_API_KEY !== 'YOUR_GIPHY_API_KEY') {
    echo "<div class='success'>✅ Giphy API key is configured</div>";
} else {
    echo "<div class='warning'>⚠️ Giphy API key needs to be configured</div>";
}
echo "</div>";

// Step 5: File permissions check
echo "<div class='step'>
    <h3>Step 5: File Permissions Check</h3>";

$directories = ['config', 'api', 'assets', 'database'];
foreach ($directories as $dir) {
    if (is_dir($dir) && is_readable($dir)) {
        echo "<div class='success'>✅ Directory '$dir' is readable</div>";
    } else {
        echo "<div class='error'>❌ Directory '$dir' is not accessible</div>";
    }
}
echo "</div>";

// Step 6: API connectivity test
echo "<div class='step'>
    <h3>Step 6: API Connectivity Test</h3>";

// Test PokeAPI (no key required)
echo "<div class='api-test'>
    <h4>PokeAPI Test</h4>";
$pokeapi_url = "https://pokeapi.co/api/v2/pokemon/1/";
$pokeapi_response = @file_get_contents($pokeapi_url);
if ($pokeapi_response !== false) {
    echo "<div class='success'>✅ PokeAPI is accessible</div>";
} else {
    echo "<div class='error'>❌ PokeAPI is not accessible</div>";
}
echo "</div>";

// Test other APIs if keys are configured
if (defined('OPENWEATHER_API_KEY') && OPENWEATHER_API_KEY !== 'YOUR_OPENWEATHER_API_KEY') {
    echo "<div class='api-test'>
        <h4>OpenWeatherMap API Test</h4>";
    $weather_url = "http://api.openweathermap.org/data/2.5/weather?q=London&appid=" . OPENWEATHER_API_KEY;
    $weather_response = @file_get_contents($weather_url);
    if ($weather_response !== false) {
        $weather_data = json_decode($weather_response, true);
        if (isset($weather_data['main'])) {
            echo "<div class='success'>✅ OpenWeatherMap API is working</div>";
        } else {
            echo "<div class='error'>❌ OpenWeatherMap API returned an error</div>";
        }
    } else {
        echo "<div class='error'>❌ OpenWeatherMap API is not accessible</div>";
    }
    echo "</div>";
}

echo "</div>";

// Step 7: Summary and next steps
echo "<div class='step'>
    <h3>Step 7: Setup Summary</h3>";

if (empty($missing_extensions) && empty($missing_tables)) {
    echo "<div class='success'>
        <h4>🎉 Setup Complete!</h4>
        <p>Your Adventure Hunt application is ready to use. You can now:</p>
        <ul>
            <li><a href='index.php'>Visit the main page</a></li>
            <li><a href='register.php'>Create a new account</a></li>
            <li><a href='login.php'>Login to existing account</a></li>
        </ul>
    </div>";
} else {
    echo "<div class='warning'>
        <h4>⚠️ Setup Incomplete</h4>
        <p>Please resolve the issues above before using the application.</p>
    </div>";
}

echo "<div class='info'>
    <h4>📋 Next Steps:</h4>
    <ol>
        <li>Configure your API keys in config/database.php</li>
        <li>Import the database schema if tables are missing</li>
        <li>Test the application functionality</li>
        <li>Customize the challenges and features as needed</li>
    </ol>
</div>";

echo "</div>";

// Security note
echo "<div class='warning'>
    <h4>🔒 Security Note</h4>
    <p>For production use, please:</p>
    <ul>
        <li>Delete this setup.php file after configuration</li>
        <li>Use HTTPS for all connections</li>
        <li>Set appropriate file permissions</li>
        <li>Configure proper error logging</li>
    </ul>
</div>";

echo "</body></html>";
?> 