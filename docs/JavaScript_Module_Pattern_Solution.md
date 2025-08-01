# JavaScript Module Pattern Solution - Memorial vs SPK

## 🎯 **Problem Identification**

### **Issue**: SPK AJAX Request Tidak Berjalan
- **Symptom**: DataTables tidak load, JavaScript errors, AJAX requests gagal
- **Root Cause**: Perbedaan implementasi JavaScript modules antara Memorial (benar) dan SPK (salah)

### **Analysis**:
| **Memorial (✅ Benar)** | **SPK (❌ Salah)** |
|---|---|
| ES6 modules dengan import | Traditional JavaScript |
| IIFE pattern | Manual initialization |
| Destructure dari $globalVariable | Manual dependency checking |
| type="module" di view | Tidak ada type="module" |

## 🔍 **Technical Analysis**

### **Memorial Pattern (Working)**:
```javascript
// memorial.js
import $globalVariable, { publicURL, csfr_token } from "../base-function.js";

(function (
    $,
    {
        baseSwal,
        baseAjax,
        formAjax,
        getModal,
        globalDelete,
        applyPlugins,
        mergeWithDefaultOptions,
        swalConfirm,
    }
) {
    var datatableMain = $("#datatableMain").DataTable({
        ...mergeWithDefaultOptions({
            // Configuration
        })
    });
})($, $globalVariable);
```

### **SPK Pattern (Original - Not Working)**:
```javascript
// spk.js - WRONG APPROACH
var options = {};
var spkInitialized = false;

$(document).ready(function() {
    function checkDependencies() {
        if (typeof $globalVariable === 'undefined') {
            setTimeout(checkDependencies, 100);
            return;
        }
        initializeSPK();
    }
    checkDependencies();
});

function initializeSPK() {
    // Manual initialization
}
```

## ✅ **Solution Implementation**

### **Step 1: Update View to Use ES6 Modules**
```blade
<!-- resources/views/spk/index.blade.php -->
@push('js')
    <script src="{{ asset('assets/js/produksi/spk.js') }}" type="module"></script>
@endpush
```

### **Step 2: Rewrite JavaScript Following Memorial Pattern**
```javascript
// public/assets/js/produksi/spk.js
import $globalVariable, { publicURL, csfr_token } from "../base-function.js";

(function (
    $,
    {
        baseSwal,
        baseAjax,
        formAjax,
        getModal,
        globalDelete,
        applyPlugins,
        mergeWithDefaultOptions,
        swalConfirm,
    }
) {
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
            $defaultOpt: {
                buttons: [
                    { $keyButton: "tambah", className: "btn-module" },
                    "colvis", "refresh",
                    { $keyButton: "excel-pdf", className: "btn-module" },
                    "flexiblefixed",
                    { $keyButton: "excel", exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
                    { $keyButton: "pdf", exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } }
                ]
            },
            columns: [
                { data: null, orderable: false, searchable: false, defaultContent: "", className: "dt-control" },
                { data: "NoBukti" },
                { data: "Tanggal" },
                { data: "NoSO" },
                { data: "KodeBrg" },
                { data: "NamaBrg" },
                { data: "Qnt", className: "text-right" },
                { data: "Satuan" },
                { data: "IsOtorisasi1Html" },
                { data: "OtoUser1" },
                { data: "TglOto1" },
                { data: "action", orderable: false, searchable: false, className: "text-center parentBtnRow" }
            ],
            fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData.indikatorExpand === false) {
                    $(nRow).find("td.dt-control").addClass("indicator-white");
                }
                if (aData.IsOtorisasi1 == 1) {
                    $(nRow).addClass("yellowClass");
                }
            }
        })
    });

    // Event handlers
    $(document).on("click", "#datatableMain > tbody td.dt-control", function () {
        var tr = $(this).closest("tr");
        var row = datatableMain.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass("shown");
        } else {
            row.child(formatChildTable(row.data())).show();
            tr.addClass("shown");
        }
    });

    $(document).on("change", 'input[name="IsOtorisasi1"]', function (e) {
        e.preventDefault();
        let tr = $(this).closest("tr");
        const data = datatableMain.row(tr).data();
        swalConfirm(
            "Apakah anda yakin akan mengubah status otorisasi?",
            function confirmed() {
                baseAjax({
                    url: publicURL + "/produksi/transaksi-spk/set-otorisasi",
                    type: "POST",
                    param: {
                        NoBukti: data.NoBukti,
                        otoLevel: $(e.target).attr("name"),
                        status: $(e.target).is(":checked") ? 1 : 0,
                    },
                    successCallback: function (res) {
                        datatableMain.ajax.reload();
                    },
                    errorCallback: function (xhr) {
                        $(e.target).prop("checked", !$(e.target).is(":checked"));
                    },
                });
            },
            function dismissed() {
                $(e.target).prop("checked", !$(e.target).is(":checked"));
            }
        );
    });

    $(document).on("click", "#datatableMain_wrapper .buttons-add.btn-module", function (e) {
        e.preventDefault();
        var options = {
            data: {
                resource: "components.produksi.spk.modal-insert",
                modalId: "modalAddSPK",
                formId: "formAddSPK",
                modalWidth: "lg",
                url: publicURL + "/produksi/transaksi-spk/store",
                fnData: {
                    class: "\\App\\Http\\Controllers\\SPKController",
                    function: "getDataByNoBukti",
                    params: [$(this).data("bukti") || null],
                },
                checkPermission: true,
                codeAccess: "05001",
                access: "ISTAMBAH",
            },
            callback: function (response, modal) {
                modalAddEditSPK(response, modal);
            }
        };
        getModal(options);
    });

    function formatChildTable(data) {
        return `
            <div class="row">
                <div class="col-md-12">
                    <table id="datatableExpand" class="table table-bordered table-striped table-hover nowrap w-100">
                        <thead>
                            <tr>
                                <th>Urut</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Quantity</th>
                                <th>Satuan</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        `;
    }

    function modalAddEditSPK(response, modal) {
        console.log('modalAddEditSPK called');
        applyPlugins(modal);
    }

})($, $globalVariable);
```

