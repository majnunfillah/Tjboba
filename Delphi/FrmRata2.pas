unit FrmRata2;

interface

uses
  Windows, Messages, SysUtils, Classes, Graphics, Controls, Forms, Dialogs,
  StdCtrls, PBNumEdit, Db, ADODB, ComCtrls, ExtCtrls, Gauges,Buttons,
  XiProgressBar, RxGIF, jpeg, Math, dxTL, dxDBCtrl, dxDBGrid, dxCntner,
  ShellApi, StrUtils;

type
  TSaveMethod = procedure (const FileName: String; ASaveAll: Boolean) of object;

  TFrRata2 = class(TForm)
    Bulan: TPBNumEdit;
    Tahun: TPBNumEdit;
    Shape1: TShape;
    Bevel1: TBevel;
    Label1: TLabel;
    BitBtn1: TBitBtn;
    XiProgressBar1: TXiProgressBar;
    Image2: TImage;
    BitBtn2: TBitBtn;
    Bevel2: TBevel;
    Label2: TLabel;
    MySP: TADOStoredProc;
    ComboBox1: TComboBox;
    Awal: TEdit;
    Label24: TLabel;
    Akhir: TEdit;
    QuTempHPP: TADOQuery;
    QuTempHPPKodeBrg: TStringField;
    QuTempHPPNQnt: TBCDField;
    QuTempHPPNRp: TBCDField;
    QuTempHPPNHrgRata: TBCDField;
    QuTempHPPNamaBrg: TStringField;
    QuBeli: TADOQuery;
    QuBeliIDUser: TStringField;
    QuBeliUrut: TIntegerField;
    QuBeliKodeGdg: TStringField;
    QuBeliKodeBrg: TStringField;
    QuBeliKodeBng: TStringField;
    QuBeliKodeJenis: TStringField;
    QuBeliKodeWarna: TStringField;
    QuBeliJenisBahan: TStringField;
    DsQuBeli: TDataSource;
    dxDBGrid1: TdxDBGrid;
    dxDBGrid1IDUser: TdxDBGridMaskColumn;
    dxDBGrid1Urut: TdxDBGridMaskColumn;
    dxDBGrid1JenisBahan: TdxDBGridMaskColumn;
    dxDBGrid1KodeGdg: TdxDBGridMaskColumn;
    dxDBGrid1KodeBrg: TdxDBGridMaskColumn;
    dxDBGrid1KodeBng: TdxDBGridMaskColumn;
    dxDBGrid1KodeJenis: TdxDBGridMaskColumn;
    dxDBGrid1KodeWarna: TdxDBGridMaskColumn;
    Label4: TLabel;
    SpeedButton1: TSpeedButton;
    SaveDialog: TSaveDialog;
    procedure FormShow(Sender: TObject);
    procedure FormKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure FormClose(Sender: TObject; var Action: TCloseAction);
    procedure BitBtn1Click(Sender: TObject);
    procedure BitBtn2Click(Sender: TObject);
    procedure ComboBox1Change(Sender: TObject);
    procedure AwalKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure AkhirKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure SpeedButton1Click(Sender: TObject);
  private
    { Private declarations }
    strBulan, strTahun: String;
    xHarga: Real;
    iUrutStockMinus: Integer;
    procedure Save(ADefaultExt, AFilter, AFileName: String; AMethod: TSaveMethod);
  public
    { Public declarations }
    IsTambah,IsKoreksi,IsHapus, IsCetak, isExcel : Integer;
    Myharga : Real;
    MyWip,MyHPP :real;
    Procedure ProsesBahan(xBulan,xTahun:integer; xKodebrg1,xkodebrg2:String);
    Procedure InHPPtoTRS(xBulan,xTahun:integer; xKodebrg1,xkodebrg2:String); overload;
    Procedure InHPPtoTRS(xNobukti, xUrut, xTipe: string; xHarga: Real); overload;
    Procedure ProsesKemasan(xBulan,xTahun:Integer);
    Procedure ProsesAkhirBulan(xBulan,xTahun:integer);
    procedure TampilIsiBarang;
    procedure TampilIsiBarang2;
    procedure ProsesINVLOG(xBulan,xTahun:Integer);
  end;

var
  FrRata2: TFrRata2;

implementation

uses FrmBrows,MyProcedure,MyGlobal,MyModul;

{$R *.DFM}

procedure TFrRata2.Save(ADefaultExt, AFilter, AFileName: String; AMethod: TSaveMethod);
begin
  with SaveDialog do
  begin
    DefaultExt := ADefaultExt;
    Filter := AFilter;
    FileName := AFileName;
    if Execute then
      AMethod(FileName, true);
  end;
end;

procedure TFrRata2.ProsesINVLOG(xBulan,xTahun:Integer);
begin
end;

Procedure TFrRata2.ProsesBahan(xBulan,xTahun:integer; xKodebrg1,xkodebrg2:String);
var xHrgRata, xQntMasuk, xQntKeluar, xRpMasuk, xRpKeluar, xSaldo, xSaldoRp: Real;
    xTipe, xKodeBrg, xKodeGdg, xGdgAsal, xSQLWhere: String;
    xRefNoBukti, xJenisBahan, xRefKodeBrg, xRefKodeWarna: String;
    xRefUrut: Integer;
    xHPPADI_OK: Boolean;
