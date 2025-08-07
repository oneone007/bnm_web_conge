-- Table to store user session info
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    login_time DATETIME NOT NULL,
    logout_time DATETIME DEFAULT NULL
);
