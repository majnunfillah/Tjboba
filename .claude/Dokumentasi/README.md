# 🏢 Tjboba - Sistem Manajemen Keuangan

[![Laravel](https://img.shields.io/badge/Laravel-9.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![SQL Server](https://img.shields.io/badge/SQL%20Server-2008+-yellow.svg)](https://microsoft.com/sql-server)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Aplikasi Laravel untuk manajemen sistem keuangan dengan fitur lengkap termasuk SPK (Surat Perintah Kerja), kas bank, memorial, dan aktiva. Dikonfigurasi untuk bekerja optimal dengan AI assistants seperti GitHub Copilot, Cursor, dan Claude.

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Cara Menjalankan](#-cara-menjalankan)
- [Struktur Project](#-struktur-project)
- [API Documentation](#-api-documentation)
- [Developer Guide](#-developer-guide)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [License](#-license)

## ✨ Fitur Utama

### 💰 Modul Keuangan
- **Kas Bank**: Manajemen kas dan rekening bank
- **Memorial**: Transaksi jurnal memorial
- **Aktiva**: Manajemen aset dan depresiasi
- **Berkas**: Sistem dokumen keuangan

### 📋 Modul Operasional
- **SPK**: Surat Perintah Kerja (Work Orders)
- **Inventory**: Manajemen stok dan barang
- **Sales Order**: Pengelolaan pesanan penjualan

### 🎨 User Interface
- **AdminLTE**: Modern admin dashboard
- **DataTables**: Advanced table management
- **Chart.js**: Interactive data visualization
- **Responsive Design**: Mobile-friendly interface

## 🛠️ Persyaratan Sistem

### Software Requirements
- **PHP**: 8.1 atau lebih tinggi
- **Composer**: 2.x
- **Node.js**: 18.x atau lebih tinggi
- **NPM**: 8.x atau lebih tinggi

### Database
- **SQL Server**: 2008 atau lebih tinggi
- **SQL Server Driver**: Microsoft ODBC Driver for SQL Server

### Web Server
- **Apache**: 2.4+ dengan mod_rewrite
- **Nginx**: 1.18+ 
- **PHP Extensions**:
  - OpenSSL
  - PDO
  - PDO SQL Server
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/majnunfillah/Tjboba.git
cd Tjboba
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Build assets
npm run build
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

## ⚙️ Konfigurasi

### Database Configuration
Edit file `.env` sesuai dengan konfigurasi SQL Server Anda:

```env
# Database Configuration
DB_CONNECTION=sqlsrv
DB_HOST=localhost
DB_PORT=1433
DB_DATABASE=tjboba_db
DB_USERNAME=sa
DB_PASSWORD=your_password

# SQL Server 2008 Compatibility
DB_TRUST_SERVER_CERTIFICATE=true
DB_ENCRYPT=false
```

### Application Configuration
```env
# Application
APP_NAME="Tjboba"
APP_ENV=local
APP_KEY=base64:your_app_key
APP_DEBUG=true
APP_URL=http://localhost

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail Configuration (Optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

## 🏃‍♂️ Cara Menjalankan

### Development Server
```bash
# Start Laravel development server
php artisan serve

# Akses aplikasi di:
# http://localhost:8000
```

### Production Deployment

#### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/tjboba/public
    
    <Directory /path/to/tjboba/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/tjboba_error.log
    CustomLog ${APACHE_LOG_DIR}/tjboba_access.log combined
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/tjboba/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Performance Optimization
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

## 📁 Struktur Project

```
Tjboba/
├── app/
│   ├── Http/
│   │   ├── Controllers/         # Application controllers
│   │   ├── Middleware/          # Custom middleware
│   │   └── Requests/            # Form request validation
│   ├── Models/                  # Eloquent models
│   ├── Repositories/            # Repository pattern implementation
│   └── Services/                # Business logic services
├── config/                      # Configuration files
├── database/
│   ├── migrations/              # Database migrations
│   ├── seeders/                 # Database seeders
│   └── factories/               # Model factories
├── public/                      # Web accessible files
├── resources/
│   ├── views/                   # Blade templates
│   ├── js/                      # JavaScript files
│   └── sass/                    # Sass stylesheets
├── routes/
│   ├── web.php                  # Web routes
│   ├── api.php                  # API routes
│   └── console.php              # Console commands
├── storage/                     # Storage files
├── tests/                       # Application tests
├── .cursorrules                 # Cursor AI configuration
├── .github/
│   └── copilot-instructions.md  # GitHub Copilot context
└── mcp-server/                  # MCP server for Claude AI
```

## 🔗 API Documentation

### Authentication
Semua API endpoint memerlukan authentication menggunakan Laravel Sanctum.

```bash
# Login
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}

# Response
{
    "token": "your_api_token",
    "user": { ... }
}
```

### Core Endpoints

#### Kas Bank API
```bash
# Get all kas bank
GET /api/kas-bank

# Create kas bank
POST /api/kas-bank
{
    "kode": "KB001",
    "nama": "Kas Utama",
    "saldo_awal": 1000000,
    "status": true
}

# Update kas bank
PUT /api/kas-bank/{id}

# Delete kas bank
DELETE /api/kas-bank/{id}
```

#### Memorial API
```bash
# Get memorial transactions
GET /api/memorial

# Create memorial transaction
POST /api/memorial
{
    "tanggal": "2024-01-15",
    "keterangan": "Transaksi memorial",
    "total": 500000,
    "details": [...]
}
```

### API Response Format
```json
{
    "status": "success|error",
    "message": "Response message",
    "data": { ... },
    "meta": {
        "current_page": 1,
        "total": 100,
        "per_page": 15
    }
}
```

## 👨‍💻 Developer Guide

### Code Standards
- **PSR-12**: PHP coding standards
- **Repository Pattern**: Data access layer
- **Type Hints**: All method parameters and returns
- **Indonesian Comments**: Business logic documentation

### SQL Server 2008 Compatibility

#### ✅ Use These:
```php
// String concatenation
DB::select("SELECT nama + ' - ' + kode AS display_name FROM table");

// Conditional logic  
DB::select("SELECT CASE WHEN status = 1 THEN 'Aktif' ELSE 'Tidak Aktif' END FROM table");

// Date formatting
DB::select("SELECT CONVERT(VARCHAR(10), created_at, 103) AS tanggal FROM table");
```

#### ❌ Avoid These:
```php
// Not available in SQL Server 2008
DB::select("SELECT CONCAT(nama, kode)");     // ❌
DB::select("SELECT IIF(status = 1, 'A', 'B')"); // ❌
DB::select("SELECT FORMAT(date, 'dd/MM/yyyy')"); // ❌
```

### Creating New Module

#### 1. Generate Files
```bash
# Create controller
php artisan make:controller ModuleController

# Create model
php artisan make:model Module -m

# Create repository
php artisan make:repository ModuleRepository

# Create form request
php artisan make:request ModuleRequest
```

#### 2. Controller Pattern
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModuleRequest;
use App\Repositories\ModuleRepository;

class ModuleController extends Controller
{
    public function __construct(
        protected ModuleRepository $repository
    ) {}

    public function index(): View
    {
        return view('module.index');
    }

    public function store(ModuleRequest $request): JsonResponse
    {
        $data = $this->repository->create($request->validated());
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
```

#### 3. Repository Pattern
```php
<?php

namespace App\Repositories;

use App\Models\Module;

class ModuleRepository
{
    public function __construct(
        protected Module $model
    ) {}

    public function getAllWithPagination(int $perPage = 15)
    {
        return $this->model->latest()->paginate($perPage);
    }

    public function create(array $data): Module
    {
        return $this->model->create($data);
    }
}
```

### Testing

#### Feature Tests
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ModuleTest

# Generate test coverage
php artisan test --coverage
```

#### Test Example
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModuleTest extends TestCase
{
    public function test_can_create_module(): void
    {
        $response = $this->post('/api/modules', [
            'name' => 'Test Module',
            'status' => true
        ]);

        $response->assertStatus(201)
                ->assertJson(['status' => 'success']);
    }
}
```

### AI Development Integration

#### GitHub Copilot
- File `.github/copilot-instructions.md` berisi context project
- Auto-suggest SQL Server 2008 compatible syntax
- Repository pattern recommendations

#### Cursor AI
- File `.cursorrules` untuk project context
- Context7 integration untuk up-to-date docs
- Command: "use context7" dalam prompt

#### Claude AI
- MCP Server untuk real-time project access
- Database query capabilities
- File system integration

## 🔧 Troubleshooting

### Common Issues

#### Database Connection Error
```bash
# Check SQL Server service
services.msc → SQL Server (MSSQLSERVER)

# Test connection
sqlcmd -S localhost -U sa -P your_password -Q "SELECT 1"

# Check PHP SQL Server extension
php -m | grep sqlsrv
```

#### Permission Issues
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### Performance Issues
```bash
# Enable query logging
DB_LOG_QUERIES=true

# Check slow queries
php artisan telescope:install
```

### Debug Mode
```bash
# Enable debug mode
APP_DEBUG=true

# View logs
tail -f storage/logs/laravel.log

# Database query logging
DB::enableQueryLog();
// Your queries here
dd(DB::getQueryLog());
```

## 🤝 Contributing

### Development Workflow
1. Fork repository
2. Create feature branch: `git checkout -b feature/new-feature`
3. Commit changes: `git commit -am 'Add new feature'`
4. Push branch: `git push origin feature/new-feature`
5. Submit Pull Request

### Code Review Checklist
- [ ] PSR-12 compliance
- [ ] SQL Server 2008 compatibility
- [ ] Type hints on all methods
- [ ] Unit tests included
- [ ] Documentation updated
- [ ] No breaking changes

### Issue Reporting
Gunakan template issue di GitHub dengan informasi:
- Laravel version
- PHP version
- SQL Server version
- Steps to reproduce
- Expected vs actual behavior

## 📞 Support

### Development Team
- **Project Lead**: [Maintainer Name]
- **Email**: support@tjboba.com
- **GitHub Issues**: [Issues Page]

### Documentation
- **API Docs**: `/docs/api`
- **Developer Wiki**: [Wiki Link]
- **Video Tutorials**: [YouTube Channel]

### Community
- **Discord**: [Discord Server]
- **Telegram**: [Telegram Group]
- **Forum**: [Discussion Forum]

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Laravel Framework**: Elegant PHP framework
- **AdminLTE**: Beautiful admin template
- **Microsoft**: SQL Server database
- **Upstash**: Context7 MCP server
- **Community**: All contributors and users

---

**Made with ❤️ by Tjboba Development Team**