begin
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('Select distinct a.KodeBrg, b.NamaBrg ');
    SQL.Add('from vwKartuStock a');
    SQL.Add('left outer join dbBarang b on b.KodeBrg=a.KodeBrg ');
    SQL.Add('where a.Tahun='+IntToStr(Tahun.AsInteger)+' and a.Bulan='+IntToStr(Bulan.AsInteger));
    if ComboBox1.ItemIndex=1 then
      SQL.Add('and a.KodeBrg between '+QuotedStr(Awal.Text)+' and '+QuotedStr(Akhir.Text));
    SQL.Add('order by a.KodeBrg ');
    Open;
  end;

  if not DM.QuCari.IsEmpty then
  begin
    XiProgressBar1.Position :=0;
    XiProgressBar1.Max:=DM.QuCari.RecordCount;
    DM.QuCari.DisableControls;
    DM.QuCari.First;
    while not dm.QuCari.Eof do
    begin
      Application.ProcessMessages;
      Label2.Caption := 'Proses Hitung HPP '+#13+
                        'Kode Barang : '+ DM.QuCari.Fieldbyname('kodebrg').AsString +#13+
                        'Nama Barang : '+DM.QuCari.Fieldbyname('Namabrg').AsString;
      XiProgressBar1.Position:=DM.QuCari.RecNo;

      xBulan:=Bulan.AsInteger;
      xTahun:=Tahun.AsInteger;
      xKodeBrg:=DM.QuCari.FieldByName('KodeBrg').AsString;
      xKodeGdg:='';
      xSaldo:=0;
      xSaldoRp:=0;
      xHrgRata:=0;
      with DM.QuKOde do
      begin
        Close;
        SQL.Clear;
        SQL.Add('delete dbTempRata2');
        ExecSQL;
        SQL.Clear;
        SQL.Add('select MyTipe Tipe, Prioritas, KodeBrg, KodeGdg, QntSaldo, HrgSaldo, Tanggal, NoBukti, Urut ');
        SQL.Add('from vwKartuStock ');
        SQL.Add('Where Bulan='+IntToStr(xBulan)+' and Tahun='+IntToStr(xTahun)+' and KodeBrg='+QuotedStr(xKodeBrg));
        //sql  sql.add (' mytipe <>'''PNJ'');
        SQL.Add('Order by Tanggal, Prioritas, NoBukti, Urut, case when MyTipe=''TRO'' or Tipe=''PBO'' then 0 else 1 end  ');
        Open;
      end;
      if not DM.QuKOde.IsEmpty then
      begin
        while not DM.QuKOde.Eof do
        begin
          if xKodeGdg<>DM.QuKOde.Fieldbyname('KodeGdg').AsString then
          begin
            if xKodeGdg<>'' then
            begin
              if DataBersyarat('select * from dbTempRata2 where KodeGdg=:0',[xKodeGdg],DM.QueryBrow)=True then
              begin
                with DM.QueryBrow do
                begin
                  Close;
                  SQL.Clear;
                  SQL.Add('update dbTempRata2 set QntSaldo=:0, HrgSaldo=:1 ');
                  SQL.Add('where KodeGdg='+QuotedStr(xKodeGdg));
                  Prepared;
                  Parameters[0].Value:=xSaldo;
                  Parameters[1].Value:=xSaldoRp;
                  ExecSQL;
                end;
              end else
              begin
                with DM.QueryBrow do
                begin
                  Close;
                  SQL.Clear;
                  SQL.Add('insert into dbTempRata2 (KodeGdg,QntSaldo,HrgSaldo) values('+QuotedStr(xKodeGdg)+',:0, :1) ');
                  Prepared;
                  Parameters[0].Value:=xSaldo;
                  Parameters[1].Value:=xSaldoRp;
                  ExecSQL;
                end;
              end;
            end;
            if xKodeGdg='' then
            begin
              xKodeGdg:=DM.QuKOde.Fieldbyname('KodeGdg').AsString;
            end else
            begin
              xKodeGdg:=DM.QuKOde.Fieldbyname('KodeGdg').AsString;
              with DM.QueryBrow do
              begin
                Close;
                SQL.Clear;
                SQL.Add('select * from dbTempRata2 where KodeGdg='+QuotedStr(xKodeGdg));
                Open;
                if IsEmpty then
                begin
                  xHrgRata:=0;
                  xSaldo:=0;
                  xSaldoRp:=0;
                end else
                begin
                  xHrgRata:=FieldByName('HrgRata').AsFloat;
                  xSaldo:=FieldByName('QntSaldo').AsFloat;
                  xSaldoRp:=FieldByName('HrgSaldo').AsFloat;
                end;
              end;
            end;
          end;
          xTipe:=DM.QuKOde.FieldByName('Tipe').AsString;
          if (xTipe='AWL') or (xTipe='PBL') or (xTipe='ADI')  or (xTipe='TRI') {or (xTipe='HP')  or (xTipe='RPJ') }
            or (xTipe='UKI') or (xTipe='RPK') or (xTipe='PBI') then
          begin
            xQntMasuk:=DM.QuKOde.Fieldbyname('QntSaldo').AsFloat;
            if (xTipe='RPJ') then
            begin
              xSQLWhere:=FormatDateTime('MM/dd/yyyy',DM.QuKode.Fieldbyname('Tanggal').AsDateTime);
              xRefNoBukti:=DM.QuKOde.FieldByName('NoBukti').AsString;
              xRefUrut:=DM.QuKOde.FieldByName('Urut').AsInteger;
              {if DataBersyarat('select isnull(D.HPP,0) HPP from dbSPBRJualDet A '+
                ' left outer join dbSPBRJual A0 on A0.NoBukti=A.NoBukti '+
                ' left outer join DBRInvoicePLDet B on B.NOBUKTI=A.NoRPJ and B.Urut=A.UrutRPJ '+
                ' left outer join dbInvoicePLDet C on C.NoBukti=B.NoInvoice and C.Urut=B.UrutInvoice '+
                ' left outer join dbSPBDet D on D.NoBukti=C.NoSPB and D.Urut=C.UrutSPB '+
                ' where A.NoBukti=:0 and A.Urut=:1 and A0.Tanggal<='+QuotedStr(xSQLWhere),
                [xRefNoBukti, xRefUrut],DM.QueryBrow)=True then}
                if DataBersyarat('select isnull(D.HPP,0) HPP from dbSPBRJualDet A '+
                ' left outer join dbSPBRJual A0 on A0.NoBukti=A.NoBukti '+
                ' left outer join DBINVOICERPJDet B on B.NOspr=A.Nobukti and B.Urutspr=A.Urut '+
                ' left outer join dbInvoicePLDet C on C.NoBukti=a.NoInv and C.Urut=a.UrutInv '+
                ' left outer join dbSPBDet D on D.NoBukti=C.NoSPB and D.Urut=C.UrutSPB '+
                ' where A.NoBukti=:0 and A.Urut=:1 and A0.Tanggal<='+QuotedStr(xSQLWhere),
                [xRefNoBukti, xRefUrut],DM.QueryBrow)=True then
              begin
                xHrgRata:=DM.QueryBrow.FieldByName('HPP').AsFloat;
              end else
                xHrgRata:=0;
              xRpMasuk:=xQntMasuk*xHrgRata;
              InHPPtoTRS(DM.QuKOde.Fieldbyname('NoBukti').AsString,DM.QuKOde.Fieldbyname('Urut').AsString, xTipe, xHrgRata);
            end else
            if (xTipe='RPK') then
            begin
              xSQLWhere:=FormatDateTime('MM/dd/yyyy',DM.QuKode.Fieldbyname('Tanggal').AsDateTime);
              xRefNoBukti:=DM.QuKOde.FieldByName('NoBukti').AsString;
              xRefUrut:=DM.QuKOde.FieldByName('Urut').AsInteger;
              if DataBersyarat('select isnull(round(B.HPP,0),0)/*isnull(B.HPP,0)*/ HPP from DBRPenyerahanBhndet A '+
                ' left outer join DBRPenyerahanBhn A0 on A0.NoBukti=A.NoBukti '+
                ' left outer join DBPenyerahanBhndet B on B.NOBUKTI=A.NoPenyerahanBhn and B.Urut=A.UrutPenyerahanBhn'+
                ' where A.NoBukti=:0 and A.Urut=:1 and A0.Tanggal<='+QuotedStr(xSQLWhere),
                [xRefNoBukti, xRefUrut],DM.QueryBrow)=True then
              begin
                xHrgRata:=DM.QueryBrow.FieldByName('HPP').AsFloat;
              end else
                xHrgRata:=0;
              xRpMasuk:=xQntMasuk*xHrgRata;
              InHPPtoTRS(DM.QuKOde.Fieldbyname('NoBukti').AsString,DM.QuKOde.Fieldbyname('Urut').AsString, xTipe, xHrgRata);
            end else
            if (xTipe='HP') then
            begin
                DataBersyarat('select isnull(HPPBrg,0) HPP from dbHPPProduksi where KodeBrg=:1 and Tahun='+IntToStr(Tahun.AsInteger)+' and Bulan='+IntToStr(Bulan.AsInteger),
                    [xKodebrg],DM.QueryBrow);
                if DM.QueryBrow.IsEmpty then
                begin
                  xRpMasuk:=0;
                  with DM.QuCari2 do
                  begin
                      Close;
                      SQL.Clear;
                      //SQL.Add('alter table DBHASILPRDDet disable trigger Trg_UPD_DbHASILPRDdet ');
                      SQL.Add('Update DBHASILPRDDet set HPP=:0 ');
                      SQL.Add('where NoBukti='+QuotedStr(DM.QuKOde.FieldByName('NoBukti').AsString)+' and Urut='+IntToStr(DM.QuKOde.FieldByName('Urut').AsInteger));
                      //SQL.Add('alter table DBHASILPRDDet disable trigger Trg_UPD_DbHASILPRDdet ');
                      Prepared;
                      Parameters[0].Value:=0;
                      ExecSQL;
                  end;
                end else
                begin
                  xRpMasuk:=DM.QueryBrow.FieldByName('HPP').AsFloat*xQntMasuk;
                  with DM.QuCari2 do
                  begin
                      Close;
                      SQL.Clear;
                      //SQL.Add('alter table DBHASILPRDDet disable trigger Trg_UPD_DbHASILPRDdet ');
                      SQL.Add('Update DBHASILPRDDet set HPP=:0 ');
                      SQL.Add('where NoBukti='+QuotedStr(DM.QuKOde.FieldByName('NoBukti').AsString)+' and Urut='+IntToStr(DM.QuKOde.FieldByName('Urut').AsInteger));
                      //SQL.Add('alter table DBHASILPRDDet disable trigger Trg_UPD_DbHASILPRDdet ');
                      Prepared;
                      Parameters[0].Value:=DM.QueryBrow.FieldByName('HPP').AsFloat;
                      ExecSQL;
                  end;
                end;
            end else
            if (xTipe='ADI') then
            begin
              xHPPADI_OK:=False;
              DataBersyarat('select isnull(Harga,0) HPP from dbKoreksiDet where NoBukti=:0 and Urut=:1 and Harga<>0',
              [DM.QuKOde.FieldByName('NoBukti').AsString,DM.QuKOde.FieldByName('Urut').AsInteger],DM.QueryBrow);
              if (xHPPADI_OK=False) and (not DM.QueryBrow.IsEmpty) then
              begin
                  xRpMasuk:=DM.QueryBrow.FieldByName('HPP').AsFloat*xQntMasuk;
                  xHrgRata:=DM.QueryBrow.FieldByName('HPP').AsFloat;
                  {with DM.QuCari2 do
                  begin
                      Close;
                      SQL.Clear;
                      SQL.Add('alter table DBKoreksiDet disable trigger TRI_UPD_DBKOREKSIDET ');
                      SQL.Add('Update DBKoreksiDet set HPP=:0 ');
                      SQL.Add('where NoBukti='+QuotedStr(DM.QuKOde.FieldByName('NoBukti').AsString)+' and Urut='+IntToStr(DM.QuKOde.FieldByName('Urut').AsInteger));
                      SQL.Add('alter table DBKoreksiDet disable trigger TRI_UPD_DBKOREKSIDET ');
                      Prepared;
                      Parameters[0].Value:=DM.QueryBrow.FieldByName('HPP').AsFloat;
                      ExecSQL;
                  end;}
                  xHPPADI_OK:=True;
              end;

              if xHPPADI_OK=False then
              begin
                DataBersyarat('select isnull(HPPBrg,0) HPP from dbHPPProduksi where KodeBrg=:0 and Tahun='+IntToStr(Tahun.AsInteger)+' and Bulan='+IntToStr(Bulan.AsInteger),
                [xKodebrg],DM.QueryBrow);
                if DM.QueryBrow.IsEmpty then
                begin
                  xRpMasuk:=0;
                  xHrgRata:=0;
                  {with DM.QuCari2 do
                  begin
                      Close;
                      SQL.Clear;
                      SQL.Add('alter table DBKoreksiDet disable trigger TRI_UPD_DBKOREKSIDET ');
                      SQL.Add('Update DBKoreksiDet set HPP=:0 ');
                      SQL.Add('where NoBukti='+QuotedStr(DM.QuKOde.FieldByName('NoBukti').AsString)+' and Urut='+IntToStr(DM.QuKOde.FieldByName('Urut').AsInteger));
                      SQL.Add('alter table DBKoreksiDet disable trigger TRI_UPD_DBKOREKSIDET ');
                      Prepared;
                      Parameters[0].Value:=0;
                      ExecSQL;
                  end;}
                end else
                begin
                  xRpMasuk:=DM.QueryBrow.FieldByName('HPP').AsFloat*xQntMasuk;
                  xHrgRata:=DM.QueryBrow.FieldByName('HPP').AsFloat;
                  {with DM.QuCari2 do
                  begin
                      Close;
                      SQL.Clear;
                      SQL.Add('alter table DBKoreksiDet disable trigger TRI_UPD_DBKOREKSIDET ');
                      SQL.Add('Update DBKoreksiDet set HPP=:0 ');
                      SQL.Add('where NoBukti='+QuotedStr(DM.QuKOde.FieldByName('NoBukti').AsString)+' and Urut='+IntToStr(DM.QuKOde.FieldByName('Urut').AsInteger));
                      SQL.Add('alter table DBKoreksiDet disable trigger TRI_UPD_DBKOREKSIDET ');
                      Prepared;
                      Parameters[0].Value:=DM.QueryBrow.FieldByName('HPP').AsFloat;
                      ExecSQL;
                  end;}
                end;
                xHPPADI_OK:=True;
              end;

              if xHPPADI_OK=False then
              begin
                xSQLWhere:=FormatDateTime('yyyy.MM.dd',DM.QuKOde.Fieldbyname('Tanggal').AsDateTime)+LeftStr(DM.QuKOde.Fieldbyname('Prioritas').AsString+'00000',5)+DM.QuKOde.Fieldbyname('Nobukti').AsString+RightStr('00000'+DM.QuKOde.Fieldbyname('Urut').AsString,5);
                if xSaldo=0 then
                begin
                  if DataBersyarat('select top 1 Tanggal, Prioritas, NoBukti, Urut, HPP from vwKartuStock '+
                    ' where convert(varchar(10),Tanggal,102)+left(Prioritas+''00000'',5)+NoBukti+right(''00000''+cast(Urut as varchar(5)),5)<:0 '+
                    ' and KodeBrg='+QuotedStr(xKodebrg)+' and KodeGdg='+QuotedStr(xKodegdg)+
                    ' order by Tanggal desc, Prioritas desc, NoBukti desc, Urut desc ',
                    [xSQLWhere],DM.QueryBrow)=True then
                  begin
                    xRpMasuk:=DM.QueryBrow.FieldByName('HPP').AsFloat*xQntMasuk;
                    xHrgRata:=DM.QueryBrow.FieldByName('HPP').AsFloat;
                  end else
                  if DataBersyarat('select top 1 Tanggal, Prioritas, NoBukti, Urut, HPP from vwKartuStock '+
                    ' where convert(varchar(10),Tanggal,102)+left(Prioritas+''00000'',5)+NoBukti+right(''00000''+cast(Urut as varchar(5)),5)<:0 '+
                    ' and KodeBrg='+QuotedStr(xKodebrg)+
                    ' order by Tanggal desc, Prioritas desc, NoBukti desc, Urut desc ',
                    [xSQLWhere],DM.QueryBrow)=True then
                  begin
                    xRpMasuk:=DM.QueryBrow.FieldByName('HPP').AsFloat*xQntMasuk;
                    xHrgRata:=DM.QueryBrow.FieldByName('HPP').AsFloat;
                  end;
                end else
                begin
                  xRpMasuk:=xQntMasuk*xHrgRata;
                end;
              end;

              with DM.QuCari2 do
              begin
                Close;
                SQL.Clear;
                SQL.Add('alter table DBKoreksiDet disable trigger TRI_UPD_DBKOREKSIDET ');
                SQL.Add('Update DBKoreksiDet set HPP=:0 ');
                SQL.Add('where NoBukti='+QuotedStr(DM.QuKOde.FieldByName('NoBukti').AsString)+' and Urut='+IntToStr(DM.QuKOde.FieldByName('Urut').AsInteger));
                SQL.Add('alter table DBKoreksiDet disable trigger TRI_UPD_DBKOREKSIDET ');
                Prepared;
                Parameters[0].Value:=xHrgRata;
                ExecSQL;
              end;
            end
            else
            if (xTipe='TRI') or (xTipe='PBI') then
            begin
                DataBersyarat('select HrgSaldo from vwKartuStock where NoBukti=:0 and KodeBrg=:1 and Urut=:2',
                    [DM.QuKOde.FieldByName('NoBukti').AsString,xKodebrg,DM.QuKOde.FieldByName('Urut').AsInteger],DM.QueryBrow);
                xRpMasuk:=DM.QueryBrow.FieldByName('HrgSaldo').AsFloat;
            end
            else
              xRpMasuk:=DM.QuKOde.Fieldbyname('HrgSaldo').AsFloat;
            xSaldo:=xSaldo+xQntMasuk;
            xSaldoRp:=xSaldoRp+xRpMasuk;
            if xSaldo<>0 then
                xHrgRata:=roundto(xSaldoRp/xSaldo,0)  //RoundTo(xharga,0)
            else
                xHrgRata:=0;
          end
          else
          begin
            if (xSaldo)<>0 then
                xHrgRata:=roundto((xSaldoRp)/(xSaldo),0)      //RoundTo(xharga,0)
                //xHrgRata:=((xSaldoRp)/(xSaldo))
            else
            begin
                xHrgRata:=0;
            end;
            xSaldo:=xSaldo+DM.QuKOde.Fieldbyname('QntSaldo').AsFloat;
            //xSaldoRp:=xSaldoRp+(DM.QuKOde.Fieldbyname('QntSaldo').AsFloat*xHrgRata);
            xSaldoRp:=xSaldoRp+roundto((DM.QuKOde.Fieldbyname('QntSaldo').AsFloat*xHrgRata),0  )
          end;
          if (xTipe='RPB')  or (xTipe='TRO') or (xTipe='UKO') or (xTipe='ADO') or (xTipe='PMK')   //  or (xTipe='PNJ')
            or (xTipe='PBO') then
            InHPPtoTRS(DM.QuKOde.Fieldbyname('Nobukti').AsString, DM.QuKOde.Fieldbyname('Urut').AsString, xTipe, xHrgRata);
          if RoundTo(xSaldo,-4)<0 then
          begin
            xJenisBahan:='';
            DataBuka('select * from TempStockMinus where IDUser='+QuotedStr(IDUser)+' and KodeBrg='+QuotedStr(xKodeBrg), DM.QuCari2);
            if DM.QuCari2.IsEmpty then
            begin
              iUrutStockMinus:=iUrutStockMinus+1;
              with DM.QuCari2 do
              begin
                Close;
                SQL.Clear;
                SQL.Add('insert into TempStockMinus (IDUser, Urut, JenisBahan, KodeGdg, KodeBrg, KodeBng, KodeJenis, KodeWarna) ');
                SQL.Add('values ('+QuotedStr(IDUser)+','+IntToStr(iUrutStockMinus)+','+QuotedStr(xJenisBahan)+','+QuotedStr(xKodeGdg)+','+QuotedStr(xKodeBrg)+','''','''','+QuotedStr(xTipe)+')');
                ExecSQL;
              end;
              QuBeli.Close;
              QuBeli.Open;
              QuBeli.Locate('Urut',iUrutStockMinus,[]);
            end;
          end;
          DM.QuKOde.Next;
        end;
        xHarga:=xHrgRata;
      end;
      DM.QuCari.Next;
    end;
    DM.QuCari.EnableControls;
  end;
