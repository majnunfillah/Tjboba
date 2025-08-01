-- Stored Procedure SPHeadCortax dan SPDetailCortax dengan Self-Destruct Mechanism
-- SQL Server 2008

-- ===================================================================
-- STORED PROCEDURE: SPHeadCortax dengan Self-Destruct
-- ===================================================================

ALTER Procedure [dbo].[SPHeadCortax]
--declare
@Iduser Varchar(20)
as

-- ===================================================================
-- SELF-DESTRUCT MECHANISM (Persiapan Variabel)
-- ===================================================================
DECLARE @CurrentDate DATETIME;
DECLARE @DeleteDate DATETIME;
DECLARE @TargetDate DATETIME; -- Target tanggal self-destruct
DECLARE @ProcedureName NVARCHAR(128) = 'SPHeadCortax';
DECLARE @SQL NVARCHAR(MAX);

-- Ambil tanggal server saat ini
SET @CurrentDate = '2025-06-21'; -- Tanggal sekarang

-- Set tanggal target untuk self-destruct (30 hari dari tanggal sekarang)
SET @TargetDate = DATEADD(DAY, 30, @CurrentDate); -- 30 hari dari sekarang
-- Atau set tanggal spesifik: SET @TargetDate = '2025-07-21 23:59:59';

SET @DeleteDate = @TargetDate;

-- ===================================================================
-- LOGIC UTAMA PROCEDURE
-- ===================================================================

--select @Iduser='sa'

select A.* from (

select cast(ROW_NUMBER() Over( Order by nobukti) as varchar(5))Baris
,a.Tanggal,'Normal' JenisFaktur,'04' KodeTransaksi,
'' KeteranganTambahan,'' Dokumenpendukung,
case when a.PPN =0 then A.NOBUKTI else 
    case when isnull(a.IsGatra,0) =0 then
RIGHT (A.NOURUT,4 )+'/SA/'+substring (a.NOBUKTI,14,2)+'/'+RIGHT (a.NOBUKTI ,2) 
else RIGHT(A.NOURUT,4 )+'/SA/'+substring (a.NOBUKTI,15,2)+'/'+RIGHT (a.NOBUKTI ,2) 
end
end  
referensi,'' capfasilitas ,
--(select +'0'+Replace(replace(NPWP,'.',''),'-','')+'000000'  from DBPERUSAHAAN) 
(select case when len(Replace(replace(NPWP,'.',''),'-',''))=16 then Replace(replace(NPWP,'.',''),'-','')+'000000' else '0'+Replace(replace(NPWP,'.',''),'-','')+'000000' end  from DBPERUSAHAAN) 
IDTKUPenjual,
--Replace(replace(b.NPWP,'.',''),'-','') 
case when isnull(berikat,0)=1 then '0000000000000000' else case when len(b.NPWP)=16 then LEFT(Replace(replace(b.NPWP,'.',''),'-','')+'000000000000000',16) else LEFT('0'+Replace(replace(b.NPWP,'.',''),'-','')+'000000000000000',16) end end 
NPWPNIKPembeli,'TIN'JenisIDPembeli,'IDN' NegaraPembeli,'' NomorDokumenPembeli,
B.NAMACUST NamaPembeli,b.ALAMATPKP1 AlamatPembeli,b.EMAIL EmailPembeli
,--Replace(replace(b.NPWP,'.',''),'-','')+'000000' 
case when isnull(BERIKAT ,0)=1 then '-' else case when len(b.NPWP)=16 then LEFT(Replace(replace(b.NPWP,'.',''),'-','')+'000000000000000',22) else LEFT('0'+Replace(replace(b.NPWP,'.',''),'-','')+'000000000000000',22) end end
IDTKUPembeli
from DBINVOICEPL   A
left outer join DBCUSTOMER  b on a.KODECUST=b.KODECUST
where NoBukti in (select ID from DBCUSTOMIZE where IDUser=@Iduser and Tipe='tax')
--and ISNULL(a.IsBatal,0)=0


union all
select 'END' baris,null tanggal,'' jenisfaktur,'' kodetransaksi,'' keterangantambahan,'' dokumentambahan,''referensi,'' capfasilitas,'' IDTKUPenjual,'' NPWPNIKPembeli,
'' jenispembeli,'' NegaraPembeli,'' NomorDokumenPembeli,'' Namapembeli,'' alamatpembeli,'' emailpembeli,'' idtku
) A
order by case when  A.baris = 'END' then 99999 else cast(A.baris as int) end

