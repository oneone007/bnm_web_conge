SELECT (
    (SELECT COALESCE(SUM(iar.GRANDTOTAL), 0) 
     FROM C_INVOICE_V iar
     WHERE iar.DOCSTATUS IN ('CO','CL') 
     AND iar.ISSOTRX = 'N'
     AND iar.C_BPARTNER_ID = 1123624
     AND iar.DATEINVOICED < TO_DATE('01-01-2025', 'DD-MM-YYYY'))
     
    -
    
    (SELECT COALESCE(SUM(par.PAYAMT) + SUM(par.discountamt), 0)
     FROM C_PAYMENT par
     WHERE par.DOCSTATUS IN ('CO','CL') 
     AND par.ISRECEIPT = 'N'
     AND par.C_BPARTNER_ID = 1123624
     AND par.DATETRX < TO_DATE('01-01-2025', 'DD-MM-YYYY'))
     
    -
    
    (SELECT COALESCE(SUM(cl.Amount), 0)
     FROM C_CashLine cl
     INNER JOIN C_Cash c ON (cl.C_Cash_ID = c.C_Cash_ID)
     INNER JOIN C_Invoice i ON (cl.C_Invoice_ID = i.C_Invoice_ID)
     WHERE c.DOCSTATUS IN ('CO','CL')
     AND i.ispaid = 'Y'
     AND cl.isactive = 'Y'
     AND i.C_BPARTNER_ID = 1123624
     AND c.StatementDATE < TO_DATE('01-01-2025', 'DD-MM-YYYY'))
) AS OpeningBal 
FROM DUAL