### **Step 3: Ensure Complete Routes**
```php
// routes/web.php
Route::prefix('produksi')->name('produksi.')->group(function () {
    Route::prefix('transaksi-spk')->name('spk')->middleware('policy:HASACCESS,05001')->group(function () {
        Route::get('/', [SPKController::class, 'index'])->name('.index');
        Route::post('/store', [SPKController::class, 'store'])->name('.store');
        Route::get('/detail/{noBukti}', [SPKController::class, 'getSpkDetailByNoBukti'])->name('.detail-Spk');
        Route::post('/set-otorisasi', [SPKController::class, 'setOtorisasi'])->name('.set-otorisasi');
        Route::delete('/delete/{noBukti}', [SPKController::class, 'destroy'])->name('.delete');
    });
});
```

## 🎯 **Universal Template for New Modules**

### **JavaScript Template**:
```javascript
import $globalVariable, { publicURL, csfr_token } from "../base-function.js";

(function (
    $,
    {
        baseSwal,
        baseAjax,
        formAjax,
        getModal,
        globalDelete,
        applyPlugins,
        mergeWithDefaultOptions,
        swalConfirm,
    }
) {
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
            $defaultOpt: {
                buttons: [
                    { $keyButton: "tambah", className: "btn-module" },
                    "colvis", "refresh",
                    { $keyButton: "excel-pdf", className: "btn-module" },
                    "flexiblefixed",
                    { $keyButton: "excel", exportOptions: { columns: [1,2,3,4,5,6,7] } },
                    { $keyButton: "pdf", exportOptions: { columns: [1,2,3,4,5,6,7] } }
                ]
            },
            columns: [
                { data: null, orderable: false, searchable: false, defaultContent: "", className: "dt-control" },
                { data: "PrimaryKey" },
                { data: "Tanggal" },
                { data: "Keterangan" },
                { data: "Status" },
                { data: "IsOtorisasi1Html" },
                { data: "OtoUser1" },
                { data: "TglOto1" },
                { data: "action", orderable: false, searchable: false, className: "text-center parentBtnRow" }
            ],
            fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData.indikatorExpand === false) {
                    $(nRow).find("td.dt-control").addClass("indicator-white");
                }
                if (aData.IsOtorisasi1 == 1) {
                    $(nRow).addClass("yellowClass");
                }
            }
        })
    });

    // Standard event handlers
    $(document).on("click", "#datatableMain > tbody td.dt-control", function () {
        // Expand/collapse logic
    });

    $(document).on("change", 'input[name="IsOtorisasi1"]', function (e) {
        // Otorisasi logic using swalConfirm
    });

    $(document).on("click", "#datatableMain_wrapper .buttons-add.btn-module", function (e) {
        // Modal logic using getModal
    });

})($, $globalVariable);
```

