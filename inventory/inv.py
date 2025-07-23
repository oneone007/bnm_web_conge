
import oracledb
from flask import Flask, jsonify, request, send_file, make_response
from flask_cors import CORS
import logging
import pandas as pd
from io import BytesIO
from openpyxl import Workbook
from openpyxl.styles import PatternFill, Font
from openpyxl.worksheet.table import Table, TableStyleInfo
from datetime import datetime
import mysql.connector


import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart

from reportlab.lib.pagesizes import letter
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table as ReportLabTable, TableStyle
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib import colors
from reportlab.lib.units import inch
from datetime import datetime
import io

app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Configure Oracle database connection pool
DB_POOL = oracledb.create_pool(
    user="compiere",
    password="compiere",
    dsn="192.168.1.213/compiere",
    min=2,
    max=10,
    increment=1
)

# Email sending utility
def send_email(subject, body, to_email,
               from_email='benmalek.abderrahmane@bnmparapharm.com',
               from_password='********',
               smtp_server='mail.bnmparapharm.com',
               smtp_port=465):
    """Send an email using your company SMTP server."""
    try:
        msg = MIMEMultipart()
        msg['From'] = from_email
        msg['To'] = to_email
        msg['Subject'] = subject
        msg.attach(MIMEText(body, 'plain'))

        # Use SSL if port 465, else TLS
        if smtp_port == 465:
            import smtplib
            server = smtplib.SMTP_SSL(smtp_server, smtp_port)
            logger.info(f"Connecting to SMTP server {smtp_server}:{smtp_port} using SSL...")
        else:
            server = smtplib.SMTP(smtp_server, smtp_port)
            logger.info(f"Connecting to SMTP server {smtp_server}:{smtp_port} using TLS...")
            server.starttls()

        if from_password is None:
            logger.error("No password provided for email sending.")
            raise Exception("No password provided for email sending.")
        server.login(from_email, from_password)
        logger.info(f"Logged in as {from_email}, sending email to {to_email}...")
        server.send_message(msg)
        server.quit()
        logger.info(f"Email sent successfully to {to_email} with subject '{subject}'")
        return {"success": True, "message": "Email sent successfully"}
    except Exception as e:
        logger.error(f"Failed to send email to {to_email}: {e}")
        return {"success": False, "error": str(e)}

# MySQL connection for bank data
def get_localdb_connection():
    try:
        return mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="bnm",
            charset="utf8",
            use_unicode=True,
            autocommit=False
        )
    except mysql.connector.Error as err:
        logger.error(f"Error connecting to MySQL database: {err}")
        return None


# Inventory Management Functions for Local MySQL Database

def setup_inventory_tables():
    """Create inventory tables if they don't exist"""
    connection = get_localdb_connection()
    if not connection:
        return {"success": False, "error": "Database connection failed"}
    
    try:
        cursor = connection.cursor()
        
        # Create inventories table
        create_inventories_table = """
        CREATE TABLE IF NOT EXISTS inventories (
            id INT(11) NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            notes TEXT DEFAULT NULL,
            status ENUM('pending', 'confirmed', 'canceled', 'done') NOT NULL DEFAULT 'pending',
            created_by VARCHAR(100) NOT NULL COMMENT 'Username of the user who created this inventory',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL,
            completed_at DATETIME DEFAULT NULL,
            casse ENUM('yes') DEFAULT NULL COMMENT 'Indicates if this inventory is related to casse',
            PRIMARY KEY (id),
            KEY idx_status (status),
            KEY idx_created_by (created_by),
            KEY idx_created_at (created_at),
            KEY idx_casse (casse)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        """
        
        # Create inventory_items table
        create_inventory_items_table = """
        CREATE TABLE IF NOT EXISTS inventory_items (
            id INT(11) NOT NULL AUTO_INCREMENT,
            inventory_id INT(11) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            quantity INT(11) NOT NULL,
            date DATE DEFAULT NULL,
            lot VARCHAR(100) DEFAULT NULL,
            ppa DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Prix Produit Achat (Purchase Price)',
            qty_dispo INT(11) DEFAULT 0 COMMENT 'Quantity Available',
            type ENUM('entry','sortie') NOT NULL DEFAULT 'entry',
            is_manual_entry TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Flag to indicate if this is a manual entry',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_inventory_id (inventory_id),
            KEY idx_product_name (product_name),
            KEY idx_type (type),
            KEY idx_is_manual_entry (is_manual_entry),
            CONSTRAINT fk_inventory_items_inventory 
                FOREIGN KEY (inventory_id) REFERENCES inventories(id) 
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        """
        
        cursor.execute(create_inventories_table)
        cursor.execute(create_inventory_items_table)
        
        # Add casse column if it doesn't exist (for existing databases)
        try:
            alter_casse_query = """
                ALTER TABLE inventories 
                ADD COLUMN casse ENUM('yes') DEFAULT NULL COMMENT 'Indicates if this inventory is related to casse',
                ADD KEY idx_casse (casse)
            """
            cursor.execute(alter_casse_query)
            logger.info("Added casse column to inventories table")
        except mysql.connector.Error as e:
            if "Duplicate column name" in str(e):
                logger.info("Casse column already exists")
            else:
                logger.warning(f"Could not add casse column: {e}")
        
        connection.commit()
        
        logger.info("Inventory tables created/verified successfully")
        return {"success": True, "message": "Tables created successfully"}
        
    except mysql.connector.Error as e:
        logger.error(f"Error creating tables: {e}")
        connection.rollback()
        return {"success": False, "error": str(e)}
    finally:
        cursor.close()
        connection.close()


