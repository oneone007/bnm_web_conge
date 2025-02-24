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

# Fetch reserved data from Oracle DB
def fetch_reserved_data():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
            SELECT 
                us.name AS OPERATEUR,
                co.documentno AS NDOCUMENT,
                m.name AS PRODUCT,
                co.dateordered AS DATECOMMANDE,
                s.qtyreserved AS TOTALRESERVE,
                cl.qtyreserved AS QTYRESERVE,
                l.name AS NAME,
                CASE
                    WHEN co.docstatus = 'CO' THEN 'prepared'
                    ELSE 'not prepared'
                END AS STATUS
            FROM
                m_storage s
            INNER JOIN c_orderline cl
                ON cl.m_attributesetinstance_id = s.m_attributesetinstance_id
            INNER JOIN c_order co
                ON co.c_order_id = cl.c_order_id
            INNER JOIN m_product m
                ON m.m_product_id = s.m_product_id
            INNER JOIN ad_user us
                ON us.ad_user_id = co.salesrep_id
            JOIN XX_Laboratory l
                ON l.XX_Laboratory_ID = m.XX_Laboratory_ID
            WHERE
                cl.qtyreserved != 0
                AND co.c_doctypetarget_id = 1000539
                AND cl.m_product_id = s.m_product_id
                AND co.ad_org_id = 1000000
                AND s.qtyonhand > 0
                AND s.qtyreserved > 0
            FETCH FIRST 1048575 ROWS ONLY
            """
            cursor.execute(query)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            data = [dict(zip(columns, row)) for row in rows]
            return data
    except Exception as e:
        logger.error(f"Error fetching reserved data: {e}")
        return {"error": "An error occurred while fetching reserved data."}

# Fetch remise data from Oracle DB
def fetch_remise_data():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
            WITH LastFournisseur AS (
                SELECT
                    cf.m_product_id,
                    cf.c_bpartner_id,
                    ROW_NUMBER() OVER (
                        PARTITION BY cf.m_product_id
                        ORDER BY cf.dateinvoiced DESC
                    ) AS rn
                FROM
                    xx_ca_fournisseur_facture cf
            )
            SELECT
                mp.name AS product,
                md.name AS reward,
                l.name AS laboratory_name,
                cb.name AS fournisseur,
                CBG.NAME AS Type_Client
            FROM
                m_product mp
                INNER JOIN C_BPartner_Product cbp ON mp.m_product_id = cbp.m_product_id
                INNER JOIN  C_BP_Group CBG  ON CBG.C_BP_Group_ID = CBP.C_BP_Group_ID
                JOIN XX_Laboratory l ON l.XX_Laboratory_ID = mp.XX_Laboratory_ID
                JOIN LastFournisseur lf ON mp.m_product_id = lf.m_product_id AND lf.rn = 1
                JOIN c_bpartner cb ON cb.c_bpartner_id = lf.c_bpartner_id
                INNER JOIN M_DiscountSchema md ON cbp.M_DiscountSchema_id = md.M_DiscountSchema_id
            WHERE
                cbp.C_BPartner_Product_id IS NOT NULL
                AND cbp.m_discountschema_id IS NOT NULL
            ORDER BY
                mp.name
            FETCH FIRST 1048575 ROWS ONLY
            """
            cursor.execute(query)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            data = [dict(zip(columns, row)) for row in rows]
            return data
    except Exception as e:
        logger.error(f"Error fetching remise data: {e}")
        return {"error": "An error occurred while fetching remise data."}

# Fetch marge data from Oracle DB
def fetch_marge_data():
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
            "source"."QTY" "QTY"
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
                    LEAST(round((marge), 2), 100) AS marge,  -- Cap margin at 100%
                    labo,
                    lot,
                    qty
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
                            ) AS marge  -- Ensure margin does not exceed 100%
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
                                            ) lot
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
                                            AND mst.m_locator_id IN (1000614, 1001135)
                                            AND mst.qtyonhand != 0
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
                    qty
                ORDER BY
                    fournisseur
            ) "source"
    )
WHERE
    rownum <= 1048575

            """
            cursor.execute(query)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            data = [dict(zip(columns, row)) for row in rows]
            return data
    except Exception as e:
        logger.error(f"Error fetching marge data: {e}")
        return {"error": "An error occurred while fetching marge data."}

# Fetch bonus data from Oracle DB
def fetch_bonus_data():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
            SELECT DISTINCT 
                mp.name AS product,
                (SELECT description 
                 FROM XX_SalesContext xsc 
                 WHERE mp.XX_SalesContext_id = xsc.XX_SalesContext_id) AS bonus,
                l.name AS laboratory_name,
                cbp.name AS fournisseur
            FROM 
                m_product mp
            JOIN XX_Laboratory l ON l.XX_Laboratory_ID = mp.XX_Laboratory_ID
            JOIN m_storage s ON s.m_product_id = mp.m_product_id
            JOIN M_ATTRIBUTEINSTANCE asi ON s.M_ATTRIBUTESETINSTANCE_ID = asi.M_ATTRIBUTESETINSTANCE_ID
            JOIN c_bpartner cbp ON cbp.c_bpartner_id = ValueNUMBER_of_ASI('Fournisseur', asi.m_attributesetinstance_id)
            WHERE 
                mp.xx_salescontext_id NOT IN (1000000, 1000100)
                AND mp.ad_org_id = 1000000
            ORDER BY 
                mp.name
            FETCH FIRST 1048575 ROWS ONLY
            """
            cursor.execute(query)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            data = [dict(zip(columns, row)) for row in rows]
            return data
    except Exception as e:
        logger.error(f"Error fetching bonus data: {e}")
        return {"error": "An error occurred while fetching bonus data."}



# Test database connection
def test_db_connection():
    try:
        with DB_POOL.acquire() as connection:
            logger.info("Database connection successful.")
        return True
    except Exception as e:
        logger.error(f"Database connection failed: {str(e)}")
        return False
    



# Route to fetch reserved data
@app.route('/fetch-reserved-data', methods=['GET'])
def fetch_reserved():
    if not test_db_connection():
        return jsonify({"error": "Failed to connect to the database."}), 500
    data = fetch_reserved_data()
    return jsonify(data)

# Route to fetch remise data
@app.route('/fetch-remise-data', methods=['GET'])
def fetch_remise():
    if not test_db_connection():
        return jsonify({"error": "Failed to connect to the database."}), 500
    data = fetch_remise_data()
    return jsonify(data)

# Route to fetch marge data
@app.route('/fetch-data', methods=['GET'])
def fetch_marge():
    if not test_db_connection():
        return jsonify({"error": "Failed to connect to the database."}), 500
    data = fetch_marge_data()
    return jsonify(data)


# Route to fetch bonus data
@app.route('/fetch-bonus-data', methods=['GET'])
def fetch_bonus():
    if not test_db_connection():
        return jsonify({"error": "Failed to connect to the database."}), 500
    data = fetch_bonus_data()
    return jsonify(data)

# Route to download Excel for reserved data
@app.route('/download-reserved-excel', methods=['GET'])
def download_reserved_excel():
    reserve_value = request.args.get("reserve", "RESERVE")
    data = fetch_reserved_data()
    return generate_excel(data, reserve_value)

# Route to download Excel for remise data
@app.route('/download-remise-excel', methods=['GET'])
def download_remise_excel():
    remise_value = request.args.get("remise", "REMISE")
    data = fetch_remise_data()
    return generate_excel(data, remise_value)


