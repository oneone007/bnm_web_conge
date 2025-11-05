-- Active: 1762332277056@@127.0.0.1@3306@bnm
-------------- facture ---------------------------

-- Selected full invoice columns for a specific invoice
SELECT
	orgtrx.NAME                                 AS Organisation_Trx,
	c.TOTALLINES,
	c.GRANDTOTAL,
	doctype.NAME                                AS Code_journal,
	c.DOCSTATUS,
	CASE c.DOCSTATUS
		WHEN 'CL' THEN 'Clôturé'
		WHEN 'CO' THEN 'Achevé'
		WHEN 'DR' THEN 'Brouillon'
		WHEN 'IN' THEN 'Inactif'
		WHEN 'RE' THEN 'Extourné'
		WHEN 'VO' THEN 'Annulé'
		ELSE c.DOCSTATUS
	END                                          AS Statut_document,
	c.DOCACTION,
	bploc.NAME AS "Adresse_du_tiers",

	c.ISPAID,
	c.C_INVOICE_ID,
	client.NAME                                  AS Société,
	org.NAME                                     AS Organisation,
	ord.DOCUMENTNO                               AS Ordre_de_vente,
	c.DATEORDERED,
	c.DOCUMENTNO,
	c.POREFERENCE,
	c.DESCRIPTION,
	doctypetarget.NAME                           AS Type_document,
	c.DATEINVOICED,
	c.DATEACCT,
	partner.NAME                                 AS Client,
	usr.NAME                                     AS Contact,
	pricelist.NAME                               AS Tarif,
	curr.ISO_CODE                                AS Devise,
	salesrep.NAME                                AS Vendeur,
	c.PAYMENTRULE,
	CASE c.PAYMENTRULE
		WHEN 'P' THEN 'A credit'
		WHEN 'B' THEN 'Espece'
		WHEN 'S' THEN 'Cheque'
		WHEN 'D' THEN 'Debit immediat'
		WHEN 'T' THEN 'Virement'
		WHEN 'K' THEN 'Carte de credit'
    WHEN 'V' THEN 'Versement'

		ELSE c.PAYMENTRULE
	END                                          AS PaymentRuleLabel,
	zsub.NAME                                    AS Sous_methode_de_paiement,
	c.ISSELFSERVICE,
	payterm.NAME                                 AS Delai_de_paiement
FROM C_Invoice c
LEFT JOIN AD_Client client               ON client.AD_Client_ID = c.AD_CLIENT_ID
LEFT JOIN AD_Org org                     ON org.AD_Org_ID = c.AD_ORG_ID
LEFT JOIN AD_Org orgtrx                  ON orgtrx.AD_Org_ID = c.AD_ORGTRX_ID
LEFT JOIN C_Order ord                    ON ord.C_Order_ID = c.C_ORDER_ID
LEFT JOIN C_DocType doctype              ON doctype.C_DocType_ID = c.C_DOCTYPE_ID
LEFT JOIN C_DocType doctypetarget        ON doctypetarget.C_DocType_ID = c.C_DOCTYPETARGET_ID
LEFT JOIN C_BPartner partner             ON partner.C_BPartner_ID = c.C_BPARTNER_ID
LEFT JOIN AD_User usr                    ON usr.AD_User_ID = c.AD_USER_ID
LEFT JOIN AD_User salesrep               ON salesrep.AD_User_ID = c.SALESREP_ID
LEFT JOIN M_PriceList pricelist          ON pricelist.M_PriceList_ID = c.M_PRICELIST_ID
LEFT JOIN C_Currency curr                ON curr.C_Currency_ID = c.C_CURRENCY_ID
LEFT JOIN C_PaymentTerm payterm          ON payterm.C_PaymentTerm_ID = c.C_PAYMENTTERM_ID
LEFT JOIN ZSubPaymentRule zsub           ON zsub.ZSubPaymentRule_ID = c.ZSUBPAYMENTRULE_ID
LEFT JOIN C_BPartner_Location bploc ON bploc.C_BPartner_Location_ID = c.C_BPartner_Location_ID

-- Filter by document number (use bind parameter :documentno)
WHERE c.DOCUMENTNO = :documentno;



--------------------------------------------------------FACTURE LINES -------




-----------------
select * from M_AttributeSetInstance where M_AttributeSetInstance_ID = 1538709;

-- Return one sample DOCUMENTNO (and invoice id) for each DOCSTATUS value
SELECT PAYMENTRULE,
	   MIN(C_Invoice_ID) KEEP (DENSE_RANK FIRST ORDER BY C_Invoice_ID) AS sample_invoice_id,
	   MIN(DOCUMENTNO) KEEP (DENSE_RANK FIRST ORDER BY C_Invoice_ID) AS sample_documentno