end;

Procedure TFrRata2.InHPPtoTRS(xBulan,xTahun:integer; xKodebrg1,xkodebrg2:String);
var xLeftKodeBrg: String;
begin
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('Select distinct left(a.KodeBrg,2) KodeBrg ');
    SQL.Add('from vwKartuStock a');
    SQL.Add('left outer join dbBarang b on b.KodeBrg=a.KodeBrg ');
    SQL.Add('where a.Tahun='+IntToStr(Tahun.AsInteger)+' and a.Bulan='+IntToStr(Bulan.AsInteger));
    if ComboBox1.ItemIndex=1 then
      SQL.Add('and a.KodeBrg between '+QuotedStr(Awal.Text)+' and '+QuotedStr(Akhir.Text));
    SQL.Add('order by left(a.KodeBrg,2) ');
    Open;
  end;
  if not DM.QuCari.IsEmpty then
  begin
    XiProgressBar1.Position :=0;
    XiProgressBar1.Max:=DM.QuCari.RecordCount;
    DM.QuCari.DisableControls;
    DM.QuCari.First;
    while not DM.QuCari.Eof do
    begin
      xLeftKodeBrg:=DM.QuCari.FieldByName('KodeBrg').AsString;
      //Caption:=xLeftKodeBrg;
      Application.ProcessMessages;
      //Label2.Caption := 'Proses Hitung HPP '+#13+
      //                  'Kode Barang : '+ DM.QuCari.Fieldbyname('kodebrg').AsString +#13+
      //                  'Nama Barang : '+DM.QuCari.Fieldbyname('Namabrg').AsString;
      XiProgressBar1.Position:=DM.QuCari.RecNo;
      with DM.QuKOde do
      begin
        Close;
        SQL.Clear;
        SQL.Add('update dbStockBrg set QntPBL=isnull(b.QntPBL,0), Qnt2PBL=isnull(b.Qnt2PBL,0), HrgPBL=isnull(b.HrgPBL,0), ');
        SQL.Add('  QntRPB=isnull(b.QntRPB,0), Qnt2RPB=isnull(b.Qnt2RPB,0), HrgRPB=isnull(b.HrgRPB,0), ');
        SQL.Add('  QntPNJ=isnull(b.QntPNJ,0), Qnt2PNJ=isnull(b.Qnt2PNJ,0), HrgPNJ=isnull(b.HrgPNJ,0), ');
        SQL.Add('  QntRPJ=isnull(b.QntRPJ,0), Qnt2RPJ=isnull(b.Qnt2RPJ,0), HrgRPJ=isnull(b.HrgRPJ,0), ');
        SQL.Add('  QntADI=isnull(b.QntADI,0), Qnt2ADI=isnull(b.Qnt2ADI,0), HrgADI=isnull(b.HrgADI,0), ');
        SQL.Add('  HRGMADI=isnull(b.HRGMADI,0), ');
        SQL.Add('  QntADO=isnull(b.QntADO,0), Qnt2ADO=isnull(b.Qnt2ADO,0), HrgADO=isnull(b.HrgADO,0), ');
        SQL.Add('  HRGMADO=isnull(b.HRGMADO,0), ');
        SQL.Add('  QntUKI=isnull(b.QntUKI,0), Qnt2UKI=isnull(b.Qnt2UKI,0), HrgUKI=isnull(b.HrgUKI,0), ');
        SQL.Add('  QntUKO=isnull(b.QntUKO,0), Qnt2UKO=isnull(b.Qnt2UKO,0), HrgUKO=isnull(b.HrgUKO,0), ');
        SQL.Add('  QntTRI=isnull(b.QntTRI,0), Qnt2TRI=isnull(b.Qnt2TRI,0), HrgTRI=isnull(b.HrgTRI,0), ');
        SQL.Add('  QntTRO=isnull(b.QntTRO,0), Qnt2TRO=isnull(b.Qnt2TRO,0), HrgTRO=isnull(b.HrgTRO,0), ');
        SQL.Add('  QntPMK=isnull(b.QntPMK,0), Qnt2PMK=isnull(b.Qnt2PMK,0), HrgPMK=isnull(b.HrgPMK,0), ');
        SQL.Add('  QntRPK=isnull(b.QntRPK,0), Qnt2RPK=isnull(b.Qnt2RPK,0), HrgRPK=isnull(b.HrgRPK,0), ');
        SQL.Add('  QntHPRD=isnull(b.QntHPRD,0), Qnt2HPRD=isnull(b.Qnt2HPRD,0), HrgHPRD=isnull(b.HrgHPRD,0) ');
        SQL.Add('from dbStockBrg a ');
        SQL.Add('left outer join ');
        SQL.Add('  ( ');
        SQL.Add('  select  Tahun, Bulan, KodeBrg, KodeGdg, ');
        SQL.Add('  sum(case when Tipe=''PBL'' then QntSaldo else 0 end) QntPBL, ');
        SQL.Add('  sum(case when Tipe=''PBL'' then Qnt2Saldo else 0 end) Qnt2PBL, ');
        SQL.Add('  sum(case when Tipe=''PBL'' then HrgSaldo else 0 end) HrgPBL, ');
        SQL.Add('  sum(case when Tipe=''RPB'' then -1*QntSaldo else 0 end) QntRPB, ');
        SQL.Add('  sum(case when Tipe=''RPB'' then -1*Qnt2Saldo else 0 end) Qnt2RPB, ');
        SQL.Add('  sum(case when Tipe=''RPB'' then -1*HrgSaldo else 0 end) HrgRPB, ');
        SQL.Add('  sum(case when Tipe=''PNJ'' then -1*QntSaldo else 0 end) QntPNJ, ');
        SQL.Add('  sum(case when Tipe=''PNJ'' then -1*Qnt2Saldo else 0 end) Qnt2PNJ, ');
        SQL.Add('  sum(case when Tipe=''PNJ'' then -1*HrgSaldo else 0 end) HrgPNJ, ');
        SQL.Add('  sum(case when Tipe=''RPJ'' then QntSaldo else 0 end) QntRPJ, ');
        SQL.Add('  sum(case when Tipe=''RPJ'' then Qnt2Saldo else 0 end) Qnt2RPJ, ');
        SQL.Add('  sum(case when Tipe=''RPJ'' then HrgSaldo else 0 end) HrgRPJ, ');
        SQL.Add('  sum(case when Tipe=''ADI'' then QntSaldo else 0 end) QntADI, ');
        SQL.Add('  sum(case when Tipe=''ADI'' then Qnt2Saldo else 0 end) Qnt2ADI, ');
        SQL.Add('  sum(case when Tipe=''ADI'' then HrgSaldo else 0 end) HrgADI, ');
        SQL.Add('  sum(case when Tipe=''MADI'' then HrgSaldo else 0 end) HrgMADI, '); //tambahan
        SQL.Add('  sum(case when Tipe=''ADO'' then -1*QntSaldo else 0 end) QntADO, ');
        SQL.Add('  sum(case when Tipe=''ADO'' then -1*Qnt2Saldo else 0 end) Qnt2ADO, ');
        SQL.Add('  sum(case when Tipe=''ADO'' then -1*HrgSaldo else 0 end) HrgADO, ');
        SQL.Add('  sum(case when Tipe=''MADO'' then -1*HrgSaldo else 0 end) HrgMADO, ');  //tambahan
        SQL.Add('  sum(case when Tipe=''UKI'' then QntSaldo else 0 end) QntUKI, ');
        SQL.Add('  sum(case when Tipe=''UKI'' then Qnt2Saldo else 0 end) Qnt2UKI, ');
        SQL.Add('  sum(case when Tipe=''UKI'' then HrgSaldo else 0 end) HrgUKI, ');
        SQL.Add('  sum(case when Tipe=''UKO'' then -1*QntSaldo else 0 end) QntUKO, ');
        SQL.Add('  sum(case when Tipe=''UKO'' then -1*Qnt2Saldo else 0 end) Qnt2UKO, ');
        SQL.Add('  sum(case when Tipe=''UKO'' then -1*HrgSaldo else 0 end) HrgUKO, ');
        SQL.Add('  sum(case when Tipe=''TRI'' then QntSaldo else 0 end) QntTRI, ');
        SQL.Add('  sum(case when Tipe=''TRI'' then Qnt2Saldo else 0 end) Qnt2TRI, ');
        SQL.Add('  sum(case when Tipe=''TRI'' then HrgSaldo else 0 end) HrgTRI, ');
        SQL.Add('  sum(case when Tipe=''TRO'' then -1*QntSaldo else 0 end) QntTRO, ');
        SQL.Add('  sum(case when Tipe=''TRO'' then -1*Qnt2Saldo else 0 end) Qnt2TRO, ');
        SQL.Add('  sum(case when Tipe=''TRO'' then -1*HrgSaldo else 0 end) HrgTRO, ');
        SQL.Add('  sum(case when Tipe=''PMK'' then -1*QntSaldo else 0 end) QntPMK, ');
        SQL.Add('  sum(case when Tipe=''PMK'' then -1*Qnt2Saldo else 0 end) Qnt2PMK, ');
        SQL.Add('  sum(case when Tipe=''PMK'' then -1*HrgSaldo else 0 end) HrgPMK, ');
        SQL.Add('  sum(case when Tipe=''RPK'' then QntSaldo else 0 end) QntRPK, ');
        SQL.Add('  sum(case when Tipe=''RPK'' then Qnt2Saldo else 0 end) Qnt2RPK, ');
        SQL.Add('  sum(case when Tipe=''RPK'' then HrgSaldo else 0 end) HrgRPK, ');
        SQL.Add('  sum(case when Tipe=''HP'' then QntSaldo else 0 end) QntHPRD, ');
        SQL.Add('  sum(case when Tipe=''HP'' then Qnt2Saldo else 0 end) Qnt2HPRD, ');
        SQL.Add('  sum(case when Tipe=''HP'' then HrgSaldo else 0 end) HrgHPRD ');
        SQL.Add('  from 	vwKartuStock where Tahun='+IntToStr(Tahun.AsInteger)+' and Bulan='+IntToStr(Bulan.AsInteger));
        SQL.Add('  and left(KodeBrg,2)='+QuotedStr(xLeftKodeBrg));
        //SQL.Add('  and kodebrg ='+QuotedStr(xLeftKodeBrg));
        SQL.Add('  group by Tahun, Bulan, KodeBrg, KodeGdg ');
        SQL.Add('  ) b on b.KodeBrg=a.KodeBrg and b.KodeGdg=a.KodeGdg and b.Tahun=a.Tahun and b.Bulan=a.Bulan ');
        //SQL.Add('left outer join dbBarang c on c.KodeBrg=a.KodeBrg and isnull(c.KodeWarna,'''')=isnull(a.KodeWarna,'''') ');
        SQL.Add('where a.Tahun='+IntToStr(Tahun.AsInteger)+' and a.Bulan='+IntToStr(Bulan.AsInteger));
        SQL.Add('  and left(a.KodeBrg,2)='+QuotedStr(xLeftKodeBrg));
        //SQL.Add('  and a.kodebrg='+QuotedStr(xLeftKodeBrg));
        sql.SaveToFile('c:\Hitungulang.txt');
        ExecSQL;
      end;

      Application.ProcessMessages;
      with DM.QuKOde do
      begin
        Close;
        SQL.Clear;
        SQL.Add('insert into dbStockBrg (TAHUN, BULAN, KODEBRG, KODEGDG, QNTAWAL, Qnt2AWAL, HRGAWAL, ');
        SQL.Add('  QNTPBL, Qnt2PBL, HRGPBL, QNTRPB, Qnt2RPB, HRGRPB, QNTPNJ, Qnt2PNJ, HRGPNJ, QNTRPJ, Qnt2RPJ, HRGRPJ, ');
        //SQL.Add('  QNTADI, Qnt2ADI, HRGADI, QNTADO, Qnt2ADO, HRGADO, QNTUKI, Qnt2UKI, HRGUKI, QNTUKO, Qnt2UKO, HRGUKO, ');  //asli
        SQL.Add('  QNTADI, Qnt2ADI, HRGADI,HRGMADI, QNTADO, Qnt2ADO, HRGADO,HRGMADO, QNTUKI, Qnt2UKI, HRGUKI, QNTUKO, Qnt2UKO, HRGUKO, ');
        SQL.Add('  QNTTRI, Qnt2TRI, HRGTRI, QNTTRO, Qnt2TRO, HRGTRO, ');
        SQL.Add('  QNTPMK, Qnt2PMK, HRGPMK, QNTRPK, Qnt2RPK, HRGRPK, QNTHPRD, Qnt2HPRD, HRGHPRD)');
        SQL.Add('select a.TAHUN, a.BULAN, a.KODEBRG, a.KODEGDG, 0 QNTAWAL, 0 Qnt2AWAL, 0 HRGAWAL, ');
        SQL.Add('  a.QNTPBL, a.Qnt2PBL, a.HRGPBL, a.QNTRPB, a.Qnt2RPB, a.HRGRPB, a.QNTPNJ, a.Qnt2PNJ, a.HRGPNJ, a.QNTRPJ, a.Qnt2RPJ, a.HRGRPJ, ');
        //SQL.Add('  a.QNTADI, a.Qnt2ADI, a.HRGADI, a.QNTADO, a.Qnt2ADO, a.HRGADO, a.QNTUKI, a.Qnt2UKI, a.HRGUKI, a.QNTUKO, a.Qnt2UKO, a.HRGUKO, ');   Asli
        SQL.Add('  a.QNTADI, a.Qnt2ADI, a.HRGADI,a.HRGMADI, a.QNTADO, a.Qnt2ADO, a.HRGADO,a.HRGMADO, a.QNTUKI, a.Qnt2UKI, a.HRGUKI, a.QNTUKO, a.Qnt2UKO, a.HRGUKO, ');
        SQL.Add('  a.QNTTRI, a.Qnt2TRI, a.HRGTRI, a.QNTTRO, a.Qnt2TRO, a.HRGTRO, ');
        SQL.Add('  a.QNTPMK, a.Qnt2PMK, a.HRGPMK, a.QNTRPK, a.Qnt2RPK, a.HRGRPK, a.QNTHPRD, a.Qnt2HPRD, a.HRGHPRD ');
        SQL.Add('from ');
        SQL.Add('  ( ');
        SQL.Add('  select  Tahun, Bulan, KodeBrg, KodeGdg, ');
        SQL.Add('  sum(case when Tipe=''PBL'' then QntSaldo else 0 end) QntPBL, ');
        SQL.Add('  sum(case when Tipe=''PBL'' then Qnt2Saldo else 0 end) Qnt2PBL, ');
        SQL.Add('  sum(case when Tipe=''PBL'' then HrgSaldo else 0 end) HrgPBL, ');
        SQL.Add('  sum(case when Tipe=''RPB'' then -1*QntSaldo else 0 end) QntRPB, ');
        SQL.Add('  sum(case when Tipe=''RPB'' then -1*Qnt2Saldo else 0 end) Qnt2RPB, ');
        SQL.Add('  sum(case when Tipe=''RPB'' then -1*HrgSaldo else 0 end) HrgRPB, ');
        SQL.Add('  sum(case when Tipe=''PNJ'' then -1*QntSaldo else 0 end) QntPNJ, ');
        SQL.Add('  sum(case when Tipe=''PNJ'' then -1*Qnt2Saldo else 0 end) Qnt2PNJ, ');
        SQL.Add('  sum(case when Tipe=''PNJ'' then -1*HrgSaldo else 0 end) HrgPNJ, ');
        SQL.Add('  sum(case when Tipe=''RPJ'' then QntSaldo else 0 end) QntRPJ, ');
        SQL.Add('  sum(case when Tipe=''RPJ'' then Qnt2Saldo else 0 end) Qnt2RPJ, ');
        SQL.Add('  sum(case when Tipe=''RPJ'' then HrgSaldo else 0 end) HrgRPJ, ');
        SQL.Add('  sum(case when Tipe=''ADI'' then QntSaldo else 0 end) QntADI, ');
        SQL.Add('  sum(case when Tipe=''ADI'' then Qnt2Saldo else 0 end) Qnt2ADI, ');
        SQL.Add('  sum(case when Tipe=''ADI'' then HrgSaldo else 0 end) HrgADI, ');
        SQL.Add('  sum(case when Tipe=''MADI'' then HrgSaldo else 0 end) HrgMADI, ');   //tambahan
        SQL.Add('  sum(case when Tipe=''ADO'' then -1*QntSaldo else 0 end) QntADO, ');
        SQL.Add('  sum(case when Tipe=''ADO'' then -1*Qnt2Saldo else 0 end) Qnt2ADO, ');
        SQL.Add('  sum(case when Tipe=''ADO'' then -1*HrgSaldo else 0 end) HrgADO, ');
        SQL.Add('  sum(case when Tipe=''MADO'' then -1*HrgSaldo else 0 end) HrgMADO, ');
        SQL.Add('  sum(case when Tipe=''UKI'' then QntSaldo else 0 end) QntUKI, ');
        SQL.Add('  sum(case when Tipe=''UKI'' then Qnt2Saldo else 0 end) Qnt2UKI, ');
        SQL.Add('  sum(case when Tipe=''UKI'' then HrgSaldo else 0 end) HrgUKI, ');
        SQL.Add('  sum(case when Tipe=''UKO'' then -1*QntSaldo else 0 end) QntUKO, ');
        SQL.Add('  sum(case when Tipe=''UKO'' then -1*Qnt2Saldo else 0 end) Qnt2UKO, ');
        SQL.Add('  sum(case when Tipe=''UKO'' then -1*HrgSaldo else 0 end) HrgUKO, ');
        SQL.Add('  sum(case when Tipe=''TRI'' then QntSaldo else 0 end) QntTRI, ');
        SQL.Add('  sum(case when Tipe=''TRI'' then Qnt2Saldo else 0 end) Qnt2TRI, ');
        SQL.Add('  sum(case when Tipe=''TRI'' then HrgSaldo else 0 end) HrgTRI, ');
        SQL.Add('  sum(case when Tipe=''TRO'' then -1*QntSaldo else 0 end) QntTRO, ');
        SQL.Add('  sum(case when Tipe=''TRO'' then -1*Qnt2Saldo else 0 end) Qnt2TRO, ');
        SQL.Add('  sum(case when Tipe=''TRO'' then -1*HrgSaldo else 0 end) HrgTRO, ');
        SQL.Add('  sum(case when Tipe=''PMK'' then -1*QntSaldo else 0 end) QntPMK, ');
        SQL.Add('  sum(case when Tipe=''PMK'' then -1*Qnt2Saldo else 0 end) Qnt2PMK, ');
        SQL.Add('  sum(case when Tipe=''PMK'' then -1*HrgSaldo else 0 end) HrgPMK, ');
        SQL.Add('  sum(case when Tipe=''RPK'' then QntSaldo else 0 end) QntRPK, ');
        SQL.Add('  sum(case when Tipe=''RPK'' then Qnt2Saldo else 0 end) Qnt2RPK, ');
        SQL.Add('  sum(case when Tipe=''RPK'' then HrgSaldo else 0 end) HrgRPK, ');
        SQL.Add('  sum(case when Tipe=''HP'' then -1*QntSaldo else 0 end) QntHPRD, ');
        SQL.Add('  sum(case when Tipe=''HP'' then -1*Qnt2Saldo else 0 end) Qnt2HPRD, ');
        SQL.Add('  sum(case when Tipe=''HP'' then -1*HrgSaldo else 0 end) HrgHPRD ');
        SQL.Add('  from 	vwKartuStock where Tahun='+IntToStr(Tahun.AsInteger)+' and Bulan='+IntToStr(Bulan.AsInteger));
        SQL.Add('    and left(KodeBrg,2)='+QuotedStr(xLeftKodeBrg));
        //SQL.Add('and kodebrg='+QuotedStr(xLeftKodeBrg));
        SQL.Add('  group by Tahun, Bulan, KodeBrg, KodeGdg ');
        SQL.Add('  ) a ');
        SQL.Add('left outer join dbStockBrg b on b.KodeBrg=a.KodeBrg and b.KodeGdg=a.KodeGdg and b.Tahun=a.Tahun and b.Bulan=a.Bulan ');
        SQL.Add('left outer join dbBarang c on c.KodeBrg=a.KodeBrg ');
        SQL.Add('left outer join dbGudang d on d.KodeGdg=a.KodeGdg ');
        SQL.Add('where a.Tahun='+IntToStr(Tahun.AsInteger)+' and a.Bulan='+IntToStr(Bulan.AsInteger));
        SQL.Add('  and d.KodeGdg is not null and c.KodeBrg is not null and b.KodeBrg is null');
        SQL.Add('  and left(a.KodeBrg,2)='+QuotedStr(xLeftKodeBrg));
        //SQL.Add('  and a.kodebrg ='+QuotedStr(xLeftKodeBrg));
        ExecSQL;
      end;
      Application.ProcessMessages;
      with DM.QuKOde do
      begin
        Close;
        SQL.Clear;
        SQL.Add('update dbStockBrg set HrgRata=case when SaldoQnt=0 then 0 else SaldoRp/SaldoQnt end ');
        SQL.Add('where Tahun='+IntToStr(Tahun.AsInteger)+' and Bulan='+IntToStr(Bulan.AsInteger));
        SQL.Add('  and left(KodeBrg,2)='+QuotedStr(xLeftKodeBrg));
        //SQL.Add(' and kodebrg ='+QuotedStr(xLeftKodeBrg));
        ExecSQL;
      end;
      DM.QuCari.Next;
    end;
  end;
  XiProgressBar1.Position:=0;
