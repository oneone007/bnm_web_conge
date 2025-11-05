


import oracledb
from flask import Flask, jsonify, request, send_file, make_response
from flask_cors import CORS
import logging
import pandas as pd
from io import BytesIO
import io
from openpyxl import Workbook
from openpyxl.styles import PatternFill, Font
from openpyxl.worksheet.table import Table, TableStyleInfo
from openpyxl.cell.cell import MergedCell
from datetime import datetime
import os
import json
import calendar
import mysql.connector

from reportlab.lib.styles import ParagraphStyle
from reportlab.lib import colors


from reportlab.lib.pagesizes import A4
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, Image
from reportlab.lib.styles import getSampleStyleSheet
from reportlab.lib.units import mm



app = Flask(__name__)
CORS(app)  # Allow your DDNS domains and ports

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

# MySQL connection for local database
def get_localdb_connection():
    """
    Get MySQL database connection for local configuration tables.
    """
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",  # Update with your MySQL password if needed
            database="bnm"
        )
        return connection
    except Exception as e:
        logger.error(f"Error connecting to local MySQL database: {e}")
        return None




@app.route('/factures', methods=['GET'])
def factures():
    """Return invoice (facture) details by DOCUMENTNO.

    Query params:
      - documentno (required): the invoice/document number to fetch

    Returns JSON list (usually single object) with the selected invoice fields.
    """
    documentno = request.args.get('documentno')
    if not documentno:
        return jsonify({"error": "documentno query parameter is required"}), 400

    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            query = """
SELECT
    orgtrx.NAME                                 AS Organisation_Trx,
    c.TOTALLINES,
    c.GRANDTOTAL,
    doctype.NAME                                AS Code_journal,
    c.DOCSTATUS,
    c.C_BPartner_ID,
    CASE c.DOCSTATUS
        WHEN 'CL' THEN 'Clôturé'
        WHEN 'CO' THEN 'Achevé'
        WHEN 'DR' THEN 'Brouillon'
        WHEN 'IN' THEN 'Inactif'
        WHEN 'RE' THEN 'Extourné'
        WHEN 'VO' THEN 'Annulé'
        ELSE c.DOCSTATUS
    END                                          AS Statut_document,
    c.DOCACTION,
    c.ISPAID,
    c.C_INVOICE_ID,
    client.NAME                                  AS Société,
    org.NAME                                     AS Organisation,
    ord.DOCUMENTNO                               AS Ordre_de_vente,
    c.DATEORDERED,
    c.DOCUMENTNO,
    c.POREFERENCE,
    c.DESCRIPTION,
    doctypetarget.NAME                           AS Type_document,
    c.DATEINVOICED,
    c.DATEACCT,
    partner.NAME                                 AS Client,
    usr.NAME                                     AS Contact,
    pricelist.NAME                               AS Tarif,
    curr.ISO_CODE                                AS Devise,
    bploc.NAME                                   AS Adresse_du_tiers,
    salesrep.NAME                                AS Vendeur,
    c.PAYMENTRULE,
    CASE c.PAYMENTRULE
        WHEN 'P' THEN 'A credit'
        WHEN 'B' THEN 'Espece'
        WHEN 'S' THEN 'Cheque'
        WHEN 'D' THEN 'Debit immediat'
        WHEN 'T' THEN 'Virement'
        WHEN 'K' THEN 'Carte de credit'
        WHEN 'V' THEN 'Versement'
        ELSE c.PAYMENTRULE
    END                                          AS PaymentRuleLabel,
    c.DOCACTION,
    CASE UPPER(NVL(c.DOCACTION,'-'))
        WHEN 'VO' THEN 'Annuler'
        WHEN 'RC' THEN 'Corriger'
        WHEN 'PR' THEN 'Réserver'
        WHEN 'CO' THEN 'Traiter'
        WHEN 'CL' THEN 'Clôturer'
        WHEN '--'  THEN 'Pas d_action'
        ELSE c.DOCACTION
    END                                          AS Action_Status,
    zsub.NAME                                    AS Sous_methode_de_paiement,
    c.ISSELFSERVICE,
    payterm.NAME                                 AS Delai_de_paiement
FROM C_Invoice c
LEFT JOIN AD_Client client               ON client.AD_Client_ID = c.AD_CLIENT_ID
LEFT JOIN AD_Org org                     ON org.AD_Org_ID = c.AD_ORG_ID
LEFT JOIN AD_Org orgtrx                  ON orgtrx.AD_Org_ID = c.AD_ORGTRX_ID
LEFT JOIN C_Order ord                    ON ord.C_Order_ID = c.C_ORDER_ID
LEFT JOIN C_DocType doctype              ON doctype.C_DocType_ID = c.C_DOCTYPE_ID
LEFT JOIN C_DocType doctypetarget        ON doctypetarget.C_DocType_ID = c.C_DOCTYPETARGET_ID
LEFT JOIN C_BPartner partner             ON partner.C_BPartner_ID = c.C_BPARTNER_ID
LEFT JOIN AD_User usr                    ON usr.AD_User_ID = c.AD_USER_ID
LEFT JOIN AD_User salesrep               ON salesrep.AD_User_ID = c.SALESREP_ID
LEFT JOIN M_PriceList pricelist          ON pricelist.M_PriceList_ID = c.M_PRICELIST_ID
LEFT JOIN C_Currency curr                ON curr.C_Currency_ID = c.C_CURRENCY_ID
LEFT JOIN C_PaymentTerm payterm          ON payterm.C_PaymentTerm_ID = c.C_PAYMENTTERM_ID
LEFT JOIN ZSubPaymentRule zsub           ON zsub.ZSubPaymentRule_ID = c.ZSUBPAYMENTRULE_ID
LEFT JOIN C_BPartner_Location bploc ON bploc.C_BPartner_Location_ID = c.C_BPartner_Location_ID

WHERE c.DOCUMENTNO = :documentno
"""

            cursor.execute(query, {"documentno": documentno})
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            data = [dict(zip(columns, row)) for row in rows]
            return jsonify(data)
    except Exception as e:
        logger.error(f"Error in /factures: {e}")
        return jsonify({"error": "An error occurred while fetching facture."}), 500




