#!/usr/bin/env python3
"""
Quick setup and test script for BNM Inventory Management
This script sets up the database and tests both user and admin operations
"""

import subprocess
import time
import sys
import json

def run_command(command, description):
    """Run a shell command and return the result"""
    print(f"\n🔄 {description}...")
    try:
        result = subprocess.run(command, shell=True, capture_output=True, text=True, timeout=10)
        if result.returncode == 0:
            print(f"✅ {description} - Success")
            return True, result.stdout
        else:
            print(f"❌ {description} - Failed")
            print(f"Error: {result.stderr}")
            return False, result.stderr
    except subprocess.TimeoutExpired:
        print(f"⏰ {description} - Timeout")
        return False, "Command timed out"
    except Exception as e:
        print(f"❌ {description} - Exception: {e}")
        return False, str(e)

def check_python_api():
    """Check if Python API is running"""
    print("\n🔍 Checking if Python API is running...")
    success, output = run_command("curl -s http://localhost:5003/test_db_connection", "Testing API connection")
    if success:
        try:
            result = json.loads(output)
            if result.get('status') == 'success':
                print("✅ Python API is running and database is connected")
                return True
        except:
            pass
    print("❌ Python API is not running or not responding")
    print("📝 To start the API, run: python3 /opt/lampp/htdocs/bnm_web/inventory/pym.py")
    return False

def setup_database():
    """Setup database tables"""
    print("\n🗄️ Setting up database tables...")
    success, output = run_command("curl -s -X POST http://localhost:5003/inventory/setup", "Creating inventory tables")
    if success:
        try:
            result = json.loads(output)
            if result.get('success'):
                print("✅ Database tables created successfully")
                return True
            else:
                print(f"❌ Database setup failed: {result.get('error')}")
        except:
            print(f"❌ Invalid response: {output}")
    return False

def test_save_inventory():
    """Test saving an inventory"""
    print("\n💾 Testing inventory save...")
    
    test_data = {
        "title": "Test Admin Inventory",
        "notes": "This is a test inventory for admin operations",
        "created_by": "admin_test",
        "items": [
            {
                "product": "Admin Test Product 1",
                "qty": 15,
                "date": "2025-01-17",
                "lot": "ADM001",
                "ppa": 45.75,
                "qty_dispo": 150,
                "type": "entry",
                "is_manual_entry": False
            },
            {
                "product": "Admin Test Product 2",
                "qty": 8,
                "date": "2025-01-17",
                "lot": "ADM002",
                "ppa": 60.25,
                "qty_dispo": 80,
                "type": "sortie",
                "is_manual_entry": True
            }
        ]
    }
    
    # Save test data to a temporary file
    import tempfile
    with tempfile.NamedTemporaryFile(mode='w', suffix='.json', delete=False) as f:
        json.dump(test_data, f)
        temp_file = f.name
    
    success, output = run_command(
        f"curl -s -X POST -H 'Content-Type: application/json' -d @{temp_file} http://localhost:5003/inventory/save",
        "Saving test inventory"
    )
    
    # Clean up temp file
    import os
    os.unlink(temp_file)
    
    if success:
        try:
            result = json.loads(output)
            if result.get('success'):
                inventory_id = result.get('inventory_id')
                print(f"✅ Inventory saved successfully with ID: {inventory_id}")
                return inventory_id
            else:
                print(f"❌ Save failed: {result.get('error')}")
        except:
            print(f"❌ Invalid response: {output}")
    return None

def test_list_inventories():
    """Test getting inventory list"""
    print("\n📋 Testing inventory list retrieval...")
    success, output = run_command("curl -s 'http://localhost:5003/inventory/list?limit=5'", "Getting inventory list")
    if success:
        try:
            result = json.loads(output)
            if result.get('success'):
                inventories = result.get('inventories', [])
                print(f"✅ Retrieved {len(inventories)} inventories")
                return True
            else:
                print(f"❌ List failed: {result.get('error')}")
        except:
            print(f"❌ Invalid response: {output}")
    return False

def test_status_update(inventory_id):
    """Test updating inventory status"""
    if not inventory_id:
        print("\n⚠️ Skipping status update test - no inventory ID")
        return False
        
    print(f"\n🔄 Testing status update for inventory {inventory_id}...")
    
    test_data = {
        "status": "confirmed",
        "updated_by": "admin_test"
    }
    
    import tempfile
    with tempfile.NamedTemporaryFile(mode='w', suffix='.json', delete=False) as f:
        json.dump(test_data, f)
        temp_file = f.name
    
    success, output = run_command(
        f"curl -s -X PUT -H 'Content-Type: application/json' -d @{temp_file} http://localhost:5003/inventory/update_status/{inventory_id}",
        "Updating inventory status"
    )
    
    # Clean up temp file
    import os
    os.unlink(temp_file)
    
    if success:
        try:
            result = json.loads(output)
            if result.get('success'):
                print("✅ Status updated successfully")
                return True
            else:
                print(f"❌ Status update failed: {result.get('error')}")
        except:
            print(f"❌ Invalid response: {output}")
    return False

def test_inventory_details(inventory_id):
    """Test getting inventory details"""
    if not inventory_id:
        print("\n⚠️ Skipping details test - no inventory ID")
        return False
        
    print(f"\n📄 Testing inventory details for ID {inventory_id}...")
    success, output = run_command(f"curl -s http://localhost:5003/inventory/details/{inventory_id}", "Getting inventory details")
    if success:
        try:
            result = json.loads(output)
            if result.get('success'):
                inventory = result.get('inventory', {})
                items = result.get('items', [])
                print(f"✅ Retrieved details for '{inventory.get('title')}' with {len(items)} items")
                return True
            else:
                print(f"❌ Details failed: {result.get('error')}")
        except:
            print(f"❌ Invalid response: {output}")
    return False

def main():
    """Main test function"""
    print("=" * 70)
    print("🚀 BNM Inventory Management - Setup & Test Script")
    print("=" * 70)
    print("This script will:")
    print("1. Check if Python API is running")
    print("2. Setup database tables")
    print("3. Test inventory operations")
    print("4. Test admin operations")
    print("=" * 70)
    
    # Check if API is running
    if not check_python_api():
        print("\n❌ Cannot proceed without the Python API running.")
        print("Please start it with: python3 /opt/lampp/htdocs/bnm_web/inventory/pym.py")
        return False
    
    # Setup database
    if not setup_database():
        print("\n❌ Database setup failed. Cannot proceed.")
        return False
    
    # Test basic operations
    inventory_id = test_save_inventory()
    test_list_inventories()
    test_status_update(inventory_id)
    test_inventory_details(inventory_id)
    
    print("\n" + "=" * 70)
    print("✅ All tests completed!")
    print("🌐 You can now access:")
    print("   • User Interface: http://localhost/bnm_web/inventory/inv.php")
    print("   • Admin Interface: http://localhost/bnm_web/inventory/inv_admin.php")
    print("   • Python API: http://localhost:5003")
    print("=" * 70)
    
    return True

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\n⚠️ Test interrupted by user")
    except Exception as e:
        print(f"\n\n❌ Unexpected error: {e}")
        sys.exit(1)