end;

Procedure TFrRata2.InHPPtoTRS(xNobukti, xUrut, xTipe: string; xHarga: Real);
begin
  with DM.QuNomor do
  begin
    if xTipe='RPB' then
    begin
      close;
      sql.Clear;
      SQL.Add('alter table dbRBelidet disable trigger Trg_UPD_DBRBELIdet ');
      SQL.Add('Update dbRBelidet set HPP=:0 ');
      SQL.Add('where NoBukti='+QuotedStr(xNobukti)+' and Urut='+xUrut);
      SQL.Add('alter table dbRBelidet disable trigger Trg_UPD_DBRBELIdet ');
      Prepared;
      Parameters[0].Value:=xHarga;
      ExecSQL;
    end;
    if xTipe='PNJ' then
    begin
      close;
      sql.Clear;
      SQL.Add('alter table dbSPBDet disable trigger Trg_UPD_DBSPBdet ');
      SQL.Add('Update dbSPBDet set HPP=:0 ');
      SQL.Add('where NoBukti='+QuotedStr(xNobukti)+' and Urut='+xUrut);
      SQL.Add('alter table dbSPBDet enable trigger Trg_UPD_DBSPBdet ');
      Prepared;
      Parameters[0].Value:=xHarga;
      ExecSQL;
    end;
    if xTipe='RPJ' then
    begin
      close;
      sql.Clear;
      SQL.Add('alter table DBSPBRJualDet disable trigger Trg_UPD_DBSPBRJUALdet ');
      SQL.Add('Update DBSPBRJualDet set HPP=:0 ');
      SQL.Add('where NoBukti='+QuotedStr(xNobukti)+' and Urut='+xUrut);
      SQL.Add('alter table DBSPBRJualDet enable trigger Trg_UPD_DBSPBRJUALdet ');
      Prepared;
      Parameters[0].Value:=xHarga;
      ExecSQL;
    end;
    if (xTipe='ADO')  then
    begin
      close;
      sql.Clear;
      SQL.Add('alter table dbKoreksiDet disable trigger TRI_UPD_DBKOREKSIDET ');
      SQL.Add('Update dbKoreksiDet set HPP=:0 ');
      SQL.Add('where NoBukti='+QuotedStr(xNobukti)+' and Urut='+xUrut);
      SQL.Add('alter table dbKoreksiDet enable trigger TRI_UPD_DBKOREKSIDET ');
      Prepared;
      Parameters[0].Value:=xHarga;
      ExecSQL;
    end;
    if (xTipe='ADI') then
    begin
      close;
      sql.Clear;
      SQL.Add('alter table dbKoreksiDet disable trigger TRI_UPD_DBKOREKSIDET ');
      SQL.Add('Update dbKoreksiDet set HPP=:0 ');
      SQL.Add('where NoBukti='+QuotedStr(xNobukti)+' and Urut='+xUrut);
      SQL.Add('alter table dbKoreksiDet enable trigger TRI_UPD_DBKOREKSIDET ');
      Prepared;
      Parameters[0].Value:=xHarga;
      ExecSQL;
    end;
    if (xTipe='TRO') or (xTipe='TRI') then
    begin
      close;
      sql.Clear;
      SQL.Add('alter table dbTransferDet disable trigger TRI_UPD_DBTRANSFERDET ');
      SQL.Add('Update dbTransferDet set HPP=:0 ');
      SQL.Add('where NoBukti='+QuotedStr(xNobukti)+' and Urut='+xUrut);
      SQL.Add('alter table dbTransferDet enable trigger TRI_UPD_DBTRANSFERDET ');
      Prepared;
      Parameters[0].Value:=xHarga;
      ExecSQL;
    end;
    if (xTipe='PBO') or (xTipe='PBI') then
    begin
      close;
      sql.Clear;
      SQL.Add('alter table DBBPPBTDET disable trigger TRI_UPD_DBBPPBTDET ');
      SQL.Add('Update DBBPPBTDET set HPP=:0 ');
      SQL.Add('where NoBukti='+QuotedStr(xNobukti)+' and Urut='+xUrut);
      SQL.Add('alter table DBBPPBTDET enable trigger TRI_UPD_DBBPPBTDET ');
      Prepared;
      Parameters[0].Value:=xHarga;
      ExecSQL;
    end;
    if xTipe='PMK' then
    begin
      close;
      sql.Clear;
      SQL.Add('alter table DBPenyerahanBhndet disable trigger TRG_UPD_DBPenyerahanBhnDET ');
      SQL.Add('Update DBPenyerahanBhndet set HPP=:0 ');
      SQL.Add('where NoBukti='+QuotedStr(xNobukti)+' and Urut='+xUrut);
      SQL.Add('alter table DBPenyerahanBhndet enable trigger TRG_UPD_DBPenyerahanBhnDET ');
      Prepared;
      Parameters[0].Value:=xHarga ;//;  RoundTo(xharga,0)
      ExecSQL;
    end;
    if xTipe='RPK' then
    begin
      close;
      sql.Clear;
      SQL.Add('alter table DBRPenyerahanBhndet disable trigger TRG_UPD_DBRPenyerahanBhnDET ');
      SQL.Add('Update DBRPenyerahanBhndet set HPP=:0 ');
      SQL.Add('where NoBukti='+QuotedStr(xNobukti)+' and Urut='+xUrut);
      SQL.Add('alter table DBRPenyerahanBhndet enable trigger TRG_UPD_DBRPenyerahanBhnDET ');
      Prepared;
      Parameters[0].Value:=xHarga;
      ExecSQL;
    end;
    if xTipe='UKO' then
    begin
      close;
      sql.Clear;
      SQL.Add('alter table DBUBAHKEMASANDET disable trigger TRI_UPD_DBUBAHKEMASANDET ');
      SQL.Add('Update DBUBAHKEMASANDET set HPP=:0 ');
      SQL.Add('where NoBukti='+QuotedStr(xNobukti)+' and Urut='+xUrut);
      SQL.Add('alter table DBUBAHKEMASANDET disable trigger TRI_UPD_DBUBAHKEMASANDET ');
      Prepared;
      Parameters[0].Value:=xHarga;
      ExecSQL;
    end;
  end;