def save_inventory_data(data):
    """Save inventory data to local MySQL database"""
    connection = get_localdb_connection()
    if not connection:
        return {"success": False, "error": "Database connection failed"}
    
    try:
        # Validate input data
        if not data or 'title' not in data or 'items' not in data:
            return {"success": False, "error": "Missing required fields: title and items"}
        
        title = data.get('title', '').strip()
        notes = data.get('notes', '').strip() if data.get('notes') else None
        items = data.get('items', [])
        created_by = data.get('created_by', 'system')
        casse = data.get('casse', None)  # New casse field
        
        if not title:
            return {"success": False, "error": "Title cannot be empty"}
        
        if not items:
            return {"success": False, "error": "No items to save"}
        
        cursor = connection.cursor()
        
        # Start transaction
        connection.start_transaction()
        
        # Insert into inventories table with casse field
        inventory_query = """
            INSERT INTO inventories (title, notes, status, created_by, created_at, casse) 
            VALUES (%s, %s, 'pending', %s, NOW(), %s)
        """
        cursor.execute(inventory_query, (title, notes, created_by, casse))
        inventory_id = cursor.lastrowid
        
        # Insert items into inventory_items table
        item_query = """
            INSERT INTO inventory_items 
            (inventory_id, product_name, quantity, date, lot, ppa, qty_dispo, type, is_manual_entry) 
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        item_count = 0
        for item in items:
            # Validate item data
            if not item.get('product') or not item.get('qty') or not item.get('type'):
                continue
            
            product_name = item.get('product', '').strip()
            quantity = int(item.get('qty', 0))
            date = item.get('date') if item.get('date') else None
            lot = item.get('lot', '').strip() if item.get('lot') else None
            ppa = float(item.get('ppa', 0.0))
            qty_dispo = int(item.get('qty_dispo', 0))
            item_type = item.get('type', '').strip()
            is_manual_entry = bool(item.get('is_manual_entry', False))
            
            # Skip invalid items
            if quantity <= 0 or not product_name or item_type not in ['entry', 'sortie']:
                continue
            
            cursor.execute(item_query, (
                inventory_id,
                product_name,
                quantity,
                date,
                lot,
                ppa,
                qty_dispo,
                item_type,
                is_manual_entry
            ))
            
            item_count += 1
        
        if item_count == 0:
            connection.rollback()
            return {"success": False, "error": "No valid items to save"}
        
        # Commit transaction
        connection.commit()
        
        result = {
            'success': True,
            'inventory_id': inventory_id,
            'total_items': item_count,
            'message': 'Inventory saved as pending successfully'
        }
        
        logger.info(f"Inventory saved successfully: ID {inventory_id}, Items: {item_count}, Casse: {casse}")
        return result
        
    except (mysql.connector.Error, ValueError) as e:
        connection.rollback()
        error_msg = str(e)
        logger.error(f"Error saving inventory: {error_msg}")
        return {"success": False, "error": error_msg}
    finally:
        cursor.close()
        connection.close()


def get_inventory_list(limit=50, offset=0, status=None):
    """Get list of inventories with pagination"""
    connection = get_localdb_connection()
    if not connection:
        return {"success": False, "error": "Database connection failed"}
    
    try:
        cursor = connection.cursor(dictionary=True)
        
        # Build query with optional status filter
        base_query = """
            SELECT 
                i.id,
                i.title,
                i.notes,
                i.status,
                i.created_by,
                i.created_at,
                i.updated_at,
                i.casse,
                COUNT(ii.id) as total_items,
                SUM(CASE WHEN ii.type = 'entry' THEN ii.quantity ELSE 0 END) as total_entries,
                SUM(CASE WHEN ii.type = 'sortie' THEN ii.quantity ELSE 0 END) as total_sorties
            FROM inventories i
            LEFT JOIN inventory_items ii ON i.id = ii.inventory_id
        """
        
        params = []
        if status:
            base_query += " WHERE i.status = %s"
            params.append(status)
        
        base_query += """
            GROUP BY i.id, i.title, i.notes, i.status, i.created_by, i.created_at, i.updated_at, i.casse
            ORDER BY i.created_at DESC
            LIMIT %s OFFSET %s
        """
        params.extend([limit, offset])
        
        cursor.execute(base_query, params)
        inventories = cursor.fetchall()
        
        # Convert datetime objects to strings for JSON serialization
        for inventory in inventories:
            if inventory['created_at']:
                inventory['created_at'] = inventory['created_at'].isoformat()
            if inventory['updated_at']:
                inventory['updated_at'] = inventory['updated_at'].isoformat()
        
        return {"success": True, "inventories": inventories}
        
    except mysql.connector.Error as e:
        logger.error(f"Error getting inventory list: {e}")
        return {"success": False, "error": str(e)}
    finally:
        cursor.close()
        connection.close()


def get_inventory_details(inventory_id):
    """Get detailed inventory information with items"""
    connection = get_localdb_connection()
    if not connection:
        return {"success": False, "error": "Database connection failed"}
    
    try:
        cursor = connection.cursor(dictionary=True)
        
        # Get inventory header
        inventory_query = "SELECT * FROM inventories WHERE id = %s"
        cursor.execute(inventory_query, (inventory_id,))
        inventory = cursor.fetchone()
        
        if not inventory:
            return {"success": False, "error": "Inventory not found"}
        
        # Get inventory items
        items_query = "SELECT * FROM inventory_items WHERE inventory_id = %s ORDER BY type, product_name"
        cursor.execute(items_query, (inventory_id,))
        items = cursor.fetchall()
        
        # Convert datetime objects to strings
        if inventory['created_at']:
            inventory['created_at'] = inventory['created_at'].isoformat()
        if inventory['updated_at']:
            inventory['updated_at'] = inventory['updated_at'].isoformat()
        
        for item in items:
            if item['created_at']:
                item['created_at'] = item['created_at'].isoformat()
            if item['date']:
                item['date'] = item['date'].isoformat()
        
        return {"success": True, "inventory": inventory, "items": items}
        
    except mysql.connector.Error as e:
        logger.error(f"Error getting inventory details: {e}")
        return {"success": False, "error": str(e)}
    finally:
        cursor.close()
        connection.close()


def update_inventory_status(inventory_id, status, updated_by):
    """Update inventory status with proper workflow validation"""
    connection = get_localdb_connection()
    if not connection:
        return {"success": False, "error": "Database connection failed"}
    
    try:
        # Validate status
        valid_statuses = ['pending', 'confirmed', 'canceled', 'done']
        if status not in valid_statuses:
            return {"success": False, "error": f"Invalid status. Must be one of: {', '.join(valid_statuses)}"}
        
        cursor = connection.cursor(dictionary=True)
        
        # Get current inventory status
        check_query = "SELECT id, status, title FROM inventories WHERE id = %s"
        cursor.execute(check_query, (inventory_id,))
        inventory = cursor.fetchone()
        
        if not inventory:
            return {"success": False, "error": "Inventory not found"}
        
        current_status = inventory['status']
        
        # Validate status transitions based on workflow rules
        valid_transitions = {
            'pending': ['confirmed', 'canceled'],  # From pending: can be confirmed or canceled
            'confirmed': ['done', 'canceled'],     # From confirmed: can be marked as done or canceled
            'canceled': ['pending'],               # From canceled: can be reopened (pending)
            'done': []                            # From done: no transitions allowed (final state)
        }
        
        if current_status == status:
            return {"success": False, "error": f"Inventory is already {status}"}
        
        if status not in valid_transitions[current_status]:
            return {"success": False, "error": f"Cannot change status from '{current_status}' to '{status}'. Valid transitions: {', '.join(valid_transitions[current_status]) if valid_transitions[current_status] else 'none (final state)'}"}
        
        # Update the status
        update_query = """
            UPDATE inventories 
            SET status = %s, 
                updated_at = NOW(),
                completed_at = CASE WHEN %s = 'done' THEN NOW() ELSE completed_at END
            WHERE id = %s
        """
        cursor.execute(update_query, (status, status, inventory_id))
        connection.commit()
        
        logger.info(f"Inventory {inventory_id} ('{inventory['title']}') status changed from '{current_status}' to '{status}' by {updated_by}")
        
        return {
            "success": True, 
            "message": f"Inventory status updated from '{current_status}' to '{status}'",
            "previous_status": current_status,
            "new_status": status
        }
        
    except mysql.connector.Error as e:
        connection.rollback()
        logger.error(f"Error updating inventory status: {e}")
        return {"success": False, "error": str(e)}
    finally:
        cursor.close()
        connection.close()


def delete_inventory(inventory_id, deleted_by):
    """Delete inventory and all its items"""
    connection = get_localdb_connection()
    if not connection:
        return {"success": False, "error": "Database connection failed"}
    
    try:
        cursor = connection.cursor()
        
        # Check if inventory exists
        check_query = "SELECT id FROM inventories WHERE id = %s"
        cursor.execute(check_query, (inventory_id,))
        
        if not cursor.fetchone():
            return {"success": False, "error": "Inventory not found"}
        
        # Start transaction
        connection.start_transaction()
        
        # Delete inventory items first (foreign key constraint)
        cursor.execute("DELETE FROM inventory_items WHERE inventory_id = %s", (inventory_id,))
        items_deleted = cursor.rowcount
        
        # Delete inventory
        cursor.execute("DELETE FROM inventories WHERE id = %s", (inventory_id,))
        
        connection.commit()
        
        logger.info(f"Inventory {inventory_id} deleted by {deleted_by} (items: {items_deleted})")
        return {"success": True, "message": f"Inventory and {items_deleted} items deleted successfully"}
        
    except mysql.connector.Error as e:
        connection.rollback()
        logger.error(f"Error deleting inventory: {e}")
        return {"success": False, "error": str(e)}
    finally:
        cursor.close()
        connection.close()


# Add new Flask routes for inventory management

@app.route('/inventory/setup', methods=['POST'])
def setup_inventory_tables_route():
    """Setup inventory database tables"""
    result = setup_inventory_tables()
    if result['success']:
        return jsonify(result), 200
    else:
        return jsonify(result), 500


@app.route('/inventory/save', methods=['POST'])
def save_inventory_route():
    """Save inventory data"""
    try:
        data = request.get_json()
        if not data:
            return jsonify({"success": False, "error": "No data provided"}), 400


        result = save_inventory_data(data)

        # Prepare inventory details for email body
        if result['success']:
            inv_id = result.get('inventory_id')
            total_items = result.get('total_items')
            title = data.get('title', '')
            notes = data.get('notes', '')
            casse = data.get('casse', '')
            items = data.get('items', [])
            item_lines = []
            for item in items:
                item_lines.append(f"- {item.get('product','')} | Qty: {item.get('qty','')} | Type: {item.get('type','')}")
            items_str = '\n'.join(item_lines)
            body = f"The inventory has been created successfully.\n\nInventory ID: {inv_id}\nTitle: {title}\nNotes: {notes}\nCasse: {casse}\nTotal Items: {total_items}\n\nItems:\n{items_str}\n Best regards,\nBNM System "
        else:
            body = f"Inventory creation failed. Error: {result.get('error','Unknown error')}"

        # Send email after saving inventory (regardless of success)
        email_result = send_email(
            subject="INVENTORY CREATED",
            body=body,
            to_email="benmalek.abderrahmane@bnmparapharm.com"
        )
        logger.info(f"Email send result: {email_result}")

        if result['success']:
            return jsonify(result), 200
        else:
            return jsonify(result), 400

    except Exception as e:
        logger.error(f"Error in save_inventory_route: {e}")
        return jsonify({"success": False, "error": str(e)}), 500


@app.route('/inventory/list', methods=['GET'])
def get_inventory_list_route():
    """Get inventory list with pagination"""
    try:
        limit = int(request.args.get('limit', 50))
        offset = int(request.args.get('offset', 0))
        status = request.args.get('status', None)
        
        result = get_inventory_list(limit, offset, status)
        return jsonify(result), 200
        
    except Exception as e:
        logger.error(f"Error in get_inventory_list_route: {e}")
        return jsonify({"success": False, "error": str(e)}), 500


@app.route('/inventory/details/<int:inventory_id>', methods=['GET'])
def get_inventory_details_route(inventory_id):
    """Get inventory details by ID"""
    try:
        result = get_inventory_details(inventory_id)
        return jsonify(result), 200
        
    except Exception as e:
        logger.error(f"Error in get_inventory_details_route: {e}")
        return jsonify({"success": False, "error": str(e)}), 500


@app.route('/inventory/update_status/<int:inventory_id>', methods=['PUT'])
def update_inventory_status_route(inventory_id):
    """Update inventory status"""
    try:
        data = request.get_json()
        if not data or 'status' not in data or 'updated_by' not in data:
            return jsonify({"success": False, "error": "Status and updated_by are required"}), 400
        
        result = update_inventory_status(inventory_id, data['status'], data['updated_by'])
        if result['success']:
            return jsonify(result), 200
        else:
            return jsonify(result), 400
            
    except Exception as e:
        logger.error(f"Error in update_inventory_status_route: {e}")
        return jsonify({"success": False, "error": str(e)}), 500


@app.route('/inventory/delete/<int:inventory_id>', methods=['DELETE'])
def delete_inventory_route(inventory_id):
    """Delete inventory"""
    try:
        data = request.get_json()
        if not data or 'deleted_by' not in data:
            return jsonify({"success": False, "error": "deleted_by is required"}), 400
        
        result = delete_inventory(inventory_id, data['deleted_by'])
        if result['success']:
            return jsonify(result), 200
        else:
            return jsonify(result), 400
            
    except Exception as e:
        logger.error(f"Error in delete_inventory_route: {e}")
        return jsonify({"success": False, "error": str(e)}), 500


# Test database connection
def test_db_connection():
    try:
        with DB_POOL.acquire() as connection:
            logger.info("Database connection successful.")
        return True
    except Exception as e:
        logger.error(f"Database connection failed: {str(e)}")
        return False
    

@app.route('/test_db_connection', methods=['GET'])
def test_db_connection_route():
    if test_db_connection():
        return jsonify({"status": "success", "message": "Database connection successful."}), 200
    else:
        return jsonify({"status": "error", "message": "Database connection failed."}), 500
    



def fetch_rotation_product_data():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
            SELECT M_product_id, name FROM M_PRODUCT
                WHERE AD_Client_ID = 1000000
                AND AD_Org_ID = 1000000
                AND ISACTIVE = 'Y'
                AND ROWNUM <= 10
                ORDER BY name

            """
            cursor.execute(query)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            data = [dict(zip(columns, row)) for row in rows]
            return data
    except Exception as e:
        logger.error(f"Error fetching remise data: {e}")
        return {"error": "An error occurred while fetching emplacement data."}
    
