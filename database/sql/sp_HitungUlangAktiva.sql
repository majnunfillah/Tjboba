CREATE PROCEDURE [dbo].[sp_HitungUlangAktiva]
    @Bulan int,
    @Tahun int,
    @Perkiraan varchar(50)
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @NilaiAktiva decimal(18,2) = 0,
            @NilaiPenyusutan decimal(18,2) = 0,
            @NilaiBuku decimal(18,2) = 0,
            @TanggalAkhir datetime;
    
    -- Calculate end of month date
    IF @Bulan < 12
        SET @TanggalAkhir = DATEADD(DAY, -1, DATEADD(MONTH, 1, DATEFROMPARTS(@Tahun, @Bulan, 1)));
    ELSE
        SET @TanggalAkhir = DATEFROMPARTS(@Tahun, @Bulan, 31);
    
    -- Get asset value (Aktiva Perolehan)
    SELECT @NilaiAktiva = ISNULL(SUM(
        CASE 
            WHEN StatusAktivaP = 'D' THEN Debet 
            WHEN StatusAktivaP = 'K' THEN -Debet 
            ELSE 0 
        END), 0)
    FROM dbTransaksi 
    WHERE NoAktivaP = @Perkiraan 
    AND Tanggal <= @TanggalAkhir;
    
    -- Get depreciation value (Aktiva Liabilitas)
    SELECT @NilaiPenyusutan = ISNULL(SUM(
        CASE 
            WHEN StatusAktivaL = 'D' THEN Debet 
            WHEN StatusAktivaL = 'K' THEN -Debet 
            ELSE 0 
        END), 0)
    FROM dbTransaksi 
    WHERE NoAktivaL = @Perkiraan 
    AND Tanggal <= @TanggalAkhir;
    
    -- Calculate book value
    SET @NilaiBuku = @NilaiAktiva - @NilaiPenyusutan;
    
    -- Update or insert into dbAktivaDet
    IF EXISTS (
        SELECT 1 FROM dbAktivaDet 
        WHERE Perkiraan = @Perkiraan 
        AND Bulan = @Bulan 
        AND Tahun = @Tahun
    )
    BEGIN
        UPDATE dbAktivaDet 
        SET 
            Awal = @NilaiAktiva,
            AwalSusut = @NilaiPenyusutan,
            Akhir = @NilaiBuku,
            AkhirSusut = @NilaiPenyusutan
        WHERE Perkiraan = @Perkiraan 
        AND Bulan = @Bulan 
        AND Tahun = @Tahun;
    END
    ELSE
    BEGIN
        INSERT INTO dbAktivaDet (
            Perkiraan, Bulan, Tahun, 
            Awal, AwalSusut, Akhir, AkhirSusut
        )
        VALUES (
            @Perkiraan, @Bulan, @Tahun,
            @NilaiAktiva, @NilaiPenyusutan, @NilaiBuku, @NilaiPenyusutan
        );
    END
END 