unit FrmBrows;

interface

uses
  Windows, Messages, SysUtils, Classes, Graphics, Controls, Forms, Dialogs,
  Grids, Db, ADODB, StdCtrls, ExtCtrls, Buttons,
  dxCntner, dxTL, dxDBCtrl, dxDBGrid, cxLookAndFeelPainters,StrUtils,dxGridMenus ;

type
  TFrBrows = class(TForm)
    QuBrows: TADOQuery;
    DsBrows: TDataSource;
    Panel2: TPanel;
    QuBrowGL: TADOQuery;
    DsQuBrowGL: TDataSource;
    Sp_Simpan: TADOStoredProc;
    GridBrows: TdxDBGrid;
    Panel1: TPanel;
    Panel3: TPanel;
    Label1: TLabel;
    EditFilter: TEdit;
    Panel4: TPanel;
    TambahBtn: TSpeedButton;
    KoreksiBtn: TSpeedButton;
    HapusBtn: TSpeedButton;
    Button1: TButton;
    Button2: TButton;
    Button3: TButton;
    Procedure ViewItem(mSelect : String; judul,Lebar: Array of Variant;
                             Var mQuery : TAdoQuery;carikata: string);
    procedure ResizeControls(padOnly: boolean);
    procedure FormKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    Procedure ViewItems(mSelect : String; mParam,judul,Lebar: Array of Variant;
                             Var mQuery : TAdoQuery;Carikata:String);
    procedure FormClose(Sender: TObject; var Action: TCloseAction);
    procedure GridBrowsDblClick(Sender: TObject);
    procedure FormShow(Sender: TObject);
    procedure Button1Click(Sender: TObject);
    procedure Button2Click(Sender: TObject);
    procedure GridBrowsMouseUp(Sender: TObject; Button: TMouseButton;
      Shift: TShiftState; X, Y: Integer);
    procedure Button4Click(Sender: TObject);
    procedure TambahBtnClick(Sender: TObject);
    procedure GridBrowsKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure EditFilterKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure EditFilterChange(Sender: TObject);
  private
    { Private declarations }
//    xFilterDataBrows: String;
    procedure FilterDataBrows;
  public
     MaxWidth : integer;
     Tbl, brGudang, Gudang, NoKira, NoKira1, NoKira2, Customer,Devisi,kodearea,kodesales,sIsiBrg,JenisBahan:string;
     SortBy, IsiData, isidata1, NoSupp, mNopo, mNoSo, mPosisi, mSales1, mSales2, mLokasi, mKodebrg, mKodegdg, Tipeppn, mNobukti : String;
     GrpTipe,GrpPPN,GrpGdg : Byte;
     TglAwal,TglAkhir:Tdatetime;
     EditBrows,EditBrows1, IsLokal,Vw,Ordr,Colm1,Colm2,Fi : String;
     isKecuali,SelectAllRecord, IsTampil : Boolean;
     brTahun, brBulan,JnsPakai: Integer;

    { Public declarations }
  end;

const
     LoC = LoCaseInsensitive;
     LoP = LoPartialKey;
var
  FrBrows: TFrBrows;
  S:array[0..255]of char;

implementation

uses MyGlobal,MyModul,
  MyProcedure;

{$R *.DFM}

Procedure TFrBrows.ViewItem(mSelect : String; judul,Lebar: Array of Variant;
                             Var mQuery : TAdoQuery;carikata: string);
Var
   I: Word;
Begin
  FrBrows.DsBrows.DataSet:=mQuery;
  With mQuery Do
  Begin
    Close;
    Sql.Clear;
      Sql.Add(mSelect);
    Open;
  end;
  with Frbrows.GridBrows do
  begin
     BeginUpdate;
     DestroyColumns;
     KeyField := '';
     DataSource := FrBrows.DsBrows;
     CreateDefaultColumns(DataSource.DataSet, GridBrows);
     KeyField := mQuery.Fields[0].FieldName;
     EndUpdate;

     Columns[0].SummaryFooterField:=mQuery.Fields[0].FieldName;
     Columns[0].SummaryFooterType:=cstCount;
     For I := Low(Judul) to High(Judul) Do
     begin
         Columns[i].Width :=Lebar[i]*5;
         Columns[i].Caption :=Judul[i];
         Columns[i].HeaderAlignment:=taCenter;
         if Columns[i].Caption='N' then
            Columns[i].Visible := false;
         if (mQuery.Fields[i] is TNumericField) then
             (mQuery.Fields[i] as TNumericField).DisplayFormat:=',0.00';
         if (mQuery.Fields[i] is TIntegerField) then
             (mQuery.Fields[i] as TIntegerField).DisplayFormat:=',0';
     end;
     GridBrows.StartSearch(0, EditBrows);
     GridBrows.Invalidate;
  end;
  ResizeControls(false);
end;



Procedure TFrBrows.ViewItems(mSelect : String; mParam,judul,Lebar: Array of Variant;
                             Var mQuery : TAdoQuery;Carikata:String);
Var
   I,J: Word;
Begin
  FrBrows.DsBrows.DataSet:=mQuery;
  With mQuery Do
  Begin
    Close;
    Sql.Clear;
    Sql.Add(mSelect);
    Prepared;
    For J := Low(mParam) to High(mParam) Do
       Parameters[j].Value:=mParam[j];
    Open;
  end;
  with Frbrows.GridBrows do
  begin
     BeginUpdate;
     DestroyColumns;
     KeyField := '';
     DataSource := FrBrows.DsBrows;
     CreateDefaultColumns(DataSource.DataSet, GridBrows);
     KeyField := mQuery.Fields[0].FieldName;
     Columns[0].SummaryFooterField:=mQuery.Fields[0].FieldName;
     Columns[0].SummaryFooterType:=cstCount;
     EndUpdate;
     For I := Low(Judul) to High(Judul) Do
     begin
         Columns[i].Width :=Lebar[i]*5;
         Columns[i].Caption :=Judul[i];
         Columns[i].HeaderAlignment:=taCenter;
         if Columns[i].Caption='N' then
            Columns[i].Visible := false;
         if (mQuery.Fields[i] is TNumericField) then
             (mQuery.Fields[i] as TNumericField).DisplayFormat:=',0.00';
         if (mQuery.Fields[i] is TIntegerField) then
             (mQuery.Fields[i] as TIntegerField).DisplayFormat:=',0';
     end;
     GridBrows.StartSearch(0, EditBrows);
     GridBrows.Invalidate;
  end;
  ResizeControls(false);
end;

procedure TFrBrows.FilterDataBrows;
var xFilterData: String;
    xStrSQL: String;

