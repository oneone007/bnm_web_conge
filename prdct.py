
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


def fetch_historique_rotation(product):
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
                        c.dateinvoiced BETWEEN TO_DATE('01/01/2020', 'DD/MM/YYYY') AND SYSDATE
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
    product = request.args.get('product')

    data = fetch_historique_rotation(product)
    return jsonify(data)




def rotation_par_mois(start_date, end_date, product):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
WITH date_range AS (
    SELECT 
        CASE 
            WHEN TO_DATE(:end_date, 'YYYY-MM-DD') - TO_DATE(:start_date, 'YYYY-MM-DD') < 30 THEN 
                TO_CHAR(TO_DATE(:start_date, 'YYYY-MM-DD') + LEVEL - 1, 'YYYY-MM-DD')
            ELSE 
                TO_CHAR(ADD_MONTHS(TO_DATE(:start_date, 'YYYY-MM-DD'), LEVEL - 1), 'YYYY-MM')
        END AS invoice_period
    FROM DUAL
    CONNECT BY 
        CASE 
            WHEN TO_DATE(:end_date, 'YYYY-MM-DD') - TO_DATE(:start_date, 'YYYY-MM-DD') < 30 THEN 
                TO_DATE(:start_date, 'YYYY-MM-DD') + LEVEL - 1
            ELSE 
                ADD_MONTHS(TO_DATE(:start_date, 'YYYY-MM-DD'), LEVEL - 1)
        END <= TO_DATE(:end_date, 'YYYY-MM-DD')
),

invoice_data AS (
    SELECT
        dr.invoice_period,
        NVL(SUM(ff.QTYENTERED), 0) AS qty_vendu
    FROM
        date_range dr
    LEFT JOIN
        xx_ca_fournisseur_facture ff
    ON
        CASE 
            WHEN TO_DATE(:end_date, 'YYYY-MM-DD') - TO_DATE(:start_date, 'YYYY-MM-DD') < 30 THEN 
                TO_CHAR(ff.DATEINVOICED, 'YYYY-MM-DD')
            ELSE 
                TO_CHAR(ff.DATEINVOICED, 'YYYY-MM')
        END = dr.invoice_period
        AND ff.DATEINVOICED BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD')
        AND ff.AD_ORG_ID = 1000000
        AND ff.product LIKE '%' || :product || '%'
    GROUP BY
        dr.invoice_period
),

purchase_data AS (
    SELECT
        dr.invoice_period,
        SUM(
            CASE 
                WHEN xf.C_DocType_ID = 1000646 THEN -1 * mi.QTYENTERED
                ELSE mi.QTYENTERED
            END
        ) AS qty_acheté
    FROM
        date_range dr
    LEFT JOIN
        M_InOut xf ON
            CASE 
                WHEN TO_DATE(:end_date, 'YYYY-MM-DD') - TO_DATE(:start_date, 'YYYY-MM-DD') < 30 THEN 
                    TO_CHAR(xf.MOVEMENTDATE, 'YYYY-MM-DD')
                ELSE 
                    TO_CHAR(xf.MOVEMENTDATE, 'YYYY-MM')
            END = dr.invoice_period
        AND xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD')
        AND xf.AD_Org_ID = 1000000
        AND xf.C_DocType_ID IN (1000013, 1000646)
        AND xf.M_Warehouse_ID != 1000521
    JOIN M_INOUTLINE mi ON mi.M_INOUT_ID = xf.M_INOUT_ID
        AND mi.AD_Org_ID = 1000000
    JOIN C_InvoiceLine ci ON ci.M_INOUTLINE_ID = mi.M_INOUTLINE_ID
    JOIN M_PRODUCT m ON m.M_PRODUCT_ID = mi.M_PRODUCT_ID
        AND m.AD_Org_ID = 1000000
        AND m.name LIKE '%' || :product || '%'
    LEFT JOIN C_BPartner cb ON cb.C_BPARTNER_ID = xf.C_BPARTNER_ID
    GROUP BY
        dr.invoice_period
),

sold_totals AS (
    SELECT
        'TOTAL' AS invoice_period,
        SUM(qty_vendu) AS qty_vendu,
        NULL AS qty_acheté,
        2 AS sort_order
    FROM
        invoice_data
),
sold_averages AS (
    SELECT
        'MOYENNE' AS invoice_period,
        TRUNC(AVG(qty_vendu)) AS qty_vendu,
        NULL AS qty_acheté,
        3 AS sort_order
    FROM
        invoice_data
),

