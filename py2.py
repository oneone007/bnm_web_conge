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


@app.route('/listfournisseur_etat')
def list_fournisseur_etat():
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT cb.name, cb.C_BPartner_ID
                FROM C_BPartner cb
                WHERE cb.AD_Client_ID = 1000000
                  AND cb.ISVENDOR = 'Y'
                  AND cb.ISACTIVE = 'Y'
                ORDER BY cb.name
            """
            cursor.execute(query)
            # Return both name and ID as a list of dictionaries
            result = [{"name": row[0], "id": row[1]} for row in cursor.fetchall()]
            return jsonify(result)
    except Exception as e:
        logger.error(f"Error fetching fournisseurs: {e}")
        return jsonify({"error": "Could not fetch fournisseur list"}), 500

def fetch_etat_f(date1, date2, c_bpartner_id=None, ispaid=None):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT ispaid, ad_org_id, c_bpartner_id, dateinvoiced, nbl, nfact, documentno, grandtotal, verse_fact, 
                       verse_cheque, cheque, name, dateversement, nb, region,  
                       SUM(grandtotal/nb) OVER(PARTITION BY c_bpartner_id) AS bp_chiffre,
                       SUM(verse_fact/nb) OVER(PARTITION BY c_bpartner_id) AS verse_tot,
                       orgname, phone, phone2, fax,
                       address1, address2, address3, address4, city, postal
                FROM 
                (SELECT cs.ispaid, cs.ad_org_id, cs.c_bpartner_id, cs.dateinvoiced, cs.nbl, cs.nfact, cs.documentno AS documentno, 
                        cs.grandtotal, cs.verse_fact, 
                        cs.verse_cheque, cs.cheque, cs.name, cs.dateversement, cs.region,  
                        COUNT(cs.c_invoice_id) OVER(PARTITION BY cs.c_invoice_id) AS nb,
                        org.name as orgname, oi.phone, oi.phone2, oi.fax,
                        loc.address1, loc.address2, loc.address3, loc.address4, loc.city, loc.postal
                 FROM xx_vendor_status cs  
                 INNER JOIN ad_org org ON (cs.ad_org_id = org.ad_org_id)
                 INNER JOIN ad_orginfo oi ON (org.ad_org_id = oi.ad_org_id)
                 INNER JOIN c_location loc ON (oi.c_location_id = loc.c_location_id)
                 WHERE cs.AD_Client_ID = 1000000
                 AND cs.AD_Org_ID = 1000000
                 AND (:c_bpartner_id IS NULL OR cs.C_BPartner_ID = :c_bpartner_id)
                 AND cs.dateinvoiced BETWEEN TO_DATE(:date1, 'DD/MM/YYYY') AND TO_DATE(:date2, 'DD/MM/YYYY')
                 AND (:ispaid IS NULL OR :ispaid = '' OR cs.ispaid = :ispaid)
                 ORDER BY cs.dateinvoiced, cs.nbl, cs.nfact)
                ORDER BY dateinvoiced, nbl, nfact
            """
            
            params = {
                'date1': date1,
                'date2': date2,
                'c_bpartner_id': c_bpartner_id,
                'ispaid': ispaid
            }
            
            cursor.execute(query, params)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            
            data = [dict(zip(columns, row)) for row in rows]
            return data
            
    except Exception as e:
        logger.error(f"Error fetching etat_f data: {e}")
        return {"error": "An error occurred while fetching vendor status data."}

@app.route('/etat_f', methods=['GET'])
def etat_f():
    try:
        date1 = request.args.get('date1')
        date2 = request.args.get('date2')
        c_bpartner_id = request.args.get('c_bpartner_id')
        ispaid = request.args.get('ispaid')
        
        # Validate required parameters
        if not date1 or not date2:
            return jsonify({"error": "date1 and date2 are required parameters"}), 400
        
        # Convert c_bpartner_id to int if provided
        if c_bpartner_id:
            try:
                c_bpartner_id = int(c_bpartner_id)
            except ValueError:
                return jsonify({"error": "c_bpartner_id must be a valid integer"}), 400
        
        data = fetch_etat_f(date1, date2, c_bpartner_id, ispaid)
        
        if isinstance(data, dict) and "error" in data:
            return jsonify(data), 500
            
        return jsonify(data)
        
    except Exception as e:
        logger.error(f"Error in etat_f route: {e}")
        return jsonify({"error": "An error occurred while processing the request"}), 500