FROM C_Invoice
WHERE ad_client_id = 1000000
GROUP BY PAYMENTRULE;
-----------------------------

-----------------------------------------------
SELECT
  il.C_InvoiceLine_ID,
  il.A_Asset_ID,
  asset.NAME                             AS Immobilisation,       -- A_Asset.NAME
  il.AD_Client_ID,
  cli.NAME                               AS Société,              -- AD_Client.NAME
  il.AD_Org_ID,
  org.NAME                               AS Organisation,         -- AD_Org.NAME
  il.C_Invoice_ID,
  inv.DOCUMENTNO                         AS Facture,              -- C_Invoice.DOCUMENTNO
  il.Line,

  -- Replaced M_InOutLine_ID with composed delivery info
  NVL(TO_CHAR(iol.Line), '')
    || '_' || NVL(TO_CHAR(iol.MovementQty), '')
    || '_' || NVL(prod.NAME, '')
    || '_' || NVL(io.DOCUMENTNO, '')
    || ' - ' || NVL(TO_CHAR(io.MovementDate, 'DD/MM/YYYY'), '')
    || ' - ' || NVL(ord.DOCUMENTNO, '')
    || ' - ' || NVL(TO_CHAR(ord.DateOrdered, 'DD/MM/YYYY'), '')
    || ' - ' || NVL(bp.NAME, '')
    AS Ligne_livraison,

  -- Replaced C_OrderLine_ID with composed order line info
  NVL(TO_CHAR(col.Line),'')
    || '_' || NVL(prod.NAME,'')
    || '_' || NVL(ord.DOCUMENTNO,'')
    || ' - ' || NVL(TO_CHAR(ord.DATEORDERED,'DD/MM/YYYY'),'')
    || '_' || NVL(TO_CHAR(il.LineNetAmt,'FM999G999G999D00','NLS_NUMERIC_CHARACTERS = ''.,'''),'0,00')
    AS Ligne_commande_de_vente,

  il.M_Product_ID,
  prod.NAME                               AS Article,              -- M_Product.NAME
  il.C_Charge_ID,
  chg.NAME                                AS Charge,               -- C_Charge.NAME
  il.M_AttributeSetInstance_ID,
  masi.DESCRIPTION                        AS Lot,                  -- M_AttributeSetInstance.DESCRIPTION
  il.S_ResourceAssignment_ID,
  il.DESCRIPTION,
  il.QtyInvoiced,
  il.QtyEntered,
  il.C_UOM_ID,
  uom_trl.NAME                            AS Unité,                -- C_UOM_TRL.NAME (localized)
  il.PriceEntered,
  il.PriceActual,
  il.PriceList,
  il.C_Tax_ID,
  tx.NAME                                 AS TVA,                  -- C_TAX.NAME
  il.AD_OrgTrx_ID,
  orgtrx.NAME                             AS Organisation_Trx,     -- AD_Org (trx) NAME
  il.LineNetAmt,
  il.IsDescription,
  il.IsPrinted,
  il.Repricing,
  il.XX_BLInvoiceLine_ID
FROM C_InvoiceLine il
LEFT JOIN C_Invoice                   inv    ON inv.C_Invoice_ID   = il.C_Invoice_ID
LEFT JOIN A_Asset                     asset  ON asset.A_Asset_ID    = il.A_Asset_ID
LEFT JOIN AD_Client                   cli    ON cli.AD_Client_ID   = il.AD_Client_ID
LEFT JOIN AD_Org                      org    ON org.AD_Org_ID      = il.AD_Org_ID
LEFT JOIN M_Product                   prod   ON prod.M_Product_ID  = il.M_Product_ID
LEFT JOIN C_Charge                    chg    ON chg.C_Charge_ID    = il.C_Charge_ID
LEFT JOIN M_AttributeSetInstance      masi   ON masi.M_AttributeSetInstance_ID = il.M_AttributeSetInstance_ID
LEFT JOIN C_UOM                       uom    ON uom.C_UOM_ID      = il.C_UOM_ID
LEFT JOIN C_UOM_TRL                   uom_trl ON uom_trl.C_UOM_ID  = uom.C_UOM_ID
    -- add: AND uom_trl.AD_Language = 'fr_FR' if you want French unit names
