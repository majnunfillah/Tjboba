unit FrmProsesTutupBuku;

interface

uses
  Windows, Messages, SysUtils, Classes, Graphics, Controls, Forms, Dialogs,
  StdCtrls, PBNumEdit, Db, ADODB, ComCtrls, ExtCtrls, Gauges,Buttons,
  XiProgressBar, RxGIF, jpeg, dxCntner, dxEditor, dxExEdtr, dxEdLib,dateUtils,
  Variants,StrUtils;

type
  TFrTutupBuku = class(TForm)
    Shape1: TShape;
    Bevel1: TBevel;
    Label1: TLabel;
    BitBtn1: TBitBtn;
    XiProgressBar1: TXiProgressBar;
    Image2: TImage;
    BitBtn2: TBitBtn;
    Bevel2: TBevel;
    Label2: TLabel;
    Bulan: TComboBox;
    Tahun: TdxCurrencyEdit;
    ComboBox1: TComboBox;
    sp_ProsesPosting: TADOStoredProc;
    Sp_GetNilaiJurnal: TADOStoredProc;
    QuPostJual: TADOQuery;
    QuUpdate: TADOQuery;
    Sp_GetNilaiJurnalHUtang: TADOStoredProc;
    QuKbasah: TADOQuery;
    sp_ProsesPostingPemakaianBhn: TADOStoredProc;
    sp_ProsesAktiva: TADOStoredProc;
    Sp_Proses: TADOStoredProc;
    QuDevisi: TADOQuery;
    QuBarang: TADOQuery;
    Sp_HitAktiva: TADOStoredProc;
    ProsesHitungUlang: TADOStoredProc;
    Sp_ProsesLRSubDevisi: TADOStoredProc;
    QJumlah: TADOQuery;
    QJumlahDevisi: TStringField;
    QJumlahNomor: TStringField;
    QJumlahPerkiraan: TStringField;
    QJumlahKeterangan: TStringField;
    QJumlahTipe: TStringField;
    QJumlahTanda: TStringField;
    QJumlahJumlah: TStringField;
    QJumlahPersen: TStringField;
    QJumlahTahun: TIntegerField;
    QJumlahTampil: TStringField;
    QJumlahTotalAA: TBCDField;
    QJumlahTotalBB: TBCDField;
    QJumlahTotalCC: TBCDField;
    QJumlahTotalA: TBCDField;
    QJumlahTotalB: TBCDField;
    QJumlahTotalC: TBCDField;
    Sp_GetLabaini: TADOStoredProc;
    Sp_Transaksi: TADOStoredProc;
    QJumlahBulan: TIntegerField;
    QJumlahGrup: TStringField;
    QJumlahIsLRHPP: TBooleanField;
    procedure FormShow(Sender: TObject);
    procedure FormKeyDown(Sender: TObject; var Key: Word;
      Shift: TShiftState);
    procedure FormClose(Sender: TObject; var Action: TCloseAction);
    procedure BitBtn2Click(Sender: TObject);
    procedure BitBtn1Click(Sender: TObject);
  private
    { Private declarations }
    HslAkhir: Real;
    ProsesLRHpp : Boolean;
    procedure CariJumlah(Devisi:string);
    procedure SetTotal(var HasilAkhir:Real;Devisi:string);
    procedure CariJumlahDevisi;
    procedure SetTotalDevisi(var HslAkhir:real);
    procedure Postingdata(Bulan,tahun:Integer);
    procedure PostingKacngBasah(Bulan,tahun:Integer);
    procedure SimpanData(Choice,Bukti,devisi,perkiraan,lawan,Keterangan : String;tanggal:Tdatetime;Jumlah:Real;Urut:integer);
    procedure ProsesAktiva(Bulan,Tahun:integer);
    procedure ProsesHPPRL(Bulan,Tahun:Integer;HPPRL:Boolean;Devisi:String);
    procedure ProsesHitUlangNeraca(Bulan,Tahun:Integer);
    procedure ProsesHitUlangAktiva(Bulan,Tahun:Integer);
    procedure ProsesHPPRLSubDevisi(Bulan,Tahun,HPPRL:Integer;Devisi,SubDevisi:String);
    procedure JurnalKoreksi(HslAkhir:real;Bulan,Tahun:Integer;Devisi:String);
    procedure JumTotal(var HslAkhir:real;Devisi:String;Bulan,Tahun:integer);
    procedure JurnalKoreksiLDT(Bulan,Tahun:Integer;Devisi:String);
    procedure ProsesHitUlangAktivaFK(Bulan,Tahun:Integer);
    procedure ProsesAktivaFK(Bulan,Tahun:integer);
  public
    { Public declarations }
  end;

var
  FrTutupBuku: TFrTutupBuku;
  jumdvs : byte;
implementation
uses FrmBrows,MyProcedure,MyGlobal,MyModul;
{$R *.DFM}

procedure TFrTutupBuku.JumTotal(var HslAkhir:real;Devisi:String;Bulan,Tahun:integer);
begin
   with Sp_GetLabaini do
   begin
      Close;
      Prepared;
      Parameters[1].Value:=Tahun;
      Parameters[2].Value:=Devisi;
      Parameters[3].Value:=0;
      Parameters[4].Value:=Bulan;
      Open;
   end;
   if Sp_GetLabaini.RecordCount > 0 then
      HslAkhir:=Sp_GetLabaini.Fields[0].AsFloat
   else
      HslAkhir:=0;
end;

procedure TFrTutupBuku.JurnalKoreksi(HslAkhir:real;Bulan,Tahun:Integer;Devisi:String);
Function CariDiPosting(Flds:string):string;
begin
   with Dm.Qucari do
   begin
      close;
      sql.clear;
      sql.add('select Perkiraan from dbPostHutPiut where Kode='+QuotedStr(Flds));
      prepared;
      open;
      Result := fields[0].asstring;
   end;
end;
var
   prd,perkRl,perkRlThn,nomorBukti : string;
   ttgl:TDateTime;
begin
   if bulan<12 then
   begin
     if bulan+1<10 then
        prd := '/0'+IntToStr(bulan+1)+RightStr(IntToStr(Tahun),2)
     else
        prd := '/'+IntToStr(bulan+1)+RightStr(IntToStr(Tahun),2);
     ttgl := encodedate(Tahun, bulan+1, 1);
   end else
   begin
     prd := '/01'+RightStr(IntToStr(Tahun+1),2);
     ttgl := encodedate(Tahun+1, 1, 1);
   end;
   nomorBukti := 'RL00001'+prd+'/BIG';
   with sp_transaksi do
   begin
      Close;
      Parameters[1].Value:='D';
      Parameters[2].Value:=nomorBukti;
      parameters[22].value:=1;
      ExecProc;
   end;
   PerkRl := CariDiPosting('RLB');
   PerkRlThn := CariDiPosting('RLI');
  with Sp_Transaksi do
  begin
    Parameters[1].Value := 'I';
    Parameters[2].Value := nomorBukti;
    Parameters[3].Value := '00001';
    Parameters[4].Value := ttgl;
    Parameters[5].Value := 'Koreksi Rugi Laba Bulan ini Ke Tahun Berjalan';
    Parameters[6].Value := 0;
    Parameters[7].Value := Devisi;
    Parameters[8].Value := perkRl;
    Parameters[9].Value := perkRlThn;
    Parameters[10].Value:= 'Koreksi Rugi Laba Bulan ini Ke Tahun Berjalan';
    Parameters[11].Value:= '';
    Parameters[12].Value:= HslAkhir;
    Parameters[13].Value:= 0;
    Parameters[14].Value:= 'IDR';
    Parameters[15].Value:= 1;
    Parameters[16].Value:= HslAkhir;
    Parameters[17].Value:= 0;
    Parameters[18].Value:= 'BJK';
    Parameters[19].Value:= 'C';
    Parameters[20].Value:= '';
    Parameters[21].Value:= '';
    Parameters[22].Value:= 1;
    Parameters[23].Value:= '';
    Parameters[24].Value:= '';
    Parameters[25].Value:= '';
    Parameters[26].Value:= '';
    Parameters[27].Value:= '';
    Parameters[28].Value:= '';
    Parameters[29].Value:= '';
    Parameters[30].Value:= '';
    Parameters[31].Value:= '';
     try
       ExecProc;
         LoggingData(IDUser,'I','BJK',nomorBukti,
                    ' No. Bukti = '+QuotedStr(nomorBukti)+
                    ' Tanggal = '+QuotedStr(datetostr(ttgl))+
                    ' Note = '+QuotedStr('Koreksi Rugi Laba Bulan ini Ke Tahun Berjalan')+
                    ' Debet = '+QuotedStr(perkRl)+
                    ' Kredit = '+QuotedStr(perkRlThn)+
                    ' Sumber = '+QuotedStr('C')+
                    ' Jumlah = '+QuotedStr(FormatFloat(',0.00',HslAkhir))+
                    ' JumlahRp = '+QuotedStr(FormatFloat(',0.00',HslAkhir)));
       except
       on E: Exception do
           begin
             Application.MessageBox(StrPCopy(S,E.Message),'Peringatan',MB_OK or MB_ICONSTOP);
           end;
     end;
  end;
end;

procedure TFrTutupBuku.JurnalKoreksiLDT(Bulan,Tahun:Integer;Devisi:String);
Function CariDiPosting(Flds:string):string;
begin
   with Dm.Qucari do
   begin
      close;
      sql.clear;
      sql.add('select Perkiraan from dbPostHutPiut where Kode='+QuotedStr(Flds));
      prepared;
      open;
      Result := fields[0].asstring;
   end;
end;

var
   prd,PerkRl,perkRlThn,PerkLDT,nomorBukti : string;
   ttgl:TDateTime;
   JmlRLB:real;
