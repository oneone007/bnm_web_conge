-- Migration script to transfer data from old bank table to new normalized structure
-- Run this AFTER creating the new tables

-- First, let's create a backup of the old table
CREATE TABLE IF NOT EXISTS bank_backup AS SELECT * FROM bank;

-- Insert data from the old bank table to the new structure
INSERT INTO bank_transactions (bank_id, transaction_date, creation_time, remise, sold, check_amount)
SELECT 
    1 as bank_id, -- BNA bank ID
    DATE(creation_time) as transaction_date,
    creation_time,
    COALESCE(bna_remise, 0.00) as remise,
    COALESCE(bna_sold, 0.00) as sold,
    COALESCE(bna_check, 0.00) as check_amount
FROM bank
WHERE (bna_remise IS NOT NULL AND bna_remise != 0) 
   OR (bna_sold IS NOT NULL AND bna_sold != 0) 
   OR (bna_check IS NOT NULL AND bna_check != 0)

UNION ALL

SELECT 
    2 as bank_id, -- Baraka bank ID
    DATE(creation_time) as transaction_date,
    creation_time,
    COALESCE(baraka_remise, 0.00) as remise,
    COALESCE(baraka_sold, 0.00) as sold,
    COALESCE(baraka_check, 0.00) as check_amount
FROM bank
WHERE (baraka_remise IS NOT NULL AND baraka_remise != 0) 
   OR (baraka_sold IS NOT NULL AND baraka_sold != 0) 
   OR (baraka_check IS NOT NULL AND baraka_check != 0);

-- After verifying the data migration is successful, you can drop the old table
-- DROP TABLE bank;