LEFT JOIN C_Tax                       tx     ON tx.C_Tax_ID       = il.C_Tax_ID
LEFT JOIN AD_Org                      orgtrx ON orgtrx.AD_Org_ID   = il.AD_OrgTrx_ID
LEFT JOIN C_OrderLine                 col    ON col.C_OrderLine_ID = il.C_OrderLine_ID
LEFT JOIN C_Order                     ord    ON ord.C_Order_ID     = col.C_Order_ID

-- Delivery joins
LEFT JOIN M_InOutLine                 iol    ON iol.M_InOutLine_ID = il.M_InOutLine_ID
LEFT JOIN M_InOut                     io     ON io.M_InOut_ID      = iol.M_InOut_ID
LEFT JOIN C_BPartner                  bp     ON bp.C_BPartner_ID   = io.C_BPartner_ID

WHERE il.C_InvoiceLine_ID = :C_InvoiceLine_ID
ORDER BY il.Line;



--------------download header information societee-----------------------


select  etebac5_senderid as RIB_SOCIETE,
       siren as NAI_SOCIETE,
       siret as NIS_SOCIETE,
       phone as TELE_societe,
        fax as FAX_societe,
        taxid as NIF_SOCIETE,
        duns as NRC_SOCIETE,
        loc.address1 as addresse_societe,
        email as EMAIL_SOCIETE,
        xx_site as WEBSITE_SOCIETE,
        org.description as ACTIVITE_SOCIEETE,
        xx_capital as CAPITAL_SOCIEETE
 from AD_OrgInfo
 LEFT JOIN c_location loc ON loc.c_location_id = AD_OrgInfo.c_location_id
 left join AD_Org org on org.ad_org_id=AD_OrgInfo.ad_org_id
 where AD_OrgInfo.ad_org_id=1000000;


-------------- download header information client-----------------------  
-- -------------- Download Header Information (Client) -----------------------
SELECT
    bp.DESCRIPTION               AS Activite_client,
    bp.XX_RC                     AS RC_client,
    bp.XX_NIF                    AS NIF_client,
    bp.XX_NIS                    AS NIS_client,
    bp.XX_AI                     AS AI_client,
    reg.NAME                     AS Region_de_vente,
    locc.address1                AS Location_ID_client
FROM C_BPartner bp
LEFT JOIN C_BPartner_Location loc ON loc.C_BPartner_ID = bp.C_BPartner_ID

LEFT JOIN C_SalesRegion reg      ON reg.C_SalesRegion_ID = loc.C_SalesRegion_ID
 LEFT JOIN c_location locc ON locc.c_location_id = loc.c_location_id
WHERE bp.C_BPartner_ID = 1116610;





-------------- sales region of bpartner location -----------------------
select loc.c_salesregion_id,
loc.c_bpartner_id,
       reg.NAME as sales_region_name
 from C_BPartner_Location loc
left join C_SalesRegion reg on reg.C_SalesRegion_ID = loc.C_SalesRegion_ID
where loc.C_BPartner_Location_ID = 1108147;

 --




SELECT 
    io.DOCUMENTNO AS Expedition,
    MAX(prod.NAME) AS Article,    -- pick one representative name (same expedition)
    MAX(masi.LOT) AS Lot,
        il.C_InvoiceLine_ID,
    TO_CHAR(MAX(masi.GUARANTEEDATE), 'DD/MM/YYYY') AS Guaranteedate,
    MAX(
        (SELECT valuenumber 
           FROM M_AttributeInstance ai
          WHERE ai.M_AttributeSetInstance_ID = il.M_AttributeSetInstance_ID 
            AND ai.M_Attribute_ID = 1000503)
    ) AS PPA,
    ROUND(AVG(il.pricelist), 2) AS Prix_Unitaire,
    SUM(il.QtyInvoiced) AS Quantite,
    AVG(NVL(col.Discount, 0)) AS Remise

FROM C_InvoiceLine il
LEFT JOIN M_Product prod ON prod.M_Product_ID = il.M_Product_ID
LEFT JOIN C_OrderLine col ON col.C_OrderLine_ID = il.C_OrderLine_ID
LEFT JOIN M_InOutLine iol ON iol.M_InOutLine_ID = il.M_InOutLine_ID
LEFT JOIN M_InOut io ON io.M_InOut_ID = iol.M_InOut_ID
LEFT JOIN C_BPartner bp ON bp.C_BPartner_ID = io.C_BPartner_ID
LEFT JOIN C_Invoice inv ON inv.C_Invoice_ID = il.C_Invoice_ID
LEFT JOIN C_Order ord ON ord.C_Order_ID = col.C_Order_ID
LEFT JOIN M_AttributeSetInstance masi 
       ON masi.M_AttributeSetInstance_ID = il.M_AttributeSetInstance_ID

