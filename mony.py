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
import os
import json
import mysql.connector
import schedule
import time
from threading import Thread
import calendar

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
    








def fetch_caisse():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
                SELECT 
                    ROUND(endingbalance, 2) AS caisse
                FROM 
                    C_BankStatement
                WHERE 
                    C_BankAccount_ID = 1000205
                    AND docstatus = 'CO'
                    AND AD_Client_ID = 1000000
                ORDER BY 
                    statementdate DESC
                FETCH FIRST 1 ROW ONLY
            """

            cursor.execute(query)
            row = cursor.fetchone()
            caisse = row[0] if row else 0.0
            return {"caisse": caisse}

    except Exception as e:
        logger.error(f"Error fetching caisse data: {e}")
        return {"error": "An error occurred while fetching caisse data."}


@app.route('/caisse', methods=['GET'])
def caisse():
    if not test_db_connection():
        return jsonify({"error": "Failed to connect to the database."}), 500
    data = fetch_caisse()
    return jsonify(data)



def fourniseurdettfond():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
                SELECT 
                    ROUND(SUM(invopenamt), 2) AS credit_fournisseur
                FROM (
                    SELECT 
                        COALESCE(invoiceOpen(inv.C_Invoice_ID, 0), 0) AS invopenamt
                    FROM
                        C_Invoice inv
                        INNER JOIN c_bpartner bp ON bp.c_bpartner_id = inv.c_bpartner_id
                        LEFT OUTER JOIN C_BPARTNER_LOCATION bpl ON bp.c_bpartner_id = bpl.c_bpartner_id
                        LEFT OUTER JOIN C_SalesRegion SR ON SR.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                        INNER JOIN C_PAYMENTTERM pt ON inv.C_PaymentTerm_ID = pt.C_PaymentTerm_ID
                        LEFT OUTER JOIN ad_user usr ON usr.ad_user_id = inv.salesrep_id
                        LEFT OUTER JOIN ad_user usr2 ON usr2.ad_user_id = bp.salesrep_id
                        LEFT OUTER JOIN C_City ct ON ct.C_City_ID = bpl.C_City_ID
                    WHERE
                        inv.docstatus IN ('CO', 'CL')
                        AND inv.ad_client_id = 1000000
                        --AND inv.ISSOTRX = 'N'
                        AND bp.NAME not like '%MBN%'
                        AND bp.isactive = 'Y'
                        AND bp.isvendor = 'Y'
                )
            """

            cursor.execute(query)
            result = cursor.fetchone()
            return result[0] if result else 0.0

    except Exception as e:
        print(f"An error occurred: {e}")
        return None


@app.route('/fourniseurdettfond', methods=['GET'])
def get_fourniseurdettfond():
    result = get_total_dette()
    if 'error' in result:
        return jsonify({"error": result['error']}), 500
    return jsonify({"value": result['details']['dette_fournisseur']})







def fetch_paiment():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
SELECT
    NVL(SUM(
        CASE 
            WHEN z.name = 'Encaiss: Espèces' THEN ROUND(ci.PayAmt, 2)
            WHEN z.name = 'Décaiss: Espèces' THEN -ROUND(ci.PayAmt, 2)
            ELSE 0 
        END
    ), 0) AS total_difference
FROM 
    C_Payment ci
    INNER JOIN ZSubPaymentRule z ON ci.ZSubPaymentRule_ID = z.ZSubPaymentRule_ID
