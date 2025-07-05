<?php
// Simple API Test Script
// Visit this file in your browser to test all API endpoints

echo "<h1>API Test Results</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .test { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
</style>";

// Test database connection
echo "<div class='test'>";
echo "<h3>Testing Database Connection...</h3>";
try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    echo "<p class='success'>✓ Database connection successful</p>";
    
    // Test if tables exist
    $tables = ['users', 'challenges', 'user_progress'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✓ Table '$table' exists</p>";
        } else {
            echo "<p class='error'>✗ Table '$table' does not exist</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test Challenges API
echo "<div class='test'>";
echo "<h3>Testing Challenges API...</h3>";
$challengesUrl = 'http://localhost/call2code%201/api/challenges.php?action=all';
$challengesResponse = @file_get_contents($challengesUrl);
if ($challengesResponse !== false) {
    $challengesData = json_decode($challengesResponse, true);
    if ($challengesData && isset($challengesData['success'])) {
        if ($challengesData['success']) {
            echo "<p class='success'>✓ Challenges API working</p>";
            echo "<p>Found " . count($challengesData['data']) . " challenges</p>";
        } else {
            echo "<p class='error'>✗ Challenges API error: " . $challengesData['error'] . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Challenges API returned invalid JSON</p>";
    }
} else {
    echo "<p class='error'>✗ Challenges API not accessible</p>";
}
echo "</div>";

// Test Simple Weather API
echo "<div class='test'>";
echo "<h3>Testing Simple Weather API...</h3>";
$weatherUrl = 'http://localhost/call2code%201/api/weather_simple.php?action=get_weather&lat=40.7128&lon=-74.0060';
$weatherResponse = @file_get_contents($weatherUrl);
if ($weatherResponse !== false) {
    $weatherData = json_decode($weatherResponse, true);
    if ($weatherData && isset($weatherData['success'])) {
        if ($weatherData['success']) {
            echo "<p class='success'>✓ Simple Weather API working</p>";
            echo "<p>Temperature: " . $weatherData['data']['temperature'] . "°C</p>";
            echo "<p>Condition: " . $weatherData['data']['condition'] . "</p>";
        } else {
            echo "<p class='error'>✗ Simple Weather API error: " . $weatherData['error'] . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Simple Weather API returned invalid JSON</p>";
    }
} else {
    echo "<p class='error'>✗ Simple Weather API not accessible</p>";
}
echo "</div>";

// Test Pokemon API
echo "<div class='test'>";
echo "<h3>Testing Pokemon API...</h3>";
$pokemonUrl = 'http://localhost/call2code%201/api/pokemon.php?action=random';
$pokemonResponse = @file_get_contents($pokemonUrl);
if ($pokemonResponse !== false) {
    $pokemonData = json_decode($pokemonResponse, true);
    if ($pokemonData && isset($pokemonData['success'])) {
        if ($pokemonData['success']) {
            echo "<p class='success'>✓ Pokemon API working</p>";
            echo "<p>Pokemon: " . $pokemonData['data']['name'] . "</p>";
        } else {
            echo "<p class='error'>✗ Pokemon API error: " . $pokemonData['error'] . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Pokemon API returned invalid JSON</p>";
    }
} else {
    echo "<p class='error'>✗ Pokemon API not accessible</p>";
}
echo "</div>";

// Test News API
echo "<div class='test'>";
echo "<h3>Testing News API...</h3>";
$newsUrl = 'http://localhost/call2code%201/api/news.php?action=latest&pageSize=1';
$newsResponse = @file_get_contents($newsUrl);
if ($newsResponse !== false) {
    $newsData = json_decode($newsResponse, true);
    if ($newsData && isset($newsData['success'])) {
        if ($newsData['success']) {
            echo "<p class='success'>✓ News API working</p>";
            echo "<p>Articles found: " . count($newsData['data']['articles']) . "</p>";
        } else {
            echo "<p class='error'>✗ News API error: " . $newsData['error'] . "</p>";
        }
    } else {
        echo "<p class='error'>✗ News API returned invalid JSON</p>";
    }
} else {
    echo "<p class='error'>✗ News API not accessible</p>";
}
echo "</div>";

// Test Giphy API
echo "<div class='test'>";
echo "<h3>Testing Giphy API...</h3>";
$giphyUrl = 'http://localhost/call2code%201/api/giphy.php?action=random&tag=celebration';
$giphyResponse = @file_get_contents($giphyUrl);
if ($giphyResponse !== false) {
    $giphyData = json_decode($giphyResponse, true);
    if ($giphyData && isset($giphyData['success'])) {
        if ($giphyData['success']) {
            echo "<p class='success'>✓ Giphy API working</p>";
            echo "<p>GIF title: " . $giphyData['data']['title'] . "</p>";
        } else {
            echo "<p class='error'>✗ Giphy API error: " . $giphyData['error'] . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Giphy API returned invalid JSON</p>";
    }
} else {
    echo "<p class='error'>✗ Giphy API not accessible</p>";
}
echo "</div>";

// Test Joke API
echo "<div class='test'>";
echo "<h3>Testing Joke API...</h3>";
$jokeUrl = 'http://localhost/call2code%201/api/joke.php?action=random';
$jokeResponse = @file_get_contents($jokeUrl);
if ($jokeResponse !== false) {
    $jokeData = json_decode($jokeResponse, true);
    if ($jokeData && isset($jokeData['success'])) {
        if ($jokeData['success']) {
            echo "<p class='success'>✓ Joke API working</p>";
            if ($jokeData['data']['type'] === 'single') {
                echo "<p>Joke: " . $jokeData['data']['joke'] . "</p>";
            } else {
                echo "<p>Setup: " . $jokeData['data']['setup'] . "</p>";
                echo "<p>Delivery: " . $jokeData['data']['delivery'] . "</p>";
            }
        } else {
            echo "<p class='error'>✗ Joke API error: " . $jokeData['error'] . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Joke API returned invalid JSON</p>";
    }
} else {
    echo "<p class='error'>✗ Joke API not accessible</p>";
}
echo "</div>";

// Test CoinGecko API
echo "<div class='test'>";
echo "<h3>Testing CoinGecko API...</h3>";
$coinUrl = 'http://localhost/call2code%201/api/coingecko.php?action=top_coins&limit=1';
$coinResponse = @file_get_contents($coinUrl);
if ($coinResponse !== false) {
    $coinData = json_decode($coinResponse, true);
    if ($coinData && isset($coinData['success'])) {
        if ($coinData['success']) {
            echo "<p class='success'>✓ CoinGecko API working</p>";
            echo "<p>Top coin: " . $coinData['data'][0]['name'] . " ($" . $coinData['data'][0]['current_price'] . ")</p>";
        } else {
            echo "<p class='error'>✗ CoinGecko API error: " . $coinData['error'] . "</p>";
        }
    } else {
        echo "<p class='error'>✗ CoinGecko API returned invalid JSON</p>";
    }
} else {
    echo "<p class='error'>✗ CoinGecko API not accessible</p>";
}
echo "</div>";

echo "<h2>Summary</h2>";
echo "<p>If you see any errors above, please:</p>";
echo "<ol>";
echo "<li>Make sure XAMPP is running (Apache and MySQL)</li>";
echo "<li>Import the database schema from database/schema.sql</li>";
echo "<li>Check that all API files exist in the api/ directory</li>";
echo "<li>Verify your database credentials in config/database.php</li>";
echo "</ol>";

echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li><a href='setup.php'>Run the setup script</a></li>";
echo "<li><a href='register.php'>Register a new user</a></li>";
echo "<li><a href='dashboard.php'>Test the dashboard</a></li>";
echo "</ol>";
?> 