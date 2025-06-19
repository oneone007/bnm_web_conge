


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


# Stock Movement functionality
def fetch_stock_movement_data(start_date=None, end_date=None, product=None, fournisseur=None, emplacement=None):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            
            # For empty or None emplacement, we want to fetch both Préparation and HANGAR
            if emplacement == '' or emplacement is None:
                emplacement_condition = """
                AND (l.value LIKE 'Préparation%' OR l.value LIKE 'HANGAR%')
                """
                stock_initial_condition = """
                AND (l.value LIKE 'Préparation%' OR l.value LIKE 'HANGAR%')
                """
                # Don't include emplacement in params for this case
                params = {
                    'start_date': start_date,
                    'end_date': end_date,
                    'product': product,
                    'fournisseur': fournisseur
                }
            else:
                # For specific emplacement, use only that one
                emplacement_condition = """
                AND l.value LIKE :emplacement || '%'
                """
                stock_initial_condition = """
                AND l.value LIKE :emplacement || '%'
                """
                params = {
                    'start_date': start_date,
                    'end_date': end_date,
                    'product': product,
                    'fournisseur': fournisseur,
                    'emplacement': emplacement
                }
            
            query = """
            SELECT
                t.MovementDate AS MovementDate,
                nvl(nvl(io.documentno,inv.documentno),m.documentno) as documentno,
                nvl(bp.name, nvl(inv.description,m.description)) as name,
                p.name AS productname,
                CASE WHEN t.movementqty > 0 then t.movementqty else 0 end as ENTREE,
                CASE WHEN t.movementqty < 0 then ABS(t.movementqty) else 0 end as SORTIE,                coalesce((SELECT SUM(s.movementqty)
                FROM m_transaction s
                inner join m_product p on (s.m_product_id = p.m_product_id)
                inner join m_locator l on (l.m_locator_id = s.m_locator_id)
                WHERE s.movementdate < t.movementdate
                AND (:product IS NULL OR p.name LIKE :product || '%')
                """ + stock_initial_condition + """
                ), 0) AS StockInitial,
                asi.lot,
                l_from.value AS locator_from,
                l_to.value AS locator_to
            FROM M_Transaction t
            INNER JOIN ad_org org
            ON org.ad_org_id = t.ad_org_id
            LEFT JOIN ad_orginfo oi
            ON oi.ad_org_id = org.ad_org_id
            LEFT JOIN c_location orgloc
            ON orgloc.c_location_id = oi.c_location_id
            INNER JOIN M_Locator l
            ON (t.M_Locator_ID=l.M_Locator_ID)
            INNER JOIN M_Product p
            ON (t.M_Product_ID=p.M_Product_ID)
            LEFT OUTER JOIN M_InventoryLine il
            ON (t.M_InventoryLine_ID=il.M_InventoryLine_ID)
            LEFT OUTER JOIN M_Inventory inv
            ON (inv.m_inventory_id = il.m_inventory_id)
            LEFT OUTER JOIN M_MovementLine ml
            ON (t.M_MovementLine_ID=ml.M_MovementLine_ID 
                AND NOT (ml.M_Locator_ID = 1001135 AND ml.M_LocatorTo_ID = 1000614)
                AND NOT (ml.M_Locator_ID = 1000614 AND ml.M_LocatorTo_ID = 1001135))
            LEFT OUTER JOIN M_Movement m
            ON (m.M_Movement_ID=ml.M_Movement_ID)
            LEFT OUTER JOIN M_InOutLine iol
            ON (t.M_InOutLine_ID=iol.M_InOutLine_ID)
            LEFT OUTER JOIN M_Inout io
            ON (iol.M_InOut_ID=io.M_InOut_ID)
            LEFT OUTER JOIN C_BPartner bp
            ON (bp.C_BPartner_ID = io.C_BPartner_ID)
            INNER JOIN M_attributesetinstance asi on t.m_attributesetinstance_id = asi.m_attributesetinstance_id
            INNER JOIN M_attributeinstance att on (att.m_attributesetinstance_id = asi.m_attributesetinstance_id)
            -- Add joins for from and to locators
            LEFT JOIN M_Locator l_from ON (
                CASE 
                    WHEN t.M_MovementLine_ID IS NOT NULL THEN ml.M_Locator_ID 
                    WHEN t.M_InOutLine_ID IS NOT NULL THEN 
                        CASE WHEN t.MovementQty > 0 THEN iol.M_Locator_ID ELSE NULL END
                    ELSE NULL 
                END = l_from.M_Locator_ID
            )
            LEFT JOIN M_Locator l_to ON (
                CASE 
                    WHEN t.M_MovementLine_ID IS NOT NULL THEN ml.M_LocatorTo_ID 
                    WHEN t.M_InOutLine_ID IS NOT NULL THEN 
                        CASE WHEN t.MovementQty < 0 THEN iol.M_Locator_ID ELSE NULL END
                    ELSE NULL 
                END = l_to.M_Locator_ID
            )
            WHERE (io.docstatus IN ('CO' , 'CL') 
            OR m.docstatus IN ('CO' , 'CL')
            OR inv.docstatus IN ('CO' , 'CL')) 
            AND att.m_attribute_id = 1000508            AND (:end_date IS NULL OR t.movementdate <= TO_DATE(:end_date, 'YYYY-MM-DD'))
            AND (:start_date IS NULL OR t.movementdate >= TO_DATE(:start_date, 'YYYY-MM-DD'))            AND (:product IS NULL OR P.NAME LIKE :product || '%')
            AND (:fournisseur IS NULL OR att.value like :fournisseur || '%')
            """ + emplacement_condition + """
            AND t.AD_Client_ID = 1000000
            ORDER BY t.MovementDate DESC            """

            print("Query parameters:", params)
            print("Emplacement value:", emplacement, "Type:", type(emplacement).__name__)

            cursor.execute(query, params)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            data = [dict(zip(columns, row)) for row in rows]
            
            return data

    except Exception as e:
        logger.error(f"Database error in fetch_stock_movement_data: {e}")
        return {"error": "An error occurred while fetching stock movement data."}

