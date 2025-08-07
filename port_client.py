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
# PDF generation imports
from reportlab.lib.pagesizes import A4, letter
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.platypus import SimpleDocTemplate, Table as PDFTable, TableStyle, Paragraph, Spacer
from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT

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


def get_client_operator_data():
    """
    Fetch client and operator data from database
    """
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            
            query = """
            SELECT DISTINCT cb.name as client, ad.name as operator
            FROM c_bpartner cb
            INNER JOIN ad_user ad ON (cb.salesrep_id = ad.ad_user_id)
            WHERE ad.c_bpartner_id IN (1121780,1122143,1118392,1122144,1119089,1111429,1122761,1122868,1122142,1143361)
            AND cb.isactive = 'Y'
            AND cb.ad_org_id = 1000000
            ORDER BY ad.name, cb.name 
            """

            cursor.execute(query)
            rows = cursor.fetchall()
            columns = [col[0] for col in cursor.description]
            data = [dict(zip(columns, row)) for row in rows]

            return data

    except Exception as e:
        logger.error(f"Error fetching client-operator data: {e}")
        return {"error": "An error occurred while fetching client-operator data."}


def generate_client_operator_pdf(data):
    """
    Generate a well-organized PDF report for client-operator data
    """
    buffer = BytesIO()
    
    # Create PDF document
    doc = SimpleDocTemplate(
        buffer,
        pagesize=A4,
        topMargin=1*inch,
        bottomMargin=1*inch,
        leftMargin=0.75*inch,
        rightMargin=0.75*inch
    )
    
    # Define styles
    styles = getSampleStyleSheet()
    title_style = ParagraphStyle(
        'CustomTitle',
        parent=styles['Title'],
        fontSize=18,
        textColor=colors.darkblue,
        alignment=TA_CENTER,
        spaceAfter=30
    )
    
    subtitle_style = ParagraphStyle(
        'CustomSubtitle',
        parent=styles['Normal'],
        fontSize=12,
        textColor=colors.grey,
        alignment=TA_CENTER,
        spaceAfter=20
    )
    
    # Build content
    content = []
    
    # Title
    title = Paragraph("Client-Operator Report", title_style)
    content.append(title)
    
    # Subtitle with date
    current_date = datetime.now().strftime("%B %d, %Y at %I:%M %p")
    subtitle = Paragraph(f"Generated on {current_date}", subtitle_style)
    content.append(subtitle)
    
    # Add some space
    content.append(Spacer(1, 20))
    
    if isinstance(data, list) and len(data) > 0:
        # Group data by operator
        grouped_data = {}
        for item in data:
            operator = item.get('OPERATOR', 'Unknown')
            client = item.get('CLIENT', 'Unknown')
            if operator not in grouped_data:
                grouped_data[operator] = []
            grouped_data[operator].append(client)
        
        # Create summary section
        summary_data = [
            ['Total Operators', str(len(grouped_data))],
            ['Total Clients', str(len(data))],
            ['Average Clients per Operator', f"{len(data)/len(grouped_data):.1f}" if len(grouped_data) > 0 else "0"]
        ]
        
        summary_table = PDFTable(summary_data, colWidths=[3*inch, 2*inch])
        summary_table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, -1), colors.lightgrey),
            ('TEXTCOLOR', (0, 0), (-1, -1), colors.black),
            ('FONTNAME', (0, 0), (-1, -1), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, -1), 12),
            ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
            ('GRID', (0, 0), (-1, -1), 1, colors.black),
            ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ]))
        
        content.append(Paragraph("Summary", styles['Heading2']))
        content.append(summary_table)
        content.append(Spacer(1, 30))
        
        # Create detailed table
        table_data = [['Operator', 'Client']]  # Header row
        
        for item in data:
            row = [
                item.get('OPERATOR', 'N/A'),
                item.get('CLIENT', 'N/A')
            ]
            table_data.append(row)
        
        # Create table
        table = PDFTable(table_data, colWidths=[3*inch, 4*inch])
        
        # Style the table
        table.setStyle(TableStyle([
            # Header row styling
            ('BACKGROUND', (0, 0), (-1, 0), colors.darkblue),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, 0), 12),
            ('ALIGN', (0, 0), (-1, 0), 'CENTER'),
            
            # Data rows styling
            ('FONTNAME', (0, 1), (-1, -1), 'Helvetica'),
            ('FONTSIZE', (0, 1), (-1, -1), 10),
            ('ALIGN', (0, 1), (-1, -1), 'LEFT'),
            ('GRID', (0, 0), (-1, -1), 1, colors.black),
            ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
            
            # Alternating row colors
            ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.lightgrey]),
        ]))
        
        content.append(Paragraph("Detailed Client-Operator List", styles['Heading2']))
        content.append(table)
        
    else:
        # No data message
        no_data_msg = Paragraph("No data available for the specified criteria.", styles['Normal'])
        content.append(no_data_msg)
    
    # Build PDF
    doc.build(content)
    buffer.seek(0)
    
    return buffer


