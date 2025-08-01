


ALTER Procedure [dbo].[Sp_InsertTglInvoiceSPK_old] 

--declare
@tglawal datetime,
@tglakhir Datetime
--select @tglawal ='1-1-2020' ,@tglakhir ='12-31-2020'



truncate table  TempRepSPKInvoice
;with nospkinvoice as
(select distinct d.NoSPK,-- b.Tanggal,--convert(varchar, b.Tanggal, 23) tgl ,
DATEADD(month, DATEDIFF(month, 0, b.TANGGAL ), 0)  tgl
--,CAST(ROW_NUMBER() Over(PARTITION BY nospk,DATEADD(month, DATEDIFF(month, 0, b.TANGGAL ), 0)  Order by b.tanggal) As int) urutinv
from dbInvoicePLDet a
left outer join dbinvoicepl b on b.nobukti=a.nobukti
left outer join dbSPBDet c on c.NoBukti =a.NoSPB and c.Urut = a.UrutSPB 
left outer join DBHASILPRDDET  d on d.NoBukti =c.NoSPP  and d.urut = c.UrutSPP 
--left outer join DbWipMasuk e on e.Nospk =d.NoSPK 
where b.tanggal between @tglawal and @tglakhir)

insert into TempRepSPKInvoice (NoSPK ,Tanggal,TglInvoiceTerakhir ,TglAwalReport,TglAkhirReport,rpwip,UrutPrd--,urutinv
)--

select a.nospk,a.Tanggal,tgl , @tglawal,@tglakhir ,wip  ,
CAST(ROW_NUMBER() Over(PARTITION BY a.nospk Order by a.tanggal) As int)--,urutinv
from DbWipMasuk a
left outer join nospkinvoice b on b.NoSPK=a.Nospk and b.tgl=a.Tanggal 
where a.nospk in (
select nospk  from nospkinvoice ) 

		declare @nospk varchar(30) ,@tglwip datetime,@tglawalreport datetime,@tglakhirreport datetime
		,@tglinvoiceterakhir datetime,@tglhpdterakhir datetime,@tglinv datetime

	declare CurrHslPrd cursor for
		select nospk,tanggal,tglawalreport,tglakhirreport,TglInvoiceTerakhir from TempRepSPKInvoice a	where TglHpdTerakhir  is null 
		--and tanggal<=tglakhirreport 
		order by a.nospk,tanggal
	open CurrHslPrd
	Fetch Next from CurrHslPrd into @nospk,@tglwip,@tglawalreport,@tglakhirreport,@TglInvoiceTerakhir
	  	while @@FETCH_STATUS=0
        	begin
        	  if not exists(select b.TANGGAL from DBHASILPRDDET a
                              left outer join DBHASILPRD b on b.NOBUKTI=a.NOBUKTI
                              where a.NoSPK=@nospk and year(b.TANGGAl)=year(@tglwip) and MONTH(b.TANGGAL)=MONTH(@tglwip))
        	  begin
        	    update TempRepSPKInvoice set TglHpdTerakhir=b.tglhpd--, NobuktiPrd=1
         	    from TempRepSPKInvoice a
         	    left outer join 
         	      (select NoSPK ,min(DATEADD(month, DATEDIFF(month, 0, c.TANGGAL ), 0))tglhpd 
         	       from DBHASILPRDDET b 
        	       left outer join DBHASILPRD c on c.NOBUKTI =b.NOBUKTI 
        	       left outer join dbSPBDet d on d.NoSPP =b.NOBUKTI   and d.UrutSPP =b.urut
        	       left outer join DBinvoicepldet e on e.NoSPB =d.NoBukti and e.UrutSPB=d.Urut
        	       left outer join DBinvoicepl f on f.NOBUKTI =e.NOBUKTI 
        	       where b.NoSPK =@nospk and f.TANGGAL <=@tglakhirreport	
        	       group by NoSPk
        	      )b on b.NoSPK =a.NoSPK 
        	    where a.NoSPK =@nospk and a.tanggal=@tglwip
        	  end else
        	  begin
                update TempRepSPKInvoice set TglHpdTerakhir=b.tglhpd--, NobuktiPrd=2
         	    from TempRepSPKInvoice a
         	    left outer join 
         	      (select NoSPK ,(DATEADD(month, DATEDIFF(month, 0, c.TANGGAL ), 0))tglhpd 
         	       from DBHASILPRDDET b 
        	       left outer join DBHASILPRD c on c.NOBUKTI =b.NOBUKTI 
        	       left outer join dbSPBDet d on d.NoSPP =b.NOBUKTI   and d.UrutSPP =b.urut
        	       left outer join DBinvoicepldet e on e.NoSPB =d.NoBukti and e.UrutSPB=d.Urut
        	       left outer join DBinvoicepl f on f.NOBUKTI =e.NOBUKTI 
        	       where b.NoSPK =@nospk and f.TANGGAL <=@tglakhirreport	
        	      )b on b.NoSPK =a.NoSPK 
        	    where a.NoSPK =@nospk and a.tanggal=b.tglhpd        	  
        	  end 
        	
        	--print @nospk
             Fetch Next from CurrHslPrd into @nospk,@tglwip,@tglawalreport,@tglakhirreport,@TglInvoiceTerakhir
            end 
            	
	close CurrHslPrd
	Deallocate CurrHslPrd

