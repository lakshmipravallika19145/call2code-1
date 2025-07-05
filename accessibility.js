// Accessibility Features for Adventure Hunt App

class AccessibilityManager {
    constructor() {
        this.isVoiceEnabled = false;
        this.isColorblindMode = false;
        this.currentLanguage = 'en';
        this.speechSynthesis = window.speechSynthesis;
        this.recognition = null;
        this.isListening = false;
        
        this.init();
    }

    init() {
        this.setupSpeechRecognition();
        this.setupEventListeners();
        this.loadUserPreferences();
        this.setupKeyboardNavigation();
        this.setupScreenReaderSupport();
    }

    // Voice Navigation
    setupSpeechRecognition() {
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();
            
            this.recognition.continuous = false;
            this.recognition.interimResults = false;
            this.recognition.lang = 'en-US';
            
            this.recognition.onstart = () => {
                this.isListening = true;
                this.updateVoiceUI();
                this.speak('Voice recognition activated. Please speak your command.');
            };
            
            this.recognition.onresult = (event) => {
                const command = event.results[0][0].transcript.toLowerCase();
                this.processVoiceCommand(command);
            };
            
            this.recognition.onend = () => {
                this.isListening = false;
                this.updateVoiceUI();
            };
            
            this.recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                this.speak('Voice recognition error. Please try again.');
            };
        }
    }

    toggleVoiceNavigation() {
        if (!this.recognition) {
            this.showNotification('Voice recognition is not supported in your browser.', 'error');
            return;
        }

        this.isVoiceEnabled = !this.isVoiceEnabled;
        localStorage.setItem('voiceEnabled', this.isVoiceEnabled);
        
        if (this.isVoiceEnabled) {
            this.startListening();
            this.showNotification('Voice navigation enabled. Say "help" for available commands.', 'success');
        } else {
            this.stopListening();
            this.showNotification('Voice navigation disabled.', 'info');
        }
        
        this.updateVoiceUI();
    }

    startListening() {
        if (this.recognition && this.isVoiceEnabled) {
            this.recognition.start();
        }
    }

    stopListening() {
        if (this.recognition) {
            this.recognition.stop();
        }
    }

    processVoiceCommand(command) {
        console.log('Voice command:', command);
        
        const commands = {
            'help': () => this.speakVoiceCommands(),
            'home': () => this.navigateToSection('home'),
            'challenges': () => this.navigateToSection('challenges'),
            'about': () => this.navigateToSection('about'),
            'dark mode': () => this.toggleTheme(),
            'light mode': () => this.toggleTheme(),
            'weather': () => this.speakWeatherInfo(),
            'pokemon': () => this.speakPokemonInfo(),
            'news': () => this.speakNewsInfo(),
            'scroll up': () => this.scrollPage('up'),
            'scroll down': () => this.scrollPage('down'),
            'stop': () => this.stopListening(),
            'start': () => this.startListening()
        };

        for (const [key, action] of Object.entries(commands)) {
            if (command.includes(key)) {
                action();
                return;
            }
        }

        this.speak(`Command not recognized. Say "help" for available commands.`);
    }

    speakVoiceCommands() {
        const commands = [
            'Available commands:',
            'Say "home" to go to home section',
            'Say "challenges" to view challenges',
            'Say "about" to go to about section',
            'Say "dark mode" or "light mode" to toggle theme',
            'Say "weather" for current weather',
            'Say "pokemon" for pokemon information',
            'Say "news" for latest news',
            'Say "scroll up" or "scroll down" to navigate',
            'Say "stop" to stop listening',
            'Say "start" to start listening again'
        ];
        
        commands.forEach((command, index) => {
            setTimeout(() => this.speak(command), index * 2000);
        });
    }

    // Text-to-Speech
    speak(text, options = {}) {
        if (!this.speechSynthesis) {
            console.warn('Speech synthesis not supported');
            return;
        }

        // Cancel any ongoing speech
        this.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = options.rate || 1;
        utterance.pitch = options.pitch || 1;
        utterance.volume = options.volume || 1;
        utterance.lang = this.currentLanguage;

        this.speechSynthesis.speak(utterance);
    }

    speakWeatherInfo() {
        if (window.adventureHunt && window.adventureHunt.weatherData) {
            const weather = window.adventureHunt.weatherData;
            const text = `Current weather is ${weather.condition.toLowerCase()}, temperature ${Math.round(weather.temperature)} degrees Celsius, humidity ${weather.humidity} percent.`;
            this.speak(text);
        } else {
            this.speak('Weather information is not available.');
        }
    }

    speakPokemonInfo() {
        if (window.adventureHunt && window.adventureHunt.pokemonData) {
            const pokemon = window.adventureHunt.pokemonData;
            const text = `Current pokemon is ${pokemon.name}, a ${pokemon.types.join(' and ')} type pokemon.`;
            this.speak(text);
        } else {
            this.speak('Pokemon information is not available.');
        }
    }

    speakNewsInfo() {
        if (window.adventureHunt && window.adventureHunt.newsData) {
            const news = window.adventureHunt.newsData.articles[0];
            const text = `Latest news: ${news.title}.`;
            this.speak(text);
        } else {
            this.speak('News information is not available.');
        }
    }

    // Colorblind Support
    toggleColorblindMode() {
        this.isColorblindMode = !this.isColorblindMode;
        localStorage.setItem('colorblindMode', this.isColorblindMode);
        
        if (this.isColorblindMode) {
            document.documentElement.setAttribute('data-theme', 'high-contrast');
            this.showNotification('High contrast mode enabled for better visibility.', 'success');
        } else {
            const currentTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', currentTheme);
            this.showNotification('High contrast mode disabled.', 'info');
        }
    }

    // Multilingual Support
    changeLanguage(languageCode) {
        this.currentLanguage = languageCode;
        localStorage.setItem('language', languageCode);
        
        // Update speech recognition language
        if (this.recognition) {
            this.recognition.lang = this.getLanguageCode(languageCode);
        }
        
        this.translatePage(languageCode);
        this.showNotification(`Language changed to ${this.getLanguageName(languageCode)}.`, 'success');
    }

    getLanguageCode(code) {
        const codes = {
            'en': 'en-US',
            'es': 'es-ES',
            'fr': 'fr-FR',
            'de': 'de-DE',
            'ja': 'ja-JP'
        };
        return codes[code] || 'en-US';
    }

    getLanguageName(code) {
        const names = {
            'en': 'English',
            'es': 'Español',
            'fr': 'Français',
            'de': 'Deutsch',
            'ja': '日本語'
        };
        return names[code] || 'English';
    }

    async translatePage(languageCode) {
        if (languageCode === 'en') {
            // Reset to original English
            location.reload();
            return;
        }

        // Simple translation mapping (in a real app, you'd use a translation API)
        const translations = {
            'es': {
                'Adventure Hunt': 'Búsqueda de Aventuras',
                'Location-Aware Scavenger Hunt': 'Búsqueda del Tesoro con Conciencia de Ubicación',
                'Get Started': 'Comenzar',
                'Login': 'Iniciar Sesión',
                'Challenges': 'Desafíos',
                'About': 'Acerca de'
            },
            'fr': {
                'Adventure Hunt': 'Chasse au Trésor',
                'Location-Aware Scavenger Hunt': 'Chasse au Trésor Géolocalisée',
                'Get Started': 'Commencer',
                'Login': 'Se Connecter',
                'Challenges': 'Défis',
                'About': 'À Propos'
            }
        };

        const translation = translations[languageCode];
        if (translation) {
            this.translateElements(translation);
        }
    }

    translateElements(translations) {
        // Translate text content
        document.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, a, button').forEach(element => {
            const originalText = element.textContent.trim();
            if (translations[originalText]) {
                element.textContent = translations[originalText];
            }
        });
    }

    // Navigation
    navigateToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            this.speak(`Navigated to ${sectionId} section.`);
        } else {
            this.speak(`Section ${sectionId} not found.`);
        }
    }

    toggleTheme() {
        if (window.adventureHunt) {
            window.adventureHunt.toggleTheme();
            const currentTheme = localStorage.getItem('theme') || 'light';
            this.speak(`Switched to ${currentTheme} mode.`);
        }
    }

    scrollPage(direction) {
        const scrollAmount = 300;
        const currentScroll = window.pageYOffset;
        
        if (direction === 'up') {
            window.scrollTo({ top: currentScroll - scrollAmount, behavior: 'smooth' });
            this.speak('Scrolled up.');
        } else {
            window.scrollTo({ top: currentScroll + scrollAmount, behavior: 'smooth' });
            this.speak('Scrolled down.');
        }
    }

    // Keyboard Navigation
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (event) => {
            // Alt + V to toggle voice navigation
            if (event.altKey && event.key === 'v') {
                event.preventDefault();
                this.toggleVoiceNavigation();
            }
            
            // Alt + C to toggle colorblind mode
            if (event.altKey && event.key === 'c') {
                event.preventDefault();
                this.toggleColorblindMode();
            }
            
            // Alt + H for help
            if (event.altKey && event.key === 'h') {
                event.preventDefault();
                this.speakVoiceCommands();
            }
            
            // Escape to stop voice recognition
            if (event.key === 'Escape' && this.isListening) {
                this.stopListening();
            }
        });
    }

    // Screen Reader Support
    setupScreenReaderSupport() {
        // Add ARIA labels to interactive elements
        document.querySelectorAll('button, a, input, select').forEach(element => {
            if (!element.getAttribute('aria-label')) {
                const text = element.textContent.trim() || element.placeholder || element.title;
                if (text) {
                    element.setAttribute('aria-label', text);
                }
            }
        });

        // Add skip links for keyboard navigation
        this.addSkipLinks();
    }

    addSkipLinks() {
        const skipLinks = document.createElement('div');
        skipLinks.className = 'skip-links';
        skipLinks.innerHTML = `
            <a href="#main-content" class="skip-link">Skip to main content</a>
            <a href="#navigation" class="skip-link">Skip to navigation</a>
        `;
        
        document.body.insertBefore(skipLinks, document.body.firstChild);
    }

    // UI Updates
    updateVoiceUI() {
        const voiceBtn = document.querySelector('[onclick="toggleVoiceNavigation()"]');
        if (voiceBtn) {
            const icon = voiceBtn.querySelector('i');
            const text = voiceBtn.querySelector('span') || voiceBtn;
            
            if (this.isListening) {
                icon.className = 'fas fa-microphone-slash';
                text.textContent = 'Disable Voice';
                voiceBtn.classList.add('listening');
            } else {
                icon.className = 'fas fa-microphone';
                text.textContent = 'Enable Voice';
                voiceBtn.classList.remove('listening');
            }
        }
    }

    // Event Listeners
    setupEventListeners() {
        // Auto-start voice recognition when voice is enabled
        document.addEventListener('click', () => {
            if (this.isVoiceEnabled && !this.isListening) {
                setTimeout(() => this.startListening(), 1000);
            }
        });

        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && this.isListening) {
                this.stopListening();
            } else if (!document.hidden && this.isVoiceEnabled && !this.isListening) {
                setTimeout(() => this.startListening(), 1000);
            }
        });
    }

    // User Preferences
    loadUserPreferences() {
        this.isVoiceEnabled = localStorage.getItem('voiceEnabled') === 'true';
        this.isColorblindMode = localStorage.getItem('colorblindMode') === 'true';
        this.currentLanguage = localStorage.getItem('language') || 'en';
        
        if (this.isColorblindMode) {
            document.documentElement.setAttribute('data-theme', 'high-contrast');
        }
        
        this.updateVoiceUI();
    }

    // Notifications
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.className = 'notification-close';
        closeBtn.onclick = () => notification.remove();
        notification.appendChild(closeBtn);
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
        
        // Speak notification for voice users
        if (this.isVoiceEnabled) {
            this.speak(message);
        }
    }
}