begin
  xFilterData:=QuotedStr('%'+EditFilter.Text+'%');
  case KodeBrows of
                 91117 : begin
                  ViewItem('select a.nobukti,a.kodebrg,namabrg,noso,namacustsupp from dbspk a left ' +
                    'outer join dbbarang b on b.kodebrg=a.kodebrg left outer ' +
                    'join dbso c on c.nobukti=a.noso left outer join dbcustsupp d on d.kodecustsupp =c.kodecust ',
                  ['NOSPK', 'Kode Barang','Nama Barang','No. SO','Nama Customer'],
                  [30, 30,30,30,30], QuBrows, EditBrows);
                end;
            { 91118 : begin
                 ViewItem('select a.KODEMSN,b.Ket,a.Tanggal,tarif,a.urut from ' +
                   'DBJADWALPRD a  left outer join DBMesin b on b.KodeMsn= ' +
                   'a.KODEMSN left outer join DBProduksidet c on c.NOSPK = ' +
                   'a.NOSPK and c.KODEMSN = a.KODEMSN and c.urutmesin =a.urut where c.KodeMsn  is null  ' +
                   //'and a.urutmesin='+ inttostr(jnspakai) +
                   ' and a.nospk=' +QuotedStr(IsiData) +' order by a.urut' ,
                  ['Kodemsn', 'Keterangan','TanggalSPK','N','Urut'],
                  [15, 30,20,0,5], QuBrows, EditBrows);
                end;}

        500101 : begin
                   SortBy:='KodeCust';
                   Viewitem('select A.NOBUKTI, A.TANGGAL from dbPesanTunai A '+
                             'left outer join dbPesanTunaiDet B on B.NOBUKTI = A.NOBUKTI '+
                             'where B.NoSPK = '''' '+
                             'group by A.NOBUKTI, A.TANGGAL',
                             ['No Pesanan','Tanggal Pesanan'],
                             [25,15],QuBrows,EditBrows);
                 end;
        120302 : begin
                   ViewItem('select A.KodeBrg, A.NamaBrg, A.Sat1, A.Sat2, A.Isi1, A.Isi2, A.NFix '+
                   ' from vwBarang A '+
                   ' where (A.IsBarang=1 or A.IsBarang=2) and A.IsAktif=1 '+
                   ' and (A.KodeBrg like ''%'+EditFilter.Text+'%'' or A.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                   ' order by A.KodeBrg ',
                   ['Kode Barang','Nama Barang','Sat1','Sat2','N','N','N'],
                   [15,40,6,6,1,1,1],QuBrows,EditBrows);
                 end;
        120303 : begin
                   ViewItem('select NoSO,NoBukti ,KodeBrg,Namabrg ,QNT ,SAT_1 '+
                   ' from VwBSTB A '+
                   ' where kodecustsupp = '+quotedstr(NoKira)+
                   //' and (A.KodeBrg like ''%'+EditFilter.Text+'%'' or A.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                   ' order by nobukti ',
                   ['NoSO','No SJ','Kode Barang','Nama Barang','Qnt','Satuan'],
                   [20,40,30,30,10,20],QuBrows,EditBrows);
                 end;

        50402  : begin
                   ViewItem('select KodeBOM, IsDefault from  dbBOM '+
                   ' where KodeBrg='+QuotedStr(NoKira)+
                   ' order by KodeBOM',
                   ['Kode BOM','Default'],
                   [20, 15], QuBrows, EditBrows);
                 end;
        500102 : begin
                   //SortBy:='KodeCust';
                   Viewitem('select A.KodeBrg, B.NAMABRG from dbPesanTunaiDet A '+
                             'left outer join DBBARANG B on B.KODEBRG = A.KodeBrg '+
                             'where A.NoSPK = '''' '+
                             'group by A.KodeBrg, B.NAMABRG ',
                             ['Kode','Nama'],
                             [15,25],QuBrows,EditBrows);
                 end;

         81:begin
               // master Customer member
               SortBy:='KodeCust';
               Viewitem('Select KODECUSTSUPP,NAMACUSTSUPP,Alamat1+Char(13)+Alamat2+Char(13)+kota Alamat,TELPON,DiscMember '+
                         'from DBCUSTSUPP '+
                         'where IsMember = 1 and IsAktif = 1 '+
                         'Order by KODECUSTSUPP',
                         ['Kode','Nama Member','Alamat','Telepon','Disc (%)'],
                         [20,40,40,20,10],QuBrows,EditBrows);
            end;

        // Customer or Supplier
        100101  : begin
                  ViewItem('select distinct a.KodeGdg, a.Nama NamaGdg, a.IsRusak from dbGudang a '+
                           ' left outer join dbPemakaiGdg b on b.kodegdg=a.kodegdg '+
                           ' where A.Kodegdg<>'+ QuotedStr(Gudang)+
                           ' order by a.KodeGdg',
                  ['Kode', 'Nama Gudang','N'],
                  [15, 50, 1], QuBrows, EditBrows);
                end;
        110701 : begin
                   ViewItem(' select X.KodeCustSupp, Y.NamaCustSupp, '+
                            ' rtrim(ltrim(isnull(Y.Alamat1,'''')+case when isnull(Y.Alamat2,'''')='''' then '''' else '' ''+Y.Alamat2 end)) Alamat, '+
                            ' Y.Kota,x.PERKIRAAN ,PPN,Hari,PPN,isppn '+
                            ' from '+
                            '  (select A.KodeCustSupp,a.Perkiraan  from DBPERKCUSTSUPP A, DBPOSTHUTPIUT B '+
                            '   where B.Perkiraan=A.Perkiraan and B.Kode='+QuotedStr(NoKira)+
                            '   group by A.KodeCustSupp,a.Perkiraan  '+
                            '  ) X, DBCUSTSUPP Y '+
                            ' where X.KodeCustSupp=Y.KodeCustSupp '+
                            ' and (Y.KodeCustSupp like ''%'+EditFilter.Text+'%'' or Y.NamaCustSupp like ''%'+EditFilter.Text+'%'') '+
                            ' order by X.KodeCustSupp',
                   ['Kode','Nama','Alamat','Kota','Perkiraan','N','N','N'],
                   [15,40,50,20,10,1,1,1], QuBrows, EditBrows);

                 end;
        1107019:Begin
                    ViewItem(' declare @namaCustSupp varchar(30),@KOdeSls Integer  '+
                             ' select @KOdeSls='+IntTostr(StrToint(Nokira))+'   '+
                             ' set @namaCustSupp=(select Top 1 Count(*)   '+
                             '                                 from DBSALESCUSTOMER A    '+
                             '                                 Left Outer Join DBCUSTSUPP B on A.KodeCustSupp=B.KODECUSTSUPP '+
                             '                                 where KeyNik=@KOdeSls group by A.KodeCustSupp)  '+
                             ' if isnull(@namaCustSupp,0)=0 '+
                             ' begin     '+
                             '         select A.KodeCust KodeCustSupp, A.NAMACUST namaCustSUPP,A.PPN,Alamat,A.Hari DueDate'+
                             '         from vwBrowsCustomer A  where jenis=1 and  (a.kodecust like ''%'+EditFilter.Text+'%'' or a.namaCust like ''%'+EditFilter.Text+'%'') '+
                             //a.kodecust like ''PD.%'' '+
                             ' End      '+
                             ' else    '+
                             ' Begin    '+
                             '         select A.KodeCustSupp, B.NAMACUSTSUPP,Isnull(B.PPN,0) PPN    '+
                             ' , ltrim(B.ALAMAT1+case when ltrim(B.ALAMAT2)<>'''' then char(13)+B.ALAMAT2 else '''' end+  '+
                             ' case when ltrim(isnull(B.KOTA,''''))<>'''' then char(13)+isnull(B.KOTA,'''')+'' ''+B.KodePos else '''' end) ALAMAT    '+
                             ' ,B.hari DueDate '+
                             '         from DBSALESCUSTOMER A '+
                             '         Left Outer Join DBCUSTSUPP B on A.KodeCustSupp=B.KODECUSTSUPP   '+
                             '         where KeyNik=@KOdeSls    '+
                             ' End   ',['Kode','Nama','N','Alamat','N'],
                   [15,40,1,100,1], QuBrows, EditBrows);


                End;
        11001 : begin
                  ViewItem('select a.KodeVls, a.NamaVls, a.Kurs from dbValas a '+
                           'order by a.KodeVls',
                  ['Kode', 'Nama Valas','Kurs'],
                  [10, 30, 10], QuBrows, EditBrows);
                end;
        11002 : begin
                  ViewItem('select KodeGdg,Nama,Alamat from dbGudang  '+
                           'order by KodeGdg',
                  ['Kode', 'Nama '],
                  [10, 30], QuBrows, EditBrows);
                end;
        110022 : begin
                  ViewItem('select KodeGdg,Nama,Alamat from dbGudang  '+
                           'order by KodeGdg',
                  ['Kode', 'Nama '],
                  [10, 30], QuBrows, EditBrows);
                end;
        110021 : begin
                  ViewItem('select KodeGdg,Nama,Alamat from dbGudang where Kodegdg Not in(Select Kodegdg From dbFlPass where UserID='+QuotedStr(IDUser)+') '+
                           'order by KodeGdg',
                  ['Kode Counter', 'Nama Counter','Alamat '],
                  [10, 30,50], QuBrows, EditBrows);
                end;
        11003 : begin
                  ViewItem('select * from dbGudang where Nama Like ''%Bahan%'' '+
                           'order by KodeGdg',
                  ['Kode', 'Nama '],
                  [10, 30], QuBrows, EditBrows);
                end;

        11009 : begin
                  ViewItem('select KodeGdg, Nama from dbGudang '+
                           'order by KodeGdg',
                  ['Kode', 'Nama '],
                  [10, 30], QuBrows, EditBrows);
                end;

        110091 : begin
                  ViewItem('select * from dbGudang where ISProduksi=0 '+
                           'order by KodeGdg',
                  ['Kode', 'Nama '],
                  [10, 30], QuBrows, EditBrows);
                end;

        1100919 : begin
                  ViewItem('select * from dbGudang  '+
                           'order by KodeGdg',
                  ['Kode', 'Nama '],
                  [10, 30], QuBrows, EditBrows);
                end;

        {11004 : begin
                  ViewItem('select * from dbGudang where Nama Not Like ''%Bahan%'' '+
                           'order by KodeGdg',
                  ['Kode', 'Nama '],
                  [10, 30], QuBrows, EditBrows);
                end; }
        11004 : begin
                  ViewItem('select * from dbGudang  '+
                           'order by KodeGdg',
                  ['Kode', 'Nama '],
                  [10, 30], QuBrows, EditBrows);
                end;
        1100411 : begin
                  ViewItem('select KodeBiaya,Keterangan,Perkiraan from dbBIAYA  '+
                           'order by KodeBiaya',
                  ['Kode Biaya', 'Keterangan ','Perkiraan'],
                  [10, 30,30], QuBrows, EditBrows);
                end;
        110011 : begin
                  ViewItem('select a.KodeSubGrp,a.NamaSubGrp from dbSubGroup a Left Outer Join dbGroup b on a.KodeGrp=b.KodeGrp '+
                           'where a.KodeGrp Not in(''BJ'',''BU'') and (KodeSubGrp Like  ''%'+EditFilter.Text+'%'' or NamaSubGrp Like  ''%'+EditFilter.Text+'%'') order by a.KodeSubGrp',
                  ['Kode Sub','Nama Sub'],
                  [20, 30], QuBrows, EditBrows);
                end;
        1100112 : begin
                  ViewItem('select KodeGrp,Nama from  dbGroup '+
                           'where KodeGrp Not in(''BJ'',''BU'') order by KodeGrp',
                  ['Kode Group','Nama'],
                  [15, 30], QuBrows, EditBrows);
                end;
        3001101
            :Begin
               SortBy:='KodeBrg';
               ViewItem('Select A.KODEBRG, A.NAMABRG, A.SAT1 Sat_1, A.Sat2 Sat_2, A.Isi2 Isi '+
               ' from dbBarang A  order by A.KodeBrg',
               ['Kode Barang','Nama Barang','Sat1','Sat2','N'],
               [15,40,5,5,1],QuBrows,EditBrows);
             end;
        30011019
            :Begin
               SortBy:='KodeBrg';
               ViewItems('Select A.KODEBRG, A.NAMABRG, A.SAT1 Sat_1, A.Sat2 Sat_2, A.Isi2 Isi '+
               ' from dbBarang A  '+
               ' Left Outer join DbKOreksidet B on A.KodeBrg = B.kodebrg '+
               ' where kodegrp=:0 '+
               ' and (a.KodeBrg like ''%'+EditFilter.Text+'%'' or a.NamaBrg like ''%'+EditFilter.Text+'%'') '+
               ' and  B.Nobukti =:1 '+
               ' order by A.KodeBrg',
               [nokira,IsiData],['Kode Barang','Nama Barang','Sat1','Sat2','N'],
               [15,40,5,5,1],QuBrows,EditBrows);
             ActiveControl:=EditFilter;
             end;
        2420111: begin // browse barang jadi ada stok
                 SortBy:='KodeBrg';
                 Viewitem('Select Isnull(NFix,0)Nfix,A.Isi2,A.KodeBrg,A.Sat1,A.Sat2, A.NamaBrg, Isnull(b.Qnt,0) QntSaldo, Isnull(b.Qnt2,0) Qnt2Saldo '+
                          'from dbBarang A '+
                          'left Outer Join (select Kodebrg,Sum(SaldoQnt)Qnt,Sum(Saldo2Qnt)Qnt2 from DBStockBrg a Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg where  Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by kodebrg)b On b.kodebrg=a.KodeBrg ' +
                          'where a.isAktif=1 and A.KodeGrp=''BJP'' and (a.KodeBrg like ''%'+EditFilter.Text+'%'' or a.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                          'order by A.KodeBrg ',
                          ['N','N','Kode Barang','N','N', 'Nama Barang', 'Saldo','Saldo2'],
                          [1,1,25,1,1,70,15,15],QuBrows,EditBrows);
                 ActiveControl :=EditFilter;
               end;
        2420114: begin // browse barang jadi ada stok
                 SortBy:='KodeBrg';
                 Viewitems('select NoBukti from vwBrowsOutSO_SPP where KodeBrg = :0 group by NoBukti '+
                          'order by NoBukti ',
                          [IsiData], ['No SO'],
                          [25],QuBrows,EditBrows);
                 ActiveControl :=EditFilter;
               end;

         24201122: begin // browse barang jadi ada stok
                 SortBy:='KodeBrg';
                 Viewitem('Select Isnull(NFix,0)Nfix,A.Isi2,A.KodeBrg,A.Sat1,A.Sat2, A.NamaBrg, Isnull(b.Qnt,0) QntSaldo, Isnull(b.Qnt2,0) Qnt2Saldo '+
                          'from dbBarang A '+
                          'left Outer Join (select Kodebrg,Sum(SaldoQnt)Qnt,Sum(Saldo2Qnt)Qnt2 from DBStockBrg a Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg where Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by kodebrg)b On b.kodebrg=a.KodeBrg ' +
                          'where a.isAktif=1 and A.KodeGrp<>''BJP'' and (a.KodeBrg like ''%'+EditFilter.Text+'%'' or a.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                          'order by A.KodeBrg ',
                          ['N','N','Kode Barang','N','N', 'Nama Barang', 'Saldo','Saldo2'],
                          [1,1,25,1,1,70,15,15],QuBrows,EditBrows);
               end;
         2420112: begin // browse barang jadi ada stok
                 SortBy:='KodeBrg';
                 Viewitems('Select distinct Isnull(NFix,0)Nfix,A.Isi2,A.KodeBrg,A.Sat1,A.Sat2, A.NamaBrg, Isnull(b.Qnt,0) QntSaldo, Isnull(b.Qnt2,0) Qnt2Saldo '+
                          'from dbBarang A '+
                          ' Left Outer Join DbKOreksidet M1 on A.kodebrg = M1.kodebrg '+
                          'left Outer Join (select a.Kodegdg,Kodebrg,Sum(SaldoQnt)Qnt,Sum(Saldo2Qnt)Qnt2 from DBStockBrg a Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg where a.Kodegdg='+QuotedStr(IsiData)+ ' and Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by a.Kodegdg,kodebrg)b On b.kodebrg=a.KodeBrg ' +
                          'where a.isAktif=1 and ( A.KodeGrp<>''BJP'') and (a.KodeBrg like ''%'+EditFilter.Text+'%'' or a.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                          '  --and  M1.Nobukti <>:0 '+
                          'order by A.KodeBrg ',[Nokira],
                          ['N','N','Kode Barang','N','N', 'Nama Barang', 'Saldo','Saldo2'],
                          [1,1,25,1,1,70,15,15],QuBrows,EditBrows);
               end;
        2420113: begin // browse pesanan sesuai kodebarang
                 SortBy:='KodeBrg';
                 Viewitems('select X.NoBukti, X.Urut, X.KodeBrg, B.NAMABRG,SUM(X.Qnt) Sisa  '+
                          'from '+
                          '( '+
                          '	select A.NOBUKTI NoBukti, A.URUT Urut, A.KodeBrg, A.QNT Qnt ' +
                          '	from dbPesanTunaiDet A ' +
                          '	union all '+
                          '	select A.NOBUKTIORDER, A.URUTORDER, B.KODEBRG,-A.QTY '+
                          '	from DBSPKORDER A '+
                          '	left outer join DBSPKBJDET B on B.NOBUKTI = A.NOBUKTI and B.URUT = A.URUTSPK '+
                          ') X '+
                          'left outer join DBBARANG B on B.KODEBRG = X.KodeBrg '+
                          'where X.KodeBrg = :0 '+
                          'group by X.NoBukti, X.Urut, X.KodeBrg, B.NAMABRG '+
                          'order by X.NOBUKTI, X.URUT ',[IsiData],
                          ['No Bukti','Urut','Kode Barang','Nama Barang','Sisa'],
                          [25,10,25,50,25],QuBrows,EditBrows);
               end;
         2420115: begin // browse barang jadi ada stok
                 SortBy:='KodeBrg';
                 Viewitems('Select distinct Isnull(NFix,0)Nfix,A.Isi2,A.KodeBrg,A.Sat1,A.Sat2, A.NamaBrg, Isnull(b.Qnt,0) QntSaldo, Isnull(b.Qnt2,0) Qnt2Saldo '+
                          'from dbBarang A '+
                          ' Left Outer Join DbKOreksidet M1 on A.kodebrg = M1.kodebrg '+
                          'left Outer Join (select a.Kodegdg,Kodebrg,Sum(SaldoQnt)Qnt,Sum(Saldo2Qnt)Qnt2 from DBStockBrg a Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg where a.Kodegdg='+QuotedStr(IsiData)+ ' and Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by a.Kodegdg,kodebrg)b On b.kodebrg=a.KodeBrg ' +
                          'where a.isAktif=1 and ( A.KodeGrp=''BJP'') and (a.KodeBrg like ''%'+EditFilter.Text+'%'' or a.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                          '  --and  M1.Nobukti <>:0 '+
                          'order by A.KodeBrg ',[Nokira],
                          ['N','N','Kode Barang','N','N', 'Nama Barang', 'Saldo','Saldo2'],
                          [1,1,25,1,1,70,15,15],QuBrows,EditBrows);
               end;

        242011: begin // browse BPPB
                 SortBy:='KodeBrg';
                 Viewitem('Select Isnull(NFix,0)Nfix,Isi2,A.KodeBrg,A.Sat1,A.Sat2, A.NamaBrg, Isnull(b.Qnt,0) QntSaldo, Isnull(b.Qnt2,0) Qnt2Saldo '+
                          'from dbBarang A '+
                          'left Outer Join (select a.KodeGdg,Kodebrg,Sum(SaldoQnt)Qnt,Sum(Saldo2Qnt)Qnt2 from DBStockBrg a Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg where a.Kodegdg='+QuotedStr(IsiData)+ ' and Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by a.kodegdg,kodebrg)b On b.kodebrg=a.KodeBrg ' +
                          'where A.KodeGrp in (''BJB'',''BJP'') '+
                          //and (Isnull(b.Qnt,0)>0 or Isnull(b.Qnt2,0)>0) '+
                          ' and a.isAktif=1 and (a.KodeBrg like ''%'+EditFilter.Text+'%'' or a.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                          'order by A.KodeBrg ',
                          ['N','N','Kode Barang','N','N', 'Nama Barang', 'Saldo','Saldo2'],
                          [1,1,25,1,1,70,15,15],QuBrows,EditBrows);
               end;
        242012: begin // browse PPL
                 SortBy:='KodeBrg';
                 Viewitem('Select A.KodeBrg, A.NamaBrg, Isnull(Sisa,0) QntSaldo,QntBPPB,Stock '+
                          'from dbBarang A '+
                          'left outer join (select a.Kodebrg,0.00 QntBPPB,Isnull(b.Qnt,0)Stock,0.00 Sisa from DBBPPBDET a '+
                          'left Outer Join (select Kodebrg,Sum(SaldoQnt)Qnt,Sum(Saldo2Qnt)Qnt2 from DBStockBrg where Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by kodebrg)b On b.kodebrg=a.KodeBrg group by a.KodeBrg,b.Qnt)b On a.KODEBRG=b.Kodebrg ' +
                          'where a.isAktif=1 and (a.KodeBrg like ''%'+EditFilter.Text+'%'' or a.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                          ' and a.KodeGrp not in (''BJ'',''BU'') '+
                          'order by A.KodeBrg ',
                          ['Kode Barang', 'Nama Barang', 'Saldo','N','N'],
                          [25,70,15,1,1],QuBrows,EditBrows);
               end;
        242013: begin // browse BPPB
                 SortBy:='KodeBrg';
                 Viewitem('Select Isnull(NFix,0)Nfix,Isi2,A.KodeBrg,kodebhn,A.Sat1,' +
                   'A.Sat2, A.NamaBrg, Isnull(b.Qnt,0) QntSaldo, Isnull(b.Qnt2,0) Qnt2Saldo '+
                          'from dbBarang A '+
                          'left Outer Join (select a.KodeGdg,Kodebrg,Sum(SaldoQnt)Qnt,Sum(Saldo2Qnt)Qnt2 from DBStockBrg a Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg where a.Kodegdg='+QuotedStr(IsiData)+ ' and Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by a.kodegdg,kodebrg)b On b.kodebrg=a.KodeBrg ' +
                          'where A.KodeGrp not in (''BJP'') '+
                          //and (Isnull(b.Qnt,0)>0 or Isnull(b.Qnt2,0)>0) '+
                          ' and a.isAktif=1 and (a.KodeBrg like ''%'+EditFilter.Text+'%'' or a.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                          ' and dbo.cekGudanglLpb(a.KODEBRG )='+QuotedStr(IsiData)+' '+

                          'order by A.KodeBrg ',
                          ['N','N','Kode Barang','Kode Bahan','N','N', 'Nama Barang', 'Saldo','Saldo2'],
                          [1,1,25,25,1,1,70,15,15],QuBrows,EditBrows);
               end;
        {252013: begin // browse BPPB
                 SortBy:='KodeBrg';
                 Viewitem(' declare @kodegd varchar(6),@bln int, @thn int ,@nospk varchar(30) '+
                          ' select @kodegd ='+QuotedStr(IsiData)+' '+

                          ' select x.*,b.qntambil, b.qntspk from ( '+
                         ' Select Isnull(NFix,0)Nfix,Isi2,A.KodeBrg,kodebhn,A.Sat1,' +
                   'A.Sat2, A.NamaBrg, Isnull(b.Qnt,0) QntSaldo, Isnull(b.Qnt2,0) Qnt2Saldo '+
                          'from dbBarang A '+
                          'left Outer Join (select a.KodeGdg,Kodebrg,Sum(SaldoQnt)Qnt,Sum(Saldo2Qnt)Qnt2 from DBStockBrg a '+
                          'Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg where a.Kodegdg='+QuotedStr(IsiData)+ '   and Bulan='+PeriodBln+
                          ' and Tahun='+PeriodThn+' group by a.kodegdg,kodebrg)b On b.kodebrg=a.KodeBrg ' +
                          'where A.KodeGrp not in (''BJP'') '+
                          ' and a.isAktif=1 and (a.KodeBrg like ''%'+EditFilter.Text+'%'' or a.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                          ' and dbo.cekGudanglLpb(a.KODEBRG )='+QuotedStr(IsiData)+' '+
                          ' and  (a.KODEBRG in (select KODEBRG  from DBSPKDET  where  @kodegd in(''G001'',''G002'') and NOBUKTI ='+QuotedStr(nokira1)+ '))' +
                          ' /*and (a.KODEBRG in (select KODEBRG  from DBSPKDET  where  @kodegd not in(''G001'',''G002'') and NOBUKTI ='+QuotedStr(nokira1)+ '))*/'+

                          ' )x      ' +
                          ' left outer join (  '+
                          ' select NoSPK,a.kodebrg  ,SUM(a.Qnt )qntambil,SUM(b.QNT )qntspk from DBPenyerahanBhnDET a '+
                          ' left outer join DBSPKDET b on b.NOBUKTI =a.NoSPK and a.kodebrg =b.KODEBRG '+
                          ' where NoSPK  = '+QuotedStr(nokira1)+ ' '+
                          ' group by NoSPK,a.kodebrg ) b on b.kodebrg =x.KODEBRG '+
                          '  union all  '+
                          '   Select Isnull(NFix,0)Nfix,Isi2,A.KodeBrg,kodebhn,A.Sat1,A.Sat2, A.NamaBrg, Isnull(b.Qnt,0) QntSaldo, Isnull(b.Qnt2,0) Qnt2Saldo ,0,0 '+
                          '  from dbBarang A '+
                          '  left Outer Join (select a.KodeGdg,Kodebrg,Sum(SaldoQnt)Qnt,Sum(Saldo2Qnt)Qnt2 from DBStockBrg a '+
                          '  Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg where a.Kodegdg='+QuotedStr(IsiData)+ ' and Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by a.kodegdg,kodebrg)b '+
                          '  On b.kodebrg=a.KodeBrg where A.KodeGrp not in (''BJP'') '+
                          'and a.isAktif=1 and (a.KodeBrg like ''%'+EditFilter.Text+'%'' or a.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                          '   and dbo.cekGudanglLpb(a.KODEBRG )=@kodegd and @kodegd not in (''G001'',''G002'',''G003'') '+
                          ' /*order by KodeBrg*/ ',
                          ['N','N','Kode Barang','N','N','N', 'Nama Barang', 'Saldo','Saldo2','Qnt Ambil','Qnt Rencana'],
                          [1,1,25,1,1,1,70,15,15,10,10],QuBrows,EditBrows);
               end;}
        252013: Begin
                 Viewitem(' declare @kodegd varchar(6),@bln int, @thn int ,@nospk varchar(30),@kodebrg varchar(50) '+
                          ' select @kodegd='+QuotedStr(IsiData)+',@bln='+PeriodBln+',@thn='+PeriodThn+',@nospk='+QuotedStr(nokira1) + ',@kodebrg='+ quotedstr(EditFilter.Text)  +
                          ' select * from cekBahanSPK(@kodegd,@bln,@thn,@nospk,@kodebrg) '+
                          ' where (sisaambil>0 or sisaambil is null  ) order by nfix ',//or (sisaambil =0 and @kodegd =''G002'')
                          ['N','N','Kode Barang','N','N','N', 'Nama Barang', 'N','N','Qnt Rencana','Qnt Ambil','Tambahan','Qnt Sisa'],
                          [1,1,25,1,1,1,70,1,1,10,10,10,10],QuBrows,EditBrows);

                end;
        252014: begin
                 SortBy:='KodeBrg';
                 Viewitem('Select Isnull(NFix,0)Nfix,Isi2,A.KodeBrg,kodebhn,A.Sat1,' +
                   'A.Sat2, A.NamaBrg, 0 QntSaldo, 0 Qnt2Saldo,0 sisaambil '+
                          'from dbBarang A '+
                          'left Outer Join (select a.KodeGdg,Kodebrg,Sum(SaldoQnt)Qnt,Sum(Saldo2Qnt)Qnt2 from DBStockBrg a Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg where a.Kodegdg='+QuotedStr(IsiData)+ ' and Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by a.kodegdg,kodebrg)b On b.kodebrg=a.KodeBrg ' +
                          'where A.KodeGrp  in (''BLL'') '+
                          //and (Isnull(b.Qnt,0)>0 or Isnull(b.Qnt2,0)>0) '+
                          ' and a.isAktif=1 and (a.KodeBrg like ''%'+EditFilter.Text+'%'' or a.NamaBrg like ''%'+EditFilter.Text+'%'') '+
                        //  ' and dbo.cekGudanglLpb(a.KODEBRG )='+QuotedStr(IsiData)+' '+

                          'order by A.KodeBrg ',
                          ['N','N','Kode Barang','Kode Bahan','N','N', 'Nama Barang', 'N','N','N'],
                          [1,1,25,25,1,1,70,15,15,1],QuBrows,EditBrows);


                end;
        2420121: begin // browse PPL
                 SortBy:='KodeBrg';
                 Viewitem('Select A.KodeBrg,a.kodebhn, A.NamaBrg,Isnull(Proses,0)IsJasa,perkpers '+
                          ' from dbBarang A '+
                          ' left outer join DBSubGroupJnsTambah b on b.KodeGrp=' +
                          ' a.KODEGRP and b.KodeSubGrp=a.KODESUBGRP '+
                          ' left outer join dbsubgroup c on c.kodegrp = ' +
                          'a.KODEGRP and c.KodeSubGrp=a.KODESUBGRP'+
                          ' where a.isAktif=1  and ( b.Keterangan like ''%'+EditFilter.Text+'%'' ) '+
                          //or a.NamaBrg like ''%'+EditFilter.Text+'%''
                          '  /*or Isnull(A.Proses,0)=1*/ order by A.KodeBrg ',
                          ['Kode Barang','Kode Bahan', 'Nama Barang','N','Kode Biaya'],
                          [25,25,70,1,10],QuBrows,EditBrows);
               end;
        242019: begin // Browse Barang pada Pemakaian Bahan
                 SortBy:='KodeBrg';
                 Viewitem('select A.Urut, A.KodeBrg, Br.NamaBrg, 1 NFix, A.NoSat, case when A.NoSat=1 then Br.Sat1 else Br.Sat2 end Satuan, '+
                          ' A.QntSPK, A.QntPakai, A.QntSisa '+
                          ' from vwOutSPK_Pakai A '+
                          ' left outer join dbBarang Br on Br.KodeBrg=A.KodeBrg '+
                          ' where (A.NoBukti='+QuotedStr(NoKira1)+') --and A.QntSisa>0 '+
                          ' order by A.Urut ',
                          ['N','Kode Barang','Nama Barang','N','N','Satuan','Qnt SPK','Qnt Pakai','Sisa'],
                          [1,22,40,1,1,5,12,12,12],QuBrows,EditBrows);
               end;
        242014: begin  //brwse PPL
                 SortBy:='Nobukti';
                 Viewitem('select a.NoBukti from DBPPLDET a  '+
                          'Left Outer Join (select NoPPL,Kodebrg,SUM(Qnt*isi)Qnt from DBPODET group by NoPPL,Kodebrg)b On a.Nobukti=b.NoPPL and a.kodebrg=b.KODEBRG  '+
                          'left Outer Join DBBARANG c On c.KODEBRG=a.kodebrg '+
                          'group by a.NoBukti,b.Qnt  ' +
                          'having SUM(a.Qnt*isi)-Isnull(b.Qnt,0)<>0',
                          ['NoBukti'],
                          [25],QuBrows,EditBrows);
                end;
        242015: begin  //brwse PPL barang
                 SortBy:='Nobukti';
                 Viewitem('select a.KodeBrg, a.NamaBrg, a.Sat, a.NoSat, a.Isi, a.Qnt, a.QntPO, a.SisaPPL, a.NoBukti, a.Urut,a.tolerate,a.proses isjasa,NamaBarang,perkiraan '+
                          'from vwOutPPL a where perkiraan=  '+QuotedStr(IsiData)+
                          'order by a.KodeBrg, a.NoSat, a.NoBukti',
                          ['Kode Barang','Nama Barang','Sat','N','N','Qnt PR','Qnt PO','Sisa PR','No. PR','N','N','N','Nama Barang Jasa','Perkiraan'],
                          [18,45,6,0,0,10,10,10,22,0,0,0,30,20],QuBrows,EditBrows);
                end;
        242016: begin  //brwse PB
                SortBy:='Nobukti';
                Viewitem('Select A.Nobukti, C.Tanggal , Nospk'+#13+
                         'From DBPenyerahanBhnDET A'+#13+
                         '     left outer join (Select x.NoPenyerahanBHN,  SUM(x.Qnt) Qnt, SUM(x.Qnt2) Qnt2'+#13+
                         '                      from DBRPenyerahanBhnDET x'+#13+
                         '                           left outer join DBRPenyerahanBhn y on y.Nobukti=x.Nobukti'+#13+
                         '                      Group by x.NoPenyerahanBHN) B on B.NoPenyerahanBHN=A.Nobukti'+#13+
                         '     left outer join DBPenyerahanBhn c on c.Nobukti=A.Nobukti'+#13+
                         'where Cast(Case when Case when C.IsOtorisasi1=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi2=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi3=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi4=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi5=1 then 1 else 0 end=C.MaxOL then 0'+#13+
                         '                  else 1'+#13+
                         '             end As Bit)=0 and ((A.Qnt-isnull(B.Qnt,0)>0) or (A.Qnt2-isnull(B.Qnt2,0)>0)) and C.IsSampel='+IntToStr(GrpTipe)+#13+
                         'Group by A.Nobukti, C.Tanggal, nospk',
                         ['No. Bukti', 'Tanggal','No SPK'],
                         [45,50,45],QuBrows,EditBrows);
                end;
         242017: begin  //brwse PB
                SortBy:='Nobukti';
                Viewitem('Select A.Nobukti, A.urut, A.kodebrg, d.NAMABRG, A.Qnt,A.Qnt2, A.Isi, A.NoSat, (A.Qnt-isnull(B.Qnt,0)) QntSisa,(A.Qnt2-isnull(B.Qnt2,0)) Qnt2Sisa,'+#13+
                         '       d.NFix'+#13+
                         'From DBPenyerahanBhnDET A'+#13+
                         '     left outer join (Select x.NoPenyerahanBHN,x.UrutPenyerahanBHN,  SUM(x.Qnt) Qnt, SUM(x.Qnt2) Qnt2'+#13+
                         '                      from DBRPenyerahanBhnDET x'+#13+
                         '                           left outer join DBRPenyerahanBhn y on y.Nobukti=x.Nobukti'+#13+
                         '                      Group by x.NoPenyerahanBHN, x.urutPenyerahanBHN) B on B.NoPenyerahanBHN=A.Nobukti and B.urutPenyerahanBHN=A.urut'+#13+
                         '     left outer join DBPenyerahanBhn c on c.Nobukti=A.Nobukti'+#13+
                         '     left Outer join DBBARANG d on d.KODEBRG=a.kodebrg'+#13+
                         'where Cast(Case when Case when C.IsOtorisasi1=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi2=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi3=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi4=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi5=1 then 1 else 0 end=C.MaxOL then 0'+#13+
                         '                  else 1'+#13+
                         '             end As Bit)=0 and ((A.Qnt-isnull(B.Qnt,0)>0) or (A.Qnt2-isnull(B.Qnt2,0)>0))'+#13+
                         '      and A.nobukti='+QuotedStr(NoKira)+' and C.IsSampel='+IntToStr(GrpTipe)+#13+
                         'Order by A.kodebrg',
                         ['N','N','Kode Brg.','Nama Brg.','N','N','N','N','Saldo','Saldo2','N'],
                         [1,1,25,50,1,1,1,1,15,15,1],QuBrows,EditBrows);
                end;
        242018: begin  //brwse Beli Utk Invoice
                SortBy:='Nobukti';
                Viewitem('select a.NoBukti,a.KodeSupp,NamaCustSupp,Sum(NDPP)NDPP,Sum(NPPN)NPPN,Sum(NNET)NNET from dbBeli a  '+
                         'Left Outer Join dbBeliDet b On a.NoBukti=b.noBukti      '+
                         'Left Outer join dbCustSupp c On c.KodeCustSupp=a.KodeSupp  '+
                         'where a.NoBukti Not in(select NoBeli from dbInvoiceDet)  Group by a.NoBukti,a.KodeSupp,NamaCustSupp',
                         ['No. Pemebelian','N','Nama Supp.','Nilai DPP','Nilai PPN','Nilai Net'],
                         [25,1,20,15,15,15],QuBrows,EditBrows);

                end;
        242020: begin  //brwse PB
                SortBy:='Nobukti';
                Viewitem('Select A.Nobukti, C.Tanggal , Nospk'+#13+
                         'From DBPenyerahanBhnDET A'+#13+
                         '     left outer join (Select x.NoPenyerahanBHN,  SUM(x.Qnt) Qnt, SUM(x.Qnt2) Qnt2'+#13+
                         '                      from DBRPenyerahanBhnDET x'+#13+
                         '                           left outer join DBRPenyerahanBhn y on y.Nobukti=x.Nobukti'+#13+
                         '                      Group by x.NoPenyerahanBHN) B on B.NoPenyerahanBHN=A.Nobukti'+#13+
                         '     left outer join DBPenyerahanBhn c on c.Nobukti=A.Nobukti'+#13+
                         'where Cast(Case when Case when C.IsOtorisasi1=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi2=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi3=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi4=1 then 1 else 0 end+'+#13+
                         '                       Case when C.IsOtorisasi5=1 then 1 else 0 end=C.MaxOL then 0'+#13+
                         '                  else 1'+#13+
                         '             end As Bit)=0 and flagtipe=1 and  ((A.Qnt-isnull(B.Qnt,0)>0) or (A.Qnt2-isnull(B.Qnt2,0)>0)) and C.IsSampel='+IntToStr(GrpTipe)+#13+
                         'Group by A.Nobukti, C.Tanggal, nospk',
                         ['No. Bukti', 'Tanggal','No SPK'],
                         [45,50,45],QuBrows,EditBrows);
                end;

        2420182: begin  //brwse  Utk Invoice PL
                SortBy:='Nobukti';
                Viewitem('select a.NoBukti,a.KodeSupp,NamaCustSupp,Sum(NDPP)NDPP,Sum(NPPN)NPPN,Sum(NNET)NNET from dbInvoicePL a  '+
                         'Left Outer Join dbInvoicePLDet b On a.NoBukti=b.noBukti      '+
                         'Left Outer join dbCustSupp c On c.KodeCustSupp=a.KodeSupp  '+
                         'Group by a.NoBukti,a.KodeSupp,NamaCustSupp',
                         ['No. Pemebelian','N','Nama Supp.','Nilai DPP','Nilai PPN','Nilai Net'],
                         [25,1,20,15,15,15],QuBrows,EditBrows);

                end;
        2420181 : begin
                  SortBy:='Nobukti';
                  ViewItem('select NOBukti,a.KodeVls,Kurs,a.PPN,TipeBayar,a.Hari from dbPO a '+
                           'left outer join DBCUSTSUPP b on b.KODECUSTSUPP=a.KODESUPP '+
                           'where KodeSupp='+QuotedStr(IsiData)+' /*and NoBukti ' +
                           'Not In(Select Isnull(NoPO,'''') from dbInvoice)*/ and ' +
                           'isotorisasi1=1 and b.PPN =1  order by NoBukti ' ,
                  ['No. PO','N','N','N','N','N'],
                  [30,1,1,1,1,1], QuBrows, EditBrows);
                end;
        243010: begin // Browse No. BPPB pada Penyerahan Barang
                 SortBy:='KodeBrg';
                 Viewitem('Select A.NoBukti, A.Tanggal, A.KodeGdgT, A.KDDep, Dp.NmDep '+
                          ' from vwOutBPPB A0 '+
                          ' left outer join dbBPPB A on A.NoBukti=A0.NoBukti '+
                          ' left outer join DBDEPART Dp on Dp.KDDEP=A.KDDEP '+
                          ' where A0.QntSisa>0 and a.isotorisasi1=1 group by A.NoBukti, A.Tanggal, A.KodeGdg, A.KDDep, Dp.NmDep '+
                          ' order by A.Tanggal, A.NoBukti ',
                          ['No. Bukti','Tanggal','Gudang','N','Departemen'],
                          [25,15,10,1,25],QuBrows,EditBrows);
               end;
        243011: begin // Browse Barang pada Penyerahan Barang
                 SortBy:='KodeBrg';
                 Viewitem('select A.Urut, A.KodeBrg, Br.NamaBrg, Br.NFix, A.NoSat, case when A.NoSat=1 then Br.Sat1 else Br.Sat2 end Satuan, '+
                          '       case when A.NoSat=1 then A.Qnt else A.Qnt2 End Qnt,'+#13+
                          '       case when A.NoSat=1 then A.QntBPPBT else A.Qnt2BPPBT End QntBPPBT,'+#13+
                          '       case when A.NoSat=1 then A.QntSisa else A.Qnt2Sisa End QntSisa'+#13+
                          ' from vwOutBPPB A '+
                          ' left outer join dbBarang Br on Br.KodeBrg=A.KodeBrg '+
                          ' where A.NoBukti='+QuotedStr(NoKira1)+' and A.QntSisa>0 '+
                          ' order by A.Urut ',
                          ['N','Kode Barang','Nama Barang','N','N','Satuan','Minta','Terima','Sisa'],
                          [1,22,40,1,1,5,12,12,12],QuBrows,EditBrows);
               end;
        243012: begin // Browse No. SPK pada Pemakaian Bahan
                 SortBy:='KodeBrg';
                 Viewitem('Select A0.KodeBrg,namabrg, a0.QntSPK,a0.QntPakai,a0.QntSisa,  '+
                          '  Isnull(b.Qnt,0) QntSaldo, Isnull(b.Qnt2,0) Qnt2Saldo  ' +
                          '  ' +
                          ' from vwOutSPK_Pakai A0 '+
                          ' left outer join dbSPK A on A.NoBukti=A0.NoBukti '+
                          ' left outer join dbbarang c on c.kodebrg=' +
                          'a0.kodebrg left Outer Join ' +
                          '(select a.KodeGdg,Kodebrg,Sum(SaldoQnt)Qnt,Sum(' +
                          'Saldo2Qnt)Qnt2 from DBStockBrg a '+
                          'Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg'+
                          ' where a.Kodegdg='+QuotedStr(IsiData)+ ' and Bulan='+PeriodBln+' and Tahun='+PeriodThn+' ' +
                          ' group by a.kodegdg,kodebrg)b On b.kodebrg=a.KodeBrg  '+
                          ' where /*A0.QntSisa>0 and*/ a0.nobukti = '+QuotedStr(NoKira1)+' group by A.NoBukti, A.Tanggal, A0.Kodebrg,namabrg,b.Qnt,b.Qnt2 ,a0.QntSPK,a0.QntPakai,a0.QntSisa '+
                          ' order by A.Tanggal, A.NoBukti  ',
                          ['Kode Barang', 'Nama Barang', 'Qnt Bahan Spk',' Qnt Pakai', 'Qnt Sisa ', 'Saldo 1', 'Saldo 2'],
                          [25,25,10,10,10,0,0],QuBrows,EditBrows);
               end;
          {2430121: begin // Browse No. SPK pada Pemakaian Bahan
                 SortBy:='KodeBrg';
                 Viewitem('Select A.NoBukti, A.Tanggal,a.noso,c.namacustsupp,a.kodebrg, namabrg, a.Qnt '+
                          ' from vwOutSPK_Pakai A0 '+
                          ' left outer join dbSPK A on A.NoBukti=A0.NoBukti '+
                          ' left outer join dbso b on b.NoBukti=' +
                          'A.Noso '+
                          ' left outer join dbsodet e on e.NoBukti=' +
                          'A.Noso and a.urutso = e.urut'+
                          ' left outer join dbcustsupp c on c.kodecustsupp=' +
                          'b.kodecust '+
                          ' left outer join dbbarang d on d.kodebrg=' +
                          'a.kodebrg '+

                          ' where A0.QntSisa<>0 and a.TANGGAL > ''1-1-2015''   '+
                          ' and A.NoBukti like ''%'+EditFilter.Text+'%''  '+
                          ' group by A.NoBukti, A.Tanggal,a.noso,c.namacustsupp,a.kodebrg, namabrg,a.qnt '+
                          ' order by A.Tanggal, A.NoBukti ',
                          ['No. Bukti','Tanggal','No SO','Nama Customer','Kode Barang', 'Nama Barang','Qnt SO'],
                          [25,15,20,20,20,20,20],QuBrows,EditBrows);
               end;}
2430121: begin // Browse No. SPK pada Pemakaian Bahan
                 SortBy:='KodeBrg';
                 Viewitems(' Declare @tglhpd datetime   ' +
                          ' set @tglhpd =:0' +
                          ' Select A.NoBukti, A.Tanggal,a.noso,c.namacustsupp,a.kodebrg, namabrg, a.Qnt '+
                          ' from /*vwOutSPK_Pakai A0 '+
                          ' left outer join */ dbSPK A /*on A.NoBukti=A0.NoBukti*/ '+
                          ' left outer join dbso b on b.NoBukti=' +
                          'A.Noso '+
                          ' left outer join dbsodet e on e.NoBukti=' +
                          'A.Noso and a.urutso = e.urut'+
                          ' left outer join dbcustsupp c on c.kodecustsupp=' +
                          'b.kodecust '+
                          ' left outer join dbbarang d on d.kodebrg=' +
                          'a.kodebrg '+

                          ' where /*A0.QntSisa<>0*/  @tglhpd<=case when dbo.cekhpd(a.NOBUKTI ) =''1900-01-31 23:59:59.000'' then ''2100-1-1'' else dbo.cekhpd(a.NOBUKTI )         end  and a.TANGGAL > ''1-1-2015''   '+
                          //' and A.NoBukti like ''%'+EditFilter.Text+'%''  '+
                          ' group by A.NoBukti, A.Tanggal,a.noso,c.namacustsupp,a.kodebrg, namabrg,a.qnt '+
' union '+
' select NoSPK ,b.TANGGAL ,NoSO ,NAMACUSTSUPP ,a.KODEBRG ,NAMABRG ,SUM(a.QNT ) ' +
'qnthpd from DBHASILPRDDET a '+
' left outer join ( select TANGGAL ,NOBUKTI ,NoSO,UrutSO  ,Qnt qntspk from ' +
'DBSPK) b on b.NOBUKTI =a.NoSPK  '+
' left outer join dbso c on c.NoBukti=b.Noso '+
' left outer join dbsodet d on d.NoBukti=b.Noso and b.urutso = d.urut '+
' left outer join dbbarang e on e.kodebrg=a.kodebrg   '+
' left outer join dbcustsupp f on f.kodecustsupp=c.kodecust '+
' group by nospk,NOSo,b.TANGGAL ,NAMACUSTSUPP,a.KODEBRG,NAMABRG '+
' having sum(qntspk) -SUM(a.QNT ) >0 '+

                          ' order by A.Tanggal, A.NoBukti ',  [TglAwal],
                          ['No. Bukti','Tanggal','No SO','Nama Customer','Kode Barang', 'Nama Barang','Qnt SO'],
                          [25,15,20,20,20,20,20],QuBrows,EditBrows);
               end;
        110013 : begin
                  ViewItem('select a.KodeGrp,a.Nama from dbGroup a '+
                           'where KodeGrp ='+IsiData+' order by a.KodeGrp',
                  ['Kode Group','Nama'],
                  [15, 30], QuBrows, EditBrows);
                end;

         110014 : begin
                  ViewItem('select a.KodeGrp,b.Nama,a.KodeSubGrp,a.NamaSubGrp from dbSubGroup a Left Outer Join dbGroup b on a.KodeGrp=b.KodeGrp '+
                           'where a.Kodegrp='+QuotedStr(isidata1)+' and (a.KodeSubGrp Like  ''%'+EditFilter.Text+'%'' or a.NamaSubGrp Like  ''%'+EditFilter.Text+'%'') order by a.KodeSubGrp',
                  ['Kode Group','Nama', 'Kode Sub','Nama Sub'],
                  [10, 30, 20, 30], QuBrows, EditBrows);
                end;
        110012 : begin
                  ViewItem('select Nama,KodeGrp  from dbGroup '+
                           'where KodeGrp Like  ''%'+IsiData+'%'' order by KodeGrp',
                  ['Nama', 'Kode Group'],
                  [30, 10], QuBrows, EditBrows);
                end;
        110015 : begin
                  ViewItem('select KodeJnsTambahan, NAMA from DBJNSTambahan ',
                  ['Kode Jns Tambahan', 'Nama Jns Tambahan'],
                  [15, 20], QuBrows, EditBrows);
                end;
        110016 : begin
                  ViewItem('select KODEBRG, NAMABRG from DBBARANG A where  A.Kodegrp Not in('+QuotedStr('BJ')+','+Quotedstr('BU')+')     ',
                  ['Kode Bahan', 'Nama Bahan'],
                  [15, 20], QuBrows, EditBrows);
                end;

        1002  : begin
                  ViewItem('select Kodebag,Namabag from dbbagian order by Kodebag',
                  ['Kode Bagian', 'Nama Bagian'],
                  [15, 50], QuBrows, EditBrows);
                end;
        10021  : begin
                  ViewItem('select KdDep,NmDep from dbDepart order by KdDep',
                  ['Kode Dept.', 'Nama Dept.'],
                  [15, 50], QuBrows, EditBrows);
                end;
        1003  : begin
                  ViewItem('select KodeJab,Namajab from dbjabatan order by Kodejab',
                  ['Kode Jabatan', 'Nama Jabatan'],
                  [15, 50], QuBrows, EditBrows);
                end;
        1004  : begin
                  ViewItem('select Devisi,NamaDevisi from dbDevisi order by Devisi',
                  ['Kode Devisi', 'Nama Devisi'],
                  [15, 50], QuBrows, EditBrows);
                end;
      100400  : begin
                  ViewItem('select Perkiraan,keterangan from dbPerkiraan where tipe=1 and '+
                           'perkiraan not in (select Perkiraan from dbposthutpiut) order by Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
      100401  : begin
                  ViewItem('select a.Perkiraan,b.keterangan from dbposthutpiut a '+
                           'left outer join dbperkiraan b on b.perkiraan=a.perkiraan '+
                           'where a.Kode=''AKV'' order by a.Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
      100402  : begin
                  ViewItem('select a.Perkiraan,b.keterangan from dbposthutpiut a '+
                           'left outer join dbperkiraan b on b.perkiraan=a.perkiraan '+
                           'where a.Kode=''AKM'' order by a.Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
      100403  : begin
                  ViewItem('select a.Perkiraan,b.keterangan from dbposthutpiut a '+
                           'left outer join dbperkiraan b on b.perkiraan=a.perkiraan '+
                           'where a.Kode=''KAS'' order by a.Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
      100404  : begin
                  ViewItem('select a.Perkiraan,b.keterangan from dbposthutpiut a '+
                           'left outer join dbperkiraan b on b.perkiraan=a.perkiraan '+
                           'where a.Kode=''BANK'' order by a.Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
      100405  : begin
                  ViewItem('Select Bank, Nogiro, TglGiro, Case when kodevls=''IDR'' then KreditRp else Kredit end jumlah, '+
                           ' KodeVls, Kurs, Keterangan from dbGiro where Tipe=''HT'' '+
                           ' and TglCair is null '+
                           ' and (NoGiro like '+QuotedStr('%'+EditFilter.Text+'%')+')'+
                           ' Order by Bank, Nogiro, TglGiro ',
                  ['Bank', 'No. Giro','Tgl Giro Jatuh Tempo','Jumlah','Valas','Kurs','Keterangan'],
                  [15,15,15,15,5,12,50], QuBrows, EditBrows);
                end;
      100406  : begin
                  ViewItem('Select Bank, Nogiro, TglGiro, Case when kodevls=''IDR'' then DebetRp else Debet end jumlah, '+
                           'KodeVls, Kurs, Keterangan from dbGiro where Tipe=''PT'' '+
                           'and TglCair is null '+
                           'Order by Bank, Nogiro, TglGiro ',
                  ['Bank', 'No. Giro','Tgl Giro Jatuh Tempo','Jumlah','Valas','Kurs','Keterangan'],
                  [15,15,12,12,5,9,50], QuBrows, EditBrows);
                end;
      100407  : begin
                  ViewItems('Select A.Devisi,b.NamaBag,A.Perkiraan, A.Keterangan,A.Tanggal, '+
                           '       Case when A.Tipe=''L'' then ''[L]urus'' '+
                           '            when A.Tipe=''M'' then ''[M]enurun'' '+
                           '            when A.Tipe=''P'' then ''[P]ajak'' '+
                           '            else '''' '+
                           '       end Metode,A.Persen,A.Quantity,A.Kodebag, '+
                           '       A.Akumulasi, D.Keterangan NamaAkumulasi,'+
                           '       A.Nomuka,C.Keterangan NamaGroupAktiva,A.noBelakang,A.NoBelakang2, A.Biaya,a.biaya2,a.persenbiaya1,a.persenbiaya2,'+
                           '       E.NamaDevisi,a.TipeAktiva,a.Kelompok '+
                           'From DBAktiva A '+
                           '     left outer join dbBagian b on b.kodebag=a.kodebag '+
                           '     left outer join dbperkiraan c on c.perkiraan=a.Nomuka and c.tipe=1 '+
                           '     left outer join dbperkiraan d on d.perkiraan=a.Akumulasi and d.Tipe=1 '+
                           '     left outer join dbDevisi e on e.Devisi=a.Devisi '+
                           'Where A.NoMuka=:0 or A.Akumulasi=:1 '+
                           'Order by A.Perkiraan  ',[NoKira,NoKira],
                  //['Devisi','Bagian', 'Kode Aktiva', 'Keterangan','Tanggal', 'Metode','Susut (%)','Qty',
                  ['Devisi','Bagian', 'Kode Aktiva', 'Keterangan','Tanggal',
                   'N','N','N','N','N',
                   'N','N','N','N','N',
                   'N','N','N','N','N',
                   'N','N'],
                  [15,25,26,50,12,
                   1,1,1,1,1,
                   1,1,1,1,1,
                   1,1,1,1,1,
                   1,1], QuBrows, EditBrows);
                end;
      100408  : begin
                  ViewItem('select a.Perkiraan,b.keterangan from dbposthutpiut a '+
                           'left outer join dbperkiraan b on b.perkiraan=a.perkiraan '+
                           'where a.Kode=''PT'' order by a.Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
      100409  : begin
                  ViewItem('select a.Perkiraan,b.keterangan from dbposthutpiut a '+
                           'left outer join dbperkiraan b on b.perkiraan=a.perkiraan '+
                           'where a.Kode=''HT'' order by a.Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
      100410  : begin
                  ViewItem('select a.Perkiraan,b.keterangan from dbposthutpiut a '+
                           'left outer join dbperkiraan b on b.perkiraan=a.perkiraan '+
                           'where a.Kode=''DP'' order by a.Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
      100411  : begin
                  ViewItem('Select Bank, NoDeposito, TglJatuhTempo, Case when kodevls=''IDR'' then DebetRp else Debet end jumlah, '+
                           'KodeVls, Kurs, Keterangan from dbDeposito where TglCair is null '+
                           'Order by Bank, NoDeposito, TgljatuhTempo ',
                  ['Bank', 'No. Deposito','Tgl Deposito Jatuh Tempo','Jumlah','Valas','Kurs','Keterangan'],
                  [15,15,12,12,5,9,50], QuBrows, EditBrows);
                end;
      100412  : begin
                  ViewItems('Select A.Devisi,b.NamaBag,A.Perkiraan, A.Keterangan,A.Tanggal, '+
                           '       Case when A.Tipe=''L'' then ''[L]urus'' '+
                           '            when A.Tipe=''M'' then ''[M]enurun'' '+
                           '            when A.Tipe=''P'' then ''[P]ajak'' '+
                           '            else '''' '+
                           '       end Metode,A.Persen,A.Quantity,A.Kodebag, '+
                           '       A.Akumulasi, D.Keterangan NamaAkumulasi,'+
                           '       A.Nomuka,C.Keterangan NamaGroupAktiva,A.noBelakang,A.NoBelakang2, A.Biaya,a.biaya2,a.persenbiaya1,a.persenbiaya2,'+
                           '       E.NamaDevisi,a.TipeAktiva '+
                           'From DBAktiva A '+
                           '     left outer join dbBagian b on b.kodebag=a.kodebag '+
                           '     left outer join dbperkiraan c on c.perkiraan=a.Nomuka and c.tipe=1 '+
                           '     left outer join dbperkiraan d on d.perkiraan=a.Akumulasi and d.Tipe=1 '+
                           '     left outer join dbDevisi e on e.Devisi=a.Devisi '+
                           'Where A.NoMuka=:0 and A.Kelompok=0'+
                           'Order by A.Perkiraan  ',[NoKira],
                  //['Devisi','Bagian', 'Kode Aktiva', 'Keterangan','Tanggal', 'Metode','Susut (%)','Qty',
                  ['Devisi','Bagian', 'Kode Aktiva', 'Keterangan','Tanggal', 'N','N','N',
                   'N','N','N','N','N','N','N','N','N','N','N','N','N'],
                  [15,25,26,50,12,25,12,12,1,1,1,1,1,1,1,1,1,1,1,1,1], QuBrows, EditBrows);
                end;
       100413  : begin
                  ViewItem('Select A.Devisi,b.NamaBag,A.Perkiraan, A.Keterangan,A.Tanggal, '+
                           '       Case when A.Tipe=''L'' then ''[L]urus'' '+
                           '            when A.Tipe=''M'' then ''[M]enurun'' '+
                           '            when A.Tipe=''P'' then ''[P]ajak'' '+
                           '            else '''' '+
                           '       end Metode,A.Persen,A.Quantity,A.Kodebag, '+
                           '       A.Akumulasi, D.Keterangan NamaAkumulasi,'+
                           '       A.Nomuka,C.Keterangan NamaGroupAktiva,A.noBelakang,A.NoBelakang2, A.Biaya,a.biaya2,a.persenbiaya1,a.persenbiaya2,'+
                           '       E.NamaDevisi,a.TipeAktiva '+
                           'From DBAktiva A '+
                           '     left outer join dbBagian b on b.kodebag=a.kodebag '+
                           '     left outer join dbperkiraan c on c.perkiraan=a.Nomuka and c.tipe=1 '+
                           '     left outer join dbperkiraan d on d.perkiraan=a.Akumulasi and d.Tipe=1 '+
                           '     left outer join dbDevisi e on e.Devisi=a.Devisi '+
                           'Where A.Kelompok=0'+
                           'Order by A.Perkiraan  ',
                  ['Devisi','Bagian', 'Kode Aktiva', 'Keterangan','Tanggal', 'N','N','N',
                   'N','N','N','N','N','N','N','N','N','N','N','N','N'],
                  [15,25,26,50,12,25,12,12,1,1,1,1,1,1,1,1,1,1,1,1,1], QuBrows, EditBrows);
                end;
        100414  : begin
                  ViewItem('select a.Perkiraan, b.keterangan '+
                           'from dbposthutpiut a '+
                           'left outer join dbperkiraan b on b.perkiraan=a.perkiraan '+
                           'where a.Kode in (''HT'',''PT'',''UHT'',''UPT'') and '+
                           'a.Perkiraan not in (Select Perkiraan from dbperkcustsupp where kodecustsupp='+QuotedStr(Nokira)+') '+
                           'order by a.Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
        1005  : begin
                  ViewItem('select Perkiraan,Keterangan from dbPerkiraan where  tipe=1 order by Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
        10053  : begin
                  ViewItem('select Perkiraan,Keterangan from dbPerkiraan where  tipe=1 and kelompok=3 order by Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
       10051  : begin

       
                  ViewItems('select a.Perkiraan,a.Keterangan from dbPerkiraan a '+
                            'left Outer join dbAksesPerkiraan b on b.perkiraan=a.Perkiraan '+
                            'where  a.tipe=1 and a.Perkiraan<>:1 and b.Userid='+QuotedStr(IDUser)+
                            ' and (a.Keterangan like ''%'+EditFilter.Text+'%'' or a.Perkiraan like ''%'+EditFilter.Text+'%'') '+
                            'order by a.Perkiraan',
                  [NoKira],
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
       10052  : begin
                  ViewItem('select Perkiraan,Keterangan from dbPerkiraan where Kelompok >1  order by Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
       100529  : begin
                  ViewItems('Exec Sp_CekPerlLR :0,:1',[IsiData,Nokira],
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;

       1250  : begin
                  ViewItem('select KodeExp,NamaExp, Alamat1, Alamat2, Kota from dbExpedisi order by KodeExp',
                  ['Kode Ekspedisi', 'Nama EksPedisi', 'N','N','N'],
                  [20, 50,1,1,1], QuBrows, EditBrows);
                end;
       10057  : begin
                  ViewItem('select Perkiraan, Keterangan from dbPerkiraan where Tipe=1 and Perkiraan Like ''153%'' order by Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
        10054  :begin
                  ViewItemS('select Nomor, Keterangan from dbLRHPP where IsLRHPP=:0 and Bulan=:1 and Tahun=:2 order by Nomor',
                  [isTampil,brBulan, BrTahun],['Nomor', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
        10055  :begin
                  ViewItems('Select A.Perkiraan,A.Keterangan '+
                           'From DBPERKIRAAN A '+
                           '     left Outer join DBAKSESPERKIRAAN B on B.Perkiraan=A.Perkiraan '+
                           '     left Outer join DBPOSTHUTPIUT C on C.Perkiraan=A.Perkiraan '+
                           'where B.UserID='+QuotedStr(IDUser)+'  and C.Kode=:0 '+
                           'Order by A.Perkiraan',[NoKira2],
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
        10056  : begin
                  ViewItems('Select A.Perkiraan,A.Keterangan '+
                           'From DBPERKIRAAN A '+
                           '     left Outer join DBAKSESPERKIRAAN B on B.Perkiraan=A.Perkiraan '+
                           '     left Outer join DBPOSTHUTPIUT C on C.Perkiraan=A.Perkiraan '+
                           'where B.UserID='+QuotedStr(IDUser)+'  and C.Kode<>:0 '+
                           'Order by A.Perkiraan',[NoKira2],
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
        10059  :begin
                  ViewItem('Select A.Perkiraan,A.Keterangan '+
                           'From DBPERKIRAAN A '+
                           '     left Outer join DBAKSESPERKIRAAN B on B.Perkiraan=A.Perkiraan '+
                           '     left Outer join DBPOSTHUTPIUT C on C.Perkiraan=A.Perkiraan '+
                           'where B.UserID='+QuotedStr(IDUser)+' '+
                           'Order by A.Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;

        1006  : begin
                  ViewItem('select Kodevls,NamaVls,Kurs from dbValas order by kodevls',
                  ['Valas', 'Keterangan','Kurs'],
                  [15, 50,12], QuBrows, EditBrows);
                end;
        1007  : begin
                  ViewItem('select Kodeak,Namaak from dbaruskas order by kodeak',
                  ['Kode Aktivitas', 'Nama Aktivitas'],
                  [30,50], QuBrows, EditBrows);
                end;
        10071  :begin
                  ViewItemS('select Kodesubak,Namasubak from dbaruskasdet where kodeak=:0 order by Kodesubak',
                  [isidata],['Kode Sub Aktivitas', 'Nama Sub Aktivitas'],
                  [30,75], QuBrows, EditBrows);
                end;
        1008  : begin//Master Kategori
                  SortBy:='KodeKategori';
                  ViewItem('Select KodeKategori,Keterangan from dbKategori Order by KodeKategori',['Kode Kategori','Nama Kategori'],[15,40],QuBrows,EditBrows);
                end;
        10081 : begin//Master Kategori
                  SortBy:='KodeKategori';
                  ViewItem('Select KodeKategori, Keterangan from dbKategoriBrgJadi Order by KodeKategori',['Kode Kategori','Nama Kategori'],[15,40],QuBrows,EditBrows);
                end;
        1014  : begin // Master CustSupp
                  SortBy:='KodeCustSupp';
                   ViewItem(' select X.KodeCustSupp, Y.NamaCustSupp, '+
                            ' rtrim(ltrim(isnull(Y.Alamat1,'''')+case when isnull(Y.Alamat2,'''')='''' then '''' else '' ''+Y.Alamat2 end)) Alamat, '+
                            ' Y.Kota'+
                            ' from vwGroupCustSupp X, DBCUSTSUPP Y '+
                            ' where X.KodeCustSupp=Y.KodeCustSupp  ' +
                            ' and x.perkiraan ='+QuotedStr(NoKira)+
                            ' and (Y.KodeCustSupp like ''%'+EditFilter.Text+'%'' or Y.NamaCustSupp like ''%'+EditFilter.Text+'%'') '+
                            ' order by X.KodeCustSupp',
                   ['Kode','Nama','Alamat','Kota'],
                   [15,40,50,20], QuBrows, EditBrows);

                end;
        10141 : begin // Master CustSupp
                  SortBy:='KodeCustSupp';
                  ViewItem('select a.KodeCustsupp, a.NamaCustSupp NamaCust, A.Alamat, A.Telpon '+
                            'from vwBrowsSupp A '+
                            'where a.isaktif=1 '+
                            'Order by a.kodecustsupp',
                         ['Kode ', 'Nama ', 'Alamat','Telpon'],
                         [20, 50, 50, 50], QuBrows, EditBrows);
                end;
        10142 : begin // Master CustSupp
                  SortBy:='KodeCustSupp';
                  ViewItem('select a.KodeCustsupp, a.NamaCustSupp NamaCust, A.Alamat, A.Telpon '+
                            'from vwBrowscust A '+
                            'where a.isaktif=1 '+
                            'Order by a.kodecustsupp',
                         ['Kode ', 'Nama ', 'Alamat','Telpon'],
                         [20, 50, 50, 50], QuBrows, EditBrows);
                end;
        10143 : begin // Master CustSupp
                  SortBy:='KodeCustSupp';
                  ViewItem('select a.KodeCustsupp, a.NamaCustSupp NamaCust, A.Alamat, A.Telpon '+
                            'from vwBrowsExpedisi A '+
                            'where a.isaktif=1 '+
                            'Order by a.kodecustsupp',
                         ['Kode ', 'Nama ', 'Alamat','Telpon'],
                         [20, 50, 50, 50], QuBrows, EditBrows);
                end;
         ////////////////
         101411 : begin // Ambil SPK
                  SortBy:='NoBukti';
                  ViewItem('Select A.NoBukti,A.KodeBrg BrgJ,E.NamaBrg NamaBrgJ ,A.Qnt QntJ,A.Nosat NosatJ,A.Isi IsiJ,A.Satuan SatJ,   '+
                           '       ISNULL(Case when A.Nosat=1 then B.QntHasilPrd       '+
                           '                   when A.Nosat=2 then B.QntHasilPrd/A.isi      '+
                           '                   else 0         '+
                           '              end,0) QntH,        '+
                           '       A.QNT-                  '+
                           '       ISNULL(Case when A.Nosat=1 then B.QntHasilPrd      '+
                           '                   when A.Nosat=2 then B.QntHasilPrd/A.isi   '+
                           '                   else 0  '+
                           '              end,0) SisaSPK            '+
                           'From [vwSPK] A          '+
                           '     Left Outer join dbBarang E on E.KodeBrg=A.Kodebrg       '+
                           '     Left Outer join (Select y.NoSPK,y.KODEBRG, y.KodeGdg ,Sum( y.QNT*y.ISI)QntHasilPrd  from DBHASILPRD x       '+
                           '                           left Outer join DBHASILPRDDET y on y.NOBUKTI=x.NOBUKTI Group by y.NoSPK,y.KODEBRG, y.KodeGdg) B on B.NoSPK=A.NOBUKTI and B.KODEBRG=A.KODEBRG '+
                          { 'where A.QNT-        '+
                           '       ISNULL(Case when A.Nosat=1 then B.QntHasilPrd        '+
                           '                   when A.Nosat=2 then B.QntHasilPrd/A.isi   '+
                           '                   else 0        '+
                           '              end,0)>0          '+  }
                           'Order by A.NoBukti',
                         ['No. Bukti ','N', 'Nama Barang','N','N','N','Sat','N','Qnt'],
                         [30,1, 50, 10,1,1, 10,1,12], QuBrows, EditBrows);
                end;
        101412: Begin
                  ViewItem('Select A.NoBukti,A.noso,A.KodeBrg BrgJ,E.NamaBrg + '' '' + a.namacustsupp NamaBrgJ ,A.Qnt QntJ,A.Nosat NosatJ,A.Isi IsiJ,A.Satuan SatJ,   '+
                           '       ISNULL(Case when A.Nosat=1 then B.QntHasilPrd       '+
                           '                   when A.Nosat=2 then B.QntHasilPrd/A.isi      '+
                           '                   else 0         '+
                           '              end,0) QntH,        '+
                           '       A.QNT-                  '+
                           '       ISNULL(Case when A.Nosat=1 then B.QntHasilPrd      '+
                           '                   when A.Nosat=2 then B.QntHasilPrd/A.isi   '+
                           '                   else 0  '+
                           '              end,0) SisaSPK,tglselesai            '+
                           'From [vwSPK] A          '+
                           '     Left Outer join dbBarang E on E.KodeBrg=A.Kodebrg       '+
                           '     Left Outer join (Select y.isclosespk,y.NoSPK,y.KODEBRG, y.KodeGdg ,Sum( y.QNT*y.ISI)QntHasilPrd  from DBHASILPRD x       '+
                           '                           left Outer join ' +
                           'DBHASILPRDDET y on y.NOBUKTI=x.NOBUKTI Group by ' +
                           'y.NoSPK,y.KODEBRG, y.KodeGdg,isclosespk) B on B.NoSPK=A.NOBUKTI and B.KODEBRG=A.KODEBRG '+
                           'where  isnull(isclosespk,0)=0         '+
                           ' and   a.qnt -   ISNULL(Case when A.Nosat=1 then B.QntHasilPrd        '+
                           '                   when A.Nosat=2 then B.QntHasilPrd/A.isi   '+
                           '                   else 0        '+
                           '              end,0)>0          '+
                           'Order by A.NoBukti',
                         ['No. Bukti ','NoSO','N', 'Nama Barang','N','N','N','Sat','N','Qnt','Tgl Selesai'],
                         [30,30,1, 50, 10,1,1, 10,1,12,20], QuBrows, EditBrows);

                end;
        1014121: Begin
                  ViewItems('Declare @tglhpd datetime '+
                  ' set @tglhpd =:0 ' +
                  ' select  y.NoBukti,noso,case when BrgJ like ''PBJ.Proff%'' then ''PBJ.Proff'' else BrgJ end brgj,NamaBrgJ,QntJ,NosatJ,IsiJ,SatJ,QntH,SisaSPK,tglselesai,namacustsupp,qntcetak  from ( '+

                  'Select A.NoBukti,A.noso,A.KodeBrg BrgJ,E.NamaBrg + '' '' + a.namacustsupp NamaBrgJ ,A.Qnt QntJ,A.Nosat NosatJ,A.Isi IsiJ,A.Satuan SatJ,   '+
                           '       ISNULL(Case when A.Nosat=1 then B.QntHasilPrd       '+
                           '                   when A.Nosat=2 then B.QntHasilPrd/A.isi      '+
                           '                   else 0         '+
                           '              end,0) QntH,        '+
                           '       A.QNT-                  '+
                           '       ISNULL(Case when A.Nosat=1 then B.QntHasilPrd      '+
                           '                   when A.Nosat=2 then B.QntHasilPrd/A.isi   '+
                           '                   else 0  '+
                           '              end,0) SisaSPK,a1.tglselesai,namacustsupp,qntcetak            '+
                           'From dbo.cekhpd2(@tglhpd)  a1        '+
                           '  left outer join [vwSPK]  A on a.nobukti=a1.nospk  '+
                           '     Left Outer join dbBarang E on E.KodeBrg=A.Kodebrg       '+
                           '     Left Outer join (Select y.isclosespk,y.NoSPK,y.KODEBRG, y.KodeGdg ,Sum( y.QNT*y.ISI)QntHasilPrd  from DBHASILPRD x       '+
                           '                           left Outer join ' +
                           'DBHASILPRDDET y on y.NOBUKTI=x.NOBUKTI Group by ' +
                           'y.NoSPK,y.KODEBRG, y.KodeGdg,isclosespk) B on B.NoSPK=A.NOBUKTI and B.KODEBRG=A.KODEBRG '+
                           'where  isnull(isclosespk,0)=0 /*and @tglhpd<=dbo.cekhpd(NOBUKTI ) */       '+
                           '/*and @tglhpd<=case when dbo.cekhpd(NOBUKTI ) =''1900-01-31 23:59:59.000'' then ''2100-1-1'' else dbo.cekhpd(NOBUKTI )         end '+
                           ' and   a.qnt -   ISNULL(Case when A.Nosat=1 then B.QntHasilPrd        '+
                           '                   when A.Nosat=2 then B.QntHasilPrd/A.isi   '+
                           '                   else 0        '+
                           '              end,0)>0*/          '+
                        ' union '+
                        ' Select A.NoBukti,A.noso,A.KodeBrg BrgJ,E.NamaBrg + '' '' + a.namacustsupp '+
                        ' NamaBrgJ ,A.Qnt QntJ,A.Nosat NosatJ,A.Isi IsiJ,A.Satuan SatJ, '+
                        ' ISNULL(Case when A.Nosat=1 then B.QntHasilPrd '+
                        ' when A.Nosat=2 then B.QntHasilPrd/A.isi else 0 end,0) QntH, '+
                        ' A.QNT- ISNULL(Case when A.Nosat=1 then B.QntHasilPrd '+
                        ' when A.Nosat=2 then B.QntHasilPrd/A.isi '+
                        ' else 0                end,0) SisaSPK, '+
                        ' a1.tglselesai,namacustsupp,qntcetak '+
                        ' From dbo.cekhpd2(@tglhpd)  a1  '+
                        ' left outer join  [vwSPK] A on a.nobukti =a1.nospk' +
                        ' Left Outer join dbBarang E on E.KodeBrg=A.Kodebrg '+
                        ' Left Outer join ( '+
                        ' Select y.isclosespk,y.NoSPK,y.KODEBRG, y.KodeGdg ,Sum( y.QNT*' +
                        'y.ISI)QntHasilPrd '+
                        ' from DBHASILPRD x '+
                        ' left Outer join DBHASILPRDDET y on y.NOBUKTI=x.NOBUKTI '+
                        ' Group by y.NoSPK,y.KODEBRG, y.KodeGdg,isclosespk) B on B.NoSPK=A.NOBUKTI and '+
                        ' B.KODEBRG=A.KODEBRG '+
                        ' where '+
                        ' A.QNT- ISNULL(Case when A.Nosat=1 then B.QntHasilPrd '+
                        ' when A.Nosat=2 then B.QntHasilPrd/A.isi '+
                        ' else 0                end,0 )>0 and b.isclosespk=0 '+
                        '       ) y   '+
                        ' left outer join DBSODET x on x.NOBUKTI =y.noso and x.KODEBRG =y.BrgJ '+
                        ' where isnull(x.IsCloseDet,0) =0 '+

                           'Order by  y.NoBukti ',[TglAwal],
                         ['No. Bukti ','NoSO','N', 'Nama Barang','N','N','N','Sat','N','Qnt','Tgl Selesai','N'],
                          [30,30,1, 50, 10,1,1, 10,1,12,20,1], QuBrows, EditBrows);

                end;
        1014122:begin
                        ViewItem('select e.nobukti NoBukti,b.kodebrg BrgJ,namacustsupp,d.namabrg NamaBrgJ,b.qnt QntJ,e.QntCetak from dbso a '+
                            'left outer join dbsodet b on b.nobukti =a.nobukti '+
                            'left outer join dbcustsupp c on c.kodecustsupp=a.kodecust '+
                            'left outer join dbbarang d on d.kodebrg=b.kodebrg '+
                            'left outer join dbspk e on e.noso =a.nobukti '+
                            'where year (a.tanggal)>=2022 and a.nobukti not like ''%proff%'' '+
                            'and e.nobukti not in (select nospk from dbfinishingdet) '+
                            'order by e.nobukti',
                         ['NoSO','Kode barang',' Customer',' Nama Barang','Qnt','N'], [15,20,40,15,10,1], QuBrows, EditBrows);
                End;
         {101411 : begin // Ambil SPK
                  SortBy:='NoBukti';
                  ViewItems('Select A.NoBukti,A1.KodeBrg BrgJ,E.NamaBrg NamaBrgJ ,A1.Qnt QntJ,A1.Nosat NosatJ,A1.Isi IsiJ,A1.Satuan SatJ,'+#13+
                           '       ISNULL(Case when A1.Nosat=1 then Case when B.NOSAT=1 then B.QNT'+#13+
                           '                                            when B.NOSAT=2 then B.QNT*A.isi'+#13+
                           '                                            else 0'+#13+
                           '                                       end'+#13+
                           '                   when A1.Nosat=2 then Case when B.NOSAT=1 then B.QNT/A.isi'+#13+
                           '                                            when B.NOSAT=2 then B.QNT'+#13+
                           '                                            else 0'+#13+
                           '                                       end'+#13+
                           '                   else 0'+#13+
                           '              end,0) QntH,'+#13+
                           '       A1.QNT-'+#13+
                           '       ISNULL(Case when A.Nosat=1 then Case when B.NOSAT=1 then B.QNT'+#13+
                           '                                            when B.NOSAT=2 then B.QNT*A.isi'+#13+
                           '                                            else 0'+#13+
                           '                                       end'+#13+
                           '                   when A.Nosat=2 then Case when B.NOSAT=1 then B.QNT/A.isi'+#13+
                           '                                            when B.NOSAT=2 then B.QNT'+#13+
                           '                                            else 0'+#13+
                           '                                       end'+#13+
                           '                   else 0'+#13+
                           '              end,0) SisaSPK'+#13+
                           'From dbSPK A'+#13+
                           'Left Outer Join dbSPKBJDet A1 On A1.NoBukti=A.NoBukti '+
                           '     Left Outer join dbBarang E on E.KodeBrg=A1.Kodebrg'+#13+
                           '     Left Outer join (Select y.NoSPK,y.KODEBRG, y.KodeGdg, y.QNT, y.NOSAT, y.ISI, y.SATUAN'+#13+
                           '                      from DBHASILPRD x'+#13+
                           '                           left Outer join DBHASILPRDDET y on y.NOBUKTI=x.NOBUKTI) B on B.NoSPK=A.NOBUKTI and B.KODEBRG=A1.KODEBRG'+#13+
                           'where A.IsPesan = :0 and A1.QNT-'+#13+
                           '       ISNULL(Case when A1.Nosat=1 then Case when B.NOSAT=1 then B.QNT'+#13+
                           '                                            when B.NOSAT=2 then B.QNT*A.isi'+#13+
                           '                                            else 0'+#13+
                           '                                       end'+#13+
                           '                   when A1.Nosat=2 then Case when B.NOSAT=1 then B.QNT/A.isi'+#13+
                           '                                            when B.NOSAT=2 then B.QNT'+#13+
                           '                                            else 0'+#13+
                           '                                       end'+#13+
                           '                   else 0'+#13+
                           '              end,0)>0'+#13+
                           'Order by A.NoBukti',[IsiData],
                         ['No. Bukti ','N', 'Nama Barang','N','N','N','Sat','N','Qnt'],
                         [30,1, 50, 10,1,1, 10,1,12], QuBrows, EditBrows);
                end; }

         20011:begin
                 SortBy:='Perkiraan';
                 ViewItem('Select Perkiraan, Keterangan, Simbol from dbPerkiraan where Tipe=1 '+
                         ' and Perkiraan in (select Perkiraan from dbPostHutPiut where Kode='+QuotedStr(NoKira1)+')'+
                         ' and (Perkiraan like ''%'+EditFilter.Text+'%'' or Keterangan like ''%'+EditFilter.Text+'%'') '+
                         //' and Perkiraan in (select Perkiraan from dbAksesPerkiraan where UserID='+QuotedStr(NoKira2)+')'+
                         ' Order by Perkiraan',
                         ['Perkiraan','Nama Perkiraan','Simbol'], [15,40,5], QuBrows, EditBrows);
               end;
         20012:begin
                 SortBy:='Perkiraan';
                 ViewItem('Select Perkiraan, Keterangan, Simbol from dbPerkiraan where Tipe=1 '+
                         ' and Perkiraan in (select Perkiraan from dbPostHutPiut where Kode=''KAS'')'+
                         //' and (Perkiraan like ''%'+EditFilter.Text+'%'' or Keterangan like ''%'+EditFilter.Text+'%'') '+
                         //' and Perkiraan in (select Perkiraan from dbAksesPerkiraan where UserID='+QuotedStr(NoKira2)+')'+
                         ' Order by Perkiraan',
                         ['Perkiraan','Nama Perkiraan','Simbol'], [15,40,5], QuBrows, EditBrows);
               end;
        23201: begin // Barang di Beli
                 //SortBy:='KodeBrg';
                 ViewItem('select min(A.Urut) Urut, A.KodeBrg, case when ketbrg ='''' then Br.NamaBrg else ketbrg end namabrg, A.NoSat, '+
                          ' case when A.NOSAT=1 then Br.SAT1 else Br.SAT2 end Satuan, '+
                          ' case when A.NOSAT=1 then 1 else Br.ISI2 end Isi, '+
                          ' Br.Isi1, Br.Isi2, '+
                          ' sum(A.QntOut) QntSisa ,  '''' NFix,ketbrg '+
                          ' from ( '+
                          '   select A.NoBukti, A.Tanggal, B.Urut, B.KODEBRG, B.NOSAT, B.Qnt, B.Qnt QntOut, B.HARGA,ketbrg '+
                          '   from DBPO A, DBPODET B '+
                          '   where A.NOBUKTI=B.NOBUKTI and A.NOBUKTI='+QuotedStr(NoKira1)+
                          '   union all '+
                          '   select A.NoBukti, A.Tanggal, 99999 Urut, B.KODEBRG, B.NOSAT, 0.00 Qnt, -B.QNT QntOut, 0.00 Harga, '''' Ketbrg '+
                          '   from DBPO A, DBBELIDET B '+
                          '   where A.NOBUKTI=B.NoPO and A.NOBUKTI='+QuotedStr(NoKira1)+                                                                                                                          
                          '   ) A '+
                          ' left outer join DBBARANG Br on Br.KODEBRG=A.KODEBRG '+
                          ' group by A.NOBUKTI, A.TANGGAL, A.KODEBRG, Br.NAMABRG, A.NOSAT, Br.SAT1, Br.SAT2, Br.ISI1, Br.ISI2, a.ketbrg '+
                          ' having sum(A.QntOut)>0 '+
                          ' order by min(A.Urut) ',
                          ['N','Kode Barang','Nama Barang','N','Satuan','N','N','N','Qnt Sisa','N','N'],
                          [1,25,50,1,5,1,1,1,10,1,1], QuBrows, EditBrows);
               end;
         232011 : begin
                 SortBy:='KodeBrg';
                 ViewItem('select kodebrg,namabrg,namasubgrp,perkpers  from dbbarang a left outer join dbsubgroup b on b.kodesubgrp =a.kodesubgrp where a.kodegrp=''BLB''',
                          ['Kode Barang','Nama Barang','Sub Group','Kode Perkiraan'],
                          [25,50,50,10], QuBrows, EditBrows);
                end;
        23301: begin // Barang di Retur Beli
                 SortBy:='KodeBrg';
                 ViewItem('Select A.pphp,A.URUT, A.KODEBRG, Br.NAMABRG, A.NOSAT, A.ISI, A.SATUAN, A.QntTerima,'+#13+
                          '       A.QntTerima-Sum(Case when A.NOSAT=1 then Case when B.NOSAT=1 then B.QntReject'+#13+
                          '                                                     when B.NOSAT=2 then B.QntReject*Br.ISI2'+#13+
                          '                                                     else 0'+#13+
                          '                                                end'+#13+
                          '                            when A.NOSAT=2 then Case when B.NOSAT=1 then B.QntReject/Br.ISI2'+#13+
                          '                                                     when B.NOSAT=2 then B.QntReject'+#13+
                          '                                                     else 0'+#13+
                          '                                                end'+#13+
                          '                        else 0'+#13+
                          '                   end)-isnull(D.Qnt,0) Qnt,'+#13+
                          '       A.Qnt1Terima-Sum(Case when A.NOSAT=1 then Case when B.NOSAT=1 then B.Qnt1Reject'+#13+
                          '                                                      when B.NOSAT=2 then B.Qnt1Reject'+#13+
                          '                                                      else 0'+#13+
                          '                                                 end'+#13+
                          '                             when A.NOSAT=2 then Case when B.NOSAT=1 then B.Qnt1Reject'+#13+
                          '                                                      when B.NOSAT=2 then B.Qnt1Reject'+#13+
                          '                                                      else 0'+#13+
                          '                                                 end'+#13+
                          '                        else 0'+#13+
                          '                   end)-isnull(D.Qnt1,0) Qnt1,'+#13+
                          '       A.Qnt2Terima-Sum(Case when A.NOSAT=1 then Case when B.NOSAT=1 then B.Qnt2Reject'+#13+
                          '                                                      when B.NOSAT=2 then B.Qnt2Reject'+#13+
                          '                                                      else 0'+#13+
                          '                                                 end'+#13+
                          '                             when A.NOSAT=2 then Case when B.NOSAT=1 then B.Qnt2Reject'+#13+
                          '                                                      when B.NOSAT=2 then B.Qnt2Reject'+#13+
                          '                                                      else 0'+#13+
                          '                                                 end'+#13+
                          '                        else 0'+#13+
                          '                   end)-isnull(D.Qnt2,0) Qnt2, Br.NFix'+#13+
                          'from (Select PphP,urut, kodebrg, QNT, QntTerima,Qnt1Terima, Qnt2Terima, NOSAT, ISI, SATUAN, NOBUKTI'+#13+
                          '      from DBBELIDET'+#13+
                          '      where QntTerima<>0) A'+#13+
                          '      Left Outer join (Select urut, kodebrg, QNT,  QntReject, Qnt1Reject, Qnt2Reject,  NOSAT, ISI, SATUAN, NOBUKTI'+#13+
                          '                       from DBBELIDET'+#13+
                          '                       where Qnt1Reject<>0) B on B.NOBUKTI=A.NOBUKTI and B.KODEBRG=A.KODEBRG'+#13+
                          '      left outer join DBBARANG Br on Br.KODEBRG=A.KODEBRG'+#13+
                          '      Left Outer join (Select x.NOPBL, x.URUTPBL, SUM(x.QNT) Qnt, SUM(x.Qnt1) Qnt1, SUM(x.Qnt2) Qnt2'+#13+
                          '                       from DBRBELIDET x'+#13+
                          '                       group by x.NOPBL, x.URUTPBL) D on D.NOPBL=A.NOBUKTI and D.URUTPBL=A.URUT'+#13+
                          'where A.NoBukti='+QuotedStr(NoKira1)+#13+
                          'Group by a.pphp,A.URUT, A.KODEBRG, Br.NAMABRG, A.NOSAT, A.ISI, A.SATUAN, Br.NFix, A.QntTerima, A.Qnt1Terima, A.Qnt2Terima,D.Qnt,D.Qnt1, D.Qnt2'+#13+
                          'Having A.QntTerima-Sum(Case when A.NOSAT=1 then Case when B.NOSAT=1 then B.QntReject'+#13+
                          '                                                     when B.NOSAT=2 then B.QntReject*Br.ISI2'+#13+
                          '                                                     else 0'+#13+
                          '                                                end'+#13+
                          '                            when A.NOSAT=2 then Case when B.NOSAT=1 then B.QntReject/Br.ISI2'+#13+
                          '                                                     when B.NOSAT=2 then B.QntReject'+#13+
                          '                                                     else 0'+#13+
                          '                                                end'+#13+
                          '                        else 0'+#13+
                          '                   end)-isnull(D.Qnt,0)>0'+#13+
                          'order by A.Urut',
                          ['N','Kode Barang','Nama Barang','N','N','Sat','N', 'Qnt','N','N','N'],
                          [1,20,50,1,1,5,1,10,1,1,1], QuBrows, EditBrows);
               end;
        23302: begin // No. Bukti Beli pada FrRBeli
                 SortBy:='KodeBrg';
                 Viewitem('Select  A.NoBukti, A.Tanggal from dbBeli A  '+
                          ' where A.KodeSupp='+QuotedStr(NoKira1)+
                          ' and (a.NoBukti like ''%'+EditFilter.Text+'%'') '+
                          'and Cast(Case when Case when A.IsOtorisasi1=1 then 1 else 0 end+ '+
                          '                   Case when A.IsOtorisasi2=1 then 1 else 0 end+ '+
                          '                   Case when A.IsOtorisasi3=1 then 1 else 0 end+ '+
                          '                   Case when A.IsOtorisasi4=1 then 1 else 0 end+ '+
                          '                   Case when A.IsOtorisasi5=1 then 1 else 0 end=A.MaxOL then 0'+
                          '              else 1'+
                          '            end As Bit)=0 order by A.NoBukti ',
                          ['No Bukti','Tanggal'],[25,15],QuBrows,EditBrows);
               end;
        300161
            :Begin
               SortBy:='KodeBrg';
               ViewItem('Select A.KODEBRG, A.NAMABRG, A.SAT1 Sat_1, A.Sat2 Sat_2, A.Isi2 Isi, 0 IsSet, 0 IsInspeksi, nFix, 1 Nosat, 0 Urut '+
               ' from dbBarang A where A.KodeGrp = ''BB'' or A.kodegrp =''BJB'' or A.kodegrp =''BBA'' or A.kodegrp=''BT'' or A.kodegrp=''BBL'' or A.kodegrp=''BLL'' order by A.KodeBrg ',
               ['Kode Barang','Nama Barang','Sat1','Sat2','N','N','N','N','N','N'],
               [15,60,5,5,1,1,1,10,30,30,1,1,1],QuBrows,EditBrows);
             end;
        30056 : Begin
                  SortBy := 'KodeSubtipe';
                  ViewItemS('Select A.KODETIPE,B.KODESUBTIPE,B.Nama '+
                           'from DBTIPETRANS A '+
                           '     Left Outer join DBSUBTIPETRANS B on B.KODETIPE=A.KODETIPE '+
                           'where A.KodeTipe=:0 '+
                           'Order by A.KODETIPE,B.KODESUBTIPE',[NoKira],
                           ['Kode Jenis','Kode Sub Jenis', 'Keterangan'],[15,15,50],QuBrows,EditBrows);
                end;
        30057 : Begin
                  SortBy := 'Kodetipe';
                  ViewItems('Select A.KODETIPE,A.Nama '+
                           'from DBTIPETRANS A where isJasaBeliJual=:0 '+
                           'Order by A.KODETIPE',[JnsPakai],
                           ['Kode Jenis', 'Keterangan'],[15,50],QuBrows,EditBrows);
                end;
        251010,252010,301010,302010, //Nobukti
        251020,252020,301020,302020, //KodeCustSupp
        251030,252030,301030,302030, //AccPersediaan
        251040,252040,301040,302040://AccHutpiut
               begin
                   SortBy:='NoBukti';
                   Viewitems('Select '+ vw +
                            ' where tanggal between :0 and :1 '+
                            ' Order By  '+ Ordr ,
                            [TglAwal,tglAkhir],[''+colm1 ,''+Colm2],[30,15],QuBrows,EditBrows);
               end;

        251050  : begin
                  ViewItem('select Kodetipe,Nama from DbTipeTrans order by Kodetipe',
                  ['Kode Tipe', 'Nama'],
                  [15, 50], QuBrows, EditBrows);
                end;

        9111 : begin
                  ViewItem('select * from DbArea  '+
                           'order by KodeArea',
                  ['Kode', 'Nama '],
                  [10, 30], QuBrows, EditBrows);
                end;
        911 : begin
                 SortBy:='Kodebrg';
                  ViewItem('select a.Kodebrg, a.NamaBrg, a.Isi2,a.Sat1, a.Sat2 from Dbbarang a '+
                           ' where a.isaktif=1 and KodeGrp=''BJ'' order by a.Kodebrg',
                  ['Kode Barang', 'Nama Barang','N','N','N'],
                  [30, 50,1,1,1], QuBrows, EditBrows);
                end;
        912 : begin
                 SortBy:='KodeCustSupp';
                  ViewItem('select a.KodeCustSupp, a.NamaCustSupp from DbCustSupp a '+
                           ' where a.isaktif=1 order by a.Kodecustsupp',
                  ['Kode Barang', 'Nama Barang'],
                  [30, 50], QuBrows, EditBrows);
                end;

        913 : begin
                 SortBy:='Kodebrg';
                  ViewItem(' select a.Kodebrg, a.NamaBrg, a.Isi2, a.NFix, Cast(Case when B.KodeBrg is null then 0 Else 1 end as bit) Kontrak from DBbarang a '+
                           ' Left Outer join DBBARANGCUSTOMER B on B.kodebrg=A.Kodebrg and B.KodecustSupp='+QuotedStr(NoKira)+
                           ' where a.isaktif=1 and A.KodeGrp like ''BJ%'' order by Cast(Case when B.KodeBrg is null then 0 Else 1 end as bit) DESC, a.Kodebrg ASC',
                  ['Kode Barang', 'Nama Barang','N','N','Kontrak'],
                  [30, 50,1,1,32], QuBrows, EditBrows);

                end;

        918 : begin
                 SortBy:='KodeCustSupp';
                  ViewItem('select a.Kodesubcustomer ,a.Namasubcustomer from Dbsubcustomer a '+
                           '  order by a.Kodesubcustomer',
                  ['Kode Sub Customer', 'Nama Sub Customer'],
                  [30, 50], QuBrows, EditBrows);
                end;

        9131 : begin
                 SortBy:='Kodebrg';
                  ViewItem(' select a.Kodebrg, a.NamaBrg, a.Isi2, a.NFix, Cast(Case when B.KodeBrg is null then 0 Else 1 end as bit) Kontrak from DBbarang a '+
                           ' Left Outer join DBBARANGCUSTOMER B on B.kodebrg=A.Kodebrg and B.KodecustSupp='+QuotedStr(NoKira)+
                           ' where a.isaktif=1 and A.KodeGrp like ''BJP%'' and a.KodeBrg like ''%'+NoKira+'.%'' order by Cast(Case when B.KodeBrg is null then 0 Else 1 end as bit) DESC, a.Kodebrg ASC',
                  ['Kode Barang', 'Nama Barang','N','N','Kontrak'],
                  [30, 50,1,1,32], QuBrows, EditBrows);

                end;
        9132 : begin
                 SortBy:='Kodebrg';
                  ViewItem(' select a.Kodebrg, a.NamaBrg, a.Isi2, a.NFix, Cast(Case when B.KodeBrg is null then 0 Else 1 end as bit) Kontrak from DBbarang a '+
                           ' Left Outer join DBBARANGCUSTOMER B on B.kodebrg=A.Kodebrg '+
                           ' where a.isaktif=1 and A.KodeGrp like ''BJP%'' and a.KodeBrg like ''%'+NoKira+'.%'' order by Cast(Case when B.KodeBrg is null then 0 Else 1 end as bit) DESC, a.Kodebrg ASC',
                  ['Kode Barang', 'Nama Barang','N','N','Kontrak'],
                  [30, 50,1,1,32], QuBrows, EditBrows);

                end;

         9149 : begin
                 SortBy:='Kodebrg';
                  ViewItem(' select a.Kodebrg, a.NamaBrg, a.Sat1,a.isi1,a.sat2,a.Isi2,a.nfix from DBbarang a '+
                           ' where a.isaktif=1 and A.KodeGrp<>''BJ'' and dbo.cekGudanglLpb(a.KODEBRG )='+QuotedStr(IsiData)+' Order by a.Kodebrg ',
                  ['Kode Barang', 'Nama Barang','Sat 1','Isi 1','Sat 2','Isi2','N'],
                  [30, 50,20,20,20,20,1], QuBrows, EditBrows);
                end;
        91111 : begin
                  ViewItem('select A.*,NamaArea from DbKota A '+
                           'Left Outer join DBArea B on A.kodearea=B.KodeAreA order by KodeKota',
                  ['Kode Kota', 'Nama Kota','Kode Area','Nama Area'],
                  [10, 30,10,30], QuBrows, EditBrows);
                end;
        1154  : begin
                  ViewItem(' select KodeCustSupp KodeCust, NamaCustSupp NamaCust, Alamat, Hari DueDate,  JENIS,IsPpn '+
                  ' from vwBrowsCust '+
                  ' Group by KodeCustSupp, NamaCustSupp, Alamat, Hari , JENIS,IsPpn'+
                  ' order by KodeCustSupp',
                  ['Kode', 'Nama Pelanggan', 'Alamat', 'N','N','N'],
                  [15, 50, 50, 5, 1,1], QuBrows, EditBrows);
                end;
        11542 : begin
                  ViewItem(' select Nomor, Nama, ALamat AlamatKirim, '''' KodeExp, '''' NamaExp '+
                  ' from vwAlamatCust where KodeCustsupp='+QuotedStr(NoKira)+' order by Nomor',
                  ['No. ', 'Nama','Alamat Kirim','N','N'],
                  [10,30 ,100, 1, 1], QuBrows, EditBrows);
                end;
        1158  : begin
                  ViewItems('declare @namaSls varchar(30),@KOdeCUstSupp varchar(15) '+
                           '     select @KOdeCUstSupp=:0  '+
                           '     set @namaSls=(                   '+
                           '                             select  B.Nama NamaSls                 '+
                           '                 from dbSalesCustomer a                             '+
                           '                         Left Outer Join dbKaryawan b on b.KeyNik=A.KeyNik   '+
                           '                  where A.KodecustSupp=@KOdeCUstSupp               '+
                           '              )                                     '+
                           '     if isnull(@namaSls,'''')=''''                      '+
                           '     begin                                          '+
                           '     select A.Keynik KodeSls, A.Nama NamaSls        '+
                           '     from dbKaryawan A                              '+
                           '     where A.IsSales=1                              '+
                           '      order by A.Keynik                             '+
                           '     End                                            '+
                           '     else                                           '+
                           '     Begin                                          '+
                           '     select A.Keynik KodeSls, B.Nama NamaSls        '+
                           '     from dbSalesCustomer a                        '+
                           '           Left Outer Join dbKaryawan b on b.KeyNik=A.KeyNik  '+
                           '     where A.KodecustSupp=@KOdeCUstSupp             '+
                           '      order by A.Keynik                             '+
                           '     End ',[Nokira],['Kode', 'Nama Sales'],
                  [15, 50], QuBrows, EditBrows);
                end;

        2530101,2530102,2530103,
        2530201,2530202, 2530203
        ,2540101,2540102,2540103,
        2540201,2540202,2540203,
        25501,25502,25503,
        2560101,2560102,2560103,
        2560201,2560202,2560203,
        25701,25702,25703,
        25711,25712,25713,
        25721,25722,25723,
        25731,25732,25733,25741,25742,
        25761,25762,25763,
        3030101,3030102,3030103,
        3030201,3030202,3030203,
        35101,35102,
        4010201,4010202,
        40201,40202,
        40301,40302,
        4040101,4040102,
        4040201,4040202,
        40501,40502,
        40701,40702,
        40801,40802:
         begin
           SortBy:='NoBukti';
           Viewitems('Select '+ Fi +
                    ' From '+ Vw +
                    ' where tanggal between :0 and :1 '+
                    ' Order By  '+ Ordr ,
                    [TglAwal,tglAkhir],[''+colm1 ,''+Colm2],[30,15],QuBrows,EditBrows);
         end;

         40401
            :Begin
               SortBy:='NoBukti';
               ViewItem('Select distinct A.NoBukti, B.Tanggal, B.Kodecust KodeCustSupp, C.NamaCust NamaCustSupp, C.Alamat, C.kodekota Kota, '''' NoPO, null TGLPO, null Ship_Mark, A.Nobukti NoSO, '''' NoLC '+
                        'from vwBrowsOutSO_SPP A '+
                        '      left outer join dbSO B on B.NoBukti=A.NoBukti '+
                        ' left outer join vwBrowsCustomer C on C.KodeCust=B.KODECUST '+
                        ' order by B.Tanggal, A.NoBukti',
               ['Nomor Bukti','Tanggal','N','Customer','N','N','N','N','N','No. SO','N'],
               [25,15,1,40,1,1,1,1,1,25,1],QuBrows,EditBrows);
             end;
        40402
            :Begin
               SortBy:='KodeBrg';
               ViewItem(' Select A.Urut, A.KODEBRG, B.NAMABRG, '+
                        ' A.Satuan, A.QntSisa,  A.Isi, A.Nosat, A.Namabrgkom,B.Sat1,B.Sat2 '+
                         ' from vwBrowsOutSO_SPP A left outer join dbBarang B on B.KodeBrg=A.KodeBrg '+
                         '      left Outer join dbSO D on d.Nobukti=A.nobukti '+
                         ' where A.NoBukti='+QuotedStr(NoKira)+
                         ' and Isnull(A.IsCLoseDet,0)=0 '+
                         ' order by A.KodeBrg ',
               ['N','Kode Barang','Nama Barang','Satuan','Sisa', 'N','N','N','N','N'],
               [1,20,20,10,5,10,5,1,1,1],QuBrows,EditBrows);
             end;
        40421
            :Begin
               SortBy:='NoBukti';
               ViewItem(' Select distinct A.NoBukti, B.Tanggal, B.NoSO, B.KodeCustSupp, C.NamaCust NamaCustSupp, C.Alamat, C.kodekota Kota, A.Catatan '+
                        ' from vwOutSpp A '+
                        ' left outer join (Select x.NoBukti,o.Tanggal,x.Noso, z.Kodecust Kodecustsupp, o.IsFlag '+
                        '                  from dbSPPDet x '+
                        '                       left outer join dbSO z on z.Nobukti=x.NoSO '+
                        '                       left outer join dbSPP o on o.NoBukti=x.NoBukti '+
                        '                  group by x.Nobukti,o.Tanggal,z.Kodecust, o.IsFlag, x.NOSO) B on B.NoBukti=A.NoBukti '+
                        ' left outer join vwBrowsCustomer C on C.KodeCust=B.KodeCustSupp '+
                        ' left Outer join (Select NospB, UrutSPB from dbInvoicePLDet) D on D.NoSPB=A.NoBukti  and D.UrutSPB=A.Urut '+
                        ' where B.NoBukti='+QuotedStr(NoKira2)+' and (D.NoSPB is null and D.UrutSPB is null) '+
                        ' order by B.Tanggal, A.NoBukti',
               ['Nomor Bukti','Tanggal','N','N','Supplier','N','N','N'],[25,15,1,1,40,1,1,1],QuBrows,EditBrows);
             end;
        40422
            :Begin
               SortBy:='KodeBrg';
               ViewItem(' Select A.Urut, A.KODEBRG, B.NAMABRG, A.Qnt2Sisa, A.Sat_2, A.QntSisa, A.Sat_1, A.Isi, A.Nosat, A.NetW, A.GrossW, A.Namabrg Namabrgkom, 0.00 Toleransi, '+
                        ' A.Qnt, A.Qnt2,A.QntSPB, A.Qnt2SPB, B.Nfix Konversi '+
                        ' from vwBrowsOutSpp A left outer join dbBarang B on B.KodeBrg=A.KodeBrg '+
                        ' left Outer join dbSPP c on c.nobukti=a.nobukti '+
                        ' where A.NoBukti='+QuotedStr(NoKira)+
                        ' order by A.KodeBrg ',
               ['N','Kode Barang','Nama Barang','Sisa2','Sat2','Sisa1','Sat1','N','N','N','N','N','N','N','N','N','N','N'],
               [1,20,50,10,5,10,5,1,1,1,1,1,1,1,1,1,1,1],QuBrows,EditBrows);
             end;
        404301
            :begin
               Sortby:='NoSPB';
               ViewItem('Select  Distinct B.NoSPB, B.TglSPB, B.NoSPP, B.TglSPP, B.Noship, B.TglShip, B.NoSC, B.TglSC, B.IsLokal,'+
                        'B.KodecustSupp,c.NamaCust NAMACUSTSUPP, c.Alamat+'' ''+c.NamaKota Alamat ,A.FlagTipe '+
                        'from vwbrowsOutSPB_RSPB a '+
                        '     left Outer join vwSPB b on b.noSPB=a.nobukti '+
                        'left Outer join vwBrowsCustomer c on c.KODECUST=b.kodecustSupp '+
                        'where b.IsFlag='+NoKira+
                        ' Order by b.nospb',
                        ['No SPB', 'Tanggal SPB','N','N','N','N','N','N','Tipe','Kode Customer','Nama Customer','Alamat','N'],
                        [30,20,1,1,1,1,1,1,20,15,40,40,1],QuBrows,EditBrows);
             end;
        404302
            :begin
               Sortby:='NoSPB';
               ViewItems('Select a.nobukti, a.urut, a.kodebrg, b.Namabrg, a.Namabrg NamabrgKom, '+
                         '       Case when a.nosat=1 then a.QntSisa '+
                         '            when a.nosat=2 then a.Qnt2Sisa '+
                         '            else 0 '+
                         '       end Qty,'+
                         '       Case when a.nosat=1 then a.Sat_1 '+
                         '            when a.nosat=2 then a.Sat_2 '+
                         '            else '''' '+
                         '       end Satuan,'+
                         'a.Sat_1, a.sat_2, a.nosat, a.isi, a.qnt, a.qnt2, a.qntRSPB, a.Qnt2RSPB,'+
                         '       a.QntSisa, A.qnt2Sisa, a.netw, a.GrossW,B.NFix '+
                         'from vwBrowsOutspB_RSPB a '+
                         '     left Outer join (Select Kodebrg, Namabrg,Nfix from DBBARANG) b on b.KODEBRG=a.kodebrg '+
                         'Where a.nobukti=:0 '+
                         'Order by a.nobukti,a.urut ',[Nokira],
                        ['N', 'N','Kode Barang','Nama Barang','N','Qty','Satuan','N','N','N','N','N','N','N','N','N','N','N','N','N'],
                        [1, 1,30,40,1,15,5,1,1,1,1,1,1,1,1,1,1,1,1,1],
                        QuBrows,EditBrows);
             end;
        30051
            :Begin
               SortBy:='KodeCustSupp';
               ViewItem(' Select A.KodeCustSupp, A.NamaCustSupp, A.Alamat, A.Kota, A.Fax, A.Negara, A.Usaha,PPN from vwBrowsCust A '+
                        ' order by A.KodeCustSupp',
               ['Kode','Nama','N','N','N','N','N','N'],
               [15,50,1,1,1,1,1,1],QuBrows,EditBrows);
             end;
        8005 : begin   //dbinvoicePL + lokal
                   SortBy:='NoBukti';
                   ViewItem(' select a.nobukti, a.tanggal, '''' Keterangan, a.kodecustSupp,B.NoSo '+
                            ' from dbInvoicePL a '+
                            '      Left Outer join (Select nobukti,Sum(Qnt) Qnt,NoSo From dbinvoicePLDet x Group by nobukti,NoSo) b on b.nobukti=a.nobukti '+
                            '      Left Outer join (Select noinvoice,Sum(Qnt) Qnt From dbRinvoicePLDet x Group by noinvoice) c on c.noinvoice=a.nobukti '+
                            ' where Cast(Case when Case when A.IsOtorisasi1=1 then 1 else 0 end+'+#13+
                            '                      Case when A.IsOtorisasi2=1 then 1 else 0 end+'+#13+
                            '                      Case when A.IsOtorisasi3=1 then 1 else 0 end+'+#13+
                            '                      Case when A.IsOtorisasi4=1 then 1 else 0 end+'+#13+
                            '                      Case when A.IsOtorisasi5=1 then 1 else 0 end=A.MaxOL then 0'+#13+
                            '                else 1'+#13+
                            '           end As Bit)=0 and a.KodecustSupp='+QuotedStr(NoKira)+
                            '       and B.Qnt-isnull(c.Qnt,0)>0 '+
                            ' Order by a.nobukti',
                   ['No. Faktur Penjualan','Tanggal','N', 'N','N'],
                   [25,15,20,1,1],QuBrows,EditBrows);
                 end;
        8006 :begin
                   SortBy :='KodeBrg';
                   ViewItems('Select Kodebrg, Namabrg, QtySisa, Satuan, NetWSisa, GrossWSisa, SAT_1, SAT_2, Nosat, Isi, Urut, QntSisa, Qnt2Sisa, Harga, NamabrgKom '+
                             'from vwOutInvoicePL_RInvoicePL '+
                             'where nobukti=:0 and NoSPB=:1 '+
                             'Order by urut',
                             [NoKira, Nokira2],
                             ['Kode Barang', 'Nama Barang', 'Qty', 'Satuan', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N'],
                             [25,50,12,10,12,12,1,1,1,1,1,1,1,1,1], Qubrows, EditBrows);
                 end;
        8007 :begin   //SPB Lokal + Export
                   SortBy:='NoBukti';
                   ViewItem('select nobukti, tanggal from dbSPB where noBukti='+QuotedStr(NoKira)+' '+
                            'Order by nobukti',
                   ['No. SPB','Tanggal'],
                   [25,15],QuBrows,EditBrows);
                 end;
        8013:begin
                   SortBy:='Nobukti';
                   ViewItem(
                           ' Select distinct A.NoBukti, D.Tanggal, B.NoSO, B.KodeCustSupp, C.NamaCust NamaCustSupp, C.Alamat, C.NamaKota Kota, '''' Catatan,'''' '+
                           ' from vwBrowsOutRJual A '+
                           ' Left Outer join DBRInvoicePL D on D.NoBukti=A.Nobukti'+
                           ' Left outer join (Select x.NoBukti,x.Tanggal, x.KodeCustSupp, z.NOBUKTI NoSo, z1.KODESLS '+
                           '                  from dbInvoicePL x                                                    '+
                           '                  Left Outer join dbInvoicePLDet y on y.nobukti=x.nobukti               '+
                           '                  left Outer join dbSPBDet SPB on SPB.NoBukti=y.NoSPB and SPB.Urut=y.UrutSPB '+
                           '                  left Outer join dbSPPDet o on o.NoBukti=SPB.NoSPP and o.Urut=SPB.UrutSPP '+
                           '                  left Outer join DBSODET z on z.NoBukti=o.NoSO and z.Urut=o.UrutSO '+
                           '                  left outer join DBSO z1 on z1.NOBUKTI=z.NOBUKTI                   '+
                           '                  Group by  x.NoBukti,x.Tanggal, x.KodeCustSupp, z.NOBUKTI, z1.KODESLS '+
                           ') B on B.NoBukti=A.Noinvoice                                                        '+
                           'left outer join vwBrowsCustomer C on C.KodeCust=B.KodeCustSupp and c.Sales=B.KODESLS '+
                           ' order by D.Tanggal, A.NoBukti ',
                            ['No. Retur ', 'Tanggal', 'No. SO','N', 'Nama Supplier','N', 'N', 'N', 'N'],
                            [50,12,50,1,50,1,1,1,1],QuBrows,EditBrows);
                 end;
        80131:begin
                   SortBy:='Nobukti';
                   ViewItems('select A.Kodebrg,C.NAMABRG,  '+
                           ' Case when A.NOSAT=1 then A.SAT_1  '+
                           '		When A.NOSAT=2 Then A.SAT_2 End Satuan,  '+
                           '                 Case When A.NOSAT=1 then A.QNT When A.NOSAT=2 then A.QNT2 End Qnt,  '+
                           '                 A.QNT QNT1,A.QNT2 QNT2,B.Noinv,A.Urut UrutINv,A.Sat_1,A.Sat_2 '+
                           ' ,A.ISI ,A.Qnt - Isnull(B.QNT1,0) QntSisa,A.Qnt2 - Isnull(B.QNT2,0) Qnt2Sisa,A.NOsat '+
                           '     from dbInvoicePLDet A   '+
                           '     Left OUter Join (       '+
                           '                       select NoINV,UrutINV,KodeBrg,SUM(ISNULL(QNT,0)) Qnt1,SUM(ISNULL(QNT2,0)) Qnt2  '+
                           '                       from dbSPBRJualDet  '+
                           '                       Group By NoINV,UrutINV,KodeBrg  '+
                           '                      ) B on A.NoBukti=B.NoINV and A.Urut=B.UrutINV  '+
                           '     Left OUter join DBBARANG C on A.KodeBrg =C.KodeBrg  '+
                           '     Where  '+
                           '     (A.QNT-isnull(B.Qnt1,0)>0 or A.QNT2 - isnull(B.Qnt2,0)>0) And A.Nobukti=:0   '+
                           ' order by A.KodeBrg ',[nokira],
                            ['Kode Barang ', 'Nama Barang', 'Satuan','QNT','N', 'N','N','N','N','N','N','Sisa','N','N'],
                            [15,50,20,21,1,1,1,1,1,1,1,20,1,1],QuBrows,EditBrows);
                 end;
        8014:begin
                   SortBy:='Nobukti';
                   ViewItems('Select a.NoBukti, a.Tanggal, a.KodeCustSupp, B.NamaCustSupp, B.Alamat, C1.NOSPB, D.Tanggal  TGLSPB,  '+#13+
                            ' C1.Tipe,A.noRpj NoINv,a.FlagTipe,M1.Tanggal TglINv,m1.PONO,M1.ContractNo,M2.Tanggal TglSPBX,M4.TipeBayar,M4.Hari  '+#13+                            ' ,M1.Valas,M1.PPN,M1.Kurs  '+#13+
                            ' from dbSPBRjual a '+#13+
                            ' left Outer join vwBrowsCust b on b.KodeCustSupp=A.kodecustSupp    '+#13+
                            ' Left Outer join (Select nobukti, NoInvoice from DBRInvoicePLDET Group by nobukti, NoInvoice) C on C.nobukti=a.NoRPJ   '+#13+                            ' Left Outer join (Select NoSPB, NoBukti, 1 Tipe from dbInvoicePLDet group by NoSPB, NoBukti) C1 on C1.NoBukti=C.NoInvoice   '+#13+                            ' Left Outer Join DbInvoicePL M1 on A.NoRpj =M1.Nobukti   '+#13+                            ' left Outer join (Select Nobukti, Tanggal from DBSPB ) D on D.NoBukti=C1.NoSPB   '+#13+                            ' left Outer join (select Distinct(Nobukti),NOspp,tanggal from DbSPb) M2 on M1.ContractNo=M2.Nobukti  '+#13+                            ' Left Outer join (select Distinct(Nobukti),NOship from DbSPP) M3 on M2.NOSPP=M3.NObukti   '+#13+                            ' Left Outer join (select Distinct(Nobukti),tipebayar,hari from DBSO) M4 on M3.NOSHIP=M4.NObukti   '+#13+
                            ' where '+#13+
                            '       Cast(Case when Case when A.IsOtorisasi1=1 then 1 else 0 end+'+#13+
                            '                      Case when A.IsOtorisasi2=1 then 1 else 0 end+'+#13+
                            '                      Case when A.IsOtorisasi3=1 then 1 else 0 end+'+#13+
                            '                      Case when A.IsOtorisasi4=1 then 1 else 0 end+'+#13+
                            '                      Case when A.IsOtorisasi5=1 then 1 else 0 end=A.MaxOL then 0'+#13+
                            '                  else 1'+#13+
                            '             end As Bit)=0'+#13+
                            ' And A.KodeCustSupp=:0  and A.FlagTipe=:1 '+#13+
                            ' order by a.nobukti',[IsiData,Nokira],
                            ['No. Retur ', 'Tanggal', 'N', 'Nama Supplier','N', 'N', 'N','N','N','N','N','N','N','N','N','N','N','N','N'],
                            [50,12,1,100,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],QuBrows,EditBrows);
                 end;
          80141 :begin
                   SortBy:='Nobukti';
                   ViewItems('Declare @noBukti Varchar(30)'+#13+
                             'Set @Nobukti=:0'+#13+
                             'Select A.KODEBRG, B.NAMABRG, A.NoSat, A.Qnt2-isnull(C.Qnt2,0) Qnt2Sisa, A.Sat_2, A.Qnt-isnull(C.Qnt,0) QntSisa, '+#13+
                             '       A.Sat_1, A.Isi, A.NoBukti, A.Urut,M1.Harga '+#13+
                             'from dbSPBRJualDet A '+#13+
                             'left outer join dbBarang B on B.KodeBrg=A.KodeBrg '+#13+
                             '     left outer join (select NOSPR, UrutSPR, sum(Qnt2) Qnt2, sum(Qnt) Qnt '+#13+
                             '                      from DBINVOICERPJDet where NOSPR=@Nobukti '+#13+
                             '                      group by NOSPR, UrutSPR) C on C.NOSPR=A.NoBukti and C.UrutSPR=A.Urut '+#13+
                             ' Left OUter join dbInvoicePLDet M1 on A.Noinv = M1.Nobukti and A.urutINv=M1.urut '+#13+
                             'where A.NoBukti=@Nobukti and ((A.Qnt2-isnull(C.Qnt2,0))>0 or (A.Qnt-isnull(C.Qnt,0))>0) '+#13+
                             '  order by A.KodeBrg',[Nokira],
                            ['Kode Barang', 'Nama Barang', 'N', 'Qnt2 Sisa','Sat 2', 'Qnt Sisa', 'Sat 1', 'N','N','N'],
                            [30,50,1,12,6,12,6,1,1,1],QuBrows,EditBrows);
                 end;
        914 : begin
                 SortBy := 'KodeSubtipe';
                  ViewItemS('Select A.Lokasi '+
                           'from DBLokasi A '+
                           'where A.KodeGDG=:0 '+
                           'Order by A.Lokasi',[NoKira],
                           ['Lokasi'],[80],QuBrows,EditBrows);
                end;
        915 : begin
                 SortBy:='Kodebrg';
                  ViewItem('select a.Kodebrg, a.NamaBrg from Dbbarang a '+
                           ' where a.isaktif=1 order by a.Kodebrg',
                  ['Kode Barang', 'Nama Barang','N'],
                  [30, 50], QuBrows, EditBrows);
                end;
        916 : begin
                  ViewItem('select KodeGDg,Nama NamaGdg from dbGudang  '+
                           'order by KodeGdg',
                  ['Kode', 'Nama '],
                  [10, 30], QuBrows, EditBrows);
                end;
        917 : begin
                 SortBy:='Kodebrg';
                  ViewItem('select a.Kodebrg, a.NamaBrg from Dbbarang a '+
                           ' where a.isaktif=1 order by a.Kodebrg',
                  ['Kode Barang', 'Nama Barang'],
                  [30, 50], QuBrows, EditBrows);
                end;
        100444  : begin
                  ViewItem('select Perkiraan,keterangan from dbPerkiraan where tipe=1 '+
                           ' order by Perkiraan',
                  ['Perkiraan', 'Keterangan'],
                  [15, 50], QuBrows, EditBrows);
                end;
        157  :begin
                  ViewItemS('select KOdeSubGrp,NamaSubGrp from DbSubGroup where Kodegrp=:0 order by KodeSubGrp',
                  [Nokira],['Kode Sub Group', 'Nama Sub Group'],
                  [15, 50], QuBrows, EditBrows);
                end;
        1577  :begin
                  ViewItem('select KeyNIK,NIK,Nama from dbKaryawan where IsSales=1 and Aktif=1 and KeyNIK not in (select KeyNIK from DBASM)',
                  ['N','NIK', 'Nama'],
                  [1,15, 50], QuBrows, EditBrows);
                end;
        1576  :begin
                  ViewItem('select KeyNIK,NIK,Nama from dbKaryawan where  Aktif=1 and KeyNIK not in (select KeyNIK from DBASM)',
                  ['Keynik','NIK', 'Nama'],
                  [5,15, 50], QuBrows, EditBrows);
                end;

        15779  :begin
                  ViewItem('select KeyNIK,NIK,Nama from dbKaryawan where  Aktif=1 and KeyNIK not in (select KeyNIK from DBASM)',
                  ['N','NIK', 'Nama'],
                  [1,15, 50], QuBrows, EditBrows);
                end;
        15780  :begin
                  ViewItem('select KeyNIK,a.NIK,Nama,tarif from dbKaryawan a left outer join dbtariftenaker b on b.nik=a.nik where  Aktif=1 and Produksi<>0',
                  ['N','NIK', 'Nama','Tarif'],
                  [1,15, 50,20], QuBrows, EditBrows);
                end;

         91112 : begin
                  ViewItem('Select * from DbjenisCustSupp',
                  ['Kode jenis', 'Nama Jenis'],
                  [10, 30], QuBrows, EditBrows);
                end;

         91113 : begin
                  ViewItem('Select KodePrs,Keterangan from DbProses',
                  ['Kode Proses', 'Ket'],
                  [10, 30], QuBrows, EditBrows);
                end;
         91114 : begin
                  ViewItem('Select KodeMsn,Ket,kapasitas,Tarif from DBMesin',
                  ['Kode Mesin', 'Ket','N','Tarif'],
                  [10, 30,0,10], QuBrows, EditBrows);
                end;

         91115 : begin
                  ViewItem('select Perkiraan, Keterangan from DBPERKIRAAN where Kelompok = 4',
                  ['Perkiraan', 'Keterangan'],
                  [10, 30], QuBrows, EditBrows);
                end;
         91116 : begin
                 ViewItem('select a.KODEMSN,b.Ket,a.Tanggal,tarif,a.urut from ' +
                   'DBJADWALPRD a  left outer join DBMesin b on b.KodeMsn= ' +
                   'a.KODEMSN left outer join DBHASILPRDDET c on c.NOSPK = ' +
                   'a.NOSPK and c.KODEMSN = a.KODEMSN and c.urutmesin =a.urut where c.KodeMsn  is null  ' +
                   //'and a.urutmesin='+ inttostr(jnspakai) +
                   ' and a.nospk=' +QuotedStr(IsiData) +' order by a.urut' ,
                  ['Kodemsn', 'Keterangan','TanggalSPK','N','Urut'],
                  [15, 30,20,0,5], QuBrows, EditBrows);


{                  ViewItem('select a.KODEMSN,b.Ket,a.Tanggal,tarif,a.urut from
  ' +
                    'DBJADWALPRD a  left outer join DBMesin b on b.KodeMsn=' +
                    'a.KODEMSN left outer join DBHASILPRDDET c on c.NOSPK =' +
                    'a.NOSPK '+
                    'and c.KODEMSN = a.KODEMSN where c.KodeMsn  is null and urutmesin='+ jnspakai  +'and a.nospk=' +QuotedStr(IsiData),
                  [ 'Kode Mesin','Nama Mesin','Tanggal','Tarif'],
                  [ 30,30,1,10], QuBrows, EditBrows);}
                end;
         2082   : begin
                   ViewItem('select a.KodeVls, a.NamaVls, a.Kurs from dbValas a '+
                   ' order by a.KodeVls',
                   ['Kode', 'Nama Valas','Kurs'],
                   [10, 30, 10], QuBrows, EditBrows);
                 end;
    // 911141 : begin  //spk

    //        end;
        { 999   : begin
                    SortBy:='Kodebrg';
                    Viewitems('select NoBukti,Urut,KodeBrg,Disc,NOSAT,SAT_1,QNT,SAT_2,QNT2,ISI,DiscP,DiscP2,DiscP3,DiscP4,DiscP5,DiscRp  '+
                            ' ,DISCTOT,HARGA    '+
                            ' ,Case when Nosat=1 then QNT when Nosat=2 then QNT2 End QntEnt '+
                            ' from dbInvoicePLDet '+
                            ' where NoBukti=:0  '+
                    ' Order By  NOBukti',[IsiData],
                    ['Nomor Bukti','Urut','Kode Barang','N','Nosat','Sat 1','Qnt 1','Sat 2','Qnt 2','Isi','Discp','Discp2',
                    'Discp3','Discp4','Discp5','Disc RP','Harga','N'],[30,10,20,1,10,20,20,20,20,20,20,20,20,20,20,20,30,30,1],QuBrows,EditBrows);
                End; }


  end;
end;

procedure TFrBrows.ResizeControls(padOnly: boolean);
const spacing = 13;
var
   i,growByWidth, desiredSize: integer;
   minWidth: integer;
   totalColWidth: integer;
   temp: integer;

   Function max(x,y: integer): integer;
   begin
      if x>y then result:=x
      else result:= y;
   end;
   Function min(x,y: integer): integer;
   begin
      if x<y then result:=x
      else result:= y;
   end;
begin
   desiredSize:= GetSystemMetrics(SM_CXHThumb) + 1;  {Win95 fix }
   {$ifdef win32}
   inc(desiredSize,3);
   {$endif}

   minWidth:= 3;

   for i:= 0 to GridBrows.ColumnCount-1 do
      desiredSize:= desiredSize + GridBrows.Columns[i].Width + 1;//GridBrows.GridLineWidth;
   TotalColWidth:= desiredSize;

   if (desiredSize < minWidth) then {pad last field }
   begin
      { 4/30/98 - ColWidths is rounded to character boundary so expand grid by difference }
      //temp:= GridBrows.colWidths[GridBrows.getColCount-1] + (minWidth - desiredSize);
      //GridBrows.colWidths[GridBrows.getColCount-1]:= temp;
      //desiredSize:= minWidth +  GridBrows.colWidths[GridBrows.getColCount-1]-temp;

      temp:= GridBrows.Columns[GridBrows.ColumnCount-1].Width + (minWidth - desiredSize);

      GridBrows.Columns[GridBrows.ColumnCount-1].Width:= temp;
      desiredSize:= minWidth +  GridBrows.Columns[GridBrows.ColumnCount-1].Width-temp;
   end;
   if padOnly then exit;

   If MaxWidth= 0 then MaxWidth:= Screen.width-40
   else MaxWidth:= min(MaxWidth, screen.width - 40);

   desiredSize:= min(desiredSize, MaxWidth);
   desiredSize:= max(desiredSize, minWidth);

   GrowByWidth:= desiredSize - GridBrows.width;
   GridBrows.width:= GridBrows.width + GrowByWidth;

   //if TotalColWidth <= GridBrows.width then  { Don't show horizontal scroll bar }
   //   GridBrows.ShowHorzScrollBar:= False;
   //GridBrows.DoPerfectFit;  { Adjust size before its shown }

   self.width:= self.width + GrowByWidth;

   FrBrows.Width:=GridBrows.width+30;
end;



procedure TFrBrows.FormKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
   if key=VK_ESCAPE then
   begin
      Close;
   end else
   if Key=VK_RETURN then
   begin
     if QuBrows.IsEmpty then
      Close;
   end;
end;

procedure TFrBrows.FormClose(Sender: TObject; var Action: TCloseAction);
begin
  Action:=cafree;
end;

procedure TFrBrows.GridBrowsDblClick(Sender: TObject);
begin
  ModalResult:=mrOk;
end;

procedure TFrBrows.FormShow(Sender: TObject);
begin
  QuBrows.Connection:=DM.MyStock;
  EditBrows:=IsiData;
  EditBrows1:=IsiData1;
  {TambahBtn.Visible:=false;
  HapusBtn.Visible:=false;
  KoreksiBtn.Visible:=false; }
  GridBrows.OptionsBehavior:=[edgoAutoSearch,edgoAutoSort,edgoDragScroll,edgoEnterShowEditor,edgoMultiSelect,
    edgoTabThrough,edgoVertThrough];
  FilterDataBrows;
  EditBrows:=IsiData;
  Button2.Left:=FrBrows.Width-100;
  Button1.Left:=Button2.Left-75;
  if QuBrows.IsEmpty then ActiveControl:= Button2;

end;

procedure TFrBrows.Button1Click(Sender: TObject);
begin
  ModalResult:=mrOK;
end;

procedure TFrBrows.Button2Click(Sender: TObject);
begin
  case KodeBrows of
    100407 : begin
               xAktiva:='NO';
             end;
  end;
  close;
end;

procedure TFrBrows.GridBrowsMouseUp(Sender: TObject; Button: TMouseButton;
  Shift: TShiftState; X, Y: Integer);
begin
  if (Button <> mbRight) or (Shift <> []) then Exit;
  TdxDBGridPopupMenuManager.Instance.ShowGridPopupMenu(Sender as TdxDBGrid);
end;

procedure TFrBrows.Button4Click(Sender: TObject);
begin
    GridBrows.SelectAll;
end;

procedure TFrBrows.TambahBtnClick(Sender: TObject);
begin
  case KodeBrows of
    100407 : begin
               xAktiva:='OK';
               ModalResult := mrNo;
             end;
  end;
end;

procedure TFrBrows.GridBrowsKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
  if (Key=VK_UP) and (QuBrows.RecNo=1) then
    ActiveControl:=EditFilter
   else if key=13 then
    begin
    ModalResult:= mrOK;
    End;
end;

procedure TFrBrows.EditFilterKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
 // if Key=VK_Down then
 //   ActiveControl:=GridBrows;
 if Key=VK_Down then
    ActiveControl:=GridBrows
  else if Key=13 then
    begin
     if QuBrows.IsEmpty then
      ModalResult:=mrOK
     Else
     Begin
     FormShow(Self);
     Application.ProcessMessages;
     activecontrol:=GridBrows;
     End;
    End
 else if Key=Vk_Escape then
    begin
     ModalResult:=mrOK;
    End;
end;

procedure TFrBrows.EditFilterChange(Sender: TObject);
begin
  FilterDataBrows;
 // Application.ProcessMessages;
end;

end.
