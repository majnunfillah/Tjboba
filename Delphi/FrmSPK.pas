unit FrmSPK;

interface

uses
  Windows, Messages, SysUtils, Classes, Graphics, Controls, Forms, Dialogs,
  PBNumEdit, StdCtrls, Mask, ToolEdit, ExtCtrls, Buttons, Grids, DBGrids,
  SpeedBar, ComCtrls, RXCtrls, ADODB, Db, DBCtrls, ImgList, dxDBTLCl, dxGrClms,
  dxTL, dxDBCtrl, dxDBGrid, dxCntner, variants, dxEditor, dxExEdtr, dxEdLib,
  Dateutils, raCodMod, frxClass, frxDMPExport, frxDBSet, frxDesgn, MyGlobal, strUtils;

type
  TFrSPK = class(TForm)
    Panel1: TPanel;
    Panel3: TPanel;
    QuBeli: TADOQuery;
    Sp_Beli: TADOStoredProc;
    DsQuBeli: TDataSource;
    QuUpdatedbbeli: TADOQuery;
    Label10: TLabel;
    DataSource1: TDataSource;
    QuReport: TADOQuery;
    BitBtn1: TBitBtn;
    Panel2: TPanel;
    Panel4: TPanel;
    SpeedButton1: TSpeedButton;
    dxDBGrid1: TdxDBGrid;
    PanelShow1: TPanel;
    Cetak: TSpeedButton;
    HapusBtn: TSpeedButton;
    KoreksiBtn: TSpeedButton;
    TambahBtn: TSpeedButton;
    frxDataPerusahaan: TfrxDBDataset;
    frxDBDataset1: TfrxDBDataset;
    frxDotMatrixExport1: TfrxDotMatrixExport;
    SpeedButton8: TSpeedButton;
    frxReport1: TfrxReport;
    frxDesigner1: TfrxDesigner;
    dxDBGrid1KodeBrg: TdxDBGridMaskColumn;
    dxDBGrid1NamaBrg: TdxDBGridMaskColumn;
    dxDBGrid1Satuan: TdxDBGridMaskColumn;
    dxDBGrid1Qnt: TdxDBGridMaskColumn;
    QuByAngkut: TADOQuery;
    Label26: TLabel;
    Label42: TLabel;
    Shape4: TShape;
    Label12: TLabel;
    Label2: TLabel;
    NamaBrg: TRxLabel;
    NoUrut: TEdit;
    Tanggal: TDateEdit;
    NoBukti: TEdit;
    KdBrg: TEdit;
    LNamaBrg: TRxLabel;
    Label1: TLabel;
    Label4: TLabel;
    Qnt: TPBNumEdit;
    KodeBrg: TEdit;
    Label19: TLabel;
    NoSat: TPBNumEdit;
    LNamaSat: TRxLabel;
    Label3: TLabel;
    QntJ: TPBNumEdit;
    Label5: TLabel;
    NoSatJ: TPBNumEdit;
    NmSat: TRxLabel;
    CSPK: TCheckBox;
    QuBeliNoBukti: TStringField;
    QuBeliBrgJ: TStringField;
    QuBeliQntJ: TBCDField;
    QuBeliNosatJ: TWordField;
    QuBeliIsiJ: TBCDField;
    QuBeliSatJ: TStringField;
    QuBeliKodeBrg: TStringField;
    QuBeliQnt: TBCDField;
    QuBeliSatuan: TStringField;
    QuBeliisi: TBCDField;
    QuBeliNosat: TWordField;
    QuBeliNamaBrgJ: TStringField;
    QuBeliNamaBrg: TStringField;
    QuBeliurut: TIntegerField;
    QuBelitanggal: TDateTimeField;
    NoBatch: TEdit;
    Label6: TLabel;
    TglExpired: TDateEdit;
    Label7: TLabel;
    QuBeliNoBatch: TStringField;
    QuBeliTglExpired: TDateTimeField;
    KodeBOM: TEdit;
    Label8: TLabel;
    QuBeliKodeBOM: TStringField;
    Button1: TButton;
    QntBOMX: TPBNumEdit;
    Label9: TLabel;
    KodeBOMDet: TEdit;
    Label11: TLabel;
    IntLevelBOM: TPBNumEdit;
    StrLevelBOM: TEdit;
    Label13: TLabel;
    NoSO: TEdit;
    Label14: TLabel;
    UrutSO: TEdit;
    QntSO: TPBNumEdit;
    Label15: TLabel;
    Panel7: TPanel;
    Panel10: TPanel;
    SpeedButton7: TSpeedButton;
    Label16: TLabel;
    NmMsn: TLabel;
    Label17: TLabel;
    Label18: TLabel;
    Label20: TLabel;
    Label21: TLabel;
    Label22: TLabel;
    BitBtn2: TBitBtn;
    kodeMSn: TEdit;
    Ket: TEdit;
    TanggalPrs: TDateEdit;
    JamAwl: TMaskEdit;
    JamAkhir: TMaskEdit;
    QntSPK: TPBNumEdit;
    Panel8: TPanel;
    Panel9: TPanel;
    Panel11: TPanel;
    SpeedButton2: TSpeedButton;
    SpeedButton3: TSpeedButton;
    SpeedButton4: TSpeedButton;
    SpeedButton5: TSpeedButton;
    SpeedButton6: TSpeedButton;
    dxDBGrid3: TdxDBGrid;
    dxDBGrid3NOSPK: TdxDBGridMaskColumn;
    dxDBGrid3KodePrs: TdxDBGridMaskColumn;
    dxDBGrid3Urut: TdxDBGridMaskColumn;
    dxDBGrid3KODEMSN: TdxDBGridMaskColumn;
    dxDBGrid3TANGGAL: TdxDBGridDateColumn;
    dxDBGrid3JAMAWAL: TdxDBGridDateColumn;
    dxDBGrid3JAMAKHIR: TdxDBGridDateColumn;
    dxDBGrid3QNTSPK: TdxDBGridMaskColumn;
    QuMesin: TADOQuery;
    QuMesinNOSPK: TStringField;
    QuMesinKodePrs: TStringField;
    QuMesinUrut: TIntegerField;
    QuMesinKODEMSN: TStringField;
    QuMesinTANGGAL: TDateTimeField;
    QuMesinJAMAWAL: TDateTimeField;
    QuMesinJAMAKHIR: TDateTimeField;
    QuMesinQNTSPK: TBCDField;
    QuMesinKeterangan: TStringField;
    QuMesinNoBatch: TStringField;
    QuMesinTglExpired: TDateTimeField;
    QuMesinNoUrut: TStringField;
    QuMesinBrgJ: TStringField;
    QuMesinQntJ: TBCDField;
    QuMesinNosatJ: TWordField;
    QuMesinIsiJ: TBCDField;
    QuMesinSatJ: TStringField;
    QuMesinKodeBOM: TStringField;
    QuMesinTanggalBukti: TDateTimeField;
    DsQuMesin: TDataSource;
    Sp_Prs: TADOStoredProc;
    QuPrs: TADOQuery;
    QuPrsKodeBOM: TStringField;
    QuPrsUrut: TIntegerField;
    QuPrsKodePrs: TStringField;
    QuPrsKeterangan: TStringField;
    DsQuPrs: TDataSource;
    QuMesinket: TStringField;
    dxDBGrid3Column9: TdxDBGridColumn;
    BiayaL: TPBNumEdit;
    Label23: TLabel;
    QuBelibiayalain: TBCDField;
    JamTenaker: TPBNumEdit;
    Label24: TLabel;
    Label25: TLabel;
    JmlTenaker: TPBNumEdit;
    Label27: TLabel;
    TarifTenaker: TPBNumEdit;
    TrfMesin: TPBNumEdit;
    Label28: TLabel;
    QuMesinTarifMesin: TBCDField;
    QuMesinJamTenaker: TBCDField;
    QuMesinJmlTenaker: TBCDField;
    QuMesinTarifTenaker: TBCDField;
    Label29: TLabel;
    LTK1: TRxLabel;
    TrfTenaker: TEdit;
    QuBeliKodeBOMDet: TStringField;
    dxDBGrid1Column5: TdxDBGridColumn;
    QuBelinourut: TStringField;
    Label30: TLabel;
    TglSelesai: TDateEdit;
    Label31: TLabel;
    TglJTSO: TDateEdit;
    QuBelitglselesai: TDateTimeField;
    QuBelinoso: TStringField;
    QuBeliurutso: TIntegerField;
    Qureport2: TADOQuery;
    frxDBDataset2: TfrxDBDataset;
    dxDBGrid3Column10: TdxDBGridColumn;
    Label32: TLabel;
    JmlCetak: TPBNumEdit;
    Label44: TLabel;
    Jenis: TComboBox;
    QuBeliQntCetak: TBCDField;
    QuBeliJenisSpk: TWordField;
    procedure FormClose(Sender: TObject; var Action: TCloseAction);
    procedure FormKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure FormShow(Sender: TObject);
    procedure TampilData(No_Bukti:String);
    procedure UpdateDataBeli(Choice:Char;Kodet:String);
    procedure FormCreate(Sender: TObject);
    procedure TambahBtnClick(Sender: TObject);
    procedure ClearPanelDetail;
    procedure RefreshAll;
    procedure QntKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure TanggalKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure UpdatedbBeli;
    procedure KoreksiBtnClick(Sender: TObject);
    procedure wwDBGrid1KeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure HapusBtnClick(Sender: TObject);
    procedure wwDBGrid1Enter(Sender: TObject);
    procedure wwDBGrid1Exit(Sender: TObject);
    procedure wwDBGrid1DblClick(Sender: TObject);
    procedure NoUrutKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure TanggalEnter(Sender: TObject);
    procedure TanggalExit(Sender: TObject);
    procedure KodeSuppEnter(Sender: TObject);
    procedure CetakClick(Sender: TObject);
    Function CheckFormMaster:boolean;
    procedure BitBtn1Click(Sender: TObject);
    procedure SpeedButton1Click(Sender: TObject);
    procedure NoDOKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure FormDestroy(Sender: TObject);
    procedure KodeGdgEnter(Sender: TObject);
    procedure dxDBGrid1CustomDrawCell(Sender: TObject; ACanvas: TCanvas;
      ARect: TRect; ANode: TdxTreeListNode; AColumn: TdxTreeListColumn;
      ASelected, AFocused, ANewItemRow: Boolean; var AText: String;
      var AColor: TColor; AFont: TFont; var AAlignment: TAlignment;
      var ADone: Boolean);
    procedure BPrevClick(Sender: TObject);
    procedure BNextClick(Sender: TObject);
    procedure KodeBrgKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure SpeedButton8Click(Sender: TObject);
    procedure KdBrgEnter(Sender: TObject);
    procedure KdBrgExit(Sender: TObject);
    procedure TanggalChange(Sender: TObject);
    procedure NoUrutEnter(Sender: TObject);
    procedure KodeBrgEnter(Sender: TObject);
    procedure KodeBrgExit(Sender: TObject);
    procedure frxReport1GetValue(const VarName: String;
      var Value: Variant);
    procedure frxReport1AfterPrintReport(Sender: TObject);
    procedure NoUrutExit(Sender: TObject);
    procedure NoSatJExit(Sender: TObject);
    procedure KodeBOMExit(Sender: TObject);
    procedure Button1Click(Sender: TObject);
    procedure NoSOExit(Sender: TObject);
    procedure QntJExit(Sender: TObject);
    procedure SpeedButton5Click(Sender: TObject);
    procedure SpeedButton4Click(Sender: TObject);
    procedure SpeedButton3Click(Sender: TObject);
    procedure BitBtn2Click(Sender: TObject);
    procedure SpeedButton7Click(Sender: TObject);
    procedure kodeMSnExit(Sender: TObject);
    procedure kodeMSnEnter(Sender: TObject);
    procedure JamTenakerExit(Sender: TObject);
    procedure TrfTenakerExit(Sender: TObject);
    procedure TrfTenakerEnter(Sender: TObject);

  private
    { Private declarations }
    mSat_1, mSat_2, mStrMsg: String;
    mUbahHd, mUbahHdDet: Boolean;
    mNamaSatuan: TNamaSatuanBrg;
    mIsiSatuan: TIsiSatuanBrg;
    mNamaSatuanJ: TNamaSatuanBrg;
    mIsiSatuanJ: TIsiSatuanBrg;
    procedure TampilTombolDetail(pNilai: Boolean);
    function  CekAdaSuratJln(pNOSO: String): Boolean;
    function  CekBahan(pNOSO: String): Boolean;
    procedure TampilIsiBrgJadi;
    procedure TampilIsiBarang;
    procedure IsiNoBuktiBaru;
    procedure IsiSatuan;
    procedure IsiSatuanBJ;
    procedure IsiSatuanJ;
    Procedure AmBilDataBeli;
    procedure TampilIsiGudang;
    procedure IsiBahanDariBOM;
    procedure IsiBahanDariCekBOM;
    procedure TampilNoSO;
     function CekJadwalProduksi : Boolean;
    Procedure AmBilDataSO;
    procedure TampilTombolDetailPrs(pNilai: Boolean);
    procedure ClearPanelDetailPrs;
    procedure TampilIsiMesin;
    procedure UpdateDataBeliPrs(Choice:Char;Kodet:String);
    procedure TampilDataMesin;
    procedure TampilISiKaryawan;
  public
    { Public declarations }
    IsTambah,IsKoreksi,IsHapus, IsCetak, IsExcel,xModalKoreksi: Boolean;
    yy,mm,dd : word;
    sishal, tothal,trfmsn : integer ;


  end;
