# 🛠️ Panduan Instalasi Tjboba

Dokumen ini berisi langkah-langkah detail untuk menginstall dan menjalankan aplikasi Tjboba.

## 📋 Persiapan Awal

### Persyaratan Sistem
- **Operating System**: Windows 10/11, macOS 10.15+, Ubuntu 18.04+
- **RAM**: Minimum 4GB, Recommended 8GB
- **Storage**: Minimum 2GB free space
- **Internet**: Untuk download dependencies

### Software Prerequisites

#### 1. PHP 8.1+
**Windows:**
```powershell
# Download dari php.net atau gunakan XAMPP
# Atau install via Chocolatey
choco install php

# Verify installation
php --version
```

**macOS:**
```bash
# Install via Homebrew
brew install php@8.1

# Verify installation
php --version
```

**Ubuntu:**
```bash
# Update package list
sudo apt update

# Install PHP and extensions
sudo apt install php8.1 php8.1-cli php8.1-common php8.1-zip php8.1-mbstring php8.1-xml php8.1-bcmath

# Verify installation
php --version
```

#### 2. Composer
```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Windows: Download dari getcomposer.org

# Verify installation
composer --version
```

#### 3. Node.js & NPM
```bash
# Download dari nodejs.org
# Atau install via package manager

# Windows (Chocolatey)
choco install nodejs

# macOS (Homebrew)
brew install node

# Ubuntu
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify installation
node --version
npm --version
```

#### 4. SQL Server

**Windows:**
1. Download SQL Server 2019 Developer Edition
2. Install dengan default settings
3. Install SQL Server Management Studio (SSMS)

**macOS/Linux:**
```bash
# Install using Docker
docker run -e "ACCEPT_EULA=Y" -e "SA_PASSWORD=YourPassword123" \
   -p 1433:1433 --name sqlserver \
   -d mcr.microsoft.com/mssql/server:2019-latest
```

#### 5. PHP SQL Server Extensions (DETAIL LENGKAP)

**🪟 WINDOWS (Step-by-Step):**

##### A. Download Microsoft Drivers
```powershell
# 1. Download Microsoft ODBC Driver for SQL Server
# URL: https://docs.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server
# Pilih: Microsoft ODBC Driver 18 for SQL Server

# 2. Download Microsoft Drivers for PHP for SQL Server
# URL: https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
# Pilih versi sesuai PHP Anda (PHP 8.1)
```

##### B. Install ODBC Driver
```powershell
# Jalankan installer ODBC Driver
# msodbcsql.msi - Install dengan default settings
# RESTART KOMPUTER setelah install ODBC Driver
```

##### C. Install PHP Extensions
```powershell
# 1. Extract file zip PHP drivers
# 2. Copy file yang sesuai ke PHP ext directory

# Untuk PHP 8.1 Thread Safe (TS):
copy php_sqlsrv_81_ts.dll "C:\php\ext\"
copy php_pdo_sqlsrv_81_ts.dll "C:\php\ext\"

# Untuk PHP 8.1 Non Thread Safe (NTS):
copy php_sqlsrv_81_nts.dll "C:\php\ext\"
copy php_pdo_sqlsrv_81_nts.dll "C:\php\ext\"
```

##### D. Configure php.ini
```ini
# Edit file php.ini (biasanya di C:\php\php.ini)
# Tambahkan di bagian extensions:

# Untuk Thread Safe
extension=php_sqlsrv_81_ts.dll
extension=php_pdo_sqlsrv_81_ts.dll

# ATAU untuk Non Thread Safe
extension=php_sqlsrv_81_nts.dll
extension=php_pdo_sqlsrv_81_nts.dll

# Uncomment extension directory jika belum:
extension_dir = "C:\php\ext"
```

##### E. Restart Web Server
```powershell
# Restart Apache atau Nginx
# Atau restart PHP-FPM service
```

**🐧 LINUX (Ubuntu/Debian):**

