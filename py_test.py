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




# Test database connection
def test_db_connection():
    try:
        with DB_POOL.acquire() as connection:
            logger.info("Database connection successful.")
        return True
    except Exception as e:
        logger.error(f"Database connection failed: {str(e)}")
        return False
    
    


@app.route('/simulation_fetchBCCBProduct', methods=['GET'])
def simulation_fetch_bccb_p():
    bccb = request.args.get('bccb')
    ad_org_id = request.args.get('ad_org_id')

    data = simulation_fetch_bccb_product(bccb, ad_org_id)
    return jsonify(data)



def merge_free_products_with_normal(data):
    """
    Merge products with same name where one has margin 0 or -100 (free products).
    Free products are excluded from margin calculation but their quantity is added to normal products.
    """
    if not data:
        return data
    
    # Group products by name
    product_groups = {}
    for item in data:
        product_name = item.get('PRODUCT', '').strip()
        if product_name not in product_groups:
            product_groups[product_name] = []
        product_groups[product_name].append(item)
    
    merged_results = []
    
    for product_name, products in product_groups.items():
        if len(products) == 1:
            # Single product, add as is
            merged_results.append(products[0])
        else:
            # Multiple products with same name, need to merge
            normal_products = []
            free_products = []
            
            for product in products:
                marge = float(product.get('MARGE', 0))
                # Consider margin <= 0 or == -100 as free products
                if marge <= 0 or marge == -100:
                    free_products.append(product)
                else:
                    normal_products.append(product)
            
            if normal_products:
                # Use the first normal product as base
                base_product = normal_products[0].copy()
                
                # Add quantities from all free products to the normal product
                total_added_qty = 0
                for free_product in free_products:
                    free_qty = float(free_product.get('QTY', 0))
                    total_added_qty += free_qty
                    print(f"🎁 Merging free product qty {free_qty} into {product_name}")
                
                # Update the base product's quantity
                base_qty = float(base_product.get('QTY', 0))
                base_product['QTY'] = base_qty + total_added_qty
                
                print(f"📦 Merged {product_name}: {base_qty} + {total_added_qty} = {base_product['QTY']} qty")
                merged_results.append(base_product)
                
                # Add any additional normal products (if more than one normal product exists)
                for normal_product in normal_products[1:]:
                    merged_results.append(normal_product)
            else:
                # Only free products exist, keep the first one
                if free_products:
                    merged_results.append(free_products[0])
    
    return merged_results


