# 📋 EXPANDABLE PATTERN RULES - SPK Implementation

## 🎯 **PATTERN YANG BERHASIL**

### **1. Controller Pattern - SELALU IKUTI INI**
```php
public function getSpkDetailByNoBukti()
{
    try {
        if (!request()->NoBukti) {
            return $this->setResponseError('No Bukti tidak boleh kosong');
        }

        $this->requestAjax($this->access, 'HASACCESS');

        $trans = $this->spkRepository->getSpkByNoBukti(request()->NoBukti);
        if ($trans instanceof \Illuminate\Support\Collection) {
            $trans = $trans->first();
        }

        if (!$trans) {
            return $this->setResponseError('Data tidak ditemukan');
        }

        $datatableData = CustomDataTable::init()
            ->of($this->spkRepository->getSpkDetailByNoBukti(request()->NoBukti))
            ->apply()
            ->mapData(function ($row) {
                $row->Urut = $row->Urut ?? '';
                $row->NoBukti = request()->NoBukti;
                return $row;
            })
            ->addColumn('action', function ($data) use ($trans) {
                $html = '';
                if (($trans->IsOtorisasi1 ?? 0) == 0) {
                    // DEFINE VARIABLES BEFORE USING
                    $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                    $urut = isset($data->Urut) ? $data->Urut : '';
                    
                    $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                        <div class="notification-container close-button-container">';
                    if (in_array('ISKOREKSI', $this->access)) {
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditSpk btn--detail' data-bukti='{$noBukti}' data-urut='{$urut}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                    }
                    if (in_array('ISHAPUS', $this->access)) {
                        $url = route('produksi.spk.delete');
                        $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete btn--detail' data-url='{$url}' data-id='{$noBukti}' data-urut='{$urut}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                    }
                    $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                }
                return $html;
            })
            ->done();

        return $datatableData;

    } catch (\Exception $e) {
        \Log::error('SPK Detail Error:', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
        return $this->setResponseError($e->getMessage());
    }
}
```

### **2. Repository Pattern - SELALU IKUTI INI**
```php
public function getSpkDetailByNoBukti($noBukti)
{
    try {
        \Log::info('getSpkDetailByNoBukti function is called with NoBukti: ' . $noBukti);

        // DYNAMIC COLUMN DETECTION
        $columns = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'dbSPKDet' ORDER BY ORDINAL_POSITION");
        $columnNames = array_map(function($col) { return $col->COLUMN_NAME; }, $columns);
        $hasUrut = in_array('Urut', $columnNames);

        // BUILD QUERY BASED ON AVAILABLE COLUMNS
        if ($hasUrut) {
            $query = "select A.Urut, A.KodeBrg, B.NamaBrg, A.Qnt,
            CASE WHEN A.NoSat=1 THEN B.SAT1 WHEN A.NoSat=2 THEN B.SAT2 ELSE '' END as Satuan,
            A.NoSat, A.Isi, '' as Keterangan
            from dbSPKDet A
            left join dbBarang B on A.KodeBrg = B.KodeBrg
            where A.NoBukti = ?
            order by A.Urut";
        } else {
            $query = "select ROW_NUMBER() OVER (ORDER BY A.KodeBrg) as Urut, A.KodeBrg, B.NamaBrg, A.Qnt,
            CASE WHEN A.NoSat=1 THEN B.SAT1 WHEN A.NoSat=2 THEN B.SAT2 ELSE '' END as Satuan,
            A.NoSat, A.Isi, '' as Keterangan
            from dbSPKDet A
            left join dbBarang B on A.KodeBrg = B.KodeBrg
            where A.NoBukti = ?";
        }

        $result = DB::select($query, [$noBukti]);
        return $result;
    } catch (\Illuminate\Database\QueryException $ex) {
        \Log::error('Error in getSpkDetailByNoBukti: ' . $ex->getMessage());
        throw $ex;
    }
}
```

### **3. Routes Pattern - SELALU IKUTI INI**
```php
Route::prefix('produksi')->name('produksi.')->group(function () {
    Route::prefix('transaksi-spk')->name('spk')->middleware('policy:HASACCESS,08103')->group(function () {
        Route::get('/', [SPKController::class, 'index'])->name('.index');
        Route::delete('/', [SPKController::class, 'delete'])->name('.delete');
        Route::post('/detail', [SPKController::class, 'getSpkDetailByNoBukti'])->name('.detail-Spk');
        Route::post('/set-otorisasi', [SPKController::class, 'setOtorisasi'])->name('.set-otorisasi');
    });
});
```

