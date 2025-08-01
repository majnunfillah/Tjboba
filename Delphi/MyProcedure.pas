unit MyProcedure;

interface

uses
    SysUtils, ADOdb, Windows, Forms, Dialogs, Graphics, Wwdbgrid, WinSock, Variants,
    StdCtrls, DB, dxdbGrid, Mask, Controls, StrUtils, PBNumEdit,cxGrid,
     Sockets, cxStyles, cxCustomData, cxGraphics,
  cxFilter, cxData, cxEdit, cxDBData, cxGridLevel, cxGridCustomTableView,
  cxGridTableView, cxGridDBTableView, cxClasses, cxControls,
  cxGridCustomView,  dxPageControl, cxGridBandedTableView,
  cxGridDBBandedTableView,messages, ToolEdit,MyGlobal,frxDBSet,frxClass;

const
     HrfAngka : Array[0..19] of String = ('', 'Satu ', 'Dua ', 'Tiga ', 'Empat ', 'Lima ', 'Enam ', 'Tujuh ', 'Delapan ',
                                           'Sembilan ' , 'Sepuluh ', 'Sebelas ' ,'Dua Belas ', 'Tiga Belas ', 'Empat Belas ',
                                           'Lima Belas ', 'Enam Belas ', 'Tujuh Belas ', 'Delapan Belas ', 'Sembilan Belas ');
var
    Kode,Nama :String;

procedure BukaSatuanBrg(pKodeBrg: string; var pADOQuery: TADOQuery);
procedure IsiQnt1FromQnt2(pKodeBrg: String; pQnt2: TPBNumEdit; var pQnt1: TPBNumEdit);
procedure TampilLabelDiskon(pTipeDisc: Integer; var pLblDiscP, pLblDiscRp: TLabel);
procedure UpdateValas(pValas: String; pNilaiKurs: Real);
procedure ShowReportPreview(JudulForm:String;PageIndex:Integer);
function MyFindField(Tabel:String;Field:String;Data:String):boolean;
procedure FindDuaKategori(Query:TADOQuery;Field1:String;Field2:String;Tabel:String;Data:String);
function MyCariPeriode(Nama:string):Boolean;
function MyCek_Lock_Periode(Bukti:string):Boolean;
function MyAktifTgl(T:TdateTime;Nama:String):TdateTime;
function MyCariUserName(Nama : string; Kunci : String; var Tstatus,Tlevel: integer):Boolean;
procedure Delay(Lama : LongWord);
function Check_Nomor(Bulan,Tahun:integer;Tipe:String;var Nomor,xNoUrut:String;xDate:TDateTime):Boolean; Overload;
function Check_NomorBeli(Bulan,Tahun:integer;Tipe:String;var Nomor,
  xNoUrut:String;xDate:TDateTime;Xbeli:string):Boolean; Overload;
{function Check_NomorPB(Bulan,Tahun:integer;Tipe:String;var Nomor,
  xNoUrut:String;xDate:TDateTime;Xbeli:string):Boolean; Overload;}
function Check_NomorPB(Bulan,Tahun:integer;Tipe:String;var Nomor,
  xNoUrut:String;xDate:TDateTime;Xbeli:string;Jenis:integer):Boolean; Overload;
function Check_NomorRPB(Bulan,Tahun:integer;Tipe:String;var Nomor,
  xNoUrut:String;xDate:TDateTime;Xbeli:string;Jenis:integer):Boolean; Overload;

function Check_Nomor(Bulan,Tahun:integer;Tipe:String;var Nomor,xNoUrut:String;xDate:TDateTime;xCustSupp:String;xIsPpn:Boolean):Boolean; OverLoad;
function Check_NomorKasBank(pBulan, pTahun: integer; pTipe:String; var pNomor, pNoUrut: String; pDate: TDateTime; pSimbol: String; pIsPpn: Boolean): Boolean; OverLoad;
function NewNo(No:String;Digit:integer):String;
Function KonversiKeTeks(Bilangan : Real):String;
function Daftar_Nomor(Tipe,xNoUrut,Nomor:String;Koreksi:integer):Boolean;
procedure UpdateNomor(Bulan,tahun,Tipe:String;PPn:String;Nomor:String;Digit:integer);
procedure Hapus_Daftar_Nomor(Tipe:String;NoBukti,pemakai:String);
function Romawi(Tanggal:Tdatetime;Mode:String):String;
function CekPeriode(Nama:string;tgl:Tdatetime):Boolean;
Function DataBersyarat(mSelect : String; mParam : Array of Variant; Var mQuery : TAdoQuery) : Boolean;
procedure DataBuka(mSelect : String; Var mQuery : TAdoQuery);
procedure UrutField(mSelect : String; Var mQuery : TAdoQuery);
Procedure UrutField2(mSelect : String; mParam : Array of Variant; Var mQuery : TAdoQuery);
procedure Hapus_Daftar_Nomor_User(Tipe:String;Pemakai:String);
procedure GeserKalimat(Kalimat:String;var Hasil:String);
function IsLockPeriode(Bulan,Tahun:Integer):Boolean;
procedure UpdateStatusUser(Pemakai:String;Status:integer);
Function GetMyHostName: String;
function Kalimat(Digit:Integer;Kata:String):String;
function Encrypt(const InString:string; StartKey,MultKey,AddKey:longInt): string;
function Decrypt(const InString:string; StartKey,MultKey,AddKey:Integer): string;
function GenerateCode(tabel,field,nama:string):string;
function GenerateCodeCust(tabel,field,nama:string):string;
function GenerateCodeSupp(tabel,field,nama:string):string;
function GenerateCode2(tabel,field,nama:string):string;
function GenerateCode3(tabel,field,nama:string):string;
function GetImageLinkTimeStamp(const FileName: string): DWORD;
procedure CekOtoritasMenu(Pemakai:String;NoMenu:integer;var Tmb,Hps,Krs,Ctk,
  Exc:Boolean);
  function UnixTimeToDateTime(UnixTime: DWORD): TDateTime;
Procedure SaveToIniFiles(pemakai:string;form:TForm;mGrid : TdxdbGrid);
Function Mymonth(x:integer):String;
function MyCek_Lock_PeriodeProses(Bln,Thn:String):Boolean;
procedure Hapus_Daftar_Nomor_Grp(Tipe:String);
Procedure LoggingData(pPemakai, pAktivitas, pSumber, pNoBukti, pKeterangan: String);
Procedure Konfig(Tipe,xNomor:String;Var xF1,xF2,xF3,xF4,NoUrut : String;Var yF1,yF2,yF3,yF4:Integer;xDate:TDateTime);OverLoad;
Procedure Konfig(Tipe,xNomor:String;Var xF1,xF2,xF3,xF4,NoUrut : String;Var yF1,yF2,yF3,yF4:Integer;xDate:TDateTime;xIsPpn:Boolean);OverLoad;
Function CariKoma(Nilai : string) : Integer;
procedure MsgTglTidakSesuaiPeriode;
function  CekPemakaiGdg(pPemakai, pGudang: String): Boolean;
Function BukaNoLot(pKodeGrp: string; var pADOQuery: TADOQuery):Boolean;
function GetIPFromHost(var HostName, IPaddr, WSAErr: string): Boolean;
procedure TampilIsiData(xForm:TForm;var xEdit:TEdit;var xlabel:TLabel;xkodebrows:Integer;xkode,xNama:String);
function UrutAktiva(No,xDevisi:String;Digit:integer):String;
function  MyNoBukti: String;
procedure MsgDataTidakBolehKosong(pData: String);
procedure MsgKoreksiDataKosong;
procedure MsgTglKirimDataKosong;
procedure MsgHapusDataKosong;
procedure MsgTidakBerhakTambahData;
procedure MsgTidakBerhakKoreksiData;
procedure MsgTidakBerhakHapusData;
procedure MsgTidakBerhakExportData;
procedure MsgTidakBerhakCetakData;
procedure MsgPeriodeSudahDikunci;
procedure MsgTanggalTidakSesuaiPeriode;
procedure MsgProsesGagal(pChoice: String);
procedure MsgDataSudahADA(pData: String);
procedure MsgNeedOtorisasi;
procedure MsgCetakOtorisasi;
function  MsgNomorBuktiSudahAda(pData, pNoBuktiLama, pNoBuktiBaru: String): Boolean;
Procedure CariSatuan(Kode,mNoPo:String;Satuan:Byte;Var Harga,xIsibrg:Real;var xSat,xSatuan:String;var xStatus:Boolean);
Procedure IsiDataAktiva(xGroupAktiva:sTring; var xTipe,xAkumulasi,xBiaya1,xBiaya2 : String; Var xPersen,xPbiaya1,xPBiaya2 : Real);
function  UrutAktiva2(No,xDevisi:String;Digit:integer):String;
procedure UpdateUrutTransaksi(xTable,xnobukti:String);
Function  MyRomawi(x:integer):String;
procedure DisableEnableTrigger(Enabled:Boolean;xTable:String);
Procedure Otorisasi(nTabel, xkodemenu, xIDUSER,xKunci, xNobukti :String);
Procedure BatalOtorisasi(nTabel, xkodemenu, xIDUSER,xKunci, xNobukti :String);
procedure ViewOtorisasi(Var TView : TcxGridDBBandedTableView; Var mQuery: TADOQuery; OL: Integer);
procedure ViewOtorisasiNonBandedTableView(Var TView : TcxGridDBTableView; Var mQuery: TADOQuery; OL: Integer);
procedure ComboBox_AutoWidth(Const theComboBox: TCombobox);
function NoToStr(No,Digit:integer):String;
function GenerateCode4(tabel,field,nama:string):string;
function IsiSatuanBrg(pKodeBrg: string; var pNamaSatuan: TNamaSatuanBrg; var pIsiSatuan: TIsiSatuanBrg): String;
function IsiSatuanBrgJual(pKodeBrg, pkodeCust: string; var pNamaSatuan: TNamaSatuanBrg; var pIsiSatuan: TIsiSatuanBrg; Var pHargaBrg: THargabrg): String;
procedure MainCetak(TFRXDB1,TFRXDB2:TfrxDBDataset;Q1,Q2:TADOQuery;SP1,SP2,NOB,Fr3:String;frx:TfrxReport);
procedure NilaiCetak(Fie,Tbl,Param:String);
Function CekOtorisasi(nTabel, xKodemenu,xIDUser,xKunci, xNobukti :String):Boolean;
Function GetNomorJurnal(nTabel, xKodemenu,xIDUser,xKunci, xNobukti :String):String;
Procedure KonfigNojurnal(Tipe,xNomor:String;Var xF1,xF2,xF3,xF4,NoUrut : String;Var yF1,yF2,yF3,yF4:Integer;xDate:TDateTime;XPPN:Integer);
procedure pembatalan(xtable,xfield,xnobukti,xuser,xkodemenu:String);
procedure GetBrowse(XkodeBrowse:Integer;XDb,Xkode,Xname:String;xform:TForm;xEdit:TEdit;xlabel:Tlabel);
procedure CekNota(Fie,Tbl,Param:String);
procedure MSGStatusCetak;
procedure VlsIDR(XEdit:TEDIT;XNumEdit:TPBNumEDIT);
Procedure IsiComboBox(pComboBox: TComboBox; pItemsComboBox: Array of Variant);
procedure pembatalanPO(xtable,xfield,xnobukti,xkodebrg,xqnt,xuser,
  xkodemenu:String;QntBatal,Xurut:Integer);
procedure MainCetak1(TFRXDB1,TFRXDB2:TfrxDBDataset;Q1,Q2:TADOQuery;SP1,SP2,NOB,Fr3:String;frx:TfrxReport);
function IsLockPeriodeNK(Bulan,Tahun:Integer):Boolean;

implementation
uses MyModul, FrmBrows, dxDBCtrl, DateUtils, frmReportpreview;


procedure ComboBox_AutoWidth(const theComboBox: TCombobox);
const
  HORIZONTAL_PADDING = 4;
var
  itemsFullWidth: integer;
  idx: integer;
  itemWidth: integer;
begin
  itemsFullWidth := 0;

  // get the max needed with of the items in dropdown state
  for idx := 0 to -1 + theComboBox.Items.Count do
  begin
    itemWidth := theComboBox.Canvas.TextWidth(theComboBox.Items[idx]);
    Inc(itemWidth, 18 * HORIZONTAL_PADDING);
    if (itemWidth > itemsFullWidth) then itemsFullWidth := itemWidth;
  end;

  // set the width of drop down if needed
  if (itemsFullWidth > theComboBox.Width) then
  begin
    //check if there would be a scroll bar
    if theComboBox.DropDownCount < theComboBox.Items.Count then
      itemsFullWidth := itemsFullWidth + GetSystemMetrics(SM_CXVSCROLL);

    SendMessage(theComboBox.Handle, CB_SETDROPPEDWIDTH, itemsFullWidth, 0);
  end;
end;

function GenerateCode4(tabel,field,nama:string):string;
var
   temp,a1 : string;
begin
   with dm.qucari do
   begin
      close;
      sql.clear;
      sql.add('select max(Case when isnumeric(substring('+field+',2,5))=1 then cast(substring('+field+',2,5)as int) else '''' end) from '+tabel);
      sql.Add('where substring('+field+',1, 1)=:0 ');
      prepared;
      parameters[0].Value:=nama;
      open;
      a1:=fields[0].asstring;
      if fields[0].asstring='' then
         temp := '00001'
      else
         temp := inttostr(strtoint(fields[0].asstring)+1);
   end;
   Result :=nama+ copy('00000',1,5-length(temp))+temp;

end;

procedure ViewOtorisasi(Var TView : TcxGridDBBandedTableView; Var mQuery: TADOQuery; OL: Integer);
var i : integer;
begin
  //mFieldName:=(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1));
  TView.Bands.Items[TView.Bands.Count-1].Visible := (OL<>0);
  if OL<>0 then
     TView.Bands.Items[0].FixedKind := fkLeft
  else
     TView.Bands.Items[0].FixedKind := fkNone;

  for i := 0 to TView.ColumnCount-1 do
  begin
    if TView.Columns[i].DataBinding.FieldName='IsOtorisasi1' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized 1';
      TView.Columns[i].Width := 70;
    end;
    if TView.Columns[i].DataBinding.FieldName='OtoUser1' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized User 1';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='TglOto1' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 1';
      TView.Columns[i].Width := 80;
    end;

    if TView.Columns[i].DataBinding.FieldName='IsOtorisasi2' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized 2';
      TView.Columns[i].Width := 70;
    end;
    if TView.Columns[i].DataBinding.FieldName='OtoUser2' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized User 2';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='TglOto2' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 2';
      TView.Columns[i].Width := 80;
    end;

    if TView.Columns[i].DataBinding.FieldName='IsOtorisasi3' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized 3';
      TView.Columns[i].Width := 70;
    end;
    if TView.Columns[i].DataBinding.FieldName='OtoUser3' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized User 3';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='TglOto3' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 3';
      TView.Columns[i].Width := 80;
    end;

    if TView.Columns[i].DataBinding.FieldName='IsOtorisasi4' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 4';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='OtoUser4' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized User 4';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='TglOto4' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 4';
      TView.Columns[i].Width := 80;

    end;

    if TView.Columns[i].DataBinding.FieldName='IsOtorisasi5' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 5';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='OtoUser5' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized User 5';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='TglOto5' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 5';
      TView.Columns[i].Width := 80;
    end;
  end;
end;

procedure ViewOtorisasiNonBandedTableView(Var TView : TcxGridDBTableView; Var mQuery: TADOQuery; OL: Integer);
var i : integer;
begin
  //mFieldName:=(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1));
  {TView.Items[TView.ItemCount-1].Visible := (OL<>0);
  if OL<>0 then
     TView.Items[0].FixedKind := fkLeft
  else
     TView.Items[0].FixedKind := fkNone;}


  for i := 0 to TView.ColumnCount-1 do
  begin
    if TView.Columns[i].DataBinding.FieldName='IsOtorisasi1' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized 1';
      TView.Columns[i].Width := 70;
    end;
    if TView.Columns[i].DataBinding.FieldName='OtoUser1' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized User 1';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='TglOto1' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi1').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 1';
      TView.Columns[i].Width := 80;
    end;

    if TView.Columns[i].DataBinding.FieldName='IsOtorisasi2' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized 2';
      TView.Columns[i].Width := 70;
    end;
    if TView.Columns[i].DataBinding.FieldName='OtoUser2' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized User 2';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='TglOto2' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi2').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 2';
      TView.Columns[i].Width := 80;
    end;

    if TView.Columns[i].DataBinding.FieldName='IsOtorisasi3' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized 3';
      TView.Columns[i].Width := 70;
    end;
    if TView.Columns[i].DataBinding.FieldName='OtoUser3' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized User 3';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='TglOto3' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi3').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 3';
      TView.Columns[i].Width := 80;
    end;

    if TView.Columns[i].DataBinding.FieldName='IsOtorisasi4' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 4';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='OtoUser4' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized User 4';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='TglOto4' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi4').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 4';
      TView.Columns[i].Width := 80;

    end;

    if TView.Columns[i].DataBinding.FieldName='IsOtorisasi5' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 5';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='OtoUser5' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized User 5';
      TView.Columns[i].Width := 80;
    end;
    if TView.Columns[i].DataBinding.FieldName='TglOto5' then
    begin
      TView.Columns[i].Visible := ((strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))>0) and
                                     (strtoint(RightStr(mQuery.FieldByName('IsOtorisasi5').FieldName,1))<=OL));
      TView.Columns[i].Caption :='Authorized Date 5';
      TView.Columns[i].Width := 80;
    end;
  end;
end;

Procedure KonfigNojurnal(Tipe,xNomor:String;Var xF1,xF2,xF3,xF4,NoUrut :
  String;Var yF1,yF2,yF3,yF4:Integer;xDate:TDateTime;XPPN:Integer);
begin
  with DM.QuNomor do
  begin
    Close;
    sql.Clear;
    sql.Add('Select * from dbnomor');
    open;
    xInisialTrans := Tipe;
    case FieldByname('Pemisah').AsInteger of
      0 : Separator := ':';
      1 : Separator := '-';
      2 : Separator := '/';
      3 : Separator := ' ';
    end;
    case FieldByname('Format1').AsInteger of
      0 : xF1 := FieldByname('ALIAS').AsString;
      1 : xF1 := xInisialTrans;
      2 : xF1 := FormatDateTime('MMYY',xDate);
      3 : xF1 := FormatDateTime('MMYYYY',xDate);
      4 : xF1 := xNomor;
      5 : xF1 := FormatDateTime('YYMM',xDate);
      6 : xF1 := FormatDateTime('YYYYMM',xDate);
    end;
    case FieldByname('Format2').AsInteger of
      0 : xF2 := FieldByname('ALIAS').AsString;
      1 : xF2 := xInisialTrans;
      2 : xF2 := FormatDateTime('MMYY',xDate);
      3 : xF2 := FormatDateTime('MMYYYY',xDate);
      4 : xF2 := xNomor;
      5 : xF2 := FormatDateTime('YYMM',xDate);
      6 : xF2 := FormatDateTime('YYYYMM',xDate);
    end;
    case FieldByname('Format3').AsInteger of
      //0 : xF3 := FieldByname('ALIAS').AsString;
      0 :Begin
          if XPPN=1 then
            xF3 := FieldByname('ALIAS').AsString
          Else if XPPN=0 then  xF3 := FieldByname('INICAB').AsString;
         End;
      1 : xF3 := xInisialTrans;
      2 : xF3 := FormatDateTime('MMYY',xDate);
      3 : xF3 := FormatDateTime('MMYYYY',xDate);
      4 : xF3 := xNomor;
      5 : xF3 := FormatDateTime('YYMM',xDate);
      6 : xF3 := FormatDateTime('YYYYMM',xDate);
    end;
    case FieldByname('Format4').AsInteger of
      0 : xF4 := FieldByname('ALIAS').AsString;
      1 : xF4 := xInisialTrans;
      2 : xF4 := FormatDateTime('MMYY',xDate);
      3 : xF4 := FormatDateTime('MMYYYY',xDate);
      4 : xF4 := xNomor;
      5 : xF4 := FormatDateTime('YYMM',xDate);
      6 : xF4 := FormatDateTime('YYYYMM',xDate);
    end;
    If FieldByname('Format1').AsInteger=4 then NoUrut := xNomor;
    If FieldByname('Format2').AsInteger=4 then NoUrut := xNomor;
    If FieldByname('Format3').AsInteger=4 then NoUrut := xNomor;
    If FieldByname('Format4').AsInteger=4 then NoUrut := xNomor;

    yF1 := FieldByname('Format1').AsInteger;
    yF2 := FieldByname('Format2').AsInteger;
    yF3 := FieldByname('Format3').AsInteger;
    yF4 := FieldByname('Format4').AsInteger;
    xReset := FieldByname('Reset').AsInteger;
  end;
end;


Procedure IsiComboBox(pComboBox: TComboBox; pItemsComboBox: Array of Variant);
var I: Word;
begin
   pComboBox.Items.Clear;
   For I := Low(pItemsComboBox) to High(pItemsComboBox) Do
   begin
       pComboBox.Items.Add(pItemsComboBox[I]);
   end;
   pComboBox.ItemIndex:=0;
end;


Function GetNomorJurnal(nTabel, xKodemenu,xIDUser,xKunci, xNobukti :String):String;
Var xTipeTrans, xNoUrut, NomorBukti :String;
    xTglJurnal : TDateTime;
    XPPN:Integer;
begin
  with DM.QuCari do
  begin
    close;
    sql.Clear;
    sql.add('Declare @IDUSER varchar(15), @Kodemenu varchar(15), @Nobukti varchar(30),');
    sql.add('        @isOTO1 bit, @isOTO2 bit,@isOTO3 bit,@isOTO4 bit,@isOTO5 bit, @OtoLevel int, @HasOto int, @MaxLevel int,');
    sql.add('        @CanOto Bit, @MaxLevelMenu int');
    sql.add('Select @IDUSER='+QuotedStr(xIDUser)+', @Kodemenu='+QuotedStr(xKodemenu)+', @Nobukti='+QuotedStr(xNobukti));
    sql.add('Select B.TipeTrans, B.Kodemenu ');
    sql.add('from DBMENU B ');
    sql.add('where B.Kodemenu=@Kodemenu');
    open;
    if not IsEmpty then
       xTipeTrans := FieldByname('TipeTrans').AsString
    else
       xTipeTrans := '';
  end;
  with DM.QuCari do
  begin
    close;
    sql.Clear;
    sql.Add('Select Tanggal, Nourut,Flagtipe from '+nTabel+' where nobukti='+QuotedStr(xNobukti));
    open;
    if not IsEmpty then
    begin
       xTgljurnal := DM.QuCari.Fieldbyname('Tanggal').AsDateTime;
       xNoUrut := DM.QuCari.Fieldbyname('NoUrut').AsString;
       Xppn:=  DM.QuCari.Fieldbyname('Flagtipe').AsInteger;
    end
    else
    begin
       xTgljurnal := null;
       xNoUrut := '';
    end;
  end;
  //KonfigNojurnal(xTipeTrans,xNoUrut,F1,F2,F3,F4,NomorBukti,xN1,xN2,xN3,xN4,xTgljurnal,XPPN);
  KonfigNojurnal(xTipeTrans,xNoUrut,F1,F2,F3,F4,NomorBukti,xN1,xN2,xN3,xN4,
    xTgljurnal,1);
  NomorBukti := myNobukti;
  with dm.QuCari do
  begin
    close;
    sql.Clear;
    sql.Add('update '+nTabel+' set Nojurnal=:0, TglJurnal=:1, ');
    sql.Add('        nourutJurnal=:2 where nobukti=:3 and Nojurnal=''''');
    Prepared;
    Parameters[0].Value := NomorBukti;
    Parameters[1].Value := xTgljurnal;
    Parameters[2].Value := xNoUrut;
    Parameters[3].Value := xNobukti;
    ExecSQL;
  end;
  Result :=NomorBukti;