WHERE 
    TRUNC(ci.DATETRX) = TRUNC(SYSDATE)
    AND ci.DOCACTION IN ('CO', 'CL', 'co', 'cl')
    AND ci.AD_Client_ID = 1000000
    AND z.name IN ('Encaiss: Espèces', 'Décaiss: Espèces')


            """

            cursor.execute(query)
            row = cursor.fetchone()
            paiment = row[0] if row else 0.0
            return {"Total_Paiment": paiment}

    except Exception as e:
        logger.error(f"Error fetching paiment data: {e}")
        return {"error": "An error occurred while fetching paiment data."}


@app.route('/paiement-net', methods=['GET'])
def paiment():
    if not test_db_connection():
        return jsonify({"error": "Failed to connect to the database."}), 500
    data = fetch_paiment()
    return jsonify(data)









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


def get_total_checks():
    conn = None
    cursor = None
    try:
        conn = get_localdb_connection()
        if not conn:
            logger.error("Could not connect to MySQL database")
            return {"error": "Database connection failed"}
        
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT 
                bna_check, baraka_check, total_checks, creation_time 
            FROM bank 
            ORDER BY creation_time DESC 
            LIMIT 1
        """
        cursor.execute(query)
        latest_entry = cursor.fetchone()
        
        if not latest_entry:
            logger.error("No bank data found")
            return {"error": "No bank data available"}
            
        bna_checks = float(latest_entry['bna_check'] or 0)
        baraka_checks = float(latest_entry['baraka_check'] or 0)
        total_checks = float(latest_entry['total_checks'] or 0)
        
        creation_time = latest_entry['creation_time'].strftime('%Y-%m-%d %H:%M') if latest_entry['creation_time'] else None
        
        return {
            "total_checks": total_checks,
            "creation_time": creation_time,
            "details": {
                "BNA": {
                    "checks": bna_checks
                },
                "Baraka": {
                    "checks": baraka_checks
                }
            }
        }
    except Exception as e:
        logger.error(f"Error calculating total checks: {e}")
        return {"error": "An error occurred while calculating total checks"}
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()

@app.route('/total-checks', methods=['GET'])
def total_checks():
    try:
        result = get_total_checks()
        return jsonify(result)
    except Exception as e:
        logger.error(f"Error in total checks route: {e}")
        return jsonify({"error": "Failed to get total checks"}), 500

