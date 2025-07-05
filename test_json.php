<?php
// Simple JSON Test - Check if APIs return valid JSON
echo "<h1>JSON API Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .test { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
    pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
</style>";

function testAPI($name, $url) {
    echo "<div class='test'>";
    echo "<h3>Testing $name</h3>";
    echo "<p>URL: <code>$url</code></p>";
    
    $response = @file_get_contents($url);
    
    if ($response === false) {
        echo "<p class='error'>✗ Failed to fetch response</p>";
        echo "</div>";
        return false;
    }
    
    // Check if response starts with HTML (error)
    if (strpos($response, '<!DOCTYPE') === 0 || strpos($response, '<html') === 0 || strpos($response, '<br />') !== false) {
        echo "<p class='error'>✗ Response contains HTML (PHP error)</p>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
        echo "</div>";
        return false;
    }
    
    // Try to decode JSON
    $json = json_decode($response, true);
    if ($json === null) {
        echo "<p class='error'>✗ Invalid JSON response</p>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 200)) . "...</pre>";
        echo "</div>";
        return false;
    }
    
    echo "<p class='success'>✓ Valid JSON response</p>";
    echo "<p>Success: " . ($json['success'] ? 'true' : 'false') . "</p>";
    
    if (!$json['success'] && isset($json['error'])) {
        echo "<p class='error'>Error: " . htmlspecialchars($json['error']) . "</p>";
    }
    
    if ($json['success'] && isset($json['data'])) {
        echo "<p>Data received: " . (is_array($json['data']) ? count($json['data']) . ' items' : 'Available') . "</p>";
    }
    
    echo "</div>";
    return $json['success'];
}

// Test the main APIs
$tests = [
    'Challenges API' => 'http://localhost/call2code%201/api/challenges.php?action=all',
    'Simple Weather API' => 'http://localhost/call2code%201/api/weather_simple.php?action=get_weather&lat=40.7128&lon=-74.0060',
    'Pokemon API' => 'http://localhost/call2code%201/api/pokemon.php?action=random',
    'News API' => 'http://localhost/call2code%201/api/news.php?action=latest&pageSize=1',
    'Giphy API' => 'http://localhost/call2code%201/api/giphy.php?action=random&tag=celebration',
    'Joke API' => 'http://localhost/call2code%201/api/joke.php?action=random',
    'CoinGecko API' => 'http://localhost/call2code%201/api/coingecko.php?action=top_coins&limit=1'
];

$passed = 0;
$total = count($tests);

foreach ($tests as $name => $url) {
    if (testAPI($name, $url)) {
        $passed++;
    }
}

echo "<h2>Test Results</h2>";
echo "<p>Passed: <span class='success'>$passed</span> / <span class='info'>$total</span></p>";

if ($passed === $total) {
    echo "<p class='success'>🎉 All APIs are working correctly!</p>";
    echo "<p>You can now:</p>";
    echo "<ol>";
    echo "<li><a href='register.php'>Register a new user</a></li>";
    echo "<li><a href='dashboard.php'>Test the dashboard</a></li>";
    echo "<li>Start challenges and verify they work</li>";
    echo "</ol>";
} else {
    echo "<p class='error'>⚠️ Some APIs are still not working correctly.</p>";
    echo "<p>Please check:</p>";
    echo "<ol>";
    echo "<li>Database connection in config/database.php</li>";
    echo "<li>Database tables exist (import database/schema.sql)</li>";
    echo "<li>XAMPP is running (Apache + MySQL)</li>";
    echo "<li>File permissions are correct</li>";
    echo "</ol>";
    echo "<p><a href='debug.php'>Run detailed debug</a> to see specific issues.</p>";
}
?> 