@app.route('/api/client-operator/pdf', methods=['GET'])
def download_client_operator_pdf():
    """
    API endpoint to download client-operator data as PDF
    """
    try:
        # Fetch data
        data = get_client_operator_data()
        
        if isinstance(data, dict) and 'error' in data:
            return jsonify(data), 500
        
        # Generate PDF
        pdf_buffer = generate_client_operator_pdf(data)
        
        # Generate filename with timestamp
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"client_operator_report_{timestamp}.pdf"
        
        return send_file(
            pdf_buffer,
            as_attachment=True,
            download_name=filename,
            mimetype='application/pdf'
        )
        
    except Exception as e:
        logger.error(f"Error generating PDF: {e}")
        return jsonify({"error": "An error occurred while generating the PDF."}), 500


@app.route('/api/client-operator', methods=['GET'])
def get_client_operator():
    """
    API endpoint to get client-operator data as JSON
    """
    try:
        data = get_client_operator_data()
        return jsonify(data)
    except Exception as e:
        logger.error(f"Error in client-operator endpoint: {e}")
        return jsonify({"error": "An error occurred while fetching data."}), 500






def get_client_operator_data2():
    """
    Fetch client and operator data from database and group clients by operator
    Returns a list of dictionaries with operator name and their clients
    """
    try:
        with DB_POOL.acquire() as connection:
            cursor = connection.cursor()
            
            query = """
            SELECT DISTINCT cb.name as client, ad.name as operator
            FROM c_bpartner cb
            INNER JOIN ad_user ad ON (cb.salesrep_id = ad.ad_user_id)
            WHERE ad.c_bpartner_id IN (1121780,1122143,1118392,1122144,1119089,1111429,1122761,1122868,1122142,1143361)
            AND cb.isactive = 'Y'
            AND cb.ad_org_id = 1000000
            ORDER BY ad.name, cb.name 
            """

            cursor.execute(query)
            rows = cursor.fetchall()
            
            # Group clients by operator
            operator_data = {}
            for row in rows:
                operator = row[1].strip()  # Remove extra whitespace
                client = row[0].strip()    # Remove extra whitespace
                
                if operator not in operator_data:
                    operator_data[operator] = []
                operator_data[operator].append(client)
            
            # Convert to desired output format
            result = []
            for operator, clients in operator_data.items():
                result.append({
                    "operator": operator,
                    "clients": clients
                })
            
            return result

    except Exception as e:
        logger.error(f"Error fetching client-operator data: {e}")
        return {"error": "An error occurred while fetching client-operator data."}


@app.route('/api/client-operator2', methods=['GET'])
def get_client_operator():
    """
    API endpoint to get client-operator data as JSON grouped by operator
    """
    try:
        data = get_client_operator_data2()
        return jsonify(data)
    except Exception as e:
        logger.error(f"Error in client-operator endpoint: {e}")
        return jsonify({"error": "An error occurred while fetching data."}), 500

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000)