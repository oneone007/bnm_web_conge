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