### **4. JavaScript Pattern - SELALU IKUTI INI**
```javascript
import $globalVariable, { publicURL, csfr_token } from "../base-function.js";

(function ($, { baseSwal, baseAjax, formAjax, getModal, globalDelete, applyPlugins, mergeWithDefaultOptions, swalConfirm }) {
    
    var datatableMain = $("#datatableMain").DataTable({
        ...mergeWithDefaultOptions({
            ajax: {
                url: $("#datatableMain").data("server"),
                type: 'GET',
                error: function (xhr, error, thrown) {
                    console.log('DataTables error:', error);
                    console.log('Exception:', thrown);
                    console.log('Response:', xhr.responseText);
                }
            },
            columns: [
                { data: null, orderable: false, searchable: false, defaultContent: "", className: "dt-control" },
                { data: "NoBukti" },
                { data: "Tanggal" },
                { data: "Keterangan" },
                { data: "action", orderable: false, searchable: false, className: "text-center parentBtnRow" }
            ],
            fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData.indikatorExpand === false) {
                    $(nRow).find("td.dt-control").addClass("indicator-white");
                }
            }
        })
    });

    // EXPAND/COLLAPSE HANDLER
    $(document).on("click", "#datatableMain > tbody td.dt-control", function () {
        var tr = $(this).closest("tr");
        var row = datatableMain.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass("shown");
        } else {
            showChildDatatable(row, tr);
        }
    });

    // SHOW CHILD DATATABLE
    function showChildDatatable(row, tr) {
        row.child(formatChildTable(row.data())).show();
        tr.addClass("shown");
        
        let childTable = row.child().find('#datatableExpand').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: row.data().detailUrl,
                type: 'GET',
                error: function (xhr, error, thrown) {
                    console.error('Child DataTables error:', error, thrown);
                }
            },
            columns: [
                { data: "Urut" },
                { data: "KodeBrg" },
                { data: "NamaBrg" },
                { data: "Qnt", className: "text-right" },
                { data: "Satuan" },
                { data: "Keterangan" }
            ],
            order: [[0, 'asc']],
            pageLength: 10,
            responsive: true
        });
    }

    // FORMAT CHILD TABLE HTML
    function formatChildTable(data) {
        return `
            <div class="row">
                <div class="col-md-12">
                    <table id="datatableExpand" class="table table-bordered table-striped table-hover nowrap w-100"
                        data-server="${data.detailUrl}">
                        <thead>
                            <tr>
                                <th>Urut</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        `;
    }

})($, $globalVariable);
```

## 🚨 **TROUBLESHOOTING RULES**

### **Error 1: "Undefined variable $noBukti"**
**SOLUSI:** Definisikan variabel sebelum digunakan dalam closure
```php
// ✅ BENAR
$noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
$urut = isset($data->Urut) ? $data->Urut : '';
$html .= "<button data-bukti='{$noBukti}' data-urut='{$urut}'>";
```

### **Error 2: "Missing required parameter for route"**
**SOLUSI:** Hapus route yang konflik
```php
// ❌ SALAH - Ada 2 route yang konflik
Route::delete('/', [Controller::class, 'delete'])->name('.delete');
Route::delete('/delete/{noBukti}', [Controller::class, 'destroy'])->name('.delete');

// ✅ BENAR - Hanya satu route
Route::delete('/', [Controller::class, 'delete'])->name('.delete');
```

### **Error 3: "Method deleteSpk does not exist"**
**SOLUSI:** Tambahkan method di repository
```php
public function deleteSpk($noBukti)
{
    try {
        // Delete detail records first
        $deletedDetails = DB::delete("DELETE FROM dbSPKDet WHERE NoBukti = ?", [$noBukti]);
        
        // Delete header record
        $deletedHeader = DB::delete("DELETE FROM dbSPK WHERE NoBukti = ?", [$noBukti]);
        
        return $deletedHeader > 0;
    } catch (\Illuminate\Database\QueryException $ex) {
        \Log::error('Error in deleteSpk: ' . $ex->getMessage());
        throw $ex;
    }
}
```

### **Error 4: "DataTables unknown parameter 'Urut'"**
**SOLUSI:** Pastikan field ada di query dan mapping
```php
->mapData(function ($row) {
    $row->Urut = $row->Urut ?? '';
    $row->NoBukti = request()->NoBukti;
    return $row;
})
```

### **Error 5: "501 Not Implemented"**
**SOLUSI:** Ikuti pola Memorial - TIDAK gunakan parameter Request
```php
// ❌ SALAH
public function getSpkDetailByNoBukti(Request $request)

// ✅ BENAR
public function getSpkDetailByNoBukti()
{
    if (!request()->NoBukti) {
        return $this->setResponseError('No Bukti tidak boleh kosong');
    }
    // ... rest of logic
}
```

## ✅ **CHECKLIST IMPLEMENTASI**

### **Controller Checklist:**
- [ ] Method tanpa parameter Request
- [ ] Menggunakan request() helper
- [ ] Variabel terdefinisi dalam closure
- [ ] Error handling dengan logging
- [ ] CustomDataTable dengan addColumn

### **Repository Checklist:**
- [ ] Dynamic column detection
- [ ] Fallback query untuk kolom yang tidak ada
- [ ] Comprehensive logging
- [ ] Error handling dengan QueryException

### **Routes Checklist:**
- [ ] POST method untuk detail endpoint
- [ ] Tidak ada route yang konflik
- [ ] Middleware policy yang tepat
- [ ] Naming convention yang konsisten

### **JavaScript Checklist:**
- [ ] ES6 modules pattern
- [ ] Proper error handling
- [ ] Expand/collapse logic
- [ ] Child DataTable initialization

## 🎯 **GOLDEN RULES**

1. **SELALU ikuti pola Memorial** yang sudah terbukti berjalan
2. **JANGAN gunakan parameter Request** untuk method detail
3. **DEFINISIKAN variabel** sebelum digunakan dalam closure
4. **HAPUS route yang konflik** dengan nama yang sama
5. **TAMBAHKAN method yang dibutuhkan** di controller/repository
6. **CLEAR route cache** setelah mengubah routes
7. **LOG semua error** untuk debugging
8. **TEST step by step** untuk memastikan setiap komponen berfungsi

---

**Last Updated**: December 2024  
**Status**: ✅ Tested & Working  
**Reference**: SPK Implementation (Success) 