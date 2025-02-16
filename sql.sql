WITH LastFournisseur AS (
  SELECT
    cf.m_product_id,
    cf.c_bpartner_id,
    ROW_NUMBER() OVER (
      PARTITION BY cf.m_product_id
      ORDER BY cf.dateinvoiced DESC
    ) AS rn
  FROM
    xx_ca_fournisseur_facture cf
)
SELECT
  mp.name AS product,
  md.name AS reward,
  l.name AS laboratory_name,
  cb.name AS fournisseur,
  CBG.NAME AS Type_Client
FROM
  m_product mp
  INNER JOIN C_BPartner_Product cbp ON mp.m_product_id = cbp.m_product_id
  INNER JOIN  C_BP_Group CBG  ON CBG.C_BP_Group_ID = CBP.C_BP_Group_ID
  JOIN XX_Laboratory l ON l.XX_Laboratory_ID = mp.XX_Laboratory_ID
  JOIN LastFournisseur lf ON mp.m_product_id = lf.m_product_id AND lf.rn = 1
  JOIN c_bpartner cb ON cb.c_bpartner_id = lf.c_bpartner_id
  INNER JOIN M_DiscountSchema md ON cbp.M_DiscountSchema_id = md.M_DiscountSchema_id
WHERE
  cbp.C_BPartner_Product_id IS NOT NULL
  AND cbp.m_discountschema_id IS NOT NULL
ORDER BY
  mp.name
FETCH FIRST 1048575 ROWS ONLY;



SELECT NAME  FROM C_BP_Group ;


- C_BP_Group_ID=1001330




SELECT DISTINCT 
    mp.name AS product,
    (SELECT description 
     FROM XX_SalesContext xsc 
     WHERE mp.XX_SalesContext_id = xsc.XX_SalesContext_id) AS bonus,
    l.name AS laboratory_name,
    cbp.name AS fournisseur
FROM 
    m_product mp
JOIN XX_Laboratory l ON l.XX_Laboratory_ID = mp.XX_Laboratory_ID
JOIN m_storage s ON s.m_product_id = mp.m_product_id
JOIN M_ATTRIBUTEINSTANCE asi ON s.M_ATTRIBUTESETINSTANCE_ID = asi.M_ATTRIBUTESETINSTANCE_ID
JOIN c_bpartner cbp ON cbp.c_bpartner_id = ValueNUMBER_of_ASI('Fournisseur', asi.m_attributesetinstance_id)
WHERE 
    mp.xx_salescontext_id NOT IN (1000000, 1000100)
    AND mp.ad_org_id = 1000000
ORDER BY 
    mp.name
FETCH FIRST 1048575 ROWS ONLY;


select percentage, name from c_bpartner 
WHERE c_bpartner_id IN (1121780, 1122761, 1122868, 1122144, 1111429, 1122142, 1118392, 1119089, 1122143);



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
                        "source"."QTY" "QTY"
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
                                round((marge), 2) marge,
                                labo,
                                lot,
                                qty
                            FROM
                                (
                                    SELECT
                                        d.*,
                                        round(
                                            (((ventef - ((ventef * nvl(rma, 0)) / 100))) - p_revient) / p_revient * 100,
                                            2
                                        ) marge
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
                                                        ) lot
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
                                                        AND mst.m_locator_id IN (1000614, 1001135)
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
                                qty
                            ORDER BY
                                fournisseur
                        ) "source"
                )
            WHERE
                rownum <= 1048575






                SELECT 
    mati.value AS fournisseur, 
    m.name,  
    SUM(m_storage.qtyonhand) AS qty,
    SUM(M_ATTRIBUTEINSTANCE.valuenumber * m_storage.qtyonhand) AS prix,
    SUM(m_storage.qtyonhand - m_storage.QTYRESERVED) AS qty_dispo, 
    SUM(M_ATTRIBUTEINSTANCE.valuenumber * (m_storage.qtyonhand - m_storage.QTYRESERVED)) AS prix_dispo,
    ml.M_Locator_ID AS locatorid,
    m.m_product_id AS productid,
    1 AS sort_order
FROM 
    M_ATTRIBUTEINSTANCE
JOIN 
    m_storage ON m_storage.M_ATTRIBUTEsetINSTANCE_id = M_ATTRIBUTEINSTANCE.M_ATTRIBUTEsetINSTANCE_id
JOIN 
    M_PRODUCT m ON m.M_PRODUCT_id = m_storage.M_PRODUCT_id