##### A. Install Microsoft ODBC Driver
```bash
# 1. Update package list
sudo apt-get update

# 2. Install prerequisites
sudo apt-get install -y curl apt-transport-https gnupg lsb-release

# 3. Add Microsoft repository
curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | sudo gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg
curl https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/prod.list | sudo tee /etc/apt/sources.list.d/mssql-release.list

# 4. Update package list again
sudo apt-get update

# 5. Install ODBC Driver
sudo ACCEPT_EULA=Y apt-get install -y msodbcsql18
sudo ACCEPT_EULA=Y apt-get install -y mssql-tools18

# 6. Install unixODBC development headers
sudo apt-get install -y unixodbc-dev
```

##### B. Install PHP Extensions
```bash
# 1. Install development tools
sudo apt-get install -y php8.1-dev php8.1-xml php-pear build-essential

# 2. Install SQL Server extensions via PECL
sudo pecl channel-update pecl.php.net
sudo pecl install sqlsrv
sudo pecl install pdo_sqlsrv

# 3. Add extensions to php.ini
echo "extension=sqlsrv.so" | sudo tee -a /etc/php/8.1/cli/php.ini
echo "extension=pdo_sqlsrv.so" | sudo tee -a /etc/php/8.1/cli/php.ini

# For Apache
echo "extension=sqlsrv.so" | sudo tee -a /etc/php/8.1/apache2/php.ini
echo "extension=pdo_sqlsrv.so" | sudo tee -a /etc/php/8.1/apache2/php.ini

# For Nginx (FPM)
echo "extension=sqlsrv.so" | sudo tee -a /etc/php/8.1/fpm/php.ini
echo "extension=pdo_sqlsrv.so" | sudo tee -a /etc/php/8.1/fpm/php.ini
```

**🍎 macOS:**

##### A. Install ODBC Driver
```bash
# 1. Install Homebrew (jika belum ada)
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# 2. Install Microsoft ODBC Driver
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Microsoft/homebrew-mssql-release/master/install.sh)"

# 3. Install ODBC Driver
brew tap microsoft/mssql-release https://github.com/Microsoft/homebrew-mssql-release
brew update
HOMEBREW_ACCEPT_EULA=Y brew install msodbcsql18 mssql-tools18
```

##### B. Install PHP Extensions
```bash
# 1. Install development tools
brew install autoconf pkg-config

# 2. Install extensions
sudo pecl install sqlsrv
sudo pecl install pdo_sqlsrv

# 3. Add to php.ini
echo "extension=sqlsrv.so" >> $(php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||")
echo "extension=pdo_sqlsrv.so" >> $(php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||")
```

#### 🔧 VERIFIKASI INSTALASI

##### Test Extensions Loaded
```bash
# Check if extensions are loaded
php -m | grep sqlsrv
php -m | grep pdo_sqlsrv

# Should output:
# pdo_sqlsrv
# sqlsrv
```

##### Test Connection Script
```php
<?php
// test_connection.php

echo "=== PHP SQL Server Connection Test ===\n";

// 1. Check if extensions are loaded
echo "1. Checking extensions...\n";
if (extension_loaded('sqlsrv')) {
    echo "   ✅ sqlsrv extension loaded\n";
} else {
    echo "   ❌ sqlsrv extension NOT loaded\n";
}

if (extension_loaded('pdo_sqlsrv')) {
    echo "   ✅ pdo_sqlsrv extension loaded\n";
} else {
    echo "   ❌ pdo_sqlsrv extension NOT loaded\n";
}

// 2. Test ODBC drivers
echo "\n2. Available ODBC drivers:\n";
if (function_exists('sqlsrv_client_info')) {
    $drivers = sqlsrv_client_info();
    foreach ($drivers as $key => $value) {
        echo "   {$key}: {$value}\n";
    }
} else {
    echo "   ❌ sqlsrv functions not available\n";
}

// 3. Test PDO drivers
echo "\n3. Available PDO drivers:\n";
$drivers = PDO::getAvailableDrivers();
foreach ($drivers as $driver) {
    echo "   - {$driver}\n";
}

if (in_array('sqlsrv', $drivers)) {
    echo "   ✅ PDO SQL Server driver available\n";
} else {
    echo "   ❌ PDO SQL Server driver NOT available\n";
}

// 4. Test actual connection (ganti dengan setting Anda)
echo "\n4. Testing database connection...\n";
$serverName = "localhost";
$database = "master"; // test dengan database master dulu
$username = "sa";
$password = "YourPassword123";

try {
    $pdo = new PDO("sqlsrv:server={$serverName};database={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✅ Database connection successful!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT @@VERSION as version");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   SQL Server Version: " . substr($result['version'], 0, 50) . "...\n";
    
} catch (PDOException $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
```

