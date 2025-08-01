# DOKUMENTASI SPK DATATABLE EXPAND FUNCTIONALITY

## Status: ✅ IMPLEMENTED - Struktur Updated dengan Paging Lengkap

## Struktur yang Diinginkan User
```
Main SPK (NoBukti, Tanggal, NoSO, KodeBrg utama)
├── Level 1: SPK Detail (klik ⊞ biru)
│      tampil semua level 1 beserta paging nya
└── Level 2: Jadwal Produksi (klik ◐ hijau) - SEMUA jadwal produksi untuk NoBukti tersebut   
       tampil semua level 2 beserta paging nya  
```

## Struktur Saat Ini (Updated)
- ✅ Level 1: ⊞ biru dengan paging lengkap (5, 10, 25, 50 items per page)
- ✅ Level 2: ◐ hijau dengan paging lengkap (5, 10, 25, 50 items per page)
- ✅ Independent expand functionality
- ✅ Indonesian language support for pagination

## Implementasi Teknis

### 1. Database Structure
- **Main SPK**: Table `dbSPK` - Data SPK utama
- **Level 1**: Table `dbSPKDet` - Detail SPK (Urut, KodeBrg, NamaBrg, Qnt, Satuan)
- **Level 2**: Table `DBJADWALPRD` - Jadwal Produksi (KodePrs, KodeMesin, Tanggal, JamAwal, JamAkhir, QntSPK, TarifMesin, TarifTenaker)

### 2. Frontend Components

#### HTML Structure
**File**: `resources/views/produksi/spk/index.blade.php`
```html
<table id="datatableMain" class="table table-bordered table-striped table-hover nowrap w-100">
    <thead>
        <tr>
            <th>⊞ Detail</th>     <!-- Kolom expand Level 1 (biru) -->
            <th>◐ Jadwal</th>     <!-- Kolom expand Level 2 (hijau) -->
            <th>No Bukti</th>
            <th>Tanggal</th>
            <th>No SO</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Quantity</th>
            <th>Satuan</th>
            <th>Authorized 1</th>
            <th>Authorized User 1</th>
            <th>Authorized Date 1</th>
            <th>Action</th>
        </tr>
    </thead>
</table>
```

#### JavaScript Configuration
**File**: `public/assets/js/produksi/spk/spk.js`

**Columns Definition:**
```javascript
columns: [
    {
        data: null,
        orderable: false,
        searchable: false,
        defaultContent: "",
        className: "dt-control",  // Level 1 expand (⊞ biru)
    },
    {
        data: null,
        orderable: false,
        searchable: false,
        defaultContent: "",
        className: "dt-control-level2-main",  // Level 2 expand (◐ hijau)
    },
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
    {
        data: "action",
        orderable: false,
        searchable: false,
        className: "text-center parentBtnRow",
    },
],
```

#### CSS Styling
**File**: `public/assets/css/spk.css`

**Level 1 Expand Button (Biru):**
```css
table#datatableMain td.dt-control {
    background-color: #f8f9fa !important;
    cursor: pointer !important;
    border: 2px solid #007bff !important;
    /* Symbol: ⊞ biru */
}
```

**Level 2 Expand Button (Hijau):**
```css
table#datatableMain td.dt-control-level2-main {
    background-color: #d4edda !important;
    cursor: pointer !important;
    border: 2px solid #28a745 !important;
    /* Symbol: ◐ hijau */
}
```

### 3. Backend Components

#### Routes
**File**: `routes/web.php`
```php
Route::prefix('transaksi-spk')->name('spk')->group(function () {
    Route::get('/', [SPKController::class, 'index'])->name('.index');
    Route::post('/detail', [SPKController::class, 'getSpkDetailByNoBukti'])->name('.detail-Spk');
    Route::get('/detail-level2-all', [SPKController::class, 'getSpkDetailLevel2AllByNoBukti'])->name('.detail-level2-all');
});
```

#### Controller Methods
**File**: `app/Http/Controllers/SPKController.php`

**Main SPK Data:**
- `index()` - Menampilkan data SPK utama dengan `indikatorExpand = true`

**Level 1 Data:**
- `getSpkDetailByNoBukti()` - Menampilkan SPK Detail berdasarkan NoBukti

**Level 2 Data:**
- `getSpkDetailLevel2AllByNoBukti()` - Menampilkan semua Jadwal Produksi berdasarkan NoBukti

#### Repository Methods
**File**: `app/Http/Repository/SPKRepository.php`

**Level 1:**
- `getSpkDetailByNoBukti($noBukti)` - Query ke table `dbSPKDet`

**Level 2:**
- `getSpkDetailLevel2AllByNoBukti($noBukti)` - Query ke table `DBJADWALPRD`

### 4. Event Handlers

#### Level 1 Expand Handler
```javascript
$(document).on("click", "#datatableMain > tbody td.dt-control", function () {
    // Expand/collapse SPK Detail
    showChildDatatable(row, tr);
});
```

#### Level 2 Expand Handler
```javascript
$(document).on("click", "#datatableMain > tbody td.dt-control-level2-main", function () {
    // Expand/collapse Jadwal Produksi
    showLevel2MainDetail(row, tr);
});
```

