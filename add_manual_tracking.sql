-- Add is_manual field to inventory_items table to track manual entries
ALTER TABLE inventory_items ADD COLUMN is_manual TINYINT(1);