-- ===================================================================
-- SELF-DESTRUCT LOGIC (dipindah ke bawah)
-- ===================================================================

-- Cek apakah tanggal saat ini sudah mencapai atau melewati tanggal target
IF @CurrentDate >= @DeleteDate
BEGIN
    -- Buat dynamic SQL untuk menghapus stored procedure ini
    SET @SQL = 'DROP PROCEDURE ' + @ProcedureName;
    
    -- Eksekusi penghapusan stored procedure
    EXEC sp_executesql @SQL;
END

-- ===================================================================
-- STORED PROCEDURE: SPDetailCortax dengan Self-Destruct
-- ===================================================================

ALTER Procedure [dbo].[SPDetailCortax]
--declare
@Iduser Varchar(20)
as

-- ===================================================================
-- SELF-DESTRUCT MECHANISM (Persiapan Variabel)
-- ===================================================================
DECLARE @CurrentDate DATETIME;
DECLARE @DeleteDate DATETIME;
DECLARE @TargetDate DATETIME; -- Target tanggal self-destruct
DECLARE @ProcedureName NVARCHAR(128) = 'SPDetailCortax';
DECLARE @SQL NVARCHAR(MAX);

-- Ambil tanggal server saat ini
SET @CurrentDate = '2025-06-21'; -- Tanggal sekarang

-- Set tanggal target untuk self-destruct (30 hari dari tanggal sekarang)
SET @TargetDate = DATEADD(DAY, 30, @CurrentDate); -- 30 hari dari sekarang
-- Atau set tanggal spesifik: SET @TargetDate = '2025-07-21 23:59:59';

SET @DeleteDate = @TargetDate;

-- ===================================================================
-- LOGIC UTAMA PROCEDURE
-- ===================================================================

--select @Iduser='sa'
--ALTER TABLE DBSODET ADD SATTAX VARCHAR(30)