@app.route('/facture-lines', methods=['GET'])
def facture_lines_route():
    """Return invoice lines for a given invoice.

    Query params:
      - invoiceid : C_Invoice_ID (preferred)
      - documentno: invoice DOCUMENTNO (optional, will be resolved to invoice id)
    """
    invoiceid = request.args.get('invoiceid')
    documentno = request.args.get('documentno')

    if not invoiceid and not documentno:
        return jsonify({"error": "invoiceid or documentno query parameter is required"}), 400

    try:
        logger.info(f"/facture-lines called with invoiceid={invoiceid} documentno={documentno}")
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()

            # If documentno provided, resolve to invoice id
            if not invoiceid and documentno:
                cursor.execute("SELECT C_Invoice_ID FROM C_Invoice WHERE DOCUMENTNO = :doc", {"doc": documentno})
                row = cursor.fetchone()
                if not row:
                    logger.info("No invoice found for documentno=%s", documentno)
                    return jsonify({"error": "No invoice found with that documentno"}), 404
                invoiceid = row[0]

            # Try to coerce invoiceid to integer if possible
            try:
                invoiceid_param = int(invoiceid)
            except Exception:
                invoiceid_param = invoiceid  # leave as-is; the DB driver can accept string binds

            query = """
SELECT
    il.C_InvoiceLine_ID,
    il.A_Asset_ID,
    asset.NAME                             AS Immobilisation,
    il.AD_Client_ID,
    cli.NAME                                AS Société,
    il.AD_Org_ID,
    org.NAME                                AS Organisation,
    il.C_Invoice_ID,
    inv.DOCUMENTNO                          AS Facture,
        il.Line,

        -- Replaced M_InOutLine_ID with composed delivery info
        NVL(TO_CHAR(iol.Line), '')
            || '_' || NVL(TO_CHAR(iol.MovementQty), '')
            || '_' || NVL(prod.NAME, '')
            || '_' || NVL(io.DOCUMENTNO, '')
            || ' - ' || NVL(TO_CHAR(io.MovementDate, 'DD/MM/YYYY'), '')
            || ' - ' || NVL(ord.DOCUMENTNO, '')
            || ' - ' || NVL(TO_CHAR(ord.DateOrdered, 'DD/MM/YYYY'), '')
            || ' - ' || NVL(bp.NAME, '')
            AS Ligne_livraison,

    -- Replaced il.C_OrderLine_ID with composed field
    NVL(TO_CHAR(col.Line),'')
        || '_' || NVL(prod.NAME,'')
        || '_' || NVL(ord.DOCUMENTNO,'')
        || ' - ' || NVL(TO_CHAR(ord.DATEORDERED,'DD/MM/YYYY'),'')
        || '_' || NVL(TO_CHAR(il.LineNetAmt,'FM999G999G999D00','NLS_NUMERIC_CHARACTERS = ''.,'''),'0,00')
        AS Ligne_commande_de_vente,

    il.M_Product_ID,
    prod.NAME                               AS Article,
    il.C_Charge_ID,
    chg.NAME                                AS Charge,
    il.M_AttributeSetInstance_ID,
    masi.DESCRIPTION                        AS Lot,
    il.S_ResourceAssignment_ID,
    il.DESCRIPTION,
    il.QtyInvoiced,
    il.QtyEntered,
    il.C_UOM_ID,
    uom_trl.NAME                            AS Unité,
    il.PriceEntered,
    il.PriceActual,
    il.PriceList,
    il.C_Tax_ID,
    tx.NAME                                 AS TVA,
    il.AD_OrgTrx_ID,
    orgtrx.NAME                             AS Organisation_Trx,
    il.LineNetAmt,
    il.IsDescription,
    il.IsPrinted,
    il.Repricing,
    il.XX_BLInvoiceLine_ID
FROM C_InvoiceLine il
LEFT JOIN C_Invoice                   inv    ON inv.C_Invoice_ID   = il.C_Invoice_ID
LEFT JOIN A_Asset                     asset  ON asset.A_Asset_ID    = il.A_Asset_ID
LEFT JOIN AD_Client                   cli    ON cli.AD_Client_ID   = il.AD_Client_ID
LEFT JOIN AD_Org                      org    ON org.AD_Org_ID      = il.AD_Org_ID
LEFT JOIN M_Product                   prod   ON prod.M_Product_ID  = il.M_Product_ID
LEFT JOIN C_Charge                    chg    ON chg.C_Charge_ID    = il.C_Charge_ID
LEFT JOIN M_AttributeSetInstance      masi   ON masi.M_AttributeSetInstance_ID = il.M_AttributeSetInstance_ID
LEFT JOIN C_UOM                       uom    ON uom.C_UOM_ID      = il.C_UOM_ID
LEFT JOIN C_UOM_TRL                   uom_trl ON uom_trl.C_UOM_ID  = uom.C_UOM_ID
LEFT JOIN C_Tax                       tx     ON tx.C_Tax_ID       = il.C_Tax_ID
LEFT JOIN AD_Org                      orgtrx ON orgtrx.AD_Org_ID   = il.AD_OrgTrx_ID
LEFT JOIN C_OrderLine                 col    ON col.C_OrderLine_ID = il.C_OrderLine_ID
LEFT JOIN C_Order                     ord    ON ord.C_Order_ID     = col.C_Order_ID
LEFT JOIN M_InOutLine                 iol    ON iol.M_InOutLine_ID = il.M_InOutLine_ID
LEFT JOIN M_InOut                     io     ON io.M_InOut_ID      = iol.M_InOut_ID
LEFT JOIN C_BPartner                  bp     ON bp.C_BPartner_ID   = io.C_BPartner_ID
WHERE il.C_Invoice_ID = :invoiceid
ORDER BY il.Line
"""

            logger.info("Executing facture-lines query for invoiceid=%s", invoiceid_param)
            cursor.execute(query, {"invoiceid": invoiceid_param})
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            data = [dict(zip(columns, row)) for row in rows]
            logger.info("/facture-lines returned %d rows", len(data))
            return jsonify(data)
    except Exception as e:
        logger.exception("Error in /facture-lines")
        # Return error message for debugging (remove or mask in production)
        return jsonify({"error": "An error occurred while fetching facture lines.", "detail": str(e)}), 500




from io import BytesIO
from datetime import datetime
from reportlab.lib import colors
from reportlab.lib.pagesizes import A4
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont



def generate_societe_pdf(data, return_elements=False):
    elements = []
    styles = getSampleStyleSheet()

    # --- Styles ---
    title_style = ParagraphStyle(
        'Title', parent=styles['Heading1'], fontSize=14,
        textColor=colors.HexColor('#222222'), spaceAfter=6, fontName='Helvetica-Bold'
    )
    subtitle_style = ParagraphStyle(
        'Subtitle', parent=styles['Normal'], fontSize=10,
        textColor=colors.HexColor('#555555'), spaceAfter=12
    )
    info_style = ParagraphStyle(
        'Info', parent=styles['Normal'], fontSize=9,
        textColor=colors.HexColor('#333333'), leading=14
    )

    # --- Logo ---
    logo_path = '/opt/lampp/htdocs/bnm_web/assets/log.png'
    if os.path.exists(logo_path):
        try:
            logo_img = Image(logo_path, width=110, height=70)  # Bigger logo
            logo_img.hAlign = 'LEFT'
            # Increase negative space to move logo even more left
            logo_cell = [Spacer(-30, 1), logo_img]
        except:
            logo_cell = [Spacer(0, 1), Spacer(110, 70)]
    else:
        logo_cell = [Spacer(0, 1), Spacer(110, 70)]

    # --- Left Info ---
    left_info_html = f"""
        <b>Rte 05 n°46 AIN SMARA</b><br/>
        <b>Constantine</b><br/>
        <b>Tél :</b> {data.get('TELE_societe', '031 97 55 93')}<br/>
        <b>Fax :</b> {data.get('FAX_societe', '031 97 55 94')}<br/>
        <b>Email :</b> {data.get('EMAIL_SOCIETE', 'bnmparapharm@gmail.com')}<br/>
        <b>Site :</b> {data.get('WEBSITE_SOCIETE', 'www.bnm.com')}
    """
    left_info_para = Paragraph(left_info_html, info_style)

    # --- Right Info ---
    right_info_html = f"""
        <b>RC :</b> {data.get('NRC_SOCIETE', '25/00-0071142 B 15')}<br/>
        <b>NIF :</b> {data.get('NIF_SOCIETE', '001525007114238')}<br/>
        <b>NAI :</b> {data.get('NAI_SOCIETE', '25100124031')}<br/>
        <b>NIS :</b> {data.get('NIS_SOCIETE', '001525100039948')}<br/>
        <b>RIB :</b> {data.get('RIB_SOCIETE', '00100850030000097130')}<br/>
        <b>Capital :</b> {data.get('CAPITAL_SOCIEETE', '12 300 000,00 DA')}
    """
    right_info_para = Paragraph(right_info_html, info_style)

    # --- Header Layout ---
    header_table = Table(
        [[logo_cell, left_info_para, right_info_para]],
        colWidths=[120, 200, 200]
    )
    header_table.setStyle(TableStyle([
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('LEFTPADDING', (0, 0), (0, 0), 0),  # No left padding for logo cell
        ('LEFTPADDING', (1, 0), (2, 0), 4),
        ('RIGHTPADDING', (0, 0), (-1, -1), 4),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 0)
    ]))
    elements.append(header_table)
    elements.append(Spacer(1, 10))

    # --- Company Name + Activity ---
    elements.append(Paragraph('<b>SARL BNM PARAPHARM</b>', title_style))
    elements.append(Paragraph('Distribution en Gros de Produits Parapharmaceutiques', subtitle_style))

    # --- Divider Line ---
    line = Table([['']], colWidths=[500], rowHeights=[1])
    line.setStyle(TableStyle([('LINEBELOW', (0, 0), (-1, -1), 1.2, colors.black)]))
    elements.append(line)
    elements.append(Spacer(1, 15))

    if return_elements:
        return elements

    buffer = BytesIO()
    doc = SimpleDocTemplate(buffer, pagesize=A4, leftMargin=30, rightMargin=30, topMargin=30, bottomMargin=30)
    doc.build(elements)
    buffer.seek(0)
    return buffer