const
     LoC = LoCaseInsensitive;
     LoP = LoPartialKey;

var
  FrSPK: TFrSPK;
  mValid,mExit,mMasterKoreksi,xIsPpn: Boolean;
  Sat:String;
  mUrut,mUrutPO,IsTutup,mNosat,mPosisi,mUrutRO:Integer;
  TipeTrans,PlusPPN,Nomor,Model:String;
  mBulan,mTahun:string;
  S:array[0..255]of char;
  BM : TBookmark;

implementation
uses FrmBrows, MyProcedure, MyModul, FrmMainSPK, FrmTempStockBOM;
{$R *.DFM}

// PROCEDURE DAN FUNCTION PRIBADI

procedure TFrSPK.TampilTombolDetail(pNilai: Boolean);
begin
  //PanelShow1.Visible:=not pNilai;
  //PanelShow2.Visible:=not pNilai;
  Panel3.Visible:=pNilai;
  Panel1.Enabled:=not pNilai;
  Panel2.Enabled:=not pNilai;
  Panel3.Enabled:=pNilai;
end;

procedure TFrSPK.ClearPanelDetailPrs;
var i:integer;
begin
   for i:= 0 to Panel10.ControlCount-1 do
   begin
      if (Panel10.Controls[i] is TEdit) then
         (Panel10.Controls[i] as TEdit).Clear;
      if (Panel10.Controls[i] is TPBNumEdit) then
         (Panel10.Controls[i] as TPBNumEdit).Value:=0;
      if (Panel10.Controls[i] is TCheckBox) then
         (Panel10.Controls[i] as TCheckBox).Checked:=False;
      if (Panel10.Controls[i] is TMaskEdit) then
         (Panel10.Controls[i] as TMaskEdit).Clear;
   end;
   LTK1.Caption:='[....]'
end;

procedure TFrSPK.TampilTombolDetailPrs(pNilai: Boolean);
begin
  //PanelShow1.Visible:=not pNilai;
  //PanelShow2.Visible:=not pNilai;
  Panel10.Visible:=pNilai;
  Panel1.Enabled:=not pNilai;
  Panel2.Enabled:=not pNilai;
  Panel10.Enabled:=pNilai;
end;

procedure TFrSPK.TampilNoSO;
begin
  KodeBrows:=2420114;
  Application.CreateForm(TFrBrows, FrBrows);
  FrBrows.IsiData:=KdBrg.Text;
  FrBrows.ShowModal;
  if FrBrows.ModalResult=mrOk then
  begin
    with FrBrows.QuBrows do
    begin
      NoSO.Text:=fieldbyname('NoBukti').AsString;
    end;
  end else
    ActiveControl:=NoSO;
end;

procedure TFrSPK.TampilIsiBrgJadi;
begin
  KodeBrows:=2420111;
  Application.CreateForm(TFrBrows, FrBrows);
  FrBrows.IsiData:=KdBrg.Text;
  FrBrows.ShowModal;
  if FrBrows.ModalResult=mrOk then
  begin
    with FrBrows.QuBrows do
    begin
      KdBrg.Text:=fieldbyname('KodeBrg').AsString;
      NamaBrg.Caption:='[..'+fieldbyname('NamaBrg').AsString+'..]';
    end;
  end else
    ActiveControl:=KdBrg;
end;

procedure TFrSPK.TampilIsiBarang;
begin
  KodeBrows:=24201122;
  Application.CreateForm(TFrBrows, FrBrows);
  FrBrows.IsiData:='%'+KodeBrg.Text+'%';
  FrBrows.EditFilter.Text:=KodeBrg.Text;
  FrBrows.ShowModal;
  if (FrBrows.ModalResult=mrOk) and (FrBrows.QuBrows.IsEmpty=False) then
  begin
    with FrBrows.QuBrows do
    begin
      KodeBrg.Text:=fieldbyname('KodeBrg').AsString;
      LNamaBrg.Caption:='[ '+fieldbyname('NamaBrg').AsString+' ]';
      Qnt.AsFloat:=fieldbyname('QntSaldo').AsFloat;
      ActiveControl:=Qnt;
    end;
  end
  else
    ActiveControl:=KodeBrg;
end;



procedure TFrSPK.IsiNoBuktiBaru;
begin
  DecodeDate(Date,YY,MM,DD);
  //NomorBukti:='';
  If DayOfTheMonth(Date)<=DD then
     DD:=DayOfTheMonth(date);
  Try
    TANGGAL.Date := EncodeDate(StrToInt(PeriodThn),StrToInt(PeriodBln),DD);
  Except
    TANGGAL.Date := EndOfAMonth(StrToInt(PeriodThn),StrToInt(PeriodBln))
  end;
  nomor:='';nomorbukti:='';
  Check_Nomor(StrToInt(PeriodBln),StrToInt(PeriodThn),TipeTrans,Nomor,NomorBukti,Tanggal.Date);
  NoUrut.Text := NomorBukti;
  NOBUKTI.Text := Nomor;
end;

procedure TFrSPK.TampilData(No_Bukti:String);
begin
  QuBeli.Close;
  QuBeli.SQL.Strings[2]:='select @NoBukti='+QuotedStr(No_Bukti);
  QuBeli.Open;
  //KdBrg.Enabled:=QuBeli.IsEmpty;
  KodeBOM.Enabled:=QuBeli.IsEmpty;
  if QuBeli.IsEmpty=false then
  begin
      dxDBGrid1.OptionsDB:=[edgoCanNavigation,edgoLoadAllRecords,edgoUseBookmarks]
  end
  else
  begin
      dxDBGrid1.OptionsDB:=[edgoCanNavigation,edgoUseBookmarks];
  end;
end;

Function TFrSPK.CheckFormMaster:boolean;
begin
   CheckFormMaster:=true;
end;

procedure TFrSPK.UpdatedbBeli;
begin
  if (not QuBeli.IsEmpty) and (mUbahHd=True)and(FrMainSPK.ModalKoreksi) then
  begin
    with QuUpdatedbBeli do
    begin
       Close;
       Sql.Clear;
       SQL.Add('Update dbSPK');
       SQL.Add('set TANGGAL=:0, KodeBrg=:1,Qnt=:2, NoBatch='+QuotedStr(NoBatch.Text)+', TglExpired=:3,Nosat=:4 ');
       SQL.Add(' ,BiayaLain=:5,TglSelesai=:6,QntCetak=:7,JenisSpk=:8 Where NoBukti='+QuotedStr(NoBukti.Text));
       Prepared;
       Parameters[0].Value := TANGGAL.Date;
       Parameters[1].Value := KdBrg.Text;
       Parameters[2].Value := Qntj.AsFloat;
       Parameters[3].Value := TglExpired.Date;
       Parameters[4].Value := NosatJ.AsInteger;
       Parameters[5].Value := BiayaL.AsInteger;
       Parameters[6].Value := TglSelesai.Date;
       Parameters[7].Value := JmlCetak.Value;
       Parameters[8].Value := Jenis.ItemIndex;
       ExecSQL;
       mUbahHd:=False; mUbahHdDet:=False;
    end;
    TampilData(NoBukti.Text);
  end;
end;

procedure TFrSPK.ClearPanelDetail;
begin
   KodeBrg.Text:='';
   LNamaBrg.Caption:='[ . . . ]';
   Qnt.Value:=0;
   Nosat.AsInteger:=1;
   LNamaSat.Caption:='[. . . ]';
end;

procedure TFrSPK.RefreshAll;
var i:integer;
begin
   mValid:=False;
   for i:= 0 to Panel1.ControlCount-1 do
   begin
      if (Panel1.Controls[i] is TEdit) then
         (Panel1.Controls[i] as TEdit).Clear;
      if (Panel1.Controls[i] is TPBNumEdit) then
         (Panel1.Controls[i] as TPBNumEdit).Value:=0;
   end;
   for i:= 0 to Panel3.ControlCount-1 do
   begin
      if (Panel3.Controls[i] is TEdit) then
         (Panel3.Controls[i] as TEdit).Clear;
      if (Panel3.Controls[i] is TPBNumEdit) then
         (Panel3.Controls[i] as TPBNumEdit).Value:=0;
   end;
   ClearPanelDetail;
