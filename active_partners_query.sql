-- Conditions: Active, IsCustomer, xx_nis is empty, AD_Org_ID=1000000

SELECT
    Name,
    IsActive,
    IsCustomer,
    xx_nis,
    CASE WHEN AD_Org_ID = 1000000 THEN 'SARL BNM PARAPHARM' ELSE TO_CHAR(AD_Org_ID) END AS Organization
FROM C_BPartner
WHERE IsActive = 'Y'
    AND IsCustomer = 'Y'
    AND (xx_nis IS NULL OR xx_nis = '')
    AND AD_Org_ID = 1000000
    AND AD_Client_ID = 1000000
ORDER BY Name;




SELECT
    Name,
    IsActive,
    IsCustomer,
    xx_nis,
    CASE WHEN AD_Org_ID = 1000012 THEN 'facturation' ELSE TO_CHAR(AD_Org_ID) END AS Organization
FROM C_BPartner
WHERE IsActive = 'Y'
    AND IsCustomer = 'Y'
    AND (xx_nis IS NULL OR xx_nis = '')
    AND AD_Org_ID = 1000012
    AND AD_Client_ID = 1000022
ORDER BY Name;