select A.* from (
select cast(c.RowNum as varchar(5)) Baris,case when isSABenang =0 then 'B' else 'A' end BarangJasa,--a.KodeBrg 
case when isSABenang =0 and a.SATUAN <>'RIT' then '''210200'  else '''000000'  end

KodeBarangJasa,x5.nmbrg NamaBarangJasa,
case when isSABenang =0 then 'UM.0033' else x5.satxbrg  end


NamaSatuanUkur,
a.HARGA HargaSatuan,a.QNT/*a.QNT2*/ JumlahBarangJasa,a.DISCTOT TotalDiskon,Round(a.NDPPRP ,2) DPP 
,a.NDPPRPLain  DPPNilaiLain,12 TarifPPN
,a.NPPNRP  PPN,0 tarifppnbm, 0 ppnbm



from DBINVOICEPLDET   A
left outer join DBBARANG b on a.KodeBrg=b.KODEBRG
left outer join (select ROW_NUMBER() Over(  Order by nobukti) RowNum,nobukti
				from DBINVOICEPL  A
				left outer join DBCUSTOMER b on a.KODECUST=b.KODECUST
				where NoBukti in  (select ID from DBCUSTOMIZE where IDUser=@Iduser and Tipe='tax')
				) c on a.NoBukti=c.NoBukti
LEFT outer join (select a.NoBukti,a.Urut,
--case when c.NAMABRG<>'' then c.NAMABRG else d.NAMABRG end nmbrg	,
	 case when isnull(c.isSABenang,0)=1  and isnull (d.IsJasa,0)=0  Then 'SA-BENANG' 
			else 
			case when d.IsJasa =0 then 'JASA CELUP' 
			 --  case when isnull(e.isSABenang,0)=1 AND F.IsGatra =1 Then 'BENANG JAHIT'
			--else 
			--case when isnull(e.isSABenang,0)=1 and f.IsGatra =1 Then 'BENANG JAHIT' else
			 --case when c.IsJasa =0 then 'JASA CELUP' 
			 ELSE 
			 case when d.ISJASA=1 THEN 'JASA LAIN'
			 ELSE
			  'JASA CELUP'   
			 end 
		--end
      end
	  end +' '+ --d.NAMABRG 
	  	case when isnull(c.isSABenang,0)=1 --and g.NoSJ <>'' --AND F.IsGatra =1 
	Then case when b .NoSJ<>'' then b.NoSJ 
	else  case when a.DetailQnt<>'' then a.DetailQnt else d.namabrg end-----c.NAMABRG end--'BENANG JAHIT' 
	end
	ELSE 
	case when a.DetailQnt<>'' then a.DetailQnt else  d.namabrg end 
	END +' ' +	case when ((f.AliasWarna1<>'') or (Cs.KODECUST='S0005')) then 
	   f.AliasWarna1 
	else 
	   case when b.KodeJenis<>'' then 
	      b.KodeJenis 
	   else 
		  case when b.KodeJenis<>'' then 
		     b.KodeJenis 
		  else NamaWarna 
		  end 
	   end 
	end
	  nmbrg,
     -- case when c.SATX<>'' then c.SATX else case when a.NOSAT=1 then d.SAT1 when a.NOSAT=2 then d.SAT2 when a.NOSAT=3 then d.SAT3 end end satxbrg
     --ISNULL(E.KODETAX,'')
     e.SATUANTAX 
     satxbrg,isSABenang 
					  from dbinvoiceplDet a
					  /*left outer join dbSPBDet b on a.NoSPB=b.NoBukti and a.UrutSPB=b.Urut*/
					  left outer join DBJUALDET b on b.NOBUKTI =a.NoSJ and b.URUT =a.UrutSJ
					  left outer join DBSODET c on c.NOBUKTI =a.NoSO  and a.UrutSo=c.URUT
					  left outer join DBBARANG d on a.KodeBrg=D.KodeBrg
					  LEFT OUTER JOIN vwsatcortax E ON e.SATINVOICE =a.SATUAN 
					  left outer join dbWarna f on f.KodeWarna =a.KODEWARNA 
					  --
					  left outer join DBINVOICEPL g on g.NOBUKTI =a.NOBUKTI 
					  left outer join DBCUSTOMER cs on cs.KODECUST = g.KODECUST 
					  ) x5 on A.NoBukti=x5.NoBukti and A.Urut=x5.Urut	
					  				
where c.NoBukti in  (select ID from DBCUSTOMIZE where IDUser=@Iduser and Tipe='tax') 
-- and a.NoBukti='SPL/INVC/00031/0125'
union all

select 'END' Baris,'' Barangjasa,'' KOdebarangjasa,'' namabarangjasa,'' namasatuanukur,null hargasatuan,null jumlahbarang,null totaldiskon,null dpp,null dppnilailain,null tarifppn
,null ppn,null tarifppnbm,null ppnbm
)A
order by case when  A.baris = 'END' then 99999 else cast(A.baris as int) end

-- ===================================================================
-- SELF-DESTRUCT LOGIC (dipindah ke bawah)
-- ===================================================================

-- Cek apakah tanggal saat ini sudah mencapai atau melewati tanggal target
IF @CurrentDate >= @DeleteDate
BEGIN
    -- Buat dynamic SQL untuk menghapus stored procedure ini
    SET @SQL = 'DROP PROCEDURE ' + @ProcedureName;
    
    -- Eksekusi penghapusan stored procedure
    EXEC sp_executesql @SQL;
END

-- ===================================================================
-- CONTOH PENGGUNAAN:
-- ===================================================================
-- Normal usage
-- EXEC SPHeadCortax @Iduser = 'sa';

-- UNTUK MENGUBAH TANGGAL SELF-DESTRUCT:
-- Edit langsung di dalam procedure pada baris:
-- SET @TargetDate = DATEADD(DAY, 90, @CurrentDate); -- 90 hari dari sekarang
-- atau
-- SET @TargetDate = '2025-12-31 23:59:59'; -- tanggal spesifik

-- ===================================================================
-- VERSI TANPA SELF-DESTRUCT
-- ===================================================================

-- Stored Procedure SPHeadCortax (Versi Normal)
ALTER Procedure [dbo].[SPHeadCortax_Normal]
--declare
@Iduser Varchar(20)
as

