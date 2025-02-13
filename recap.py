import oracledb
from flask import Flask, jsonify, request
from flask_cors import CORS  # Import CORS
import logging
from flask import Flask, request, send_file
import pandas as pd
from io import BytesIO
from openpyxl import Workbook
from openpyxl.styles import PatternFill, Font
from openpyxl.worksheet.table import Table, TableStyleInfo
from datetime import datetime
from openpyxl import Workbook
from openpyxl.styles import PatternFill, Font
from openpyxl.worksheet.table import Table, TableStyleInfo

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

# Fetch data from Oracle DB

def fetch_rcap_data():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
           SELECT 
    xf.MOVEMENTDATE,  -- Include the date so it can be filtered later
    SUM(xf.TOTALLINE) AS CHIFFRE, 
    SUM(xf.qtyentered) AS QTY,
    SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION) AS MARGE,
    CASE 
        WHEN SUM(xf.CONSOMATION) < 0 
        THEN ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / (SUM(xf.CONSOMATION) * -1))
        ELSE (SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / NULLIF(SUM(xf.CONSOMATION), 0) -- Avoid division by zero
    END AS POURCENTAGE
FROM xx_ca_fournisseur xf
WHERE 
    xf.AD_Org_ID = 1000000
    AND xf.DOCSTATUS != 'RE'
GROUP BY xf.MOVEMENTDATE  -- Group by date so filtering is possible later in the script
ORDER BY xf.MOVEMENTDATE

            """
            cursor.execute(query)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            data = [dict(zip(columns, row)) for row in rows]
            return data
    except Exception as e:
        logger.error(f"Error fetching bonus data: {e}")
        return {"error": "An error occurred while fetching bonus data."}





@app.route('/fetch-t-recap', methods=['GET'])
def fetch_t_recap():
    if not fetch_rcap_data():
        return jsonify({"error": "Failed to connect to the database."}), 500
    data = fetch_rcap_data()
    return jsonify(data)


if __name__ == "__main__":
    app.run(debug=True, port=5003)