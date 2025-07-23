-- =============================================
-- Inventory Management System Database Schema
-- =============================================

-- Create inventories table
CREATE TABLE IF NOT EXISTS inventories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    notes TEXT,
    status ENUM('pending','confirmed','canceled','done') DEFAULT 'pending',
    created_by VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
);

-- Create inventory_items table
CREATE TABLE IF NOT EXISTS inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    date DATE,
    lot VARCHAR(100),
    ppa DECIMAL(10,2) DEFAULT 0.00,
    qty_dispo INT DEFAULT 0,
    type ENUM('entry','sortie') DEFAULT 'entry',
    is_manual_entry TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_inventory_id (inventory_id),
    INDEX idx_product_name (product_name),
    INDEX idx_type (type),
    INDEX idx_manual_entry (is_manual_entry),
    FOREIGN KEY (inventory_id) REFERENCES inventories(id) ON DELETE CASCADE
);

-- Create inventory summary view
CREATE OR REPLACE VIEW inventory_summary AS
SELECT 
    i.id,
    i.title,
    i.notes,
    i.status,
    i.created_by,
    i.created_at,
    i.updated_at,
    i.completed_at,
    COUNT(ii.id) as total_items,
    SUM(CASE WHEN ii.type = 'entry' THEN ii.quantity ELSE 0 END) as total_entries,
    SUM(CASE WHEN ii.type = 'sortie' THEN ii.quantity ELSE 0 END) as total_sorties,
    SUM(CASE WHEN ii.is_manual_entry = 1 THEN 1 ELSE 0 END) as manual_entries_count
FROM inventories i
LEFT JOIN inventory_items ii ON i.id = ii.inventory_id
GROUP BY i.id;

-- Performance indexes
CREATE INDEX IF NOT EXISTS idx_inventory_status_created ON inventories (status, created_at);
CREATE INDEX IF NOT EXISTS idx_items_inventory_type ON inventory_items (inventory_id, type);



INSERT INTO users (username, password, confirmPassword, Role)
VALUES ('boulfaida', 'bnmreclamation', 'bnmreclamation', 'gestion stock');