@app.route('/fetch-stock-movement-data', methods=['GET'])
def fetch_stock_movement():
    try:
        start_date = request.args.get("start_date", None)
        end_date = request.args.get("end_date", None)
        product = request.args.get("product", None)
        fournisseur = request.args.get("fournisseur", None)
        emplacement = request.args.get("emplacement", None)

        data = fetch_stock_movement_data(start_date, end_date, product, fournisseur, emplacement)
        return jsonify(data)

    except Exception as e:
        logger.error(f"Error in fetch_stock_movement endpoint: {e}")
        return jsonify({"error": "Failed to fetch stock movement data"}), 500

@app.route('/download-stock-movement-excel', methods=['GET'])
def download_stock_movement_excel():
    try:
        start_date = request.args.get("start_date", None)
        end_date = request.args.get("end_date", None)
        product = request.args.get("product", None)
        fournisseur = request.args.get("fournisseur", None)
        emplacement = request.args.get("emplacement", None)

        data = fetch_stock_movement_data(start_date, end_date, product, fournisseur, emplacement)
        
        if isinstance(data, dict) and "error" in data:
            return jsonify(data), 500

        # Generate Excel file
        excel_output = generate_excel_stock_movement(data)
        
        # Generate filename
        today_date = datetime.now().strftime("%d-%m-%Y")
        filename = f"Stock_Movement_{today_date}.xlsx"

        return send_file(excel_output, as_attachment=True, download_name=filename, 
                        mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")

    except Exception as e:
        logger.error(f"Error in download_stock_movement_excel: {e}")
        return jsonify({"error": "Failed to generate Excel file"}), 500

def generate_excel_stock_movement(data):
    """Generate Excel file for stock movement data"""
    df = pd.DataFrame(data)
    
    wb = Workbook()
    ws = wb.active
    ws.title = "Stock Movement"

    # Define header styles
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

    # Write headers
    if not df.empty:
        ws.append(df.columns.tolist())
        for cell in ws[1]:
            cell.fill = header_fill
            cell.font = header_font

        # Apply auto filter
        ws.auto_filter.ref = ws.dimensions

        # Write data rows
        for row_idx, row in enumerate(df.itertuples(index=False), start=2):
            ws.append(row)

        # Create table
        table = Table(displayName="StockMovementTable", ref=ws.dimensions)
        style = TableStyleInfo(
            name="TableStyleMedium9",
            showFirstColumn=False,
            showLastColumn=False,
            showRowStripes=True,
            showColumnStripes=False
        )
        table.tableStyleInfo = style
        ws.add_table(table)

    # Save to BytesIO
    output = BytesIO()
    wb.save(output)
    output.seek(0)
    return output

@app.route('/listfournisseur')
def list_fournisseur():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT cb.name
                FROM C_BPartner cb
                WHERE cb.AD_Client_ID = 1000000
                  AND cb.ISVENDOR = 'Y'
                  AND cb.ISACTIVE = 'Y'
                ORDER BY cb.name
            """
            cursor.execute(query)
            result = [row[0] for row in cursor.fetchall()]
            return jsonify(result)
    except Exception as e:
        logger.error(f"Error fetching fournisseurs: {e}")
        return jsonify({"error": "Could not fetch fournisseur list"}), 500


@app.route('/listproduct')
def list_product():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT name 
                FROM M_PRODUCT
                WHERE AD_Client_ID = 1000000
                  AND AD_Org_ID = 1000000
                  AND ISACTIVE = 'Y'
                ORDER BY name
            """
            cursor.execute(query)
            result = [row[0] for row in cursor.fetchall()]
            return jsonify(result)
    except Exception as e:
        logger.error(f"Error fetching products: {e}")
        return jsonify({"error": "Could not fetch products list"}), 500

@app.route('/fetch-emplacements')
def fetch_emplacements():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT ml.value AS EMPLACEMENT
                FROM M_Locator ml
                JOIN M_Warehouse m ON m.M_WAREHOUSE_ID = ml.M_WAREHOUSE_ID
                WHERE m.ISACTIVE = 'Y'
                  AND m.AD_Client_ID = 1000000
                  AND ml.ISACTIVE = 'Y'
                  AND ml.AD_Client_ID = 1000000
                ORDER BY m.value
            """
            cursor.execute(query)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            data = [dict(zip(columns, row)) for row in rows]
            return jsonify(data)
    except Exception as e:
        logger.error(f"Error fetching emplacements: {e}")
        return jsonify({"error": "Could not fetch emplacement list"}), 500
    


if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5002)