end;

procedure TFrSPK.UpdateDataBeli(Choice:Char;Kodet:String);
begin
   BM:=QuBeli.GetBookmark;
   With Sp_Beli do
   begin
       Parameters.Refresh;
       Parameters[1].Value:=Choice;
       Parameters[2].Value:=NoBukti.Text;
       Parameters[3].Value:=TANGGAL.Date;
       Parameters[4].Value:=KdBrg.Text;
       Parameters[5].Value:=QntJ.AsFloat;
       if Choice<>'I' then
       Parameters[6].Value:=CSPK.Checked;
       Parameters[7].Value:=NosatJ.AsInteger;
       Parameters[8].Value:=mNamaSatuanJ[NoSatJ.AsInteger];
       Parameters[9].Value:=mIsiSatuanJ[NoSatJ.AsInteger];
       Parameters[10].Value:=KodeBrg.Text;
       if Choice<>'I' then
       Parameters[11].Value:=QuBeliurut.AsInteger;
       Parameters[12].Value:=mIsiSatuan[NoSat.AsInteger];
       Parameters[13].Value:=Qnt.AsFloat;
       Parameters[14].Value:=NoSat.AsInteger;
       Parameters[15].Value:=mNamaSatuan[NoSat.AsInteger];
       Parameters[16].Value:=NoUrut.Text;
       Parameters[17].Value:=NoBatch.Text;
       Parameters[18].Value:=TglExpired.Date;
       Parameters[19].Value:=KodeBOM.Text;
       Parameters[20].Value:=KodeBOMDet.Text;
       Parameters[21].Value:=BiayaL.AsFloat;
       Parameters[22].Value:=StrLevelBOM.Text;
       Parameters[23].Value:=IntLevelBOM.AsInteger;
       Parameters[26].Value:=tglselesai.Date ;
       Parameters[27].Value:=JmlCetak.AsFloat ;
       Parameters[28].Value:=jenis.ItemIndex ;

       if Choice <> 'D' then
         begin
           Parameters[24].Value:=NoSO.Text;
           Parameters[25].Value:=StrToInt(UrutSO.Text);
         end;
       try
          ExecProc;
          if Choice='I' then
          begin
            TampilData(NOBUKTI.Text);
            //QuBeli.Locate('KodeBrg',KodeBrg.Text,[LOP,LOC]);
            LoggingData(IDUser,Choice,'SPK',NoBukti.Text,
                    ' Bahan = '+QuotedStr(KodeBrg.Text)+
                    ' Barang Jadi = '+QuotedStr(KodeBrg.Text)+
                    ' Qnt = '+QuotedStr(Qnt.Text));
          end else if Choice='U' then
          begin
            TampilData(NOBUKTI.Text);
            if QuBeli.BookmarkValid(BM) then
            begin
              try
                 QuBeli.GotoBookmark(BM);
              finally
                 QuBeli.FreeBookmark(BM);
              end
            end else
            begin
              QuBeli.FreeBookmark(BM);
              //QuBeli.Last;
              //QuBeli.Locate('Urut', mUrut, []);
            end;
            //QuBeli.Locate('Urut', mUrut, []);
            LoggingData(IDUser,Choice,'SPK',NoBukti.Text,
                    ' Bahan = '+QuotedStr(KodeBrg.Text)+
                    ' Barang Jadi = '+QuotedStr(KodeBrg.Text)+
                    ' Qnt = '+QuotedStr(Qnt.Text));
          end
          Else if Choice='D' then
          begin
            LoggingData(IDUser,Choice,'SPK',NoBukti.Text,
                    ' Bahan = '+QuotedStr(KodeBrg.Text)+
                    ' Barang Jadi = '+QuotedStr(KodeBrg.Text)+
                    ' Qnt = '+QuotedStr(QuBeliQnt.DisplayText));
            TampilData(NOBUKTI.Text);
          end;
          //Hitung;
          QuBeli.Locate('Urut', mUrut, []);
       except
          If (Choice='I') Or (Choice='U') then
             showmessage('Proses Gagal !                   ')
          else If (Choice='D') then
             showmessage('Proses Gagal !, Barang berkaitan dengan transaksi lain.');
       end;

   end;
end;

function  TFrSPK.CekBahan(pNOSO: String): Boolean;
var xNoSuratJln: String;
    i: Integer;
 xStrMsg, xStrMsgDet: String;
begin
  xStrMsg:='';
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    //SQL.Add('select  Noso from dbSPK ');
    SQL.Add(' select Nobukti  from DBPenyerahanBhnDET where kodebrg not like ''%1143%'' and NoSPK like '+quotedstr(QuBeliNoBukti.AsString))     ;
    SQL.Add(' and kodebrg like '+quotedstr(pNOSO));
    ;

    //SQL.Add('where  Noso='+QuotedStr(QuBeliNoBukti.AsString));
    Open;
    xStrMsgDet:='';
    if not IsEmpty then
    begin
      while not Eof do
      begin
        if xStrMsgDet='' then xStrMsgDet:=FieldByName('nobukti').AsString
          else xStrMsgDet:=xStrMsgDet+', '+FieldByName('nobukti').AsString;
        Next;
      end;
      if xStrMsg='' then
        xStrMsg:=xStrMsg+' No. Bukti RPP : '+xStrMsgDet
      else xStrMsg:=xStrMsg+chr(13)+'Retur Barang No. Bukti : '+xStrMsgDet;
    end;
  end;
  mStrMsg:=xStrMsg;
  if xStrMsg='' then
  begin
    Result:=False
  end else
  begin
    Result:=True;
  end;

end;




function  TFrSPK.CekAdaSuratJln(pNOSO: String): Boolean;
var xNoSuratJln: String;
    i: Integer;
 xStrMsg, xStrMsgDet: String;
begin
{  xNoSuratJln:='';
  i:=0;
  with DM.QueryBrow do
  begin
    Close;
    SQL.Clear;
    SQL.Add('select distinct NoBukti from dbSuratJlnDet where NOSO='+QuotedStr(pNOSO));
    Open;
  end;
  if DM.QueryBrow.IsEmpty then CekAdaSuratJln:=False else
  begin
    CekAdaSuratJln:=True;
    DM.QueryBrow.First;
    while not DM.QueryBrow.Eof do
    begin
      if xNoSuratJln='' then
        xNoSuratJln:=xNoSuratJln+DM.QueryBrow.FieldByName('NoBukti').AsString
      else xNoSuratJln:=xNoSuratJln+chr(13)+DM.QueryBrow.FieldByName('NoBukti').AsString;
      DM.QueryBrow.Next;
    end;
    MessageDlg('Sales Order (SO) tidak dapat dihapus, sudah ada SPK'+chr(13)+xNoSuratJln,
    mtInformation,[mbOK],0);
  end;   }
  //Result := False;
  xStrMsg:='';
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    //SQL.Add('select  Noso from dbSPK ');
    SQL.Add(' select Nobukti  from DBPenyerahanBhnDET where NoSPK like '+quotedstr(QuBeliNoBukti.AsString));
    SQL.Add (' union all ');
    SQL.Add(' select Nobukti  from DBPRODUKSIDET  where NoSPK like '+quotedstr(QuBeliNoBukti.AsString));
    SQL.Add (' union all ');
    SQL.Add(' select Nobukti  from DBtransaksi  where kodebag like '+quotedstr(QuBeliNoBukti.AsString));

    //SQL.Add('where  Noso='+QuotedStr(QuBeliNoBukti.AsString));
    Open;
    xStrMsgDet:='';
    if not IsEmpty then
    begin
      while not Eof do
      begin
        if xStrMsgDet='' then xStrMsgDet:=FieldByName('nobukti').AsString
          else xStrMsgDet:=xStrMsgDet+', '+FieldByName('nobukti').AsString;
        Next;
      end;
      if xStrMsg='' then
        xStrMsg:=xStrMsg+' No. Bukti RPP : '+xStrMsgDet
      else xStrMsg:=xStrMsg+chr(13)+'Retur Barang No. Bukti : '+xStrMsgDet;
    end;
  end;
  mStrMsg:=xStrMsg;
  if xStrMsg='' then
  begin
    Result:=False
  end else
  begin
    Result:=True;
  end;
end;


Procedure TFrSPK.AmBilDataSO;
var myDate : TDateTime;
begin
  with FrMainSPK do
    begin
      NoSO.Text         := QuMaster2NOBUKTI.AsString;
      UrutSO.Text       := QuMaster2URUT.AsString;
      KdBrg.Text        := QuMaster2KODEBRG.AsString;
      NamaBrg.Caption   := QuMaster2NAMABRG.AsString;
      QntSO.Value       := QuMaster2Saldo.Value;
      NoSatJ.Value      := QuMaster2NOSAT.AsInteger;
      NmSat.Caption     := QuMaster2SATUAN.AsString;
      Mydate            := EncodeDate(yearOf(Tanggal.Date),monthOf(Tanggal.Date), DayOf(Tanggal.Date)+1);
      TglExpired.Date   := EncodeDate(yearOf(Tanggal.Date),monthOf(Tanggal.Date), DayOf(Tanggal.Date)+1);
      TglSelesai.date   := EncodeDate(yearOf(QuMaster2tglselesai.AsDateTime),monthOf(QuMaster2tglselesai.AsDateTime), DayOf(QuMaster2tglselesai.AsDateTime)-1);
      //TglExpired.Date     := QuMaster2tglmulai.AsDateTime;
      //TglJTSO.Date  :=QuMaster2tglkirim.AsDateTime ;
      //TglSelesai:=EncodeDate(QuMaster2tglkirim,StrToInt(PeriodBln),DD)-1;
      //TglSelesai:=IncDay(QuMaster2t , -1);
      isisatuanBJ;
    end;
end;