def get_total_bank():
    conn = None
    cursor = None
    try:
        conn = get_localdb_connection()
        if not conn:
            logger.error("Could not connect to MySQL database")
            return {"error": "Database connection failed"}
        
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT 
                bna_remise, bna_sold,
                baraka_remise, baraka_sold,
                total_bank, creation_time
            FROM bank 
            ORDER BY creation_time DESC 
            LIMIT 1
        """
        
        cursor.execute(query)
        latest_entry = cursor.fetchone()
        
        if not latest_entry:
            logger.error("No bank data found")
            return {"error": "No bank data available"}
            
        bna_sold = float(latest_entry['bna_sold'] or 0)
        bna_remise = float(latest_entry['bna_remise'] or 0)
        baraka_sold = float(latest_entry['baraka_sold'] or 0)
        baraka_remise = float(latest_entry['baraka_remise'] or 0)
        total_bank = float(latest_entry['total_bank'] or 0)
        
        creation_time = latest_entry['creation_time'].strftime('%Y-%m-%d %H:%M') if latest_entry['creation_time'] else None
        
        return {
            "total_bank": total_bank,
            "creation_time": creation_time,
            "details": {
                "BNA": {
                    "sold": bna_sold,
                    "remise": bna_remise,
                    "total": bna_sold + bna_remise
                },
                "Baraka": {
                    "sold": baraka_sold,
                    "remise": baraka_remise,
                    "total": baraka_sold + baraka_remise
                }
            }
        }
    except Exception as e:
        logger.error(f"Error calculating total bank: {str(e)}")
        return {"error": "An error occurred while calculating total bank"}
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()

@app.route('/total-bank', methods=['GET'])
def total_bank():
    try:
        result = get_total_bank()
        return jsonify(result)
    except Exception as e:
        logger.error(f"Error in total bank route: {e}")
        return jsonify({"error": "Failed to get total bank"}), 500


def get_total_tresorie():
    try:
        # Get data from the three sources
        bank_data = get_total_bank()
        if 'error' in bank_data:
            return {"error": "Failed to fetch bank data"}
            
        caisse_data = fetch_caisse()
        if 'error' in caisse_data:
            return {"error": "Failed to fetch caisse data"}
            
        paiement_data = fetch_paiment()
        if 'error' in paiement_data:
            return {"error": "Failed to fetch paiement data"}
            
        # Extract values with fallback to 0
        total_bank = float(bank_data.get('total_bank', 0))
        caisse = float(caisse_data.get('caisse', 0))
        paiement_net = float(paiement_data.get('Total_Paiment', 0))
        
        # Calculate total tresorie
        total_tresorie = total_bank + caisse + paiement_net
        
        # Return the data
        return {
            "total_tresorie": total_tresorie,
            "details": {
                "total_bank": total_bank,
                "caisse": caisse,
                "paiement_net": paiement_net
            }
        }
        
    except Exception as e:
        logger.error(f"Error in total tresorie: {e}")
        return {"error": f"Failed to process tresorie data: {str(e)}"}

@app.route('/total-tresorie', methods=['GET'])
def total_tresorie():
    try:
        result = get_total_tresorie()
        if 'error' in result:
            return jsonify(result), 500
        return jsonify(result)
    except Exception as e:
        logger.error(f"Error in tresorie route: {e}")
        return jsonify({"error": "Failed to get total tresorie"}), 500

def fetch_total_stock_by_location():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
                WITH stock_principale_data AS (
                    SELECT 
                        ROUND(SUM(M_ATTRIBUTEINSTANCE.valuenumber * m_storage.qtyonhand), 2) AS stock_principale
                    FROM 
                        M_ATTRIBUTEINSTANCE
                    JOIN 
                        m_storage ON m_storage.M_ATTRIBUTEsetINSTANCE_id = M_ATTRIBUTEINSTANCE.M_ATTRIBUTEsetINSTANCE_id
                    WHERE 
                        M_ATTRIBUTEINSTANCE.M_Attribute_ID = 1000504
                        AND m_storage.qtyonhand > 0
                        AND m_storage.m_locator_id = 1000614
                ),
                hangar_data AS (
                    SELECT 
                        ROUND(SUM(M_ATTRIBUTEINSTANCE.valuenumber * m_storage.qtyonhand), 2) AS hangar
                    FROM 
                        M_ATTRIBUTEINSTANCE
                    JOIN 
                        m_storage ON m_storage.M_ATTRIBUTEsetINSTANCE_id = M_ATTRIBUTEINSTANCE.M_ATTRIBUTEsetINSTANCE_id
                    WHERE 
                        M_ATTRIBUTEINSTANCE.M_Attribute_ID = 1000504
                        AND m_storage.qtyonhand > 0
                        AND m_storage.m_locator_id = 1001135
                ),
                hangarresrev_data AS (
                    SELECT 
                        ROUND(SUM(M_ATTRIBUTEINSTANCE.valuenumber * m_storage.qtyonhand), 2) AS hangarresrev
                    FROM 
                        M_ATTRIBUTEINSTANCE
                    JOIN 
                        m_storage ON m_storage.M_ATTRIBUTEsetINSTANCE_id = M_ATTRIBUTEINSTANCE.M_ATTRIBUTEsetINSTANCE_id
                    WHERE 
                        M_ATTRIBUTEINSTANCE.M_Attribute_ID = 1000504
                        AND m_storage.qtyonhand > 0
                        AND m_storage.m_locator_id = 1001136
                ),
                depot_reserver_data AS (
                    SELECT 
                        ROUND(SUM(M_ATTRIBUTEINSTANCE.valuenumber * m_storage.qtyonhand), 2) AS depot_reserver
                    FROM 
                        M_ATTRIBUTEINSTANCE
                    JOIN 
                        m_storage ON m_storage.M_ATTRIBUTEsetINSTANCE_id = M_ATTRIBUTEINSTANCE.M_ATTRIBUTEsetINSTANCE_id
                    WHERE 
                        M_ATTRIBUTEINSTANCE.M_Attribute_ID = 1000504
                        AND m_storage.qtyonhand > 0
                        AND m_storage.m_locator_id = 1001128
                )

                SELECT 
                    ROUND(SUM(sp.stock_principale), 2) AS STOCK_principale,
                    ROUND(SUM(h.hangar), 2) AS hangar,
                    ROUND(SUM(hr.hangarresrev), 2) AS hangarréserve,
                    ROUND(SUM(dr.depot_reserver), 2) AS depot_reserver,
                    ROUND(
                        NVL(SUM(sp.stock_principale), 0) + 
                        NVL(SUM(h.hangar), 0) + 
                        NVL(SUM(hr.hangarresrev), 0) + 
                        NVL(SUM(dr.depot_reserver), 0), 
                        2
                    ) AS total_stock
                FROM 
                    stock_principale_data sp,
                    hangar_data h,
                    hangarresrev_data hr,
                    depot_reserver_data dr
            """

            cursor.execute(query)
            result = cursor.fetchone()

            return {
                "STOCK_principale": result[0] or 0,
                "hangar": result[1] or 0,
                "hangarréserve": result[2] or 0,
                "depot_reserver": result[3] or 0,
                "total_stock": result[4] or 0
            }

    except Exception as e:
        logger.error(f"Database error: {e}")
        return {"error": "An error occurred while fetching stock data."}