purchased_totals AS (
    SELECT
        'TOTAL' AS invoice_period,
        NULL AS qty_vendu,
        SUM(qty_acheté) AS qty_acheté,
        2 AS sort_order
    FROM
        purchase_data
),
purchased_averages AS (
    SELECT
        'MOYENNE' AS invoice_period,
        NULL AS qty_vendu,
        TRUNC(AVG(qty_acheté)) AS qty_acheté,
        3 AS sort_order
    FROM
        purchase_data
),

combined_data AS (
    SELECT
        COALESCE(id.invoice_period, pd.invoice_period) AS invoice_period,
        id.qty_vendu,
        pd.qty_acheté,
        1 AS sort_order
    FROM
        invoice_data id
    FULL OUTER JOIN
        purchase_data pd
    ON
        id.invoice_period = pd.invoice_period
),

final_results AS (
    SELECT
        invoice_period,
        COALESCE(qty_vendu, 0) AS qty_vendu,
        COALESCE(qty_acheté, 0) AS qty_acheté,
        sort_order
    FROM
        combined_data
    UNION ALL
    SELECT
        invoice_period,
        qty_vendu,
        qty_acheté,
        sort_order
    FROM
        sold_totals
    UNION ALL
    SELECT
        invoice_period,
        qty_vendu,
        qty_acheté,
        sort_order
    FROM
        sold_averages
    UNION ALL
    SELECT
        invoice_period,
        qty_vendu,
        qty_acheté,
        sort_order
    FROM
        purchased_totals
    UNION ALL
    SELECT
        invoice_period,
        qty_vendu,
        qty_acheté,
        sort_order
    FROM
        purchased_averages
)

SELECT
    invoice_period AS period,
    SUM(qty_vendu) AS qty_vendu,
    SUM(qty_acheté) AS qty_acheté
FROM
    final_results
GROUP BY
    invoice_period,
    sort_order