Procedure TFrSPK.AmBilDataBeli;
begin
  with dm.QuCari do
  begin
    Close;
    SQL.Clear;
    //sql.SaveToFile('d:\spk.txt');
     sql.Add('Select A.NOBUKTI, A.URUT, A.KODEBRG, SUM(QntSO) QntSO, SUM(' +
       'QntSPK) QntSPK, SUM(Saldo) Saldo, B.NAMABRG, A.NOSAT,case when A.NOSAT =' +
       ' 1 then SAT1 when A.NOSAT = 2 then SAT2 when A.NOSAT = 3 then SAT3 end ' +
       'Satuan ,max ( tglmulai ) tglmulai ,max(tglkirim) tglkirim ,max(' +
       'tglselesai)tglselesai '+
        'from ' +
        '( '+
	'select NOBUKTI, URUT, KODEBRG, QNT QntSO, 0 QntSPK, QNT Saldo, ' +
        'NOSAT, null tglmulai,null tglselesai '+
	' from DBSODET '+
	'union all '+
	' select A.NoSO, A.UrutSO, A.KODEBRG, 0 QntSO, A.Qnt QntSPK, -A.Qnt ' +
        'Saldo, A.Nosat,TglExpired tglmulai,tglselesai '+
	'from DBSPK A '+
        ' )A '+
        ' left outer join DBBARANG B on B.KODEBRG = A.KODEBRG '+
        ' left outer join dbso c on c.nobukti=a.nobukti '+
        ' where a.urut ='+ inttostr(QuBeliurutso.AsInteger) +
        ' and a.nobukti ='+quotedstr(QuBelinoso.AsString) +
        'group by A.NOBUKTI, A.URUT, A.KODEBRG, B.NAMABRG, A.NOSAT, SAT1, SAT2, ' +
        'SAT3  '+
        'having SUM(Saldo) <> 0 ');
    Open;
    if IsEmpty then
       qntso.Value:=0
    else
        qntso.Value:=fieldbyname('Saldo').AsVariant;


  end;

   if not QuBeli.IsEmpty then
   begin
      Tanggal.Date:=QuBeliTanggal.AsDateTime;
      KdBrg.Text:=QuBeliBrgJ.AsString;
      NamaBrg.Caption:=QuBeliNamaBrgJ.AsString;
      NoBatch.Text:=QuBeliNoBatch.AsString;
      TglExpired.Date:=QuBeliTglExpired.AsDateTime;
      TglSelesai.Date:=QuBelitglselesai.AsDateTime;
      NosatJ.AsInteger:=QuBeliNosatJ.AsInteger;
      NmSat.Caption:=QuBeliSatJ.AsString;
      //NosatJ.AsInteger:=QuBeliNosatJ.AsInteger;
      Qntj.AsFloat:=QuBeliQntJ.AsFloat;
      IsiSatuanJ;
      KodeBOM.Text:=QuBeliKodeBOM.AsString;
      NoSO.Text := FrMainSPK.QuMasterNoSO.AsString;
      UrutSO.Text := FrMainSPK.QuMasterUrutSO.AsString;
      //qntso.Value:= FrMainSPK.QuMaster2Saldo.AsInteger;
      biayal.Value:=QuBelibiayalain.AsFloat ;
      JmlCetak.Value := QuBeliQntCetak.AsInteger;
      jenis.ItemIndex:=QuBeliJenisSpk.AsInteger

   end;
end;

procedure TFrSPK.IsiBahanDariBOM;
var xIsiBrgJadi: Real;
    xSatBrgJadi: String;
begin
  with DM.QuCari2 do
  begin
    Close;
    SQL.Clear;
    if NoSatJ.AsInteger=1 then
      SQL.Add('select Sat1 Satuan, Isi1 Isi from dbBarang where KodeBrg='+QuotedStr(KdBrg.Text))
    else
      SQL.Add('select Sat2 Satuan, Isi2 Isi from dbBarang where KodeBrg='+QuotedStr(KdBrg.Text));
    Open;
    xIsiBrgJadi:=FieldByName('Isi').AsCurrency;
    xSatBrgJadi:=FieldByName('Satuan').AsString;
    Close;
    SQL.Clear;
    SQL.Add('select A.* from dbBOMDet A, dbBOM B ');
    SQL.Add('where A.KodeBOM=B.KodeBOM');
    SQL.Add('and B.KodeBrg='+QuotedStr(KdBrg.Text)+' and B.KodeBOM='+QuotedStr(KodeBOM.Text));
    SQL.Add('order by A.Urut ');
    Open;
  end;
  DM.QuCari2.First;
  while not DM.QuCari2.Eof do
  begin
    KodeBrg.Text:=DM.QuCari2.FieldByName('KodeBrg').AsString;
    Qnt.Value:=DM.QuCari2.FieldByName('Qnt').AsCurrency*QntJ.AsCurrency*xIsiBrgJadi;
    NoSat.Value:=1;
    IsiSatuan;
    with Sp_Beli do
    begin
      Parameters.Refresh;
      Parameters[1].Value:='I';
      Parameters[2].Value:=NoBukti.Text;
      Parameters[3].Value:=TANGGAL.Date;
      Parameters[4].Value:=KdBrg.Text;
      Parameters[5].Value:=QntJ.AsFloat;
      Parameters[6].Value:=CSPK.Checked;
      Parameters[7].Value:=NosatJ.AsInteger;
      Parameters[8].Value:=xSatBrgJadi;
      Parameters[9].Value:=xIsiBrgJadi;
      Parameters[10].Value:=KodeBrg.Text;
      Parameters[11].Value:=1;
      Parameters[12].Value:=mIsiSatuan[NoSat.AsInteger];
      Parameters[13].Value:=Qnt.AsFloat;
      Parameters[14].Value:=NoSat.AsInteger;
      Parameters[15].Value:=mNamaSatuan[NoSat.AsInteger];
      Parameters[16].Value:=NoUrut.Text;
      Parameters[17].Value:=NoBatch.Text;
      Parameters[18].Value:=TglExpired.Date;
      Parameters[19].Value:=KodeBOM.Text;
      try
        ExecProc;
        TampilData(NoBukti.Text);
                      //QuBeli.Locate('KodeBrg',KodeBrg.Text,[LOP,LOC]);
        LoggingData(IDUser,'I','SPK',NoBukti.Text,
        ' Barang = '+QuotedStr(KodeBrg.Text)+
        ' Qnt = '+QuotedStr(Qnt.Text));
      except
        showmessage('Proses Gagal !                   ') ;
      end;
      //TampilData(NoBukti.Text);
    end;
    DM.QuCari2.Next;
  end;
end;

procedure TFrSPK.IsiBahanDariCekBOM;
var xIsiBrgJadi: Real;
    xSatBrgJadi: String;
begin
  with DM.QuCari2 do
  begin
    Close;
    SQL.Clear;
    SQL.Add('delete dbSPKDet where NoBukti='+QuotedStr(NoBukti.Text));
    ExecSQL;
    SQL.Clear;
    SQL.Add('delete dbSPK where NoBukti='+QuotedStr(NoBukti.Text));
    ExecSQL;
  end;
  FrTempStockBOM.QuBeli.First;
  while not FrTempStockBOM.QuBeli.Eof do
  begin
    KodeBrg.Text:=FrTempStockBOM.QuBeliKodeBrg.AsString;
    Qnt.Value:=FrTempStockBOM.QuBeliQntBOM.AsCurrency;
    QntBOMX.Value:=FrTempStockBOM.QuBeliQntBOMX.AsCurrency;
    KodeBOMDet.Text:=FrTempStockBOM.QuBeliKodeBOM.AsString;
    StrLevelBOM.Text:=FrTempStockBOM.QuBeliStrLevelBOM.AsString;
    IntLevelBOM.Value:=FrTempStockBOM.QuBeliIntLevelBOM.AsInteger;
    NoSat.Value:=1;
    IsiSatuan;
    with Sp_Beli do
    begin
      Parameters.Refresh;
      Parameters[1].Value:='I';
      Parameters[2].Value:=NoBukti.Text;
      Parameters[3].Value:=TANGGAL.Date;
      Parameters[4].Value:=KdBrg.Text;
      Parameters[5].Value:=QntJ.AsFloat;
      Parameters[6].Value:=CSPK.Checked;
      Parameters[7].Value:=NosatJ.AsInteger;
      Parameters[8].Value:=xSatBrgJadi;
      Parameters[9].Value:=xIsiBrgJadi;
      Parameters[10].Value:=KodeBrg.Text;
      Parameters[11].Value:=1;
      Parameters[12].Value:=mIsiSatuan[NoSat.AsInteger];
      Parameters[13].Value:=Qnt.AsFloat;
      Parameters[14].Value:=NoSat.AsInteger;
      Parameters[15].Value:=mNamaSatuan[NoSat.AsInteger];
      Parameters[16].Value:=NoUrut.Text;
      Parameters[17].Value:=NoBatch.Text;
      Parameters[18].Value:=TglExpired.Date;
      Parameters[19].Value:=KodeBOM.Text;
      Parameters[20].Value:=KodeBOMDet.Text;
      Parameters[21].Value:=BiayaL.AsFloat;
      Parameters[22].Value:=StrLevelBOM.Text;
      Parameters[23].Value:=IntLevelBOM.AsInteger;
      try
        ExecProc;
        TampilData(NoBukti.Text);
                      //QuBeli.Locate('KodeBrg',KodeBrg.Text,[LOP,LOC]);
        LoggingData(IDUser,'I','SPK',NoBukti.Text,
        ' Barang = '+QuotedStr(KodeBrg.Text)+
        ' Qnt = '+QuotedStr(Qnt.Text));
      except
        showmessage('Proses Gagal !                   ') ;
      end;
      //TampilData(NoBukti.Text);
    end;
    FrTempStockBOM.QuBeli.Next;
  end;
end;

// PROCEDURE DAN FUNCTION MILIK DELPHI

procedure TFrSPK.FormClose(Sender: TObject; var Action: TCloseAction);
begin
  if mExit=true then
  begin
     Hapus_Daftar_Nomor('SPK',NoBukti.Text,IDUser);
     FrMainSPK.GetData(StrToInt(PeriodBln),StrToInt(PeriodThn));
     Action:=cafree;
  end
  else
  begin
     Action:=caNone;
  end;
end;

procedure TFrSPK.FormKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
   if key=13 then SendMessage(handle,WM_NEXTDLGCTL,0,0);
end;

procedure TFrSPK.FormShow(Sender: TObject);

begin
  mValid:=False;

  xModalKoreksi:=FrMainSPK.ModalKoreksi;
  CSPK.Enabled:=FrMainSPK.ModalKoreksi;
  If FrMainSPK.ModalKoreksi = False then
  begin
    RefreshAll;
    IsiNoBuktiBaru;

    TampilData(NoBukti.Text);
    //NoUrut.Enabled:=True;
    //NoUrut.Enabled:=False;
    AmBilDataSO;
  end
  else
  Begin
    NOBUKTI.Text := FrMainSPK.QuMaster.FieldByname('Nobukti').AsString;
    Hapus_Daftar_Nomor_User ('SPK',IDUser);
    Daftar_Nomor('SPK',NoUrut.Text,NOBUKTI.Text,IsMax);

    TampilData(NOBUKTI.Text);
    TampilDataMesin;
    AmBilDataBeli;
    //AmBilDataSO;
    NoUrut.Text:=QuBeliNoUrut.AsString;
//      QuBeliNoUrut.AsString;
    //NoUrut.Enabled:=False;
    qntj.Enabled:=False;
  end;
end;

