<?php
// Quick test to verify JSON fixes
echo "<h1>Quick JSON Test</h1>";
echo "<p>Testing if APIs return valid JSON instead of HTML errors...</p>";

// Test challenges API
$challengesUrl = 'http://localhost/call2code%201/api/challenges.php?action=all';
$response = @file_get_contents($challengesUrl);

if ($response === false) {
    echo "<p style='color: red;'>❌ Failed to fetch challenges API</p>";
} elseif (strpos($response, '<br />') !== false || strpos($response, '<html') !== false) {
    echo "<p style='color: red;'>❌ Challenges API still returning HTML</p>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 200)) . "...</pre>";
} else {
    $json = json_decode($response, true);
    if ($json === null) {
        echo "<p style='color: red;'>❌ Challenges API returning invalid JSON</p>";
    } else {
        echo "<p style='color: green;'>✅ Challenges API working correctly</p>";
    }
}

// Test weather API
$weatherUrl = 'http://localhost/call2code%201/api/weather_simple.php?action=get_weather&lat=40.7128&lon=-74.0060';
$response = @file_get_contents($weatherUrl);

if ($response === false) {
    echo "<p style='color: red;'>❌ Failed to fetch weather API</p>";
} elseif (strpos($response, '<br />') !== false || strpos($response, '<html') !== false) {
    echo "<p style='color: red;'>❌ Weather API still returning HTML</p>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 200)) . "...</pre>";
} else {
    $json = json_decode($response, true);
    if ($json === null) {
        echo "<p style='color: red;'>❌ Weather API returning invalid JSON</p>";
    } else {
        echo "<p style='color: green;'>✅ Weather API working correctly</p>";
    }
}

echo "<hr>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>If you see ❌ errors, check your database connection</li>";
echo "<li>If you see ✅ success, your app should work now</li>";
echo "<li><a href='test_json.php'>Run full API test</a></li>";
echo "<li><a href='dashboard.php'>Test the dashboard</a></li>";
echo "</ol>";
?> 