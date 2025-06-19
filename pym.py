
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



@app.route('/listproduct')
def listproduct():
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
            result = [row[0] for row in cursor.fetchall()]
            return jsonify(result)
    except Exception as e:
        logger.error(f"Error fetching products: {e}")
        return jsonify({"error": "Could not fetch products list"}), 500
    


@app.route('/download-journal-vente-excel', methods=['GET'])
def download_journal_vente_excel():
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    client = request.args.get('client', 'All_Clients')

    if not start_date or not end_date:
        return jsonify({"error": "Missing start_date or end_date parameters"}), 400

    # Fetch data
    data = journal_vente(start_date, end_date, client)

    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    # Generate filename
    download_datetime = datetime.now().strftime("%d-%m-%Y_%H-%M")
    sanitized_client = client.replace(" ", "_").replace("/", "-")
    filename = f"JournalVente_{sanitized_client}_{start_date}_to_{end_date}_{download_datetime}.xlsx"

    return generate_excel_journal(data, filename)


def generate_excel_journal(data, filename):
    if not data or "error" in data:
        return jsonify({"error": "No data available"}), 400

    df = pd.DataFrame(data)
    if df.empty:
        return jsonify({"error": "No data available"}), 400

    wb = Workbook()
    ws = wb.active
    ws.title = "Journal Vente"

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
    table = Table(displayName="JournalVenteTable", ref=ws.dimensions)
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