procedure TFrSPK.TampilDataMesin;
begin
  QuMesin.Close;
  QuMesin.SQL.Strings[2]:='select @NoBukti='+QuotedStr(NoBukti.Text);
  //QuMesin.SQL.Strings[3]:='select @KodePrs='+QuotedStr(QuPrsKodePrs.AsString);
  QuMesin.Open;
  //KdBrg.Enabled:=QuBeli.IsEmpty;
  //KodeBOM.Enabled:=QuBeli.IsEmpty;

  if QuMesin.IsEmpty=false then
  begin
      dxDBGrid3.OptionsDB:=[edgoCanNavigation,edgoLoadAllRecords,edgoUseBookmarks]
  end
  else
  begin
      dxDBGrid3.OptionsDB:=[edgoCanNavigation,edgoUseBookmarks];
  end;
end;

procedure TFrSPK.FormCreate(Sender: TObject);
begin
  TipeTrans := 'SPK';
  Hapus_Daftar_Nomor_User('SPK',IDUser);
end;

procedure TFrSPK.TambahBtnClick(Sender: TObject);
begin
 // if CheckFormMaster=true then
  if QuMesin.IsEmpty = false then
  begin
      Model:='write';
      TampilTombolDetail(True);
      jamawl.Text :='00:00';
      jamakhir.Text :='00:00';
      TrfMesin.Value:=0;
      JamTenaker.Value:=0;
      JmlTenaker.Value:=0;
      TarifTenaker.Value:=0;
      TrfTenaker.text:='';
      KodeBrg.Enabled:=True;
      ActiveControl:=KodeBrg;
  end
  else
  begin
     //ShowMessage('Form Master Tidak Lengkap, Silahkan Anda Periksa !');
     ShowMessage('Belum memilih mesin !');
  end;
end;

procedure TFrSPK.QntKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
   if Key=VK_Escape then
   begin
      mValid:=false;
      KodeBrg.Enabled:=True;
      //Panel2.Enabled:=true;
      ClearPanelDetail;
      if Model='write' then
         ActiveControl:=KodeBrg
      else
      begin
         SpeedButton1.Click;
      end;
   end;
end;

procedure TFrSPK.TanggalKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
  if Key=Vk_Return then
  begin
    if (Sender=Tanggal) and (CekPeriode(IDUser,TANGGAL.Date)=False) then
      Abort;
    UpdatedbBeli;
  end
  else if Key=Vk_Escape then
  begin
    If FrMainSPK.ModalKoreksi = False then
    begin
      Hapus_Daftar_Nomor_User('SPK',IDUser);
      mValid:=False;
      RefreshAll;
      IsiNoBuktiBaru;
      //Keterangan.Text :='';
      TampilData(NOBUKTI.Text);
      if nourut.Enabled then

      ActiveControl := NoUrut;
      if noso.Text='' then
        begin
          mExit := true;
          close;

        end
    end
    else
    begin
      mExit := true;
      close;
    end;
  end;
end;

procedure TFrSPK.KoreksiBtnClick(Sender: TObject);
begin
  if CheckFormMaster=true then
  begin
     mValid:=false;
     model:='koreksi';
     // data yang di update
     if QuBeli.IsEmpty=false then
     begin
        if CekBahan(QuBeliKODEBRG.AsString)=False then
        begin
            TampilTombolDetail(True);
            KodeBrg.Enabled:=False;
            KodeBrg.Text:=QuBeliKODEBRG.AsString;
            LNamaBrg.Caption:='[ '+QuBeliNamaBrg.AsString+' ]';
            LNamaSat.Caption:='[ '+QuBeliSATUAN.AsString+' ]';
            NoSat.AsInteger:=QuBeliNOSAT.AsInteger;
            Qnt.AsFloat:=QuBeliQNT.AsFloat;
            mPosisi:=QuBeliURUT.AsInteger;
            KodeBOMDet.Text:=QuBeliKodeBOMDet.AsString;
            IsiSatuan;
            ActiveControl:=Qnt;
        end;
     end
     else
     begin
        ShowMessage('Tidak ada transaksi yang dikoreksi !');
        ActiveControl:=dxDBGrid1;
     end;
  end else
  begin
     ShowMessage('Form Master Tidak Lengkap, Silahkan Anda Periksa !');
  end;
end;

procedure TFrSPK.wwDBGrid1KeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
  if key=VK_Escape then
  begin
    Hapus_Daftar_Nomor_User('SPK',IDUser);
    if xmodalkoreksi then
    begin
      mExit := True;
      Close;
    end
    else
    begin
      RefreshAll;
      IsiNoBuktiBaru;
      TampilData(NOBUKTI.Text);
      if nourut.Enabled  then

      ActiveControl:=NoUrut;
    end;
  end
  else if key=VK_return then
  begin
    if QuBeli.IsEmpty then
      TambahBtn.Click
    else KoreksiBtn.Click;
  end
  else if key=VK_delete then
  begin
    HapusBtn.Click;
  end
  else if key=VK_insert then
  begin
    TambahBtn.Click;
  end;
end;

procedure TFrSPK.HapusBtnClick(Sender: TObject);
begin
      if IsLockPeriode(StrToInt(PeriodBln),StrToInt(PeriodThn)) then
      begin
        if CekBahan(QuBeliKODEBRG.AsString)=False then
        begin
             if QuBeli.IsEmpty=true then
             begin
                Application.MessageBox('Tabel Kosong','Informasi',
                MB_OK Or MB_ICONWARNING);
             end
             else
             begin
                StrPCopy(S,Format('Anda yakin NO %s dan Nama %s dihapus ?',
                [QuBeli.Fields[0].asstring,
                QuBeli.Fields[2].asstring]));
                if (Application.MessageBox(S,'Hapus Data',
                   MB_YESNO or MB_ICONSTOP + MB_DEFBUTTON2)=IDYES)then
                begin
                   UpdateDataBeli('D','BL');
                end;
             end;
        end;
      end
      else
      begin
         ShowMessage('Periode Sudah Di Kunci !');
         ActiveControl:=dxDBGrid1;
      end;
end;

procedure TFrSPK.wwDBGrid1Enter(Sender: TObject);
begin
  FrSPK.KeyPreview:=false;
  if CheckFormMaster=false then
  begin
     ShowMessage('Form Master Tidak Lengkap, Silahkan Anda Periksa !');
     Hapus_Daftar_Nomor(TipeTrans,NoBukti.Text,iduser);
     if nourut.Enabled then

     ActiveControl:=NoUrut;
  end;
end;

procedure TFrSPK.wwDBGrid1Exit(Sender: TObject);
begin
  FrSPK.KeyPreview:=true;
end;

procedure TFrSPK.wwDBGrid1DblClick(Sender: TObject);
begin
  KoreksiBtn.Click;
end;

procedure TFrSPK.NoUrutKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
  {if Key=Vk_Return then
  begin
    If Length(NoUrut.Text)<4 then
    begin
      MessageDlg('Nomor urut kurang '+InttoStr(4-Length(NoUrut.Text))+' digit.',mtWarning,[MbOk],0);
      ActiveControl := NoUrut;
    end
    else
    begin
      TampilData(nobukti.Text);
      if not QuBeli.IsEmpty then
      Begin
         AmBilDataBeli;
      end
      else
      Begin
        if FrMainSPK.ModalKoreksi then
        begin
          ActiveControl := NoUrut;
        end;
      end;
      If Not Daftar_Nomor('SPK',NoUrut.Text,NOBUKTI.Text,IsMax) then
      begin
        MessageDlg('Nomor : '+NOBUKTI.Text+' telah digunakan oleh '+MyUser+'.',mtWarning,[MbOk],0);
        ActiveControl := NoUrut;
      end;
    end;
  end
  else }if Key=Vk_Escape then
  begin
    mExit := true;
    Close;
  end;
end;

procedure TFrSPK.TanggalEnter(Sender: TObject);
begin
  mValid:=true;
end;

procedure TFrSPK.TanggalExit(Sender: TObject);
begin
  if mValid then
  begin
     if CekPeriode(IDUser,TANGGAL.Date)=false then
     begin
        MessageDlg('Tanggal tidak sesuai periode transaksi.',mtWarning,[mbok],0);
        ActiveControl:=Tanggal;
     end;
  end;
end;

procedure TFrSPK.KodeSuppEnter(Sender: TObject);
begin
  mValid:=true;
end;

procedure TFrSPK.CetakClick(Sender: TObject);
var N1, N2, N3 : string;
    T1, T2 : Real;
    Tot : byte;
    H1  : real;
begin
  tothal :=(QuBeli.RecordCount div 10);
  sishal :=(QuBeli.RecordCount mod 10);
  if sishal>0 then tothal :=tothal+1;
  With QuUpdatedbbeli do
  begin
    close;
    sql.clear;
    sql.add('Select * From vwPerusahaan ');
    Open;
  end;
  frxDBDataset1.DataSet:=Qureport;
  with qureport do
  begin
    Close;
    Sql.Clear;
    Sql.Add('Exec cetakSPK '+QuotedStr(nobukti.Text));
    open
  end;
  with qureport2 do
  begin
    Close;
    Sql.Clear;
    Sql.Add('Exec CetakSPKMesinTenaker '+QuotedStr(nobukti.Text));
    open
  end;

  frxDBDataset1.close;
  frxDBDataset1.Open;
  frxReport1.LoadFromFile(currDir+'Nota\NotaProduksiSPK.fr3');
  frxReport1.ShowReport;
end;

procedure TFrSPK.BitBtn1Click(Sender: TObject);
begin
   if CekPeriode(IDUser, Tanggal.Date)=True then
   begin
      if Model='write' then
         begin
               if length(Kodebrg.Text)>0 then
               begin
                  UpdateDataBeli('I','BL');
                  ClearPanelDetail;
                  ActiveControl:=KodeBrg;
               end
               else
               begin
                  ShowMessage('Kode barang tidak boleh kosong !');
                  ActiveControl:=KodeBrg;
               end;

         end
         else if model='koreksi' then
         begin
             if length(Kodebrg.Text)>0 then
             begin
                mValid:=false;
                UpdateDataBeli('U','BL');
                SpeedButton1.Click;
             end
             else
             begin
                ShowMessage('Kode barang tidak boleh kosong !');
                ActiveControl:=KodeBrg;
             end;
         end;
   end
   else
     ShowMessage('Tanggal Periode Tidak Sesuai !');
end;

procedure TFrSPK.SpeedButton1Click(Sender: TObject);
begin
  mValid:=false;
  TampilTombolDetail(False);
  ActiveControl:=dxDBGrid1;
  ClearPanelDetail;
end;

procedure TFrSPK.NoDOKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
  if key=VK_Escape then
  begin
     mValid :=false;
     SpeedButton1.Click;
  end;
end;

procedure TFrSPK.FormDestroy(Sender: TObject);
begin
  FrSPK:=nil;
end;

