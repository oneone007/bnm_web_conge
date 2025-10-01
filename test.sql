SELECT 
    ROUND(endingbalance, 2) AS caisse
FROMFROM 
    C_BankStatement
WHERE 
    C_BankAccount_ID = 1000205
    AND docstatus = 'CO'
    AND AD_Client_ID = 1000000
ORDER BY 
    statementdate DESC
FETCH FIRST 1 ROW ONLY;
-----------------------------


SELECT DOCUMENTNO, CREATED, DESCRIPTION 
FROM M_InOut 
WHERE TRUNC(CREATED) = TRUNC(SYSDATE)
  AND ISACTIVE = 'Y'
  AND DOCACTION = 'CO'
  AND DOCSTATUS = 'IP'
  AND PROCESSED = 'N'
  AND ISAPPROVED = 'N'
  AND XX_CONTROLEUR_ID IS NULL
  AND XX_PREPARATEUR_ID IS NULL
  AND XX_CONTROLEUR_CH_ID IS NULL
  AND XX_PREPARATEUR_CH_ID IS NULL
  AND XX_EMBALEUR_CH_ID IS NULL
  AND XX_EMBALEUR_ID IS NULL;

-------------------------------
SELECT DOCUMENTNO, CREATED, DESCRIPTION 
FROM M_InOut;


SELECT 
                    ROUND(PayAmt, 2) AS paiment
                FROM 
                    C_Payment
                WHERE 



SELECT 
    ROUND(ci.PayAmt, 2) AS paiment,
    ci.DOCACTION,
    ci.DOCUMENTNO,
    z.name 
FROM 
    C_Payment ci
    INNER JOIN ZSubPaymentRule z ON ci.ZSubPaymentRule_ID = z.ZSubPaymentRule_ID
WHERE 
    TRUNC(ci.DATETRX) = TRUNC(SYSDATE)
    AND ci.DOCACTION IN ('CO', 'CL', 'co', 'cl')
    AND ci.AD_Client_ID = 1000000;



    --------------- total paiment-------------------------------------
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
    AND z.name IN ('Encaiss: Espèces', 'Décaiss: Espèces');

----------------------paiment rows---------------------------------
SELECT 
    ROUND(ci.PayAmt, 2) AS paiment , ci.DOCACTION ,ci.DOCUMENTNO,z.name 
FROM 
    C_Payment ci   
    inner join ZSubPaymentRule z on (ci.ZSubPaymentRule_ID =z.ZSubPaymentRule_ID)
WHERE 
    TRUNC(ci.DATETRX) = TRUNC(TO_DATE('21-04-2025', 'DD-MM-YYYY'))
    AND ci.DOCACTION IN ('CO', 'CL', 'cl' , 'co')
    AND ci.AD_Client_ID = 1000000;
MAKE TODAY SYSTADE I MEAN

--------------------------------- cota---------------------------------------
select percentage, name from c_bpartner 
WHERE c_bpartner_id IN (1121780, 1122761, 1122868, 1122144, 1111429, 1122142, 1118392, 1119089, 1122143);






------------------------------------------------------------------------
SELECT 
    ROUND(SUM(total), 2) AS "OBJECTIF MENSUEL", 
    ROUND(SUM(totalp), 2) AS "TOTAL RECOUVREMENT",
    ROUND(SUM(totalp)/SUM(total), 2) AS "POURCENTAGE"
    FROM(
    SELECT 
        481114494.36 AS total,
        SUM(p.payamt) AS totalp
    FROM 
        C_Payment p
        JOIN C_BPartner b ON b.C_BPartner_id = p.C_BPartner_id
    WHERE 
        b.iscustomer = 'Y'
        AND b.C_PaymentTerm_ID != 1000000
        AND p.AD_Client_ID = 1000000
     --   AND p.docaction = 'CL'
        AND p.docstatus in ('CO','CL')
        AND p.ZSubPaymentRule_ID in (1000007,1000016)
       --- and p.C_DocType_ID=1001615
        --and p.ZSubPaymentRule_ID=1000016)
        AND p.datetrx >= '01/06/2025'
        AND p.datetrx <= '30/06/2025'
) temp_combined
;

SELECT s.sid, s.serial#, s.username
FROM v$session s
WHERE s.blocking_session IS NOT NULL;



SELECT *    FROM M_InOut 
 WHERE TRUNC(CREATED) = TO_DATE('2025-05-25', 'YYYY-MM-DD')
 and M_InOut_ID in (3190802, 3190808, 3190766)



 SELECT * FROM AD_User WHERE AD_User_ID = 1037318;


-----------------
SELECT 
  IO.DOCUMENTNO, 
  IO.CREATED, 
  IO.DESCRIPTION, 
  IO.C_ORDER_ID, 
  BP.NAME,
  SR.NAME AS SALES_REGION
FROM 
  M_InOut IO
  JOIN C_BPartner BP ON IO.C_BPartner_ID = BP.C_BPartner_ID
  JOIN C_BPartner_Location BPL ON IO.C_BPartner_ID = BPL.C_BPartner_ID
  JOIN C_SalesRegion SR ON BPL.C_SalesRegion_ID = SR.C_SalesRegion_ID
WHERE 
  TRUNC(IO.CREATED) = TRUNC(SYSDATE)
  AND IO.ISACTIVE = 'Y'
  AND IO.DOCACTION = 'CO'
  AND IO.DOCSTATUS = 'IP'
  AND IO.PROCESSED = 'N'
  AND IO.ISAPPROVED = 'N'
  AND IO.XX_CONTROLEUR_ID IS NULL
  AND IO.XX_PREPARATEUR_ID IS NULL
  AND IO.XX_CONTROLEUR_CH_ID IS NULL
  AND IO.XX_PREPARATEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_ID IS NULL;

-----------------------

SELECT 
  IO.DOCUMENTNO, 
  IO.CREATED, 
  IO.DESCRIPTION, 
  IO.C_ORDER_ID, 
  BP.NAME,
  SR.NAME AS SALES_REGION
FROM 
  M_InOut IO
  JOIN C_BPartner BP ON IO.C_BPartner_ID = BP.C_BPartner_ID
  JOIN (
    SELECT BPL.*
    FROM C_BPartner_Location BPL
    WHERE BPL.ISACTIVE = 'Y'
      AND BPL.C_BPartner_Location_ID = (
        SELECT MAX(BPL2.C_BPartner_Location_ID)
        FROM C_BPartner_Location BPL2
        WHERE BPL2.C_BPartner_ID = BPL.C_BPartner_ID
      )
  ) BPL ON IO.C_BPartner_ID = BPL.C_BPartner_ID
  JOIN C_SalesRegion SR ON BPL.C_SalesRegion_ID = SR.C_SalesRegion_ID
WHERE 
  TRUNC(IO.CREATED) = TRUNC(SYSDATE)
  AND IO.ISACTIVE = 'Y'
  AND IO.DOCACTION = 'CO'
  AND IO.DOCSTATUS = 'IP'
  AND IO.PROCESSED = 'N'
  AND IO.ISAPPROVED = 'N'
  AND IO.XX_CONTROLEUR_ID IS NULL
  AND IO.XX_PREPARATEUR_ID IS NULL
  AND IO.XX_CONTROLEUR_CH_ID IS NULL
  AND IO.XX_PREPARATEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_ID IS NULL;




SELECT * 
FROM M_InOut 
WHERE TRUNC(CREATED) = TRUNC(SYSDATE)
  AND ISACTIVE = 'Y'
  AND DOCACTION = 'CO'
  AND DOCSTATUS = 'IP'
  AND PROCESSED = 'N'
  AND ISAPPROVED = 'N'
  AND XX_CONTROLEUR_ID IS NULL
  AND XX_PREPARATEUR_ID IS NULL
  AND XX_CONTROLEUR_CH_ID IS NULL
  AND XX_PREPARATEUR_CH_ID IS NULL
  AND XX_EMBALEUR_CH_ID IS NULL
  AND XX_EMBALEUR_ID IS NULL;


SELECT C_SALESREGION_ID, NAME FROM C_SalesRegion ;







1002286


SELECT * FROM C_BPartner_Location WHERE C_BPartner_Location_ID=1108093;



SELECT 
  IO.DOCUMENTNO, 
  IO.CREATED, 
  IO.DESCRIPTION, 
  IO.M_InOut_ID, 
  BP.NAME,
  SR.NAME AS SALES_REGION
FROM 
  M_InOut IO
  JOIN C_BPartner BP ON IO.C_BPartner_ID = BP.C_BPartner_ID
  JOIN C_BPartner_Location BPL ON IO.C_BPartner_ID = BPL.C_BPartner_ID
  JOIN C_SalesRegion SR ON BPL.C_SalesRegion_ID = SR.C_SalesRegion_ID
WHERE 
  
 IO.ISACTIVE = 'Y'
  AND IO.DOCACTION = 'CO'
  AND IO.DOCSTATUS = 'IP'
  AND IO.PROCESSED = 'N'
  AND IO.ISAPPROVED = 'N'
  AND IO.XX_CONTROLEUR_ID IS NULL
  AND IO.XX_PREPARATEUR_ID IS NULL
  AND IO.XX_CONTROLEUR_CH_ID IS NULL
  AND IO.XX_PREPARATEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_ID IS NULL
  AND IO.C_DocType_ID='1002733';
---------------------------------------------
SELECT *
                FROM M_InOut 
                WHERE  ISACTIVE = 'Y'
                  AND DOCACTION = 'CO'
                  AND DOCSTATUS = 'IP'
                  AND PROCESSED = 'N'
                  AND ISAPPROVED = 'N'
                  AND XX_CONTROLEUR_ID IS NULL
                  AND XX_PREPARATEUR_ID IS NULL
                  AND XX_CONTROLEUR_CH_ID IS NULL
                  AND XX_PREPARATEUR_CH_ID IS NULL
                  AND XX_EMBALEUR_CH_ID IS NULL
                  AND XX_EMBALEUR_ID IS NULL
                  AND DOCUMENTNO IN('BEC26269/2025', 'BEC26265/2025','BEC26260/2025');


---------------------------------------


----------------

BEC26261/2025

DESCRIBE M_InOutline;

SELECT * FROM M_InOutline WHERE M_InOutLine_ID = 3216270;

SELECT NAME FROM m_product WHERE M_Product_ID = 1187190;

SELECT P.NAME , ML.MOVEMENTQTY FROM 
M_InOutline ML
JOIN 
M_Product P ON ML.M_Product_ID = P.M_Product_ID
WHERE 
ML.M_InOut_ID = 3191188;






SELECT s.sid, s.serial#, s.username
FROM v$session s
WHERE s.blocking_session IS NOT NULL;





SELECT C_SALESREGION_ID, NAME 
                FROM C_SalesRegion
                     where ISACTIVE= 'Y'
                     and AD_Client_ID = 1000000
                ORDER BY NAME
           ;



SELECT *
                FROM C_SalesRegion
                     where ISACTIVE= 'Y'
                     and C_SALESREGION_ID in(1001793, 1002181)
                ORDER BY NAME
           ;






    SELECT * FROM     C_Payment WHERE C_Payment_ID=1260402;    



 elif processed == 'Y' and docstatus == 'CO' and docaction.lower() == 'cl':
        state = "Achevé"
    elif processed == 'N' and docstatus == 'DR' and (docaction.upper() == 'PR' or docaction.upper() == 'CO'):
        state = "Brouillon"



    ------------------------------
   SELECT  processed , docstatus , docaction FROM  C_BankStatement WHERE C_BankStatement_ID=1019580;


------------------------ Pour Ouvrir Un Extrait de Caisse -----------------------------------------------------
-----------------------------------------------------------------
DEFINE BankStatement = 1019580;
UPDATE C_BankStatement 
SET  processed = 'N',docstatus = 'DR', docaction = 'CO'
WHERE C_BankStatement_id = &BankStatement;

UPDATE C_BankStatementLine  
SET processed = 'N'
WHERE C_BankStatement_id = &BankStatement;
COMMIT;
--------------------------------------------------------------
------------------------ Pour Achever Un Extrait de Caisse -----------------------------------------------------
-----------------------------------------------------------------
UPDATE C_BankStatement 
SET processed = 'Y',docstatus = 'CO', docaction = 'CL'
WHERE C_BankStatement_id = &BankStatement;
UPDATE C_BankStatementLine  
SET processed = 'Y'
WHERE C_BankStatement_id = &BankStatement;
COMMIT;

--------------------------------------------------------------
----------------------- UPDATE  Remise IN (Articles ----> Articles-Tiers) ------------------------
update C_BPartner_Product set m_discountschema_id = 1001229 -- id de remise 5% 1000718    7% 1000720   10%  1000007    13% 1000724   8%  1000719
where m_product_id in (select str.m_product_id from m_storage str
inner join m_product mp on (str.m_product_id = mp.m_product_id)
inner join m_attributeinstance att on (str.m_attributesetinstance_id = att.m_attributesetinstance_id)
where att.m_attribute_id = 1000508 and att.value like '%SPIC sifaoui (PARA)%'-- fournisseur
and mp.name like 'SPIC%' --produit
) and c_bp_group_id in(1001330)-- id de type client (client para,client potentiel) ;1000003 PARA 1001330 POT





-- Select products with empty m_discountschema_id, showing product name and fournisseur
SELECT 
    mp.name AS product_name,
    att.value AS fournisseur
FROM 
    C_BPartner_Product cbpp
    INNER JOIN m_product mp ON cbpp.m_product_id = mp.m_product_id
    INNER JOIN m_storage str ON str.m_product_id = mp.m_product_id
    INNER JOIN m_attributeinstance att ON str.m_attributesetinstance_id = att.m_attributesetinstance_id
WHERE 
    cbpp.m_discountschema_id IS NULL
    AND att.m_attribute_id = 1000508
    AND att.value LIKE '%LILIUM PHARMA ALGERIA%'
    AND mp.name LIKE 'LILIUM%';



    CREATE TABLE IF NOT EXISTS bnm.bank_data (id INT AUTO_INCREMENT PRIMARY KEY, date DATETIME, bna_sold DECIMAL(10,2), bna_remise DECIMAL(10,2), bna_check DECIMAL(10,2), baraka_sold DECIMAL(10,2), baraka_remise DECIMAL(10,2), baraka_check DECIMAL(10,2));



    ✅ 1. main_kpi – summary table

CREATE TABLE IF NOT EXISTS bnm.main_kpi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    time DATETIME NOT NULL,
    total_profit DECIMAL(12,2),
    total_stock DECIMAL(12,2),
    credit_client DECIMAL(12,2),
    total_tresorerie DECIMAL(12,2),
    total_dette DECIMAL(12,2)
);

✅ 2. stock – stock details table

CREATE TABLE IF NOT EXISTS bnm.stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME NOT NULL,
    principale DECIMAL(12,2),
    depot_reserver DECIMAL(12,2),
    hangar DECIMAL(12,2),
    hangar_reserve DECIMAL(12,2),
    total_stock DECIMAL(12,2) GENERATED ALWAYS AS (principale + depot_reserver + hangar + hangar_reserve) STORED
);

✅ 3. tresori – treasury breakdown table

CREATE TABLE IF NOT EXISTS bnm.tresori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME NOT NULL,
    caisse DECIMAL(12,2),
    paiement_net DECIMAL(12,2),
    total_bank DECIMAL(12,2),
    total_tresorerie DECIMAL(12,2) GENERATED ALWAYS AS (caisse + paiement_net + total_bank) STORED
);

✅ 4. bank_data – bank-level details (used in both tresori and dette)

CREATE TABLE IF NOT EXISTS bnm.bank_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME NOT NULL,
    bna_sold DECIMAL(12,2),
    bna_remise DECIMAL(12,2),
    bna_check DECIMAL(12,2),
    baraka_sold DECIMAL(12,2),
    baraka_remise DECIMAL(12,2),
    baraka_check DECIMAL(12,2),
    total_bank DECIMAL(12,2) GENERATED ALWAYS AS (bna_sold + bna_remise + baraka_sold + baraka_remise) STORED,
    total_checks DECIMAL(12,2) GENERATED ALWAYS AS (bna_check + baraka_check) STORED
);

✅ 5. dette – debt table

CREATE TABLE IF NOT EXISTS bnm.dette (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME NOT NULL,
    dette_fournisseur DECIMAL(12,2),
    totalchecks DECIMAL(12,2),
    total_dette DECIMAL(12,2) GENERATED ALWAYS AS (dette_fournisseur + totalchecks) STORED
);



select * from xx_vendor_status;



SELECT ml.value, ml.m_locator_id AS EMPLACEMENT
                FROM M_Locator ml
                JOIN M_Warehouse m ON m.M_WAREHOUSE_ID = ml.M_WAREHOUSE_ID
                WHERE m.ISACTIVE = 'Y'
                  AND m.AD_Client_ID = 1000000
                  AND ml.ISACTIVE = 'Y'
                  AND ml.AD_Client_ID = 1000000;





                  select * from M_MovementLine where M_MovementLine_ID=1102924

                  1001135

                  m_locator_id = 1001135
                  m_locatorto_id = 1000614



           SELECT *
FROM (
  SELECT *
  FROM M_MovementLine
  WHERE m_locator_id = 1000614
    AND m_locatorto_id = 1001135
)
WHERE ROWNUM <= 50;

select * from M_Movement where m_movement_id=1059403;


SELECT
                t.MovementDate AS MovementDate,
                nvl(nvl(io.documentno,inv.documentno),m.documentno) as documentno,
                nvl(bp.name, nvl(inv.description,m.description)) as name,
                p.name AS productname,
                CASE WHEN t.movementqty > 0 then t.movementqty else 0 end as ENTREE,
                CASE WHEN t.movementqty < 0 then ABS(t.movementqty) else 0 end as SORTIE,
                coalesce((SELECT SUM(s.movementqty)
                FROM m_transaction s
                inner join m_product p on (s.m_product_id = p.m_product_id)
                inner join m_locator l on (l.m_locator_id = s.m_locator_id)
                WHERE s.movementdate < t.movementdate
                AND (:product IS NULL OR p.name LIKE :product || '%')
                AND (:emplacement IS NULL OR
                     CASE 
                         WHEN :emplacement = '' THEN l.value IN ('Préparation', 'HANGAR')
                         ELSE l.value LIKE :emplacement || '%'
                     END)
                ), 0) AS StockInitial,
                asi.lot,
                l_from.value AS locator_from,
                l_to.value AS locator_to
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
            ON (inv.m_inventory_id = il.m_inventory_id)
            LEFT OUTER JOIN M_MovementLine ml
            ON (t.M_MovementLine_ID=ml.M_MovementLine_ID 
                AND NOT (ml.M_Locator_ID = 1001135 AND ml.M_LocatorTo_ID = 1000614)
                AND NOT (ml.M_Locator_ID = 1000614 AND ml.M_LocatorTo_ID = 1001135))
            LEFT OUTER JOIN M_Movement m
            ON (m.M_Movement_ID=ml.M_Movement_ID)
            LEFT OUTER JOIN M_InOutLine iol
            ON (t.M_InOutLine_ID=iol.M_InOutLine_ID)
            LEFT OUTER JOIN M_Inout io
            ON (iol.M_InOut_ID=io.M_InOut_ID)
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
            )
            WHERE (io.docstatus IN ('CO' , 'CL') 
            OR m.docstatus IN ('CO' , 'CL')
            OR inv.docstatus IN ('CO' , 'CL')) 
            AND att.m_attribute_id = 1000508
            AND (:end_date IS NULL OR t.movementdate <= TO_DATE(:end_date, 'YYYY-MM-DD'))
            AND (:start_date IS NULL OR t.movementdate >= TO_DATE(:start_date, 'YYYY-MM-DD'))
            AND (:product IS NULL OR P.NAME LIKE :product || '%')
            AND (:fournisseur IS NULL OR att.value like :fournisseur || '%')
            AND (:emplacement IS NULL OR 
                 CASE 
                     WHEN :emplacement = '' THEN l.value IN ('Préparation', 'HANGAR')
                     ELSE l.value LIKE :emplacement || '%'
                 END)
            AND t.AD_Client_ID = 1000000
            ORDER BY t.MovementDate DESC;

            ------------------------------

            select * from M_PRODUCT
WHERE AD_Client_ID = 1000000
AND AD_Org_ID = 1000000
  AND ISACTIVE = 'Y'
  and name like 'BIOMAX APPETIT MAX SIROP 150ML%'
ORDER BY name;

select * from XX_LABORATORY
where XX_LABORATORY_ID=1003313;

SELECT *
                FROM C_BPartner cb
                WHERE cb.AD_Client_ID = 1000000
                  AND cb.ISVENDOR = 'Y'
                  AND cb.ISACTIVE = 'Y'
                  and cb.name like 'BIOMAX PHARM (PARA)%'
                ORDER BY cb.name;



