CREATE PROCEDURE [dbo].[sp_HitungUlangAktivaFK]
    @Bulan int,
    @Tahun int,
    @Perkiraan varchar(50)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @TanggalAwal datetime,
            @TanggalAkhir datetime,
            @NilaiPerolehan decimal(18,2),
            @NilaiPenyusutan decimal(18,2);

    -- Set period dates
    SET @TanggalAwal = DATEFROMPARTS(@Tahun, @Bulan, 1);
    SET @TanggalAkhir = EOMONTH(@TanggalAwal);

    -- Calculate fiscal acquisition value
    SELECT @NilaiPerolehan = ISNULL(SUM(
        CASE 
            WHEN StatusAktivaP = 'D' THEN Debet
            WHEN StatusAktivaP = 'K' THEN -Debet
        END), 0)
    FROM dbTransaksi
    WHERE NoAktivaP = @Perkiraan
    AND Tanggal <= @TanggalAkhir;

    -- Calculate fiscal accumulated depreciation
    SELECT @NilaiPenyusutan = ISNULL(SUM(
        CASE 
            WHEN StatusAktivaL = 'D' THEN Debet
            WHEN StatusAktivaL = 'K' THEN -Debet
        END), 0)
    FROM dbTransaksi
    WHERE NoAktivaL = @Perkiraan
    AND Tanggal <= @TanggalAkhir;

    -- Update fiscal asset values
    UPDATE dbAktivaFK
    SET NilaiPerolehanFiskal = @NilaiPerolehan,
        NilaiPenyusutanFiskal = @NilaiPenyusutan,
        NilaiBukuFiskal = @NilaiPerolehan - @NilaiPenyusutan
    WHERE Perkiraan = @Perkiraan;

    -- Update fiscal neraca values
    UPDATE dbNeracaFiskal
    SET AwalD = @NilaiPerolehan,
        AwalK = @NilaiPenyusutan,
        AkhirD = @NilaiPerolehan,
        AkhirK = @NilaiPenyusutan
    WHERE Perkiraan = @Perkiraan
    AND Bulan = @Bulan
    AND Tahun = @Tahun;
END; 