unit MyGlobal;

interface

uses
   Classes,Db,Controls;

const
     StartKey = 2367;
     MultKey  = 5682;
     AddKey   = 9870053;
     {StartKey = 1024;
     MultKey  = 512;
     AddKey   = 128;}
     yPassword ='anekajc1a9';
     //yPassword ='Anekajc1a9';
     Usd='sa';
type
    TNamaSatuanBrg= array[1..4] of String;
    TIsiSatuanBrg= array[1..4] of Real;
    THargaBrg= array[1..4] of Real;        
var
   KodeForm,IsMax, gFlagMenu: Byte;
   KodeBrows,KodeBrows2, StatusUser : Integer;
   nNamaCust,NomorBukti,IDUser, PeriodBln, PeriodThn, TempCode, xInisialPNJ, xInisialTrans , NoBuktiBeli,AlamatGdg,XnamaGudang: String;
   SetUpTgl :Tdatetime;
   Koneksi : array [0..20] of string;
   SimbolFlagMenu : array [0..1] of string;
   xReset : Byte;
   Teks,mGrpCustomer,mBarang,Sat1,Sat2,Sat3,currDir,
   F1,F2,F3,F4,Separator,MyUser,SKodebrg,SKodeBrg1,mDevisi : String;
   Level,GraphSelections,xUrutPo,xUrutBeli,xUrutSo,xUrutJual,Slv,NoUrutBeli    : Integer;
   IsBrowsSimpan, isSO : Boolean;
   LevelUserAccess,xN1,xN2,xN3,xN4 : integer;
   //HrgJlMin,XSqm : real;
   xList : TList;
   LetakRecord : TBookMarkStr;
   gTempNoBukti,gTempNoBuktiSO, gINSGdgSPB, gINSBrgSPB, gINSGdgSO, gINSBrgSO,
   gINSGdgSJ, gINSBrgSJ, gINSGdgJual, gINSBrgJual, gINSGdgRJual, gINSBrgRJual,gTipeTrans: String;
   //gTempNoBuktiSPB, gTempGudangSPB: String;
   //gTempNoBuktiBeli_I, gTempContainerBeli_I: String;
   gTempTglLPBBeli_I: TDateTime;
   gDatabaseStk, gDatabaseGL, gProgram: String;
   gPemakaiAllGdg,IsLRHPP,xIsLokal,Xbatal,SCetak: Boolean;
   gIsPPN: Boolean;
   //gSuratJlnSdhAdaNota: Boolean;
   gFilterKodeCustA, gFilterKodeCustB, gFilterKodeGdg,myTerbilang,xPerkPostHutPiut,xAktiva,xkodeexport,xCustSupp: String;
   gFilterTanggalA, gFilterTanggalB: TDateTime;
   gFilterKodeTipe,xIsPpn: Integer;
   xStatusBrows : TModalResult;
   xValue : Array of Variant;
   JenisBJ,NamaBJ:string;
implementation

end.
