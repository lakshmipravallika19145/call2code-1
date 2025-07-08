<?php
// API Keys Configuration for Scavenger Hunt App
// Replace these with your actual API keys

// Weather API
define('OPENWEATHER_API_KEY', 'your_api');
define('OPENWEATHER_BASE_URL', 'http://api.openweathermap.org/data/2.5');

// Google Maps API
define('GOOGLE_MAPS_API_KEY', 'your_api');
define('GOOGLE_MAPS_BASE_URL', 'https://maps.googleapis.com/maps/api');

// News API
define('NEWS_API_KEY', 'your_api'); // Get from https://newsapi.org/
define('NEWS_BASE_URL', 'https://newsapi.org/v2');

// Giphy API
define('GIPHY_API_KEY', 'your api'); // Get from https://developers.giphy.com/
define('GIPHY_BASE_URL', 'https://api.giphy.com/v1/gifs');

// Pokemon API (No key required)
define('POKEAPI_BASE_URL', 'https://pokeapi.co/api/v2');

// CoinGecko API (No key required)
define('COINGECKO_BASE_URL', 'https://api.coingecko.com/api/v3');

// Joke API (No key required)
define('JOKE_API_BASE_URL', 'https://v2.jokeapi.dev');

// Cat API (No key required)
define('CAT_API_BASE_URL', 'https://api.thecatapi.com/v1');

// Dog API (No key required)
define('DOG_API_BASE_URL', 'https://api.thedogapi.com/v1');

// NASA API
define('NASA_API_KEY', 'your_api'); // Get from https://api.nasa.gov/
define('NASA_BASE_URL', 'https://api.nasa.gov');

// TacoFancy API (No key required)
define('TACOFANCY_BASE_URL', 'https://taco-fancy.herokuapp.com');

// Open Trivia API (No key required)
define('OPENTRIVIA_BASE_URL', 'https://opentdb.com/api.php');

// Rate limiting settings
define('API_RATE_LIMIT', 100); // requests per hour
define('API_RATE_LIMIT_WINDOW', 3600); // seconds

// Cache settings
define('API_CACHE_DURATION', 3600); // 1 hour in seconds
define('API_CACHE_DIR', __DIR__ . '/../api/cache');

// Error handling
define('API_OFFLINE_MODE', true); // Enable offline fallbacks
define('API_DEBUG_MODE', false); // Enable debug logging

// Security headers
define('API_SECURITY_HEADERS', [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin'
]);
?> 
