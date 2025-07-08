<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
define('GIPHY_API_KEY', 'your_api');
define('GIPHY_BASE_URL', 'https://api.giphy.com/v1/gifs');

require_once '../config/database.php';

class GiphyAPI {
    private $apiKey;
    private $baseUrl;
    
    public function __construct() {
        $this->apiKey = GIPHY_API_KEY;
        $this->baseUrl = GIPHY_BASE_URL;
    }
    
    public function searchGif($query, $limit = 10, $rating = 'g') {
        try {
            $url = "{$this->baseUrl}/search?q=" . urlencode($query) . "&api_key={$this->apiKey}&limit={$limit}&rating={$rating}";
            
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Failed to fetch Giphy data");
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['meta']['status']) && $data['meta']['status'] !== 200) {
                throw new Exception("Giphy API error: " . ($data['meta']['msg'] ?? 'Unknown error'));
            }
            
            return [
                'success' => true,
                'data' => [
                    'total_count' => $data['pagination']['total_count'],
                    'count' => $data['pagination']['count'],
                    'gifs' => array_map(function($gif) {
                        return [
                            'id' => $gif['id'],
                            'title' => $gif['title'],
                            'url' => $gif['url'],
                            'embed_url' => $gif['embed_url'],
                            'images' => [
                                'original' => $gif['images']['original']['url'],
                                'fixed_height' => $gif['images']['fixed_height']['url'],
                                'fixed_width' => $gif['images']['fixed_width']['url'],
                                'preview' => $gif['images']['preview_gif']['url']
                            ]
                        ];
                    }, $data['data'])
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
    
    public function getRandomGif($tag = 'celebration', $rating = 'g') {
        try {
            $url = "{$this->baseUrl}/random?tag=" . urlencode($tag) . "&api_key={$this->apiKey}&rating={$rating}";
            
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Failed to fetch random Giphy data");
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['meta']['status']) && $data['meta']['status'] !== 200) {
                throw new Exception("Giphy API error: " . ($data['meta']['msg'] ?? 'Unknown error'));
            }
            
            return [
                'success' => true,
                'data' => [
                    'id' => $data['data']['id'],
                    'title' => $data['data']['title'],
                    'url' => $data['data']['url'],
                    'embed_url' => $data['data']['embed_url'],
                    'images' => [
                        'original' => $data['data']['images']['original']['url'],
                        'fixed_height' => $data['data']['images']['fixed_height']['url'],
                        'fixed_width' => $data['data']['images']['fixed_width']['url'],
                        'preview' => $data['data']['images']['preview_gif']['url']
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
    
    public function getTrendingGifs($limit = 10, $rating = 'g') {
        try {
            $url = "{$this->baseUrl}/trending?api_key={$this->apiKey}&limit={$limit}&rating={$rating}";
            
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Failed to fetch trending Giphy data");
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['meta']['status']) && $data['meta']['status'] !== 200) {
                throw new Exception("Giphy API error: " . ($data['meta']['msg'] ?? 'Unknown error'));
            }
            
            return [
                'success' => true,
                'data' => [
                    'total_count' => $data['pagination']['total_count'],
                    'count' => $data['pagination']['count'],
                    'gifs' => array_map(function($gif) {
                        return [
                            'id' => $gif['id'],
                            'title' => $gif['title'],
                            'url' => $gif['url'],
                            'embed_url' => $gif['embed_url'],
                            'images' => [
                                'original' => $gif['images']['original']['url'],
                                'fixed_height' => $gif['images']['fixed_height']['url'],
                                'fixed_width' => $gif['images']['fixed_width']['url'],
                                'preview' => $gif['images']['preview_gif']['url']
                            ]
                        ];
                    }, $data['data'])
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
    
    public function getCelebrationGif() {
        $celebrationTags = ['celebration', 'success', 'winner', 'party', 'congratulations'];
        $randomTag = $celebrationTags[array_rand($celebrationTags)];
        return $this->getRandomGif($randomTag);
    }
    
    public function getWeatherGif($weatherCondition) {
        $weatherTags = [
            'sunny' => ['sun', 'sunny', 'clear sky'],
            'rainy' => ['rain', 'rainy', 'umbrella'],
            'snowy' => ['snow', 'snowy', 'winter'],
            'cloudy' => ['clouds', 'cloudy', 'overcast'],
            'stormy' => ['thunderstorm', 'lightning', 'storm']
        ];
        
        $tag = $weatherTags[strtolower($weatherCondition)] ?? 'weather';
        $randomTag = is_array($tag) ? $tag[array_rand($tag)] : $tag;
        
        return $this->getRandomGif($randomTag);
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $giphyAPI = new GiphyAPI();
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'search':
                if (isset($_GET['query'])) {
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                    $rating = isset($_GET['rating']) ? $_GET['rating'] : 'g';
                    $result = $giphyAPI->searchGif($_GET['query'], $limit, $rating);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Missing query parameter']);
                }
                break;
                
            case 'random':
                $tag = isset($_GET['tag']) ? $_GET['tag'] : 'celebration';
                $rating = isset($_GET['rating']) ? $_GET['rating'] : 'g';
                $result = $giphyAPI->getRandomGif($tag, $rating);
                echo json_encode($result);
                break;
                
            case 'trending':
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $rating = isset($_GET['rating']) ? $_GET['rating'] : 'g';
                $result = $giphyAPI->getTrendingGifs($limit, $rating);
                echo json_encode($result);
                break;
                
            case 'celebration':
                $result = $giphyAPI->getCelebrationGif();
                echo json_encode($result);
                break;
                
            case 'weather':
                if (isset($_GET['condition'])) {
                    $result = $giphyAPI->getWeatherGif($_GET['condition']);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Missing condition parameter']);
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
