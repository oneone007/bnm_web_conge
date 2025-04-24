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