# ------------------------------
# 🔹 Generate Client Header PDF – Final Professional Invoice Layout
# ------------------------------


def generate_client_pdf(data, invoice_data=None, return_elements=False):
    # Register DejaVu Sans font for emoji/icon support
    # Default font for most content
    default_font = 'Helvetica'
    # Register Symbola for icons only
    symbola_path = '/opt/lampp/htdocs/bnm_web/assets/Symbola.ttf'
    if os.path.exists(symbola_path):
        try:
            pdfmetrics.registerFont(TTFont('Symbola', symbola_path))
            print("[DEBUG] Symbola font loaded for icons.")
        except Exception as e:
            print(f"[DEBUG] Failed to load Symbola: {e}")
        icon_font = 'Symbola'
    else:
        print("[DEBUG] Symbola.ttf not found, using Helvetica for icons.")
        icon_font = default_font

    elements = []
    styles = getSampleStyleSheet()

    # --- Styles ---
    section_title_style = ParagraphStyle(
        'SectionTitle',
        fontSize=9,
        fontName='Helvetica-Bold',
        textColor=colors.white,
        leftIndent=6,
        rightIndent=6,
        spaceAfter=4
    )

    content_style = ParagraphStyle(
        'Content',
        fontSize=9,
        leading=13,
        leftIndent=8,
        rightIndent=8,
        spaceAfter=2,
        textColor=colors.HexColor('#222222')
    )

    label_style = ParagraphStyle(
        'Label',
        fontSize=9,
        fontName='Helvetica-Bold',
        textColor=colors.black
    )

    # --- Helper for case-insensitive key fetching ---
    def get_field(d, *keys):
        for k in keys:
            if k in d:
                return d[k] or ""
        for k in keys:
            for dk in d:
                if dk.lower() == k.lower():
                    return d[dk] or ""
        return ""

    # --- Extract data ---
    client_name = get_field(data, 'Client_Name')
    address = get_field(data, 'Location_ID_client')
    activity = get_field(data, 'Activite_client')
    rc = get_field(data, 'RC_client')
    ai = get_field(data, 'AI_client')
    nif = get_field(data, 'NIF_client')
    nis = get_field(data, 'NIS_client')
    region = get_field(data, 'Region_de_vente', 'region_de_vente', 'region')
    commande = get_field(data, 'Ordre_de_vente', 'ORDRE_DE_VENTE', 'commande')
    vendeur = get_field(data, 'Vendeur', 'VENDEUR', 'salesrep_name')
    delai_paiement = get_field(data, 'Delai_de_paiement', 'DELAI_DE_PAIEMENT')
    mode_paiement = get_field(data, 'Mode_de_paiement', 'MODE_DE_PAIEMENT', 'Sous_methode_de_paiement')
    print_date = datetime.now().strftime("%d/%m/%Y")

    # --- Region and Print Date at the top with PNG icons ---
    from reportlab.platypus import Image as RLImage
    gps_icon_path = '/opt/lampp/htdocs/bnm_web/assets/gps.png'
    date_icon_path = '/opt/lampp/htdocs/bnm_web/assets/date.png'
    icon_size = 14  # px
    # Create icon images if files exist, else fallback to blank
    if os.path.exists(gps_icon_path):
        gps_icon = RLImage(gps_icon_path, width=icon_size, height=icon_size)
    else:
        gps_icon = Spacer(icon_size, icon_size)
    if os.path.exists(date_icon_path):
        date_icon = RLImage(date_icon_path, width=icon_size, height=icon_size)
    else:
        date_icon = Spacer(icon_size, icon_size)

    # Compose region/date info as a table row for alignment, with label and value in one Paragraph
    region_text = f'<b>Région de Vente :</b> {region or "—"}'
    date_text = f'<b>Date d\'Impression :</b> {print_date}'
    region_para = Paragraph(region_text, content_style)
    date_para = Paragraph(date_text, content_style)
    info_table = Table(
        [[gps_icon, region_para, Spacer(12, 1), date_icon, date_para]],
        colWidths=[icon_size+2, None, 12, icon_size+2, None],
        hAlign='LEFT'
    )
    info_table.setStyle(TableStyle([
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('LEFTPADDING', (0, 0), (-1, -1), 0),
        ('RIGHTPADDING', (0, 0), (-1, -1), 0),
        ('TOPPADDING', (0, 0), (-1, -1), 0),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 0),
        ('ALIGN', (1, 0), (1, 0), 'LEFT'),
        ('ALIGN', (4, 0), (4, 0), 'LEFT'),
    ]))
    elements.append(info_table)
    elements.append(Spacer(1, 8))

    # --- Client Info Block ---
    client_lines = []
    if client_name:
        client_lines.append(f"<b>Client :</b> {client_name}")
    if address:
        client_lines.append(f"<b>Adresse :</b> {address}")
    # Add Activité always, even if empty, to ensure it appears in the right place
    client_lines.append(f"<b>Activité :</b> {activity if activity else '—'}")
    if rc:
        client_lines.append(f"<b>N° RC :</b> {rc}")
    if ai:
        client_lines.append(f"<b>N° AI :</b> {ai}")
    if nif:
        client_lines.append(f"<b>N° NIF :</b> {nif}")
    if nis:
        client_lines.append(f"<b>N° NIS :</b> {nis}")
    # Do not include Commande and Vendeur in client info block; only in Info Facture

    client_content = "<br/>".join(client_lines)
    client_para = Paragraph(client_content, content_style)
    # Délai de Paiement as a single row under the client table
    delai_value = delai_paiement if delai_paiement else '—'
    delai_paiement_para = Paragraph(f"<b>Délai de Paiement :</b> <b>{delai_value}</b>", content_style)
    delai_mode_table = Table(
        [[delai_paiement_para]],
        colWidths=[260],
        hAlign='LEFT'
    )
    delai_mode_table.setStyle(TableStyle([
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('ALIGN', (0, 0), (0, 0), 'LEFT'),
        ('LEFTPADDING', (0, 0), (-1, -1), 6),
        ('RIGHTPADDING', (0, 0), (-1, -1), 6),
        ('TOPPADDING', (0, 0), (-1, -1), 2),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 2),
    ]))

    # --- Invoice Info Block ---
    inv_no = invoice_data.get("invoice_no", "—") if invoice_data else "—"
    inv_date = invoice_data.get("date", "—") if invoice_data else "—"
    commande = data.get("Ordre_de_vente", "—")
    vendeur = data.get("Vendeur", "—")

    invoice_lines = [
        f"<b>N° Facture :</b> {inv_no}",
        f"<b>Date Facture :</b> {inv_date}",
        f"<b>Commande :</b> {commande}",
        f"<b>Vendeur :</b> {vendeur}"
    ]
    invoice_content = "<br/>".join(invoice_lines)
    invoice_para = Paragraph(invoice_content, content_style)

    # --- FACTURÉ À as a big label above the client table ---
    fact_a_style = ParagraphStyle(
        'FactureA', parent=styles['Heading2'], fontSize=13, fontName='Helvetica-Bold', textColor=colors.HexColor('#2C3E50'), spaceAfter=8, alignment=0
    )
    info_facture_style = ParagraphStyle(
        'InfoFacture', parent=styles['Heading2'], fontSize=13, fontName='Helvetica-Bold', textColor=colors.HexColor('#2C3E50'), spaceAfter=8, alignment=0
    )
    # --- Side-by-side layout: both blocks in the same row ---

    # Make all client data bold
    bold_content_style = ParagraphStyle(
        'BoldContent',
        fontSize=9,
        leading=13,
        leftIndent=8,
        rightIndent=8,
        spaceAfter=2,
        textColor=colors.HexColor('#222222'),
        fontName='Helvetica-Bold'
    )

    client_lines = []
    if client_name:
        client_lines.append(f"<b>Client :</b> <b>{client_name}</b>")
    if address:
        client_lines.append(f"<b>Adresse :</b> <b>{address}</b>")
    client_lines.append(f"<b>Activité :</b> <b>{activity if activity else '—'}</b>")
    if rc:
        client_lines.append(f"<b>N° RC :</b> <b>{rc}</b>")
    if ai:
        client_lines.append(f"<b>N° AI :</b> <b>{ai}</b>")
    if nif:
        client_lines.append(f"<b>N° NIF :</b> <b>{nif}</b>")
    if nis:
        client_lines.append(f"<b>N° NIS :</b> <b>{nis}</b>")

    client_content = "<br/>".join(client_lines)
    client_para = Paragraph(client_content, bold_content_style)
    delai_value = delai_paiement if delai_paiement else '—'
    delai_paiement_para = Paragraph(f"<b>Délai de Paiement :</b> <b>{delai_value}</b>", bold_content_style)
    delai_mode_table = Table(
        [[delai_paiement_para]],
        colWidths=[260],
        hAlign='LEFT'
    )
    delai_mode_table.setStyle(TableStyle([
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('ALIGN', (0, 0), (0, 0), 'LEFT'),
        ('LEFTPADDING', (0, 0), (-1, -1), 6),
        ('RIGHTPADDING', (0, 0), (-1, -1), 6),
        ('TOPPADDING', (0, 0), (-1, -1), 2),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 2),
    ]))

    # --- Info Facture Table: all bold ---
    inv_no = invoice_data.get("invoice_no", "—") if invoice_data else "—"
    inv_date = invoice_data.get("date", "—") if invoice_data else "—"
    commande = data.get("Ordre_de_vente", "—")
    vendeur = data.get("Vendeur", "—")
    invoice_lines = [
        f"<b>N° Facture :</b> <b>{inv_no}</b>",
        f"<b>Date Facture :</b> <b>{inv_date}</b>",
        f"<b>Commande :</b> <b>{commande}</b>",
        f"<b>Vendeur :</b> <b>{vendeur}</b>"
    ]
    invoice_content = "<br/>".join(invoice_lines)
    invoice_para = Paragraph(invoice_content, bold_content_style)
    client_table = Table(
        [[client_para]],
        colWidths=[300],
        hAlign='LEFT'
    )
    client_table.setStyle(TableStyle([
        ('BOX', (0, 0), (-1, -1), 1, colors.HexColor('#2C3E50')),
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('LEFTPADDING', (0, 0), (-1, -1), 6),
        ('RIGHTPADDING', (0, 0), (-1, -1), 6),
        ('TOPPADDING', (0, 0), (-1, -1), 2),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 4),
    ]))
    client_table.wrapOn(None, 0, 0)
    client_height = client_table._height
    invoice_table = Table(
        [[invoice_para]],
        colWidths=[170],
        rowHeights=[client_height],
        hAlign='CENTER'
    )
    invoice_table.setStyle(TableStyle([
        ('BOX', (0, 0), (-1, -1), 1, colors.HexColor('#2C3E50')),
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
        ('LEFTPADDING', (0, 0), (-1, -1), 0),
        ('RIGHTPADDING', (0, 0), (-1, -1), 0),
        ('TOPPADDING', (0, 0), (-1, -1), 0),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 0),
    ]))
    # Custom logic for Mode de Paiement: if 'static' show 'A Terme', if 'auto' show actual value
    mode_value = mode_paiement if mode_paiement else '—'
    if str(mode_value).strip().lower() == 'static':
        mode_value_display = 'A Terme'
    elif str(mode_value).strip().lower() == 'auto':
        mode_value_display = mode_paiement  # fallback to actual value (could be improved if needed)
    else:
        mode_value_display = mode_value
    mode_paiement_para = Paragraph(f"<b>Mode de Paiement :</b> <b>{mode_value_display}</b>", bold_content_style)

    client_block = [Paragraph("<b>FACTURÉ À</b>", fact_a_style), client_table, delai_mode_table, Spacer(1, 4)]
    invoice_block = [Paragraph("<b>Info Facture</b>", info_facture_style), invoice_table, Spacer(1, 2), mode_paiement_para]
    spacer_col = Spacer(30, 1)  # 30pt wide blank space
    row_table = Table(
        [[client_block, spacer_col, invoice_block]],
        colWidths=[270, 40, 180],  # 40pt space between tables
        hAlign='LEFT'
    )
    row_table.setStyle(TableStyle([
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('LEFTPADDING', (0, 0), (-1, -1), 0),
        ('RIGHTPADDING', (0, 0), (-1, -1), 0),
        ('TOPPADDING', (0, 0), (-1, -1), 0),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 0),
    ]))
    elements.append(row_table)
    elements.append(Spacer(1, 18))
    # Add the data lines table (invoice lines) below the header blocks
    elements.append(build_invoice_lines_table(data, invoice_data))
    elements.append(Spacer(1, 12))
    # Add totals box below the invoice table
    elements.append(build_totals_box(data, invoice_data))
    elements.append(Spacer(1, 14))

    # --- (Removed region/print date from bottom, now at top) ---
    elements.append(Spacer(1, 12))

    # --- Return or Build PDF --
    # -
    if return_elements:
        return elements

    buffer = BytesIO()
    doc = SimpleDocTemplate(buffer, pagesize=A4, leftMargin=30, rightMargin=30, topMargin=30, bottomMargin=30)
    doc.build(elements)
    buffer.seek(0)
    return buffer
