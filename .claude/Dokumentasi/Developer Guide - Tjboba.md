# 👨‍💻 Developer Guide - Tjboba

Panduan lengkap untuk developer yang ingin berkontribusi atau mengembangkan aplikasi Tjboba.

## 📋 Daftar Isi

- [Architecture Overview](#-architecture-overview)
- [Code Standards](#-code-standards)
- [Database Design](#-database-design)
- [Development Workflow](#-development-workflow)
- [API Development](#-api-development)
- [Frontend Development](#-frontend-development)
- [Testing Strategy](#-testing-strategy)
- [Deployment Guide](#-deployment-guide)
- [AI Integration](#-ai-integration)
- [Contributing Guidelines](#-contributing-guidelines)

## 🏗️ Architecture Overview

### System Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│   (AdminLTE)    │◄──►│   (Laravel)     │◄──►│  (SQL Server)   │
│   + Bootstrap   │    │   + Repository  │    │   2008+         │
│   + jQuery      │    │   + Services    │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   JavaScript    │    │   API Layer     │    │   Cache Layer   │
│   - Chart.js    │    │   - RESTful     │    │   - File Cache  │
│   - DataTables  │    │   - JSON        │    │   - Redis       │
│   - AJAX        │    │   - Validation  │    │   (Optional)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Design Patterns

#### 1. Repository Pattern
Memisahkan logic akses data dari business logic.

```php
<?php

namespace App\Repositories;

use App\Models\KasBank;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface KasBankRepositoryInterface
{
    public function getAllWithPagination(int $perPage = 15): LengthAwarePaginator;
    public function getById(int $id): ?KasBank;
    public function create(array $data): KasBank;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getActiveAccounts(): Collection;
}

class KasBankRepository implements KasBankRepositoryInterface
{
    public function __construct(
        protected KasBank $model
    ) {}

    public function getAllWithPagination(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['transaksi' => fn($q) => $q->latest()->limit(5)])
            ->latest()
            ->paginate($perPage);
    }

    public function getById(int $id): ?KasBank
    {
        return $this->model->with('transaksi')->find($id);
    }

    public function create(array $data): KasBank
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

    public function getActiveAccounts(): Collection
    {
        return $this->model->where('status', true)->get();
    }
}
```

#### 2. Service Layer Pattern
Business logic terpisah dari controllers.

```php
<?php

namespace App\Services;

use App\Repositories\KasBankRepositoryInterface;
use App\Repositories\TransaksiRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class KasBankService
{
    public function __construct(
        protected KasBankRepositoryInterface $kasBankRepo,
        protected TransaksiRepositoryInterface $transaksiRepo
    ) {}

    public function createWithInitialBalance(array $data): array
    {
        DB::beginTransaction();
        
        try {
            // Create kas bank account
            $kasBank = $this->kasBankRepo->create([
                'kode' => $data['kode'],
                'nama' => $data['nama'],
                'saldo_awal' => $data['saldo_awal'],
                'status' => $data['status'] ?? true
            ]);

            // Create initial balance transaction if saldo_awal > 0
            if ($data['saldo_awal'] > 0) {
                $this->transaksiRepo->create([
                    'kas_bank_id' => $kasBank->id,
                    'tanggal' => now(),
                    'jenis' => 'debit',
                    'jumlah' => $data['saldo_awal'],
                    'keterangan' => 'Saldo awal kas bank',
                    'reference_type' => 'initial_balance'
                ]);
            }

            DB::commit();
            
            return [
                'status' => 'success',
                'data' => $kasBank->load('transaksi'),
                'message' => 'Kas bank berhasil dibuat'
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            
            return [
                'status' => 'error',
                'message' => 'Gagal membuat kas bank: ' . $e->getMessage()
            ];
        }
    }

    public function calculateCurrentBalance(int $kasBankId): float
    {
        $kasBank = $this->kasBankRepo->getById($kasBankId);
        
        if (!$kasBank) {
            throw new Exception('Kas bank tidak ditemukan');
        }

        $totalDebit = $this->transaksiRepo->getTotalByType($kasBankId, 'debit');
        $totalKredit = $this->transaksiRepo->getTotalByType($kasBankId, 'kredit');
        
        return $kasBank->saldo_awal + $totalDebit - $totalKredit;
    }
}
```

## 📏 Code Standards

### PHP Standards (PSR-12)

#### Class Structure
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\KasBankRequest;
use App\Services\KasBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Kas Bank API Controller
 * 
 * Handles CRUD operations for kas bank accounts
 * Supports both web and API interfaces
 */
class KasBankController extends BaseApiController
{
    public function __construct(
        protected KasBankService $kasBankService
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of kas bank accounts
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $data = $this->kasBankService->getAllWithPagination($perPage);
        
        return $this->successResponse($data, 'Data kas bank berhasil diambil');
    }

    /**
     * Store a newly created kas bank account
     */
    public function store(KasBankRequest $request): JsonResponse
    {
        $result = $this->kasBankService->createWithInitialBalance(
            $request->validated()
        );
        
        if ($result['status'] === 'error') {
            return $this->errorResponse($result['message'], 422);
        }
        
        return $this->successResponse(
            $result['data'], 
            $result['message'], 
            201
        );
    }

    /**
     * Display the specified kas bank account
     */
    public function show(int $id): JsonResponse
    {
        $kasBank = $this->kasBankService->getWithTransactions($id);
        
        if (!$kasBank) {
            return $this->errorResponse('Kas bank tidak ditemukan', 404);
        }
        
        return $this->successResponse($kasBank);
    }

    /**
     * Update the specified kas bank account
     */
    public function update(KasBankRequest $request, int $id): JsonResponse
    {
        $result = $this->kasBankService->update($id, $request->validated());
        
        if ($result['status'] === 'error') {
            return $this->errorResponse($result['message'], 422);
        }
        
        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Remove the specified kas bank account
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->kasBankService->softDelete($id);
        
        if ($result['status'] === 'error') {
            return $this->errorResponse($result['message'], 422);
        }
        
        return $this->successResponse(null, $result['message']);
    }
}
```

#### Naming Conventions
```php
// Classes: PascalCase
class KasBankController
class TransaksiMemorialService
class AktivaRepository

// Methods: camelCase
public function getAllWithPagination()
public function createWithValidation()
public function updateStatusBatch()

// Variables: camelCase
$kasBankData
$totalTransaksi
$isActiveAccount

// Constants: SCREAMING_SNAKE_CASE
const MAX_UPLOAD_SIZE = 5242880;
const DEFAULT_PAGINATION_SIZE = 15;
const TRANSACTION_TYPES = ['debit', 'kredit'];

// Database columns: snake_case
created_at, updated_at, kas_bank_id, saldo_awal
```

### Documentation Standards

#### DocBlocks
```php
/**
 * Calculate monthly depreciation for aktiva
 * 
 * Menggunakan metode straight-line depreciation
 * Formula: (Harga Perolehan - Nilai Residu) / Umur Ekonomis (bulan)
 * 
 * @param float $hargaPerolehan Harga perolehan aset
 * @param float $nilaiResidu Nilai residu akhir masa manfaat
 * @param int $umurEkonomisBulan Umur ekonomis dalam bulan
 * @return float Nilai depresiasi bulanan
 * 
 * @throws InvalidArgumentException Jika parameter tidak valid
 * @throws DivisionByZeroError Jika umur ekonomis = 0
 * 
 * @example
 * $depresiasi = $this->calculateMonthlyDepreciation(
 *     hargaPerolehan: 120000000,
 *     nilaiResidu: 20000000,
 *     umurEkonomisBulan: 60
 * );
 * // Returns: 1666666.67
 */
public function calculateMonthlyDepreciation(
    float $hargaPerolehan, 
    float $nilaiResidu, 
    int $umurEkonomisBulan
): float {
    if ($umurEkonomisBulan <= 0) {
        throw new InvalidArgumentException('Umur ekonomis harus lebih dari 0');
    }
    
    if ($hargaPerolehan < $nilaiResidu) {
        throw new InvalidArgumentException('Harga perolehan tidak boleh kurang dari nilai residu');
    }
    
    return ($hargaPerolehan - $nilaiResidu) / $umurEkonomisBulan;
}
```

## 🗄️ Database Design

### SQL Server 2008 Compatibility

#### Migration Guidelines
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
            $table->id(); // BIGINT IDENTITY
            $table->string('kode', 20)->unique(); // VARCHAR(20) UNIQUE
            $table->string('nama', 100); // VARCHAR(100)
            $table->decimal('saldo_awal', 15, 2)->default(0); // DECIMAL(15,2)
            $table->boolean('status')->default(true); // BIT
            $table->text('keterangan')->nullable(); // TEXT NULL
            $table->timestamps(); // DATETIME2
            $table->softDeletes(); // DATETIME2 NULL
            
            // Indexes untuk performance
            $table->index(['status', 'deleted_at'], 'idx_kas_bank_status_deleted');
            $table->index('kode', 'idx_kas_bank_kode');
            $table->index('created_at', 'idx_kas_bank_created');
        });
        
        // Add table comment (SQL Server specific)
        DB::statement("
            EXEC sys.sp_addextendedproperty 
                @name=N'MS_Description', 
                @value=N'Master data kas dan bank perusahaan', 
                @level0type=N'SCHEMA', @level0name=N'dbo', 
                @level1type=N'TABLE', @level1name=N'kas_bank'
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_bank');
    }
};
```

#### Query Patterns untuk SQL Server 2008
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KasBank extends Model
{
    // ✅ SQL Server 2008 Compatible Queries
    
    public function scopeWithCurrentBalance($query)
    {
        return $query->addSelect([
            '*',
            DB::raw("
                saldo_awal + 
                ISNULL((
                    SELECT SUM(CASE WHEN jenis = 'debit' THEN jumlah ELSE -jumlah END)
                    FROM transaksi_kas_bank tkb 
                    WHERE tkb.kas_bank_id = kas_bank.id 
                    AND tkb.deleted_at IS NULL
                ), 0) as current_balance
            ")
        ]);
    }
    
    public function scopeActiveAccounts($query)
    {
        return $query->where('status', 1)
                    ->whereNull('deleted_at');
    }
    
    public function getDisplayNameAttribute(): string
    {
        // ✅ Use + operator for concatenation (SQL Server 2008)
        return DB::selectOne("
            SELECT kode + ' - ' + nama as display_name 
            FROM kas_bank 
            WHERE id = ?
        ", [$this->id])->display_name;
    }
    
    // ❌ Avoid these patterns in SQL Server 2008:
    // CONCAT(kode, ' - ', nama) -- Not available
    // IIF(status = 1, 'Aktif', 'Tidak Aktif') -- Not available  
    // FORMAT(created_at, 'dd/MM/yyyy') -- Not available
}
```

### Database Schema

#### Core Tables Structure
```sql
-- Kas Bank (Master)
CREATE TABLE kas_bank (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    saldo_awal DECIMAL(15,2) DEFAULT 0,
    status BIT DEFAULT 1,
    keterangan TEXT NULL,
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE(),
    deleted_at DATETIME2 NULL
);

-- Transaksi Kas Bank
CREATE TABLE transaksi_kas_bank (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    kas_bank_id BIGINT NOT NULL,
    tanggal DATE NOT NULL,
    jenis VARCHAR(10) NOT NULL, -- 'debit' or 'kredit'
    jumlah DECIMAL(15,2) NOT NULL,
    keterangan VARCHAR(255) NOT NULL,
    reference_type VARCHAR(50) NULL, -- 'spk', 'memorial', 'manual'
    reference_id BIGINT NULL,
    created_by BIGINT NULL,
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE(),
    deleted_at DATETIME2 NULL,
    
    FOREIGN KEY (kas_bank_id) REFERENCES kas_bank(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Memorial (Jurnal Umum)
CREATE TABLE memorial (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    nomor_jurnal VARCHAR(50) UNIQUE NOT NULL,
    tanggal DATE NOT NULL,
    keterangan TEXT NOT NULL,
    total_debit DECIMAL(15,2) NOT NULL,
    total_kredit DECIMAL(15,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'draft', -- 'draft', 'approved', 'posted'
    approved_by BIGINT NULL,
    approved_at DATETIME2 NULL,
    created_by BIGINT NOT NULL,
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE(),
    deleted_at DATETIME2 NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Memorial Detail
CREATE TABLE memorial_detail (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    memorial_id BIGINT NOT NULL,
    account_code VARCHAR(20) NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    debit DECIMAL(15,2) DEFAULT 0,
    kredit DECIMAL(15,2) DEFAULT 0,
    keterangan VARCHAR(255) NULL,
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE(),
    
    FOREIGN KEY (memorial_id) REFERENCES memorial(id) ON DELETE CASCADE
);

-- Aktiva (Assets)
CREATE TABLE aktiva (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    kode_aktiva VARCHAR(30) UNIQUE NOT NULL,
    nama_aktiva VARCHAR(150) NOT NULL,
    kategori VARCHAR(50) NOT NULL, -- 'tanah', 'bangunan', 'kendaraan', 'peralatan'
    tanggal_perolehan DATE NOT NULL,
    harga_perolehan DECIMAL(15,2) NOT NULL,
    nilai_residu DECIMAL(15,2) DEFAULT 0,
    umur_ekonomis_bulan INT NOT NULL,
    metode_depresiasi VARCHAR(30) DEFAULT 'straight_line',
    lokasi VARCHAR(100) NULL,
    kondisi VARCHAR(20) DEFAULT 'baik', -- 'baik', 'rusak_ringan', 'rusak_berat'
    status VARCHAR(20) DEFAULT 'aktif', -- 'aktif', 'dijual', 'hilang', 'rusak'
    created_by BIGINT NOT NULL,
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE(),
    deleted_at DATETIME2 NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- SPK (Surat Perintah Kerja)
CREATE TABLE spk (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    nomor_spk VARCHAR(50) UNIQUE NOT NULL,
    tanggal_spk DATE NOT NULL,
    jenis_pekerjaan VARCHAR(100) NOT NULL,
    deskripsi_pekerjaan TEXT NOT NULL,
    lokasi_pekerjaan VARCHAR(150) NOT NULL,
    target_selesai DATE NOT NULL,
    estimasi_biaya DECIMAL(15,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'draft', -- 'draft', 'approved', 'in_progress', 'completed', 'cancelled'
    prioritas VARCHAR(10) DEFAULT 'normal', -- 'low', 'normal', 'high', 'urgent'
    assigned_to BIGINT NULL,
    approved_by BIGINT NULL,
    completed_at DATETIME2 NULL,
    created_by BIGINT NOT NULL,
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE(),
    deleted_at DATETIME2 NULL,
    
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

## 🔄 Development Workflow

### Git Workflow

#### Branch Strategy
```bash
# Main branches
main/master    # Production-ready code
develop        # Integration branch
staging        # Pre-production testing

# Feature branches
feature/kas-bank-module
feature/memorial-approval
feature/aktiva-depreciation

# Hotfix branches  
hotfix/security-patch
hotfix/critical-bug-fix

# Release branches
release/v1.0.0
release/v1.1.0
```

#### Commit Convention
```bash
# Format: type(scope): description

# Types:
feat(kas-bank): add automatic balance calculation
fix(memorial): resolve posting validation issue
docs(api): update endpoint documentation
style(ui): improve table responsive layout
refactor(repository): optimize query performance
test(spk): add unit tests for SPK service
chore(deps): update Laravel to 9.52.0

# Examples:
git commit -m "feat(kas-bank): implement real-time balance updates"
git commit -m "fix(memorial): handle negative balance validation"
git commit -m "docs(readme): add installation instructions"
```

### Code Review Process

#### Pull Request Template
```markdown
## 📋 Description
Brief description of changes made.

## 🎯 Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] This change requires a documentation update

## 🧪 Testing
- [ ] Unit tests pass
- [ ] Feature tests pass
- [ ] Manual testing completed
- [ ] SQL Server 2008 compatibility verified

## 📝 Checklist
- [ ] My code follows the PSR-12 style guidelines
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes

## 📸 Screenshots (if applicable)
Add screenshots to help explain your changes.

## 🔗 Related Issues
Fixes #(issue number)
```

### Local Development Setup

#### Environment Configuration
```bash
# Copy environment file
cp .env.example .env.local

# Development-specific settings
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug

# Database for development
DB_DATABASE=tjboba_dev
DB_USERNAME=dev_user
DB_PASSWORD=dev_password

# Enable query logging
DB_LOG_QUERIES=true

# Development cache settings
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

#### Docker Development Environment
```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.dev
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www/html
      - ./storage:/var/www/html/storage
    environment:
      - APP_ENV=local
      - DB_HOST=sqlserver
    depends_on:
      - sqlserver
      
  sqlserver:
    image: mcr.microsoft.com/mssql/server:2019-latest
    environment:
      SA_PASSWORD: "TjbobaDB123!"
      ACCEPT_EULA: "Y"
    ports:
      - "1433:1433"
    volumes:
      - sqlserver_data:/var/opt/mssql

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

volumes:
  sqlserver_data:
```

## 🔌 API Development

### RESTful API Standards

#### Endpoint Naming
```php
// Resource-based URLs
GET    /api/kas-bank              # List all kas bank accounts
POST   /api/kas-bank              # Create new kas bank account
GET    /api/kas-bank/{id}         # Get specific kas bank account
PUT    /api/kas-bank/{id}         # Update kas bank account
DELETE /api/kas-bank/{id}         # Delete kas bank account

// Nested resources
GET    /api/kas-bank/{id}/transaksi           # Get transactions for specific account
POST   /api/kas-bank/{id}/transaksi           # Create transaction for specific account

// Custom actions
POST   /api/kas-bank/{id}/freeze              # Freeze account
POST   /api/memorial/{id}/approve             # Approve memorial
GET    /api/aktiva/{id}/depreciation          # Get depreciation schedule
```

#### Response Format Standards
```php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    /**
     * Success response format
     */
    protected function successResponse(
        mixed $data = null, 
        string $message = 'Success', 
        int $status = 200
    ): JsonResponse {
        $response = [
            'status' => 'success',
            'message' => $message,
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Error response format
     */
    protected function errorResponse(
        string $message = 'Error', 
        int $status = 400, 
        array $errors = []
    ): JsonResponse {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Validation error response
     */
    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            422,
            $errors
        );
    }
}
```

#### API Versioning
```php
// Route versioning
Route::prefix('api/v1')->group(function () {
    Route::apiResource('kas-bank', KasBankController::class);
    Route::apiResource('memorial', MemorialController::class);
});

Route::prefix('api/v2')->group(function () {
    Route::apiResource('kas-bank', V2\KasBankController::class);
});

// Header versioning
class ApiVersionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $version = $request->header('Accept-Version', 'v1');
        
        // Set version context
        app()->instance('api.version', $version);
        
        return $next($request);
    }
}
```

### Authentication & Authorization

#### Sanctum Setup
```php
// config/sanctum.php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),
    
    'expiration' => env('SANCTUM_EXPIRATION', 60 * 24), // 24 hours
];

// API Authentication
class AuthController extends BaseApiController
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        
        if (!Auth::attempt($credentials)) {
            return $this->errorResponse('Invalid credentials', 401);
        }
        
        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;
        
        return $this->successResponse([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration') * 60
        ], 'Login successful');
    }
    
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        
        return $this->successResponse(null, 'Logged out successfully');
    }
}
```

## 🎨 Frontend Development

### AdminLTE Integration

#### Layout Structure
```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Tjboba') }} - @yield('title', 'Dashboard')</title>
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        @include('layouts.partials.navbar')
        
        <!-- Main Sidebar -->
        @include('layouts.partials.sidebar')
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>
        
        <!-- Footer -->
        @include('layouts.partials.footer')
        
        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
    
    <!-- Custom Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
```

#### DataTables Integration
```blade
{{-- resources/views/kas_bank/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Kas Bank')
@section('page-title', 'Manajemen Kas Bank')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Kas Bank</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Kas Bank</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" onclick="showCreateModal()">
                        <i class="fas fa-plus"></i> Tambah Data
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                <table id="kas-bank-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Saldo Awal</th>
                            <th>Saldo Saat Ini</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
@include('kas_bank.partials.modal')
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#kas-bank-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('kas-bank.data') }}",
            type: 'GET'
        },
        columns: [
            {data: 'kode', name: 'kode'},
            {data: 'nama', name: 'nama'},
            {
                data: 'saldo_awal', 
                name: 'saldo_awal',
                render: function(data) {
                    return formatCurrency(data);
                }
            },
            {
                data: 'current_balance', 
                name: 'current_balance',
                render: function(data) {
                    return formatCurrency(data);
                }
            },
            {
                data: 'status', 
                name: 'status',
                render: function(data) {
                    return data ? 
                        '<span class="badge badge-success">Aktif</span>' : 
                        '<span class="badge badge-danger">Tidak Aktif</span>';
                }
            },
            {
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false
            }
        ],
        language: {
            url: "{{ asset('plugins/datatables/Indonesian.json') }}"
        },
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });

    // Refresh table function
    window.refreshTable = function() {
        table.ajax.reload(null, false);
    };
});

// Format currency helper
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Show create modal
function showCreateModal() {
    $('#kasbank-modal').modal('show');
    $('#kasbank-form')[0].reset();
    $('#modal-title').text('Tambah Kas Bank');
    $('#kasbank-id').val('');
}

// Show edit modal
function editKasBank(id) {
    $.get(`/kas-bank/${id}/edit`, function(data) {
        $('#kasbank-modal').modal('show');
        $('#modal-title').text('Edit Kas Bank');
        $('#kasbank-id').val(data.id);
        $('#kode').val(data.kode);
        $('#nama').val(data.nama);
        $('#saldo_awal').val(data.saldo_awal);
        $('#status').val(data.status ? '1' : '0');
        $('#keterangan').val(data.keterangan);
    });
}

// Delete kas bank
function deleteKasBank(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus kas bank "${nama}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/kas-bank/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire('Terhapus!', response.message, 'success');
                    refreshTable();
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON.message, 'error');
                }
            });
        }
    });
}
</script>
@endpush
```

### JavaScript Architecture

#### Module Pattern
```javascript
// resources/js/modules/kas-bank.js
const KasBankModule = (function() {
    'use strict';
    
    // Private variables
    let dataTable = null;
    let currentEditId = null;
    
    // Private methods
    function initializeDataTable() {
        dataTable = $('#kas-bank-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/api/kas-bank/datatable',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`
                }
            },
            columns: [
                {data: 'kode', name: 'kode'},
                {data: 'nama', name: 'nama'},
                {data: 'saldo_awal', name: 'saldo_awal', render: formatCurrency},
                {data: 'current_balance', name: 'current_balance', render: formatCurrency},
                {data: 'status', name: 'status', render: renderStatus},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            language: {
                url: '/assets/datatables/Indonesian.json'
            }
        });
    }
    
    function formatCurrency(data) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(data);
    }
    
    function renderStatus(data) {
        return data ? 
            '<span class="badge badge-success">Aktif</span>' : 
            '<span class="badge badge-danger">Tidak Aktif</span>';
    }
    
    function bindEvents() {
        // Form submission
        $('#kasbank-form').on('submit', handleFormSubmit);
        
        // Modal events
        $('#kasbank-modal').on('hidden.bs.modal', resetForm);
        
        // Search functionality
        $('#search-input').on('keyup', function() {
            dataTable.search(this.value).draw();
        });
    }
    
    function handleFormSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const url = currentEditId ? 
            `/api/kas-bank/${currentEditId}` : 
            '/api/kas-bank';
        const method = currentEditId ? 'PUT' : 'POST';
        
        // Show loading
        showLoading(true);
        
        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showNotification('success', response.message);
                $('#kasbank-modal').modal('hide');
                dataTable.ajax.reload();
            },
            error: function(xhr) {
                handleFormErrors(xhr.responseJSON);
            },
            complete: function() {
                showLoading(false);
            }
        });
    }
    
    function handleFormErrors(response) {
        if (response.errors) {
            Object.keys(response.errors).forEach(field => {
                const input = $(`[name="${field}"]`);
                input.addClass('is-invalid');
                input.next('.invalid-feedback').text(response.errors[field][0]);
            });
        } else {
            showNotification('error', response.message);
        }
    }
    
    function resetForm() {
        $('#kasbank-form')[0].reset();
        $('#kasbank-form .is-invalid').removeClass('is-invalid');
        currentEditId = null;
    }
    
    function showLoading(show) {
        if (show) {
            $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        } else {
            $('#submit-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
        }
    }
    
    function showNotification(type, message) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        
        Toast.fire({
            icon: type,
            title: message
        });
    }
    
    // Public methods
    return {
        init: function() {
            initializeDataTable();
            bindEvents();
        },
        
        create: function() {
            currentEditId = null;
            $('#modal-title').text('Tambah Kas Bank');
            $('#kasbank-modal').modal('show');
        },
        
        edit: function(id) {
            currentEditId = id;
            $('#modal-title').text('Edit Kas Bank');
            
            $.get(`/api/kas-bank/${id}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`
                }
            })
            .done(function(response) {
                const data = response.data;
                $('#kode').val(data.kode);
                $('#nama').val(data.nama);
                $('#saldo_awal').val(data.saldo_awal);
                $('#status').val(data.status ? '1' : '0');
                $('#keterangan').val(data.keterangan);
                $('#kasbank-modal').modal('show');
            });
        },
        
        delete: function(id, nama) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus kas bank "${nama}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/api/kas-bank/${id}`,
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            showNotification('success', response.message);
                            dataTable.ajax.reload();
                        },
                        error: function(xhr) {
                            showNotification('error', xhr.responseJSON.message);
                        }
                    });
                }
            });
        },
        
        refresh: function() {
            if (dataTable) {
                dataTable.ajax.reload();
            }
        }
    };
})();