@app.route('/stock-summary', methods=['GET'])
def get_stock_summary():
    try:
        # Fetch stock data from Oracle and store in MySQL
        stock_data = fetch_total_stock_by_location()
        
        if 'error' in stock_data:
            logger.error(f"Error fetching from Oracle: {stock_data['error']}")
            return jsonify({"error": "Failed to fetch stock data from Oracle"}), 500
        
        # Return success response with the stored data
        return jsonify({
            **stock_data,
            "message": "Stock data successfully fetched"
        })
                
    except Exception as e:
        logger.error(f"Error in stock summary: {e}")
        return jsonify({"error": "Failed to process stock data"}), 500





def get_total_dette():
    try:
        # Get dette fournisseur data
        dette_fournisseur = fourniseurdettfond()
        if dette_fournisseur is None:
            return {"error": "Failed to fetch dette fournisseur data"}
            
        # Get checks data
        checks_data = get_total_checks()
        if 'error' in checks_data:
            return {"error": "Failed to fetch checks data"}
            
        # Extract values with fallback to 0
        dette_value = float(dette_fournisseur or 0)
        total_checks = float(checks_data.get('total_checks', 0))
        
        # Calculate total dette
        total_dette = dette_value + total_checks
        
        # Return the data
        return {
            "total_dette": total_dette,
            "details": {
                "dette_fournisseur": dette_value,
                "total_checks": total_checks,
                "checks_details": checks_data.get('details', {})
            }
        }
        
    except Exception as e:
        logger.error(f"Error in total dette: {e}")
        return {"error": f"Failed to process dette data: {str(e)}"}

@app.route('/total-dette', methods=['GET'])
def total_dette():
    try:
        result = get_total_dette()
        if 'error' in result:
            return jsonify(result), 500
        return jsonify(result)
    except Exception as e:
        logger.error(f"Error in dette route: {e}")
        return jsonify({"error": "Failed to get total dette"}), 500