begin
   prd := '/01'+RightStr(IntToStr(Tahun+1),2);
   ttgl := encodedate(Tahun+1, 1, 1);
   nomorBukti := 'RL00002'+prd+'/PWT';
   with Dm.QuKOde do
   begin
     close;
     sql.Clear;
     sql.add(' select sum(AkhirKRp) JmlRLB from DBNERACA a ');
     sql.add(' left outer join DBPOSTHUTPIUT b on b.Perkiraan=a.Perkiraan ');
     sql.add(' where a.Bulan=:0 and a.Tahun=:1 ');
     sql.add(' and b.Kode in (:2,:3) ');
     prepared;
     Parameters[0].Value := Bulan;
     Parameters[1].Value := Tahun;
     Parameters[2].Value := 'RLB';
     Parameters[3].Value := 'RLI' ;
     open;
     JmlRLB := Fieldbyname('JmlRLB').AsCurrency;
   end;
   with sp_transaksi do
   begin
      Close;
      Parameters[1].Value:='D';
      Parameters[2].Value:=nomorBukti;
      parameters[22].value:=1;
      ExecProc;
   end;
   PerkRlThn := CariDiPosting('RLI');
   PerkLDT := CariDiPosting('RLL');
  with Sp_Transaksi do
  begin
    Parameters[1].Value := 'I';
    Parameters[2].Value := nomorBukti;
    Parameters[3].Value := '00002';
    Parameters[4].Value := ttgl;
    Parameters[5].Value := 'Koreksi Tahun Berjalan Ke Laba Ditahan';
    Parameters[6].Value := 0;
    Parameters[7].Value := Devisi;
    Parameters[8].Value := PerkRlThn;
    Parameters[9].Value := PerkLDT;
    Parameters[10].Value:= 'Koreksi Tahun Berjalan Ke Laba Ditahan';
    Parameters[11].Value:= '';
    Parameters[12].Value:= JmlRLB;
    Parameters[13].Value:= 0;
    Parameters[14].Value:= 'IDR';
    Parameters[15].Value:= 1;
    Parameters[16].Value:= JmlRLB;
    Parameters[17].Value:= 0;
    Parameters[18].Value:= 'BJK';
    Parameters[19].Value:= 'C';
    Parameters[20].Value:= '';
    Parameters[21].Value:= '';
    Parameters[22].Value:= 1;
    Parameters[23].Value:= '';
    Parameters[24].Value:= '';
    Parameters[25].Value:= '';
    Parameters[26].Value:= '';
    Parameters[27].Value:= '';
    Parameters[28].Value:= '';
    Parameters[29].Value:= '';
    Parameters[30].Value:= '';
    Parameters[31].Value:= '';
     try
       ExecProc;
         LoggingData(IDUser,'I','BJK',nomorBukti,
                    ' No. Bukti = '+QuotedStr(nomorBukti)+
                    ' Tanggal = '+QuotedStr(datetostr(ttgl))+
                    ' Note = '+QuotedStr('Koreksi Tahun Berjalan Ke Laba Ditahan')+
                    ' Debet = '+QuotedStr(perkRlThn)+
                    ' Kredit = '+QuotedStr(PerkLDT)+
                    ' Sumber = '+QuotedStr('C')+
                    ' Jumlah = '+QuotedStr(FormatFloat(',0.00',JmlRLB))+
                    ' JumlahRp = '+QuotedStr(FormatFloat(',0.00',JmlRLB)));
       except
       on E: Exception do
           begin
             Application.MessageBox(StrPCopy(S,E.Message),'Peringatan',MB_OK or MB_ICONSTOP);
           end;
     end;
  end;
end;

procedure TFrTutupBuku.CariJumlahDevisi;
begin
   {with QJumlah do
   begin
      close;
      sql.clear;
      sql.add('select *,');
      sql.add('TotalAA=(Case when Tanda=''-'' then -1*TotalA else TotalA end),');
      sql.add('TotalBB=(Case when Tanda=''-'' then -1*TotalB else TotalB end),');
      sql.add('TotalCC=(Case when Tanda=''-'' then -1*TotalC else TotalC end)');
      if prosesrlhpp=0 then
         sql.add('from dbLaplrDevisi where tahun=:0 and devisi=:1 and Bulan=:2 and Counter=:3 order by nomor')
      else
         sql.add('from dbLaphpp where tahun=:0 and devisi=:1 and Bulan=:2 order by nomor');
      prepared;
      Parameters[0].Value:=Tahun.Value;
      parameters[1].value:='01';
      Parameters[2].Value:=Bulan.ItemIndex;
      Parameters[3].Value:=QuDevisi.FieldByname('Counter').AsString;
      Open;
   end;}
end;

procedure TFrTutupBuku.CariJumlah(Devisi:string);
begin
   with QJumlah do
   begin
      close;
      sql.clear;
      sql.add('select *,');
      sql.add('TotalAA=(Case when Tanda=''-'' then -1*TotalA else TotalA end),');
      sql.add('TotalBB=(Case when Tanda=''-'' then -1*TotalB else TotalB end),');
      sql.add('TotalCC=(Case when Tanda=''-'' then -1*TotalC else TotalC end)');
      sql.add('from dbLRHPP where tahun=:0 and devisi=:1 and Bulan=:2 and IsLRHPP=:3 and Devisi=:4 order by nomor');
      prepared;
      Parameters[0].Value:=Tahun.Value;
      parameters[1].value:=Devisi;
      Parameters[2].Value:=Bulan.ItemIndex;
      Parameters[3].Value:=ProsesLRHpp;
      parameters[4].value:=Devisi;
      Open;
   end;
end;

{procedure TFrTutupBuku.JumTotal(var HslAkhir:real);
begin
   with Sp_GetLabaini do
   begin
      Close;
      Parameters[1].Value:=FrReportPreview.Tahun1.Value;
      Parameters[2].Value:=FrReportPreview.Divisi4.Text;
      Parameters[3].Value:=0;
      Open;
   end;
   if Sp_GetLabaini.RecordCount > 0 then
      HslAkhir:=Sp_GetLabaini.Fields[0].AsFloat
   else HslAkhir:=0;
end;
}

procedure TFrTutupBuku.SetTotalDevisi;
Var
   jmlS : array[1..3] of currency;
   jmlG : array[1..3] of currency;
   jmlT : array[1..3] of currency;
   i : byte;
   perkiraan: string;
begin
   CariJumlahDevisi;
   for i:=1 to 3 do
   begin
      jmlS[i] := 0; jmlG[i] := 0; jmlT[i] := 0;
   end;
   with QJumlah do
   begin
      First;
      while not Eof do
      begin
         perkiraan:=QJumlah.FieldByName('nomor').AsString;
         if QJumlahJumlah.Value='' then
         begin
            jmlS[1]:=jmlS[1]+QJumlahTotalAA.AsCurrency;
            jmlS[2]:=jmlS[2]+QJumlahTotalBB.AsCurrency;
            jmlS[3]:=jmlS[3]+QJumlahTotalCC.AsCurrency;
            jmlG[1]:=jmlG[1]+QJumlahTotalAA.AsCurrency;
            jmlG[2]:=jmlG[2]+QJumlahTotalBB.AsCurrency;
            jmlG[3]:=jmlG[3]+QJumlahTotalCC.AsCurrency;
            jmlT[1]:=jmlT[1]+QJumlahTotalAA.AsCurrency;
            jmlT[2]:=jmlT[2]+QJumlahTotalBB.AsCurrency;
            jmlT[3]:=jmlT[3]+QJumlahTotalCC.AsCurrency;
         end
         else
         if QJumlahJumlah.Value<>'' then
         begin
            if qjumlahjumlah.AsString='S' then
            begin
               Edit;
               QJumlahTotalA.AsCurrency:=jmlS[1];
               QJumlahTotalB.AsCurrency:=jmlS[2];
               QJumlahTotalC.AsCurrency:=jmlS[3];
               Post;
               jmlS[1]:=0; jmlS[2]:=0; jmlS[3]:=0;
            end
            else
            if QJumlahjumlah.asstring='G' then
            begin
               Edit;
               QJumlahTotalA.AsCurrency:=jmlG[1];
               QJumlahTotalB.AsCurrency:=jmlG[2];
               QJumlahTotalC.AsCurrency:=jmlG[3];
               Post;
               jmlS[1]:=0; jmlS[2]:=0; jmlS[3]:=0;
               jmlG[1]:=0; jmlG[2]:=0; jmlG[3]:=0;
            end
            else
            if QJumlahjumlah.asstring='T' then
            begin
               Edit;
               QJumlahTotalA.AsCurrency:=jmlT[1];
               QJumlahTotalB.AsCurrency:=jmlT[2];
               QJumlahTotalC.AsCurrency:=jmlT[3];
               Post;
               jmlS[1]:=0; jmlS[2]:=0; jmlS[3]:=0;
               jmlG[1]:=0; jmlG[2]:=0; jmlG[3]:=0;
            end;
         end;
         Next;
      end;
   end;
end;

procedure TFrTutupBuku.SetTotal(var HasilAkhir:Real;Devisi:String);
Var
   jmlS : array[1..3] of currency;
   jmlG : array[1..3] of currency;
   jmlT : array[1..3] of currency;
   i : byte;
   perkiraan: string;