// Initialize when document ready
$(document).ready(function() {
    KasBankModule.init();
});
```

## 🧪 Testing Strategy

### Unit Testing

#### Repository Testing
```php
<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Models\KasBank;
use App\Repositories\KasBankRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KasBankRepositoryTest extends TestCase
{
    use RefreshDatabase;
    
    protected KasBankRepository $repository;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new KasBankRepository(new KasBank());
    }
    
    public function test_can_create_kas_bank(): void
    {
        $data = [
            'kode' => 'KB001',
            'nama' => 'Kas Utama',
            'saldo_awal' => 1000000,
            'status' => true
        ];
        
        $kasBank = $this->repository->create($data);
        
        $this->assertInstanceOf(KasBank::class, $kasBank);
        $this->assertEquals('KB001', $kasBank->kode);
        $this->assertEquals('Kas Utama', $kasBank->nama);
        $this->assertEquals(1000000, $kasBank->saldo_awal);
        $this->assertTrue($kasBank->status);
    }
    
    public function test_can_get_active_accounts(): void
    {
        // Create test data
        KasBank::factory()->create(['status' => true]);
        KasBank::factory()->create(['status' => true]);
        KasBank::factory()->create(['status' => false]);
        
        $activeAccounts = $this->repository->getActiveAccounts();
        
        $this->assertCount(2, $activeAccounts);
        $activeAccounts->each(function ($account) {
            $this->assertTrue($account->status);
        });
    }
    
    public function test_can_get_paginated_data(): void
    {
        KasBank::factory()->count(25)->create();
        
        $paginatedData = $this->repository->getAllWithPagination(10);
        
        $this->assertEquals(10, $paginatedData->perPage());
        $this->assertEquals(25, $paginatedData->total());
        $this->assertEquals(3, $paginatedData->lastPage());
    }
}
```

#### Service Testing
```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\KasBankService;
use App\Repositories\KasBankRepositoryInterface;
use App\Repositories\TransaksiRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class KasBankServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected KasBankService $service;
    protected $kasBankRepo;
    protected $transaksiRepo;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->kasBankRepo = Mockery::mock(KasBankRepositoryInterface::class);
        $this->transaksiRepo = Mockery::mock(TransaksiRepositoryInterface::class);
        
        $this->service = new KasBankService(
            $this->kasBankRepo,
            $this->transaksiRepo
        );
    }
    
    public function test_create_with_initial_balance_success(): void
    {
        $data = [
            'kode' => 'KB001',
            'nama' => 'Kas Utama',
            'saldo_awal' => 1000000,
            'status' => true
        ];
        
        $kasBank = new \App\Models\KasBank($data);
        $kasBank->id = 1;
        
        $this->kasBankRepo
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($kasBank);
            
        $this->transaksiRepo
            ->shouldReceive('create')
            ->once()
            ->with([
                'kas_bank_id' => 1,
                'tanggal' => now(),
                'jenis' => 'debit',
                'jumlah' => 1000000,
                'keterangan' => 'Saldo awal kas bank',
                'reference_type' => 'initial_balance'
            ]);
        
        $result = $this->service->createWithInitialBalance($data);
        
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Kas bank berhasil dibuat', $result['message']);
    }
    
    public function test_calculate_current_balance(): void
    {
        $kasBank = new \App\Models\KasBank([
            'saldo_awal' => 1000000
        ]);
        $kasBank->id = 1;
        
        $this->kasBankRepo
            ->shouldReceive('getById')
            ->with(1)
            ->andReturn($kasBank);
            
        $this->transaksiRepo
            ->shouldReceive('getTotalByType')
            ->with(1, 'debit')
            ->andReturn(500000);
            
        $this->transaksiRepo
            ->shouldReceive('getTotalByType')
            ->with(1, 'kredit')
            ->andReturn(200000);
        
        $balance = $this->service->calculateCurrentBalance(1);
        
        // 1000000 + 500000 - 200000 = 1300000
        $this->assertEquals(1300000, $balance);
    }
}
```

### Feature Testing

#### API Endpoint Testing
```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\KasBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class KasBankApiTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }
    
    public function test_can_list_kas_bank(): void
    {
        KasBank::factory()->count(5)->create();
        
        $response = $this->getJson('/api/kas-bank');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'kode',
                                'nama',
                                'saldo_awal',
                                'current_balance',
                                'status',
                                'created_at',
                                'updated_at'
                            ]
                        ],
                        'meta' => [
                            'current_page',
                            'total',
                            'per_page'
                        ]
                    ]
                ]);
    }
    
    public function test_can_create_kas_bank(): void
    {
        $data = [
            'kode' => 'KB001',
            'nama' => 'Kas Utama',
            'saldo_awal' => 1000000,
            'status' => true,
            'keterangan' => 'Kas bank utama perusahaan'
        ];
        
        $response = $this->postJson('/api/kas-bank', $data);
        
        $response->assertStatus(201)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Kas bank berhasil dibuat'
                ])
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'kode',
                        'nama',
                        'saldo_awal',
                        'status'
                    ]
                ]);
        
        $this->assertDatabaseHas('kas_bank', [
            'kode' => 'KB001',
            'nama' => 'Kas Utama',
            'saldo_awal' => 1000000
        ]);
    }
    
    public function test_validation_errors_on_create(): void
    {
        $response = $this->postJson('/api/kas-bank', []);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'kode',
                    'nama',
                    'saldo_awal'
                ]);
    }
    
    public function test_can_update_kas_bank(): void
    {
        $kasBank = KasBank::factory()->create([
            'kode' => 'KB001',
            'nama' => 'Kas Lama'
        ]);
        
        $updateData = [
            'kode' => 'KB002',
            'nama' => 'Kas Baru',
            'saldo_awal' => 2000000,
            'status' => false
        ];
        
        $response = $this->putJson("/api/kas-bank/{$kasBank->id}", $updateData);
        
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success'
                ]);
        
        $this->assertDatabaseHas('kas_bank', [
            'id' => $kasBank->id,
            'kode' => 'KB002',
            'nama' => 'Kas Baru',
            'saldo_awal' => 2000000,
            'status' => false
        ]);
    }
    
    public function test_can_delete_kas_bank(): void
    {
        $kasBank = KasBank::factory()->create();
        
        $response = $this->deleteJson("/api/kas-bank/{$kasBank->id}");
        
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success'
                ]);
        
        $this->assertSoftDeleted('kas_bank', [
            'id' => $kasBank->id
        ]);
    }
    
    public function test_cannot_access_without_authentication(): void
    {
        Sanctum::actingAs(null);
        
        $response = $this->getJson('/api/kas-bank');
        
        $response->assertStatus(401);
    }
}
```

### Database Testing

#### Migration Testing
```php
<?php