end;

Procedure Otorisasi(nTabel, xkodemenu, xIDUSER,xKunci, xNobukti :String);
var mStatus, Mlevel, TotalOL : integer;
    nomor : String;
    isOto1, isOto2, isOto3, isOto4, isOto5 : Boolean;
    xTgljurnal : TDateTime;
begin
{  if (MyCariUserName(xIDUSER,xKunci,mStatus, Mlevel)=true) then
  begin }
    with DM.QuCari do
    begin
      close;
      sql.Clear;
      sql.Add('Select IsOtorisasi1, IsOtorisasi2, IsOtorisasi3, IsOtorisasi4, IsOtorisasi5, B.OL ');
      sql.add('from dbflmenu A');
      SQl.add('     left Outer join dbMenu B on B.Kodemenu=A.L1');
      sql.add('where A.userid='+QuotedStr(xIDUser)+' and A.L1='+QuotedStr(xKodemenu));
      open;
      isOto1 := DM.QuCari.FieldByName('IsOtorisasi1').AsBoolean;
      isOto2 := DM.QuCari.FieldByName('IsOtorisasi2').AsBoolean;
      isOto3 := DM.QuCari.FieldByName('IsOtorisasi3').AsBoolean;
      isOto4 := DM.QuCari.FieldByName('IsOtorisasi4').AsBoolean;
      isOto5 := DM.QuCari.FieldByName('IsOtorisasi5').AsBoolean;
    end;
    if isOto1 or isOto2 or isOto3 or isOto4 or isOto5 then
    begin
      if CekOtorisasi(nTabel,xkodemenu, xIDUSER, xKunci, xNobukti) then
      begin
        with DM.QuCari do
        begin
          Close;
          sql.Clear;
          Sql.Add('Declare @OtoLv1 bit ,@OtoLv2 bit, @OtoLv3 bit, @OtoLv4 bit,@OtoLv5 bit, @MaxLvl tinyint, @Tgloto datetime,');
          SQl.add('        @IDuser varchar(15), @Nobukti varchar(30)');
          Sql.add('Select @IDUSER='+QuotedStr(xIDUser)+', @nobukti='+QuotedStr(xNobukti));
          Sql.Add('Declare Mydata Cursor for');
          sql.Add('Select IsOtorisasi1, IsOtorisasi2, IsOtorisasi3, IsOtorisasi4, IsOtorisasi5, B.OL ');
          sql.add('from dbflmenu A');
          SQl.add('     left Outer join dbMenu B on B.Kodemenu=A.L1');
          sql.add('where A.userid=@IDuser and A.L1='+QuotedStr(xKodemenu));
          Sql.Add('open Mydata');
          Sql.Add('Fetch next from mydata into @OtoLv1, @OtoLv2 , @OtoLv3, ' +
            '@OtoLv4, @OtoLv5, @MaxLvl');
          SQl.Add('While @@fetch_status=0');
          SQl.Add('begin');
          Sql.Add('  Set @TglOto=Getdate()');
          sql.Add('  update '+nTabel+' Set ');
          if isOto1 then
          begin
            sql.Add('                        IsOtorisasi1=Case when @Otolv1=1 then @OtoLv1 else 0 end, ');
            SQl.add('                        OtoUser1=Case when @Otolv1=1 then @IdUser else '''' end, ');
            SQl.add('                        TglOto1=Case when @Otolv1=1 then @TglOto else null end, MaxOL=@MaxLvl ');

          end
          else if isOto2 then
          begin
          SQl.Add('                             IsOtorisasi2=Case when @Otolv2=1 then @OtoLv2 else 0 end, ');
          SQl.add('                             OtoUser2=Case when @Otolv2=1 then @IdUser else '''' end, ');
          SQl.add('                             TglOto2=Case when @Otolv2=1 then @TglOto else null end ');
          end
          else if isOto3 then
          begin
          SQl.Add('                        IsOtorisasi3=Case when @Otolv3=1 then @OtoLv3 else 0 end, ');
          SQl.add('                        OtoUser3=Case when @Otolv3=1 then @IdUser else '''' end, ');
          SQl.add('                        TglOto3=Case when @Otolv3=1 then @TglOto else null end ');
          end
          else if isOto4 then
          begin
          SQl.Add('                             IsOtorisasi4=Case when @Otolv4=1 then @OtoLv4 else 0 end, ');
          SQl.add('                             OtoUser4=Case when @Otolv4=1 then @IdUser else '''' end, ');
          SQl.add('                             TglOto4=Case when @Otolv4=1 then @TglOto else null end ');
          end
          else if isOto5 then
          begin
          SQl.Add('                        IsOtorisasi5=Case when @Otolv5=1 then @OtoLv5 else 0 end, ');
          SQl.add('                        OtoUser5=Case when @Otolv5=1 then @IdUser else '''' end, ');
          SQl.add('                        TglOto5=Case when @Otolv5=1 then @TglOto else null end ');
          end;
          SQl.add('  where Nobukti=@Nobukti and Case when @OtoLv1=1 then Isnull(Isotorisasi1,0) ');
          SQl.add('                                  when @OtoLv2=1 then Isnull(Isotorisasi2,0) ');
          SQl.add('                                  when @OtoLv3=1 then Isnull(Isotorisasi3,0) ');
          SQl.add('                                  when @OtoLv4=1 then Isnull(Isotorisasi4,0) ');
          SQl.add('                                  when @OtoLv5=1 then Isnull(Isotorisasi5,0) ');
          SQl.add('                             end=0');
          SQL.add('  Fetch next from mydata into @OtoLv1, @OtoLv2 , @OtoLv3, @OtoLv4, @OtoLv5, @MaxLvl');
          Sql.add('end');
          Sql.add('Close mydata');
          Sql.add('Deallocate mydata');
          ExecSQL;
        end;
        with DM.QuCari do
        begin
          close;
          sql.Clear;
          sql.Add('Select Case when IsOtorisasi1=1 then 1 else 0 end+');
          sql.Add('       Case when IsOtorisasi2=1 then 1 else 0 end+');
          sql.Add('       Case when IsOtorisasi3=1 then 1 else 0 end+');
          sql.Add('       Case when IsOtorisasi4=1 then 1 else 0 end+');
          sql.Add('       Case when IsOtorisasi5=1 then 1 else 0 end OL, A.NoBukti');
          sql.Add('from '+nTabel+' A');
          sql.Add('where A.nobukti='+QuotedStr(xNobukti));
          open;
          TotalOL := FieldByname('OL').AsInteger;
        end;
        with DM.QuCari do
        begin
          close;
          sql.Clear;
          sql.Add('Select  OL, A.kodemenu, A.TipeTrans,Cast(Cast(Cast(GetDate() as float) as int) as datetime) Tgljurnal');
          sql.Add('from DBMenu A');
          sql.Add('where A.Kodemenu='+QuotedStr(xkodemenu));
          open;
        end;
        if (TotalOL=DM.QuCari.FieldByName('OL').AsInteger) and (DM.QuCari.FieldByName('TipeTrans').AsString<>'') then
        Begin
          GetNomorJurnal(nTabel,xkodemenu,IDUser,xKunci,xNobukti);
        end;
      end;
    end
    else
    begin
      MessageDlg('Anda tidak berhak melakukan otorisasi data',mtWarning, [mbok],0);
    end;

    //Check_Nomor(StrToInt(PeriodBln),StrToInt(PeriodThn),Mode.Text,Nomor,NomorBukti,Tanggal.Date,'',False);
    //NoUrut.Text := NomorBukti;
    //NOBUKTI.Text := Nomor;
{  end
  else
  begin
    MessageDlg('User ID dan Password Salah'+#13+'Otorisasi Gagal',mtError,[mbok],0);
  end; }
end;

Procedure BatalOtorisasi(nTabel, xkodemenu, xIDUSER,xKunci, xNobukti :String);
var mStatus, Mlevel, TotalOL : integer;
    nomor : String;
    isOto1, isOto2, isOto3, isOto4, isOto5,isbatal : Boolean;
begin
{  if (MyCariUserName(xIDUSER,xKunci,mStatus, Mlevel)=true) then
  begin }
    with DM.QuCari do
    begin
      close;
      sql.Clear;
      sql.Add('Select IsOtorisasi1, IsOtorisasi2, IsOtorisasi3, IsOtorisasi4, IsOtorisasi5, B.OL,isbatal ');
      sql.add('from dbflmenu A');
      SQl.add('     left Outer join dbMenu B on B.Kodemenu=A.L1');
      sql.add('where A.userid='+QuotedStr(xIDUser)+' and A.L1='+QuotedStr(xKodemenu));
      open;
      isOto1 := DM.QuCari.FieldByName('IsOtorisasi1').AsBoolean;
      isOto2 := DM.QuCari.FieldByName('IsOtorisasi2').AsBoolean;
      isOto3 := DM.QuCari.FieldByName('IsOtorisasi3').AsBoolean;
      isOto4 := DM.QuCari.FieldByName('IsOtorisasi4').AsBoolean;
      isOto5 := DM.QuCari.FieldByName('IsOtorisasi5').AsBoolean;
      Isbatal:= DM.QuCari.FieldByName('IsBatal').AsBoolean;
    end;
    //if isOto1 or isOto2 or isOto3 or isOto4 or isOto5 then
    if isbatal then
    begin
      with DM.QuCari do
      begin
        Close;
        sql.Clear;
        Sql.Add('Declare @OtoLv1 bit ,@OtoLv2 bit, @OtoLv3 bit, @OtoLv4 bit,@OtoLv5 bit, @MaxLvl tinyint, @Tgloto datetime,');
        SQl.add('        @IDuser varchar(15), @Nobukti varchar(30)');
        Sql.add('Select @IDUSER='+QuotedStr(xIDUser)+', @nobukti='+QuotedStr(xNobukti));
        Sql.Add('Declare Mydata Cursor for');
        sql.Add('Select IsOtorisasi1, IsOtorisasi2, IsOtorisasi3, IsOtorisasi4, IsOtorisasi5, B.OL ');
        sql.add('from dbflmenu A');
        SQl.add('     left Outer join dbMenu B on B.Kodemenu=A.L1');
        sql.add('where A.userid=@IDuser and A.L1='+QuotedStr(xKodemenu));
        Sql.Add('open Mydata');
        Sql.Add('Fetch next from mydata into @OtoLv1, @OtoLv2 , @OtoLv3, @OtoLv4, @OtoLv5, @MaxLvl');
        SQl.Add('While @@fetch_status=0');
        SQl.Add('begin');
        Sql.Add('  Set @TglOto=Getdate()');
        sql.Add('  update '+nTabel+' Set ');
        //if isOto1 then
        begin
          sql.Add('                        IsOtorisasi1=0, ');
          SQl.add('                        OtoUser1='''', ');
          SQl.add('                        TglOto1=null,');

        end;
       // else if isOto2 then
        begin
        SQl.Add('                             IsOtorisasi2=0, ');
        SQl.add('                             OtoUser2='''', ');
        SQl.add('                             TglOto2=null, ');
        end;
        //else if isOto3 then
        begin
        SQl.Add('                        IsOtorisasi3=0, ');
        SQl.add('                        OtoUser3='''', ');
        SQl.add('                        TglOto3=null,');
        end ;
       // else if isOto4 then
        begin
        SQl.Add('                             IsOtorisasi4=0, ');
        SQl.add('                             OtoUser4='''', ');
        SQl.add('                             TglOto4=null, ');
        end  ;
        //else if isOto5 then
        begin
        SQl.Add('                        IsOtorisasi5=0, ');
        SQl.add('                        OtoUser5='''', ');
        SQl.add('                        TglOto5=null, MaxOL=-1 ');
        end;
        SQl.add('  where Nobukti=@Nobukti and Case when @OtoLv1=1 then Isotorisasi1 ');
        SQl.add('                                  when @OtoLv2=1 then Isotorisasi2 ');
        SQl.add('                                  when @OtoLv3=1 then Isotorisasi3 ');
        SQl.add('                                  when @OtoLv4=1 then Isotorisasi4 ');
        SQl.add('                                  when @OtoLv5=1 then Isotorisasi5 ');
        SQl.add('                             end=1');
        SQL.add('  Fetch next from mydata into @OtoLv1, @OtoLv2 , @OtoLv3, @OtoLv4, @OtoLv5, @MaxLvl');
        Sql.add('end');
        Sql.add('Close mydata');
        Sql.add('Deallocate mydata');
        ExecSQL;
      end;

      with dm.QuKOde do
      begin
        close;
        sql.Clear;
        sql.Add('update '+nTabel+' set Nojurnal='''', TglJurnal=null, nourutJurnal='''' where nobukti=:0 and Nojurnal<>'''' ');
        Prepared;
        {Parameters[0].Value := '';
        Parameters[1].Value := null;
        Parameters[2].Value := '';  }
        Parameters[0].Value := xNobukti;
        Try
          ExecSQL;
          Except

        End;
      end;

    end
    else
    begin
      MessageDlg('Anda tidak berhak melakukan pembatalan otorisasi data',mtWarning, [mbok],0);
    end;

    //Check_Nomor(StrToInt(PeriodBln),StrToInt(PeriodThn),Mode.Text,Nomor,NomorBukti,Tanggal.Date,'',False);
    //NoUrut.Text := NomorBukti;
    //NOBUKTI.Text := Nomor;
{  end
  else
  begin
    MessageDlg('User ID dan Password Salah'+#13+'Otorisasi Gagal',mtError,[mbok],0);
  end;}
end;

Function CekOtorisasi(nTabel, xKodemenu,xIDUser,xKunci, xNobukti :String):Boolean;
Var xS : String;
Begin
  Result := False;
  with DM.QuCari do
  begin
    close;
    sql.Clear;
    sql.add('Declare @IDUSER varchar(15), @Kodemenu varchar(15), @Nobukti varchar(30),');
    sql.add('        @isOTO1 bit, @isOTO2 bit,@isOTO3 bit,@isOTO4 bit,@isOTO5 bit, @OtoLevel int, @HasOto int, @MaxLevel int,');
    sql.add('        @CanOto Bit, @MaxLevelMenu int');
    sql.add('Select @IDUSER='+QuotedStr(xIDUser)+', @Kodemenu='+QuotedStr(xKodemenu)+', @Nobukti='+QuotedStr(xNobukti));
    sql.add('Select @isOTO1=IsOtorisasi1,@isOTO2=IsOtorisasi2,@isOTO3=IsOtorisasi3,@isOTO4=IsOtorisasi4,@isOTO5=IsOtorisasi5,');
    sql.add('       @OtoLevel=Case when A.IsOtorisasi1=1 then 1 else 0 end+');
    sql.add('                 Case when A.IsOtorisasi2=1 then 2 else 0 end+');
    sql.add('                 Case when A.IsOtorisasi3=1 then 3 else 0 end+');
    sql.add('                 Case when A.IsOtorisasi4=1 then 4 else 0 end+');
    sql.add('                 Case when A.IsOtorisasi5=1 then 5 else 0 end,');
    sql.add('       @MaxLevelMenu=B.OL');
    sql.add('from DBFLMENU A');
    sql.add('Left Outer join DBMENU B on B.KODEMENU=A.L1');
    sql.add('where Userid=@IDUser and L1=@Kodemenu');
    //sql.add('Select @OtoLevel OtoLevel');
    sql.add('Select @HASOto=Case when A.IsOtorisasi1=1 then 1 else 0 end+');
    sql.add('               Case when A.IsOtorisasi2=1 then 1 else 0 end+');
    sql.add('               Case when A.IsOtorisasi3=1 then 1 else 0 end+');
    sql.add('               Case when A.IsOtorisasi4=1 then 1 else 0 end+');
    sql.add('               Case when A.IsOtorisasi5=1 then 1 else 0 end,');
    sql.add('       @MaxLevel=MAXOl');
    sql.add('from '+nTabel+' A');
    sql.add('where NOBUKTI=@Nobukti');
    //sql.add('Select @HasOto HasOTO, @MaxLevel MaxLevel');
    sql.add('Set @CanOto=Case when (@HasOto=0) and (@OtoLevel-1=0) and (@OtoLevel<=@MaxLevelMenu) then 1');
    sql.add('                 when (@HasOto>0) and (@HasOto=@OtoLevel-1) and (@OtoLevel<=@MaxLevel) then 1');
    sql.add('                 else 0');
    sql.add('            end');
    sql.add('Select @CanOto CanOto, @HasOto HasOTO, @MaxLevel MaxLevel, @OtoLevel OtoLevel');

    open;
    if IsEmpty then
       Result := False
    else
    begin
      Result := FieldByName('CanOto').AsBoolean;
      if Not FieldByName('CanOto').AsBoolean then
      begin
        if (FieldByName('HasOto').AsInteger=FieldByName('MaxLevel').AsInteger) then
          xS:=Format('Transaksi No : %s Otorisasi telah lengkap !', [xNobukti])
        else
        if (FieldByName('HasOto').AsInteger=FieldByName('OtoLevel').AsInteger) then
           xS:=Format('Otorisasi level %s telah terotorisasi !', [intToStr(FieldByName('HasOto').AsInteger)])
        else
        if Not (FieldByName('HasOto').AsInteger=FieldByName('OtoLevel').AsInteger-1) then
           xS:=Format('Otorisasi level %s belum terotorisasi !', [intToStr(FieldByName('OtoLevel').AsInteger-1)]);
        if xS<>'' then
           Application.MessageBox(PChar(xS),'Hapus Data',MB_OK or MB_ICONSTOP + MB_DEFBUTTON1);
      end;
    end;
  end;
end;


Function MyRomawi(x:integer):String;
begin
  case x of
    1 : Result := 'I';
    2 : Result := 'II';
    3 : Result := 'III';
    4 : Result := 'IV';
    5 : Result := 'V';
    6 : Result := 'VI';
    7 : Result := 'VII';
    8 : Result := 'VIII';
    9 : Result := 'IX';
    10: Result := 'X';
    11: Result := 'XI';
    12: Result := 'XII';
  end;
end;

function NoToStr(No,Digit:integer):String;
var i,j:integer;
    Str:String;
    Zero,Finish:String;
begin
  i:=length(InttoStr(No));
  Zero:='0';
  Str:='';
  for j:= 1 to digit-i do
  begin
    Str:=Str+Zero;
  end;
  Finish:=Str+InttoStr(No);
  NoToStr:=Finish
end;

procedure DisableEnableTrigger(Enabled:Boolean;xTable:String);
var     Trigger : String;
begin
  with dm.QuNomor do
  begin
    close;
    sql.Clear;
    sql.Add('select a.Name Nama');
    SQl.Add('from sys.triggers a');
    sql.add('     left outer join sys.trigger_events b on b.object_id=a.object_id');
    sql.add('where a.parent_id in (Select object_id from sys.tables where name='+QuotedStr(xTable)+')');
    sql.add('and b.type_desc=''UPDATE''');
    open;
    Trigger:=Fieldbyname('Nama').AsString;
    if (Trigger<>'') and (Enabled=False) then
    begin
      close;
      sql.Clear;
      sql.Add('alter Table dbo.'+xTable+' DISABLE Trigger '+Trigger);
      ExecSQL;
      close;
    end
    else if (Trigger<>'') and (Enabled) then
    if Trigger<>'' then
    begin
      close;
      sql.Clear;
      sql.Add('alter Table dbo.'+xTable+' Enable Trigger '+Trigger);
      ExecSQL;
    end;
  end;

