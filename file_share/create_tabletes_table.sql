-- SQL for creating the 'tabletes' table
CREATE TABLE IF NOT EXISTS tabletes (
    id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    `key` VARCHAR(100) NOT NULL,
    number VARCHAR(50),
    persone VARCHAR(100),
    ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