def fetch_credit_client_this_month():
    try:
        # Get current month date range
        today = datetime.now()
        year = today.year
        month = today.month
        
        start_date = f"{year}-{month:02d}-01"
        last_day = calendar.monthrange(year, month)[1]
        end_date = f"{year}-{month:02d}-{last_day}"
        
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
                SELECT 
                    ROUND(SUM(SoldeFact + SoldeBL), 2) AS credit_client
                FROM (
                    SELECT 
                        bp.c_bpartner_id, 
                        (
                            SELECT COALESCE(SUM(invoiceOpen(inv.C_Invoice_ID, 0)), 0) 
                            FROM C_Invoice inv 
                            INNER JOIN C_PaymentTerm pt ON inv.C_PaymentTerm_ID = pt.C_PaymentTerm_ID
                            WHERE bp.c_bpartner_id = inv.c_bpartner_id 
                            AND paymentTermDueDays(inv.C_PAYMENTTERM_ID, inv.DATEINVOICED, TO_DATE('01/01/3000', 'DD/MM/YYYY')) >= 0
                            AND inv.docstatus IN ('CO', 'CL') 
                            AND inv.AD_ORGTRX_ID = inv.ad_org_id 
                            AND inv.ad_client_id = 1000000
                            AND inv.C_PaymentTerm_ID != 1000000
                            
                            AND (inv.dateinvoiced + pt.netdays) <= TO_DATE(:end_date, 'YYYY-MM-DD')
                        ) AS SoldeFact,
                        (
                            SELECT COALESCE(SUM(invoiceOpen(inv.C_Invoice_ID, 0)), 0) 
                            FROM C_Invoice inv 
                            INNER JOIN C_PaymentTerm pt ON inv.C_PaymentTerm_ID = pt.C_PaymentTerm_ID
                            WHERE bp.c_bpartner_id = inv.c_bpartner_id 
                            AND paymentTermDueDays(inv.C_PAYMENTTERM_ID, inv.DATEINVOICED, TO_DATE('01/01/3000', 'DD/MM/YYYY')) >= 0
                            AND inv.docstatus IN ('CO', 'CL') 
                            AND inv.AD_ORGTRX_ID <> inv.ad_org_id 
                            AND inv.ad_client_id = 1000000
                            AND inv.C_PaymentTerm_ID != 1000000
                           
                            AND (inv.dateinvoiced + pt.netdays) <= TO_DATE(:end_date, 'YYYY-MM-DD')
                        ) AS SoldeBL
                    FROM 
                        c_bpartner bp 
                        INNER JOIN C_BPartner_Location bpl ON bp.C_BPartner_id = bpl.C_BPartner_id
                        INNER JOIN ad_user u ON bp.salesrep_id = u.ad_user_id
                        LEFT OUTER JOIN AD_User u2 ON u2.AD_User_ID = bp.XX_TempSalesRep_ID
                        LEFT OUTER JOIN C_SalesRegion sr ON sr.C_SalesRegion_id = bpl.C_SalesRegion_id
                        LEFT OUTER JOIN C_City sr2 ON sr2.C_City_id = bpl.C_City_id
                    WHERE 
                        bp.iscustomer = 'Y' 
                        AND bp.C_BP_Group_ID IN (1000003, 1000926, 1001330)
                        AND bp.isactive = 'Y' 
                        AND bp.NAME not like '%MBN%'

                        AND sr.isactive = 'Y'
                        AND bp.C_PaymentTerm_ID != 1000000
                        AND bpl.c_salesregion_id IN (
                            101, 102, 1000032, 1001776, 1001777, 1001778, 1001779, 1001780, 1001781,
                            1001782, 1001783, 1001784, 1001785, 1001786, 1001787, 1001788, 1001789,
                            1001790, 1001791, 1001792, 1001793, 1001794, 1002076, 1002077, 1002078,
                            1002079, 1002080, 1002176, 1002177, 1002178, 1002179, 1002180, 1002181,
                            1002283, 1002285, 1002286, 1002287, 1002288
                        )
                ) subquery
            """

            cursor.execute(query, {
                'end_date': end_date
            })
            
            row = cursor.fetchone()
            credit_client = row[0] if row else 0.0
            return {"credit_client": credit_client}

    except Exception as e:
        logger.error(f"Error in credit client processing: {e}")
        return {"error": "An error occurred while processing credit client data."}







def fetch_credit_client():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            query = """
                SELECT 
                    ROUND(SUM(SoldeFact + SoldeBL), 2) AS credit_client
                FROM (
                    SELECT 
                        bp.c_bpartner_id, 
                        (
                            SELECT COALESCE(SUM(invoiceOpen(inv.C_Invoice_ID, 0)), 0) 
                            FROM C_Invoice inv 
                            WHERE bp.c_bpartner_id = inv.c_bpartner_id 
                            AND paymentTermDueDays(inv.C_PAYMENTTERM_ID, inv.DATEINVOICED, TO_DATE('01/01/3000', 'DD/MM/YYYY')) >= 0
                            AND inv.docstatus IN ('CO', 'CL') 
                            AND inv.AD_ORGTRX_ID = inv.ad_org_id 
                            AND inv.ad_client_id = 1000000
                            AND inv.C_PaymentTerm_ID != 1000000
                        ) AS SoldeFact,
                        (
                            SELECT COALESCE(SUM(invoiceOpen(inv.C_Invoice_ID, 0)), 0) 
                            FROM C_Invoice inv 
                            WHERE bp.c_bpartner_id = inv.c_bpartner_id 
                            AND paymentTermDueDays(inv.C_PAYMENTTERM_ID, inv.DATEINVOICED, TO_DATE('01/01/3000', 'DD/MM/YYYY')) >= 0
                            AND inv.docstatus IN ('CO', 'CL') 
                            AND inv.AD_ORGTRX_ID <> inv.ad_org_id 
                            AND inv.ad_client_id = 1000000
                            AND inv.C_PaymentTerm_ID != 1000000
                        ) AS SoldeBL
                    FROM 
                        c_bpartner bp 
                        INNER JOIN C_BPartner_Location bpl ON bp.C_BPartner_id = bpl.C_BPartner_id
                        INNER JOIN ad_user u ON bp.salesrep_id = u.ad_user_id
                        LEFT OUTER JOIN AD_User u2 ON u2.AD_User_ID = bp.XX_TempSalesRep_ID
                        LEFT OUTER JOIN C_SalesRegion sr ON sr.C_SalesRegion_id = bpl.C_SalesRegion_id
                        LEFT OUTER JOIN C_City sr2 ON sr2.C_City_id = bpl.C_City_id
                    WHERE 
                        bp.iscustomer = 'Y' 
                        AND bp.C_BP_Group_ID IN (1000003, 1000926, 1001330)
                        AND bp.isactive = 'Y' 
                        AND bp.NAME not like '%MBN%'

                        AND sr.isactive = 'Y'
                        AND bp.C_PaymentTerm_ID != 1000000
                        AND bpl.c_salesregion_id IN (
                            101, 102, 1000032, 1001776, 1001777, 1001778, 1001779, 1001780, 1001781,
                            1001782, 1001783, 1001784, 1001785, 1001786, 1001787, 1001788, 1001789,
                            1001790, 1001791, 1001792, 1001793, 1001794, 1002076, 1002077, 1002078,
                            1002079, 1002080, 1002176, 1002177, 1002178, 1002179, 1002180, 1002181,
                            1002283, 1002285, 1002286, 1002287, 1002288
                        )
                ) subquery
            """

            cursor.execute(query)
            row = cursor.fetchone()
            credit_client = row[0] if row else 0.0
            return {"credit_client": credit_client}

    except Exception as e:
        logger.error(f"Error in credit client processing: {e}")
        return {"error": "An error occurred while processing credit client data."}




@app.route('/credit-client', methods=['GET'])
def credit_client():
    if not test_db_connection():
        return jsonify({"error": "Failed to connect to the database."}), 500
    data = fetch_credit_client()
    return jsonify(data)


def calculate_total_profit():
    conn = None
    cursor = None
    try:
        # Get all necessary data
        credit_client_data = fetch_credit_client()
        if 'error' in credit_client_data:
            return {"error": "Failed to fetch credit client data"}
            
        tresorie_data = get_total_tresorie()
        if 'error' in tresorie_data:
            return {"error": "Failed to fetch tresorie data"}
            
        stock_data = fetch_total_stock_by_location()
        if 'error' in stock_data:
            return {"error": "Failed to fetch stock data"}
            
        dette_data = get_total_dette()
        if 'error' in dette_data:
            return {"error": "Failed to fetch dette data"}
            
        # Extract all values with fallback to 0
        credit_client = float(credit_client_data.get('credit_client', 0))
        total_tresorie = float(tresorie_data.get('total_tresorie', 0))
        total_stock = float(stock_data.get('total_stock', 0))
        dette_fournisseur = float(dette_data['details'].get('dette_fournisseur', 0))
        
        # Get detailed values for MySQL storage
        caisse = float(tresorie_data['details'].get('caisse', 0))
        paiement_net = float(tresorie_data['details'].get('paiement_net', 0))
        total_bank = float(tresorie_data['details'].get('total_bank', 0))
        total_checks = float(dette_data['details'].get('total_checks', 0))
        
        # Calculate total profit
        total_profit = (credit_client + total_tresorie + total_stock) - dette_fournisseur
        
        # Store all data in MySQL database
        conn = get_localdb_connection()
        if not conn:
            logger.error("Failed to connect to MySQL database")
            return {"error": "Database connection failed"}
            
        cursor = conn.cursor()
        
        try:
            # Start transaction
            conn.start_transaction()
            
            # Insert stock data
            cursor.execute("""
                INSERT INTO stock (
                    total_stock, principal, hangar, 
                    depot_reserver, hangar_reserver, time
                ) VALUES (%s, %s, %s, %s, %s, SYSDATE())
            """, (
                stock_data['total_stock'],
                stock_data['STOCK_principale'],
                stock_data['hangar'],
                stock_data['depot_reserver'],
                stock_data['hangarréserve']
            ))
            
            # Insert tresorie data
            cursor.execute("""
                INSERT INTO tresorie (
                    total_tresorie, caisse, paiement_net, 
                    total_bank, time
                ) VALUES (%s, %s, %s, %s, SYSDATE())
            """, (total_tresorie, caisse, paiement_net, total_bank))
            
            # Insert credit client data
            cursor.execute("""
                INSERT INTO creance_client (
                    creance, time
                ) VALUES (%s, SYSDATE())
            """, (credit_client,))
            
            # Insert dette data
            cursor.execute("""
                INSERT INTO dette (
                    total_dette, dette_fournisseur, total_checks, 
                    time
                ) VALUES (%s, %s, %s, SYSDATE())
            """, (dette_data['total_dette'], dette_fournisseur, total_checks))
            
            # Insert total profit data
            cursor.execute("""
                INSERT INTO analyse (
                    total_profit, time
                ) VALUES (%s, SYSDATE())
            """, (total_profit,))
            
            # Commit all changes
            conn.commit()
            
        except Exception as e:
            conn.rollback()
            raise e
            
        finally:
            cursor.close()
            conn.close()
            
        # Return the data
        return {
            "total_profit": total_profit,
            "details": {
                "credit_client": credit_client,
                "total_tresorie": total_tresorie,
                "total_stock": total_stock,
                "dette_fournisseur": dette_fournisseur,
                "total_dette": dette_data['total_dette']
            }
        }
        
    except Exception as e:
        logger.error(f"Error calculating total profit: {e}")
        if conn:
            conn.rollback()
        return {"error": f"Failed to calculate total profit: {str(e)}"}
        
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()




@app.route('/total-profit', methods=['GET'])
def total_profit():
    try:
        result = calculate_total_profit()
        if 'error' in result:
            return jsonify(result), 500
        return jsonify(result)
    except Exception as e:
        logger.error(f"Error in total profit route: {e}")
        return jsonify({"error": "Failed to get total profit"}), 500


def total_profit_page():
    try:
        # Get all necessary data
        credit_client_data = fetch_credit_client()
        if 'error' in credit_client_data:
            return {"error": "Failed to fetch credit client data"}
            
        tresorie_data = get_total_tresorie()
        if 'error' in tresorie_data:
            return {"error": "Failed to fetch tresorie data"}
            
        stock_data = fetch_total_stock_by_location()
        if 'error' in stock_data:
            return {"error": "Failed to fetch stock data"}
            
        dette_data = get_total_dette()
        if 'error' in dette_data:
            return {"error": "Failed to fetch dette data"}
            
        # Extract all values with fallback to 0
        credit_client = float(credit_client_data.get('credit_client', 0))
        total_tresorie = float(tresorie_data.get('total_tresorie', 0))
        total_stock = float(stock_data.get('total_stock', 0))
        dette_fournisseur = float(dette_data['details'].get('dette_fournisseur', 0))
        
        # Calculate total profit
        total_profit = (credit_client + total_tresorie + total_stock) - dette_data['total_dette']
        
        # Return the data without storing in database
        return {
            "total_profit": total_profit,
            "details": {
                "credit_client": credit_client,
                "total_tresorie": total_tresorie,
                "total_stock": total_stock,
                "dette_fournisseur": dette_fournisseur,
                "total_dette": dette_data['total_dette']
            }
        }
        
    except Exception as e:
        logger.error(f"Error calculating total profit: {e}")
        return {"error": f"Failed to calculate total profit: {str(e)}"}



@app.route('/total-profit-page', methods=['GET'])
def get_total_profit_page():
    try:
        result = total_profit_page()
        if 'error' in result:
            return jsonify(result), 500
        return jsonify(result)
    except Exception as e:
        logger.error(f"Error in total profit page route: {e}")
        return jsonify({"error": "Failed to get total profit"}), 500


def run_scheduler():
    def job():
        try:
            result = calculate_total_profit()
            if 'error' in result:
                logger.error(f"Scheduled job failed: {result['error']}")
            else:
                logger.info("Scheduled calculation completed successfully")
        except Exception as e:
            logger.error(f"Error in scheduled job: {e}")

    def monthly_recouvrement_job():
        """Monthly job to fetch credit client data and store in recouvrement table"""
        try:
            # Fetch credit client data
            credit_data = fetch_credit_client_this_month()
            if 'error' in credit_data:
                logger.error(f"Monthly recouvrement job failed: {credit_data['error']}")
                return
            
            credit_value = float(credit_data.get('credit_client', 0))
            
            # Connect to MySQL database
            conn = get_localdb_connection()
            if not conn:
                logger.error("Failed to connect to MySQL database for recouvrement")
                return
            
            cursor = conn.cursor()
            
            try:
                # Get current date and time as string
                current_datetime = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                
                # Insert new row in recouvrement table with DOUBLE value and VARCHAR datetime
                cursor.execute("""
                    INSERT INTO recouvrement (value, date) 
                    VALUES (%s, %s)
                """, (credit_value, current_datetime))
                
                conn.commit()
                logger.info(f"Monthly recouvrement data saved - Value: {credit_value}, Date: {current_datetime}")
                
            except Exception as e:
                conn.rollback()
                logger.error(f"Error inserting recouvrement data: {e}")
                logger.error(f"Attempted to insert - Value: {credit_value}, Date: {current_datetime}")
                
            finally:
                cursor.close()
                conn.close()
                
        except Exception as e:
            logger.error(f"Error in monthly recouvrement job: {e}")

    # Schedule jobs
    schedule.every().day.at("08:00").do(job)
    schedule.every().day.at("12:00").do(job)
    schedule.every().day.at("17:00").do(job)
    
    # Schedule daily check for monthly recouvrement job at 12:00
    def check_monthly_recouvrement():
        """Check if today is the 1st of the month and run recouvrement job"""
        from datetime import datetime
        if datetime.now().day == 1:
            monthly_recouvrement_job()
    
    schedule.every().day.at("12:00").do(check_monthly_recouvrement)
    
    while True:
        schedule.run_pending()
        time.sleep(60)  # Check every minute

# Start the scheduler in a separate thread
def start_scheduler():
    scheduler_thread = Thread(target=run_scheduler, daemon=True)
    scheduler_thread.start()
    logger.info("Scheduler started for automatic profit calculations")



@app.route('/kpi-trends-data', methods=['GET'])
def get_kpi_trends_data():
    conn = None
    cursor = None
    try:
        # Get parameters from request
        start_date = request.args.get('start_date')
        end_date = request.args.get('end_date')
        days = request.args.get('days')
        metric = request.args.get('metric')  # 'profit', 'tresorerie', 'dette', 'creance', or 'stock'
        
        if not metric:
            return jsonify({"error": "Metric parameter is required"})

        conn = get_localdb_connection()
        if not conn:
            return jsonify({"error": "Database connection failed"})
        
        cursor = conn.cursor(dictionary=True)
        
        # Determine which table and column to query based on the metric
        table_info = {
            'profit': {'table': 'analyse', 'column': 'total_profit'},
            'tresorerie': {'table': 'tresorie', 'column': 'total_tresorie'},
            'dette': {'table': 'dette', 'column': 'total_dette'},
            'creance': {'table': 'creance_client', 'column': 'creance'},
            'stock': {'table': 'stock', 'column': 'total_stock'}
        }
        
        if metric not in table_info:
            return jsonify({"error": "Invalid metric parameter"})
        
        table = table_info[metric]['table']
        column = table_info[metric]['column']
        
        # Query to get data points within the date range for the specific metric
        query = f"""
        SELECT 
            DATE(time) as date,
            TIME(time) as time_of_day,
            {column} as value
        FROM {table}
        WHERE 1=1
        """
        
        params = []
        
        # Add date filtering based on provided parameters
        if start_date and end_date:
            query += " AND DATE(time) BETWEEN %s AND %s"
            params.extend([start_date, end_date])
        elif days:
            try:
                days = int(days)
                query += " AND time >= DATE_SUB(CURRENT_DATE(), INTERVAL %s DAY)"
                params.append(days)
            except ValueError:
                return jsonify({"error": "Invalid days parameter"})
                
        # Add ordering to query results
        query += """
        ORDER BY time ASC
        """
        
        cursor.execute(query, params)
        rows = cursor.fetchall()
        
        # Prepare data for response
        dates = []
        values = []
        
        for row in rows:
            # Format date with time
            date_str = row['date'].strftime('%d/%m/%Y')
            time_str = str(row['time_of_day'])[:5]  # Get HH:MM from the time
            date_label = f"{date_str} {time_str}"
            dates.append(date_label)
            values.append(float(row.get('value', 0) or 0))

        # Prepare final response
        response = {
            'labels': dates,
            'datasets': [{
                'label': metric.capitalize(),
                'data': values,
                'borderColor': get_color_for_metric(metric),
                'backgroundColor': get_background_color_for_metric(metric),
                'tension': 0.4,
                'fill': False
            }]
        }

        return jsonify(response)

    except Exception as e:
        logger.error(f"Error fetching {metric} data: {e}")
        return jsonify({"error": f"Failed to fetch {metric} data: {str(e)}"})
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()

def get_color_for_metric(metric):
    colors = {
        'tresorerie': '#9b5de5',
        'dette': '#ff0033',
        'creance': '#0088ff',
        'stock': '#00f5d4',
        'profit': '#ffcc00'
    }
    return colors.get(metric, '#cccccc')

def get_background_color_for_metric(metric):
    colors = {
        'tresorerie': 'rgba(155, 93, 229, 0.1)',
        'dette': 'rgba(255, 0, 51, 0.1)',
        'creance': 'rgba(0, 136, 255, 0.1)',
        'stock': 'rgba(0, 245, 212, 0.1)',
        'profit': 'rgba(255, 204, 0, 0.1)'
    }
    return colors.get(metric, 'rgba(204, 204, 204, 0.1)')


            
if __name__ == "__main__":
    # Start the scheduler
    start_scheduler()
    # Run the Flask app
    app.run(host='0.0.0.0', port=4999)