begin
   CariJumlah(Devisi);
   for i:=1 to 3 do
   begin
      jmlS[i] := 0; jmlG[i] := 0; jmlT[i] := 0;
   end;
   with QJumlah do
   begin
      First;
      while not Eof do
      begin
         perkiraan:=QJumlah.FieldByName('nomor').AsString;
         if QJumlahJumlah.Value='' then
         begin
            jmlS[1]:=jmlS[1]+QJumlahTotalAA.AsCurrency;
            jmlS[2]:=jmlS[2]+QJumlahTotalBB.AsCurrency;
            jmlS[3]:=jmlS[3]+QJumlahTotalCC.AsCurrency;
            jmlG[1]:=jmlG[1]+QJumlahTotalAA.AsCurrency;
            jmlG[2]:=jmlG[2]+QJumlahTotalBB.AsCurrency;
            jmlG[3]:=jmlG[3]+QJumlahTotalCC.AsCurrency;
            jmlT[1]:=jmlT[1]+QJumlahTotalAA.AsCurrency;
            jmlT[2]:=jmlT[2]+QJumlahTotalBB.AsCurrency;
            jmlT[3]:=jmlT[3]+QJumlahTotalCC.AsCurrency;
         end
         else
         if QJumlahJumlah.Value<>'' then
         begin
            if qjumlahjumlah.AsString='S' then
            begin
               Edit;
               QJumlahTotalA.AsCurrency:=jmlS[1];
               QJumlahTotalB.AsCurrency:=jmlS[2];
               QJumlahTotalC.AsCurrency:=jmlS[3];
               Post;
               jmlS[1]:=0; jmlS[2]:=0; jmlS[3]:=0;
            end
            else
            if QJumlahjumlah.asstring='G' then
            begin
               Edit;
               QJumlahTotalA.AsCurrency:=jmlG[1];
               QJumlahTotalB.AsCurrency:=jmlG[2];
               QJumlahTotalC.AsCurrency:=jmlG[3];
               Post;
               jmlS[1]:=0; jmlS[2]:=0; jmlS[3]:=0;
               jmlG[1]:=0; jmlG[2]:=0; jmlG[3]:=0;
            end
            else
            if QJumlahjumlah.asstring='T' then
            begin
               Edit;
               QJumlahTotalA.AsCurrency:=jmlT[1];
               QJumlahTotalB.AsCurrency:=jmlT[2];
               QJumlahTotalC.AsCurrency:=jmlT[3];
               Post;
               jmlS[1]:=0; jmlS[2]:=0; jmlS[3]:=0;
               jmlG[1]:=0; jmlG[2]:=0; jmlG[3]:=0;
            end;
         end;
         Next;
      end;
   end;
end;

procedure TFrTutupBuku.ProsesHPPRLSubDevisi(Bulan,Tahun,HPPRL:Integer;Devisi,SubDevisi:String);
var
   ttgl : tdatetime;
begin
  {if bulan<12 then
      ttgl := encodedate(tahun,bulan+1,1)-1
   else
      ttgl := encodedate(tahun,bulan,31);

   with QuPostJual do
   begin
      Close;
      sql.Clear;
      sql.add('Exec Sp_ProsesLabaRugiDevisi :0,:1,:2,:3,:4,:5,:6');
      Prepared;
      Parameters[0].Value:= Bulan;
      Parameters[1].Value:= Tahun;
      Parameters[2].Value:= Devisi;
      parameters[3].value:= IDUser;
      parameters[4].value:= prosesRlHpp;
      Parameters[5].Value:= ttgl;
      parameters[6].value:= Subdevisi;
      try
         ExecSQL;
      except
         ShowMessage('Proses Gagal.');
      end;
   end;
   //SetTotal(HslAkhir);
   //JumTotal(HslAkhir);
             }
end;

procedure TFrTutupBuku.PostingKacngBasah(bulan,Tahun:integer);
var
   ttglawal,ttglAKhir : tdatetime;
   dd,mm,yy : word;
begin
  if IsLockPeriode(bulan,tahun)=false then
  begin
     ttglawal := EncodeDate(Tahun,Bulan,1);
     dd := DaysInAMonth(tahun,Bulan);
     ttglAKhir:= EncodeDate(Tahun,Bulan,DD);
     with sp_ProsesPosting do
     begin
        Close;
        Prepared;
        Parameters[1].Value:=ttglawal;
        Parameters[2].Value:=ttglAKhir;
        try
           ExecProc;
        except
           ShowMessage('Proses Gagal.');
        end;
     end;

  end else
  begin
    Showmessage('Periode sudah di lock');
  end;
end;

procedure TFrTutupBuku.ProsesHitUlangAktiva(Bulan,Tahun:Integer);
begin
  with QuBarang do
  begin
     Close;
     SQL.Clear;
     SQL.Add('select noaktivaP as perkiraan ');
     SQL.Add('from dbtransaksi');
     SQL.Add('where month(tanggal)=:0 and year(tanggal)=:1 and noaktivaP<>'''' ');
     SQL.Add('group by noaktivaP');
     SQL.Add('union all');
     SQL.Add('select noaktivaL as Perkiraan');
     SQL.Add('from dbtransaksi');
     SQL.Add('where month(tanggal)=:2 and year(tanggal)=:3 and noaktivaL<>'''' ');
     SQL.Add('group by noaktival');
     SQL.Add('order by perkiraan');
     Parameters[0].Value:=Bulan;
     Parameters[1].Value:=Tahun;
     Parameters[2].Value:=Bulan;
     Parameters[3].Value:=Tahun;
     Open;
  end;
  if not QuBarang.IsEmpty then
  begin
    XiProgressBar1.Max:=QuBarang.RecordCount;
    QuBarang.First;
    XiProgressBar1.Position:=0;
    while Not QuBarang.Eof do
    begin
       Label2.Caption:='Proses Hitung Ulang Aktiva '+QuBarang.fieldbyName('perkiraan').AsString;
       Delay(1);
       XiProgressBar1.Position:=XiProgressBar1.Position+1;
       with Sp_HitAktiva do
       begin
            close;
            Parameters[1].Value:=Bulan;
            Parameters[2].Value:=Tahun;
            Parameters[3].Value:=QuBarang.fieldbyName('perkiraan').AsString;
            ExecProc;
       end;
       QuBarang.next;
    end;
    XiProgressBar1.Position:=0;
    Label2.Caption:='Proses Hitung Ulang Aktiva Selesai';
  end;

end;

procedure TFrTutupBuku.ProsesHitUlangAktivaFK(Bulan,Tahun:Integer);
begin
  with QuBarang do
  begin
     Close;
     SQL.Clear;

     //Sql.Add(' declare @bulan int , @tahun int ');
     //Sql.Add(' select @bulan = , @tahun = ');
     SQL.Add('select Z.perkiraan from ( ');
     SQL.Add('select NoAktivaP as perkiraan from DBPERURI..dbTransaksi ');
     SQL.Add('where MONTH(Tanggal)=:0 and YEAR(Tanggal)=:1 and NoAktivaP<>'''' and YEAR(Tanggal)=2018 ');
     SQL.Add('group by NoAktivaP ');
     SQL.Add('union all ');
     SQL.Add('select NoAktivaL as perkiraan from DBPERURI..dbTransaksi ');
     SQL.Add('where MONTH(Tanggal)=:2 and YEAR(Tanggal)=:3 and NoAktivaL<>'''' and YEAR(Tanggal)=2018 ');
     SQL.Add('group by NoAktivaL ');
     SQL.Add('union all ');
     SQL.Add('select NoAktivaP as perkiraan from dbTransaksi ');
     SQL.Add('where MONTH(Tanggal)=:4 and YEAR(Tanggal)=:5 and NoAktivaP<>'''' and YEAR(Tanggal)>2018 ');
     SQL.Add('group by NoAktivaP ');
     SQL.Add('union all ');
     SQL.Add('select NoAktivaL as perkiraan from dbTransaksi ');
     SQL.Add('where MONTH(Tanggal)=:6 and YEAR(Tanggal)=:7 and NoAktivaL<>'''' and YEAR(Tanggal)>2018 ');
     SQL.Add('group by NoAktivaL ');
     SQL.Add(') Z order by Z.perkiraan ');

     SQL.Add('select noaktivaP as perkiraan ');
     SQL.Add('from dbtransaksi');
     SQL.Add('where month(tanggal)=:8 and year(tanggal)=:9 and noaktivaP<>'''' ');
     SQL.Add('group by noaktivaP');
     SQL.Add('union all');
     SQL.Add('select noaktivaL as Perkiraan');
     SQL.Add('from dbtransaksi');
     SQL.Add('where month(tanggal)=:10 and year(tanggal)=:11 and noaktivaL<>'''' ');
     SQL.Add('group by noaktival');
     SQL.Add('order by perkiraan');
     {Parameters[0].Value:=Bulan;
     Parameters[1].Value:=Tahun;
     Parameters[2].Value:=Bulan;
     Parameters[3].Value:=Tahun;}
     Parameters[0].Value:=Bulan;
     Parameters[1].Value:=Tahun;
     Parameters[2].Value:=Bulan;
     Parameters[3].Value:=Tahun;
     Parameters[4].Value:=Bulan;
     Parameters[5].Value:=Tahun;
     Parameters[6].Value:=Bulan;
     Parameters[7].Value:=Tahun;
     Parameters[8].Value:=Bulan;
     Parameters[9].Value:=Tahun;
     Parameters[10].Value:=Bulan;
     Parameters[11].Value:=Tahun;

     Open;
  end;
  if not QuBarang.IsEmpty then
  begin
    XiProgressBar1.Max:=QuBarang.RecordCount;
    QuBarang.First;
    XiProgressBar1.Position:=0;
    while Not QuBarang.Eof do
    begin
       Label2.Caption:='Proses Hitung Ulang Aktiva Fiskal '+QuBarang.fieldbyName('perkiraan').AsString;
       Delay(1);
       XiProgressBar1.Position:=XiProgressBar1.Position+1;
       Sp_HitAktiva.Close;
       Sp_HitAktiva.ProcedureName :='sp_HitungUlangAktivaFK;1';
       with Sp_HitAktiva do
       begin
            close;
            Prepared;
            Parameters.Refresh;
            Parameters[1].Value:=Bulan;
            Parameters[2].Value:=Tahun;
            Parameters[3].Value:=QuBarang.fieldbyName('perkiraan').AsString;
            ExecProc;
       end;
       QuBarang.next;
    end;
    XiProgressBar1.Position:=0;
    Label2.Caption:='Proses Hitung Ulang Aktiva Fiskal Selesai';
  end;

end;