end;



Procedure TFrRata2.ProsesKemasan(xBulan,xTahun:Integer);
begin
    {with MySP do
    begin
      close;
      ProcedureName:='sp_ProsesUbahKemasan;1';
      Parameters.Refresh;
      Parameters[1].Value := xBulan;
      Parameters[2].Value := xTahun;
      ExecProc;
    end;}
    with  DM.QuCari do
        begin
          Close;
          SQL.Clear;
          SQL.Add('select NoBukti from dbUbahKemasan where month(Tanggal)='+IntToStr(Bulan.AsInteger)+' and year(Tanggal)='+IntToStr(Tahun.AsInteger));
          SQL.Add('order by Convert(Int,NoUrut)');
          Open;
        end;


    {with Dm.QuCari do
    begin
      close;
      sql.Clear;
      sql.Add('select Distinct  b.Kodebrg,c.Namabrg ');
      sql.Add('from dbubahKemasan a');
      sql.Add('left outer join dbubahkemasandet b on (a.nobukti=b.nobukti)');
      sql.Add('left outer join dbbarang c on c.kodebrg=b.kodebrg');
      sql.Add('where month(a.tanggal)=:0 and year(a.tanggal)=:1 and b.qntdb<>0 --and c.Jenis in (0,1) ');

      sql.Add('order by b.kodebrg');
      Prepared;
      Parameters[0].Value := xBulan;
      Parameters[1].Value := xTahun;
      open;
    end; }
    if not DM.QuCari.IsEmpty then
    begin
      XiProgressBar1.Max := Dm.QuCari.RecordCount;
      XiProgressBar1.Position := 0;
      while not Dm.QuCari.Eof do
      begin
        Delay(3);
        Label2.Caption := 'Proses Bahan Dalam ubah Kemasan'+#13+
                          'Nobukti : '+ DM.QuCari.Fieldbyname(
                            'nobukti').AsString;// +#13//+
                          //'Nama Barang : '+DM.QuCari.Fieldbyname('Namabrg').AsString;
          with  DM.QuKOde do
          begin
            Close;
            SQL.Clear;
            SQL.Add('exec sp_ProsesHPPUbahKemasan '+QuotedStr(DM.QuCari.FieldByName('NoBukti').AsString));
            ExecSQL;
          end;

        //ProsesBahan(Dm.QuCari.Fieldbyname('Kodebrg').AsString,Bulan.,
        //Tahun.AsInteger,Myharga);
        //ProsesBahan(Bulan.AsInteger,Tahun.AsInteger,Awal.Text,Akhir.Text);
        //InHPPtoTRS(DM.QuCari.Fieldbyname('kodebrg').AsString,Bulan.AsInteger,Tahun.AsInteger,Myharga);
        //InHPPtoSTK(DM.QuCari.Fieldbyname('kodebrg').AsString,Bulan.AsInteger,Tahun.AsInteger);
        //InHPPtoTRS(Bulan.AsInteger,Tahun.AsInteger,Awal.Text,Akhir.Text);
        XiProgressBar1.Position := XiProgressBar1.Position+1;
        DM.QuCari.Next;
      end;
    end;
    ProsesBahan(Bulan.AsInteger,Tahun.AsInteger,Awal.Text,Akhir.Text);
    InHPPtoTRS(Bulan.AsInteger,Tahun.AsInteger,Awal.Text, Akhir.Text);