SELECT DISTINCT
                    CAST(cb.name AS VARCHAR2(300)) AS FOURNISSEUR
                FROM 
                    M_InOut xf
                JOIN M_INOUTLINE mi ON mi.M_INOUT_ID = xf.M_INOUT_ID
                JOIN C_BPartner cb ON cb.C_BPARTNER_ID = xf.C_BPARTNER_ID
                JOIN M_PRODUCT m ON m.M_PRODUCT_id = mi.M_PRODUCT_id
                WHERE 
                    UPPER(m.name) LIKE UPPER(:product) || '%'
                    AND xf.AD_Org_ID = 1000000
                    AND xf.C_DocType_ID IN (1000013, 1000646)
                    AND xf.M_Warehouse_ID IN (1000724, 1000000, 1000720, 1000725)
                ORDER BY 
                 FOURNISSEUR;





                 select processed, docstatus, docaction from M_InOut  where M_InOut_ID=3088414;

                 UPDATE M_InOut
SET processed = 'N',
    docstatus = 'DR',
    docaction = 'PR'
WHERE M_InOut_ID = 3196674;




                     elif processed == 'N' and docstatus == 'DR' and (docaction.upper() == 'PR' or docaction.upper() == 'CO'):
        state = "Brouillon"


    

    

    SELECT ispaid, ad_org_id, c_bpartner_id, dateinvoiced, nbl,nfact, documentno, grandtotal, verse_fact, 
verse_cheque, cheque, name, dateversement, nb, region,  
SUM (grandtotal/nb) over( PARTITION BY c_bpartner_id) AS bp_chiffre,
SUM (verse_fact/nb) over( PARTITION BY c_bpartner_id ) AS verse_tot,
orgname, phone, phone2, fax,
address1, address2, address3, address4, city, postal
FROM 
(SELECT cs.ispaid, cs.ad_org_id , cs.c_bpartner_id,  cs.dateinvoiced, cs.nbl,cs.nfact, cs.documentno AS documentno, 
 cs.grandtotal, cs.verse_fact, 
 cs.verse_cheque, cs.cheque, cs.name, cs.dateversement, cs.region,  
 COUNT(cs.c_invoice_id) over (PARTITION BY cs.c_invoice_id) AS nb 
, org.name as orgname, oi.phone, oi.phone2, oi.fax,
loc.address1, loc.address2, loc.address3, loc.address4, loc.city, loc.postal
 FROM xx_vendor_status cs  
inner join ad_org org on (cs.ad_org_id=org.ad_org_id)
inner join ad_orginfo oi on (org.ad_org_id=oi.ad_org_id)
inner join c_location loc on (oi.c_location_id=loc.c_location_id)
 WHERE cs.AD_Client_ID=$P{AD_Client_ID}
and cs.AD_Org_ID=$P{AD_Org_ID}
and cs.C_BPartner_ID=$P{C_BPartner_ID}
and cs.dateinvoiced between $P{Date1} and $P{Date2}
and ($P{IsPaid} is null or $P{IsPaid}='' or $P{IsPaid}=cs.ispaid)
and ($P{C_DocType_ID} is null or $P{C_DocType_ID}=0 or cs.C_DocType_ID=$P{C_DocType_ID})
and (cs.ad_orgtrx_id=$P{AD_OrgTrx_ID} or $P{AD_OrgTrx_ID} is null or $P{AD_OrgTrx_ID}=0)
order by cs.dateinvoiced, cs.nbl, cs.nfact)
order by dateinvoiced, nbl, nfact;



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
 AND cs.C_BPartner_ID = :C_BPartner_ID
 AND cs.dateinvoiced BETWEEN :Date1 AND :Date2
 AND (:IsPaid IS NULL OR :IsPaid = '' OR :IsPaid = cs.ispaid)
 AND (:C_DocType_ID IS NULL OR :C_DocType_ID = 0 OR cs.C_DocType_ID = :C_DocType_ID)
 AND (cs.ad_orgtrx_id IS NULL OR cs.ad_orgtrx_id = 0)
 ORDER BY cs.dateinvoiced, cs.nbl, cs.nfact)
ORDER BY dateinvoiced, nbl, nfact;


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
 AND cs.C_BPartner_ID = 1123624
 AND cs.dateinvoiced BETWEEN TO_DATE('01/01/2025', 'DD/MM/YYYY') AND TO_DATE('01/06/2025', 'DD/MM/YYYY')
 
 ORDER BY cs.dateinvoiced, cs.nbl, cs.nfact)
ORDER BY dateinvoiced, nbl, nfact;


SELECT inv.DATEINVOICED AS DateTrx,
                  inv.DOCUMENTNO,
                  inv.POREFERENCE as POREFERENCE,
                  doc.PrintName as name,
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
                  END AS GRANDTOTAL
                FROM C_INVOICE inv,
                  C_DOCTYPE doc
                WHERE inv.C_DOCTYPE_ID =doc.C_DOCTYPE_ID
                AND inv.DOCSTATUS     IN ('CO','CL')
                AND doc.docbasetype   IN ('API','APC')
                AND inv.AD_Client_ID = 1000000
                AND inv.AD_Org_ID = 1000000
                AND inv.C_BPARTNER_ID  = :fournisseur_id
                AND (inv.DATEINVOICED >= TO_DATE(:date1, 'YYYY-MM-DD')
                AND inv.DATEINVOICED  <= TO_DATE(:date2, 'YYYY-MM-DD'))
                UNION
                SELECT pa.DATETRX AS DateTrx,
                  pa.DOCUMENTNO,
                  NULL as POREFERENCE,
                  'Discount' AS NAME,
                  '' AS DESCRIPTION,
                  pa.discountamt * -1 AS GRANDTOTAL
                FROM C_PAYMENT pa,
                  C_DOCTYPE doc
                WHERE pa.C_DOCTYPE_ID=doc.C_DOCTYPE_ID
                AND pa.DOCSTATUS    IN ('CO','CL')
                AND doc.docbasetype IN ('APP')
                AND pa.AD_Client_ID = 1000000
                AND pa.AD_Org_ID = 1000000
                AND pa.C_BPARTNER_ID = :fournisseur_id
                AND (pa.DATETRX     >= TO_DATE(:date1, 'YYYY-MM-DD')
                AND pa.DATETRX      <= TO_DATE(:date2, 'YYYY-MM-DD'))
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
                    ELSE ''
                  END AS DESCRIPTION,
                  (pa.PAYAMT * -1) AS GRANDTOTAL
                FROM C_PAYMENT pa,
                  C_DOCTYPE doc
                WHERE pa.C_DOCTYPE_ID=doc.C_DOCTYPE_ID
                AND pa.DOCSTATUS    IN ('CO','CL')
                AND doc.docbasetype IN ('APP')
                AND pa.AD_Client_ID = 1000000
                AND pa.AD_Org_ID = 1000000
                AND pa.C_BPARTNER_ID = :fournisseur_id
                AND (pa.DATETRX     >= TO_DATE(:date1, 'YYYY-MM-DD')
                AND pa.DATETRX      <= TO_DATE(:date2, 'YYYY-MM-DD'))
                UNION
                SELECT DATETRX as DateTrx,
                DOCUMENTNO as DOCUMENTNO,
                NULL as POREFERENCE,
                 doc.PrintName as name,
                 'DIFFERENCE' as DESCRIPTION,
                 COALESCE((select sum(al.writeoffamt* -1) from C_ALLOCATIONLINE al where (al.C_PAYMENT_ID = par.C_PAYMENT_ID and par.C_BPARTNER_ID = :fournisseur_id)), 0) AS GRANDTOTAL
                FROM C_PAYMENT par, c_doctype doc
                WHERE doc.docbasetype IN ('APP')
                AND par.DOCSTATUS IN ('CO','CL')
                AND par.AD_Client_ID = 1000000
                AND par.AD_Org_ID = 1000000
                AND par.C_BPARTNER_ID = :fournisseur_id
                AND par.C_DOCTYPE_ID=doc.C_DOCTYPE_ID
                AND (par.DATETRX >= TO_DATE(:date1, 'YYYY-MM-DD')
                AND par.DATETRX <= TO_DATE(:date2, 'YYYY-MM-DD'))
                AND COALESCE((select sum(al.writeoffamt* -1) from C_ALLOCATIONLINE al where (al.C_PAYMENT_ID = par.C_PAYMENT_ID and par.C_BPARTNER_ID = :fournisseur_id)), 0) <> 0
                UNION
                SELECT c.StatementDATE AS DateTrx,
                  c.Name As DOCUMENTNO,
                  i.POREFERENCE as POREFERENCE,
                  'Facture sur Caisse' as NAME,
                 CASE
                    WHEN cl.DESCRIPTION IS NOT NULL
                    THEN cl.DESCRIPTION
                    WHEN cl.C_INVOICE_ID IS NOT NULL
                    THEN
                      i.DOCUMENTNO
                    ELSE ''
                  END AS DESCRIPTION,
                  cl.Amount * -1 AS GRANDTOTAL
                FROM C_CashLine cl
                INNER JOIN C_Cash c ON (cl.C_Cash_ID=c.C_Cash_ID)
                INNER JOIN C_Invoice i ON (cl.C_Invoice_ID=i.C_Invoice_ID)
                WHERE 
                 c.DOCSTATUS IN ('CO','CL')
                 AND i.ispaid='Y'
                AND cl.isactive='Y'
                AND c.AD_Client_ID = 1000000
                AND c.AD_Org_ID = 1000000
                AND i.C_BPARTNER_ID = :fournisseur_id
                AND (c.StatementDATE >= TO_DATE(:date1, 'YYYY-MM-DD')
                AND c.StatementDATE <= TO_DATE(:date2, 'YYYY-MM-DD'))
                ORDER BY DateTrx;






select name from M_PRODUCT
WHERE AD_Client_ID = 1000000
AND AD_Org_ID = 1000000
  AND ISACTIVE = 'Y'
  and CREATED >= TO_DATE('01/07/2025', 'DD/MM/YYYY')
ORDER BY name
LIMIT 100;









SELECT cb.name, cb.C_BPartner_ID, cb.description
                FROM C_BPartner cb
                WHERE cb.AD_Client_ID = 1000000
                and ad_org_id = 1000000
                  AND cb.iscustomer = 'Y'
                  AND cb.ISACTIVE = 'Y'
                  AND (XX_RC IS NULL
                  OR XX_NIF IS NULL
                  OR XX_AI IS NULL )
                ORDER BY cb.name;





SELECT 
    cb.name, 
    cb.C_BPartner_ID, 
    cb.description,
    CASE 
        WHEN cb.XX_RC IS NULL THEN TO_NCHAR('NO RC') 
        ELSE TO_NCHAR(cb.XX_RC) 
    END AS RC_Status,
    CASE 
        WHEN cb.XX_NIF IS NULL THEN TO_NCHAR('NO NIF') 
        ELSE TO_NCHAR(cb.XX_NIF) 
    END AS NIF_Status,
    CASE 
        WHEN cb.XX_AI IS NULL THEN TO_NCHAR('NO AI') 
        ELSE TO_NCHAR(cb.XX_AI) 
    END AS AI_Status
FROM C_BPartner cb
WHERE cb.AD_Client_ID = 1000000
  AND cb.AD_Org_ID = 1000000
  AND cb.IsCustomer = 'Y'
  AND cb.IsActive = 'Y'
  AND (
      cb.XX_RC IS NULL
      OR cb.XX_NIF IS NULL
      OR cb.XX_AI IS NULL
  )
ORDER BY cb.name;

SELECT DISTINCT
                    cb.C_BPartner_ID AS "CLIENT_ID",
                    cb.name AS "CLIENT_NAME",
                    sr.name AS "ZONE"
                FROM C_SalesRegion sr
                JOIN C_BPartner_Location bpl ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                JOIN C_BPartner cb ON cb.C_BPartner_ID = bpl.C_BPartner_ID
                JOIN xx_ca_fournisseur xf ON bpl.C_BPartner_ID = xf.CLIENTID
                WHERE UPPER(sr.name) = UPPER(:zone)
                    AND xf.AD_Org_ID = 1000000
                    AND xf.DOCSTATUS != 'RE'
                ORDER BY cb.name;

SELECT DISTINCT
    cb.C_BPartner_ID AS "CLIENT_ID",
    cb.name AS "CLIENT_NAME",
    sr.name AS "ZONE"
FROM C_SalesRegion sr
JOIN C_BPartner_Location bpl ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
JOIN C_BPartner cb ON cb.C_BPartner_ID = bpl.C_BPartner_ID
JOIN xx_ca_fournisseur xf ON bpl.C_BPartner_ID = xf.CLIENTID
WHERE UPPER(sr.name) IN (
    'CNE 1 (SMK/DJBEL WAHECHE/BOUSSOUF)',
    'TIZI OUZOU',
    'BATNA',
    'BEJAIA',
    'EL OUED',
    'SKIKDA',
    'SETIF',
    'EL BORDJ /MSILA',
    'TEBESSA / KHENCHELA',
    'JIJEL/ MILA',
    'GUELMA',
    'ANNABA',
    '<AUCUNE>',
    'OUARGLA',
    'CHELGHOUM',
    'CNE 3 (AIN SMARA/ZOUAGHI)',
    'CNE 2 (NOUVELLE/KHROUB)',
    'EL KALA',
    'BISKRA'
)
AND xf.AD_Org_ID = 1000000
AND xf.DOCSTATUS != 'RE'
ORDER BY sr.name;





SELECT

                t.MovementDate AS MovementDate,
                nvl(nvl(io.documentno,inv.documentno),m.documentno) as documentno,
                nvl(bp.name, nvl(inv.description,m.description)) as name,
                p.name AS productname,
                CASE WHEN t.movementqty > 0 then t.movementqty else 0 end as ENTREE,
                CASE WHEN t.movementqty < 0 then ABS(t.movementqty) else 0 end as SORTIE,
                asi.lot,
                l.value AS locator,
                COALESCE(io.docstatus, m.docstatus, inv.docstatus, 'N/A') AS docstatus
            FROM M_Transaction t
            INNER JOIN M_Locator l ON (t.M_Locator_ID=l.M_Locator_ID)
            INNER JOIN M_Product p ON (t.M_Product_ID=p.M_Product_ID)
            LEFT OUTER JOIN M_InventoryLine il ON (t.M_InventoryLine_ID=il.M_InventoryLine_ID)
            LEFT OUTER JOIN M_Inventory inv ON (inv.m_inventory_id = il.m_inventory_id)
            LEFT OUTER JOIN M_MovementLine ml ON (t.M_MovementLine_ID=ml.M_MovementLine_ID)
            LEFT OUTER JOIN M_Movement m ON (m.M_Movement_ID=ml.M_Movement_ID)
            LEFT OUTER JOIN M_InOutLine iol ON (t.M_InOutLine_ID=iol.M_InOutLine_ID)
            LEFT OUTER JOIN M_Inout io ON (iol.M_InOut_ID=io.M_InOut_ID)
            LEFT OUTER JOIN C_BPartner bp ON (bp.C_BPartner_ID = io.C_BPartner_ID)
            INNER JOIN M_attributesetinstance asi on t.m_attributesetinstance_id = asi.m_attributesetinstance_id
            INNER JOIN M_attributeinstance att on (att.m_attributesetinstance_id = asi.m_attributesetinstance_id)
            WHERE
            att.m_attribute_id = 1000508
            AND COALESCE(io.docstatus, m.docstatus, inv.docstatus) IN ('VO')
            AND NOT (t.movementqty = 0)
            AND t.AD_Client_ID = 1000000
            AND (inv.description IS NULL and m.description IS NULL and io.description IS NULL)
            ORDER BY t.MovementDate DESC;



select  description , docstatus from M_InOut
WHERE M_InOut_ID in(3114317, 3114338);


update M_InOut
SET docstatus = 'VO'
WHERE M_InOut_ID =3021394;









SELECT * FROM (
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
                        1 AS sort_order
                    FROM 
                        c_order co
                    INNER JOIN ad_org org ON co.ad_org_id = org.ad_org_id
                    INNER JOIN c_bpartner cb ON co.c_bpartner_id = cb.c_bpartner_id
                    INNER JOIN ad_user us ON co.salesrep_id = us.ad_user_id
                    WHERE 
                         co.docaction  ='PR'
                        AND co.ad_org_id = 1000000
                        and docstatus = 'IP'
                        and issotrx = 'Y'


                    
                    UNION ALL
                    
                    SELECT 
                        CAST('Total' AS VARCHAR2(300)) AS organisation,
                        CAST(NULL AS VARCHAR2(50)) AS ndocument,
                        CAST(NULL AS VARCHAR2(300)) AS tier,
                        NULL AS datecommande,
                        CAST(NULL AS VARCHAR2(100)) AS vendeur,
                        ROUND(AVG(ROUND(((co.totallines / (SELECT SUM(mat.valuenumber * li.qtyentered) 
                             FROM c_orderline li 
                             INNER JOIN m_attributeinstance mat ON mat.m_attributesetinstance_id = li.m_attributesetinstance_id
                             WHERE mat.m_attribute_id = 1000504 
                               AND li.c_order_id = co.c_order_id 
                               AND li.qtyentered > 0 
                             GROUP BY li.c_order_id)) - 1) * 100, 2)), 2) AS marge,
                        ROUND(SUM(co.totallines), 2) AS montant,
                        0 AS sort_order
                    FROM 
                        c_order co
                    INNER JOIN ad_org org ON co.ad_org_id = org.ad_org_id
                    INNER JOIN c_bpartner cb ON co.c_bpartner_id = cb.c_bpartner_id
                    INNER JOIN ad_user us ON co.salesrep_id = us.ad_user_id
                    WHERE 
                         co.docaction  ='PR'
                        AND co.ad_org_id = 1000000
                        and docstatus = 'IP'
                        and issotrx = 'Y'
                        
                )
                ORDER BY sort_order, montant DESC;




                select * from c_order where c_order_id in (3258167,3222958);






                SELECT ml.value AS EMPLACEMENT, ml.m_locator_id
                FROM M_Locator ml
                JOIN M_Warehouse m ON m.M_WAREHOUSE_ID = ml.M_WAREHOUSE_ID
                WHERE m.ISACTIVE = 'Y'
                  AND m.AD_Client_ID = 1000000
                  AND ml.ISACTIVE = 'Y'
                  AND ml.AD_Client_ID = 1000000;



                  SELECT DISTINCT m.value AS MAGASIN, ml.m_locator_id
                FROM M_Locator ml
                JOIN M_Warehouse m ON m.M_WAREHOUSE_ID = ml.M_WAREHOUSE_ID
                WHERE m.ISACTIVE = 'Y'
                  AND m.AD_Client_ID = 1000000
                  AND ml.ISACTIVE = 'Y'
                  AND ml.AD_Client_ID = 1000000;
----------------------------------------------------------------
--- ORM--------------
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
                    ROUND(co.totallines, 2) AS montant
                FROM 
                    c_order co
                INNER JOIN ad_org org ON co.ad_org_id = org.ad_org_id
                INNER JOIN c_bpartner cb ON co.c_bpartner_id = cb.c_bpartner_id
                INNER JOIN ad_user us ON co.salesrep_id = us.ad_user_id
                WHERE 
    co.ad_org_id = 1000000
    AND issotrx = 'Y'
    AND C_DOCTYPETARGET_ID=1001408;

    ---------------------------------
   select * from C_DocType where C_DocType_ID = 1001408;
   ------------
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
    ROUND(co.totallines, 2) AS montant
FROM 
    c_order co
INNER JOIN ad_org org ON co.ad_org_id = org.ad_org_id
INNER JOIN c_bpartner cb ON co.c_bpartner_id = cb.c_bpartner_id
INNER JOIN ad_user us ON co.salesrep_id = us.ad_user_id
WHERE 
    co.ad_org_id = 1000000
    AND issotrx = 'Y'
    AND C_DOCTYPETARGET_ID = 1001408
    AND co.dateordered >= TO_DATE(:start_date, 'YYYY-MM-DD')
    AND co.dateordered <= TO_DATE(:end_date, 'YYYY-MM-DD')


;


select docstatus from C_Order where C_Order_ID in (3259877,3259876);






SELECT 
    i.documentno, 
    i.totallines, 
    i.description, 
    i.dateinvoiced, 
    i.c_bpartner_id, 
    i.C_Invoice_ID,
FROM C_Invoice i
JOIN C_BPartner cb ON i.c_bpartner_id = cb.c_bpartner_id
WHERE cb.name = :partner_name
  AND i.dateinvoiced BETWEEN :start_date AND :end_date;




SELECT 
    qtyentered, 
    m_product_id, 
    linenetamt
FROM C_InvoiceLine
WHERE c_invoice_id = :invoice_id;




select CLIENTID from xx_ca_fournisseur where  MOVEMENTDATE BETWEEN :start_date AND :end_date FETCH FIRST 1 ROWS ONLY;


SELECT name, c_bp_group_id,C_BPARTNER_ID
                FROM C_BPartner
                WHERE iscustomer = 'Y'
                  AND AD_Client_ID = 1000000
                  AND AD_Org_ID = 1000000
                ORDER BY name;

                --1000003 para     1001330 pot
SELECT 

    cb.name, 
    cb.c_bp_group_id,
    cb.C_BPARTNER_ID,
    CASE 
        WHEN cb.c_bp_group_id = 1000003 THEN 'para'
        WHEN cb.c_bp_group_id = 1001330 THEN 'potentiel'
        ELSE 'autre'
    END AS group_label