end;

procedure UpdateUrutTransaksi(xTable,xNobukti:String);
Var x : Integer;
    Trigger : String;
begin
  with dm.QuNomor do
  begin
    close;
    sql.Clear;
    sql.Add('select a.Name Nama');
    SQl.Add('from sys.triggers a');
    sql.add('     left outer join sys.trigger_events b on b.object_id=a.object_id');
    sql.add('where a.parent_id in (Select object_id from sys.tables where name='+QuotedStr(xTable)+')');
    sql.add('and b.type_desc=''UPDATE''');
    open;
    Trigger:=Fieldbyname('Nama').AsString;
    if Trigger<>'' then
    begin
      close;
      sql.Clear;
      sql.Add('alter Table dbo.'+xTable+' DISABLE Trigger '+Trigger);
      ExecSQL;
      close;
    end;
    sql.Clear;
    sql.Add('Select nobukti,urut from '+xTable+' where nobukti='+QuotedStr(xnobukti)+' order by urut');
    open;
  end;
  x:=1;
  DM.Qunomor.DisableControls;
  Dm.Qunomor.First;
  while (not dm.Qunomor.eof) and (not dm.Qunomor.IsEmpty) do
  begin
    with dm.QuKOde do
    begin
      close;
      sql.Clear;
      sql.Add('update '+xTable+' set UrutTrans=:0 where nobukti=:1 and urut=:2');
      Prepared;
      Parameters[0].Value:=x;
      Parameters[1].Value:=dm.Qunomor.FieldByname('Nobukti').AsString;
      Parameters[2].Value:=dm.Qunomor.FieldByname('Urut').AsInteger;
      ExecSQL;
    end;
    x:=x+1;
    dm.Qunomor.next;
  end;
  DM.Qunomor.EnableControls;
  if Trigger<>'' then
  begin
    with dm.Qunomor do
    begin
      close;
      sql.Clear;
      sql.Add('alter Table dbo.'+xTable+' Enable Trigger '+Trigger);
      ExecSQL;
    end;
  end;
end;

