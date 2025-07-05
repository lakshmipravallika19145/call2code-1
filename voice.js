// Voice Navigation and Text-to-Speech Features

class VoiceNavigation {
    constructor() {
        this.speechSynthesis = window.speechSynthesis;
        this.recognition = null;
        this.isListening = false;
        this.voiceCommands = new Map();
        this.currentVoice = null;
        this.voiceQueue = [];
        this.isSpeaking = false;
        
        this.init();
    }

    init() {
        this.setupSpeechRecognition();
        this.setupVoiceCommands();
        this.setupVoiceSelection();
        this.setupEventListeners();
        this.loadVoicePreferences();
    }

    // Speech Recognition Setup
    setupSpeechRecognition() {
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();
            
            this.recognition.continuous = true;
            this.recognition.interimResults = true;
            this.recognition.lang = 'en-US';
            
            this.recognition.onstart = () => {
                this.isListening = true;
                this.updateListeningIndicator();
                this.speak('Voice recognition activated. Listening for commands.');
            };
            
            this.recognition.onresult = (event) => {
                let finalTranscript = '';
                let interimTranscript = '';
                
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        finalTranscript += transcript;
                    } else {
                        interimTranscript += transcript;
                    }
                }
                
                if (finalTranscript) {
                    this.processVoiceCommand(finalTranscript.toLowerCase());
                }
            };
            
            this.recognition.onend = () => {
                this.isListening = false;
                this.updateListeningIndicator();
                
                // Restart if voice navigation is enabled
                if (window.accessibilityManager && window.accessibilityManager.isVoiceEnabled) {
                    setTimeout(() => this.startListening(), 1000);
                }
            };
            
            this.recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                this.speak(`Voice recognition error: ${event.error}. Please try again.`);
            };
        }
    }

    // Voice Commands Setup
    setupVoiceCommands() {
        // Navigation commands
        this.voiceCommands.set('go home', () => this.navigateToSection('home'));
        this.voiceCommands.set('go to home', () => this.navigateToSection('home'));
        this.voiceCommands.set('show challenges', () => this.navigateToSection('challenges'));
        this.voiceCommands.set('go to challenges', () => this.navigateToSection('challenges'));
        this.voiceCommands.set('show about', () => this.navigateToSection('about'));
        this.voiceCommands.set('go to about', () => this.navigateToSection('about'));
        
        // Theme commands
        this.voiceCommands.set('switch to dark mode', () => this.switchTheme('dark'));
        this.voiceCommands.set('switch to light mode', () => this.switchTheme('light'));
        this.voiceCommands.set('toggle theme', () => this.toggleTheme());
        this.voiceCommands.set('dark mode', () => this.switchTheme('dark'));
        this.voiceCommands.set('light mode', () => this.switchTheme('light'));
        
        // Information commands
        this.voiceCommands.set('what is the weather', () => this.speakWeatherInfo());
        this.voiceCommands.set('tell me the weather', () => this.speakWeatherInfo());
        this.voiceCommands.set('weather information', () => this.speakWeatherInfo());
        this.voiceCommands.set('what pokemon is available', () => this.speakPokemonInfo());
        this.voiceCommands.set('tell me about pokemon', () => this.speakPokemonInfo());
        this.voiceCommands.set('pokemon information', () => this.speakPokemonInfo());
        this.voiceCommands.set('what is the latest news', () => this.speakNewsInfo());
        this.voiceCommands.set('tell me the news', () => this.speakNewsInfo());
        this.voiceCommands.set('news information', () => this.speakNewsInfo());
        
        // Control commands
        this.voiceCommands.set('stop listening', () => this.stopListening());
        this.voiceCommands.set('pause voice', () => this.stopListening());
        this.voiceCommands.set('start listening', () => this.startListening());
        this.voiceCommands.set('resume voice', () => this.startListening());
        this.voiceCommands.set('what can i say', () => this.speakAvailableCommands());
        this.voiceCommands.set('help', () => this.speakAvailableCommands());
        this.voiceCommands.set('voice commands', () => this.speakAvailableCommands());
        
        // Scrolling commands
        this.voiceCommands.set('scroll up', () => this.scrollPage('up'));
        this.voiceCommands.set('scroll down', () => this.scrollPage('down'));
        this.voiceCommands.set('go to top', () => this.scrollToTop());
        this.voiceCommands.set('go to bottom', () => this.scrollToBottom());
        
        // Accessibility commands
        this.voiceCommands.set('enable high contrast', () => this.toggleColorblindMode());
        this.voiceCommands.set('disable high contrast', () => this.toggleColorblindMode());
        this.voiceCommands.set('high contrast mode', () => this.toggleColorblindMode());
        
        // Language commands
        this.voiceCommands.set('switch to spanish', () => this.changeLanguage('es'));
        this.voiceCommands.set('switch to french', () => this.changeLanguage('fr'));
        this.voiceCommands.set('switch to german', () => this.changeLanguage('de'));
        this.voiceCommands.set('switch to japanese', () => this.changeLanguage('ja'));
        this.voiceCommands.set('switch to english', () => this.changeLanguage('en'));
    }

    // Process Voice Commands
    processVoiceCommand(command) {
        console.log('Processing voice command:', command);
        
        // Check for exact matches first
        if (this.voiceCommands.has(command)) {
            this.voiceCommands.get(command)();
            return;
        }
        
        // Check for partial matches
        for (const [key, action] of this.voiceCommands) {
            if (command.includes(key) || key.includes(command)) {
                action();
                return;
            }
        }
        
        // No match found
        this.speak(`Command not recognized: "${command}". Say "help" for available commands.`);
    }

    // Navigation Functions
    navigateToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            this.speak(`Navigated to ${sectionId} section.`);
        } else {
            this.speak(`Section ${sectionId} not found.`);
        }
    }

    switchTheme(theme) {
        if (window.adventureHunt) {
            const currentTheme = localStorage.getItem('theme') || 'light';
            if (currentTheme !== theme) {
                window.adventureHunt.toggleTheme();
            }
            this.speak(`Switched to ${theme} mode.`);
        }
    }

    toggleTheme() {
        if (window.adventureHunt) {
            window.adventureHunt.toggleTheme();
            const currentTheme = localStorage.getItem('theme') || 'light';
            this.speak(`Switched to ${currentTheme} mode.`);
        }
    }

    // Information Functions
    speakWeatherInfo() {
        if (window.adventureHunt && window.adventureHunt.weatherData) {
            const weather = window.adventureHunt.weatherData;
            const text = `Current weather is ${weather.condition.toLowerCase()}, temperature ${Math.round(weather.temperature)} degrees Celsius, humidity ${weather.humidity} percent, wind speed ${weather.wind_speed} meters per second.`;
            this.speak(text);
        } else {
            this.speak('Weather information is not currently available. Please try again later.');
        }
    }

    speakPokemonInfo() {
        if (window.adventureHunt && window.adventureHunt.pokemonData) {
            const pokemon = window.adventureHunt.pokemonData;
            const text = `Current pokemon is ${pokemon.name}, a ${pokemon.types.join(' and ')} type pokemon. It has ${pokemon.abilities.length} abilities and weighs ${pokemon.weight} kilograms.`;
            this.speak(text);
        } else {
            this.speak('Pokemon information is not currently available. Please try again later.');
        }
    }

    speakNewsInfo() {
        if (window.adventureHunt && window.adventureHunt.newsData) {
            const news = window.adventureHunt.newsData.articles[0];
            const text = `Latest news: ${news.title}. ${news.description}. Published by ${news.source}.`;
            this.speak(text);
        } else {
            this.speak('News information is not currently available. Please try again later.');
        }
    }

    // Control Functions
    startListening() {
        if (this.recognition && !this.isListening) {
            this.recognition.start();
        }
    }

    stopListening() {
        if (this.recognition && this.isListening) {
            this.recognition.stop();
        }
    }

    speakAvailableCommands() {
        const commands = [
            'Available voice commands:',
            'Navigation: "go home", "show challenges", "show about"',
            'Theme: "dark mode", "light mode", "toggle theme"',
            'Information: "what is the weather", "tell me about pokemon", "what is the latest news"',
            'Control: "stop listening", "start listening", "help"',
            'Scrolling: "scroll up", "scroll down", "go to top", "go to bottom"',
            'Accessibility: "enable high contrast", "switch to spanish"'
        ];
        
        this.speakQueue(commands);
    }

    // Scrolling Functions
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

    scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        this.speak('Scrolled to top of page.');
    }

    scrollToBottom() {
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        this.speak('Scrolled to bottom of page.');
    }

    // Accessibility Functions
    toggleColorblindMode() {
        if (window.accessibilityManager) {
            window.accessibilityManager.toggleColorblindMode();
        }
    }

    changeLanguage(languageCode) {
        if (window.accessibilityManager) {
            window.accessibilityManager.changeLanguage(languageCode);
        }
    }

    // Text-to-Speech Functions
    speak(text, options = {}) {
        if (!this.speechSynthesis) {
            console.warn('Speech synthesis not supported');
            return;
        }

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = options.rate || 1;
        utterance.pitch = options.pitch || 1;
        utterance.volume = options.volume || 1;
        utterance.lang = options.lang || 'en-US';
        
        if (this.currentVoice) {
            utterance.voice = this.currentVoice;
        }

        this.speechSynthesis.speak(utterance);
    }

    speakQueue(texts, delay = 2000) {
        texts.forEach((text, index) => {
            setTimeout(() => this.speak(text), index * delay);
        });
    }

    // Voice Selection
    setupVoiceSelection() {
        this.speechSynthesis.onvoiceschanged = () => {
            this.loadAvailableVoices();
        };
    }

    loadAvailableVoices() {
        const voices = this.speechSynthesis.getVoices();
        const preferredVoice = localStorage.getItem('preferredVoice');
        
        if (preferredVoice) {
            this.currentVoice = voices.find(voice => voice.name === preferredVoice);
        }
        
        if (!this.currentVoice && voices.length > 0) {
            // Prefer English voices
            this.currentVoice = voices.find(voice => voice.lang.startsWith('en')) || voices[0];
        }
    }

    setVoice(voiceName) {
        const voices = this.speechSynthesis.getVoices();
        this.currentVoice = voices.find(voice => voice.name === voiceName);
        
        if (this.currentVoice) {
            localStorage.setItem('preferredVoice', voiceName);
            this.speak(`Voice changed to ${voiceName}.`);
        }
    }

    // UI Updates
    updateListeningIndicator() {
        const voiceBtn = document.querySelector('[onclick="toggleVoiceNavigation()"]');
        if (voiceBtn) {
            const icon = voiceBtn.querySelector('i');
            const text = voiceBtn.querySelector('span') || voiceBtn;
            
            if (this.isListening) {
                icon.className = 'fas fa-microphone-slash';
                text.textContent = 'Disable Voice';
                voiceBtn.classList.add('listening');
                
                // Add visual indicator
                this.addListeningIndicator();
            } else {
                icon.className = 'fas fa-microphone';
                text.textContent = 'Enable Voice';
                voiceBtn.classList.remove('listening');
                
                // Remove visual indicator
                this.removeListeningIndicator();
            }
        }
    }

    addListeningIndicator() {
        if (!document.getElementById('voice-indicator')) {
            const indicator = document.createElement('div');
            indicator.id = 'voice-indicator';
            indicator.className = 'voice-indicator';
            indicator.innerHTML = `
                <div class="voice-indicator-dot"></div>
                <div class="voice-indicator-dot"></div>
                <div class="voice-indicator-dot"></div>
                <span>Listening...</span>
            `;
            document.body.appendChild(indicator);
        }
    }

    removeListeningIndicator() {
        const indicator = document.getElementById('voice-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    // Event Listeners
    setupEventListeners() {
        // Keyboard shortcuts
        document.addEventListener('keydown', (event) => {
            // Ctrl + Shift + V to toggle voice navigation
            if (event.ctrlKey && event.shiftKey && event.key === 'V') {
                event.preventDefault();
                this.toggleVoiceNavigation();
            }
            
            // Ctrl + Shift + H for help
            if (event.ctrlKey && event.shiftKey && event.key === 'H') {
                event.preventDefault();
                this.speakAvailableCommands();
            }
            
            // Escape to stop listening
            if (event.key === 'Escape' && this.isListening) {
                this.stopListening();
            }
        });

        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && this.isListening) {
                this.stopListening();
            }
        });
    }

    // User Preferences
    loadVoicePreferences() {
        const preferredVoice = localStorage.getItem('preferredVoice');
        if (preferredVoice) {
            this.setVoice(preferredVoice);
        }
    }

    // Public Methods
    toggleVoiceNavigation() {
        if (!this.recognition) {
            this.showNotification('Voice recognition is not supported in your browser.', 'error');
            return;
        }

        if (this.isListening) {
            this.stopListening();
            this.speak('Voice navigation disabled.');
        } else {
            this.startListening();
            this.speak('Voice navigation enabled. Say "help" for available commands.');
        }
    }

    showNotification(message, type = 'info') {
        if (window.accessibilityManager) {
            window.accessibilityManager.showNotification(message, type);
        }
    }
}