```bash
# Run test script
php test_connection.php
```

## 🚀 Instalasi Aplikasi

### Step 1: Clone Repository
```bash
# Clone dari GitHub
git clone https://github.com/majnunfillah/Tjboba.git

# Masuk ke directory
cd Tjboba

# Check file structure
ls -la
```

### Step 2: Install Dependencies

#### PHP Dependencies
```bash
# Install menggunakan Composer
composer install

# Untuk production (tanpa dev dependencies)
composer install --no-dev --optimize-autoloader
```

#### JavaScript Dependencies
```bash
# Install NPM packages
npm install

# Build assets untuk development
npm run dev

# Build assets untuk production
npm run build
```

### Step 3: Environment Configuration

#### Copy Environment File
```bash
# Copy template environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### Edit Configuration
Edit file `.env` sesuai environment Anda:

```env
# Application Settings
APP_NAME="Tjboba"
APP_ENV=local                    # local/staging/production
APP_KEY=base64:generated_key
APP_DEBUG=true                   # false untuk production
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=sqlsrv
DB_HOST=localhost
DB_PORT=1433
DB_DATABASE=tjboba_db
DB_USERNAME=sa
DB_PASSWORD=YourStrongPassword123

# SQL Server Specific
DB_TRUST_SERVER_CERTIFICATE=true
DB_ENCRYPT=false

# Cache Configuration
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Queue Configuration
QUEUE_CONNECTION=sync

# Mail Configuration (Optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tjboba.com"
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

### Step 4: Database Setup (DETAIL LENGKAP)

#### 🗄️ A. INSTALL SQL SERVER

**🪟 Windows (Recommended):**

##### 1. Download SQL Server
```powershell
# Option 1: SQL Server 2019 Developer Edition (FREE)
# URL: https://www.microsoft.com/en-us/sql-server/sql-server-downloads
# Pilih: Developer edition (Free)

# Option 2: SQL Server Express (FREE, Limited)
# URL: https://www.microsoft.com/en-us/sql-server/sql-server-editions-express
```

##### 2. Install SQL Server
```powershell
# 1. Jalankan installer
# 2. Pilih "Custom" installation
# 3. Download dan extract files
# 4. Pilih "New SQL Server stand-alone installation"
# 5. Accept license terms
# 6. Pilih features:
#    ✅ Database Engine Services
#    ✅ SQL Server Replication (optional)
#    ✅ Client Tools Connectivity
# 7. Instance Configuration:
#    • Default instance: MSSQLSERVER
#    • Instance root directory: default
# 8. Server Configuration:
#    • SQL Server Database Engine: Automatic
#    • SQL Server Agent: Manual (optional)
# 9. Database Engine Configuration:
#    • Authentication Mode: Mixed Mode (PENTING!)
#    • sa password: Buat password kuat (min 8 karakter)
#    • Add current user as administrator
# 10. Complete installation
```

##### 3. Install SQL Server Management Studio (SSMS)
```powershell
# Download SSMS dari:
# https://docs.microsoft.com/en-us/sql/ssms/download-sql-server-management-studio-ssms

# Install dengan default settings
```

**🐧 Linux (Docker - Lebih Mudah):**

##### 1. Install Docker
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y docker.io docker-compose

# Start Docker service
sudo systemctl start docker
sudo systemctl enable docker

# Add user to docker group
sudo usermod -aG docker $USER
# Logout and login again
```

##### 2. Run SQL Server Container
```bash
# Pull SQL Server 2019 image
docker pull mcr.microsoft.com/mssql/server:2019-latest

# Run SQL Server container
docker run -e "ACCEPT_EULA=Y" \
    -e "SA_PASSWORD=TjbobaDB123!" \
    -p 1433:1433 \
    --name sqlserver \
    --restart unless-stopped \
    -d mcr.microsoft.com/mssql/server:2019-latest

# Check if running
docker ps

# Should show sqlserver container
```

##### 3. Install SQL Server Command Tools
```bash
# Add Microsoft repository
curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
curl https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/prod.list | sudo tee /etc/apt/sources.list.d/mssql-release.list

