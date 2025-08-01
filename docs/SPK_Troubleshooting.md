# SPK Module Troubleshooting Guide

## 🚨 Error Resolution Documentation

### **Error 0: Missing Controller Methods - AJAX Request Failure**

**Symptoms:**
- `request()->ajax()` tidak bisa dieksekusi
- Data tidak muncul di UI
- DataTables loading terus menerus
- AJAX request gagal tanpa error yang jelas
- Route tidak dikenali Laravel

**Root Cause:**
Routes merujuk ke method yang tidak ada di controller. Laravel tidak bisa compile routes dengan benar ketika ada missing methods.

**Example Error Pattern:**
```php
// Di routes/web.php
Route::post('/set-otorisasi', [SPKController::class, 'setOtorisasi']);
Route::post('/store', [SPKController::class, 'store']);
Route::delete('/delete/{noBukti}', [SPKController::class, 'destroy']);

// Di SPKController.php - METHOD TIDAK ADA!
// Missing: setOtorisasi(), store(), destroy()
```

**Solution:**
```php
// Tambahkan stub methods di controller
public function setOtorisasi(Request $request)
{
    try {
        Log::info('setOtorisasi called', $request->all());
        // TODO: Implement logic
        return response()->json(['success' => true]);
    } catch (Exception $e) {
        Log::error('Error in setOtorisasi: ' . $e->getMessage());
        return response()->json(['success' => false], 500);
    }
}

public function store(Request $request)
{
    try {
        Log::info('store called', $request->all());
        // TODO: Implement logic
        return response()->json(['success' => true]);
    } catch (Exception $e) {
        Log::error('Error in store: ' . $e->getMessage());
        return response()->json(['success' => false], 500);
    }
}

public function destroy(Request $request, $noBukti)
{
    try {
        Log::info('destroy called', ['noBukti' => $noBukti]);
        // TODO: Implement logic
        return response()->json(['success' => true]);
    } catch (Exception $e) {
        Log::error('Error in destroy: ' . $e->getMessage());
        return response()->json(['success' => false], 500);
    }
}
```

**Prevention:** 
- Selalu buat stub methods dengan TODO comment saat mendefinisikan routes
- Periksa routes vs controller methods sebelum debugging masalah lain
- Gunakan `php artisan route:list` untuk verifikasi routes

**Files Modified:**
- `app/Http/Controllers/SPKController.php`

---

### **Error 1: JavaScript `$globalVariable is not defined`**

**Symptoms:**
- Console error: `$globalVariable is not defined` at line 30 of spk.js
- SPK module fails to load properly
- DataTables not initializing

**Root Cause:**
- SPK module using legacy JavaScript pattern while other modules use ES6
- `base-function.js` commented out in layout
- `helper.js` loaded as ES6 module but not exporting to global scope

**Solution:**
1. **Export global variable in helper.js:**
   ```javascript
   // Add to both dev and production helper.js
   window.$globalVariable = $globalVariable;
   ```

2. **Implement retry mechanism in spk.js:**
   ```javascript
   function waitForGlobalVariable(callback, maxAttempts = 10) {
       let attempts = 0;
       function check() {
           if (typeof $globalVariable !== 'undefined') {
               callback();
           } else if (attempts < maxAttempts) {
               attempts++;
               setTimeout(check, 100);
           } else {
               console.error('$globalVariable not available after maximum attempts');
           }
       }
       check();
   }
   ```

**Files Modified:**
- `public/assets/js/helper.js` (dev & production)
- `public/assets/js/produksi/spk.js`

---

### **Error 2: 405 Method Not Allowed for debug-log endpoint**

**Symptoms:**
- Error: `POST http://127.0.0.1:8000/produksi/spk/debug-log 405 (Method Not Allowed)`
- Debug logging not working

**Root Cause:**
- Missing route definition for debug-log endpoint
- Controller method not implemented

**Solution:**
1. **Add route in web.php:**
   ```php
   Route::post('/produksi/spk/debug-log', [SPKController::class, 'debugLog'])->name('produksi.spk.debug-log');
   ```

2. **Add controller method:**
   ```php
   public function debugLog(Request $request)
   {
       Log::info('SPK Debug Log', $request->all());
       return response()->json(['status' => 'logged']);
   }
   ```

