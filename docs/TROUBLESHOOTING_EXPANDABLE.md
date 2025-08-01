# 🚨 TROUBLESHOOTING EXPANDABLE PATTERN

## **Error 1: "Undefined variable $noBukti"**
**Penyebab:** Variabel tidak terdefinisi dalam closure function
**Solusi:** Definisikan variabel sebelum digunakan
```php
// ✅ BENAR
$noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
$urut = isset($data->Urut) ? $data->Urut : '';
$html .= "<button data-bukti='{$noBukti}' data-urut='{$urut}'>";
```

## **Error 2: "Missing required parameter for route"**
**Penyebab:** Route konflik atau parameter tidak diberikan
**Solusi:** Hapus route yang konflik
```php
// ❌ SALAH - Ada 2 route yang konflik
Route::delete('/', [Controller::class, 'delete'])->name('.delete');
Route::delete('/delete/{noBukti}', [Controller::class, 'destroy'])->name('.delete');

// ✅ BENAR - Hanya satu route
Route::delete('/', [Controller::class, 'delete'])->name('.delete');
```

## **Error 3: "Method deleteData does not exist"**
**Penyebab:** Method tidak ada di repository
**Solusi:** Tambahkan method di repository
```php
public function deleteData($noBukti)
{
    try {
        // Delete detail records first
        $deletedDetails = DB::delete("DELETE FROM detail_table WHERE NoBukti = ?", [$noBukti]);
        
        // Delete header record
        $deletedHeader = DB::delete("DELETE FROM header_table WHERE NoBukti = ?", [$noBukti]);
        
        return $deletedHeader > 0;
    } catch (\Illuminate\Database\QueryException $ex) {
        \Log::error('Error in deleteData: ' . $ex->getMessage());
        throw $ex;
    }
}
```

## **Error 4: "DataTables unknown parameter 'Urut'"**
**Penyebab:** Column tidak ada di hasil query
**Solusi:** Pastikan field ada di query dan mapping
```php
->mapData(function ($row) {
    $row->Urut = $row->Urut ?? '';
    $row->NoBukti = request()->NoBukti;
    return $row;
})
```

## **Error 5: "501 Not Implemented"**
**Penyebab:** Method signature tidak sesuai
**Solusi:** Ikuti pola Memorial - TIDAK gunakan parameter Request
```php
// ❌ SALAH
public function getDetailByNoBukti(Request $request)

// ✅ BENAR
public function getDetailByNoBukti()
{
    if (!request()->NoBukti) {
        return $this->setResponseError('No Bukti tidak boleh kosong');
    }
    // ... rest of logic
}
```

## **Debugging Commands**
```bash
# Clear caches
php artisan route:clear
php artisan config:clear
php artisan view:clear

# Check routes
php artisan route:list | findstr "modul-name"

# Check logs
tail -f storage/logs/laravel.log
```

## **Golden Rules**
1. **SELALU ikuti pola Memorial** yang sudah terbukti berjalan
2. **JANGAN gunakan parameter Request** untuk method detail
3. **DEFINISIKAN variabel** sebelum digunakan dalam closure
4. **HAPUS route yang konflik** dengan nama yang sama
5. **TAMBAHKAN method yang dibutuhkan** di controller/repository
6. **CLEAR route cache** setelah mengubah routes
7. **LOG semua error** untuk debugging
8. **TEST step by step** untuk memastikan setiap komponen berfungsi 