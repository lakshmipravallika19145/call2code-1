<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
define('NEWS_API_KEY', '0ee33f34a5f241e0bc4657c964792958');
define('NEWS_BASE_URL', 'https://newsapi.org/v2');

require_once '../config/database.php';

class NewsAPI {
    private $apiKey;
    private $baseUrl;
    
    public function __construct() {
        $this->apiKey = NEWS_API_KEY;
        $this->baseUrl = NEWS_BASE_URL;
    }
    
    public function getLatestNews($keyword = 'technology', $pageSize = 10) {
        try {
            $url = "{$this->baseUrl}/everything?q={$keyword}&pageSize={$pageSize}&sortBy=publishedAt&apiKey={$this->apiKey}";
            
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Failed to fetch news data");
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['status']) && $data['status'] !== 'ok') {
                throw new Exception("News API error: " . ($data['message'] ?? 'Unknown error'));
            }
            
            return [
                'success' => true,
                'data' => [
                    'totalResults' => $data['totalResults'],
                    'articles' => array_map(function($article) {
                        return [
                            'title' => $article['title'],
                            'description' => $article['description'],
                            'url' => $article['url'],
                            'urlToImage' => $article['urlToImage'],
                            'publishedAt' => $article['publishedAt'],
                            'source' => $article['source']['name'],
                            'content' => $article['content']
                        ];
                    }, $data['articles'])
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
    
    public function getNewsByCategory($category = 'technology', $pageSize = 10) {
        try {
            $url = "{$this->baseUrl}/top-headlines?category={$category}&pageSize={$pageSize}&apiKey={$this->apiKey}";
            
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Failed to fetch news data");
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['status']) && $data['status'] !== 'ok') {
                throw new Exception("News API error: " . ($data['message'] ?? 'Unknown error'));
            }
            
            return [
                'success' => true,
                'data' => [
                    'totalResults' => $data['totalResults'],
                    'articles' => array_map(function($article) {
                        return [
                            'title' => $article['title'],
                            'description' => $article['description'],
                            'url' => $article['url'],
                            'urlToImage' => $article['urlToImage'],
                            'publishedAt' => $article['publishedAt'],
                            'source' => $article['source']['name'],
                            'content' => $article['content']
                        ];
                    }, $data['articles'])
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
    
    public function searchNews($query, $pageSize = 10) {
        try {
            $url = "{$this->baseUrl}/everything?q=" . urlencode($query) . "&pageSize={$pageSize}&sortBy=relevancy&apiKey={$this->apiKey}";
            
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Failed to fetch news data");
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['status']) && $data['status'] !== 'ok') {
                throw new Exception("News API error: " . ($data['message'] ?? 'Unknown error'));
            }
            
            return [
                'success' => true,
                'data' => [
                    'totalResults' => $data['totalResults'],
                    'query' => $query,
                    'articles' => array_map(function($article) {
                        return [
                            'title' => $article['title'],
                            'description' => $article['description'],
                            'url' => $article['url'],
                            'urlToImage' => $article['urlToImage'],
                            'publishedAt' => $article['publishedAt'],
                            'source' => $article['source']['name'],
                            'content' => $article['content']
                        ];
                    }, $data['articles'])
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
    
    public function getRandomNewsFact() {
        try {
            $news = $this->getLatestNews('technology', 1);
            
            if (!$news['success']) {
                throw new Exception($news['error']);
            }
            
            $article = $news['data']['articles'][0];
            
            return [
                'success' => true,
                'data' => [
                    'title' => $article['title'],
                    'description' => $article['description'],
                    'source' => $article['source']
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
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $newsAPI = new NewsAPI();
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'latest':
                $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : 'technology';
                $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
                $result = $newsAPI->getLatestNews($keyword, $pageSize);
                echo json_encode($result);
                break;
                
            case 'category':
                $category = isset($_GET['category']) ? $_GET['category'] : 'technology';
                $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
                $result = $newsAPI->getNewsByCategory($category, $pageSize);
                echo json_encode($result);
                break;
                
            case 'search':
                if (isset($_GET['query'])) {
                    $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
                    $result = $newsAPI->searchNews($_GET['query'], $pageSize);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Missing query parameter']);
                }
                break;
                
            case 'random_fact':
                $result = $newsAPI->getRandomNewsFact();
                echo json_encode($result);
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No action specified']);
    }
}
?> 