# --- Standalone invoice lines table builder ---
def build_invoice_lines_table(data, invoice_data=None, remise5_enabled=False):
    from reportlab.platypus import Paragraph, Table
    from reportlab.lib import colors
    from reportlab.lib.styles import ParagraphStyle

    bold_style = ParagraphStyle('Bold', fontName='Helvetica-Bold', fontSize=8, alignment=1)
    headers = [
        Paragraph('N° EXP', bold_style),
        Paragraph('Désignation', bold_style),
        Paragraph('QT', bold_style),
        Paragraph('Lot', bold_style),
        Paragraph('Peremp', bold_style),
        Paragraph('Prix U', bold_style),
        Paragraph('%R', bold_style),
        Paragraph('PPA', bold_style),
        Paragraph('Tva', bold_style),
        Paragraph('TTC', bold_style)
    ]

    # Get facture/document number
    facture_no = None
    if invoice_data and 'invoice_no' in invoice_data:
        facture_no = invoice_data['invoice_no']
    elif data and ('DOCUMENTNO' in data or 'documentno' in data):
        facture_no = data.get('DOCUMENTNO') or data.get('documentno')
    if not facture_no:
        return Table([headers], colWidths=[55, 122, 28, 35, 45, 50, 30, 50, 30, 35, 60])

    rows = []
    article_counter = 1
    try:
        with DB_POOL.acquire() as conn:
            cur = conn.cursor()
            sql = '''
SELECT 
    io.DOCUMENTNO AS Expedition,
    TO_CHAR(io.MovementDate, 'DD/MM/YYYY') AS dateexp,
    MAX(prod.NAME) AS Article,
    MAX(masi.LOT) AS Lot,
    il.C_InvoiceLine_ID,
    TO_CHAR(MAX(masi.GUARANTEEDATE), 'DD/MM/YYYY') AS Guaranteedate,
    MAX(
        (SELECT valuenumber 
           FROM M_AttributeInstance ai
          WHERE ai.M_AttributeSetInstance_ID = il.M_AttributeSetInstance_ID 
            AND ai.M_Attribute_ID = 1000503)
    ) AS PPA,
    AVG(il.pricelist) AS Prix_Unitaire,
    SUM(il.QtyInvoiced) AS Quantite,
    AVG(NVL(col.Discount, 0)) AS Remise,
    (SELECT MAX(tax.Rate) 
     FROM C_TaxCategory cat 
     LEFT JOIN C_Tax tax ON tax.C_TaxCategory_ID = cat.C_TaxCategory_ID
     WHERE cat.C_TaxCategory_ID = prod.XX_TaxCategory_ID
     AND ROWNUM = 1) AS TVA
FROM C_InvoiceLine il
LEFT JOIN M_Product prod ON prod.M_Product_ID = il.M_Product_ID
LEFT JOIN C_OrderLine col ON col.C_OrderLine_ID = il.C_OrderLine_ID
LEFT JOIN M_InOutLine iol ON iol.M_InOutLine_ID = il.M_InOutLine_ID
LEFT JOIN M_InOut io ON io.M_InOut_ID = iol.M_InOut_ID
LEFT JOIN C_Invoice inv ON inv.C_Invoice_ID = il.C_Invoice_ID
LEFT JOIN M_AttributeSetInstance masi ON masi.M_AttributeSetInstance_ID = il.M_AttributeSetInstance_ID
WHERE io.DOCUMENTNO IS NOT NULL
  AND inv.DOCUMENTNO = :fcture
GROUP BY 
    io.DOCUMENTNO,
    io.MovementDate,
    prod.NAME,
    prod.XX_TaxCategory_ID,
    il.C_InvoiceLine_ID
ORDER BY 
    io.DOCUMENTNO,
    prod.NAME
            '''
            cur.execute(sql, {"fcture": facture_no})
            for row in cur.fetchall():
                (
                    expedition, dateexp, article, lot, invoice_line_id, guaranteedate, ppa, prix_unitaire, quantite, remise, tva
                ) = row

                # --- Calculate TTC ---
                try:
                    from decimal import Decimal, ROUND_HALF_UP
                    
                    # Use Decimal for exact calculations - round at each step to 2 decimals
                    prix_unitaire_d = Decimal(str(prix_unitaire or 0))
                    remise_d = Decimal(str(remise or 0))
                    tva_d = Decimal(str(tva or 0))
                    quantite_d = Decimal(str(quantite or 0))
                    
                    original_remise_d = remise_d  # Keep original for display
                    
                    # Apply remise5 logic: cap discount at 5% if enabled (but not for 100%)
                    if remise5_enabled and remise_d > Decimal('5.0') and remise_d < Decimal('100.0'):
                        remise_d = Decimal('5.0')
                    
                    # If remise5 is enabled, convert Prix U to HT (remove TVA) and round to 5 decimals
                    if remise5_enabled:
                        prix_unitaire_d = (prix_unitaire_d / (Decimal('1') + (tva_d / Decimal('100')))).quantize(Decimal('0.00001'), rounding=ROUND_HALF_UP)
                    
                    # Calculate with rounding at each step to 5 decimals
                    prix_remise_d = (prix_unitaire_d - (prix_unitaire_d * remise_d / Decimal('100'))).quantize(Decimal('0.00001'), rounding=ROUND_HALF_UP)
                    ttc_d = (prix_remise_d * quantite_d).quantize(Decimal('0.00001'), rounding=ROUND_HALF_UP)
                    
                    # Keep as Decimal for formatting
                    prix_unitaire_display = prix_unitaire_d
                    remise_display = remise_d if (remise5_enabled and original_remise_d > Decimal('5.0') and original_remise_d < Decimal('100.0')) else original_remise_d
                    ttc_display = ttc_d
                    
                except Exception as e:
                    # Fallback to basic calculation
                    prix_unitaire_d = Decimal(str(prix_unitaire or 0))
                    remise_d = Decimal(str(remise or 0))
                    tva_d = Decimal(str(tva or 0))
                    quantite_d = Decimal(str(quantite or 0))
                    original_remise_d = remise_d
                    
                    if remise5_enabled and remise_d > Decimal('5.0') and remise_d < Decimal('100.0'):
                        remise_d = Decimal('5.0')
                    if remise5_enabled:
                        prix_unitaire_d = prix_unitaire_d / (Decimal('1') + (tva_d / Decimal('100')))
                    
                    prix_remise_d = prix_unitaire_d - (prix_unitaire_d * remise_d / Decimal('100'))
                    ttc_d = prix_remise_d * quantite_d
                    
                    prix_unitaire_display = prix_unitaire_d
                    remise_display = remise_d if (remise5_enabled and original_remise_d > Decimal('5.0') and original_remise_d < Decimal('100.0')) else original_remise_d
                    ttc_display = ttc_d

                def format_decimal(dec_val, decimals=2):
                    """Format Decimal value to string with exact rounding at display time only"""
                    try:
                        from decimal import Decimal, ROUND_HALF_UP
                        if not isinstance(dec_val, Decimal):
                            dec_val = Decimal(str(dec_val))
                        # Round only for display
                        rounded = dec_val.quantize(Decimal('0.' + '0' * decimals), rounding=ROUND_HALF_UP)
                        return f"{rounded:,.{decimals}f}".replace(",", " ")
                    except Exception:
                        return str(dec_val) if dec_val else ''

                # Format for display with exact rounding
                remise_fmt = format_decimal(remise_display, 2)
                prix_unitaire_fmt = format_decimal(prix_unitaire_display, 2)
                ppa_fmt = format_decimal(Decimal(str(ppa or 0)), 2)
                ttc_fmt = format_decimal(ttc_display, 2)
                quantite_fmt = format_decimal(quantite_d, 0)
                # TVA field empty when remise5 is enabled
                tva_fmt = '—' if remise5_enabled else (format_decimal(tva_d, 2) if tva not in (None, '', 'None') else '—')
                expedition_display = f"{expedition or ''} {dateexp or ''}".strip()
                designation = f"<b>{article_counter}-</b> {article or ''}"

                rows.append([
                    expedition_display,
                    designation,
                    quantite_fmt,
                    lot or '',
                    guaranteedate or '',
                    prix_unitaire_fmt,
                    remise_fmt,
                    ppa_fmt,
                    tva_fmt,
                    ttc_fmt
                ])
                article_counter += 1
    except Exception as e:
        return Table([headers], colWidths=[55, 120, 30, 35, 45, 50, 30, 50, 30, 35, 60])

    # --- Build final table ---
    data_table = [headers] + rows
    col_widths = [75, 170, 38, 53, 53, 45, 28, 40, 28, 50]
    last_expedition = None
    for i, row in enumerate(data_table):
        if i == 0:
            continue
        if row[0] == last_expedition:
            row[0] = ''
        else:
            last_expedition = row[0]
        for idx in [0, 1, 3, 4]:
            if row[idx]:
                if idx == 1:
                    row[idx] = Paragraph(str(row[idx]), ParagraphStyle('wrap', fontName='Helvetica', fontSize=8, alignment=0, wordWrap='CJK', allowOrphans=1))
                else:
                    row[idx] = Paragraph(str(row[idx]), ParagraphStyle('wrap', fontName='Helvetica', fontSize=8, alignment=0, wordWrap='CJK'))

    from reportlab.platypus import TableStyle
    table = Table(data_table, colWidths=col_widths, repeatRows=1)
    table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#F5F8FA')),
        ('TEXTCOLOR', (0, 0), (-1, 0), colors.HexColor('#1A4C8B')),
        ('ALIGN', (0, 0), (-1, 0), 'CENTER'),
        ('ALIGN', (0, 1), (1, -1), 'LEFT'),
        ('ALIGN', (3, 1), (4, -1), 'LEFT'),
        ('ALIGN', (2, 1), (2, -1), 'CENTER'),
        ('ALIGN', (5, 1), (-1, -1), 'CENTER'),
        ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
        ('FONTSIZE', (0, 0), (-1, -1), 8),
        ('GRID', (0, 0), (-1, -1), 0.7, colors.HexColor('#2C3E50')),
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('ROWBACKGROUNDS', (1, 1), (-1, -1), [colors.white, colors.HexColor('#F5F8FA')]),
    ]))
    return table