FROM C_BPartner cb
WHERE cb.ISCUSTOMER = 'Y'
  AND cb.AD_Client_ID = 1000000
  AND cb.AD_Org_ID = 1000000
  AND cb.C_BPARTNER_ID = (
        SELECT CLIENTID 
        FROM xx_ca_fournisseur 
        WHERE MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD')
        FETCH FIRST 1 ROWS ONLY
  )
;

SELECT 
    cb.name, 
    cb.c_bp_group_id,
    cb.C_BPARTNER_ID,
    CASE 
        WHEN cb.c_bp_group_id = 1000003 THEN 'para'
        WHEN cb.c_bp_group_id = 1001330 THEN 'potentiel'
        ELSE 'autre'
    END AS group_label
FROM C_BPartner cb
WHERE cb.ISCUSTOMER = 'Y'
  AND cb.AD_Client_ID = 1000000
  AND cb.AD_Org_ID = 1000000
  AND cb.C_BPARTNER_ID IN (
      SELECT CLIENTID 
      FROM xx_ca_fournisseur 
      WHERE MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD')
  AND (
      (:group_label IS NULL) 
      OR 
      (CASE 
          WHEN cb.c_bp_group_id = 1000003 THEN 'para'
          WHEN cb.c_bp_group_id = 1001330 THEN 'potentiel'
          ELSE 'autre'
      END = :group_label)
  )
  );
------------------------------------------


                DEFINE BankStatement = 1025270;
UPDATE C_BankStatement 
SET  processed = 'N',docstatus = 'DR', docaction = 'CO'
WHERE C_BankStatement_id = &BankStatement;
UPDATE C_BankStatementLine  
SET processed = 'N'
WHERE C_BankStatement_id = &BankStatement;
COMMIT;
--------------------------------------------------------------
------------------------ Pour Achever Un Extrait de Caisse -----------------------------------------------------
-----------------------------------------------------------------
UPDATE C_BankStatement 
SET processed = 'Y',docstatus = 'CO', docaction = 'CL'
WHERE C_BankStatement_id = &BankStatement;
UPDATE C_BankStatementLine  
SET processed = 'Y'
WHERE C_BankStatement_id = &BankStatement;
COMMIT;





SELECT DISTINCT cb.name as client, ad.name as operator
            FROM c_bpartner cb
            INNER JOIN ad_user ad ON (cb.salesrep_id = ad.ad_user_id)
            WHERE ad.c_bpartner_id IN (1121780,1122143,1118392,1122144,1119089,1111429,1122761,1122868,1122142,1143361)
            AND cb.isactive = 'Y'
            AND cb.ad_org_id = 1000000
            ORDER BY ad.name, cb.name ;


            SELECT 
    ad.name as operator,
    COUNT(DISTINCT cb.c_bpartner_id) as client_count
FROM c_bpartner cb
INNER JOIN ad_user ad ON (cb.salesrep_id = ad.ad_user_id)
WHERE ad.c_bpartner_id IN (1121780,1122143,1118392,1122144,1119089,1111429,1122761,1122868,1122142,1143361)
AND cb.isactive = 'Y'
AND cb.ad_org_id = 1000000
GROUP BY ad.name
ORDER BY ad.name;



select * from c_bpartner;



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
      "source"."LOCATION" "LOCATION"
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
          END AS location
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
                      ) ppa
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
                      AND mst.m_locator_id IN (1001135, 1000614, 1001128, 1001136, 1001020)
                      AND mst.qtyonhand != 0
                      AND p.m_product_id = :product_id
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
          m_locator_id
        ORDER BY
          fournisseur
      ) "source"
  )
WHERE
  rownum <= 1048575



;


SELECT
    "source"."M_PRODUCT_ID" "M_PRODUCT_ID",
    "source"."QTY_DISPO" "QTY_DISPO",
    "source"."LOCATION" "LOCATION"
FROM
    (
        SELECT
            DISTINCT 
            p.m_product_id AS "M_PRODUCT_ID",
            (mst.qtyonhand - mst.QTYRESERVED) AS "QTY_DISPO",
            CASE 
                WHEN mst.m_locator_id = 1000614 THEN 'Préparation'
                WHEN mst.m_locator_id = 1001135 THEN 'HANGAR'
                WHEN mst.m_locator_id = 1001128 THEN 'Dépot_réserve'
                WHEN mst.m_locator_id = 1001136 THEN 'HANGAR_'
                WHEN mst.m_locator_id = 1001020 THEN 'Depot_Vente'
            END AS "LOCATION"
        FROM
            m_product p
            INNER JOIN m_storage mst ON p.m_product_id = mst.m_product_id
            INNER JOIN m_attributeinstance mati ON mst.m_attributesetinstance_id = mati.m_attributesetinstance_id
        WHERE
            mati.m_attribute_id = 1000508
            AND mst.m_locator_id IN (1001135, 1000614, 1001128, 1001136, 1001020)
            AND mst.qtyonhand != 0
            AND p.m_product_id = :product_id
        ORDER BY
            p.m_product_id
    ) "source"
WHERE
    rownum <= 1048575;



------------------------------------------change qty dispo----------------------------
    UPDATE m_storage
SET  QTYRESERVED = 0  -- This makes QTY_DISPO = qtyonhand - QTYRESERVED = 0
WHERE m_product_id = 1159659
and QTYRESERVED = 15;
-------------------------------------------------------------------------
 
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
            "source"."QTY" "QTY",
            "source"."QTY_DISPO" "QTY_DISPO",

            "source"."GUARANTEEDATE" "GUARANTEEDATE",  -- Added the GUARANTEEDATE column
            "source"."PPA" "PPA",  -- Added the PPA column
            "source"."LOCATION" "LOCATION"
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
                    qty,
                    qty_dispo,

                    guaranteedate,  -- Added the GUARANTEEDATE column
                    ppa,  -- Added the PPA column
                    CASE 
                        WHEN m_locator_id = 1000614 THEN 'Préparation'
                        WHEN m_locator_id = 1001135 THEN 'HANGAR'
                        WHEN m_locator_id = 1001128 THEN 'Dépot_réserve'
                        WHEN m_locator_id = 1001136 THEN 'HANGAR_'
                        WHEN m_locator_id = 1001020 THEN 'Depot_Vente'
                    END AS location
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
                                                    valuenumber
                                                FROM
                                                    m_attributeinstance
                                                WHERE
                                                    m_attributesetinstance_id = mst.m_attributesetinstance_id
                                                    AND m_attribute_id = 1000503
                                            ) ppa
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
                                            AND mst.m_locator_id IN (1001135, 1000614, 1001128, 1001136, 1001020)
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
                    qty,
                    qty_dispo,
                    guaranteedate,  -- Added to GROUP BY
                    ppa,  -- Added PPA to GROUP BY
                    m_locator_id
                ORDER BY
                    fournisseur
            ) "source"
    )
WHERE
    rownum <= 1048575



    ;


M_Attribute - M_Attribute_ID=1000808 ---- bonus achat

    select * from m_attributesetinstance where m_product_id = 1190579;




// ...existing code...
-- Fixed query: get bonus achat (1000808) and bonus vente (1000908) as columns
SELECT
    pr.M_Product_ID,
    pr.Name,
    MAX(CASE WHEN ai.M_Attribute_ID = 1000808 THEN ai.ValueNumber END) AS ug_achat,
    MAX(CASE WHEN ai.M_Attribute_ID = 1000908 THEN ai.ValueNumber END) AS ug_vente
FROM M_Product pr
JOIN M_Storage st
  ON st.M_Product_ID = pr.M_Product_ID
JOIN M_AttributeSetInstance mat
  ON mat.M_AttributeSetInstance_ID = st.M_AttributeSetInstance_ID
JOIN M_AttributeInstance ai
  ON ai.M_AttributeSetInstance_ID = mat.M_AttributeSetInstance_ID
WHERE pr.AD_Client_ID = 1000000
  AND pr.AD_Org_ID = 1000000
  AND pr.IsActive = 'Y'
  AND pr.M_Product_ID = 1193472
  AND ai.M_Attribute_ID IN (1000808, 1000908)
GROUP BY pr.M_Product_ID, pr.Name;





select pr.xx_ugbylot, sc.name from m_product pr 
join XX_SalesContext sc on sc.XX_SalesContext_ID = pr.XX_SalesContext_ID
where pr.m_product_id = 1193472;




--------------------------------------------------- NO AI NIF NIS---------------------------------
SELECT 
  name,
  CASE 
    WHEN xx_rc IS NULL THEN 'manque rc ' 
    ELSE TO_CHAR(xx_rc) 
  END AS xx_rc,
  CASE 
    WHEN xx_ai IS NULL THEN 'manque ai ' 
    ELSE TO_CHAR(xx_ai) 
  END AS xx_ai,
  CASE 
    WHEN xx_nif IS NULL THEN 'manque nif ' 
    ELSE TO_CHAR(xx_nif) 
  END AS xx_nif
FROM C_BPartner cb
WHERE isactive = 'Y'
  AND AD_Org_ID = 1000000
  AND AD_Client_ID = 1000000
  AND iscustomer = 'Y'
  AND (xx_rc IS NULL OR xx_ai IS NULL OR xx_nif IS NULL );
------------------------------------------------------------------------------


SELECT *
 FROM M_AttributeSetInstance WHERE M_AttributeSetInstance_ID = 1556024;


SELECT DOCACTION, DOCSTATUS FROM M_InOut WHERE M_InOut_ID=3154985;

----------------------------------- status of document---------------------------------------------
SELECT XX_PREPARATEUR_ID FROM M_InOut WHERE M_InOut_ID=3168410;


select DOCACTION, DOCSTATUS FROM C_Invoice WHERE C_Invoice_ID=1715497;


UPDATE  M_InOut  SET  XX_PREPARATEUR_ID = 1129847   WHERE M_InOut_ID=3209913;



UPDATE  C_Invoice  SET  DOCACTION = 'CL' , DOCSTATUS = 'CO'   WHERE C_Invoice_ID=1715497;



UPDATE  M_InOut  SET  DOCACTION = 'CO' , DOCSTATUS = 'IP'   WHERE M_InOut_ID=3209913;



---
select percentage, name from c_bpartner 
WHERE c_bpartner_id IN (1121780, 1122761, 1122868, 1122144, 1111429, 1122142, 1118392, 1119089, 1122143);


select name from AD_User  
WHERE AD_User_ID = 1037443;

select NAME from C_BPartner WHERE  ISACTIVE='Y' AND iscustomer='Y' AND TOTALOPENBALANCE<0;
----------------------------
SELECT
cc.documentno AS invoice_no,
pa.documentno AS reception_no,
cc.totallines,
par.name AS partner_name,
SUM(a.MOVEMENTQTY * att1.VALUENUMBER) AS calculated_total,
cc.totallines - SUM(a.MOVEMENTQTY * att1.VALUENUMBER) AS difference,
CASE
WHEN cc.totallines = 0 THEN NULL
ELSE ROUND((cc.totallines - SUM(a.MOVEMENTQTY * att1.VALUENUMBER)) / cc.totallines * 100, 2)
END AS difference_percent,
cc.C_Invoice_ID
FROM M_InOutLine a , C_Invoice cc
INNER JOIN M_ATTRIBUTEINSTANCE att1
ON a.M_ATTRIBUTESETINSTANCE_ID = att1.M_ATTRIBUTESETINSTANCE_ID
INNER JOIN M_InOut pa
ON pa.M_InOut_ID = a.M_InOut_ID

INNER JOIN C_BPartner par
ON par.C_BPartner_ID = cc.C_BPartner_ID
inner join M_MatchInv inv on inv.M_InOutLine_ID=a.M_InOutLine_ID
WHERE att1.M_Attribute_ID = 1000504
AND pa.C_DocType_ID = 1000013
AND pa.M_InOut_ID IN (
SELECT iol.M_InOut_ID
FROM C_InvoiceLine il
INNER JOIN M_InOutLine iol ON iol.M_InOutLine_ID = il.M_InOutLine_ID
WHERE il.C_Invoice_ID = cc.C_Invoice_ID
)
AND pa.CREATED BETWEEN TO_DATE(:date_debut, 'YYYY-MM-DD')
AND TO_DATE(:date_fin, 'YYYY-MM-DD') + 1 - (1/86400)
GROUP BY cc.documentno, pa.documentno, cc.totallines, par.name, cc.C_Invoice_ID
ORDER BY par.name, ABS(cc.totallines - SUM(a.MOVEMENTQTY * att1.VALUENUMBER)) DESC
;


select * from C_InvoiceLine where C_InvoiceLine_ID=5857160;


select  * from  M_MatchInv where M_MatchInv_ID=1256777;



select * from M_InOut where M_InOut_ID=3213187;

select * from M_InOutLine where M_InOutLine_ID=5752610;




SELECT IL.qtyentered as Quantite  ,IL.qtyinvoiced as Qty_facture ,p.name
FROM C_Invoice inv
JOIN C_InvoiceLine il ON inv.C_Invoice_ID = il.C_Invoice_ID
JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
WHERE inv.C_Invoice_ID=1714792;





SELECT 
    IL.qtyentered AS Quantite,
    IL.qtyinvoiced AS Qty_facture,
    p.name,
    mi.qty AS qty_RECEPTION,
    mi.C_InvoiceLine_ID AS match_invoice_id,
    mi.documentno AS match_documentno
FROM C_Invoice inv
JOIN C_InvoiceLine il ON inv.C_Invoice_ID = il.C_Invoice_ID
JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
LEFT JOIN M_MatchInv mi ON mi.C_InvoiceLine_ID = il.C_InvoiceLine_ID
WHERE inv.C_Invoice_ID = 1712053;
-------------------------------------------

SELECT 
    IL.qtyentered AS Quantite,
    IL.qtyinvoiced AS Qty_facture,
    p.name,
    mi.qty AS qty_RECEPTION,
    mi.C_InvoiceLine_ID AS match_invoice_id,
    mi.documentno AS match_documentno,
    il.C_InvoiceLine_ID AS invoice_line_id
FROM C_Invoice inv
JOIN C_InvoiceLine il ON inv.C_Invoice_ID = il.C_Invoice_ID
JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
LEFT JOIN M_MatchInv mi ON mi.C_InvoiceLine_ID = il.C_InvoiceLine_ID
WHERE inv.dateinvoiced > TO_DATE('2025-01-01', 'YYYY-MM-DD')
and inv.ad_org_id = 1000000
and inv.c_doctype_id = 1001509
and inv.docstatus in ('CO','CL') 
  AND (
        (IL.qtyentered != NVL(mi.qty, 0)) -- mismatch between invoice qty and reception qty
        OR (IL.qtyentered IS NOT NULL AND mi.documentno IS NULL) -- has qty but no match
      );


------------------------------------------------sql facture and reception difrence in qty mine + nazim sql
SELECT 
    IL.qtyentered AS Quantite,
    IL.qtyinvoiced AS Qty_facture,
    p.name,
    il.C_InvoiceLine_ID AS invoice_line_id,
    SUM(NVL(mi.qty, 0)) AS total_qty_reception,
    LISTAGG(mi.documentno, ', ') WITHIN GROUP (ORDER BY mi.documentno) AS match_documentnos
FROM C_Invoice inv
JOIN C_InvoiceLine il ON inv.C_Invoice_ID = il.C_Invoice_ID
JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
LEFT JOIN M_MatchInv mi ON mi.C_InvoiceLine_ID = il.C_InvoiceLine_ID
WHERE inv.dateinvoiced > TO_DATE('2025-01-01', 'YYYY-MM-DD')
  AND inv.ad_org_id = 1000000
  AND inv.c_doctype_id = 1001509
  AND inv.docstatus IN ('CO','CL')
GROUP BY 
    IL.qtyentered,
    IL.qtyinvoiced,
    p.name,
    il.C_InvoiceLine_ID
HAVING 
    IL.qtyentered != SUM(NVL(mi.qty, 0)) -- mismatch between invoice qty and sum of receptions
    OR (IL.qtyentered IS NOT NULL AND COUNT(mi.documentno) = 0) -- has qty but no match
ORDER BY 
    il.C_InvoiceLine_ID;


      
SELECT 
    ci.DOCUMENTNO AS INVOICE_NUMBER,
    ci.DATEINVOICED,
    bp.NAME AS CUSTOMER_NAME,
    mp.NAME AS PRODUCT_NAME,
    cil.LINE AS INVOICE_LINE_NUMBER,
    cil.QTYINVOICED AS INVOICED_QUANTITY,
    COALESCE(SUM(mmi.QTY), 0) AS RECEIVED_QUANTITY,
    (cil.QTYINVOICED - COALESCE(SUM(mmi.QTY), 0)) AS MISSING_QUANTITY,
    cil.LINENETAMT AS LINE_AMOUNT,
    COUNT(mmi.M_MATCHINV_ID) AS MATCH_RECORDS,
    -- Additional debug info
    LISTAGG(mmi.M_MATCHINV_ID, ', ') WITHIN GROUP (ORDER BY mmi.M_MATCHINV_ID) AS MATCHINV_IDS,
    LISTAGG(mmi.QTY, ', ') WITHIN GROUP (ORDER BY mmi.M_MATCHINV_ID) AS INDIVIDUAL_QTYS,
    LISTAGG(mmi.M_INOUTLINE_ID, ', ') WITHIN GROUP (ORDER BY mmi.M_MATCHINV_ID) AS INOUTLINE_IDS
FROM 
    C_INVOICE ci
    INNER JOIN C_INVOICELINE cil ON ci.C_INVOICE_ID = cil.C_INVOICE_ID
    INNER JOIN M_PRODUCT mp ON cil.M_PRODUCT_ID = mp.M_PRODUCT_ID
    INNER JOIN C_BPARTNER bp ON ci.C_BPARTNER_ID = bp.C_BPARTNER_ID
    LEFT JOIN M_MATCHINV mmi ON cil.C_INVOICELINE_ID = mmi.C_INVOICELINE_ID 
        AND mmi.AD_ORG_ID = 1000000
WHERE 
    ci.AD_ORG_ID = 1000000
    AND ci.C_DocTypeTarget_ID = 1001509
    AND ci.DOCSTATUS IN ('CO', 'CL')
    and ci.dateinvoiced > TO_DATE('2025-01-01', 'YYYY-MM-DD')


    AND cil.M_PRODUCT_ID IS NOT NULL
GROUP BY 
    ci.DOCUMENTNO,
    ci.DATEINVOICED,
    bp.NAME,
    mp.NAME,
    cil.LINE,
    cil.C_INVOICELINE_ID,
    cil.QTYINVOICED,
    cil.LINENETAMT
HAVING 
    cil.QTYINVOICED != COALESCE(SUM(mmi.QTY), 0)  -- Only show non-matching quantities
ORDER BY 
    ci.DATEINVOICED DESC,
    ci.DOCUMENTNO,
    cil.LINE;


    ----------------------------------    

    select * from C_DocType  where C_DocType_ID =1001510;
where isactive='Y' and ad_orgtrx_id=1000000 and ad_client_id=1000000


select isactive from C_BPartner_Product where C_BPartner_Product_ID=1018736;


SELECT 
    bp.NAME AS SUPPLIER_NAME,
    p.NAME AS PRODUCT_NAME,
    sc.name AS BONUS,
    iol.MOVEMENTQTY AS QUANTITY_RECEIVED,
    iol.QTYENTERED AS QTY_ENTERED,
    io.MOVEMENTDATE AS DATE_RECEIVED,
    io.DOCUMENTNO AS RECEIPT_DOCUMENT_NO,
    iol.M_ATTRIBUTESETINSTANCE_ID as lot_id,

    dsp.NAME AS M_DISCOUNTSCHEMA_POTENTIEL,
    dspara.NAME AS M_DISCOUNTSCHEMA_PARA

FROM M_INOUT io
INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
INNER JOIN M_PRODUCT p ON iol.M_PRODUCT_ID = p.M_PRODUCT_ID
INNER JOIN C_BPARTNER bp ON io.C_BPARTNER_ID = bp.C_BPARTNER_ID
JOIN XX_SALESCONTEXT sc ON p.XX_SALESCONTEXT_ID = sc.XX_SALESCONTEXT_ID

-- Join Potentiel Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1001330
) bppot ON bppot.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dsp ON dsp.M_DiscountSchema_ID = bppot.M_DISCOUNTSCHEMA_ID

-- Join Para Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1000003
) bppara ON bppara.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dspara ON dspara.M_DiscountSchema_ID = bppara.M_DISCOUNTSCHEMA_ID

WHERE io.DOCSTATUS IN ('CO', 'CL')
    AND io.C_DOCTYPE_ID = 1000013
    AND io.AD_CLIENT_ID = 1000000
    AND io.ISACTIVE = 'Y'
    AND iol.ISACTIVE = 'Y'
    AND p.ISACTIVE = 'Y'
    AND bp.ISACTIVE = 'Y'
    AND iol.M_PRODUCT_ID IS NOT NULL
    AND io.MOVEMENTDATE BETWEEN TO_DATE('2025-08-26', 'YYYY-MM-DD') AND TO_DATE('2025-08-26', 'YYYY-MM-DD')

