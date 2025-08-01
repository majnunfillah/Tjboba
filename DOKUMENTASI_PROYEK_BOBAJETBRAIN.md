# DOKUMENTASI LENGKAP PROYEK BOBAJETBRAIN

## DAFTAR ISI
1. [Gambaran Umum Proyek](#gambaran-umum-proyek)
2. [Arsitektur Aplikasi](#arsitektur-aplikasi)
3. [Struktur Folder](#struktur-folder)
4. [Teknologi yang Digunakan](#teknologi-yang-digunakan)
5. [Modul Utama](#modul-utama)
6. [Analisis Kode Memorial](#analisis-kode-memorial)
7. [Database dan Model](#database-dan-model)
8. [Frontend dan JavaScript](#frontend-dan-javascript)
9. [Sistem Keamanan](#sistem-keamanan)
10. [Workflow Aplikasi](#workflow-aplikasi)

---

## GAMBARAN UMUM PROYEK

**Bobajetbrain** adalah sistem informasi akuntansi berbasis web yang dibangun menggunakan framework Laravel 8. Aplikasi ini dirancang untuk mengelola transaksi keuangan perusahaan dengan fitur-fitur seperti:

- **Manajemen Memorial**: Pencatatan jurnal memorial
- **Bank & Kas**: Pengelolaan transaksi bank dan kas
- **Master Data**: Pengelolaan data perkiraan, aktiva, dan lainnya
- **Laporan**: Generasi laporan keuangan
- **Tutup Buku**: Proses penutupan periode akuntansi

---

## ARSITEKTUR APLIKASI

### Pola Arsitektur
Aplikasi menggunakan pola **Repository Pattern** dan **MVC (Model-View-Controller)** dengan struktur:

```
Controller → Repository → Model → Database
     ↓
   View (Blade Template)
     ↓
   JavaScript (Frontend Logic)
```

### Komponen Utama
1. **Controllers**: Menangani request HTTP dan business logic
2. **Repositories**: Abstraksi layer untuk database operations
3. **Models**: Representasi tabel database
4. **Services**: Logic khusus seperti CustomDataTable
5. **Middleware**: Validasi akses dan otentikasi
6. **Views**: Template Blade untuk UI
7. **JavaScript Modules**: Logic frontend dengan ES6 modules

---

## STRUKTUR FOLDER

### Backend (PHP/Laravel)
```
app/
├── Console/                 # Artisan commands
├── Exceptions/              # Custom exception handlers
├── Helpers/                 # Helper functions
├── Http/
│   ├── Controllers/         # HTTP request handlers
│   ├── Middleware/          # Request middleware
│   ├── Repository/          # Data access layer
│   ├── Requests/            # Form request validation
│   └── Services/            # Business logic services
├── Models/                  # Eloquent models
├── Providers/               # Service providers
├── Repositories/            # Global repositories
├── Services/                # Application services
└── View/                    # View components
```

### Frontend (JavaScript/CSS)
```
public/assets/
├── css/                     # Stylesheets
├── js/
│   ├── accounting/          # Modul akuntansi
│   ├── berkas/              # Modul berkas
│   ├── laporan-laporan/     # Modul laporan
│   ├── master-data/         # Modul master data
│   ├── pages/               # Halaman khusus
│   ├── utilitas/            # Utilities
│   ├── base-function.js     # Fungsi dasar
│   └── helper.js            # Helper functions
└── plugins/                 # Third-party plugins
```

---

## TEKNOLOGI YANG DIGUNAKAN

### Backend
- **Laravel 8.75**: Framework PHP utama
- **PHP 7.3|8.0**: Bahasa pemrograman server
- **MySQL**: Database management system
- **DomPDF**: Generasi dokumen PDF
- **PhpSpreadsheet**: Export Excel
- **Yajra DataTables**: Server-side processing untuk tabel

### Frontend
- **jQuery 3.x**: Library JavaScript utama
- **DataTables**: Plugin tabel interaktif
- **AdminLTE 3**: Template admin dashboard
- **Bootstrap 4**: CSS framework
- **Select2**: Enhanced select boxes
- **SweetAlert2**: Modal notifications
- **Moment.js**: Date manipulation
- **Font Awesome**: Icon library

### Development Tools
- **Composer**: PHP dependency manager
- **NPM**: Node package manager
- **Laravel Mix**: Asset compilation
- **ESLint**: JavaScript linting

---

## MODUL UTAMA

### 1. Modul Memorial (Jurnal Memorial)
**Lokasi**: `app/Http/Controllers/MemorialController.php`

**Fungsi Utama**:
- Pencatatan transaksi jurnal memorial
- Sistem otorisasi bertingkat (Level 1 & 2)
- Export PDF dan Excel
- Validasi periode aktif

**Fitur**:
- CRUD operations untuk memorial
- Detail transaksi dengan expandable rows
- Validasi nomor bukti otomatis
- Sistem approval workflow

### 2. Modul Bank & Kas
**Lokasi**: `app/Http/Controllers/BankOrKasController.php`

**Fungsi Utama**:
- Manajemen transaksi bank dan kas
- Pelunasan hutang
- Rekonsiliasi bank

### 3. Modul Master Data
**Fungsi Utama**:
- Master Perkiraan (Chart of Accounts)
- Master Aktiva
- Master Group & Sub Group
- Posting Configuration

### 4. Modul Laporan
**Fungsi Utama**:
- Neraca
- Laba Rugi
- Arus Kas
- Custom reports

---

## ANALISIS KODE MEMORIAL

### Controller Layer (`MemorialController.php`)

#### Method `index()`
```php
public function index()
{
    if (request()->ajax()) {
        // Logika untuk DataTable server-side processing
        $hasOtorisasi1 = in_array('IsOtorisasi1', $this->access);
        $hasOtorisasi2 = in_array('IsOtorisasi2', $this->access);
        $canKoreksi = in_array('ISKOREKSI', $this->access);
        
        // Mengambil data dari repository
        $memorialData = $this->memorialRepository->getAllMemorial();
        
        // Processing dengan CustomDataTable
        return CustomDataTable::init()
            ->of($memorialData)
            ->apply()
            ->mapData(function ($row) use ($hasOtorisasi1, $hasOtorisasi2) {
                // Format tanggal dan angka
                // Generate HTML untuk checkbox otorisasi
                // Tambahkan expandable table
            })
            ->addColumn('action', function ($data) {
                // Generate tombol aksi berdasarkan permission
            })
            ->done();
    }
    
    return view('accounting.memorial');
}
```

**Penjelasan**:
1. **Permission Check**: Validasi hak akses user
2. **Data Retrieval**: Mengambil data dari repository
3. **Data Processing**: Format data untuk DataTable
4. **Action Buttons**: Generate tombol berdasarkan permission
5. **Response**: Return JSON untuk AJAX atau view untuk HTTP

#### Method `store()` dan `storeMemorial()`
```php
public function storeMemorial(Request $request)
{
    try {
        $this->requestAjax($this->access, 'ISTAMBAH');
        $result = $this->memorialRepository->storeMemorial($request);
        return $this->setResponseSuccess('Berhasil menyimpan data');
    } catch (\Exception $e) {
        return $this->setResponseError($e->getMessage());
    }
}
```

**Penjelasan**:
1. **Permission Validation**: Cek hak akses tambah data
2. **Repository Call**: Delegate ke repository layer
3. **Response Handling**: Return success/error response

### Repository Layer (`MemorialRepository.php`)

#### Method `getAllMemorial()`
```php
public function getAllMemorial()
{
    $userid = auth()->user()->USERID;
    $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
    
    return DB::select("SELECT A.NoBukti, A.Tanggal, A.Note, 
        sum(case when B.Valas='IDR' then 0.00 else B.Debet+B.Kredit end) TotalD,
        sum((B.Debet+B.Kredit)*B.Kurs) TotalRp,
        A.IsOtorisasi1, A.OtoUser1, A.TglOto1, 
        A.IsOtorisasi2, A.OtoUser2, A.TglOto2
        FROM dbTrans A
        LEFT JOIN dbTransaksi B ON B.NoBukti=A.NoBukti
        WHERE year(A.Tanggal)={$periode->TAHUN} 
        AND month(A.Tanggal)={$periode->BULAN}
        AND A.TipeTransHD IN ('BMM','BJK','PBL','PJL')
        GROUP BY A.NoBukti, A.Tanggal, A.Note, [otorisasi fields]
        ORDER BY A.NoBukti");
}
```

**Penjelasan**:
1. **Period Filter**: Filter berdasarkan periode aktif user
2. **Complex Query**: Join multiple tables dengan aggregation
3. **Transaction Types**: Filter tipe transaksi memorial
4. **Authorization Data**: Include status otorisasi

#### Method `storeMemorial()`
```php
public function storeMemorial($request)
{
    DB::beginTransaction();
    try {
        // Validasi data
        $validatedData = $request->validate([
            'NoBukti' => ['required', 'string', 'max:30'],
            'Keterangan' => ['nullable', 'string', 'max:8000'],
            'Valas' => ['required', 'string', 'max:15'],
            // ... validasi lainnya
        ]);
        
        // Cek header transaksi
        $trans = $this->queryModel('dbtrans')->where('NoBukti', $request->NoBukti)->first();
        
        // Generate urutan
        $count = $this->queryModel('dbtransaksi')
            ->where('NoBukti', $request->NoBukti)
            ->orderBy('Urut', 'desc')
            ->value('Urut') ?? 0;
        $count++;
        
        // Hitung nilai Rupiah
        $DebetRp = floatval($request->Debet) * floatval($request->Kurs);
        
        // Call stored procedure
        DB::statement('EXEC sp_Transaksi ?, ?, ?, ...', [
            'Choice' => 'I',
            'NoBukti' => $request->NoBukti,
            // ... parameter lainnya
        ]);
        
        DB::commit();
        return true;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

**Penjelasan**:
1. **Transaction Management**: Menggunakan database transaction
2. **Data Validation**: Validasi input dengan Laravel validation
3. **Business Logic**: Kalkulasi dan generate urutan
4. **Stored Procedure**: Call SP untuk insert data
5. **Error Handling**: Rollback jika terjadi error

### Frontend Layer (`memorial.js`)

#### DataTable Initialization
```javascript
var datatableMain = $("#datatableMain").DataTable({
    ...mergeWithDefaultOptions({
        ajax: {
            url: $("#datatableMain").data("server"),
            type: 'GET',
            error: function (xhr, error, thrown) {
                console.log('DataTables error:', error);
            }
        },
        columns: [
            { data: null, className: "dt-control" }, // Expand control
            { data: "NoBukti" },
            { data: "Tanggal" },
            { data: "Note" },
            { data: "TotalD", className: "text-right" },
            { data: "TotalRp", className: "text-right" },
            // ... kolom lainnya
            {
                data: "action",
                orderable: false,
                searchable: false,
                className: "text-center parentBtnRow"
            }
        ],
        fnRowCallback: function (nRow, aData) {
            // Styling berdasarkan status otorisasi
            if (aData.IsOtorisasi1 == 1) {
                $(nRow).addClass("yellowClass");
            }
            if (aData.IsOtorisasi2 == 1) {
                $(nRow).addClass("redClass");
            }
        }
    })
});
```

**Penjelasan**:
1. **Server-Side Processing**: Data diambil via AJAX
2. **Column Configuration**: Definisi kolom dan format
3. **Row Styling**: Conditional styling berdasarkan data
4. **Action Column**: Tombol aksi dinamis

#### Event Handlers
```javascript
// Expand/Collapse Detail
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

// Authorization Change
$(document).on("change", 'input[name="IsOtorisasi2"],input[name="IsOtorisasi1"]', function (e) {
    e.preventDefault();
    let tr = $(this).closest("tr");
    const data = datatableMain.row(tr).data();
    confirm(
        "Apakah anda yakin akan mengubah status otorisasi?",
        function confirmed() {
            baseAjax({
                url: publicURL + "/accounting/transaksi-memorial/set-otorisasi",
                type: "POST",
                param: {
                    NoBukti: data.NoBukti,
                    otoLevel: $(e.target).attr("name"),
                    status: $(e.target).is(":checked") ? 1 : 0
                },
                successCallback: function (res) {
                    datatableMain.ajax.reload();
                }
            });
        }
    );
});
```

**Penjelasan**:
1. **Event Delegation**: Menggunakan $(document).on() untuk dynamic content
2. **Row Expansion**: Toggle detail view dengan child datatable
3. **Authorization**: Real-time update status otorisasi
4. **AJAX Communication**: Komunikasi dengan backend via custom AJAX function

#### Modal Management
```javascript
function modalAddEditMemorial(response, modal) {
    let data = response.res;
    var tahunPeriode = $("#spanYear").text();
    var bulanPeriode = $("#spanMonth").text();
    
    // Populate form jika edit mode
    if (Object.keys(response.res).length !== 0) {
        let Tanggal = moment(data.Tanggal).format("YYYY-MM-DD");
        modal.find('input[name="NoBukti"]').val(data.NoBukti);
        modal.find('input[name="Tanggal"]').val(Tanggal);
        modal.find('input[name="Note"]').val(data.Note);
    }
    
    // Event handler untuk perubahan tipe transaksi
    modal.on("change", 'select[name="TipeTransHd"]', function (e) {
        baseAjax({
            url: publicURL + "/accounting/transaksi-memorial/get-nomor-bukti",
            type: "POST",
            param: { tipe: $(this).val() },
            successCallback: function (res) {
                modal.find('input[name="NoBukti"]').val(res.NoBukti || '');
                modal.find('input[name="NoUrut"]').val(res.NoUrut || '');
                tahunPeriode = res.Tahun || '';
                bulanPeriode = res.Bulan || '';
            }
        });
    });
    
    // Validasi periode saat input tanggal
    modal.on("change", 'input[name="Tanggal"]', function (e) {
        let TanggalVal = $(this).val();
        if (TanggalVal != "") {
            let Tanggal = moment(TanggalVal);
            let Tahun = Tanggal.format("YYYY");
            let Bulan = Tanggal.format("MM");
            if (Tahun != tahunPeriode || Bulan != bulanPeriode) {
                $(this).val("");
                $(this).focus();
                alert("Tanggal tidak dalam periode aktif. Periode aktif adalah " + bulanPeriode + "/" + tahunPeriode);
            }
        }
    });
}
```

**Penjelasan**:
1. **Form Population**: Mengisi form berdasarkan data existing
2. **Dynamic Number Generation**: Auto-generate nomor bukti
3. **Period Validation**: Validasi tanggal terhadap periode aktif
4. **Event Binding**: Bind event ke modal elements

---

## DATABASE DAN MODEL

### Struktur Database Utama

#### Tabel `dbTrans` (Header Transaksi)
```sql
CREATE TABLE dbTrans (
    NoBukti VARCHAR(30) PRIMARY KEY,
    Tanggal DATE,
    Note NVARCHAR(500),
    TipeTransHd VARCHAR(10),
    PerkiraanHd VARCHAR(25),
    IsOtorisasi1 BIT DEFAULT 0,
    OtoUser1 VARCHAR(50),
    TglOto1 DATETIME,
    IsOtorisasi2 BIT DEFAULT 0,
    OtoUser2 VARCHAR(50),
    TglOto2 DATETIME,
    MaxOL INT DEFAULT -1
);
```

#### Tabel `dbTransaksi` (Detail Transaksi)
```sql
CREATE TABLE dbTransaksi (
    NoBukti VARCHAR(30),
    Urut INT,
    Perkiraan VARCHAR(25),
    Lawan VARCHAR(25),
    Keterangan NVARCHAR(8000),
    Debet DECIMAL(18,2),
    Kredit DECIMAL(18,2),
    Valas VARCHAR(15),
    Kurs DECIMAL(18,6),
    DebetRp DECIMAL(18,2),
    KreditRp DECIMAL(18,2),
    PRIMARY KEY (NoBukti, Urut)
);
```

### Model Classes

#### BaseRepository Pattern
```php
class BaseRepository implements BaseInterface
{
    protected $model;
    private $nmspc = 'App\Models\\';
    
    public function queryModel($model)
    {
        if (!property_exists($this->model, $model)) {
            $this->model->{$model} = strtoupper($model);
            $class = $this->nmspc . $this->model->{$model};
            $this->model->{$model} = new $class;
            return $this->model->{$model};
        }
        return $this->model->{$model};
    }
}
```

**Penjelasan**:
1. **Dynamic Model Loading**: Load model secara dinamis berdasarkan nama
2. **Namespace Management**: Otomatis resolve namespace model
3. **Instance Caching**: Cache instance model untuk performa

---

## FRONTEND DAN JAVASCRIPT

### Arsitektur JavaScript

#### ES6 Modules
```javascript
// base-function.js - Export utilities
export { publicURL, csfr_token };
export default $globalVariable;

// memorial.js - Import dan gunakan
import $globalVariable, { publicURL, csfr_token } from "../base-function.js";
```

#### Global Variable Object
```javascript
const $globalVariable = {
    baseAjax: function(options) {
        // Custom AJAX wrapper dengan error handling
        // Loading indicators
        // Timeout management
    },
    formAjax: function(options) {
        // Form submission dengan validasi
        // Serialize form data
        // Success/error callbacks
    },
    baseSwal: function(type, title, text) {
        // SweetAlert wrapper
    },
    // ... fungsi utility lainnya
};
```

#### DataTable Configuration
```javascript
const defaultOptionDatatable = {
    dom: "<'row'<'col button-table'B><'col-auto row'lf>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col'i><'col'p>>",
    scrollX: "100%",
    processing: true,
    serverSide: true,
    searchDelay: 1000,
    buttons: [
        {
            $keyButton: "tambah",
            onRender: function(options) {
                return `<button class="btn btn-success btn-sm mr-2 buttons-add ${options.className || ""}">
                    <i class="fa fa-plus mr-2"></i>Tambah
                </button>`;
            }
        },
        // ... tombol lainnya
    ]
};
```

### Custom DataTable Service

#### Server-Side Processing
```php
class CustomDataTable
{
    public function of($query, $forPrint = false)
    {
        $this->request = request();
        
        if ($this->request->has('start')) {
            $this->start = $this->request->start;
        }
        if ($this->request->has('length')) {
            $this->length = $this->request->length == 2147483647 ? -1 : $this->request->length;
        }
        
        return $this;
    }
    
    public function apply($smartSearch = false)
    {
        if ($smartSearch) {
            $this->smartSearch();
        } else {
            $this->search();
        }
        $this->order();
        
        $this->totalData = self::$query->count();
        
        if ($this->length != -1) {
            self::$query = self::$query->skip($this->start)->take($this->length);
        }
        
        return $this;
    }
}
```

**Penjelasan**:
1. **Flexible Query Input**: Support array, collection, query builder
2. **Search & Order**: Implementasi search dan sorting
3. **Pagination**: Handle start dan length parameter
4. **Smart Search**: Advanced search untuk query builder

---

## SISTEM KEAMANAN

### Authentication & Authorization

#### Middleware Permission
```php
Route::prefix('accounting')->middleware('policy:HASACCESS,02002')->group(function () {
    // Routes yang memerlukan akses khusus
});
```

#### Permission Check dalam Controller
```php
public function __construct(MemorialInterface $memorialRepository)
{
    $this->memorialRepository = $memorialRepository;
    $this->middleware(function ($request, $next) {
        $this->access = auth()->user()->getPermissionsName('02002');
        return $next($request);
    });
}

public function index()
{
    $hasOtorisasi1 = in_array('IsOtorisasi1', $this->access);
    $canKoreksi = in_array('ISKOREKSI', $this->access);
    // ... logic berdasarkan permission
}
```

#### CSRF Protection
```javascript
// Setiap AJAX request menyertakan CSRF token
headers: { "X-CSRF-TOKEN": csfr_token }
```

### Data Validation

#### Backend Validation
```php
$validatedData = $request->validate([
    'NoBukti' => ['required', 'string', 'max:30'],
    'Tanggal' => ['required', 'date', 'after_or_equal:date(' . $periode->TAHUN . '-' . $periode->BULAN . '-01)'],
    'Note' => ['required', 'string', 'max:500'],
    'Valas' => ['required', 'string', 'max:15'],
    'Kurs' => ['required', 'numeric'],
    'Debet' => ['required', 'numeric'],
    'Perkiraan' => ['required', 'string', 'max:25'],
    'Lawan' => ['required', 'string', 'max:25']
]);
```

#### Frontend Validation
```javascript
// Validasi periode aktif
if (Tahun != tahunPeriode || Bulan != bulanPeriode) {
    $(this).val("");
    $(this).focus();
    alert("Tanggal tidak dalam periode aktif. Periode aktif adalah " + bulanPeriode + "/" + tahunPeriode);
}

// Validasi perkiraan tidak sama dengan lawan
if ($request->Perkiraan === $request->Lawan) {
    throw new \Exception('Perkiraan and Lawan cannot be the same');
}
```

---

## WORKFLOW APLIKASI

### 1. Workflow Memorial

#### A. Tambah Memorial Baru
```
1. User klik tombol "Tambah"
2. System cek permission ISTAMBAH
3. Modal form terbuka
4. User pilih TipeTransHd
5. System generate NoBukti otomatis
6. User isi form header (Tanggal, Note)
7. User submit form
8. System validasi data
9. System simpan ke dbTrans
10. Modal tutup, DataTable refresh
```

#### B. Tambah Detail Memorial
```
1. User klik expand row
2. System load detail DataTable
3. User klik "Tambah" di detail
4. Modal detail form terbuka
5. User isi detail transaksi
6. System validasi (Perkiraan ≠ Lawan)
7. System hitung DebetRp = Debet × Kurs
8. System call sp_Transaksi
9. Detail DataTable refresh
```

#### C. Proses Otorisasi
```
1. User dengan hak IsOtorisasi1 centang checkbox
2. System konfirmasi perubahan
3. System update status otorisasi
4. System catat user dan timestamp
5. Row styling berubah (yellow/red)
6. Button Edit/Hapus disabled jika sudah diotorisasi
```

### 2. Workflow Export

#### Export PDF
```
1. User klik tombol PDF
2. System cek permission ISCETAK
3. System ambil data header dan detail
4. System generate PDF dengan DomPDF
5. System stream PDF ke browser
```

### 3. Data Flow

#### Request Flow
```
Browser → Route → Middleware → Controller → Repository → Model → Database
```

#### Response Flow
```
Database → Model → Repository → Controller → JSON/View → Browser
```

#### JavaScript Flow
```
User Action → Event Handler → AJAX Request → Backend Processing → JSON Response → UI Update
```

---

## KESIMPULAN

Proyek **bobajetbrain** adalah aplikasi akuntansi yang well-structured dengan implementasi:

### Kelebihan Arsitektur:
1. **Separation of Concerns**: Pemisahan yang jelas antara Controller, Repository, Model
2. **Reusable Components**: CustomDataTable, BaseRepository untuk reusability
3. **Security**: Multi-layer permission system
4. **Modularity**: JavaScript modules untuk maintainability
5. **Responsive Design**: AdminLTE template dengan Bootstrap

### Best Practices yang Diimplementasi:
1. **Repository Pattern**: Abstraksi database operations
2. **Service Layer**: Business logic terpisah dari controller
3. **Validation**: Multi-layer validation (frontend & backend)
4. **Transaction Management**: Database transaction untuk data consistency
5. **Error Handling**: Comprehensive error handling dan logging

### Teknologi Modern:
1. **ES6 Modules**: Modern JavaScript module system
2. **Server-Side DataTables**: Efficient large data handling
3. **AJAX Communication**: Seamless user experience
4. **PDF Generation**: Professional document output
5. **Responsive UI**: Mobile-friendly interface

Aplikasi ini menunjukkan implementasi enterprise-level accounting system dengan arsitektur yang scalable dan maintainable. 