ORDER BY
    sort_order, invoice_period
            """

            params = {
                'start_date': start_date,
                'end_date': end_date,
                'product': product or '%'
            }

            cursor.execute(query, params)
            rows = cursor.fetchall()

            data = [
                {
                    "PERIOD": row[0],
                    "QTY_VENDU": row[1],
                    "QTY_ACHETÉ": row[2]
                }
                for row in rows
            ]

            return data

    except Exception as e:
        logger.error(f"Error fetching rotation par mois: {e}")
        return {"error": "An error occurred while fetching rotation data."}

@app.route('/rotationParMois', methods=['GET'])
def fetch_rotation():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    product = request.args.get('product')
    
    data = rotation_par_mois(start_date, end_date, product)
    return jsonify(data)




@app.route('/download-rotation-par-mois-excel', methods=['GET'])
def download_rotation_par_mois_excel():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    product = request.args.get('product', 'All_Products')  # Default if no product is provided

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data
    data = rotation_par_mois(start_date, end_date, product)

    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Generate filename with product name, date range, and download date & time
    download_datetime = datetime.now().strftime("%d-%m-%Y_%H-%M")  # Day-Month-Year_Hour-Minute
    sanitized_product = product.replace(" ", "_").replace("/", "-")  # Replace spaces & slashes
    filename = f"Rotation_{sanitized_product}_{start_date}_to_{end_date}_{download_datetime}.xlsx"

    return generate_excel_rotation(data, filename)


def generate_excel_rotation(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Convert data to DataFrame
    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    # Create Excel workbook
    wb = Workbook()
    ws = wb.active
    ws.title = "Rotation Par Mois"

    # Formatting headers
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

    # Add headers
    ws.append(df.columns.tolist())
    for col_idx, cell in enumerate(ws[1], 1):
        cell.fill = header_fill
        cell.font = header_font
    ws.auto_filter.ref = ws.dimensions

    # Add data rows with alternating row colors
    for row_idx, row in enumerate(df.itertuples(index=False), start=2):
        ws.append(row)
        if row_idx % 2 == 0:
            for cell in ws[row_idx]:
                cell.fill = PatternFill(start_color="EAEAEA", end_color="EAEAEA", fill_type="solid")

    # Add an Excel table
    table = Table(displayName="RotationTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    # Save the file in memory
    output = BytesIO()
    wb.save(output)
    output.seek(0)

    # Send the file to the client
    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")




def histogram(start_date, end_date, product):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
WITH 
date_range AS (
  SELECT 
    CASE 
      WHEN TO_DATE(:end_date, 'YYYY-MM-DD') - TO_DATE(:start_date, 'YYYY-MM-DD') < 30 THEN 
        TO_CHAR(TO_DATE(:start_date, 'YYYY-MM-DD') + LEVEL - 1, 'YYYY-MM-DD')
      ELSE 
        TO_CHAR(ADD_MONTHS(TO_DATE(:start_date, 'YYYY-MM-DD'), LEVEL - 1), 'YYYY-MM')
    END AS invoice_period
  FROM DUAL
  CONNECT BY 
    CASE 
      WHEN TO_DATE(:end_date, 'YYYY-MM-DD') - TO_DATE(:start_date, 'YYYY-MM-DD') < 30 THEN 
        TO_DATE(:start_date, 'YYYY-MM-DD') + LEVEL - 1
      ELSE 
        ADD_MONTHS(TO_DATE(:start_date, 'YYYY-MM-DD'), LEVEL - 1)
    END <= TO_DATE(:end_date, 'YYYY-MM-DD')
),

invoice_data AS (
  SELECT
    dr.invoice_period,
    NVL(SUM(ff.QTYENTERED), 0) AS qty_vendu
  FROM
    date_range dr
  LEFT JOIN
    xx_ca_fournisseur_facture ff
  ON
    CASE 
      WHEN TO_DATE(:end_date, 'YYYY-MM-DD') - TO_DATE(:start_date, 'YYYY-MM-DD') < 30 THEN 
        TO_CHAR(ff.DATEINVOICED, 'YYYY-MM-DD')
      ELSE 
        TO_CHAR(ff.DATEINVOICED, 'YYYY-MM')
    END = dr.invoice_period
    AND ff.DATEINVOICED BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD')
    AND ff.AD_ORG_ID = 1000000
    AND ff.product LIKE '%' || :product || '%'
  GROUP BY
    dr.invoice_period
),

purchase_data AS (
  SELECT
    dr.invoice_period,
    SUM(
      CASE 
        WHEN xf.C_DocType_ID = 1000646 THEN -1 * mi.QTYENTERED
        ELSE mi.QTYENTERED
      END
    ) AS qty_acheté
  FROM
    date_range dr
  LEFT JOIN
    M_InOut xf ON
      CASE 
        WHEN TO_DATE(:end_date, 'YYYY-MM-DD') - TO_DATE(:start_date, 'YYYY-MM-DD') < 30 THEN 
          TO_CHAR(xf.MOVEMENTDATE, 'YYYY-MM-DD')
        ELSE 
          TO_CHAR(xf.MOVEMENTDATE, 'YYYY-MM')
      END = dr.invoice_period
    AND xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD')
    AND xf.AD_Org_ID = 1000000
    AND xf.C_DocType_ID IN (1000013, 1000646)
    AND xf.M_Warehouse_ID != 1000521
  JOIN M_INOUTLINE mi ON mi.M_INOUT_ID = xf.M_INOUT_ID
    AND mi.AD_Org_ID = 1000000
  JOIN C_InvoiceLine ci ON ci.M_INOUTLINE_ID = mi.M_INOUTLINE_ID
  JOIN M_PRODUCT m ON m.M_PRODUCT_ID = mi.M_PRODUCT_ID
    AND m.AD_Org_ID = 1000000
    AND m.name LIKE '%' || :product || '%'
  LEFT JOIN C_BPartner cb ON cb.C_BPARTNER_ID = xf.C_BPARTNER_ID
  GROUP BY
    dr.invoice_period
),

combined_data AS (
  SELECT
    COALESCE(id.invoice_period, pd.invoice_period) AS invoice_period,
    id.qty_vendu,
    pd.qty_acheté,
    1 AS sort_order
  FROM
    invoice_data id
  FULL OUTER JOIN
    purchase_data pd
  ON
    id.invoice_period = pd.invoice_period
)

SELECT
  invoice_period AS period,
  COALESCE(SUM(qty_acheté), 0) AS qty_acheté,
  COALESCE(SUM(qty_vendu), 0) AS qty_vendu
FROM
  combined_data
GROUP BY
  invoice_period,
  sort_order
ORDER BY
  invoice_period
            """

            params = {
                'start_date': start_date,
                'end_date': end_date,
                'product': product or '%'
            }

            cursor.execute(query, params)
            rows = cursor.fetchall()

            data = [
                {
                    "PERIOD": row[0],
                    "QTY_ACHETÉ": row[1],
                    "QTY_VENDU": row[2]
                }
                for row in rows
            ]

            return data

    except Exception as e:
        logger.error(f"Error fetching histogram data: {e}")
        return {"error": "An error occurred while fetching histogram data."}

@app.route('/histogram', methods=['GET'])
def histogram_route():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    product = request.args.get('product', '')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date"}), 400

    data = histogram(start_date, end_date, product)
    return jsonify(data)



if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000)