@app.route('/fetch-rotation-product-data', methods=['GET'])
def fetch_data():
    data = fetch_rotation_product_data()
    return jsonify(data)


@app.route('/listproduct_inv', methods=['GET'])
def listproduct_inv():
    """
    Returns list of products with both ID and name for inventory management
    """
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
            SELECT M_product_id, name FROM M_PRODUCT
            WHERE AD_Client_ID = 1000000
            AND AD_Org_ID = 1000000
            AND ISACTIVE = 'Y'
            ORDER BY name
            """
            cursor.execute(query)
            rows = cursor.fetchall()
            # Return array of objects with id and name
            products = [{"id": row[0], "name": row[1]} for row in rows]
            return jsonify(products)
    except Exception as e:
        logger.error(f"Error fetching product list: {e}")
        return jsonify({"error": "Could not fetch products list"}), 500


@app.route('/fetch-product-details', methods=['GET'])
def fetch_product_details():
    try:
        product_name = request.args.get("product_name", None)
        
        if not product_name:
            return jsonify({"error": "Product name is required"}), 400

        data = fetch_product_details_data(product_name)
        return jsonify(data)

    except Exception as e:
        logger.error(f"Error fetching product details: {e}")
        return jsonify({"error": "Failed to fetch product details"}), 500


@app.route('/inventory-products', methods=['GET'])
def inventory_products():
    try:
        product_id = request.args.get("product_id", None)
        category = request.args.get("category", "all")  # Default to "all" if no category provided
        
        if not product_id:
            return jsonify({"error": "Product ID is required"}), 400

        data = fetch_inventory_products_data(product_id, category)
        return jsonify(data)

    except Exception as e:
        logger.error(f"Error fetching inventory products: {e}")
        return jsonify({"error": "Failed to fetch inventory products"}), 500


def fetch_inventory_products_data(product_id, category="all"):
    """
    Fetch inventory product information - returns Product, Lot, PPA, QTY_DISPO, Guarantee Date, P_REVIENT
    Filter by category to show different locator groups.
    
    Categories:
    - 'all': Shows all locators (1001135, 1000614, 1001128, 1001136, 1001020, 1000314, 1000210, 1000211, 1000109, 1000209, 1000213, 1000214, 1000414, 1000817, 1001129)
    - 'preparation': Shows preparation locators (1001135, 1000614, 1001128, 1001136, 1001020)
    - 'tempo': Shows temporary storage locators (1000314, 1000210, 1000211, 1000109, 1000209, 1000213, 1000214, 1000414, 1000817, 1001129)
    
    Returns:
    - PRODUCT: Product name
    - LOT: Lot number
    - PPA: Prix Produit Achat (Purchase Price)
    - QTY_DISPO: Available quantity (on hand - reserved)
    - GUARANTEEDATE: Guarantee/expiry date
    - P_REVIENT: Cost price calculated as (P_ACHAT - (P_ACHAT * REM_ACHAT / 100)) / (1 + BON_ACHAT / 100)
    """
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            
            # Define locator IDs for different categories
            # These are logical groupings based on the existing locator IDs
            locator_groups = {
                "all": "(1001135, 1000614, 1001128, 1001136, 1001020, 1000314, 1000210, 1000211, 1000109, 1000209, 1000213, 1000214, 1000414, 1000817, 1001129)",
                "preparation": "(1001135, 1000614, 1001128, 1001136, 1001020)",  # First group - possibly preparation areas
                "tempo": "(1000314, 1000210, 1000211, 1000109, 1000209, 1000213, 1000214, 1000414, 1000817, 1001129)"  # Second group - possibly temporary storage areas
            }
            
            # Get the appropriate locator list for the category
            locator_list = locator_groups.get(category, locator_groups["all"])
            
            # Log the category filter being used
            logger.info(f"Fetching inventory products with P_REVIENT for product_id: {product_id}, category: {category}, locators: {locator_list}")
            
            query = f"""
            SELECT
                p.name AS PRODUCT,
                (
                    SELECT
                        lot
                    FROM
                        m_attributesetinstance
                    WHERE
                        m_attributesetinstance_id = mst.m_attributesetinstance_id
                ) AS LOT,
                (
                    SELECT
                        valuenumber
                    FROM
                        m_attributeinstance
                    WHERE
                        m_attributesetinstance_id = mst.m_attributesetinstance_id
                        AND m_attribute_id = 1000503
                ) AS PPA,
                (mst.qtyonhand - mst.QTYRESERVED) AS QTY_DISPO,
                mats.guaranteedate AS GUARANTEEDATE,
                ROUND(
                    (
                        (
                            SELECT
                                valuenumber
                            FROM
                                m_attributeinstance
                            WHERE
                                m_attributesetinstance_id = mst.m_attributesetinstance_id
                                AND m_attribute_id = 1000501
                        ) - (
                            (
                                SELECT
                                    valuenumber
                                FROM
                                    m_attributeinstance
                                WHERE
                                    m_attributesetinstance_id = mst.m_attributesetinstance_id
                                    AND m_attribute_id = 1000501
                            ) * (
                                SELECT
                                    NVL(valuenumber, 0)
                                FROM
                                    m_attributeinstance
                                WHERE
                                    m_attributesetinstance_id = mst.m_attributesetinstance_id
                                    AND m_attribute_id = 1001009
                            ) / 100
                        )
                    ) / (
                        1 + (
                            (
                                SELECT
                                    NVL(valuenumber, 0)
                                FROM
                                    m_attributeinstance
                                WHERE
                                    m_attributesetinstance_id = mst.m_attributesetinstance_id
                                    AND m_attribute_id = 1000808
                            ) / 100
                        )
                    ), 2
                ) AS P_REVIENT
            FROM
                m_product p
                INNER JOIN m_storage mst ON p.m_product_id = mst.m_product_id
                INNER JOIN m_attributesetinstance mats ON mst.m_attributesetinstance_id = mats.m_attributesetinstance_id
            WHERE
                p.m_product_id = :product_id
                AND mst.m_locator_id IN {locator_list}
                AND mst.qtyonhand != 0
            ORDER BY
                p.name, mats.guaranteedate
            """

            cursor.execute(query, {"product_id": product_id})
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            data = [dict(zip(columns, row)) for row in rows]

            return data

    except Exception as e:
        logger.error(f"Error fetching inventory products: {e}")
        return {"error": "An error occurred while fetching inventory products."}

 
def fetch_product_details_data(product_name):
    """
    Fetch detailed product information similar to the marge data structure
    """
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            
            query = """
            SELECT
                *
            FROM
                (
                    SELECT
                        "source"."FOURNISSEUR" "FOURNISSEUR",
                        "source"."PRODUCT" "PRODUCT",
                        "source"."P_ACHAT" "P_ACHAT",
                        "source"."P_VENTE" "P_VENTE",
                        "source"."REM_ACHAT" "REM_ACHAT",
                        "source"."REM_VENTE" "REM_VENTE",
                        "source"."BON_ACHAT" "BON_ACHAT",
                        "source"."BON_VENTE" "BON_VENTE",
                        "source"."REMISE_AUTO" "REMISE_AUTO",
                        "source"."BONUS_AUTO" "BONUS_AUTO",
                        "source"."P_REVIENT" "P_REVIENT",
                        "source"."MARGE" "MARGE",
                        "source"."LABO" "LABO",
                        "source"."LOT" "LOT",
                        "source"."LOT_ACTIVE" "LOT_ACTIVE",
                        "source"."QTY" "QTY",
                        "source"."QTY_DISPO" "QTY_DISPO",
                        "source"."GUARANTEEDATE" "GUARANTEEDATE",
                        "source"."PPA" "PPA",
                        "source"."LOCATION" "LOCATION"
                    FROM
                        (
                            SELECT
                                DISTINCT fournisseur,
                                product,
                                p_achat,
                                p_vente,
                                round(rem_achat, 2) AS rem_achat,
                                rem_vente,
                                round(bon_achat, 2) AS bon_achat,
                                bon_vente,
                                remise_auto,
                                bonus_auto,
                                round(p_revient, 2) AS p_revient,
                                LEAST(round((marge), 2), 100) AS marge,
                                labo,
                                lot,
                                lot_active,
                                qty,
                                qty_dispo,
                                guaranteedate,
                                ppa,
                                CASE 
                                    WHEN m_locator_id = 1000614 THEN 'Préparation'
                                    WHEN m_locator_id = 1001135 THEN 'HANGAR'
                                    WHEN m_locator_id = 1001128 THEN 'Dépot_réserve'
                                    WHEN m_locator_id = 1001136 THEN 'HANGAR_'
                                    WHEN m_locator_id = 1001020 THEN 'Depot_Vente'
                                END AS location
                            FROM
                                (
                                    SELECT
                                        d.*,
                                        LEAST(
                                            round(
                                                (((ventef - ((ventef * nvl(rma, 0)) / 100))) - p_revient) / p_revient * 100,
                                                2
                                            ), 
                                            100
                                        ) AS marge
                                    FROM
                                        (
                                            SELECT
                                                det.*,
                                                (det.p_achat - ((det.p_achat * det.rem_achat) / 100)) / (1 + (det.bon_achat / 100)) p_revient,
                                                (
                                                    det.p_vente - ((det.p_vente * nvl(det.rem_vente, 0)) / 100)
                                                ) / (
                                                    1 + (
                                                        CASE
                                                            WHEN det.bna > 0 THEN det.bna
                                                            ELSE det.bon_vente
                                                        END / 100
                                                    )
                                                ) ventef
                                            FROM
                                                (
                                                    SELECT
                                                        p.name product,
                                                        (
                                                            SELECT
                                                                NAME
                                                            FROM
                                                                XX_Laboratory
                                                            WHERE
                                                                XX_Laboratory_id = p.XX_Laboratory_id
                                                        ) labo,
                                                        mst.qtyonhand qty,
                                                        (mst.qtyonhand - mst.QTYRESERVED) qty_dispo,
                                                        mst.m_locator_id,
                                                        mati.value fournisseur,
                                                        mats.guaranteedate,
                                                        md.name remise_auto,
                                                        sal.description bonus_auto,
                                                        md.flatdiscount rma,
                                                        TO_NUMBER(
                                                            CASE
                                                                WHEN REGEXP_LIKE(sal.name, '^[0-9]+$') THEN sal.name
                                                                ELSE NULL
                                                            END
                                                        ) AS bna,
                                                        (
                                                            SELECT
                                                                valuenumber
                                                            FROM
                                                                m_attributeinstance
                                                            WHERE
                                                                m_attributesetinstance_id = mst.m_attributesetinstance_id
                                                                AND m_attribute_id = 1000501
                                                        ) p_achat,
                                                        (
                                                            SELECT
                                                                valuenumber
                                                            FROM
                                                                m_attributeinstance
                                                            WHERE
                                                                m_attributesetinstance_id = mst.m_attributesetinstance_id
                                                                AND m_attribute_id = 1001009
                                                        ) rem_achat,
                                                        (
                                                            SELECT
                                                                valuenumber
                                                            FROM
                                                                m_attributeinstance
                                                            WHERE
                                                                m_attributesetinstance_id = mst.m_attributesetinstance_id
                                                                AND m_attribute_id = 1000808
                                                        ) bon_achat,
                                                        (
                                                            SELECT
                                                                valuenumber
                                                            FROM
                                                                m_attributeinstance
                                                            WHERE
                                                                m_attributesetinstance_id = mst.m_attributesetinstance_id
                                                                AND m_attribute_id = 1000502
                                                        ) p_vente,
                                                        (
                                                            SELECT
                                                                valuenumber
                                                            FROM
                                                                m_attributeinstance
                                                            WHERE
                                                                m_attributesetinstance_id = mst.m_attributesetinstance_id
                                                                AND m_attribute_id = 1001408
                                                        ) rem_vente,
                                                        (
                                                            SELECT
                                                                valuenumber
                                                            FROM
                                                                m_attributeinstance
                                                            WHERE
                                                                m_attributesetinstance_id = mst.m_attributesetinstance_id
                                                                AND m_attribute_id = 1000908
                                                        ) bon_vente,
                                                        (
                                                            SELECT
                                                                lot
                                                            FROM
                                                                m_attributesetinstance
                                                            WHERE
                                                                m_attributesetinstance_id = mst.m_attributesetinstance_id
                                                        ) lot,
                                                        (
                                                            SELECT
                                                                isactive
                                                            FROM
                                                                m_attributesetinstance
                                                            WHERE
                                                                m_attributesetinstance_id = mst.m_attributesetinstance_id
                                                        ) lot_active,
                                                        (
                                                            SELECT
                                                                valuenumber
                                                            FROM
                                                                m_attributeinstance
                                                            WHERE
                                                                m_attributesetinstance_id = mst.m_attributesetinstance_id
                                                                AND m_attribute_id = 1000503
                                                        ) ppa
                                                    FROM
                                                        m_product p
                                                        INNER JOIN m_storage mst ON p.m_product_id = mst.m_product_id
                                                        INNER JOIN m_attributeinstance mati ON mst.m_attributesetinstance_id = mati.m_attributesetinstance_id
                                                        INNER JOIN m_attributesetinstance mats ON mst.m_attributesetinstance_id = mats.m_attributesetinstance_id
                                                        LEFT JOIN C_BPartner_Product cp ON cp.m_product_id = p.m_product_id
                                                            OR cp.C_BPartner_Product_id IS NULL
                                                        LEFT JOIN M_DiscountSchema md ON cp.M_DiscountSchema_id = md.M_DiscountSchema_id
                                                        LEFT JOIN XX_SalesContext sal ON p.XX_SalesContext_ID = sal.XX_SalesContext_ID
                                                    WHERE
                                                        mati.m_attribute_id = 1000508
                                                        AND mst.m_locator_id IN (1001135, 1000614, 1001128, 1001136, 1001020)
                                                        AND mst.qtyonhand != 0
                                                        AND p.name = :product_name
                                                    ORDER BY
                                                        p.name
                                                ) det
                                            WHERE
                                                det.rem_achat < 200
                                        ) d
                                )
                            GROUP BY
                                fournisseur,
                                product,
                                p_achat,
                                p_vente,
                                rem_achat,
                                rem_vente,
                                bon_achat,
                                bon_vente,
                                remise_auto,
                                bonus_auto,
                                p_revient,
                                marge,
                                labo,
                                lot,
                                lot_active,
                                qty,
                                qty_dispo,
                                guaranteedate,
                                ppa,
                                m_locator_id
                            ORDER BY
                                fournisseur
                        ) "source"
                )
            WHERE
                rownum <= 1048575
            """

            cursor.execute(query, {"product_name": product_name})
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            data = [dict(zip(columns, row)) for row in rows]

            return data

    except Exception as e:
        logger.error(f"Error fetching product details: {e}")
        return {"error": "An error occurred while fetching product details."}





# Flask route to get count of pending inventories
@app.route('/inventory/pending_count', methods=['GET'])
def get_inv_pending_route():
    """API endpoint to get count of pending inventories"""
    result = get_inv_pending()
    return jsonify(result), 200 if result.get('success') else 500
def get_inv_pending():
    """Return the count of pending inventories"""
    connection = get_localdb_connection()
    if not connection:
        return {"success": False, "error": "Database connection failed", "pending_count": 0}
    try:
        cursor = connection.cursor()
        cursor.execute("SELECT COUNT(*) FROM inventories WHERE status = 'pending'")
        count = cursor.fetchone()[0]
        return {"success": True, "pending_count": count}
    except Exception as e:
        logger.error(f"Error counting pending inventories: {e}")
        return {"success": False, "error": str(e), "pending_count": 0}
    finally:
        cursor.close()
        connection.close()



# Flask route to get count of confirmed inventories with casse = yes
@app.route('/inventory/confirmed_casse_count', methods=['GET'])
def get_confirmed_casse_count_route():
    """API endpoint to get count of confirmed inventories with casse = yes"""
    result = get_confirmed_casse_count()
    return jsonify(result), 200 if result.get('success') else 500

def get_confirmed_casse_count():
    """Return the count of confirmed inventories with casse = yes"""
    connection = get_localdb_connection()
    if not connection:
        return {"success": False, "error": "Database connection failed", "confirmed_casse_count": 0}
    try:
        cursor = connection.cursor()
        cursor.execute("SELECT COUNT(*) FROM inventories WHERE status = 'confirmed' AND casse = 'yes'")
        count = cursor.fetchone()[0]
        return {"success": True, "confirmed_casse_count": count}
    except Exception as e:
        logger.error(f"Error counting confirmed casse inventories: {e}")
        return {"success": False, "error": str(e), "confirmed_casse_count": 0}
    finally:
        cursor.close()
        connection.close()



# Route to send a test email to multiple recipients
@app.route('/send_saisie_mail', methods=['GET'])
def send_saisie_mail_route():
    recipients = [
        "guend.hamza@bnmparapharm.com",
        "seifeddine.nemdili@bnmparapharm.com"
    ]
    subject = "Inventory system notification: Please do the inventory and mark it as done"
    body = (
        "Dear Team,\n\n"
        "The inventory is being created. Please proceed to do the inventory as soon as possible, "
        "and once completed, mark it as done in the system.\n\n"
        "Good job!\n\n"
        "Best regards,\nBNM System"
    )
    results = []
    for to_email in recipients:
        result = send_email(subject=subject, body=body, to_email=to_email)
        logger.info(f"Test mail to {to_email}: {result}")
        results.append({"to": to_email, **result})
    return jsonify({"results": results}), 200 if all(r["success"] for r in results) else 500

        

# Route to send an info email to multiple recipients
@app.route('/send_info_mail', methods=['GET'])
def send_info_mail_route():
    recipients = [
        "maamri.yasser@bnmparapharm.com",
        "mahroug.nazim@bnmparapharm.com",
        "benmalek.abderrahmane@bnmparapharm.com"
    ]
    subject = "INFO: Inventory system notification"
    body = (
        "Dear Team,\n\n"
        "DO THE INV .\n\n"
        "Please proceed to do the inventory as soon as possible, "
        "Best regards,\nBNM System"
    )
    results = []
    for to_email in recipients:
        result = send_email(subject=subject, body=body, to_email=to_email)
        logger.info(f"Info mail to {to_email}: {result}")
        results.append({"to": to_email, **result})
    return jsonify({"results": results}), 200 if all(r["success"] for r in results) else 500





if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5003)