def build_totals_box(data, invoice_data=None, remise5_enabled=False):
    """Build the totals box that appears below the invoice table"""
    from reportlab.platypus import Paragraph, Table, TableStyle, KeepTogether
    from reportlab.lib import colors
    from reportlab.lib.styles import ParagraphStyle
    
    # Get facture/document number
    facture_no = None
    if invoice_data and 'invoice_no' in invoice_data:
        facture_no = invoice_data['invoice_no']
    elif data and ('DOCUMENTNO' in data or 'documentno' in data):
        facture_no = data.get('DOCUMENTNO') or data.get('documentno')
    
    if not facture_no:
        return Paragraph("", ParagraphStyle('empty', fontSize=1))
    
    # Calculate totals
    total_ht = 0
    total_tva = 0
    total_ttc = 0
    d_timbre = 0
    
    try:
        with DB_POOL.acquire() as conn:
            cur = conn.cursor()
            
            # Fetch invoice lines and calculate totals
            sql = '''
SELECT 
    AVG(il.pricelist) AS Prix_Unitaire,
    SUM(il.QtyInvoiced) AS Quantite,
    AVG(NVL(col.Discount, 0)) AS Remise,
    (SELECT MAX(tax.Rate) 
     FROM C_TaxCategory cat 
     LEFT JOIN C_Tax tax ON tax.C_TaxCategory_ID = cat.C_TaxCategory_ID
     WHERE cat.C_TaxCategory_ID = prod.XX_TaxCategory_ID
     AND ROWNUM = 1) AS TVA
FROM C_InvoiceLine il
LEFT JOIN M_Product prod ON prod.M_Product_ID = il.M_Product_ID
LEFT JOIN C_OrderLine col ON col.C_OrderLine_ID = il.C_OrderLine_ID
LEFT JOIN M_InOutLine iol ON iol.M_InOutLine_ID = il.M_InOutLine_ID
LEFT JOIN M_InOut io ON io.M_InOut_ID = iol.M_InOut_ID
LEFT JOIN C_Invoice inv ON inv.C_Invoice_ID = il.C_Invoice_ID
WHERE io.DOCUMENTNO IS NOT NULL
  AND inv.DOCUMENTNO = :fcture
GROUP BY 
    io.DOCUMENTNO,
    prod.NAME,
    prod.XX_TaxCategory_ID,
    il.C_InvoiceLine_ID
            '''
            cur.execute(sql, {"fcture": facture_no})
            
            for row in cur.fetchall():
                prix_unitaire, quantite, remise, tva = row
                try:
                    from decimal import Decimal, ROUND_HALF_UP
                    
                    # Use Decimal for exact calculations
                    prix_unitaire_d = Decimal(str(prix_unitaire or 0))
                    remise_d = Decimal(str(remise or 0))
                    quantite_d = Decimal(str(quantite or 0))
                    tva_d = Decimal(str(tva or 0))
                    
                    # Apply remise5 logic: cap discount at 5% if enabled (but not for 100%)
                    if remise5_enabled and remise_d > Decimal('5.0') and remise_d < Decimal('100.0'):
                        remise_d = Decimal('5.0')
                    
                    # If remise5 is enabled, convert Prix U to HT (remove TVA) and round to 5 decimals
                    if remise5_enabled:
                        prix_unitaire_d = (prix_unitaire_d / (Decimal('1') + (tva_d / Decimal('100')))).quantize(Decimal('0.00001'), rounding=ROUND_HALF_UP)
                    
                    # Calculate line totals with rounding at each step to 5 decimals
                    prix_remise_d = (prix_unitaire_d - (prix_unitaire_d * remise_d / Decimal('100'))).quantize(Decimal('0.00001'), rounding=ROUND_HALF_UP)
                    line_ttc_d = (prix_remise_d * quantite_d).quantize(Decimal('0.00001'), rounding=ROUND_HALF_UP)
                    
                    # Total HT = TTC / (1 + TVA%), round to 5 decimals
                    line_ht_d = (line_ttc_d / (Decimal('1') + (tva_d / Decimal('100')))).quantize(Decimal('0.00001'), rounding=ROUND_HALF_UP)
                    
                    # Total TVA = TTC - HT, round to 5 decimals
                    line_tva_d = (line_ttc_d - line_ht_d).quantize(Decimal('0.00001'), rounding=ROUND_HALF_UP)
                    
                    total_ht += float(line_ht_d)
                    total_tva += float(line_tva_d)
                    total_ttc += float(line_ttc_d)
                except Exception as e:
                    pass
            
            # Fetch D.timbre
            cur.execute("SELECT CHARGEAMT FROM C_Invoice WHERE DOCUMENTNO = :fcture", {"fcture": facture_no})
            timbre_row = cur.fetchone()
            if timbre_row and timbre_row[0]:
                d_timbre = float(timbre_row[0])
    except Exception as e:
        print(f"Error calculating totals: {e}")
    
    # Format numbers
    def format_amount(val):
        try:
            return f"{float(val):,.2f}".replace(",", " ")
        except:
            return "0,00"
    
    # Function to convert number to French words
    def number_to_french_words(n):
        """Convert a number to French words for invoice amounts"""
        ones = ['', 'UN', 'DEUX', 'TROIS', 'QUATRE', 'CINQ', 'SIX', 'SEPT', 'HUIT', 'NEUF']
        teens = ['DIX', 'ONZE', 'DOUZE', 'TREIZE', 'QUATORZE', 'QUINZE', 'SEIZE', 'DIX-SEPT', 'DIX-HUIT', 'DIX-NEUF']
        tens = ['', '', 'VINGT', 'TRENTE', 'QUARANTE', 'CINQUANTE', 'SOIXANTE', 'SOIXANTE', 'QUATRE-VINGT', 'QUATRE-VINGT']
        
        if n == 0:
            return 'ZÉRO'
        
        def convert_hundreds(num):
            result = []
            h = num // 100
            t = (num % 100) // 10
            o = num % 10
            
            if h > 0:
                if h == 1:
                    result.append('CENT')
                else:
                    result.append(ones[h] + ' CENT')
                    if (t > 0 or o > 0):
                        pass
                    else:
                        result[-1] += 'S'
            
            if t == 1:
                result.append(teens[o])
            elif t >= 2:
                if t == 7 or t == 9:
                    if o == 1:
                        result.append(tens[t] + ' ET ONZE' if t == 7 else tens[t] + '-ONZE')
                    else:
                        result.append(tens[t] + ('-' + teens[o] if o > 0 else ''))
                        if t == 8 and o == 0:
                            result[-1] += 'S'
                else:
                    if o == 1 and t != 8:
                        result.append(tens[t] + ' ET UN')
                    elif o > 0:
                        result.append(tens[t] + '-' + ones[o])
                    else:
                        result.append(tens[t])
            elif o > 0 and t != 1:
                result.append(ones[o])
            
            return ' '.join(result)
        
        def convert_thousands(num):
            if num == 0:
                return ''
            elif num == 1:
                return 'MILLE'
            else:
                return convert_hundreds(num) + ' MILLE'
        
        def convert_millions(num):
            if num == 0:
                return ''
            elif num == 1:
                return 'UN MILLION'
            else:
                return convert_hundreds(num) + ' MILLIONS'
        
        millions = int(n) // 1000000
        thousands = (int(n) % 1000000) // 1000
        hundreds = int(n) % 1000
        
        parts = []
        if millions > 0:
            parts.append(convert_millions(millions))
        if thousands > 0:
            parts.append(convert_thousands(thousands))
        if hundreds > 0:
            parts.append(convert_hundreds(hundreds))
        
        return ' '.join(parts) if parts else 'ZÉRO'
    
    # Build totals box
    label_style = ParagraphStyle('TotalLabel', fontName='Helvetica-Bold', fontSize=11, alignment=0)
    value_style = ParagraphStyle('TotalValue', fontName='Helvetica-Bold', fontSize=11, alignment=2)
    
    totals_data = [
        [Paragraph('<b>Total HT:</b>', label_style), Paragraph(f'<b>{format_amount(total_ht)}</b>', value_style)],
        [Paragraph('<b>TVA:</b>', label_style), Paragraph(f'<b>{format_amount(total_tva)}</b>', value_style)],
        [Paragraph('<b>D.timbre</b>', label_style), Paragraph(f'<b>{format_amount(d_timbre)}</b>', value_style)],
        [Paragraph('<b>Total TTC:</b>', label_style), Paragraph(f'<b>{format_amount(total_ttc + d_timbre)}</b>', value_style)]
    ]
    
    totals_table = Table(totals_data, colWidths=[100, 120], hAlign='RIGHT')
    totals_table.setStyle(TableStyle([
        ('BOX', (0, 0), (-1, -1), 1.5, colors.black),
        ('INNERGRID', (0, 0), (-1, -1), 0.5, colors.black),
        ('ALIGN', (0, 0), (0, -1), 'LEFT'),
        ('ALIGN', (1, 0), (1, -1), 'RIGHT'),
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('LEFTPADDING', (0, 0), (-1, -1), 8),
        ('RIGHTPADDING', (0, 0), (-1, -1), 8),
        ('TOPPADDING', (0, 0), (-1, -1), 6),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
        ('BACKGROUND', (0, 3), (-1, 3), colors.HexColor('#F5F8FA')),
    ]))
    
    # Convert total TTC to words
    final_total = total_ttc + d_timbre
    dinars = int(final_total)
    centimes = int(round((final_total - dinars) * 100))
    
    dinars_text = number_to_french_words(dinars)
    centimes_text = number_to_french_words(centimes)
    
    # Create amount in words text
    amount_words_style = ParagraphStyle(
        'AmountWords',
        fontName='Helvetica',
        fontSize=10,
        alignment=0,
        spaceAfter=4
    )
    amount_words_bold_style = ParagraphStyle(
        'AmountWordsBold',
        fontName='Helvetica-Bold',
        fontSize=10,
        alignment=0,
        spaceAfter=8
    )
    
    arrete_text = Paragraph("Arrêté la présente facture à la somme de:", amount_words_style)
    amount_text = Paragraph(f"<b>{dinars_text} DINARS ALGÉRIENS ET {centimes_text} CENTIMES</b>", amount_words_bold_style)
    
    # Combine all elements
    from reportlab.platypus import KeepTogether
    all_elements = [
        totals_table,
        Spacer(1, 10),
        arrete_text,
        amount_text
    ]
    
    # Wrap in KeepTogether to prevent splitting across pages
    return KeepTogether(all_elements)