procedure IsiDataAktiva(xGroupAktiva:sTring; var xTipe,xAkumulasi,xBiaya1,xBiaya2 : String; Var xPersen,xPbiaya1,xPBiaya2 : Real);
begin
  with dm.QuCari do
  begin
    close;
    sql.Clear;
    sql.Add('Select *,Case when A.Tipe=''L'' then ''[L]urus'' ');
    sql.add('              when A.Tipe=''M'' then ''[M]enurun'' ');
    Sql.add('              when A.Tipe=''P'' then ''[P]ajak'' ');
    sql.add('              else ''''end Metode from dbposthutpiut A where perkiraan=:0 and Kode=''AKV'' ');
    Prepared;
    Parameters[0].Value := xGroupAktiva;
    open;
    xTipe := fieldbyname('Metode').Value;
    xAkumulasi := fieldbyname('Akumulasi').Value;
    xBiaya1 := fieldbyname('Biaya1').Value;
    xBiaya2 := fieldbyname('Biaya2').Value;
    xPersen := fieldbyname('Persen').Value;
    xPbiaya1:= fieldbyname('PersenBiaya1').Value;
    xPBiaya2:= fieldbyname('PersenBiaya2').Value;
  end;
end;

Procedure CariSatuan(Kode,mNoPo:String;Satuan:Byte;Var Harga,xIsibrg:Real;var xSat,xSatuan:String;var xStatus:Boolean);
//var No :String;
begin
  with dm.QuCari do
  begin
    close;
    sql.Clear;
    SQL.Add('Select B.kodebrg,b.Sat1,isi1,b.Sat2,isi2');
    SQL.Add('From dbBarang B');
    SQL.Add('Where (B.KodeBrg = :0)');
    SQL.Add('Order by B.KodeBrg');
    Prepared;
    Parameters[0].Value:=kode;
    Open;
  end;
   if not Dm.QuCari.IsEmpty then
   begin
     xSatuan := Dm.QuCari.FieldbyName('Sat1').AsString;
     Case Satuan of
       1 :begin
            xSat:=Dm.QuCari.FieldbyName('Sat1').AsString;
            xISiBrg:=Dm.QuCari.FieldbyName('Isi2').AsFloat;
            xStatus := True;
          end;
       2 :begin
            xSat:=Dm.QuCari.FieldbyName('Sat2').AsString;
            xISiBrg:=Dm.QuCari.FieldbyName('Isi2').AsFloat;
            xStatus := True;
          end;
       else  xStatus := False;
     end;

   end
   else xStatus := False;
end;

procedure MsgDataTidakBolehKosong(pData: String);
begin
  MessageDlg(pData+' tidak boleh kosong, harus diisi !',mtError,[mbOK],0);
end;

procedure MsgKoreksiDataKosong;
begin
  MessageDlg('Data kosong, tidak dapat dikoreksi !',mtError,[mbOK],0);
end;

procedure MsgTglKirimDataKosong;
begin
  MessageDlg('Data kosong, tidak dapat isi Tanggal Kirim !',mtError,[mbOK],0);
end;

procedure MsgHapusDataKosong;
begin
  MessageDlg('Data kosong, tidak dapat dihapus !',mtError,[mbOK],0);
end;

procedure MsgTidakBerhakTambahData;
begin
  MessageDlg('Anda tidak berhak menambah data !',mtInformation,[mbOK],0);
end;

procedure MsgTidakBerhakKoreksiData;
begin
  MessageDlg('Anda tidak berhak mengkoreksi data !',mtInformation,[mbOK],0);
end;

procedure MsgTidakBerhakHapusData;
begin
  MessageDlg('Anda tidak berhak menghapus data !',mtInformation,[mbOK],0);
end;

procedure MsgTidakBerhakExportData;
begin
  MessageDlg('Anda tidak berhak mengeksport data ke Excel !',mtInformation,[mbOK],0);
end;

procedure MsgTidakBerhakCetakData;
begin
  MessageDlg('Anda tidak berhak mencetak data !',mtInformation,[mbOK],0);
end;

procedure MsgPeriodeSudahDikunci;
begin
  MessageDlg('Periode sudah dikunci !',mtInformation,[mbOK],0);
end;

procedure MsgTanggalTidakSesuaiPeriode;
begin
  MessageDlg('Tanggal dan Periode tidak sesuai !',mtError,[mbOK],0);
end;

procedure MsgProsesGagal(pChoice: String);
begin
  MessageDlg('Proses Gagal !',mtError,[mbOK],0);
end;

procedure MsgDataSudahADA(pData: String);
begin
  MessageDlg('Transksi Nomor : '+pData+' Sudah Ada  !',mtError,[mbOK],0);
end;

procedure MsgNeedOtorisasi;
Begin
  MessageDlg('Transaksi telah di Otorisasi, tidak dapat dikoreksi !!!',mtWarning, [mbOK],0)
end;

procedure MsgCetakOtorisasi;
Begin
  MessageDlg('SO Belum Di Otorisasi !!!',mtWarning, [mbOK],0)
end;

procedure MsgBlmOtorisasi;
Begin
  MessageDlg('Transaksi Belum di Otorisasi !!!',mtWarning, [mbOK],0)
end;


procedure MSGStatusCetak;
begin
  MessageDlg('Data Sudah dicetak, Otorisasi tidak bisa dibatalkan !',mtInformation,[mbOK],0);
end;
function MsgNomorBuktiSudahAda(pData, pNoBuktiLama, pNoBuktiBaru: String): Boolean;
begin
  if MessageDlg('Nomor Bukti '+pNoBuktiLama+' sudah ada'+chr(13)+
    'Simpan data dengan nomor baru '+pNoBuktiBaru+' ?',
    mtConfirmation,[mbYes, mbNo],0)=mrYes
  then Result:=True
  else Result:=False;
end;

function UrutAktiva2(No,xDevisi:String;Digit:integer):String;
var i,j:integer;
    Zero,Finish,Str:String;
    NoAkhir,S:String;
    mKode,mNomor,awal,Jml:integer;
begin
  with DM.QuCari do
  begin
     Close;
     SQL.Clear;
     SQL.Add('select * from dbaktiva where devisi=:0 and Nomuka+''.''+NoBelakang=:1 and Kelompok=1 order by perkiraan');
     Parameters[0].Value:=xDevisi;
     Parameters[1].Value:=No;
     Open;
  end;
  if DM.QuCari.RecordCount > 0 then
  begin
     DM.QuCari.Last;
     noAkhir:=DM.QuCari.fieldbyname('Perkiraan').AsString;
     Awal:=length(No)+1;
     Jml:=(length(noAkhir)-length(No));
     S:=copy(noAkhir,Awal+1,Jml);
     s := DM.QuCari.fieldbyname('NoBelakang2').AsString;
     val(S,mNomor,mkode);
     if mkode=0 then
     begin
        mNomor:=mNomor+1;
        i:=length(IntToStr(mNomor));
        Zero:='0';
        Str:='';
        for j:= 1 to digit do
        begin
           Str:=Str+Zero;
        end;
        Finish:=Copy(Str,1,Digit-i);
        Result:=Finish+IntToStr(mNomor);
     end
     else
        Result:='';
  end
  else
  begin
     Result:='00001';
  end;
end;

function UrutAktiva(No,xDevisi:String;Digit:integer):String;
var i,j:integer;
    Zero,Finish,Str:String;
    NoAkhir,S:String;
    mKode,mNomor,awal,Jml:integer;
begin
  with DM.QuCari do
  begin
     Close;
     SQL.Clear;
     SQL.Add('select * from dbaktiva where devisi=:0 and Nomuka=:1 order by perkiraan');
     Parameters[0].Value:=xDevisi;
     Parameters[1].Value:=No;
     Open;
  end;
  if DM.QuCari.RecordCount > 0 then
  begin
     DM.QuCari.Last;
     noAkhir:=DM.QuCari.fieldbyname('Perkiraan').AsString;
     Awal:=length(No)+1;
     Jml:=(length(noAkhir)-length(No));
     S:=copy(noAkhir,Awal,Jml);
     s := DM.QuCari.fieldbyname('NoBelakang').AsString;
     val(S,mNomor,mkode);
     if mkode=0 then
     begin
        mNomor:=mNomor+1;
        i:=length(IntToStr(mNomor));
        Zero:='0';
        Str:='';
        for j:= 1 to digit do
        begin
           Str:=Str+Zero;
        end;
        Finish:=Copy(Str,1,Digit-i);
        Result:=Finish+IntToStr(mNomor);
     end
     else
        Result:='';
  end
  else
  begin
     UrutAktiva:='00001';
  end;
end;

procedure TampilIsiData(xForm:TForm;var xEdit:TEdit;var xlabel:TLabel;xkodebrows:Integer;xkode,xNama:String);
var i : integer;
begin
  KodeBrows:=xkodebrows;
  Application.CreateForm(TFrBrows, FrBrows);
  FrBrows.IsiData:=xedit.Text;
  frbrows.ShowModal;
  if FrBrows.ModalResult=mrOk then
  begin
    if not FrBrows.QuBrows.IsEmpty then
    begin
      xedit.Text:=FrBrows.QuBrows.fieldbyname(xKode).AsString;
      xLabel.Caption:='[ '+FrBrows.QuBrows.fieldbyname(xNama).AsString+' ]';
      if xkodebrows=110021 Then
      Alamatgdg:=FrBrows.QuBrows.fieldbyname('Alamat').AsString;
      SetLength(xValue,FrBrows.QuBrows.FieldCount);
      for i := Low(xValue) to High(xValue) do
      begin
        xValue[i] := FrBrows.QuBrows.Fields[i].Value;
      end;
    end
    else
      xForm.ActiveControl := xedit;
  end
  else
    xForm.ActiveControl := xedit;
end;

function GetIPFromHost(var HostName, IPaddr, WSAErr: string): Boolean;
type
  Name = array[0..100] of Char;
  PName = ^Name;
var
  HEnt: pHostEnt;
  HName: PName;
  WSAData: TWSAData;
  i: Integer;
begin
  Result := False;
  if WSAStartup($0101, WSAData) <> 0 then begin
    WSAErr := 'Winsock is not responding."';
    Exit;
  end;
  IPaddr := '';
  New(HName);
  if GetHostName(HName^, SizeOf(Name)) = 0 then
  begin
    HostName := StrPas(HName^);
    HEnt := GetHostByName(HName^);
    for i := 0 to HEnt^.h_length - 1 do
     IPaddr :=
      Concat(IPaddr,
      IntToStr(Ord(HEnt^.h_addr_list^[i])) + '.');
    SetLength(IPaddr, Length(IPaddr) - 1);
    Result := True;
  end
  else begin
   case WSAGetLastError of
    WSANOTINITIALISED:WSAErr:='WSANotInitialised';
    WSAENETDOWN      :WSAErr:='WSAENetDown';
    WSAEINPROGRESS   :WSAErr:='WSAEInProgress';
   end;
  end;
  Dispose(HName);
  WSACleanup;
end;

Function BukaNoLot(pKodeGrp: string; var pADOQuery: TADOQuery): Boolean;
begin
  pADOQuery.Close;
  pADOQuery.SQL.Clear;
  pADOQuery.SQL.Add('select * from dbGroup');
  pADOQuery.SQL.Add('where KodeGrp='+QuotedStr(pKodeGrp));
  pADOQuery.SQL.Add('order by Kodegrp');
  pADOQuery.Open;
  Result := pADOQuery.FieldByName('IsiNoLot').AsBoolean;
end;

procedure BukaSatuanBrg(pKodeBrg: string; var pADOQuery: TADOQuery);
begin
  pADOQuery.Close;
  pADOQuery.SQL.Clear;
  pADOQuery.SQL.Add('select KodeBrg, NamaBrg,0 NoSat,Sat1 Satuan,Isi2 Isi,Sat2 SatuanRoll from vwSatuanBrg');
  pADOQuery.SQL.Add('where KodeBrg='+QuotedStr(pKodeBrg));
  pADOQuery.SQL.Add('order by NoSat');
  pADOQuery.Open;
end;

procedure IsiQnt1FromQnt2(pKodeBrg: String; pQnt2: TPBNumEdit; var pQnt1: TPBNumEdit);
begin

end;


procedure TampilLabelDiskon(pTipeDisc: Integer; var pLblDiscP, pLblDiscRp: TLabel);
begin

end;

procedure UpdateValas(pValas: String; pNilaiKurs: Real);
begin
      with DM.QuCari2 do
      begin
         Close;
         Sql.Clear;
         SQL.Add('Update dbValas Set Kurs=:0');
         SQL.Add('Where KOdeVls='+QuotedStr(pValas));
         Prepared;
         Parameters[0].Value:=pNilaiKurs;
         ExecSQL;
      end;
end;

Function CariKoma(Nilai : string) : Integer;
begin
  Result := Pos(',', Nilai)
end;


procedure MsgTglTidakSesuaiPeriode;
begin
  try
    MessageDlg('Tanggal tidak sesuai dengan priode transaksi'+chr(13)+
    'Periode transaksi bulan '+FormatDateTime('MMMM yyyy',EncodeDate(StrToInt(PeriodThn),StrToInt(PeriodBln),1)),
    mtError, [mbOK], 0);
  except
    MessageDlg('Tanggal tidak sesuai dengan priode transaksi', mtError, [mbOK], 0);
  end;
end;

function  CekPemakaiGdg(pPemakai, pGudang: String): Boolean;
var xResult: Boolean;
begin
  xResult:=True;
  if gPemakaiAllGdg=False then
  begin
    with DM.QueryBrow do
    begin
      Close;
      SQL.Clear;
      SQL.Add('select * from dbPemakaiGdg where UserID='+QuotedStr(pPemakai)+
                   ' and KodeGdg='+QuotedStr(pGudang));
      Open;
    end;
    if DM.QueryBrow.IsEmpty then
      xResult:=False;
  end;
  Result:=xResult;
  if xResult=False then
    MessageDlg('Anda tidak diberi hak akses ke Gudang '+pGudang,mtInformation,[mbOK],0);
end;

Procedure Konfig(Tipe,xNomor:String;Var xF1,xF2,xF3,xF4,NoUrut : String;Var yF1,yF2,yF3,yF4:Integer;xDate:TDateTime);OverLoad;
var xAlias: String;
begin
  if DM.TaNomor.Active=False then
    DM.TaNomor.Open;
  With DM.TaNomor do
  begin
    If not IsEmpty then
    begin
      //if gJenisPPN=0 then xAlias:=FieldByName('Alias').AsString
      //else if gJenisPPN=1 then xAlias:=FieldByName('Alias2').AsString;
      case FieldByname('Pemisah').AsInteger of
        0 : Separator := ':';
        1 : Separator := '-';
        2 : Separator := '/';
        3 : Separator := ' ';
      end;
      if (Tipe='INVX') then
      begin
        if xIsLokal then
        begin
           xF1 :=xNomor;
           xF2 :='SZ-XP';
           xF3 :=MyRomawi(StrToInt(PeriodBln));
           xF4 :=PeriodThn;
        end
        else
        begin
           xF1 :=xNomor;
           xF2 :='';
           xF3 :=MyRomawi(StrToInt(PeriodBln));
           xF4 :=PeriodThn;
        end;
      end else
      begin
        xAlias:=FieldByName('Alias').AsString;
        xInisialTrans := FieldByname(TIPE).AsString;

        case FieldByname('Format1').AsInteger of
          0 :xF1 := FieldByname('ALIAS').AsString;
          1 : xF1 := xInisialTrans;
          2 : xF1 := FormatDateTime('MMYY',xDate);
          3 : xF1 := FormatDateTime('MMYYYY',xDate);
          4 : xF1 := xNomor;
          5 : xF1 := FormatDateTime('YYMM',xDate);
          6 : xF1 := FormatDateTime('YYYYMM',xDate);
        end;
        case FieldByname('Format2').AsInteger of
          0 : xF2 := FieldByname('ALIAS').AsString;
          1 : xF2 := xInisialTrans;
          2 : xF2 := FormatDateTime('MMYY',xDate);
          3 : xF2 := FormatDateTime('MMYYYY',xDate);
          4 : xF2 := xNomor;
          5 : xF2 := FormatDateTime('YYMM',xDate);
          6 : xF2 := FormatDateTime('YYYYMM',xDate);
        end;
        case FieldByname('Format3').AsInteger of
          0 : xF3 := FieldByname('ALIAS').AsString;
          1 : xF3 := xInisialTrans;
          2 : xF3 := FormatDateTime('MMYY',xDate);
          3 : xF3 := FormatDateTime('MMYYYY',xDate);
          4 : xF3 := xNomor;
          5 : xF3 := FormatDateTime('YYMM',xDate);
          6 : xF3 := FormatDateTime('YYYYMM',xDate);
        end;
        case FieldByname('Format4').AsInteger of
          0 : xF4 := FieldByname('ALIAS').AsString;
          1 : xF4 := xInisialTrans;
          2 : xF4 := FormatDateTime('MMYY',xDate);
          3 : xF4 := FormatDateTime('MMYYYY',xDate);
          4 : xF4 := xNomor;
          5 : xF4 := FormatDateTime('YYMM',xDate);
          6 : xF4 := FormatDateTime('YYYYMM',xDate);
        end;

      end;
      If FieldByname('Format1').AsInteger=4 then NoUrut := xNomor;
      If FieldByname('Format2').AsInteger=4 then NoUrut := xNomor;
      If FieldByname('Format3').AsInteger=4 then NoUrut := xNomor;
      If FieldByname('Format4').AsInteger=4 then NoUrut := xNomor;

      yF1 := FieldByname('Format1').AsInteger;
      yF2 := FieldByname('Format2').AsInteger;
      yF3 := FieldByname('Format3').AsInteger;
      yF4 := FieldByname('Format4').AsInteger;
      xReset := FieldByname('Reset').AsInteger;
    end;
  end;
end;

Procedure Konfig(Tipe,xNomor:String;Var xF1,xF2,xF3,xF4,NoUrut : String;Var yF1,yF2,yF3,yF4:Integer;xDate:TDateTime;xIsPpn:Boolean); Overload;
begin
  if DM.TaNomor.Active=False then
     DM.TaNomor.Open;
  With DM.TaNomor do
  begin
    If not IsEmpty then
    begin
      xInisialTrans := FieldByname(TIPE).AsString;
      if Tipe='SPB' then
      begin
       {
        if DM.TaNomor.FieldByName('InsSPB').AsString='GB' then
          xInisialTrans:=xInisialTrans+gINSGdgSPB+DM.TaNomor.FieldByName('PemisahSPB').AsString+gINSBrgSPB
        else if DM.TaNomor.FieldByName('InsSPB').AsString='BG' then
          xInisialTrans:=xInisialTrans+gINSBrgSPB+DM.TaNomor.FieldByName('PemisahSPB').AsString+gINSGdgSPB
        else xInisialTrans:=xInisialTrans+gINSGdgSPB+DM.TaNomor.FieldByName('PemisahSPB').AsString+gINSBrgSPB;
        }
      end else
      if Tipe='SO' then
      begin
        {if DM.TaNomor.FieldByName('InsSO').AsString='GB' then
          xInisialTrans:=xInisialTrans+gINSGdgSO+DM.TaNomor.FieldByName('PemisahSO').AsString+gINSBrgSO
        else if DM.TaNomor.FieldByName('InsSO').AsString='BG' then
          xInisialTrans:=xInisialTrans+gINSBrgSO+DM.TaNomor.FieldByName('PemisahSO').AsString+gINSGdgSO
        else
         xInisialTrans:=xInisialTrans+gINSGdgSO+DM.TaNomor.FieldByName('PemisahSO').AsString+gINSBrgSO; }
      end else
      if Tipe='SJL' then
      begin
        if DM.TaNomor.FieldByName('InsSJ').AsString='GB' then
          xInisialTrans:=xInisialTrans+gINSGdgSJ+DM.TaNomor.FieldByName('PemisahSJ').AsString+gINSBrgSJ
        else if DM.TaNomor.FieldByName('InsSJ').AsString='BG' then
          xInisialTrans:=xInisialTrans+gINSBrgSJ+DM.TaNomor.FieldByName('PemisahSJ').AsString+gINSGdgSJ
        else xInisialTrans:=xInisialTrans+gINSGdgSJ+DM.TaNomor.FieldByName('PemisahSJ').AsString+gINSBrgSJ;
      end else
      if Tipe='PNJ' then
      begin
        if DM.TaNomor.FieldByName('InsJual').AsString='GB' then
          xInisialTrans:=xInisialTrans+gINSGdgJual+DM.TaNomor.FieldByName('PemisahJual').AsString+gINSBrgJual
        else if DM.TaNomor.FieldByName('InsJual').AsString='BG' then
          xInisialTrans:=xInisialTrans+gINSBrgJual+DM.TaNomor.FieldByName('PemisahJual').AsString+gINSGdgJual
        else xInisialTrans:=xInisialTrans+gINSGdgJual+DM.TaNomor.FieldByName('PemisahJual').AsString+gINSBrgJual;
      end else
      if Tipe='RPJ' then
      begin
        if DM.TaNomor.FieldByName('InsRJual').AsString='GB' then
          xInisialTrans:=xInisialTrans+gINSGdgRJual+DM.TaNomor.FieldByName('PemisahRJual').AsString+gINSBrgRJual
        else if DM.TaNomor.FieldByName('InsRJual').AsString='BG' then
          xInisialTrans:=xInisialTrans+gINSBrgRJual+DM.TaNomor.FieldByName('PemisahRJual').AsString+gINSGdgRJual
        else xInisialTrans:=xInisialTrans+gINSGdgRJual+DM.TaNomor.FieldByName('PemisahRJual').AsString+gINSBrgRJual;
      end;


      case FieldByname('Pemisah').AsInteger of
        0 : Separator := ':';
        1 : Separator := '-';
        2 : Separator := '/';
        3 : Separator := ' ';
      end;
      case FieldByname('Format1').AsInteger of
        0 : begin
              if xIsPpn then
                 xF1 := FieldByname('ALIAS').AsString
              else
                 xF1 := FieldByname('IniCab').AsString;
            end;
        1 : xF1 := xInisialTrans;
        2 : xF1 := FormatDateTime('MMYY',xDate);
        3 : xF1 := FormatDateTime('MMYYYY',xDate);
        4 : xF1 := xNomor;
        5 : xF1 := FormatDateTime('YYMM',xDate);
        6 : xF1 := FormatDateTime('YYYYMM',xDate);
      end;
      case FieldByname('Format2').AsInteger of
        0 : begin
              if xIsPpn then
                 xF2 := FieldByname('ALIAS').AsString
              else
                 xF2 := FieldByname('IniCab').AsString;
            end;
        1 : xF2 := xInisialTrans;
        2 : xF2 := FormatDateTime('MMYY',xDate);
        3 : xF2 := FormatDateTime('MMYYYY',xDate);
        4 : xF2 := xNomor;
        5 : xF2 := FormatDateTime('YYMM',xDate);
        6 : xF2 := FormatDateTime('YYYYMM',xDate);
      end;
      case FieldByname('Format3').AsInteger of
        0 : begin
              if xIsPpn then
                 xF3 :=   FieldByname('IniCab').AsString
              else
                 xF3 := FieldByname('ALIAS').AsString;
            end;
        1 : xF3 := xInisialTrans;
        2 : xF3 := FormatDateTime('MMYY',xDate);
        3 : xF3 := FormatDateTime('MMYYYY',xDate);
        4 : xF3 := xNomor;
        5 : xF3 := FormatDateTime('YYMM',xDate);
        6 : xF3 := FormatDateTime('YYYYMM',xDate);
      end;
      case FieldByname('Format4').AsInteger of
        0 : begin
              if xIsPpn then
                 xF4 := FieldByname('ALIAS').AsString
              else
                 xF4 := FieldByname('IniCab').AsString;
            end;
        1 : xF4 := xInisialTrans;
        2 : xF4 := FormatDateTime('MMYY',xDate);
        3 : xF4 := FormatDateTime('MMYYYY',xDate);
        4 : xF4 := xNomor;
        5 : xF4 := FormatDateTime('YYMM',xDate);
        6 : xF4 := FormatDateTime('YYYYMM',xDate);
      end;
      If FieldByname('Format1').AsInteger=4 then NoUrut := xNomor;
      If FieldByname('Format2').AsInteger=4 then NoUrut := xNomor;
      If FieldByname('Format3').AsInteger=4 then NoUrut := xNomor;
      If FieldByname('Format4').AsInteger=4 then NoUrut := xNomor;

      yF1 := FieldByname('Format1').AsInteger;
      yF2 := FieldByname('Format2').AsInteger;
      yF3 := FieldByname('Format3').AsInteger;
      yF4 := FieldByname('Format4').AsInteger;
      xReset := FieldByname('Reset').AsInteger;
    end;
  end;
end;

function NewNo(No:String;Digit:integer):String;
var i,j:integer;
    Str:String;
    Zero,Finish:String;
begin
    i:=length(No);
    Zero:='0';
    Str:='';
    for j:= 1 to digit do
    begin
       Str:=Str+Zero;
    end;
    Finish:=Copy(Str,1,Digit-i);
    NewNo:=Finish+No;
end;

function  MyNoBukti: String;
var xxNomor: String;
begin
  xxNomor:='';
  if Trim(F1)<>'' then
    xxNomor:=xxNomor+Trim(F1)+Separator;
  if Trim(F2)<>'' then
    xxNomor:=xxNomor+Trim(F2)+Separator;
  if Trim(F3)<>'' then
  begin
    if Trim(F4)<>'' then
      xxNomor:=xxNomor+Trim(F3)+Separator+Trim(F4)
    else xxNomor:=xxNomor+Trim(F3);
  end;
  Result:=xxNomor;
end;

function IsiSatuanBrg(pKodeBrg: string; var pNamaSatuan: TNamaSatuanBrg; var pIsiSatuan: TIsiSatuanBrg): String;
begin
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('select Sat1, Sat2, Sat3, Isi1, Isi2, Isi3');
    SQL.Add('from dbBarang');
    SQL.Add('where KodeBrg='+QuotedStr(pKodeBrg));
    Open;
    pNamaSatuan[1]:=FieldByName('Sat1').AsString;
    pNamaSatuan[2]:=FieldByName('Sat2').AsString;
    pNamaSatuan[3]:=FieldByName('Sat3').AsString;
    pIsiSatuan[1]:=FieldByName('Isi1').AsFloat;
    pIsiSatuan[2]:=FieldByName('Isi2').AsFloat;
    pIsiSatuan[3]:=FieldByName('Isi3').AsFloat;
  end;
  Result:='[1]'+pNamaSatuan[1]+' [2]'+pNamaSatuan[2];//+' [3]'+pNamaSatuan[3];
end;

function IsiSatuanBrgJual(pKodeBrg, pkodeCust: string; var pNamaSatuan: TNamaSatuanBrg; var pIsiSatuan: TIsiSatuanBrg; Var pHargaBrg: THargabrg): String;
begin
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('select Sat1, Sat2, Sat3, Isi1, Isi2, Isi3, Case when B.Harga_1 Is null then Hrg1_1 Else B.Harga_1 end Hrg1_1, ');
    SQL.Add('       Case when B.Harga_2 Is null then Hrg1_2 Else B.Harga_2 end Hrg1_2, Hrg1_3');
    SQL.Add('from dbBarang A');
    Sql.add('     Left Outer join DBBARANGCUSTOMER B on B.KodeBrg=A.KODEBRG and B.KodecustSupp='+QuotedStr(pkodeCust));
    SQL.Add('where A.KodeBrg='+QuotedStr(pKodeBrg));
    Open;
    pNamaSatuan[1]:=FieldByName('Sat1').AsString;
    pNamaSatuan[2]:=FieldByName('Sat2').AsString;
    pNamaSatuan[3]:=FieldByName('Sat3').AsString;
    pIsiSatuan[1]:=FieldByName('Isi1').AsFloat;
    pIsiSatuan[2]:=FieldByName('Isi2').AsFloat;
    pIsiSatuan[3]:=FieldByName('Isi3').AsFloat;
    pHargaBrg[1]:=FieldByName('Hrg1_1').AsFloat;
    pHargaBrg[2]:=FieldByName('Hrg1_2').AsFloat;
    pHargaBrg[3]:=FieldByName('Hrg1_3').AsFloat;
  end;
  Result:='[1]'+pNamaSatuan[1]+' [2]'+pNamaSatuan[2]+' [3]'+pNamaSatuan[3];
end;


function Check_Nomor(Bulan,Tahun:integer;Tipe:String;var Nomor,xNoUrut:String;xDate:TDateTime):Boolean; Overload;
var S1,S2 : String;
    N1,N2,N3,N4 : Integer;
    xDigitNomor: String;
begin
  if DM.TaNomor.Active=False then
    DM.TaNomor.Open;
  xReset:=DM.TaNomor.FieldByName('Reset').AsInteger;
  xDigitNomor:=DM.TaNomor.FieldByName('DigitNomor').AsString;
  with DM.QuNomor do
  begin
    close;
    sql.Clear;
    Sql.add('Select MAX(x.nomor) nomor from(');
    if (Tipe='SPK')//or (Tipe='INV')
    or (Tipe='DN') or (Tipe='KN')or(Tipe='HT')or(Tipe='PT') Then
    begin
    Sql.Add('Select Isnull(Max(case when isnumeric(Right(NoBukti,4))=1 then cast(Right(NoBukti,4) as int) else 0 end')
    end
    else
    Sql.Add('Select Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end');
    if Tipe='AKM' then
    begin
      Sql.Add('),0) Nomor from dbTransaksi ');
    end
    else
    if Tipe='BHN' then
    begin
      Sql.Add('),0) Nomor from dbPemakaianBhn ');
    end;
    if (Tipe='BPPB')Then
    begin
      Sql.Add('),0) Nomor from dbBPPB ');
    end;
    if (Tipe='BPPBT')Then
    begin
      Sql.Add('),0) Nomor from dbBPPBT ');
    end;
    if (Tipe='PR')Then
    begin
      Sql.Add('),0) Nomor from dbPPL ');
    end;
    if (Tipe='AGR')Then
    begin
      Sql.Add('),0) Nomor from dbAnggaran ');
    end;
    if (Tipe='PO')Then
    begin
      Sql.Add('),0) Nomor from dbPO ');
    end;
    if (Tipe='PRD')Then
    begin
      Sql.Add('),0) Nomor from dbProduksi ');
    end;
    if (Tipe='FNS')Then
    begin
      Sql.Add('),0) Nomor from dbFinishing ');
    end;
    if (Tipe='SO') or (Tipe='SOI')Then
    begin
      Sql.Add('),0) Nomor from dbSO ');
    end;
    if (Tipe='SPP')Then
    begin
      Sql.Add('),0) Nomor from dbSPP ');
    end;
    if (Tipe='SPB')Then
    begin
      Sql.Add('),0) Nomor from dbSPB ');
    end;
    if (Tipe='RSPB')Then
    begin
      Sql.Add('),0) Nomor from dbRSPB ');
    end;
    if (Tipe='DSN')Then
    begin
      Sql.Add('),0) Nomor from dbDesain ');
    end;
    if Tipe='HT' then
      begin
        Sql.Add('),0) Nomor from vwHUTPIUT ');
      end
    else
    if Tipe='PT' then
      begin
       Sql.Add('),0) Nomor from vwHUTPIUT ');
      end;
    if (Tipe='INVC')Then
    begin
      //Sql.Add('),0) Nomor from dbInvoicePL ');
       sql.Add('),0) Nomor from (Select NoBukti,NoUrut, Tanggal From dbInvoicePL x union  Select NoBukti,NoUrut, Tanggal From dbInvoiceUM x) A');
    end;
    if (Tipe='RPJ')Then
    begin
      Sql.Add('),0) Nomor from dbRInvoicePL ');
    end;
    if (Tipe='SPR')Then
    begin
      Sql.Add('),0) Nomor from dbSPBRJual ');
    end;
    If Tipe='TT' then
    begin
      Sql.Add('),0) Nomor from (select NoUrut, Tanggal From dbInvoiceRPJ');
      SQL.add('                 ) A');
    end;
    if (Tipe='PBL')Then
    begin
      Sql.Add('),0) Nomor from dbBeli ');
    end;
    if (Tipe='TRS')Then
    begin
      Sql.Add('),0) Nomor from dbTransfer ');
    end;
    if (Tipe='OPN')  Then
    begin
      Sql.Add('),0) Nomor from dbKoreksi ');
    end;
    if  (tipe='KRS') then
    begin
      Sql.Add('),0) Nomor from dbKoreksi ');
    end;
   if (Tipe='OPBJ')   Then
    begin
      Sql.Add('),0) Nomor from dbKoreksi ');
    end;
    if (Tipe='KMS')Then
    begin
      Sql.Add('),0) Nomor from (Select NoUrut, Tanggal from dbUbahKemasan where FlagTipe=0) A ');
    end;
    if (Tipe='KMBJ')Then
    begin
      Sql.Add('),0) Nomor from (Select NoUrut, Tanggal from dbUbahKemasan where FlagTipe=1) A ');
    end;
    if (Tipe='INV')Then
    begin
      Sql.Add('),0) Nomor from dbInvoice ');
    end;
    if (Tipe='DN')Then
    begin
      Sql.Add('),0) Nomor from dbDebetNote ');
    end;
    if (Tipe='KN')Then
    begin
      Sql.Add('),0) Nomor from dbKreditNote ');
    end;
   if (Tipe='SPK')Then
    begin
      Sql.Add('),0) Nomor from dbSPK ');
    end;
     if (Tipe='HPD')Then
    begin
      Sql.Add('),0) Nomor from dbHASILPRD ');
    end;
    if (Tipe='BP')  Then
    begin
      Sql.Add('),0) Nomor from (Select NoUrut, Tanggal from dbPenyerahanBhn a  ');
  //    sql.add(' left outer join DBgudang b on b.KODEgdg =a.Kodegdg ');
      sql.add('  where IsSampel=0 and (Nourut not like (''0D%'') and Nourut  not like (''0E%'') ) ) A ');
    end;
    if (Tipe='PBS')  Then
    begin
      Sql.Add('),0) Nomor from (Select NoUrut, Tanggal from dbPenyerahanBhn where IsSampel=1) A ');
    end;
    if (Tipe='RBP')Then
    begin
      Sql.Add('),0) Nomor from (Select NoUrut, Tanggal from dbRPenyerahanBhn where IsSampel=0) A');
    end;
     if (Tipe='RPBS')Then
    begin
      Sql.Add('),0) Nomor from (Select NoUrut, Tanggal from dbRPenyerahanBhn where IsSampel=1) A');
    end;
    if (Tipe='RPB')Then
    begin
      Sql.Add('),0) Nomor from dbRBeli ');
    end;

    if Tipe='POS' then
    begin
      Sql.Add('),0) Nomor from dbJualTunai ');
    end;

      if xReset = 0 then
         Sql.Add('where month(Tanggal)='+IntToStr(Bulan)+' and year(Tanggal)='+IntToStr(Tahun))
      else
         Sql.Add('where  year(Tanggal)='+IntToStr(Tahun));
    if (Tipe='SPK')or (Tipe='INV')or (Tipe='DN') or (Tipe='KN')or(Tipe='HT')or(Tipe='PT')Then
    begin
     SQL.Add('and isnumeric(Right(NoBukti,4))=1 ')
    end
    else
    SQL.Add('and isnumeric(NoUrut)=1 ');
    //if (Tipe='PO')Then Sql.add(' and FlagTipe='+IntToStr(XPPN)+''); //PPN NON PPN
    if Tipe='HT' Then Sql.Add(' and Tipe=''HT'' and NoBukti like ''%HT%''')  ;
    If Tipe='PT' Then Sql.Add(' and Tipe=''PT'' and NoBukti like ''%PT%''')  ;
    //if tipe='PO' then  Sql.Add(' and Isnull(Flagtipe,0)=1 ');
    if tipe='SO' then  Sql.Add(' and Isnull(TipePPN,0)=1 ');
    if tipe='SO' then  Sql.Add(' and Flagtipe =''L''') else if tipe ='SOI' then Sql.Add(' and Flagtipe =''I''');
    if tipe='PBL' then  Sql.Add(' and Isnull(Flagtipe,0)=0 ');
    {if tipe='FNS' then
    begin
    Sql.Add(' and Isnull(Flagtipe,0)=0 ');
    end;}

    SQl.Add('Union All');
    sql.add('Select  Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end),0) Nomor');
    sql.Add('from DBNOMORPK ');
    sql.add('where Tipe='+QuotedStr(Tipe));
    if Tipe='HT' then Sql.add('and nobukti like ''%HT%'' ');
    if Tipe='PT' then Sql.add('and nobukti like ''%PT%'' ');

    if xReset = 0 then
         Sql.Add(' and bulan='+IntToStr(Bulan)+' and tahun='+IntToStr(Tahun))
      else
         Sql.Add(' and  Tahun='+IntToStr(Tahun));
    if (Tipe='OPN')or (Tipe='OPBJ')or (Tipe='KMS')or (Tipe='KMBJ') Then
     SQL.Add('and NoBukti Like :0');
   if (Tipe='SPK')or (Tipe='INV') or
    (Tipe='DN') or(Tipe='HT')or(Tipe='PT') Then
    begin
     SQL.Add('and isnumeric(Right(NoBukti,4))=1 ')
    end
    else
    SQL.Add('and isnumeric(NoUrut)=1 ');
    Sql.add(') x');
    if (Tipe='OPN')or (Tipe='OPBJ')or (Tipe='KMS')or (Tipe='KMBJ')Then
    Parameters[0].Value:='%'+Tipe+'%';
    //sql.SaveToFile('c:\nomor.txt');
    Open;
    if IsEmpty then Nomor := '1' else
    begin
       try
         Nomor := IntToStr(Fields[0].AsInteger+1);
       except
         Nomor:='1';
       end;
    end;
  end;
  xNoUrut:=FormatFloat(xDigitNomor,StrToInt(Nomor));
  {
  if (Tipe='INVC')  then
  xNoUrut:=FormatFloat('00000',StrToInt(Nomor))
  else
  if (Tipe='BPPB') or (Tipe='BPSB') or (Tipe='PR') or (Tipe='BPB') or (Tipe='SPK') or (Tipe='PO')or (Tipe='DN')or (Tipe='KMS')or
     (Tipe='KMBJ')or (Tipe='OPBJ')or (Tipe='OPN')or (Tipe='PBL')or (Tipe='HPD')or (Tipe='INVC')or (Tipe='RPB')or
     (Tipe='BP')or (Tipe='RBP')or (Tipe='BBP') or (Tipe='RBPB') or (Tipe='BPL') or (Tipe='PBS') or (Tipe='RPBS') then
    xNoUrut:=FormatFloat('0000',StrToInt(Nomor))
  else xNoUrut:=FormatFloat(xDigitNomor,StrToInt(Nomor));
  }
  //Nomor:=xNoUrut;
  //if (Tipe='BPPB') or (Tipe='PR') or (Tipe='BPB') or (Tipe='SPK') or (Tipe='PO')or (Tipe='DN')or (Tipe='KMS')or (Tipe='KMBJ')or (Tipe='OPBJ')or (Tipe='OPN')or (Tipe='PBL')or (Tipe='HPD')or (Tipe='INV')or (Tipe='RPB')or (Tipe='BP')or (Tipe='RBP')or (Tipe='BBP') or (Tipe='RBPB') or (Tipe='BPL') then
  //  Nomor:=DM.TaNomor.FieldByName(Tipe).AsString+'/'+xNoUrut
  //else
  Nomor:=xNoUrut;
  Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate);
  Nomor:=MyNoBukti;