procedure TFrSPK.KodeGdgEnter(Sender: TObject);
begin
  mValid:=True;
end;

procedure TFrSPK.dxDBGrid1CustomDrawCell(Sender: TObject;
  ACanvas: TCanvas; ARect: TRect; ANode: TdxTreeListNode;
  AColumn: TdxTreeListColumn; ASelected, AFocused, ANewItemRow: Boolean;
  var AText: String; var AColor: TColor; AFont: TFont;
  var AAlignment: TAlignment; var ADone: Boolean);
begin
    if ANode.HasChildren then
      Exit;

    if not AFocused then
    begin
      if ANode.Index mod 2 =0 then
         AColor:=clWhite
      else
         AColor:=$00E1FFFF;
    end;
end;

procedure TFrSPK.BPrevClick(Sender: TObject);
begin
        Hapus_Daftar_Nomor('SPK',NoBukti.Text,iduser);
        RefreshAll;
        ActiveControl:=TANGGAL;
        Hapus_Daftar_Nomor('SPK',NoBukti.Text,iduser);

        FormShow(self);
end;

procedure TFrSPK.BNextClick(Sender: TObject);
begin
        Hapus_Daftar_Nomor('SPK',NoBukti.Text,iduser);
        RefreshAll;
        ActiveControl:=TANGGAL;
        Hapus_Daftar_Nomor('SPK',NoBukti.Text,iduser);

        FormShow(self);

end;

procedure TFrSPK.KodeBrgKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
  if Key=VK_Escape then
  begin
    mValid:=False;
    SpeedButton1.Click;
  end;
end;

procedure TFrSPK.SpeedButton8Click(Sender: TObject);
begin
  frxReport1.LoadFromFile(currDir+'Nota\NotaProduksiSPK.fr3');
  frxReport1.DesignReport;
end;

procedure TFrSPK.KdBrgEnter(Sender: TObject);
begin
  mValid:=True;
end;

procedure TFrSPK.KdBrgExit(Sender: TObject);
begin
  if mValid Then
  begin
    if length(KdBrg.Text)=0 then TampilIsiBrgJadi else
    begin
      if DataBersyarat('Select A.KodeBrg, A.NamaBrg, Isnull(b.Qnt,0) QntSaldo '+
                       'from dbBarang A '+
                       'left Outer Join (select Kodebrg,Sum(SaldoQnt)Qnt from DBStockBrg a Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg where B.Nama not Like ''%Bahan%'' and Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by kodebrg)b On b.kodebrg=a.KodeBrg ' +
                       'where a.isAktif=1 and A.KodeGrp=''BJ'' and (a.KodeBrg like :0 or a.NamaBrg like :1) '+
                       'order by A.KodeBrg',['%'+KdBrg.Text+'%','%'+KdBrg.Text+'%'],Dm.Qucari)=True then
      begin
        with DM.QuCari do
        begin
           KdBrg.Text:=fieldbyname('KodeBrg').AsString;
           NamaBrg.Caption:='[..'+fieldbyname('NamaBrg').AsString+'..]';
           //QntJ.AsFloat:=0.00;
           //ActiveControl:=QntJ;
        end;
      end
      else TampilIsiBrgJadi;
    end;
    IsiSatuanJ;
  end;
end;


procedure TFrSPK.TanggalChange(Sender: TObject);
begin
  mUbahHd:=True;
end;

procedure TFrSPK.NoUrutEnter(Sender: TObject);
begin
  mValid:=True;
end;

procedure TFrSPK.IsiSatuan;
begin
  LNamaSat.Caption:=IsiSatuanBrg(KodeBrg.Text, mNamaSatuan, mIsiSatuan);
end;

procedure TFrSPK.IsiSatuanBJ;
begin
  LNamaSat.Caption:=IsiSatuanBrg(KdBrg.Text, mNamaSatuan, mIsiSatuan);
end;

procedure TFrSPK.IsiSatuanJ;
begin
  NmSat.Caption:=IsiSatuanBrg(KdBrg.Text, mNamaSatuan, mIsiSatuan);
  //NosatJ.Value :=1;
end;

procedure TFrSPK.KodeBrgEnter(Sender: TObject);
begin
mValid:=True;
end;

procedure TFrSPK.KodeBrgExit(Sender: TObject);
begin
  if mValid Then
  begin
        if length(KodeBrg.Text)=0 then TampilIsiBarang else
        begin
          if DataBersyarat('Select A.KodeBrg, A.NamaBrg, Isnull(b.Qnt,0) QntSaldo '+
                           'from dbBarang A '+
                           'left Outer Join (select Kodebrg,Sum(SaldoQnt)Qnt from DBStockBrg a Left Outer Join dbGudang b On a.Kodegdg=b.Kodegdg where b.Nama Like ''%Bahan%'' and Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by kodebrg)b On b.kodebrg=a.KodeBrg ' +
                           'where  a.isAktif=1 and (a.KodeBrg like :0 or a.NamaBrg like :1) '+
                           'order by A.KodeBrg ',['%'+KodeBrg.Text+'%','%'+KodeBrg.Text+'%'],Dm.Qucari)=true then
          begin
            with DM.QuCari do
            begin
             KodeBrg.Text:=fieldbyname('KodeBrg').AsString;
             LNamaBrg.Caption:='[ '+fieldbyname('NamaBrg').AsString+' ]';
             Qnt.AsFloat:=fieldbyname('QntSaldo').AsFloat;
             ActiveControl:=Qnt;
            end;
          end
          else TampilIsiBarang;
        end;
         IsiSatuan;   
  end;

end;

procedure TFrSPK.TampilIsiGudang;
begin

end;

procedure TFrSPK.frxReport1GetValue(const VarName: String;
  var Value: Variant);
begin
if CompareText(varName,'iduser')=0 then
        value:=IDUser;
end;

procedure TFrSPK.frxReport1AfterPrintReport(Sender: TObject);
begin
NilaiCetak('CetakKe','DBSPK',nobukti.Text);
end;

procedure TFrSPK.NoUrutExit(Sender: TObject);
var myTempNoUrut: String;
begin
  if mValid then
  begin
    myTempNoUrut:=NoUrut.Text;
    NoUrut.Text:=RightStr('00000'+myTempNoUrut,5);
    Konfig(TipeTrans,NoUrut.Text,F1,F2,F3,F4,NomorBukti,xN1,xN2,xN3,xN4,Tanggal.Date);
    NOBUKTI.Text:=MyNoBukti;
    TampilData(NoBukti.Text);
    If Length(NoUrut.Text)<4 then
    begin
      MessageDlg('Nomor urut kurang '+InttoStr(5-Length(NoUrut.Text))+' digit.',mtWarning,[MbOk],0);
      if NoUrut.Enabled then

      ActiveControl := NoUrut;
    end
    else
    begin
      if (not QuBeli.IsEmpty) then
      Begin
        if (xModalKoreksi=True)  then
           AmBilDataBeli
        else
        Begin
          TampilData('');
          MsgDataSudahADA(NoBukti.Text);
          if NoUrut.Enabled then

          ActiveControl := NoUrut;
        end;
      end;
      If Not Daftar_Nomor(TipeTrans,NoUrut.Text, NOBUKTI.Text,isMax) then
      begin
        MessageDlg('Nomor : '+NOBUKTI.Text+' telah digunakan oleh '+MyUser+'.',mtWarning,[MbOk],0);
        if nourut.Enabled then

        ActiveControl := NoUrut;
      end;
    end;
  end;

end;

procedure TFrSPK.NoSatJExit(Sender: TObject);
var xsat:string;xisi:Double;
begin
  DataBersyarat('Select * from Dbbarang where kodebrg=:0',[KdBrg.text],Dm.Qucari);
  if NoSatJ.AsInteger=1 then
  begin
    xsat:=Dm.Qucari.Fieldbyname('Sat1').asstring;
    xisi:=Dm.Qucari.Fieldbyname('Isi1').AsFloat ;
  end
  else if nosatj.value=2 then
  begin
    xsat:=Dm.Qucari.Fieldbyname('Sat2').asstring;
    xisi:=Dm.Qucari.Fieldbyname('Isi2').asfloat ;
  end;
  {
  if  DataBersyarat('Select * from DbBOM where KOdebrgJd =:0',[KdBrg.text],Dm.Qucari2) =True then
  begin
    if CekPeriode(IDUser, Tanggal.Date)=True then
         begin
           while not Dm.Qucari2.Eof do
           Begin
            ClearPanelDetail;
            KOdebrg.Text :=Dm.QuCari2.Fieldbyname('KOdebrgBhn').asstring;
            Qnt.Value:= Dm.QuCari2.Fieldbyname('Qnt').AsFloat * QntJ.Value * xisi;
            NoSat.Value:=1;
            //UpdateDataBeli('I','BL');
            IsiSatuan;
            BM:=QuBeli.GetBookmark;
             With Sp_Beli do
             begin
                 Parameters.Refresh;
                 Parameters[1].Value:='I';
                 Parameters[2].Value:=NoBukti.Text;
                 Parameters[3].Value:=TANGGAL.Date;
                 Parameters[4].Value:=KdBrg.Text;
                 Parameters[5].Value:=QntJ.AsFloat;
                 if Parameters[1].Value='I' then
                 Parameters[6].Value:=CSPK.Checked;
                 Parameters[7].Value:=NosatJ.AsInteger;
                 Parameters[8].Value:=xsat;
                 Parameters[9].Value:=xisi;
                 Parameters[10].Value:=KodeBrg.Text;
                 if Parameters[1].Value='I' then
                 Parameters[11].Value:=QuBeliurut.AsInteger;
                 Parameters[12].Value:=mIsiSatuan[NoSat.AsInteger];
                 Parameters[13].Value:=Qnt.AsFloat;
                 Parameters[14].Value:=NoSat.AsInteger;
                 Parameters[15].Value:=mNamaSatuan[NoSat.AsInteger];
                 Parameters[16].Value:=NoUrut.Text;
                 Parameters[17].Value:=NoBatch.Text;
                 Parameters[18].Value:=TglExpired.Date;
                 try
                    ExecProc;
                     if Parameters[1].Value='I' then
                    begin
                      TampilData(NOBUKTI.Text);
                      //QuBeli.Locate('KodeBrg',KodeBrg.Text,[LOP,LOC]);
                      LoggingData(IDUser,'I','SPK',NoBukti.Text,
                              ' Barang = '+QuotedStr(KodeBrg.Text)+
                              ' Qnt = '+QuotedStr(Qnt.Text));
                    end ;
                 except
                   showmessage('Proses Gagal !                   ') ;

                 end;
              TampilData(NoBukti.Text);
             end;
            Dm.Qucari2.Next;
           End;
         End
     else ShowMessage('Tanggal Periode Tidak Sesuai !');
  End
  Else
    Begin
    ShowMessage('Data B.O.M untuk Barang Jadi : '+KdBrg.Text+' Tidak Terdaftar');
    End;
 ActiveControl:=dxDBGrid1;}
