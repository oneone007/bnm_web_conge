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
WITH Latest_Purchase AS (
    SELECT 
        cl.M_Product_ID,
        SUM(cl.qtyentered) AS last_purchase_qty,
        c.dateinvoiced,
        ROW_NUMBER() OVER (PARTITION BY cl.M_Product_ID ORDER BY c.dateinvoiced DESC) AS rn
    FROM 
        C_InvoiceLine cl
    JOIN 
        C_Invoice c ON c.C_Invoice_id = cl.C_Invoice_id
        join M_INOUTLINE ml on ml.M_INOUTLINE_id = cl.M_INOUTLINE_ID
         
    WHERE 
        c.dateinvoiced BETWEEN TO_DATE('01/01/2020', 'DD/MM/YYYY') AND SYSDATE
        AND c.AD_Client_ID = 1000000
        AND c.AD_Org_ID = 1000000
        AND c.ISSOTRX = 'N'
        AND c.DOCSTATUS in ('CO','CL')
        and ml.M_Locator_ID!=1001020
    GROUP BY 
        cl.M_Product_ID, c.dateinvoiced
),
Filtered_Latest_Purchase AS (
    SELECT 
        M_Product_ID,
        last_purchase_qty,
        dateinvoiced
    FROM 
        Latest_Purchase
    WHERE 
        rn = 1
),
On_Hand_Quantity AS (
    SELECT  
        m.M_Product_ID as midp,
        m.name AS product_name,
        SUM(s.QTYONHAND) - SUM(s.QTYRESERVED) AS QTYONHAND
    FROM 
        m_product m
    JOIN 
        m_storage s ON s.M_PRODUCT_ID = m.M_PRODUCT_ID
    JOIN 
        M_Locator ml ON ml.M_Locator_ID = s.M_Locator_ID
    WHERE 
        s.AD_Client_ID = 1000000
        AND m.AD_Client_ID = 1000000
        AND s.M_Locator_ID IN (1001135, 1000614, 1001128,1001136)
        AND m.name LIKE 'ABC BOITE A PHARMACIE GM  (10 PAS DE VRAC)'
    GROUP BY 
        m.M_Product_ID, m.name
),
Stock_Principale AS (
    SELECT 
        ROUND(SUM(M_ATTRIBUTEINSTANCE.valuenumber * (m_storage.qtyonhand-m_storage.QTYRESERVED)), 2) AS stock_principale
    FROM 
        M_ATTRIBUTEINSTANCE
    JOIN 
        m_storage ON m_storage.M_ATTRIBUTEsetINSTANCE_id = M_ATTRIBUTEINSTANCE.M_ATTRIBUTEsetINSTANCE_id
    WHERE 
        M_ATTRIBUTEINSTANCE.M_Attribute_ID = 1000504
        AND m_storage.qtyonhand > 0
        AND m_storage.M_Locator_ID IN (1001135, 1000614, 1001128,1001136)
        AND m_storage.M_Product_ID IN (SELECT M_Product_ID FROM M_Product WHERE name LIKE 'ABC BOITE A PHARMACIE GM  (10 PAS DE VRAC)')
)
SELECT 
    oq.midp,
    oq.product_name,
    oq.QTYONHAND AS "QTY DISPO",
    COALESCE(fp.last_purchase_qty, 0) AS "DERNIER ACHAT",
    fp.dateinvoiced AS "DATE",
    sp.stock_principale AS "valeur"
FROM 
    On_Hand_Quantity oq
LEFT JOIN 
    Filtered_Latest_Purchase fp ON oq.midp = fp.M_Product_ID
CROSS JOIN 
    Stock_Principale sp
ORDER BY 
    oq.product_name
;

----------------------------
SELECT * FROM c_order WHERE c_order_id = '3216369';














select ml.M_LOCATOR_ID, ml.value AS EMPLACEMENT
from M_Locator ml
join M_Warehouse m on m.M_WAREHOUSE_ID=ml.M_WAREHOUSE_ID

and m.ISACTIVE ='Y'
and m.AD_Client_ID = 1000000
and ml.ISACTIVE = 'Y'
and ml.AD_Client_ID = 1000000
order by m.value;





SELECT C_SalesRegion_id from C_SalesRegion;
-------------------------







SELECT s.sid, s.serial#, s.username
FROM v$session s
WHERE s.blocking_session IS NOT NULL;




SELECT 
                    ROUND(SUM(SoldeFact + SoldeBL), 2) AS credit_client
                FROM (
                    SELECT 
                        bp.c_bpartner_id, 
                        (
                            SELECT COALESCE(SUM(invoiceOpen(inv.C_Invoice_ID, 0)), 0) 
                            FROM C_Invoice inv 
                            WHERE bp.c_bpartner_id = inv.c_bpartner_id 
                            AND paymentTermDueDays(inv.C_PAYMENTTERM_ID, inv.DATEINVOICED, TO_DATE('01/01/3000', 'DD/MM/YYYY')) >= 0
                            AND inv.docstatus IN ('CO', 'CL') 
                            AND inv.AD_ORGTRX_ID = inv.ad_org_id 
                            AND inv.ad_client_id = 1000000
                            AND inv.C_PaymentTerm_ID != 1000000
                        ) AS SoldeFact,
                        (
                            SELECT COALESCE(SUM(invoiceOpen(inv.C_Invoice_ID, 0)), 0) 
                            FROM C_Invoice inv 
                            WHERE bp.c_bpartner_id = inv.c_bpartner_id 
                            AND paymentTermDueDays(inv.C_PAYMENTTERM_ID, inv.DATEINVOICED, TO_DATE('01/01/3000', 'DD/MM/YYYY')) >= 0
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
                        AND bp.C_BP_Group_ID IN (1000003, 1000926, 1001330)
                        AND bp.isactive = 'Y' 
                        AND bp.NAME not like '%MBN%'

                        AND sr.isactive = 'Y'
                        AND bp.C_PaymentTerm_ID != 1000000
                        AND bpl.c_salesregion_id IN (
                            101, 102, 1000032, 1001776, 1001777, 1001778, 1001779, 1001780, 1001781,
                            1001782, 1001783, 1001784, 1001785, 1001786, 1001787, 1001788, 1001789,
                            1001790, 1001791, 1001792, 1001793, 1001794, 1002076, 1002077, 1002078,
                            1002079, 1002080, 1002176, 1002177, 1002178, 1002179, 1002180, 1002181,
                            1002283, 1002285, 1002286, 1002287, 1002288
                        )
                ) subquery;
                

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
                 'DIFFÉRENCE' as DESCRIPTION,
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
                
SELECT cb.value,cb.name, cbl.name
FROM c_bpartner cb
JOIN c_bpartner_location cbl ON cb.c_bpartner_id = cbl.c_bpartner_id
WHERE cbl.name LIKE '%Eulma%';