procedure TFrTutupBuku.ProsesHitUlangNeraca(Bulan,Tahun:integer);
begin
  with DM.QuCari  do
  begin
    close;
    sql.Clear;
    sql.Add('update DBNERACA set MD=0,MK=0,MDRp=0, MKRp=0, JPD=0, JPK=0, JPDRp=0, JPKRp=0, RLD=0, RLK=0, RLDRp=0, RLKRp=0');
    sql.add('where  Bulan=:0 and Tahun=:1')                                                                                ;
    Prepared;
    Parameters[0].Value := Bulan;
    Parameters[1].Value := Tahun;
    ExecSQL;
    close;
    sql.Clear;
    sql.Add('Select a.nobukti,A.Devisi,a.Perkiraan,a.Lawan,A.Valas,a.Kurs,a.Debet,a.DebetRp,b.DK DKP,c.DK DKL,');
    sql.Add('       A.StatusAktivaP,a.StatusAktivaL,a.NoAktivaP,a.NoAktivaL,a.TipeTrans ');
    //sql.add('from dbTransaksi a');
    sql.add('from vwTransaksi a');
    sql.add('left outer join DBPERKIRAAN b on b.Perkiraan=a.Perkiraan');
    sql.add('left outer join DBPERKIRAAN c on c.Perkiraan=a.Lawan');
    Sql.add('where MONTH(tanggal)=:0 and year(tanggal)=:1');
    sql.Add('Order by A.nobukti');
    Prepared;
    Parameters[0].Value := Bulan;
    Parameters[1].Value := Tahun;
    Open;
  end;
  if not DM.QuCari.IsEmpty then
     XiProgressBar1.Max:=dm.QuCari.RecordCount;
  dm.QuCari.First;
  XiProgressBar1.Position:=0;
  ProsesHitungUlang.Close;
  ProsesHitungUlang.ProcedureName :='sp_HitungUlangTransaksi;1';
  while (not dm.QuCari.eof) and  (not dm.QuCari.IsEmpty) do
  begin
    Label2.Caption:='Proses : '+DM.QuCari.FieldByname('nobukti').AsString;
    Application.ProcessMessages;
    with ProsesHitungUlang do
    begin
       close;
       Prepared;
       Parameters.Refresh;
       Parameters[1].Value:=DM.QuCari.FieldByname('Devisi').AsString;
       Parameters[2].Value:=DM.QuCari.FieldByname('Perkiraan').AsString;
       Parameters[3].Value:=DM.QuCari.FieldByname('DKP').AsInteger;
       Parameters[4].Value:=DM.QuCari.FieldByname('Lawan').AsString;
       Parameters[5].Value:=DM.QuCari.FieldByname('DKL').AsInteger;
       Parameters[6].Value:=DM.QuCari.FieldByname('Debet').AsFloat;
       Parameters[7].Value:=DM.QuCari.FieldByname('DebetRp').AsFloat;
       Parameters[8].Value:=DM.QuCari.FieldByname('statusAktivaP').AsString;
       Parameters[9].Value:=DM.QuCari.FieldByname('StatusAktivaL').AsString;
       Parameters[10].Value:=DM.QuCari.FieldByname('NoAktivaP').AsString;
       Parameters[11].Value:=DM.QuCari.FieldByname('NoAktivaL').AsString;
       Parameters[12].Value:=DM.QuCari.FieldByname('tipeTrans').AsString;
       Parameters[13].Value:=Bulan;
       Parameters[14].Value:=Tahun;
       Parameters[15].Value:=DM.QuCari.FieldByname('Valas').AsString;
       ExecProc;
    end;
    XiProgressBar1.Position := XiProgressBar1.Position+1;
    dm.QuCari.next;
  end;

  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('update DBNERACA ');
    SQL.Add('set	DK=isnull(B.DK,0) ');
    SQL.Add('from DBNERACA A ');
    SQL.Add('left outer join DBPERKIRAAN B on B.Perkiraan=A.Perkiraan ');
    SQL.Add('where A.Tahun='+IntToStr(Tahun)+' and A.Bulan='+IntToStr(Bulan));
    ExecSQL;
  end;

  with DM.QuCari  do
  begin
    close;
    sql.Clear;
    sql.Add('Select Distinct A.Perkiraan,B.Keterangan,A.Devisi ');
    sql.add('from dbNeraca a');
    sql.add('left outer join DBPERKIRAAN b on b.Perkiraan=a.Perkiraan');
    Sql.add('where Bulan=:0 and Tahun=:1');
    sql.Add('Order by A.Perkiraan');
    Prepared;
    Parameters[0].Value := Bulan;
    Parameters[1].Value := Tahun;
    Open;
  end;
  XiProgressBar1.Max:=dm.QuCari.RecordCount;
  dm.QuCari.First;
  XiProgressBar1.Position:=0;
  ProsesHitungUlang.Close;
  ProsesHitungUlang.ProcedureName :='sp_PindahSaldoNeraca;1';
  while (not dm.QuCari.eof) do
  begin
    Label2.Caption:='Proses : '+DM.QuCari.FieldByname('Keterangan').AsString+' ('+DM.QuCari.FieldByname('Keterangan').AsString+')';
    Application.ProcessMessages;
    with ProsesHitungUlang do
    begin
       close;
       Prepared;
       Parameters.Refresh;
       Parameters[1].Value:=DM.QuCari.FieldByname('Devisi').AsString;
       Parameters[2].Value:=DM.QuCari.FieldByname('Perkiraan').AsString;
       Parameters[3].Value:=Bulan;
       Parameters[4].Value:=Tahun;
       ExecProc;
    end;
    XiProgressBar1.Position := XiProgressBar1.Position+1;
    dm.QuCari.next;
  end;
  Label2.Caption:='Proses Hitung Ulang Neraca Selesai';

end;

procedure CariJumDvs;
begin
   with dm.QuCari do
   begin
      close;
      sql.clear;
      sql.add('select count(devisi) from dbdevisi');
      open;
      JumDvs := fields[0].asinteger;
   end;
end;


Function CariDiPosting(Flds,Devisi:string):string;
begin
   with dm.QuCari do
   begin
      close;
      sql.clear;
      sql.add('select '+flds+' from dbPostHutPiut where kode=''RLB''');
      open;
      Result := fields[0].asstring;
   end;
end;

procedure TFrTutupBuku.ProsesHPPRL(Bulan,Tahun:Integer;HPPRL:Boolean;Devisi:String);
var
   ttgl : tdatetime;
   perkRl : string;
   prdJK : string;
begin
  PerkRl := CariDiPosting('Perkiraan',Devisi);
  if bulan<12 then
      ttgl := encodedate(tahun,bulan+1,1)-1
   else
      ttgl := encodedate(tahun,bulan,31);
   if ((bulan+1)<10) then
      prdJK := '/0'+(IntToStr(bulan+1))+'/'+IntToStr(tahun)
   else
   if (bulan+1)<=12 then
      prdJK := '/'+IntToStr(bulan+1)+'/'+IntToStr(tahun)
   else
      prdJK := '/01/'+IntToStr(tahun+1);
   with Sp_proses do
   begin
      Close;
      Prepared;
      Parameters[1].Value:=Bulan;
      Parameters[2].Value:=Tahun;
      Parameters[3].Value:=HPPRL;
      parameters[4].value:=ttgl;
      parameters[5].value:=IDUser;
      Parameters[6].Value:=Devisi;
      try
         ExecProc;
      except
         ShowMessage('Proses Gagal.');
      end;
   end;
   ProsesLRHpp:=HPPRL;
   SetTotal(HslAkhir,Devisi);
   JumTotal(HslAkhir,Devisi,Bulan,tahun);
   if (HPPRL) {and (Bulan<12)} then
      JurnalKoreksi(HslAkhir,Bulan,Tahun,Devisi);
   if (HPPRL) and (Bulan=12) then
      JurnalKoreksiLDT(Bulan,Tahun,Devisi);
end;

procedure TFrTutupBuku.ProsesAktiva(Bulan,Tahun:integer);
var
   ttgl : tdatetime;
   Nomor : String;
