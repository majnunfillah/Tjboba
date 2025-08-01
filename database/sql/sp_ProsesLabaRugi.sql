CREATE PROCEDURE [dbo].[sp_ProsesLabaRugi]
    @Bulan int,
    @Tahun int,
    @IsHPPRL bit,
    @Tanggal datetime,
    @IDUser varchar(50),
    @Devisi varchar(10)
AS
BEGIN
    SET NOCOUNT ON;

    -- Clear existing data
    DELETE FROM dbLRHPP 
    WHERE Tahun = @Tahun 
    AND Bulan = @Bulan 
    AND Devisi = @Devisi 
    AND IsLRHPP = @IsHPPRL;

    -- Insert header accounts
    INSERT INTO dbLRHPP (
        Nomor, Perkiraan, Keterangan, Tipe, Tanda,
        Jumlah, Persen, Tahun, Bulan, Tampil,
        TotalA, TotalB, TotalC, Grup, IsLRHPP,
        Devisi
    )
    SELECT 
        Nomor, Perkiraan, Keterangan, Tipe, Tanda,
        Jumlah, Persen, @Tahun, @Bulan, Tampil,
        0, 0, 0, Grup, @IsHPPRL,
        @Devisi
    FROM dbLRHPPMaster
    WHERE IsHPPRL = @IsHPPRL;

    -- Calculate totals
    WITH AccountTotals AS (
        SELECT 
            p.Perkiraan,
            ISNULL(SUM(CASE 
                WHEN n.DK = 1 THEN n.AkhirD - n.AkhirK
                ELSE n.AkhirK - n.AkhirD
            END), 0) as Total
        FROM dbPerkiraan p
        LEFT JOIN dbNeraca n ON p.Perkiraan = n.Perkiraan
        WHERE n.Bulan = @Bulan 
        AND n.Tahun = @Tahun
        AND (n.Devisi = @Devisi OR @Devisi = '')
        GROUP BY p.Perkiraan
    )
    UPDATE l
    SET TotalA = CASE 
            WHEN l.Tanda = '-' THEN -at.Total
            ELSE at.Total
        END
    FROM dbLRHPP l
    INNER JOIN AccountTotals at ON l.Perkiraan = at.Perkiraan
    WHERE l.Tahun = @Tahun 
    AND l.Bulan = @Bulan
    AND l.Devisi = @Devisi
    AND l.IsLRHPP = @IsHPPRL;

    -- Calculate subtotals
    WITH SubTotals AS (
        SELECT 
            Grup,
            SUM(TotalA) as SubTotal
        FROM dbLRHPP
        WHERE Tahun = @Tahun 
        AND Bulan = @Bulan
        AND Devisi = @Devisi
        AND IsLRHPP = @IsHPPRL
        AND Jumlah = ''
        GROUP BY Grup
    )
    UPDATE l
    SET TotalA = st.SubTotal
    FROM dbLRHPP l
    INNER JOIN SubTotals st ON l.Grup = st.Grup
    WHERE l.Tahun = @Tahun 
    AND l.Bulan = @Bulan
    AND l.Devisi = @Devisi
    AND l.IsLRHPP = @IsHPPRL
    AND l.Jumlah = 'S';

    -- Calculate grand totals
    WITH GrandTotals AS (
        SELECT 
            SUM(CASE WHEN Jumlah = 'S' THEN TotalA ELSE 0 END) as GrandTotal
        FROM dbLRHPP
        WHERE Tahun = @Tahun 
        AND Bulan = @Bulan
        AND Devisi = @Devisi
        AND IsLRHPP = @IsHPPRL
    )
    UPDATE l
    SET TotalA = gt.GrandTotal
    FROM dbLRHPP l
    CROSS JOIN GrandTotals gt
    WHERE l.Tahun = @Tahun 
    AND l.Bulan = @Bulan
    AND l.Devisi = @Devisi
    AND l.IsLRHPP = @IsHPPRL
    AND l.Jumlah = 'T';

    -- Create journal entries if this is profit/loss calculation
    IF @IsHPPRL = 1
    BEGIN
        DECLARE @TotalRL decimal(18,2);
        
        SELECT @TotalRL = TotalA
        FROM dbLRHPP
        WHERE Tahun = @Tahun 
        AND Bulan = @Bulan
        AND Devisi = @Devisi
        AND IsLRHPP = 1
        AND Jumlah = 'T';

        IF @TotalRL <> 0
        BEGIN
            -- Generate journal entries for profit/loss
            EXEC sp_CreateRLJournal 
                @Bulan,
                @Tahun,
                @Devisi,
                @TotalRL,
                @Tanggal,
                @IDUser;
        END;
    END;
END; 