# GitHub Copilot Instructions - Tjboba Project

## Project Context
Tjboba adalah aplikasi Laravel untuk manajemen sistem keuangan dengan fitur lengkap termasuk SPK (Surat Perintah Kerja), kas bank, memorial, dan aktiva. Project ini dikonfigurasi untuk bekerja optimal dengan AI assistants seperti GitHub Copilot.

## Technical Stack
- **Framework**: Laravel 9.x
- **PHP Version**: 8.1+
- **Database**: SQL Server 2008 (Legacy compatibility required)
- **Frontend**: AdminLTE + Bootstrap + DataTables
- **JavaScript**: jQuery + Chart.js
- **Architecture**: MVC + Repository Pattern

## Critical Database Constraints
⚠️ **IMPORTANT**: SQL Server 2008 compatibility is MANDATORY

### ✅ Use These (SQL Server 2008 Compatible):
- String concatenation: `+` operator
- Conditional logic: `CASE WHEN ... THEN ... ELSE ... END`
- Date formatting: `CONVERT(VARCHAR, date, format_code)`
- String functions: `SUBSTRING()`, `LEN()`, `LTRIM()`, `RTRIM()`

### ❌ Avoid These (Not available in SQL Server 2008):
- `CONCAT()` function
- `IIF()` function  
- `FORMAT()` function
- `STRING_AGG()` function
- Window functions with advanced features

## Code Style Guidelines
- Follow PSR-12 coding standards
- Use Repository Pattern for data access layer
- Type hints required for all method parameters and return types
- Indonesian language for comments and business logic documentation
- English for technical documentation and variable names

## Project Modules
1. **Kas Bank** - Cash and bank account management
2. **Memorial** - Memorial journal transactions
3. **Aktiva** - Asset management and depreciation
4. **Berkas** - Financial document system
5. **SPK** - Work Orders (Surat Perintah Kerja)
6. **Inventory** - Stock and inventory management
7. **Sales Order** - Sales order processing

## Standard Code Patterns

### Controller Structure
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\{ModuleName}Request;
use App\Repositories\{ModuleName}Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class {ModuleName}Controller extends Controller
{
    public function __construct(
        protected {ModuleName}Repository $repository
    ) {}

    public function index(): View
    {
        return view('{module_name}.index');
    }

    public function store({ModuleName}Request $request): JsonResponse
    {
        $data = $this->repository->create($request->validated());
        return response()->json(['status' => 'success', 'data' => $data]);
    }
}
```

### Repository Pattern
```php
<?php

namespace App\Repositories;

use App\Models\{ModelName};
use Illuminate\Pagination\LengthAwarePaginator;

class {ModelName}Repository
{
    public function __construct(
        protected {ModelName} $model
    ) {}

    public function getAllWithPagination(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }
}
```

### Model Structure
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class {ModelName} extends Model
{
    use SoftDeletes;

    protected $fillable = [];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

### Blade View Structure (AdminLTE)
```blade
@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <h1>{Module Name} <small>{Description}</small></h1>
    </div>
    
    <section class="content">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Data {Module}</h3>
            </div>
            <div class="box-body">
                {{-- Content here --}}
            </div>
        </div>
    </section>
</div>
@endsection
```

### DataTables Implementation
```javascript
$('#data-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{!! route('{module}.data') !!}",
    columns: [
        {data: 'column1', name: 'column1'},
        {data: 'action', name: 'action', orderable: false, searchable: false}
    ],
    language: {
        url: "{!! asset('plugins/datatables/Indonesian.json') !!}"
    }
});
```

## SQL Server 2008 Query Examples

### ✅ Correct Usage:
```sql
-- String concatenation
SELECT nama + ' - ' + kode AS display_name FROM table_name;

-- Conditional logic
SELECT 
    CASE 
        WHEN status = 1 THEN 'Aktif'
        ELSE 'Tidak Aktif'
    END AS status_text
FROM table_name;

-- Date formatting
SELECT CONVERT(VARCHAR(10), created_at, 103) AS tanggal FROM table_name;
```

### ❌ Incorrect Usage:
```sql
-- Don't use these in SQL Server 2008
SELECT CONCAT(nama, ' - ', kode) AS display_name; -- ❌
SELECT IIF(status = 1, 'Aktif', 'Tidak Aktif'); -- ❌ 
SELECT FORMAT(created_at, 'dd/MM/yyyy'); -- ❌
```

## Environment Configuration
- **Development**: Use `bobajetbrain/` environment for development and testing
- **Production**: `me.pmk.my.id/` is READ ONLY reference - do not suggest modifications
- **Database**: Always consider SQL Server 2008 limitations in suggestions

## Form Request Validation
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {ModuleName}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'field_name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'field_name.required' => 'Field wajib diisi.',
        ];
    }
}
```

## API Response Format
```php
// Success response
return response()->json([
    'status' => 'success',
    'message' => 'Data berhasil disimpan',
    'data' => $data
]);

// Error response
return response()->json([
    'status' => 'error',
    'message' => 'Terjadi kesalahan',
    'errors' => $errors
], 422);
```

## Migration Guidelines
- Always use SQL Server compatible column types
- Add proper indexes for performance
- Use `softDeletes()` for most tables
- Consider existing database structure

## Testing Patterns
- Feature tests for HTTP endpoints
- Unit tests for repositories and services
- Use database transactions for test isolation
- Mock external services

## Security Considerations
- Always use Form Request validation
- Implement CSRF protection
- Use Eloquent ORM to prevent SQL injection
- Validate file uploads properly
- Implement proper authorization checks

## Performance Guidelines
- Use eager loading to prevent N+1 queries
- Implement pagination for large datasets  
- Use DataTables server-side processing
- Cache frequently accessed data
- Optimize database queries for SQL Server 2008

## AI Coding Assistance Rules
1. **Always** suggest SQL Server 2008 compatible syntax
2. **Prefer** Repository pattern over direct Model access
3. **Use** Indonesian comments for business logic
4. **Follow** AdminLTE structure for frontend components
5. **Consider** legacy database constraints in all suggestions
6. **Maintain** consistency with existing codebase patterns
7. **Prioritize** backwards compatibility and stability