begin
    if IsLockPeriode(Bulan,Tahun) then
    begin
       if bulan<12 then
          ttgl := encodedate(tahun,bulan+1,1)-1
       else
          ttgl := encodedate(tahun,bulan,31);

       //ttgl := EndOfAMonth(Tahun,Bulan);
       //Check_Nomor(Bulan,Tahun,'AKM',Nomor,nomorbukti,TTGL);
       Check_Nomor(Bulan,Tahun,'AKM',Nomor,nomorbukti,TTGL,'',False);
       with DM.QuCari do
       begin
         close;
         Sql.Clear;
         sql.Add('Alter Table dbo.dbTransaksi Disable Trigger TRI_Del_DBTRANSAKSI');
         ExecSQL;

         close;
         Sql.Clear;
         sql.Add('Delete from dbtransaksi where Nobukti Like ''%AKM%'' and month(tanggal)=:0 and year(tanggal)=:1');
         Prepared;
         Parameters[0].Value:=Bulan;
         Parameters[1].Value:=Tahun;
         ExecSQL;
         close;
         Sql.Clear;
         sql.Add('Delete from dbtrans where Nobukti Like ''%AKM%'' and month(tanggal)=:0 and year(tanggal)=:1');
         Prepared;
         Parameters[0].Value:=Bulan;
         Parameters[1].Value:=Tahun;
         ExecSQL;
         close;
         Sql.Clear;
         sql.Add('Alter Table dbo.dbTransaksi Disable Trigger TRI_ADD_DBTRANSAKSI');
         ExecSQL;
         close;
         sql.Clear;
         Sql.add('Declare @Bulan int, @Tahun int, @Ttgl Datetime');
         Sql.add('Select @Bulan=:0, @Tahun=:1, @Ttgl=:2');
         SQL.Add('select a.perkiraan,a.keterangan,a.persen,a.tipe,a.akumulasi,');
         Sql.add('       a.biaya,a.biaya2,a.persenbiaya1,a.persenbiaya2,a.biaya3,a.biaya4,a.persenbiaya3,a.persenbiaya4,');
         Sql.add('       a.TipeAktiva, a.kodebag,A.Devisi,A.Tanggal');
         sql.add('from dbaktiva a');
         Sql.add('where a.tanggal<=@Ttgl');
         Sql.add('Order by A.Perkiraan');
         Prepared;
         Parameters[0].Value := Bulan;
         Parameters[1].Value := Tahun;
         Parameters[2].Value := ttgl;
         Open;
       end;
       if DM.QuCari.IsEmpty then
         XiProgressBar1.Max:=1
       else XiProgressBar1.Max:=dm.QuCari.RecordCount;
       dm.QuCari.First;
       XiProgressBar1.Position:=0;

       while (not dm.QuCari.eof) do
       begin
         Label2.Caption:='Proses Penyusutan Aktiva : '+ DM.QuCari.FieldByname('Keterangan').AsString+' ('+DM.QuCari.FieldByname('Perkiraan').AsString+')';
         Application.ProcessMessages;
{         with dm.QuCari2 do
         begin
           Close;
           SQL.Clear;
           sql.Add(' exec ProsesAktiva '+inttostr(Bulan) + ' , '+inttostr(Tahun) +', '+
           quotedstr (DM.QuCari.FieldByName('Devisi').AsString) +
           ' , '+ quotedstr(IDUSER) +' , ' +quotedstr(FormatDateTime('MM-dd-yyyy',ttgl))+' , ' +
           quotedstr(DM.QuCari.FieldByName('Perkiraan').AsString) +' , ' +
           quotedstr(DM.QuCari.FieldByName('KodeBag').AsString) + ' , ' +  quotedstr(DM.QuCari.FieldByName('Keterangan').AsString) +' , '
           + floattostr(formatfloat(DM.QuCari.FieldByName('Persen').AsFloat,'0.##')) + ' , ' +  quotedstr(DM.QuCari.FieldByName('Tipe').AsString) + ' , ' +
           quotedstr( (DM.QuCari.FieldByName('Akumulasi').AsString) )+ ' , ' +  quotedstr(DM.QuCari.FieldByName('Biaya').AsString)  + ' , ' +
           floattostr (DM.QuCari.FieldByName('PersenBiaya1').AsFloat) +' , ' +  quotedstr(DM.QuCari.FieldByName('Biaya2').AsString )+' , ' +
            floattostr (DM.QuCari.FieldByName('PersenBiaya2').AsFloat)  +' , ' +  quotedstr(     DM.QuCari.FieldByName('Biaya3').AsString )+' , ' +
            floattostr(DM.QuCari.FieldByName('PersenBiaya3').AsFloat)  +' , ' +    quotedstr(   DM.QuCari.FieldByName('Biaya4').AsString) +' , ' +
            floattostr(DM.QuCari.FieldByName('PersenBiaya4').AsFloat)  +' , ' +  inttostr(DM.QuCari.FieldByName('TipeAktiva').AsInteger)  +' , ' +
            quotedstr(Nomor)  +' , ' +          quotedstr(NomorBukti) +' , ' +
            //quotedstr(datetostr(DM.QuCari.FieldByName('Tanggal').AsDateTime))
            quotedstr(FormatDateTime('MM-dd-yyyy',DM.QuCari.FieldByName('Tanggal').AsDateTime))
           ) ;
//           SQL.SaveToFile('c:\prosesaktiva.txt');
           ExecSQL;
         end;}
         with Sp_prosesAktiva do
         begin
            Close;
            Parameters[1].Value:=Bulan;
            Parameters[2].Value:=Tahun;
            Parameters[3].Value:=DM.QuCari.FieldByName('Devisi').AsString;
            parameters[4].value:=IDUser;
            parameters[5].value:=ttgl;
            Parameters[6].Value:=DM.QuCari.FieldByName('Perkiraan').AsString;
            Parameters[7].Value:=DM.QuCari.FieldByName('KodeBag').AsString;
            Parameters[8].Value:=DM.QuCari.FieldByName('Keterangan').AsString;
            Parameters[9].Value:=DM.QuCari.FieldByName('Persen').AsFloat;
            Parameters[10].Value:=DM.QuCari.FieldByName('Tipe').AsString;
            Parameters[11].Value:=DM.QuCari.FieldByName('Akumulasi').AsString;
            Parameters[12].Value:=DM.QuCari.FieldByName('Biaya').AsString;
            Parameters[13].Value:=DM.QuCari.FieldByName('PersenBiaya1').AsFloat;
            Parameters[14].Value:=DM.QuCari.FieldByName('Biaya2').AsString;
            Parameters[15].Value:=DM.QuCari.FieldByName('PersenBiaya2').AsFloat;
            Parameters[16].Value:=DM.QuCari.FieldByName('Biaya3').AsString;
            Parameters[17].Value:=DM.QuCari.FieldByName('PersenBiaya3').AsFloat;
            Parameters[18].Value:=DM.QuCari.FieldByName('Biaya4').AsString;
            Parameters[19].Value:=DM.QuCari.FieldByName('PersenBiaya4').AsFloat;
            Parameters[20].Value:=DM.QuCari.FieldByName('TipeAktiva').AsInteger;
            Parameters[21].Value:=Nomor;
            Parameters[22].Value:=NomorBukti;
            Parameters[23].Value:=DM.QuCari.FieldByName('Tanggal').AsDateTime;
            try
               ExecProc;
            except
               ShowMessage('Proses Gagal.');
            end;
         end;
         DM.QuCari.Next;
       end;
       with dm.QuCari do
       begin
         close;
         Sql.Clear;
         sql.Add('Alter Table dbo.dbTransaksi Enable Trigger TRI_ADD_DBTRANSAKSI');
         ExecSQL;
         close;
         Sql.Clear;
         sql.Add('Alter Table dbo.dbTransaksi Enable Trigger TRI_Del_DBTRANSAKSI');
         ExecSQL;

       end;
    end
    else
       Showmessage('Periode sudah di lock');
end;

procedure TFrTutupBuku.ProsesAktivaFK(Bulan,Tahun:integer);
var
   ttgl : tdatetime;
   Nomor : String;
begin
//    if IsLockPeriode(Bulan,Tahun) then
    //begin
       if bulan<12 then
          ttgl := encodedate(tahun,bulan+1,1)-1
       else
          ttgl := encodedate(tahun,bulan,31);

       //ttgl := EndOfAMonth(Tahun,Bulan);
       //Check_Nomor(Bulan,Tahun,'AKM',Nomor,nomorbukti,TTGL);
       //Check_Nomor(Bulan,Tahun,'AKM',Nomor,nomorbukti,TTGL,'',False);
       with DM.QuCari do
       begin
         {close;
         Sql.Clear;
         sql.Add('Delete from dbtransaksi where Nobukti Like ''%AKM%'' and month(tanggal)=:0 and year(tanggal)=:1');
         Prepared;
         Parameters[0].Value:=Bulan;
         Parameters[1].Value:=Tahun;
         ExecSQL;
         close;
         Sql.Clear;
         sql.Add('Delete from dbtrans where Nobukti Like ''%AKM%'' and month(tanggal)=:0 and year(tanggal)=:1');
         Prepared;
         Parameters[0].Value:=Bulan;
         Parameters[1].Value:=Tahun;
         ExecSQL;
         close;
         Sql.Clear;
         sql.Add('Alter Table dbo.dbTransaksi Disable Trigger TRI_ADD_DBTRANSAKSI');
         ExecSQL;  }
         close;
         sql.Clear;
         Sql.add('Declare @Bulan int, @Tahun int, @Ttgl Datetime');
         Sql.add('Select @Bulan=:0, @Tahun=:1, @Ttgl=:2');
         SQL.Add('select a.perkiraan,a.keterangan,a.persen,a.tipe,a.akumulasi,');
         Sql.add('       a.biaya,a.biaya2,a.persenbiaya1,a.persenbiaya2,a.biaya3,a.biaya4,a.persenbiaya3,a.persenbiaya4,');
         Sql.add('       a.TipeAktiva, a.kodebag,A.Devisi,A.Tanggal');
         sql.add('from dbaktivaFK a');

        { sql.add('where a.tanggal<=convert(datetime, convert(varchar(2),@bulan)+''-15-''+convert(varchar(4),@tahun)) and a.tipe<>''P''');
         SQl.add('union All');
         Sql.add('select a.perkiraan,a.keterangan,a.persen,a.tipe,a.akumulasi,');
         Sql.add('       a.biaya,a.biaya2,a.persenbiaya1,a.persenbiaya2,a.biaya3,a.biaya4,a.persenbiaya3,a.persenbiaya4,');
         Sql.add('       a.TipeAktiva, A.kodebag, A.Devisi');
         sql.add('from dbaktiva a');}
         Sql.add('where a.tanggal<=@Ttgl');//  and a.tipe=''P''');
         //sql.add(' and perkiraan = ''1314.1.00095''   ');
         //sql.add(' and a.Perkiraan in  (select id from dbcustomize) ');
         Sql.add('Order by A.Perkiraan');
         Prepared;
         Parameters[0].Value := Bulan;
         Parameters[1].Value := Tahun;
         Parameters[2].Value := ttgl;
         Open;
       end;
       if DM.QuCari.IsEmpty then
         XiProgressBar1.Max:=1
       else XiProgressBar1.Max:=dm.QuCari.RecordCount;
       dm.QuCari.First;
       XiProgressBar1.Position:=0;

       while (not dm.QuCari.eof) do
       begin
         Label2.Caption:='Proses Penyusutan Aktiva Fiskal : '+ DM.QuCari.FieldByname('Keterangan').AsString+' ('+DM.QuCari.FieldByname('Perkiraan').AsString+')';
         Application.ProcessMessages;
         Sp_prosesAktiva.Close;
         Sp_prosesAktiva.ProcedureName :='ProsesAktivaFK;1';
         with Sp_prosesAktiva do
         begin
            close;
            Prepared;
            Parameters.Refresh;
            Parameters[1].Value:=Bulan;
            Parameters[2].Value:=Tahun;
            Parameters[3].Value:=DM.QuCari.FieldByName('Devisi').AsString;
            parameters[4].value:=IDUser;
            parameters[5].value:=ttgl;
            Parameters[6].Value:=DM.QuCari.FieldByName('Perkiraan').AsString;
            Parameters[7].Value:=DM.QuCari.FieldByName('KodeBag').AsString;
            Parameters[8].Value:=DM.QuCari.FieldByName('Keterangan').AsString;
            Parameters[9].Value:=DM.QuCari.FieldByName('Persen').AsFloat;
            Parameters[10].Value:=DM.QuCari.FieldByName('Tipe').AsString;
            Parameters[11].Value:=DM.QuCari.FieldByName('Akumulasi').AsString;
            Parameters[12].Value:=DM.QuCari.FieldByName('Biaya').AsString;
            Parameters[13].Value:=DM.QuCari.FieldByName('PersenBiaya1').AsFloat;
            Parameters[14].Value:=DM.QuCari.FieldByName('Biaya2').AsString;
            Parameters[15].Value:=DM.QuCari.FieldByName('PersenBiaya2').AsFloat;
            Parameters[16].Value:=DM.QuCari.FieldByName('Biaya3').AsString;
            Parameters[17].Value:=DM.QuCari.FieldByName('PersenBiaya3').AsFloat;
            Parameters[18].Value:=DM.QuCari.FieldByName('Biaya4').AsString;
            Parameters[19].Value:=DM.QuCari.FieldByName('PersenBiaya4').AsFloat;
            Parameters[20].Value:=DM.QuCari.FieldByName('TipeAktiva').AsInteger;
            Parameters[21].Value:=Nomor;
            Parameters[22].Value:=NomorBukti;
            Parameters[23].Value:=DM.QuCari.FieldByName('Tanggal').AsDateTime;
            try
               ExecProc;
            except
               ShowMessage('Proses Gagal.');
            end;
         end;
         DM.QuCari.Next;
       end;
       with dm.QuCari do
       begin
         close;
         Sql.Clear;
         sql.Add('Alter Table dbo.dbTransaksi Enable Trigger TRI_ADD_DBTRANSAKSI');
         ExecSQL;
       end;
    //end
    //else
     //  Showmessage('Periode sudah di lock');
