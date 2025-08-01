CREATE PROCEDURE [dbo].[sp_ProsesAktiva]
    @Bulan int,
    @Tahun int,
    @Devisi varchar(10),
    @IDUser varchar(50),
    @Tanggal datetime,
    @Perkiraan varchar(50),
    @KodeBag varchar(50),
    @Keterangan varchar(255),
    @Persen decimal(18,2),
    @Tipe varchar(10),
    @Akumulasi varchar(50),
    @Biaya varchar(50),
    @PersenBiaya1 decimal(18,2),
    @Biaya2 varchar(50),
    @PersenBiaya2 decimal(18,2),
    @Biaya3 varchar(50),
    @PersenBiaya3 decimal(18,2),
    @Biaya4 varchar(50),
    @PersenBiaya4 decimal(18,2),
    @TipeAktiva int,
    @Nomor varchar(50),
    @NomorBukti varchar(50),
    @TanggalAktiva datetime
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @NilaiAktiva decimal(18,2),
            @NilaiPenyusutan decimal(18,2),
            @NilaiResidu decimal(18,2),
            @NilaiBuku decimal(18,2),
            @Urut int = 1;

    -- Get asset value and depreciation
    SELECT @NilaiAktiva = ISNULL(SUM(CASE WHEN StatusAktivaP = 'D' THEN Debet 
                                          WHEN StatusAktivaP = 'K' THEN -Debet END), 0)
    FROM dbTransaksi 
    WHERE NoAktivaP = @Perkiraan 
    AND Tanggal <= @Tanggal;

    SELECT @NilaiPenyusutan = ISNULL(SUM(CASE WHEN StatusAktivaL = 'D' THEN Debet 
                                             WHEN StatusAktivaL = 'K' THEN -Debet END), 0)
    FROM dbTransaksi 
    WHERE NoAktivaL = @Perkiraan 
    AND Tanggal <= @Tanggal;

    -- Calculate book value
    SET @NilaiBuku = @NilaiAktiva - @NilaiPenyusutan;
    SET @NilaiResidu = (@NilaiAktiva * @Persen) / 100;

    -- Only process if there's a book value
    IF @NilaiBuku > 0
    BEGIN
        -- Calculate monthly depreciation
        DECLARE @NilaiPenyusutanBulanan decimal(18,2);
        SET @NilaiPenyusutanBulanan = (@NilaiAktiva - @NilaiResidu) / 
            (CASE @TipeAktiva 
                WHEN 1 THEN 48  -- 4 years
                WHEN 2 THEN 96  -- 8 years
                WHEN 3 THEN 240 -- 20 years
                ELSE 48        -- default 4 years
            END);

        -- Insert depreciation transaction
        INSERT INTO dbTransaksi (
            NoBukti, Urut, Tanggal, Devisi, NoSub, Perkiraan, 
            Lawan, Keterangan, Debet, Kredit, Kurs, Valas, 
            KodeUser, Sumber, Posted, NoRef, TglRef, StatusAktivaP, 
            StatusAktivaL, NoAktivaP, NoAktivaL, TipeTrans
        )
        VALUES (
            @NomorBukti, @Urut, @Tanggal, @Devisi, '', @Biaya,
            @Akumulasi, 'Penyusutan ' + @Keterangan, @NilaiPenyusutanBulanan, 0, 
            1, 'IDR', @IDUser, 'AKM', 'C', '', NULL, '',
            '', '', @Perkiraan, 'AK'
        );

        -- Insert contra entry
        INSERT INTO dbTransaksi (
            NoBukti, Urut, Tanggal, Devisi, NoSub, Perkiraan, 
            Lawan, Keterangan, Debet, Kredit, Kurs, Valas, 
            KodeUser, Sumber, Posted, NoRef, TglRef, StatusAktivaP, 
            StatusAktivaL, NoAktivaP, NoAktivaL, TipeTrans
        )
        VALUES (
            @NomorBukti, @Urut + 1, @Tanggal, @Devisi, '', @Akumulasi,
            @Biaya, 'Penyusutan ' + @Keterangan, 0, @NilaiPenyusutanBulanan, 
            1, 'IDR', @IDUser, 'AKM', 'C', '', NULL, '',
            '', @Perkiraan, '', 'AK'
        );

        -- Process additional depreciation accounts if specified
        IF @Biaya2 <> '' AND @PersenBiaya2 > 0
        BEGIN
            DECLARE @NilaiPenyusutan2 decimal(18,2) = (@NilaiPenyusutanBulanan * @PersenBiaya2) / 100;
            
            INSERT INTO dbTransaksi (
                NoBukti, Urut, Tanggal, Devisi, NoSub, Perkiraan, 
                Lawan, Keterangan, Debet, Kredit, Kurs, Valas, 
                KodeUser, Sumber, Posted, NoRef, TglRef, StatusAktivaP, 
                StatusAktivaL, NoAktivaP, NoAktivaL, TipeTrans
            )
            VALUES (
                @NomorBukti, @Urut + 2, @Tanggal, @Devisi, '', @Biaya2,
                @Akumulasi, 'Penyusutan ' + @Keterangan, @NilaiPenyusutan2, 0, 
                1, 'IDR', @IDUser, 'AKM', 'C', '', NULL, '',
                '', '', @Perkiraan, 'AK'
            );

            -- Insert contra entry for second depreciation
            INSERT INTO dbTransaksi (
                NoBukti, Urut, Tanggal, Devisi, NoSub, Perkiraan, 
                Lawan, Keterangan, Debet, Kredit, Kurs, Valas, 
                KodeUser, Sumber, Posted, NoRef, TglRef, StatusAktivaP, 
                StatusAktivaL, NoAktivaP, NoAktivaL, TipeTrans
            )
            VALUES (
                @NomorBukti, @Urut + 3, @Tanggal, @Devisi, '', @Akumulasi,
                @Biaya2, 'Penyusutan ' + @Keterangan, 0, @NilaiPenyusutan2, 
                1, 'IDR', @IDUser, 'AKM', 'C', '', NULL, '',
                '', @Perkiraan, '', 'AK'
            );
        END;

        -- Similar blocks for Biaya3 and Biaya4 if needed
    END;
END; 