JOIN 
    M_Locator ml ON ml.M_Locator_ID = m_storage.M_Locator_ID
INNER JOIN 
    m_attributeinstance mati ON m_storage.m_attributesetinstance_id = mati.m_attributesetinstance_id
WHERE 
    M_ATTRIBUTEINSTANCE.M_Attribute_ID = 1000504
    AND m_storage.qtyonhand > 0
    AND mati.m_attribute_id = 1000508
    AND mati.value LIKE 'FOURNISSEUR%' -- Replace with actual value
    AND m_storage.AD_Client_ID = 1000000
    AND m_storage.M_Locator_ID IN (
        SELECT M_Locator_ID 
        FROM M_Locator 
        WHERE M_Warehouse_ID IN (
            SELECT M_Warehouse_ID 
            FROM M_Warehouse 
            WHERE VALUE IN ('HANGAR','1-Dépôt Principal','8-Dépot réserve','88-Dépot Hangar réserve')
        )
    )
AND (
  (M_Warehouse_ID != 1000000 AND 
   m_storage.M_Locator_ID IN (
       SELECT M_Locator_ID 
       FROM M_Locator 
       WHERE value LIKE 'emplacement%')) -- Replace with actual value
OR 
  (M_Warehouse_ID = 1000000 AND 
   m.M_Locator_ID IN (
       SELECT M_Locator_ID 
       FROM M_Locator 
       WHERE value LIKE 'emplacement%')) -- Replace with actual value
)
GROUP BY 
    m.name, mati.value, m.m_product_id, ml.M_Locator_ID 

UNION ALL

SELECT 
    'Total' AS fournisseur, 
    '' AS name, 
    SUM(m_storage.qtyonhand) AS qty,
    SUM(M_ATTRIBUTEINSTANCE.valuenumber * m_storage.qtyonhand) AS prix,
    SUM(m_storage.qtyonhand - m_storage.QTYRESERVED) AS qty_dispo, 
    SUM(M_ATTRIBUTEINSTANCE.valuenumber * (m_storage.qtyonhand - m_storage.QTYRESERVED)) AS prix_dispo,
    NULL AS locatorid,  
    NULL AS productid,  
    0 AS sort_order
FROM 
    M_ATTRIBUTEINSTANCE
JOIN 
    m_storage ON m_storage.M_ATTRIBUTEsetINSTANCE_id = M_ATTRIBUTEINSTANCE.M_ATTRIBUTEsetINSTANCE_id
JOIN 
    M_PRODUCT m ON m.M_PRODUCT_id = m_storage.M_PRODUCT_id
JOIN 
    M_Locator ml ON ml.M_Locator_ID = m_storage.M_Locator_ID
INNER JOIN 
    m_attributeinstance mati ON m_storage.m_attributesetinstance_id = mati.m_attributesetinstance_id
WHERE 
    M_ATTRIBUTEINSTANCE.M_Attribute_ID = 1000504
    AND m_storage.qtyonhand > 0
    AND mati.m_attribute_id = 1000508
    AND mati.value LIKE 'FOURNISSEUR%' -- Replace with actual value
    AND m_storage.AD_Client_ID = 1000000
    AND m_storage.M_Locator_ID IN (
        SELECT M_Locator_ID 
        FROM M_Locator 
        WHERE M_Warehouse_ID IN (
            SELECT M_Warehouse_ID 
            FROM M_Warehouse 
            WHERE VALUE IN ('HANGAR','1-Dépôt Principal','8-Dépot réserve','88-Dépot Hangar réserve')
        )
    )
AND (
  (M_Warehouse_ID != 1000000 AND 
   m_storage.M_Locator_ID IN (
       SELECT M_Locator_ID 
       FROM M_Locator 
       WHERE value LIKE 'emplacement%')) -- Replace with actual value
OR 
  (M_Warehouse_ID = 1000000 AND 
   m.M_Locator_ID IN (
       SELECT M_Locator_ID 
       FROM M_Locator 
       WHERE value LIKE 'emplacement%')) -- Replace with actual value
)
ORDER BY 
    sort_order, fournisseur, name





SELECT 
    mati.value AS fournisseur, 
    m.name,  
    SUM(m_storage.qtyonhand) AS qty,
    SUM(M_ATTRIBUTEINSTANCE.valuenumber * m_storage.qtyonhand) AS prix,
    SUM(m_storage.qtyonhand - m_storage.QTYRESERVED) AS qty_dispo, 
    SUM(M_ATTRIBUTEINSTANCE.valuenumber * (m_storage.qtyonhand - m_storage.QTYRESERVED)) AS prix_dispo,
    ml.M_Locator_ID AS locatorid,
    m.m_product_id AS productid,
    1 AS sort_order
