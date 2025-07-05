<?php
// Prevent any HTML output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start output buffering to catch any unexpected output
ob_start();

try {
    require_once '../config/database.php';
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Configuration error: ' . $e->getMessage()
    ]);
    exit;
}

class SimpleWeatherAPI {
    
    public function __construct() {
        // No API key needed for this simple implementation
    }
    
    public function getWeather($lat, $lon) {
        try {
            // Generate mock weather data based on coordinates and time
            $weatherData = $this->generateMockWeather($lat, $lon);
            
            return [
                'success' => true,
                'data' => $weatherData
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
        try {
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
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'offline_mode' => true
            ];
        }
    }
    
    private function generateMockWeather($lat, $lon) {
        // Use coordinates and time to generate consistent but varied weather
        $seed = abs($lat * 1000 + $lon * 1000 + date('H'));
        srand($seed);
        
        $conditions = [
            'Clear', 'Clouds', 'Rain', 'Snow', 'Thunderstorm', 'Drizzle', 'Mist', 'Fog'
        ];
        
        $condition = $conditions[array_rand($conditions)];
        
        // Generate temperature based on time and location
        $hour = (int)date('H');
        $baseTemp = 20; // Base temperature 20°C
        
        // Temperature varies by time of day
        if ($hour >= 6 && $hour <= 18) {
            // Daytime: warmer
            $tempVariation = rand(-5, 15);
        } else {
            // Nighttime: cooler
            $tempVariation = rand(-15, 5);
        }
        
        // Temperature varies by latitude (rough approximation)
        $latEffect = ($lat - 40) * 0.5; // Cooler further from equator
        
        $temperature = $baseTemp + $tempVariation + $latEffect;
        
        // Generate humidity based on condition
        $humidity = $this->getHumidityForCondition($condition);
        
        // Generate wind speed
        $windSpeed = rand(0, 20);
        
        // Generate description
        $descriptions = [
            'Clear' => ['clear sky', 'sunny', 'bright'],
            'Clouds' => ['scattered clouds', 'broken clouds', 'overcast'],
            'Rain' => ['light rain', 'moderate rain', 'heavy rain'],
            'Snow' => ['light snow', 'moderate snow', 'heavy snow'],
            'Thunderstorm' => ['thunderstorm', 'storm', 'electrical storm'],
            'Drizzle' => ['light drizzle', 'drizzle'],
            'Mist' => ['mist', 'hazy'],
            'Fog' => ['fog', 'foggy']
        ];
        
        $description = $descriptions[$condition][array_rand($descriptions[$condition])];
        
        // Generate icon based on condition and time
        $icon = $this->getIconForCondition($condition, $hour);
        
        return [
            'temperature' => round($temperature, 1),
            'condition' => $condition,
            'description' => $description,
            'humidity' => $humidity,
            'wind_speed' => $windSpeed,
            'icon' => $icon,
            'pressure' => rand(1000, 1020),
            'visibility' => rand(5000, 10000),
            'sunrise' => '06:00',
            'sunset' => '18:00',
            'timestamp' => time()
        ];
    }
    
    private function getHumidityForCondition($condition) {
        $humidityRanges = [
            'Clear' => [30, 60],
            'Clouds' => [50, 80],
            'Rain' => [80, 95],
            'Snow' => [70, 90],
            'Thunderstorm' => [85, 95],
            'Drizzle' => [75, 90],
            'Mist' => [90, 100],
            'Fog' => [95, 100]
        ];
        
        $range = $humidityRanges[$condition] ?? [50, 70];
        return rand($range[0], $range[1]);
    }
    
    private function getIconForCondition($condition, $hour) {
        $isDay = $hour >= 6 && $hour <= 18;
        $dayNight = $isDay ? 'd' : 'n';
        
        $iconMap = [
            'Clear' => "01{$dayNight}",
            'Clouds' => "02{$dayNight}",
            'Rain' => "10{$dayNight}",
            'Snow' => "13{$dayNight}",
            'Thunderstorm' => "11{$dayNight}",
            'Drizzle' => "09{$dayNight}",
            'Mist' => "50{$dayNight}",
            'Fog' => "50{$dayNight}"
        ];
        
        return $iconMap[$condition] ?? "01{$dayNight}";
    }
    
    public function getWeatherForecast($lat, $lon, $days = 5) {
        try {
            $forecast = [];
            
            for ($i = 0; $i < $days; $i++) {
                // Generate weather for each day
                $daySeed = abs($lat * 1000 + $lon * 1000 + $i);
                srand($daySeed);
                
                $conditions = ['Clear', 'Clouds', 'Rain', 'Snow'];
                $condition = $conditions[array_rand($conditions)];
                
                $tempMin = rand(5, 15);
                $tempMax = rand(20, 30);
                
                $forecast[] = [
                    'date' => date('Y-m-d', strtotime("+{$i} days")),
                    'condition' => $condition,
                    'temp_min' => $tempMin,
                    'temp_max' => $tempMax,
                    'humidity' => $this->getHumidityForCondition($condition),
                    'wind_speed' => rand(0, 15)
                ];
            }
            
            return [
                'success' => true,
                'data' => [
                    'forecast' => $forecast,
                    'location' => [
                        'lat' => $lat,
                        'lon' => $lon
                    ]
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
}

// Handle API requests
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $weatherAPI = new SimpleWeatherAPI();
        
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'get_weather':
                    if (isset($_GET['lat']) && isset($_GET['lon'])) {
                        $result = $weatherAPI->getWeather($_GET['lat'], $_GET['lon']);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing lat/lon parameters'];
                    }
                    break;
                    
                case 'check_condition':
                    if (isset($_GET['lat']) && isset($_GET['lon']) && isset($_GET['condition'])) {
                        $result = $weatherAPI->checkWeatherCondition($_GET['lat'], $_GET['lon'], $_GET['condition']);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing parameters'];
                    }
                    break;
                    
                case 'forecast':
                    if (isset($_GET['lat']) && isset($_GET['lon'])) {
                        $days = isset($_GET['days']) ? (int)$_GET['days'] : 5;
                        $result = $weatherAPI->getWeatherForecast($_GET['lat'], $_GET['lon'], $days);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing lat/lon parameters'];
                    }
                    break;
                    
                default:
                    $result = ['success' => false, 'error' => 'Invalid action'];
            }
        } else {
            $result = ['success' => false, 'error' => 'No action specified'];
        }
        
        // Clear any unexpected output and return JSON
        ob_end_clean();
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    // Clear any output and return error
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Weather API Error: ' . $e->getMessage()
    ]);
}
?> 