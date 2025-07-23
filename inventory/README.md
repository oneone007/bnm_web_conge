# BNM Inventory Management - Python Integration

This document explains how the inventory management system now uses Python for database operations instead of PHP.

## Architecture Overview

The system now follows this architecture:
- **Frontend**: HTML/JavaScript (in `inv.php`)
- **PHP Layer**: Session management and API proxy (`inv.php`)
- **Python API**: Database operations and business logic (`pym.py`)
- **Database**: MySQL local database using root connection

## Files Structure

```
inventory/
├── inv.php                    # Main inventory management page (PHP frontend)
├── pym.py                     # Python Flask API with database operations
├── test_inventory_api.py      # Test script for Python API
└── README.md                  # This file
```

## Python Database Functions

The Python script (`pym.py`) includes these inventory-related functions:

### Core Database Operations
- `get_localdb_connection()` - Connects to local MySQL database with root privileges
- `setup_inventory_tables()` - Creates necessary database tables
- `save_inventory_data(data)` - Saves inventory data to database
- `get_inventory_list(limit, offset, status)` - Retrieves inventory list with pagination
- `get_inventory_details(inventory_id)` - Gets detailed inventory information
- `update_inventory_status(inventory_id, status, updated_by)` - Updates inventory status
- `delete_inventory(inventory_id, deleted_by)` - Deletes inventory and items

### Flask API Endpoints
- `POST /inventory/setup` - Setup database tables
- `POST /inventory/save` - Save inventory data
- `GET /inventory/list` - Get inventory list
- `GET /inventory/details/<id>` - Get inventory details
- `PUT /inventory/update_status/<id>` - Update inventory status
- `DELETE /inventory/delete/<id>` - Delete inventory

## Database Schema

### inventories table
```sql
CREATE TABLE inventories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    notes TEXT,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_by VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### inventory_items table
```sql
CREATE TABLE inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    date DATE,
    lot VARCHAR(100),
    ppa DECIMAL(10, 2) DEFAULT 0.00,
    qty_dispo INT DEFAULT 0,
    type ENUM('entry', 'sortie') NOT NULL,
    is_manual_entry BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_id) REFERENCES inventories(id) ON DELETE CASCADE
);
```

## Setup Instructions

1. **Start the Python Flask API**:
   ```bash
   cd /opt/lampp/htdocs/bnm_web/inventory/
   python3 pym.py
   ```
   The API will run on `http://localhost:5003`

2. **Setup Database Tables** (first time only):
   ```bash
   # Option 1: Use cURL
   curl -X POST http://localhost:5003/inventory/setup
   
   # Option 2: Use the test script
   python3 test_inventory_api.py
   ```

3. **Access the Inventory Management**:
   - Open `http://localhost/bnm_web/inventory/inv.php` in your browser
   - The PHP frontend will communicate with the Python API automatically

## How It Works

1. **User submits inventory data** through the web interface
2. **PHP receives the request** and validates session/permissions
3. **PHP forwards the data** to the Python Flask API via cURL
4. **Python processes the request** and performs database operations
5. **Python returns the result** as JSON
6. **PHP forwards the response** back to the frontend

## Testing

Run the test script to verify everything is working:

```bash
python3 test_inventory_api.py
```

This will test:
- Database connection and setup
- Saving inventory data
- Retrieving inventory list
- Getting inventory details

## Configuration

### Python API Configuration
- **Host**: localhost
- **Port**: 5003
- **Database**: MySQL localhost with root user
- **Database Name**: bnm

### PHP Configuration
- **API URL**: `http://localhost:5003/inventory/save`
- **Timeout**: 30 seconds
- **Method**: cURL POST with JSON data

## Error Handling

The system includes comprehensive error handling:
- **Database connection errors** are logged and returned as JSON
- **Validation errors** prevent invalid data from being saved
- **Transaction rollback** ensures data consistency
- **API communication errors** are handled gracefully in PHP

## Security

- **Session validation** in PHP layer
- **Role-based access control** (restricts certain user roles)
- **SQL injection prevention** through parameterized queries
- **Input validation** on both PHP and Python sides

## Logging

Python operations are logged to:
- Console output (when running interactively)
- Application logs (when run as service)

Check logs for debugging:
```bash
tail -f /opt/lampp/htdocs/bnm_web/inventory/pym.log
```