end;

Procedure TFrRata2.ProsesAkhirBulan(xBulan,xTahun:integer);
begin
  if Bulan.AsInteger = 12 then
  begin
    xBulan := 1;
    xTahun := xTahun+1;
  end
  else
  begin
    xBulan := xBulan+1;
    xTahun := xTahun;
  end;
    //Delay(3);
  Label2.Caption := 'Inisialisasi';
  with Dm.QuCari do
  begin
    close;
    sql.Clear;
    sql.Add('Declare @Bulan int,@tahun int');
    Sql.Add('Select @Bulan=:0,@tahun=:1');
    sql.Add('Update dbStockbrg set qntAwal=0, Qnt2Awal=0, hrgawal=0 ');
    if ComboBox1.ItemIndex=1 then
    begin
      sql.Add('where Bulan=Case when @bulan=12 then 1 else @bulan+1 end and Tahun=Case when @bulan=12 then @tahun+1 else @tahun end and (kodebrg between :2 and :3) ');
      Parameters[0].Value:=Bulan.AsInteger;
      Parameters[1].Value:=Tahun.AsInteger;
      Parameters[2].Value:=Awal.Text;
      Parameters[3].Value:=Akhir.Text;
    end else
    begin
      sql.Add('where Bulan=Case when @bulan=12 then 1 else @bulan+1 end and Tahun=Case when @bulan=12 then @tahun+1 else @tahun end ');
      Parameters[0].Value:=Bulan.AsInteger;
      Parameters[1].Value:=Tahun.AsInteger;
    end;
    Prepared;
    ExecSQL;
    close;
    sql.Clear;
    Sql.add('Declare @bulan int, @tahun int, @kodebrg1 varchar(25), @kodebrg2 varchar(25) ');
    Sql.add('Select @bulan=:0, @tahun=:1, @kodebrg1=:2,@kodebrg2=:3 ');
    sql.Add('Select b.Kodebrg, c.Namabrg, b.kodegdg, b.SaldoQnt QntAwal, b.Saldo2Qnt Qnt2Awal, b.SaldoRp HrgAwal, B.HrgRata, ''BHN'' xCode');
    sql.Add('from dbstockbrg b');
    sql.Add('left outer join dbbarang c on c.kodebrg=b.kodebrg');
    sql.add('Where b.bulan=@bulan and b.Tahun=@tahun ');
    if ComboBox1.ItemIndex=1 then
    begin
      sql.Add('and (b.kodebrg between @kodebrg1 and @kodebrg2 ) ');
    end;
    sql.Add('order by B.KodeBrg');
    Parameters[0].Value := Bulan.AsInteger;
    Parameters[1].Value := Tahun.AsInteger;
    Parameters[2].Value := Awal.Text;
    Parameters[3].Value := Akhir.Text;
    Prepared;
    Open;
  end;
  XiProgressBar1.Position := 0;
  try
    if DM.QuCari.IsEmpty then XiProgressBar1.Max:=1
    else XiProgressBar1.Max := DM.QuCari.RecordCount;
  except
    XiProgressBar1.Max:=1;
  end;
  //XiProgressBar1.Max := DM.QuCari.RecordCount;
  while not Dm.QuCari.Eof do
  begin
    //Delay(3);
    XiProgressBar1.Position := XiProgressBar1.Position+1;
    Label2.Caption := 'Pindah Saldo Stock : '+ DM.QuCari.Fieldbyname('kodebrg').AsString +#13+
                      'Nama Barang : '+DM.QuCari.Fieldbyname('Namabrg').AsString;
    Application.ProcessMessages;
    With dm.QuKOde do
    begin
      close;
      sql.Clear;
      Sql.Add('Declare @Kodebrg varchar(25),@Kodegdg varchar(15),@kodeLokasi Varchar(15),');
      sql.Add('        @QntAwal numeric(18,2), @Qnt2Awal numeric(18,2), @HrgAwal numeric(18,2),@Bulan int,@Tahun int, @hrgRata numeric(18,2)');
      sql.Add('Select @kodebrg=:0,@Kodegdg=:1, @QntAwal=:2,@Qnt2Awal=:3, @hrgAwal=:4,@bulan=:5,@Tahun=:6, @HrgRata=:7');
      if DataBersyarat('Select Kodebrg from dbstockbrg Where Kodebrg=:0 and Kodegdg=:1 and Bulan=:2 and Tahun=:3 ',
         [DM.QuCari.Fieldbyname('kodebrg').AsString,DM.QuCari.Fieldbyname('kodegdg').AsString,
          xBulan,xTahun],Dm.QuNomor) then
      begin

          sql.Add('Update dbstockbrg set QntAwal=@Qntawal,Qnt2Awal=@Qnt2Awal, HrgAwal=@HrgAwal, HrgRata=@hrgRata');
          Sql.Add('Where Kodebrg=@Kodebrg and Kodegdg=@Kodegdg and Bulan=@Bulan and Tahun=@tahun');

      end
      else
      begin

          Sql.Add('Insert into dbStockbrg(Kodebrg,Kodegdg,Bulan,Tahun,QntAwal,Qnt2Awal,HrgAwal, HrgRata)');
          sql.Add('Values(@Kodebrg,@Kodegdg,@Bulan,@Tahun,@QntAwal,@Qnt2Awal,@HrgAwal,@HrgRata)');
        
      end;
      Prepared;
      Parameters[0].Value := DM.QuCari.Fieldbyname('kodebrg').AsString;
      Parameters[1].Value := DM.QuCari.Fieldbyname('kodegdg').AsString;
      Parameters[2].Value := DM.QuCari.Fieldbyname('Qntawal').AsFloat;
      Parameters[3].Value := DM.QuCari.Fieldbyname('Qnt2awal').AsFloat;
      Parameters[4].Value := DM.QuCari.Fieldbyname('Hrgawal').AsFloat;
      Parameters[5].Value := xBulan;
      Parameters[6].Value := xTahun;
      Parameters[7].Value := DM.QuCari.Fieldbyname('HrgRata').AsFloat;
      ExecSql;
    end;
    dm.QuCari.Next;
  end;
  XiProgressBar1.Position := 0;