FROM 
    M_ATTRIBUTEINSTANCE
JOIN 
    m_storage ON m_storage.M_ATTRIBUTEsetINSTANCE_id = M_ATTRIBUTEINSTANCE.M_ATTRIBUTEsetINSTANCE_id
JOIN 
    M_PRODUCT m ON m.M_PRODUCT_id = m_storage.M_PRODUCT_id
JOIN 
    M_Locator ml ON ml.M_Locator_ID = m_storage.M_Locator_ID
INNER JOIN 
    m_attributeinstance mati ON m_storage.m_attributesetinstance_id = mati.m_attributesetinstance_id
WHERE 
    M_ATTRIBUTEINSTANCE.M_Attribute_ID = 1000504
    AND m_storage.qtyonhand > 0
    AND mati.m_attribute_id = 1000508
    [[AND mati.value LIKE {{FOURNISSEUR}} || '%']]
    AND m_storage.AD_Client_ID = 1000000
    AND m_storage.M_Locator_ID IN (
        SELECT M_Locator_ID 
        FROM M_Locator 
        WHERE M_Warehouse_ID IN (
            SELECT M_Warehouse_ID 
            FROM M_Warehouse 
            WHERE VALUE in [[ {{ MAGASIN }} --]] ('HANGAR','1-Dépôt Principal','8-Dépot réserve','88-Dépot Hangar réserve')
        )
    )
AND (
  (M_Warehouse_ID != 1000000 AND 
   m_storage.M_Locator_ID IN (
       SELECT M_Locator_ID 
       FROM M_Locator 
       [[WHERE (value LIKE {{emplacement}} || '%' OR value IS NULL OR value = '')]]
   ))
OR 
  (M_Warehouse_ID = 1000000 AND 
   m.M_Locator_ID IN (
       SELECT M_Locator_ID 
       FROM M_Locator 
       [[WHERE (value LIKE {{emplacement}} || '%' OR value IS NULL OR value = '')]]
   ))
)
GROUP BY 
    m.name, mati.value, m.m_product_id, ml.M_Locator_ID 

UNION ALL





--------------------------------------------------


