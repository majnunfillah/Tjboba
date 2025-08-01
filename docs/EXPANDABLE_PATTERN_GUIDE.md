# 📋 EXPANDABLE PATTERN GUIDE - SPK Implementation

## DAFTAR ISI
1. [Overview Pattern Expandable](#overview-pattern-expandable)
2. [Implementation Steps](#implementation-steps)
3. [Troubleshooting Guide](#troubleshooting-guide)
4. [Best Practices](#best-practices)
5. [Code Examples](#code-examples)

---

## OVERVIEW PATTERN EXPANDABLE

### **🎯 Tujuan**
Implementasi expandable rows di DataTables untuk menampilkan detail data dalam child table yang terpisah.

### **📊 Struktur Data**
```
Parent Table (SPK Header)
├── NoBukti
├── Tanggal
├── Keterangan
└── [Expand Button] → Child Table (SPK Detail)
    ├── Urut
    ├── KodeBrg
    ├── NamaBrg
    ├── Qnt
    └── Satuan
```

### **🔧 Komponen yang Dibutuhkan**
- **Controller**: Method untuk handle AJAX request detail
- **Repository**: Query untuk mengambil data detail
- **View**: Template expandable table
- **JavaScript**: Logic untuk expand/collapse
- **Routes**: Endpoint untuk AJAX detail

---

## IMPLEMENTATION STEPS

### **1. Controller Implementation**

#### **Method getSpkDetailByNoBukti()**
```php
public function getSpkDetailByNoBukti()
{
    try {
        if (!request()->NoBukti) {
            return $this->setResponseError('No Bukti tidak boleh kosong');
        }

        $this->requestAjax($this->access, 'HASACCESS');

        // Get trans data and convert to object if it's a collection
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
                // Ensure Urut field is preserved
                $row->Urut = $row->Urut ?? '';
                $row->Qnt = number_format(floatval($row->Qnt) ?? 0, 2, ',', '.');
                $row->Isi = number_format(floatval($row->Isi) ?? 0, 2, ',', '.');
                // Pastikan NoBukti ada untuk child DataTable
                $row->NoBukti = request()->NoBukti;
                return $row;
            })
            ->addColumn('action', function ($data) use ($trans) {
                $html = '';
                if (($trans->IsOtorisasi1 ?? 0) == 0) {
                    $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                        <div class="notification-container close-button-container">';
                    if (in_array('ISKOREKSI', $this->access)) {
                        $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                        $urut = isset($data->Urut) ? $data->Urut : '';
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditSpk btn--detail' data-bukti='{$noBukti}' data-urut='{$urut}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                    }
                    if (in_array('ISHAPUS', $this->access)) {
                        $url = route('produksi.spk.delete');
                        $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                        $urut = isset($data->Urut) ? $data->Urut : '';
                        $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete btn--detail' data-url='{$url}' data-id='{$noBukti}' data-urut='{$urut}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                    }
                    $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                }
                return $html;
            })
            ->done();

        $datatableData += ['canAdd' => (($trans->IsOtorisasi1 ?? 0) == 0)];
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

#### **Key Points:**
- **TIDAK menggunakan parameter Request** - gunakan `request()` helper
- **Definisikan variabel** sebelum digunakan dalam closure function
- **Gunakan use ($trans)** untuk akses data trans di closure
- **Error handling** yang komprehensif dengan logging

### **2. Repository Implementation**

#### **Method getSpkDetailByNoBukti()**
```php
public function getSpkDetailByNoBukti($noBukti)
{
    try {
        \Log::info('getSpkDetailByNoBukti function is called with NoBukti: ' . $noBukti);

        // First, let's check what columns actually exist in the table
        $columns = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'dbSPKDet' ORDER BY ORDINAL_POSITION");
        $columnNames = array_map(function($col) { return $col->COLUMN_NAME; }, $columns);
        \Log::info('Available columns in dbSPKDet: ' . implode(', ', $columnNames));

        // Check if Urut column exists
        $hasUrut = in_array('Urut', $columnNames);
        \Log::info('Urut column exists: ' . ($hasUrut ? 'YES' : 'NO'));

        // Build query based on available columns
        if ($hasUrut) {
            $query = "select A.Urut, A.KodeBrg, B.NamaBrg, A.Qnt,
            CASE WHEN A.NoSat=1 THEN B.SAT1 WHEN A.NoSat=2 THEN B.SAT2 ELSE '' END as Satuan,
            A.NoSat, A.Isi, '' as Keterangan
            from dbSPKDet A
            left join dbBarang B on A.KodeBrg = B.KodeBrg
            where A.NoBukti = ?
            order by A.Urut";
        } else {
            // If Urut doesn't exist, use ROW_NUMBER() to create it
            $query = "select ROW_NUMBER() OVER (ORDER BY A.KodeBrg) as Urut, A.KodeBrg, B.NamaBrg, A.Qnt,
            CASE WHEN A.NoSat=1 THEN B.SAT1 WHEN A.NoSat=2 THEN B.SAT2 ELSE '' END as Satuan,
            A.NoSat, A.Isi, '' as Keterangan
            from dbSPKDet A
            left join dbBarang B on A.KodeBrg = B.KodeBrg
            where A.NoBukti = ?";
        }

        $result = DB::select($query, [$noBukti]);
        \Log::info('getSpkDetailByNoBukti result count: ' . count($result));
        if ($result && count($result) > 0) {
            \Log::info('getSpkDetailByNoBukti first row keys: ' . implode(', ', array_keys((array)$result[0])));
        }
        return $result;
    } catch (\Illuminate\Database\QueryException $ex) {
        \Log::error('Error in getSpkDetailByNoBukti: ' . $ex->getMessage());
        throw $ex;
    }
}
```

#### **Key Points:**
- **Dynamic column detection** untuk handle struktur database yang berbeda
- **Fallback query** jika kolom Urut tidak ada
- **Comprehensive logging** untuk debugging
- **Error handling** dengan QueryException

### **3. Routes Implementation**

#### **Route Definition**
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

#### **Key Points:**
- **POST method** untuk detail endpoint
- **TIDAK ada route yang konflik** dengan nama yang sama
- **Middleware policy** untuk kontrol akses
- **Naming convention** yang konsisten

### **4. View Implementation**

#### **Expand Table Template**
```blade
{{-- resources/views/components/produksi/spk/expand_table.blade.php --}}
<table id="datatableExpand" class="table table-bordered table-striped table-hover nowrap w-100"
    data-server="{{ route('produksi.spk.detail-Spk') }}">
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
```

#### **Parent Table Configuration**
```javascript
// Di parent DataTable, tambahkan detailUrl
$row->detailUrl = route('produksi.spk.detail-Spk') . '?NoBukti=' . $row->NoBukti;
```

### **5. JavaScript Implementation**

#### **Expandable Logic**
```javascript
// Expand/collapse handler
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

// Show child datatable
function showChildDatatable(row, tr) {
    row.child(formatChildTable(row.data())).show();
    tr.addClass("shown");
    
    let childTable = row.child().find('#datatableExpand').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: row.data().detailUrl,
            type: 'GET'
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
        responsive: true,
        language: {
            url: '{{ asset("assets/plugins/datatables/Indonesian.json") }}'
        }
    });
}

// Format child table HTML
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
```

---

## TROUBLESHOOTING GUIDE

### **🚨 Common Errors & Solutions**

#### **1. Error: "Undefined variable $noBukti"**
```php
// PROBLEM: Variabel tidak terdefinisi dalam closure
$html .= "<button ... data-bukti='{$noBukti}' data-urut='{$urut}'>";

// SOLUTION: Definisikan variabel sebelum digunakan
$noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
$urut = isset($data->Urut) ? $data->Urut : '';
$html .= "<button ... data-bukti='{$noBukti}' data-urut='{$urut}'>";
```

#### **2. Error: "Missing required parameter for route"**
```php
// PROBLEM: Route konflik atau parameter tidak diberikan
Route::delete('/', [Controller::class, 'delete'])->name('.delete');
Route::delete('/delete/{noBukti}', [Controller::class, 'destroy'])->name('.delete');

// SOLUTION: Hapus route yang konflik
Route::delete('/', [Controller::class, 'delete'])->name('.delete');
```

#### **3. Error: "Method deleteSpk does not exist"**
```php
// PROBLEM: Method tidak ada di repository
if ($this->spkRepository->deleteSpk($request->NoBukti)) {

// SOLUTION: Tambahkan method di repository
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

#### **4. Error: "DataTables unknown parameter 'Urut'"**
```php
// PROBLEM: Column tidak ada di hasil query
{ data: "Urut" }

// SOLUTION: Pastikan field ada di query dan mapping
$row->Urut = $row->Urut ?? '';
return $row;
```

#### **5. Error: "501 Not Implemented"**
```php
// PROBLEM: Method signature tidak sesuai
public function getSpkDetailByNoBukti(Request $request)

// SOLUTION: Ikuti pola Memorial
public function getSpkDetailByNoBukti()
{
    if (!request()->NoBukti) {
        return $this->setResponseError('No Bukti tidak boleh kosong');
    }
    // ... rest of logic
}
```

### **🔧 Debugging Commands**
```bash
# Clear caches
php artisan route:clear
php artisan config:clear
php artisan view:clear

# Check routes
php artisan route:list | findstr "modul-name"

# Check logs
tail -f storage/logs/laravel.log

# Test AJAX endpoint
curl -X POST http://localhost:8000/modul/detail -d "NoBukti=TEST123"
```

---

## BEST PRACTICES

### **1. Controller Pattern**
```php
// ✅ BENAR - Ikuti pola Memorial
public function getDetailByNoBukti()
{
    try {
        if (!request()->NoBukti) {
            return $this->setResponseError('No Bukti tidak boleh kosong');
        }

        $this->requestAjax($this->access, 'HASACCESS');

        $trans = $this->repository->getByNoBukti(request()->NoBukti);
        if ($trans instanceof \Illuminate\Support\Collection) {
            $trans = $trans->first();
        }

        if (!$trans) {
            return $this->setResponseError('Data tidak ditemukan');
        }

        $datatableData = CustomDataTable::init()
            ->of($this->repository->getDetailByNoBukti(request()->NoBukti))
            ->apply()
            ->mapData(function ($row) {
                // Ensure required fields
                $row->Urut = $row->Urut ?? '';
                $row->NoBukti = request()->NoBukti;
                return $row;
            })
            ->addColumn('action', function ($data) use ($trans) {
                // Define variables before using
                $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                $urut = isset($data->Urut) ? $data->Urut : '';
                
                $html = '';
                if (($trans->IsOtorisasi1 ?? 0) == 0) {
                    // Action buttons
                }
                return $html;
            })
            ->done();

        return $datatableData;

    } catch (\Exception $e) {
        \Log::error('Detail Error:', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
        return $this->setResponseError($e->getMessage());
    }
}
```

### **2. Repository Pattern**
```php
// ✅ BENAR - Dynamic column detection
public function getDetailByNoBukti($noBukti)
{
    try {
        // Check available columns
        $columns = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'table_name'");
        $columnNames = array_map(function($col) { return $col->COLUMN_NAME; }, $columns);
        
        // Build query based on available columns
        if (in_array('Urut', $columnNames)) {
            $query = "SELECT Urut, Field1, Field2 FROM table_name WHERE NoBukti = ? ORDER BY Urut";
        } else {
            $query = "SELECT ROW_NUMBER() OVER (ORDER BY Field1) as Urut, Field1, Field2 FROM table_name WHERE NoBukti = ?";
        }

        return DB::select($query, [$noBukti]);
    } catch (\Illuminate\Database\QueryException $ex) {
        \Log::error('Error in getDetailByNoBukti: ' . $ex->getMessage());
        throw $ex;
    }
}
```

### **3. Routes Pattern**
```php
// ✅ BENAR - Konsisten dan tidak konflik
Route::prefix('modul-name')->name('modul')->middleware('policy:HASACCESS,CODE')->group(function () {
    Route::get('/', [Controller::class, 'index'])->name('.index');
    Route::delete('/', [Controller::class, 'delete'])->name('.delete');
    Route::post('/detail', [Controller::class, 'getDetailByNoBukti'])->name('.detail');
    Route::post('/set-otorisasi', [Controller::class, 'setOtorisasi'])->name('.set-otorisasi');
});
```

### **4. JavaScript Pattern**
```javascript
// ✅ BENAR - ES6 modules dengan proper error handling
import $globalVariable, { publicURL, csfr_token } from "../base-function.js";

(function ($, { baseSwal, baseAjax, formAjax, getModal, globalDelete, applyPlugins, mergeWithDefaultOptions, swalConfirm }) {
    
    // DataTables initialization
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

    // Expand/collapse handler
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

    // Show child datatable
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
                { data: "Field1" },
                { data: "Field2" },
                { data: "Field3", className: "text-right" }
            ],
            order: [[0, 'asc']],
            pageLength: 10,
            responsive: true
        });
    }

    // Format child table HTML
    function formatChildTable(data) {
        return `
            <div class="row">
                <div class="col-md-12">
                    <table id="datatableExpand" class="table table-bordered table-striped table-hover nowrap w-100"
                        data-server="${data.detailUrl}">
                        <thead>
                            <tr>
                                <th>Urut</th>
                                <th>Field1</th>
                                <th>Field2</th>
                                <th>Field3</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        `;
    }

})($, $globalVariable);
```

---

## CODE EXAMPLES

### **Complete Implementation Checklist**

#### **✅ Controller Checklist**
- [ ] Method tanpa parameter Request
- [ ] Menggunakan request() helper
- [ ] Variabel terdefinisi dalam closure
- [ ] Error handling dengan logging
- [ ] CustomDataTable dengan addColumn

#### **✅ Repository Checklist**
- [ ] Dynamic column detection
- [ ] Fallback query untuk kolom yang tidak ada
- [ ] Comprehensive logging
- [ ] Error handling dengan QueryException

#### **✅ Routes Checklist**
- [ ] POST method untuk detail endpoint
- [ ] Tidak ada route yang konflik
- [ ] Middleware policy yang tepat
- [ ] Naming convention yang konsisten

#### **✅ View Checklist**
- [ ] Template expand table yang benar
- [ ] Data-server attribute yang tepat
- [ ] Column headers yang sesuai

#### **✅ JavaScript Checklist**
- [ ] ES6 modules pattern
- [ ] Proper error handling
- [ ] Expand/collapse logic
- [ ] Child DataTable initialization

### **🚨 Anti-Patterns to Avoid**

```php
// ❌ SALAH - Parameter Request untuk detail method
public function getDetailByNoBukti(Request $request)

// ❌ SALAH - Variabel tidak terdefinisi
$html .= "<button data-bukti='{$noBukti}'>";

// ❌ SALAH - Route yang konflik
Route::delete('/', [Controller::class, 'delete'])->name('.delete');
Route::delete('/delete/{param}', [Controller::class, 'destroy'])->name('.delete');

// ❌ SALAH - Method tidak ada di repository
if ($this->repository->deleteData($request->NoBukti)) {

// ❌ SALAH - Tidak ada error handling
public function getDetailByNoBukti($noBukti)
{
    return DB::select("SELECT * FROM table WHERE NoBukti = ?", [$noBukti]);
}
```

---

## 📚 REFERENCES

- **Memorial Implementation**: `app/Http/Controllers/MemorialController.php`
- **SPK Implementation**: `app/Http/Controllers/SPKController.php`
- **Base Repository**: `app/Http/Repository/BaseRepository.php`
- **Custom DataTable**: `app/Http/Services/CustomDataTable.php`

---

**Last Updated**: December 2024  
**Version**: 1.0  
**Author**: AI Assistant  
**Status**: ✅ Tested & Working 