CREATE PROCEDURE [dbo].[sp_HitungUlangTransaksi]
    @Devisi varchar(10),
    @Perkiraan varchar(50),
    @DKP int,
    @Lawan varchar(50),
    @DKL int,
    @Debet decimal(18,2),
    @DebetRp decimal(18,2),
    @StatusAktivaP varchar(10),
    @StatusAktivaL varchar(10),
    @NoAktivaP varchar(50),
    @NoAktivaL varchar(50),
    @TipeTrans varchar(10),
    @Bulan int,
    @Tahun int,
    @Valas varchar(10)
AS
BEGIN
    SET NOCOUNT ON;

    -- Update main account
    UPDATE dbNeraca
    SET MD = CASE 
            WHEN @DKP = 1 THEN MD + @Debet
            ELSE MD
        END,
        MK = CASE 
            WHEN @DKP = 2 THEN MK + @Debet
            ELSE MK
        END,
        MDRp = CASE 
            WHEN @DKP = 1 THEN MDRp + @DebetRp
            ELSE MDRp
        END,
        MKRp = CASE 
            WHEN @DKP = 2 THEN MKRp + @DebetRp
            ELSE MKRp
        END
    WHERE Perkiraan = @Perkiraan
    AND Bulan = @Bulan 
    AND Tahun = @Tahun;

    -- Update contra account
    UPDATE dbNeraca
    SET MD = CASE 
            WHEN @DKL = 1 THEN MD + @Debet
            ELSE MD
        END,
        MK = CASE 
            WHEN @DKL = 2 THEN MK + @Debet
            ELSE MK
        END,
        MDRp = CASE 
            WHEN @DKL = 1 THEN MDRp + @DebetRp
            ELSE MDRp
        END,
        MKRp = CASE 
            WHEN @DKL = 2 THEN MKRp + @DebetRp
            ELSE MKRp
        END
    WHERE Perkiraan = @Lawan
    AND Bulan = @Bulan 
    AND Tahun = @Tahun;

    -- Handle asset transactions
    IF @StatusAktivaP <> ''
    BEGIN
        UPDATE dbNeraca
        SET JPD = CASE 
                WHEN @StatusAktivaP = 'D' THEN JPD + @Debet
                ELSE JPD
            END,
            JPK = CASE 
                WHEN @StatusAktivaP = 'K' THEN JPK + @Debet
                ELSE JPK
            END,
            JPDRp = CASE 
                WHEN @StatusAktivaP = 'D' THEN JPDRp + @DebetRp
                ELSE JPDRp
            END,
            JPKRp = CASE 
                WHEN @StatusAktivaP = 'K' THEN JPKRp + @DebetRp
                ELSE JPKRp
            END
        WHERE Perkiraan = @NoAktivaP
        AND Bulan = @Bulan 
        AND Tahun = @Tahun;
    END;

    IF @StatusAktivaL <> ''
    BEGIN
        UPDATE dbNeraca
        SET JPD = CASE 
                WHEN @StatusAktivaL = 'D' THEN JPD + @Debet
                ELSE JPD
            END,
            JPK = CASE 
                WHEN @StatusAktivaL = 'K' THEN JPK + @Debet
                ELSE JPK
            END,
            JPDRp = CASE 
                WHEN @StatusAktivaL = 'D' THEN JPDRp + @DebetRp
                ELSE JPDRp
            END,
            JPKRp = CASE 
                WHEN @StatusAktivaL = 'K' THEN JPKRp + @DebetRp
                ELSE JPKRp
            END
        WHERE Perkiraan = @NoAktivaL
        AND Bulan = @Bulan 
        AND Tahun = @Tahun;
    END;

    -- Handle profit/loss transactions
    IF @TipeTrans = 'RL'
    BEGIN
        UPDATE dbNeraca
        SET RLD = CASE 
                WHEN @DKP = 1 THEN RLD + @Debet
                ELSE RLD
            END,
            RLK = CASE 
                WHEN @DKP = 2 THEN RLK + @Debet
                ELSE RLK
            END,
            RLDRp = CASE 
                WHEN @DKP = 1 THEN RLDRp + @DebetRp
                ELSE RLDRp
            END,
            RLKRp = CASE 
                WHEN @DKP = 2 THEN RLKRp + @DebetRp
                ELSE RLKRp
            END
        WHERE Perkiraan = @Perkiraan
        AND Bulan = @Bulan 
        AND Tahun = @Tahun;

        UPDATE dbNeraca
        SET RLD = CASE 
                WHEN @DKL = 1 THEN RLD + @Debet
                ELSE RLD
            END,
            RLK = CASE 
                WHEN @DKL = 2 THEN RLK + @Debet
                ELSE RLK
            END,
            RLDRp = CASE 
                WHEN @DKL = 1 THEN RLDRp + @DebetRp
                ELSE RLDRp
            END,
            RLKRp = CASE 
                WHEN @DKL = 2 THEN RLKRp + @DebetRp
                ELSE RLKRp
            END
        WHERE Perkiraan = @Lawan
        AND Bulan = @Bulan 
        AND Tahun = @Tahun;
    END;
END; 