WHERE io.DOCUMENTNO IS NOT NULL
  AND inv.DOCUMENTNO = :fcture

GROUP BY 
    io.DOCUMENTNO,
    prod.NAME,
    il.C_InvoiceLine_ID
ORDER BY 
    io.DOCUMENTNO,
prod.NAME;

------------------------------------v2-------------------------------
SELECT 
    io.DOCUMENTNO AS Expedition,
    MAX(prod.NAME) AS Article,
    MAX(masi.LOT) AS Lot,
    il.C_InvoiceLine_ID,
    TO_CHAR(MAX(masi.GUARANTEEDATE), 'DD/MM/YYYY') AS Guaranteedate,
    MAX(
        (SELECT valuenumber 
           FROM M_AttributeInstance ai
          WHERE ai.M_AttributeSetInstance_ID = il.M_AttributeSetInstance_ID 
            AND ai.M_Attribute_ID = 1000503)
    ) AS PPA,
    ROUND(AVG(il.pricelist), 2) AS Prix_Unitaire,
    SUM(il.QtyInvoiced) AS Quantite,
    AVG(NVL(col.Discount, 0)) AS Remise,
    MAX(tax.Rate) AS TVA       -- ✅ Fetch TVA (tax rate) from C_Tax

FROM C_InvoiceLine il
LEFT JOIN M_Product prod 
       ON prod.M_Product_ID = il.M_Product_ID
LEFT JOIN C_OrderLine col 
       ON col.C_OrderLine_ID = il.C_OrderLine_ID
LEFT JOIN C_Tax tax 
       ON tax.C_Tax_ID = col.C_Tax_ID             -- ✅ join tax table
LEFT JOIN M_InOutLine iol 
       ON iol.M_InOutLine_ID = il.M_InOutLine_ID
LEFT JOIN M_InOut io 
       ON io.M_InOut_ID = iol.M_InOut_ID
LEFT JOIN C_BPartner bp 
       ON bp.C_BPartner_ID = io.C_BPartner_ID
LEFT JOIN C_Invoice inv 
       ON inv.C_Invoice_ID = il.C_Invoice_ID
LEFT JOIN C_Order ord 
       ON ord.C_Order_ID = col.C_Order_ID
LEFT JOIN M_AttributeSetInstance masi 
       ON masi.M_AttributeSetInstance_ID = il.M_AttributeSetInstance_ID

WHERE io.DOCUMENTNO IS NOT NULL
  AND inv.DOCUMENTNO = :fcture

GROUP BY 
    io.DOCUMENTNO,
    prod.NAME,
    il.C_InvoiceLine_ID

ORDER BY 
    io.DOCUMENTNO,
    prod.NAME;




    UPDATE m_storage
SET  qtyonhand = 35  -- This makes QTY_DISPO = qtyonhand - QTYRESERVED = 0
WHERE m_product_id = 1159288
and   m_attributesetinstance_id=1566473;


    select m_attributesetinstance_id from  m_storage
WHERE m_product_id = 1159288
and   qtyonhand=35;


describe m_storage;


describe inventories;


select CHARGEAMT  as D_timbre from c_invoice where documentno='9926/2025';
SELECT 
    AVG(il.pricelist) AS Prix_Unitaire,
    SUM(il.QtyInvoiced) AS Quantite,
    AVG(NVL(col.Discount, 0)) AS Remise,
    (SELECT MAX(tax.Rate) 
     FROM C_TaxCategory cat 
     LEFT JOIN C_Tax tax ON tax.C_TaxCategory_ID = cat.C_TaxCategory_ID
     WHERE cat.C_TaxCategory_ID = prod.XX_TaxCategory_ID
     AND ROWNUM = 1) AS TVA
FROM C_InvoiceLine il
LEFT JOIN M_Product prod ON prod.M_Product_ID = il.M_Product_ID
LEFT JOIN C_OrderLine col ON col.C_OrderLine_ID = il.C_OrderLine_ID
LEFT JOIN M_InOutLine iol ON iol.M_InOutLine_ID = il.M_InOutLine_ID
LEFT JOIN M_InOut io ON io.M_InOut_ID = iol.M_InOut_ID
LEFT JOIN C_Invoice inv ON inv.C_Invoice_ID = il.C_Invoice_ID
WHERE io.DOCUMENTNO IS NOT NULL
  AND inv.DOCUMENTNO = :fcture
GROUP BY 
    io.DOCUMENTNO,
    prod.NAME,
    prod.XX_TaxCategory_ID,
    il.C_InvoiceLine_ID