end;

function Check_NomorBeli(Bulan,Tahun:integer;Tipe:String;var Nomor,xNoUrut:String;xDate:TDateTime;Xbeli:string):Boolean; Overload;
var S1 : String;
    //S1,S2 : String;
    N1,N2,N3,N4 : Integer;
    xxNomor, xDigitNomor,xTipe: String;
begin
  //Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate);
{  if xIsPpn then
     xTipe :='1'
  else
     xtipe :='0';  }
  xReset:=DM.TaNomor.FieldByName('Reset').AsInteger;
  xDigitNomor:='00000';//DM.TaNomor.FieldByName('DigitNomor').AsString;
  if Tipe<>'' then
  begin
    with DM.QuNomor do
    begin
      close;
      sql.Clear;
      Sql.add('Select MAX(x.nomor) nomor from(');
      Sql.Add(' Select Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end ),0) Nomor');
      sql.add('from dbBeli a');
      sql.add('left outer join DBgudang b on b.KODEgdg =a.Kodegdghd');
      if xReset = 0 then
         Sql.Add('where month(a.Tanggal)='+IntToStr(Bulan)+' and year(Tanggal)='+IntToStr(Tahun))
      else
         Sql.Add('where  year(a.Tanggal)='+IntToStr(Tahun));
      SQL.Add('and isnumeric(a.NoUrut)=1 ');
      sql.add('and kodegdghd = ' +quotedstr(Xkodeexport));
      sql.add('group by kodegdg ');
      SQl.Add('Union All');
      sql.add('Select  Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end),0) Nomor ');
      sql.Add('from DBNOMORPK ');
      sql.add('where Tipe='+QuotedStr(copy(Tipe,1,2)));
//      sql.add('');
      if xReset = 0 then
           Sql.Add('  bulan='+IntToStr(Bulan)+' and tahun='+IntToStr(Tahun))
        else
           Sql.Add(' and  Tahun='+IntToStr(Tahun));
      SQL.Add('and isnumeric(NoUrut)=1 ');
      Sql.add(') x');
      Open;

      if IsEmpty then Nomor := '1' else
      begin
         try
           Nomor := IntToStr(Fields[0].AsInteger+1);
         except
           Nomor:='1';
         end;
      end;
    end;
  end
  else
  Nomor :='00001';
  xNoUrut:=FormatFloat(xDigitNomor,StrToInt(Nomor));
  Nomor:=xNoUrut;
  //Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate,xIsPpn);
   Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate);
  //F1:='';

  //KonfigInvoiceExport(TIPE,NOMOR,F1,xkodeexport,F3,F4,S1,N1,N2,N3,N4,xDate);
  xxNomor:='';
   if Trim(F1)<>'' then
    xxNomor:=xxNomor+Trim(F1)+Separator;
  if Trim(F2)<>'' then
    xxNomor:=xxNomor+Trim(F2)+'-'+XBeli+Separator;
  if Trim(F3)<>'' then
  begin
    if Trim(F4)<>'' then
      xxNomor:=xxNomor+Trim(F3)+Separator+Trim(F4)
    else xxNomor:=xxNomor+Trim(F3);
  end;
  Nomor:=xxNomor;
end;


{function Check_NomorPB(Bulan,Tahun:integer;Tipe:String;var Nomor,xNoUrut:String;xDate:TDateTime;Xbeli:string):Boolean; Overload;
var S1 : String;
    //S1,S2 : String;
    N1,N2,N3,N4 : Integer;
    xxNomor, xDigitNomor,xTipe: String;
begin}
  //Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate);
{  if xIsPpn then
     xTipe :='1'
  else
     xtipe :='0';  }
{  xReset:=DM.TaNomor.FieldByName('Reset').AsInteger;
  xDigitNomor:='00000';//DM.TaNomor.FieldByName('DigitNomor').AsString;
  if Tipe<>'' then
  begin
    with DM.QuNomor do
    begin
      close;
      sql.Clear;
      Sql.add('Select MAX(x.nomor) nomor from(');
      Sql.Add(' Select Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end ),0) Nomor');
      sql.add('from dbpenyerahanBhn a');
      //sql.add('left outer join DBgudang b on b.KODEgdg =a.Kodegdg');
      if xReset = 0 then
         Sql.Add('where month(a.Tanggal)='+IntToStr(Bulan)+' and year(Tanggal)='+IntToStr(Tahun))
      else
         Sql.Add('where  year(a.Tanggal)='+IntToStr(Tahun));
      SQL.Add('and isnumeric(a.NoUrut)=1 ');
      sql.add('and a.kodegdg = ' +quotedstr(Xbeli));
      sql.add('group by a.kodegdg ');
      SQl.Add('Union All');
      sql.add('Select  Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end),0) Nomor ');
      sql.Add('from DBNOMORPK ');
      sql.add('where Tipe='+QuotedStr(copy(Tipe,1,2)));
      sql.add('and kodegdg = ' +quotedstr(Xbeli));
//      sql.add('');
      if xReset = 0 then
           Sql.Add('  bulan='+IntToStr(Bulan)+' and tahun='+IntToStr(Tahun))
        else
           Sql.Add(' and  Tahun='+IntToStr(Tahun));
      SQL.Add('and isnumeric(NoUrut)=1 ');
      Sql.add(') x');
      Open;

      if IsEmpty then Nomor := '1' else
      begin
         try
           Nomor := IntToStr(Fields[0].AsInteger+1);
         except
           Nomor:='1';
         end;
      end;
    end;
  end
  else
  Nomor :='00001';
  xNoUrut:=FormatFloat(xDigitNomor,StrToInt(Nomor));
  Nomor:=xNoUrut;
  //Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate,xIsPpn);
  F2:=QUOTEDSTR(Xbeli);
   Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate);
  //F1:='';

  //KonfigInvoiceExport(TIPE,NOMOR,F1,xkodeexport,F3,F4,S1,N1,N2,N3,N4,xDate);
  xxNomor:='';
   if Trim(F1)<>'' then
    xxNomor:=xxNomor+Trim(F1)+Separator;
  if Trim(F2)<>'' then
    xxNomor:=xxNomor+Trim(F2)+'-'+XBeli+Separator;
  if Trim(F3)<>'' then
  begin
    if Trim(F4)<>'' then
      xxNomor:=xxNomor+Trim(F3)+Separator+Trim(F4)
    else xxNomor:=xxNomor+Trim(F3);
  end;
  Nomor:=xxNomor;
end;}

function Check_NomorPB(Bulan,Tahun:integer;Tipe:String;var Nomor,xNoUrut:String;xDate:TDateTime;Xbeli:string;Jenis:integer):Boolean; Overload;
var S1 : String;
    //S1,S2 : String;
    N1,N2,N3,N4 : Integer;
    xxNomor, xDigitNomor,xTipe: String;
begin
  //Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate);
{  if xIsPpn then
     xTipe :='1'
  else
     xtipe :='0';  }
  xReset:=DM.TaNomor.FieldByName('Reset').AsInteger;
  xDigitNomor:='00000';//DM.TaNomor.FieldByName('DigitNomor').AsString;
  if Tipe<>'' then
  begin
    with DM.QuNomor do
    begin
      close;
      sql.Clear;
      Sql.add('Select MAX(x.nomor) nomor from(');
      Sql.Add(' Select Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end ),0) Nomor');
      sql.add('from dbpenyerahanBhn a');
      //sql.add('left outer join DBgudang b on b.KODEgdg =a.Kodegdg');
      if xReset = 0 then
         Sql.Add('where month(a.Tanggal)='+IntToStr(Bulan)+' and year(Tanggal)='+IntToStr(Tahun))
      else
         Sql.Add('where  year(a.Tanggal)='+IntToStr(Tahun));
      SQL.Add('and isnumeric(a.NoUrut)=1 and flagtipe = '+IntToStr(Jenis) );
      sql.add('and a.kodegdg = ' +quotedstr(Xbeli));
      sql.add('group by a.kodegdg ');
      SQl.Add('Union All');
      sql.add('Select  Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end),0) Nomor ');
      sql.Add('from DBNOMORPK ');
      sql.add('where Tipe='+QuotedStr(copy(Tipe,1,2)));
      sql.add('and kodegdg = ' +quotedstr(Xbeli));
//      sql.add('');
      if xReset = 0 then
           Sql.Add('  bulan='+IntToStr(Bulan)+' and tahun='+IntToStr(Tahun))
        else
           Sql.Add(' and  Tahun='+IntToStr(Tahun));
      SQL.Add('and isnumeric(NoUrut)=1 ');
      Sql.add(') x');
      Open;

      if IsEmpty then Nomor := '1' else
      begin
         try
           Nomor := IntToStr(Fields[0].AsInteger+1);
         except
           Nomor:='1';
         end;
      end;
    end;
  end
  else
  Nomor :='00001';
  xNoUrut:=FormatFloat(xDigitNomor,StrToInt(Nomor));
  Nomor:=xNoUrut;
  //Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate,xIsPpn);
  F2:=QUOTEDSTR(Xbeli);
   Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate);
  //F1:='';

  //KonfigInvoiceExport(TIPE,NOMOR,F1,xkodeexport,F3,F4,S1,N1,N2,N3,N4,xDate);
  xxNomor:='';
   if Trim(F1)<>'' then
    xxNomor:=xxNomor+Trim(F1)+Separator;
  if Trim(F2)<>'' then
    xxNomor:=xxNomor+Trim(F2)+'-'+XBeli+Separator;
  if Trim(F3)<>'' then
  begin
    if Trim(F4)<>'' then
      xxNomor:=xxNomor+Trim(F3)+Separator+Trim(F4)
    else xxNomor:=xxNomor+Trim(F3);
  end;
  Nomor:=xxNomor;