def simulation_fetch_bccb_product(bccb, ad_org_id):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT product, qty, remise, marge, priceentered, remise_vente, bonus_vente, p_revient,ventef, pricelist
                FROM (
                    SELECT det.*, 
                           ROUND((det.ventef - det.p_revient) / det.p_revient * 100, 2) AS marge 
                    FROM (
                        SELECT lot.*, 
                               (lot.priceentered - ((lot.priceentered * NVL(lot.remise_vente, 0)) / 100)) / 
                               (1 + (lot.bonus_vente / 100)) AS ventef 
                        FROM (
                            SELECT ol.priceentered AS priceentered, 
                                   ol.qtyentered AS qty, 
                                   ol.pricelist AS pricelist,
                                   mp.name AS product, 
                                   ol.discount / 100 AS remise, 
                                   (SELECT valuenumber FROM m_attributeinstance 
                                    WHERE m_attributesetinstance_id = ol.m_attributesetinstance_id 
                                          AND m_attribute_id = 1000504) AS p_revient, 
                                   (SELECT valuenumber FROM m_attributeinstance 
                                    WHERE m_attributesetinstance_id = ol.m_attributesetinstance_id 
                                          AND m_attribute_id = 1000908) AS bonus_vente, 
                                   (SELECT valuenumber FROM m_attributeinstance 
                                    WHERE m_attributesetinstance_id = ol.m_attributesetinstance_id 
                                          AND m_attribute_id = 1001408) AS remise_vente 
                            FROM c_orderline ol 
                            INNER JOIN c_order o ON o.c_order_id = ol.c_order_id 
                            INNER JOIN m_product mp ON ol.m_product_id = mp.m_product_id 
                            WHERE ol.qtyentered > 0 
                                  AND (:bccb IS NULL OR UPPER(o.documentno) LIKE UPPER(:bccb) || '%')
                                  AND o.AD_Org_ID = 1000000
                        ) lot
                    ) det
                )
            """
            
            params = {
                'bccb': bccb or None
            }
            
            cursor.execute(query, params)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]  # Get column names
            data = [dict(zip(columns, row)) for row in rows]
            
            # Process data to merge free products with normal products
            logger.info(f"Original data count: {len(data)}")
            merged_data = merge_free_products_with_normal(data)
            logger.info(f"Merged data count: {len(merged_data)}")
            return merged_data
    except Exception as e:
        logger.error(f"Error fetching BCCB product data: {e}")
        return {"error": "An error occurred while fetching BCCB product data."}



@app.route('/real_simulation_all', methods=['GET'])
def simulation_get_simulation_all():
    ndocument = request.args.get('ndocument')
    if not ndocument:
        return jsonify({'error': 'Missing ndocument parameter'}), 400
    result = simulation_fetch_simulation_by_ndocument(ndocument)
    if result:
        return jsonify(result)
    else:
        return jsonify({'error': 'No data found for the given ndocument'}), 404





# New: fetch simulation by ndocument
def simulation_fetch_simulation_by_ndocument(ndocument):
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT 
                    CAST(org.name AS VARCHAR2(300)) AS organisation,
                    CAST(co.documentno AS VARCHAR2(50)) AS ndocument,
                    CAST(cb.name AS VARCHAR2(300)) AS tier,
                    co.dateordered AS datecommande,
                    CAST(us.name AS VARCHAR2(100)) AS vendeur,
                    ROUND(((co.totallines / (SELECT SUM(mat.valuenumber * li.qtyentered) 
                         FROM c_orderline li 
                         INNER JOIN m_attributeinstance mat ON mat.m_attributesetinstance_id = li.m_attributesetinstance_id
                         WHERE mat.m_attribute_id = 1000504 
                           AND li.c_order_id = co.c_order_id 
                           AND li.qtyentered > 0 
                         GROUP BY li.c_order_id)) - 1) * 100, 2) AS marge,
                    ROUND(co.totallines, 2) AS montant,
                    (SELECT mat.valuenumber 
                     FROM c_orderline li 
                     INNER JOIN m_attributeinstance mat ON mat.m_attributesetinstance_id = li.m_attributesetinstance_id
                     WHERE mat.m_attribute_id = 1000504 
                       AND li.c_order_id = co.c_order_id 
                       AND li.qtyentered > 0 
                       AND ROWNUM = 1) AS valuenumber
                FROM 
                    c_order co
                INNER JOIN ad_org org ON co.ad_org_id = org.ad_org_id
                INNER JOIN c_bpartner cb ON co.c_bpartner_id = cb.c_bpartner_id
                INNER JOIN ad_user us ON co.salesrep_id = us.ad_user_id
                WHERE 
                     co.ad_org_id = 1000000
                    AND issotrx = 'Y'
                    AND co.documentno = :ndocument
            """
            cursor.execute(query, {'ndocument': ndocument})
            row = cursor.fetchone()
            if row:
                columns = [col[0] for col in cursor.description]
                return dict(zip(columns, row))
            else:
                return None
    except Exception as e:
        logging.error(f"Error fetching simulation data by ndocument: {e}")
        return {"error": "An error occurred while fetching simulation data by ndocument."}




@app.route('/simulation_fetch-product-details', methods=['GET'])
def simulation_fetch_product_details():
    try:
        product_name = request.args.get("product_name", None)
        client_type = request.args.get("client_type", None)
        
        if not product_name:
            return jsonify({"error": "Product name is required"}), 400

        data = simulation_fetch_product_details_data(product_name, client_type)
        return jsonify(data)

    except Exception as e:
        logger.error(f"Error fetching product details: {e}")
        return jsonify({"error": "Failed to fetch product details"}), 500




 