namespace Tests\Feature\Database;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_kas_bank_table_structure(): void
    {
        $this->assertTrue(Schema::hasTable('kas_bank'));
        
        $columns = [
            'id', 'kode', 'nama', 'saldo_awal', 'status', 
            'keterangan', 'created_at', 'updated_at', 'deleted_at'
        ];
        
        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('kas_bank', $column),
                "Column {$column} does not exist in kas_bank table"
            );
        }
    }
    
    public function test_kas_bank_indexes(): void
    {
        $indexes = Schema::getIndexes('kas_bank');
        $indexNames = array_column($indexes, 'name');
        
        // Check unique index on kode
        $this->assertContains('kas_bank_kode_unique', $indexNames);
        
        // Check composite index on status and deleted_at
        $this->assertContains('idx_kas_bank_status_deleted', $indexNames);
    }
    
    public function test_foreign_key_constraints(): void
    {
        // Test transaksi_kas_bank foreign key
        $foreignKeys = Schema::getForeignKeys('transaksi_kas_bank');
        
        $this->assertNotEmpty($foreignKeys);
        
        $kasBankFk = collect($foreignKeys)->first(function ($fk) {
            return $fk['columns'] === ['kas_bank_id'];
        });
        
        $this->assertNotNull($kasBankFk);
        $this->assertEquals('kas_bank', $kasBankFk['foreign_table']);
        $this->assertEquals(['id'], $kasBankFk['foreign_columns']);
    }
}
```

## 🚀 Deployment Guide

### Production Deployment

#### Server Requirements
```bash
# Server specifications
- CPU: 2+ cores
- RAM: 4GB minimum, 8GB recommended
- Storage: 50GB+ SSD
- OS: Ubuntu 20.04 LTS or CentOS 8