end;


function Check_NomorRPB(Bulan,Tahun:integer;Tipe:String;var Nomor,xNoUrut:String;xDate:TDateTime;Xbeli:string;Jenis:integer):Boolean; Overload;
var S1 : String;
    //S1,S2 : String;
    N1,N2,N3,N4 : Integer;
    xxNomor, xDigitNomor,xTipe: String;
begin
  //Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate);
{  if xIsPpn then
     xTipe :='1'
  else
     xtipe :='0';  }
  xReset:=DM.TaNomor.FieldByName('Reset').AsInteger;
  xDigitNomor:='00000';//DM.TaNomor.FieldByName('DigitNomor').AsString;
  if Tipe<>'' then
  begin
    with DM.QuNomor do
    begin
      close;
      sql.Clear;
      Sql.add('Select MAX(x.nomor) nomor from(');
      Sql.Add(' Select Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end ),0) Nomor');
      sql.add('from dbrpenyerahanBhn a');
      //sql.add('left outer join DBgudang b on b.KODEgdg =a.Kodegdg');
      if xReset = 0 then
         Sql.Add('where month(a.Tanggal)='+IntToStr(Bulan)+' and year(Tanggal)='+IntToStr(Tahun))
      else
         Sql.Add('where  year(a.Tanggal)='+IntToStr(Tahun));
      SQL.Add('and isnumeric(a.NoUrut)=1 and flagtipe = '+IntToStr(Jenis) );
      sql.add('and a.kodegdg = ' +quotedstr(Xbeli));
      sql.add('group by a.kodegdg ');
      SQl.Add('Union All');
      sql.add('Select  Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end),0) Nomor ');
      sql.Add('from DBNOMORPK ');
      sql.add('where Tipe='+QuotedStr(copy(Tipe,1,2)));
      //sql.add('and kodegdg = ' +quotedstr(Xbeli));
//      sql.add('');
      if xReset = 0 then
           Sql.Add('  bulan='+IntToStr(Bulan)+' and tahun='+IntToStr(Tahun))
        else
           Sql.Add(' and  Tahun='+IntToStr(Tahun));
      SQL.Add('and isnumeric(NoUrut)=1 ');
      Sql.add(') x');
      Open;

      if IsEmpty then Nomor := '1' else
      begin
         try
           Nomor := IntToStr(Fields[0].AsInteger+1);
         except
           Nomor:='1';
         end;
      end;
    end;
  end
  else
  Nomor :='00001';
  xNoUrut:=FormatFloat(xDigitNomor,StrToInt(Nomor));
  Nomor:=xNoUrut;
  //Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate,xIsPpn);
  F2:=QUOTEDSTR(Xbeli);
   Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate);
  //F1:='';

  //KonfigInvoiceExport(TIPE,NOMOR,F1,xkodeexport,F3,F4,S1,N1,N2,N3,N4,xDate);
  xxNomor:='';
   if Trim(F1)<>'' then
    xxNomor:=xxNomor+Trim(F1)+Separator;
  if Trim(F2)<>'' then
    xxNomor:=xxNomor+Trim(F2)+Separator;
  if Trim(F3)<>'' then
  begin
    if Trim(F4)<>'' then
      xxNomor:=xxNomor+Trim(F3)+Separator+Trim(F4)
    else xxNomor:=xxNomor+Trim(F3);
  end;
  Nomor:=xxNomor;
end;


function Check_Nomor(Bulan,Tahun:integer;Tipe:String;var Nomor,xNoUrut:String;xDate:TDateTime;xCustSupp:String;xIsPpn:Boolean):Boolean; OverLoad;
var S1 : String;
    //S1,S2 : String;
    N1,N2,N3,N4 : Integer;
    xxNomor, xDigitNomor,xTipe: String;
begin
  //Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate);
  if xIsPpn then
     xTipe :='1'
  else
     xtipe :='0';
  xReset:=DM.TaNomor.FieldByName('Reset').AsInteger;
  xDigitNomor:=DM.TaNomor.FieldByName('DigitNomor').AsString;
  if Tipe<>'' then
  begin
    with DM.QuNomor do
    begin
      close;
      sql.Clear;
      Sql.add('Select MAX(x.nomor) nomor from(');
      Sql.Add('Select Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end');
      if Tipe='PO' then
         sql.add('),0) NOmor from DBPO')
      Else if Tipe='PBL' then
        sql.add('),0) NOmor from DBBELI')
      Else if Tipe='RPB' then
        sql.add('),0) NOmor from DBRBELI')
      Else if Tipe='SO' then
        sql.add('),0) NOmor from DBSO')
      Else if Tipe='SPP' then
        sql.add('),0) NOmor from DBSPP')
      Else if Tipe='SPB' then
        sql.add('),0) NOmor from DBSPB')
      Else if Tipe='RSPB' then
        sql.add('),0) NOmor from DBRSPB')
      Else if Tipe='DSN' then
        sql.add('),0) NOmor from DBDesain')
      Else if Tipe='INVC' then
        //sql.add('),0) NOmor from DBInvoicePL')
        sql.Add('),0) Nomor from (Select NoBukti,NoUrut, Tanggal From ' +
          'dbInvoicePL x union  Select NoBukti,NoUrut, Tanggal From dbInvoiceUM ' +
          'x) A')
      Else if Tipe='SPR' then
        sql.add('),0) NOmor from DBSPBRjual')
      Else if Tipe='TT' then
        sql.add('),0) NOmor from DBINVOICERPJ');
      //if (Tipe='BKK') or (Tipe='BKM') or (Tipe='BBM') or (Tipe='BBK')  or (Tipe='BMM') or (Tipe='BJK') or (Tipe='PJL')  or (Tipe='AKM') then   //    asli
     if (Tipe='BKK') or (Tipe='BKM') or (Tipe='BBM') or (Tipe='BBK') or (Tipe='BJK') or (Tipe='PJL')  or (Tipe='AKM') then
      begin
        Sql.Add('),0) Nomor from (select x.NoUrut, x.Tanggal from dbTrans x, dbtransaksi y  where x.Nobukti=y.nobukti ');
        SQl.add('                 and Left(y.Tipetrans,2)='+QuotedStr(copy(Tipe,1,2))+' Group by x.Nourut,x.Tanggal ');
        SQl.Add('                 Union ALL ');
        SQl.add('                 Select x.NoUrut, x.Tanggal  From DbJurnalOto x where Left(x.Tipetrans,2)='+QuotedStr(copy(Tipe,1,2)));
        SQl.Add('                 Union ALL ');
        SQl.add('                 Select x.NoUrutJurnal, x.Tgljurnal Tanggal From DbSPB x ');//where Left(x.Tipetrans,2)='+QuotedStr(copy(Tipe,1,2)));
        SQl.Add('                 Union ALL ');
        SQl.add('                 Select x.NoUrutJurnal, x.Tgljurnal Tanggal From DbRSPB x ');
        SQl.Add('                 Union ALL ');
        SQl.add('                 Select x.NoUrutJurnal, x.Tgljurnal Tanggal From DbSPBRjual x ');//where Left(x.Tipetrans,2)='+QuotedStr(copy(Tipe,1,2)));
        SQl.add(') T ');
      end;
      if (Tipe='BMM') then
        Begin
        Sql.Add('),0) Nomor from (select x.NoUrut, x.Tanggal from dbTrans x, dbtransaksi y  where x.Nobukti=y.nobukti ');
          if xCustSupp='0' then

            SQL.add(' and isnull(Flagtipe,0)=0')
          else
             SQL.add(' and isnull(Flagtipe,0)=1') ;
        SQl.add('                 and Left(y.Tipetrans,2)='+QuotedStr(copy(Tipe,1,2))+' Group by x.Nourut,x.Tanggal ');
        SQl.add(') T ');
        end;
      if xReset = 0 then
         Sql.Add('where month(Tanggal)='+IntToStr(Bulan)+' and year(Tanggal)='+IntToStr(Tahun))
      else
        Sql.Add('where  year(Tanggal)='+IntToStr(Tahun));
      SQL.Add('and isnumeric(NoUrut)=1 ');
      if Tipe='SO' then
          SQL.Add('and TipePPN=0 ')
      Else if Tipe='BMM'
      then
        begin
          SQL.add('');

          
        end
      Else
      begin
      if Tipe<>'AKM' then
       begin
          if (tipe='INVC') or (tipe='PO') or (xtipe='1') then
            //SQL.Add('and TipePPN=0 ')
            SQL.Add('and Flagtipe=1 ')
            else
           SQL.Add('and Flagtipe=0 ')


       end;
      End;

      SQl.Add('Union All');
      sql.add('Select  Isnull(Max(case when isnumeric(NoUrut)=1 then cast(NoUrut as int) else 0 end),0) Nomor');
      sql.Add('from DBNOMORPK ');
      sql.add('where Tipe='+QuotedStr(copy(Tipe,0,3)));
      if xReset = 0 then
           Sql.Add(' and bulan='+IntToStr(Bulan)+' and tahun='+IntToStr(Tahun))
        else
           Sql.Add(' and  Tahun='+IntToStr(Tahun));
      SQL.Add('and isnumeric(NoUrut)=1 ');
      Sql.add(') x');
      //Sql.SaveToFile('c:\nomor.txt');
      Open;
      if IsEmpty then Nomor := '1' else
      begin
         try
           Nomor := IntToStr(Fields[0].AsInteger+1);
         except
           Nomor:='1';
         end;
      end;
    end;
  end
  else
  Nomor :='00001';
  xNoUrut:=FormatFloat(xDigitNomor,StrToInt(Nomor));
  Nomor:=xNoUrut;
  Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate,xIsPpn);
  xxNomor:='';
  if Trim(F1)<>'' then
    xxNomor:=xxNomor+Trim(F1)+Separator;
    if Tipe='AKM' then
    xxNomor:=xxNomor+'AKM/';
  if Trim(F2)<>'' then
    xxNomor:=xxNomor+Trim(F2)+Separator;
  if Trim(F3)<>'' then
  begin
    if Trim(F4)<>'' then
      xxNomor:=xxNomor+Trim(F3)+Separator+Trim(F4)
    else xxNomor:=xxNomor+Trim(F3);
  end;
  Nomor:=xxNomor;
end;

function Check_NomorKasBank(pBulan, pTahun: integer; pTipe:String; var pNomor, pNoUrut: String; pDate: TDateTime; pSimbol: String; pIsPpn: Boolean): Boolean; OverLoad;
var S1 : String;
    //S1,S2 : String;
    N1,N2,N3,N4 : Integer;
    xxNomor, xDigitNomor: String;
    pReset: Byte;
begin
  //Konfig(TIPE,NOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,xDate);
  //if xIsPpn then
  //   xTipe :='1'
  //else
  //   xtipe :='0';
  pReset:=DM.TaNomor.FieldByName('Reset').AsInteger;
  xDigitNomor:=DM.TaNomor.FieldByName('DigitNomor').AsString;
  if pTipe<>'' then
  begin
    with DM.QuNomor do
    begin
      Close;
      SQL.Clear;
      SQL.add('select top 1 NoUrut from dbTrans');
      if xReset = 0 then
         Sql.Add('where month(Tanggal)='+IntToStr(pBulan)+' and year(Tanggal)='+IntToStr(pTahun))
      else
         Sql.Add('where  year(Tanggal)='+IntToStr(pTahun));
      if (pTipe='BKK') or (pTipe='BKM') then
      begin
        SQL.Add('and TipeTransHD in (''BKK'',''BKM'') ');
      end else
      begin
        SQL.Add('and TipeTransHD in (''BBK'',''BBM'') ');
      end;
      SQL.Add('and Simbol='+QuotedStr(pSimbol));
      SQL.Add('and isnumeric(NoUrut)=1 ');
      SQL.Add('order by NoUrut desc ');
      Open;
      if IsEmpty then pNomor := '1' else
      begin
         try
           pNomor := IntToStr(Fields[0].AsInteger+1);
         except
           pNomor:='1';
         end;
      end;
    end;
  end
  else
  pNomor :='00001';
  pNoUrut:=FormatFloat(xDigitNomor,StrToInt(pNomor));
  //pNoUrut:='XXX';
  pNomor:=pNoUrut+pSimbol;
  Konfig(pTIPE,pNOMOR,F1,F2,F3,F4,S1,N1,N2,N3,N4,pDate,pIsPpn);
  xxNomor:='';
  if Trim(F1)<>'' then
    xxNomor:=xxNomor+Trim(F1)+Separator;
  if Trim(F2)<>'' then
    xxNomor:=xxNomor+Trim(F2)+Separator;
  if Trim(F3)<>'' then
  begin
    if Trim(F4)<>'' then
      xxNomor:=xxNomor+Trim(F3)+Separator+Trim(F4)
    else xxNomor:=xxNomor+Trim(F3);
  end;
  pNomor:=xxNomor;
end;


Procedure LoggingData(pPemakai, pAktivitas, pSumber, pNoBukti, pKeterangan: String);
begin
  With DM.QuCari2 do
  begin
    Close;
    SQL.Clear;
    SQL.Add('Insert into dbLogFile (Tahun, Bulan, Tanggal,Pemakai,Aktivitas,Sumber,NoBukti,Keterangan)');
    Sql.Add('Values(year(GETDATE()),MONTH(GETDATE()), Getdate(),'+QuotedStr(pPemakai)+','+QuotedStr(pAktivitas)+','+QuotedStr(pSumber)+','+QuotedStr(pNoBukti)+','+QuotedStr(pKeterangan)+')');
    try
      ExecSQL;
    except
    end;
  end;
end;

procedure Hapus_Daftar_Nomor_Grp(Tipe:String);
begin
      // Hapus Nomor Yang ada di daftar (dbNOMORPK)
      with Dm.DaftarNO do
      begin
         Close;
         SQL.Clear;
         SQL.Add('Delete from dbNOMORPK');
         SQL.Add('Where tipe =:0');
         Parameters[0].Value:=Tipe;
         try
           ExecSQL;
         except
           ShowMessage('Hapus Daftar Nomor Gagal !');
         end;
      end;
end;

function MyCek_Lock_PeriodeProses(Bln,Thn:String):Boolean;
begin
   with dm.QuCari do
   begin
      Close;
      SQL.Clear;
      SQL.Add('Select * from dbLockPeriode');
      SQL.Add('Where Bulan=:0 and Tahun=:1');
      Prepared;
      Parameters[0].Value:=Bln;
      Parameters[1].Value:=Thn;
      Open;
   end;
   Result := DM.QuCari.IsEmpty;
end;

procedure ShowReportPreview(JudulForm:String;PageIndex:Integer);
var
  I: Integer;
  //I,J: Integer;
  t1,t2 : tdatetime;
begin
   FrReportPreview.PageIdx := PageIndex;
   FrReportPreview.LJudulReport.Caption:=JudulForm;
   t1 := EncodeDate(StrToInt(PeriodThn),StrToInt(PeriodBln),1);
   t2 := IncDay(IncMonth(t1,1),-1);
   if PageIndex>=0 then
   begin
       //FrReportPreview.Panel5.Height := FrReportPreview.heightPanel;
       FrReportPreview.dxPageControl1.Visible := true;
       FrReportPreview.TabIndex:=PageIndex;
       for I:=0 to FrReportPreview.dxPageControl1.PageCount-1 do
          if I=FrReportPreview.TabIndex then
          begin
            FrReportPreview.dxPageControl1.ActivePageIndex:=I;
            FrReportPreview.dxPageControl1.Pages[I].TabVisible:=True;
          end
          else
             FrReportPreview.dxPageControl1.Pages[I].TabVisible:=False;
       if PageIndex=0 then
       begin
          FrReportPreview.Awal.Date := t1;
          FrReportPreview.Akhir.Date := t2;
       end;
       if PageIndex=1 then
       begin
          FrReportPreview.Awal3.Date := t1;
          FrReportPreview.Akhir3.Date := t2;
       end;
       if PageIndex=2 then
       begin
          FrReportPreview.Bulan.AsInteger := StrToInt(PeriodBln);
          FrReportPreview.Tahun.AsInteger := StrToInt(PeriodThn);
       end;
       if PageIndex=3 then
       begin
          FrReportPreview.TglAwal4.Date := t1;
          FrReportPreview.TglAkhir4.Date := t2;
       end;
       if PageIndex=9 then
       begin
          FrReportPreview.Bulan10.Value :=StrToInt(PeriodBln);
          FrReportPreview.Tahun10.Value :=StrToInt(PeriodThn);
          FrReportPreview.Awal3.Date := t1;
          FrReportPreview.Akhir3.Date := t2;
       end;
   end
   else
   begin
      FrReportPreview.dxPageControl1.Visible := false;
      //FrReportPreview.Panel5.Height := FrReportPreview.heightPanel-FrReportPreview.dxPageControl1.Height;
   end;
   SelectedSemuaRecord:=True;
end;

Function Mymonth(x:integer):String;
begin
  case x of
    1 : Result := 'Januari';
    2 : Result := 'Februari';
    3 : Result := 'Maret';
    4 : Result := 'April';
    5 : Result := 'Mei';
    6 : Result := 'Juni';
    7 : Result := 'Juli';
    8 : Result := 'Agustus';
    9 : Result := 'September';
    10: Result := 'Oktober';
    11: Result := 'Nopember';
    12: Result := 'Desember';
  end;
end;

  procedure CekOtoritasMenu(Pemakai:String;NoMenu:integer;var Tmb,Hps,Krs,Ctk,Exc:Boolean);
  begin
     with dm.Qucari do
     begin
        close;
        SQL.Clear;
        SQL.Add('Select A.* from dbflmenu a left outer join dbmenu b on b.kodemenu=a.L1 where a.userid=:0 and b.ACCESS=:1');
        Parameters[0].Value:=Pemakai;
        Parameters[1].Value:=NoMenu;
        Open;
     end;
     Tmb:=DM.QuCari.Fieldbyname('isTambah').AsBoolean;
     hps:=DM.QuCari.Fieldbyname('ishapus').AsBoolean;
     krs:=DM.QuCari.Fieldbyname('isKoreksi').AsBoolean;
     Ctk:=DM.QuCari.Fieldbyname('isCetak').AsBoolean;
     Exc:=DM.QuCari.Fieldbyname('isExPort').AsBoolean;
  end;

function GenerateCodeCust(tabel,field,nama:string):string;
var
   temp,a1 : string;
begin
   with dm.qucari do
   begin
      close;
      sql.clear;
      sql.add('select max(cast(Right('+field+',5) as int)) from '+tabel);
      sql.Add('where substring('+field+',2, 1)=:0 and kind=''1'' and ISNUMERIC(Right('+Field+',5))=1 ');
      prepared;
      parameters[0].Value:=Copy(nama,1,1);
      open;
      a1:=fields[0].asstring;
      if fields[0].asstring='' then
         temp := '00001'
      else
         temp := inttostr(strtoint(fields[0].asstring)+1);
   end;
   Result :='C'+Copy(nama,1,1)+ copy('00000',1,5-length(temp))+temp;
