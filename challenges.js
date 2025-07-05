// Challenges Management JavaScript for Adventure Hunt App

class ChallengeManager {
    constructor() {
        this.currentChallenge = null;
        this.userId = this.getUserId();
        this.currentLocation = null;
        this.challenges = [];
        this.userProgress = [];
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadChallenges();
        this.loadUserProgress();
        this.requestLocation();
    }
    
    // User Management
    getUserId() {
        // Get user ID from PHP session or localStorage
        return window.currentUserId || localStorage.getItem('user_id') || sessionStorage.getItem('user_id') || null;
    }
    
    setUserId(userId) {
        localStorage.setItem('user_id', userId);
        sessionStorage.setItem('user_id', userId);
        this.userId = userId;
    }
    
    // Event Listeners
    setupEventListeners() {
        // Challenge filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.filterChallenges(e.target.dataset.filter);
                this.updateActiveFilter(e.target);
            });
        });
        
        // Challenge start buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('start-challenge-btn')) {
                const challengeId = e.target.dataset.challengeId;
                this.startChallenge(challengeId);
            }
        });
        
        // Challenge completion buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('complete-challenge-btn')) {
                this.completeCurrentChallenge();
            }
        });
        
        // Modal close buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('close-modal') || e.target.classList.contains('modal-overlay')) {
                this.closeChallengeModal();
            }
        });
        
        // Location-based challenges
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('location-check-btn')) {
                this.checkLocationForChallenge();
            }
        });
        
        // Weather-based challenges
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('weather-check-btn')) {
                this.checkWeatherForChallenge();
            }
        });
    }
    
    updateActiveFilter(activeBtn) {
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        activeBtn.classList.add('active');
    }
    
    // API Calls
    async loadChallenges() {
        try {
            this.showLoading('Loading challenges...');
            
            const response = await fetch('api/challenges.php?action=all');
            const data = await response.json();
            
            if (data.success) {
                this.challenges = data.data;
                this.renderChallenges();
            } else {
                this.showError('Failed to load challenges: ' + data.error);
            }
        } catch (error) {
            this.showError('Network error: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    async loadUserProgress() {
        if (!this.userId) return;
        
        try {
            const response = await fetch(`api/challenges.php?action=user_progress&user_id=${this.userId}`);
            const data = await response.json();
            
            if (data.success) {
                this.userProgress = data.data;
                this.updateProgressDisplay();
            }
        } catch (error) {
            console.error('Failed to load user progress:', error);
        }
    }
    
    async startChallenge(challengeId) {
        try {
            this.showLoading('Loading challenge...');
            
            const response = await fetch(`api/challenges.php?action=get&id=${challengeId}`);
            const data = await response.json();
            
            if (data.success) {
                this.currentChallenge = data.data;
                this.showChallengeModal();
            } else {
                this.showError('Failed to load challenge: ' + data.error);
            }
        } catch (error) {
            this.showError('Network error: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    async completeCurrentChallenge() {
        if (!this.currentChallenge || !this.userId) {
            this.showError('No active challenge or user not logged in');
            return;
        }
        
        try {
            this.showLoading('Completing challenge...');
            
            const completionData = this.getChallengeCompletionData();
            
            const response = await fetch('api/challenges.php?action=complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_id: this.userId,
                    challenge_id: this.currentChallenge.id,
                    completion_data: completionData
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`Challenge completed! +${data.data.points_earned} points`);
                this.closeChallengeModal();
                this.loadUserProgress();
                this.updateProgressDisplay();
            } else {
                this.showError('Failed to complete challenge: ' + data.error);
            }
        } catch (error) {
            this.showError('Network error: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    getChallengeCompletionData() {
        const data = {
            completed_at: new Date().toISOString(),
            user_location: this.currentLocation
        };
        
        // Add challenge-specific data
        switch (this.currentChallenge.challenge_type) {
            case 'weather':
                data.weather_condition = this.getCurrentWeatherCondition();
                break;
            case 'location':
                data.location_verified = this.verifyLocation();
                break;
            case 'pokemon':
                data.pokemon_found = this.getCurrentPokemon();
                break;
            case 'news':
                data.news_article = this.getCurrentNewsArticle();
                break;
        }
        
        return data;
    }
    
    // Challenge Rendering
    renderChallenges() {
        const container = document.querySelector('.challenges-grid');
        if (!container) return;
        
        container.innerHTML = '';
        
        this.challenges.forEach(challenge => {
            const card = this.createChallengeCard(challenge);
            container.appendChild(card);
        });
    }
    
    createChallengeCard(challenge) {
        const card = document.createElement('div');
        card.className = 'challenge-card';
        card.dataset.type = challenge.challenge_type;
        card.dataset.difficulty = challenge.difficulty;
        
        const isCompleted = this.userProgress.some(p => p.challenge_id == challenge.id);
        
        card.innerHTML = `
            <div class="challenge-header">
                <h3>${challenge.title}</h3>
                <span class="difficulty-badge ${challenge.difficulty}">${challenge.difficulty}</span>
            </div>
            <p class="challenge-description">${challenge.description}</p>
            <div class="challenge-meta">
                <span class="points">${challenge.points} points</span>
                <span class="type-badge">${challenge.type}</span>
            </div>
            <div class="challenge-actions">
                ${isCompleted ? 
                    '<button class="completed-btn" disabled>✓ Completed</button>' :
                    '<button class="start-challenge-btn" data-challenge-id="' + challenge.id + '">Start Challenge</button>'
                }
            </div>
        `;
        
        return card;
    }
    
    filterChallenges(filter) {
        const cards = document.querySelectorAll('.challenge-card');
        
        cards.forEach(card => {
            if (filter === 'all' || card.dataset[filter]) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Challenge Modal
    showChallengeModal() {
        const modal = document.getElementById('challenge-modal');
        if (!modal) return;
        
        const content = this.generateChallengeContent();
        modal.querySelector('.modal-content').innerHTML = content;
        modal.classList.add('active');
        
        // Setup challenge-specific event listeners
        this.setupChallengeEventListeners();
    }
    
    generateChallengeContent() {
        if (!this.currentChallenge) return '';
        
        const challenge = this.currentChallenge;
        let content = `
            <div class="modal-header">
                <h2>${challenge.title}</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="challenge-description">${challenge.description}</p>
                <div class="challenge-details">
                    <span class="difficulty">${challenge.difficulty}</span>
                    <span class="points">${challenge.points} points</span>
                    <span class="type">${challenge.type}</span>
                </div>
        `;
        
        // Add challenge-specific content
        switch (challenge.challenge_type) {
            case 'weather':
                content += this.generateWeatherChallengeContent();
                break;
            case 'location':
                content += this.generateLocationChallengeContent();
                break;
            case 'pokemon':
                content += this.generatePokemonChallengeContent();
                break;
            case 'news':
                content += this.generateNewsChallengeContent();
                break;
            default:
                content += this.generateDefaultChallengeContent();
        }
        
        content += `
                <div class="challenge-actions">
                    <button class="complete-challenge-btn">Complete Challenge</button>
                </div>
            </div>
        `;
        
        return content;
    }
    
    generateWeatherChallengeContent() {
        return `
            <div class="weather-challenge">
                <h3>Current Weather</h3>
                <div id="current-weather" class="weather-display">
                    <div class="loading">Loading weather data...</div>
                </div>
                <button class="weather-check-btn">Check Weather</button>
            </div>
        `;
    }
    
    generateLocationChallengeContent() {
        return `
            <div class="location-challenge">
                <h3>Location Verification</h3>
                <div id="location-status" class="location-display">
                    <div class="loading">Checking location...</div>
                </div>
                <button class="location-check-btn">Verify Location</button>
            </div>
        `;
    }
    
    generatePokemonChallengeContent() {
        return `
            <div class="pokemon-challenge">
                <h3>Pokemon Challenge</h3>
                <div id="pokemon-display" class="pokemon-display">
                    <div class="loading">Loading Pokemon...</div>
                </div>
                <button class="pokemon-check-btn">Get Random Pokemon</button>
            </div>
        `;
    }
    
    generateNewsChallengeContent() {
        return `
            <div class="news-challenge">
                <h3>News Challenge</h3>
                <div id="news-display" class="news-display">
                    <div class="loading">Loading news...</div>
                </div>
                <button class="news-check-btn">Get Latest News</button>
            </div>
        `;
    }
    
    generateDefaultChallengeContent() {
        return `
            <div class="default-challenge">
                <p>Complete this challenge to earn points!</p>
            </div>
        `;
    }
    
    setupChallengeEventListeners() {
        // Weather challenge
        const weatherBtn = document.querySelector('.weather-check-btn');
        if (weatherBtn) {
            weatherBtn.addEventListener('click', () => this.loadWeatherData());
        }
        
        // Location challenge
        const locationBtn = document.querySelector('.location-check-btn');
        if (locationBtn) {
            locationBtn.addEventListener('click', () => this.checkLocationForChallenge());
        }
        
        // Pokemon challenge
        const pokemonBtn = document.querySelector('.pokemon-check-btn');
        if (pokemonBtn) {
            pokemonBtn.addEventListener('click', () => this.loadPokemonData());
        }
        
        // News challenge
        const newsBtn = document.querySelector('.news-check-btn');
        if (newsBtn) {
            newsBtn.addEventListener('click', () => this.loadNewsData());
        }
    }
    
    closeChallengeModal() {
        const modal = document.getElementById('challenge-modal');
        if (modal) {
            modal.classList.remove('active');
        }
        this.currentChallenge = null;
    }
    
    // Challenge-specific functionality
    async loadWeatherData() {
        if (!this.currentLocation) {
            this.showError('Location not available');
            return;
        }
        
        try {
            // Try simple weather API first (no API key required)
            const response = await fetch(`api/weather_simple.php?action=get_weather&lat=${this.currentLocation.lat}&lon=${this.currentLocation.lng}`);
            const data = await response.json();
            
            if (data.success) {
                this.updateWeatherDisplay(data.data);
            } else {
                // Fallback to original weather API
                const fallbackResponse = await fetch(`api/weather.php?action=get_weather&lat=${this.currentLocation.lat}&lon=${this.currentLocation.lng}`);
                const fallbackData = await fallbackResponse.json();
                
                if (fallbackData.success) {
                    this.updateWeatherDisplay(fallbackData.data);
                } else {
                    this.showError('Failed to load weather: ' + fallbackData.error);
                }
            }
        } catch (error) {
            this.showError('Weather API error: ' + error.message);
        }
    }
    
    async loadPokemonData() {
        try {
            const response = await fetch('api/pokemon.php?action=random');
            const data = await response.json();
            
            if (data.success) {
                this.updatePokemonDisplay(data.data);
            } else {
                this.showError('Failed to load Pokemon: ' + data.error);
            }
        } catch (error) {
            this.showError('Pokemon API error: ' + error.message);
        }
    }
    
    async loadNewsData() {
        try {
            const response = await fetch('api/news.php?action=latest&pageSize=1');
            const data = await response.json();
            
            if (data.success && data.data.articles.length > 0) {
                this.updateNewsDisplay(data.data.articles[0]);
            } else {
                this.showError('Failed to load news: ' + data.error);
            }
        } catch (error) {
            this.showError('News API error: ' + error.message);
        }
    }
    
    updateWeatherDisplay(weatherData) {
        const display = document.getElementById('current-weather');
        if (display) {
            display.innerHTML = `
                <div class="weather-info">
                    <div class="temperature">${weatherData.temperature}°C</div>
                    <div class="condition">${weatherData.condition}</div>
                    <div class="description">${weatherData.description}</div>
                    <div class="details">
                        <span>Humidity: ${weatherData.humidity}%</span>
                        <span>Wind: ${weatherData.wind_speed} m/s</span>
                    </div>
                </div>
            `;
        }
    }
    
    updatePokemonDisplay(pokemonData) {
        const display = document.getElementById('pokemon-display');
        if (display) {
            display.innerHTML = `
                <div class="pokemon-info">
                    <img src="${pokemonData.sprite}" alt="${pokemonData.name}" class="pokemon-sprite">
                    <h4>${pokemonData.name}</h4>
                    <div class="pokemon-details">
                        <span>Type: ${pokemonData.types.join(', ')}</span>
                        <span>Height: ${pokemonData.height} dm</span>
                        <span>Weight: ${pokemonData.weight} hg</span>
                    </div>
                </div>
            `;
        }
    }
    
    updateNewsDisplay(newsArticle) {
        const display = document.getElementById('news-display');
        if (display) {
            display.innerHTML = `
                <div class="news-info">
                    <h4>${newsArticle.title}</h4>
                    <p>${newsArticle.description}</p>
                    <div class="news-meta">
                        <span>Source: ${newsArticle.source}</span>
                        <span>Published: ${new Date(newsArticle.publishedAt).toLocaleDateString()}</span>
                    </div>
                </div>
            `;
        }
    }
    
    // Location handling
    requestLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.currentLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    this.updateLocationDisplay();
                },
                (error) => {
                    console.error('Location error:', error);
                    this.showError('Location access denied');
                }
            );
        } else {
            this.showError('Geolocation not supported');
        }
    }
    
    updateLocationDisplay() {
        const display = document.getElementById('location-status');
        if (display && this.currentLocation) {
            display.innerHTML = `
                <div class="location-info">
                    <span>Latitude: ${this.currentLocation.lat.toFixed(4)}</span>
                    <span>Longitude: ${this.currentLocation.lng.toFixed(4)}</span>
                </div>
            `;
        }
    }
    
    checkLocationForChallenge() {
        if (!this.currentLocation) {
            this.showError('Location not available');
            return;
        }
        
        this.updateLocationDisplay();
        this.showSuccess('Location verified!');
    }
    
    // Progress tracking
    updateProgressDisplay() {
        const totalCompleted = this.userProgress.length;
        const totalPoints = this.userProgress.reduce((sum, p) => sum + parseInt(p.score), 0);
        
        // Update progress counters
        const completedElement = document.getElementById('completed-count');
        if (completedElement) {
            completedElement.textContent = totalCompleted;
        }
        
        const pointsElement = document.getElementById('total-points');
        if (pointsElement) {
            pointsElement.textContent = totalPoints;
        }
        
        // Update progress chart if it exists
        this.updateProgressChart();
    }
    
    updateProgressChart() {
        // Implementation for progress chart visualization
        // This would integrate with Chart.js or similar library
    }
    
    // Utility functions
    showLoading(message = 'Loading...') {
        this.isLoading = true;
        const loadingEl = document.getElementById('loading');
        if (loadingEl) {
            loadingEl.textContent = message;
            loadingEl.style.display = 'block';
        }
    }
    
    hideLoading() {
        this.isLoading = false;
        const loadingEl = document.getElementById('loading');
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
    }
    
    showError(message) {
        const errorEl = document.getElementById('error-message');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
            setTimeout(() => {
                errorEl.style.display = 'none';
            }, 5000);
        }
    }
    
    showSuccess(message) {
        const successEl = document.getElementById('success-message');
        if (successEl) {
            successEl.textContent = message;
            successEl.style.display = 'block';
            setTimeout(() => {
                successEl.style.display = 'none';
            }, 3000);
        }
    }
}

// Initialize challenge manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.challengeManager = new ChallengeManager();
});

// Global functions for onclick handlers
function startChallenge(challengeId) {
    if (window.challengeManager) {
        window.challengeManager.startChallenge(challengeId);
    }
}

function completeChallenge() {
    if (window.challengeManager) {
        window.challengeManager.completeCurrentChallenge();
    }
}

function closeChallengeModal() {
    if (window.challengeManager) {
        window.challengeManager.closeChallengeModal();
    }
} 