# Software requirements
- PHP 8.1+
- Nginx 1.18+ or Apache 2.4+
- SQL Server 2008+
- Redis (optional, for caching)
- Supervisor (for queue workers)
```

#### Deployment Script
```bash
#!/bin/bash
# deploy.sh

set -e

PROJECT_DIR="/var/www/tjboba"
BACKUP_DIR="/backup/tjboba"
USER="www-data"

echo "Starting deployment..."

# Create backup
echo "Creating backup..."
DATE=$(date +"%Y%m%d_%H%M%S")
mkdir -p $BACKUP_DIR
tar -czf "$BACKUP_DIR/tjboba_backup_$DATE.tar.gz" $PROJECT_DIR

# Pull latest changes
echo "Pulling latest changes..."
cd $PROJECT_DIR
git pull origin main

# Install/update dependencies
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Clear caches
echo "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Cache optimization
echo "Optimizing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets
echo "Building assets..."
npm ci --production
npm run build

# Set permissions
echo "Setting permissions..."
chown -R $USER:$USER $PROJECT_DIR
chmod -R 755 $PROJECT_DIR
chmod -R 775 $PROJECT_DIR/storage
chmod -R 775 $PROJECT_DIR/bootstrap/cache

# Restart services
echo "Restarting services..."
systemctl reload nginx
systemctl restart php8.1-fpm
supervisorctl restart tjboba-worker:*