ORDER BY io.MOVEMENTDATE DESC, bp.NAME, p.VALUE
;





        SELECT
    iol.M_INOUTLINE_ID,
    MAX(CASE WHEN ai.M_ATTRIBUTE_ID = 1001408 THEN ai.VALUENUMBER END) AS REM_VENTE,
    MAX(CASE WHEN ai.M_ATTRIBUTE_ID = 1000908 THEN ai.VALUENUMBER END) AS BON_VENTE
FROM M_INOUT io
INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
LEFT JOIN M_ATTRIBUTESETINSTANCE asi ON iol.M_ATTRIBUTESETINSTANCE_ID = asi.M_ATTRIBUTESETINSTANCE_ID
LEFT JOIN M_ATTRIBUTEINSTANCE ai ON asi.M_ATTRIBUTESETINSTANCE_ID = ai.M_ATTRIBUTESETINSTANCE_ID
WHERE asi.ISACTIVE = 'Y'
    AND ai.ISACTIVE = 'Y'
    AND ai.M_ATTRIBUTE_ID IN (1000908, 1001408)
    AND io.M_InOut_ID = 3215778
GROUP BY iol.M_INOUTLINE_ID;






SELECT 
    bp.NAME AS SUPPLIER_NAME,
    p.NAME AS PRODUCT_NAME,
    sc.name AS BONUS,
    iol.MOVEMENTQTY AS QUANTITY_RECEIVED,
    iol.QTYENTERED AS QTY_ENTERED,
    io.MOVEMENTDATE AS DATE_RECEIVED,
    io.DOCUMENTNO AS RECEIPT_DOCUMENT_NO,
    iol.M_ATTRIBUTESETINSTANCE_ID as lot_id,

    -- Fetch REM_VENTE and BON_VENTE from attributes
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1001408
          AND ai.ISACTIVE = 'Y'
    ) AS REM_VENTE,
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1000908
          AND ai.ISACTIVE = 'Y'
    ) AS BON_VENTE,

    dsp.NAME AS M_DISCOUNTSCHEMA_POTENTIEL,
    dspara.NAME AS M_DISCOUNTSCHEMA_PARA

FROM M_INOUT io
INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
INNER JOIN M_PRODUCT p ON iol.M_PRODUCT_ID = p.M_PRODUCT_ID
INNER JOIN C_BPARTNER bp ON io.C_BPARTNER_ID = bp.C_BPARTNER_ID
JOIN XX_SALESCONTEXT sc ON p.XX_SALESCONTEXT_ID = sc.XX_SALESCONTEXT_ID

-- Join Potentiel Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1001330
) bppot ON bppot.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dsp ON dsp.M_DiscountSchema_ID = bppot.M_DISCOUNTSCHEMA_ID

-- Join Para Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1000003
) bppara ON bppara.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dspara ON dspara.M_DiscountSchema_ID = bppara.M_DISCOUNTSCHEMA_ID

WHERE io.DOCSTATUS IN ('CO', 'CL')
    AND io.C_DOCTYPE_ID = 1000013
    AND io.AD_CLIENT_ID = 1000000
    AND io.ISACTIVE = 'Y'
    AND iol.ISACTIVE = 'Y'
    AND p.ISACTIVE = 'Y'
    AND bp.ISACTIVE = 'Y'
    AND iol.M_PRODUCT_ID IS NOT NULL
    AND io.MOVEMENTDATE BETWEEN TO_DATE('2025-08-26', 'YYYY-MM-DD') AND TO_DATE('2025-08-26', 'YYYY-MM-DD')

ORDER BY io.MOVEMENTDATE DESC, bp.NAME, p.VALUE
;


SELECT 
    bp.NAME AS SUPPLIER_NAME,
    p.NAME AS PRODUCT_NAME,
    sc.name AS BONUS,
    iol.MOVEMENTQTY AS QUANTITY_RECEIVED,
    iol.QTYENTERED AS QTY_ENTERED,
    io.MOVEMENTDATE AS DATE_RECEIVED,
    io.DOCUMENTNO AS RECEIPT_DOCUMENT_NO,
    iol.M_ATTRIBUTESETINSTANCE_ID as lot_id,

    -- Fetch REM_VENTE and BON_VENTE from attributes
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1001408
          AND ai.ISACTIVE = 'Y'
    ) AS REM_VENTE,
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1000908
          AND ai.ISACTIVE = 'Y'
    ) AS BON_VENTE,

    -- Fetch qty_dispo from m_storage
    (SELECT SUM(ms.qtyonhand - ms.qtyreserved)
        FROM m_storage ms
        WHERE ms.m_attributesetinstance_id = iol.m_attributesetinstance_id
    ) AS qty_dispo,

    dsp.NAME AS M_DISCOUNTSCHEMA_POTENTIEL,
    dspara.NAME AS M_DISCOUNTSCHEMA_PARA

FROM M_INOUT io
INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
INNER JOIN M_PRODUCT p ON iol.M_PRODUCT_ID = p.M_PRODUCT_ID
INNER JOIN C_BPARTNER bp ON io.C_BPARTNER_ID = bp.C_BPARTNER_ID
JOIN XX_SALESCONTEXT sc ON p.XX_SALESCONTEXT_ID = sc.XX_SALESCONTEXT_ID

-- Join Potentiel Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1001330
) bppot ON bppot.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dsp ON dsp.M_DiscountSchema_ID = bppot.M_DISCOUNTSCHEMA_ID

-- Join Para Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1000003
) bppara ON bppara.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dspara ON dspara.M_DiscountSchema_ID = bppara.M_DISCOUNTSCHEMA_ID

WHERE io.DOCSTATUS IN ('CO', 'CL')
    AND io.C_DOCTYPE_ID = 1000013
    AND io.AD_CLIENT_ID = 1000000
    AND io.ISACTIVE = 'Y'
    AND iol.ISACTIVE = 'Y'
    AND p.ISACTIVE = 'Y'
    AND bp.ISACTIVE = 'Y'
    AND iol.M_PRODUCT_ID IS NOT NULL
    AND io.MOVEMENTDATE BETWEEN TO_DATE('2025-08-26', 'YYYY-MM-DD') AND TO_DATE('2025-08-26', 'YYYY-MM-DD')

ORDER BY io.MOVEMENTDATE DESC, bp.NAME, p.VALUE
;




------------------ version of qty-------------------------

SELECT 
    bp.NAME AS SUPPLIER_NAME,
    p.NAME AS PRODUCT_NAME,
    sc.name AS BONUS,
    iol.MOVEMENTQTY AS QUANTITY_RECEIVED,
    iol.QTYENTERED AS QTY_ENTERED,
    io.MOVEMENTDATE AS DATE_RECEIVED,
    io.DOCUMENTNO AS RECEIPT_DOCUMENT_NO,
    iol.M_ATTRIBUTESETINSTANCE_ID as lot_id,

    -- Fetch REM_VENTE and BON_VENTE from attributes
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1001408
          AND ai.ISACTIVE = 'Y'
    ) AS REM_VENTE,
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1000908
          AND ai.ISACTIVE = 'Y'
    ) AS BON_VENTE,

    -- Qty dispo for this lot
    (SELECT SUM(ms.qtyonhand - ms.qtyreserved)
        FROM m_storage ms
        WHERE ms.m_attributesetinstance_id = iol.m_attributesetinstance_id

    ) AS qty_dispo,

    -- Qty dispo for all lots of this product
    (SELECT SUM(ms.qtyonhand - ms.qtyreserved)
        FROM m_storage ms
        WHERE ms.m_product_id = iol.m_product_id
          AND ms.ad_client_id = 1000000
          AND ms.qtyonhand > 0
          AND ms.m_locator_id = 1000614
    ) AS qty_dispo_total,

    dsp.NAME AS M_DISCOUNTSCHEMA_POTENTIEL,
    dspara.NAME AS M_DISCOUNTSCHEMA_PARA

FROM M_INOUT io
INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
INNER JOIN M_PRODUCT p ON iol.M_PRODUCT_ID = p.M_PRODUCT_ID
INNER JOIN C_BPARTNER bp ON io.C_BPARTNER_ID = bp.C_BPARTNER_ID
JOIN XX_SALESCONTEXT sc ON p.XX_SALESCONTEXT_ID = sc.XX_SALESCONTEXT_ID

-- Join Potentiel Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1001330
) bppot ON bppot.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dsp ON dsp.M_DiscountSchema_ID = bppot.M_DISCOUNTSCHEMA_ID

-- Join Para Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1000003
) bppara ON bppara.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dspara ON dspara.M_DiscountSchema_ID = bppara.M_DISCOUNTSCHEMA_ID

WHERE io.DOCSTATUS IN ('CO', 'CL')
    AND io.C_DOCTYPE_ID = 1000013
    AND io.AD_CLIENT_ID = 1000000
    AND io.ISACTIVE = 'Y'
    AND iol.ISACTIVE = 'Y'
    AND p.ISACTIVE = 'Y'
    AND bp.ISACTIVE = 'Y'
    AND iol.M_PRODUCT_ID IS NOT NULL
    AND io.MOVEMENTDATE BETWEEN TO_DATE('2025-08-26', 'YYYY-MM-DD') AND TO_DATE('2025-08-26', 'YYYY-MM-DD')

ORDER BY io.MOVEMENTDATE DESC, bp.NAME, p.VALUE
;

------------  CHANGE QTY FROM ORDER FACTURE EXP TRANSCTION M_STORAGE--------------------------------------


    UPDATE m_storage
SET  QTYONHAND = 2  -- This makes QTY_DISPO = qtyonhand - QTYRESERVED = 0
WHERE m_product_id = 1187592
 and m_locator_id = 1001020
 and M_ATTRIBUTESETINSTANCE_ID=1555135;




    select QTYRESERVED , QTYONHAND from m_storage
where   
 m_product_id = 1187592
 and m_locator_id = 1000614
;


 select M_ATTRIBUTESETINSTANCE_ID from C_OrderLine where C_OrderLine_ID=8643988;



select xx_preparateur_id from M_InOut where M_InOut_ID=3216327;

update M_InOut set docstatus='CO', docaction='CL' where M_InOut_ID=3209895;



update M_InOut set xx_preparateur_id=1145260  where M_InOut_ID=3209895;





SELECT QTYDELIVERED, QTYINVOICED  FROM C_OrderLine WHERE C_OrderLine_ID=8643988;

UPDATE C_OrderLine SET QTYINVOICED = 0, QTYDELIVERED = 0 WHERE C_OrderLine_ID=8643988;






SELECT MOVEMENTQTY FROM M_Transaction WHERE M_Transaction_ID=4884650;

UPDATE M_Transaction SET MOVEMENTQTY = 0 WHERE M_Transaction_ID=4884650;

---------------------------------------------------------------------------------------------------




 select * from M_Product where M_Product_ID=1193472;

SELECT DISTINCT
  p.M_Product_ID,
  p.name AS product_name,
  st.M_ATTRIBUTESETINSTANCE_ID,
  asi.lot,
  asi.guaranteedate,
  asi.description
FROM M_Product p
INNER JOIN M_Storage st ON p.M_Product_ID = st.M_Product_ID
INNER JOIN M_AttributeSetInstance asi ON st.M_AttributeSetInstance_ID = asi.M_AttributeSetInstance_ID
WHERE p.M_Product_ID = :product_id
  AND p.AD_Client_ID = 1000000
  AND p.IsActive = 'Y'
  AND asi.IsActive = 'Y'
ORDER BY asi.lot;



SELECT 
                  bp.NAME AS SUPPLIER_NAME,
                  p.NAME AS PRODUCT_NAME,
                  sc.name AS BONUS,
                  MAX((
                      SELECT ai.VALUENUMBER
                      FROM M_ATTRIBUTEINSTANCE ai
                      WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
                        AND ai.M_ATTRIBUTE_ID = 1001408
                        AND ai.ISACTIVE = 'Y'
                  )) AS REM_VENTE,
                  MAX((
                      SELECT ai.VALUENUMBER
                      FROM M_ATTRIBUTEINSTANCE ai
                      WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
                        AND ai.M_ATTRIBUTE_ID = 1000908
                        AND ai.ISACTIVE = 'Y'
                  )) AS BON_VENTE,
                  (SELECT SUM(ms.qtyonhand - ms.qtyreserved)
                      FROM m_storage ms
                      WHERE ms.m_product_id = p.m_product_id
                        AND ms.ad_client_id = 1000000
                        AND ms.qtyonhand > 0
                        AND ms.m_locator_id = 1000614
                  ) AS qty_dispo,
                  MAX(dsp.NAME)   AS REM_POT,
                  MAX(dspara.NAME) AS REM_PARA
              FROM M_INOUT io
              INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
              INNER JOIN M_PRODUCT p ON iol.M_PRODUCT_ID = p.M_PRODUCT_ID
              INNER JOIN C_BPARTNER bp ON io.C_BPARTNER_ID = bp.C_BPARTNER_ID
              JOIN XX_SALESCONTEXT sc ON p.XX_SALESCONTEXT_ID = sc.XX_SALESCONTEXT_ID
              LEFT JOIN (
                  SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
                  FROM C_BPartner_Product 
                  WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1001330
              ) bppot ON bppot.M_PRODUCT_ID = p.M_PRODUCT_ID
              LEFT JOIN M_DiscountSchema dsp ON dsp.M_DiscountSchema_ID = bppot.M_DiscountSchema_ID
              LEFT JOIN (
                  SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
                  FROM C_BPartner_Product 
                  WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1000003
              ) bppara ON bppara.M_PRODUCT_ID = p.M_PRODUCT_ID
              LEFT JOIN M_DiscountSchema dspara ON dspara.M_DiscountSchema_ID = bppara.M_DiscountSchema_ID
              WHERE io.DOCSTATUS IN ('CO', 'CL')
                AND io.C_DOCTYPE_ID = 1000013
                AND io.AD_CLIENT_ID = 1000000
                AND io.ISACTIVE = 'Y'
                AND iol.ISACTIVE = 'Y'
                AND p.ISACTIVE = 'Y'
                AND bp.ISACTIVE = 'Y'
                AND iol.M_PRODUCT_ID IS NOT NULL
                AND io.MOVEMENTDATE BETWEEN TO_DATE('2025-01-01', 'YYYY-MM-DD') AND TO_DATE('2025-08-27', 'YYYY-MM-DD')
              GROUP BY bp.NAME, p.NAME, sc.name, p.m_product_id
              ORDER BY bp.NAME, p.NAME
              ;



----------------
SELECT * FROM M_PRODUCTFROM 
    C_BankStatement
WHERE 
    C_BankAccount_ID = 1000205
    AND docstatus = 'CO'
    AND AD_Client_ID = 1000000
ORDER BY 
    statementdate DESC
FETCH FIRST 1 ROW ONLY;
-----------------------------


SELECT DOCUMENTNO, CREATED, DESCRIPTION 
FROM M_InOut 
WHERE TRUNC(CREATED) = TRUNC(SYSDATE)
  AND ISACTIVE = 'Y'
  AND DOCACTION = 'CO'
  AND DOCSTATUS = 'IP'
  AND PROCESSED = 'N'
  AND ISAPPROVED = 'N'
  AND XX_CONTROLEUR_ID IS NULL
  AND XX_PREPARATEUR_ID IS NULL
  AND XX_CONTROLEUR_CH_ID IS NULL
  AND XX_PREPARATEUR_CH_ID IS NULL
  AND XX_EMBALEUR_CH_ID IS NULL
  AND XX_EMBALEUR_ID IS NULL;

-------------------------------
SELECT DOCUMENTNO, CREATED, DESCRIPTION 
FROM M_InOut;


SELECT 
                    ROUND(PayAmt, 2) AS paiment
                FROM 
                    C_Payment
                WHERE 



SELECT 
    ROUND(ci.PayAmt, 2) AS paiment,
    ci.DOCACTION,
    ci.DOCUMENTNO,
    z.name 
FROM 
    C_Payment ci
    INNER JOIN ZSubPaymentRule z ON ci.ZSubPaymentRule_ID = z.ZSubPaymentRule_ID
WHERE 
    TRUNC(ci.DATETRX) = TRUNC(SYSDATE)
    AND ci.DOCACTION IN ('CO', 'CL', 'co', 'cl')
    AND ci.AD_Client_ID = 1000000;



    --------------- total paiment-------------------------------------
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
    AND z.name IN ('Encaiss: Espèces', 'Décaiss: Espèces');

----------------------paiment rows---------------------------------
SELECT 
    ROUND(ci.PayAmt, 2) AS paiment , ci.DOCACTION ,ci.DOCUMENTNO,z.name 
FROM 
    C_Payment ci   
    inner join ZSubPaymentRule z on (ci.ZSubPaymentRule_ID =z.ZSubPaymentRule_ID)
WHERE 
    TRUNC(ci.DATETRX) = TRUNC(TO_DATE('21-04-2025', 'DD-MM-YYYY'))
    AND ci.DOCACTION IN ('CO', 'CL', 'cl' , 'co')
    AND ci.AD_Client_ID = 1000000;
MAKE TODAY SYSTADE I MEAN

--------------------------------- cota---------------------------------------
select percentage, name from c_bpartner 
WHERE c_bpartner_id IN (1121780, 1122761, 1122868, 1122144, 1111429, 1122142, 1118392, 1119089, 1122143);






------------------------------------------------------------------------
SELECT 
    ROUND(SUM(total), 2) AS "OBJECTIF MENSUEL", 
    ROUND(SUM(totalp), 2) AS "TOTAL RECOUVREMENT",
    ROUND(SUM(totalp)/SUM(total), 2) AS "POURCENTAGE"
    FROM(
    SELECT 
        481114494.36 AS total,
        SUM(p.payamt) AS totalp
    FROM 
        C_Payment p
        JOIN C_BPartner b ON b.C_BPartner_id = p.C_BPartner_id
    WHERE 
        b.iscustomer = 'Y'
        AND b.C_PaymentTerm_ID != 1000000
        AND p.AD_Client_ID = 1000000
     --   AND p.docaction = 'CL'
        AND p.docstatus in ('CO','CL')
        AND p.ZSubPaymentRule_ID in (1000007,1000016)
       --- and p.C_DocType_ID=1001615
        --and p.ZSubPaymentRule_ID=1000016)
        AND p.datetrx >= '01/06/2025'
        AND p.datetrx <= '30/06/2025'
) temp_combined
;

SELECT s.sid, s.serial#, s.username
FROM v$session s
WHERE s.blocking_session IS NOT NULL;



SELECT *    FROM M_InOut 
 WHERE TRUNC(CREATED) = TO_DATE('2025-05-25', 'YYYY-MM-DD')
 and M_InOut_ID in (3190802, 3190808, 3190766)



 SELECT * FROM AD_User WHERE AD_User_ID = 1037318;


-----------------
SELECT 
  IO.DOCUMENTNO, 
  IO.CREATED, 
  IO.DESCRIPTION, 
  IO.C_ORDER_ID, 
  BP.NAME,
  SR.NAME AS SALES_REGION
FROM 
  M_InOut IO
  JOIN C_BPartner BP ON IO.C_BPartner_ID = BP.C_BPartner_ID
  JOIN C_BPartner_Location BPL ON IO.C_BPartner_ID = BPL.C_BPartner_ID
  JOIN C_SalesRegion SR ON BPL.C_SalesRegion_ID = SR.C_SalesRegion_ID
WHERE 
  TRUNC(IO.CREATED) = TRUNC(SYSDATE)
  AND IO.ISACTIVE = 'Y'
  AND IO.DOCACTION = 'CO'
  AND IO.DOCSTATUS = 'IP'
  AND IO.PROCESSED = 'N'
  AND IO.ISAPPROVED = 'N'
  AND IO.XX_CONTROLEUR_ID IS NULL
  AND IO.XX_PREPARATEUR_ID IS NULL
  AND IO.XX_CONTROLEUR_CH_ID IS NULL
  AND IO.XX_PREPARATEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_ID IS NULL;

-----------------------

SELECT 
  IO.DOCUMENTNO, 
  IO.CREATED, 
  IO.DESCRIPTION, 
  IO.C_ORDER_ID, 
  BP.NAME,
  SR.NAME AS SALES_REGION
FROM 
  M_InOut IO
  JOIN C_BPartner BP ON IO.C_BPartner_ID = BP.C_BPartner_ID
  JOIN (
    SELECT BPL.*
    FROM C_BPartner_Location BPL
    WHERE BPL.ISACTIVE = 'Y'
      AND BPL.C_BPartner_Location_ID = (
        SELECT MAX(BPL2.C_BPartner_Location_ID)
        FROM C_BPartner_Location BPL2
        WHERE BPL2.C_BPartner_ID = BPL.C_BPartner_ID
      )
  ) BPL ON IO.C_BPartner_ID = BPL.C_BPartner_ID
  JOIN C_SalesRegion SR ON BPL.C_SalesRegion_ID = SR.C_SalesRegion_ID