end;

procedure TFrRata2.FormShow(Sender: TObject);
begin
   Bulan.Value:=Strtoint(PeriodBln);
   Tahun.Value:=Strtoint(PeriodThn);
end;

procedure TFrRata2.FormKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
if key=13 then sendmessage(handle,WM_NEXTDLGCTL,0,0)
else if key=27 then close;
end;

procedure TFrRata2.FormClose(Sender: TObject;
  var Action: TCloseAction);
begin
Action:=cafree;
end;
                                                                              
procedure TFrRata2.BitBtn1Click(Sender: TObject);
begin
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('delete TempStockMinus where IDUser='+QuotedStr(IDUser));
    ExecSQL;
  end;
  QuBeli.Close;
  QuBeli.SQL.Clear;
  QuBeli.SQL.Add('select * from TempStockMinus where IDUser='+QuotedStr(IDUser));
  QuBeli.Open;

  iUrutStockMinus:=0;

  strBulan:=IntToStr(Bulan.AsInteger);
  strTahun:=IntToStr(Tahun.AsInteger);
  if MyCek_Lock_PeriodeProses(Bulan.Text,Tahun.Text) then
  begin
    Delay(3);
    LoggingData(IDUser,'','','',' Hitung Harga Pokok Periode '+Bulan.Text+'-'+Tahun.Text);
    Label2.Caption := 'Inisialisasi';

    with Dm.QuCari do
    begin
      Close;
      sql.Clear;
      //sql.Add('Update dbStockBrg set HRGPBL=0,HRGRPB=0,HRGPNJ=0,HRGRPJ=0,HRGADI=0,HRGADO=0,');    asli
      sql.Add('Update dbStockBrg set HRGPBL=0,HRGRPB=0,HRGPNJ=0,HRGRPJ=0,HRGADI=0,HRGMADI=0,HRGADO=0,HRGMADO=0,');
      Sql.add('                      HRGUKI=0,HRGUKO=0,HRGTRI=0,HRGTRO=0,HRGPMK=0,HRGRPK=0,HRGHPrd=0 ');
      sql.Add('where  Bulan='+IntToStr(Bulan.AsInteger)+' and Tahun='+IntToStr(Tahun.AsInteger));
      if ComboBox1.ItemIndex=1 then
      begin
        SQL.add(' and (KodeBrg between '+QuotedStr(Awal.Text)+' and '+QuotedStr(Akhir.Text)+')');
      end;
      ExecSQL;
    end;

    ProsesBahan(Bulan.AsInteger,Tahun.AsInteger,Awal.Text,Akhir.Text);

    InHPPtoTRS(Bulan.AsInteger,Tahun.AsInteger,Awal.Text,Akhir.Text);
    ProsesKemasan(Bulan.AsInteger,Tahun.AsInteger);

    ProsesAkhirBulan(Bulan.AsInteger,Tahun.AsInteger);
    Label2.Caption := 'Proses hitung harga pokok selesai.';
    XiProgressBar1.Position := 0;
  end
  else
    MessageDlg('Periode Sudah terkunci, tidak dapat hitung ulang',mtWarning,[mbok],0);