**Files Modified:**
- `routes/web.php`
- `app/Http/Controllers/SPKController.php`

---

### **Error 3: DataTables Column Mismatch**

**Symptoms:**
- Error: `Cannot read properties of null (reading 'length')`
- DataTables not rendering properly
- Column count mismatch between HTML and JavaScript config

**Root Cause:**
- HTML table headers don't match DataTables column configuration
- Missing columns in repository data structure

**Solution:**
1. **Outstanding SO Table - Fix column mapping:**
   ```javascript
   // HTML has 11 columns, config must match
   columns: [
       { data: null, orderable: false, searchable: false, defaultContent: "", className: "dt-control" },
       { data: "NoBukti" },
       { data: "Urut" },
       { data: "KodeBrg" },
       { data: "NamaBrg" },
       { data: "Tanggal" },
       { data: "QntSO", className: "text-right" },
       { data: "QntSPK", className: "text-right" },
       { data: "Saldo", className: "text-right" },
       { data: "Satuan" },
       { data: "action", orderable: false, searchable: false, className: "text-center" }
   ]
   ```

2. **Add null checks in initComplete:**
   ```javascript
   initComplete: function (settings, json) {
       try {
           if (json && json.data && json.data.length == 0) {
               // Handle empty data
           }
       } catch (error) {
           console.error('Error in initComplete:', error);
       }
   }
   ```

**Files Modified:**
- `public/assets/js/produksi/spk.js`
- `app/Http/Repository/SPKRepository.php`

---

### **Error 4: PHP Syntax Error - Missing Closing Brace**

**Symptoms:**
- Error: `syntax error, unexpected token "public"` at line 493
- 500 Internal Server Error when accessing SPK module
- Laravel application fails to load

**Root Cause:**
- Missing closing brace for `try` block in `getOutstandingSO` method
- Missing `catch` block and Exception import

**Solution:**
1. **Add missing imports:**
   ```php
   use Exception;
   ```

2. **Fix method structure:**
   ```php
   public function getOutstandingSO($request = null)
   {
       try {
           // ... method logic ...
           return $result;
       } catch (Exception $e) {
           Log::error('Error in getOutstandingSO: ' . $e->getMessage());
           throw $e;
       }
   }
   ```

3. **Verify syntax:**
   ```bash
   php -l app/Http/Repository/SPKRepository.php
   ```

**Files Modified:**
- `app/Http/Repository/SPKRepository.php`

---

## 🔧 **General Troubleshooting Steps**

### **1. JavaScript Issues**
```bash
# Check browser console for errors
# Verify module loading order
# Check global variable availability
```

### **2. PHP Syntax Issues**
```bash
# Check syntax
php -l path/to/file.php

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### **3. Database Issues**
```bash
# Check database connection
php artisan tinker
DB::connection()->getPdo();

# Check SQL Server 2008 compatibility
# Avoid: CONCAT(), FORMAT(), IIF()
# Use: +, CONVERT(), CASE WHEN
```

### **4. Route Issues**
```bash
# List all routes
php artisan route:list --name=spk

