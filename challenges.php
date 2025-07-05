<?php
// Prevent any HTML output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
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

class ChallengeAPI {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    public function getAllChallenges() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM challenges WHERE is_active = 1 ORDER BY difficulty, challenge_type");
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $challenges
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getChallengesByType($type) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM challenges WHERE challenge_type = ? AND is_active = 1 ORDER BY difficulty");
            $stmt->execute([$type]);
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $challenges
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getChallengesByDifficulty($difficulty) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM challenges WHERE difficulty = ? AND is_active = 1 ORDER BY challenge_type");
            $stmt->execute([$difficulty]);
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $challenges
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getChallengeById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM challenges WHERE id = ?");
            $stmt->execute([$id]);
            $challenge = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$challenge) {
                return [
                    'success' => false,
                    'error' => 'Challenge not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $challenge
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function createChallenge($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO challenges (title, description, challenge_type, difficulty, points, coordinates_lat, coordinates_lng, radius_meters, weather_condition, pokemon_id, news_keyword, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['challenge_type'],
                $data['difficulty'],
                $data['points'],
                $data['coordinates_lat'] ?? null,
                $data['coordinates_lng'] ?? null,
                $data['radius_meters'] ?? 100,
                $data['weather_condition'] ?? null,
                $data['pokemon_id'] ?? null,
                $data['news_keyword'] ?? null
            ]);
            
            return [
                'success' => true,
                'data' => [
                    'id' => $this->pdo->lastInsertId(),
                    'message' => 'Challenge created successfully'
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function completeChallenge($userId, $challengeId, $completionData = []) {
        try {
            // Check if already completed
            $stmt = $this->pdo->prepare("SELECT * FROM user_progress WHERE user_id = ? AND challenge_id = ?");
            $stmt->execute([$userId, $challengeId]);
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'error' => 'Challenge already completed'
                ];
            }
            
            // Get challenge details
            $stmt = $this->pdo->prepare("SELECT * FROM challenges WHERE id = ?");
            $stmt->execute([$challengeId]);
            $challenge = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$challenge) {
                return [
                    'success' => false,
                    'error' => 'Challenge not found'
                ];
            }
            
            // Record completion
            $stmt = $this->pdo->prepare("
                INSERT INTO user_progress (user_id, challenge_id, completed_at, score_earned, location_lat, location_lng, weather_data, pokemon_data, news_data)
                VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $challengeId,
                $challenge['points'],
                $completionData['user_location']['lat'] ?? null,
                $completionData['user_location']['lng'] ?? null,
                json_encode($completionData['weather_condition'] ?? null),
                json_encode($completionData['pokemon_found'] ?? null),
                json_encode($completionData['news_article'] ?? null)
            ]);
            
            return [
                'success' => true,
                'data' => [
                    'points_earned' => $challenge['points'],
                    'message' => 'Challenge completed successfully!'
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getUserProgress($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT up.*, c.title, c.challenge_type, c.difficulty, c.points
                FROM user_progress up
                JOIN challenges c ON up.challenge_id = c.id
                WHERE up.user_id = ?
                ORDER BY up.completed_at DESC
            ");
            $stmt->execute([$userId]);
            $progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $progress
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getUserStats($userId) {
        try {
            // Total completed challenges
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total_completed, SUM(c.points) as total_points
                FROM user_progress up
                JOIN challenges c ON up.challenge_id = c.id
                WHERE up.user_id = ?
            ");
            $stmt->execute([$userId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Challenges by type
            $stmt = $this->pdo->prepare("
                SELECT c.challenge_type, COUNT(*) as count
                FROM user_progress up
                JOIN challenges c ON up.challenge_id = c.id
                WHERE up.user_id = ?
                GROUP BY c.challenge_type
            ");
            $stmt->execute([$userId]);
            $byType = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Challenges by difficulty
            $stmt = $this->pdo->prepare("
                SELECT c.difficulty, COUNT(*) as count
                FROM user_progress up
                JOIN challenges c ON up.challenge_id = c.id
                WHERE up.user_id = ?
                GROUP BY c.difficulty
            ");
            $stmt->execute([$userId]);
            $byDifficulty = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => [
                    'total_completed' => (int)$stats['total_completed'],
                    'total_points' => (int)$stats['total_points'],
                    'by_type' => $byType,
                    'by_difficulty' => $byDifficulty
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getNearbyChallenges($lat, $lng, $radius = 5) {
        try {
            // Convert radius from km to degrees (approximate)
            $radiusDegrees = $radius / 111;
            
            $stmt = $this->pdo->prepare("
                SELECT *, 
                (6371 * acos(cos(radians(?)) * cos(radians(coordinates_lat)) * 
                cos(radians(coordinates_lng) - radians(?)) + 
                sin(radians(?)) * sin(radians(coordinates_lat)))) AS distance
                FROM challenges 
                WHERE coordinates_lat IS NOT NULL 
                AND coordinates_lng IS NOT NULL
                AND is_active = 1
                HAVING distance <= ?
                ORDER BY distance
            ");
            
            $stmt->execute([$lat, $lng, $lat, $radius]);
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $challenges
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

// Handle API requests
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $challengeAPI = new ChallengeAPI();
        
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'all':
                    $result = $challengeAPI->getAllChallenges();
                    break;
                    
                case 'by_type':
                    if (isset($_GET['type'])) {
                        $result = $challengeAPI->getChallengesByType($_GET['type']);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing type parameter'];
                    }
                    break;
                    
                case 'by_difficulty':
                    if (isset($_GET['difficulty'])) {
                        $result = $challengeAPI->getChallengesByDifficulty($_GET['difficulty']);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing difficulty parameter'];
                    }
                    break;
                    
                case 'get':
                    if (isset($_GET['id'])) {
                        $result = $challengeAPI->getChallengeById($_GET['id']);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing id parameter'];
                    }
                    break;
                    
                case 'user_progress':
                    if (isset($_GET['user_id'])) {
                        $result = $challengeAPI->getUserProgress($_GET['user_id']);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing user_id parameter'];
                    }
                    break;
                    
                case 'user_stats':
                    if (isset($_GET['user_id'])) {
                        $result = $challengeAPI->getUserStats($_GET['user_id']);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing user_id parameter'];
                    }
                    break;
                    
                case 'nearby':
                    if (isset($_GET['lat']) && isset($_GET['lng'])) {
                        $radius = isset($_GET['radius']) ? (float)$_GET['radius'] : 5;
                        $result = $challengeAPI->getNearbyChallenges($_GET['lat'], $_GET['lng'], $radius);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing lat/lng parameters'];
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
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $challengeAPI = new ChallengeAPI();
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'create':
                    if ($input) {
                        $result = $challengeAPI->createChallenge($input);
                    } else {
                        $result = ['success' => false, 'error' => 'Invalid JSON data'];
                    }
                    break;
                    
                case 'complete':
                    if (isset($input['user_id']) && isset($input['challenge_id'])) {
                        $completionData = $input['completion_data'] ?? [];
                        $result = $challengeAPI->completeChallenge($input['user_id'], $input['challenge_id'], $completionData);
                    } else {
                        $result = ['success' => false, 'error' => 'Missing user_id or challenge_id'];
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
        'error' => 'API Error: ' . $e->getMessage()
    ]);
}
?> 