end;

procedure TFrTutupBuku.SimpanData(Choice,Bukti,devisi,perkiraan,lawan,Keterangan : String;tanggal:Tdatetime;Jumlah:Real;Urut:integer);
begin
   with Sp_Transaksi do
   begin
       if Choice='I' then
       begin
          Parameters[1].Value:=Choice;
          Parameters[2].Value:=Bukti;
          Parameters[3].Value:=TANGGAL;
          Parameters[4].Value:=Devisi;
          Parameters[5].Value:='';
          Parameters[6].Value:=Perkiraan;
          Parameters[7].Value:=Lawan;
          Parameters[8].Value:=Keterangan;
          Parameters[9].Value:=Jumlah;
          Parameters[10].Value:=0;
          Parameters[11].Value:=1;
          Parameters[12].Value:='IDR';
          Parameters[13].Value:='MM';
          Parameters[14].Value:='C';
          Parameters[15].Value:='';
          Parameters[16].Value:='';
          Parameters[17].Value:='';
          Parameters[18].Value:=0;
          Parameters[19].Value:=tanggal;
          Parameters[20].Value:=tanggal;
          Parameters[21].Value:=Tanggal;
          Parameters[22].Value:='SA';
          Parameters[23].Value:=Urut;
          Parameters[24].Value:='';
          Parameters[25].Value:='';
          Parameters[26].Value:='';
          Parameters[27].Value:='';
          Parameters[28].Value:='';
          Parameters[29].Value:='';
          Parameters[30].Value:='';
          Parameters[31].Value:='';

       end;
       try
            ExecProc;
       except
          ShowMessage('Proses Gagal');
       end;
   end;
end;

procedure TFrTutupBuku.Postingdata(Bulan,tahun:Integer);
var
   ttgl : tdatetime;
   thn1,bln1,tgl1 : word;
begin
  if IsLockPeriode(Bulan,Tahun)=false then
  begin
    with dm.QuCari do
    begin
      close;
      sql.Clear;
      sql.Add('Exec sp_ProsesPostingLedger :0,:1,:2');
      Prepared;
      Parameters[0].Value := Bulan;
      Parameters[1].Value := Tahun;
      Parameters[2].value := '01';
      ExecSQL;
    end;
  end else
    Showmessage('Periode sudah di lock');
end;

procedure TFrTutupBuku.FormShow(Sender: TObject);
begin

   Bulan.ItemIndex:=Strtoint(PeriodBln);
   Tahun.Value:=Strtoint(PeriodThn);
end;

procedure TFrTutupBuku.FormKeyDown(Sender: TObject; var Key: Word;
  Shift: TShiftState);
begin
  if key=13 then sendmessage(handle,WM_NEXTDLGCTL,0,0)
  else if key=27 then close;
end;

procedure TFrTutupBuku.FormClose(Sender: TObject;
  var Action: TCloseAction);
begin
  Action:=cafree;
end;

procedure TFrTutupBuku.BitBtn2Click(Sender: TObject);
begin
  Close;
end;

procedure TFrTutupBuku.BitBtn1Click(Sender: TObject);
var FieQnt1,FieQnt2,FieQnt1lalu,FieQnt2lalu,FieRP,FieRpLalu:string;
Qnt1Lalu,Qnt2lalu :Integer;
begin
 Case ComboBox1.ItemIndex of
 0,1,2,3,4,5 :
 Begin
  if MyCek_Lock_PeriodeProses(Bulan.Text,Tahun.Text) then
  begin
  Case ComboBox1.ItemIndex of
    0 : begin
          Label2.Caption :='Posting Data';
          Delay(10);
          Label2.Caption :='Proses Aktiva';
          delay(10);
          ProsesAktiva(Bulan.ItemIndex,StrToInt(Tahun.Text));
//          delay(5);
          ProsesHitUlangAktiva(Bulan.ItemIndex,StrToInt(Tahun.Text));

          delay(10);
          ProsesHitUlangAktivaFK(Bulan.ItemIndex,StrToInt(Tahun.Text));
