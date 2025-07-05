# Adventure Hunt - Location-Aware Scavenger Hunt

A comprehensive, API-driven scavenger hunt application that combines real-world exploration with digital challenges using weather data, Pokémon encounters, news updates, and location-based missions.

## 🌟 Features

### Core Functionality
- **Location-Based Challenges**: GPS-enabled missions requiring real-world exploration
- **Weather Integration**: Dynamic challenges based on current weather conditions
- **Pokémon Encounters**: Real-world Pokémon discovery using PokeAPI
- **News Challenges**: Location-based news exploration and current events
- **Multiplayer Mode**: Real-time parallel interaction with friends
- **Offline Support**: Queue system for API failures and offline functionality

### Advanced Features
- **Voice Navigation**: Hands-free exploration with voice commands
- **Text-to-Speech**: Audio feedback for all content and challenges
- **Accessibility**: Colorblind support, keyboard navigation, screen reader compatibility
- **Dark Mode**: Complete theme switching with dynamic theming
- **Multilingual Support**: Internationalization ready
- **Story Mode**: Narrative-driven challenge progression

### Challenge Categories
- **Easy (2 pts)**: Creative error handling, dark mode, loading states, basic weather/Pokémon missions
- **Medium (4 pts)**: Dynamic theming, multilingual support, story mode, news exploration
- **Hard (6 pts)**: Voice navigation, text-to-speech, parallel interaction, offline handling

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **APIs**: OpenWeatherMap, PokeAPI, NewsAPI, Giphy API, Google Maps
- **Additional**: Font Awesome, Google Fonts

## 📋 Prerequisites

- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser with JavaScript enabled
- API keys for external services

## 🚀 Installation

1. **Clone/Download** the project to your web server directory
2. **Configure Database**:
   - Create a MySQL database named `scavenger_hunt`
   - Import the schema from `database/schema.sql`
   - Update database credentials in `config/database.php`

3. **Configure API Keys**:
   Edit `config/database.php` and replace placeholder API keys:
   ```php
   define('OPENWEATHER_API_KEY', 'your_openweather_api_key');
   define('GOOGLE_MAPS_API_KEY', 'your_google_maps_api_key');
   define('NEWS_API_KEY', 'your_news_api_key');
   define('GIPHY_API_KEY', 'your_giphy_api_key');
   ```

4. **Update Google Maps API Key**:
   In `index.php` and `dashboard.php`, replace `YOUR_GOOGLE_MAPS_API_KEY` with your actual key

## 🔑 Required API Keys

### OpenWeatherMap API
- **Purpose**: Weather-based challenges and conditions
- **Get Key**: [OpenWeatherMap API](https://openweathermap.org/api)
- **Usage**: Free tier available (1000 calls/day)

### Google Maps API
- **Purpose**: Location services, maps, and geolocation
- **Get Key**: [Google Cloud Console](https://console.cloud.google.com/)
- **Usage**: Enable Maps JavaScript API and Places API

### NewsAPI
- **Purpose**: News-based challenges and current events
- **Get Key**: [NewsAPI](https://newsapi.org/)
- **Usage**: Free tier available (1000 requests/day)

### Giphy API
- **Purpose**: GIF animations and visual feedback
- **Get Key**: [Giphy Developers](https://developers.giphy.com/)
- **Usage**: Free tier available (1000 requests/day)

### PokeAPI
- **Purpose**: Pokémon data and encounters
- **Get Key**: No key required (free public API)
- **Usage**: Unlimited requests

## 📁 Project Structure

```
call2code 1/
├── index.php              # Main landing page
├── home.php               # Redirect to index
├── login.php              # User authentication
├── register.php           # User registration
├── dashboard.php          # User dashboard
├── logout.php             # Session logout
├── config/
│   └── database.php       # Database and API configuration
├── database/
│   └── schema.sql         # MySQL database schema
├── api/
│   ├── weather.php        # OpenWeatherMap integration
│   ├── pokemon.php        # PokeAPI integration
│   ├── news.php           # NewsAPI integration
│   └── giphy.php          # Giphy API integration
└── assets/
    ├── css/
    │   ├── style.css      # Main stylesheet
    │   ├── auth.css       # Authentication styles
    │   └── dashboard.css  # Dashboard styles
    └── js/
        ├── main.js        # Main application logic
        ├── auth.js        # Authentication handling
        ├── dashboard.js   # Dashboard functionality
        ├── accessibility.js # Accessibility features
        └── voice.js       # Voice navigation
```

## 🎮 Usage

1. **Registration**: Create a new account with username and email
2. **Login**: Access your personalized dashboard
3. **Challenges**: Browse available challenges by difficulty
4. **Exploration**: Complete location-based missions using GPS
5. **Multiplayer**: Create or join game sessions with friends
6. **Progress**: Track your score, level, and completed challenges

## 🔧 Configuration Options

### Database Settings
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'scavenger_hunt');
```

### Theme Settings
Users can toggle between light and dark modes using the theme button in the navigation.

### Accessibility Settings
- Voice navigation can be enabled/disabled
- Colorblind support is automatically detected
- Keyboard navigation is fully supported

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Verify MySQL is running
   - Check database credentials in `config/database.php`
   - Ensure database `scavenger_hunt` exists

2. **API Errors**:
   - Verify all API keys are correctly set
   - Check API rate limits
   - Ensure internet connectivity

3. **Map Not Loading**:
   - Verify Google Maps API key is set
   - Enable Maps JavaScript API in Google Cloud Console

4. **Voice Features Not Working**:
   - Ensure browser supports Web Speech API
   - Check microphone permissions
   - Use HTTPS for production deployment

## 📱 Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## 🔒 Security Features

- Password hashing using PHP's password_hash()
- SQL injection prevention with prepared statements
- Session-based authentication
- Input validation and sanitization
- CSRF protection ready

## 🚀 Deployment

For production deployment:

1. **Use HTTPS**: Required for voice features and geolocation
2. **Set Production API Keys**: Replace development keys
3. **Configure Database**: Use production MySQL server
4. **Enable Error Logging**: Set appropriate PHP error reporting
5. **Optimize Performance**: Enable PHP OPcache and MySQL query cache

## 📄 License

This project is created for educational purposes and the Call2Code challenge.

## 🤝 Contributing

This is a demonstration project for the Call2Code challenge. All features are implemented according to the specified requirements.

## 📞 Support

For technical support or questions about the implementation, refer to the code comments and documentation within each file.

---

**Built with ❤️ for the Call2Code Challenge** 