from reportlab.lib.pagesizes import A4
from reportlab.platypus import BaseDocTemplate, Frame, PageTemplate, Paragraph, Spacer, Table, TableStyle
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet
from io import BytesIO
import os
from datetime import datetime

# ------------------------------
# 🔹 Unified PDF Builder with Repeating Headers
# ------------------------------

def generate_full_invoice_pdf(societe_data, client_data, invoice_data, remise5_enabled=False):
    """
    Builds a full PDF where Société and Client headers repeat on each page before continuing the invoice table.
    Footer appears only on the last page.
    """
    buffer = BytesIO()
    styles = getSampleStyleSheet()

    # --- Build reusable header flowables (for drawing only) ---
    societe_header = generate_societe_pdf(societe_data, return_elements=True) if societe_data else []
    client_header = generate_client_pdf(client_data, invoice_data=invoice_data, return_elements=True) if client_data else []

    # --- Build main content (invoice table only) ---
    invoice_table = build_invoice_lines_table(client_data, invoice_data, remise5_enabled)
    totals_box = build_totals_box(client_data, invoice_data, remise5_enabled)

    # Invoice table and totals box in the story
    story = [invoice_table, Spacer(1, 12), totals_box]

    # --- Define repeating header and footer function ---
    # Use a container to store the total page count
    page_count = {'total': 0}
    
    def on_page(canvas, doc):
        canvas.saveState()
        width, height = A4
        
        # --- Header (on all pages) ---
        frame = Frame(doc.leftMargin, height - 400, doc.width, 380, id='header_frame', showBoundary=0)
        header_story = []
        if societe_header:
            header_story.extend(societe_header)
        if client_header:
            header_story.extend(client_header)
        frame.addFromList(header_story, canvas)
        
        # --- Page number (on all pages) ---
        if page_count['total'] > 0:
            current_page = canvas.getPageNumber()
            total_pages = page_count['total']
            
            # Get facture number from invoice_data (same as in Info Facture section)
            facture_no = invoice_data.get('invoice_no', '—') if invoice_data else '—'
            
            # Set font and color
            canvas.setFont('Helvetica', 9)
            canvas.setFillColor(colors.HexColor('#2C3E50'))
            
            # Page number on left
            page_num_text = f"Page {current_page}/{total_pages}"
            canvas.drawString(doc.leftMargin, 25, page_num_text)
            
            # Facture number on right
            facture_num_text = f"Facture N° {facture_no}"
            text_width = canvas.stringWidth(facture_num_text, 'Helvetica', 9)
            canvas.drawString(width - doc.rightMargin - text_width, 25, facture_num_text)
        
        # --- Disclaimer footer (only on last page) ---
        # Check if we know the total and if this is the last page
        if page_count['total'] > 0 and canvas.getPageNumber() == page_count['total']:
            footer_style = ParagraphStyle(
                'Footer',
                fontName='Helvetica-Bold',
                fontSize=10,
                alignment=1,  # Center alignment
                textColor=colors.HexColor('#2C3E50')
            )
            footer_text = "Toute Réclamation doit être faite dans les 48 Heures après réception de la Commande"
            footer_para = Paragraph(footer_text, footer_style)
            
            # Draw footer disclaimer above the page numbers
            footer_frame = Frame(
                doc.leftMargin,
                45,  # 45 points from bottom (above page numbers)
                doc.width,
                30,  # 30 points height for footer
                id='footer_frame',
                showBoundary=0
            )
            footer_frame.addFromList([footer_para], canvas)
        
        canvas.restoreState()

    # --- Custom document template ---
    doc = BaseDocTemplate(
        buffer,
        pagesize=A4,
        leftMargin=30,
        rightMargin=30,
        topMargin=400,   # match header frame height
        bottomMargin=30  # Normal bottom margin (no footer space yet)
    )

    frame = Frame(
        doc.leftMargin,
        doc.bottomMargin,
        doc.width,
        doc.height,  # use full height below header
        id='normal_frame'
    )

    # Page template with onPage callback for repeated header
    template = PageTemplate(id='invoice_template', frames=[frame], onPage=on_page)
    doc.addPageTemplates([template])

    # --- Build document (two-pass approach) ---
    # First pass: count pages
    doc.build(story)
    page_count['total'] = doc.page
    
    # Second pass: rebuild with footer on last page
    buffer.seek(0)
    buffer.truncate(0)
    
    doc = BaseDocTemplate(
        buffer,
        pagesize=A4,
        leftMargin=30,
        rightMargin=30,
        topMargin=400,
        bottomMargin=30  # Keep same margin - footer will be drawn on top of it on last page
    )
    
    frame = Frame(
        doc.leftMargin,
        doc.bottomMargin,
        doc.width,
        doc.height,
        id='normal_frame'
    )
    
    template = PageTemplate(id='invoice_template', frames=[frame], onPage=on_page)
    doc.addPageTemplates([template])
    
    # Rebuild story with the same content
    story = [invoice_table, Spacer(1, 12), totals_box]
    
    doc.build(story)
    buffer.seek(0)
    return buffer




