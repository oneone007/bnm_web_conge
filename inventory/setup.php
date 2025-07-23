<?php
/**
 * Simple Database Setup Script for Inventory System
 * This script creates the inventory tables using the SQL file
 */

// Include database configuration
require_once __DIR__ . '/../db_config.php';

try {
    echo "<!DOCTYPE html><html><head><title>Inventory Database Setup</title></head><body>";
    echo "<h1>Inventory Management System - Database Setup</h1>\n";
    
    // Read and execute SQL file
    $sql_file = __DIR__ . '/create_tables.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: $sql_file");
    }
    
    echo "<h2>Reading SQL file...</h2>\n";
    $sql_content = file_get_contents($sql_file);
    
    if (!$sql_content) {
        throw new Exception("Could not read SQL file");
    }
    
    echo "<p>✅ SQL file loaded successfully</p>\n";
    
    // Split SQL statements (simple approach)
    $statements = explode(';', $sql_content);
    $executed = 0;
    $errors = 0;
    
    echo "<h2>Executing SQL statements...</h2>\n";
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        // Skip empty statements and comments
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
            echo "<p style='color: green; font-size: 12px;'>✅ Executed: " . substr($statement, 0, 50) . "...</p>\n";
        } catch (PDOException $e) {
            $errors++;
            echo "<p style='color: orange; font-size: 12px;'>⚠️ Warning: " . $e->getMessage() . "</p>\n";
        }
    }
    
    echo "<h2>Setup Results</h2>\n";
    echo "<p><strong>Statements executed:</strong> $executed</p>\n";
    echo "<p><strong>Warnings/Errors:</strong> $errors</p>\n";
    
    // Verify tables exist
    echo "<h2>Verifying Tables</h2>\n";
    $tables = ['inventories', 'inventory_items'];
    $all_good = true;
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>\n";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' NOT found</p>\n";
            $all_good = false;
        }
    }
    
    if ($all_good) {
        echo "<h2 style='color: green;'>🎉 Database setup completed successfully!</h2>\n";
        echo "<div style='margin: 20px 0;'>";
        echo "<a href='inv.php' style='background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Open Inventory System</a>";
        echo "<a href='inv_admin.php' style='background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>Open Admin Panel</a>";
        echo "</div>";
    } else {
        echo "<h2 style='color: red;'>❌ Setup incomplete - some tables are missing</h2>\n";
    }
    
    echo "</body></html>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Database Error</h2>\n";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "</body></html>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error</h2>\n";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "</body></html>";
}
?>
