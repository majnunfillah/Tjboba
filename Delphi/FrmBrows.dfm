object FrBrows: TFrBrows
  Left = 640
  Top = 173
  BorderStyle = bsDialog
  Caption = 'Cari Data . . .'
  ClientHeight = 366
  ClientWidth = 623
  Color = 16432540
  Font.Charset = DEFAULT_CHARSET
  Font.Color = clWindowText
  Font.Height = -11
  Font.Name = 'MS Sans Serif'
  Font.Style = []
  KeyPreview = True
  OldCreateOrder = False
  Position = poDesktopCenter
  OnClose = FormClose
  OnKeyDown = FormKeyDown
  OnShow = FormShow
  PixelsPerInch = 96
  TextHeight = 13
  object Panel2: TPanel
    Left = 0
    Top = 60
    Width = 623
    Height = 272
    Align = alClient
    BevelInner = bvRaised
    BevelOuter = bvLowered
    Color = 16432540
    TabOrder = 0
    object GridBrows: TdxDBGrid
      Left = 2
      Top = 2
      Width = 619
      Height = 268
      Hint = 'Untuk cari data ketik kata yang dicari di grid'
      Bands = <
        item
        end>
      DefaultLayout = True
      HeaderPanelRowCount = 1
      ShowSummaryFooter = True
      SummaryGroups = <>
      SummarySeparator = ', '
      Align = alClient
      ParentShowHint = False
      ShowHint = True
      TabOrder = 0
      OnKeyDown = GridBrowsKeyDown
      OnMouseUp = GridBrowsMouseUp
      AutoExpandOnSearch = False
      Filter.Criteria = {00000000}
      LookAndFeel = lfFlat
      OptionsBehavior = [edgoAutoSearch, edgoAutoSort, edgoDragScroll, edgoEnterShowEditor, edgoMultiSelect, edgoTabThrough, edgoVertThrough]
      OptionsCustomize = [edgoBandMoving, edgoBandSizing, edgoColumnMoving, edgoColumnSizing, edgoFullSizing, edgoKeepColumnWidth, edgoNotHideColumn]
      OptionsDB = [edgoCancelOnExit, edgoCanDelete, edgoCanInsert, edgoCanNavigation, edgoConfirmDelete, edgoLoadAllRecords, edgoUseBookmarks]
      OptionsView = [edgoAutoWidth, edgoBandHeaderWidth, edgoIndicator, edgoRowAutoHeight, edgoUseBitmap]
      ShowRowFooter = True
    end
  end
  object Panel1: TPanel
    Left = 0
    Top = 0
    Width = 623
    Height = 60
    Align = alTop
    BevelOuter = bvNone
    BorderWidth = 8
    BorderStyle = bsSingle
    TabOrder = 1
    object Panel3: TPanel
      Left = 8
      Top = 8
      Width = 603
      Height = 40
      Align = alClient
      BevelOuter = bvNone
      TabOrder = 0
      DesignSize = (
        603
        40)
      object Label1: TLabel
        Left = 0
        Top = 0
        Width = 59
        Height = 16
        Caption = 'Filter Data'
        Font.Charset = DEFAULT_CHARSET
        Font.Color = clWindowText
        Font.Height = -13
        Font.Name = 'Tahoma'
        Font.Style = []
        ParentFont = False
      end
      object EditFilter: TEdit
        Left = 0
        Top = 18
        Width = 601
        Height = 21
        Anchors = [akLeft, akTop, akRight]
        BevelKind = bkFlat
        BorderStyle = bsNone
        Color = 14810109
        TabOrder = 0
        OnChange = EditFilterChange
        OnKeyDown = EditFilterKeyDown
      end
    end
  end
  object Panel4: TPanel
    Left = 0
    Top = 332
    Width = 623
    Height = 34
    Align = alBottom
    BevelOuter = bvNone
    TabOrder = 2
    object TambahBtn: TSpeedButton
      Left = 5
      Top = 5
      Width = 56
      Height = 25
      Caption = '&Tambah'
      Font.Charset = DEFAULT_CHARSET
      Font.Color = clBlack
      Font.Height = -11
      Font.Name = 'Tahoma'
      Font.Style = [fsBold]
      ParentFont = False
      Transparent = False
      Visible = False
      OnClick = TambahBtnClick
    end
    object KoreksiBtn: TSpeedButton
      Left = 63
      Top = 5
      Width = 56
      Height = 25
      Caption = '&Koreksi'
      Font.Charset = DEFAULT_CHARSET
      Font.Color = clBlack
      Font.Height = -11
      Font.Name = 'Tahoma'
      Font.Style = [fsBold]
      NumGlyphs = 2
      ParentFont = False
      Transparent = False
      Visible = False
    end
    object HapusBtn: TSpeedButton
      Left = 121
      Top = 5
      Width = 56
      Height = 25
      Caption = '&Hapus'
      Font.Charset = DEFAULT_CHARSET
      Font.Color = clBlack
      Font.Height = -11
      Font.Name = 'Tahoma'
      Font.Style = [fsBold]
      NumGlyphs = 2
      ParentFont = False
      Transparent = False
      Visible = False
    end
    object Button1: TButton
      Left = 469
      Top = 5
      Width = 75
      Height = 25
      Caption = '&OK'
      TabOrder = 0
      OnClick = Button1Click
    end
    object Button2: TButton
      Left = 543
      Top = 5
      Width = 75
      Height = 25
      Caption = '&Batal'
      TabOrder = 1
      OnClick = Button2Click
    end
    object Button3: TButton
      Left = 189
      Top = 5
      Width = 75
      Height = 25
      Caption = 'Proses'
      TabOrder = 2
      Visible = False
    end
  end
  object QuBrows: TADOQuery
    CacheSize = 2000
    Connection = DM.MyStock
    CursorType = ctStatic
    Parameters = <>
    Left = 20
    Top = 120
  end
  object DsBrows: TDataSource
    DataSet = QuBrows
    Left = 50
    Top = 120
  end
  object QuBrowGL: TADOQuery
    Parameters = <>
    Left = 85
    Top = 117
  end
  object DsQuBrowGL: TDataSource
    DataSet = QuBrowGL
    Left = 120
    Top = 122
  end
  object Sp_Simpan: TADOStoredProc
    Connection = DM.MyStock
    ProcedureName = 'Sp_Customer;1'
    Parameters = <
      item
        Name = 'RETURN_VALUE'
        DataType = ftInteger
        Direction = pdReturnValue
        Precision = 10
        Value = Null
      end
      item
        Name = '@mode'
        Attributes = [paNullable]
        DataType = ftWord
        Precision = 3
        Value = Null
      end
      item
        Name = '@kodecust'
        Attributes = [paNullable]
        DataType = ftString
        Size = 10
        Value = Null
      end
      item
        Name = '@namacust'
        Attributes = [paNullable]
        DataType = ftString
        Size = 40
        Value = Null
      end
      item
        Name = '@Alamat'
        Attributes = [paNullable]
        DataType = ftString
        Size = 50
        Value = Null
      end
      item
        Name = '@Kota'
        Attributes = [paNullable]
        DataType = ftString
        Size = 25
        Value = Null
      end
      item
        Name = '@Telpon'
        Attributes = [paNullable]
        DataType = ftString
        Size = 25
        Value = Null
      end
      item
        Name = '@Fax'
        Attributes = [paNullable]
        DataType = ftString
        Size = 25
        Value = Null
      end
      item
        Name = '@person'
        Attributes = [paNullable]
        DataType = ftString
        Size = 25
        Value = Null
      end
      item
        Name = '@Rekening'
        Attributes = [paNullable]
        DataType = ftString
        Size = 25
        Value = Null
      end
      item
        Name = '@Negara'
        Attributes = [paNullable]
        DataType = ftString
        Size = 15
        Value = Null
      end
      item
        Name = '@Area'
        Attributes = [paNullable]
        DataType = ftString
        Size = 5
        Value = Null
      end
      item
        Name = '@Status'
        Attributes = [paNullable]
        DataType = ftString
        Size = 5
        Value = Null
      end
      item
        Name = '@NPWP'
        Attributes = [paNullable]
        DataType = ftString
        Size = 20
        Value = Null
      end
      item
        Name = '@KodeSls'
        Attributes = [paNullable]
        DataType = ftString
        Size = 5
        Value = Null
      end
      item
        Name = '@Diskon'
        Attributes = [paNullable]
        DataType = ftBCD
        NumericScale = 2
        Precision = 6
        Value = Null
      end
      item
        Name = '@Plafon'
        Attributes = [paNullable]
        DataType = ftBCD
        NumericScale = 2
        Precision = 18
        Value = Null
      end
      item
        Name = '@Hari'
        Attributes = [paNullable]
        DataType = ftWord
        Precision = 3
        Value = Null
      end
      item
        Name = '@Tipe'
        Attributes = [paNullable]
        DataType = ftString
        Size = 1
        Value = Null
      end
      item
        Name = '@Catatan'
        Attributes = [paNullable]
        DataType = ftString
        Size = 255
        Value = Null
      end
      item
        Name = '@Toko'
        Attributes = [paNullable]
        DataType = ftWord
        Precision = 3
        Value = Null
      end
      item
        Name = '@kepada'
        Attributes = [paNullable]
        DataType = ftString
        Size = 50
        Value = Null
      end
      item
        Name = '@att'
        Attributes = [paNullable]
        DataType = ftString
        Size = 50
        Value = Null
      end
      item
        Name = '@cc'
        Attributes = [paNullable]
        DataType = ftString
        Size = 50
        Value = Null
      end
      item
        Name = '@Kodebrd'
        Attributes = [paNullable]
        DataType = ftString
        Size = 5
        Value = Null
      end
      item
        Name = '@Kodefcs'
        Attributes = [paNullable]
        DataType = ftString
        Size = 5
        Value = Null
      end
      item
        Name = '@Kodesubfcs'
        Attributes = [paNullable]
        DataType = ftString
        Size = 5
        Value = Null
      end
      item
        Name = '@Tanggal'
        Attributes = [paNullable]
        DataType = ftDateTime
        Value = Null
      end
      item
        Name = '@BERIKAT'
        Attributes = [paNullable]
        DataType = ftInteger
        Precision = 10
        Value = Null
      end>
    Left = 150
    Top = 125
  end
end