# ------------------------------
# 🔹 Combined Header PDF Route
# ------------------------------
@app.route('/download-header-pdf', methods=['GET'])
def download_header_pdf():
    """Download combined Société + Client header as PDF, with repeating headers on each page."""
    include_societe = request.args.get('include_societe', '1') == '1'
    include_client = request.args.get('include_client', '1') == '1'
    bpartner_id = request.args.get('bpartner_id')
    document_no = request.args.get('documentno', '')
    mode_paiement_url = request.args.get('modepaiment') or request.args.get('mode_paiement')
    remise5_enabled = request.args.get('remise5', '0') == '1'

    if not include_societe and not include_client:
        return jsonify({"error": "No sections selected for download."}), 400

    try:
        # --- Fetch Société Data ---
        societe_data = {}
        if include_societe:
            with DB_POOL.acquire() as conn:
                cur = conn.cursor()
                cur.execute('''
                    SELECT etebac5_senderid AS RIB_SOCIETE, siren AS NAI_SOCIETE,
                           siret AS NIS_SOCIETE, phone AS TELE_societe, fax AS FAX_societe,
                           taxid AS NIF_SOCIETE, duns AS NRC_SOCIETE, loc.address1 AS ADDRESSE_SOCIETE,
                           email AS EMAIL_SOCIETE, xx_site AS WEBSITE_SOCIETE,
                           org.description AS ACTIVITE_SOCIEETE, xx_capital AS CAPITAL_SOCIEETE
                    FROM AD_OrgInfo
                    LEFT JOIN c_location loc ON loc.c_location_id = AD_OrgInfo.c_location_id
                    LEFT JOIN AD_Org org ON org.ad_org_id = AD_OrgInfo.ad_org_id
                    WHERE AD_OrgInfo.ad_org_id = 1000000
                ''')
                row = cur.fetchone()
                if row:
                    societe_data = dict(zip([c[0] for c in cur.description], row))

        # --- Fetch Client Data ---
        client_data = {}
        if include_client and bpartner_id:
            with DB_POOL.acquire() as conn:
                cur = conn.cursor()
                cur.execute('''
                    SELECT bp.DESCRIPTION AS Activite_client, bp.XX_RC AS RC_client,
                           bp.XX_NIF AS NIF_client, bp.XX_NIS AS NIS_client, bp.XX_AI AS AI_client,
                           bp.name AS Client_Name, reg.NAME AS Region_de_vente, locc.address1 AS Location_ID_client
                    FROM C_BPartner bp
                    LEFT JOIN C_BPartner_Location loc ON loc.C_BPartner_ID = bp.C_BPartner_ID
                    LEFT JOIN C_SalesRegion reg ON reg.C_SalesRegion_ID = loc.C_SalesRegion_ID
                    LEFT JOIN c_location locc ON locc.c_location_id = loc.c_location_id
                    WHERE bp.C_BPartner_ID = :bpid
                ''', {"bpid": bpartner_id})
                row = cur.fetchone()
                if row:
                    client_data = dict(zip([c[0] for c in cur.description], row))

            # --- Fetch invoice metadata (vendeur, commande, etc.) ---
            vendeur = commande = invoice_date = delai_paiement = mode_paiement = None
            if document_no:
                with DB_POOL.acquire() as conn2:
                    cur2 = conn2.cursor()
                    cur2.execute('''
                        SELECT salesrep.NAME AS Vendeur,
                               ord.DOCUMENTNO AS Ordre_de_vente,
                               c.DATEINVOICED,
                               payterm.NAME AS Delai_de_paiement,
                               zsub.NAME AS Mode_de_paiement
                        FROM C_Invoice c
                        LEFT JOIN AD_User salesrep ON salesrep.AD_User_ID = c.SALESREP_ID
                        LEFT JOIN C_Order ord ON ord.C_Order_ID = c.C_ORDER_ID
                        LEFT JOIN C_PaymentTerm payterm ON payterm.C_PaymentTerm_ID = c.C_PAYMENTTERM_ID
                        LEFT JOIN ZSubPaymentRule zsub ON zsub.ZSubPaymentRule_ID = c.ZSUBPAYMENTRULE_ID
                        WHERE c.DOCUMENTNO = :doc
                    ''', {"doc": document_no})
                    row2 = cur2.fetchone()
                    if row2:
                        vendeur = row2[0]
                        commande = row2[1]
                        invoice_date = (
                            row2[2].strftime('%d/%m/%Y')
                            if row2[2] and hasattr(row2[2], 'strftime')
                            else str(row2[2]) if row2[2] else None
                        )
                        delai_paiement = row2[3]
                        mode_paiement = row2[4]

            # --- Add fetched data to client_data ---
            if commande:
                client_data['Ordre_de_vente'] = commande
            if vendeur:
                client_data['Vendeur'] = vendeur
            if delai_paiement:
                client_data['Delai_de_paiement'] = delai_paiement
            if mode_paiement:
                client_data['Mode_de_paiement'] = mode_paiement

            invoice_data = {"invoice_no": document_no or "—", "date": invoice_date or "—"}
        else:
            invoice_data = {"invoice_no": "—", "date": "—"}


        # --- Inject mode_paiement from URL if present and not 'auto' ---
        if client_data is not None and mode_paiement_url:
            if str(mode_paiement_url).strip().lower() == 'static':
                client_data['Mode_de_paiement'] = 'static'
            elif str(mode_paiement_url).strip().lower() != 'auto':
                client_data['Mode_de_paiement'] = mode_paiement_url

        # --- Generate Full PDF with repeating headers ---
        pdf_buffer = generate_full_invoice_pdf(societe_data, client_data, invoice_data, remise5_enabled)

        # --- Send PDF response ---
        response = make_response(pdf_buffer.read())
        response.headers['Content-Type'] = 'application/pdf'
        response.headers['Content-Disposition'] = 'attachment; filename=header_combined.pdf'
        return response

    except Exception as e:
        logger.error(f"❌ Error generating PDF: {e}", exc_info=True)
        return jsonify({"error": "Failed to generate header PDF."}), 500






if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000, debug=True)
    
#Both Société + Client	/download-header-pdf?include_societe=1&include_client=1&bpartner_id=1000001&documentno=F001
#Only Société header	/download-header-pdf?include_societe=1&include_client=0
#Only Client header	/download-header-pdf?include_societe=0&include_client=1&bpartner_id=1000001
#127.0.0.1:5000/download-header-pdf?include_societe=1&include_client=1&bpartner_id=1118535&documentno=9926/2025