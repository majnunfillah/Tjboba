CREATE PROCEDURE [dbo].[sp_CreateRLJournal]
    @Bulan int,
    @Tahun int,
    @Devisi varchar(10),
    @TotalRL decimal(18,2),
    @Tanggal datetime,
    @IDUser varchar(50)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @NomorBukti varchar(50),
            @PerkiraanRL varchar(50),
            @PerkiraanRLTahun varchar(50),
            @PerkiraanLDT varchar(50);

    -- Get account numbers from posting configuration
    SELECT @PerkiraanRL = Perkiraan FROM dbPostHutPiut WHERE Kode = 'RLB';
    SELECT @PerkiraanRLTahun = Perkiraan FROM dbPostHutPiut WHERE Kode = 'RLI';
    SELECT @PerkiraanLDT = Perkiraan FROM dbPostHutPiut WHERE Kode = 'RLL';

    -- Generate document number
    SET @NomorBukti = 'RL' + 
        RIGHT('00000' + CAST(NEXT VALUE FOR NoBuktiSequence AS varchar), 5) + 
        '/' + RIGHT('00' + CAST(@Bulan + 1 AS varchar), 2) + 
        RIGHT(CAST(@Tahun AS varchar), 2) + '/BIG';

    -- Create journal entries
    IF @Bulan < 12
    BEGIN
        -- Monthly profit/loss entry
        INSERT INTO dbTransaksi (
            NoBukti, Urut, Tanggal, Devisi, NoSub, Perkiraan, 
            Lawan, Keterangan, Debet, Kredit, Kurs, Valas, 
            KodeUser, Sumber, Posted, NoRef, TglRef, StatusAktivaP, 
            StatusAktivaL, NoAktivaP, NoAktivaL, TipeTrans
        )
        VALUES (
            @NomorBukti, 1, @Tanggal, @Devisi, '', @PerkiraanRL,
            @PerkiraanRLTahun, 'Koreksi Rugi Laba Bulan ini Ke Tahun Berjalan',
            CASE WHEN @TotalRL > 0 THEN @TotalRL ELSE 0 END,
            CASE WHEN @TotalRL < 0 THEN ABS(@TotalRL) ELSE 0 END,
            1, 'IDR', @IDUser, 'BJK', 'C', '', NULL, '', '', '', '', 'RL'
        );

        -- Contra entry
        INSERT INTO dbTransaksi (
            NoBukti, Urut, Tanggal, Devisi, NoSub, Perkiraan, 
            Lawan, Keterangan, Debet, Kredit, Kurs, Valas, 
            KodeUser, Sumber, Posted, NoRef, TglRef, StatusAktivaP, 
            StatusAktivaL, NoAktivaP, NoAktivaL, TipeTrans
        )
        VALUES (
            @NomorBukti, 2, @Tanggal, @Devisi, '', @PerkiraanRLTahun,
            @PerkiraanRL, 'Koreksi Rugi Laba Bulan ini Ke Tahun Berjalan',
            CASE WHEN @TotalRL < 0 THEN ABS(@TotalRL) ELSE 0 END,
            CASE WHEN @TotalRL > 0 THEN @TotalRL ELSE 0 END,
            1, 'IDR', @IDUser, 'BJK', 'C', '', NULL, '', '', '', '', 'RL'
        );
    END
    ELSE
    BEGIN
        -- Year-end profit/loss entry
        INSERT INTO dbTransaksi (
            NoBukti, Urut, Tanggal, Devisi, NoSub, Perkiraan, 
            Lawan, Keterangan, Debet, Kredit, Kurs, Valas, 
            KodeUser, Sumber, Posted, NoRef, TglRef, StatusAktivaP, 
            StatusAktivaL, NoAktivaP, NoAktivaL, TipeTrans
        )
        VALUES (
            @NomorBukti, 1, @Tanggal, @Devisi, '', @PerkiraanRLTahun,
            @PerkiraanLDT, 'Koreksi Tahun Berjalan Ke Laba Ditahan',
            CASE WHEN @TotalRL > 0 THEN @TotalRL ELSE 0 END,
            CASE WHEN @TotalRL < 0 THEN ABS(@TotalRL) ELSE 0 END,
            1, 'IDR', @IDUser, 'BJK', 'C', '', NULL, '', '', '', '', 'RL'
        );

        -- Contra entry
        INSERT INTO dbTransaksi (
            NoBukti, Urut, Tanggal, Devisi, NoSub, Perkiraan, 
            Lawan, Keterangan, Debet, Kredit, Kurs, Valas, 
            KodeUser, Sumber, Posted, NoRef, TglRef, StatusAktivaP, 
            StatusAktivaL, NoAktivaP, NoAktivaL, TipeTrans
        )
        VALUES (
            @NomorBukti, 2, @Tanggal, @Devisi, '', @PerkiraanLDT,
            @PerkiraanRLTahun, 'Koreksi Tahun Berjalan Ke Laba Ditahan',
            CASE WHEN @TotalRL < 0 THEN ABS(@TotalRL) ELSE 0 END,
            CASE WHEN @TotalRL > 0 THEN @TotalRL ELSE 0 END,
            1, 'IDR', @IDUser, 'BJK', 'C', '', NULL, '', '', '', '', 'RL'
        );
    END;
END; 