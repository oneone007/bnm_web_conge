#!/usr/bin/env python3
import mysql.connector
import json

def test_db():
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="bnm",
            charset="utf8",
            use_unicode=True
        )
        
        cursor = connection.cursor(dictionary=True)
        
        # Test inventories table
        print("=== INVENTORIES TABLE ===")
        cursor.execute("SELECT * FROM inventories WHERE id IN (50, 51) ORDER BY id")
        inventories = cursor.fetchall()
        for inv in inventories:
            print(f"ID: {inv['id']}, Title: {inv['title']}, Casse: {inv['casse']}, Status: {inv['status']}")
        
        # Test inventory_items table
        print("\n=== INVENTORY_ITEMS TABLE ===")
        cursor.execute("SELECT * FROM inventory_items WHERE inventory_id IN (50, 51) ORDER BY inventory_id, id")
        items = cursor.fetchall()
        for item in items:
            print(f"Inventory ID: {item['inventory_id']}, Product: {item['product_name']}, Qty: {item['quantity']}, Type: {item['type']}")
        
        # Test table structure
        print("\n=== TABLE STRUCTURE ===")
        cursor.execute("DESCRIBE inventories")
        desc = cursor.fetchall()
        print("Inventories columns:", [col['Field'] for col in desc])
        
        cursor.execute("DESCRIBE inventory_items")
        desc = cursor.fetchall()
        print("Inventory_items columns:", [col['Field'] for col in desc])
        
        cursor.close()
        connection.close()
        print("\nDatabase test completed successfully!")
        
    except mysql.connector.Error as e:
        print(f"Database error: {e}")
    except Exception as e:
        print(f"General error: {e}")

if __name__ == "__main__":
    test_db()