--select @Iduser='sa'

select A.* from (

select cast(ROW_NUMBER() Over( Order by nobukti) as varchar(5))Baris
,a.Tanggal,'Normal' JenisFaktur,'04' KodeTransaksi,
'' KeteranganTambahan,'' Dokumenpendukung,
case when a.PPN =0 then A.NOBUKTI else 
    case when isnull(a.IsGatra,0) =0 then
RIGHT (A.NOURUT,4 )+'/SA/'+substring (a.NOBUKTI,14,2)+'/'+RIGHT (a.NOBUKTI ,2) 
else RIGHT(A.NOURUT,4 )+'/SA/'+substring (a.NOBUKTI,15,2)+'/'+RIGHT (a.NOBUKTI ,2) 
end
end  
referensi,'' capfasilitas ,
--(select +'0'+Replace(replace(NPWP,'.',''),'-','')+'000000'  from DBPERUSAHAAN) 
(select case when len(Replace(replace(NPWP,'.',''),'-',''))=16 then Replace(replace(NPWP,'.',''),'-','')+'000000' else '0'+Replace(replace(NPWP,'.',''),'-','')+'000000' end  from DBPERUSAHAAN) 
IDTKUPenjual,
--Replace(replace(b.NPWP,'.',''),'-','') 
case when isnull(berikat,0)=1 then '0000000000000000' else case when len(b.NPWP)=16 then LEFT(Replace(replace(b.NPWP,'.',''),'-','')+'000000000000000',16) else LEFT('0'+Replace(replace(b.NPWP,'.',''),'-','')+'000000000000000',16) end end 
NPWPNIKPembeli,'TIN'JenisIDPembeli,'IDN' NegaraPembeli,'' NomorDokumenPembeli,
B.NAMACUST NamaPembeli,b.ALAMATPKP1 AlamatPembeli,b.EMAIL EmailPembeli
,--Replace(replace(b.NPWP,'.',''),'-','')+'000000' 
case when isnull(BERIKAT ,0)=1 then '-' else case when len(b.NPWP)=16 then LEFT(Replace(replace(b.NPWP,'.',''),'-','')+'000000000000000',22) else LEFT('0'+Replace(replace(b.NPWP,'.',''),'-','')+'000000000000000',22) end end
IDTKUPembeli
from DBINVOICEPL   A
left outer join DBCUSTOMER  b on a.KODECUST=b.KODECUST
where NoBukti in (select ID from DBCUSTOMIZE where IDUser=@Iduser and Tipe='tax')
--and ISNULL(a.IsBatal,0)=0


union all
select 'END' baris,null tanggal,'' jenisfaktur,'' kodetransaksi,'' keterangantambahan,'' dokumentambahan,''referensi,'' capfasilitas,'' IDTKUPenjual,'' NPWPNIKPembeli,
'' jenispembeli,'' NegaraPembeli,'' NomorDokumenPembeli,'' Namapembeli,'' alamatpembeli,'' emailpembeli,'' idtku
) A
order by case when  A.baris = 'END' then 99999 else cast(A.baris as int) end

-- ===================================================================

-- Stored Procedure SPDetailCortax (Versi Normal)
ALTER Procedure [dbo].[SPDetailCortax_Normal]
--declare
@Iduser Varchar(20)
as

--select @Iduser='sa'
--ALTER TABLE DBSODET ADD SATTAX VARCHAR(30)

