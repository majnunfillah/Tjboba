# DataTables Column Management Guide

## 📋 **Panduan Menambah dan Mengurangi Kolom DataTables**

### 🎯 **Overview**
Dokumentasi ini berdasarkan pengalaman praktis menghilangkan kolom otorisasi dari Outstanding SO table di modul SPK. Proses ini melibatkan 6 langkah kritis yang harus dilakukan secara berurutan.

---

## 🔄 **Kronologi Perubahan Kolom (Case Study: Outstanding SO)**

### **📊 Kondisi Awal (Sebelum Perubahan):**
- **HTML Headers**: 11 kolom (termasuk otorisasi, user otorisasi, tgl otorisasi, action)
- **JavaScript Config**: 11 kolom DataTables configuration
- **Repository Query**: Select statement dengan kolom otorisasi
- **Repository addColumn**: Processing untuk kolom otorisasi dan action
- **Export Options**: Export 11 kolom
- **fnRowCallback**: Logic untuk styling berdasarkan otorisasi

### **📊 Kondisi Akhir (Setelah Perubahan):**
- **HTML Headers**: 7 kolom (hanya data utama)
- **JavaScript Config**: 7 kolom DataTables configuration
- **Repository Query**: Select statement tanpa kolom otorisasi
- **Repository addColumn**: Processing hanya untuk kolom yang diperlukan
- **Export Options**: Export 7 kolom
- **fnRowCallback**: Logic tanpa referensi otorisasi

---

## 🚀 **Langkah-Langkah Menghilangkan Kolom**

### **Step 1: Update HTML Table Headers**
**File**: `resources/views/module/index.blade.php`

**Sebelum:**
```html
<thead>
    <tr>
        <th></th>
        <th>No Bukti SO</th>
        <th>Tanggal</th>
        <th>Keterangan</th>
        <th>Qnt SO</th>
        <th>Qnt SPK</th>
        <th>Saldo</th>
        <th>Otorisasi</th>        <!-- HAPUS -->
        <th>User Otorisasi</th>   <!-- HAPUS -->
        <th>Tgl Otorisasi</th>    <!-- HAPUS -->
        <th>Action</th>           <!-- HAPUS -->
    </tr>
</thead>
```

**Sesudah:**
```html
<thead>
    <tr>
        <th></th>
        <th>No Bukti SO</th>
        <th>Tanggal</th>
        <th>Keterangan</th>
        <th>Qnt SO</th>
        <th>Qnt SPK</th>
        <th>Saldo</th>
    </tr>
</thead>
```

### **Step 2: Update JavaScript DataTables Columns**
**File**: `public/assets/js/module/file.js`

**Sebelum:**
```javascript
columns: [
    { data: null, orderable: false, searchable: false, defaultContent: "", className: "dt-control" },
    { data: "NoBukti" },
    { data: "Tanggal" },
    { data: "NamaBrg" },
    { data: "QntSO", className: "text-right" },
    { data: "QntSPK", className: "text-right" },
    { data: "Saldo", className: "text-right" },
    { data: "IsOtorisasi1Html" },                    // HAPUS
    { data: "OtoUser1", defaultContent: '-' },       // HAPUS
    { data: "TglOto1" },                             // HAPUS
    { data: "action", orderable: false, searchable: false, className: "text-center parentBtnRow" } // HAPUS
]
```

**Sesudah:**
```javascript
columns: [
    { data: null, orderable: false, searchable: false, defaultContent: "", className: "dt-control" },
    { data: "NoBukti" },
    { data: "Tanggal" },
    { data: "NamaBrg" },
    { data: "QntSO", className: "text-right" },
    { data: "QntSPK", className: "text-right" },
    { data: "Saldo", className: "text-right" }
]
```

### **Step 3: Update Repository Query**
**File**: `app/Http/Repository/ModuleRepository.php`

**Sebelum:**
```php
->select([
    'A.NoBukti',
    'A.Urut',
    'A.KodeBrg',
    'C.NamaBrg',
    'B.Tanggal',
    DB::raw("CASE WHEN A.NoSat=1 THEN C.SAT1 WHEN A.NoSat=2 THEN C.SAT2 ELSE '' END as Satuan"),
    'A.Qnt as QntSO',
    DB::raw('COALESCE(SPK.QntSPK, 0) as QntSPK'),
    DB::raw('(A.Qnt - COALESCE(SPK.QntSPK, 0)) as Saldo'),
    DB::raw('CASE WHEN B.IsOtorisasi1=1 THEN 1 ELSE 0 END as IsOtorisasi1'), // HAPUS
    'B.OtoUser1',  // HAPUS
    'B.TglOto1'    // HAPUS
])
```

