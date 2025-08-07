SELECT SUM(
  CASE 
    WHEN inv.docstatus = 'RE' OR m.docstatus = 'RE' OR io.docstatus = 'RE' 
      THEN s.movementqty - s.movementqty 
    ELSE s.movementqty 
  END
) AS initial_stock
                FROM m_transaction s
                INNER JOIN m_product p ON (s.m_product_id = p.m_product_id)
                INNER JOIN m_locator l ON (l.m_locator_id = s.m_locator_id)
                INNER JOIN M_attributeinstance att ON (att.m_attributesetinstance_id = s.m_attributesetinstance_id)
                LEFT OUTER JOIN M_InventoryLine il
                ON (s.M_InventoryLine_ID=il.M_InventoryLine_ID)
                
                LEFT OUTER JOIN M_Inventory inv
                ON (inv.m_inventory_id = il.m_inventory_id)

                LEFT OUTER JOIN M_MovementLine ml
                ON (s.M_MovementLine_ID=ml.M_MovementLine_ID)

                LEFT OUTER JOIN M_Movement m
                ON (m.M_Movement_ID=ml.M_Movement_ID)

                LEFT OUTER JOIN M_InOutLine iol
                ON (s.M_InOutLine_ID=iol.M_InOutLine_ID)

                LEFT OUTER JOIN M_Inout io
                ON (iol.M_InOut_ID=io.M_InOut_ID)
                
                WHERE s.movementdate < TO_DATE(:target_date, 'YYYY-MM-DD')
                AND p.m_product_id = :product_id
                AND (l.value LIKE 'Préparation%' OR l.value LIKE 'HANGAR%' OR l.value LIKE 'Dépot Hangar réserve%' OR l.value LIKE 'Dépot réserve%')
                AND att.m_attribute_id = 1000508
                AND s.AD_Client_ID = 1000000;





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
                ORDER BY s.movementdate DESC;
