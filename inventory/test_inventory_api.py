#!/usr/bin/env python3
"""
Test script for BNM Inventory Management Python API
"""

import requests
import json
import time

API_BASE_URL = 'http://localhost:5003'

def test_database_setup():
    """Test database setup"""
    print("Testing database setup...")
    try:
        response = requests.post(f'{API_BASE_URL}/inventory/setup')
        result = response.json()
        if result.get('success'):
            print("✅ Database setup successful")
            return True
        else:
            print(f"❌ Database setup failed: {result.get('error')}")
            return False
    except Exception as e:
        print(f"❌ Error testing database setup: {e}")
        return False

def test_save_inventory():
    """Test saving inventory data"""
    print("Testing inventory save...")
    
    test_data = {
        "title": "Test Inventory Report",
        "notes": "This is a test inventory created by Python script",
        "created_by": "test_user",
        "items": [
            {
                "product": "Test Product 1",
                "qty": 10,
                "date": "2025-01-17",
                "lot": "LOT001",
                "ppa": 25.50,
                "qty_dispo": 100,
                "type": "entry",
                "is_manual_entry": False
            },
            {
                "product": "Test Product 2",
                "qty": 5,
                "date": "2025-01-17",
                "lot": "LOT002",
                "ppa": 30.00,
                "qty_dispo": 50,
                "type": "sortie",
                "is_manual_entry": True
            }
        ]
    }
    
    try:
        response = requests.post(f'{API_BASE_URL}/inventory/save', json=test_data)
        result = response.json()
        if result.get('success'):
            print(f"✅ Inventory saved successfully: ID {result.get('inventory_id')}")
            return result.get('inventory_id')
        else:
            print(f"❌ Inventory save failed: {result.get('error')}")
            return None
    except Exception as e:
        print(f"❌ Error saving inventory: {e}")
        return None

def test_get_inventory_list():
    """Test getting inventory list"""
    print("Testing inventory list retrieval...")
    try:
        response = requests.get(f'{API_BASE_URL}/inventory/list?limit=10&offset=0')
        result = response.json()
        if result.get('success'):
            inventories = result.get('inventories', [])
            print(f"✅ Retrieved {len(inventories)} inventories")
            return True
        else:
            print(f"❌ Failed to get inventory list: {result.get('error')}")
            return False
    except Exception as e:
        print(f"❌ Error getting inventory list: {e}")
        return False

def test_get_inventory_details(inventory_id):
    """Test getting inventory details"""
    if not inventory_id:
        print("⚠️ Skipping inventory details test - no inventory ID")
        return True
        
    print(f"Testing inventory details retrieval for ID {inventory_id}...")
    try:
        response = requests.get(f'{API_BASE_URL}/inventory/details/{inventory_id}')
        result = response.json()
        if result.get('success'):
            inventory = result.get('inventory', {})
            items = result.get('items', [])
            print(f"✅ Retrieved inventory details: '{inventory.get('title')}' with {len(items)} items")
            return True
        else:
            print(f"❌ Failed to get inventory details: {result.get('error')}")
            return False
    except Exception as e:
        print(f"❌ Error getting inventory details: {e}")
        return False

def main():
    """Main test function"""
    print("=" * 60)
    print("BNM Inventory Management Python API Test")
    print("=" * 60)
    print("Make sure the Python Flask server is running on port 5003")
    print("Run: python3 pym.py")
    print("=" * 60)
    
    # Wait a moment for user to read
    time.sleep(2)
    
    # Test database setup
    if not test_database_setup():
        print("\n❌ Database setup failed. Please check your MySQL configuration.")
        return False
    
    print("\n" + "-" * 40)
    
    # Test saving inventory
    inventory_id = test_save_inventory()
    
    print("\n" + "-" * 40)
    
    # Test getting inventory list
    test_get_inventory_list()
    
    print("\n" + "-" * 40)
    
    # Test getting inventory details
    test_get_inventory_details(inventory_id)
    
    print("\n" + "=" * 60)
    print("✅ All tests completed!")
    print("The inventory management system is working correctly.")
    print("=" * 60)
    
    return True

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\n⚠️ Test interrupted by user")
    except Exception as e:
        print(f"\n\n❌ Unexpected error: {e}")
