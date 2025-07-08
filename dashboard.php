<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getDBConnection();
$user = null;
$challenges = [];
$userProgress = [];

try {
    // Get user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get available challenges
    $stmt = $pdo->prepare("SELECT * FROM challenges WHERE is_active = 1 ORDER BY difficulty, points");
    $stmt->execute();
    $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get user progress
    $stmt = $pdo->prepare("SELECT challenge_id, completed_at, score_earned FROM user_progress WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userProgress = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create progress lookup
    $completedChallenges = array_column($userProgress, 'challenge_id');
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Adventure Hunt</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/challenges.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=your_api&libraries=places"></script>
    <script>
        // Set user ID for JavaScript
        window.currentUserId = <?php echo $_SESSION['user_id']; ?>;
    </script>
</head>
<body class="light-mode">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-map-marked-alt"></i>
                <span>Adventure Hunt</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="#challenges" class="nav-link">Challenges</a>
                <a href="#progress" class="nav-link">Progress</a>
                <a href="#leaderboard" class="nav-link">Leaderboard</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
            <div class="nav-toggle">
                <button id="theme-toggle" class="theme-btn">
                    <i class="fas fa-moon"></i>
                </button>
                <button id="menu-toggle" class="menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Dashboard Header -->
    <section class="dashboard-header">
        <div class="container">
            <div class="dashboard-welcome">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <h1>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                        <p>Ready for your next adventure?</p>
                    </div>
                </div>
                <div class="user-stats">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $user['total_score']; ?></h3>
                            <p>Total Score</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $user['current_level']; ?></h3>
                            <p>Level</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count($completedChallenges); ?></h3>
                            <p>Completed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="quick-actions">
        <div class="container">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <div class="action-card" onclick="startRandomChallenge()">
                    <div class="action-icon">
                        <i class="fas fa-dice"></i>
                    </div>
                    <h3>Random Challenge</h3>
                    <p>Get a random challenge to complete</p>
                </div>
                <div class="action-card" onclick="viewNearbyChallenges()">
                    <div class="action-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Nearby Challenges</h3>
                    <p>Find challenges near your location</p>
                </div>
                <div class="action-card" onclick="startMultiplayer()">
                    <div class="action-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Multiplayer Mode</h3>
                    <p>Play with friends in real-time</p>
                </div>
                <div class="action-card" onclick="viewProgress()">
                    <div class="action-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>View Progress</h3>
                    <p>Check your challenge completion</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Challenges Section -->
    <section id="challenges" class="challenges-section">
        <div class="container">
            <div class="section-header">
                <h2>Available Challenges</h2>
                <div class="filter-controls">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="easy">Easy</button>
                    <button class="filter-btn" data-filter="medium">Medium</button>
                    <button class="filter-btn" data-filter="hard">Hard</button>
                </div>
            </div>
            
            <div class="challenges-grid">
                <?php foreach ($challenges as $challenge): ?>
                    <?php $isCompleted = in_array($challenge['id'], $completedChallenges); ?>
                    <div class="challenge-card <?php echo $challenge['difficulty']; ?> <?php echo $isCompleted ? 'completed' : ''; ?>" 
                         data-difficulty="<?php echo $challenge['difficulty']; ?>"
                         data-type="<?php echo $challenge['challenge_type']; ?>">
                        <div class="challenge-header">
                            <div class="challenge-info">
                                <h3><?php echo htmlspecialchars($challenge['title']); ?></h3>
                                <span class="difficulty-badge <?php echo $challenge['difficulty']; ?>">
                                    <?php echo ucfirst($challenge['difficulty']); ?>
                                </span>
                            </div>
                            <div class="challenge-points">
                                <span class="points"><?php echo $challenge['points']; ?> pts</span>
                            </div>
                        </div>
                        
                        <div class="challenge-content">
                            <p><?php echo htmlspecialchars($challenge['description']); ?></p>
                            
                            <div class="challenge-meta">
                                <div class="meta-item">
                                    <i class="fas fa-tag"></i>
                                    <span><?php echo ucfirst($challenge['challenge_type']); ?></span>
                                </div>
                                <?php if ($challenge['api_required']): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-plug"></i>
                                        <span><?php echo ucfirst($challenge['api_required']); ?> API</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="challenge-actions">
                            <?php if ($isCompleted): ?>
                                <button class="btn btn-success" disabled>
                                    <i class="fas fa-check"></i> Completed
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary" onclick="startChallenge(<?php echo $challenge['id']; ?>)">
                                    <i class="fas fa-play"></i> Start Challenge
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Progress Section -->
    <section id="progress" class="progress-section">
        <div class="container">
            <h2>Your Progress</h2>
            <div class="progress-overview">
                <div class="progress-chart">
                    <canvas id="progressChart"></canvas>
                </div>
                <div class="progress-stats">
                    <div class="progress-stat">
                        <h3>Completion Rate</h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo count($challenges) > 0 ? (count($completedChallenges) / count($challenges)) * 100 : 0; ?>%"></div>
                        </div>
                        <p><?php echo count($completedChallenges); ?> of <?php echo count($challenges); ?> challenges completed</p>
                    </div>
                    
                    <div class="progress-stat">
                        <h3>Points Breakdown</h3>
                        <div class="points-breakdown">
                            <div class="point-item">
                                <span class="point-label">Easy (2 pts):</span>
                                <span class="point-value"><?php echo getCompletedPointsByDifficulty($userProgress, $challenges, 'easy'); ?></span>
                            </div>
                            <div class="point-item">
                                <span class="point-label">Medium (4 pts):</span>
                                <span class="point-value"><?php echo getCompletedPointsByDifficulty($userProgress, $challenges, 'medium'); ?></span>
                            </div>
                            <div class="point-item">
                                <span class="point-label">Hard (6 pts):</span>
                                <span class="point-value"><?php echo getCompletedPointsByDifficulty($userProgress, $challenges, 'hard'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <h2>Challenge Map</h2>
            <div class="map-container">
                <div id="challenge-map" class="challenge-map"></div>
                <div class="map-controls">
                    <button class="map-btn" onclick="centerOnUser()">
                        <i class="fas fa-crosshairs"></i> My Location
                    </button>
                    <button class="map-btn" onclick="showAllChallenges()">
                        <i class="fas fa-map"></i> All Challenges
                    </button>
                    <button class="map-btn" onclick="showNearbyChallenges()">
                        <i class="fas fa-location-arrow"></i> Nearby
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Multiplayer Section -->
    <section class="multiplayer-section">
        <div class="container">
            <h2>Multiplayer Mode</h2>
            <div class="multiplayer-options">
                <div class="multiplayer-card">
                    <div class="multiplayer-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <h3>Create Session</h3>
                    <p>Start a new multiplayer session and invite friends</p>
                    <button class="btn btn-primary" onclick="createMultiplayerSession()">
                        <i class="fas fa-plus"></i> Create Session
                    </button>
                </div>
                
                <div class="multiplayer-card">
                    <div class="multiplayer-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h3>Join Session</h3>
                    <p>Join an existing session with a session code</p>
                    <div class="join-form">
                        <input type="text" id="session-code" placeholder="Enter session code" maxlength="10">
                        <button class="btn btn-secondary" onclick="joinMultiplayerSession()">
                            <i class="fas fa-sign-in-alt"></i> Join
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Challenge Modal -->
    <div id="challenge-modal" class="modal">
        <div class="modal-content challenge-modal">
            <!-- Modal content will be dynamically generated -->
        </div>
    </div>

    <!-- Success Message -->
    <div id="success-message" class="message success-message" style="display: none;">
        <i class="fas fa-check-circle"></i>
        <span id="success-text"></span>
    </div>

    <!-- Error Message -->
    <div id="error-message" class="message error-message" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <span id="error-text"></span>
    </div>

    <!-- Loading Indicator -->
    <div id="loading" class="loading-indicator" style="display: none;">
        <div class="spinner"></div>
        <span id="loading-text">Loading...</span>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-content">
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
            <div class="loading-text">
                <h3 id="loading-message">Loading...</h3>
                <p id="loading-detail">Please wait while we prepare your adventure.</p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/challenges.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>

<?php
// Helper function to get completed points by difficulty
function getCompletedPointsByDifficulty($userProgress, $challenges, $difficulty) {
    $points = 0;
    $completedIds = array_column($userProgress, 'challenge_id');
    
    foreach ($challenges as $challenge) {
        if ($challenge['difficulty'] === $difficulty && in_array($challenge['id'], $completedIds)) {
            $points += $challenge['points'];
        }
    }
    
    return $points;
}
?> 
