<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adventure Hunt - Location-Aware Scavenger Hunt</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAUtbajb0ykdFBVRJGuuHyKReSO1cdshns&libraries=places"></script>
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
                <a href="#home" class="nav-link">Home</a>
                <a href="#challenges" class="nav-link">Challenges</a>
                <a href="#leaderboard" class="nav-link">Leaderboard</a>
                <a href="#about" class="nav-link">About</a>
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link btn-primary">Sign Up</a>
                <?php endif; ?>
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

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <span class="gradient-text">Adventure Hunt</span>
                    <br>Location-Aware Scavenger Hunt
                </h1>
                <p class="hero-description">
                    Embark on an epic journey combining real-world exploration with digital challenges. 
                    Use weather data, Pokémon encounters, and news updates to complete exciting missions!
                </p>
                <div class="hero-buttons">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-play"></i> Start Adventure
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Get Started
                        </a>
                        <a href="login.php" class="btn btn-secondary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-map">
                    <div id="map" class="map-container"></div>
                    <div class="map-overlay">
                        <div class="weather-widget">
                            <i class="fas fa-sun weather-icon"></i>
                            <span class="weather-temp">22°C</span>
                        </div>
                        <div class="pokemon-widget">
                            <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png" alt="Pikachu" class="pokemon-sprite">
                            <span class="pokemon-name">Pikachu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose Adventure Hunt?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Location-Based Challenges</h3>
                    <p>Real-world locations combined with GPS tracking for authentic exploration experiences.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-cloud-sun"></i>
                    </div>
                    <h3>Weather Integration</h3>
                    <p>Dynamic challenges that adapt to current weather conditions in your area.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-dragon"></i>
                    </div>
                    <h3>Pokémon Encounters</h3>
                    <p>Discover and catch Pokémon in the real world using our integrated PokeAPI.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3>News Challenges</h3>
                    <p>Stay informed with location-based news challenges and current events.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Multiplayer Mode</h3>
                    <p>Compete or collaborate with friends in real-time parallel interaction mode.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-volume-up"></i>
                    </div>
                    <h3>Voice Navigation</h3>
                    <p>Accessible voice commands and text-to-speech for hands-free exploration.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Challenge Types Section -->
    <section id="challenges" class="challenges">
        <div class="container">
            <h2 class="section-title">Challenge Types</h2>
            <div class="challenge-types">
                <div class="challenge-type easy">
                    <div class="challenge-header">
                        <h3>Easy Challenges (2 pts)</h3>
                        <span class="difficulty-badge easy">Easy</span>
                    </div>
                    <ul>
                        <li>Creative 404/Error Handling</li>
                        <li>Dark Mode Support</li>
                        <li>Custom Loading States</li>
                        <li>Weather-based location finding</li>
                        <li>Pokémon discovery missions</li>
                    </ul>
                </div>
                <div class="challenge-type medium">
                    <div class="challenge-header">
                        <h3>Medium Challenges (4 pts)</h3>
                        <span class="difficulty-badge medium">Medium</span>
                    </div>
                    <ul>
                        <li>Dynamic Theming Based on API Data</li>
                        <li>Multilingual Support</li>
                        <li>The Story Mode</li>
                        <li>News-based exploration</li>
                        <li>Weather condition matching</li>
                    </ul>
                </div>
                <div class="challenge-type hard">
                    <div class="challenge-header">
                        <h3>Hard Challenges (6 pts)</h3>
                        <span class="difficulty-badge hard">Hard</span>
                    </div>
                    <ul>
                        <li>Voice Navigation & Accessibility</li>
                        <li>Text-to-Speech for Content</li>
                        <li>Parallel Interaction Mode</li>
                        <li>Offline-first handling</li>
                        <li>Real-time collaboration</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Story Mode Section -->
    <section class="story-mode">
        <div class="container">
            <div class="story-content">
                <div class="story-text">
                    <h2>The Adventure Begins...</h2>
                    <p>Welcome, brave explorer! You are about to embark on a journey unlike any other. 
                    Our AI guide, <strong>AdventureBot</strong>, will be your companion throughout this epic quest.</p>
                    
                    <div class="story-character">
                        <div class="character-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="character-speech">
                            <p>"Greetings, adventurer! I'm AdventureBot, your AI guide. Together, we'll explore the world, 
                            discover hidden treasures, and complete amazing challenges. Are you ready to begin?"</p>
                        </div>
                    </div>
                    
                    <div class="story-features">
                        <div class="story-feature">
                            <i class="fas fa-microphone"></i>
                            <span>Voice-guided navigation</span>
                        </div>
                        <div class="story-feature">
                            <i class="fas fa-book-open"></i>
                            <span>Interactive storytelling</span>
                        </div>
                        <div class="story-feature">
                            <i class="fas fa-magic"></i>
                            <span>Dynamic content adaptation</span>
                        </div>
                    </div>
                </div>
                <div class="story-visual">
                    <div class="story-animation">
                        <div class="floating-elements">
                            <div class="floating-icon" style="--delay: 0s">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="floating-icon" style="--delay: 1s">
                                <i class="fas fa-cloud-sun"></i>
                            </div>
                            <div class="floating-icon" style="--delay: 2s">
                                <i class="fas fa-dragon"></i>
                            </div>
                            <div class="floating-icon" style="--delay: 3s">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Accessibility Features -->
    <section class="accessibility">
        <div class="container">
            <h2 class="section-title">Accessibility Features</h2>
            <div class="accessibility-grid">
                <div class="accessibility-card">
                    <div class="accessibility-icon">
                        <i class="fas fa-volume-up"></i>
                    </div>
                    <h3>Voice Navigation</h3>
                    <p>Navigate the app using voice commands and receive audio feedback for all interactions.</p>
                    <button class="btn btn-secondary" onclick="toggleVoiceNavigation()">
                        <i class="fas fa-microphone"></i> Enable Voice
                    </button>
                </div>
                <div class="accessibility-card">
                    <div class="accessibility-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Colorblind Support</h3>
                    <p>Special themes and high contrast modes for users with color vision deficiencies.</p>
                    <button class="btn btn-secondary" onclick="toggleColorblindMode()">
                        <i class="fas fa-palette"></i> Enable High Contrast
                    </button>
                </div>
                <div class="accessibility-card">
                    <div class="accessibility-icon">
                        <i class="fas fa-language"></i>
                    </div>
                    <h3>Multilingual Support</h3>
                    <p>Seamless translation into multiple languages for global accessibility.</p>
                    <select class="language-selector" onchange="changeLanguage(this.value)">
                        <option value="en">English</option>
                        <option value="es">Español</option>
                        <option value="fr">Français</option>
                        <option value="de">Deutsch</option>
                        <option value="ja">日本語</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Adventure Hunt</h3>
                    <p>Location-aware scavenger hunt app combining real-world exploration with digital challenges.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#challenges">Challenges</a></li>
                        <li><a href="#leaderboard">Leaderboard</a></li>
                        <li><a href="#about">About</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>APIs Used</h4>
                    <ul>
                        <li>OpenWeatherMap</li>
                        <li>PokeAPI</li>
                        <li>NewsAPI</li>
                        <li>Google Maps</li>
                        <li>Giphy</li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>Email: info@adventurehunt.com</p>
                    <p>Phone: +1 (555) 123-4567</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Adventure Hunt. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-content">
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
            <div class="loading-text">
                <h3>Loading Adventure...</h3>
                <p id="loading-fact">Did you know? The first scavenger hunt was created in 1930!</p>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Oops! Something went wrong</h3>
                <button class="modal-close" onclick="closeErrorModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="error-illustration">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p id="error-message">We're experiencing some technical difficulties. Please try again later.</p>
                <div class="error-actions">
                    <button class="btn btn-primary" onclick="retryAction()">Retry</button>
                    <button class="btn btn-secondary" onclick="closeErrorModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/accessibility.js"></script>
    <script src="assets/js/voice.js"></script>
</body>
</html>
