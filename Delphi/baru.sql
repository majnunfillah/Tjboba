
ALTER PROCEDURE [dbo].[Sp_InsertTglInvoiceSPK] 
@tglawal datetime,
@tglakhir Datetime
AS
BEGIN
    SET NOCOUNT ON
    
    -- Step 1: Truncate table (sama seperti asli)
    TRUNCATE TABLE TempRepSPKInvoice
    
    -- Step 2: Build nospkinvoice data (sama seperti asli dengan CTE)
    ;WITH nospkinvoice AS (
        SELECT DISTINCT d.NoSPK,
        DATEADD(month, DATEDIFF(month, 0, b.TANGGAL), 0) as tgl
        FROM dbInvoicePLDet a
        LEFT OUTER JOIN dbinvoicepl b ON b.nobukti = a.nobukti
        LEFT OUTER JOIN dbSPBDet c ON c.NoBukti = a.NoSPB AND c.Urut = a.UrutSPB 
        LEFT OUTER JOIN DBHASILPRDDET d ON d.NoBukti = c.NoSPP AND d.urut = c.UrutSPP 
        WHERE b.tanggal BETWEEN @tglawal AND @tglakhir
    )
    
    -- Step 3: Insert main data (sama seperti asli)
    INSERT INTO TempRepSPKInvoice (NoSPK, Tanggal, TglInvoiceTerakhir, TglAwalReport, TglAkhirReport, rpwip, UrutPrd)
    SELECT 
        a.nospk,
        a.Tanggal,
        b.tgl, 
        @tglawal,
        @tglakhir,
        a.wip,
        CAST(ROW_NUMBER() OVER(PARTITION BY a.nospk ORDER BY a.tanggal) AS int)
    FROM DbWipMasuk a
    LEFT OUTER JOIN nospkinvoice b ON b.NoSPK = a.Nospk AND b.tgl = a.Tanggal 
    WHERE a.nospk IN (SELECT nospk FROM nospkinvoice)
    
    -- Step 4: Process TglHpdTerakhir (menggunakan cursor seperti asli)
    DECLARE @nospk varchar(30), @tglwip datetime, @tglawalreport datetime, @tglakhirreport datetime
    DECLARE @tglinvoiceterakhir datetime, @tglhpdterakhir datetime, @tglinv datetime

    DECLARE CurrHslPrd CURSOR FOR
        SELECT nospk, tanggal, tglawalreport, tglakhirreport, TglInvoiceTerakhir 
        FROM TempRepSPKInvoice a 
        WHERE TglHpdTerakhir IS NULL 
        ORDER BY a.nospk, tanggal
    
    OPEN CurrHslPrd
    FETCH NEXT FROM CurrHslPrd INTO @nospk, @tglwip, @tglawalreport, @tglakhirreport, @TglInvoiceTerakhir
    
    WHILE @@FETCH_STATUS = 0
    BEGIN
        IF NOT EXISTS(
            SELECT b.TANGGAL 
            FROM DBHASILPRDDET a
            LEFT OUTER JOIN DBHASILPRD b ON b.NOBUKTI = a.NOBUKTI
            WHERE a.NoSPK = @nospk 
            AND YEAR(b.TANGGAL) = YEAR(@tglwip) 
            AND MONTH(b.TANGGAL) = MONTH(@tglwip)
        )
        BEGIN
            -- Update menggunakan MIN date (sama seperti asli)
            UPDATE TempRepSPKInvoice 
            SET TglHpdTerakhir = b.tglhpd
            FROM TempRepSPKInvoice a
            LEFT OUTER JOIN (
                SELECT NoSPK, MIN(DATEADD(month, DATEDIFF(month, 0, c.TANGGAL), 0)) as tglhpd 
                FROM DBHASILPRDDET b 
                LEFT OUTER JOIN DBHASILPRD c ON c.NOBUKTI = b.NOBUKTI 
                LEFT OUTER JOIN dbSPBDet d ON d.NoSPP = b.NOBUKTI AND d.UrutSPP = b.urut
                LEFT OUTER JOIN DBinvoicepldet e ON e.NoSPB = d.NoBukti AND e.UrutSPB = d.Urut
                LEFT OUTER JOIN DBinvoicepl f ON f.NOBUKTI = e.NOBUKTI 
                WHERE b.NoSPK = @nospk AND f.TANGGAL <= @tglakhirreport	
                GROUP BY NoSPK
            ) b ON b.NoSPK = a.NoSPK 
            WHERE a.NoSPK = @nospk AND a.tanggal = @tglwip
        END 
        ELSE
        BEGIN
            -- Update menggunakan exact date match (sama seperti asli)
            UPDATE TempRepSPKInvoice 
            SET TglHpdTerakhir = b.tglhpd
            FROM TempRepSPKInvoice a
            LEFT OUTER JOIN (
                SELECT NoSPK, (DATEADD(month, DATEDIFF(month, 0, c.TANGGAL), 0)) as tglhpd 
                FROM DBHASILPRDDET b 
                LEFT OUTER JOIN DBHASILPRD c ON c.NOBUKTI = b.NOBUKTI 
                LEFT OUTER JOIN dbSPBDet d ON d.NoSPP = b.NOBUKTI AND d.UrutSPP = b.urut
                LEFT OUTER JOIN DBinvoicepldet e ON e.NoSPB = d.NoBukti AND e.UrutSPB = d.Urut
                LEFT OUTER JOIN DBinvoicepl f ON f.NOBUKTI = e.NOBUKTI 
                WHERE b.NoSPK = @nospk AND f.TANGGAL <= @tglakhirreport	
            ) b ON b.NoSPK = a.NoSPK 
            WHERE a.NoSPK = @nospk AND a.tanggal = b.tglhpd
        END 
        
        FETCH NEXT FROM CurrHslPrd INTO @nospk, @tglwip, @tglawalreport, @tglakhirreport, @TglInvoiceTerakhir
    END 
    
    CLOSE CurrHslPrd
    DEALLOCATE CurrHslPrd

    -- Step 5: Process invoice dates (menggunakan cursor seperti asli)
    DECLARE CurrHslPrd CURSOR FOR
        SELECT NoSPK, DATEADD(month, DATEDIFF(month, 0, a.Tanggal), 0) as tlginv
        FROM dbInvoicePLDet b
        LEFT OUTER JOIN dbinvoicepl a ON a.NoBukti = b.NoBukti 
        LEFT OUTER JOIN dbSPBDet c ON c.nobukti = b.nospb AND c.Urut = b.UrutSPB 
        LEFT OUTER JOIN DBHASILPRDDET d ON d.nobukti = c.NoSPP AND c.Urut = c.UrutSPP  
        WHERE d.NoSPK IN (SELECT NoSPK FROM TempRepSPKInvoice)
        GROUP BY NoSPK, DATEADD(month, DATEDIFF(month, 0, a.Tanggal), 0)
    
    OPEN CurrHslPrd
    FETCH NEXT FROM CurrHslPrd INTO @nospk, @tglinv
    
    WHILE @@FETCH_STATUS = 0
    BEGIN
        UPDATE TempRepSPKInvoice 
        SET TglInvoiceTerakhir = @tglinv   
        WHERE NoSPK = @nospk 
        AND TglInvoiceTerakhir IS NULL 
        AND TglHpdTerakhir <= @tglinv

        FETCH NEXT FROM CurrHslPrd INTO @nospk, @tglinv
    END 
    
    CLOSE CurrHslPrd
    DEALLOCATE CurrHslPrd

    -- Step 6: Update TglAwalSPK (sama seperti asli)
    UPDATE TempRepSPKInvoice 
    SET tglawalspk = Tanggal, urutproses = 6  
    WHERE TglHpdTerakhir = TglInvoiceTerakhir 
    AND TGlAwalSPK IS NULL
    AND TglInvoiceTerakhir >= TglAwalReport

    UPDATE TempRepSPKInvoice 
    SET tglawalspk = tanggal, urutproses = 7 
    WHERE TGlAwalSPK IS NULL
    AND TglInvoiceTerakhir >= TglAwalReport 

    -- Step 7: Update NoSO (sama seperti asli)
    UPDATE TempRepSPKInvoice 
    SET noso = b.NoSO 
    FROM TempRepSPKInvoice a
    LEFT OUTER JOIN DBSPK b ON b.NOBUKTI = a.NoSPK 
    
    -- Step 8: Update WIP susulan (sama seperti asli)
    UPDATE TempRepSPKInvoice 
    SET rpwipsusulan = RpWip  
    WHERE Tanggal > dbo.TglSelesaiSPKInvoice(NoSPK) 
    AND Tanggal > TglInvoiceTerakhir 

    -- Step 9: Update TglSelesaiSPK (tambahan dari baru.sql)
    UPDATE TempRepSPKInvoice 
    SET TglSelesaiSPK = dbo.fn_TanggalSelesaiSPK(nospk)
    
    SET NOCOUNT OFF
END
