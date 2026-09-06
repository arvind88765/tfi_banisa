-- Import this once via hPanel > Databases > phpMyAdmin, after creating your database.

CREATE TABLE IF NOT EXISTS rounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mode ENUM('audio','image','music') NOT NULL,
    tmdb_id INT NOT NULL,
    movie_title VARCHAR(255) NOT NULL,
    movie_year VARCHAR(10) DEFAULT NULL,
    poster_path VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS round_assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    round_id INT NOT NULL,
    sort_order INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    kind ENUM('clip','image') NOT NULL,
    FOREIGN KEY (round_id) REFERENCES rounds(id) ON DELETE CASCADE
);

-- Tracks which round has already been used for which calendar day,
-- so the daily puzzle is stable (same for every visitor) and never repeats
-- until every round has been used once.
CREATE TABLE IF NOT EXISTS daily_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    play_date DATE NOT NULL UNIQUE,
    round_id INT NOT NULL,
    FOREIGN KEY (round_id) REFERENCES rounds(id) ON DELETE CASCADE
);
