-- New normalized database structure for banks

-- 1. Create the banks table to store bank information
CREATE TABLE IF NOT EXISTS banks (
    id_bank INT(11) PRIMARY KEY AUTO_INCREMENT,
    bank_name VARCHAR(100) NOT NULL UNIQUE,
    bank_code VARCHAR(10) UNIQUE,
    logo_filename VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
);

-- 2. Create the bank_transactions table to store financial data
CREATE TABLE IF NOT EXISTS bank_transactions (
    id_transaction INT(11) PRIMARY KEY AUTO_INCREMENT,
    bank_id INT(11) NOT NULL,
    transaction_date DATE NOT NULL,
    creation_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    remise DECIMAL(15,2) DEFAULT 0.00, 
    sold DECIMAL(15,2) DEFAULT 0.00,
    check_amount DECIMAL(15,2) DEFAULT 0.00,
    notes TEXT,
    created_by INT(11),
    FOREIGN KEY (bank_id) REFERENCES banks(id_bank) ON DELETE CASCADE,
    INDEX idx_bank_date (bank_id, transaction_date),
    INDEX idx_creation_time (creation_time)
);

-- 3. Insert default banks (BNA and Baraka)
INSERT INTO banks (bank_name, bank_code, is_active) VALUES 
('BNA', 'BNA', TRUE),
('Al Baraka Bank', 'BARAKA', TRUE)
ON DUPLICATE KEY UPDATE 
    bank_name = VALUES(bank_name),
    is_active = VALUES(is_active);

-- 4. Create a view for easy reporting (similar to your current total calculations)
CREATE OR REPLACE VIEW bank_daily_summary AS
SELECT 
    bt.transaction_date,
    bt.creation_time,
    SUM(bt.remise) AS total_remise,
    SUM(bt.sold) AS total_sold,
    SUM(bt.check_amount) AS total_checks,
    SUM(bt.remise + bt.sold) AS total_bank,
    COUNT(DISTINCT bt.bank_id) AS banks_count
FROM bank_transactions bt
JOIN banks b ON bt.bank_id = b.id_bank
WHERE b.is_active = TRUE
GROUP BY bt.transaction_date, bt.creation_time
ORDER BY bt.creation_time DESC;

-- 5. Create a view for bank-specific daily summary
CREATE OR REPLACE VIEW bank_specific_summary AS
SELECT 
    bt.transaction_date,
    bt.creation_time,
    b.bank_name,
    b.bank_code,
    bt.remise,
    bt.sold,
    bt.check_amount,
    (bt.remise + bt.sold) AS bank_total
FROM bank_transactions bt
JOIN banks b ON bt.bank_id = b.id_bank
WHERE b.is_active = TRUE
ORDER BY bt.creation_time DESC, b.bank_name;
