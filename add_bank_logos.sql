-- Add logo field to banks table
ALTER TABLE banks ADD COLUMN logo_filename VARCHAR(255) DEFAULT NULL AFTER bank_code;

-- Update existing banks with their logo filenames
UPDATE banks SET logo_filename = 'bna.png' WHERE bank_code = 'BNA';
UPDATE banks SET logo_filename = 'baraka.jpeg' WHERE bank_code = 'BARAKA';
UPDATE banks SET logo_filename = 'sga.png' WHERE bank_code = 'SGA';
 