# Clear OPcache
echo "Clearing OPcache..."
php artisan optimize:clear

echo "Deployment completed successfully!"

# Health check
echo "Running health check..."
curl -f http://localhost/health || exit 1

echo "Health check passed!"
```

#### CI/CD Pipeline (GitHub Actions)
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      sqlserver:
        image: mcr.microsoft.com/mssql/server:2019-latest
        env:
          SA_PASSWORD: TestPassword123!
          ACCEPT_EULA: Y
        ports:
          - 1433:1433
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: pdo_sqlsrv, sqlsrv
        
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
      
    - name: Copy environment file
      run: cp .env.example .env.testing
      
    - name: Generate key
      run: php artisan key:generate --env=testing
      
    - name: Run migrations
      run: php artisan migrate --env=testing
      
    - name: Run tests
      run: php artisan test

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
    - name: Deploy to server
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /var/www/tjboba
          ./deploy.sh
```

### Monitoring & Logging

#### Application Monitoring
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestLogging
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        // Log request
        Log::info('Request started', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id()
        ]);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2);
        
        // Log response
        Log::info('Request completed', [
            'status' => $response->getStatusCode(),
            'execution_time_ms' => $executionTime,
            'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
        ]);
        
        return $response;
    }
}
```

#### Performance Monitoring
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthCheck extends Command
{
    protected $signature = 'health:check';
    protected $description = 'Check application health status';
    
    public function handle(): int
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue()
        ];
        
        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');
        
        if ($allHealthy) {
            $this->info('✅ All systems healthy');
            return 0;
        } else {
            $this->error('❌ Some systems unhealthy');
            $this->table(['Service', 'Status', 'Message'], 
                collect($checks)->map(fn($check, $service) => [
                    $service, 
                    $check['status'], 
                    $check['message']
                ])->toArray()
            );
            return 1;
        }
    }
    
    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'ok', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function checkCache(): array
    {
        try {
            Cache::put('health_check', time(), 60);
            $value = Cache::get('health_check');
            return ['status' => 'ok', 'message' => 'Cache working properly'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function checkStorage(): array
    {
        try {
            $path = storage_path('logs/laravel.log');
            $writable = is_writable(dirname($path));
            
            if ($writable) {
                return ['status' => 'ok', 'message' => 'Storage is writable'];
            } else {
                return ['status' => 'error', 'message' => 'Storage not writable'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function checkQueue(): array
    {
        try {
            // Check if supervisor is running queue workers
            $output = shell_exec('supervisorctl status tjboba-worker:*');
            
            if (strpos($output, 'RUNNING') !== false) {
                return ['status' => 'ok', 'message' => 'Queue workers running'];
            } else {
                return ['status' => 'warning', 'message' => 'Queue workers not running'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
```