# Update and install
sudo apt-get update
sudo ACCEPT_EULA=Y apt-get install -y mssql-tools unixodbc-dev

# Add to PATH
echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bashrc
source ~/.bashrc
```

#### 🔧 B. KONFIGURASI SQL SERVER

##### 1. Enable TCP/IP Protocol (Windows)
```powershell
# 1. Buka "SQL Server Configuration Manager"
# 2. Expand "SQL Server Network Configuration"
# 3. Klik "Protocols for MSSQLSERVER"
# 4. Right-click "TCP/IP" → Enable
# 5. Right-click "TCP/IP" → Properties
# 6. Tab "IP Addresses":
#    - Scroll ke bawah ke "IPALL"
#    - TCP Port: 1433
#    - TCP Dynamic Ports: (kosongkan)
# 7. Restart SQL Server service
```

##### 2. Configure Windows Firewall
```powershell
# Buka Windows Firewall
# Add inbound rule untuk port 1433

# Atau via command line:
netsh advfirewall firewall add rule name="SQL Server" dir=in action=allow protocol=TCP localport=1433
```

##### 3. Test SQL Server Connection
```bash
# Windows (Command Prompt)
sqlcmd -S localhost -U sa -P YourPassword123 -Q "SELECT @@VERSION"

# Linux (after installing mssql-tools)
sqlcmd -S localhost -U sa -P TjbobaDB123! -Q "SELECT @@VERSION"

# Should return SQL Server version info
```

#### 📊 C. CREATE TJBOBA DATABASE

##### 1. Create Database
```sql
-- Connect via SSMS or sqlcmd
-- Create database
CREATE DATABASE tjboba_db;
GO

-- Use database
USE tjboba_db;
GO

-- Create database user (optional, recommended for security)
CREATE LOGIN tjboba_user WITH PASSWORD = 'TjbobaUser123!';
GO

-- Create user in database
CREATE USER tjboba_user FOR LOGIN tjboba_user;
GO

-- Grant permissions
ALTER ROLE db_owner ADD MEMBER tjboba_user;
GO

-- Verify database
SELECT name FROM sys.databases WHERE name = 'tjboba_db';
GO
```

##### 2. Test Database Connection
```bash
# Test with sa user
sqlcmd -S localhost -U sa -P YourPassword123 -d tjboba_db -Q "SELECT DB_NAME() as current_database"

# Test with tjboba_user
sqlcmd -S localhost -U tjboba_user -P TjbobaUser123! -d tjboba_db -Q "SELECT DB_NAME() as current_database"
```

#### ⚙️ D. CONFIGURE LARAVEL .ENV

```env
# Database Configuration
DB_CONNECTION=sqlsrv
DB_HOST=localhost
DB_PORT=1433
DB_DATABASE=tjboba_db
DB_USERNAME=tjboba_user
DB_PASSWORD=TjbobaUser123!

# SQL Server Specific Settings
DB_TRUST_SERVER_CERTIFICATE=true
DB_ENCRYPT=false
DB_MULTIPLE_ACTIVE_RESULT_SETS=false

# Optional: Connection timeout
DB_TIMEOUT=30
```

#### 🔍 E. TROUBLESHOOTING DATABASE CONNECTION

##### Error 1: "Extension not loaded"
```bash
# Check PHP extensions
php -m | grep sqlsrv

# If not showing, install extensions (lihat section sebelumnya)
# Restart web server after installing
```

##### Error 2: "Login timeout expired"
```sql
-- Check SQL Server is running
-- Windows:
services.msc → SQL Server (MSSQLSERVER) → Status should be "Running"

-- Linux Docker:
docker ps | grep sqlserver
```

##### Error 3: "Cannot connect to server"
```bash
# Check if SQL Server listening on port 1433
netstat -an | grep 1433

# Windows:
netstat -an | findstr 1433

# Should show: TCP 0.0.0.0:1433 LISTENING
```

##### Error 4: "Login failed for user"
```sql
-- Check user exists and has permissions
USE tjboba_db;
GO

-- Check users
SELECT name, type_desc FROM sys.database_principals WHERE type IN ('S', 'U');
GO