def simulation_fetch_product_details_data(product_name, client_type=None):
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
                        "source"."LOCATION" "LOCATION",
                        "source"."CLIENT_TYPE" "CLIENT_TYPE"

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
                                    WHEN m_locator_id = 1000717 THEN 'hangar reception'
                                END AS location,
                                c_bp_group_id,
                                CASE 
                                    WHEN c_bp_group_id = 1001330 THEN 'Client Potentiel'
                                    WHEN c_bp_group_id = 1000003 THEN 'Client Para'
                                    ELSE 'Other'
                                END AS client_type
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
                                                        ) ppa,
                                                        NVL(cp.C_BP_Group_ID, 0) AS c_bp_group_id
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
                                                        AND mst.m_locator_id IN (1001135, 1000614, 1001128, 1001136, 1001020, 1000717)
                                                        AND mst.qtyonhand != 0
                                                        AND p.name = :product_name
                                                        AND (:client_type IS NULL OR 
                                                             (:client_type = 'Client Potentiel' AND cp.C_BP_Group_ID = 1001330) OR
                                                             (:client_type = 'Client Para' AND cp.C_BP_Group_ID = 1000003))
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
                                m_locator_id,
                                c_bp_group_id
                            ORDER BY
                                fournisseur
                        ) "source"
                )
            WHERE
                rownum <= 1048575
            """

            cursor.execute(query, {"product_name": product_name, "client_type": client_type})
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            data = [dict(zip(columns, row)) for row in rows]

            return data

    except Exception as e:
        logger.error(f"Error fetching product details: {e}")
        return {"error": "An error occurred while fetching product details."}





@app.route('/simulation_listproduct')
def simulation_listproduct():
    try:
        data = simulation_fetch_all_products()
        return jsonify(data)
    except Exception as e:
        logger.error(f"Error fetching products list: {e}")
        return jsonify({"error": "An error occurred while fetching products list."}), 500

def simulation_fetch_all_products():
    """
    Fetch all products with basic information for search functionality
    """
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT DISTINCT
                    mp.name AS NAME,
                    mp.value AS CODE,
                    mp.m_product_id AS PRODUCT_ID
                FROM m_product mp
                WHERE mp.isactive = 'Y'
                AND mp.issold = 'Y'
                ORDER BY mp.name
            """
            cursor.execute(query)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            return [dict(zip(columns, row)) for row in rows]
    except Exception as e:
        logger.error(f"Error fetching all products: {e}")
        return []
    



@app.route('/orders_from_facture', methods=['GET'])
def get_orders_from_facture():
    """
    Get all orders related to a specific facture (invoice)
    
    Query Parameters:
    - facture_number: The invoice document number (required)
    
    Example: /orders_from_facture?facture_number=15933/2025
    """
    facture_number = request.args.get('facture_number')
    
    if not facture_number:
        return jsonify({'error': 'Missing facture_number parameter'}), 400
    
    try:
        result = fetch_orders_from_facture(facture_number)
        if result:
            return jsonify({
                'success': True,
                'facture_number': facture_number,
                'orders_count': len(result),
                'orders': result
            })
        else:
            return jsonify({
                'success': True,
                'facture_number': facture_number,
                'orders_count': 0,
                'orders': [],
                'message': 'No orders found for this facture'
            })
    except Exception as e:
        logger.error(f"Error in get_orders_from_facture: {e}")
        return jsonify({'error': 'An error occurred while fetching orders from facture'}), 500


def fetch_orders_from_facture(facture_number):
    """
    Fetch orders related to a specific facture (invoice) using the invoice document number
    
    Args:
        facture_number (str): The invoice document number
    
    Returns:
        list: List of dictionaries containing order information
    """
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
                SELECT DISTINCT
                    o.DocumentNo as OrderNumber,
                    o.GrandTotal as OrderTotal,
                    bp.Name as BusinessPartnerName
                FROM 
                    C_Invoice i
                    INNER JOIN C_InvoiceLine il ON i.C_Invoice_ID = il.C_Invoice_ID
                    INNER JOIN C_OrderLine ol ON il.C_OrderLine_ID = ol.C_OrderLine_ID
                    INNER JOIN C_Order o ON ol.C_Order_ID = o.C_Order_ID
                    LEFT JOIN C_BPartner bp ON o.C_BPartner_ID = bp.C_BPartner_ID
                WHERE 
                    i.DocumentNo = :invoice_document_no
                    AND i.DocStatus IN ('CO', 'CL')
                    AND i.IsActive = 'Y'
                    AND il.IsActive = 'Y'
                    AND ol.IsActive = 'Y'
                    AND o.IsActive = 'Y'
                ORDER BY 
                    o.DocumentNo
            """
            
            params = {
                'invoice_document_no': facture_number
            }
            
            cursor.execute(query, params)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            
            # Convert rows to dictionaries
            result = [dict(zip(columns, row)) for row in rows]
            
            return result
            
    except Exception as e:
        logger.error(f"Error fetching orders from facture {facture_number}: {e}")
        raise e







if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000, debug=True)


    