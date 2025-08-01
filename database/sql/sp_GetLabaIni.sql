CREATE PROCEDURE [dbo].[sp_GetLabaIni]
    @Tahun int,
    @Devisi varchar(10),
    @IsHPPRL bit,
    @Bulan int
AS
BEGIN
    SET NOCOUNT ON;

    -- Calculate profit/loss for the period
    SELECT ISNULL(SUM(
        CASE 
            WHEN n.DK = 1 THEN n.AkhirD - n.AkhirK
            ELSE n.AkhirK - n.AkhirD
        END * 
        CASE 
            WHEN l.Tanda = '-' THEN -1 
            ELSE 1 
        END), 0) as HasilAkhir
    FROM dbLRHPP l
    INNER JOIN dbNeraca n ON l.Perkiraan = n.Perkiraan
    WHERE n.Tahun = @Tahun
    AND n.Bulan = @Bulan
    AND (n.Devisi = @Devisi OR @Devisi = '')
    AND l.IsLRHPP = @IsHPPRL
    AND l.Jumlah = '';
END; 