**Sesudah:**
```php
->select([
    'A.NoBukti',
    'A.Urut',
    'A.KodeBrg',
    'C.NamaBrg',
    'B.Tanggal',
    DB::raw("CASE WHEN A.NoSat=1 THEN C.SAT1 WHEN A.NoSat=2 THEN C.SAT2 ELSE '' END as Satuan"),
    'A.Qnt as QntSO',
    DB::raw('COALESCE(SPK.QntSPK, 0) as QntSPK'),
    DB::raw('(A.Qnt - COALESCE(SPK.QntSPK, 0)) as Saldo')
])
```

### **Step 4: Update Repository addColumn**
**File**: `app/Http/Repository/ModuleRepository.php`

**Sebelum:**
```php
->addColumn('IsOtorisasi1Html', function ($row) {
    if ($row->IsOtorisasi1) {
        return '<span class="badge badge-success">Sudah</span>';
    }
    return '<span class="badge badge-warning">Belum</span>';
})
->addColumn('TglOto1', function ($row) {
    return $row->TglOto1 ? date('d/m/Y H:i', strtotime($row->TglOto1)) : '-';
})
->addColumn('action', function ($row) {
    $actions = '';
    $actions .= '<button type="button" class="btn btn-sm btn-primary btn-create-spk" data-no-bukti="' . $row->NoBukti . '" data-urut="' . $row->Urut . '" title="Buat SPK"><i class="fas fa-plus"></i> SPK</button>';
    return $actions;
})
->rawColumns(['IsOtorisasi1Html', 'action'])
```

**Sesudah:**
```php
// Kolom dihapus
->rawColumns([]) // Kosong karena tidak ada HTML column
```

### **Step 5: Update Export Options**
**File**: `public/assets/js/module/file.js`

**Sebelum:**
```javascript
{ $keyButton: "excel", exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11] } },
{ $keyButton: "pdf", exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11] } }
```

**Sesudah:**
```javascript
{ $keyButton: "excel", exportOptions: { columns: [1,2,3,4,5,6,7] } },
{ $keyButton: "pdf", exportOptions: { columns: [1,2,3,4,5,6,7] } }
```

### **Step 6: Update fnRowCallback**
**File**: `public/assets/js/module/file.js`

**Sebelum:**
```javascript
fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
    if (aData.indikatorExpand === false) {
        $(nRow).find("td.dt-control").addClass("indicator-white");
    }
    if (aData.IsOtorisasi1 == 1) {  // HAPUS
        $(nRow).addClass("yellowClass");
    }
}
```

**Sesudah:**
```javascript
fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
    if (aData.indikatorExpand === false) {
        $(nRow).find("td.dt-control").addClass("indicator-white");
    }
}
```

---

## 🆕 **Langkah-Langkah Menambah Kolom**

### **Step 1: Update Repository Query**
**File**: `app/Http/Repository/ModuleRepository.php`

```php
->select([
    // ... existing columns
    'NewColumn',  // TAMBAH
    DB::raw('CASE WHEN condition THEN value ELSE default END as ComputedColumn')  // TAMBAH
])
```

### **Step 2: Update Repository addColumn (Optional)**
**File**: `app/Http/Repository/ModuleRepository.php`

```php
->addColumn('FormattedNewColumn', function ($row) {
    return $row->NewColumn ? format($row->NewColumn) : '-';
})
->addColumn('ActionColumn', function ($row) {
    return '<button class="btn btn-sm btn-primary">Action</button>';
})
->rawColumns(['FormattedNewColumn', 'ActionColumn'])  // UPDATE
```

### **Step 3: Update HTML Table Headers**
**File**: `resources/views/module/index.blade.php`

```html
<thead>
    <tr>
        <!-- ... existing headers -->
        <th>New Column</th>        <!-- TAMBAH -->
        <th>Computed Column</th>   <!-- TAMBAH -->
    </tr>
</thead>
```

### **Step 4: Update JavaScript DataTables Columns**
**File**: `public/assets/js/module/file.js`

```javascript
columns: [
    // ... existing columns
    { data: "NewColumn" },                                    // TAMBAH
    { data: "ComputedColumn", className: "text-center" }     // TAMBAH
]
```

### **Step 5: Update Export Options**
**File**: `public/assets/js/module/file.js`

```javascript
// Update column numbers to include new columns
{ $keyButton: "excel", exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },  // UPDATE
{ $keyButton: "pdf", exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } }     // UPDATE
```

### **Step 6: Update fnRowCallback (Optional)**
**File**: `public/assets/js/module/file.js`