@app.route('/fetch_etat_fournisseur_cumule')
def fetch_etat_fournisseur_cumule():
    try:
        # Get parameters from request
        c_bpartner_id = request.args.get('c_bpartner_id', type=int)
        start_date = request.args.get('start_date', '01-01-2025')
        end_date = request.args.get('end_date', '01-06-2025')
        
        # Validate required parameters
        if not c_bpartner_id:
            return jsonify({"error": "c_bpartner_id parameter is required"}), 400
        
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            
            # SQL query based on your provided SQL with character set fixes
            query = """
                SELECT inv.DATEINVOICED AS DateTrx ,
                inv.DOCUMENTNO AS DOC_ID,
                inv.POREFERENCE as N_BL,
                doc.PrintName as DOC_TYPE,
                CASE
                  WHEN inv.DESCRIPTION IS NULL
                  THEN
                    (SELECT pro.NAME
                    FROM M_PRODUCT pro,
                      C_INVOICELINE il
                    WHERE inv.C_INVOICE_ID=il.C_INVOICE_ID
                    AND il.M_PRODUCT_ID   =pro.M_PRODUCT_ID
                    AND rownum=1
                    )
                  ELSE inv.DESCRIPTION
                END AS DESCRIPTION,
                CASE
                  WHEN (doc.docbasetype IN ('APC') OR doc.C_DocType_ID =1001510)
                  THEN inv.GRANDTOTAL   *-1
                  ELSE inv.GRANDTOTAL
                END AS "MONTANT"
              FROM C_INVOICE inv,
                C_DOCTYPE doc
              WHERE inv.C_DOCTYPE_ID =doc.C_DOCTYPE_ID
              AND inv.DOCSTATUS     IN ('CO','CL')
              AND doc.docbasetype   IN ('API','APC')
              AND inv.C_BPARTNER_ID  = :c_bpartner_id
              AND inv.AD_Client_ID = 1000000
              AND inv.AD_Org_ID = 1000000
              AND (inv.DATEINVOICED >= TO_DATE(:start_date, 'DD-MM-YYYY')
              AND inv.DATEINVOICED  <= TO_DATE(:end_date, 'DD-MM-YYYY') )
              UNION
              SELECT pa.DATETRX AS DateTrx,
                pa.DOCUMENTNO,
                NULL as POREFERENCE,
                N'Discount' AS NAME,
                N''         AS DESCRIPTION,
                pa.discountamt * -1
              FROM C_PAYMENT pa ,
                C_DOCTYPE doc
              WHERE pa.C_DOCTYPE_ID=doc.C_DOCTYPE_ID
              AND pa.DOCSTATUS    IN ('CO','CL')
              AND doc.docbasetype IN ('APP')
              AND pa.C_BPARTNER_ID = :c_bpartner_id
              AND pa.AD_Client_ID = 1000000
              AND pa.AD_Org_ID = 1000000
              AND (pa.DATETRX     >= TO_DATE(:start_date, 'DD-MM-YYYY')
              AND pa.DATETRX      <= TO_DATE(:end_date, 'DD-MM-YYYY') )
              AND pa.discountamt   > 0
              UNION
              SELECT pa.DATETRX AS DateTrx,
                pa.DOCUMENTNO,
                NULL as POREFERENCE,
                doc.Printname as NAME,
                CASE
                  WHEN pa.DESCRIPTION IS NOT NULL
                  THEN pa.DESCRIPTION
                  WHEN pa.C_INVOICE_ID IS NOT NULL
                  THEN
                    (SELECT inv.DOCUMENTNO
                    FROM C_INVOICE inv
                    WHERE inv.C_INVOICE_ID=pa.C_INVOICE_ID
                    AND rownum=1
                    )
                  ELSE N''
                END AS DESCRIPTION,
                (pa.PAYAMT * -1)
              FROM C_PAYMENT pa ,
                C_DOCTYPE doc
              WHERE pa.C_DOCTYPE_ID=doc.C_DOCTYPE_ID
              AND pa.DOCSTATUS    IN ('CO','CL')
              AND doc.docbasetype IN ('APP')
              AND pa.C_BPARTNER_ID = :c_bpartner_id
              AND pa.AD_Client_ID = 1000000
              AND pa.AD_Org_ID = 1000000
              AND (pa.DATETRX     >= TO_DATE(:start_date, 'DD-MM-YYYY')
              AND pa.DATETRX      <= TO_DATE(:end_date, 'DD-MM-YYYY') )

              UNION
              select DATETRX as DateTrx , 
              DOCUMENTNO as DOCUMENTNO  ,
              NULL as POREFERENCE,
              doc.PrintName  as  name , translate ('DIFFÉRENCE_' using nchar_cs) as DESCRIPTION ,  COALESCE( (select  sum(al.writeoffamt* -1) from C_ALLOCATIONLINE al where (al.C_PAYMENT_ID = par.C_PAYMENT_ID and par.C_BPartner_ID = :c_bpartner_id) ),0) 
                            
                            from C_PAYMENT par , c_doctype doc
                            where doc.docbasetype IN ('APP')
                            and par.DOCSTATUS IN ('CO','CL')
                            and  par.C_BPARTNER_ID = :c_bpartner_id
                            and par.AD_Client_ID = 1000000
                            and par.AD_Org_ID = 1000000
                            AND par.C_DOCTYPE_ID=doc.C_DOCTYPE_ID
                            AND (par.DATETRX     >= TO_DATE(:start_date, 'DD-MM-YYYY')
                    AND par.DATETRX      <= TO_DATE(:end_date, 'DD-MM-YYYY'))
                            and COALESCE( (select  sum(al.writeoffamt* -1) from C_ALLOCATIONLINE al where (al.C_PAYMENT_ID = par.C_PAYMENT_ID and par.C_BPartner_ID = :c_bpartner_id) ),0) <> 0              
                            

              UNION
              SELECT c.StatementDATE AS DateTrx,
                c.Name As DOCUMENTNO,
                i.POREFERENCE as POREFERENCE,
                N'Facture sur Caisse' as NAME,
              CASE
                  WHEN cl.DESCRIPTION IS NOT NULL
                  THEN cl.DESCRIPTION
                  WHEN cl.C_INVOICE_ID IS NOT NULL
                  THEN
                    i.DOCUMENTNO
                  ELSE N''
                END  AS DESCRIPTION,
                cl.Amount * -1
              FROM C_CashLine cl
              INNER JOIN  C_Cash c ON (cl.C_Cash_ID=c.C_Cash_ID)
              INNER JOIN C_Invoice i ON (cl.C_Invoice_ID=i.C_Invoice_ID)
              WHERE 
              c.DOCSTATUS    IN ('CO','CL')
              AND i.ispaid='Y'
              AND cl.isactive='Y'
              AND i.C_BPARTNER_ID = :c_bpartner_id
              AND i.AD_Client_ID = 1000000
              AND i.AD_Org_ID = 1000000
              AND (c.StatementDATE     >= TO_DATE(:start_date, 'DD-MM-YYYY')
              AND c.StatementDATE      <= TO_DATE(:end_date, 'DD-MM-YYYY') )
              ORDER BY DateTrx
            """
            
            # Execute query with parameters
            cursor.execute(query, {
                'c_bpartner_id': c_bpartner_id,
                'start_date': start_date,
                'end_date': end_date
            })
            
            # Fetch results and convert to list of dictionaries
            columns = [desc[0] for desc in cursor.description]
            result = []
            for row in cursor.fetchall():
                row_dict = {}
                for i, value in enumerate(row):
                    # Convert Oracle date objects to string format
                    if hasattr(value, 'strftime'):
                        row_dict[columns[i]] = value.strftime('%Y-%m-%d')
                    elif value is None:
                        row_dict[columns[i]] = None
                    else:
                        row_dict[columns[i]] = value
                result.append(row_dict)
            
            return jsonify(result)
            
    except Exception as e:
        logger.error(f"Error fetching supplier cumulative statement: {e}")
        return jsonify({"error": f"Could not fetch supplier cumulative statement: {str(e)}"}), 500