# Route to download Excel for bonus data
@app.route('/download-bonus-excel', methods=['GET'])
def download_bonus_excel():
    bonus_value = request.args.get("bonus", "BONUS")
    data = fetch_bonus_data()
    return generate_excel(data, bonus_value)


# Route to download Excel for marge data
@app.route('/download-marge-excel', methods=['GET'])
def download_marge_excel():
    data = fetch_marge_data()  # Fetch all data
    filters = {
        "fournisseur": request.args.get("fournisseur", "").strip(),
        "product": request.args.get("product", "").strip(),
        "marge": request.args.get("marge", "").strip(),
    }

    # Apply filters if provided
    if filters["fournisseur"]:
        data = [row for row in data if filters["fournisseur"].lower() in row["FOURNISSEUR"].lower()]
    if filters["product"]:
        data = [row for row in data if filters["product"].lower() in row["PRODUCT"].lower()]
    if filters["marge"]:
        try:
            marge_value = float(filters["marge"])
            data = [row for row in data if float(row["MARGE"]) >= marge_value]
        except ValueError:
            return jsonify({"error": "Invalid marge value"}), 400

    return generate_excel_marge(data, filters)

#----------------------------
def generate_excel_marge(data, filters):
    if not data:
        return {"error": "No data available"}, 400

    df = pd.DataFrame(data)
    wb = Workbook()
    ws = wb.active
    ws.title = "Filtered Data"

    # Apply header styles
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)
    ws.append(df.columns.tolist())
    
    for cell in ws[1]:
        cell.fill = header_fill
        cell.font = header_font

    # Append data with alternating row colors
    for row_idx, row in enumerate(df.itertuples(index=False), start=2):
        ws.append(row)
        if row_idx % 2 == 0:
            for cell in ws[row_idx]:
                cell.fill = PatternFill(start_color="EAEAEA", end_color="EAEAEA", fill_type="solid")

    # Generate filename dynamically based on filters (include values)
    today_date = datetime.now().strftime("%d-%m-%Y")
    filter_text = "_".join([f"{k}-{v}" for k, v in filters.items() if v])  # Include values
    filename = f"Marge_{filter_text}_{today_date}.xlsx" if filter_text else f"Marge_{today_date}.xlsx"

    output = BytesIO()
    wb.save(output)
    output.seek(0)

    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")


