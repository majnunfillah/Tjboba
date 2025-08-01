unit MyModul;

interface

uses
  Windows, Messages, cxGridBandedTableView, cxClasses, cxStyles,
  cxGridTableView, ADODB, ImgList, Controls, DB, Classes, SysUtils,
  Graphics, Forms, Dialogs, frxClass, frxDBSet;

type
  TDM = class(TDataModule)
    MyStock: TADOConnection;
    QuCari: TADOQuery;
    DsQuBrows: TDataSource;
    QuNomor: TADOQuery;
    DaftarNO: TADOQuery;
    QueryBrow: TADOQuery;
    QuBukaKunci: TADOQuery;
    QuKOde: TADOQuery;
    Qusisa: TADOQuery;
    QuReg: TADOQuery;
    sp_getLastPrice: TADOStoredProc;
    QuCari2: TADOQuery;
    ImageList2: TImageList;
    QuJenisBarang: TADOQuery;
    TaNomor: TADOTable;
    QuTemp: TADOQuery;
    QuPerusahaan: TADOQuery;
    DBTransfer: TADOConnection;
    QuCariGL: TADOQuery;
    QuCariUrut: TADOQuery;
    MyStockMkt: TADOConnection;
    cxStyleRepository1: TcxStyleRepository;
    cxStyle1: TcxStyle;
    cxStyle2: TcxStyle;
    cxStyle3: TcxStyle;
    cxStyle4: TcxStyle;
    cxStyle5: TcxStyle;
    cxStyle6: TcxStyle;
    cxStyle7: TcxStyle;
    cxStyle8: TcxStyle;
    cxGridStyleHd: TcxGridTableViewStyleSheet;
    cxGridStyleDt: TcxGridTableViewStyleSheet;
    cxGridBandStyleHd: TcxGridBandedTableViewStyleSheet;
    cxGridBandStyleDt: TcxGridBandedTableViewStyleSheet;
    cxGridStyle: TcxGridTableViewStyleSheet;
    cxGridBandStyle: TcxGridBandedTableViewStyleSheet;
    frxDBPerusahaan: TfrxDBDataset;
  private
    { Private declarations }
  public
    { Public declarations }
  end;

var
  DM: TDM;

implementation

{$R *.DFM}

end.







