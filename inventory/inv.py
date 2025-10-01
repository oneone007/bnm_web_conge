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
import json
import os

app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# JSON Recipients Management
RECIPIENTS_FILE = os.path.join(os.path.dirname(__file__), 'mail_recipients.json')

def get_recipients_from_json(route_name):
    """Get recipients for a specific route from JSON file"""
    try:
        if os.path.exists(RECIPIENTS_FILE):
            with open(RECIPIENTS_FILE, 'r') as f:
                recipients_data = json.load(f)
                return recipients_data.get(route_name, [])
        return []
    except Exception as e:
        logger.error(f"Error reading recipients JSON: {e}")
        return []

def save_recipients_to_json(route_name, recipients):
    """Save recipients for a specific route to JSON file"""
    try:
        recipients_data = {}
        if os.path.exists(RECIPIENTS_FILE):
            with open(RECIPIENTS_FILE, 'r') as f:
                recipients_data = json.load(f)
        
        recipients_data[route_name] = recipients
        
        with open(RECIPIENTS_FILE, 'w') as f:
            json.dump(recipients_data, f, indent=4)
        
        return True
    except Exception as e:
        logger.error(f"Error saving recipients JSON: {e}")
        return False

def get_all_recipients():
    """Get all recipients configuration"""
    try:
        if os.path.exists(RECIPIENTS_FILE):
            with open(RECIPIENTS_FILE, 'r') as f:
                return json.load(f)
        return {}
    except Exception as e:
        logger.error(f"Error reading recipients JSON: {e}")
        return {}

# Configure Oracle database connection pool
DB_POOL = oracledb.create_pool(
    user="compiere",
    password="compiere",
    dsn="192.168.1.213/compiere",
    min=2,
    max=10,
    increment=1
)

# Mail Management Functions