declare CurrHslPrd cursor for
			select NoSPK,DATEADD(month, DATEDIFF(month, 0, a.Tanggal  ), 0)tlginv
			  from 
			dbInvoicePLDet b
			left outer join dbinvoicepl a on a.NoBukti=b.NoBukti 
			left outer join dbSPBDet c on c.nobukti =b.nospb and c.Urut =b.UrutSPB 
			left outer join DBHASILPRDDET d on d.nobukti =c.NoSPP and c.Urut =c.UrutSPP  
        				where d.NoSPK in (select NoSPK  from TempRepSPKInvoice)
			group by NoSPK,DATEADD(month, DATEDIFF(month, 0, a.Tanggal  ), 0)
		
	open CurrHslPrd
	Fetch Next from CurrHslPrd into @nospk,@tglinv
	  	while @@FETCH_STATUS=0
        	begin
        	update TempRepSPKInvoice set TglInvoiceTerakhir =@tglinv   where NoSPK =@nospk and 
        	TglInvoiceTerakhir is null and TglHpdTerakhir <=@tglinv

             Fetch Next from CurrHslPrd into @nospk,@tglinv--,@tglwip,@tglawalreport,@tglakhirreport
            end 
            	
	close CurrHslPrd
	Deallocate CurrHslPrd


--update TempRepSPKInvoice set TGlAwalSPK  =TglAwalReport    where     	TglInvoiceTerakhir between @tglawal  and @tglakhir*
update TempRepSPKInvoice set tglawalspk=Tanggal,urutproses=6  where TglHpdTerakhir =TglInvoiceTerakhir and TGlAwalSPK is null
and TglInvoiceTerakhir >=TglAwalReport


update TempRepSPKInvoice set tglawalspk=tanggal  ,urutproses=7 
 where --TglHpdTerakhir =TglInvoiceTerakhir and 
 TGlAwalSPK is null
and TglInvoiceTerakhir >=TglAwalReport 

update TempRepSPKInvoice set noso =b.NoSO 
from TempRepSPKInvoice a
left outer join DBSPK b on b.NOBUKTI =a.NoSPK 
/*
update TempRepSPKInvoice set rpwipsusulan=RpWip  
where Tanggal > dbo.TglSelesaiSPKInvoice(NoSPK ) --and Tanggal <=
*/
update TempRepSPKInvoice set rpwipsusulan=RpWip  
where
 Tanggal > dbo.TglSelesaiSPKInvoice(NoSPK ) 
and Tanggal  > TglInvoiceTerakhir 
