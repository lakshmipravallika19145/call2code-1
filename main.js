// Main JavaScript for Adventure Hunt App

class AdventureHuntApp {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'light';
        this.isLoading = false;
        this.currentLocation = null;
        this.weatherData = null;
        this.pokemonData = null;
        this.newsData = null;
        
        this.init();
    }

    init() {
        this.setupTheme();
        this.setupEventListeners();
        this.setupMap();
        this.loadInitialData();
        this.setupLoadingStates();
        this.setupErrorHandling();
    }

    // Theme Management
    setupTheme() {
        document.documentElement.setAttribute('data-theme', this.currentTheme);
        this.updateThemeIcon();
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        localStorage.setItem('theme', this.currentTheme);
        document.documentElement.setAttribute('data-theme', this.currentTheme);
        this.updateThemeIcon();
        this.updateThemeBasedOnWeather();
    }

    updateThemeIcon() {
        const themeBtn = document.getElementById('theme-toggle');
        if (themeBtn) {
            const icon = themeBtn.querySelector('i');
            icon.className = this.currentTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }
    }

    updateThemeBasedOnWeather() {
        if (this.weatherData) {
            const condition = this.weatherData.condition.toLowerCase();
            const hour = new Date().getHours();
            
            // Dynamic theming based on weather and time
            if (condition.includes('rain') || condition.includes('storm')) {
                document.body.style.setProperty('--primary-color', '#3b82f6');
                document.body.style.setProperty('--bg-secondary', '#1e3a8a');
            } else if (condition.includes('sun') || condition.includes('clear')) {
                if (hour >= 6 && hour <= 18) {
                    document.body.style.setProperty('--primary-color', '#f59e0b');
                    document.body.style.setProperty('--bg-secondary', '#fef3c7');
                } else {
                    document.body.style.setProperty('--primary-color', '#6366f1');
                    document.body.style.setProperty('--bg-secondary', '#1e1b4b');
                }
            }
        }
    }

    // Event Listeners
    setupEventListeners() {
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

        // Smooth scrolling for navigation links
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

        // Window scroll effects
        window.addEventListener('scroll', () => {
            this.handleScroll();
        });

        // Geolocation permission
        this.requestLocationPermission();
    }

    // Map Setup
    setupMap() {
        if (typeof google !== 'undefined' && google.maps) {
            const mapElement = document.getElementById('map');
            if (mapElement) {
                const map = new google.maps.Map(mapElement, {
                    center: { lat: 40.7589, lng: -73.9851 }, // Default to NYC
                    zoom: 12,
                    styles: this.getMapStyles(),
                    disableDefaultUI: true,
                    zoomControl: true
                });

                this.map = map;
                this.addMapMarkers();
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

    addMapMarkers() {
        // Add sample challenge markers
        const challenges = [
            { lat: 40.7589, lng: -73.9851, title: 'Central Park Challenge', type: 'location' },
            { lat: 40.7484, lng: -73.9857, title: 'Weather Challenge', type: 'weather' },
            { lat: 40.7505, lng: -73.9934, title: 'Pokemon Hunt', type: 'pokemon' }
        ];

        challenges.forEach(challenge => {
            const marker = new google.maps.Marker({
                position: { lat: challenge.lat, lng: challenge.lng },
                map: this.map,
                title: challenge.title,
                icon: this.getMarkerIcon(challenge.type)
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `<div class="map-info-window">
                    <h3>${challenge.title}</h3>
                    <p>Click to start this challenge!</p>
                </div>`
            });

            marker.addListener('click', () => {
                infoWindow.open(this.map, marker);
            });
        });
    }

    getMarkerIcon(type) {
        const icons = {
            location: '📍',
            weather: '🌤️',
            pokemon: '⚡',
            news: '📰'
        };
        return icons[type] || '📍';
    }

    // Geolocation
    requestLocationPermission() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.currentLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    this.loadLocationBasedData();
                },
                (error) => {
                    console.log('Geolocation error:', error);
                    this.showError('Location access denied. Some features may be limited.');
                }
            );
        }
    }

    // API Data Loading
    async loadInitialData() {
        this.showLoading('Loading adventure data...');
        
        try {
            await Promise.all([
                this.loadWeatherData(),
                this.loadPokemonData(),
                this.loadNewsData(),
                this.loadRandomFact()
            ]);
        } catch (error) {
            console.error('Error loading initial data:', error);
            this.showError('Failed to load some data. Please check your connection.');
        } finally {
            this.hideLoading();
        }
    }

    async loadLocationBasedData() {
        if (!this.currentLocation) return;

        try {
            await this.loadWeatherData();
            this.updateMapCenter();
        } catch (error) {
            console.error('Error loading location data:', error);
        }
    }

    async loadWeatherData() {
        if (!this.currentLocation) return;

        try {
            const response = await fetch(`api/weather.php?action=get_weather&lat=${this.currentLocation.lat}&lon=${this.currentLocation.lng}`);
            const data = await response.json();
            
            if (data.success) {
                this.weatherData = data.data;
                this.updateWeatherWidget();
                this.updateThemeBasedOnWeather();
            }
        } catch (error) {
            console.error('Weather API error:', error);
        }
    }

    async loadPokemonData() {
        try {
            const response = await fetch('api/pokemon.php?action=random');
            const data = await response.json();
            
            if (data.success) {
                this.pokemonData = data.data;
                this.updatePokemonWidget();
            }
        } catch (error) {
            console.error('Pokemon API error:', error);
        }
    }

    async loadNewsData() {
        try {
            const response = await fetch('api/news.php?action=latest&pageSize=1');
            const data = await response.json();
            
            if (data.success) {
                this.newsData = data.data;
            }
        } catch (error) {
            console.error('News API error:', error);
        }
    }

    async loadRandomFact() {
        try {
            const response = await fetch('api/news.php?action=random_fact');
            const data = await response.json();
            
            if (data.success) {
                this.updateLoadingFact(data.data.title);
            }
        } catch (error) {
            console.error('Random fact error:', error);
        }
    }

    // UI Updates
    updateWeatherWidget() {
        const weatherWidget = document.querySelector('.weather-widget');
        if (weatherWidget && this.weatherData) {
            const icon = weatherWidget.querySelector('.weather-icon');
            const temp = weatherWidget.querySelector('.weather-temp');
            
            icon.className = `fas ${this.getWeatherIcon(this.weatherData.condition)} weather-icon`;
            temp.textContent = `${Math.round(this.weatherData.temperature)}°C`;
        }
    }

    updatePokemonWidget() {
        const pokemonWidget = document.querySelector('.pokemon-widget');
        if (pokemonWidget && this.pokemonData) {
            const sprite = pokemonWidget.querySelector('.pokemon-sprite');
            const name = pokemonWidget.querySelector('.pokemon-name');
            
            sprite.src = this.pokemonData.sprite;
            name.textContent = this.pokemonData.name;
        }
    }

    updateLoadingFact(fact) {
        const loadingFact = document.getElementById('loading-fact');
        if (loadingFact) {
            loadingFact.textContent = fact;
        }
    }

    updateMapCenter() {
        if (this.map && this.currentLocation) {
            this.map.setCenter(this.currentLocation);
        }
    }

    getWeatherIcon(condition) {
        const icons = {
            'Clear': 'fa-sun',
            'Clouds': 'fa-cloud',
            'Rain': 'fa-cloud-rain',
            'Snow': 'fa-snowflake',
            'Thunderstorm': 'fa-bolt',
            'Drizzle': 'fa-cloud-drizzle',
            'Mist': 'fa-smog',
            'Fog': 'fa-smog'
        };
        return icons[condition] || 'fa-cloud';
    }

    // Loading States
    setupLoadingStates() {
        // Show loading on page load
        this.showLoading('Initializing adventure...');
        
        // Hide loading after page is ready
        window.addEventListener('load', () => {
            setTimeout(() => {
                this.hideLoading();
            }, 2000);
        });
    }

    showLoading(message = 'Loading...') {
        this.isLoading = true;
        const overlay = document.getElementById('loading-overlay');
        const loadingText = overlay?.querySelector('h3');
        
        if (overlay) {
            overlay.classList.add('active');
            if (loadingText) {
                loadingText.textContent = message;
            }
        }
    }

    hideLoading() {
        this.isLoading = false;
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    }

    // Error Handling
    setupErrorHandling() {
        window.addEventListener('error', (event) => {
            this.showError('An unexpected error occurred. Please try again.');
        });

        window.addEventListener('unhandledrejection', (event) => {
            this.showError('Network error. Please check your connection.');
        });
    }

    showError(message) {
        const modal = document.getElementById('error-modal');
        const errorMessage = document.getElementById('error-message');
        
        if (modal && errorMessage) {
            errorMessage.textContent = message;
            modal.classList.add('active');
        }
    }

    closeErrorModal() {
        const modal = document.getElementById('error-modal');
        if (modal) {
            modal.classList.remove('active');
        }
    }

    retryAction() {
        this.closeErrorModal();
        this.loadInitialData();
    }

    // Scroll Effects
    handleScroll() {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                if (this.currentTheme === 'dark') {
                    navbar.style.background = 'rgba(17, 24, 39, 0.98)';
                }
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                if (this.currentTheme === 'dark') {
                    navbar.style.background = 'rgba(17, 24, 39, 0.95)';
                }
            }
        }

        // Parallax effect for hero section
        const hero = document.querySelector('.hero');
        if (hero) {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        }
    }

    // Utility Functions
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Offline Support
    setupOfflineSupport() {
        window.addEventListener('online', () => {
            this.showNotification('Connection restored!', 'success');
            this.loadInitialData();
        });

        window.addEventListener('offline', () => {
            this.showNotification('You are offline. Some features may be limited.', 'warning');
        });
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

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.adventureHunt = new AdventureHuntApp();
});

// Global functions for HTML onclick handlers
function closeErrorModal() {
    if (window.adventureHunt) {
        window.adventureHunt.closeErrorModal();
    }
}

function retryAction() {
    if (window.adventureHunt) {
        window.adventureHunt.retryAction();
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdventureHuntApp;
} 