// Initialize voice navigation
document.addEventListener('DOMContentLoaded', () => {
    window.voiceNavigation = new VoiceNavigation();
});

// Global function for HTML onclick handler
function toggleVoiceNavigation() {
    if (window.voiceNavigation) {
        window.voiceNavigation.toggleVoiceNavigation();
    }
}

// Add CSS for voice navigation
const voiceCSS = `
    .voice-indicator {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 1rem 2rem;
        border-radius: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 10000;
        animation: slideDown 0.3s ease;
    }

    .voice-indicator-dot {
        width: 8px;
        height: 8px;
        background: #ef4444;
        border-radius: 50%;
        animation: pulse 1s infinite;
    }

    .voice-indicator-dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .voice-indicator-dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes slideDown {
        from { transform: translateX(-50%) translateY(-100%); opacity: 0; }
        to { transform: translateX(-50%) translateY(0); opacity: 1; }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
    }

    .btn.listening {
        background: #ef4444 !important;
        animation: pulse 1s infinite;
    }

    .voice-controls {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        z-index: 1000;
    }

    .voice-control-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: none;
        background: var(--primary-color);
        color: white;
        font-size: 1.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-medium);
    }

    .voice-control-btn:hover {
        transform: scale(1.1);
    }

    .voice-control-btn:active {
        transform: scale(0.95);
    }
`;

// Inject voice CSS
const voiceStyle = document.createElement('style');
voiceStyle.textContent = voiceCSS;
document.head.appendChild(voiceStyle); 