## 🤖 AI Integration

### Context7 Usage Examples

#### Laravel Development
```php
// Prompt example with Context7
"Create a Laravel 9 controller for memorial transactions with validation, repository pattern, and SQL Server 2008 compatibility. use context7"

// Context7 will provide:
// - Latest Laravel 9 controller patterns
// - Current validation rules syntax
// - Best practices for repository pattern
// - SQL Server compatibility guidelines
```

#### AdminLTE Components
```javascript
// Prompt example
"Create AdminLTE DataTable with server-side processing and custom filters using latest AdminLTE 3.x syntax. use context7"

// Context7 provides current AdminLTE documentation for:
// - DataTable initialization
// - Server-side processing setup
// - Custom filter implementation
// - Responsive design patterns
```

### GitHub Copilot Integration

The `.github/copilot-instructions.md` file provides context for GitHub Copilot to understand:
- Project structure and conventions
- SQL Server 2008 compatibility requirements
- Repository pattern implementation
- Indonesian documentation standards

### Cursor AI Integration

The `.cursorrules` file enables Cursor to:
- Understand project architecture
- Suggest SQL Server 2008 compatible code
- Follow established patterns
- Generate appropriate documentation

## 🤝 Contributing Guidelines

### Code Contribution Process

1. **Fork the repository**
2. **Create feature branch**: `git checkout -b feature/new-feature`
3. **Follow coding standards**: PSR-12, type hints, documentation
4. **Write tests**: Unit tests and feature tests
5. **Verify SQL Server 2008 compatibility**
6. **Update documentation** if needed
7. **Submit pull request** with detailed description

