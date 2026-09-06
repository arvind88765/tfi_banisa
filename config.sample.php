<?php
// ============================================
// COPY THIS FILE TO config.php AND FILL IN REAL VALUES
// config.php is gitignored — it never gets pushed to GitHub.
// On Hostinger, create config.php manually via File Manager after deploying.
// ============================================

// Database credentials — hPanel > Databases > MySQL Databases
define('DB_HOST', 'localhost');
define('DB_NAME', 'u123456789_moviequiz');
define('DB_USER', 'u123456789_dbuser');
define('DB_PASS', 'your_db_password_here');

// TMDb API key — free at https://www.themoviedb.org/settings/api
define('TMDB_API_KEY', 'YOUR_TMDB_API_KEY_HERE');

// Admin panel login
define('ADMIN_USER', 'admin');
define('ADMIN_PASSWORD', 'change_this_password');

// Game branding
define('GAME_NAME', 'Pattukunte Pattucheera');

// Daily puzzle #1 was on this date — everything counts up from here
define('LAUNCH_DATE', '2024-01-01');

// Max attempts for music mode (single clip, multiple tries)
define('MUSIC_MODE_ATTEMPTS', 5);
