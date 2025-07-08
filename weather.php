<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
define('OPENWEATHER_API_KEY', 'your_api');
define('OPENWEATHER_BASE_URL', 'https://api.openweathermap.org/data/2.5');

require_once '../config/database.php';

class WeatherAPI {
    private $apiKey;
    private $baseUrl;
    
    public function __construct() {
        $this->apiKey = OPENWEATHER_API_KEY;    
        $this->baseUrl = OPENWEATHER_BASE_URL;
    }
    
    public function getWeather($lat, $lon) {
        $url = "{$this->baseUrl}/weather?lat={$lat}&lon={$lon}&appid={$this->apiKey}&units=metric";
        
        try {
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Failed to fetch weather data");
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['cod']) && $data['cod'] !== 200) {
                throw new Exception("Weather API error: " . ($data['message'] ?? 'Unknown error'));
            }
            
            return [
                'success' => true,
                'data' => [
                    'temperature' => $data['main']['temp'],
                    'condition' => $data['weather'][0]['main'],
                    'description' => $data['weather'][0]['description'],
                    'humidity' => $data['main']['humidity'],
                    'wind_speed' => $data['wind']['speed'],
                    'icon' => $data['weather'][0]['icon']
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'offline_mode' => true
            ];
        }
    }
    
    public function checkWeatherCondition($lat, $lon, $requiredCondition) {
        $weather = $this->getWeather($lat, $lon);
        
        if (!$weather['success']) {
            return [
                'success' => false,
                'error' => $weather['error'],
                'offline_mode' => true
            ];
        }
        
        $currentCondition = $weather['data']['condition'];
        $matches = strtolower($currentCondition) === strtolower($requiredCondition);
        
        return [
            'success' => true,
            'matches' => $matches,
            'current_condition' => $currentCondition,
            'required_condition' => $requiredCondition,
            'weather_data' => $weather['data']
        ];
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $weatherAPI = new WeatherAPI();
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'get_weather':
                if (isset($_GET['lat']) && isset($_GET['lon'])) {
                    $result = $weatherAPI->getWeather($_GET['lat'], $_GET['lon']);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Missing lat/lon parameters']);
                }
                break;
                
            case 'check_condition':
                if (isset($_GET['lat']) && isset($_GET['lon']) && isset($_GET['condition'])) {
                    $result = $weatherAPI->checkWeatherCondition($_GET['lat'], $_GET['lon'], $_GET['condition']);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No action specified']);
    }
}
?> 