### Code Review Checklist

#### For Reviewers
- [ ] Code follows PSR-12 standards
- [ ] All methods have proper type hints
- [ ] SQL queries are SQL Server 2008 compatible
- [ ] Tests are included and passing
- [ ] Documentation is updated
- [ ] No security vulnerabilities
- [ ] Performance considerations addressed
- [ ] Indonesian comments for business logic

#### For Contributors
- [ ] Self-review completed
- [ ] Tests written and passing
- [ ] Documentation updated
- [ ] Breaking changes documented
- [ ] Migration scripts provided if needed
- [ ] Backward compatibility maintained

### Issue Reporting

When reporting issues, include:
- **Environment details**: PHP version, SQL Server version, OS
- **Steps to reproduce**: Clear step-by-step instructions
- **Expected behavior**: What should happen
- **Actual behavior**: What actually happens
- **Error messages**: Full error messages and stack traces
- **Screenshots**: If UI-related

### Documentation Standards

- **Code comments**: Indonesian for business logic, English for technical
- **API documentation**: OpenAPI/Swagger format
- **User guides**: Step-by-step with screenshots
- **Developer docs**: Architecture decisions, patterns used
- **Deployment guides**: Environment-specific instructions

---

**Tjboba Developer Guide v1.0**  
*For questions or clarifications, contact the development team or create an issue on GitHub.*