# Helper function to generate Excel file
def generate_excel(data, value):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    wb = Workbook()
    ws = wb.active
    ws.title = "All Data"

    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

    ws.append(df.columns.tolist())
    for col_idx, cell in enumerate(ws[1], 1):
        cell.fill = header_fill
        cell.font = header_font
    ws.auto_filter.ref = ws.dimensions

    for row_idx, row in enumerate(df.itertuples(index=False), start=2):
        ws.append(row)
        if row_idx % 2 == 0:
            for cell in ws[row_idx]:
                cell.fill = PatternFill(start_color="EAEAEA", end_color="EAEAEA", fill_type="solid")

    table = Table(displayName="DataTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    today_date = datetime.now().strftime("%d-%m-%Y")
    filename = f"{value}_{today_date}.xlsx"

    output = BytesIO()
    wb.save(output)
    output.seek(0)

    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")


def fetch_filtred_emplacment_from_db():
    try:
        # Fetch emplacement data
        emplacements_data = fetch_emplacment_data()

        # Extract emplacement values
        emplacements = [entry["EMPLACEMENT"] for entry in emplacements_data]

        # Build the emplacement filter dynamically
        emplacement_filter = ""
        if emplacements:
            emplacement_values = "', '".join(emplacements)  # Convert list to SQL format
            emplacement_filter = f"AND ml.value IN ('{emplacement_values}')"

        # Define the SQL query with dynamic emplacement filtering
        query = f"""
        SELECT 
            mati.value AS fournisseur, 
            m.name,  
            SUM(m_storage.qtyonhand) AS qty,
            SUM(M_ATTRIBUTEINSTANCE.valuenumber * m_storage.qtyonhand) AS prix,
            SUM(m_storage.qtyonhand - m_storage.QTYRESERVED) AS qty_dispo, 
            SUM(M_ATTRIBUTEINSTANCE.valuenumber * (m_storage.qtyonhand - m_storage.QTYRESERVED)) AS prix_dispo,
            ml.M_Locator_ID AS locatorid,
            m.m_product_id AS productid,
            1 AS sort_order
        FROM 
            M_ATTRIBUTEINSTANCE
        JOIN 
            m_storage ON m_storage.M_ATTRIBUTEsetINSTANCE_id = M_ATTRIBUTEINSTANCE.M_ATTRIBUTEsetINSTANCE_id
        JOIN 
            M_PRODUCT m ON m.M_PRODUCT_id = m_storage.M_PRODUCT_id
        JOIN 
            M_Locator ml ON ml.M_Locator_ID = m_storage.M_Locator_ID
        INNER JOIN 
            m_attributeinstance mati ON m_storage.m_attributesetinstance_id = mati.m_attributesetinstance_id
        WHERE 
            M_ATTRIBUTEINSTANCE.M_Attribute_ID = 1000504
            AND m_storage.qtyonhand > 0
            AND mati.m_attribute_id = 1000508
            AND m_storage.AD_Client_ID = 1000000
            {emplacement_filter}  -- Dynamically added emplacement filter
        GROUP BY 
            m.name, mati.value, m.m_product_id, ml.M_Locator_ID
        ORDER BY 
            fournisseur, name
        """

        # Execute the query
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            cursor.execute(query)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            data = [dict(zip(columns, row)) for row in rows]

        return data

    except Exception as e:
        logger.error(f"Error fetching data: {e}")
        return {"error": "An error occurred while fetching data."}



def fetch_emplacment_data():
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
            columns = [col[0] for col in cursor.description]  # Get column names
            data = [dict(zip(columns, row)) for row in rows]
            return data
    except Exception as e:
        logger.error(f"Error fetching remise data: {e}")
        return {"error": "An error occurred while fetching emplacement data."}
    
@app.route('/fetch-emplacement-data', methods=['GET'])
def fetch_data():
    data = fetch_emplacment_data()
    return jsonify(data)

@app.route('/fetch-data-stock', methods=['GET'])
def fetch_stock_data():
    data = fetch_filtred_emplacment_from_db()
    return jsonify(data)

# Route to download Excel for bonus data
@app.route('/download-stock-excel', methods=['GET'])
def download_stock_excel():
    stock_value = request.args.get("stock", "STOCK")
    filters = {
        "locatorid": request.args.get("locatorid"),
        "locatorname": request.args.get("locatorname"),  # Capture the name
        "fournisseur": request.args.get("fournisseur"),
        "name": request.args.get("name")
    }

    data = fetch_filtred_emplacment_from_db()

    # Apply filters
    if filters["locatorid"]:
        data = [row for row in data if str(row["LOCATORID"]) == filters["locatorid"]]
    if filters["fournisseur"]:
        data = [row for row in data if filters["fournisseur"].lower() in row["FOURNISSEUR"].lower()]
    if filters["name"]:
        data = [row for row in data if filters["name"].lower() in row["NAME"].lower()]

    # Generate filename dynamically
    today_date = datetime.now().strftime("%d-%m-%Y")
    location_part = f"_{filters['locatorname']}" if filters["locatorname"] else ""
    filter_values = "_".join([f"{key}-{value}" for key, value in filters.items() if value and key != "locatorname"])
    
    filename = f"{stock_value}_{today_date}{location_part}.xlsx" if not filter_values else f"{stock_value}_{today_date}{location_part}_{filter_values}.xlsx"

    return generate_excel_stock(data, filename)



# Helper function to generate Excel file
def generate_excel_stock(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    wb = Workbook()
    ws = wb.active
    ws.title = "Filtered Data"

    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

    ws.append(df.columns.tolist())
    for col_idx, cell in enumerate(ws[1], 1):
        cell.fill = header_fill
        cell.font = header_font
    ws.auto_filter.ref = ws.dimensions

    for row_idx, row in enumerate(df.itertuples(index=False), start=2):
        ws.append(row)
        if row_idx % 2 == 0:
            for cell in ws[row_idx]:
                cell.fill = PatternFill(start_color="EAEAEA", end_color="EAEAEA", fill_type="solid")

    table = Table(displayName="DataTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    output = BytesIO()
    wb.save(output)
    output.seek(0)

    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")

# Fetch total recap data
def fetch_rcap_data(start_date, end_date):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT 
                    SUM(xf.TOTALLINE) AS CHIFFRE, 
                    SUM(xf.qtyentered) AS QTY,
                    SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION) AS MARGE,
                    SUM(xf.CONSOMATION) AS CONSOMATION,
                    CASE 
                        WHEN SUM(xf.CONSOMATION) < 0 
                        THEN ROUND(((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / (SUM(xf.CONSOMATION) * -1)), 4)
                        ELSE ROUND((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / NULLIF(SUM(xf.CONSOMATION), 0), 4)
                    END AS POURCENTAGE
                FROM xx_ca_fournisseur xf
                WHERE 
                    xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                    AND TO_DATE(:end_date, 'YYYY-MM-DD')
                    AND xf.AD_Org_ID = 1000000
                    AND xf.DOCSTATUS != 'RE'
            """
            cursor.execute(query, {'start_date': start_date, 'end_date': end_date})
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            data = [dict(zip(columns, row)) for row in rows]
            return data
    except Exception as e:
        logger.error(f"Error fetching data: {e}")
        return {"error": "An error occurred while fetching data."}

# Fetch fournisseur data
def fetch_fournisseur_data(start_date, end_date, fournisseur, product, client, operateur, bccb, zone):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT * FROM (
                    SELECT 
                        CAST(xf.name AS VARCHAR2(300)) AS FOURNISSEUR,   
                        SUM(xf.TOTALLINE) AS total, 
                        SUM(xf.qtyentered) AS QTY,
                        ROUND(
                            CASE 
                                WHEN SUM(xf.CONSOMATION) = 0 THEN 0
                                WHEN SUM(xf.CONSOMATION) < 0 THEN ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION) * -1) * 100
                                ELSE ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION)) * 100
                            END, 4) AS marge,
                        0 AS sort_order
                    FROM xx_ca_fournisseur xf
                    JOIN C_BPartner cb ON cb.C_BPartner_ID = xf.CLIENTID
                    JOIN AD_User au ON au.AD_User_ID = xf.SALESREP_ID
                    JOIN C_BPartner_Location bpl ON bpl.C_BPartner_ID = xf.CLIENTID
                    JOIN C_SalesRegion sr ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    JOIN C_ORDER C ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD')
                        AND xf.AD_Org_ID = 1000000
                        AND xf.DOCSTATUS != 'RE'
                        AND (:fournisseur IS NULL OR xf.name LIKE :fournisseur || '%')
                        AND (:product IS NULL OR xf.product LIKE :product || '%')
                        AND (:client IS NULL OR cb.name LIKE :client || '%')
                        AND (:operateur IS NULL OR au.name LIKE :operateur || '%')
                        AND (:bccb IS NULL OR C.DOCUMENTNO LIKE :bccb || '%')
                        AND (:zone IS NULL OR sr.name LIKE :zone || '%')
                    GROUP BY xf.name
                    UNION ALL
                    SELECT 
                        CAST('Total' AS VARCHAR2(300)) AS name, 
                        SUM(xf.TOTALLINE) AS total, 
                        SUM(xf.qtyentered) AS QTY,
                        ROUND(
                            CASE 
                                WHEN SUM(xf.CONSOMATION) = 0 THEN 0
                                WHEN SUM(xf.CONSOMATION) < 0 THEN ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION) * -1) * 100
                                ELSE ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION)) * 100
                            END, 4) AS marge,
                        1 AS sort_order
                    FROM xx_ca_fournisseur xf
                    JOIN C_BPartner cb ON cb.C_BPartner_ID = xf.CLIENTID
                    JOIN AD_User au ON au.AD_User_ID = xf.SALESREP_ID
                    JOIN C_BPartner_Location bpl ON bpl.C_BPartner_ID = xf.CLIENTID
                    JOIN C_SalesRegion sr ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    JOIN C_ORDER C ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD')
                        AND xf.AD_Org_ID = 1000000
                        AND xf.DOCSTATUS != 'RE'
                        AND (:fournisseur IS NULL OR xf.name LIKE :fournisseur || '%')
                        AND (:product IS NULL OR xf.product LIKE :product || '%')
                        AND (:client IS NULL OR cb.name LIKE :client || '%')
                        AND (:operateur IS NULL OR au.name LIKE :operateur || '%')
                        AND (:bccb IS NULL OR C.DOCUMENTNO LIKE :bccb || '%')
                        AND (:zone IS NULL OR sr.name LIKE :zone || '%')
                )
                ORDER BY sort_order, total DESC
            """
            params = {
                'start_date': start_date,
                'end_date': end_date,
                'fournisseur': fournisseur or None,
                'product': product or None,
                'client': client or None,
                'operateur': operateur or None,
                'bccb': bccb or None,
                'zone': zone or None
            }

            cursor.execute(query, params)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            data = [dict(zip(columns, row)) for row in rows]
            return data
    except Exception as e:
        logger.error(f"Error fetching fournisseur data: {e}")
        return {"error": "An error occurred while fetching fournisseur data."}

def fetch_product_data(start_date, end_date, fournisseur, product, client, operateur, bccb, zone):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT * FROM (
                    -- Main Product Breakdown
                    SELECT 
                        CAST(xf.product AS VARCHAR2(400)) AS "PRODUIT",
                        SUM(xf.TOTALLINE) AS "TOTAL",
                        SUM(xf.qtyentered) AS "QTY",
                        CASE 
                            WHEN SUM(xf.CONSOMATION) = 0 THEN 0
                            ELSE ROUND((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / NULLIF(SUM(xf.CONSOMATION), 0), 4)
                        END AS "MARGE",
                        0 AS "SORT_ORDER"
                    FROM xx_ca_fournisseur xf
                    LEFT JOIN C_BPartner cb ON cb.C_BPartner_ID = xf.CLIENTID
                    LEFT JOIN AD_User au ON au.AD_User_ID = xf.SALESREP_ID
                    LEFT JOIN C_BPartner_Location bpl ON bpl.C_BPartner_ID = xf.CLIENTID
                    LEFT JOIN C_SalesRegion sr ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    LEFT JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    LEFT JOIN C_ORDER c ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                          AND xf.AD_Org_ID = 1000000
                          AND xf.DOCSTATUS != 'RE'
                          AND (:fournisseur IS NULL OR UPPER(xf.name) LIKE UPPER(:fournisseur) || '%')
                          AND (:product IS NULL OR UPPER(xf.product) LIKE UPPER(:product) || '%')
                          AND (:client IS NULL OR UPPER(cb.name) LIKE UPPER(:client) || '%')
                          AND (:operateur IS NULL OR UPPER(au.name) LIKE UPPER(:operateur) || '%')
                          AND (:bccb IS NULL OR UPPER(c.DOCUMENTNO) LIKE UPPER(:bccb) || '%')
                          AND (:zone IS NULL OR UPPER(sr.name) LIKE UPPER(:zone) || '%')
                    GROUP BY xf.product

                    UNION ALL

                    -- Total Row
                    SELECT 
                        'Total' AS "PRODUIT",
                        SUM(xf.TOTALLINE) AS "TOTAL",
                        SUM(xf.qtyentered) AS "QTY",
                        CASE 
                            WHEN SUM(xf.CONSOMATION) = 0 THEN 0
                            ELSE ROUND((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / NULLIF(SUM(xf.CONSOMATION), 0), 4)
                        END AS "MARGE",
                        1 AS "SORT_ORDER"
                    FROM xx_ca_fournisseur xf
                    LEFT JOIN C_BPartner cb ON cb.C_BPartner_ID = xf.CLIENTID
                    LEFT JOIN AD_User au ON au.AD_User_ID = xf.SALESREP_ID
                    LEFT JOIN C_BPartner_Location bpl ON bpl.C_BPartner_ID = xf.CLIENTID
                    LEFT JOIN C_SalesRegion sr ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    LEFT JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    LEFT JOIN C_ORDER c ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                          AND xf.AD_Org_ID = 1000000
                          AND xf.DOCSTATUS != 'RE'
                          AND (:fournisseur IS NULL OR UPPER(xf.name) LIKE UPPER(:fournisseur) || '%')
                          AND (:product IS NULL OR UPPER(xf.product) LIKE UPPER(:product) || '%')
                          AND (:client IS NULL OR UPPER(cb.name) LIKE UPPER(:client) || '%')
                          AND (:operateur IS NULL OR UPPER(au.name) LIKE UPPER(:operateur) || '%')
                          AND (:bccb IS NULL OR UPPER(c.DOCUMENTNO) LIKE UPPER(:bccb) || '%')
                          AND (:zone IS NULL OR UPPER(sr.name) LIKE UPPER(:zone) || '%')
                ) ORDER BY "SORT_ORDER", "TOTAL" DESC
            """
            params = {
                'start_date': start_date,
                'end_date': end_date,
                'fournisseur': fournisseur or None,
                'product': product or None,
                'client': client or None,
                'operateur': operateur or None,
                'bccb': bccb or None,
                'zone': zone or None
            }

            cursor.execute(query, params)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            return [dict(zip(columns, row)) for row in rows]

    except Exception as e:
        logger.error(f"Error fetching product data: {e}")
        return {"error": "An error occurred while fetching product data."}
def fetch_operator_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT * FROM (
                    SELECT CAST(au.name AS VARCHAR2(100)) AS "OPERATEUR", 
                           SUM(xf.TOTALLINE) AS "TOTAL", 
                           SUM(xf.qtyentered) AS "QTY",
                           CASE 
                               WHEN SUM(xf.CONSOMATION) = 0 THEN 0
                               WHEN SUM(xf.CONSOMATION) < 0 THEN ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION) * -1)
                               ELSE (SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION)
                           END AS "MARGE",
                           0 AS "SORT_ORDER"
                    FROM AD_User au
                    JOIN xx_ca_fournisseur xf ON au.AD_User_ID = xf.SALESREP_ID
                    JOIN C_BPartner cb ON cb.C_BPartner_ID = xf.CLIENTID
                    JOIN C_BPartner_Location bpl ON bpl.C_BPartner_ID = xf.CLIENTID
                    JOIN C_SalesRegion sr ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    JOIN C_ORDER c ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                          AND xf.AD_Org_ID = 1000000
                          AND xf.DOCSTATUS != 'RE'
                          AND (:fournisseur IS NULL OR UPPER(xf.name) LIKE UPPER(:fournisseur) || '%')
                          AND (:product IS NULL OR UPPER(xf.product) LIKE UPPER(:product) || '%')
                          AND (:client IS NULL OR UPPER(cb.name) LIKE UPPER(:client) || '%')
                          AND (:operateur IS NULL OR UPPER(au.name) LIKE UPPER(:operateur) || '%')
                          AND (:zone IS NULL OR UPPER(sr.name) LIKE UPPER(:zone) || '%')
                          AND (:bccb IS NULL OR UPPER(c.DOCUMENTNO) LIKE UPPER(:bccb) || '%')
                    GROUP BY au.name

                    UNION ALL

                    SELECT 'Total' AS "OPERATEUR", 
                           SUM(xf.TOTALLINE) AS "TOTAL", 
                           SUM(xf.qtyentered) AS "QTY",
                           CASE 
                               WHEN SUM(xf.CONSOMATION) = 0 THEN 0
                               WHEN SUM(xf.CONSOMATION) < 0 THEN ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION) * -1)
                               ELSE (SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION)
                           END AS "MARGE",
                           1 AS "SORT_ORDER"
                    FROM AD_User au
                    JOIN xx_ca_fournisseur xf ON au.AD_User_ID = xf.SALESREP_ID
                    JOIN C_BPartner cb ON cb.C_BPartner_ID = xf.CLIENTID
                    JOIN C_BPartner_Location bpl ON bpl.C_BPartner_ID = xf.CLIENTID
                    JOIN C_SalesRegion sr ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    JOIN C_ORDER c ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                          AND xf.AD_Org_ID = 1000000
                          AND xf.DOCSTATUS != 'RE'
                          AND (:fournisseur IS NULL OR UPPER(xf.name) LIKE UPPER(:fournisseur) || '%')
                          AND (:product IS NULL OR UPPER(xf.product) LIKE UPPER(:product) || '%')
                          AND (:client IS NULL OR UPPER(cb.name) LIKE UPPER(:client) || '%')
                          AND (:operateur IS NULL OR UPPER(au.name) LIKE UPPER(:operateur) || '%')
                          AND (:zone IS NULL OR UPPER(sr.name) LIKE UPPER(:zone) || '%')
                          AND (:bccb IS NULL OR UPPER(c.DOCUMENTNO) LIKE UPPER(:bccb) || '%')
                ) ORDER BY "SORT_ORDER", "TOTAL" DESC
            """
            params = {
                'start_date': start_date,
                'end_date': end_date,
                'fournisseur': fournisseur or None,
                'product': product or None,
                'client': client or None,
                'operateur': operateur or None,
                'bccb': bccb or None,
                'zone': zone or None
            }

            cursor.execute(query, params)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            return [dict(zip(columns, row)) for row in rows]
    except Exception as e:
        logger.error(f"Error fetching operator recap: {e}")
        return {"error": "An error occurred while fetching operator recap."}
def fetch_bccb_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT c.DOCUMENTNO, c.DATEORDERED, c.GRANDTOTAL 
                FROM C_Order c
                JOIN M_InOut mi ON mi.C_ORDER_ID = c.C_ORDER_ID
                JOIN xx_ca_fournisseur xf ON xf.DOCUMENTNO = mi.DOCUMENTNO
                JOIN C_BPartner cb ON cb.C_BPartner_ID = xf.CLIENTID
                JOIN AD_User au ON au.AD_User_ID = xf.SALESREP_ID
                JOIN C_BPartner_Location bpl ON bpl.C_BPartner_ID = xf.CLIENTID
                JOIN C_SalesRegion sr ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                WHERE c.AD_Org_ID = 1000000
                      AND xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                      AND (c.DOCSTATUS = 'CL' OR c.DOCSTATUS = 'CO')
                      AND c.C_DocType_ID IN (1000539, 1001408)
                      AND (:bccb IS NULL OR UPPER(c.DOCUMENTNO) LIKE UPPER(:bccb) || '%')
                      AND (:fournisseur IS NULL OR UPPER(xf.name) LIKE UPPER(:fournisseur) || '%')
                      AND (:product IS NULL OR UPPER(xf.product) LIKE UPPER(:product) || '%')
                      AND (:client IS NULL OR UPPER(cb.name) LIKE UPPER(:client) || '%')
                      AND (:operateur IS NULL OR UPPER(au.name) LIKE UPPER(:operateur) || '%')
                      AND (:zone IS NULL OR UPPER(sr.name) LIKE UPPER(:zone) || '%')
                GROUP BY c.DOCUMENTNO, c.DATEORDERED, c.GRANDTOTAL 
                ORDER BY c.DOCUMENTNO
            """
            params = {
                'start_date': start_date,
                'end_date': end_date,
                'fournisseur': fournisseur or None,
                'product': product or None,
                'client': client or None,
                'operateur': operateur or None,
                'bccb': bccb or None,
                'zone': zone or None
            }
            
            cursor.execute(query, params)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            return [dict(zip(columns, row)) for row in rows]
    except Exception as e:
        logger.error(f"Error fetching BCCB recap: {e}")
        return {"error": "An error occurred while fetching BCCB recap."}

def fetch_zone_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT * FROM (
                    -- Recap by Zone
                    SELECT 
                        CAST(sr.name AS VARCHAR2(100)) AS "ZONE",
                        SUM(xf.TOTALLINE) AS "TOTAL",
                        SUM(xf.qtyentered) AS "QTY",
                        CASE 
                            WHEN SUM(xf.CONSOMATION) = 0 THEN 0
                            ELSE ROUND((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / NULLIF(SUM(xf.CONSOMATION), 0), 4)
                        END AS "MARGE",
                        0 AS "SORT_ORDER"
                    FROM C_SalesRegion sr
                    JOIN C_BPartner_Location bpl ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    JOIN xx_ca_fournisseur xf ON bpl.C_BPartner_ID = xf.CLIENTID
                    JOIN C_BPartner cb ON cb.C_BPartner_ID = xf.CLIENTID
                    JOIN AD_User au ON au.AD_User_ID = xf.SALESREP_ID
                    JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    JOIN C_ORDER c ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                          AND xf.AD_Org_ID = 1000000
                          AND xf.DOCSTATUS != 'RE'
                          AND (:fournisseur IS NULL OR UPPER(xf.name) LIKE UPPER(:fournisseur) || '%')
                          AND (:product IS NULL OR UPPER(xf.product) LIKE UPPER(:product) || '%')
                          AND (:client IS NULL OR UPPER(cb.name) LIKE UPPER(:client) || '%')
                          AND (:operateur IS NULL OR UPPER(au.name) LIKE UPPER(:operateur) || '%')
                          AND (:bccb IS NULL OR UPPER(c.DOCUMENTNO) LIKE UPPER(:bccb) || '%')
                          AND (:zone IS NULL OR UPPER(sr.name) LIKE UPPER(:zone) || '%')
                    GROUP BY sr.name

                    UNION ALL

                    -- Total Row
                    SELECT 
                        'Total' AS "ZONE",
                        SUM(xf.TOTALLINE) AS "TOTAL",
                        SUM(xf.qtyentered) AS "QTY",
                        CASE 
                            WHEN SUM(xf.CONSOMATION) = 0 THEN 0
                            ELSE ROUND((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / NULLIF(SUM(xf.CONSOMATION), 0), 4)
                        END AS "MARGE",
                        1 AS "SORT_ORDER"
                    FROM C_SalesRegion sr
                    JOIN C_BPartner_Location bpl ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    JOIN xx_ca_fournisseur xf ON bpl.C_BPartner_ID = xf.CLIENTID
                    JOIN C_BPartner cb ON cb.C_BPartner_ID = xf.CLIENTID
                    JOIN AD_User au ON au.AD_User_ID = xf.SALESREP_ID
                    JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    JOIN C_ORDER c ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                          AND xf.AD_Org_ID = 1000000
                          AND xf.DOCSTATUS != 'RE'
                          AND (:fournisseur IS NULL OR UPPER(xf.name) LIKE UPPER(:fournisseur) || '%')
                          AND (:product IS NULL OR UPPER(xf.product) LIKE UPPER(:product) || '%')
                          AND (:client IS NULL OR UPPER(cb.name) LIKE UPPER(:client) || '%')
                          AND (:operateur IS NULL OR UPPER(au.name) LIKE UPPER(:operateur) || '%')
                          AND (:bccb IS NULL OR UPPER(c.DOCUMENTNO) LIKE UPPER(:bccb) || '%')
                          AND (:zone IS NULL OR UPPER(sr.name) LIKE UPPER(:zone) || '%')
                ) ORDER BY "SORT_ORDER", "TOTAL" DESC
            """
            params = {
                'start_date': start_date,
                'end_date': end_date,
                'fournisseur': fournisseur or None,
                'product': product or None,
                'client': client or None,
                'operateur': operateur or None,
                'bccb': bccb or None,
                'zone': zone or None
            }
            
            cursor.execute(query, params)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            return [dict(zip(columns, row)) for row in rows]
    except Exception as e:
        logger.error(f"Error fetching zone recap: {e}")
        return {"error": "An error occurred while fetching zone recap."}


def fetch_client_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT * FROM (
                    -- Recap by Client
                    SELECT 
                        CAST(cb.name AS VARCHAR2(100)) AS "CLIENT",
                        SUM(xf.TOTALLINE) AS "TOTAL",
                        SUM(xf.qtyentered) AS "QTY",
                        CASE 
                            WHEN SUM(xf.CONSOMATION) = 0 THEN 0
                            ELSE ROUND((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / NULLIF(SUM(xf.CONSOMATION), 0), 4)
                        END AS "MARGE",
                        0 AS "SORT_ORDER"
                    FROM C_BPartner cb
                    JOIN xx_ca_fournisseur xf ON cb.C_BPartner_ID = xf.CLIENTID
                    JOIN AD_User au ON au.AD_User_ID = xf.SALESREP_ID
                    JOIN C_BPartner_Location bpl ON bpl.C_BPartner_ID = xf.CLIENTID
                    JOIN C_SalesRegion sr ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    JOIN C_ORDER c ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                          AND xf.AD_Org_ID = 1000000
                          AND xf.DOCSTATUS != 'RE'
                          AND (:fournisseur IS NULL OR UPPER(xf.name) LIKE UPPER(:fournisseur) || '%')
                          AND (:product IS NULL OR UPPER(xf.product) LIKE UPPER(:product) || '%')
                          AND (:client IS NULL OR UPPER(cb.name) LIKE UPPER(:client) || '%')
                          AND (:operateur IS NULL OR UPPER(au.name) LIKE UPPER(:operateur) || '%')
                          AND (:bccb IS NULL OR UPPER(c.DOCUMENTNO) LIKE UPPER(:bccb) || '%')
                          AND (:zone IS NULL OR UPPER(sr.name) LIKE UPPER(:zone) || '%')
                    GROUP BY cb.name

                    UNION ALL

                    -- Total Row
                    SELECT 
                        'Total' AS "CLIENT",
                        SUM(xf.TOTALLINE) AS "TOTAL",
                        SUM(xf.qtyentered) AS "QTY",
                        CASE 
                            WHEN SUM(xf.CONSOMATION) = 0 THEN 0
                            ELSE ROUND((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / NULLIF(SUM(xf.CONSOMATION), 0), 4)
                        END AS "MARGE",
                        1 AS "SORT_ORDER"
                    FROM C_BPartner cb
                    JOIN xx_ca_fournisseur xf ON cb.C_BPartner_ID = xf.CLIENTID
                    JOIN AD_User au ON au.AD_User_ID = xf.SALESREP_ID
                    JOIN C_BPartner_Location bpl ON bpl.C_BPartner_ID = xf.CLIENTID
                    JOIN C_SalesRegion sr ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    JOIN C_ORDER c ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                          AND xf.AD_Org_ID = 1000000
                          AND xf.DOCSTATUS != 'RE'
                          AND (:fournisseur IS NULL OR UPPER(xf.name) LIKE UPPER(:fournisseur) || '%')
                          AND (:product IS NULL OR UPPER(xf.product) LIKE UPPER(:product) || '%')
                          AND (:client IS NULL OR UPPER(cb.name) LIKE UPPER(:client) || '%')
                          AND (:operateur IS NULL OR UPPER(au.name) LIKE UPPER(:operateur) || '%')
                          AND (:bccb IS NULL OR UPPER(c.DOCUMENTNO) LIKE UPPER(:bccb) || '%')
                          AND (:zone IS NULL OR UPPER(sr.name) LIKE UPPER(:zone) || '%')
                ) ORDER BY "SORT_ORDER", "TOTAL" DESC
            """
            params = {
                'start_date': start_date,
                'end_date': end_date,
                'fournisseur': fournisseur or None,
                'product': product or None,
                'client': client or None,
                'operateur': operateur or None,
                'bccb': bccb or None,
                'zone': zone or None
            }
            
            cursor.execute(query, params)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            return [dict(zip(columns, row)) for row in rows]
    except Exception as e:
        logger.error(f"Error fetching client recap: {e}")
        return {"error": "An error occurred while fetching client recap."}

@app.route('/fetchClientRecap', methods=['GET'])
def fetch_client():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    data = fetch_client_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)
    return jsonify(data)

@app.route('/fetchBCCBRecap', methods=['GET'])
def fetch_bccb():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    data = fetch_bccb_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)
    return jsonify(data)

@app.route('/fetchOperatorRecap', methods=['GET'])
def fetch_operator():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    data = fetch_operator_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)
    return jsonify(data)

@app.route('/fetchProductData', methods=['GET'])
def fetch_product():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    data = fetch_product_data(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)
    return jsonify(data)

@app.route('/fetchZoneRecap', methods=['GET'])
def fetch_zone():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    data = fetch_zone_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)
    return jsonify(data)

@app.route('/fetchTotalrecapData', methods=['GET'])
def fetch_recap():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    data = fetch_rcap_data(start_date, end_date)
    return jsonify(data)

@app.route('/fetchFournisseurData', methods=['GET'])
def fetch_fournisseur():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    data = fetch_fournisseur_data(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)
    return jsonify(data)


@app.route('/download-totalrecap-excel', methods=['GET'])
def download_totalrecap_excel():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data from the database
    data = fetch_rcap_data(start_date, end_date)

    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Generate a filename with the selected parameters
    today_date = datetime.now().strftime("%d-%m-%Y")
    filename = f"TotalRecap_{start_date}_to_{end_date}_{today_date}.xlsx"

    return generate_excel_totalrecap(data, filename)

def generate_excel_totalrecap(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    wb = Workbook()
    ws = wb.active
    ws.title = "Total Recap"

    # Formatting headers
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

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
    table = Table(displayName="TotalRecapTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    # Save the Excel file in memory
    output = BytesIO()
    wb.save(output)
    output.seek(0)

    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
@app.route('/download-fournisseur-excel', methods=['GET'])
def download_fournisseur_excel():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data
    data = fetch_fournisseur_data(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)

    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Generate filename
    today_date = datetime.now().strftime("%d-%m-%Y")
    filename = f"FournisseurRecap_{start_date}_to_{end_date}_{today_date}.xlsx"

    return generate_excel_fournisseur(data, filename)

def generate_excel_fournisseur(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    wb = Workbook()
    ws = wb.active
    ws.title = "Fournisseur Recap"

    # Formatting headers
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

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
    table = Table(displayName="FournisseurRecapTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    # Save the Excel file in memory
    output = BytesIO()
    wb.save(output)
    output.seek(0)

    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")


@app.route('/download-product-excel', methods=['GET'])
def download_product_excel():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data
    data = fetch_product_data(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)

    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Generate filename
    today_date = datetime.now().strftime("%d-%m-%Y")
    filename = f"ProductRecap_{start_date}_to_{end_date}_{today_date}.xlsx"

    return generate_excel_product(data, filename)


def generate_excel_product(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    wb = Workbook()
    ws = wb.active
    ws.title = "Product Recap"

    # Formatting headers
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

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
    table = Table(displayName="ProductRecapTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    # Save the Excel file in memory
    output = BytesIO()
    wb.save(output)
    output.seek(0)

    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")



@app.route('/download-zone-excel', methods=['GET'])
def download_zone_excel():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data
    data = fetch_zone_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)

    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Generate filename
    today_date = datetime.now().strftime("%d-%m-%Y")
    filename = f"ZoneRecap_{start_date}_to_{end_date}_{today_date}.xlsx"

    return generate_excel_zone(data, filename)

def generate_excel_zone(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    wb = Workbook()
    ws = wb.active
    ws.title = "Zone Recap"

    # Formatting headers
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

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
    table = Table(displayName="ZoneRecapTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    # Save the Excel file in memory
    output = BytesIO()
    wb.save(output)
    output.seek(0)

    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")

@app.route('/download-client-excel', methods=['GET'])
def download_client_excel():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data
    data = fetch_client_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)

    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Generate filename
    today_date = datetime.now().strftime("%d-%m-%Y")
    filename = f"ClientRecap_{start_date}_to_{end_date}_{today_date}.xlsx"

    return generate_excel_client(data, filename)

def generate_excel_client(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    wb = Workbook()
    ws = wb.active
    ws.title = "Client Recap"

    # Formatting headers
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

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
    table = Table(displayName="ClientRecapTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    # Save the Excel file in memory
    output = BytesIO()
    wb.save(output)
    output.seek(0)

    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")

@app.route('/download-operator-excel', methods=['GET'])
def download_operator_excel():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data
    data = fetch_operator_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)

    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Generate filename
    today_date = datetime.now().strftime("%d-%m-%Y")
    filename = f"OperatorRecap_{start_date}_to_{end_date}_{today_date}.xlsx"

    return generate_excel_operator(data, filename)

def generate_excel_operator(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    wb = Workbook()
    ws = wb.active
    ws.title = "Operator Recap"

    # Formatting headers
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

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
    table = Table(displayName="OperatorRecapTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    # Save the Excel file in memory
    output = BytesIO()
    wb.save(output)
    output.seek(0)

    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")


@app.route('/download-bccb-excel', methods=['GET'])
def download_bccb_excel():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
    client = request.args.get('client')
    operateur = request.args.get('operateur')
    bccb = request.args.get('bccb')
    zone = request.args.get('zone')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data
    data = fetch_bccb_recap(start_date, end_date, fournisseur, product, client, operateur, bccb, zone)

    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Generate filename
    today_date = datetime.now().strftime("%d-%m-%Y")
    filename = f"BCCBRecap_{start_date}_to_{end_date}_{today_date}.xlsx"

    return generate_excel_bccb(data, filename)

def generate_excel_bccb(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    wb = Workbook()
    ws = wb.active
    ws.title = "BCCB Recap"

    # Formatting headers
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

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
    table = Table(displayName="BCCBRecapTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    # Save the Excel file in memory
    output = BytesIO()
    wb.save(output)
    output.seek(0)

    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")

def fetch_total_recap_achat(start_date, end_date, fournisseur, product):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT
                    SUM(CASE 
                        WHEN xf.C_DocType_ID = 1000646 THEN -1 * TO_NUMBER(ma.valuenumber) * TO_NUMBER(mi.QTYENTERED) 
                        ELSE TO_NUMBER(ma.valuenumber) * TO_NUMBER(mi.QTYENTERED) 
                    END) AS chiffre
                FROM M_InOut xf
                JOIN M_InOutline mi ON mi.M_INOUT_ID = xf.M_INOUT_ID
                JOIN C_BPartner cb ON cb.C_BPARTNER_ID = xf.C_BPARTNER_ID
                LEFT JOIN C_InvoiceLine ci ON ci.M_INOUTLINE_ID = mi.M_INOUTLINE_ID
                JOIN M_ATTRIBUTEINSTANCE ma ON ma.M_ATTRIBUTESETINSTANCE_ID = mi.M_ATTRIBUTESETINSTANCE_ID
                JOIN M_PRODUCT m ON m.M_PRODUCT_id = mi.M_PRODUCT_id
                WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                    AND ma.M_Attribute_ID = 1000504
                    AND xf.AD_Org_ID = 1000000
                    AND xf.C_DocType_ID IN (1000013, 1000646)
                    AND xf.M_Warehouse_ID IN (1000724, 1000000, 1000720, 1000725)
                    AND (:fournisseur IS NULL OR UPPER(cb.name) LIKE UPPER(:fournisseur) || '%')
                    AND (:product IS NULL OR UPPER(m.name) LIKE UPPER(:product) || '%')
            """
            params = {
                'start_date': start_date,
                'end_date': end_date,
                'fournisseur': fournisseur or None,
                'product': product or None
            }

            cursor.execute(query, params)
            row = cursor.fetchone()
            
            return {"chiffre": row[0] if row and row[0] is not None else 0}
    
    except Exception as e:
        logger.error(f"Error fetching total recap achat: {e}")
        return {"error": "An error occurred while fetching total recap achat."}
@app.route('/fetchTotalRecapAchat', methods=['GET'])
def fetch_total_achat():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    data = fetch_total_recap_achat(start_date, end_date, fournisseur, product)
    return jsonify(data)
def fetch_fournisseur_recap_achat(start_date, end_date, fournisseur, product):
    try:
        # Acquire a connection from the pool
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
                SELECT
                    CAST(cb.name AS VARCHAR2(300)) AS FOURNISSEUR,   
                    SUM(CASE 
                        WHEN xf.C_DocType_ID = 1000646 THEN -1 * TO_NUMBER(ma.valuenumber) * TO_NUMBER(mi.QTYENTERED) 
                        ELSE TO_NUMBER(ma.valuenumber) * TO_NUMBER(mi.QTYENTERED) 
                    END) AS chiffre,
                    0 AS sort_order
                FROM 
                    M_InOut  xf
                JOIN M_INOUTLINE mi ON mi.M_INOUT_ID=xf.M_INOUT_ID
                JOIN C_BPartner cb ON cb.C_BPARTNER_ID=xf.C_BPARTNER_ID
                LEFT JOIN C_InvoiceLine ci ON ci.M_INOUTLINE_ID=mi.M_INOUTLINE_ID
                JOIN M_ATTRIBUTEINSTANCE ma ON ma.M_ATTRIBUTESETINSTANCE_ID=mi.M_ATTRIBUTESETINSTANCE_ID
                JOIN M_PRODUCT m ON m.M_PRODUCT_id=mi.M_PRODUCT_id
                WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                    AND ma.M_Attribute_ID=1000504
                    AND xf.AD_Org_ID = 1000000
                    AND xf.C_DocType_ID IN (1000013, 1000646)
                    AND xf.M_Warehouse_ID IN (1000724, 1000000, 1000720, 1000725)
                    AND (:fournisseur IS NULL OR UPPER(cb.name) LIKE UPPER(:fournisseur) || '%')
                    AND (:product IS NULL OR UPPER(m.name) LIKE UPPER(:product) || '%')
                GROUP BY 
                    cb.name

                UNION ALL

                SELECT
                    CAST('Total' AS VARCHAR2(300)) AS FOURNISSEUR, 
                    SUM(CASE 
                        WHEN xf.C_DocType_ID = 1000646 THEN -1 * TO_NUMBER(ma.valuenumber) * TO_NUMBER(mi.QTYENTERED) 
                        ELSE TO_NUMBER(ma.valuenumber) * TO_NUMBER(mi.QTYENTERED) 
                    END) AS chiffre,
                    1 AS sort_order
                FROM 
                    M_InOut  xf
                JOIN M_INOUTLINE mi ON mi.M_INOUT_ID=xf.M_INOUT_ID
                JOIN C_BPartner cb ON cb.C_BPARTNER_ID=xf.C_BPARTNER_ID
                LEFT JOIN C_InvoiceLine ci ON ci.M_INOUTLINE_ID=mi.M_INOUTLINE_ID
                JOIN M_ATTRIBUTEINSTANCE ma ON ma.M_ATTRIBUTESETINSTANCE_ID=mi.M_ATTRIBUTESETINSTANCE_ID
                JOIN M_PRODUCT m ON m.M_PRODUCT_id=mi.M_PRODUCT_id
                WHERE xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                    AND ma.M_Attribute_ID=1000504
                    AND xf.AD_Org_ID = 1000000
                    AND xf.C_DocType_ID IN (1000013, 1000646)
                    AND xf.M_Warehouse_ID IN (1000724, 1000000, 1000720, 1000725)
                    AND (:fournisseur IS NULL OR UPPER(cb.name) LIKE UPPER(:fournisseur) || '%')
                    AND (:product IS NULL OR UPPER(m.name) LIKE UPPER(:product) || '%')
            """

            params = {
                'start_date': start_date,
                'end_date': end_date,
                'fournisseur': fournisseur or None,
                'product': product or None
            }

            # Execute the query with the provided parameters
            cursor.execute(query, params)

            # Fetch the results
            rows = cursor.fetchall()

            # Format the results into a list of dictionaries
            data = [{"FOURNISSEUR": row[0], "CHIFFRE": row[1], "SORT_ORDER": row[2]} for row in rows]

            return data
    
    except Exception as e:
        logger.error(f"Error fetching fournisseur recap achat: {e}")
        return {"error": "An error occurred while fetching fournisseur recap achat."}

# Flask route to handle the request
@app.route('/fetchfourisseurRecapAchat', methods=['GET'])
def fetch_fournisseur_achat():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')

    # Ensure both start_date and end_date are provided
    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data from the database
    data = fetch_fournisseur_recap_achat(start_date, end_date, fournisseur, product)

    # Return the result as a JSON response
    return jsonify(data)


def fetch_product_achat_recap(start_date, end_date, fournisseur, product):
    try:
        # Acquire a connection from the pool
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
                SELECT 
                    CAST(m.name AS VARCHAR2(300)) AS produit,   
                    SUM(CASE 
                            WHEN xf.C_DocType_ID = 1000646 THEN -1 * TO_NUMBER(mi.QTYENTERED)
                            ELSE TO_NUMBER(mi.QTYENTERED)
                        END) AS qty,
                    
                    SUM(CASE 
                            WHEN xf.C_DocType_ID = 1000646 THEN -1 * TO_NUMBER(ma.valuenumber) * TO_NUMBER(mi.QTYENTERED)
                            ELSE TO_NUMBER(ma.valuenumber) * TO_NUMBER(mi.QTYENTERED)
                        END) AS chiffre,
                    0 AS sort_order
                FROM 
                    M_InOut xf
                    JOIN M_INOUTLINE mi ON mi.M_INOUT_ID = xf.M_INOUT_ID
                    JOIN M_ATTRIBUTEINSTANCE ma ON ma.M_ATTRIBUTESETINSTANCE_ID = mi.M_ATTRIBUTESETINSTANCE_ID
                    JOIN C_BPartner cb ON cb.C_BPARTNER_ID = xf.C_BPARTNER_ID
                    JOIN M_PRODUCT m ON m.M_PRODUCT_id = mi.M_PRODUCT_id
                WHERE 
                    xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                    AND xf.AD_Org_ID = 1000000
                    AND xf.C_DocType_ID IN (1000013, 1000646)
                    AND ma.M_Attribute_ID = 1000504
                    AND xf.DOCSTATUS = 'CO'
                    AND xf.M_Warehouse_ID IN (1000724, 1000000, 1000720, 1000725)
                    AND (:fournisseur IS NULL OR UPPER(cb.name) LIKE UPPER(:fournisseur) || '%')
                    AND (:product IS NULL OR UPPER(m.name) LIKE UPPER(:product) || '%')
                GROUP BY 
                    m.name

                UNION ALL

                SELECT
                    CAST('Total' AS VARCHAR2(300)) AS produit, 
                    SUM(CASE 
                            WHEN xf.C_DocType_ID = 1000646 THEN -1 * TO_NUMBER(mi.QTYENTERED)
                            ELSE TO_NUMBER(mi.QTYENTERED)
                        END) AS qty,
                    SUM(CASE 
                            WHEN xf.C_DocType_ID = 1000646 THEN -1 * TO_NUMBER(ma.valuenumber) * TO_NUMBER(mi.QTYENTERED)
                            ELSE TO_NUMBER(ma.valuenumber) * TO_NUMBER(mi.QTYENTERED)
                        END) AS chiffre,
                    1 AS sort_order
                FROM 
                    M_InOut xf
                    JOIN M_INOUTLINE mi ON mi.M_INOUT_ID = xf.M_INOUT_ID
                    JOIN M_ATTRIBUTEINSTANCE ma ON ma.M_ATTRIBUTESETINSTANCE_ID = mi.M_ATTRIBUTESETINSTANCE_ID
                    JOIN C_BPartner cb ON cb.C_BPARTNER_ID = xf.C_BPARTNER_ID
                    JOIN M_PRODUCT m ON m.M_PRODUCT_id = mi.M_PRODUCT_id
                WHERE 
                    xf.MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                                          AND TO_DATE(:end_date, 'YYYY-MM-DD')
                    AND xf.AD_Org_ID = 1000000
                    AND xf.C_DocType_ID IN (1000013, 1000646)
                    AND ma.M_Attribute_ID = 1000504
                    AND xf.DOCSTATUS = 'CO'
                    AND xf.M_Warehouse_ID IN (1000724, 1000000, 1000720, 1000725)
                    AND (:fournisseur IS NULL OR UPPER(cb.name) LIKE UPPER(:fournisseur) || '%')
                    AND (:product IS NULL OR UPPER(m.name) LIKE UPPER(:product) || '%')
            """

            params = {
                'start_date': start_date,
                'end_date': end_date,
                'fournisseur': fournisseur or None,
                'product': product or None
            }

            # Execute the query with the provided parameters
            cursor.execute(query, params)

            # Fetch the results
            rows = cursor.fetchall()

            # Format the results into a list of dictionaries
            data = [{"PRODUIT": row[0], "QTY": row[1], "CHIFFRE": row[2], "SORT_ORDER": row[3]} for row in rows]

            return data
    
    except Exception as e:
        logger.error(f"Error fetching product recap achat: {e}")
        return {"error": "An error occurred while fetching product recap achat."}

@app.route('/fetchProductRecapAchat', methods=['GET'])
def fetch_product_achat():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')

    # Ensure both start_date and end_date are provided
    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data from the database
    data = fetch_product_achat_recap(start_date, end_date, fournisseur, product)

    # Return the result as a JSON response
    return jsonify(data)


@app.route('/download-recap-product-achat-excel', methods=['GET'])
def download_recap_product_achat_excel():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')
 

    # Ensure both start and end dates are provided
    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch the data using the fetch_product_achat_recap function
    data = fetch_product_achat_recap(start_date, end_date, fournisseur, product)

    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Generate filename
    today_date = datetime.now().strftime("%d-%m-%Y")
    filename = f"ProductRecapAchat_{start_date}_to_{end_date}_{today_date}.xlsx"

    # Generate and return the Excel file
    return generate_excel_product_achat(data, filename)

def generate_excel_product_achat(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Convert the data into a DataFrame
    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    # Create an Excel workbook
    wb = Workbook()
    ws = wb.active
    ws.title = "Product Recap"

    # Formatting headers
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

    # Add headers to the sheet
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

    # Add an Excel table for better presentation
    table = Table(displayName="ProductRecapTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    # Save the Excel file in memory
    output = BytesIO()
    wb.save(output)
    output.seek(0)

    # Send the file to the client for download
    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")


@app.route('/download-recap-fournisseur-achat-excel', methods=['GET'])
def download_recap_fournisseur_achat_excel():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    fournisseur = request.args.get('fournisseur')
    product = request.args.get('product')

    # Ensure both start and end dates are provided
    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data using the fetch_fournisseur_recap_achat function
    data = fetch_fournisseur_recap_achat(start_date, end_date, fournisseur, product)

    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Generate filename
    today_date = datetime.now().strftime("%d-%m-%Y")
    filename = f"FournisseurRecapAchat_{start_date}_to_{end_date}_{today_date}.xlsx"

    # Generate and return the Excel file
    return generate_excel_fournisseur_achat(data, filename)

def generate_excel_fournisseur_achat(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Convert the data into a DataFrame
    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    # Create an Excel workbook
    wb = Workbook()
    ws = wb.active
    ws.title = "Fournisseur Recap"

    # Formatting headers
    header_fill = PatternFill(start_color="4F81BD", end_color="4F81BD", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

    # Add headers to the sheet
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

    # Add an Excel table for better presentation
    table = Table(displayName="FournisseurRecapAchatTable", ref=ws.dimensions)
    style = TableStyleInfo(
        name="TableStyleMedium9",
        showFirstColumn=False,
        showLastColumn=False,
        showRowStripes=True,
        showColumnStripes=False
    )
    table.tableStyleInfo = style
    ws.add_table(table)

    # Save the Excel file in memory
    output = BytesIO()
    wb.save(output)
    output.seek(0)

    # Send the file to the client for download
    return send_file(output, as_attachment=True, download_name=filename, mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000)