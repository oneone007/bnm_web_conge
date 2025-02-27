
import oracledb
from flask import Flask, jsonify, request, send_file
from flask_cors import CORS
import logging
import pandas as pd
from io import BytesIO
from openpyxl import Workbook
from openpyxl.styles import PatternFill, Font
from openpyxl.worksheet.table import Table, TableStyleInfo
from datetime import datetime

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


# Test database connection
def test_db_connection():
    try:
        with DB_POOL.acquire() as connection:
            logger.info("Database connection successful.")
        return True
    except Exception as e:
        logger.error(f"Database connection failed: {str(e)}")
        return False
    






def fetch_rotation_product_data():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
            select name from M_PRODUCT
WHERE AD_Client_ID = 1000000
AND AD_Org_ID = 1000000
  AND ISACTIVE = 'Y'
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

def fetch_historique_rotation(start_date, end_date, product):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
                WITH Latest_Purchase AS (
                    SELECT 
                        cl.M_Product_ID,
                        SUM(cl.qtyentered) AS last_purchase_qty,
                        c.dateinvoiced,
                        ROW_NUMBER() OVER (PARTITION BY cl.M_Product_ID ORDER BY c.dateinvoiced DESC) AS rn
                    FROM 
                        C_InvoiceLine cl
                    JOIN C_Invoice c ON c.C_Invoice_id = cl.C_Invoice_id
                    JOIN M_INOUTLINE ml ON ml.M_INOUTLINE_id = cl.M_INOUTLINE_ID
                    WHERE 
                        c.dateinvoiced BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                                              AND TO_DATE(:end_date, 'YYYY-MM-DD')
                        AND c.AD_Client_ID = 1000000
                        AND c.AD_Org_ID = 1000000
                        AND c.ISSOTRX = 'N'
                        AND c.DOCSTATUS in ('CO','CL')
                        AND ml.M_Locator_ID != 1001020
                    GROUP BY 
                        cl.M_Product_ID, c.dateinvoiced
                ),
                Filtered_Latest_Purchase AS (
                    SELECT 
                        M_Product_ID,
                        last_purchase_qty,
                        dateinvoiced
                    FROM 
                        Latest_Purchase
                    WHERE 
                        rn = 1
                ),
                On_Hand_Quantity AS (
                    SELECT  
                        m.M_Product_ID as midp,
                        m.name AS product_name,
                        SUM(s.QTYONHAND) - SUM(s.QTYRESERVED) AS QTYONHAND
                    FROM 
                        m_product m
                    JOIN m_storage s ON s.M_PRODUCT_ID = m.M_PRODUCT_ID
                    JOIN M_Locator ml ON ml.M_Locator_ID = s.M_Locator_ID
                    WHERE 
                        s.AD_Client_ID = 1000000
                        AND m.AD_Client_ID = 1000000
                        AND s.M_Locator_ID IN (1001135, 1000614, 1001128, 1001136)
                        AND (:product IS NULL OR UPPER(m.name) LIKE UPPER(:product) || '%')
                    GROUP BY 
                        m.M_Product_ID, m.name
                ),
                Stock_Principale AS (
                    SELECT 
                        ROUND(SUM(M_ATTRIBUTEINSTANCE.valuenumber * (m_storage.qtyonhand - m_storage.QTYRESERVED)), 2) AS stock_principale
                    FROM 
                        M_ATTRIBUTEINSTANCE
                    JOIN m_storage ON m_storage.M_ATTRIBUTEsetINSTANCE_id = M_ATTRIBUTEINSTANCE.M_ATTRIBUTEsetINSTANCE_id
                    WHERE 
                        M_ATTRIBUTEINSTANCE.M_Attribute_ID = 1000504
                        AND m_storage.qtyonhand > 0
                        AND m_storage.M_Locator_ID IN (1001135, 1000614, 1001128, 1001136)
                        AND (:product IS NULL OR m_storage.M_Product_ID IN (SELECT M_Product_ID FROM M_Product WHERE UPPER(name) LIKE UPPER(:product) || '%'))
                )
                SELECT 
                    oq.midp,
                    oq.product_name,
                    oq.QTYONHAND AS "QTY DISPO",
                    COALESCE(fp.last_purchase_qty, 0) AS "DERNIER ACHAT",
                    fp.dateinvoiced AS "DATE",
                    sp.stock_principale AS "VALEUR"
                FROM 
                    On_Hand_Quantity oq
                LEFT JOIN 
                    Filtered_Latest_Purchase fp ON oq.midp = fp.M_Product_ID
                CROSS JOIN 
                    Stock_Principale sp
                ORDER BY 
                    oq.product_name
            """

            params = {
                'start_date': start_date,
                'end_date': end_date,
                'product': product or None
            }

            cursor.execute(query, params)
            rows = cursor.fetchall()

            data = [
                {
                    "PRODUCT_ID": row[0],
                    "PRODUCT_NAME": row[1],
                    "QTY_DISPO": row[2],
                    "DERNIER_ACHAT": row[3],
                    "DATE": row[4],
                    "VALEUR": row[5]
                }
                for row in rows
            ]

            return data
    
    except Exception as e:
        logger.error(f"Error fetching historique rotation: {e}")
        return {"error": "An error occurred while fetching historique rotation."}


@app.route('/fetchHistoriqueRotation', methods=['GET'])
def fetch_historique():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    product = request.args.get('product')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    data = fetch_historique_rotation(start_date, end_date, product)
    return jsonify(data)


if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5003)