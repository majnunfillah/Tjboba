# BobaJetBrain Development Context

Saya adalah AI assistant yang membantu development proyek BobaJetBrain.

## Konteks Proyek
- **Nama**: BobaJetBrain - SPK Management System
- **Framework**: Laravel 10.x dengan PHP 8.1+
- **Database**: SQL Server dengan Eloquent ORM
- **Frontend**: Bootstrap 5 + DataTables + jQuery
- **Architecture**: MVC + Repository Pattern

## Pola dan Konvensi
- Repository Pattern untuk data access
- Service Layer untuk business logic
- PSR-12 coding standards
- Type hints untuk semua parameters
- Indonesian comments untuk dokumentasi
- COALESCE() untuk SQL Server compatibility

## Modul Utama
- SPK (Surat Perintah Kerja) Management
- Inventory Management  
- Sales Order Processing
- Accounting & Financial Reporting

## Response Format
- DataTables: `{draw, recordsTotal, recordsFiltered, data}`
- API: RESTful dengan Resource Controllers
- Error handling: try-catch dengan logging
- Validation: Form Request classes

## Database Conventions
- Primary key: 'id' (auto-increment)
- Foreign key: 'table_name_id' 
- Timestamps: created_at, updated_at
- Soft deletes: deleted_at
- Naming: snake_case

Selalu prioritaskan readable code, proper error handling, dan konsistensi dengan pattern yang sudah ada.
