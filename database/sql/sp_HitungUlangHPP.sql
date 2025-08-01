-- =============================================
-- Stored Procedure: sp_HitungUlangHPP
-- Description: Hitung Ulang Harga Pokok Persediaan
-- Based on Delphi FrmRata2 logic
-- =============================================

CREATE PROCEDURE sp_HitungUlangHPP
    @Bulan INT,
    @Tahun INT,
    @KodeBrg1 VARCHAR(25) = NULL,
    @KodeBrg2 VARCHAR(25) = NULL,
    @IDUser VARCHAR(30)
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @SQLWhere NVARCHAR(MAX)
    DECLARE @SQL NVARCHAR(MAX)
    
    -- Clear previous stock minus data
    DELETE FROM TempStockMinus WHERE IDUser = @IDUser
    
    -- Initialize stock data
    SET @SQL = 'UPDATE dbStockBrg SET 
        HRGPBL = 0, HRGRPB = 0, HRGPNJ = 0, HRGRPJ = 0, 
        HRGADI = 0, HRGMADI = 0, HRGADO = 0, HRGMADO = 0,
        HRGUKI = 0, HRGUKO = 0, HRGTRI = 0, HRGTRO = 0, 
        HRGPMK = 0, HRGRPK = 0, HRGHPrd = 0
        WHERE Bulan = ' + CAST(@Bulan AS VARCHAR(2)) + ' AND Tahun = ' + CAST(@Tahun AS VARCHAR(4))
    
    IF @KodeBrg1 IS NOT NULL AND @KodeBrg2 IS NOT NULL
    BEGIN
        SET @SQL = @SQL + ' AND (KodeBrg BETWEEN ''' + @KodeBrg1 + ''' AND ''' + @KodeBrg2 + ''')'
    END
    
    EXEC sp_executesql @SQL
    
    -- Process materials (ProsesBahan equivalent)
    EXEC sp_ProsesBahanHPP @Bulan, @Tahun, @KodeBrg1, @KodeBrg2, @IDUser
    
    -- Update HPP to transactions (InHPPtoTRS equivalent)
    EXEC sp_UpdateHPPtoTransactions @Bulan, @Tahun, @KodeBrg1, @KodeBrg2
    
    -- Process packaging changes (ProsesKemasan equivalent)
    EXEC sp_ProsesKemasanHPP @Bulan, @Tahun
    
    -- Process end of month (ProsesAkhirBulan equivalent)
    EXEC sp_ProsesAkhirBulanHPP @Bulan, @Tahun, @KodeBrg1, @KodeBrg2
    
    SET NOCOUNT OFF;
END
GO

-- =============================================
-- Stored Procedure: sp_ProsesBahanHPP
-- Description: Process materials for HPP calculation
-- =============================================

CREATE PROCEDURE sp_ProsesBahanHPP
    @Bulan INT,
    @Tahun INT,
    @KodeBrg1 VARCHAR(25),
    @KodeBrg2 VARCHAR(25),
    @IDUser VARCHAR(30)
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @KodeBrg VARCHAR(25)
    DECLARE @NamaBrg VARCHAR(100)
    DECLARE @UrutStockMinus INT = 0
    
    -- Get distinct items to process
    DECLARE item_cursor CURSOR FOR
    SELECT DISTINCT a.KodeBrg, b.NamaBrg
    FROM vwKartuStock a
    LEFT OUTER JOIN dbBarang b ON b.KodeBrg = a.KodeBrg
    WHERE a.Tahun = @Tahun AND a.Bulan = @Bulan
    AND (@KodeBrg1 IS NULL OR (a.KodeBrg BETWEEN @KodeBrg1 AND @KodeBrg2))
    ORDER BY a.KodeBrg
    
    OPEN item_cursor
    FETCH NEXT FROM item_cursor INTO @KodeBrg, @NamaBrg
    
    WHILE @@FETCH_STATUS = 0
    BEGIN
        -- Process each item
        EXEC sp_ProsesItemHPP @Bulan, @Tahun, @KodeBrg, @IDUser, @UrutStockMinus OUTPUT
        
        FETCH NEXT FROM item_cursor INTO @KodeBrg, @NamaBrg
    END
    
    CLOSE item_cursor
    DEALLOCATE item_cursor
    
    SET NOCOUNT OFF;
END
GO

-- =============================================
-- Stored Procedure: sp_ProsesItemHPP
-- Description: Process individual item for HPP calculation
-- =============================================

CREATE PROCEDURE sp_ProsesItemHPP
    @Bulan INT,
    @Tahun INT,
    @KodeBrg VARCHAR(25),
    @IDUser VARCHAR(30),
    @UrutStockMinus INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @KodeGdg VARCHAR(15)
    DECLARE @Saldo DECIMAL(18,2) = 0
    DECLARE @SaldoRp DECIMAL(18,2) = 0
    DECLARE @HrgRata DECIMAL(18,2) = 0
    DECLARE @Tipe VARCHAR(10)
    DECLARE @QntSaldo DECIMAL(18,2)
    DECLARE @HrgSaldo DECIMAL(18,2)
    DECLARE @NoBukti VARCHAR(20)
    DECLARE @Urut INT
    DECLARE @Tanggal DATETIME
    DECLARE @Prioritas VARCHAR(10)
    
    -- Clear temporary table
    DELETE FROM dbTempRata2
    
    -- Get stock card data for the item
    DECLARE stock_cursor CURSOR FOR
    SELECT MyTipe, Prioritas, KodeBrg, KodeGdg, QntSaldo, HrgSaldo, 
           Tanggal, NoBukti, Urut
    FROM vwKartuStock
    WHERE Bulan = @Bulan AND Tahun = @Tahun AND KodeBrg = @KodeBrg
    ORDER BY Tanggal, Prioritas, NoBukti, Urut, 
             CASE WHEN MyTipe = 'TRO' OR Tipe = 'PBO' THEN 0 ELSE 1 END
    
    OPEN stock_cursor
    FETCH NEXT FROM stock_cursor INTO @Tipe, @Prioritas, @KodeBrg, @KodeGdg, 
                                      @QntSaldo, @HrgSaldo, @Tanggal, @NoBukti, @Urut
    
    WHILE @@FETCH_STATUS = 0
    BEGIN
        -- Process warehouse change
        IF @KodeGdg != @KodeGdg
        BEGIN
            -- Save previous warehouse data
            IF @KodeGdg != ''
            BEGIN
                IF EXISTS (SELECT 1 FROM dbTempRata2 WHERE KodeGdg = @KodeGdg)
                BEGIN
                    UPDATE dbTempRata2 
                    SET QntSaldo = @Saldo, HrgSaldo = @SaldoRp
                    WHERE KodeGdg = @KodeGdg
                END
                ELSE
                BEGIN
                    INSERT INTO dbTempRata2 (KodeGdg, QntSaldo, HrgSaldo)
                    VALUES (@KodeGdg, @Saldo, @SaldoRp)
                END
            END
            
            -- Load new warehouse data
            IF @KodeGdg = ''
            BEGIN
                SET @KodeGdg = @KodeGdg
            END
            ELSE
            BEGIN
                SET @KodeGdg = @KodeGdg
                SELECT @HrgRata = HrgRata, @Saldo = QntSaldo, @SaldoRp = HrgSaldo
                FROM dbTempRata2 
                WHERE KodeGdg = @KodeGdg
                
                IF @@ROWCOUNT = 0
                BEGIN
                    SET @HrgRata = 0
                    SET @Saldo = 0
                    SET @SaldoRp = 0
                END
            END
        END
        
        -- Process transaction based on type
        IF @Tipe IN ('AWL', 'PBL', 'ADI', 'TRI', 'UKI', 'RPK', 'PBI')
        BEGIN
            -- Incoming transactions
            SET @Saldo = @Saldo + @QntSaldo
            SET @SaldoRp = @SaldoRp + @HrgSaldo
            
            IF @Saldo != 0
                SET @HrgRata = ROUND(@SaldoRp / @Saldo, 0)
            ELSE
                SET @HrgRata = 0
        END
        ELSE
        BEGIN
            -- Outgoing transactions
            IF @Saldo != 0
                SET @HrgRata = ROUND(@SaldoRp / @Saldo, 0)
            ELSE
                SET @HrgRata = 0
                
            SET @Saldo = @Saldo + @QntSaldo
            SET @SaldoRp = @SaldoRp + ROUND(@QntSaldo * @HrgRata, 0)
        END
        
        -- Check for negative stock
        IF ROUND(@Saldo, 4) < 0
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM TempStockMinus WHERE IDUser = @IDUser AND KodeBrg = @KodeBrg)
            BEGIN
                SET @UrutStockMinus = @UrutStockMinus + 1
                INSERT INTO TempStockMinus (IDUser, Urut, JenisBahan, KodeGdg, KodeBrg, KodeBng, KodeJenis, KodeWarna)
                VALUES (@IDUser, @UrutStockMinus, '', @KodeGdg, @KodeBrg, '', '', @Tipe)
            END
        END
        
        FETCH NEXT FROM stock_cursor INTO @Tipe, @Prioritas, @KodeBrg, @KodeGdg, 
                                          @QntSaldo, @HrgSaldo, @Tanggal, @NoBukti, @Urut
    END
    
    CLOSE stock_cursor
    DEALLOCATE stock_cursor
    
    SET NOCOUNT OFF;
END
GO

-- =============================================
-- Stored Procedure: sp_UpdateHPPtoTransactions
-- Description: Update HPP values in transaction tables
-- =============================================

CREATE PROCEDURE sp_UpdateHPPtoTransactions
    @Bulan INT,
    @Tahun INT,
    @KodeBrg1 VARCHAR(25),
    @KodeBrg2 VARCHAR(25)
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Update dbStockBrg with calculated values
    UPDATE dbStockBrg SET 
        QntPBL = ISNULL(b.QntPBL, 0), Qnt2PBL = ISNULL(b.Qnt2PBL, 0), HrgPBL = ISNULL(b.HrgPBL, 0),
        QntRPB = ISNULL(b.QntRPB, 0), Qnt2RPB = ISNULL(b.Qnt2RPB, 0), HrgRPB = ISNULL(b.HrgRPB, 0),
        QntPNJ = ISNULL(b.QntPNJ, 0), Qnt2PNJ = ISNULL(b.Qnt2PNJ, 0), HrgPNJ = ISNULL(b.HrgPNJ, 0),
        QntRPJ = ISNULL(b.QntRPJ, 0), Qnt2RPJ = ISNULL(b.Qnt2RPJ, 0), HrgRPJ = ISNULL(b.HrgRPJ, 0),
        QntADI = ISNULL(b.QntADI, 0), Qnt2ADI = ISNULL(b.Qnt2ADI, 0), HrgADI = ISNULL(b.HrgADI, 0),
        HRGMADI = ISNULL(b.HRGMADI, 0),
        QntADO = ISNULL(b.QntADO, 0), Qnt2ADO = ISNULL(b.Qnt2ADO, 0), HrgADO = ISNULL(b.HrgADO, 0),
        HRGMADO = ISNULL(b.HRGMADO, 0),
        QntUKI = ISNULL(b.QntUKI, 0), Qnt2UKI = ISNULL(b.Qnt2UKI, 0), HrgUKI = ISNULL(b.HrgUKI, 0),
        QntUKO = ISNULL(b.QntUKO, 0), Qnt2UKO = ISNULL(b.Qnt2UKO, 0), HrgUKO = ISNULL(b.HrgUKO, 0),
        QntTRI = ISNULL(b.QntTRI, 0), Qnt2TRI = ISNULL(b.Qnt2TRI, 0), HrgTRI = ISNULL(b.HrgTRI, 0),
        QntTRO = ISNULL(b.QntTRO, 0), Qnt2TRO = ISNULL(b.Qnt2TRO, 0), HrgTRO = ISNULL(b.HrgTRO, 0),
        QntPMK = ISNULL(b.QntPMK, 0), Qnt2PMK = ISNULL(b.Qnt2PMK, 0), HrgPMK = ISNULL(b.HrgPMK, 0),
        QntRPK = ISNULL(b.QntRPK, 0), Qnt2RPK = ISNULL(b.Qnt2RPK, 0), HrgRPK = ISNULL(b.HrgRPK, 0),
        QntHPRD = ISNULL(b.QntHPRD, 0), Qnt2HPRD = ISNULL(b.Qnt2HPRD, 0), HrgHPRD = ISNULL(b.HrgHPRD, 0)
    FROM dbStockBrg a
    LEFT OUTER JOIN (
        SELECT 
            Tahun, Bulan, KodeBrg, KodeGdg,
            SUM(CASE WHEN Tipe = 'PBL' THEN QntSaldo ELSE 0 END) QntPBL,
            SUM(CASE WHEN Tipe = 'PBL' THEN Qnt2Saldo ELSE 0 END) Qnt2PBL,
            SUM(CASE WHEN Tipe = 'PBL' THEN HrgSaldo ELSE 0 END) HrgPBL,
            SUM(CASE WHEN Tipe = 'RPB' THEN -1 * QntSaldo ELSE 0 END) QntRPB,
            SUM(CASE WHEN Tipe = 'RPB' THEN -1 * Qnt2Saldo ELSE 0 END) Qnt2RPB,
            SUM(CASE WHEN Tipe = 'RPB' THEN -1 * HrgSaldo ELSE 0 END) HrgRPB,
            SUM(CASE WHEN Tipe = 'PNJ' THEN -1 * QntSaldo ELSE 0 END) QntPNJ,
            SUM(CASE WHEN Tipe = 'PNJ' THEN -1 * Qnt2Saldo ELSE 0 END) Qnt2PNJ,
            SUM(CASE WHEN Tipe = 'PNJ' THEN -1 * HrgSaldo ELSE 0 END) HrgPNJ,
            SUM(CASE WHEN Tipe = 'RPJ' THEN QntSaldo ELSE 0 END) QntRPJ,
            SUM(CASE WHEN Tipe = 'RPJ' THEN Qnt2Saldo ELSE 0 END) Qnt2RPJ,
            SUM(CASE WHEN Tipe = 'RPJ' THEN HrgSaldo ELSE 0 END) HrgRPJ,
            SUM(CASE WHEN Tipe = 'ADI' THEN QntSaldo ELSE 0 END) QntADI,
            SUM(CASE WHEN Tipe = 'ADI' THEN Qnt2Saldo ELSE 0 END) Qnt2ADI,
            SUM(CASE WHEN Tipe = 'ADI' THEN HrgSaldo ELSE 0 END) HrgADI,
            SUM(CASE WHEN Tipe = 'MADI' THEN HrgSaldo ELSE 0 END) HrgMADI,
            SUM(CASE WHEN Tipe = 'ADO' THEN -1 * QntSaldo ELSE 0 END) QntADO,
            SUM(CASE WHEN Tipe = 'ADO' THEN -1 * Qnt2Saldo ELSE 0 END) Qnt2ADO,
            SUM(CASE WHEN Tipe = 'ADO' THEN -1 * HrgSaldo ELSE 0 END) HrgADO,
            SUM(CASE WHEN Tipe = 'MADO' THEN -1 * HrgSaldo ELSE 0 END) HrgMADO,
            SUM(CASE WHEN Tipe = 'UKI' THEN QntSaldo ELSE 0 END) QntUKI,
            SUM(CASE WHEN Tipe = 'UKI' THEN Qnt2Saldo ELSE 0 END) Qnt2UKI,
            SUM(CASE WHEN Tipe = 'UKI' THEN HrgSaldo ELSE 0 END) HrgUKI,
            SUM(CASE WHEN Tipe = 'UKO' THEN -1 * QntSaldo ELSE 0 END) QntUKO,
            SUM(CASE WHEN Tipe = 'UKO' THEN -1 * Qnt2Saldo ELSE 0 END) Qnt2UKO,
            SUM(CASE WHEN Tipe = 'UKO' THEN -1 * HrgSaldo ELSE 0 END) HrgUKO,
            SUM(CASE WHEN Tipe = 'TRI' THEN QntSaldo ELSE 0 END) QntTRI,
            SUM(CASE WHEN Tipe = 'TRI' THEN Qnt2Saldo ELSE 0 END) Qnt2TRI,
            SUM(CASE WHEN Tipe = 'TRI' THEN HrgSaldo ELSE 0 END) HrgTRI,
            SUM(CASE WHEN Tipe = 'TRO' THEN -1 * QntSaldo ELSE 0 END) QntTRO,
            SUM(CASE WHEN Tipe = 'TRO' THEN -1 * Qnt2Saldo ELSE 0 END) Qnt2TRO,
            SUM(CASE WHEN Tipe = 'TRO' THEN -1 * HrgSaldo ELSE 0 END) HrgTRO,
            SUM(CASE WHEN Tipe = 'PMK' THEN -1 * QntSaldo ELSE 0 END) QntPMK,
            SUM(CASE WHEN Tipe = 'PMK' THEN -1 * Qnt2Saldo ELSE 0 END) Qnt2PMK,
            SUM(CASE WHEN Tipe = 'PMK' THEN -1 * HrgSaldo ELSE 0 END) HrgPMK,
            SUM(CASE WHEN Tipe = 'RPK' THEN QntSaldo ELSE 0 END) QntRPK,
            SUM(CASE WHEN Tipe = 'RPK' THEN Qnt2Saldo ELSE 0 END) Qnt2RPK,
            SUM(CASE WHEN Tipe = 'RPK' THEN HrgSaldo ELSE 0 END) HrgRPK,
            SUM(CASE WHEN Tipe = 'HP' THEN QntSaldo ELSE 0 END) QntHPRD,
            SUM(CASE WHEN Tipe = 'HP' THEN Qnt2Saldo ELSE 0 END) Qnt2HPRD,
            SUM(CASE WHEN Tipe = 'HP' THEN HrgSaldo ELSE 0 END) HrgHPRD
        FROM vwKartuStock 
        WHERE Tahun = @Tahun AND Bulan = @Bulan
        AND (@KodeBrg1 IS NULL OR (KodeBrg BETWEEN @KodeBrg1 AND @KodeBrg2))
        GROUP BY Tahun, Bulan, KodeBrg, KodeGdg
    ) b ON b.KodeBrg = a.KodeBrg AND b.KodeGdg = a.KodeGdg AND b.Tahun = a.Tahun AND b.Bulan = a.Bulan
    WHERE a.Tahun = @Tahun AND a.Bulan = @Bulan
    AND (@KodeBrg1 IS NULL OR (a.KodeBrg BETWEEN @KodeBrg1 AND @KodeBrg2))
    
    -- Update average price
    UPDATE dbStockBrg 
    SET HrgRata = CASE WHEN SaldoQnt = 0 THEN 0 ELSE SaldoRp / SaldoQnt END
    WHERE Tahun = @Tahun AND Bulan = @Bulan
    AND (@KodeBrg1 IS NULL OR (KodeBrg BETWEEN @KodeBrg1 AND @KodeBrg2))
    
    SET NOCOUNT OFF;
END
GO

-- =============================================
-- Stored Procedure: sp_ProsesKemasanHPP
-- Description: Process packaging changes for HPP
-- =============================================

CREATE PROCEDURE sp_ProsesKemasanHPP
    @Bulan INT,
    @Tahun INT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @NoBukti VARCHAR(20)
    
    -- Get packaging change documents for the period
    DECLARE kemasan_cursor CURSOR FOR
    SELECT NoBukti 
    FROM dbUbahKemasan 
    WHERE MONTH(Tanggal) = @Bulan AND YEAR(Tanggal) = @Tahun
    ORDER BY CAST(NoUrut AS INT)
    
    OPEN kemasan_cursor
    FETCH NEXT FROM kemasan_cursor INTO @NoBukti
    
    WHILE @@FETCH_STATUS = 0
    BEGIN
        -- Process each packaging change
        EXEC sp_ProsesHPPUbahKemasan @NoBukti
        
        FETCH NEXT FROM kemasan_cursor INTO @NoBukti
    END
    
    CLOSE kemasan_cursor
    DEALLOCATE kemasan_cursor
    
    SET NOCOUNT OFF;
END
GO

-- =============================================
-- Stored Procedure: sp_ProsesAkhirBulanHPP
-- Description: Process end of month for HPP
-- =============================================

CREATE PROCEDURE sp_ProsesAkhirBulanHPP
    @Bulan INT,
    @Tahun INT,
    @KodeBrg1 VARCHAR(25),
    @KodeBrg2 VARCHAR(25)
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @NextBulan INT
    DECLARE @NextTahun INT
    
    -- Calculate next period
    IF @Bulan = 12
    BEGIN
        SET @NextBulan = 1
        SET @NextTahun = @Tahun + 1
    END
    ELSE
    BEGIN
        SET @NextBulan = @Bulan + 1
        SET @NextTahun = @Tahun
    END
    
    -- Reset initial quantities for next period
    UPDATE dbStockBrg 
    SET QntAwal = 0, Qnt2Awal = 0, HrgAwal = 0
    WHERE Bulan = @NextBulan AND Tahun = @NextTahun
    AND (@KodeBrg1 IS NULL OR (KodeBrg BETWEEN @KodeBrg1 AND @KodeBrg2))
    
    -- Transfer balances to next period
    INSERT INTO dbStockBrg (KodeBrg, KodeGdg, Bulan, Tahun, QntAwal, Qnt2Awal, HrgAwal, HrgRata)
    SELECT 
        b.KodeBrg, b.KodeGdg, @NextBulan, @NextTahun,
        b.SaldoQnt, b.Saldo2Qnt, b.SaldoRp, b.HrgRata
    FROM dbStockBrg b
    LEFT JOIN dbBarang c ON c.KodeBrg = b.KodeBrg
    LEFT JOIN dbGudang d ON d.KodeGdg = b.KodeGdg
    WHERE b.Bulan = @Bulan AND b.Tahun = @Tahun
    AND d.KodeGdg IS NOT NULL AND c.KodeBrg IS NOT NULL
    AND NOT EXISTS (
        SELECT 1 FROM dbStockBrg 
        WHERE KodeBrg = b.KodeBrg AND KodeGdg = b.KodeGdg 
        AND Bulan = @NextBulan AND Tahun = @NextTahun
    )
    AND (@KodeBrg1 IS NULL OR (b.KodeBrg BETWEEN @KodeBrg1 AND @KodeBrg2))
    
    -- Update existing records in next period
    UPDATE next_period SET
        QntAwal = current_period.SaldoQnt,
        Qnt2Awal = current_period.Saldo2Qnt,
        HrgAwal = current_period.SaldoRp,
        HrgRata = current_period.HrgRata
    FROM dbStockBrg next_period
    INNER JOIN dbStockBrg current_period ON 
        current_period.KodeBrg = next_period.KodeBrg 
        AND current_period.KodeGdg = next_period.KodeGdg
        AND current_period.Bulan = @Bulan 
        AND current_period.Tahun = @Tahun
    WHERE next_period.Bulan = @NextBulan 
    AND next_period.Tahun = @NextTahun
    AND (@KodeBrg1 IS NULL OR (next_period.KodeBrg BETWEEN @KodeBrg1 AND @KodeBrg2))
    
    SET NOCOUNT OFF;
END
GO 