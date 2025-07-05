// Dashboard JavaScript for Adventure Hunt App

class DashboardManager {
    constructor() {
        this.currentChallenge = null;
        this.challengeMap = null;
        this.progressChart = null;
        this.currentLocation = null;
        this.challenges = [];
        this.userProgress = [];
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupMap();
        this.setupProgressChart();
        this.setupFilters();
        this.loadChallenges();
        this.requestLocation();
    }

    // Event Listeners
    setupEventListeners() {
        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.filterChallenges(e.target.dataset.filter);
                
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
            });
        });

        // Theme toggle
        const themeBtn = document.getElementById('theme-toggle');
        if (themeBtn) {
            themeBtn.addEventListener('click', () => this.toggleTheme());
        }

        // Mobile menu toggle
        const menuBtn = document.getElementById('menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        if (menuBtn && navMenu) {
            menuBtn.addEventListener('click', () => {
                navMenu.classList.toggle('active');
            });
        }

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // Map Setup
    setupMap() {
        if (typeof google !== 'undefined' && google.maps) {
            const mapElement = document.getElementById('challenge-map');
            if (mapElement) {
                this.challengeMap = new google.maps.Map(mapElement, {
                    center: { lat: 40.7589, lng: -73.9851 }, // Default to NYC
                    zoom: 12,
                    styles: this.getMapStyles(),
                    disableDefaultUI: true,
                    zoomControl: true
                });

                this.addChallengeMarkers();
            }
        }
    }

    getMapStyles() {
        return [
            {
                featureType: 'all',
                elementType: 'geometry',
                stylers: [{ color: '#242f3e' }]
            },
            {
                featureType: 'all',
                elementType: 'labels.text.stroke',
                stylers: [{ color: '#242f3e' }]
            },
            {
                featureType: 'all',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#746855' }]
            },
            {
                featureType: 'administrative.locality',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#d59563' }]
            },
            {
                featureType: 'poi',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#d59563' }]
            },
            {
                featureType: 'poi.park',
                elementType: 'geometry',
                stylers: [{ color: '#263c3f' }]
            },
            {
                featureType: 'poi.park',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#6b9a76' }]
            },
            {
                featureType: 'road',
                elementType: 'geometry',
                stylers: [{ color: '#38414e' }]
            },
            {
                featureType: 'road',
                elementType: 'geometry.stroke',
                stylers: [{ color: '#212a37' }]
            },
            {
                featureType: 'road',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#9ca5b3' }]
            },
            {
                featureType: 'road.highway',
                elementType: 'geometry',
                stylers: [{ color: '#746855' }]
            },
            {
                featureType: 'road.highway',
                elementType: 'geometry.stroke',
                stylers: [{ color: '#1f2835' }]
            },
            {
                featureType: 'road.highway',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#f3d19c' }]
            },
            {
                featureType: 'transit',
                elementType: 'geometry',
                stylers: [{ color: '#2f3948' }]
            },
            {
                featureType: 'transit.station',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#d59563' }]
            },
            {
                featureType: 'water',
                elementType: 'geometry',
                stylers: [{ color: '#17263c' }]
            },
            {
                featureType: 'water',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#515c6d' }]
            },
            {
                featureType: 'water',
                elementType: 'labels.text.stroke',
                stylers: [{ color: '#17263c' }]
            }
        ];
    }

    addChallengeMarkers() {
        // Get challenge data from the page
        const challengeCards = document.querySelectorAll('.challenge-card');
        
        challengeCards.forEach(card => {
            const challengeId = card.querySelector('button')?.getAttribute('onclick')?.match(/\d+/)?.[0];
            const difficulty = card.dataset.difficulty;
            const type = card.dataset.type;
            
            // For demo purposes, create random locations around NYC
            const lat = 40.7589 + (Math.random() - 0.5) * 0.1;
            const lng = -73.9851 + (Math.random() - 0.5) * 0.1;
            
            const marker = new google.maps.Marker({
                position: { lat, lng },
                map: this.challengeMap,
                title: card.querySelector('h3').textContent,
                icon: this.getMarkerIcon(type, difficulty),
                animation: google.maps.Animation.DROP
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div class="map-info-window">
                        <h3>${card.querySelector('h3').textContent}</h3>
                        <p>${card.querySelector('p').textContent}</p>
                        <div class="map-info-meta">
                            <span class="difficulty-badge ${difficulty}">${difficulty}</span>
                            <span class="type-badge">${type}</span>
                        </div>
                        <button onclick="startChallenge(${challengeId})" class="btn btn-primary btn-sm">
                            Start Challenge
                        </button>
                    </div>
                `
            });

            marker.addListener('click', () => {
                infoWindow.open(this.challengeMap, marker);
            });
        });
    }

    getMarkerIcon(type, difficulty) {
        const icons = {
            location: '📍',
            weather: '🌤️',
            pokemon: '⚡',
            news: '📰'
        };
        
        const colors = {
            easy: '#10b981',
            medium: '#f59e0b',
            hard: '#ef4444'
        };
        
        return {
            url: `data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="${colors[difficulty]}"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>`,
            scaledSize: new google.maps.Size(30, 30)
        };
    }

    // Progress Chart
    setupProgressChart() {
        const ctx = document.getElementById('progressChart');
        if (ctx) {
            this.progressChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Easy', 'Medium', 'Hard'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: [
                            '#10b981',
                            '#f59e0b',
                            '#ef4444'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary')
                            }
                        }
                    }
                }
            });
        }
    }

    updateProgressChart() {
        if (this.progressChart) {
            const easyPoints = this.getCompletedPointsByDifficulty('easy');
            const mediumPoints = this.getCompletedPointsByDifficulty('medium');
            const hardPoints = this.getCompletedPointsByDifficulty('hard');
            
            this.progressChart.data.datasets[0].data = [easyPoints, mediumPoints, hardPoints];
            this.progressChart.update();
        }
    }

    getCompletedPointsByDifficulty(difficulty) {
        const completedCards = document.querySelectorAll(`.challenge-card.${difficulty}.completed`);
        let points = 0;
        
        completedCards.forEach(card => {
            const pointsText = card.querySelector('.points').textContent;
            points += parseInt(pointsText.match(/\d+/)[0]);
        });
        
        return points;
    }

    // Challenge Filtering
    setupFilters() {
        // Initialize with all challenges visible
        this.filterChallenges('all');
    }

    filterChallenges(difficulty) {
        const cards = document.querySelectorAll('.challenge-card');
        
        cards.forEach(card => {
            if (difficulty === 'all' || card.dataset.difficulty === difficulty) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    // Challenge Management
    loadChallenges() {
        // Challenges are already loaded from PHP
        // This function can be used for dynamic loading if needed
        this.updateProgressChart();
    }

    startChallenge(challengeId) {
        this.showLoading('Loading challenge...');
        
        // Simulate API call
        setTimeout(() => {
            this.loadChallengeData(challengeId);
            this.hideLoading();
        }, 1000);
    }

    loadChallengeData(challengeId) {
        // Get challenge data from the DOM
        const challengeCard = document.querySelector(`[onclick="startChallenge(${challengeId})"]`)?.closest('.challenge-card');
        
        if (challengeCard) {
            const title = challengeCard.querySelector('h3').textContent;
            const description = challengeCard.querySelector('p').textContent;
            const difficulty = challengeCard.dataset.difficulty;
            const type = challengeCard.dataset.type;
            
            this.currentChallenge = {
                id: challengeId,
                title: title,
                description: description,
                difficulty: difficulty,
                type: type
            };
            
            this.showChallengeModal();
        }
    }

    showChallengeModal() {
        const modal = document.getElementById('challenge-modal');
        const title = document.getElementById('challenge-title');
        const content = document.getElementById('challenge-content');
        
        if (modal && this.currentChallenge) {
            title.textContent = this.currentChallenge.title;
            
            // Generate challenge content based on type
            content.innerHTML = this.generateChallengeContent(this.currentChallenge);
            
            modal.classList.add('active');
        }
    }

    generateChallengeContent(challenge) {
        switch (challenge.type) {
            case 'weather':
                return this.generateWeatherChallenge(challenge);
            case 'pokemon':
                return this.generatePokemonChallenge(challenge);
            case 'news':
                return this.generateNewsChallenge(challenge);
            case 'location':
                return this.generateLocationChallenge(challenge);
            default:
                return this.generateDefaultChallenge(challenge);
        }
    }

    generateWeatherChallenge(challenge) {
        return `
            <div class="challenge-content-weather">
                <div class="weather-display">
                    <i class="fas fa-cloud-sun weather-icon"></i>
                    <div class="weather-info">
                        <h4>Current Weather</h4>
                        <p>Check the current weather conditions in your area</p>
                    </div>
                </div>
                <div class="challenge-instructions">
                    <h4>Instructions:</h4>
                    <ol>
                        <li>Allow location access to get your current weather</li>
                        <li>Check if the weather matches the challenge requirements</li>
                        <li>Take a photo or screenshot as proof</li>
                        <li>Click "Complete Challenge" when ready</li>
                    </ol>
                </div>
                <div class="weather-check">
                    <button class="btn btn-secondary" onclick="checkWeather()">
                        <i class="fas fa-sync-alt"></i> Check Weather
                    </button>
                </div>
            </div>
        `;
    }

    generatePokemonChallenge(challenge) {
        return `
            <div class="challenge-content-pokemon">
                <div class="pokemon-display">
                    <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png" alt="Pokemon" class="pokemon-sprite">
                    <div class="pokemon-info">
                        <h4>Pokemon Encounter</h4>
                        <p>Discover and catch a Pokemon in the wild!</p>
                    </div>
                </div>
                <div class="challenge-instructions">
                    <h4>Instructions:</h4>
                    <ol>
                        <li>Explore your surroundings for Pokemon</li>
                        <li>Use the Pokemon API to get random Pokemon</li>
                        <li>Learn about the Pokemon's abilities and types</li>
                        <li>Complete the challenge when satisfied</li>
                    </ol>
                </div>
                <div class="pokemon-actions">
                    <button class="btn btn-secondary" onclick="getRandomPokemon()">
                        <i class="fas fa-dice"></i> Get Random Pokemon
                    </button>
                </div>
            </div>
        `;
    }

    generateNewsChallenge(challenge) {
        return `
            <div class="challenge-content-news">
                <div class="news-display">
                    <i class="fas fa-newspaper news-icon"></i>
                    <div class="news-info">
                        <h4>Latest News</h4>
                        <p>Stay informed with the latest news updates</p>
                    </div>
                </div>
                <div class="challenge-instructions">
                    <h4>Instructions:</h4>
                    <ol>
                        <li>Read the latest news from the News API</li>
                        <li>Find an interesting article</li>
                        <li>Share your thoughts or take notes</li>
                        <li>Complete the challenge when done</li>
                    </ol>
                </div>
                <div class="news-actions">
                    <button class="btn btn-secondary" onclick="getLatestNews()">
                        <i class="fas fa-newspaper"></i> Get Latest News
                    </button>
                </div>
            </div>
        `;
    }

    generateLocationChallenge(challenge) {
        return `
            <div class="challenge-content-location">
                <div class="location-display">
                    <i class="fas fa-map-marker-alt location-icon"></i>
                    <div class="location-info">
                        <h4>Location Challenge</h4>
                        <p>Visit a specific location to complete this challenge</p>
                    </div>
                </div>
                <div class="challenge-instructions">
                    <h4>Instructions:</h4>
                    <ol>
                        <li>Navigate to the marked location on the map</li>
                        <li>Use GPS to confirm your arrival</li>
                        <li>Take a photo at the location</li>
                        <li>Complete the challenge when you arrive</li>
                    </ol>
                </div>
                <div class="location-actions">
                    <button class="btn btn-secondary" onclick="checkLocation()">
                        <i class="fas fa-crosshairs"></i> Check Location
                    </button>
                </div>
            </div>
        `;
    }

    generateDefaultChallenge(challenge) {
        return `
            <div class="challenge-content-default">
                <div class="challenge-instructions">
                    <h4>Instructions:</h4>
                    <p>${challenge.description}</p>
                    <ol>
                        <li>Read the challenge requirements carefully</li>
                        <li>Complete the necessary tasks</li>
                        <li>Provide proof of completion</li>
                        <li>Click "Complete Challenge" when ready</li>
                    </ol>
                </div>
            </div>
        `;
    }

    completeChallenge() {
        if (!this.currentChallenge) return;
        
        this.showLoading('Completing challenge...');
        
        // Simulate API call to complete challenge
        setTimeout(() => {
            this.markChallengeAsCompleted(this.currentChallenge.id);
            this.closeChallengeModal();
            this.hideLoading();
            this.showSuccessMessage(`Challenge "${this.currentChallenge.title}" completed!`);
        }, 1500);
    }

    markChallengeAsCompleted(challengeId) {
        const challengeCard = document.querySelector(`[onclick="startChallenge(${challengeId})"]`)?.closest('.challenge-card');
        
        if (challengeCard) {
            challengeCard.classList.add('completed');
            
            // Update button
            const button = challengeCard.querySelector('button');
            button.innerHTML = '<i class="fas fa-check"></i> Completed';
            button.className = 'btn btn-success';
            button.disabled = true;
            button.removeAttribute('onclick');
            
            // Update progress
            this.updateProgressChart();
            this.updateUserStats();
        }
    }

    updateUserStats() {
        // Update completion count
        const completedCount = document.querySelectorAll('.challenge-card.completed').length;
        const completedElement = document.querySelector('.stat-card:last-child .stat-info h3');
        if (completedElement) {
            completedElement.textContent = completedCount;
        }
        
        // Update total score
        const totalScore = this.calculateTotalScore();
        const scoreElement = document.querySelector('.stat-card:first-child .stat-info h3');
        if (scoreElement) {
            scoreElement.textContent = totalScore;
        }
    }

    calculateTotalScore() {
        let total = 0;
        const completedCards = document.querySelectorAll('.challenge-card.completed');
        
        completedCards.forEach(card => {
            const pointsText = card.querySelector('.points').textContent;
            total += parseInt(pointsText.match(/\d+/)[0]);
        });
        
        return total;
    }

    closeChallengeModal() {
        const modal = document.getElementById('challenge-modal');
        if (modal) {
            modal.classList.remove('active');
            this.currentChallenge = null;
        }
    }

    // Location Services
    requestLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.currentLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    this.centerOnUser();
                },
                (error) => {
                    console.log('Geolocation error:', error);
                }
            );
        }
    }

    centerOnUser() {
        if (this.challengeMap && this.currentLocation) {
            this.challengeMap.setCenter(this.currentLocation);
            this.challengeMap.setZoom(15);
        }
    }

    showAllChallenges() {
        if (this.challengeMap) {
            this.challengeMap.setZoom(12);
            this.challengeMap.setCenter({ lat: 40.7589, lng: -73.9851 });
        }
    }

    showNearbyChallenges() {
        if (this.currentLocation && this.challengeMap) {
            this.challengeMap.setCenter(this.currentLocation);
            this.challengeMap.setZoom(14);
        }
    }

    // Multiplayer Functions
    createMultiplayerSession() {
        const sessionCode = this.generateSessionCode();
        this.showNotification(`Multiplayer session created! Code: ${sessionCode}`, 'success');
        
        // In a real app, this would create a session in the database
        console.log('Created multiplayer session:', sessionCode);
    }

    joinMultiplayerSession() {
        const sessionCode = document.getElementById('session-code').value.trim();
        
        if (!sessionCode) {
            this.showNotification('Please enter a session code', 'error');
            return;
        }
        
        this.showLoading('Joining session...');
        
        // Simulate joining session
        setTimeout(() => {
            this.hideLoading();
            this.showNotification(`Joined session: ${sessionCode}`, 'success');
        }, 1000);
    }

    generateSessionCode() {
        return Math.random().toString(36).substring(2, 8).toUpperCase();
    }

    // Utility Functions
    toggleTheme() {
        if (window.adventureHunt) {
            window.adventureHunt.toggleTheme();
        }
    }

    showLoading(message = 'Loading...') {
        const overlay = document.getElementById('loading-overlay');
        const messageEl = document.getElementById('loading-message');
        
        if (overlay && messageEl) {
            messageEl.textContent = message;
            overlay.classList.add('active');
        }
    }

    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    }

    showSuccessMessage(message) {
        this.showNotification(message, 'success');
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardManager = new DashboardManager();
});

// Global functions for HTML onclick handlers
function startChallenge(challengeId) {
    if (window.dashboardManager) {
        window.dashboardManager.startChallenge(challengeId);
    }
}

function completeChallenge() {
    if (window.dashboardManager) {
        window.dashboardManager.completeChallenge();
    }
}

function closeChallengeModal() {
    if (window.dashboardManager) {
        window.dashboardManager.closeChallengeModal();
    }
}

function startRandomChallenge() {
    const availableChallenges = document.querySelectorAll('.challenge-card:not(.completed)');
    if (availableChallenges.length > 0) {
        const randomChallenge = availableChallenges[Math.floor(Math.random() * availableChallenges.length)];
        const button = randomChallenge.querySelector('button');
        const challengeId = button.getAttribute('onclick').match(/\d+/)[0];
        startChallenge(challengeId);
    } else {
        window.dashboardManager?.showNotification('No available challenges!', 'info');
    }
}

function viewNearbyChallenges() {
    window.dashboardManager?.showNearbyChallenges();
}

function startMultiplayer() {
    // Scroll to multiplayer section
    document.querySelector('.multiplayer-section').scrollIntoView({ behavior: 'smooth' });
}

function viewProgress() {
    // Scroll to progress section
    document.querySelector('#progress').scrollIntoView({ behavior: 'smooth' });
}

function centerOnUser() {
    window.dashboardManager?.centerOnUser();
}

function showAllChallenges() {
    window.dashboardManager?.showAllChallenges();
}

function showNearbyChallenges() {
    window.dashboardManager?.showNearbyChallenges();
}

function createMultiplayerSession() {
    window.dashboardManager?.createMultiplayerSession();
}

function joinMultiplayerSession() {
    window.dashboardManager?.joinMultiplayerSession();
}

// Challenge-specific functions
function checkWeather() {
    if (window.dashboardManager?.currentLocation) {
        const { lat, lng } = window.dashboardManager.currentLocation;
        fetch(`api/weather.php?action=get_weather&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.dashboardManager.showNotification(`Weather: ${data.data.condition}, ${Math.round(data.data.temperature)}°C`, 'success');
                } else {
                    window.dashboardManager.showNotification('Failed to get weather data', 'error');
                }
            })
            .catch(error => {
                window.dashboardManager.showNotification('Weather API error', 'error');
            });
    } else {
        window.dashboardManager.showNotification('Location not available', 'error');
    }
}

function getRandomPokemon() {
    fetch('api/pokemon.php?action=random')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pokemon = data.data;
                window.dashboardManager.showNotification(`Found ${pokemon.name}! Type: ${pokemon.types.join(', ')}`, 'success');
            } else {
                window.dashboardManager.showNotification('Failed to get Pokemon data', 'error');
            }
        })
        .catch(error => {
            window.dashboardManager.showNotification('Pokemon API error', 'error');
        });
}

function getLatestNews() {
    fetch('api/news.php?action=latest&pageSize=1')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const news = data.data.articles[0];
                window.dashboardManager.showNotification(`Latest: ${news.title}`, 'success');
            } else {
                window.dashboardManager.showNotification('Failed to get news data', 'error');
            }
        })
        .catch(error => {
            window.dashboardManager.showNotification('News API error', 'error');
        });
}

function checkLocation() {
    if (window.dashboardManager?.currentLocation) {
        window.dashboardManager.showNotification('Location confirmed! You are at the challenge location.', 'success');
    } else {
        window.dashboardManager.showNotification('Location not available', 'error');
    }
} 