```javascript
fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
    // ... existing logic
    if (aData.NewColumn == 'special') {  // TAMBAH
        $(nRow).addClass("specialClass");
    }
}
```

---

## 📋 **Checklist Perubahan Kolom**

### **✅ Untuk Menghilangkan Kolom:**
- [ ] Step 1: Update HTML table headers
- [ ] Step 2: Update JavaScript DataTables columns config
- [ ] Step 3: Update Repository query select statement
- [ ] Step 4: Update Repository addColumn dan rawColumns
- [ ] Step 5: Update export options column numbers
- [ ] Step 6: Update fnRowCallback logic
- [ ] Step 7: Test PHP syntax: `php -l file.php`
- [ ] Step 8: Test JavaScript in browser console
- [ ] Step 9: Verify column count matches (HTML = JS)

### **✅ Untuk Menambah Kolom:**
- [ ] Step 1: Update Repository query select statement
- [ ] Step 2: Update Repository addColumn (if needed)
- [ ] Step 3: Update HTML table headers
- [ ] Step 4: Update JavaScript DataTables columns config
- [ ] Step 5: Update export options column numbers
- [ ] Step 6: Update fnRowCallback logic (if needed)
- [ ] Step 7: Test PHP syntax: `php -l file.php`
- [ ] Step 8: Test JavaScript in browser console
- [ ] Step 9: Verify column count matches (HTML = JS)

---

## 🚨 **Common Pitfalls & Solutions**

### **1. Column Count Mismatch**
**Problem**: `Cannot read properties of null (reading 'length')`
**Solution**: Ensure HTML headers count = JavaScript columns count

### **2. Missing rawColumns Update**
**Problem**: HTML not rendering in DataTables
**Solution**: Update rawColumns array with HTML column names

### **3. Export Column Numbers**
**Problem**: Export includes wrong columns
**Solution**: Update exportOptions column numbers (0-based index)

### **4. fnRowCallback References**
**Problem**: JavaScript errors for undefined properties
**Solution**: Remove references to deleted columns

### **5. Repository Query Performance**
**Problem**: Slow queries with unnecessary joins
**Solution**: Remove unused joins when removing columns

---

## 🎯 **Best Practices**

### **1. Order of Operations**
- **Removing**: HTML → JS → Repository → Test
- **Adding**: Repository → HTML → JS → Test

### **2. Column Naming Convention**
- Use consistent naming: `snake_case` in database, `camelCase` in JavaScript
- Prefix computed columns: `Formatted`, `Html`, `Display`

### **3. Performance Considerations**
- Remove unnecessary database joins when removing columns
- Use `defaultContent` for nullable columns
- Implement proper indexing for new columns

### **4. Testing Strategy**
- Test with empty data scenarios
- Verify export functionality
- Check responsive behavior
- Test sorting and searching

### **5. Documentation**
- Update API documentation
- Document column purpose and data type
- Maintain column mapping reference

---

## 📊 **Column Types Reference**

### **Data Columns**
```javascript
{ data: "ColumnName" }                           // Simple data
{ data: "ColumnName", defaultContent: '-' }     // With default
{ data: "ColumnName", className: "text-right" } // With styling
```

### **Computed Columns**
```javascript
{ data: "ColumnName", render: function(data, type, row) {
    return data ? formatData(data) : '-';
}}
```

### **Action Columns**
```javascript
{ data: "action", orderable: false, searchable: false, className: "text-center" }
```

### **Control Columns**
```javascript
{ data: null, orderable: false, searchable: false, defaultContent: "", className: "dt-control" }
```

---

## 🔍 **Debugging Commands**

```bash
# PHP Syntax Check
php -l app/Http/Repository/ModuleRepository.php

# Check DataTables in Browser Console
$('#tableId').DataTable().data().toArray()

# Check Column Count
$('#tableId thead tr th').length  // HTML count
table.columns().count()           // DataTables count

# Clear Laravel Cache
php artisan cache:clear && php artisan config:clear && php artisan view:clear
```

---

## 📚 **Real-World Example: SPK Outstanding SO**

### **Requirement**: Remove authorization columns from Outstanding SO table

### **Files Modified**:
1. `resources/views/spk/index.blade.php` - HTML headers
2. `public/assets/js/produksi/spk.js` - DataTables config
3. `app/Http/Repository/SPKRepository.php` - Query and processing

### **Result**: 
- **Before**: 11 columns with authorization data
- **After**: 7 columns with essential data only
- **Performance**: Improved query speed by removing unnecessary joins
- **UX**: Cleaner, more focused table display

---

*Last Updated: July 16, 2025*  
*Based on: SPK Module Outstanding SO Column Removal*  
*Framework: Laravel + AdminLTE + DataTables* 