end;

function GenerateCodeSupp(tabel,field,nama:string):string;
var
   temp,a1 : string;
begin
   with dm.qucari do
   begin
      close;
      sql.clear;
      sql.add('select max(cast(Right('+field+',5) as int)) from '+tabel);
      sql.Add('where substring('+field+',2, 1)=:0 and Kind=''0'' and ISNUMERIC(Right('+Field+',5))=1');
      prepared;
      parameters[0].Value:=Copy(nama,1,1);
      open;
      a1:=fields[0].asstring;
      if fields[0].asstring='' then
         temp := '00001'
      else
         temp := inttostr(strtoint(fields[0].asstring)+1);
   end;
   Result :='S'+Copy(nama,1,1)+ copy('00000',1,5-length(temp))+temp;
end;

function GenerateCode(tabel,field,nama:string):string;
var
   temp,a1 : string;
begin
 if UpperCase(tabel)='DBCUSTSUPP' Then
  Begin
   with dm.qucari do
   begin
      close;
      sql.clear;
      sql.add('Update '+tabel+' Set Tanggal=Null where  substring('+field+',3, 1) in (''a'',''b'',''c'',''d'',''e'',''f'', ');
      sql.add('''g'',''h'',''i'',''j'',''k'',''l'',''m'',''n'',''o'',''p'',''q'',''r'',''s'',''t'',''u'',''v'',''w'',''x'',''y'',''z'')');
      ExecSQL;
   end;
  end;
   with dm.qucari do
   begin
      close;
      sql.clear;
      sql.add('select max(cast(substring('+field+',3,6) as int)) from '+tabel);
      sql.Add('where substring('+field+',1, 1)=:0  and Tanggal Is Not Null');
      prepared;
      parameters[0].Value:=nama;
      open;
      a1:=fields[0].asstring;
      if fields[0].asstring='' then
         temp := '00001'
      else
         temp := inttostr(strtoint(fields[0].asstring)+1);
   end;
   Result :=Copy(nama,1,1)+ copy('00000',1,5-length(temp))+temp;
end;

function GenerateCode2(tabel,field,nama:string):string;
var
   temp : string;
begin
   with dm.qucari do
   begin
      close;
      sql.clear;
      sql.add('select max(substring('+field+',4,2)) from '+tabel);
      sql.Add('where substring('+field+',3, 1)=:0 ');
      prepared;
      parameters[0].Value:=nama;
      open;
      if fields[0].asstring='' then
         temp := '01'
      else
      begin
        temp := inttostr(strtoint(Copy(fields[0].asstring,1,2))+1);
      end
   end;
   GenerateCode2 :=nama+ copy('00',1,2-length(temp))+temp;
end;

function GenerateCode3(tabel,field,nama:string):string;
var
   temp : string;
begin
   with dm.qucari do
   begin
      close;
      sql.clear;
      sql.add('select max(substring('+field+','+inttostr(length(Nama)+1)+',3)) from '+tabel);
      sql.Add('where substring('+field+',3, 1)=:0 ');
      prepared;
      parameters[0].Value:=nama;
      open;
      if fields[0].asstring='' then
         temp := '001'
      else
      begin
        showmessage(Copy(fields[0].asstring,1,3));
        temp := inttostr(strtoint(Copy(fields[0].asstring,1,3))+1);
      end
   end;
   GenerateCode3 :=nama+ copy('000',1,3-length(temp))+temp;
end;

Function GetMyHostName: String;
var
  MyKompie: array [0..255] of char;
  MyInit: TWSADATA;
begin
  WSAStartup($101, MyInit);
  GetHostName(@MyKompie, SizeOf(MyKompie));
  WSACleanup;
  Result:=MyKompie
end;



Procedure SaveToIniFiles(pemakai:string;form:TForm;mGrid : TdxdbGrid);
begin
end;

function Kalimat(Digit:Integer;Kata:String):String;
var i : integer;
    s: string;
begin
   S:='';
   for i:=1 to Digit-length(Kata) do
   begin
       s:=s+'0';
   end;
   Kalimat:=S+Kata;
end;

Function KonversiKeTeks(Bilangan : Real):String;
var  Nilai       : String;
     Satu, Dua,
     Tiga        : String;        // String dari tiap-2 3 digit
     {Num, }One,
     Two, Tri    : Word;          // Index Pemotongan
     Hitung      : Word;
     Gabung      : String;        // Gabungan tiap 3 digit
     Belas       : String;
     Kata        : String;
     Panjang     : Word;
     Digit       : Word;
begin
  Nilai := FloatToStr(Bilangan);
  One := 1;
  Two := 2;
  Tri := 3;
  Hitung := 1;
  Kata := '';
  Panjang := Length(Nilai);

  if (Panjang = 1) or (Panjang = 4) or
     (Panjang = 7) or (Panjang = 10) or (panjang=13) then
    Nilai := '00'+ Nilai;  //001000
  if (Panjang = 2) or (panjang = 5) or
     (Panjang = 8) or (Panjang = 11) or (Panjang=14) then
    Nilai := '0'+ Nilai;

  Panjang := Length(Nilai);

  if (Panjang >= 3) and (Panjang mod 3 = 0) then
    Digit   := Panjang div 3
  else
    Digit   := Panjang div 2;
  if Panjang < 3 then
   Digit := 1;

  While Hitung<=Digit do
   begin
     Satu   := Copy(Nilai, One, 1);
     Dua    := Copy(Nilai, Two, 1);
     Tiga   := Copy(Nilai, Tri, 1);

     Gabung := Satu + Dua + Tiga;
     if StrToInt(Satu) = 1 then
       Kata := Kata + 'Seratus ';

     if StrToInt(Satu) > 1 then
       Kata := Kata + HrfAngka[StrToInt(Satu)] + 'Ratus ';

     if StrToInt(Dua) = 1 then
      begin
        Belas := Dua + Tiga;
        Kata := Kata + HrfAngka[StrToInt(Belas)];
      end
      else
      if StrToInt(Dua) > 1 then
       begin
         Kata := Kata + HrfAngka[StrToInt(Dua)] + ' Puluh ' + HrfAngka[StrToInt(Tiga)];
       end
       else
       if (StrToInt(Dua) = 0) and (StrToInt(Tiga) >0) then
        begin
         if ((Hitung = 1 ) or (Hitung = 2)) and (Copy(Gabung,3,1) = '1') and ((Digit > 1) and (Digit < 3)) then
          begin
           if Hitung = Digit then
             Kata := Kata + HrfAngka[StrToInt(Tiga)]
           else
             Kata := Kata + 'Seribu ';
          end
         else
         if ((Hitung = 3) and (Copy(Gabung,3,1) = '1')) then
          begin
           if Hitung = Digit then
             Kata := Kata + HrfAngka[StrToInt(Tiga)]
           else
             Kata := Kata + 'Seribu ';
          end
         else
           Kata := Kata + HrfAngka[StrToInt(Tiga)];
        end;

      if ((Hitung = 1) and (StrToInt(Gabung) > 0)) and (Digit = 5) then
         Kata := Kata+'Triliun ';
      if ((Hitung = 1) and (StrToInt(Gabung) > 0)) and (Digit = 4) then
        Kata := Kata + 'Milyar ';
      if ((Hitung = 1) and (StrToInt(Gabung) > 0)) and (Digit = 3) then
        Kata := Kata + 'Juta ';
      if ((Hitung = 1) and (StrToInt(Gabung) > 0)) and (Digit = 2) then
       begin
         if (Copy(Gabung, 1, 1) = '0') and (Copy(Gabung, 2, 1) = '0') and (Copy(Gabung, 3, 1) = '1') then
          Kata := Kata + ''
         else
          Kata := Kata + 'Ribu ';
       end;
      if ((Hitung = 1) and (StrToInt(Gabung) > 0)) and (Digit = 1) then
       begin
        if Hitung <> Digit then
          Kata := Kata + 'Ratus '
        else
          Kata := Kata + '';
       end;
      if (Hitung = 2) and(Digit=5) then
          if Gabung ='000' then
            Kata := Kata + ''
         else
            Kata := Kata + 'Milyar ';
      if ((Hitung = 2) and (StrToInt(Gabung) > 0)) and (Digit = 4) then
       if Hitung = Digit then
         Kata := Kata + 'Juta'
       else
         Kata := Kata + '';
       if ((Hitung = 2) and (StrToInt(Gabung) > 0)) and (Digit = 2) then
       begin
        if Hitung <> Digit then
         Kata := Kata + 'Ribu '
        else
         Kata := Kata + '';
       end;

      if (Hitung = 2) and (Digit = 4) then
         if Gabung ='000' then
            Kata := Kata + ''
         else
            Kata := Kata + 'Juta ';
      if ((Hitung = 2) and (StrToInt(Gabung) > 0)) and (Digit = 3) then
       if Hitung = Digit then
         Kata := Kata + 'Juta '
       else
         Kata := Kata + 'Ribu ';
      if ((Hitung = 2) and (StrToInt(Gabung) > 0)) and (Digit = 2) then
       begin
        if Hitung <> Digit then
         Kata := Kata + 'Ribu '
        else
         Kata := Kata + '';
       end;
      if ((Hitung = 2) and (StrToInt(Gabung) > 0)) and (Digit = 1) then
        Kata := Kata + 'Ratus ';
      if ((Hitung = 3) and (StrToInt(Gabung) > 0)) and (Digit = 5) then
       Kata := Kata + 'Juta ';
      if ((Hitung = 3) and (StrToInt(Gabung) > 0)) and (Digit = 4) then
       Kata := Kata + 'Ribu ';
      if ((Hitung = 3) and (StrToInt(Gabung) > 0)) and (Digit = 3) then
       begin
        if Hitung = Digit then
         Kata := Kata + ''
        else
         Kata := Kata + 'Ribu ';
       end;
       if ((Hitung = 4) and (StrToInt(Gabung) > 0)) and (Digit = 5) then
       begin
        if Hitung = Digit then
         Kata := Kata + ''
        else
         Kata := Kata + 'Ribu ';
       end;
      if ((Hitung = 3) and (StrToInt(Gabung) > 0)) and (Digit = 2) then
        Kata := Kata + 'Ratus ';

      Hitung := Hitung + 1;
      One := One + 3;
      Two := Two + 3;
      Tri := Tri + 3;
   end;
   Result := Kata;
end;

function Encrypt(const InString:string; StartKey,MultKey,AddKey:longint): string;
var
  I : Byte;
begin
  Result := '';
  if Length(InString)<>0 then
  begin
    for I := 1 to Length(InString) do
    begin
      Result := Result + CHAR(Byte(InString[I]) xor (StartKey shr 8));
      StartKey := (Byte(Result[I]) + StartKey) * MultKey + AddKey;
    end;
  end;
end;

function Decrypt(const InString:string; StartKey,MultKey,AddKey:Integer): string;
var
  I : Byte;
begin
  Result := '';
  for I := 1 to Length(InString) do
  begin
    Result := Result + CHAR(Byte(InString[I]) xor (StartKey shr 8));
    StartKey := (Byte(InString[I]) + StartKey) * MultKey + AddKey;
  end;
end;

procedure UpdateStatusUser(Pemakai:String;Status:integer);
var
  Host, IP, Err: string;
begin
  with Dm.DaftarNO do
  begin
    Close;
    SQL.Clear;
    SQL.Add('Update dbflpass set status=:0, HostId=:1,IPADDres=:2 ');
    SQL.Add('Where userid =:3 ');
    Prepared;
    Parameters[0].Value:=Status;
    if (Status=1) then
    begin
      if GetIPFromHost(Host, IP, Err) then
      begin
        Parameters[1].Value:= Host;
        Parameters[2].Value:= IP;
      end
      else
      begin
        MessageDlg(Err, mtError, [mbOk], 0);
        ExitProcessProc;
      end;
    end
    else
    begin
      Parameters[1].Value:='';
      Parameters[2].Value:= '';
    end;
    Parameters[3].Value:=Pemakai;

    try
      ExecSQL;
      if (Status=1) then
          LoggingData(Pemakai,'','LOG','',' Login dari '+Host+' ('+IP+') '+' Compiled ' +DateToStr((UnixTimeToDateTime(GetImageLinkTimeStamp(Application.ExeName))) ))
      else
          LoggingData(Pemakai,'','LOG','',' Log off');
    except
      ShowMessage('error !');
    end;
    close;
  end;
end;

procedure GeserKalimat(Kalimat:String;var Hasil:String);
var i:integer;
    s1,s2:string;
begin
    i:=Length(Kalimat);
    s1:=Copy(Kalimat,1,1);
    s2:=Copy(Kalimat,2,i);
    Hasil:=s2+s1;
end;

procedure UrutField(mSelect : String; Var mQuery : TAdoQuery);
Begin
  With mQuery Do
  Begin
    Close;
    Sql.Clear;
    Sql.Add(mSelect);
    Open;
  end;
end;

Procedure UrutField2(mSelect : String; mParam : Array of Variant; Var mQuery : TAdoQuery);
Var
   I : Word;
Begin
  With mQuery Do
  Begin
    Close;
    Sql.Clear;
    Sql.Add(mSelect);
    For I := Low(mParam) to High(mParam) Do
        Parameters[i].Value := mParam[i];
    Prepared;
    Open;
  end;
end;

Function DataBersyarat(mSelect : String; mParam : Array of Variant; Var mQuery : TAdoQuery) : Boolean;
Var
   I : Word;
Begin
  Result := False;
  With mQuery Do
  Begin
    Close;
    Sql.Clear;
    Sql.Add(mSelect);
        Prepared;
    For I := Low(mParam) to High(mParam) Do
        Parameters[i].Value := mParam[i];
    Open;
  end;
  Result := (mQuery.RecordCount>0);
end;

procedure DataBuka(mSelect : String; Var mQuery : TAdoQuery);
begin
  With mQuery Do
  Begin
    Close;
    SQL.Clear;
    SQL.Add(mSelect);
    Open;
  end;
end;

function CekPeriode(Nama:string;tgl:Tdatetime):Boolean;
begin
  if (StrToInt(PeriodBln)=MonthOf(tgl)) and (StrToInt(PeriodThn)=YearOf(tgl)) then
    CekPeriode:=True
  else CekPeriode:=False;
end;

function Romawi(Tanggal:Tdatetime;Mode:String):String;
var Year,Month,Day :Word;
    Isi:String;
begin
DecodeDate(Tanggal,year,month,day);
if Mode='BULAN' then
begin
    Case month of
      1:begin
           Romawi:='01';
        end;
      2:begin
           Romawi:='02';
        end;
      3:begin
           Romawi:='03';
        end;
      4:begin
           Romawi:='04';
        end;
      5:begin
           Romawi:='05';
        end;
      6:begin
           Romawi:='06';
        end;
      7:begin
           Romawi:='07';
        end;
      8:begin
           Romawi:='08';
        end;
      9:begin
           Romawi:='09';
        end;
     10:begin
           Romawi:='10';
        end;
     11:begin
           Romawi:='11';
        end;
     12:begin
           Romawi:='12';
        end;
    end;
end
else if Mode='TAHUN' then
begin
   isi:=InttoStr(Year);
   Romawi:=Isi;
end;
end;

function Daftar_Nomor(Tipe,xNoUrut,Nomor:String;Koreksi:integer):Boolean;
//var vLoop:boolean;
//    vNomor,s:string;
begin
  With Dm.QuCari do
  begin
    Close;
    Sql.Clear;
    sql.Add('Select Tipe,nourut,NoBukti,UserID From dbNomorPK where Tipe=:0 and Nobukti=:1');
    Prepared;
    Parameters[0].Value := Tipe;
    Parameters[1].Value := Nomor;
    Open;
  end;
  //VLoop := True;
  if DM.QuCari.IsEmpty then
  begin
    with Dm.QuKOde do
    begin
      close;
      sql.Clear;
      sql.Add('Insert into dbNomorPK values(:0,:1,:2,:3,:4,:5,:6,:7)');
      Prepared;
      Parameters[0].Value := Tipe;
      Parameters[1].Value := xNoUrut;
      Parameters[2].Value := Nomor;
      Parameters[3].Value := IDUser;
      Parameters[4].Value := StrToInt(PeriodBln);
      Parameters[5].Value := StrToInt(PeriodThn);
      Parameters[6].value := 0;
      Parameters[7].value := XnamaGudang;
      ExecSQL;
    end;
  end
  else
  begin
    MyUser := dm.QuCari.FieldByname('Userid').AsString;
  end;
  Result := DM.QuCari.IsEmpty or ((not DM.QuCari.IsEmpty) and (MyUser=IDUser));
end;

procedure Hapus_Daftar_Nomor(Tipe:String;NoBukti,pemakai:String);
begin
      // Hapus Nomor Yang ada di daftar (dbNOMORPK)
      with Dm.DaftarNO do
      begin
         Close;
         SQL.Clear;
         SQL.Add('Delete from dbNOMORPK');
         SQL.Add('Where tipe =:0 and NoBukti =:1 and UserID='+QuotedStr(pemakai));
         Prepared;
         Parameters[0].Value:=Tipe;
         Parameters[1].Value:=NoBukti;
         try
           ExecSQL;
         except
           ShowMessage('Hapus Daftar Nomor Gagal !');
         end;
      end;
end;

procedure Hapus_Daftar_Nomor_User(Tipe:String;Pemakai:String);
begin
      // Hapus Nomor Yang ada di daftar (dbNOMORPK)
      with Dm.DaftarNO do
      begin
         Close;
         SQL.Clear;
         SQL.Add('Delete from dbNOMORPK');
         SQL.Add('Where tipe =:0 and UserID =:1');
         Prepared;
         Parameters[0].Value:=Tipe;
         Parameters[1].Value:=Pemakai;
         try
           ExecSQL;
         except
           ShowMessage('Hapus Daftar Nomor Gagal !');
         end;
      end;
end;

procedure UpdateNomor(Bulan,tahun,Tipe:String;PPn:String;Nomor:String;Digit:integer);
var No,NoBaru,Code:Integer;
    StrNewNo:String;
begin
   Val(Nomor,NoBaru,Code);
   No:=0;
   with DM.QuNomor do
   begin
      Close;
      SQL.Clear;
      SQL.Add('Select *');
      SQL.Add('From dbNOMOR');
      SQL.Add('Where tipe =:0 and PPN =:1 and bulan=:2 and tahun=:3  ');
      SQL.Add('Order by Tipe');
      Prepared;
      Parameters[0].Value:=Tipe;
      Parameters[1].Value:=PPN;
      Parameters[2].Value:=Bulan;
      Parameters[3].Value:=tahun;
      Open;
   end;
   if Code=0 then
   begin
      if DM.QuNomor.RecordCount > 0 then
      begin
         No:=Strtoint(DM.QuNomor.fieldbyname('Nomor').AsString);
      end;

      if NO <= NoBaru then
      begin
         inc(NoBaru);
         StrNewNO:=NewNo(inttoStr(Nobaru),Digit);
         // Update Nomor di DBNOMOR
         with Dm.DaftarNO do
         begin
           Close;
           SQL.Clear;
           SQL.Add('Update dbNOMOR');
           SQL.Add('Set Nomor =:0');
           SQL.Add('Where (Tipe =:1) and (PPn =:2) and bulan=:3 and tahun=:4  ');
           Prepared;
           Parameters[0].Value:=StrNewNO;
           Parameters[1].Value:=Tipe;
           Parameters[2].Value:=PPn;
           Parameters[3].Value:=Bulan;
           Parameters[4].Value:=tahun;
           ExecSQL;
         end;
      end;
   end;
end;



procedure FindDuaKategori(Query:TADOQuery;Field1:String;Field2:String;Tabel:String;Data:String);
begin
   with Query do
   begin
      Close;
      SQL.Clear;
      SQL.Add('Select * from '+Tabel);
      SQL.Add('Where '+Field1+' like :0 or '+Field2+' like :1 ');
      Prepared;
      Parameters[0].Value:=Data+'%';
      Parameters[1].Value:=Data+'%';
      Open;
   end;
end;


function MyFindField(Tabel:String;Field:String;Data:String):boolean;
begin
   with DM.QuCari do
   begin
      Close;
      SQL.Clear;
      SQL.Add('Select * from '+Tabel);
      SQL.Add('Where '+Field+' =:0');
      Prepared;
      Parameters[0].Value:=Data;
      Open;
   end;
   if DM.QuCari.RecordCount > 0 then
   begin
      MyFindField:=true;
      Kode:=DM.QuCari.Fields[0].AsString;
      Nama:=DM.QuCari.Fields[1].AsString;
   end
   else
   begin
      MyFindField:=false;
      Kode:='';
      Nama:='';
   end;
end;

function MyCek_Lock_Periode(Bukti:string):Boolean;
begin
//   Konfig('SPP','00000',F1,F2,F3,F4);
   with dm.QuCari do
   begin
      Close;
      SQL.Clear;
      SQL.Add('Select * from dbLockPeriode');
      SQL.Add('Where Bulan=:0 and Tahun=:1');
      Prepared;
      Parameters[0].Value:=StrToInt(PeriodBln);
      Parameters[1].Value:=StrToInt(PeriodThn);
      Open;
   end;
   if DM.QuCari.RecordCount > 0 then
   begin
      MyCek_Lock_Periode:= true;
   end
   else
      MyCek_Lock_Periode:=false;
end;

function IsLockPeriode(Bulan,Tahun:Integer):Boolean;
var Bln,Thn :string;
begin
   if bulan < 10 then
      Bln:='0'+inttostr(bulan)
   else
      Bln:=inttostr(bulan);
   thn:=inttostr(tahun);
   with dm.QuCari do
   begin
      Close;
      SQL.Clear;
      SQL.Add('Select * from dbLockPeriode');
      SQL.Add('Where Bulan=:0 and Tahun=:1');
      Prepared;
      Parameters[0].Value:=bln;
      Parameters[1].Value:=Thn;
      Open;
   end;
   Result := DM.QuCari.IsEmpty;
end;

function IsLockPeriodeNK(Bulan,Tahun:Integer):Boolean;
var Bln,Thn :string;
begin
   if bulan < 10 then
      Bln:='0'+inttostr(bulan)
   else
      Bln:=inttostr(bulan);
   thn:=inttostr(tahun);
   with dm.QuCari do
   begin
      Close;
      SQL.Clear;
      SQL.Add('Select * from dbLockPeriode');
      SQL.Add('Where NKBulan=:0 and NKTahun=:1');
      Prepared;
      Parameters[0].Value:=bln;
      Parameters[1].Value:=Thn;
      Open;
   end;
   Result := DM.QuCari.IsEmpty;
end;


function MyCariUserName(Nama : string; Kunci : String; var Tstatus, Tlevel: integer):Boolean;
begin
  Kunci:=Encrypt(Kunci,StartKey,MultKey,AddKey);
  with dm.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('Select a.* from dbFlpass a');
    //SQL.Add('Where UserID=:0 and UID=:1 ');
    sql.Add(' left outer join dbkaryawan b on b.keynik=a.keynik') ;
    SQL.Add('Where UserID=:0 and UID=:1 /*and aktif=1 */');
    Prepared;
    Parameters[0].Value:=Nama;
    Parameters[1].Value:=Kunci;
    Open;
  end;
  Result := not DM.QuCari.IsEmpty;
  TLevel:=DM.QuCari.fieldbyName('Tingkat').AsInteger;
  Tstatus:=DM.QuCari.fieldbyname('status').AsInteger;
end;

function MyCariPeriode(Nama:string):Boolean;
begin
   with dm.QuCari do
   begin
      Close;
      SQL.Clear;
      SQL.Add('Select * from dbPeriode');
      SQL.Add('Where Userid=:0');
      Prepared;
      Parameters[0].Value:=Nama;
      Open;
   end;
   if not DM.QuCari.IsEmpty then
   begin
      Result:= not DM.QuCari.IsEmpty;
      PeriodBln:=DM.QuCari.fieldbyName('Bulan').AsString;
      PeriodThn:=DM.QuCari.fieldbyName('Tahun').AsString;
   end
   else
      Result:=false;
end;

function MyAktifTgl(T:TdateTime;Nama:String):TdateTime;
var year,month,day :word;
begin
   DecodeDate(T,year,month,day);
   if MyCariPeriode(Nama)=true then
   begin
      MyAktifTgl:=EncodeDate(Strtoint(PeriodThn),StrtoInt(PeriodBln),day);
   end
   else
      MyAktifTgl:=Now;
end;

procedure Delay(Lama : LongWord);
var Ref : LongWord;
begin
   Ref := GetTickCount;
   repeat
     Application.ProcessMessages;
   until ((GetTickCount-Ref)>=Lama);
end;

procedure MainCetak(TFRXDB1,TFRXDB2:TfrxDBDataset;Q1,Q2:TADOQuery;SP1,SP2,NOB,Fr3:String;frx:TfrxReport);
begin
  TFRXDB1.DataSet := Q1;
  with Q1 do
  begin
    Close;Sql.Clear;Sql.Add('exec '+SP1+' '+QuotedStr(NOB));
    Open;
  end;
  TFRXDB1.Close;TFRXDB1.Open;
  TFRXDB2.DataSet := Q2;
  With Q2 do
  begin
    close;sql.clear;sql.add('Select * From '+Sp2);Open;
  end;
  TFRXDB2.Close;TFRXDB2.Open;Frx.LoadFromFile(currDir+Fr3);
  frx.ShowReport;
end  ;


procedure MainCetak1(TFRXDB1,TFRXDB2:TfrxDBDataset;Q1,Q2:TADOQuery;SP1,SP2,NOB,
  Fr3:String;frx:TfrxReport);
begin
  TFRXDB1.DataSet := Q1;
  with Q1 do
  begin
    Close;Sql.Clear;Sql.Add('exec '+SP1+' '+QuotedStr(NOB));
    Open;
  end;
  TFRXDB1.Close;TFRXDB1.Open;
  TFRXDB2.DataSet := Q2;
  With Q2 do
  begin
    close;sql.clear;sql.add('Select * From '+Sp2+' where nobukti = '+QuotedStr(NOB));
    Open;
  end;
  TFRXDB2.Close;TFRXDB2.Open;Frx.LoadFromFile(currDir+Fr3);
  frx.ShowReport;
end  ;


procedure NilaiCetak(Fie,Tbl,Param:String);
var CetakKe:Integer;
begin
 DataBersyarat('Select Isnull('+Fie+',0)+1 cetakke from '+Tbl+' where nobukti=:0',[Param], Dm.Qucari);
 cetakke:=Dm.Qucari.Fieldbyname('Cetakke').AsInteger;
 with dm.qucari do
  begin
    close;
    sql.Clear;
    Sql.add('Update '+Tbl+' set '+Fie+'=:0 where Nobukti='+Quotedstr(Param));
    Parameters[0].Value:=cetakke;
    Prepared;
    ExecSql;
  end;
end;

procedure pembatalan(xtable,xfield,xnobukti,xuser,xkodemenu:String);
var Batal :Boolean;
begin
DataBersyarat('Select A.pembatalan from dbflmenu A  left Outer join dbMenu B on B.Kodemenu=A.L1 '+
                ' where A.userid=:0 and A.L1=:0 and Pembatalan=1',[xuser,XKodeMenu],Dm.QuCari) ;
if Dm.QuCari.FieldByName('Pembatalan').AsBoolean=true then
   begin
         with Dm.QuCari do
         begin
            DisableEnableTrigger(False,xtable);
            Close;
            Sql.Clear;
            if Xbatal then
            Sql.Add('Update '+xtable+' set '+xfield+'=1,UserBatal='+QuotedStr(xuser)+',TglBatal=getdate() where Nobukti='+QuotedStr(xnobukti)+'')
            Else  Sql.Add('Update '+xtable+' set '+xfield+'=0,UserBatal='''',TglBatal=Null where Nobukti='+QuotedStr(xnobukti)+'');
            ExecSQL;
            DisableEnableTrigger(True,xtable);
         end ;
   end
 else ShowMessage('Anda Tidak Punya Acces Pembatalan')