WHERE 
  TRUNC(IO.CREATED) = TRUNC(SYSDATE)
  AND IO.ISACTIVE = 'Y'
  AND IO.DOCACTION = 'CO'
  AND IO.DOCSTATUS = 'IP'
  AND IO.PROCESSED = 'N'
  AND IO.ISAPPROVED = 'N'
  AND IO.XX_CONTROLEUR_ID IS NULL
  AND IO.XX_PREPARATEUR_ID IS NULL
  AND IO.XX_CONTROLEUR_CH_ID IS NULL
  AND IO.XX_PREPARATEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_ID IS NULL;




SELECT * 
FROM M_InOut 
WHERE TRUNC(CREATED) = TRUNC(SYSDATE)
  AND ISACTIVE = 'Y'
  AND DOCACTION = 'CO'
  AND DOCSTATUS = 'IP'
  AND PROCESSED = 'N'
  AND ISAPPROVED = 'N'
  AND XX_CONTROLEUR_ID IS NULL
  AND XX_PREPARATEUR_ID IS NULL
  AND XX_CONTROLEUR_CH_ID IS NULL
  AND XX_PREPARATEUR_CH_ID IS NULL
  AND XX_EMBALEUR_CH_ID IS NULL
  AND XX_EMBALEUR_ID IS NULL;


SELECT C_SALESREGION_ID, NAME FROM C_SalesRegion ;







1002286


SELECT * FROM C_BPartner_Location WHERE C_BPartner_Location_ID=1108093;



SELECT 
  IO.DOCUMENTNO, 
  IO.CREATED, 
  IO.DESCRIPTION, 
  IO.M_InOut_ID, 
  BP.NAME,
  SR.NAME AS SALES_REGION
FROM 
  M_InOut IO
  JOIN C_BPartner BP ON IO.C_BPartner_ID = BP.C_BPartner_ID
  JOIN C_BPartner_Location BPL ON IO.C_BPartner_ID = BPL.C_BPartner_ID
  JOIN C_SalesRegion SR ON BPL.C_SalesRegion_ID = SR.C_SalesRegion_ID
WHERE 
  
 IO.ISACTIVE = 'Y'
  AND IO.DOCACTION = 'CO'
  AND IO.DOCSTATUS = 'IP'
  AND IO.PROCESSED = 'N'
  AND IO.ISAPPROVED = 'N'
  AND IO.XX_CONTROLEUR_ID IS NULL
  AND IO.XX_PREPARATEUR_ID IS NULL
  AND IO.XX_CONTROLEUR_CH_ID IS NULL
  AND IO.XX_PREPARATEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_CH_ID IS NULL
  AND IO.XX_EMBALEUR_ID IS NULL
  AND IO.C_DocType_ID='1002733';
---------------------------------------------
SELECT *
                FROM M_InOut 
                WHERE  ISACTIVE = 'Y'
                  AND DOCACTION = 'CO'
                  AND DOCSTATUS = 'IP'
                  AND PROCESSED = 'N'
                  AND ISAPPROVED = 'N'
                  AND XX_CONTROLEUR_ID IS NULL
                  AND XX_PREPARATEUR_ID IS NULL
                  AND XX_CONTROLEUR_CH_ID IS NULL
                  AND XX_PREPARATEUR_CH_ID IS NULL
                  AND XX_EMBALEUR_CH_ID IS NULL
                  AND XX_EMBALEUR_ID IS NULL
                  AND DOCUMENTNO IN('BEC26269/2025', 'BEC26265/2025','BEC26260/2025');


---------------------------------------


----------------

BEC26261/2025

DESCRIBE M_InOutline;

SELECT * FROM M_InOutline WHERE M_InOutLine_ID = 3216270;

SELECT NAME FROM m_product WHERE M_Product_ID = 1187190;

SELECT P.NAME , ML.MOVEMENTQTY FROM 
M_InOutline ML
JOIN 
M_Product P ON ML.M_Product_ID = P.M_Product_ID
WHERE 
ML.M_InOut_ID = 3191188;






SELECT s.sid, s.serial#, s.username
FROM v$session s
WHERE s.blocking_session IS NOT NULL;





SELECT C_SALESREGION_ID, NAME 
                FROM C_SalesRegion
                     where ISACTIVE= 'Y'
                     and AD_Client_ID = 1000000
                ORDER BY NAME
           ;



SELECT *
                FROM C_SalesRegion
                     where ISACTIVE= 'Y'
                     and C_SALESREGION_ID in(1001793, 1002181)
                ORDER BY NAME
           ;






    SELECT * FROM     C_Payment WHERE C_Payment_ID=1260402;    



 elif processed == 'Y' and docstatus == 'CO' and docaction.lower() == 'cl':
        state = "Achevé"
    elif processed == 'N' and docstatus == 'DR' and (docaction.upper() == 'PR' or docaction.upper() == 'CO'):
        state = "Brouillon"



    ------------------------------
   SELECT  processed , docstatus , docaction FROM  C_BankStatement WHERE C_BankStatement_ID=1019580;


------------------------ Pour Ouvrir Un Extrait de Caisse -----------------------------------------------------
-----------------------------------------------------------------
DEFINE BankStatement = 1019580;
UPDATE C_BankStatement 
SET  processed = 'N',docstatus = 'DR', docaction = 'CO'
WHERE C_BankStatement_id = &BankStatement;

UPDATE C_BankStatementLine  
SET processed = 'N'
WHERE C_BankStatement_id = &BankStatement;
COMMIT;
--------------------------------------------------------------
------------------------ Pour Achever Un Extrait de Caisse -----------------------------------------------------
-----------------------------------------------------------------
UPDATE C_BankStatement 
SET processed = 'Y',docstatus = 'CO', docaction = 'CL'
WHERE C_BankStatement_id = &BankStatement;
UPDATE C_BankStatementLine  
SET processed = 'Y'
WHERE C_BankStatement_id = &BankStatement;
COMMIT;

--------------------------------------------------------------
----------------------- UPDATE  Remise IN (Articles ----> Articles-Tiers) ------------------------
update C_BPartner_Product set m_discountschema_id = 1001229 -- id de remise 5% 1000718    7% 1000720   10%  1000007    13% 1000724   8%  1000719
where m_product_id in (select str.m_product_id from m_storage str
inner join m_product mp on (str.m_product_id = mp.m_product_id)
inner join m_attributeinstance att on (str.m_attributesetinstance_id = att.m_attributesetinstance_id)
where att.m_attribute_id = 1000508 and att.value like '%SPIC sifaoui (PARA)%'-- fournisseur
and mp.name like 'SPIC%' --produit
) and c_bp_group_id in(1001330)-- id de type client (client para,client potentiel) ;1000003 PARA 1001330 POT





-- Select products with empty m_discountschema_id, showing product name and fournisseur
SELECT 
    mp.name AS product_name,
    att.value AS fournisseur
FROM 
    C_BPartner_Product cbpp
    INNER JOIN m_product mp ON cbpp.m_product_id = mp.m_product_id
    INNER JOIN m_storage str ON str.m_product_id = mp.m_product_id
    INNER JOIN m_attributeinstance att ON str.m_attributesetinstance_id = att.m_attributesetinstance_id
WHERE 
    cbpp.m_discountschema_id IS NULL
    AND att.m_attribute_id = 1000508
    AND att.value LIKE '%LILIUM PHARMA ALGERIA%'
    AND mp.name LIKE 'LILIUM%';



    CREATE TABLE IF NOT EXISTS bnm.bank_data (id INT AUTO_INCREMENT PRIMARY KEY, date DATETIME, bna_sold DECIMAL(10,2), bna_remise DECIMAL(10,2), bna_check DECIMAL(10,2), baraka_sold DECIMAL(10,2), baraka_remise DECIMAL(10,2), baraka_check DECIMAL(10,2));



    ✅ 1. main_kpi – summary table

CREATE TABLE IF NOT EXISTS bnm.main_kpi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    time DATETIME NOT NULL,
    total_profit DECIMAL(12,2),
    total_stock DECIMAL(12,2),
    credit_client DECIMAL(12,2),
    total_tresorerie DECIMAL(12,2),
    total_dette DECIMAL(12,2)
);

✅ 2. stock – stock details table

CREATE TABLE IF NOT EXISTS bnm.stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME NOT NULL,
    principale DECIMAL(12,2),
    depot_reserver DECIMAL(12,2),
    hangar DECIMAL(12,2),
    hangar_reserve DECIMAL(12,2),
    total_stock DECIMAL(12,2) GENERATED ALWAYS AS (principale + depot_reserver + hangar + hangar_reserve) STORED
);

✅ 3. tresori – treasury breakdown table

CREATE TABLE IF NOT EXISTS bnm.tresori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME NOT NULL,
    caisse DECIMAL(12,2),
    paiement_net DECIMAL(12,2),
    total_bank DECIMAL(12,2),
    total_tresorerie DECIMAL(12,2) GENERATED ALWAYS AS (caisse + paiement_net + total_bank) STORED
);

✅ 4. bank_data – bank-level details (used in both tresori and dette)

CREATE TABLE IF NOT EXISTS bnm.bank_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME NOT NULL,
    bna_sold DECIMAL(12,2),
    bna_remise DECIMAL(12,2),
    bna_check DECIMAL(12,2),
    baraka_sold DECIMAL(12,2),
    baraka_remise DECIMAL(12,2),
    baraka_check DECIMAL(12,2),
    total_bank DECIMAL(12,2) GENERATED ALWAYS AS (bna_sold + bna_remise + baraka_sold + baraka_remise) STORED,
    total_checks DECIMAL(12,2) GENERATED ALWAYS AS (bna_check + baraka_check) STORED
);

✅ 5. dette – debt table

CREATE TABLE IF NOT EXISTS bnm.dette (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME NOT NULL,
    dette_fournisseur DECIMAL(12,2),
    totalchecks DECIMAL(12,2),
    total_dette DECIMAL(12,2) GENERATED ALWAYS AS (dette_fournisseur + totalchecks) STORED
);



select * from xx_vendor_status;



SELECT ml.value, ml.m_locator_id AS EMPLACEMENT
                FROM M_Locator ml
                JOIN M_Warehouse m ON m.M_WAREHOUSE_ID = ml.M_WAREHOUSE_ID
                WHERE m.ISACTIVE = 'Y'
                  AND m.AD_Client_ID = 1000000
                  AND ml.ISACTIVE = 'Y'
                  AND ml.AD_Client_ID = 1000000;





                  select * from M_MovementLine where M_MovementLine_ID=1102924

                  1001135

                  m_locator_id = 1001135
                  m_locatorto_id = 1000614



           SELECT *
FROM (
  SELECT *
  FROM M_MovementLine
  WHERE m_locator_id = 1000614
    AND m_locatorto_id = 1001135
)
WHERE ROWNUM <= 50;

select * from M_Movement where m_movement_id=1059403;


SELECT
                t.MovementDate AS MovementDate,
                nvl(nvl(io.documentno,inv.documentno),m.documentno) as documentno,
                nvl(bp.name, nvl(inv.description,m.description)) as name,
                p.name AS productname,
                CASE WHEN t.movementqty > 0 then t.movementqty else 0 end as ENTREE,
                CASE WHEN t.movementqty < 0 then ABS(t.movementqty) else 0 end as SORTIE,
                coalesce((SELECT SUM(s.movementqty)
                FROM m_transaction s
                inner join m_product p on (s.m_product_id = p.m_product_id)
                inner join m_locator l on (l.m_locator_id = s.m_locator_id)
                WHERE s.movementdate < t.movementdate
                AND (:product IS NULL OR p.name LIKE :product || '%')
                AND (:emplacement IS NULL OR
                     CASE 
                         WHEN :emplacement = '' THEN l.value IN ('Préparation', 'HANGAR')
                         ELSE l.value LIKE :emplacement || '%'
                     END)
                ), 0) AS StockInitial,
                asi.lot,
                l_from.value AS locator_from,
                l_to.value AS locator_to
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
            ON (inv.m_inventory_id = il.m_inventory_id)
            LEFT OUTER JOIN M_MovementLine ml
            ON (t.M_MovementLine_ID=ml.M_MovementLine_ID 
                AND NOT (ml.M_Locator_ID = 1001135 AND ml.M_LocatorTo_ID = 1000614)
                AND NOT (ml.M_Locator_ID = 1000614 AND ml.M_LocatorTo_ID = 1001135))
            LEFT OUTER JOIN M_Movement m
            ON (m.M_Movement_ID=ml.M_Movement_ID)
            LEFT OUTER JOIN M_InOutLine iol
            ON (t.M_InOutLine_ID=iol.M_InOutLine_ID)
            LEFT OUTER JOIN M_Inout io
            ON (iol.M_InOut_ID=io.M_InOut_ID)
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
            )
            WHERE (io.docstatus IN ('CO' , 'CL') 
            OR m.docstatus IN ('CO' , 'CL')
            OR inv.docstatus IN ('CO' , 'CL')) 
            AND att.m_attribute_id = 1000508
            AND (:end_date IS NULL OR t.movementdate <= TO_DATE(:end_date, 'YYYY-MM-DD'))
            AND (:start_date IS NULL OR t.movementdate >= TO_DATE(:start_date, 'YYYY-MM-DD'))
            AND (:product IS NULL OR P.NAME LIKE :product || '%')
            AND (:fournisseur IS NULL OR att.value like :fournisseur || '%')
            AND (:emplacement IS NULL OR 
                 CASE 
                     WHEN :emplacement = '' THEN l.value IN ('Préparation', 'HANGAR')
                     ELSE l.value LIKE :emplacement || '%'
                 END)
            AND t.AD_Client_ID = 1000000
            ORDER BY t.MovementDate DESC;

            ------------------------------

            select * from M_PRODUCT
WHERE AD_Client_ID = 1000000
AND AD_Org_ID = 1000000
  AND ISACTIVE = 'Y'
  and name like 'BIOMAX APPETIT MAX SIROP 150ML%'
ORDER BY name;

select * from XX_LABORATORY
where XX_LABORATORY_ID=1003313;

SELECT *
                FROM C_BPartner cb
                WHERE cb.AD_Client_ID = 1000000
                  AND cb.ISVENDOR = 'Y'
                  AND cb.ISACTIVE = 'Y'
                  and cb.name like 'BIOMAX PHARM (PARA)%'
                ORDER BY cb.name;



SELECT DISTINCT
                    CAST(cb.name AS VARCHAR2(300)) AS FOURNISSEUR
                FROM 
                    M_InOut xf
                JOIN M_INOUTLINE mi ON mi.M_INOUT_ID = xf.M_INOUT_ID
                JOIN C_BPartner cb ON cb.C_BPARTNER_ID = xf.C_BPARTNER_ID
                JOIN M_PRODUCT m ON m.M_PRODUCT_id = mi.M_PRODUCT_id
                WHERE 
                    UPPER(m.name) LIKE UPPER(:product) || '%'
                    AND xf.AD_Org_ID = 1000000
                    AND xf.C_DocType_ID IN (1000013, 1000646)
                    AND xf.M_Warehouse_ID IN (1000724, 1000000, 1000720, 1000725)
                ORDER BY 
                 FOURNISSEUR;





                 select processed, docstatus, docaction from M_InOut  where M_InOut_ID=3088414;

                 UPDATE M_InOut
SET processed = 'N',
    docstatus = 'DR',
    docaction = 'PR'
WHERE M_InOut_ID = 3196674;




                     elif processed == 'N' and docstatus == 'DR' and (docaction.upper() == 'PR' or docaction.upper() == 'CO'):
        state = "Brouillon"


    

    

    SELECT ispaid, ad_org_id, c_bpartner_id, dateinvoiced, nbl,nfact, documentno, grandtotal, verse_fact, 
verse_cheque, cheque, name, dateversement, nb, region,  
SUM (grandtotal/nb) over( PARTITION BY c_bpartner_id) AS bp_chiffre,
SUM (verse_fact/nb) over( PARTITION BY c_bpartner_id ) AS verse_tot,
orgname, phone, phone2, fax,
address1, address2, address3, address4, city, postal
FROM 
(SELECT cs.ispaid, cs.ad_org_id , cs.c_bpartner_id,  cs.dateinvoiced, cs.nbl,cs.nfact, cs.documentno AS documentno, 
 cs.grandtotal, cs.verse_fact, 
 cs.verse_cheque, cs.cheque, cs.name, cs.dateversement, cs.region,  
 COUNT(cs.c_invoice_id) over (PARTITION BY cs.c_invoice_id) AS nb 
, org.name as orgname, oi.phone, oi.phone2, oi.fax,
loc.address1, loc.address2, loc.address3, loc.address4, loc.city, loc.postal
 FROM xx_vendor_status cs  
inner join ad_org org on (cs.ad_org_id=org.ad_org_id)
inner join ad_orginfo oi on (org.ad_org_id=oi.ad_org_id)
inner join c_location loc on (oi.c_location_id=loc.c_location_id)
 WHERE cs.AD_Client_ID=$P{AD_Client_ID}
and cs.AD_Org_ID=$P{AD_Org_ID}
and cs.C_BPartner_ID=$P{C_BPartner_ID}
and cs.dateinvoiced between $P{Date1} and $P{Date2}
and ($P{IsPaid} is null or $P{IsPaid}='' or $P{IsPaid}=cs.ispaid)
and ($P{C_DocType_ID} is null or $P{C_DocType_ID}=0 or cs.C_DocType_ID=$P{C_DocType_ID})
and (cs.ad_orgtrx_id=$P{AD_OrgTrx_ID} or $P{AD_OrgTrx_ID} is null or $P{AD_OrgTrx_ID}=0)
order by cs.dateinvoiced, cs.nbl, cs.nfact)
order by dateinvoiced, nbl, nfact;



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
 AND cs.C_BPartner_ID = :C_BPartner_ID
 AND cs.dateinvoiced BETWEEN :Date1 AND :Date2
 AND (:IsPaid IS NULL OR :IsPaid = '' OR :IsPaid = cs.ispaid)
 AND (:C_DocType_ID IS NULL OR :C_DocType_ID = 0 OR cs.C_DocType_ID = :C_DocType_ID)
 AND (cs.ad_orgtrx_id IS NULL OR cs.ad_orgtrx_id = 0)
 ORDER BY cs.dateinvoiced, cs.nbl, cs.nfact)
ORDER BY dateinvoiced, nbl, nfact;


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
 AND cs.C_BPartner_ID = 1123624
 AND cs.dateinvoiced BETWEEN TO_DATE('01/01/2025', 'DD/MM/YYYY') AND TO_DATE('01/06/2025', 'DD/MM/YYYY')
 
 ORDER BY cs.dateinvoiced, cs.nbl, cs.nfact)
ORDER BY dateinvoiced, nbl, nfact;


SELECT inv.DATEINVOICED AS DateTrx,
                  inv.DOCUMENTNO,
                  inv.POREFERENCE as POREFERENCE,
                  doc.PrintName as name,
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
                  END AS GRANDTOTAL
                FROM C_INVOICE inv,
                  C_DOCTYPE doc
                WHERE inv.C_DOCTYPE_ID =doc.C_DOCTYPE_ID
                AND inv.DOCSTATUS     IN ('CO','CL')
                AND doc.docbasetype   IN ('API','APC')
                AND inv.AD_Client_ID = 1000000
                AND inv.AD_Org_ID = 1000000
                AND inv.C_BPARTNER_ID  = :fournisseur_id
                AND (inv.DATEINVOICED >= TO_DATE(:date1, 'YYYY-MM-DD')
                AND inv.DATEINVOICED  <= TO_DATE(:date2, 'YYYY-MM-DD'))
                UNION
                SELECT pa.DATETRX AS DateTrx,
                  pa.DOCUMENTNO,
                  NULL as POREFERENCE,
                  'Discount' AS NAME,
                  '' AS DESCRIPTION,
                  pa.discountamt * -1 AS GRANDTOTAL
                FROM C_PAYMENT pa,
                  C_DOCTYPE doc
                WHERE pa.C_DOCTYPE_ID=doc.C_DOCTYPE_ID
                AND pa.DOCSTATUS    IN ('CO','CL')
                AND doc.docbasetype IN ('APP')
                AND pa.AD_Client_ID = 1000000
                AND pa.AD_Org_ID = 1000000
                AND pa.C_BPARTNER_ID = :fournisseur_id
                AND (pa.DATETRX     >= TO_DATE(:date1, 'YYYY-MM-DD')
                AND pa.DATETRX      <= TO_DATE(:date2, 'YYYY-MM-DD'))
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
                    ELSE ''
                  END AS DESCRIPTION,
                  (pa.PAYAMT * -1) AS GRANDTOTAL
                FROM C_PAYMENT pa,
                  C_DOCTYPE doc
                WHERE pa.C_DOCTYPE_ID=doc.C_DOCTYPE_ID
                AND pa.DOCSTATUS    IN ('CO','CL')
                AND doc.docbasetype IN ('APP')
                AND pa.AD_Client_ID = 1000000
                AND pa.AD_Org_ID = 1000000
                AND pa.C_BPARTNER_ID = :fournisseur_id
                AND (pa.DATETRX     >= TO_DATE(:date1, 'YYYY-MM-DD')
                AND pa.DATETRX      <= TO_DATE(:date2, 'YYYY-MM-DD'))
                UNION
                SELECT DATETRX as DateTrx,
                DOCUMENTNO as DOCUMENTNO,
                NULL as POREFERENCE,
                 doc.PrintName as name,
                 'DIFFERENCE' as DESCRIPTION,
                 COALESCE((select sum(al.writeoffamt* -1) from C_ALLOCATIONLINE al where (al.C_PAYMENT_ID = par.C_PAYMENT_ID and par.C_BPARTNER_ID = :fournisseur_id)), 0) AS GRANDTOTAL
                FROM C_PAYMENT par, c_doctype doc
                WHERE doc.docbasetype IN ('APP')
                AND par.DOCSTATUS IN ('CO','CL')
                AND par.AD_Client_ID = 1000000
                AND par.AD_Org_ID = 1000000
                AND par.C_BPARTNER_ID = :fournisseur_id
                AND par.C_DOCTYPE_ID=doc.C_DOCTYPE_ID
                AND (par.DATETRX >= TO_DATE(:date1, 'YYYY-MM-DD')
                AND par.DATETRX <= TO_DATE(:date2, 'YYYY-MM-DD'))
                AND COALESCE((select sum(al.writeoffamt* -1) from C_ALLOCATIONLINE al where (al.C_PAYMENT_ID = par.C_PAYMENT_ID and par.C_BPARTNER_ID = :fournisseur_id)), 0) <> 0
                UNION
                SELECT c.StatementDATE AS DateTrx,
                  c.Name As DOCUMENTNO,
                  i.POREFERENCE as POREFERENCE,
                  'Facture sur Caisse' as NAME,
                 CASE
                    WHEN cl.DESCRIPTION IS NOT NULL
                    THEN cl.DESCRIPTION
                    WHEN cl.C_INVOICE_ID IS NOT NULL
                    THEN
                      i.DOCUMENTNO
                    ELSE ''
                  END AS DESCRIPTION,
                  cl.Amount * -1 AS GRANDTOTAL
                FROM C_CashLine cl
                INNER JOIN C_Cash c ON (cl.C_Cash_ID=c.C_Cash_ID)
                INNER JOIN C_Invoice i ON (cl.C_Invoice_ID=i.C_Invoice_ID)
                WHERE 
                 c.DOCSTATUS IN ('CO','CL')
                 AND i.ispaid='Y'
                AND cl.isactive='Y'
                AND c.AD_Client_ID = 1000000
                AND c.AD_Org_ID = 1000000
                AND i.C_BPARTNER_ID = :fournisseur_id
                AND (c.StatementDATE >= TO_DATE(:date1, 'YYYY-MM-DD')
                AND c.StatementDATE <= TO_DATE(:date2, 'YYYY-MM-DD'))
                ORDER BY DateTrx;






