-- SQL script to update the inventories table status enum
-- Run this in your MySQL database for the 'bnm' database

USE bnm;

-- Update the status enum to use pending instead of draft
ALTER TABLE inventories 
MODIFY COLUMN status ENUM('pending', 'confirmed', 'canceled', 'done') DEFAULT 'pending';

-- Update any existing 'draft' records to 'pending'
UPDATE inventories SET status = 'pending' WHERE status = 'draft';

-- Verify the change
DESCRIBE inventories;