SELECT 
    xf.MOVEMENTDATE,  -- Include the date so it can be filtered later
    SUM(xf.TOTALLINE) AS CHIFFRE, 
    SUM(xf.qtyentered) AS QTY,
    SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION) AS MARGE,
    CASE 
        WHEN SUM(xf.CONSOMATION) < 0 
        THEN ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / (SUM(xf.CONSOMATION) * -1))
        ELSE (SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / NULLIF(SUM(xf.CONSOMATION), 0) -- Avoid division by zero
    END AS POURCENTAGE
FROM xx_ca_fournisseur xf
WHERE 
    xf.AD_Org_ID = 1000000
    AND xf.DOCSTATUS != 'RE'
GROUP BY xf.MOVEMENTDATE  -- Group by date so filtering is possible later in the script
ORDER BY xf.MOVEMENTDATE;
----------------------------
------------



SELECT cb.value, cb.name, cb.xx_nif, cb.xx_nis, cb.xx_ai, cb.xx_rc, cb.description, loc.name AS name_addr
FROM c_bpartner cb
left JOIN C_BPartner_Location loc ON (cb.c_bpartner_id = loc.c_bpartner_id)
WHERE cb.isactive = 'Y' 
AND cb.AD_Client_ID = 1000022
AND cb.value IN (
    '1901018', '1000496', '1901199', '1000439', '1000429', 'ALPHA PARAPHARM', '0210254', '1000499', 
    '6589874', '1901015', '1000446', '1901019', '2365454', '1000488', '1000420', 'C-P-12.0', 
    'C-P-480.0', 'CHADLI LATIFA', '1000448', '1901029', '1901009', '1000356', '6598412', '1000483', 
    '666598', '77774459', '9874521', '6666988', '333658', '1000444', '1901179', '1000468', '1901026', 
    '1000337', 'C-P-351.0', 'EURL MAMA AMINA GRANDS TRAVAUX PUBLICS', '3214568', '1901030', 
    '1901178', '1901037', '6598776', '1000459', '1000475', '1901186', '1901017', '1000497', 
    '1901177', '589741', '987654', '1901010', 'C-P-123.0', 'HAMZA KARIM', '555588774', '65478', 
    '1901014', '1901011', '1901074', '02100056', 'CP-1000424', '1000437', '1901038', '65981254', 
    '1000442', '1000431', 'LG MEDIC', '265458', '1901196', '236584', '1000426', '2156145', 
    '1901122', 'MNM PARAPHARM', 'MOHAMED TOUFIK SAID', '6598700', '65487', '369998', '1901149', 
    'C-P-244.0', '2987554', '1901094', '1000464', '1901013', '1000193', '1000428', '230110', 
    '321114', '1901170', '1000474', '1000384', '1901004', '1901198', '1000480', '6547899', 
    'SARL ETABLISSEMENT MERHANE', '654899', '1000427', '1000457', '1901140', '1000467', '1000430', 
    '1000445', '1901124', '1000436', 'SARL PHARM ACTION', '1901007', '65987', 'SOUCHA PHARM', 
    '1000425', '64565435', '1901002', '1901148', '1901028', '1000458', '1000481', 'SOUALAH MOHAMMED', 
    '1901024', '1901000', '1901008', '1901025', '1000478', 'TOUAHRIA SOUAD', 'U PROMEDIC', 'ZAROKA PHARM'
);






select cb.value,cb.name,cb.xx_nif,cb.xx_nis,cb.xx_ai,cb.xx_rc,cb.description,loc.name as name_addr
from c_bpartner cb inner join C_BPartner_Location loc on (cb.c_bpartner_id = loc.c_bpartner_id)
where cb.isactive = 'Y' and cb.AD_Client_ID=1000022 and cb.created between '03/02/2025' and '07/02/2025';
---------------------------------------
      SELECT 
            mati.value AS fournisseur, 
            m.name,  
            SUM(m_storage.qtyonhand) AS qty,
            SUM(M_ATTRIBUTEINSTANCE.valuenumber * m_storage.qtyonhand) AS prix,
            SUM(m_storage.qtyonhand - m_storage.QTYRESERVED) AS qty_dispo, 
            SUM(M_ATTRIBUTEINSTANCE.valuenumber * (m_storage.qtyonhand - m_storage.QTYRESERVED)) AS prix_dispo,
            ml.M_Locator_ID AS locatorid,
            m.m_product_id AS productid,
            1 AS sort_order
        FROM 
            M_ATTRIBUTEINSTANCE
        JOIN 
            m_storage ON m_storage.M_ATTRIBUTEsetINSTANCE_id = M_ATTRIBUTEINSTANCE.M_ATTRIBUTEsetINSTANCE_id
        JOIN 
            M_PRODUCT m ON m.M_PRODUCT_id = m_storage.M_PRODUCT_id
        JOIN 
            M_Locator ml ON ml.M_Locator_ID = m_storage.M_Locator_ID
        INNER JOIN 
            m_attributeinstance mati ON m_storage.m_attributesetinstance_id = mati.m_attributesetinstance_id
        WHERE 
            M_ATTRIBUTEINSTANCE.M_Attribute_ID = 1000504
            AND m_storage.qtyonhand > 0
            AND mati.m_attribute_id = 1000508
            AND m_storage.AD_Client_ID = 1000000
            and ml.VALUE like 'SV'
            -- Dynamically added emplacement filter
        GROUP BY 
            m.name, mati.value, m.m_product_id, ml.M_Locator_ID
        ORDER BY 
            fournisseur, name;
        

                  SELECT ml.value AS EMPLACEMENT
            FROM M_Locator ml
            JOIN M_Warehouse m ON m.M_WAREHOUSE_ID = ml.M_WAREHOUSE_ID
            WHERE m.ISACTIVE = 'Y'
                AND m.AD_Client_ID = 1000000
                AND ml.ISACTIVE = 'Y'
                AND ml.AD_Client_ID = 1000000
            ORDER BY m.value;






    SELECT 
    xf.MOVEMENTDATE,  -- Include the date so it can be filtered later
    SUM(xf.TOTALLINE) AS CHIFFRE, 
    SUM(xf.qtyentered) AS QTY,
    SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION) AS MARGE,
    SUM(xf.CONSOMATION),
    CASE 
        WHEN SUM(xf.CONSOMATION) < 0 
        THEN ROUND(((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / (SUM(xf.CONSOMATION) * -1)), 4)
        ELSE ROUND((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / NULLIF(SUM(xf.CONSOMATION), 0), 4) -- Avoid division by zero
    END AS POURCENTAGE
FROM xx_ca_fournisseur xf
WHERE 
xf.MOVEMENTDATE between '02/02/2025' and '12/02/2025' and 
    xf.AD_Org_ID = 1000000
    AND xf.DOCSTATUS != 'RE'
GROUP BY xf.MOVEMENTDATE  -- Group by date so filtering is possible later in the script
ORDER BY xf.MOVEMENTDATE;


SELECT 
    SUM(xf.TOTALLINE) AS CHIFFRE, 
    SUM(xf.qtyentered) AS QTY,
    SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION) AS MARGE,
    SUM(xf.CONSOMATION) AS CONSOMATION,
    CASE 
        WHEN SUM(xf.CONSOMATION) < 0 
        THEN ROUND(((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / (SUM(xf.CONSOMATION) * -1)), 4)
        ELSE ROUND((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / NULLIF(SUM(xf.CONSOMATION), 0), 4) -- Avoid division by zero
    END AS POURCENTAGE
FROM xx_ca_fournisseur xf
WHERE 
    xf.MOVEMENTDATE BETWEEN '02-02-2025' AND '12-02-2025'
    AND xf.AD_Org_ID = 1000000
    AND xf.DOCSTATUS != 'RE';



---------------------------
 SELECT * FROM (
                    SELECT 
                        CAST(xf.name AS VARCHAR2(300)) AS FOURNISSEUR,   
                        SUM(xf.TOTALLINE) AS total, 
                        SUM(xf.qtyentered) AS QTY,
                            ROUND(
    CASE 
        WHEN SUM(xf.CONSOMATION) = 0 THEN 0
        WHEN SUM(xf.CONSOMATION) < 0 THEN ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION) * -1)*100
        ELSE ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION))*100
    END, 
4) AS marge,
                        0 AS sort_order
                    FROM xx_ca_fournisseur xf
                    JOIN C_BPartner cb ON cb.C_BPartner_ID = xf.CLIENTID
                    JOIN AD_User au ON au.AD_User_ID = xf.SALESREP_ID
                    JOIN C_BPartner_Location bpl ON bpl.C_BPartner_ID = xf.CLIENTID
                    JOIN C_SalesRegion sr ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    JOIN C_ORDER C ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN '02-02-2025' AND '12-02-2025'
                        AND xf.AD_Org_ID = 1000000
                        AND xf.DOCSTATUS != 'RE'
                        AND (:fournisseur IS NULL OR xf.name LIKE :fournisseur || '%')
                    GROUP BY xf.name
                    UNION ALL
                    SELECT 
                        CAST('Total' AS VARCHAR2(300)) AS name, 
                        SUM(xf.TOTALLINE) AS total, 
                        SUM(xf.qtyentered) AS QTY,
                        ROUND(
    CASE 
        WHEN SUM(xf.CONSOMATION) = 0 THEN 0
        WHEN SUM(xf.CONSOMATION) < 0 THEN ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION) * -1)*100
        ELSE ((SUM(xf.TOTALLINE) - SUM(xf.CONSOMATION)) / SUM(xf.CONSOMATION))*100
    END, 