### 5. Paging Configuration

#### Level 1 Paging (SPK Detail)
```javascript
pageLength: 10,
lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
searching: true,
info: true,
paging: true,
language: {
    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ SPK Detail",
    lengthMenu: "Tampilkan _MENU_ SPK Detail",
    search: "Cari SPK Detail:",
    paginate: {
        first: "Pertama",
        last: "Terakhir", 
        next: "Selanjutnya",
        previous: "Sebelumnya"
    }
}
```

#### Level 2 Paging (Jadwal Produksi)
```javascript
pageLength: 10,
lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
searching: true,
info: true,
paging: true,
language: {
    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ Jadwal Produksi",
    lengthMenu: "Tampilkan _MENU_ Jadwal Produksi",
    search: "Cari Jadwal Produksi:",
    emptyTable: "Tidak ada jadwal produksi untuk SPK ini"
}
```

### 6. Data Flow

#### Level 1 Flow:
1. User klik ⊞ biru di Main SPK
2. AJAX call ke `/produksi/transaksi-spk/detail` dengan `NoBukti`
3. Controller `getSpkDetailByNoBukti()` dipanggil
4. Repository query `dbSPKDet` table
5. Return data: `Urut`, `KodeBrg`, `NamaBrg`, `Qnt`, `Satuan`
6. Tampilkan dalam nested DataTable

#### Level 2 Flow:
1. User klik ◐ hijau di Main SPK
2. AJAX call ke `/produksi/transaksi-spk/detail-level2-all` dengan `NoBukti`
3. Controller `getSpkDetailLevel2AllByNoBukti()` dipanggil
4. Repository query `DBJADWALPRD` table
5. Return data: `KodePrs`, `KodeMesin`, `Tanggal`, `JamAwal`, `JamAkhir`, `QntSPK`, `TarifMesin`, `TarifTenaker`
6. Tampilkan dalam table terpisah dengan paging lengkap

## Features Implemented

### ✅ Completed Features
- **Independent Expand System**: Level 1 dan Level 2 bekerja secara independen
- **Visual Distinction**: ⊞ biru untuk Level 1, ◐ hijau untuk Level 2
- **Full Paging Support**: Kedua level memiliki paging lengkap dengan options 5, 10, 25, 50
- **Indonesian Language**: Semua text pagination dalam bahasa Indonesia
- **Search Functionality**: Dapat mencari dalam SPK Detail dan Jadwal Produksi
- **Responsive Design**: Tables responsive di berbagai screen size
- **Loading Indicators**: Loading state saat data sedang dimuat

### 🎯 Key Improvements
- **Enhanced Paging**: Dropdown length menu dengan multiple options
- **Better UX**: Clear visual indication dengan simbol berbeda
- **Data Formatting**: Right-aligned untuk kolom numerik
- **Error Handling**: Proper error messages dan fallbacks

## Rules dan Guidelines

### Rule 1: Independensi Level
- Level 1 dan Level 2 harus independen
- Tidak ada nested expand di dalam Level 1
- User bisa membuka Level 1 saja, Level 2 saja, atau keduanya

### Rule 2: Visual Distinction
- Level 1 expand: ⊞ biru (dt-control)
- Level 2 expand: ◐ hijau (dt-control-level2-main)
- Hover effects harus jelas
- Collapse state: ⊟ dengan warna berbeda

### Rule 3: Data Integrity
- Level 1: Data dari `dbSPKDet` berdasarkan `NoBukti`
- Level 2: Data dari `DBJADWALPRD` berdasarkan `NoBukti`
- Semua data harus terformat dengan benar (tanggal, angka, etc.)

### Rule 4: Performance
- Lazy loading untuk nested tables
- Efficient AJAX calls
- Proper error handling
- Loading indicators

### Rule 5: Responsive Design
- Table harus responsive di berbagai screen size
- Expand buttons harus accessible di mobile
- Proper column sizing

## Debugging dan Logging

### JavaScript Console Logs
```javascript
console.log('Level 1 Expand button clicked!');
console.log('Level 2 Main Expand button clicked!');
console.log('AJAX response:', json);
```

### Laravel Logs
```php
\Log::info('SPK Detail Method Called', ['NoBukti' => $noBukti]);
\Log::info('SPK Detail Level 2 All Error:', ['message' => $e->getMessage()]);
```

## File Structure Summary
```
resources/views/produksi/spk/
├── index.blade.php (Main table structure)
└── components/produksi/spk/
    └── expand_table.blade.php (Level 1 expand template)

public/assets/
├── js/produksi/spk/spk.js (Main JavaScript logic)
└── css/spk.css (Styling for expand buttons)

app/Http/
├── Controllers/SPKController.php (API endpoints)
└── Repository/SPKRepository.php (Database queries)

routes/web.php (Route definitions)
```

## Next Steps
1. ✅ Implementasi struktur sesuai permintaan user telah selesai
2. ✅ Paging lengkap untuk kedua level telah ditambahkan  
3. ✅ Visual distinction dengan simbol berbeda telah diupdate
4. 🔄 Test functionality secara menyeluruh setelah deployment
5. 📝 Commit perubahan ke repository