//          delay(5);
          ProsesAktivaFK(Bulan.ItemIndex,StrToInt(Tahun.Text));
          delay(10);
          ProsesHitUlangNeraca(Bulan.ItemIndex,StrToInt(Tahun.Text));
          delay(10);
          ProsesHitUlangAktiva(Bulan.ItemIndex,StrToInt(Tahun.Text));
          delay(10);
          ProsesHitUlangAktivaFK(Bulan.ItemIndex,StrToInt(Tahun.Text));
          delay(5);
          ProsesHPPRL(Bulan.ItemIndex,StrToInt(Tahun.Text),False,'');
          ProsesHPPRL(Bulan.ItemIndex,StrToInt(Tahun.Text),True,'');
          delay(5);
          with QuDevisi do
          begin
            close;
            sql.Clear;
            SQL.Add('Select * from dbDevisi Order by Devisi DESC');
            Open;
          end;
          if not QuDevisi.IsEmpty then
          begin
            XiProgressBar1.Position:=0;
            XiProgressBar1.Max := QuDevisi.RecordCount;
            while not Qudevisi.Eof do
            begin
              Label2.Caption := 'Proses Rugi Laba Devisi : '+QuDevisi.FieldByname('NamaDevisi').AsString+' ('+QuDevisi.FieldByname('Devisi').AsString+')';
              XiProgressBar1.Position := XiProgressBar1.Position+1;
              Delay(10);
              ProsesHPPRL(Bulan.ItemIndex,StrToInt(Tahun.Text),False,QuDevisi.FieldByname('Devisi').AsString);
              ProsesHPPRL(Bulan.ItemIndex,StrToInt(Tahun.Text),True,QuDevisi.FieldByname('Devisi').AsString);
              QuDevisi.Next;
            end;
            XiProgressBar1.Position:=0;
          end;
          ProsesHitUlangNeraca(Bulan.ItemIndex,StrToInt(Tahun.Text));
          delay(10);
          Label2.Caption := 'Proses Tutup Buku Selesai ';
        end;

    1 : begin
          Label2.Caption :='Proses Aktiva';
          delay(10);
          ProsesAktiva(Bulan.ItemIndex,StrToInt(Tahun.Text));
          Label2.Caption :='Proses Aktiva Selesai';
        end;
    2 : begin
          ProsesHitUlangNeraca(Bulan.ItemIndex,StrToInt(Tahun.Text));
        end;
    3 : begin
          ProsesHitUlangAktiva(Bulan.ItemIndex,StrToInt(Tahun.Text));
        end;
    4 : begin
          delay(5);
          with QuDevisi do
          begin
            close;
            sql.Clear;
            SQL.Add('Select * from dbDevisi Order by Devisi DESC');
            Open;
          end;
          if not QuDevisi.IsEmpty then
          begin
            XiProgressBar1.Position:=0;
            XiProgressBar1.Max := QuDevisi.RecordCount;
            while not Qudevisi.Eof do
            begin
              Label2.Caption := 'Proses Rugi Laba Devisi : '+QuDevisi.FieldByname('NamaDevisi').AsString+' ('+QuDevisi.FieldByname('Devisi').AsString+')';
              XiProgressBar1.Position := XiProgressBar1.Position+1;
              Delay(10);
              ProsesHPPRL(Bulan.ItemIndex,StrToInt(Tahun.Text),False,QuDevisi.FieldByname('Devisi').AsString);
              ProsesHPPRL(Bulan.ItemIndex,StrToInt(Tahun.Text),True,QuDevisi.FieldByname('Devisi').AsString);
              QuDevisi.Next;
            end;
            XiProgressBar1.Position:=0;
          end;
          delay(10);
          Label2.Caption := 'Proses HPP dan Rugi Laba Selesai ';
        end;
    5:  begin
         XiProgressBar1.Position:=0;
         XiProgressBar1.Max :=3;
         label2.Caption:='Proses Dasboard ..';
          case Bulan.ItemIndex of
          1:begin FieQnt1:='QntBln1';FieQnt2:='Qnt2bln1';FieRP:='RpBln1';FieQnt1lalu:='Qntbln12';FieQnt2lalu:='Qnt2bln12';FieRpLalu:='RpBln12'; end;
          2:begin FieQnt1:='QntBln2';FieQnt2:='Qnt2bln2';FieRP:='RpBln2';FieQnt1lalu:='Qntbln1';FieQnt2lalu:='Qnt2bln1';FieRpLalu:='RpBln1';end;
          3:begin FieQnt1:='QntBln3';FieQnt2:='Qnt2bln3';FieRP:='RpBln3';FieQnt1lalu:='Qntbln2';FieQnt2lalu:='Qnt2bln2';FieRpLalu:='RpBln2';end;
          4:begin FieQnt1:='QntBln4';FieQnt2:='Qnt2bln4';FieRP:='RpBln4';FieQnt1lalu:='Qntbln3';FieQnt2lalu:='Qnt2bln3';FieRpLalu:='RpBln3';end;
          5:begin FieQnt1:='QntBln5';FieQnt2:='Qnt2bln5';FieRP:='RpBln5';FieQnt1lalu:='Qntbln4';FieQnt2lalu:='Qnt2bln4';FieRpLalu:='RpBln4';end;
          6:begin FieQnt1:='QntBln6';FieQnt2:='Qnt2bln6';FieRP:='RpBln6';FieQnt1lalu:='Qntbln5';FieQnt2lalu:='Qnt2bln5';FieRpLalu:='RpBln5';end;
          7:begin FieQnt1:='QntBln7';FieQnt2:='Qnt2bln7';FieRP:='RpBln7';FieQnt1lalu:='Qntbln6';FieQnt2lalu:='Qnt2bln6';FieRpLalu:='RpBln6';end;
          8:begin FieQnt1:='QntBln8';FieQnt2:='Qnt2bln8';FieRP:='RpBln8';FieQnt1lalu:='Qntbln7';FieQnt2lalu:='Qnt2bln7';FieRpLalu:='RpBln7';end;
          9:begin FieQnt1:='QntBln9';FieQnt2:='Qnt2bln9';FieRP:='RpBln9';FieQnt1lalu:='Qntbln8';FieQnt2lalu:='Qnt2bln8';FieRpLalu:='RpBln8';end;
          10:begin FieQnt1:='QntBln10';FieQnt2:='Qnt2bln10';FieRP:='RpBln10';FieQnt1lalu:='Qntbln9';FieQnt2lalu:='Qnt2bln9';FieRpLalu:='RpBln9';end;
          11:begin FieQnt1:='QntBln11';FieQnt2:='Qnt2bln11';FieRP:='RpBln11';FieQnt1lalu:='Qntbln10';FieQnt2lalu:='Qnt2bln10';FieRpLalu:='RpBln10';end;
          12:begin FieQnt1:='QntBln12';FieQnt2:='Qnt2bln12';FieRP:='RpBln12';FieQnt1lalu:='Qntbln11';FieQnt2lalu:='Qnt2bln11';FieRpLalu:='RpBln11';end;
          end;
          with Dm.Qucari do
          begin
             Close;
             Sql.Clear;
             Sql.add('Update DBSalesCustPrd set '+FieQnt1+' =0,'+FieQnt2+' =0,'+FieRP+'=0 where tahun='+Tahun.Text+'');
             Sql.add('Update DBCustAreaKota set '+FieQnt1+' =0,'+FieQnt2+' =0,'+FieRP+'=0 where tahun='+Tahun.Text+'');
             Sql.add('Update DBAreaKotaPrd set '+FieQnt1+' =0,'+FieQnt2+' =0,'+FieRP+'=0 where tahun='+Tahun.Text+'');
             ExecSQL;
          end;
          with Dm.QuCari do
          begin
          label2.Caption:='Proses Dasboard .........';
          XiProgressBar1.Position:=1;
            Close;
            Sql.Clear;
            Sql.add(' Declare @KOdecustSupp varchar(20),@Bulan Int,@Tahun Int,@Qnt1 Numeric(18,2),@Qnt2 numeric(18,2),@KOdebrg varchar(20),@KOdeSls Varchar(20),@NNetRp numeric(18,2)   '+ #13+
                    ' Declare mydata Cursor For    '+ #13+
                    ' With DataSPB(NOSPB, TGLSPB, NOINV,UrutINV, NoAlamatKirim, KodeSLS, NOSO, TGLSO, NoSPP, TglSPP, NoPO) AS    '+  #13+
                    ' (Select a.NoBukti, a.Tanggal, d.NoBukti NoInvoice, D.Urut UrutInvoice, e.NoAlamatKirim, f.KODESLS, F.NOBUKTI NOSO, F.TANGGAL TGLSO,  '+   #13+
                    ' e.NoBukti NoSPP, e.Tanggal TglSPP, f.NoPesanan  '+   #13+
                    ' From dbSPB a    '+   #13+
                    ' Left Outer join dbSPBDet b on b.NoBukti=a.NoBukti   '+    #13+
                    ' left Outer join dbInvoicePLDet d on d.NoSPB=B.NoBukti and d.UrutSPB=b.Urut   '+    #13+
                    ' Left Outer join dbSPPDet c on c.NoBukti=b.NoSPP and c.Urut=b.UrutSPP       '+     #13+
                    ' Left Outer join dbSPP e on e.NoBukti=c.NoBukti     '+    #13+
                    ' Left Outer join DBSO f on f.NOBUKTI=c.NoSO       '+      #13+
                    ' Group by a.NoBukti, a.Tanggal, d.NoBukti,d.Urut, e.NoAlamatKirim, f.KODESLS, F.NOBUKTI, F.TANGGAL, e.NoBukti, e.Tanggal, f.NoPesanan) '+   #13+

                    '           select A.KOdeCustsupp,A.KOdeSLS,A.KodeBrg,SUM(Isnull(A.QNT1,0)) Qnt1,SUM(Isnull(A.qnt2,0)) Qnt2,Bulan,Tahun,Sum(Isnull(A.NNetRp,0))  NnetRp from (   '+  #13+
                    '           select B.KOdeCustSupp,E.KOdeSLS,MOnth(B.Tanggal) Bulan,YEAR(B.Tanggal) Tahun,                                   '+   #13+
                    '           SUM(isnull(A.Qnt,0)) Qnt1,SUM(isnull(A.Qnt2,0))Qnt2,A.KOdeBrg,Sum(A.NNETRp) NNETRP                            '+    #13+
                    '           from dbinvoicePLdet A                                '+   #13+
                    '           Left Outer Join DbInvoicePL B on A.nobukti=B.nobukti       '+  #13+
                    '           left Outer join (Select x.NoBukti, y.NoSPP                '+   #13+
                    '                                                  from dbSPB x         '+
                    '                                                  left Outer join dbSPBDet y on y.NoBukti=x.NoBukti  '+    #13+
                    '                                          group by x.NoBukti, y.NoSPP) C on C.NoBukti=A.NoSPB    '+     #13+
                    '          left Outer join (Select x.NoBukti, y.NoSO, x.NoAlamatKirim     '+  #13+
                    '                                          from dbSPP x     '+        #13+
                    '                                          left Outer join dbSPPDet y on y.NoBukti=x.NoBukti   '+     #13+
                    '                                          Group by x.NoBukti, y.NoSO, x.NoAlamatKirim) D on D.NoBukti=C.NoSPP  '+  #13+
                    '           left outer join (Select x.*    '+        #13+
                    '                                          from DBSO x) E on E.Nobukti=D.NoSO   '+    #13+
                    '                   where MONTH(B.tanggal)='+IntToStr(Bulan.ItemIndex)+'  and YEAR(B.tanggal)='+tahun.Text+'         '+     #13+
                    '                   Group by B.KOdeCustSupp,E.KOdeSLS,MOnth(B.Tanggal),YEAR(B.Tanggal),KOdeBrg         '+       #13+

                    '           union all      '+     #13+
                    '           '+


                    '           select B.KOdeCustSupp,E.KOdeSLS,MOnth(B.Tanggal) Bulan,YEAR(B.Tanggal) Tahun,                                   '+    #13+
                    '           SUM(isnull(-A.Qnt,0)) Qnt1,SUM(-isnull(A.Qnt2,0))Qnt2,A.KOdeBrg,Sum(-A.NNETRp) NNETRP                            '+   #13+
                    '           from dbRinvoicePLdet A                                '+    #13+
                    '           Left Outer Join DbRInvoicePL B on A.nobukti=B.nobukti       '+     #13+
                    '           Left Outer join DataSPB E on e.NOINV=A.NoInvoice and E.UrutINV=A.UrutInvoice '+   #13+
                    '                   where MONTH(B.tanggal)='+IntToStr(Bulan.ItemIndex)+'  and YEAR(B.tanggal)='+tahun.Text+'         '+   #13+
                    '                   Group by B.KOdeCustSupp,E.KOdeSLS,MOnth(B.Tanggal),YEAR(B.Tanggal),A.KOdeBrg         '+  #13+
		    '	        )a Group By  A.KOdeCustsupp,A.KOdeSLS,A.KodeBrg,Bulan,Tahun                 '+   #13+
                    '           open mydata     '+    #13+

                    ' Fetch Next From mydata Into  @KOdecustSupp,@KOdeSLS,@Kodebrg,@Qnt1,@Qnt2,@Bulan,@Tahun,@NnetRP  '+    #13+
                    ' while @@FETCH_STATUS=0     '+    #13+
                    ' begin    '+       #13+
                    '  if not Exists(Select ''True'' from DBSalesCustPrd where KodeCustSupp =@KODECUSTSUPP and KodeSLS=@KodeSLS and KodeBrg=@KodeBrg and Tahun=@tahun)  '+    #13+
                    '  begin    ' +
                    '  Insert into DBSalesCustPrd (KodeCustSupp,KodeSLS,KodeBrg,Tahun,'+FieQnt1+','+FieQnt2+','+FieRP+') '+    #13+
                    '  VALUES(@KODECUSTSUPP ,@KodeSLS,@KodeBrg,@tahun,@Qnt1 ,@Qnt2,@NNetRp)'+    #13+
                    ' end   '+      #13+
                    ' Else  begin '+    #13+
                    '           update dbsalescustPRD set '+FieQnt1+' =@Qnt1,'+FieQnt2+' = @Qnt2,'+FieRP+'=@NNetRp        '+     #13+
                    '           where KOdeCustSupp=@KOdeCustSupp and tahun=@tahun and Kodebrg=@KOdebrg    and KOdeSls=@KodeSls '+      #13+
                    ' End '+     #13+
                    '           Fetch Next From mydata Into  @KOdecustSupp,@KOdeSLS,@Kodebrg,@Qnt1,@Qnt2,@Bulan,@Tahun,@NnetRP  '+  #13+
                    ' end    '+    #13+
                    ' close mydata     '+ #13+
                    ' Deallocate mydata')  ;
            ExecSQL;
            Close;
            label2.Caption:='Proses Dasboard .......................';
            XiProgressBar1.Position:=2;
            Sql.Clear;
            Sql.add(' Declare @KOdecustSupp varchar(20),@Bulan Int,@Tahun Int,@Qnt1 Numeric(18,2),@Qnt2 numeric(18,2),@KOdeKOta varchar(20),@KOdeArea Varchar(20),@NNetRp numeric(18,2)   '+
                    ' Declare mydata Cursor For    '+
                    '           select A.KOdeCustsupp,A.kota,A.KodeArea,SUM(Isnull(A.QNT1,0)) Qnt1,SUM(Isnull(A.qnt2,0)) Qnt2,Bulan,Tahun,Sum(Isnull(A.NNetRp,0))  NnetRp from (   '+
                    '           select B.KOdeCustSupp,D.kodekota kota,D.KodeArea,MOnth(B.Tanggal) Bulan,YEAR(B.Tanggal) Tahun,                                   '+
                    '           SUM(A.Qnt) Qnt1,SUM(A.Qnt2)Qnt2,Sum(A.NNETRp) NNETRP                            '+
                    '           from dbinvoicePLdet A                                '+
                    '           Left Outer Join DbInvoicePL B on A.nobukti=B.nobukti       '+
                    '           Left Outer JOin DBCUSTSUPP C on B.KodeCustSupp = C.KODECUSTSUPP         '+
                    '           Left outer join DBKOTA D on C.kota = D.KodeKota                         '+
                    '           where MONTH(B.tanggal)='+IntToStr(Bulan.ItemIndex)+'  and YEAR(B.tanggal)='+tahun.Text+'         '+
                    '           Group by B.KOdeCustSupp,D.kodekota,D.KodeArea,MOnth(B.Tanggal),YEAR(B.Tanggal)         '+
                    '           union all      '+
                    '           select B.KOdeCustSupp,D.kodekota kota,D.KodeArea,MOnth(B.Tanggal) Bulan,YEAR(B.Tanggal) Tahun,                                   '+
                    '           SUM(-A.Qnt) Qnt1,SUM(-A.Qnt2)Qnt2,Sum(-A.NNETRp) NNETRP                            '+
                    '           from dbRinvoicePLdet A                                '+
                    '           Left Outer Join DbRInvoicePL B on A.nobukti=B.nobukti       '+
                    '           left outer join DBCUSTSUPP C on B.KodeCustSupp = C.KODECUSTSUPP     '+
                    '           left outer join DBKOTA D on C.KOTA = D.KodeKota   '+
                    '           where MONTH(B.tanggal)='+IntToStr(Bulan.ItemIndex)+'  and YEAR(B.tanggal)='+tahun.Text+'         '+
                    '           Group by B.KOdeCustSupp,D.kodekota,D.KodeArea,MOnth(B.Tanggal),YEAR(B.Tanggal)         '+
		    '	        )a Group By  A.KOdeCustsupp,A.Kota,A.KodeArea,Bulan,Tahun               '+
                    '           open mydata     '+

                    ' Fetch Next From mydata Into   @KOdecustSupp,@kodeKota,@KOdeArea,@Qnt1,@Qnt2,@Bulan,@Tahun,@NnetRP   '+
                    ' while @@FETCH_STATUS=0     '+
                    ' begin    '+
                    '  if not Exists(Select ''True'' from DBCustAreaKota where KodeCustSupp =@KODECUSTSUPP and KodeArea=@KodeArea and KodeKota=@KodeKota and Tahun=@tahun) '+
                    '  begin    ' +
                    '  Insert into DBCustAreaKota (KodeCustSupp,KodeArea,KodeKota,Tahun,'+FieQnt1+','+FieQnt2+','+FieRP+') '+
                    '  VALUES(@KODECUSTSUPP ,@KodeArea,@KodeKota,@tahun,@Qnt1 ,@Qnt2,@NNetRp)'+
                    ' end   '+
                    ' Else  begin '+
                    '           update DBCustAreaKota set '+FieQnt1+' =@Qnt1,'+FieQnt2+' = @Qnt2,'+FieRP+'=@NNetRp        '+
                    '           where KOdeCustSupp=@KOdeCustSupp and tahun=@tahun and KodeKota=@KOdeKOta and KodeArea=@KOdeArea       '+
                    ' End '+
                    '           Fetch Next From mydata Into   @KOdecustSupp,@kodeKota,@KOdeArea,@Qnt1,@Qnt2,@Bulan,@Tahun,@NnetRP   '+
                    ' end    '+
                    ' close mydata     '+
                    ' Deallocate mydata');
            ExecSQL;
            label2.Caption:='Proses Dasboard .......................................';
            XiProgressBar1.Position:=3;
            Close;
            Sql.Clear;
            Sql.add(' Declare @KOdebrg varchar(20),@Bulan Int,@Tahun Int,@Qnt1 Numeric(18,2),@Qnt2 numeric(18,2),@KOdeKOta varchar(20),@KOdeArea Varchar(20),@NNetRp numeric(18,2)   '+
                    ' Declare mydata Cursor For    '+
                    '           select A.kota,A.KodeArea,A.Kodebrg,SUM(Isnull(A.QNT1,0)) Qnt1,SUM(Isnull(A.qnt2,0)) Qnt2,Bulan,Tahun,Sum(Isnull(A.NNetRp,0))  NnetRp from (   '+
                    '           select D.kodekota kota,D.KodeArea,A.Kodebrg,MOnth(B.Tanggal) Bulan,YEAR(B.Tanggal) Tahun,                                   '+
                    '           SUM(A.Qnt) Qnt1,SUM(A.Qnt2)Qnt2,Sum(A.NNETRp) NNETRP                            '+
                    '           from dbinvoicePLdet A                                '+
                    '           Left Outer Join DbInvoicePL B on A.nobukti=B.nobukti       '+
                    '           left outer join DBCUSTSUPP C on B.KodeCustSupp = C.KODECUSTSUPP     '+
                    '           left outer join DBKOTA D on C.KOTA = D.KodeKota   '+
                    '           where MONTH(B.tanggal)='+IntToStr(Bulan.ItemIndex)+'  and YEAR(B.tanggal)='+tahun.Text+'         '+
                    '           Group by D.kodekota ,D.KodeArea,A.Kodebrg,MOnth(B.Tanggal),YEAR(B.Tanggal)         '+
                    '           union all      '+
                    '           select D.kodekota kota,D.KodeArea,A.Kodebrg,MOnth(B.Tanggal) Bulan,YEAR(B.Tanggal) Tahun,                                   '+
                    '           SUM(-A.Qnt) Qnt1,SUM(-A.Qnt2)Qnt2,Sum(-A.NNETRp) NNETRP                            '+
                    '           from dbRinvoicePLdet A                                '+
                    '           Left Outer Join DbRInvoicePL B on A.nobukti=B.nobukti       '+
                    '           left outer join DBCUSTSUPP C on B.KodeCustSupp = C.KODECUSTSUPP     '+
                    '           left outer join DBKOTA D on C.KOTA = D.KodeKota   '+
                    '           where MONTH(B.tanggal)='+IntToStr(Bulan.ItemIndex)+'  and YEAR(B.tanggal)='+tahun.Text+'         '+
                    '           Group by D.kodekota ,D.KodeArea,A.Kodebrg,MOnth(B.Tanggal),YEAR(B.Tanggal)         '+
		    '	        )a Group By  A.kota,A.KodeArea,A.Kodebrg,Bulan,Tahun               '+
                    '           open mydata     '+

                    ' Fetch Next From mydata Into   @kodeKota,@KOdeArea,@Kodebrg,@Qnt1,@Qnt2,@Bulan,@Tahun,@NnetRP   '+
                    ' while @@FETCH_STATUS=0     '+
                    ' begin    '+
                    '  if not Exists(Select ''True'' from Dbareakotaprd where KodeBrg =@KODEbrg and KodeArea=@KodeArea and KodeKota=@KodeKota and Tahun=@tahun)  '+
                    '  begin    ' +
                    '  Insert into DBAreaKotaPrd (KodeArea,KodeKota,Kodebrg,Tahun,'+FieQnt1+','+FieQnt2+','+FieRP+') '+
                    '  VALUES(@KodeArea,@KodeKota,@KOdeBrg ,@tahun,@Qnt1 ,@Qnt2,@NNetRp)'+
                    ' end   '+
                    ' Else  begin '+
                    '           update DBAreaKotaPrd set '+FieQnt1+' =@Qnt1,'+FieQnt2+' = @Qnt2,'+FieRP+'=@NNetRp        '+
                    '           where tahun=@tahun and KodeKota=@KOdeKOta and KodeArea=@KOdeArea  and KOdebrg=@KOdebrg      '+
                    ' End '+
                    '           Fetch Next From mydata Into   @kodeKota,@KOdeArea,@Kodebrg,@Qnt1,@Qnt2,@Bulan,@Tahun,@NnetRP  '+
                    ' end    '+
                    ' close mydata     '+
                    ' Deallocate mydata');
             ExecSQL;
             label2.Caption:='';
             XiProgressBar1.Position:=0;
             ShowMessage('Proses Dasboard selesai');
          end;

        end;
  end;
  end else
  begin
    MsgPeriodeSudahDikunci;
  end;
 end;

   6 : begin
          Label2.Caption :='Proses Aktiva Fiskal';
          delay(10);
          ProsesAktivaFK(Bulan.ItemIndex,StrToInt(Tahun.Text));
          Label2.Caption :='Proses Aktiva Fiskal Selesai';
        end;
    7 : begin
          ProsesHitUlangAktivaFK(Bulan.ItemIndex,StrToInt(Tahun.Text));
        end;
  end;

end;

end.