select name from M_PRODUCT
WHERE AD_Client_ID = 1000000
AND AD_Org_ID = 1000000
  AND ISACTIVE = 'Y'
  and CREATED >= TO_DATE('01/07/2025', 'DD/MM/YYYY')
ORDER BY name
LIMIT 100;









SELECT cb.name, cb.C_BPartner_ID, cb.description
                FROM C_BPartner cb
                WHERE cb.AD_Client_ID = 1000000
                and ad_org_id = 1000000
                  AND cb.iscustomer = 'Y'
                  AND cb.ISACTIVE = 'Y'
                  AND (XX_RC IS NULL
                  OR XX_NIF IS NULL
                  OR XX_AI IS NULL )
                ORDER BY cb.name;





SELECT 
    cb.name, 
    cb.C_BPartner_ID, 
    cb.description,
    CASE 
        WHEN cb.XX_RC IS NULL THEN TO_NCHAR('NO RC') 
        ELSE TO_NCHAR(cb.XX_RC) 
    END AS RC_Status,
    CASE 
        WHEN cb.XX_NIF IS NULL THEN TO_NCHAR('NO NIF') 
        ELSE TO_NCHAR(cb.XX_NIF) 
    END AS NIF_Status,
    CASE 
        WHEN cb.XX_AI IS NULL THEN TO_NCHAR('NO AI') 
        ELSE TO_NCHAR(cb.XX_AI) 
    END AS AI_Status
FROM C_BPartner cb
WHERE cb.AD_Client_ID = 1000000
  AND cb.AD_Org_ID = 1000000
  AND cb.IsCustomer = 'Y'
  AND cb.IsActive = 'Y'
  AND (
      cb.XX_RC IS NULL
      OR cb.XX_NIF IS NULL
      OR cb.XX_AI IS NULL
  )
ORDER BY cb.name;

SELECT DISTINCT
                    cb.C_BPartner_ID AS "CLIENT_ID",
                    cb.name AS "CLIENT_NAME",
                    sr.name AS "ZONE"
                FROM C_SalesRegion sr
                JOIN C_BPartner_Location bpl ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                JOIN C_BPartner cb ON cb.C_BPartner_ID = bpl.C_BPartner_ID
                JOIN xx_ca_fournisseur xf ON bpl.C_BPartner_ID = xf.CLIENTID
                WHERE UPPER(sr.name) = UPPER(:zone)
                    AND xf.AD_Org_ID = 1000000
                    AND xf.DOCSTATUS != 'RE'
                ORDER BY cb.name;

SELECT DISTINCT
    cb.C_BPartner_ID AS "CLIENT_ID",
    cb.name AS "CLIENT_NAME",
    sr.name AS "ZONE"
FROM C_SalesRegion sr
JOIN C_BPartner_Location bpl ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
JOIN C_BPartner cb ON cb.C_BPartner_ID = bpl.C_BPartner_ID
JOIN xx_ca_fournisseur xf ON bpl.C_BPartner_ID = xf.CLIENTID
WHERE UPPER(sr.name) IN (
    'CNE 1 (SMK/DJBEL WAHECHE/BOUSSOUF)',
    'TIZI OUZOU',
    'BATNA',
    'BEJAIA',
    'EL OUED',
    'SKIKDA',
    'SETIF',
    'EL BORDJ /MSILA',
    'TEBESSA / KHENCHELA',
    'JIJEL/ MILA',
    'GUELMA',
    'ANNABA',
    '<AUCUNE>',
    'OUARGLA',
    'CHELGHOUM',
    'CNE 3 (AIN SMARA/ZOUAGHI)',
    'CNE 2 (NOUVELLE/KHROUB)',
    'EL KALA',
    'BISKRA'
)
AND xf.AD_Org_ID = 1000000
AND xf.DOCSTATUS != 'RE'
ORDER BY sr.name;





SELECT

                t.MovementDate AS MovementDate,
                nvl(nvl(io.documentno,inv.documentno),m.documentno) as documentno,
                nvl(bp.name, nvl(inv.description,m.description)) as name,
                p.name AS productname,
                CASE WHEN t.movementqty > 0 then t.movementqty else 0 end as ENTREE,
                CASE WHEN t.movementqty < 0 then ABS(t.movementqty) else 0 end as SORTIE,
                asi.lot,
                l.value AS locator,
                COALESCE(io.docstatus, m.docstatus, inv.docstatus, 'N/A') AS docstatus
            FROM M_Transaction t
            INNER JOIN M_Locator l ON (t.M_Locator_ID=l.M_Locator_ID)
            INNER JOIN M_Product p ON (t.M_Product_ID=p.M_Product_ID)
            LEFT OUTER JOIN M_InventoryLine il ON (t.M_InventoryLine_ID=il.M_InventoryLine_ID)
            LEFT OUTER JOIN M_Inventory inv ON (inv.m_inventory_id = il.m_inventory_id)
            LEFT OUTER JOIN M_MovementLine ml ON (t.M_MovementLine_ID=ml.M_MovementLine_ID)
            LEFT OUTER JOIN M_Movement m ON (m.M_Movement_ID=ml.M_Movement_ID)
            LEFT OUTER JOIN M_InOutLine iol ON (t.M_InOutLine_ID=iol.M_InOutLine_ID)
            LEFT OUTER JOIN M_Inout io ON (iol.M_InOut_ID=io.M_InOut_ID)
            LEFT OUTER JOIN C_BPartner bp ON (bp.C_BPartner_ID = io.C_BPartner_ID)
            INNER JOIN M_attributesetinstance asi on t.m_attributesetinstance_id = asi.m_attributesetinstance_id
            INNER JOIN M_attributeinstance att on (att.m_attributesetinstance_id = asi.m_attributesetinstance_id)
            WHERE
            att.m_attribute_id = 1000508
            AND COALESCE(io.docstatus, m.docstatus, inv.docstatus) IN ('VO')
            AND NOT (t.movementqty = 0)
            AND t.AD_Client_ID = 1000000
            AND (inv.description IS NULL and m.description IS NULL and io.description IS NULL)
            ORDER BY t.MovementDate DESC;



select  description , docstatus from M_InOut
WHERE M_InOut_ID in(3114317, 3114338);


update M_InOut
SET docstatus = 'VO'
WHERE M_InOut_ID =3021394;









SELECT * FROM (
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
                        1 AS sort_order
                    FROM 
                        c_order co
                    INNER JOIN ad_org org ON co.ad_org_id = org.ad_org_id
                    INNER JOIN c_bpartner cb ON co.c_bpartner_id = cb.c_bpartner_id
                    INNER JOIN ad_user us ON co.salesrep_id = us.ad_user_id
                    WHERE 
                         co.docaction  ='PR'
                        AND co.ad_org_id = 1000000
                        and docstatus = 'IP'
                        and issotrx = 'Y'


                    
                    UNION ALL
                    
                    SELECT 
                        CAST('Total' AS VARCHAR2(300)) AS organisation,
                        CAST(NULL AS VARCHAR2(50)) AS ndocument,
                        CAST(NULL AS VARCHAR2(300)) AS tier,
                        NULL AS datecommande,
                        CAST(NULL AS VARCHAR2(100)) AS vendeur,
                        ROUND(AVG(ROUND(((co.totallines / (SELECT SUM(mat.valuenumber * li.qtyentered) 
                             FROM c_orderline li 
                             INNER JOIN m_attributeinstance mat ON mat.m_attributesetinstance_id = li.m_attributesetinstance_id
                             WHERE mat.m_attribute_id = 1000504 
                               AND li.c_order_id = co.c_order_id 
                               AND li.qtyentered > 0 
                             GROUP BY li.c_order_id)) - 1) * 100, 2)), 2) AS marge,
                        ROUND(SUM(co.totallines), 2) AS montant,
                        0 AS sort_order
                    FROM 
                        c_order co
                    INNER JOIN ad_org org ON co.ad_org_id = org.ad_org_id
                    INNER JOIN c_bpartner cb ON co.c_bpartner_id = cb.c_bpartner_id
                    INNER JOIN ad_user us ON co.salesrep_id = us.ad_user_id
                    WHERE 
                         co.docaction  ='PR'
                        AND co.ad_org_id = 1000000
                        and docstatus = 'IP'
                        and issotrx = 'Y'
                        
                )
                ORDER BY sort_order, montant DESC;




                select * from c_order where c_order_id in (3258167,3222958);






                SELECT ml.value AS EMPLACEMENT, ml.m_locator_id
                FROM M_Locator ml
                JOIN M_Warehouse m ON m.M_WAREHOUSE_ID = ml.M_WAREHOUSE_ID
                WHERE m.ISACTIVE = 'Y'
                  AND m.AD_Client_ID = 1000000
                  AND ml.ISACTIVE = 'Y'
                  AND ml.AD_Client_ID = 1000000;



                  SELECT DISTINCT m.value AS MAGASIN, ml.m_locator_id
                FROM M_Locator ml
                JOIN M_Warehouse m ON m.M_WAREHOUSE_ID = ml.M_WAREHOUSE_ID
                WHERE m.ISACTIVE = 'Y'
                  AND m.AD_Client_ID = 1000000
                  AND ml.ISACTIVE = 'Y'
                  AND ml.AD_Client_ID = 1000000;
----------------------------------------------------------------
--- ORM--------------
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
                    ROUND(co.totallines, 2) AS montant
                FROM 
                    c_order co
                INNER JOIN ad_org org ON co.ad_org_id = org.ad_org_id
                INNER JOIN c_bpartner cb ON co.c_bpartner_id = cb.c_bpartner_id
                INNER JOIN ad_user us ON co.salesrep_id = us.ad_user_id
                WHERE 
    co.ad_org_id = 1000000
    AND issotrx = 'Y'
    AND C_DOCTYPETARGET_ID=1001408;

    ---------------------------------
   select * from C_DocType where C_DocType_ID = 1001408;
   ------------
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
    ROUND(co.totallines, 2) AS montant
FROM 
    c_order co
INNER JOIN ad_org org ON co.ad_org_id = org.ad_org_id
INNER JOIN c_bpartner cb ON co.c_bpartner_id = cb.c_bpartner_id
INNER JOIN ad_user us ON co.salesrep_id = us.ad_user_id
WHERE 
    co.ad_org_id = 1000000
    AND issotrx = 'Y'
    AND C_DOCTYPETARGET_ID = 1001408
    AND co.dateordered >= TO_DATE(:start_date, 'YYYY-MM-DD')
    AND co.dateordered <= TO_DATE(:end_date, 'YYYY-MM-DD')


;


select docstatus from C_Order where C_Order_ID in (3259877,3259876);






SELECT 
    i.documentno, 
    i.totallines, 
    i.description, 
    i.dateinvoiced, 
    i.c_bpartner_id, 
    i.C_Invoice_ID,
FROM C_Invoice i
JOIN C_BPartner cb ON i.c_bpartner_id = cb.c_bpartner_id
WHERE cb.name = :partner_name
  AND i.dateinvoiced BETWEEN :start_date AND :end_date;




SELECT 
    qtyentered, 
    m_product_id, 
    linenetamt
FROM C_InvoiceLine
WHERE c_invoice_id = :invoice_id;




select CLIENTID from xx_ca_fournisseur where  MOVEMENTDATE BETWEEN :start_date AND :end_date FETCH FIRST 1 ROWS ONLY;


SELECT name, c_bp_group_id,C_BPARTNER_ID
                FROM C_BPartner
                WHERE iscustomer = 'Y'
                  AND AD_Client_ID = 1000000
                  AND AD_Org_ID = 1000000
                ORDER BY name;

                --1000003 para     1001330 pot
SELECT 

    cb.name, 
    cb.c_bp_group_id,
    cb.C_BPARTNER_ID,
    CASE 
        WHEN cb.c_bp_group_id = 1000003 THEN 'para'
        WHEN cb.c_bp_group_id = 1001330 THEN 'potentiel'
        ELSE 'autre'
    END AS group_label
FROM C_BPartner cb
WHERE cb.ISCUSTOMER = 'Y'
  AND cb.AD_Client_ID = 1000000
  AND cb.AD_Org_ID = 1000000
  AND cb.C_BPARTNER_ID = (
        SELECT CLIENTID 
        FROM xx_ca_fournisseur 
        WHERE MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD')
        FETCH FIRST 1 ROWS ONLY
  )
;

SELECT 
    cb.name, 
    cb.c_bp_group_id,
    cb.C_BPARTNER_ID,
    CASE 
        WHEN cb.c_bp_group_id = 1000003 THEN 'para'
        WHEN cb.c_bp_group_id = 1001330 THEN 'potentiel'
        ELSE 'autre'
    END AS group_label
FROM C_BPartner cb
WHERE cb.ISCUSTOMER = 'Y'
  AND cb.AD_Client_ID = 1000000
  AND cb.AD_Org_ID = 1000000
  AND cb.C_BPARTNER_ID IN (
      SELECT CLIENTID 
      FROM xx_ca_fournisseur 
      WHERE MOVEMENTDATE BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD')
  AND (
      (:group_label IS NULL) 
      OR 
      (CASE 
          WHEN cb.c_bp_group_id = 1000003 THEN 'para'
          WHEN cb.c_bp_group_id = 1001330 THEN 'potentiel'
          ELSE 'autre'
      END = :group_label)
  )
  );
------------------------------------------


                DEFINE BankStatement = 1025270;
UPDATE C_BankStatement 
SET  processed = 'N',docstatus = 'DR', docaction = 'CO'
WHERE C_BankStatement_id = &BankStatement;
UPDATE C_BankStatementLine  
SET processed = 'N'
WHERE C_BankStatement_id = &BankStatement;
COMMIT;
--------------------------------------------------------------
------------------------ Pour Achever Un Extrait de Caisse -----------------------------------------------------
-----------------------------------------------------------------
UPDATE C_BankStatement 
SET processed = 'Y',docstatus = 'CO', docaction = 'CL'
WHERE C_BankStatement_id = &BankStatement;
UPDATE C_BankStatementLine  
SET processed = 'Y'
WHERE C_BankStatement_id = &BankStatement;
COMMIT;





SELECT DISTINCT cb.name as client, ad.name as operator
            FROM c_bpartner cb
            INNER JOIN ad_user ad ON (cb.salesrep_id = ad.ad_user_id)
            WHERE ad.c_bpartner_id IN (1121780,1122143,1118392,1122144,1119089,1111429,1122761,1122868,1122142,1143361)
            AND cb.isactive = 'Y'
            AND cb.ad_org_id = 1000000
            ORDER BY ad.name, cb.name ;


            SELECT 
    ad.name as operator,
    COUNT(DISTINCT cb.c_bpartner_id) as client_count
FROM c_bpartner cb
INNER JOIN ad_user ad ON (cb.salesrep_id = ad.ad_user_id)
WHERE ad.c_bpartner_id IN (1121780,1122143,1118392,1122144,1119089,1111429,1122761,1122868,1122142,1143361)
AND cb.isactive = 'Y'
AND cb.ad_org_id = 1000000
GROUP BY ad.name
ORDER BY ad.name;



select * from c_bpartner;



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
      "source"."LOCATION" "LOCATION"
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
          END AS location
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
                      ) ppa
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
                      AND mst.m_locator_id IN (1001135, 1000614, 1001128, 1001136, 1001020)
                      AND mst.qtyonhand != 0
                      AND p.m_product_id = :product_id
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
          m_locator_id
        ORDER BY
          fournisseur
      ) "source"
  )
WHERE
  rownum <= 1048575



;


SELECT
    "source"."M_PRODUCT_ID" "M_PRODUCT_ID",
    "source"."QTY_DISPO" "QTY_DISPO",
    "source"."LOCATION" "LOCATION"
FROM
    (
        SELECT
            DISTINCT 
            p.m_product_id AS "M_PRODUCT_ID",
            (mst.qtyonhand - mst.QTYRESERVED) AS "QTY_DISPO",
            CASE 
                WHEN mst.m_locator_id = 1000614 THEN 'Préparation'
                WHEN mst.m_locator_id = 1001135 THEN 'HANGAR'
                WHEN mst.m_locator_id = 1001128 THEN 'Dépot_réserve'
                WHEN mst.m_locator_id = 1001136 THEN 'HANGAR_'
                WHEN mst.m_locator_id = 1001020 THEN 'Depot_Vente'
            END AS "LOCATION"
        FROM
            m_product p
            INNER JOIN m_storage mst ON p.m_product_id = mst.m_product_id
            INNER JOIN m_attributeinstance mati ON mst.m_attributesetinstance_id = mati.m_attributesetinstance_id
        WHERE
            mati.m_attribute_id = 1000508
            AND mst.m_locator_id IN (1001135, 1000614, 1001128, 1001136, 1001020)
            AND mst.qtyonhand != 0
            AND p.m_product_id = :product_id
        ORDER BY
            p.m_product_id
    ) "source"
WHERE
    rownum <= 1048575;



------------------------------------------change qty dispo----------------------------
    UPDATE m_storage
SET  QTYRESERVED = 0  -- This makes QTY_DISPO = qtyonhand - QTYRESERVED = 0
WHERE m_product_id = 1159659
and QTYRESERVED = 15;
-------------------------------------------------------------------------
 
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
            "source"."QTY" "QTY",
            "source"."QTY_DISPO" "QTY_DISPO",

            "source"."GUARANTEEDATE" "GUARANTEEDATE",  -- Added the GUARANTEEDATE column
            "source"."PPA" "PPA",  -- Added the PPA column
            "source"."LOCATION" "LOCATION"
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
                    qty,
                    qty_dispo,

                    guaranteedate,  -- Added the GUARANTEEDATE column
                    ppa,  -- Added the PPA column
                    CASE 
                        WHEN m_locator_id = 1000614 THEN 'Préparation'
                        WHEN m_locator_id = 1001135 THEN 'HANGAR'
                        WHEN m_locator_id = 1001128 THEN 'Dépot_réserve'
                        WHEN m_locator_id = 1001136 THEN 'HANGAR_'
                        WHEN m_locator_id = 1001020 THEN 'Depot_Vente'
                    END AS location
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
                                                    valuenumber
                                                FROM
                                                    m_attributeinstance
                                                WHERE
                                                    m_attributesetinstance_id = mst.m_attributesetinstance_id
                                                    AND m_attribute_id = 1000503
                                            ) ppa
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
                                            AND mst.m_locator_id IN (1001135, 1000614, 1001128, 1001136, 1001020)
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
                    qty,
                    qty_dispo,
                    guaranteedate,  -- Added to GROUP BY
                    ppa,  -- Added PPA to GROUP BY
                    m_locator_id
                ORDER BY
                    fournisseur
            ) "source"
    )