def setup_mail_tables():
    """Create mail management tables if they don't exist"""
    connection = get_localdb_connection()
    if not connection:
        return {"success": False, "error": "Database connection failed"}
    
    try:
        cursor = connection.cursor()
        
        # Create email_configs table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS email_configs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                config_name VARCHAR(100) NOT NULL UNIQUE,
                subject VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
                from_email VARCHAR(255) NOT NULL DEFAULT 'inventory.system.bnm@bnmparapharm.com',
                from_password VARCHAR(255) NOT NULL DEFAULT 'bnmparapharminv',
                smtp_server VARCHAR(255) NOT NULL DEFAULT 'mail.bnmparapharm.com',
                smtp_port INT NOT NULL DEFAULT 465,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT NULL
            )
        """)
        
        # Create email_contacts table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS email_contacts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                department VARCHAR(100),
                position VARCHAR(100),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT NULL
            )
        """)
        
        # Create email_logs table (only keeps today's data)
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS email_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                config_name VARCHAR(100),
                subject VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
                from_email VARCHAR(255) NOT NULL,
                to_email VARCHAR(255) NOT NULL,
                sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('success', 'failed') NOT NULL,
                error_message TEXT,
                related_inventory_id INT,
                INDEX idx_sent_at (sent_at),
                INDEX idx_date (sent_at)
            )
        """)
        
        connection.commit()
        logger.info("Mail management tables created successfully")
        return {"success": True, "message": "Mail management tables created successfully"}
        
    except mysql.connector.Error as e:
        connection.rollback()
        logger.error(f"Error creating mail tables: {e}")
        return {"success": False, "error": str(e)}
    finally:
        cursor.close()
        connection.close()


def get_email_config(config_name):
    """Get email configuration by name"""
    connection = get_localdb_connection()
    if not connection:
        return None
    
    try:
        cursor = connection.cursor(dictionary=True)
        cursor.execute("SELECT * FROM email_configs WHERE config_name = %s AND is_active = 1", (config_name,))
        config = cursor.fetchone()
        return config
    except mysql.connector.Error as e:
        logger.error(f"Error getting email config: {e}")
        return None
    finally:
        cursor.close()
        connection.close()


def cleanup_old_email_logs():
    """Clean up email logs older than today"""
    connection = get_localdb_connection()
    if not connection:
        return False
    
    try:
        cursor = connection.cursor()
        # Delete emails older than today (keep only today's emails)
        cursor.execute("""
            DELETE FROM email_logs 
            WHERE DATE(sent_at) < CURDATE()
        """)
        deleted_count = cursor.rowcount
        connection.commit()
        
        if deleted_count > 0:
            logger.info(f"Cleaned up {deleted_count} old email logs")
        
        return True
    except mysql.connector.Error as e:
        logger.error(f"Error cleaning up email logs: {e}")
        return False
    finally:
        cursor.close()
        connection.close()


def log_email(config_name, subject, body, from_email, to_email, status, error_message=None, inventory_id=None):
    """Log sent email to database (only keeps today's data)"""
    # First, cleanup old logs to keep only today's data
    cleanup_old_email_logs()
    
    connection = get_localdb_connection()
    if not connection:
        return False
    
    try:
        cursor = connection.cursor()
        cursor.execute("""
            INSERT INTO email_logs 
            (config_name, subject, body, from_email, to_email, status, error_message, related_inventory_id)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        """, (config_name, subject, body, from_email, to_email, status, error_message, inventory_id))
        connection.commit()
        return True
    except mysql.connector.Error as e:
        logger.error(f"Error logging email: {e}")
        return False
    finally:
        cursor.close()
        connection.close()





def send_email_from_config(config_name, to_email, replacements=None, inventory_id=None):
    """Send email using configuration from database"""
    config = get_email_config(config_name)
    if not config:
        error_msg = f"Email configuration '{config_name}' not found"
        logger.error(error_msg)
        return {"success": False, "error": error_msg}
    
    # Replace placeholders in subject and body
    subject = config['subject']
    body = config['body']
    
    if replacements:
        for key, value in replacements.items():
            subject = subject.replace(f"{{{key}}}", str(value))
            body = body.replace(f"{{{key}}}", str(value))
    
    # Send the email
    result = send_email(
        subject=subject,
        body=body,
        to_email=to_email,
        from_email=config['from_email'],
        from_password=config['from_password'],
        smtp_server=config['smtp_server'],
        smtp_port=config['smtp_port']
    )
    
    # Log the email (with daily cleanup)
    status = 'success' if result['success'] else 'failed'
    error_message = result.get('error') if not result['success'] else None
    log_email(config_name, subject, body, config['from_email'], to_email, status, error_message, inventory_id)
    
    return result


def send_email(subject, body, to_email,
               from_email='inventory.system.bnm@bnmparapharm.com',
               from_password='bnmparapharminv',
               smtp_server='mail.bnmparapharm.com',
               smtp_port=465):
    """Send an email using your company SMTP server and ensure it appears in Sent folder."""
    try:
        # Create message container
        msg = MIMEMultipart()
        msg['From'] = from_email
        msg['To'] = to_email
        msg['Subject'] = subject
        msg.attach(MIMEText(body, 'plain'))
        
        # Add a copy to the Sent folder by CC'ing yourself
        msg['Cc'] = from_email

        # Use SSL if port 465, else TLS
        if smtp_port == 465:
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
        
        # Send to both recipient and your own address (for Sent folder)
        server.sendmail(from_email, [to_email, from_email], msg.as_string())
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
            casse ENUM('yes', 'no') DEFAULT NULL COMMENT 'Indicates if this inventory is related to casse',
            PRIMARY KEY (id),
            KEY idx_status (status),
            KEY idx_created_by (created_by),
            KEY idx_created_at (created_at),
            KEY idx_casse (casse)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        """
        
        # Create inventory_items table (add m_attributesetinstance_id INT DEFAULT NULL)
        create_inventory_items_table = """
        CREATE TABLE IF NOT EXISTS inventory_items (
            id INT(11) NOT NULL AUTO_INCREMENT,
            inventory_id INT(11) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            description TEXT NULL,
            quantity INT(11) NOT NULL,
            date DATE DEFAULT NULL,
            lot VARCHAR(100) DEFAULT NULL,
            m_attributesetinstance_id INT DEFAULT NULL,
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
                ADD COLUMN casse ENUM('yes', 'no') DEFAULT NULL COMMENT 'Indicates if this inventory is related to casse',
                ADD KEY idx_casse (casse)
            """
            cursor.execute(alter_casse_query)
            logger.info("Added casse column to inventories table")
        except mysql.connector.Error as e:
            if "Duplicate column name" in str(e):
                logger.info("Casse column already exists")
                # Try to modify existing column to support both 'yes' and 'no'
                try:
                    modify_casse_query = """
                        ALTER TABLE inventories 
                        MODIFY COLUMN casse ENUM('yes', 'no') DEFAULT NULL COMMENT 'Indicates if this inventory is related to casse'
                    """
                    cursor.execute(modify_casse_query)
                    logger.info("Modified casse column to support 'yes' and 'no' values")
                except mysql.connector.Error as modify_error:
                    logger.warning(f"Could not modify casse column: {modify_error}")
            else:
                logger.warning(f"Could not add casse column: {e}")
        
        # Add m_attributesetinstance_id column if it doesn't exist
        try:
            alter_maid_query = """
                ALTER TABLE inventory_items 
                ADD COLUMN m_attributesetinstance_id INT DEFAULT NULL
            """
            cursor.execute(alter_maid_query)
            logger.info("Added m_attributesetinstance_id column to inventory_items table")
        except mysql.connector.Error as e:
            if "Duplicate column name" in str(e):
                logger.info("m_attributesetinstance_id column already exists")
            else:
                logger.warning(f"Could not add m_attributesetinstance_id column: {e}")

        # Add description column if it doesn't exist
        try:
            alter_description_query = """
                ALTER TABLE inventory_items 
                ADD COLUMN description TEXT NULL AFTER product_name
            """
            cursor.execute(alter_description_query)
            logger.info("Added description column to inventory_items table")
        except mysql.connector.Error as e:
            if "Duplicate column name" in str(e):
                logger.info("description column already exists")
            else:
                logger.warning(f"Could not add description column: {e}")

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
    
    cursor = None  # Initialize cursor variable
    try:
        # Debug logging
        logger.info(f"DEBUG: Received data: {data}")
        logger.info(f"DEBUG: Data type: {type(data)}")
        logger.info(f"DEBUG: Data keys: {list(data.keys()) if data else 'None'}")
        
        # Validate input data
        if not data or 'title' not in data or 'items' not in data:
            return {"success": False, "error": "Missing required fields: title and items"}
        
        title = data.get('title', '').strip()
        notes = data.get('notes', '').strip() if data.get('notes') else None
        items = data.get('items', [])
        created_by = data.get('created_by', 'system')
        casse = data.get('casse', None)  # New casse field
        
        # Auto-generate title if empty
        if not title:
            # Generate title based on timestamp and created_by
            timestamp = datetime.now().strftime("%Y-%m-%d %H:%M")
            title = f"Inventory - {timestamp} - {created_by}"
            logger.info(f"Auto-generated title: '{title}'")
        
        if not items:
            return {"success": False, "error": "No items to save"}
        
        cursor = connection.cursor()
        
        # Start transaction
        connection.start_transaction()
        
        # Insert into inventories table with casse field
        try:
            # Try with casse column first
            inventory_query = """
                INSERT INTO inventories (title, notes, status, created_by, created_at, casse) 
                VALUES (%s, %s, 'pending', %s, NOW(), %s)
            """
            cursor.execute(inventory_query, (title, notes, created_by, casse))
        except mysql.connector.Error as e:
            if "Unknown column 'casse'" in str(e):
                # Fallback: Insert without casse column
                inventory_query_fallback = """
                    INSERT INTO inventories (title, notes, status, created_by, created_at) 
                    VALUES (%s, %s, 'pending', %s, NOW())
                """
                cursor.execute(inventory_query_fallback, (title, notes, created_by))
            else:
                raise e
        inventory_id = cursor.lastrowid
        
        # Insert items into inventory_items table
        item_query = """
            INSERT INTO inventory_items 
            (inventory_id, product_name, description, quantity, date, lot, m_attributesetinstance_id, ppa, qty_dispo, type, is_manual_entry) 
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        item_count = 0
        for item in items:
            # Validate item data
            if not item.get('product') or not item.get('qty') or not item.get('type'):
                continue

            product_name = item.get('product', '').strip()
            description = item.get('description', '').strip() if item.get('description') else ''
            description = description if description else None  # Convert empty string to None for NULL in DB
            quantity = int(item.get('qty', 0))
            date = item.get('date') if item.get('date') else None
            lot = item.get('lot', '').strip() if item.get('lot') else None
            m_attributesetinstance_id = item.get('m_attributesetinstance_id', None)
            ppa = float(item.get('ppa', 0.0))
            qty_dispo = int(item.get('qty_dispo', 0))
            item_type = item.get('type', '').strip()
            is_manual_entry = bool(item.get('is_manual_entry', False))

            # Skip invalid items
            if quantity <= 0 or not product_name or item_type not in ['entry', 'sortie']:
                continue

            quantity = -quantity if item_type == "sortie" else quantity
            
            cursor.execute(item_query, (
                inventory_id,
                product_name,
                description,
                quantity,
                date,
                lot,
                m_attributesetinstance_id,
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
        if connection:
            connection.rollback()
        error_msg = str(e)
        logger.error(f"Error saving inventory: {error_msg}")
        return {"success": False, "error": error_msg}
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()


def get_inventory_list(limit=50, offset=0, status=None):
    """Get list of inventories with pagination"""
    connection = get_localdb_connection()
    if not connection:
        return {"success": False, "error": "Database connection failed"}
    
    cursor = None  # Initialize cursor variable
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
        if cursor:
            cursor.close()
        if connection:
            connection.close()


def get_inventory_details(inventory_id):
    """Get detailed inventory information with items"""
    connection = get_localdb_connection()
    if not connection:
        return {"success": False, "error": "Database connection failed"}
    
    cursor = None  # Initialize cursor variable
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
        if cursor:
            cursor.close()
        if connection:
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


# Mail Management Routes

@app.route('/mail/setup', methods=['POST'])
def setup_mail_tables_route():
    """Setup mail management database tables"""
    result = setup_mail_tables()
    return jsonify(result), 200 if result['success'] else 500


@app.route('/mail/configs', methods=['GET'])
def get_mail_configs():
    """Get all email configurations"""
    connection = get_localdb_connection()
    if not connection:
        return jsonify({"success": False, "error": "Database connection failed"}), 500
    
    try:
        cursor = connection.cursor(dictionary=True)
        cursor.execute("SELECT * FROM email_configs ORDER BY config_name")
        configs = cursor.fetchall()
        
        # Convert datetime objects to strings
        for config in configs:
            if config['created_at']:
                config['created_at'] = config['created_at'].strftime('%Y-%m-%d %H:%M:%S')
            if config['updated_at']:
                config['updated_at'] = config['updated_at'].strftime('%Y-%m-%d %H:%M:%S')
        
        return jsonify({"success": True, "configs": configs})
    except mysql.connector.Error as e:
        return jsonify({"success": False, "error": str(e)}), 500
    finally:
        cursor.close()
        connection.close()


@app.route('/mail/configs', methods=['POST'])
def create_mail_config():
    """Create new email configuration"""
    try:
        data = request.get_json()
        required_fields = ['config_name', 'subject', 'body']
        
        if not all(field in data for field in required_fields):
            return jsonify({"success": False, "error": "Missing required fields"}), 400
        
        connection = get_localdb_connection()
        if not connection:
            return jsonify({"success": False, "error": "Database connection failed"}), 500
        
        cursor = connection.cursor()
        cursor.execute("""
            INSERT INTO email_configs 
            (config_name, subject, body, from_email, from_password, smtp_server, smtp_port, is_active)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        """, (
            data['config_name'],
            data['subject'],
            data['body'],
            data.get('from_email', 'inventory.system.bnm@bnmparapharm.com'),
            data.get('from_password', 'bnmparapharminv'),
            data.get('smtp_server', 'mail.bnmparapharm.com'),
            data.get('smtp_port', 465),
            data.get('is_active', True)
        ))
        connection.commit()
        
        return jsonify({"success": True, "message": "Email configuration created successfully"})
    except mysql.connector.Error as e:
        return jsonify({"success": False, "error": str(e)}), 500
    finally:
        cursor.close()
        connection.close()


@app.route('/mail/configs/<int:config_id>', methods=['PUT'])
def update_mail_config(config_id):
    """Update email configuration"""
    try:
        data = request.get_json()
        
        connection = get_localdb_connection()
        if not connection:
            return jsonify({"success": False, "error": "Database connection failed"}), 500
        
        cursor = connection.cursor()
        
        # Build update query dynamically based on provided fields
        update_fields = []
        update_values = []
        
        for field in ['config_name', 'subject', 'body', 'from_email', 'from_password', 'smtp_server', 'smtp_port', 'is_active']:
            if field in data:
                update_fields.append(f"{field} = %s")
                update_values.append(data[field])
        
        if not update_fields:
            return jsonify({"success": False, "error": "No fields to update"}), 400
        
        update_values.append(config_id)
        
        cursor.execute(f"""
            UPDATE email_configs 
            SET {', '.join(update_fields)}
            WHERE id = %s
        """, update_values)
        
        if cursor.rowcount == 0:
            return jsonify({"success": False, "error": "Configuration not found"}), 404
        
        connection.commit()
        return jsonify({"success": True, "message": "Email configuration updated successfully"})
    except mysql.connector.Error as e:
        return jsonify({"success": False, "error": str(e)}), 500
    finally:
        cursor.close()
        connection.close()


@app.route('/mail/contacts', methods=['GET'])
def get_mail_contacts():
    """Get all email contacts"""
    connection = get_localdb_connection()
    if not connection:
        return jsonify({"success": False, "error": "Database connection failed"}), 500
    
    try:
        cursor = connection.cursor(dictionary=True)
        cursor.execute("SELECT * FROM email_contacts WHERE is_active = 1 ORDER BY name")
        contacts = cursor.fetchall()
        
        # Convert datetime objects to strings
        for contact in contacts:
            if contact['created_at']:
                contact['created_at'] = contact['created_at'].strftime('%Y-%m-%d %H:%M:%S')
            if contact['updated_at']:
                contact['updated_at'] = contact['updated_at'].strftime('%Y-%m-%d %H:%M:%S')
        
        return jsonify({"success": True, "contacts": contacts})
    except mysql.connector.Error as e:
        return jsonify({"success": False, "error": str(e)}), 500
    finally:
        cursor.close()
        connection.close()


@app.route('/mail/contacts', methods=['POST'])
def create_mail_contact():
    """Create new email contact"""
    try:
        data = request.get_json()
        required_fields = ['name', 'email']
        
        if not all(field in data for field in required_fields):
            return jsonify({"success": False, "error": "Missing required fields"}), 400
        
        connection = get_localdb_connection()
        if not connection:
            return jsonify({"success": False, "error": "Database connection failed"}), 500
        
        cursor = connection.cursor()
        cursor.execute("""
            INSERT INTO email_contacts 
            (name, email, department, position, is_active)
            VALUES (%s, %s, %s, %s, %s)
        """, (
            data['name'],
            data['email'],
            data.get('department'),
            data.get('position'),
            data.get('is_active', True)
        ))
        connection.commit()
        
        return jsonify({"success": True, "message": "Email contact created successfully"})
    except mysql.connector.Error as e:
        return jsonify({"success": False, "error": str(e)}), 500
    finally:
        cursor.close()
        connection.close()


@app.route('/mail/contacts/<int:contact_id>', methods=['PUT'])
def update_mail_contact(contact_id):
    """Update existing email contact"""
    try:
        data = request.get_json()
        required_fields = ['name', 'email']
        
        if not all(field in data for field in required_fields):
            return jsonify({"success": False, "error": "Missing required fields"}), 400
        
        connection = get_localdb_connection()
        if not connection:
            return jsonify({"success": False, "error": "Database connection failed"}), 500
        
        cursor = connection.cursor()
        cursor.execute("""
            UPDATE email_contacts 
            SET name = %s, email = %s, department = %s, position = %s, is_active = %s, updated_at = NOW()
            WHERE id = %s
        """, (
            data['name'],
            data['email'],
            data.get('department'),
            data.get('position'),
            data.get('is_active', True),
            contact_id
        ))
        
        if cursor.rowcount == 0:
            return jsonify({"success": False, "error": "Contact not found"}), 404
        
        connection.commit()
        
        return jsonify({"success": True, "message": "Email contact updated successfully"})
    except mysql.connector.Error as e:
        return jsonify({"success": False, "error": str(e)}), 500
    finally:
        cursor.close()
        connection.close()


@app.route('/mail/send', methods=['POST'])
def send_mail_from_config():
    """Send email using configuration"""
    try:
        data = request.get_json()
        required_fields = ['config_name', 'to_emails']
        
        if not all(field in data for field in required_fields):
            return jsonify({"success": False, "error": "Missing required fields"}), 400
        
        results = []
        for to_email in data['to_emails']:
            result = send_email_from_config(
                config_name=data['config_name'],
                to_email=to_email,
                replacements=data.get('replacements', {}),
                inventory_id=data.get('inventory_id')
            )
            results.append({"to": to_email, **result})
        
        return jsonify({"success": True, "results": results})
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500


@app.route('/mail/logs', methods=['GET'])
def get_mail_logs():
    """Get today's email logs with pagination"""
    connection = None
    cursor = None
    try:
        limit = int(request.args.get('limit', 50))
        offset = int(request.args.get('offset', 0))
        
        connection = get_localdb_connection()
        if not connection:
            return jsonify({"success": False, "error": "Database connection failed"}), 500
        
        cursor = connection.cursor(dictionary=True)
        
        # Check if email_logs table exists
        cursor.execute("SHOW TABLES LIKE 'email_logs'")
        if not cursor.fetchone():
            return jsonify({"success": False, "error": "Email logs table not found. Please setup mail tables first."}), 404
        
        # Only get today's emails
        cursor.execute("""
            SELECT * FROM email_logs 
            WHERE DATE(sent_at) = CURDATE()
            ORDER BY sent_at DESC 
            LIMIT %s OFFSET %s
        """, (limit, offset))
        logs = cursor.fetchall()
        
        # Convert datetime objects to strings
        for log in logs:
            if log['sent_at']:
                log['sent_at'] = log['sent_at'].strftime('%Y-%m-%d %H:%M:%S')
        
        # Get total count for today
        cursor.execute("SELECT COUNT(*) as total FROM email_logs WHERE DATE(sent_at) = CURDATE()")
        total = cursor.fetchone()['total']
        
        return jsonify({"success": True, "logs": logs, "total": total})
    except mysql.connector.Error as e:
        return jsonify({"success": False, "error": f"Database error: {str(e)}"}), 500
    except Exception as e:
        return jsonify({"success": False, "error": f"Server error: {str(e)}"}), 500
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()


@app.route('/mail/cleanup', methods=['POST'])
def cleanup_email_logs_route():
    """Manually cleanup old email logs"""
    try:
        result = cleanup_old_email_logs()
        if result:
            return jsonify({"success": True, "message": "Old email logs cleaned up successfully"})
        else:
            return jsonify({"success": False, "error": "Failed to cleanup old logs"}), 500
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500


@app.route('/inventory/save', methods=['POST'])
def save_inventory_route():
    """Save inventory data"""
    try:
        data = request.get_json()
        if not data:
            return jsonify({"success": False, "error": "No data provided"}), 400

        result = save_inventory_data(data)

        # Only send emails if inventory save was successful
        if result['success']:
            # Prepare inventory details for email body
            inv_id = result.get('inventory_id')
            total_items = result.get('total_items')
            title = data.get('title', '')
            notes = data.get('notes', '')
            casse = data.get('casse', '')
            items = data.get('items', [])
            
            # Build email content
            inventory_details = f"""Inventory ID: {result['inventory_id']}
Title: {data.get('title', 'N/A')}
Notes: {data.get('notes', 'N/A')}
Casse: {data.get('casse', 'N/A')}
Total Items: {result['total_items']}

Items Details:
"""
            for item in data.get('items', []):
                inventory_details += f"- {item.get('product', 'N/A')} | Qty: {item.get('qty', 'N/A')} | Type: {item.get('type', 'N/A')}\n"
            
            replacements = {
                'inventory_details': inventory_details
            }

            # Get recipients from JSON file for inventory notifications
            default_recipients = get_recipients_from_json('inventory_save')
            
            # Fallback to default recipients if JSON is empty
            if not default_recipients:
                default_recipients = ["benmalek.abderrahmane@bnmparapharm.com", "mahroug.nazim@bnmparapharm.com"]

            email_results = []
            for to_email in default_recipients:
                email_result = send_email_from_config(
                    config_name='inventory_created',
                    to_email=to_email,
                    replacements=replacements,
                    inventory_id=result.get('inventory_id')
                )
                email_results.append({"to": to_email, **email_result})
            
            logger.info(f"Email send results: {email_results}")

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


@app.route('/inventory_product_search', methods=['GET'])
def inventory_product_search():
    """Search for inventories containing a specific product, optionally filtered by date"""
    try:
        product_name = request.args.get('product_name')
        date_filter = request.args.get('date')  # Optional date filter
        
        if not product_name:
            return jsonify({"success": False, "error": "product_name is required"}), 400
        
        connection = get_localdb_connection()
        if not connection:
            return jsonify({"success": False, "error": "Database connection failed"}), 500
        
        try:
            cursor = connection.cursor(dictionary=True)
            
            # Build query to find inventories with the product
            query = """
                SELECT DISTINCT i.id, i.title, i.status, i.created_at, ii.product_name, ii.date, ii.quantity
                FROM inventories i
                JOIN inventory_items ii ON i.id = ii.inventory_id
                WHERE ii.product_name LIKE %s
            """
            params = [f"%{product_name}%"]
            
            if date_filter:
                query += " AND ii.date = %s"
                params.append(date_filter)
            
            query += " ORDER BY i.created_at DESC"
            
            cursor.execute(query, params)
            results = cursor.fetchall()
            
            return jsonify({
                "success": True,
                "inventories": results,
                "count": len(results)
            }), 200
            
        except mysql.connector.Error as e:
            logger.error(f"Error searching inventory products: {e}")
            return jsonify({"success": False, "error": str(e)}), 500
        finally:
            cursor.close()
            connection.close()
            
    except Exception as e:
        logger.error(f"Error in inventory_product_search: {e}")
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



@app.route('/api/fournisseurs', methods=['GET'])
def get_fournisseurs():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            cursor.execute("""
                SELECT DISTINCT name as fournisseurs
                from c_bpartner
                WHERE AD_Client_ID = 1000000
                AND ISACTIVE = 'Y'
                AND isvendor = 'Y'
            """)
            fournisseurs = [row[0] for row in cursor.fetchall()]
            return jsonify({"success": True, "fournisseurs": fournisseurs})
    except Exception as e:
        logger.error(f"Error fetching fournisseurs: {e}")
        return jsonify({"success": False, "error": str(e)}), 500

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

 

@app.route('/inventory-products-updated', methods=['GET'])
def inventory_products_updated():
    try:
        product_id = request.args.get("product_id", None)
        category = request.args.get("category", "all")  # Default to "all" if no category provided
        show_all = request.args.get("show_all", "false").lower() == "true"  # New parameter
        print("Show all:", show_all)
        
        if not product_id:
            return jsonify({"error": "Product ID is required"}), 400

        data = fetch_inventory_products_data_updated(product_id, category, show_all)
        return jsonify(data)

    except Exception as e:
        logger.error(f"Error f+etching updated inventory products: {e}")
        return jsonify({"error": "Failed to fetch updated inventory products"}), 500
    


def fetch_inventory_products_data_updated(product_id, category="all", show_all=False):
    """
    Fetch inventory product data where:
    - ANY of these is non-zero: QTY_ONHAND, QTY_RESERVED, QTY_DISPO, QTYORDERED
    - AND GUARANTEEDATE is NOT NULL
    If show_all=True, ignore the QTY conditions and show all lots.
    """
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            
            # Define locator IDs for different categories
            locator_groups = {
                "all": "(1001135, 1000614, 1001128, 1001136, 1001020, 1000314, 1000210, 1000211, 1000109, 1000209, 1000213, 1000214, 1000414, 1000817, 1001129)",
                "preparation": "(1001135, 1000614, 1001128, 1001136, 1001020)",
                "tempo": "(1000314, 1000210, 1000211, 1000109, 1000209, 1000213, 1000214, 1000414, 1000817, 1001129)"
            }
            locator_list = locator_groups.get(category, locator_groups["all"])
            logger.info(f"Fetching inventory data for product_id: {product_id}, category: {category}, show_all: {show_all}")
            qty_condition = """
                AND (
                    mst.qtyonhand != 0 
                    OR mst.QTYRESERVED != 0 
                    OR (mst.qtyonhand - mst.QTYRESERVED) != 0
                    OR mst.QTYORDERED != 0
                )
            """ if not show_all else ""
            query = f"""
            SELECT
                p.name AS PRODUCT,
                (SELECT lot FROM m_attributesetinstance WHERE m_attributesetinstance_id = mst.m_attributesetinstance_id) AS LOT,
                (SELECT description FROM m_attributesetinstance WHERE m_attributesetinstance_id = mst.m_attributesetinstance_id) AS DESCRIPTION,
                (SELECT valuenumber FROM m_attributeinstance WHERE m_attributesetinstance_id = mst.m_attributesetinstance_id AND m_attribute_id = 1000503) AS PPA,
                (mst.qtyonhand - mst.QTYRESERVED) AS QTY_DISPO,
                mst.m_attributesetinstance_id as M_ATTRIBUTESSETINSTANCE_ID,
                mst.qtyonhand AS QTY_ONHAND,
                mst.QTYRESERVED AS QTY_RESERVED,
                mst.QTYORDERED AS QTYORDERED,
                mats.guaranteedate AS GUARANTEEDATE,
                ROUND(
                    (
                        (
                            (SELECT valuenumber FROM m_attributeinstance WHERE m_attributesetinstance_id = mst.m_attributesetinstance_id AND m_attribute_id = 1000501)
                            - 
                            ((SELECT valuenumber FROM m_attributeinstance WHERE m_attributesetinstance_id = mst.m_attributesetinstance_id AND m_attribute_id = 1000501) 
                             * 
                             (SELECT NVL(valuenumber, 0) FROM m_attributeinstance WHERE m_attributesetinstance_id = mst.m_attributesetinstance_id AND m_attribute_id = 1001009) / 100)
                        ) 
                        / 
                        (1 + (SELECT NVL(valuenumber, 0) FROM m_attributeinstance WHERE m_attributesetinstance_id = mst.m_attributesetinstance_id AND m_attribute_id = 1000808) / 100)
                    ), 2
                ) AS P_REVIENT,
                (
                    SELECT mt.movementtype 
                    FROM m_transaction mt 
                    WHERE mt.m_product_id = mst.m_product_id 
                    AND mt.m_attributesetinstance_id = mst.m_attributesetinstance_id 
                    AND mt.m_locator_id = mst.m_locator_id
                    ORDER BY mt.created DESC 
                    FETCH FIRST 1 ROW ONLY
                ) AS LTS
            FROM
                m_product p
                INNER JOIN m_storage mst ON p.m_product_id = mst.m_product_id
                INNER JOIN m_attributesetinstance mats ON mst.m_attributesetinstance_id = mats.m_attributesetinstance_id
            WHERE
                p.m_product_id = :product_id
                AND mst.m_locator_id IN {locator_list}
                {qty_condition}
                AND mats.m_attributesetinstance_id in (select m_attributesetinstance_id from m_attributeinstance where mats.m_attributesetinstance_id=m_attributesetinstance_id and m_attribute_id = 1000502 and valuenumber != 0) 
                AND (
                    mst.m_locator_id = 1000210
                    OR mats.guaranteedate > SYSDATE - 30
                )  -- Exclude expired lots (assuming 30 days grace period)
                AND mats.guaranteedate IS NOT NULL  -- NEW: Exclude NULL guarantee dates
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



@app.route('/send_saisie_mail', methods=['GET'])
def send_saisie_mail_route():
    """Send inventory notification to operations team"""
    # Get recipients from JSON file
    recipients = get_recipients_from_json('send_saisie_mail')
    
    # Fallback to default recipients if JSON is empty
    if not recipients:
        recipients = [
            "guend.hamza@bnmparapharm.com",
            "seifeddine.nemdili@bnmparapharm.com",
            "belhanachi.abdenour@bnmparapharm.com"
        ]
    
    results = []
    for to_email in recipients:
        result = send_email_from_config(
            config_name='inventory_saisie_notification',
            to_email=to_email
        )
        logger.info(f"Saisie mail to {to_email}: {result}")
        results.append({"to": to_email, **result})
    
    return jsonify({"results": results}), 200 if all(r["success"] for r in results) else 500

        

# Route to send an info email to multiple recipients
@app.route('/send_info_mail', methods=['GET'])
def send_info_mail_route():
    """Send info notification to management team"""
    # Get recipients from JSON file
    recipients = get_recipients_from_json('send_info_mail')
    
    # Fallback to default recipients if JSON is empty
    if not recipients:
        recipients = [
            "maamri.yasser@bnmparapharm.com",
            "mahroug.nazim@bnmparapharm.com",
            "benmalek.abderrahmane@bnmparapharm.com"
        ]
    
    results = []
    for to_email in recipients:
        result = send_email_from_config(
            config_name='inventory_info_notification',
            to_email=to_email
        )
        logger.info(f"Info mail to {to_email}: {result}")
        results.append({"to": to_email, **result})
    
    return jsonify({"results": results}), 200 if all(r["success"] for r in results) else 500



@app.route('/inventory/insert_inventory', methods=['POST', 'OPTIONS'])
def insert_inventory():
    if request.method == 'OPTIONS':
        return jsonify({'status': 'ok'}), 200
    
    try:
        
        # Debug the raw request data
        print("Raw request data:", request.data)
        
        data = request.get_json()
        print("Parsed JSON data:", data)
        
        # Handle both single item and items array
        if 'items' in data:
            # New format with items array
            items = data['items']
        else:
            # Old format - single item, wrap in array
            items = [data]
        print("Items extracted:", items)
        
        if not items:
            print("No items found in request")
            return jsonify({"error": "No items provided"}), 400

        if not items:
            return jsonify({"error": "No items provided"}), 400

        # Validate all items have required fields
        for item in items:
            if not all(key in item for key in ['product_name', 'quantity']):
                return jsonify({"error": "Missing required fields in one or more items"}), 400

        
        print("------------------------------------------.", item['attributes'])
        print("Attributes for item:", item['product_name'])
        

        #connection_loc = get_localdb_connection()
        #cursor_loc = connection_loc.cursor()

        # Get connection from pool
        connection = DB_POOL.acquire()
        cursor = connection.cursor()

        # Begin transaction
        connection.autocommit = False

        # 1. Insert inventory header (only once per request)
        cursor.execute("""
            INSERT INTO m_inventory (
                m_inventory_id, ad_client_id, ad_org_id, isactive, 
                created, createdby, updated, updatedby,
                documentno, description, m_warehouse_id, movementdate,
                posted, PROCESSED, PROCESSING, updateqty, generatelist,
                m_perpetualinv_id, ad_orgtrx_id, c_project_id, c_campaign_id,
                c_activity_id, user1_id, user2_id, isapproved,
                docstatus, docaction, approvalamt, c_doctype_id,
                c_bpartner_id, isarchived, xx_inversercompta, dateacct, barcodescanner
            ) SELECT 
                (select NVL(MAX(m_inventory_id),0) + 1 from m_inventory where ad_client_id = 1000000),
                1000000, 1000000, 'Y', 
                sysdate, 100, sysdate, 100,
                (SELECT Prefix || CurrentNext || Suffix 
                 FROM AD_Sequence s
                 JOIN C_DocType dt ON s.AD_Sequence_ID = dt.DocNoSequence_ID
                 WHERE dt.C_DocType_ID = 1000021),
                :description,
                1000000, sysdate, 
                'N', 'N', 'N', 'N', 'N', 
                null, null, null, null, 
                null, null, null, 'N', 
                'DR', 'CO', 0.00, 1000021,
                null, 'N', 'N', null, null
            FROM dual
        """, {
                    'description': data.get('title')
                })

        # Update document sequence
        cursor.execute("""
            UPDATE AD_Sequence 
            SET CurrentNext = CurrentNext + 1
            WHERE AD_Sequence_ID = (
                SELECT DocNoSequence_ID 
                FROM C_DocType 
                WHERE C_DocType_ID = 1000021
                AND AD_Client_ID = 1000000
            )
        """)

        # Get the newly created inventory ID
        cursor.execute("SELECT MAX(m_inventory_id) FROM m_inventory WHERE ad_client_id = 1000000")
        new_inventory_id = cursor.fetchone()[0]

        # Attribute mapping
        attribute_map = {
            "Prix Achat": 1000501,
            "Colisage": 1000507,
            "PPA": 1000503,
            "Prix Vente": 1000502,
            "Prix Revient": 1000504,
            "Fournisseur": 1000508,
            "Bonus": 1000808,
            "Bonus Vente": 1000908,
            "Remise Supp": 1001009,
            "Remise Vente": 1001408
        }

        line_number = 10  # Starting line number
        inserted_items = []
        
        for item in items:
            # Get product ID for each item
            cursor.execute("""
                SELECT M_Product_ID 
                FROM M_Product 
                WHERE Name like '%' || :name || '%' 
                AND AD_Client_ID = 1000000
                AND AD_Org_ID = 1000000
                AND IsActive='Y'
            """, {'name': item['product_name']})
            product_row = cursor.fetchone()
            print(f"Product row for {item['product_name']}: {product_row}")
            if not product_row:
                raise Exception(f"Product not found: {item['product_name']}")
            
            product_id = product_row[0]
            m_attributesetinstance_id = item.get('m_attributesetinstance_id')
            
            # Handle attributes if needed
            if not m_attributesetinstance_id and ('lot' in item or 'date_expiration' in item or 'attributes' in item):
                guaranteedate = None
                if item.get('date_expiration'):
                    try:
                        # Handle both dd/mm/yy and yyyy-mm-dd formats
                        date_str = item['date_expiration']
                        print(f"Parsing date_expiration: {date_str}")
                        if '/' in date_str:
                            guaranteedate = datetime.strptime(date_str, '%d/%m/%y')
                        elif '-' in date_str:
                            guaranteedate = datetime.strptime(date_str, '%Y-%m-%d')
                    except ValueError as e:
                        logger.error(f"Invalid date format '{date_str}': {str(e)}")
                        raise Exception(f"Invalid date format. Use DD/MM/YY or YYYY-MM-DD")
                
                cursor.execute("""
                    INSERT INTO M_ATTRIBUTESETINSTANCE (
                        m_attributesetinstance_id, ad_client_id, ad_org_id, isactive, 
                        created, createdby, updated, updatedby,
                        m_attributeset_id, serno, lot, guaranteedate, 
                        description, m_lot_id, motif_activ_instance
                    ) VALUES (
                        (SELECT NVL(MAX(m_attributesetinstance_id),0) + 1 
                         FROM M_ATTRIBUTESETINSTANCE 
                         WHERE ad_client_id = 1000000),
                        1000000, 1000000, 'Y', 
                        SYSDATE, 100, SYSDATE, 100,
                        1000405, NULL, :lot, :guaranteedate, 
                        NULL, NULL, NULL
                    )
                """, {
                    'lot': item.get('lot'),
                    'guaranteedate': guaranteedate
                })

                cursor.execute("SELECT MAX(m_attributesetinstance_id) FROM M_ATTRIBUTESETINSTANCE WHERE ad_client_id = 1000000")
                m_attributesetinstance_id = cursor.fetchone()[0]

                if 'attributes' in item and isinstance(item['attributes'], dict):
                    for attr_name, attr_value in item['attributes'].items():
                        if attr_name in attribute_map:
                            # Get the attribute ID from your map
                            attr_id = attribute_map[attr_name]

                            # Determine if this is a special attribute (Colisage or Fournisseur)
                            is_special_attr = attr_id in [attribute_map["Fournisseur"]]

                            try:
                                cursor.execute("""
                                    INSERT INTO M_ATTRIBUTEINSTANCE (
                                        m_attributesetinstance_id, m_attribute_id, ad_client_id, ad_org_id, 
                                        isactive, created, createdby, updated, updatedby, 
                                        m_attributevalue_id, value, valuenumber, valuedate
                                    ) VALUES (
                                        :asi_id, :attr_id, 1000000, 1000000, 'Y', 
                                        SYSDATE, 100, SYSDATE, 100,
                                        case when :attr_id = 1000506 then 1000405 else NULL end , 
                                        :value, 
                                        CASE WHEN :is_special = 1 THEN (select c_bpartner_id from c_bpartner where name like :value and isactive = 'Y' and ad_client_id = 1000000 and isvendor = 'Y') ELSE :num_value END, 
                                        NULL
                                    )
                                """, {
                                    'asi_id': m_attributesetinstance_id,
                                    'attr_id': attr_id,
                                    'is_special': 1 if is_special_attr else 0,
                                    'value': str(attr_value),
                                    'num_value': float(attr_value) if not is_special_attr else None
                                })
                            except Exception as e:
                                logger.error(f"Error inserting attribute {attr_name}: {str(e)}")
                                raise Exception(f"Failed to insert attribute {attr_name}: {str(e)}")

            # Insert inventory line
            cursor.execute("""
                INSERT INTO m_inventoryline (
                    m_inventoryline_id, ad_client_id, ad_org_id, isactive, 
                    created, createdby, updated, updatedby, 
                    m_inventory_id, m_locator_id, m_product_id, line, 
                    qtybook, qtycount, description, m_attributesetinstance_id, 
                    c_charge_id, inventorytype, processed, qtyinternaluse, isinternaluse,
                    ad_orgtrx_id, c_activity_id, qtyentered, c_uom_id, 
                    priceactual, a_asset_id, zchargechange, xx_inverserecriturecompta
                ) VALUES (
                    (select NVL(MAX(m_inventoryline_id),0) + 1 from m_inventoryline where ad_client_id = 1000000),
                    1000000, 1000000, 'Y', 
                    sysdate, 100, sysdate, 100,
                    :new_inventory_id, 1000614, :product_id, :line_number, 
                    :qty_dispo,:qty_dispo + :quantity, :description, :m_attributesetinstance_id, 
                    1000028, 'C', 'N', 0, 
                    'N', null, null,:qty_dispo + :quantity, 100,
                    null, null, 'N', 'N'
                )
            """, {
                'new_inventory_id': new_inventory_id,
                'product_id': product_id,
                'line_number': line_number,
                'quantity': item['quantity'],
                'qty_dispo': item.get('qty_dispo', 0),
                'm_attributesetinstance_id': m_attributesetinstance_id,
                'description': item.get('description', 'Adjustment for product')
            })
            
            line_number += 10
            inserted_items.append({
                'product_id': product_id,
                'product_name': item['product_name'],
                'quantity': item['quantity']
            })

        # Check if we have any inventory lines before inserting transactions
        cursor.execute("SELECT COUNT(*) FROM m_inventoryline WHERE m_inventory_id = :inv_id", 
                    {'inv_id': new_inventory_id})
        line_count = cursor.fetchone()[0]

        if line_count > 0:
        # INSERT INTO M_Transaction after all inventory lines are created
            cursor.execute("""
                INSERT INTO M_Transaction (
                    m_transaction_id, ad_client_id, ad_org_id, isactive, 
                    created, createdby, updated, updatedby, 
                    movementtype, m_locator_id, m_product_id, 
                    movementdate, movementqty, m_inventoryline_id, 
                    m_inoutline_id, m_productionline_id, c_projectissue_id,
                    m_attributesetinstance_id, m_warehousetask_id, 
                    m_workordertransactionline_id, z_production_inline_id, 
                    z_production_outline_id
                )
                SELECT 
                    (SELECT NVL(MAX(m_transaction_id),0) FROM m_transaction WHERE ad_client_id = 1000000) 
                    + ROW_NUMBER() OVER (ORDER BY ml.m_inventoryline_id),
                    1000000, -- AD_Client_ID
                    1000000, -- AD_Org_ID
                    'Y',
                    sysdate, 100, sysdate, 100,
                    'I+', -- MovementType
                    ml.m_locator_id, -- M_Locator_ID
                    ml.m_product_id, -- M_Product_ID
                    sysdate, -- MovementDate
                    ml.qtycount - ml.qtybook, -- MovementQty
                    ml.m_inventoryline_id, -- M_InventoryLine_ID
                    null, -- M_InOutLine_ID     
                    null, -- M_ProductionLine_ID
                    null, -- C_ProjectIssue_ID
                    ml.m_attributesetinstance_id, -- M_AttributeSetInstance_ID
                    null, -- M_WarehouseTask_ID
                    null, -- M_WorkOrderTransactionLine_ID
                    null, -- Z_Production_InLine_ID
                    null -- Z_Production_OutLine_ID
                FROM m_inventoryline ml
                JOIN m_inventory i ON ml.m_inventory_id = i.m_inventory_id
                WHERE i.m_inventory_id = :new_inventory_id
            """, {'new_inventory_id': new_inventory_id})
        else:
            logger.warning("No inventory lines found for transaction creation")
        

        # 7. Insert inventory line MA
        # 5. Get the new inventory line ID
        cursor.execute("SELECT MAX(m_inventoryline_id) FROM m_inventoryline where ad_client_id = 1000000")
        inventoryline_id = cursor.fetchone()[0]

        # 6. Handle M_INVENTORYLINEMA carefully to avoid duplicates
        if m_attributesetinstance_id:
            # First check if the record already exists
            cursor.execute("""
                SELECT COUNT(*) FROM M_INVENTORYLINEMA
                WHERE m_inventoryline_id = :line_id
                AND m_attributesetinstance_id = :asi_id
            """, 
                line_id =  inventoryline_id,
                asi_id = m_attributesetinstance_id
            )
            exists = cursor.fetchone()[0] > 0
            
            if not exists:
                cursor.execute("""
                    INSERT INTO M_InventoryLineMA (
                m_inventoryline_id, m_attributesetinstance_id,
                ad_client_id, ad_org_id, isactive, created,
                createdby, updated, updatedby, movementqty
                )
                SELECT 
                    il.m_inventoryline_id,
                    ms.m_attributesetinstance_id,
                    il.ad_client_id,
                    il.ad_org_id,
                    'Y',
                    il.created,
                    il.createdby,
                    il.updated,
                    il.updatedby,
                    ms.qtyonhand
                FROM m_inventoryline il
                JOIN m_inventory i ON il.m_inventory_id = i.m_inventory_id
                JOIN m_storage ms ON (il.m_product_id = ms.m_product_id)
                WHERE i.m_inventory_id = :new_inventory_id
                AND il.m_attributesetinstance_id IS NOT NULL
                AND i.ad_client_id = 1000000
                AND ms.qtyonhand > 0
                AND ms.m_locator_id = 1000614
                """,{
                        'new_inventory_id': new_inventory_id
                    })

        # 8. Update inventory status
        cursor.execute(f"""
            UPDATE m_inventory 
            SET processed = 'Y', isapproved = 'Y', docstatus = 'CO', docaction = 'CL' 
            WHERE m_inventory_id = :new_inventory_id
        """, new_inventory_id=new_inventory_id)

        # 9. Update inventory line status
        cursor.execute(f"""
            UPDATE m_inventoryline 
            SET processed = 'Y' 
            WHERE m_inventory_id = :new_inventory_id
        """, new_inventory_id=new_inventory_id)

        # 10. Update storage
        cursor.execute(f"""
    MERGE INTO m_storage s
    USING (
        SELECT 
            il.m_product_id,
            il.m_attributesetinstance_id,
            il.qtycount
        FROM m_inventoryline il
        WHERE il.m_inventory_id = :new_inventory_id
    ) il
    ON (
        s.m_product_id = il.m_product_id
        AND s.m_locator_id = 1000614
        AND (s.m_attributesetinstance_id = il.m_attributesetinstance_id
             OR (s.m_attributesetinstance_id IS NULL AND il.m_attributesetinstance_id IS NULL))
    )
    WHEN MATCHED THEN
        UPDATE SET s.qtyonhand = il.qtycount
    WHEN NOT MATCHED THEN
        INSERT (
            m_product_id, m_locator_id, ad_client_id, ad_org_id, isactive, 
            created, createdby, updated, updatedby, qtyonhand,
            qtyreserved, qtyordered, datelastinventory, m_attributesetinstance_id, 
            qtyallocated, qtydedicated, qtyexpected
        )
        VALUES (
            il.m_product_id, 1000614, 1000000, 1000000, 'Y',
            SYSDATE, 100, SYSDATE, 100, il.qtycount,
            0, 0, SYSDATE, il.m_attributesetinstance_id,
            0, 0, 0
        )
""", new_inventory_id=new_inventory_id)
        
       
        # Commit transaction
        connection.commit()

        return jsonify({
            "success": True,
            "message": "Inventory inserted successfully",
            "inventory_id": new_inventory_id,
            "product_id": product_id
        })

    except Exception as e:
        # Rollback in case of error
        if 'connection' in locals():
            connection.rollback()
        logger.error(f"Error inserting inventory: {str(e)}")
        return jsonify({"error": str(e)}), 500


@app.route('/mail/init_data', methods=['POST'])
def init_mail_data():
    """Initialize default email configurations and contacts"""
    connection = get_localdb_connection()
    if not connection:
        return jsonify({"success": False, "error": "Database connection failed"}), 500
    
    try:
        cursor = connection.cursor()
        
        # Insert default email configurations
        configs = [
            ('inventory_created', 'INVENTORY CREATED', 
             'Dear Team,\n\nA new inventory has been created and requires your attention.\n\nInventory Details:\n{inventory_details}\n\nPlease proceed with the inventory process.\n\nBest regards,\nBNM Inventory System'),
            ('inventory_saisie_notification', 'Inventory system notification: Please do the inventory and mark it as done',
             'Dear Team,\n\nThe inventory is being created. Please proceed to do the inventory as soon as possible, and once completed, mark it as done in the system.\n\nGood job!\n\nBest regards,\nBNM System'),
            ('inventory_info_notification', 'INFO: Inventory system notification',
             'Dear Team,\n\nDO THE INV.\n\nPlease proceed to do the inventory as soon as possible,\n\nBest regards,\nBNM System')
        ]
        
        for config_name, subject, body in configs:
            cursor.execute("""
                INSERT INTO email_configs (config_name, subject, body) 
                VALUES (%s, %s, %s)
                ON DUPLICATE KEY UPDATE subject = VALUES(subject), body = VALUES(body)
            """, (config_name, subject, body))
        
        # Insert default email contacts
        contacts = [
            ('Abderrahmane Benmalek', 'benmalek.abderrahmane@bnmparapharm.com', 'INFO', 'Sudo'),
            ('Nazim Mahroug', 'mahroug.nazim@bnmparapharm.com', 'INFO', 'Sudo'),
            ('Hamza Guend', 'guend.hamza@bnmparapharm.com', 'ACHAT', 'Saisie'),
            ('Seifeddine Nemdili', 'seifeddine.nemdili@bnmparapharm.com', 'ACHAT', 'Saisie'),
            ('Abdenour Belhanachi', 'belhanachi.abdenour@bnmparapharm.com', 'ACHAT', 'Casse'),
            ('Yasser Maamri', 'maamri.yasser@bnmparapharm.com', 'INFO', 'Sudo'),
            ('Bedjghit Hichem', 'bedjghit.hichem@bnmparapharm.com', 'Admin', 'Admin'),
            ('Guedjali Mohamed', 'guedjali.mohamed@bnmparapharm.com', 'DOP', 'Admin')
        ]
        
        for name, email, department, position in contacts:
            cursor.execute("""
                INSERT INTO email_contacts (name, email, department, position) 
                VALUES (%s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE name = VALUES(name), department = VALUES(department), position = VALUES(position)
            """, (name, email, department, position))
        
        connection.commit()
        return jsonify({"success": True, "message": "Default mail data initialized successfully"})
    except mysql.connector.Error as e:
        connection.rollback()
        return jsonify({"success": False, "error": str(e)}), 500
    finally:
        cursor.close()
        connection.close()



# JSON Recipients Management API Endpoints
@app.route('/mail/recipients', methods=['GET'])
def get_mail_recipients():
    """Get all mail recipients configuration"""
    try:
        recipients = get_all_recipients()
        return jsonify({"success": True, "recipients": recipients})
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500

@app.route('/mail/recipients/<route_name>', methods=['GET'])
def get_route_recipients(route_name):
    """Get recipients for a specific route"""
    try:
        recipients = get_recipients_from_json(route_name)
        return jsonify({"success": True, "recipients": recipients})
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500

@app.route('/mail/recipients/<route_name>', methods=['POST'])
def save_route_recipients(route_name):
    """Save recipients for a specific route"""
    try:
        data = request.get_json()
        if not data or 'recipients' not in data:
            return jsonify({"success": False, "error": "Recipients data required"}), 400
        
        recipients = data['recipients']
        if not isinstance(recipients, list):
            return jsonify({"success": False, "error": "Recipients must be a list"}), 400
        
        success = save_recipients_to_json(route_name, recipients)
        if success:
            return jsonify({"success": True, "message": f"Recipients saved for {route_name}"})
        else:
            return jsonify({"success": False, "error": "Failed to save recipients"}), 500
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500

@app.route('/mail/recipients/all', methods=['POST'])
def save_all_recipients():
    """Save all recipients configuration"""
    try:
        data = request.get_json()
        if not data:
            return jsonify({"success": False, "error": "Recipients data required"}), 400
        
        # Validate that all values are lists
        for route_name, recipients in data.items():
            if not isinstance(recipients, list):
                return jsonify({"success": False, "error": f"Recipients for {route_name} must be a list"}), 400
        
        # Save to JSON file
        with open(RECIPIENTS_FILE, 'w') as f:
            json.dump(data, f, indent=4)
        
        return jsonify({"success": True, "message": "All recipients configuration saved"})
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500





if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5003)