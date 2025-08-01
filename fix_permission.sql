-- SQL Fix for SPK Permission Issue
-- Run this in your database management tool

-- 1. Check current permissions
SELECT USERID, L1, IsOtorisasi1, HASACCESS, ISTAMBAH, ISKOREKSI 
FROM DBFLMENU 
WHERE USERID = 'adminkarir' AND L1 IN ('02002', '08103')
ORDER BY L1;

-- 2. Force update SPK permission to match Memorial
UPDATE DBFLMENU 
SET IsOtorisasi1 = 1,
    HASACCESS = 1,
    ISTAMBAH = 1,
    ISKOREKSI = 1,
    ISHAPUS = 1,
    ISCETAK = 1,
    ISEXPORT = 1
WHERE USERID = 'adminkarir' AND L1 = '08103';

-- 3. Verify the update
SELECT USERID, L1, IsOtorisasi1, HASACCESS, ISTAMBAH, ISKOREKSI 
FROM DBFLMENU 
WHERE USERID = 'adminkarir' AND L1 IN ('02002', '08103')
ORDER BY L1;

-- 4. If no SPK record exists, insert it
IF NOT EXISTS (SELECT 1 FROM DBFLMENU WHERE USERID = 'adminkarir' AND L1 = '08103')
BEGIN
    INSERT INTO DBFLMENU (USERID, L1, HASACCESS, ISTAMBAH, ISKOREKSI, ISHAPUS, ISCETAK, ISEXPORT, IsOtorisasi1, IsOtorisasi2, IsOtorisasi3, IsOtorisasi4, IsOtorisasi5, TIPE, IsBatal, pembatalan)
    VALUES ('adminkarir', '08103', 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 'PRD', 0, NULL);
END