WHERE
    rownum <= 1048575



    ;


M_Attribute - M_Attribute_ID=1000808 ---- bonus achat

    select * from m_attributesetinstance where m_product_id = 1190579;




// ...existing code...
-- Fixed query: get bonus achat (1000808) and bonus vente (1000908) as columns
SELECT
    pr.M_Product_ID,
    pr.Name,
    MAX(CASE WHEN ai.M_Attribute_ID = 1000808 THEN ai.ValueNumber END) AS ug_achat,
    MAX(CASE WHEN ai.M_Attribute_ID = 1000908 THEN ai.ValueNumber END) AS ug_vente
FROM M_Product pr
JOIN M_Storage st
  ON st.M_Product_ID = pr.M_Product_ID
JOIN M_AttributeSetInstance mat
  ON mat.M_AttributeSetInstance_ID = st.M_AttributeSetInstance_ID
JOIN M_AttributeInstance ai
  ON ai.M_AttributeSetInstance_ID = mat.M_AttributeSetInstance_ID
WHERE pr.AD_Client_ID = 1000000
  AND pr.AD_Org_ID = 1000000
  AND pr.IsActive = 'Y'
  AND pr.M_Product_ID = 1193472
  AND ai.M_Attribute_ID IN (1000808, 1000908)
GROUP BY pr.M_Product_ID, pr.Name;





select pr.xx_ugbylot, sc.name from m_product pr 
join XX_SalesContext sc on sc.XX_SalesContext_ID = pr.XX_SalesContext_ID
where pr.m_product_id = 1193472;




--------------------------------------------------- NO AI NIF NIS---------------------------------
SELECT 
  name,
  CASE 
    WHEN xx_rc IS NULL THEN 'manque rc ' 
    ELSE TO_CHAR(xx_rc) 
  END AS xx_rc,
  CASE 
    WHEN xx_ai IS NULL THEN 'manque ai ' 
    ELSE TO_CHAR(xx_ai) 
  END AS xx_ai,
  CASE 
    WHEN xx_nif IS NULL THEN 'manque nif ' 
    ELSE TO_CHAR(xx_nif) 
  END AS xx_nif
FROM C_BPartner cb
WHERE isactive = 'Y'
  AND AD_Org_ID = 1000000
  AND AD_Client_ID = 1000000
  AND iscustomer = 'Y'
  AND (xx_rc IS NULL OR xx_ai IS NULL OR xx_nif IS NULL );
------------------------------------------------------------------------------


SELECT *
 FROM M_AttributeSetInstance WHERE M_AttributeSetInstance_ID = 1556024;


SELECT DOCACTION, DOCSTATUS FROM M_InOut WHERE M_InOut_ID=3154985;

----------------------------------- status of document---------------------------------------------
SELECT XX_PREPARATEUR_ID FROM M_InOut WHERE M_InOut_ID=3168410;


select DOCACTION, DOCSTATUS FROM C_Invoice WHERE C_Invoice_ID=1715497;


UPDATE  M_InOut  SET  XX_PREPARATEUR_ID = 1129847   WHERE M_InOut_ID=3209913;



UPDATE  C_Invoice  SET  DOCACTION = 'CL' , DOCSTATUS = 'CO'   WHERE C_Invoice_ID=1715497;



UPDATE  M_InOut  SET  DOCACTION = 'CO' , DOCSTATUS = 'IP'   WHERE M_InOut_ID=3209913;



---
select percentage, name from c_bpartner 
WHERE c_bpartner_id IN (1121780, 1122761, 1122868, 1122144, 1111429, 1122142, 1118392, 1119089, 1122143);


select name from AD_User  
WHERE AD_User_ID = 1037443;

select NAME from C_BPartner WHERE  ISACTIVE='Y' AND iscustomer='Y' AND TOTALOPENBALANCE<0;
----------------------------
SELECT
cc.documentno AS invoice_no,
pa.documentno AS reception_no,
cc.totallines,
par.name AS partner_name,
SUM(a.MOVEMENTQTY * att1.VALUENUMBER) AS calculated_total,
cc.totallines - SUM(a.MOVEMENTQTY * att1.VALUENUMBER) AS difference,
CASE
WHEN cc.totallines = 0 THEN NULL
ELSE ROUND((cc.totallines - SUM(a.MOVEMENTQTY * att1.VALUENUMBER)) / cc.totallines * 100, 2)
END AS difference_percent,
cc.C_Invoice_ID
FROM M_InOutLine a , C_Invoice cc
INNER JOIN M_ATTRIBUTEINSTANCE att1
ON a.M_ATTRIBUTESETINSTANCE_ID = att1.M_ATTRIBUTESETINSTANCE_ID
INNER JOIN M_InOut pa
ON pa.M_InOut_ID = a.M_InOut_ID

INNER JOIN C_BPartner par
ON par.C_BPartner_ID = cc.C_BPartner_ID
inner join M_MatchInv inv on inv.M_InOutLine_ID=a.M_InOutLine_ID
WHERE att1.M_Attribute_ID = 1000504
AND pa.C_DocType_ID = 1000013
AND pa.M_InOut_ID IN (
SELECT iol.M_InOut_ID
FROM C_InvoiceLine il
INNER JOIN M_InOutLine iol ON iol.M_InOutLine_ID = il.M_InOutLine_ID
WHERE il.C_Invoice_ID = cc.C_Invoice_ID
)
AND pa.CREATED BETWEEN TO_DATE(:date_debut, 'YYYY-MM-DD')
AND TO_DATE(:date_fin, 'YYYY-MM-DD') + 1 - (1/86400)
GROUP BY cc.documentno, pa.documentno, cc.totallines, par.name, cc.C_Invoice_ID
ORDER BY par.name, ABS(cc.totallines - SUM(a.MOVEMENTQTY * att1.VALUENUMBER)) DESC
;


select * from C_InvoiceLine where C_InvoiceLine_ID=5857160;


select  * from  M_MatchInv where M_MatchInv_ID=1256777;



select * from M_InOut where M_InOut_ID=3213187;

select * from M_InOutLine where M_InOutLine_ID=5752610;




SELECT IL.qtyentered as Quantite  ,IL.qtyinvoiced as Qty_facture ,p.name
FROM C_Invoice inv
JOIN C_InvoiceLine il ON inv.C_Invoice_ID = il.C_Invoice_ID
JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
WHERE inv.C_Invoice_ID=1714792;





SELECT 
    IL.qtyentered AS Quantite,
    IL.qtyinvoiced AS Qty_facture,
    p.name,
    mi.qty AS qty_RECEPTION,
    mi.C_InvoiceLine_ID AS match_invoice_id,
    mi.documentno AS match_documentno
FROM C_Invoice inv
JOIN C_InvoiceLine il ON inv.C_Invoice_ID = il.C_Invoice_ID
JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
LEFT JOIN M_MatchInv mi ON mi.C_InvoiceLine_ID = il.C_InvoiceLine_ID
WHERE inv.C_Invoice_ID = 1712053;
-------------------------------------------

SELECT 
    IL.qtyentered AS Quantite,
    IL.qtyinvoiced AS Qty_facture,
    p.name,
    mi.qty AS qty_RECEPTION,
    mi.C_InvoiceLine_ID AS match_invoice_id,
    mi.documentno AS match_documentno,
    il.C_InvoiceLine_ID AS invoice_line_id
FROM C_Invoice inv
JOIN C_InvoiceLine il ON inv.C_Invoice_ID = il.C_Invoice_ID
JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
LEFT JOIN M_MatchInv mi ON mi.C_InvoiceLine_ID = il.C_InvoiceLine_ID
WHERE inv.dateinvoiced > TO_DATE('2025-01-01', 'YYYY-MM-DD')
and inv.ad_org_id = 1000000
and inv.c_doctype_id = 1001509
and inv.docstatus in ('CO','CL') 
  AND (
        (IL.qtyentered != NVL(mi.qty, 0)) -- mismatch between invoice qty and reception qty
        OR (IL.qtyentered IS NOT NULL AND mi.documentno IS NULL) -- has qty but no match
      );


------------------------------------------------sql facture and reception difrence in qty mine + nazim sql
SELECT 
    IL.qtyentered AS Quantite,
    IL.qtyinvoiced AS Qty_facture,
    p.name,
    il.C_InvoiceLine_ID AS invoice_line_id,
    SUM(NVL(mi.qty, 0)) AS total_qty_reception,
    LISTAGG(mi.documentno, ', ') WITHIN GROUP (ORDER BY mi.documentno) AS match_documentnos
FROM C_Invoice inv
JOIN C_InvoiceLine il ON inv.C_Invoice_ID = il.C_Invoice_ID
JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
LEFT JOIN M_MatchInv mi ON mi.C_InvoiceLine_ID = il.C_InvoiceLine_ID
WHERE inv.dateinvoiced > TO_DATE('2025-01-01', 'YYYY-MM-DD')
  AND inv.ad_org_id = 1000000
  AND inv.c_doctype_id = 1001509
  AND inv.docstatus IN ('CO','CL')
GROUP BY 
    IL.qtyentered,
    IL.qtyinvoiced,
    p.name,
    il.C_InvoiceLine_ID
HAVING 
    IL.qtyentered != SUM(NVL(mi.qty, 0)) -- mismatch between invoice qty and sum of receptions
    OR (IL.qtyentered IS NOT NULL AND COUNT(mi.documentno) = 0) -- has qty but no match
ORDER BY 
    il.C_InvoiceLine_ID;


      
SELECT 
    ci.DOCUMENTNO AS INVOICE_NUMBER,
    ci.DATEINVOICED,
    bp.NAME AS CUSTOMER_NAME,
    mp.NAME AS PRODUCT_NAME,
    cil.LINE AS INVOICE_LINE_NUMBER,
    cil.QTYINVOICED AS INVOICED_QUANTITY,
    COALESCE(SUM(mmi.QTY), 0) AS RECEIVED_QUANTITY,
    (cil.QTYINVOICED - COALESCE(SUM(mmi.QTY), 0)) AS MISSING_QUANTITY,
    cil.LINENETAMT AS LINE_AMOUNT,
    COUNT(mmi.M_MATCHINV_ID) AS MATCH_RECORDS,
    -- Additional debug info
    LISTAGG(mmi.M_MATCHINV_ID, ', ') WITHIN GROUP (ORDER BY mmi.M_MATCHINV_ID) AS MATCHINV_IDS,
    LISTAGG(mmi.QTY, ', ') WITHIN GROUP (ORDER BY mmi.M_MATCHINV_ID) AS INDIVIDUAL_QTYS,
    LISTAGG(mmi.M_INOUTLINE_ID, ', ') WITHIN GROUP (ORDER BY mmi.M_MATCHINV_ID) AS INOUTLINE_IDS
FROM 
    C_INVOICE ci
    INNER JOIN C_INVOICELINE cil ON ci.C_INVOICE_ID = cil.C_INVOICE_ID
    INNER JOIN M_PRODUCT mp ON cil.M_PRODUCT_ID = mp.M_PRODUCT_ID
    INNER JOIN C_BPARTNER bp ON ci.C_BPARTNER_ID = bp.C_BPARTNER_ID
    LEFT JOIN M_MATCHINV mmi ON cil.C_INVOICELINE_ID = mmi.C_INVOICELINE_ID 
        AND mmi.AD_ORG_ID = 1000000
WHERE 
    ci.AD_ORG_ID = 1000000
    AND ci.C_DocTypeTarget_ID = 1001509
    AND ci.DOCSTATUS IN ('CO', 'CL')
    and ci.dateinvoiced > TO_DATE('2025-01-01', 'YYYY-MM-DD')


    AND cil.M_PRODUCT_ID IS NOT NULL
GROUP BY 
    ci.DOCUMENTNO,
    ci.DATEINVOICED,
    bp.NAME,
    mp.NAME,
    cil.LINE,
    cil.C_INVOICELINE_ID,
    cil.QTYINVOICED,
    cil.LINENETAMT
HAVING 
    cil.QTYINVOICED != COALESCE(SUM(mmi.QTY), 0)  -- Only show non-matching quantities
ORDER BY 
    ci.DATEINVOICED DESC,
    ci.DOCUMENTNO,
    cil.LINE;


    ----------------------------------    

    select * from C_DocType  where C_DocType_ID =1001510;
where isactive='Y' and ad_orgtrx_id=1000000 and ad_client_id=1000000


select isactive from C_BPartner_Product where C_BPartner_Product_ID=1018736;


SELECT 
    bp.NAME AS SUPPLIER_NAME,
    p.NAME AS PRODUCT_NAME,
    sc.name AS BONUS,
    iol.MOVEMENTQTY AS QUANTITY_RECEIVED,
    iol.QTYENTERED AS QTY_ENTERED,
    io.MOVEMENTDATE AS DATE_RECEIVED,
    io.DOCUMENTNO AS RECEIPT_DOCUMENT_NO,
    iol.M_ATTRIBUTESETINSTANCE_ID as lot_id,

    dsp.NAME AS M_DISCOUNTSCHEMA_POTENTIEL,
    dspara.NAME AS M_DISCOUNTSCHEMA_PARA

FROM M_INOUT io
INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
INNER JOIN M_PRODUCT p ON iol.M_PRODUCT_ID = p.M_PRODUCT_ID
INNER JOIN C_BPARTNER bp ON io.C_BPARTNER_ID = bp.C_BPARTNER_ID
JOIN XX_SALESCONTEXT sc ON p.XX_SALESCONTEXT_ID = sc.XX_SALESCONTEXT_ID

-- Join Potentiel Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1001330
) bppot ON bppot.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dsp ON dsp.M_DiscountSchema_ID = bppot.M_DISCOUNTSCHEMA_ID

-- Join Para Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1000003
) bppara ON bppara.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dspara ON dspara.M_DiscountSchema_ID = bppara.M_DISCOUNTSCHEMA_ID

WHERE io.DOCSTATUS IN ('CO', 'CL')
    AND io.C_DOCTYPE_ID = 1000013
    AND io.AD_CLIENT_ID = 1000000
    AND io.ISACTIVE = 'Y'
    AND iol.ISACTIVE = 'Y'
    AND p.ISACTIVE = 'Y'
    AND bp.ISACTIVE = 'Y'
    AND iol.M_PRODUCT_ID IS NOT NULL
    AND io.MOVEMENTDATE BETWEEN TO_DATE('2025-08-26', 'YYYY-MM-DD') AND TO_DATE('2025-08-26', 'YYYY-MM-DD')

ORDER BY io.MOVEMENTDATE DESC, bp.NAME, p.VALUE
;





        SELECT
    iol.M_INOUTLINE_ID,
    MAX(CASE WHEN ai.M_ATTRIBUTE_ID = 1001408 THEN ai.VALUENUMBER END) AS REM_VENTE,
    MAX(CASE WHEN ai.M_ATTRIBUTE_ID = 1000908 THEN ai.VALUENUMBER END) AS BON_VENTE
FROM M_INOUT io
INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
LEFT JOIN M_ATTRIBUTESETINSTANCE asi ON iol.M_ATTRIBUTESETINSTANCE_ID = asi.M_ATTRIBUTESETINSTANCE_ID
LEFT JOIN M_ATTRIBUTEINSTANCE ai ON asi.M_ATTRIBUTESETINSTANCE_ID = ai.M_ATTRIBUTESETINSTANCE_ID
WHERE asi.ISACTIVE = 'Y'
    AND ai.ISACTIVE = 'Y'
    AND ai.M_ATTRIBUTE_ID IN (1000908, 1001408)
    AND io.M_InOut_ID = 3215778
GROUP BY iol.M_INOUTLINE_ID;






SELECT 
    bp.NAME AS SUPPLIER_NAME,
    p.NAME AS PRODUCT_NAME,
    sc.name AS BONUS,
    iol.MOVEMENTQTY AS QUANTITY_RECEIVED,
    iol.QTYENTERED AS QTY_ENTERED,
    io.MOVEMENTDATE AS DATE_RECEIVED,
    io.DOCUMENTNO AS RECEIPT_DOCUMENT_NO,
    iol.M_ATTRIBUTESETINSTANCE_ID as lot_id,

    -- Fetch REM_VENTE and BON_VENTE from attributes
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1001408
          AND ai.ISACTIVE = 'Y'
    ) AS REM_VENTE,
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1000908
          AND ai.ISACTIVE = 'Y'
    ) AS BON_VENTE,

    dsp.NAME AS M_DISCOUNTSCHEMA_POTENTIEL,
    dspara.NAME AS M_DISCOUNTSCHEMA_PARA

FROM M_INOUT io
INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
INNER JOIN M_PRODUCT p ON iol.M_PRODUCT_ID = p.M_PRODUCT_ID
INNER JOIN C_BPARTNER bp ON io.C_BPARTNER_ID = bp.C_BPARTNER_ID
JOIN XX_SALESCONTEXT sc ON p.XX_SALESCONTEXT_ID = sc.XX_SALESCONTEXT_ID

-- Join Potentiel Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1001330
) bppot ON bppot.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dsp ON dsp.M_DiscountSchema_ID = bppot.M_DISCOUNTSCHEMA_ID

-- Join Para Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1000003
) bppara ON bppara.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dspara ON dspara.M_DiscountSchema_ID = bppara.M_DISCOUNTSCHEMA_ID

WHERE io.DOCSTATUS IN ('CO', 'CL')
    AND io.C_DOCTYPE_ID = 1000013
    AND io.AD_CLIENT_ID = 1000000
    AND io.ISACTIVE = 'Y'
    AND iol.ISACTIVE = 'Y'
    AND p.ISACTIVE = 'Y'
    AND bp.ISACTIVE = 'Y'
    AND iol.M_PRODUCT_ID IS NOT NULL
    AND io.MOVEMENTDATE BETWEEN TO_DATE('2025-08-26', 'YYYY-MM-DD') AND TO_DATE('2025-08-26', 'YYYY-MM-DD')

ORDER BY io.MOVEMENTDATE DESC, bp.NAME, p.VALUE
;


SELECT 
    bp.NAME AS SUPPLIER_NAME,
    p.NAME AS PRODUCT_NAME,
    sc.name AS BONUS,
    iol.MOVEMENTQTY AS QUANTITY_RECEIVED,
    iol.QTYENTERED AS QTY_ENTERED,
    io.MOVEMENTDATE AS DATE_RECEIVED,
    io.DOCUMENTNO AS RECEIPT_DOCUMENT_NO,
    iol.M_ATTRIBUTESETINSTANCE_ID as lot_id,

    -- Fetch REM_VENTE and BON_VENTE from attributes
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1001408
          AND ai.ISACTIVE = 'Y'
    ) AS REM_VENTE,
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1000908
          AND ai.ISACTIVE = 'Y'
    ) AS BON_VENTE,

    -- Fetch qty_dispo from m_storage
    (SELECT SUM(ms.qtyonhand - ms.qtyreserved)
        FROM m_storage ms
        WHERE ms.m_attributesetinstance_id = iol.m_attributesetinstance_id
    ) AS qty_dispo,

    dsp.NAME AS M_DISCOUNTSCHEMA_POTENTIEL,
    dspara.NAME AS M_DISCOUNTSCHEMA_PARA

FROM M_INOUT io
INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
INNER JOIN M_PRODUCT p ON iol.M_PRODUCT_ID = p.M_PRODUCT_ID
INNER JOIN C_BPARTNER bp ON io.C_BPARTNER_ID = bp.C_BPARTNER_ID
JOIN XX_SALESCONTEXT sc ON p.XX_SALESCONTEXT_ID = sc.XX_SALESCONTEXT_ID

-- Join Potentiel Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1001330
) bppot ON bppot.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dsp ON dsp.M_DiscountSchema_ID = bppot.M_DISCOUNTSCHEMA_ID

-- Join Para Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1000003
) bppara ON bppara.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dspara ON dspara.M_DiscountSchema_ID = bppara.M_DISCOUNTSCHEMA_ID

WHERE io.DOCSTATUS IN ('CO', 'CL')
    AND io.C_DOCTYPE_ID = 1000013
    AND io.AD_CLIENT_ID = 1000000
    AND io.ISACTIVE = 'Y'
    AND iol.ISACTIVE = 'Y'
    AND p.ISACTIVE = 'Y'
    AND bp.ISACTIVE = 'Y'
    AND iol.M_PRODUCT_ID IS NOT NULL
    AND io.MOVEMENTDATE BETWEEN TO_DATE('2025-08-26', 'YYYY-MM-DD') AND TO_DATE('2025-08-26', 'YYYY-MM-DD')

ORDER BY io.MOVEMENTDATE DESC, bp.NAME, p.VALUE
;




------------------ version of qty-------------------------