def journal_vente(start_date, end_date, client):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
            SELECT ci.DocumentNo,
                   ci.DateInvoiced,
                   cb.name as client,
                   CASE WHEN ci.isreturntrx = 'Y'
                        THEN -(ci.GRANDTOTAL - getinvoicetaxamt(ci.c_invoice_id)) 
                        ELSE ci.GRANDTOTAL - getinvoicetaxamt(ci.c_invoice_id) 
                   END as TotalHT,
                   CASE WHEN ci.isreturntrx = 'Y'           
                        THEN -getinvoicetaxamt(ci.c_invoice_id) 
                        ELSE getinvoicetaxamt(ci.c_invoice_id) 
                   END as TotalTVA,
                   CASE WHEN ci.isreturntrx = 'Y'
                        THEN -ci.chargeamt 
                        ELSE ci.chargeamt 
                   END as TotalDT,
                   CASE WHEN ci.isreturntrx = 'Y'
                        THEN -ci.GRANDTOTAL 
                        ELSE ci.GRANDTOTAL 
                   END as TotalTTC,
                   CASE WHEN ci.isreturntrx = 'Y'
                        THEN -ci.GRANDTOTAL - ci.chargeamt 
                        ELSE ci.GRANDTOTAL + ci.chargeamt 
                   END as NETAPAYER,   
                   sr.name as region,
                   adc.name as Entreprise,
                   COALESCE((
                        SELECT sum(tax.taxbaseamt)
                        FROM C_InvoiceTax tax 
                        WHERE tax.C_Invoice_ID = ci.C_Invoice_ID 
                        AND tax.taxamt = 0
                    ), 0) as Montant_Exonere
            FROM C_INVOICE ci
            INNER JOIN AD_CLIENT adc ON (adc.ad_client_id = ci.ad_client_id)
            INNER JOIN AD_ORG ado ON (ado.ad_org_id = ci.ad_org_id)
            INNER JOIN C_BPARTNER cb ON (cb.c_bpartner_id = ci.c_bpartner_id) 
            INNER JOIN C_BPARTNER_Location bpl ON (bpl.C_BPARTNER_Location_id = ci.C_BPARTNER_Location_id) 
            LEFT OUTER JOIN c_salesregion sr ON (bpl.C_SalesRegion_ID = sr.C_SalesRegion_ID)
            WHERE ci.dateInvoiced BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                                     AND TO_DATE(:end_date, 'YYYY-MM-DD')
            AND ci.ad_Org_id = 1000012
            AND ci.ISSOTRX = 'Y' 
            AND ci.docstatus IN ('CO', 'CL')
            AND ci.c_doctype_id IN (SELECT c_doctype_id FROM c_doctype WHERE Xx_Excluejournalvente = 'N')
            """

            params = {
                'start_date': start_date,
                'end_date': end_date,
            }

            if client:
                query += " AND cb.name LIKE :client"
                params['client'] = f"%{client}%"

            query += " ORDER BY ci.DateInvoiced"

            cursor.execute(query, params)
            rows = cursor.fetchall()

            data = [
                {
                    "DocumentNo": row[0],
                    "DateInvoiced": row[1],
                    "Client": row[2],
                    "TotalHT": row[3],
                    "TotalTVA": row[4],
                    "TotalDT": row[5],
                    "TotalTTC": row[6],
                    "NETAPAYER": row[7],
                    "Region": row[8],
                    "Entreprise": row[9],
                    "Montant_Exonere": row[10]
                }
                for row in rows
            ]

            return data

    except Exception as e:
        logger.error(f"Error fetching journal de vente: {e}")
        return {"error": "An error occurred while fetching sales journal data."}



@app.route('/journalVente', methods=['GET'])
def fetch_journal():
    client = request.args.get('client')
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')

    data = journal_vente(start_date, end_date, client)
    return jsonify(data)

def total_journal(start_date, end_date, client):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
            SELECT 
                SUM(CASE 
                    WHEN ci.isreturntrx = 'Y' THEN -(ci.GRANDTOTAL - getinvoicetaxamt(ci.c_invoice_id)) 
                    ELSE ci.GRANDTOTAL - getinvoicetaxamt(ci.c_invoice_id) 
                END) AS TotalHT,
                SUM(CASE 
                    WHEN ci.isreturntrx = 'Y' THEN -getinvoicetaxamt(ci.c_invoice_id) 
                    ELSE getinvoicetaxamt(ci.c_invoice_id) 
                END) AS TotalTVA,
                SUM(CASE 
                    WHEN ci.isreturntrx = 'Y' THEN -ci.chargeamt 
                    ELSE ci.chargeamt 
                END) AS TotalDT,
                SUM(CASE 
                    WHEN ci.isreturntrx = 'Y' THEN -ci.GRANDTOTAL  
                    ELSE ci.GRANDTOTAL 
                END) AS TotalTTC,
                SUM(CASE 
                    WHEN ci.isreturntrx = 'Y' THEN -ci.GRANDTOTAL - ci.chargeamt
                    ELSE ci.GRANDTOTAL + ci.chargeamt
                END) AS NETAPAYER
            FROM C_INVOICE ci
            INNER JOIN AD_CLIENT adc ON adc.ad_client_id = ci.ad_client_id
            INNER JOIN AD_ORG ado ON ado.ad_org_id = ci.ad_org_id
            INNER JOIN C_BPARTNER cb ON cb.c_bpartner_id = ci.c_bpartner_id 
            INNER JOIN C_BPARTNER_Location bpl ON bpl.C_BPARTNER_Location_id = ci.C_BPARTNER_Location_id 
            LEFT OUTER JOIN C_SALESREGION sr ON bpl.C_SalesRegion_ID = sr.C_SalesRegion_ID
            WHERE ci.dateInvoiced BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                                     AND TO_DATE(:end_date, 'YYYY-MM-DD')
            AND ci.ad_Org_id = 1000012
            AND ci.ISSOTRX = 'Y' 
            AND ci.docstatus IN ('CO', 'CL')
            AND ci.c_doctype_id IN (SELECT c_doctype_id FROM c_doctype WHERE Xx_Excluejournalvente = 'N')
            """

            params = {
                'start_date': start_date,
                'end_date': end_date,
            }

            if client:
                query += " AND cb.name LIKE :client"
                params['client'] = f"%{client}%"

            cursor.execute(query, params)
            row = cursor.fetchone()

            # Assuming there's only one row of results, we return the aggregated totals
            total_data = {
                "TotalHT": row[0],
                "TotalTVA": row[1],
                "TotalDT": row[2],
                "TotalTTC": row[3],
                "NETAPAYER": row[4]
            }

            return total_data

    except Exception as e:
        logger.error(f"Error fetching total journal data: {e}")
        return {"error": "An error occurred while fetching total journal data."}

@app.route('/totalJournal', methods=['GET'])
def fetch_total_journal():
    client = request.args.get('client')
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')

    total_data = total_journal(start_date, end_date, client)
    return jsonify(total_data)




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