end;

procedure TFrRata2.BitBtn2Click(Sender: TObject);
begin
  Close;
end;

procedure TFrRata2.ComboBox1Change(Sender: TObject);
begin
  if ComboBox1.ItemIndex=0 then
  begin
    Awal.Visible:=False;
    Akhir.Visible:=False;
    Label24.Visible:=False;
  end else
  begin
    Awal.Visible:=True;
    Akhir.Visible:=True;
    Label24.Visible:=True;
  end;
end;

procedure TFrRata2.TampilIsiBarang;
begin
  KodeBrows:=3001101;
  Application.CreateForm(TFrBrows, FrBrows);
  FrBrows.IsiData:=Awal.Text;
  FrBrows.IsiData1:=Awal.Text;
  FrBrows.ShowModal;
  if FrBrows.ModalResult=mrOk then
  begin
    with FrBrows.QuBrows do
    begin
      Awal.Text:=FieldByName('KodeBrg').AsString;
    end;
  end
   else
    ActiveControl:=Awal;
end;

procedure TFrRata2.TampilIsiBarang2;
begin
  KodeBrows:=3001101;
  Application.CreateForm(TFrBrows, FrBrows);
  FrBrows.IsiData:=Akhir.Text;
  FrBrows.IsiData1:=Akhir.Text;
  FrBrows.ShowModal;
  if FrBrows.ModalResult=mrOk then
  begin
    with FrBrows.QuBrows do
    begin
      Akhir.Text:=FieldByName('KodeBrg').AsString;
    end;
  end
   else
    ActiveControl:=Akhir;
end;

procedure TFrRata2.AwalKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
  if Key=VK_Return then
  begin
      if Length(Awal.Text)=0 then TampilIsiBarang else
      begin
        if DataBersyarat('Select KodeBrg, NamaBrg, Sat1 from dbBarang where KodeBrg=:0 ', [Awal.Text], DM.QuCari)=True then

        begin
             Awal.Text:=DM.QuCari.FieldByName('KodeBrg').AsString;
        end else
             TampilIsiBarang;
      end;

  end
  Else if Key=VK_Escape then
  begin
//    SpeedButton1.Click;
  end;
end;

procedure TFrRata2.AkhirKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
  if Key=VK_Return then
  begin
      if Length(Akhir.Text)=0 then TampilIsiBarang2 else
      begin
        if DataBersyarat('Select KodeBrg, NamaBrg, Sat1 from dbBarang where KodeBrg=:0 ', [Akhir.Text], DM.QuCari)=True then

        begin
             Akhir.Text:=DM.QuCari.FieldByName('KodeBrg').AsString;
        end else
             TampilIsiBarang2;
      end;

  end
  Else if Key=VK_Escape then
  begin
//    SpeedButton1.Click;
  end;
end;

procedure TFrRata2.SpeedButton1Click(Sender: TObject);
begin
  Save('xls', 'Microsoft Excel (*.xls)|*.xls', 'Stock Minus', dxDBGrid1.SaveToXLS);
  ShellExecute(Handle, 'Open', PChar(SaveDialog.FileName), nil, nil, sw_shownormal);
end;

end.

