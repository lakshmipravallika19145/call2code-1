<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
define('JOKE_API_BASE_URL', 'https://v2.jokeapi.dev');

require_once '../config/database.php';

class JokeAPI {
    private $baseUrl;
    
    public function __construct() {
        $this->baseUrl = JOKE_API_BASE_URL;
    }
    
    public function getRandomJoke($category = 'any', $blacklist = 'nsfw,religious,political,racist,sexist,explicit') {
        try {
            $url = "{$this->baseUrl}/joke/{$category}?blacklistFlags={$blacklist}";
            
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Failed to fetch joke data");
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['error']) && $data['error'] === true) {
                throw new Exception("Joke API error: " . ($data['message'] ?? 'Unknown error'));
            }
            
            return [
                'success' => true,
                'data' => [
                    'category' => $data['category'],
                    'type' => $data['type'],
                    'joke' => $data['type'] === 'single' ? $data['joke'] : null,
                    'setup' => $data['type'] === 'twopart' ? $data['setup'] : null,
                    'delivery' => $data['type'] === 'twopart' ? $data['delivery'] : null,
                    'flags' => $data['flags'],
                    'safe' => $data['safe']
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'offline_mode' => true,
                'data' => $this->getOfflineJoke()
            ];
        }
    }
    
    public function getJokeByCategory($category) {
        return $this->getRandomJoke($category);
    }
    
    public function getProgrammingJoke() {
        return $this->getRandomJoke('programming');
    }
    
    public function getDadJoke() {
        return $this->getRandomJoke('misc');
    }
    
    private function getOfflineJoke() {
        $offlineJokes = [
            [
                'category' => 'programming',
                'type' => 'single',
                'joke' => 'Why do programmers prefer dark mode? Because light attracts bugs!',
                'safe' => true
            ],
            [
                'category' => 'misc',
                'type' => 'twopart',
                'setup' => 'Why did the scarecrow win an award?',
                'delivery' => 'Because he was outstanding in his field!',
                'safe' => true
            ],
            [
                'category' => 'programming',
                'type' => 'single',
                'joke' => 'How many programmers does it take to change a light bulb? None, that\'s a hardware problem!',
                'safe' => true
            ]
        ];
        
        return $offlineJokes[array_rand($offlineJokes)];
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $jokeAPI = new JokeAPI();
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'random':
                $category = isset($_GET['category']) ? $_GET['category'] : 'any';
                $result = $jokeAPI->getRandomJoke($category);
                echo json_encode($result);
                break;
                
            case 'programming':
                $result = $jokeAPI->getProgrammingJoke();
                echo json_encode($result);
                break;
                
            case 'dad_joke':
                $result = $jokeAPI->getDadJoke();
                echo json_encode($result);
                break;
                
            case 'category':
                if (isset($_GET['category'])) {
                    $result = $jokeAPI->getJokeByCategory($_GET['category']);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Missing category parameter']);
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