SELECT 
    bp.NAME AS SUPPLIER_NAME,
    p.NAME AS PRODUCT_NAME,
    sc.name AS BONUS,
    iol.MOVEMENTQTY AS QUANTITY_RECEIVED,
    iol.QTYENTERED AS QTY_ENTERED,
    io.MOVEMENTDATE AS DATE_RECEIVED,
    io.DOCUMENTNO AS RECEIPT_DOCUMENT_NO,
    iol.M_ATTRIBUTESETINSTANCE_ID as lot_id,

    -- Fetch REM_VENTE and BON_VENTE from attributes
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1001408
          AND ai.ISACTIVE = 'Y'
    ) AS REM_VENTE,
    (SELECT MAX(ai.VALUENUMBER)
        FROM M_ATTRIBUTEINSTANCE ai
        WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
          AND ai.M_ATTRIBUTE_ID = 1000908
          AND ai.ISACTIVE = 'Y'
    ) AS BON_VENTE,

    -- Qty dispo for this lot
    (SELECT SUM(ms.qtyonhand - ms.qtyreserved)
        FROM m_storage ms
        WHERE ms.m_attributesetinstance_id = iol.m_attributesetinstance_id

    ) AS qty_dispo,

    -- Qty dispo for all lots of this product
    (SELECT SUM(ms.qtyonhand - ms.qtyreserved)
        FROM m_storage ms
        WHERE ms.m_product_id = iol.m_product_id
          AND ms.ad_client_id = 1000000
          AND ms.qtyonhand > 0
          AND ms.m_locator_id = 1000614
    ) AS qty_dispo_total,

    dsp.NAME AS M_DISCOUNTSCHEMA_POTENTIEL,
    dspara.NAME AS M_DISCOUNTSCHEMA_PARA

FROM M_INOUT io
INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
INNER JOIN M_PRODUCT p ON iol.M_PRODUCT_ID = p.M_PRODUCT_ID
INNER JOIN C_BPARTNER bp ON io.C_BPARTNER_ID = bp.C_BPARTNER_ID
JOIN XX_SALESCONTEXT sc ON p.XX_SALESCONTEXT_ID = sc.XX_SALESCONTEXT_ID

-- Join Potentiel Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1001330
) bppot ON bppot.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dsp ON dsp.M_DiscountSchema_ID = bppot.M_DISCOUNTSCHEMA_ID

-- Join Para Discount Schema
LEFT JOIN (
    SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
    FROM C_BPartner_Product 
    WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1000003
) bppara ON bppara.M_PRODUCT_ID = p.M_PRODUCT_ID

LEFT JOIN M_DiscountSchema dspara ON dspara.M_DiscountSchema_ID = bppara.M_DISCOUNTSCHEMA_ID

WHERE io.DOCSTATUS IN ('CO', 'CL')
    AND io.C_DOCTYPE_ID = 1000013
    AND io.AD_CLIENT_ID = 1000000
    AND io.ISACTIVE = 'Y'
    AND iol.ISACTIVE = 'Y'
    AND p.ISACTIVE = 'Y'
    AND bp.ISACTIVE = 'Y'
    AND iol.M_PRODUCT_ID IS NOT NULL
    AND io.MOVEMENTDATE BETWEEN TO_DATE('2025-08-26', 'YYYY-MM-DD') AND TO_DATE('2025-08-26', 'YYYY-MM-DD')

ORDER BY io.MOVEMENTDATE DESC, bp.NAME, p.VALUE
;

------------  CHANGE QTY FROM ORDER FACTURE EXP TRANSCTION M_STORAGE--------------------------------------


    UPDATE m_storage
SET  QTYONHAND = 2  -- This makes QTY_DISPO = qtyonhand - QTYRESERVED = 0
WHERE m_product_id = 1187592
 and m_locator_id = 1001020
 and M_ATTRIBUTESETINSTANCE_ID=1555135;




    select QTYRESERVED , QTYONHAND from m_storage
where   
 m_product_id = 1187592
 and m_locator_id = 1000614
;


 select M_ATTRIBUTESETINSTANCE_ID from C_OrderLine where C_OrderLine_ID=8643988;



select xx_preparateur_id from M_InOut where M_InOut_ID=3216327;

update M_InOut set docstatus='CO', docaction='CL' where M_InOut_ID=3209895;



update M_InOut set xx_preparateur_id=1145260  where M_InOut_ID=3209895;





SELECT QTYDELIVERED, QTYINVOICED  FROM C_OrderLine WHERE C_OrderLine_ID=8643988;

UPDATE C_OrderLine SET QTYINVOICED = 0, QTYDELIVERED = 0 WHERE C_OrderLine_ID=8643988;






SELECT MOVEMENTQTY FROM M_Transaction WHERE M_Transaction_ID=4884650;

UPDATE M_Transaction SET MOVEMENTQTY = 0 WHERE M_Transaction_ID=4884650;

---------------------------------------------------------------------------------------------------




 select * from M_Product where M_Product_ID=1193472;

SELECT DISTINCT
  p.M_Product_ID,
  p.name AS product_name,
  st.M_ATTRIBUTESETINSTANCE_ID,
  asi.lot,
  asi.guaranteedate,
  asi.description
FROM M_Product p
INNER JOIN M_Storage st ON p.M_Product_ID = st.M_Product_ID
INNER JOIN M_AttributeSetInstance asi ON st.M_AttributeSetInstance_ID = asi.M_AttributeSetInstance_ID
WHERE p.M_Product_ID = :product_id
  AND p.AD_Client_ID = 1000000
  AND p.IsActive = 'Y'
  AND asi.IsActive = 'Y'
ORDER BY asi.lot;



SELECT 
                  bp.NAME AS SUPPLIER_NAME,
                  p.NAME AS PRODUCT_NAME,
                  sc.name AS BONUS,
                  MAX((
                      SELECT ai.VALUENUMBER
                      FROM M_ATTRIBUTEINSTANCE ai
                      WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
                        AND ai.M_ATTRIBUTE_ID = 1001408
                        AND ai.ISACTIVE = 'Y'
                  )) AS REM_VENTE,
                  MAX((
                      SELECT ai.VALUENUMBER
                      FROM M_ATTRIBUTEINSTANCE ai
                      WHERE ai.M_ATTRIBUTESETINSTANCE_ID = iol.M_ATTRIBUTESETINSTANCE_ID
                        AND ai.M_ATTRIBUTE_ID = 1000908
                        AND ai.ISACTIVE = 'Y'
                  )) AS BON_VENTE,
                  (SELECT SUM(ms.qtyonhand - ms.qtyreserved)
                      FROM m_storage ms
                      WHERE ms.m_product_id = p.m_product_id
                        AND ms.ad_client_id = 1000000
                        AND ms.qtyonhand > 0
                        AND ms.m_locator_id = 1000614
                  ) AS qty_dispo,
                  MAX(dsp.NAME)   AS REM_POT,
                  MAX(dspara.NAME) AS REM_PARA
              FROM M_INOUT io
              INNER JOIN M_INOUTLINE iol ON io.M_INOUT_ID = iol.M_INOUT_ID
              INNER JOIN M_PRODUCT p ON iol.M_PRODUCT_ID = p.M_PRODUCT_ID
              INNER JOIN C_BPARTNER bp ON io.C_BPARTNER_ID = bp.C_BPARTNER_ID
              JOIN XX_SALESCONTEXT sc ON p.XX_SALESCONTEXT_ID = sc.XX_SALESCONTEXT_ID
              LEFT JOIN (
                  SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
                  FROM C_BPartner_Product 
                  WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1001330
              ) bppot ON bppot.M_PRODUCT_ID = p.M_PRODUCT_ID
              LEFT JOIN M_DiscountSchema dsp ON dsp.M_DiscountSchema_ID = bppot.M_DiscountSchema_ID
              LEFT JOIN (
                  SELECT M_PRODUCT_ID, M_DISCOUNTSCHEMA_ID 
                  FROM C_BPartner_Product 
                  WHERE ISACTIVE = 'Y' AND C_BP_Group_ID = 1000003
              ) bppara ON bppara.M_PRODUCT_ID = p.M_PRODUCT_ID
              LEFT JOIN M_DiscountSchema dspara ON dspara.M_DiscountSchema_ID = bppara.M_DiscountSchema_ID
              WHERE io.DOCSTATUS IN ('CO', 'CL')
                AND io.C_DOCTYPE_ID = 1000013
                AND io.AD_CLIENT_ID = 1000000
                AND io.ISACTIVE = 'Y'
                AND iol.ISACTIVE = 'Y'
                AND p.ISACTIVE = 'Y'
                AND bp.ISACTIVE = 'Y'
                AND iol.M_PRODUCT_ID IS NOT NULL
                AND io.MOVEMENTDATE BETWEEN TO_DATE('2025-01-01', 'YYYY-MM-DD') AND TO_DATE('2025-08-27', 'YYYY-MM-DD')
              GROUP BY bp.NAME, p.NAME, sc.name, p.m_product_id
              ORDER BY bp.NAME, p.NAME
              ;



----------------
SELECT isactive FROM M_PRODUCT
WHERE M_Product_ID=1193025;

select name  from M_Warehouse
where isactive='Y';



select * from  C_Order where C_Order_ID=3205567;

select * from C_OrderLine where C_OrderLine_ID=7840870;

select or_l.m_product_id ,
 prd.name ,
 or_l.qtyentered as Qty, 
 or_l.priceEntered as price,
or_l.linenetamt as Total_from_db , 
(  or_l.qtyentered * or_l.priceentered) as My_Total
from C_OrderLine  or_l
join  C_Order ord on  ord.C_Order_ID= or_l.C_Order_ID 
join m_product prd on or_l.m_product_id=prd.m_product_id
where ord.documentno = :documentno
;
------------------------------


select documentno from c_order where
dateordered between '01-01-2025' and '08-01-2025';

------------------------------------------

select qtyentered as name  from c_orderline  or_l
join c_order ord on ord.c_order_id= or_l.C_Order_ID
join M_Product prd on prd.M_Product_ID=or_l.M_Product_ID
where ord.documentno= :documentno 
and prd.name= :name
and or_l.Discount !=100
;



update c_orderline set qtyentered = 50 


;


--------
SELECT Name from c_bpartner where 
isactive='Y' and 
ISVENDOR='Y';


select isvendor from c_bpartner where c_bpartner_id=1115992;
-------------------------


SELECT io.documentno from M_InOut io
join C_order ord on ord.c_order_id = io.c_order_id
where ord.documentno = :documentno

;
-------------------

SELECT inv.documentno from C_Invoice inv
join C_order ord on ord.c_order_id = inv.c_order_id
where ord.documentno = :documentno

;
----------------



select ino.documentno as expedition , cin.documentno as maintenance
from M_InOut ino
join C_Invoice cin on cin.c_order_id = ino.c_order_id
join C_order ord on ord.c_order_id = ino.c_order_id
where ord.documentno = :documentno
;


-- =====================================================
-- NEW QUERIES: Get Invoice Lines by Invoice Document Number
-- =====================================================

-- Basic query - Replace 'INVOICE_DOCUMENT_NO' with the actual invoice document number
SELECT 
    il.C_InvoiceLine_ID,
    il.Line,
    il.QtyEntered,
    il.QtyInvoiced,
    il.PriceEntered,
    il.PriceActual,
    il.LineNetAmt,
    il.LineTotalAmt,
    il.Description as LineDescription,
    il.M_Product_ID,
    p.Name as ProductName,
    p.Value as ProductValue,
    i.DocumentNo as InvoiceNumber,
    i.DateInvoiced,
    i.Description as InvoiceDescription
FROM 
    C_Invoice i
    INNER JOIN C_InvoiceLine il ON i.C_Invoice_ID = il.C_Invoice_ID
    LEFT JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
WHERE 
    i.DocumentNo = '15933/2025'  -- Replace with actual invoice document number
    AND i.DocStatus IN ('CO', 'CL')  -- Only completed/closed invoices
    AND i.IsActive = 'Y'
    AND il.IsActive = 'Y'
ORDER BY 
    il.Line;

-- Enhanced query with parameter binding and more details
SELECT 
    il.C_InvoiceLine_ID,
    il.Line,
    il.QtyEntered,
    il.QtyInvoiced,
    il.PriceEntered,
    il.PriceActual,
    il.LineNetAmt,
    il.LineTotalAmt,
    il.Description as LineDescription,
    il.M_Product_ID,
    p.Name as ProductName,
    p.Value as ProductValue,
    p.DocumentNote as ProductNote,
    i.DocumentNo as InvoiceNumber,
    i.DateInvoiced,
    i.Description as InvoiceDescription,
    i.GrandTotal as InvoiceTotal,
    bp.Name as BusinessPartnerName
FROM 
    C_Invoice i
    INNER JOIN C_InvoiceLine il ON i.C_Invoice_ID = il.C_Invoice_ID
    LEFT JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
    LEFT JOIN C_BPartner bp ON i.C_BPartner_ID = bp.C_BPartner_ID
WHERE 
    i.DocumentNo = :invoice_document_no  -- Parameter binding
    AND i.DocStatus IN ('CO', 'CL')
    AND i.IsActive = 'Y'
    AND il.IsActive = 'Y'
    AND i.AD_Client_ID = 1000000  -- Client filter
ORDER BY 
    il.Line;

-- Simple query to find invoice by partial document number (using LIKE)
SELECT 
    il.C_InvoiceLine_ID,
    il.Line,
    il.QtyEntered,
    il.QtyInvoiced,
    il.LineNetAmt,
    il.Description as LineDescription,
    p.Name as ProductName,
    p.Value as ProductValue,
    i.DocumentNo as InvoiceNumber
FROM 
    C_Invoice i
    INNER JOIN C_InvoiceLine il ON i.C_Invoice_ID = il.C_Invoice_ID
    LEFT JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
WHERE 
    i.DocumentNo LIKE '%' || :partial_invoice_no || '%'
    AND i.DocStatus IN ('CO', 'CL')
    AND i.IsActive = 'Y'
    AND il.IsActive = 'Y'
ORDER BY 
    i.DocumentNo, il.Line;

-- Compact query for quick lookup
SELECT 
    il.Line,
    il.QtyEntered,
    il.LineNetAmt,
    p.Name as ProductName
FROM 
    C_Invoice i
    INNER JOIN C_InvoiceLine il ON i.C_Invoice_ID = il.C_Invoice_ID
    LEFT JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
WHERE 
    i.DocumentNo = :invoice_document_no
    AND i.DocStatus IN ('CO', 'CL')
ORDER BY 
    il.Line;


-- =====================================================
-- TRACE BACK FROM INVOICE TO RELATED ORDERS
-- =====================================================

-- Get all orders related to a specific invoice through invoice lines and order lines
SELECT DISTINCT
    o.C_Order_ID,
    o.DocumentNo as OrderNumber,
    o.DateOrdered,
    o.DatePromised,
    o.GrandTotal as OrderTotal,
    o.Description as OrderDescription,
    bp.Name as BusinessPartnerName,
    o.DocStatus as OrderStatus
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
    o.DateOrdered, o.DocumentNo;

-- Detailed version with invoice line and order line details
SELECT 
    i.DocumentNo as InvoiceNumber,
    i.DateInvoiced,
    il.Line as InvoiceLine,
    il.QtyInvoiced,
    il.LineNetAmt as InvoiceLineAmount,
    ol.C_OrderLine_ID,
    ol.Line as OrderLine,
    ol.QtyOrdered,
    ol.LineNetAmt as OrderLineAmount,
    o.DocumentNo as OrderNumber,
    o.DateOrdered,
    o.GrandTotal as OrderTotal,
    p.Name as ProductName,
    p.Value as ProductValue,
    bp.Name as BusinessPartnerName
FROM 
    C_Invoice i
    INNER JOIN C_InvoiceLine il ON i.C_Invoice_ID = il.C_Invoice_ID
    INNER JOIN C_OrderLine ol ON il.C_OrderLine_ID = ol.C_OrderLine_ID
    INNER JOIN C_Order o ON ol.C_Order_ID = o.C_Order_ID
    LEFT JOIN M_Product p ON il.M_Product_ID = p.M_Product_ID
    LEFT JOIN C_BPartner bp ON o.C_BPartner_ID = bp.C_BPartner_ID
WHERE 
    i.DocumentNo = :invoice_document_no
    AND i.DocStatus IN ('CO', 'CL')
    AND i.IsActive = 'Y'
    AND il.IsActive = 'Y'
    AND ol.IsActive = 'Y'
    AND o.IsActive = 'Y'
ORDER BY 
    o.DateOrdered, o.DocumentNo, il.Line;

-- Simple query - just get order numbers related to invoice
SELECT DISTINCT
    o.DocumentNo as OrderNumber,
    o.DateOrdered,
    o.GrandTotal as OrderTotal
FROM 
    C_Invoice i
    INNER JOIN C_InvoiceLine il ON i.C_Invoice_ID = il.C_Invoice_ID
    INNER JOIN C_OrderLine ol ON il.C_OrderLine_ID = ol.C_OrderLine_ID
    INNER JOIN C_Order o ON ol.C_Order_ID = o.C_Order_ID
WHERE 
    i.DocumentNo = '15933/2025'  -- Replace with actual invoice number
    AND i.DocStatus IN ('CO', 'CL')
    AND i.IsActive = 'Y'
ORDER BY 
    o.DateOrdered;

-- Count how many orders are related to an invoice
SELECT 
    i.DocumentNo as InvoiceNumber,
    COUNT(DISTINCT o.C_Order_ID) as NumberOfRelatedOrders,
    STRING_AGG(DISTINCT o.DocumentNo, ', ') as OrderNumbers
FROM 
    C_Invoice i
    INNER JOIN C_InvoiceLine il ON i.C_Invoice_ID = il.C_Invoice_ID
    INNER JOIN C_OrderLine ol ON il.C_OrderLine_ID = ol.C_OrderLine_ID
    INNER JOIN C_Order o ON ol.C_Order_ID = o.C_Order_ID
WHERE 
    i.DocumentNo = :invoice_document_no
    AND i.DocStatus IN ('CO', 'CL')
    AND i.IsActive = 'Y'
GROUP BY 
    i.DocumentNo;




------------------------------------


select xx_lignegratuite from C_OrderLine where C_OrderLine_ID=8695013;



SELECT NAME FROM M_Warehouse WHERE M_Warehouse_ID IN (1000014, 1000721);

SELECT 
    p.name AS product,
    mst.qtyonhand AS qty,
    (mst.qtyonhand - mst.QTYRESERVED) AS qty_dispo,
    mst.m_locator_id,
    mati.value AS fournisseur,
    mats.guaranteedate,
    md.name AS remise_auto,
    sal.description AS bonus_auto,
    (
        SELECT lot
        FROM m_attributesetinstance
        WHERE m_attributesetinstance_id = mst.m_attributesetinstance_id
    ) AS lot,
    (
        SELECT valuenumber
        FROM m_attributeinstance
        WHERE m_attributesetinstance_id = mst.m_attributesetinstance_id
          AND m_attribute_id = 1000501
    ) AS p_achat,
    (
        SELECT valuenumber
        FROM m_attributeinstance
        WHERE m_attributesetinstance_id = mst.m_attributesetinstance_id
          AND m_attribute_id = 1000502
    ) AS p_vente,
    (
        SELECT valuenumber
        FROM m_attributeinstance
        WHERE m_attributesetinstance_id = mst.m_attributesetinstance_id
          AND m_attribute_id = 1000503
    ) AS ppa
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
    AND mst.m_locator_id IN (1001135, 1000614, 1001128, 1001136, 1001020)
    AND mst.qtyonhand != 0
    AND p.name = 'MAG ACTICARBINE BOITE 14 COMPRIMES '  -- Replace with your product_name
ORDER BY 
    p.name, mats.guaranteedate;




    update C_Payment set isallocated = 'Y' where C_Payment_ID=1272137;




------------------------------- find names contains more space -------------------------------
SELECT 
    M_Product_ID AS M_PRODUCT_ID, 
    Name AS NAME
FROM 
    M_Product
WHERE 
    AD_Client_ID = 1000000
    AND AD_Org_ID = 1000000
    AND IsActive = 'Y'
    AND REGEXP_LIKE(Name, ' {2,}')   -- finds 2 or more spaces
ORDER BY 
    Name;




SELECT 
    C_BPartner_ID,
    Name AS Old_Name,
    REGEXP_REPLACE(Name, ' {2,}', ' ') AS Cleaned_Name
FROM 
    C_BPartner
WHERE 
    AD_Client_ID = 1000000
    AND AD_Org_ID = 1000000
    AND IsActive = 'Y'
    AND REGEXP_LIKE(Name, ' {2,}');





    UPDATE C_BPartner
SET Name = REGEXP_REPLACE(Name, ' {2,}', ' ')
WHERE AD_Client_ID = 1000000
  AND AD_Org_ID = 1000000
  AND IsActive = 'Y'
  AND REGEXP_LIKE(Name, ' {2,}');






select docstatus , docaction from C_Order where C_Order_ID=3276557;




SELECT 
    
    co.DocumentNo,
    co.DocStatus,
    co.docaction,
    co.DateOrdered,
    col.qtyreserved

FROM 
    C_OrderLine col
INNER JOIN 
    C_Order co ON col.C_Order_ID = co.C_Order_ID
WHERE 
    col.qtyreserved > 0
    AND co.C_DocType_ID = 1000539
    AND col.m_product_id = 1158567
    and co.AD_Client_ID = 1000000
  AND co.AD_Org_ID = 1000000
ORDER BY 
    co.DateOrdered DESC, col.Line;