4) AS marge,

                        1 AS sort_order
                    FROM xx_ca_fournisseur xf
                    JOIN C_BPartner cb ON cb.C_BPartner_ID = xf.CLIENTID
                    JOIN AD_User au ON au.AD_User_ID = xf.SALESREP_ID
                    JOIN C_BPartner_Location bpl ON bpl.C_BPartner_ID = xf.CLIENTID
                    JOIN C_SalesRegion sr ON sr.C_SalesRegion_ID = bpl.C_SalesRegion_ID
                    JOIN M_InOut mi ON xf.DOCUMENTNO = mi.DOCUMENTNO
                    JOIN C_ORDER C ON mi.C_ORDER_ID = c.C_ORDER_ID
                    WHERE xf.MOVEMENTDATE BETWEEN  '02-02-2025' AND '12-02-2025'
                        AND xf.AD_Org_ID = 1000000
                        AND xf.DOCSTATUS != 'RE'
                        AND (:fournisseur IS NULL OR xf.name LIKE :fournisseur || '%')
                )
                ORDER BY sort_order, total DESC






                SELECT COUNT(*) 
FROM AD_User au 
JOIN xx_ca_fournisseur xf ON au.AD_User_ID = xf.SALESREP_ID
WHERE xf.MOVEMENTDATE BETWEEN TO_DATE('2024-01-01', 'YYYY-MM-DD') 
AND TO_DATE('2024-01-31', 'YYYY-MM-DD');
