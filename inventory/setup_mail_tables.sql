-- Run this script to create the mail management tables manually if needed

USE bnm;

-- Table for email configurations/templates
CREATE TABLE IF NOT EXISTS email_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_name VARCHAR(100) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    from_email VARCHAR(255) NOT NULL DEFAULT 'inventory.system.bnm@bnmparapharm.com',
    from_password VARCHAR(255) NOT NULL DEFAULT 'bnmparapharminv',
    smtp_server VARCHAR(255) NOT NULL DEFAULT 'mail.bnmparapharm.com',
    smtp_port INT NOT NULL DEFAULT 465,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for email recipients/contacts
CREATE TABLE IF NOT EXISTS email_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    department VARCHAR(100),
    position VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for email logs (sent emails) - only keeps today's data
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_name VARCHAR(100),
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    from_email VARCHAR(255) NOT NULL,
    to_email VARCHAR(255) NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    related_inventory_id INT,
    INDEX idx_sent_at (sent_at),
    INDEX idx_date (sent_at)
);

-- Insert default email configurations
INSERT INTO email_configs (config_name, subject, body) VALUES 
('inventory_created', 'INVENTORY CREATED', 
'Dear Team,

A new inventory has been created and requires your attention.

Inventory Details:
{inventory_details}

Please proceed with the inventory process.

Best regards,
BNM Inventory System'),

('inventory_saisie_notification', 'Inventory system notification: Please do the inventory and mark it as done',
'Dear Team,

The inventory is being created. Please proceed to do the inventory as soon as possible, and once completed, mark it as done in the system.

Good job!

Best regards,
BNM System'),

('inventory_info_notification', 'INFO: Inventory system notification',
'Dear Team,

DO THE INV.

Please proceed to do the inventory as soon as possible,

Best regards,
BNM System')
ON DUPLICATE KEY UPDATE 
subject = VALUES(subject), 
body = VALUES(body);

-- Insert default email contacts
INSERT INTO email_contacts (name, email, department, position) VALUES 
('Abderrahmane Benmalek', 'benmalek.abderrahmane@bnmparapharm.com', 'Management', 'Manager'),
('Nazim Mahroug', 'mahroug.nazim@bnmparapharm.com', 'Management', 'Manager'),
('Hamza Guend', 'guend.hamza@bnmparapharm.com', 'Operations', 'Staff'),
('Seifeddine Nemdili', 'seifeddine.nemdili@bnmparapharm.com', 'Operations', 'Staff'),
('Abdenour Belhanachi', 'belhanachi.abdenour@bnmparapharm.com', 'Operations', 'Staff'),
('Yasser Maamri', 'maamri.yasser@bnmparapharm.com', 'Management', 'Manager')
ON DUPLICATE KEY UPDATE 
name = VALUES(name), 
department = VALUES(department), 
position = VALUES(position);

SELECT 'Mail management tables and default data setup completed!' AS Result;