# Clear route cache
php artisan route:clear
```

### **5. DataTables Issues**
```javascript
// Enable debug mode
"serverSide": true,
"processing": true,
"ajax": {
    "url": "your-endpoint",
    "type": "GET",
    "error": function(xhr, error, thrown) {
        console.error('DataTables error:', error, thrown);
    }
}
```

---

## 📋 **Prevention Checklist**

### **Before Deploying:**
- [ ] Check PHP syntax: `php -l file.php`
- [ ] Test JavaScript in browser console
- [ ] Verify DataTables column count matches HTML
- [ ] Check SQL Server 2008 compatibility
- [ ] Test all CRUD operations
- [ ] Verify route definitions
- [ ] Check error logs

### **Code Quality:**
- [ ] Use try-catch blocks for error handling
- [ ] Add proper logging for debugging
- [ ] Implement null checks for DataTables
- [ ] Follow PSR-12 coding standards
- [ ] Use type hints and return types
- [ ] Add meaningful comments for complex logic

### **Testing:**
- [ ] Test in development environment first
- [ ] Verify in production-like environment
- [ ] Check browser compatibility
- [ ] Test with different data scenarios
- [ ] Verify mobile responsiveness

---

## 🎯 **Common Patterns**

### **Error Handling Pattern:**
```php
public function methodName($request = null)
{
    try {
        Log::info('Method called');
        
        // Main logic here
        
        Log::info('Method completed successfully');
        return $result;
        
    } catch (Exception $e) {
        Log::error('Error in methodName: ' . $e->getMessage());
        throw $e;
    }
}
```

### **DataTables Pattern:**
```javascript
var table = $("#tableId").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "endpoint-url",
        type: 'GET',
        error: function(xhr, error, thrown) {
            console.error('DataTables error:', error, thrown);
        }
    },
    columns: [
        // Match HTML column count exactly
    ],
    initComplete: function (settings, json) {
        try {
            if (json && json.data) {
                // Handle initialization
            }
        } catch (error) {
            console.error('Error in initComplete:', error);
        }
    }
});
```

### **SQL Server 2008 Pattern:**
```sql
-- CORRECT for SQL Server 2008
SELECT 
    field1 + ' - ' + field2 as combined,
    CASE WHEN condition THEN 'Yes' ELSE 'No' END as status,
    CONVERT(VARCHAR(10), date_field, 103) as formatted_date
FROM table_name;

-- INCORRECT (newer SQL Server versions)
SELECT 
    CONCAT(field1, ' - ', field2) as combined,
    IIF(condition, 'Yes', 'No') as status,
    FORMAT(date_field, 'dd/MM/yyyy') as formatted_date
FROM table_name;
```

---

## 📞 **Quick Reference**

### **Log Files:**
- Laravel: `storage/logs/laravel.log`
- Web Server: `public/error_log`

### **Key Commands:**
```bash
# Start development server
php artisan serve --host=127.0.0.1 --port=8000

# Check syntax
php -l file.php

# Clear caches
php artisan cache:clear && php artisan config:clear && php artisan view:clear

# Check routes
php artisan route:list --name=spk
```

### **Browser Debug:**
```javascript
// Check global variables
console.log(typeof $globalVariable);

// Debug DataTables
$('#tableId').DataTable().ajax.reload();

// Check AJAX responses
// Open Network tab in Developer Tools
```

---

*Last Updated: July 16, 2025*
*Module: SPK (Surat Perintah Kerja)*
*Framework: Laravel + AdminLTE + DataTables* 

## [2024-06-09] Troubleshooting Error 501 Expandable Child Table SPK (DataTables)

### Gejala:
- Data master SPK tampil normal.
- Saat klik expand (child table), DataTables error:
  - Ajax error (DataTables warning)
  - Console: `POST /produksi/transaksi-spk/detail 501 (Not Implemented)`
- Di Laravel log (`storage/logs/laravel.log`):
  - `local.ERROR: SPK Detail Error: {"message":"Route [produksi.spk.delete-Spk] not defined." ...}`

### Analisa:
- Route dan controller method untuk detail sudah benar dan terdaftar.
- Permission user bukan masalah (data master keluar).
- AJAX expand child table tetap error 501.
- Ternyata, di controller method `getSpkDetailByNoBukti`, pada bagian action button, ada pemanggilan:
  ```php
  $url = route('produksi.spk.delete-Spk');
  ```
- Route `produksi.spk.delete-Spk` **TIDAK ADA** di `routes/web.php`.
- Laravel throw exception saat generate URL, sehingga response AJAX gagal (501).

### Solusi:
1. **Perbaiki pemanggilan route di controller:**
   - Ganti semua `route('produksi.spk.delete-Spk')` menjadi `route('produksi.spk.delete')`.
   - Pastikan nama route sesuai dengan yang terdaftar di `routes/web.php`.
2. **Simpan file, reload halaman, dan test ulang.**
3. **Hasil:**
   - Expand/child table SPK tampil normal.
   - Tidak ada error 501 di console/log.

### Catatan:
- Error 501 pada AJAX expand DataTables seringkali disebabkan oleh exception di controller (misal: route tidak ditemukan, variabel tidak ada, dsb), bukan masalah permission atau policy.
- Selalu cek log Laravel jika AJAX error 501/500.
- Ikuti pola Memorial untuk action button dan penamaan route agar minim error.

--- 