end;

procedure TFrSPK.KodeBOMExit(Sender: TObject);
var xIsiBrgJadi: Real;
begin
  if mValid Then
  begin
    if KodeBOM.Text<>'-' then
    begin
      if (length(KodeBOM.Text)=0) and
        (DataBersyarat('Select A.* from dbBOM A where A.KodeBrg='+QuotedStr(KdBrg.Text)+
        ' and A.KodeBOM=:0 '+
        ' order by A.KodeBOM ',
        [KodeBOM.Text],DM.QuCari)=True) then
      begin
        KodeBOM.Text:=DM.QuCari.FieldByName('KodeBOM').AsString;
      end else
      begin
        KodeBrows:=50402;
        Application.CreateForm(TFrBrows, FrBrows);
        FrBrows.NoKira:=KdBrg.Text;
        FrBrows.IsiData:=KodeBOM.Text;
        FrBrows.ShowModal;
        if FrBrows.ModalResult=mrOK then
        begin
          KodeBOM.Text:=FrBrows.QuBrows.FieldByName('KodeBOM').AsString;
        end else
          ActiveControl:=KodeBOM;
      end;
    end;
    {
    if KodeBOM.Text<>'' then
    begin
      if CekPeriode(IDUser,Tanggal.Date)=True then
      begin
        IsiBahanDariBOM;
      end else
      begin
        MsgTanggalTidakSesuaiPeriode;
      end;
    end;
    }
  end;
end;

procedure TFrSPK.Button1Click(Sender: TObject);
begin
  Application.CreateForm(TFrTempStockBOM, FrTempStockBOM);
  FrTempStockBOM.KodeBrg.Text:=KdBrg.Text;
  DataBersyarat('select * from dbBarang where KodeBrg=:0',[KdBrg.Text],DM.QuCari);
  if NoSatJ.AsInteger=1 then
    FrTempStockBOM.Qnt.AsCurrency:=QntJ.AsCurrency
  else
    FrTempStockBOM.Qnt.AsCurrency:=QntJ.AsCurrency*DM.QuCari.FieldByName('Isi2').AsCurrency;
  FrTempStockBOM.NamaBrg.Caption:='[ '+DM.QuCari.FieldByName('NamaBrg').AsString+' ]';
  FrTempStockBOM.LSatuanQnt.Caption:='[ '+DM.QuCari.FieldByName('Sat1').AsString+' ]';
  FrTempStockBOM.KodeBOM.Text:=KodeBOM.Text;
  FrTempStockBOM.mNoSPK_TempStockBOM:=NoBukti.Text;
  FrTempStockBOM.ShowModal;
  if FrTempStockBOM.ModalResult=mrOK then
  begin
    IsiBahanDariCekBOM;
  end;
end;

procedure TFrSPK.NoSOExit(Sender: TObject);
begin
  if mValid Then
  begin
    TampilNoSO;
{
    if length(KdBrg.Text)=0 then TampilIsiBrgJadi else
    begin
      if DataBersyarat('Select A.KodeBrg, A.NamaBrg, Isnull(b.Qnt,0) QntSaldo '+
                       'from dbBarang A '+
                       'left Outer Join (select Kodebrg,Sum(SaldoQnt)Qnt from DBStockBrg a Left Outer Join dbGudang b On a.KodeGdg=b.KodeGdg where B.Nama not Like ''%Bahan%'' and Bulan='+PeriodBln+' and Tahun='+PeriodThn+' group by kodebrg)b On b.kodebrg=a.KodeBrg ' +
                       'where a.isAktif=1 and A.KodeGrp=''BJ'' and (a.KodeBrg like :0 or a.NamaBrg like :1) '+
                       'order by A.KodeBrg',['%'+KdBrg.Text+'%','%'+KdBrg.Text+'%'],Dm.Qucari)=True then
      begin
        with DM.QuCari do
        begin
           KdBrg.Text:=fieldbyname('KodeBrg').AsString;
           NamaBrg.Caption:='[..'+fieldbyname('NamaBrg').AsString+'..]';
           //QntJ.AsFloat:=0.00;
           //ActiveControl:=QntJ;
        end;
      end
      else TampilIsiBrgJadi;
    end;
    IsiSatuanJ;
}
  end;
end;

procedure TFrSPK.QntJExit(Sender: TObject);
begin
   {if QntJ.Value > QntSO.Value then
    begin
      ShowMessage('Melebihi Saldo SO yang tersedia !');
      ActiveControl := QntJ;
      QntJ.Value := 0;
    end;
    QntSO.Value:=QntSO.Value-QntJ.Value;}
end;

procedure TFrSPK.SpeedButton5Click(Sender: TObject);
begin
  if CheckFormMaster=true then
  begin
      Model:='write';
      TampilTombolDetailPrs(True);
      jamawl.Text :='00:00';
      jamakhir.Text :='00:00';
      TrfMesin.Value:=0;
      JamTenaker.Value:=0;
      JmlTenaker.Value:=0;
      TarifTenaker.Value:=0;
      TrfTenaker.text:='';
      kodeMSn.Enabled:=True;
      ActiveControl:=kodeMSn;
      TanggalPrs.Date := Now;
      Label27.Visible:=True;
      TrfTenaker.Visible:=true;
      TrfTenaker.Top:=53;
      LTK1.Visible:=true;
      TarifTenaker.Top:= 78;

  end
  else
  begin
     ShowMessage('Form Master Tidak Lengkap, Silahkan Anda Periksa !');
  end;
end;

procedure TFrSPK.SpeedButton4Click(Sender: TObject);
var urut : integer;
begin
  if CheckFormMaster=true then
  begin
     mValid:=false;
     model:='koreksi';
     // data yang di update
     if QuMesin.IsEmpty=false then
     begin
        TampilTombolDetailPrs(True);
        kodeMSn.Enabled:=False;
        label27.Visible:=false;
        TrfTenaker.Visible := false;
        urut:=QuMesinUrut.AsInteger;
        LTK1.Visible:=false;
        TarifTenaker.Top :=53;
        kodeMSn.Text:=QuMesinKODEMSN.AsString;
        Ket.Text := QuMesinKeterangan.AsString;
        nmMsn.Caption :=QuMesinKet.AsString;
        TanggalPrs.Date := QuMesinTANGGAL.AsDateTime;
        JamAwl.Text:=FormatDateTime('HH:mm',QuMesinJAMAWAL.AsDateTime);
        JamAkhir.Text:=FormatDateTime('HH:mm',QuMesinJAMAKHIR.AsDateTime);
        QntSPK.Value:=QuMesinQNTSPK.Value;
        JamTenaker.Value :=QuMesinJamTenaker.Value;
        JmlTenaker.Value :=QuMesinJmlTenaker.Value;
        TarifTenaker.Value :=QuMesinTarifTenaker.Value;
        TrfMesin.Value :=QuMesinTarifMesin.Value;
        ActiveControl:=Ket;
     end
     else
     begin
        ShowMessage('Tidak ada transaksi yang dikoreksi !');
        ActiveControl:=dxDBGrid3
     end;
  end else
  begin
     ShowMessage('Form Master Tidak Lengkap, Silahkan Anda Periksa !');
  end;
end;

procedure TFrSPK.SpeedButton3Click(Sender: TObject);
begin
      if IsLockPeriode(StrToInt(PeriodBln),StrToInt(PeriodThn)) then
      begin
         if QuMesin.IsEmpty=true then
         begin
            Application.MessageBox('Tabel Kosong','Informasi',
            MB_OK Or MB_ICONWARNING);
         end
         else
         begin
         if CekAdaSuratJln(NoBukti.Text)=False then
           begin
              StrPCopy(S,Format('Anda yakin NO %s dan Mesin %s dihapus ?',
              [QuMesin.Fields[2].asstring,
              QuMesin.Fields[3].asstring]));
              if (Application.MessageBox(S,'Hapus Data',
                 MB_YESNO or MB_ICONSTOP + MB_DEFBUTTON2)=IDYES)then
              begin
                 UpdateDataBeliPrs('D','BL');
              end;
           end
            else
            begin
                MessageDlg('Data tidak dapat dihapus, sudah ada transaksi'+chr(13)+mStrMsg, mtInformation,[mbOK],0);
                ActiveControl:=dxDBGrid1;
            end;
         end;
      end
      else
      begin
         ShowMessage('Periode Sudah Di Kunci !');
         ActiveControl:=dxDBGrid3;
      end;
end;


function TFrSPK.CekJadwalProduksi : Boolean;
begin
  with DM.QuCari do
    begin
      Close;
      SQL.Clear;
      SQL.Add('declare @JamAwal datetime = :0, @JamAkhir datetime = :1, @KodeMesin varchar(20) = :2, @Tanggal datetime = :3');
      SQL.Add('select NOJADWAL, KODEMSN, TANGGAL, JAMAWAL, JAMAKHIR, NOSPK from DBJADWALPRD');
      SQL.Add('where KODEMSN = @KodeMesin');
      SQL.Add('		and TANGGAL = @Tanggal');
      SQL.Add('		and DATEPART(hour, @JamAwal) between DATEPART(hour, JAMAWAL) and DATEPART(hour, JAMAKHIR)-1');
      SQL.Add('union all');
      SQL.Add('select NOJADWAL, KODEMSN, TANGGAL, JAMAWAL, JAMAKHIR, NOSPK from DBJADWALPRD');
      SQL.Add('where KODEMSN = @KodeMesin');
      SQL.Add('		and TANGGAL = @Tanggal');
      SQL.Add('		and DATEPART(hour, @JamAkhir) between DATEPART(hour, JAMAWAL)+1 and DATEPART(hour, JAMAKHIR)');
      SQL.Add('union all');
      SQL.Add('select NOJADWAL, KODEMSN, TANGGAL, JAMAWAL, JAMAKHIR, NOSPK from DBJADWALPRD');
      SQL.Add('where KODEMSN = @KodeMesin');
      SQL.Add('		and TANGGAL = @Tanggal');
      SQL.Add('		and DATEPART(hour, JAMAWAL)+1 between DATEPART(hour, @JamAwal) and DATEPART(hour, @JamAkhir)');
      SQL.Add('union all');
      SQL.Add('select NOJADWAL, KODEMSN, TANGGAL, JAMAWAL, JAMAKHIR, NOSPK from DBJADWALPRD');
      SQL.Add('where KODEMSN = @KodeMesin');
      SQL.Add('		and TANGGAL = @Tanggal');
      SQL.Add('		and DATEPART(hour, JAMAKHIR) between DATEPART(hour, @JamAwal)+1 and DATEPART(hour, @JamAkhir)');

      Parameters[0].Value := StrToTime(JamAwl.Text+':00');
      Parameters[1].Value := StrToTime(JamAkhir.Text+':00');
      Parameters[2].Value := kodeMSn.Text;
      Parameters[3].Value := TanggalPrs.Date;

      Open;
      if not IsEmpty then
        Result := True
      else
        Result := False;
    end;