select A.* from (
select cast(c.RowNum as varchar(5)) Baris,case when isSABenang =0 then 'B' else 'A' end BarangJasa,--a.KodeBrg 
case when isSABenang =0 and a.SATUAN <>'RIT' then '''210200'  else '''000000'  end

KodeBarangJasa,x5.nmbrg NamaBarangJasa,
case when isSABenang =0 then 'UM.0033' else x5.satxbrg  end


NamaSatuanUkur,
a.HARGA HargaSatuan,a.QNT/*a.QNT2*/ JumlahBarangJasa,a.DISCTOT TotalDiskon,Round(a.NDPPRP ,2) DPP 
,a.NDPPRPLain  DPPNilaiLain,12 TarifPPN
,a.NPPNRP  PPN,0 tarifppnbm, 0 ppnbm



from DBINVOICEPLDET   A
left outer join DBBARANG b on a.KodeBrg=b.KODEBRG
left outer join (select ROW_NUMBER() Over(  Order by nobukti) RowNum,nobukti
				from DBINVOICEPL  A
				left outer join DBCUSTOMER b on a.KODECUST=b.KODECUST
				where NoBukti in  (select ID from DBCUSTOMIZE where IDUser=@Iduser and Tipe='tax')
				) c on a.NoBukti=c.NoBukti
LEFT outer join (select a.NoBukti,a.Urut,
--case when c.NAMABRG<>'' then c.NAMABRG else d.NAMABRG end nmbrg	,
	 case when isnull(c.isSABenang,0)=1  and isnull (d.IsJasa,0)=0  Then 'SA-BENANG' 
			else 
			case when d.IsJasa =0 then 'JASA CELUP' 
			 --  case when isnull(e.isSABenang,0)=1 AND F.IsGatra =1 Then 'BENANG JAHIT'
			--else 
			--case when isnull(e.isSABenang,0)=1 and f.IsGatra =1 Then 'BENANG JAHIT' else
			 --case when c.IsJasa =0 then 'JASA CELUP' 
			 ELSE 
			 case when d.ISJASA=1 THEN 'JASA LAIN'
			 ELSE
			  'JASA CELUP'   
			 end 
		--end
      end
	  end +' '+ --d.NAMABRG 
	  	case when isnull(c.isSABenang,0)=1 --and g.NoSJ <>'' --AND F.IsGatra =1 
	Then case when b .NoSJ<>'' then b.NoSJ 
	else  case when a.DetailQnt<>'' then a.DetailQnt else d.namabrg end-----c.NAMABRG end--'BENANG JAHIT' 
	end
	ELSE 
	case when a.DetailQnt<>'' then a.DetailQnt else  d.namabrg end 
	END +' ' +	case when ((f.AliasWarna1<>'') or (Cs.KODECUST='S0005')) then 
	   f.AliasWarna1 
	else 
	   case when b.KodeJenis<>'' then 
	      b.KodeJenis 
	   else 
		  case when b.KodeJenis<>'' then 
		     b.KodeJenis 
		  else NamaWarna 
		  end 
	   end 
	end
	  nmbrg,
     -- case when c.SATX<>'' then c.SATX else case when a.NOSAT=1 then d.SAT1 when a.NOSAT=2 then d.SAT2 when a.NOSAT=3 then d.SAT3 end end satxbrg
     --ISNULL(E.KODETAX,'')
     e.SATUANTAX 
     satxbrg,isSABenang 
					  from dbinvoiceplDet a
					  /*left outer join dbSPBDet b on a.NoSPB=b.NoBukti and a.UrutSPB=b.Urut*/
					  left outer join DBJUALDET b on b.NOBUKTI =a.NoSJ and b.URUT =a.UrutSJ
					  left outer join DBSODET c on c.NOBUKTI =a.NoSO  and a.UrutSo=c.URUT
					  left outer join DBBARANG d on a.KodeBrg=D.KodeBrg
					  LEFT OUTER JOIN vwsatcortax E ON e.SATINVOICE =a.SATUAN 
					  left outer join dbWarna f on f.KodeWarna =a.KODEWARNA 
					  --
					  left outer join DBINVOICEPL g on g.NOBUKTI =a.NOBUKTI 
					  left outer join DBCUSTOMER cs on cs.KODECUST = g.KODECUST 
					  ) x5 on A.NoBukti=x5.NoBukti and A.Urut=x5.Urut	
					  				
where c.NoBukti in  (select ID from DBCUSTOMIZE where IDUser=@Iduser and Tipe='tax') 
-- and a.NoBukti='SPL/INVC/00031/0125'
union all

select 'END' Baris,'' Barangjasa,'' KOdebarangjasa,'' namabarangjasa,'' namasatuanukur,null hargasatuan,null jumlahbarang,null totaldiskon,null dpp,null dppnilailain,null tarifppn
,null ppn,null tarifppnbm,null ppnbm
)A
order by case when  A.baris = 'END' then 99999 else cast(A.baris as int) end
