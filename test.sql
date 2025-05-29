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
        AND p.datetrx >= '01/05/2025'
        AND p.datetrx <= '31/05/2025'
) temp_combined
;




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

-------
----------------------- UPDATE  Remise IN (Articles ----> Articles-Tiers) ------------------------
update C_BPartner_Product set m_discountschema_id = 1000719 -- id de remise
where m_product_id in (select str.m_product_id from m_storage str
inner join m_product mp on (str.m_product_id = mp.m_product_id)
inner join m_attributeinstance att on (str.m_attributesetinstance_id = att.m_attributesetinstance_id)
where att.m_attribute_id = 1000508 and att.value like '%FYTO+ PHARMA MAKERS%'-- fournisseur
and mp.name like 'FYTO+ PHARMA MAKERS%' --produit
) and c_bp_group_id in(1001330)-- id de type client (client para,client potentiel) ;


SELECT * FROM m_attributeinstance WHERE m_attribute_id = 1000508;