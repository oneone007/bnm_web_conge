SET SERVEROUTPUT ON SIZE 10000000;  -- Example: increase to 10MB


SET SERVEROUTPUT OFF;


WITH credit_fournisseur_data AS (
    SELECT 
        ROUND(SUM(invopenamt) - 19620173.63, 2) AS credit_fournisseur
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
            AND inv.ISSOTRX = 'N'
            AND bp.isactive = 'Y'
            AND bp.isvendor = 'Y'
    ) t
),
credit_client_data AS (
    SELECT 
        ROUND(SUM(SoldeFact + SoldeBL), 2) AS credit_client
    FROM (
        SELECT 
            bp.c_bpartner_id, 
            (
                SELECT COALESCE(SUM(invoiceOpen(inv.C_Invoice_ID, 0)), 0) 
                FROM C_Invoice inv 
                WHERE bp.c_bpartner_id = inv.c_bpartner_id 
                AND paymentTermDueDays(inv.C_PAYMENTTERM_ID, inv.DATEINVOICED, TO_DATE('31/12/3000', 'DD/MM/YYYY') ) >= 0
                AND inv.docstatus IN ('CO', 'CL') 
                AND inv.AD_ORGTRX_ID = inv.ad_org_id 
                AND inv.ad_client_id = 1000000
                AND inv.C_PaymentTerm_ID != 1000000
            ) AS SoldeFact,
            (
                SELECT COALESCE(SUM(invoiceOpen(inv.C_Invoice_ID, 0)), 0) 
                FROM C_Invoice inv 
                WHERE bp.c_bpartner_id = inv.c_bpartner_id 
                AND paymentTermDueDays(inv.C_PAYMENTTERM_ID, inv.DATEINVOICED, TO_DATE('31/12/3000', 'DD/MM/YYYY') ) >= 0
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
            AND bp.isactive = 'Y' 
            AND sr.isactive = 'Y'
            AND bp.C_PaymentTerm_ID != 1000000  
            AND bp.XX_PaymentTermBL_ID != 1000000 
            AND bp.XX_PaymentTermfact_ID != 1000000 
            AND bpl.c_salesregion_id IN (1001778, 1001781, 1001782, 1001783, 1001784, 1001787, 1001788, 1001789, 1001790, 1001791, 1001792, 1001793, 1001794, 1002176, 1002179, 1002286, 1002285)
    ) t
),
mbn_data AS (
    SELECT 
        ROUND(SUM(SoldeFact + SoldeBL) - 3212311.61, 2) AS mbn
    FROM (
        SELECT 
            bp.c_bpartner_id, 
            (
                SELECT COALESCE(SUM(invoiceOpen(inv.C_Invoice_ID, 0)), 0) 
                FROM C_Invoice inv 
                WHERE bp.c_bpartner_id = inv.c_bpartner_id 
                AND paymentTermDueDays(inv.C_PAYMENTTERM_ID, inv.DATEINVOICED, TO_DATE('31/12/3000', 'DD/MM/YYYY') ) >= 0
                AND inv.DATEINVOICED BETWEEN TO_DATE('01/01/2022', 'DD/MM/YYYY') AND TO_DATE('31/12/3000', 'DD/MM/YYYY')
                AND inv.docstatus IN ('CO', 'CL') 
                AND inv.AD_ORGTRX_ID = inv.ad_org_id 
                AND inv.ad_client_id = 1000000
                AND bp.C_BPartner_ID = 1121240 
            ) AS SoldeFact,
            (
                SELECT COALESCE(SUM(invoiceOpen(inv.C_Invoice_ID, 0)), 0) 
                FROM C_Invoice inv 
                WHERE bp.c_bpartner_id = inv.c_bpartner_id 
                AND paymentTermDueDays(inv.C_PAYMENTTERM_ID, inv.DATEINVOICED, TO_DATE('31/12/3000', 'DD/MM/YYYY') ) >= 0
                AND inv.docstatus IN ('CO', 'CL') 
                AND inv.AD_ORGTRX_ID <> inv.ad_org_id 
                AND inv.ad_client_id = 1000000
                AND bp.C_BPartner_ID = 1121240 
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
            AND bp.isactive = 'Y' 
            AND sr.isactive = 'Y'
            AND bp.C_BPartner_ID = 1121240 
            AND bpl.c_salesregion_id IN (1001778, 1001781, 1001782, 1001783, 1001784, 1001787, 1001788, 1001789, 1001790, 1001791, 1001792, 1001793, 1001794, 1002176, 1002179, 1002286, 1002285)
    ) t
),
caisse_data AS (
    SELECT 
        ROUND(endingbalance, 2) AS caisse
    FROM C_BankStatement
    WHERE 
        C_BankAccount_ID = 1000205
        AND docstatus = 'CO'
        AND AD_Client_ID = 1000000
    ORDER BY statementdate DESC
    FETCH FIRST 1 ROW ONLY
),
baraka_data AS (
    SELECT 
        endingbalance AS baraka
    FROM C_BankStatement
    WHERE 
        C_BankAccount_ID = 1000617
        AND docstatus = 'CO'
        AND AD_Client_ID = 1000000
    ORDER BY statementdate DESC
    FETCH FIRST 1 ROW ONLY
),
stock_principale_data AS (
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
    ROUND(SUM(cf.credit_fournisseur), 2) AS "DETTES FOURNISSEUR", 
    ROUND(SUM(cc.credit_client), 2) AS "CREANCE CLIENT", 
    ROUND(SUM(mb.mbn), 2) AS "CREANCE MBN",
    ROUND(SUM(c.caisse), 2) AS "LA CAISSE",
     39529504.80    AS "SOLDE BNA",
   31443829.88    AS "REMISE NON ENCAISSE (BNA)",
    ROUND(SUM(b.baraka), 2) AS "SOLDE BARKA",
    0 AS  "REMISE NON ENCAISSE(AL BARAKA)",
    ROUND(SUM(sp.stock_principale), 2) AS "STOCK principale",
    ROUND(SUM(h.hangar), 2) AS "hangar",
    ROUND(SUM(hr.hangarresrev), 2) AS "hangarréserve",
    ROUND(SUM(dr.depot_reserver), 2) AS "depot reserver",
    ROUND(
        NVL(SUM(h.hangar), 0) + 
        NVL(SUM(dr.depot_reserver), 0) + 
        NVL(SUM(sp.stock_principale), 0) + 
        NVL(SUM(hr.hangarresrev), 0), 
        2
    ) AS "total stock",
    ROUND(
        NVL(SUM(cc.credit_client), 0) + 
        NVL(SUM(c.caisse), 0) + 
        31443829.88 + 
        39529504.80 + 
        NVL(SUM(b.baraka), 0) + 
        NVL(SUM(sp.stock_principale), 0) - 
        NVL(SUM(cf.credit_fournisseur), 0) + 
        NVL(SUM(mb.mbn), 0) + 
        NVL(SUM(h.hangar), 0) + 
        NVL(SUM(dr.depot_reserver), 0), 
        2
    ) AS "FONDS PROPRE"
FROM 
    credit_fournisseur_data cf,
    credit_client_data cc,
    mbn_data mb,
    caisse_data c,
    baraka_data b,
    stock_principale_data sp,
    hangar_data h,
    hangarresrev_data hr,
    depot_reserver_data dr;


    --------------------------------------------------------



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
    ROUND(SUM(sp.stock_principale), 2) AS "STOCK principale",
    ROUND(SUM(h.hangar), 2) AS "hangar",
    ROUND(SUM(hr.hangarresrev), 2) AS "hangarréserve",
    ROUND(SUM(dr.depot_reserver), 2) AS "depot reserver",
    ROUND(
        NVL(SUM(sp.stock_principale), 0) + 
        NVL(SUM(h.hangar), 0) + 
        NVL(SUM(hr.hangarresrev), 0) + 
        NVL(SUM(dr.depot_reserver), 0), 
        2
    ) AS "TOTAL STOCK"
FROM 
    stock_principale_data sp,
    hangar_data h,
    hangarresrev_data hr,
    depot_reserver_data dr;
