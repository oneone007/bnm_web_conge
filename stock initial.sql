SELECT COALESCE(SUM(CASE 
    WHEN inv.docstatus = 'RE' OR m.docstatus = 'RE' OR io.docstatus = 'RE' 
      THEN s.movementqty - s.movementqty 
    ELSE s.movementqty 
  END), 0) as initial_stock
FROM m_transaction s
INNER JOIN m_product p ON (s.m_product_id = p.m_product_id)
INNER JOIN m_locator l ON (l.m_locator_id = s.m_locator_id)
INNER JOIN M_attributeinstance att ON (att.m_attributesetinstance_id = s.m_attributesetinstance_id)
LEFT JOIN m_inoutline iol ON (s.m_inoutline_id = iol.m_inoutline_id)
LEFT JOIN m_inout io ON (iol.m_inout_id = io.m_inout_id)
LEFT JOIN c_bpartner bp ON (io.c_bpartner_id = bp.c_bpartner_id)
LEFT JOIN m_inout inv ON (s.m_inout_id = inv.m_inout_id)
LEFT JOIN m_inventory m ON (s.m_inventory_id = m.m_inventory_id)
WHERE s.movementdate < TO_DATE(:target_date, 'YYYY-MM-DD')
AND p.m_product_id = :product_id
AND (l.value LIKE 'Préparation%' OR l.value LIKE 'HANGAR%' OR l.value LIKE 'Dépot Hangar réserve%' OR l.value LIKE 'Dépot réserve%')
AND att.m_attribute_id = 1000508
AND s.AD_Client_ID = 1000000
AND io.docstatus IN ('CO', 'CL')
ORDER BY s.movementdate DESC;




                SELECT 
                s.movementqty,
                s.movementdate,
                p.name AS product_name,
                p.value AS product_code,
                l.value AS location_name,
                io.documentno,
                io.docstatus,
                io.description AS document_description,
                bp.name AS business_partner_name,
                att.value AS lot
                FROM m_transaction s
                INNER JOIN m_product p ON (s.m_product_id = p.m_product_id)
                INNER JOIN m_locator l ON (l.m_locator_id = s.m_locator_id)
                INNER JOIN M_attributeinstance att ON (att.m_attributesetinstance_id = s.m_attributesetinstance_id)
                LEFT JOIN m_inoutline iol ON (s.m_inoutline_id = iol.m_inoutline_id)
                LEFT JOIN m_inout io ON (iol.m_inout_id = io.m_inout_id)
                LEFT JOIN c_bpartner bp ON (io.c_bpartner_id = bp.c_bpartner_id)
                
                WHERE s.movementdate < TO_DATE(:target_date, 'YYYY-MM-DD')
                AND p.m_product_id = :product_id
                AND (l.value LIKE 'Préparation%' OR l.value LIKE 'HANGAR%' OR l.value LIKE 'Dépot Hangar réserve%' OR l.value LIKE 'Dépot réserve%')
                AND att.m_attribute_id = 1000508
                AND s.AD_Client_ID = 1000000
                AND io.docstatus IN ('CO', 'CL')
                ORDER BY s.movementdate DESC;




                SELECT 
                COALESCE(SUM(s.movementqty), 0) as initial_stock
                FROM m_transaction s
                INNER JOIN m_product p ON (s.m_product_id = p.m_product_id)
                INNER JOIN m_locator l ON (l.m_locator_id = s.m_locator_id)
                INNER JOIN M_attributeinstance att ON (att.m_attributesetinstance_id = s.m_attributesetinstance_id)
                LEFT JOIN m_inoutline iol ON (s.m_inoutline_id = iol.m_inoutline_id)
                LEFT JOIN m_inout io ON (iol.m_inout_id = io.m_inout_id)
                LEFT JOIN c_bpartner bp ON (io.c_bpartner_id = bp.c_bpartner_id)
                
                WHERE s.movementdate < TO_DATE(:target_date, 'YYYY-MM-DD')
                AND p.m_product_id = :product_id
                AND (l.value LIKE 'Préparation%' OR l.value LIKE 'HANGAR%' OR l.value LIKE 'Dépot Hangar réserve%' OR l.value LIKE 'Dépot réserve%')
                AND att.m_attribute_id = 1000508
                AND s.AD_Client_ID = 1000000
                AND io.docstatus IN ('CO', 'CL')
                ORDER BY s.movementdate DESC;