# Tjboba Laravel Project Context

## Project Overview
Aplikasi Laravel untuk manajemen sistem keuangan dengan fitur lengkap termasuk SPK (Surat Perintah Kerja), kas bank, memorial, dan aktiva.

## Tech Stack
- **Backend**: Laravel 9.x + PHP 8.1+
- **Database**: SQL Server 2008 (Legacy compatibility)
- **Frontend**: AdminLTE + Bootstrap + DataTables
- **JavaScript**: jQuery + Chart.js
- **Architecture**: MVC + Repository Pattern

## Database Configuration
- **CRITICAL**: SQL Server 2008 compatibility required
- **Use**: `+` operator for string concatenation (NOT CONCAT)
- **Use**: `CASE WHEN` for conditional logic (NOT IIF)
- **Use**: `CONVERT()` for formatting (NOT FORMAT)
- **Avoid**: Modern SQL Server functions not available in 2008

## Code Standards
- Follow PSR-12 coding standards
- Use Repository Pattern for data access
- Type hints required for all methods
- Indonesian comments and documentation
- Eloquent ORM with proper relationships

## Project Structure
```
app/
├── Http/
│   ├── Controllers/     # Main controllers
│   ├── Requests/        # Form validation
│   └── Middleware/
├── Models/              # Eloquent models
├── Repositories/        # Repository pattern implementation
└── Services/            # Business logic

resources/
├── views/
│   ├── layouts/         # AdminLTE layouts
│   ├── kas_bank/        # Kas Bank module views
│   ├── memorial/        # Memorial module views
│   ├── aktiva/          # Aktiva module views
│   ├── spk/             # SPK module views
│   ├── inventory/       # Inventory module views
│   └── sales_order/     # Sales Order views
└── assets/              # CSS, JS, images

database/
├── migrations/          # Database migrations (SQL Server compatible)
├── seeders/             # Database seeders
└── factories/           # Model factories
```

## Core Modules
1. **Kas Bank**: Manajemen kas dan rekening bank
2. **Memorial**: Transaksi jurnal memorial
3. **Aktiva**: Manajemen aset dan depresiasi
4. **Berkas**: Sistem dokumen keuangan
5. **SPK**: Surat Perintah Kerja (Work Orders)
6. **Inventory**: Manajemen stok dan barang
7. **Sales Order**: Pengelolaan pesanan penjualan

## Development Guidelines

### Controller Pattern
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\KasBankRequest;
use App\Repositories\KasBankRepository;
use Illuminate\Http\Request;

class KasBankController extends Controller
{
    protected KasBankRepository $repository;

    public function __construct(KasBankRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(): View
    {
        $data = $this->repository->getAllWithPagination();
        return view('kas_bank.index', compact('data'));
    }
}
```

### Repository Pattern
```php
<?php

namespace App\Repositories;

use App\Models\KasBank;
use Illuminate\Pagination\LengthAwarePaginator;

class KasBankRepository
{
    protected KasBank $model;

    public function __construct(KasBank $model)
    {
        $this->model = $model;
    }

    public function getAllWithPagination(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }
}
```

### SQL Server 2008 Compatible Queries
```php
// ✅ GOOD - SQL Server 2008 compatible
DB::select("
    SELECT
        id,
        nama + ' - ' + kode AS display_name,
        CASE
            WHEN status = 1 THEN 'Aktif'
            ELSE 'Tidak Aktif'
        END AS status_text,
        CONVERT(VARCHAR(10), created_at, 103) AS tanggal_formatted
    FROM kas_bank
    WHERE deleted_at IS NULL
");

// ❌ BAD - Not compatible with SQL Server 2008
DB::select("
    SELECT
        id,
        CONCAT(nama, ' - ', kode) AS display_name,  -- ❌ CONCAT not available
        IIF(status = 1, 'Aktif', 'Tidak Aktif') AS status_text,  -- ❌ IIF not available
        FORMAT(created_at, 'dd/MM/yyyy') AS tanggal_formatted  -- ❌ FORMAT not available
    FROM kas_bank
    WHERE deleted_at IS NULL
");
```

### Model Relationships
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KasBank extends Model
{
    use SoftDeletes;

    protected $table = 'kas_bank';

    protected $fillable = [
        'nama',
        'kode',
        'saldo_awal',
        'status'
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
        'status' => 'boolean'
    ];

    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiKasBank::class, 'kas_bank_id');
    }
}
```

### Frontend (AdminLTE + DataTables)
```blade
{{-- resources/views/kas_bank/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <h1>Kas Bank <small>Manajemen Kas dan Bank</small></h1>
    </div>

    <section class="content">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Data Kas Bank</h3>
                <div class="box-tools">
                    <a href="{{ route('kas-bank.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambah Data
                    </a>
                </div>
            </div>

            <div class="box-body">
                <table id="kasbank-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Saldo</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#kasbank-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('kas-bank.data') }}",
        columns: [
            {data: 'kode', name: 'kode'},
            {data: 'nama', name: 'nama'},
            {data: 'saldo_awal', name: 'saldo_awal'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        language: {
            url: "{{ asset('plugins/datatables/Indonesian.json') }}"
        }
    });
});
</script>
@endpush
@endsection
```

## Environment Setup
- Development: `bobajetbrain/` (for coding and testing)
- Production: `me.pmk.my.id/` (READ ONLY reference)
- AI assistants should NOT modify production environment

## Migration Guidelines
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_bank', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['status', 'deleted_at']);
            $table->index('kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_bank');
    }
};
```

## API Development
- RESTful API endpoints for mobile/external integration
- JSON responses with consistent structure
- Proper HTTP status codes
- API versioning (v1/, v2/)

## Testing Guidelines
- Feature tests for controllers
- Unit tests for repositories and services
- Database testing with SQL Server compatible syntax
- MockData generation using factories

## Security Considerations
- CSRF protection on all forms
- Input validation using Form Requests
- SQL injection prevention (use Eloquent/Query Builder)
- File upload validation
- Role-based access control

## Performance Optimization
- Database query optimization for SQL Server 2008
- Proper indexing strategy
- Caching for frequently accessed data
- Image optimization for reports
- DataTables server-side processing for large datasets

## Common Patterns
1. **CRUD Operations**: Always use Repository pattern
2. **Form Validation**: Use dedicated Request classes
3. **Response Format**: Consistent JSON structure for AJAX
4. **Error Handling**: Proper exception handling with user-friendly messages
5. **Logging**: Use Laravel logging for debugging and monitoring

## AI Assistant Instructions
- Always suggest SQL Server 2008 compatible syntax
- Prioritize Repository pattern over direct Model usage
- Use Indonesian comments for business logic
- Follow AdminLTE component structure for views
- Consider legacy database constraints in suggestions
- Maintain backwards compatibility with existing codebase

## Context7 Integration
- Use "use context7" untuk dokumentasi library terbaru
- Kombinasi Context7 dengan project knowledge untuk best results
- Context7 support: Laravel, AdminLTE, DataTables, Chart.js, SQL Server

## AI Usage Patterns
- General library docs: "use context7"
- Project-specific data: MCP Tjboba tools
- Best practice: Combine both untuk comprehensive solutions