# Stock Movement functionality
def calculate_stock_initial_for_multiple_emplacements(start_date, product, fournisseur):
    """
    Calculate stock initial for multiple emplacements by summing each emplacement individually
    This solves the issue when emplacement is empty and we need to aggregate stock from all main locations
    Returns both total and breakdown by emplacement
    """
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            
            # Define the main emplacements
            emplacements = ['Préparation', 'HANGAR', 'Dépot Hangar réserve', 'Dépot réserve']
            total_stock_initial = 0
            emplacement_breakdown = {}
            
            
            for emp in emplacements:
                query = """
                SELECT COALESCE(SUM(s.movementqty), 0) as stock_initial
                FROM m_transaction s
                INNER JOIN m_product p ON (s.m_product_id = p.m_product_id)
                INNER JOIN m_locator l ON (l.m_locator_id = s.m_locator_id)
                INNER JOIN M_attributeinstance att ON (att.m_attributesetinstance_id = s.m_attributesetinstance_id)
                WHERE s.movementdate < TO_DATE(:start_date, 'YYYY-MM-DD')
                AND (:product IS NULL OR p.name LIKE :product || '%')
                AND (:fournisseur IS NULL OR att.value LIKE :fournisseur || '%')
                AND l.value LIKE :emplacement || '%'
                AND att.m_attribute_id = 1000508
                AND s.AD_Client_ID = 1000000
                """
                
                params = {
                    'start_date': start_date,
                    'product': product,
                    'fournisseur': fournisseur,
                    'emplacement': emp
                }
                
                cursor.execute(query, params)
                result = cursor.fetchone()
                emplacement_stock = result[0] if result and result[0] else 0
                
                total_stock_initial += emplacement_stock
                emplacement_breakdown[emp] = emplacement_stock
               
            
            return {
                'total': total_stock_initial,
                'breakdown': emplacement_breakdown
            }
            
    except Exception as e:
        logger.error(f"Error calculating stock initial for multiple emplacements: {e}")
        return {
            'total': 0,
            'breakdown': {}
        }