-- Check user permissions
SELECT 
    dp.name AS principal_name,
    dp.type_desc AS principal_type,
    o.name AS object_name,
    p.permission_name,
    p.state_desc AS permission_state
FROM sys.database_permissions p
LEFT JOIN sys.objects o ON p.major_id = o.object_id
LEFT JOIN sys.database_principals dp ON p.grantee_principal_id = dp.principal_id;
GO
```

##### Error 5: "SSL Provider, error: 0"
```env
# Add to .env
DB_TRUST_SERVER_CERTIFICATE=true
DB_ENCRYPT=false
```

##### Error 6: "Mixed mode authentication"
```sql
-- Enable mixed mode authentication
-- Connect as Windows Authentication first
USE master;
GO

-- Enable mixed mode
EXEC sp_configure 'show advanced options', 1;
GO
RECONFIGURE;
GO

-- Check current authentication mode
SELECT SERVERPROPERTY('IsIntegratedSecurityOnly') as [IsIntegratedSecurityOnly];
GO
-- 0 = Mixed Mode, 1 = Windows Only

-- If result is 1, enable mixed mode via Registry:
-- HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Microsoft SQL Server\MSSQL15.MSSQLSERVER\MSSQLServer
-- LoginMode = 2 (Mixed Mode)
-- Restart SQL Server service
```

#### 🧪 F. COMPREHENSIVE CONNECTION TEST

```php
<?php
// test_database_full.php

echo "=== Tjboba Database Connection Test ===\n";

// Configuration
$config = [
    'host' => 'localhost',
    'port' => '1433',
    'database' => 'tjboba_db',
    'username' => 'tjboba_user',
    'password' => 'TjbobaUser123!'
];