// Initialize accessibility features
document.addEventListener('DOMContentLoaded', () => {
    window.accessibilityManager = new AccessibilityManager();
});

// Global functions for HTML onclick handlers
function toggleVoiceNavigation() {
    if (window.accessibilityManager) {
        window.accessibilityManager.toggleVoiceNavigation();
    }
}

function toggleColorblindMode() {
    if (window.accessibilityManager) {
        window.accessibilityManager.toggleColorblindMode();
    }
}

function changeLanguage(languageCode) {
    if (window.accessibilityManager) {
        window.accessibilityManager.changeLanguage(languageCode);
    }
}

// Add CSS for accessibility features
const accessibilityCSS = `
    .skip-links {
        position: absolute;
        top: -40px;
        left: 6px;
        z-index: 10000;
    }

    .skip-link {
        position: absolute;
        top: -40px;
        left: 6px;
        background: #000;
        color: #fff;
        padding: 8px;
        text-decoration: none;
        border-radius: 4px;
        transition: top 0.3s;
    }

    .skip-link:focus {
        top: 6px;
    }

    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem;
        border-radius: 0.5rem;
        color: white;
        z-index: 10000;
        max-width: 300px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        animation: slideIn 0.3s ease;
    }

    .notification-success { background: #10b981; }
    .notification-error { background: #ef4444; }
    .notification-warning { background: #f59e0b; }
    .notification-info { background: #3b82f6; }

    .notification-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        margin-left: 1rem;
    }

    .btn.listening {
        background: #ef4444 !important;
        animation: pulse 1s infinite;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    [data-theme="high-contrast"] {
        --primary-color: #000000 !important;
        --primary-dark: #000000 !important;
        --secondary-color: #ffffff !important;
        --accent-color: #ffff00 !important;
        --text-primary: #000000 !important;
        --text-secondary: #333333 !important;
        --bg-primary: #ffffff !important;
        --bg-secondary: #f0f0f0 !important;
        --bg-tertiary: #e0e0e0 !important;
        --border-color: #000000 !important;
    }

    [data-theme="high-contrast"] .btn {
        border: 2px solid #000000 !important;
    }

    [data-theme="high-contrast"] .feature-card,
    [data-theme="high-contrast"] .challenge-type,
    [data-theme="high-contrast"] .accessibility-card {
        border: 2px solid #000000 !important;
    }
`;

// Inject accessibility CSS
const style = document.createElement('style');
style.textContent = accessibilityCSS;
document.head.appendChild(style); 