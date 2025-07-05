<?php
// Prevent any HTML output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('X-API-Version: 1.0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
define('POKEAPI_BASE_URL', 'https://pokeapi.co/api/v2');
define('API_RATE_LIMIT', 100);
define('API_RATE_LIMIT_WINDOW', 600);

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

class PokemonAPI {
    private $baseUrl;
    private $rateLimit;
    private $rateLimitWindow;
    
    public function __construct() {
        $this->baseUrl = POKEAPI_BASE_URL;
        $this->rateLimit = API_RATE_LIMIT;
        $this->rateLimitWindow = API_RATE_LIMIT_WINDOW;
        $this->checkRateLimit();
        $this->logRequest($_SERVER['REQUEST_URI'], $_GET);
    }
    
    private function checkRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $cacheKey = "rate_limit_$ip";
        
        $current = $this->getFromCache($cacheKey) ?: 0;
        if ($current >= $this->rateLimit) {
            throw new Exception("Rate limit exceeded. Please try again later.");
        }
        $this->storeInCache($cacheKey, $current + 1, $this->rateLimitWindow);
    }
    
    private function getFromCache($key) {
        // Implement with Redis/Memcached/database in production
        $cacheFile = __DIR__ . '/cache/' . md5($key) . '.json';
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data['expires'] > time()) {
                return $data['value'];
            }
            unlink($cacheFile);
        }
        return null;
    }
    
    private function storeInCache($key, $value, $ttl) {
        // Implement with Redis/Memcached/database in production
        $cacheDir = __DIR__ . '/cache';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . '/' . md5($key) . '.json';
        file_put_contents($cacheFile, json_encode([
            'value' => $value,
            'expires' => time() + $ttl
        ]));
    }
    
    private function logRequest($endpoint, $params) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'endpoint' => $endpoint,
            'params' => $params,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        file_put_contents(__DIR__ . '/api_logs.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND);
    }
    
    private function formatResponse($success, $data = null, $error = null, $meta = null) {
        $response = [
            'success' => $success,
            'timestamp' => time(),
            'version' => '1.0'
        ];
        
        if ($data) $response['data'] = $data;
        if ($error) $response['error'] = $error;
        if ($meta) $response['meta'] = $meta;
        
        return $response;
    }
    
    private function getOfflinePokemon($id) {
        $offlineData = [
            1 => ['name' => 'bulbasaur', 'types' => ['grass', 'poison'], 'height' => 7, 'weight' => 69],
            4 => ['name' => 'charmander', 'types' => ['fire'], 'height' => 6, 'weight' => 85],
            7 => ['name' => 'squirtle', 'types' => ['water'], 'height' => 5, 'weight' => 90],
            25 => ['name' => 'pikachu', 'types' => ['electric'], 'height' => 4, 'weight' => 60]
        ];
        
        return isset($offlineData[$id]) ? $offlineData[$id] : null;
    }
    
    public function getRandomPokemon() {
        try {
            // Get total count of Pokemon
            $countResponse = file_get_contents($this->baseUrl . '/pokemon');
            if ($countResponse === false) {
                throw new Exception("Failed to fetch Pokemon count");
            }
            
            $countData = json_decode($countResponse, true);
            $totalPokemon = $countData['count'];
            
            // Get random Pokemon ID
            $randomId = rand(1, $totalPokemon);
            
            return $this->getPokemonById($randomId);
            
        } catch (Exception $e) {
            $randomOfflineId = array_rand([1, 4, 7, 25]);
            return $this->formatResponse(
                false, 
                $this->getOfflinePokemon($randomOfflineId), 
                $e->getMessage()
            );
        }
    }
    
    public function getPokemonById($id) {
        try {
            if (!is_numeric($id) || $id < 1) {
                throw new Exception("Invalid Pokemon ID");
            }
            
            $cacheKey = "pokemon_$id";
            if ($cached = $this->getFromCache($cacheKey)) {
                return $this->formatResponse(true, $cached);
            }
            
            $response = file_get_contents($this->baseUrl . "/pokemon/{$id}");
            if ($response === false) {
                throw new Exception("Failed to fetch Pokemon data");
            }
            
            $pokemonData = json_decode($response, true);
            $formattedData = [
                'id' => $pokemonData['id'],
                'name' => $pokemonData['name'],
                'types' => array_map(function($type) {
                    return $type['type']['name'];
                }, $pokemonData['types']),
                'height' => $pokemonData['height'],
                'weight' => $pokemonData['weight'],
                'sprite' => $pokemonData['sprites']['front_default'],
                'abilities' => array_map(function($ability) {
                    return $ability['ability']['name'];
                }, $pokemonData['abilities'])
            ];
            
            $this->storeInCache($cacheKey, $formattedData, 3600);
            return $this->formatResponse(true, $formattedData);
            
        } catch (Exception $e) {
            return $this->formatResponse(
                false, 
                $this->getOfflinePokemon($id), 
                $e->getMessage()
            );
        }
    }
    
    public function searchPokemon($name) {
        try {
            $sanitizedName = preg_replace('/[^a-zA-Z\-]/', '', strtolower($name));
            if (empty($sanitizedName)) {
                throw new Exception("Invalid Pokemon name");
            }
            
            $cacheKey = "pokemon_search_$sanitizedName";
            if ($cached = $this->getFromCache($cacheKey)) {
                return $this->formatResponse(true, $cached);
            }
            
            $response = file_get_contents($this->baseUrl . "/pokemon/$sanitizedName");
            if ($response === false) {
                throw new Exception("Pokemon not found");
            }
            
            $pokemonData = json_decode($response, true);
            $formattedData = [
                'id' => $pokemonData['id'],
                'name' => $pokemonData['name'],
                'types' => array_map(function($type) {
                    return $type['type']['name'];
                }, $pokemonData['types']),
                'height' => $pokemonData['height'],
                'weight' => $pokemonData['weight'],
                'sprite' => $pokemonData['sprites']['front_default'],
                'abilities' => array_map(function($ability) {
                    return $ability['ability']['name'];
                }, $pokemonData['abilities'])
            ];
            
            $this->storeInCache($cacheKey, $formattedData, 3600);
            return $this->formatResponse(true, $formattedData);
            
        } catch (Exception $e) {
            // Try to find in offline data by name
            $offlineData = [
                'bulbasaur' => 1,
                'charmander' => 4,
                'squirtle' => 7,
                'pikachu' => 25
            ];
            
            $offlineId = $offlineData[strtolower($name)] ?? null;
            return $this->formatResponse(
                false, 
                $offlineId ? $this->getOfflinePokemon($offlineId) : null, 
                $e->getMessage()
            );
        }
    }
    
    public function getPokemonList($limit = 20, $offset = 0) {
        try {
            $limit = max(1, min(100, (int)$limit));
            $offset = max(0, (int)$offset);
            
            $cacheKey = "pokemon_list_{$limit}_{$offset}";
            if ($cached = $this->getFromCache($cacheKey)) {
                return $this->formatResponse(true, $cached['results'], null, $cached['meta']);
            }
            
            $response = file_get_contents($this->baseUrl . "/pokemon?limit={$limit}&offset={$offset}");
            if ($response === false) {
                throw new Exception("Failed to fetch Pokemon list");
            }
            
            $data = json_decode($response, true);
            
            $responseData = [
                'results' => $data['results'],
                'meta' => [
                    'pagination' => [
                        'total' => $data['count'],
                        'limit' => $limit,
                        'offset' => $offset,
                        'has_more' => $data['next'] !== null
                    ]
                ]
            ];
            
            $this->storeInCache($cacheKey, $responseData, 3600);
            return $this->formatResponse(true, $data['results'], null, [
                'pagination' => [
                    'total' => $data['count'],
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => $data['next'] !== null
                ]
            ]);
            
        } catch (Exception $e) {
            // Basic offline list
            $offlineList = [
                ['name' => 'bulbasaur', 'url' => $this->baseUrl . '/pokemon/1'],
                ['name' => 'charmander', 'url' => $this->baseUrl . '/pokemon/4'],
                ['name' => 'squirtle', 'url' => $this->baseUrl . '/pokemon/7'],
                ['name' => 'pikachu', 'url' => $this->baseUrl . '/pokemon/25']
            ];
            
            return $this->formatResponse(
                false, 
                array_slice($offlineList, $offset, $limit), 
                $e->getMessage(),
                [
                    'pagination' => [
                        'total' => count($offlineList),
                        'limit' => $limit,
                        'offset' => $offset,
                        'has_more' => ($offset + $limit) < count($offlineList)
                    ]
                ]
            );
        }
    }
}

// Handle API requests
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $pokemonAPI = new PokemonAPI();
        
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'random':
                    $result = $pokemonAPI->getRandomPokemon();
                    break;
                    
                case 'get_by_id':
                    if (isset($_GET['id'])) {
                        $result = $pokemonAPI->getPokemonById($_GET['id']);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing ID parameter'];
                    }
                    break;
                    
                case 'search':
                    if (isset($_GET['name'])) {
                        $result = $pokemonAPI->searchPokemon($_GET['name']);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing name parameter'];
                    }
                    break;
                    
                case 'list':
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                    $result = $pokemonAPI->getPokemonList($limit, $offset);
                    break;
                    
                default:
                    $result = ['success' => false, 'error' => 'Invalid action'];
            }
        } else {
            $result = ['success' => false, 'error' => 'No action specified'];
        }
        
        // Clear any unexpected output and return JSON
        ob_end_clean();
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        ob_end_clean();
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    // Clear any output and return error
    ob_end_clean();
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'offline_mode' => true
    ], JSON_PRETTY_PRINT);
}
?>