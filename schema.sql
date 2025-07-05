-- Scavenger Hunt Database Schema

CREATE DATABASE IF NOT EXISTS scavenger_hunt;
USE scavenger_hunt;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    total_score INT DEFAULT 0,
    current_level INT DEFAULT 1,
    dark_mode BOOLEAN DEFAULT FALSE,
    language VARCHAR(10) DEFAULT 'en'
);

-- Challenges table
CREATE TABLE challenges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard') NOT NULL,
    points INT NOT NULL,
    challenge_type ENUM('location', 'weather', 'pokemon', 'news', 'custom') NOT NULL,
    api_required VARCHAR(50),
    coordinates_lat DECIMAL(10, 8) NULL,
    coordinates_lng DECIMAL(11, 8) NULL,
    radius_meters INT DEFAULT 100,
    weather_condition VARCHAR(50) NULL,
    pokemon_id INT NULL,
    news_keyword VARCHAR(100) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User progress table
CREATE TABLE user_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    challenge_id INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score_earned INT NOT NULL,
    location_lat DECIMAL(10, 8) NULL,
    location_lng DECIMAL(11, 8) NULL,
    weather_data JSON NULL,
    pokemon_data JSON NULL,
    news_data JSON NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE
);

-- Game sessions table
CREATE TABLE game_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_code VARCHAR(10) UNIQUE NOT NULL,
    host_user_id INT NOT NULL,
    guest_user_id INT NULL,
    status ENUM('waiting', 'active', 'completed') DEFAULT 'waiting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    host_score INT DEFAULT 0,
    guest_score INT DEFAULT 0,
    FOREIGN KEY (host_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (guest_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Offline queue table for API failures
CREATE TABLE offline_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    status ENUM('pending', 'processed', 'failed') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample challenges
INSERT INTO challenges (title, description, difficulty, points, challenge_type, api_required, weather_condition) VALUES
('Weather Warrior', 'Find a location where it\'s currently sunny!', 'easy', 2, 'weather', 'openweather', 'Clear'),
('Rainy Day Adventure', 'Complete a challenge during rainfall', 'medium', 4, 'weather', 'openweather', 'Rain'),
('Pokemon Hunter', 'Find a Pikachu in the wild!', 'easy', 2, 'pokemon', 'pokeapi', NULL),
('News Explorer', 'Find a location and read the latest tech news', 'medium', 4, 'news', 'newsapi', NULL),
('Extreme Weather', 'Complete a challenge during extreme weather conditions', 'hard', 6, 'weather', 'openweather', 'Thunderstorm');

-- Insert sample location-based challenges
INSERT INTO challenges (title, description, difficulty, points, challenge_type, coordinates_lat, coordinates_lng, radius_meters) VALUES
('Park Explorer', 'Visit the central park and take a photo', 'easy', 2, 'location', 40.7589, -73.9851, 200),
('Museum Visit', 'Explore the local museum', 'medium', 4, 'location', 40.7589, -73.9851, 150),
('Hidden Treasure', 'Find the secret location marked on the map', 'hard', 6, 'location', 40.7589, -73.9851, 50); 