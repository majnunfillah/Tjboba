# PANDUAN MEMAHAMI KODE BOBAJETBRAIN UNTUK PEMULA

## DAFTAR ISI
1. [Pengantar untuk Pemula](#pengantar-untuk-pemula)
2. [Cara Membaca Struktur Kode](#cara-membaca-struktur-kode)
3. [Memahami Alur Kerja Aplikasi](#memahami-alur-kerja-aplikasi)
4. [Penjelasan Istilah Teknis](#penjelasan-istilah-teknis)
5. [Panduan Debugging](#panduan-debugging)
6. [Tips Pembelajaran](#tips-pembelajaran)

---

## PENGANTAR UNTUK PEMULA

### Apa itu Bobajetbrain?
Bobajetbrain adalah **sistem akuntansi berbasis web** yang membantu perusahaan mengelola keuangan. Bayangkan seperti buku kas digital yang canggih, dimana semua transaksi keuangan dicatat, dihitung, dan dilaporkan secara otomatis.

### Teknologi yang Digunakan
- **Laravel**: Framework PHP untuk backend (server)
- **JavaScript**: Untuk interaksi di browser (frontend)
- **MySQL**: Database untuk menyimpan data
- **HTML/CSS**: Untuk tampilan website

### Konsep Dasar yang Harus Dipahami

#### 1. MVC (Model-View-Controller)
```
Model (Database) ← → Controller (Logic) ← → View (Tampilan)
```
- **Model**: Berinteraksi dengan database
- **View**: Tampilan yang dilihat user
- **Controller**: Logic yang menghubungkan Model dan View

#### 2. Frontend vs Backend
- **Frontend**: Yang dilihat dan digunakan user (browser)
- **Backend**: Server yang memproses data (tidak terlihat user)

---

## CARA MEMBACA STRUKTUR KODE

### 1. Struktur Folder Laravel

```
bobajetbrain/
├── app/                    # Kode PHP utama
│   ├── Http/
│   │   ├── Controllers/    # Logic bisnis
│   │   ├── Repository/     # Akses database
│   │   └── Middleware/     # Filter keamanan
│   └── Models/            # Representasi tabel database
├── resources/
│   └── views/             # Template HTML
├── public/
│   └── assets/            # File CSS, JavaScript, gambar
└── routes/                # Definisi URL
```

### 2. Cara Membaca Nama File

#### Penamaan Controller
```php
MemorialController.php
│       │
│       └── Controller = File yang menangani logic
└── Memorial = Modul Memorial
```

#### Penamaan JavaScript
```javascript
memorial.js
│
└── memorial = File JavaScript untuk modul Memorial
```

#### Penamaan View
```php
memorial.blade.php
│        │      │
│        │      └── .php = File PHP
│        └── .blade = Template Laravel
└── memorial = Tampilan untuk Memorial
```

### 3. Memahami Kode PHP

#### Contoh Controller Sederhana
```php
<?php
namespace App\Http\Controllers;  // Lokasi file

class MemorialController extends Controller  // Nama class
{
    public function index()  // Function/method
    {
        return view('accounting.memorial');  // Return tampilan
    }
}
```

**Penjelasan**:
- `namespace`: Seperti alamat file
- `class`: Kumpulan function yang berkaitan
- `public function`: Function yang bisa dipanggil dari luar
- `return view()`: Menampilkan halaman HTML

#### Contoh Repository
```php
public function getAllMemorial()
{
    $userid = auth()->user()->USERID;  // Ambil ID user yang login
    $periode = $this->queryModel('dbperiode')  // Query ke database
        ->where('USERID', $userid)  // Filter berdasarkan user
        ->first();  // Ambil data pertama
    
    return DB::select("SELECT ...");  // Query SQL
}
```

**Penjelasan**:
- `auth()->user()`: Data user yang sedang login
- `->where()`: Filter data (seperti WHERE di SQL)
- `->first()`: Ambil satu data saja
- `DB::select()`: Jalankan query SQL

### 4. Memahami Kode JavaScript

#### Contoh Event Handler
```javascript
$(document).on("click", ".buttons-add", function (e) {
    e.preventDefault();  // Cegah action default
    
    // Buka modal untuk tambah data
    getModal(options);
});
```

**Penjelasan**:
- `$(document).on()`: Tunggu event klik
- `".buttons-add"`: Selector CSS (tombol dengan class buttons-add)
- `function (e)`: Function yang dijalankan saat diklik
- `e.preventDefault()`: Cegah refresh halaman

#### Contoh AJAX Request
```javascript
baseAjax({
    url: publicURL + "/accounting/transaksi-memorial",  // URL tujuan
    type: "POST",  // Method HTTP
    param: {  // Data yang dikirim
        NoBukti: data.NoBukti,
        status: 1
    },
    successCallback: function (res) {  // Jika berhasil
        datatableMain.ajax.reload();  // Refresh tabel
    }
});
```

**Penjelasan**:
- `baseAjax()`: Function custom untuk komunikasi dengan server
- `url`: Alamat endpoint di server
- `param`: Data yang dikirim ke server
- `successCallback`: Function yang dijalankan jika berhasil

---

## MEMAHAMI ALUR KERJA APLIKASI

### 1. Alur Tambah Data Memorial

#### Step 1: User Klik Tombol Tambah
```javascript
// File: memorial.js
$(document).on("click", ".buttons-add.btn-memorial", function (e) {
    e.preventDefault();
    getModal(options);  // Buka modal
});
```

#### Step 2: Modal Terbuka
```php
// File: ModalController.php
public function getModal(Request $request)
{
    $view = view($request->resource)->render();  // Render template
    return response()->json(['html' => $view]);  // Return HTML
}
```

#### Step 3: User Submit Form
```javascript
// File: memorial.js
modal.on("submit", "form", function (e) {
    e.preventDefault();
    formAjax({
        form: $(this),
        callbackSuccess: function (data) {
            modal.modal("hide");  // Tutup modal
            datatableMain.ajax.reload();  // Refresh tabel
        }
    });
});
```

#### Step 4: Controller Proses Data
```php
// File: MemorialController.php
public function store(Request $request)
{
    // Validasi data
    $request->validate([
        'NoBukti' => 'required',
        'Tanggal' => 'required|date'
    ]);
    
    // Simpan ke database
    $this->memorialRepository->store($request);
    
    return $this->setResponseSuccess('Data berhasil disimpan');
}
```

#### Step 5: Repository Simpan ke Database
```php
// File: MemorialRepository.php
public function store($request)
{
    $data = $this->queryModel('dbtrans')->firstOrNew();
    $data->NoBukti = $request->NoBukti;
    $data->Tanggal = $request->Tanggal;
    $data->save();  // Simpan ke database
}
```

### 2. Alur Load Data di Tabel

#### Step 1: DataTable Request Data
```javascript
// File: memorial.js
var datatableMain = $("#datatableMain").DataTable({
    ajax: {
        url: $("#datatableMain").data("server"),  // URL untuk ambil data
        type: 'GET'
    }
});
```

#### Step 2: Controller Return Data
```php
// File: MemorialController.php
public function index()
{
    if (request()->ajax()) {  // Jika request AJAX
        $memorialData = $this->memorialRepository->getAllMemorial();
        
        return CustomDataTable::init()
            ->of($memorialData)
            ->done();  // Return format DataTable
    }
    
    return view('accounting.memorial');  // Return halaman HTML
}
```

#### Step 3: Repository Query Database
```php
// File: MemorialRepository.php
public function getAllMemorial()
{
    return DB::select("SELECT NoBukti, Tanggal, Note FROM dbTrans");
}
```

---

## PENJELASAN ISTILAH TEKNIS

### Database Terms
- **Query**: Perintah untuk mengambil/mengubah data
- **Select**: Mengambil data
- **Insert**: Menambah data baru
- **Update**: Mengubah data existing
- **Delete**: Menghapus data
- **Join**: Menggabungkan data dari beberapa tabel
- **Where**: Filter data berdasarkan kondisi

### Business Terms (BobaJetBrain)
- **SPK**: Surat Perintah Kerja (Work Order)
- **RPP**: Rencana Produksi/Perintah (sama dengan SPK)
- **SO**: Sales Order (Pesanan Penjualan)
- **Outstanding**: Data yang belum selesai diproses
- **Otorisasi**: Proses persetujuan/approval

### Laravel Terms
- **Route**: Definisi URL dan action yang dijalankan
- **Controller**: File yang berisi logic bisnis
- **Model**: Representasi tabel database
- **View**: Template untuk tampilan
- **Middleware**: Filter yang dijalankan sebelum/sesudah request
- **Repository**: Pattern untuk memisahkan logic database

### JavaScript Terms
- **Event**: Aksi user (klik, ketik, dll)
- **Callback**: Function yang dijalankan setelah action selesai
- **AJAX**: Komunikasi dengan server tanpa refresh halaman
- **DOM**: Struktur HTML di browser
- **Selector**: Cara memilih element HTML

### Frontend Terms
- **Modal**: Pop-up window
- **DataTable**: Tabel data yang interactive
- **Bootstrap**: Framework CSS untuk styling
- **jQuery**: Library JavaScript untuk manipulasi DOM

---

## PANDUAN DEBUGGING

### 1. Debug PHP (Backend)

#### Menggunakan Log
```php
// Tambahkan di Controller
\Log::info('Data yang diterima:', $request->all());
\Log::error('Error message:', ['error' => $e->getMessage()]);
```

#### Melihat Log
```bash
# File log Laravel
tail -f storage/logs/app.log
```

#### Debug dengan dd()
```php
// Tampilkan data dan stop execution
dd($data);

// Tampilkan data tanpa stop
dump($data);
```

#### Troubleshooting Daftar RPP Kosong
```php
// 1. Cek apakah ada data SPK (RPP sama dengan SPK)
DB::table('dbSPK')->count();

// 2. Cek data untuk bulan/tahun tertentu
DB::table('dbSPK')
    ->whereYear('Tanggal', date('Y'))
    ->whereMonth('Tanggal', date('m'))
    ->get();

// 3. Gunakan debug endpoint
// GET /produksi/spk/debug untuk melihat jumlah data

// 4. Cek log untuk error
tail -f storage/logs/app.log | grep "RPP\|SPK"

// 5. Test endpoint langsung
// GET /produksi/spk/get-rpp-data
```

#### Troubleshooting Frontend (JavaScript)
```javascript
// Buka Browser Console (F12) dan cek:
// 1. Error di Network tab saat load data RPP
// 2. Console log messages untuk debugging
// 3. Response dari server

// Contoh debugging:
console.log('RPP Tab clicked');
console.log('DataTable URL:', $("#datatableRpp").data("server"));
```

### 2. Debug JavaScript (Frontend)

#### Menggunakan Console
```javascript
// Tampilkan data di browser console
console.log('Data:', data);
console.error('Error:', error);
console.warn('Warning:', warning);
```

#### Debug AJAX
```javascript
baseAjax({
    url: url,
    param: data,
    successCallback: function (res) {
        console.log('Success:', res);  // Debug response
    },
    errorCallback: function (xhr) {
        console.error('Error:', xhr.responseText);  // Debug error
    }
});
```

### 3. Debug Database

#### Melihat Query yang Dijalankan
```php
// Aktifkan query log
DB::enableQueryLog();

// Jalankan query
$data = DB::select("SELECT * FROM dbTrans");

// Lihat query yang dijalankan
dd(DB::getQueryLog());
```

### 4. Tools untuk Debugging

#### Browser Developer Tools
1. **F12** untuk buka Developer Tools
2. **Console Tab**: Lihat JavaScript errors dan log
3. **Network Tab**: Lihat AJAX requests
4. **Elements Tab**: Inspect HTML/CSS

#### Laravel Tools
1. **Laravel Telescope**: Monitor requests, queries, logs
2. **Laravel Debugbar**: Debug toolbar di browser
3. **Tinker**: Interactive PHP shell

---

## TIPS PEMBELAJARAN

### 1. Mulai dari Yang Sederhana

#### Pahami Alur Dasar
1. User buka halaman → Route → Controller → View
2. User klik tombol → JavaScript → AJAX → Controller → Database
3. Database return data → Controller → JSON → JavaScript → Update UI

#### Baca Kode Secara Bertahap
1. Mulai dari Route (`routes/web.php`)
2. Ikuti ke Controller
3. Lihat View yang di-render
4. Cek JavaScript yang terkait

### 2. Praktik Hands-On

#### Coba Modifikasi Sederhana
```php
// Contoh: Tambah kolom baru di tabel
// 1. Tambah kolom di database
// 2. Update Model
// 3. Update Controller
// 4. Update View
// 5. Update JavaScript
```

#### Gunakan Git untuk Backup
```bash
git add .
git commit -m "Backup sebelum modifikasi"
```

### 3. Belajar Debugging

#### Selalu Test Perubahan
1. Buat perubahan kecil
2. Test langsung
3. Debug jika error
4. Commit jika berhasil

#### Gunakan Browser Console
```javascript
// Tambahkan console.log di berbagai tempat
console.log('Function dipanggil');
console.log('Data yang dikirim:', data);
console.log('Response dari server:', response);
```

### 4. Pahami Pattern yang Digunakan

#### Repository Pattern
```
Controller → Repository → Model → Database
```

#### MVC Pattern
```
Route → Controller → Model/Repository → View
```

#### AJAX Pattern
```
User Action → JavaScript → AJAX → Controller → JSON Response → Update UI
```

### 5. Baca Dokumentasi

#### Laravel Documentation
- https://laravel.com/docs/8.x
- Fokus pada: Routing, Controllers, Models, Views

#### JavaScript/jQuery
- https://api.jquery.com/
- Fokus pada: Event Handling, AJAX, DOM Manipulation

### 6. Latihan Membaca Kode

#### Pilih Satu Feature
1. Pilih feature sederhana (misal: view data)
2. Trace dari awal sampai akhir
3. Buat diagram alur
4. Coba modifikasi kecil

#### Buat Catatan
```
Feature: Tambah Memorial
Files involved:
- Route: routes/web.php line 120
- Controller: MemorialController.php method store()
- Repository: MemorialRepository.php method store()
- View: memorial.blade.php
- JavaScript: memorial.js line 180
```

---

## CONTOH LATIHAN PEMAHAMAN

### Latihan 1: Trace Alur Tambah Data

#### Langkah-langkah:
1. Buka `memorial.blade.php` → Cari tombol "Tambah"
2. Lihat class CSS tombol → Cari di `memorial.js`
3. Ikuti event handler → Lihat function yang dipanggil
4. Trace sampai ke Controller dan Repository

#### Pertanyaan untuk Dijawab:
- Bagaimana modal dibuka?
- Data apa saja yang divalidasi?
- Bagaimana data disimpan ke database?
- Bagaimana response dikembalikan ke user?

### Latihan 2: Modifikasi Sederhana

#### Task: Tambah Kolom "Kode Transaksi"
1. Tambah kolom di database
2. Update form di modal
3. Update validation di Controller
4. Update save logic di Repository
5. Update tampilan di DataTable

#### Checklist:
- [ ] Database migration
- [ ] Update Model
- [ ] Update Controller validation
- [ ] Update Repository
- [ ] Update View
- [ ] Update JavaScript
- [ ] Test functionality

### Latihan 3: Debug Error

#### Skenario: Tombol Simpan Tidak Berfungsi
1. Buka Browser Console → Cek JavaScript errors
2. Lihat Network Tab → Cek AJAX request
3. Cek Laravel Log → Lihat error dari server
4. Debug step by step dengan console.log

---

## KESIMPULAN

### Yang Harus Dipahami Dulu:
1. **Alur dasar MVC**: Route → Controller → View
2. **Komunikasi AJAX**: JavaScript ↔ PHP
3. **Structure Laravel**: Controllers, Models, Views, Routes
4. **JavaScript Events**: Click, Submit, Change

### Yang Bisa Dipelajari Bertahap:
1. **Advanced Laravel**: Middleware, Service Providers, Artisan
2. **Database Optimization**: Indexing, Query Optimization
3. **Frontend Framework**: Vue.js, React (jika ingin upgrade)
4. **Testing**: Unit Tests, Feature Tests

### Tools yang Membantu:
1. **IDE**: VS Code dengan extensions Laravel dan PHP
2. **Database Tool**: phpMyAdmin, MySQL Workbench
3. **Browser**: Chrome dengan Developer Tools
4. **Version Control**: Git untuk backup dan collaboration

Ingat: **Belajar coding adalah proses bertahap. Mulai dari yang sederhana, praktik terus, dan jangan takut untuk mencoba!** 