end;

procedure TFrSPK.BitBtn2Click(Sender: TObject);
begin
   if CekPeriode(IDUser, Tanggal.Date)=True then
   begin
    if QntSPK.Value <= 0 then
      begin
        ShowMessage('Quantity SPK Salah !');
        ActiveControl := QntSPK;
      end
    else
    begin
      if Model='write' then
         begin
           {if CekJadwalProduksi then
             begin                    UpdateDataBeliPrs
               ShowMessage('Ada Produksi SPK : ' + DM.QuCari.Fields[5].AsString);
               ActiveControl := TanggalPrs;
             end
           else  }
             begin
               if length(kodeMSn.Text)>0 then
               begin
                  UpdateDataBeliPrs('I','BL');
                  ClearPanelDetailPrs;
                  jamawl.Text :='00:00';
                  jamakhir.Text :='00:00';
                  ActiveControl:=kodeMSn;
               end
               else
               begin
                  ShowMessage('Kode mesin tidak boleh kosong !');
                  ActiveControl:=kodeMSn;
               end;
             end;
         end
         else if model='koreksi' then
         begin
           if CekJadwalProduksi then
             begin
               ShowMessage('Ada Produksi SPK : ' + DM.QuCari.Fields[5].AsString);
               ActiveControl := TanggalPrs;
             end
           else
             begin
               if length(kodeMSn.Text)>0 then
               begin
                  mValid:=false;
                  UpdateDataBeliPrs('U','BL');
                  SpeedButton7.Click;
               end
               else
               begin
                  ShowMessage('Kode mesin tidak boleh kosong !');
                  ActiveControl:=kodeMSn;
               end;
             end;
         end;
    end
   end
   else
     ShowMessage('Tanggal Periode Tidak Sesuai !');

end;

procedure TFrSPK.UpdateDataBeliPrs(Choice:Char;Kodet:String);
begin
   BM:=QuMesin.GetBookmark;
   With Sp_Prs do
   begin
       Parameters.Refresh;
       if Choice <> 'D' then
         begin
           Parameters[1].Value:=Choice;
           Parameters[2].Value:=NoBukti.Text;
           Parameters[3].Value:=NoUrut.Text;
           Parameters[4].Value:=TANGGAL.Date;
           Parameters[5].Value:=KdBrg.Text;
           Parameters[6].Value:=QntJ.AsFloat;
           if Choice<>'I' then
           Parameters[7].Value:=CSPK.Checked;
           Parameters[8].Value:=NosatJ.AsInteger;
           Parameters[9].Value:=mNamaSatuan[NoSatJ.AsInteger];
           Parameters[10].Value:=mIsiSatuan[NoSatJ.AsInteger];
           Parameters[11].Value:=NoBatch.Text;
           Parameters[12].Value:=TglExpired.Date;
           Parameters[13].Value:=KodeBOM.Text;
           Parameters[14].Value:=kodeMSn.Text;
           Parameters[15].Value:=TanggalPrs.Date;
           Parameters[16].Value := 2+StrToTime(JamAwl.Text+':00');
           Parameters[17].Value := 2+StrToTime(JamAkhir.Text+':00');
           Parameters[18].Value:=0;
           Parameters[19].Value:=NoBukti.Text;
           Parameters[20].Value:=Ket.Text;
           Parameters[21].Value:=QntSPK.Value;
           Parameters[22].Value:=Biayal.Value ;
           Parameters[26].Value:=TrfMesin.Value ;
           Parameters[27].Value:=JamTenaker.Value ;
           Parameters[28].Value:=JmlTenaker.Value ;
           Parameters[29].Value:= TarifTenaker.Value*JamTenaker.Value*JmlTenaker.Value ;

           if Choice <> 'I' then
             Parameters[23].Value:=QuMesinUrut.AsInteger;
           Parameters[24].Value:=Noso.Text;
           Parameters[25].Value:=UrutSo.Text;

         end
       else
         begin
           Parameters[1].Value:=Choice;
           Parameters[19].Value:=NoBukti.Text;
           Parameters[23].Value:=QuMesinUrut.AsInteger;
           Parameters[24].Value:=noso.Text;
           Parameters[25].Value:=urutso.Text;
         end;
         Parameters[30].Value:=TglSelesai.Date;
               Parameters[31].Value:=JmlCetak.AsFloat ;
       Parameters[32].Value:=jenis.ItemIndex ;

       try
          ExecProc;
          if Choice='I' then
          begin
            TampilDataMesin;
            //QuBeli.Locate('KodeBrg',KodeBrg.Text,[LOP,LOC]);
            LoggingData(IDUser,Choice,'SPK - Mesin',NoBukti.Text,
                    ' Mesin = '+QuotedStr(kodeMSn.Text)+
                    ' Qnt SPK= '+QuotedStr(QntSPK.Text) +
                    ' Barang Jadi = '+QuotedStr(KodeBrg.Text))
                    ;

          end else if Choice='U' then
          begin
            TampilDataMesin;
            if QuMesin.BookmarkValid(BM) then
            begin
              try
                 QuMesin.GotoBookmark(BM);
              finally
                 QuMesin.FreeBookmark(BM);
              end
            end else
            begin
              QuMesin.FreeBookmark(BM);
              //QuBeli.Last;
              //QuBeli.Locate('Urut', mUrut, []);
            end;
            //QuBeli.Locate('Urut', mUrut, []);
            LoggingData(IDUser,Choice,'SPK - Mesin',NoBukti.Text,
                    ' Mesin = '+QuotedStr(kodeMSn.Text)+
                    ' Qnt = '+QuotedStr(QntSPK.Text) +
                    ' Barang Jadi = '+QuotedStr(KodeBrg.Text));
          end
          Else if Choice='D' then
          begin
            LoggingData(IDUser,Choice,'SPK - Mesin',NoBukti.Text,
                    ' Mesin = '+QuotedStr(QuMesinKODEMSN.AsString)+
                    ' Qnt = '+QuotedStr(QuMesinQNTSPK.AsString)  +
                    ' Barang Jadi = '+QuotedStr(KodeBrg.Text));
            TampilDataMesin;
          end;
          //Hitung;
          QuMesin.Locate('Urut', mUrut, []);
       except
          If (Choice='I') Or (Choice='U') then
             showmessage('Proses Gagal !                   ')
          else If (Choice='D') then
             showmessage('Proses Gagal !, Barang berkaitan dengan transaksi lain.');
       end;

   end;
end;



procedure TFrSPK.SpeedButton7Click(Sender: TObject);
begin
  mValid:=false;
  TampilTombolDetailPrs(False);
  ActiveControl:=dxDBGrid3;
  ClearPanelDetailPrs;
end;

procedure TFrSPK.TampilIsiMesin;
begin
  KodeBrows:=91114;
  Application.CreateForm(TFrBrows, FrBrows);
  FrBrows.IsiData:=kodemsn.Text;
  FrBrows.ShowModal;
  if FrBrows.ModalResult=mrOk then
  begin
    Kodemsn.Text:=FrBrows.QuBrows.FieldByName('KodeMsn').AsString;
    NmMsn.Caption:='[ '+FrBrows.QuBrows.FieldByName('Ket').AsString+' ]';
    trfmsn := FrBrows.QuBrows.FieldByName('Tarif').AsInteger;
  end
  else
    ActiveControl:=KodeMsn;
end;

procedure TFrSPK.kodeMSnExit(Sender: TObject);
begin
  if mValid Then
  begin
     if Length(Kodemsn.Text)=0 then TampilIsiMesin else
     begin
        {if DataBersyarat('select KodeMsn,ket from dbMesin where KodeMsn=:0',[KodeMesin.Text], DM.QuCari)=True then
        begin
          KodeMesin.Text:=DM.QuCari.fieldbyname('KodeMsn').AsString;
          NamaMesin.Caption:='[ '+DM.QuCari.FieldByName('Ket').AsString+' ]';
        end
        else TampilIsiMesin;   }
     end;
  end;
end;

procedure TFrSPK.kodeMSnEnter(Sender: TObject);
begin
mValid:=True;
end;

procedure TFrSPK.JamTenakerExit(Sender: TObject);
begin
     TrfMesin.Value :=trfmsn*JamTenaker.Value;
end;

procedure TFrSPK.TampilISiKaryawan;
begin
 KodeBrows:=15780;
 Application.CreateForm(TFrBrows, FrBrows);
 FrBrows.ShowModal;
 if FrBrows.ModalResult=mrOk then
 begin
     //NIk.Text:=IntToStr(FrBrows.QuBrows.FieldByName('NIK').as);
     TrfTenaker.Text:=FrBrows.QuBrows.FieldByName('NIK').AsString;
     Ltk1.Caption:='[ '+FrBrows.QuBrows.FieldByName('Nama').AsString+' ]';
     TarifTenaker.Value :=FrBrows.QuBrows.FieldByName('Tarif').AsInteger;
     //Keynik:= FrBrows.QuBrows.FieldByName('Keynik').AsInteger;
 end
 else
    ActiveControl:=TrfTenaker;

end;

procedure TFrSPK.TrfTenakerExit(Sender: TObject);
begin
  if mValid then
  begin
      if Length(TrfTenaker.Text)=0 then TampilIsiKaryawan else
      begin
          if DataBersyarat('Select a.nik, b.nama  from dbproduksitenaker a      '+
                           'Left Outer Join dbkaryawan b on b.nik =a.nik '+
                           //'on a.NoBuKti=b.NoPo left Outer Join dbBarang c on a.Kodebrg=c.Kodebrg ' +
                           'where A.Nik=:0 ',
                     [TrfTenaker.Text], DM.QuCari)=True then
        begin
             TrfTenaker.Text:=DM.QuCari.FieldByName('Nik').AsString;
             LTK1.Caption:='[ '+DM.QuCari.FieldByName('nama').AsString+' ]';
             //TftTenaker1.Value := DM.QuCari.FieldByName('Nik').AsString;
             {Qnt.MaxValue:=DM.QuCari.FieldByName('QntSisa').AsFloat+0.01;
             Qnt.AsFloat:=DM.QuCari.FieldByName('QntSisa').AsFloat;
             Harga.AsFloat:=DM.QuCari.FieldByName('Harga').AsFloat;
             IsiSatuan;}
             //TarifTenaker.value:=DM.QuCari.FieldByName('Nik').AsString;
        end
        {else
             TampilIsiKaryawan;}
      end;
  end;
  mvalid:=false;

end;

procedure TFrSPK.TrfTenakerEnter(Sender: TObject);
begin
  mValid:=True;

end;

end.
