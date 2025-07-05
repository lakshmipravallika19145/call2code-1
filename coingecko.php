<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
define('COINGECKO_BASE_URL', 'https://api.coingecko.com/api/v3');

require_once '../config/database.php';

class CoinGeckoAPI {
    private $baseUrl;
    
    public function __construct() {
        $this->baseUrl = COINGECKO_BASE_URL;
    }
    
    public function getTopCoins($limit = 10) {
        try {
            $url = "{$this->baseUrl}/coins/markets?vs_currency=usd&order=market_cap_desc&per_page={$limit}&page=1&sparkline=false";
            
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Failed to fetch cryptocurrency data");
            }
            
            $data = json_decode($response, true);
            
            return [
                'success' => true,
                'data' => array_map(function($coin) {
                    return [
                        'id' => $coin['id'],
                        'symbol' => $coin['symbol'],
                        'name' => $coin['name'],
                        'current_price' => $coin['current_price'],
                        'market_cap' => $coin['market_cap'],
                        'price_change_24h' => $coin['price_change_24h'],
                        'price_change_percentage_24h' => $coin['price_change_percentage_24h'],
                        'image' => $coin['image']
                    ];
                }, $data)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'offline_mode' => true
            ];
        }
    }
    
    public function getCoinPrice($coinId) {
        try {
            $url = "{$this->baseUrl}/simple/price?ids={$coinId}&vs_currencies=usd";
            
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Failed to fetch coin price");
            }
            
            $data = json_decode($response, true);
            
            if (!isset($data[$coinId])) {
                throw new Exception("Coin not found");
            }
            
            return [
                'success' => true,
                'data' => [
                    'coin_id' => $coinId,
                    'price_usd' => $data[$coinId]['usd']
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
    
    public function getRandomCoin() {
        try {
            $coins = $this->getTopCoins(50);
            
            if (!$coins['success']) {
                throw new Exception($coins['error']);
            }
            
            $randomCoin = $coins['data'][array_rand($coins['data'])];
            
            return [
                'success' => true,
                'data' => $randomCoin
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
    $coingeckoAPI = new CoinGeckoAPI();
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'top_coins':
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $result = $coingeckoAPI->getTopCoins($limit);
                echo json_encode($result);
                break;
                
            case 'coin_price':
                if (isset($_GET['coin_id'])) {
                    $result = $coingeckoAPI->getCoinPrice($_GET['coin_id']);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Missing coin_id parameter']);
                }
                break;
                
            case 'random_coin':
                $result = $coingeckoAPI->getRandomCoin();
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