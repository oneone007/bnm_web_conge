-- =============================================
-- Inventory Management System Database Schema
-- =============================================
-- This file creates all necessary tables for the inventory management system
-- Run this script to set up a fresh inventory database

-- Create inventories table
-- This table stores the main inventory records
CREATE TABLE IF NOT EXISTS inventories (
    id int(11) NOT NULL AUTO_INCREMENT,
    title varchar(255) NOT NULL,
    notes text DEFAULT NULL,
    status enum('pending','confirmed','canceled','done') NOT NULL DEFAULT 'pending',
    created_by varchar(100) NOT NULL COMMENT 'Username of the user who created this inventory',
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at datetime DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_status (status),
    KEY idx_created_by (created_by),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create inventory_items table
-- This table stores individual items within each inventory
CREATE TABLE IF NOT EXISTS `inventory_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `inventory_id` int(11) NOT NULL,
    `product_name` varchar(255) NOT NULL,
    `quantity` int(11) NOT NULL,
    `date` date DEFAULT NULL,
    `lot` varchar(100) DEFAULT NULL,
    `ppa` decimal(10,2) DEFAULT 0.00 COMMENT 'Prix Produit Achat (Purchase Price)',
    `qty_dispo` int(11) DEFAULT 0 COMMENT 'Quantity Available',
    `type` enum('entry','sortie') NOT NULL DEFAULT 'entry',
    `is_manual_entry` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Flag to indicate if this is a manual entry',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_inventory_id` (`inventory_id`),
    KEY `idx_product_name` (`product_name`),
    KEY `idx_type` (`type`),
    KEY `idx_is_manual_entry` (`is_manual_entry`),
    CONSTRAINT `fk_inventory_items_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data (optional)
-- Uncomment the following lines if you want to insert sample data

-- INSERT INTO `inventories` (`title`, `notes`, `status`, `created_by`) VALUES
-- ('Sample Inventory 1', 'This is a sample inventory for testing', 'pending', 'admin'),
-- ('Sample Inventory 2', 'Another sample inventory', 'confirmed', 'admin');

-- INSERT INTO `inventory_items` (`inventory_id`, `product_name`, `quantity`, `date`, `lot`, `ppa`, `qty_dispo`, `type`, `is_manual_entry`) VALUES
-- (1, 'Sample Product 1', 10, '2025-01-15', 'LOT001', 25.50, 50, 'entry', 0),
-- (1, 'Sample Product 2', 5, '2025-01-15', 'LOT002', 15.75, 25, 'sortie', 0),
-- (2, 'Manual Entry Product', 3, '2025-01-16', 'MANUAL001', 35.00, 10, 'entry', 1);

-- =============================================
-- Views for easier data access
-- =============================================

-- View to get inventory summary with item counts
CREATE OR REPLACE VIEW `inventory_summary` AS
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
GROUP BY i.id, i.title, i.notes, i.status, i.created_by, i.created_at, i.updated_at, i.completed_at;

-- View to get detailed inventory items with inventory info
CREATE OR REPLACE VIEW `inventory_details` AS
SELECT 
    i.id as inventory_id,
    i.title as inventory_title,
    i.status as inventory_status,
    i.created_by,
    i.created_at as inventory_created_at,
    ii.id as item_id,
    ii.product_name,
    ii.quantity,
    ii.date,
    ii.lot,
    ii.ppa,
    ii.qty_dispo,
    ii.type,
    ii.is_manual_entry,
    ii.created_at as item_created_at
FROM inventories i
LEFT JOIN inventory_items ii ON i.id = ii.inventory_id
ORDER BY i.created_at DESC, ii.id ASC;

-- =============================================
-- Stored Procedures (optional)
-- =============================================

-- Procedure to update inventory status
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS UpdateInventoryStatus(
    IN inventory_id INT,
    IN new_status ENUM('pending','confirmed','canceled','done'),
    IN user_name VARCHAR(100)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    UPDATE inventories 
    SET status = new_status,
        updated_at = CURRENT_TIMESTAMP,
        completed_at = CASE WHEN new_status = 'done' THEN CURRENT_TIMESTAMP ELSE completed_at END
    WHERE id = inventory_id;
    
    COMMIT;
END //
DELIMITER ;

-- =============================================
-- Indexes for performance optimization
-- =============================================

-- Additional indexes for better query performance
CREATE INDEX IF NOT EXISTS `idx_inventory_status_created` ON `inventories` (`status`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_items_inventory_type` ON `inventory_items` (`inventory_id`, `type`);
CREATE INDEX IF NOT EXISTS `idx_items_product_date` ON `inventory_items` (`product_name`, `date`);

-- =============================================
-- Database Information
-- =============================================

-- Table information query (uncomment to see table details)
-- SELECT 
--     TABLE_NAME,
--     TABLE_ROWS,
--     DATA_LENGTH,
--     INDEX_LENGTH,
--     CREATE_TIME
-- FROM information_schema.TABLES 
-- WHERE TABLE_SCHEMA = DATABASE() 
-- AND TABLE_NAME IN ('inventories', 'inventory_items');

-- =============================================
-- Migration Notes
-- =============================================

/*
Migration Notes:
1. This schema uses utf8mb4 charset for full Unicode support
2. Foreign key constraints ensure data integrity
3. Indexes are optimized for common query patterns
4. The is_manual_entry field tracks manual entries vs automatic entries
5. Views provide convenient access to common data combinations
6. Stored procedures can be used for complex operations
7. Status enum includes all workflow states: pending, confirmed, canceled, done
8. created_by field stores username (VARCHAR) instead of user_id (INT)

Usage:
- Run this script on a fresh database to create all tables
- For existing databases, use migrate_inventory_table.php to update safely
- Tables are created with IF NOT EXISTS to prevent errors on re-runs
*/
