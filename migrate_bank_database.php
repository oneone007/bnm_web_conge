<?php
/**
 * Database Migration Script
 * This script helps migrate from the old bank table structure to the new normalized structure
 */

// Include database connection
require_once 'db/db_connect.php';

echo "=== Bank Database Migration Script ===\n\n";

// Check if old bank table exists
$check_old_table = "SHOW TABLES LIKE 'bank'";
$result = $conn->query($check_old_table);

if ($result->num_rows == 0) {
    echo "❌ Old 'bank' table not found. Migration not needed.\n";
    exit();
}

// Check if new tables exist
$check_banks_table = "SHOW TABLES LIKE 'banks'";
$check_transactions_table = "SHOW TABLES LIKE 'bank_transactions'";

$banks_exists = $conn->query($check_banks_table)->num_rows > 0;
$transactions_exists = $conn->query($check_transactions_table)->num_rows > 0;

if (!$banks_exists || !$transactions_exists) {
    echo "❌ New table structure not found. Please run the SQL schema file first.\n";
    echo "Execute: new_bank_schema.sql\n\n";
    exit();
}

echo "✅ Found both old and new table structures.\n";
echo "🔄 Starting migration process...\n\n";

try {
    $conn->begin_transaction();
    
    // Step 1: Create backup of old table
    echo "1. Creating backup of old table...\n";
    $backup_sql = "CREATE TABLE IF NOT EXISTS bank_backup AS SELECT * FROM bank";
    if ($conn->query($backup_sql)) {
        echo "   ✅ Backup created successfully\n";
    } else {
        throw new Exception("Failed to create backup: " . $conn->error);
    }
    
    // Step 2: Check if banks are already inserted
    $check_banks = "SELECT COUNT(*) as count FROM banks";
    $result = $conn->query($check_banks);
    $bank_count = $result->fetch_assoc()['count'];
    
    if ($bank_count == 0) {
        echo "2. Inserting default banks...\n";
        $insert_banks = "INSERT INTO banks (bank_name, bank_code, is_active) VALUES 
                        ('BNA', 'BNA', TRUE),
                        ('Al Baraka Bank', 'BARAKA', TRUE)";
        if ($conn->query($insert_banks)) {
            echo "   ✅ Default banks inserted\n";
        } else {
            throw new Exception("Failed to insert banks: " . $conn->error);
        }
    } else {
        echo "2. Banks already exist, skipping...\n";
    }
    
    // Step 3: Get bank IDs
    $get_banks = "SELECT id_bank, bank_code FROM banks WHERE bank_code IN ('BNA', 'BARAKA')";
    $result = $conn->query($get_banks);
    $bank_ids = [];
    while ($row = $result->fetch_assoc()) {
        $bank_ids[$row['bank_code']] = $row['id_bank'];
    }
    
    // Step 4: Check if data already migrated
    $check_transactions = "SELECT COUNT(*) as count FROM bank_transactions";
    $result = $conn->query($check_transactions);
    $transaction_count = $result->fetch_assoc()['count'];
    
    if ($transaction_count > 0) {
        echo "3. Data already migrated. Transaction count: {$transaction_count}\n";
        echo "   ⚠️ Skipping data migration to avoid duplicates\n";
    } else {
        echo "3. Migrating transaction data...\n";
        
        // Get all records from old bank table
        $get_old_data = "SELECT * FROM bank ORDER BY creation_time";
        $result = $conn->query($get_old_data);
        
        $insert_transaction = "INSERT INTO bank_transactions (bank_id, transaction_date, creation_time, remise, sold, check_amount) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_transaction);
        
        $migrated_count = 0;
        
        while ($row = $result->fetch_assoc()) {
            // Migrate BNA data
            if ($row['bna_remise'] || $row['bna_sold'] || $row['bna_check']) {
                $transaction_date = date('Y-m-d', strtotime($row['creation_time']));
                $stmt->bind_param("issddd", 
                    $bank_ids['BNA'], 
                    $transaction_date, 
                    $row['creation_time'],
                    $row['bna_remise'] ?: 0,
                    $row['bna_sold'] ?: 0,
                    $row['bna_check'] ?: 0
                );
                $stmt->execute();
                $migrated_count++;
            }
            
            // Migrate Baraka data
            if ($row['baraka_remise'] || $row['baraka_sold'] || $row['baraka_check']) {
                $transaction_date = date('Y-m-d', strtotime($row['creation_time']));
                $stmt->bind_param("issddd", 
                    $bank_ids['BARAKA'], 
                    $transaction_date, 
                    $row['creation_time'],
                    $row['baraka_remise'] ?: 0,
                    $row['baraka_sold'] ?: 0,
                    $row['baraka_check'] ?: 0
                );
                $stmt->execute();
                $migrated_count++;
            }
        }
        
        echo "   ✅ Migrated {$migrated_count} transaction records\n";
    }
    
    $conn->commit();
    echo "\n🎉 Migration completed successfully!\n\n";
    
    // Show summary
    echo "=== Migration Summary ===\n";
    $summary_sql = "SELECT 
        b.bank_name,
        COUNT(bt.id_transaction) as transaction_count,
        SUM(bt.remise) as total_remise,
        SUM(bt.sold) as total_sold,
        SUM(bt.check_amount) as total_checks
    FROM banks b
    LEFT JOIN bank_transactions bt ON b.id_bank = bt.bank_id
    GROUP BY b.id_bank, b.bank_name";
    
    $result = $conn->query($summary_sql);
    while ($row = $result->fetch_assoc()) {
        echo "Bank: {$row['bank_name']}\n";
        echo "  Transactions: {$row['transaction_count']}\n";
        echo "  Total Remise: " . number_format($row['total_remise'], 2) . " DZD\n";
        echo "  Total Sold: " . number_format($row['total_sold'], 2) . " DZD\n";
        echo "  Total Checks: " . number_format($row['total_checks'], 2) . " DZD\n\n";
    }
    
    echo "Next steps:\n";
    echo "1. Test the new system with bank_improved.php\n";
    echo "2. Use bank_management.php to add/manage banks\n";
    echo "3. Once confirmed working, you can drop the old 'bank' table\n";
    echo "   (The backup 'bank_backup' table will remain for safety)\n\n";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "The database has been rolled back to its previous state.\n";
}

$conn->close();
?>