def fetch_stock_movement_data(start_date=None, end_date=None, product=None, fournisseur=None, emplacement=None):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            
            # For empty or None emplacement, we want to fetch all main locations
            if emplacement == '' or emplacement is None:
                # Calculate stock initial separately for multiple emplacements
                stock_initial_result = calculate_stock_initial_for_multiple_emplacements(start_date, product, fournisseur)
                calculated_stock_initial = stock_initial_result['total']
                emplacement_breakdown = stock_initial_result['breakdown']
                
                emplacement_condition = """
                AND (l.value LIKE 'Préparation%' OR l.value LIKE 'HANGAR%' OR l.value LIKE 'Dépot Hangar réserve%' OR l.value LIKE 'Dépot réserve%')
                """
                # Use the calculated stock initial instead of subquery
                stock_initial_value = str(calculated_stock_initial)
                  # Exclude internal transfers when no specific emplacement is selected
                movement_line_condition = ""
                internal_transfer_condition = """
                AND NOT (
                    t.M_MovementLine_ID IS NOT NULL AND (
                        (ml.M_Locator_ID = 1001135 AND ml.M_LocatorTo_ID = 1000614) OR
                        (ml.M_Locator_ID = 1000614 AND ml.M_LocatorTo_ID = 1001135) OR
                        (ml.M_Locator_ID = 1001136 AND ml.M_LocatorTo_ID = 1001135) OR
                        (ml.M_Locator_ID = 1001135 AND ml.M_LocatorTo_ID = 1001136) OR
                        (ml.M_Locator_ID = 1001136 AND ml.M_LocatorTo_ID = 1001128) OR
                        (ml.M_Locator_ID = 1001128 AND ml.M_LocatorTo_ID = 1001136) OR
                        (ml.M_Locator_ID = 1001136 AND ml.M_LocatorTo_ID = 1000614) OR
                        (ml.M_Locator_ID = 1000614 AND ml.M_LocatorTo_ID = 1001136) OR
                        (ml.M_Locator_ID = 1001128 AND ml.M_LocatorTo_ID = 1001135) OR
                        (ml.M_Locator_ID = 1001135 AND ml.M_LocatorTo_ID = 1001128) OR
                        (ml.M_Locator_ID = 1001128 AND ml.M_LocatorTo_ID = 1000614) OR
                        (ml.M_Locator_ID = 1000614 AND ml.M_LocatorTo_ID = 1001128)
                    )
                )
                """
                # Don't include emplacement in params for this case
                params = {
                'start_date': start_date,
                'end_date': end_date,
                'product': product,
                'fournisseur': fournisseur
                }
            else:
                # For specific emplacement, use the original subquery method
                emplacement_breakdown = {}  # No breakdown for specific emplacement
                emplacement_condition = """
                AND l.value LIKE :emplacement || '%'
                """
                stock_initial_value = """coalesce((SELECT SUM(s.movementqty)
                FROM m_transaction s
                inner join m_product p on (s.m_product_id = p.m_product_id)
                inner join m_locator l on (l.m_locator_id = s.m_locator_id)
                inner join M_attributeinstance att on (att.m_attributesetinstance_id = s.m_attributesetinstance_id)
                WHERE s.movementdate <  TO_DATE(:start_date, 'YYYY-MM-DD')
                AND (:product IS NULL OR p.name LIKE :product || '%')
                AND (:fournisseur IS NULL OR att.value like :fournisseur || '%')
                AND l.value LIKE :emplacement || '%'
                AND att.m_attribute_id = 1000508
                AND s.AD_Client_ID = 1000000
                ), 0)"""
                  # Don't exclude internal transfers when specific emplacement is selected
                movement_line_condition = ""
                internal_transfer_condition = ""
                params = {
                    'start_date': start_date,
                    'end_date': end_date,
                    'product': product,
                    'fournisseur': fournisseur,
                    'emplacement': emplacement                }
            
            query = """
            SELECT
                t.MovementDate AS MovementDate,
                nvl(nvl(io.documentno,inv.documentno),m.documentno) as documentno,
                nvl(bp.name, nvl(inv.description,m.description)) as name,
                p.name AS productname,
                CASE WHEN t.movementqty > 0 then t.movementqty else 0 end as ENTREE,
                CASE WHEN t.movementqty < 0 then ABS(t.movementqty) else 0 end as SORTIE,
                """ + stock_initial_value + """ AS StockInitial,
                asi.lot,
                l_from.value AS locator_from,
                l_to.value AS locator_to,
                COALESCE(io.docstatus, m.docstatus, inv.docstatus, 'N/A') AS docstatus
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
            ON (inv.m_inventory_id = il.m_inventory_id )
            LEFT OUTER JOIN M_MovementLine ml
            ON (t.M_MovementLine_ID=ml.M_MovementLine_ID""" + movement_line_condition + """
            )
            LEFT OUTER JOIN M_Movement m
            ON (m.M_Movement_ID=ml.M_Movement_ID )
            LEFT OUTER JOIN M_InOutLine iol
            ON (t.M_InOutLine_ID=iol.M_InOutLine_ID)
            LEFT OUTER JOIN M_Inout io
            ON (iol.M_InOut_ID=io.M_InOut_ID )
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
            )            WHERE
            att.m_attribute_id = 1000508
            AND (:end_date IS NULL OR t.movementdate <= TO_DATE(:end_date, 'YYYY-MM-DD'))
            AND (:start_date IS NULL OR t.movementdate >= TO_DATE(:start_date, 'YYYY-MM-DD'))
            AND (:product IS NULL OR P.NAME LIKE :product || '%')
            AND (:fournisseur IS NULL OR att.value like :fournisseur || '%')
            AND NOT (t.movementqty = 0)
            """ + emplacement_condition + internal_transfer_condition + """
            AND t.AD_Client_ID = 1000000
            ORDER BY t.MovementDate DESC
            """

           
            cursor.execute(query, params)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            data = [dict(zip(columns, row)) for row in rows]
            
            # Add emplacement breakdown to the response
            return {
                'data': data,
                'emplacement_breakdown': emplacement_breakdown
            }

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

        data_result = fetch_stock_movement_data(start_date, end_date, product, fournisseur, emplacement)
        
        if isinstance(data_result, dict) and "error" in data_result:
            return jsonify(data_result), 500

        # Extract the data part for Excel generation
        data = data_result.get('data', []) if isinstance(data_result, dict) else data_result

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



@app.route('/fetch-emplacements-stock')
def fetch_emplacements_stock():
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
    app.run(host='0.0.0.0', port=5004)