// Test 1: Basic connection
echo "1. Testing basic connection...\n";
try {
    $dsn = "sqlsrv:server={$config['host']},{$config['port']};database={$config['database']};TrustServerCertificate=true";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✅ Basic connection successful\n";
} catch (PDOException $e) {
    echo "   ❌ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Database version
echo "\n2. Checking SQL Server version...\n";
try {
    $stmt = $pdo->query("SELECT @@VERSION as version");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Version: " . substr($result['version'], 0, 80) . "...\n";
} catch (PDOException $e) {
    echo "   ❌ Version check failed: " . $e->getMessage() . "\n";
}

// Test 3: Database permissions
echo "\n3. Testing database permissions...\n";
try {
    // Test CREATE TABLE
    $pdo->exec("CREATE TABLE test_permissions (id INT, name VARCHAR(50))");
    echo "   ✅ CREATE TABLE permission: OK\n";
    
    // Test INSERT
    $pdo->exec("INSERT INTO test_permissions (id, name) VALUES (1, 'test')");
    echo "   ✅ INSERT permission: OK\n";
    
    // Test SELECT
    $stmt = $pdo->query("SELECT * FROM test_permissions");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ SELECT permission: OK\n";
    
    // Test UPDATE
    $pdo->exec("UPDATE test_permissions SET name = 'updated' WHERE id = 1");
    echo "   ✅ UPDATE permission: OK\n";
    
    // Test DELETE
    $pdo->exec("DELETE FROM test_permissions WHERE id = 1");
    echo "   ✅ DELETE permission: OK\n";
    
    // Cleanup
    $pdo->exec("DROP TABLE test_permissions");
    echo "   ✅ DROP TABLE permission: OK\n";
    
} catch (PDOException $e) {
    echo "   ❌ Permission test failed: " . $e->getMessage() . "\n";
}

// Test 4: SQL Server 2008 compatibility
echo "\n4. Testing SQL Server 2008 compatibility...\n";
try {
    // Test string concatenation with +
    $stmt = $pdo->query("SELECT 'Hello' + ' ' + 'World' as concatenated");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ String concatenation (+): " . $result['concatenated'] . "\n";
    
    // Test CASE WHEN
    $stmt = $pdo->query("SELECT CASE WHEN 1=1 THEN 'True' ELSE 'False' END as case_result");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ CASE WHEN statement: " . $result['case_result'] . "\n";
    
    // Test CONVERT for date formatting
    $stmt = $pdo->query("SELECT CONVERT(VARCHAR(10), GETDATE(), 103) as formatted_date");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ CONVERT date formatting: " . $result['formatted_date'] . "\n";
    
} catch (PDOException $e) {
    echo "   ❌ Compatibility test failed: " . $e->getMessage() . "\n";
}

// Test 5: Transaction support
echo "\n5. Testing transaction support...\n";
try {
    $pdo->beginTransaction();
    $pdo->exec("CREATE TABLE test_transaction (id INT)");
    $pdo->exec("INSERT INTO test_transaction (id) VALUES (1)");
    $pdo->rollback();
    
    // Table should not exist after rollback
    try {
        $pdo->query("SELECT * FROM test_transaction");
        echo "   ❌ Transaction rollback failed\n";
    } catch (PDOException $e) {
        echo "   ✅ Transaction support: OK\n";
    }
    
} catch (PDOException $e) {
    echo "   ❌ Transaction test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "If all tests passed, your database is ready for Tjboba!\n";
?>
```

```bash
# Run comprehensive test
php test_database_full.php
```

#### 📝 G. QUICK TROUBLESHOOTING CHECKLIST

**✅ Jika Semua Test Gagal:**
1. ☐ SQL Server service berjalan
2. ☐ PHP extensions terinstall (sqlsrv, pdo_sqlsrv)
3. ☐ ODBC Driver terinstall
4. ☐ Port 1433 terbuka
5. ☐ Mixed mode authentication enabled

**✅ Jika Connection Timeout:**
1. ☐ Windows Firewall allow port 1433
2. ☐ SQL Server Browser service running
3. ☐ TCP/IP protocol enabled
4. ☐ Coba IP address instead of localhost

**✅ Jika Login Failed:**
1. ☐ Username/password benar
2. ☐ User exists di database
3. ☐ User punya permission
4. ☐ Mixed mode authentication enabled

**✅ Jika SSL Errors:**
1. ☐ Set `DB_TRUST_SERVER_CERTIFICATE=true`
2. ☐ Set `DB_ENCRYPT=false`
3. ☐ Update SQL Server ke versi terbaru

**Setelah semua test passed, lanjut ke migration Laravel! 🚀**

### Step 5: Storage & Permissions

#### Create Storage Links
```bash
# Create symbolic link untuk storage
php artisan storage:link
```

#### Set Permissions (Linux/macOS)
```bash
# Set proper permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Atau jika tidak ada www-data
sudo chmod -R 775 storage bootstrap/cache
```

### Step 6: Cache & Optimization

#### Clear All Caches
```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear compiled classes
php artisan clear-compiled
```

#### Optimize for Production
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

## 🏃‍♂️ Menjalankan Aplikasi

### Development Mode

#### Laravel Development Server
```bash
# Start development server
php artisan serve

# Custom host and port
php artisan serve --host=0.0.0.0 --port=8080

# Access aplikasi:
# http://localhost:8000
```

#### Watch Assets (Terminal Kedua)
```bash
# Watch untuk perubahan assets
npm run dev

# Atau untuk hot reload
npm run hot
```

### Production Mode

#### Apache Setup
```apache
# /etc/apache2/sites-available/tjboba.conf
<VirtualHost *:80>
    ServerName tjboba.local
    ServerAlias www.tjboba.local
    DocumentRoot /var/www/tjboba/public
    
    <Directory /var/www/tjboba/public>
        AllowOverride All
        Require all granted
        
        # Security headers
        Header always set X-Content-Type-Options nosniff
        Header always set X-Frame-Options DENY
        Header always set X-XSS-Protection "1; mode=block"
    </Directory>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/tjboba_error.log
    CustomLog ${APACHE_LOG_DIR}/tjboba_access.log combined
    
    # Environment
    SetEnv APP_ENV production
</VirtualHost>

# Enable site
sudo a2ensite tjboba.conf
sudo systemctl reload apache2
```

#### Nginx Setup
```nginx
# /etc/nginx/sites-available/tjboba
server {
    listen 80;
    listen [::]:80;
    server_name tjboba.local www.tjboba.local;
    root /var/www/tjboba/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Handle requests
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Hide PHP version
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to sensitive files
    location ~ /\.(env|git) {
        deny all;
    }
    
    # Asset caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}

# Enable site
sudo ln -s /etc/nginx/sites-available/tjboba /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🔧 Konfigurasi Tambahan

### SSL/HTTPS Setup

#### Let's Encrypt (Certbot)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d tjboba.local -d www.tjboba.local

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### Background Jobs (Queue)

#### Setup Supervisor
```bash
# Install Supervisor
sudo apt install supervisor

# Create config file
sudo nano /etc/supervisor/conf.d/tjboba-worker.conf
```

```ini
[program:tjboba-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/tjboba/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/tjboba/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Update Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start tjboba-worker:*
```

### Backup Strategy

#### Database Backup Script
```bash
#!/bin/bash
# backup-db.sh

DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/backup/tjboba"
DB_NAME="tjboba_db"

mkdir -p $BACKUP_DIR

# Create backup
sqlcmd -S localhost -U sa -P YourPassword123 -Q "BACKUP DATABASE [$DB_NAME] TO DISK = '$BACKUP_DIR/tjboba_$DATE.bak'"

# Compress backup
gzip "$BACKUP_DIR/tjboba_$DATE.bak"

# Remove backups older than 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: tjboba_$DATE.bak.gz"
```

#### Cron Job untuk Backup
```bash
# Edit crontab
crontab -e

# Add daily backup at 2 AM
0 2 * * * /home/user/scripts/backup-db.sh
```

## ✅ Verifikasi Instalasi

### Health Check Script
```bash
#!/bin/bash
# health-check.sh

echo "=== Tjboba Health Check ==="

# Check PHP version
echo "PHP Version:"
php --version | head -n 1

# Check Composer
echo "Composer Version:"
composer --version

# Check Node.js
echo "Node.js Version:"
node --version

# Check database connection
echo "Database Connection:"
php artisan tinker --execute="echo DB::select('SELECT 1 as test')[0]->test ? 'OK' : 'FAILED';"

# Check file permissions
echo "Storage Permissions:"
ls -la storage/ | head -n 1

# Check cache status
echo "Cache Status:"
php artisan cache:table > /dev/null 2>&1 && echo "Cache table exists" || echo "Cache using file driver"

echo "=== Health Check Complete ==="
```

### Manual Testing

#### 1. Web Interface
- Akses `http://localhost:8000`
- Login dengan default credentials
- Test navigation antar module

#### 2. API Testing
```bash
# Test API endpoint
curl -X GET http://localhost:8000/api/health \
  -H "Accept: application/json"

# Expected response:
# {"status": "ok", "timestamp": "2024-01-15T10:30:00Z"}
```

#### 3. Database Query Test
```bash
php artisan tinker
> User::count()
> DB::select('SELECT COUNT(*) as total FROM users')[0]->total
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Composer Install Fails
```bash
# Clear Composer cache
composer clear-cache

# Install with verbose output
composer install -vvv

# Update Composer
composer self-update
```

#### 2. Database Connection Error
```bash
# Test SQL Server connection
sqlcmd -S localhost -U sa -P YourPassword123 -Q "SELECT 1"

# Check PHP extensions
php -m | grep -i sql

# Check .env configuration
php artisan config:show database
```

#### 3. Permission Denied
```bash
# Fix Laravel permissions
sudo chown -R $USER:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

#### 4. NPM Install Issues
```bash
# Clear NPM cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

#### 5. Migration Fails
```bash
# Check migration status
php artisan migrate:status

# Reset migrations
php artisan migrate:fresh

# Run specific migration
php artisan migrate --path=/database/migrations/specific_migration.php
```

### Log Locations
- **Laravel Logs**: `storage/logs/laravel.log`
- **Apache Logs**: `/var/log/apache2/tjboba_error.log`
- **Nginx Logs**: `/var/log/nginx/error.log`
- **SQL Server Logs**: SQL Server Management Studio → Management → SQL Server Logs

### Support

Jika mengalami masalah saat instalasi:

1. **Check Documentation**: README.md dan INSTALLATION.md
2. **GitHub Issues**: [Repository Issues](https://github.com/majnunfillah/Tjboba/issues)
3. **Community**: Discord atau Telegram group
4. **Email Support**: support@tjboba.com

---

**Installation Guide untuk Tjboba v1.0**  
*Last updated: January 2024*