### **View Template**:
```blade
@extends('layouts.app', ['title' => 'Module Name'])

@push('css-plugins')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-fixedcolumns/css/fixedColumns.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush

@section('body')
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header">
                    <h3 class="card-title">Data Module</h3>
                </div>
                <div class="card-body">
                    <table id="datatableMain" class="table table-bordered table-striped table-hover nowrap w-100"
                        data-server="{{ route('module.index') }}">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Primary Key</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                                <th>Authorized 1</th>
                                <th>Authorized User 1</th>
                                <th>Authorized Date 1</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js-plugins')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-fixedcolumns/js/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-maskmoney/jquery.maskMoney.js') }}"></script>
@endpush

@push('js')
    <script src="{{ asset('assets/js/module/file.js') }}" type="module"></script>
@endpush
```

## 🚨 **Critical Rules**

### **DO's (✅)**:
- ✅ **SELALU gunakan ES6 modules** untuk modul baru
- ✅ **SELALU import dari base-function.js** 
- ✅ **SELALU gunakan IIFE pattern** seperti Memorial
- ✅ **SELALU destructure functions** dari $globalVariable
- ✅ **SELALU gunakan type="module"** di view
- ✅ **SELALU ikuti pola Memorial** yang sudah terbukti bekerja

### **DON'Ts (❌)**:
- ❌ **JANGAN gunakan traditional JavaScript** untuk modul baru
- ❌ **JANGAN manual dependency checking** 
- ❌ **JANGAN custom initialization logic**
- ❌ **JANGAN ubah file yang sudah stabil** (helper.js, base-function.js, memorial.js)

## 🎯 **Results**

### **Before (SPK Traditional)**:
- ❌ JavaScript errors
- ❌ DataTables tidak initialize
- ❌ AJAX requests gagal
- ❌ Dependencies tidak tersedia

### **After (SPK ES6 Modules)**:
- ✅ JavaScript berjalan lancar
- ✅ DataTables initialize dengan benar
- ✅ AJAX requests berhasil
- ✅ Semua dependencies tersedia

## 📚 **References**

- **Working Example**: `public/assets/js/accounting/memorial.js`
- **Base Functions**: `public/assets/js/base-function.js`
- **Fixed Example**: `public/assets/js/produksi/spk.js`
- **View Example**: `resources/views/spk/index.blade.php`

## 🔧 **Troubleshooting**

### **Common Issues**:
1. **"$globalVariable is not defined"** → Pastikan menggunakan ES6 modules dengan import
2. **"mergeWithDefaultOptions is not a function"** → Pastikan destructure dari $globalVariable
3. **DataTables tidak initialize** → Pastikan menggunakan IIFE pattern
4. **AJAX requests gagal** → Pastikan routes lengkap dan controller methods ada

### **Debug Commands**:
```bash
# Check routes
php artisan route:list --name=module

# Check JavaScript syntax
# Open browser console and check for errors

# Check Laravel logs
tail -f storage/logs/laravel.log
```

## 🎯 **Conclusion**

**Memorial pattern adalah GOLD STANDARD** untuk semua modul JavaScript baru. Menggunakan ES6 modules dengan import dari base-function.js memastikan semua dependencies tersedia dan DataTables berjalan dengan lancar. SPK berhasil diperbaiki dengan mengikuti pola Memorial yang sama.

**Key Takeaway**: Jangan reinvent the wheel. Ikuti pola yang sudah terbukti bekerja (Memorial) untuk semua modul baru. 