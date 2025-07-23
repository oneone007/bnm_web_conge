<?php
session_start();

// Check if the user is logged in and session is valid
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat','Sup Vente', 'Comptable'])) {
    header("Location: Acess_Denied");    
    exit();
}

require_once __DIR__ . '/../db_config.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Inventory ID is required');
    }
    
    $inventory_id = intval($_GET['id']);
    
    // Get inventory details
    $stmt = $pdo->prepare("
        SELECT id, title, notes, status, created_by, created_at, updated_at, completed_at
        FROM inventories 
        WHERE id = ?
    ");
    $stmt->execute([$inventory_id]);
    $inventory = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inventory) {
        throw new Exception('Inventory not found');
    }
    
    // Get inventory items
    $stmt = $pdo->prepare("
        SELECT product_name, quantity, date, lot, ppa, qty_dispo, type, is_manual_entry, created_at
        FROM inventory_items 
        WHERE inventory_id = ?
        ORDER BY type, product_name
    ");
    $stmt->execute([$inventory_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'inventory' => $inventory,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
