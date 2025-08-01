# Laravel Browse System

Sistem pencarian data yang dikonversi dari Delphi form `FrmBrows` ke Laravel.

## Fitur

- Pencarian data dengan filter real-time
- DataTables untuk tampilan data yang responsif
- Support untuk berbagai jenis data (Gudang, Barang, Customer, dll)
- Interface yang modern dan user-friendly

## Struktur File

```
app/
├── Http/
│   ├── Controllers/
│   │   └── BrowseController.php
│   └── Requests/
│       └── BrowseRequest.php
resources/
└── views/
    ├── browse/
    │   └── index.blade.php
    └── layouts/
        └── app.blade.php
routes/
└── web.php
```

## Cara Penggunaan

1. **Akses halaman browse:**
   ```
   GET /browse
   ```

2. **Pencarian data:**
   ```
   POST /browse/search
   ```

3. **Ambil data terpilih:**
   ```
   POST /browse/get-data
   ```

## Jenis Data yang Didukung

- `100101` - Gudang
- `120302` - Barang
- `81` - Customer Member
- `11001` - Valas
- `11002` - Gudang (All)
- `1005` - Perkiraan
- `1006` - Valas

## Teknologi yang Digunakan

- Laravel 8+
- Bootstrap 5
- DataTables
- jQuery
- Font Awesome

## Setup Database

Pastikan tabel-tabel berikut sudah ada:
- `db_gudang`
- `vw_barang`
- `db_custsupp`
- `db_valas`
- `db_pemakai_gdg`

## Cara Menjalankan

1. Install dependencies:
   ```bash
   composer install
   npm install
   ```

2. Setup environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Setup database di `.env`

4. Jalankan server:
   ```bash
   php artisan serve
   ```

5. Akses `http://localhost:8000/browse` 