@app.route('/sold_initial_etat_cum')
def sold_initial_etat_cum():
    try:
        # Get parameters from request
        c_bpartner_id = request.args.get('c_bpartner_id', type=int)
        start_date = request.args.get('start_date', '01-01-2025')
        
        # Validate required parameters
        if not c_bpartner_id:
            return jsonify({"error": "c_bpartner_id parameter is required"}), 400
        
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            
            # SQL query to calculate opening balance
            query = """
                SELECT (
                    (SELECT COALESCE(SUM(iar.GRANDTOTAL), 0) 
                     FROM C_INVOICE_V iar
                     WHERE iar.DOCSTATUS IN ('CO','CL') 
                     AND iar.ISSOTRX = 'N'
                     AND iar.C_BPARTNER_ID = :c_bpartner_id
                     AND iar.DATEINVOICED < TO_DATE(:start_date, 'DD-MM-YYYY'))
                     
                    -
                    
                    (SELECT COALESCE(SUM(par.PAYAMT) + SUM(par.discountamt), 0)
                     FROM C_PAYMENT par
                     WHERE par.DOCSTATUS IN ('CO','CL') 
                     AND par.ISRECEIPT = 'N'
                     AND par.C_BPARTNER_ID = :c_bpartner_id
                     AND par.DATETRX < TO_DATE(:start_date, 'DD-MM-YYYY'))
                     
                    -
                    
                    (SELECT COALESCE(SUM(cl.Amount), 0)
                     FROM C_CashLine cl
                     INNER JOIN C_Cash c ON (cl.C_Cash_ID = c.C_Cash_ID)
                     INNER JOIN C_Invoice i ON (cl.C_Invoice_ID = i.C_Invoice_ID)
                     WHERE c.DOCSTATUS IN ('CO','CL')
                     AND i.ispaid = 'Y'
                     AND cl.isactive = 'Y'
                     AND i.C_BPARTNER_ID = :c_bpartner_id
                     AND c.StatementDATE < TO_DATE(:start_date, 'DD-MM-YYYY'))
                ) AS OpeningBal 
                FROM DUAL
            """
            
            # Execute query with parameters
            cursor.execute(query, {
                'c_bpartner_id': c_bpartner_id,
                'start_date': start_date
            })
            
            # Fetch result
            result = cursor.fetchone()
            opening_balance = result[0] if result else 0
            
            return jsonify({
                "c_bpartner_id": c_bpartner_id,
                "start_date": start_date,
                "opening_balance": float(opening_balance) if opening_balance else 0.0
            })
            
    except Exception as e:
        logger.error(f"Error fetching opening balance: {e}")
        return jsonify({"error": f"Could not fetch opening balance: {str(e)}"}), 500





# ...existing code...

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000)