end;

procedure GetBrowse(XkodeBrowse:Integer;XDb,Xkode,Xname:String;xform:TForm;xEdit:TEdit;xlabel:Tlabel);
begin
    if Length(xEdit.Text)=0 then
       begin
        Application.CreateForm(TFrBrows, FrBrows);
        kodeBrows:=XkodeBrowse;
        FrBrows.Showmodal;
        if FrBrows.ModalResult=mrOk then
         begin
           xEdit.Text:=FrBrows.QuBrows.fieldbyname(Xkode).AsString;
           xlabel.Caption:=''+FrBrows.QuBrows.fieldbyname(Xname).AsString+'';
         end
         else
            xform.ActiveControl:=xEdit;
       end
    else
       begin
         if MyFindField(XDb,Xkode,xEdit.Text)=true then
           begin
               xEdit.Text:=DM.QuCari.fieldbyname(Xkode).AsString;
               xlabel.Caption:=''+DM.QuCari.fieldbyname(Xname).AsString+'';
           end
           else
           begin
              KodeBrows:=XkodeBrowse;
              Application.CreateForm(TFrBrows, FrBrows);
              FrBrows.Showmodal;
              if FrBrows.ModalResult=mrOk then
              begin
                 xEdit.Text:=FrBrows.QuBrows.fieldbyname(Xkode).AsString;
                 xlabel.Caption:=''+FrBrows.QuBrows.fieldbyname(Xname).AsString+'';
               end
              else
                 xform.ActiveControl:=xEdit;
           end;
       end;
 end;
 procedure CekNota(Fie,Tbl,Param:String);
 begin
  DataBersyarat('Select Isnull('+Fie+',0) cetakke from '+Tbl+' where nobukti=:0',[Param], Dm.Qucari);
    if Dm.Qucari.FieldByname('Cetakke').asinteger>0 then Scetak:=true
    else Scetak:=False;
    Scetak:=False;
 End;

 procedure VlsIDR(XEdit:TEDIT;XNumEdit:TPBNumEDIT);
 Begin
 if XEdit.Text='IDR' then
  begin
  XNumEdit.Value:=1;
  XNumEdit.Enabled:=False;
  End
 else XNumEdit.Enabled:=True;

 End;

 procedure pembatalanPO(xtable,xfield,xnobukti,xkodebrg,xqnt,xuser,xkodemenu:String;QntBatal,Xurut:Integer);
var Batal :Boolean;
begin
DataBersyarat('Select A.pembatalan from dbflmenu A  left Outer join dbMenu B on B.Kodemenu=A.L1 '+
                ' where A.userid=:0 and A.L1=:0 and Pembatalan=1',[xuser,XKodeMenu],Dm.QuCari) ;
if Dm.QuCari.FieldByName('Pembatalan').AsBoolean=true then
   begin
         with Dm.QuCari do
         begin
            DisableEnableTrigger(False,xtable);
            Close;
            Sql.Clear;
            if Xbatal then
            Sql.Add('Update '+xtable+' set '+xfield+'='+IntToStr(Qntbatal)+',UserBatal='+QuotedStr(xuser)+' , '+
              'TglBatal=getdate(),isbatal=1 where Nobukti='+QuotedStr(xnobukti)+' ' +
              'and kodebrg='+quotedstr(xkodebrg)+' and Urut='+IntToStr(Xurut)+'')
            Else  Sql.Add('Update '+xtable+' set '+xfield+'=0,isbatal=0,UserBatal='''',TglBatal=Null where Nobukti='+QuotedStr(xnobukti)+' '+
                          ' and KodeBrg='+quotedstr(xkodebrg)+' and Urut='+IntToStr(Xurut)+' ');
            ExecSQL;
            DisableEnableTrigger(True,xtable);
         end ;
   end
 else ShowMessage('Anda Tidak Punya Acces Pemabatalan')
end;
function GetImageLinkTimeStamp(const FileName: string): DWORD;
const
  INVALID_SET_FILE_POINTER = DWORD(-1);
  BorlandMagicTimeStamp = $2A425E19;  // Delphi 4-6 (and above?)
  FileTime1970: TFileTime = (dwLowDateTime:$D53E8000; dwHighDateTime:$019DB1DE);
type
  PImageSectionHeaders = ^TImageSectionHeaders;
  TImageSectionHeaders = array [Word] of TImageSectionHeader;
type
  PImageResourceDirectory = ^TImageResourceDirectory;
  TImageResourceDirectory = packed record
    Characteristics: DWORD;
    TimeDateStamp: DWORD;
    MajorVersion: Word;
    MinorVersion: Word;
    NumberOfNamedEntries: Word;
    NumberOfIdEntries: Word;
  end;
var
  FileHandle: THandle;
  BytesRead: DWORD;
  ImageDosHeader: TImageDosHeader;
  ImageNtHeaders: TImageNtHeaders;
  SectionHeaders: PImageSectionHeaders;
  Section: Word;
  ResDirRVA: DWORD;
  ResDirSize: DWORD;
  ResDirRaw: DWORD;
  ResDirTable: TImageResourceDirectory;
  FileTime: TFileTime;
begin
  Result := 0;
  // Open file for read access
  FileHandle := CreateFile(PChar(FileName), GENERIC_READ, FILE_SHARE_READ, nil,
    OPEN_EXISTING, 0, 0);
  if (FileHandle <> INVALID_HANDLE_VALUE) then
  try
    // Read MS-DOS header to get the offset of the PE32 header
    // (not required on WinNT based systems - but mostly available)
    if not ReadFile(FileHandle, ImageDosHeader, SizeOf(TImageDosHeader),
      BytesRead, nil) or (BytesRead <> SizeOf(TImageDosHeader)) or
      (ImageDosHeader.e_magic <> IMAGE_DOS_SIGNATURE) then
    begin
      ImageDosHeader._lfanew := 0;
    end;
    // Read PE32 header (including optional header
    if (SetFilePointer(FileHandle, ImageDosHeader._lfanew, nil, FILE_BEGIN) =
      INVALID_SET_FILE_POINTER) then
    begin
      Exit;
    end;
    if not(ReadFile(FileHandle, ImageNtHeaders, SizeOf(TImageNtHeaders),
      BytesRead, nil) and (BytesRead = SizeOf(TImageNtHeaders))) then
    begin
      Exit;
    end;
    // Validate PE32 image header
    if (ImageNtHeaders.Signature <> IMAGE_NT_SIGNATURE) then
    begin
      Exit;
    end;
    // Seconds since 1970 (UTC)
    Result := ImageNtHeaders.FileHeader.TimeDateStamp;

    // Check for Borland's magic value for the link time stamp
    // (we take the time stamp from the resource directory table)
    if (ImageNtHeaders.FileHeader.TimeDateStamp = BorlandMagicTimeStamp) then
    with ImageNtHeaders, FileHeader, OptionalHeader do
    begin
      // Validate Optional header
      if (SizeOfOptionalHeader < IMAGE_SIZEOF_NT_OPTIONAL_HEADER) or
        (Magic <> IMAGE_NT_OPTIONAL_HDR_MAGIC) then
      begin
        Exit;
      end;
      // Read section headers
      SectionHeaders :=
        GetMemory(NumberOfSections * SizeOf(TImageSectionHeader));
      if Assigned(SectionHeaders) then
      try
        if (SetFilePointer(FileHandle,
          SizeOfOptionalHeader - IMAGE_SIZEOF_NT_OPTIONAL_HEADER, nil,
          FILE_CURRENT) = INVALID_SET_FILE_POINTER) then
        begin
          Exit;
        end;
        if not(ReadFile(FileHandle, SectionHeaders^, NumberOfSections *
          SizeOf(TImageSectionHeader), BytesRead, nil) and (BytesRead =
          NumberOfSections * SizeOf(TImageSectionHeader))) then
        begin
          Exit;
        end;
        // Get RVA and size of the resource directory
        with DataDirectory[IMAGE_DIRECTORY_ENTRY_RESOURCE] do
        begin
          ResDirRVA := VirtualAddress;
          ResDirSize := Size;
        end;
        // Search for section which contains the resource directory
        ResDirRaw := 0;
        for Section := 0 to NumberOfSections - 1 do
        with SectionHeaders[Section] do
          if (VirtualAddress <= ResDirRVA) and
            (VirtualAddress + SizeOfRawData >= ResDirRVA + ResDirSize) then
          begin
            ResDirRaw := PointerToRawData - (VirtualAddress - ResDirRVA);
            Break;
          end;
        // Resource directory table found?
        if (ResDirRaw = 0) then
        begin
          Exit;
        end;
        // Read resource directory table
        if (SetFilePointer(FileHandle, ResDirRaw, nil, FILE_BEGIN) =
          INVALID_SET_FILE_POINTER) then
        begin
          Exit;
        end;
        if not(ReadFile(FileHandle, ResDirTable,
          SizeOf(TImageResourceDirectory), BytesRead, nil) and
          (BytesRead = SizeOf(TImageResourceDirectory))) then
        begin
          Exit;
        end;
        // Convert from DosDateTime to SecondsSince1970
        if DosDateTimeToFileTime(HiWord(ResDirTable.TimeDateStamp),
          LoWord(ResDirTable.TimeDateStamp), FileTime) then
        begin
          // FIXME: Borland's linker uses the local system time
          // of the user who linked the executable image file.
          // (is that information anywhere?)
          Result := (ULARGE_INTEGER(FileTime).QuadPart -
            ULARGE_INTEGER(FileTime1970).QuadPart) div 10000000;
        end;
      finally
        FreeMemory(SectionHeaders);
      end;
    end;
  finally
    CloseHandle(FileHandle);
  end;
end;


function UnixTimeToDateTime(UnixTime: DWORD): TDateTime;
begin
  Result := EncodeDate(1970, 1, 1) + UnixTime / 86400;
end;

end.

