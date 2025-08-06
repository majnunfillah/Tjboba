# 🌍 PANDUAN INSTALASI TJBOBA - SEMUA ENVIRONMENT

Panduan super detail untuk instalasi Tjboba di berbagai environment dengan troubleshooting lengkap.

## 📋 Daftar Isi Environment

- [Windows Server 2019/2022 + IIS](#-windows-server-20192022--iis)
- [Windows 10/11 Development](#-windows-1011-development)  
- [VPS Linux (Ubuntu/CentOS)](#-vps-linux-ubuntucentos)
- [Shared Hosting](#-shared-hosting)
- [Virtual Machine (VirtualBox)](#-virtual-machine-virtualbox)
- [Docker Containers](#-docker-containers)

---

## 🖥️ WINDOWS SERVER 2019/2022 + IIS

### 📋 Prerequisites

#### System Requirements
- **Windows Server 2019/2022** (Standard/Datacenter)
- **RAM**: 4GB minimum, 8GB recommended
- **Storage**: 100GB+ free space
- **CPU**: 2+ cores
- **Administrator access**

#### Software Prerequisites
```powershell
# Check Windows version
Get-ComputerInfo | Select WindowsProductName, WindowsVersion

# Should show: Windows Server 2019/2022
```

### 🔧 STEP 1: INSTALL & CONFIGURE IIS

#### A. Install IIS Role
```powershell
# Method 1: Server Manager GUI
# 1. Open Server Manager
# 2. Add Roles and Features
# 3. Role-based installation
# 4. Select Web Server (IIS)
# 5. Add Role Services:
#    ✅ Common HTTP Features (all)
#    ✅ Application Development → CGI
#    ✅ Application Development → ISAPI Extensions
#    ✅ Application Development → ISAPI Filters
#    ✅ Health and Diagnostics (all)
#    ✅ Security → Request Filtering
#    ✅ Management Tools → IIS Management Console

# Method 2: PowerShell
Enable-WindowsOptionalFeature -Online -FeatureName IIS-WebServerRole
Enable-WindowsOptionalFeature -Online -FeatureName IIS-WebServer
Enable-WindowsOptionalFeature -Online -FeatureName IIS-CommonHttpFeatures
Enable-WindowsOptionalFeature -Online -FeatureName IIS-HttpErrors
Enable-WindowsOptionalFeature -Online -FeatureName IIS-HttpLogging
Enable-WindowsOptionalFeature -Online -FeatureName IIS-Security
Enable-WindowsOptionalFeature -Online -FeatureName IIS-RequestFiltering
Enable-WindowsOptionalFeature -Online -FeatureName IIS-StaticContent
Enable-WindowsOptionalFeature -Online -FeatureName IIS-DefaultDocument
Enable-WindowsOptionalFeature -Online -FeatureName IIS-DirectoryBrowsing
Enable-WindowsOptionalFeature -Online -FeatureName IIS-ASPNET45
Enable-WindowsOptionalFeature -Online -FeatureName IIS-CGI
Enable-WindowsOptionalFeature -Online -FeatureName IIS-ISAPIExtensions
Enable-WindowsOptionalFeature -Online -FeatureName IIS-ISAPIFilter
Enable-WindowsOptionalFeature -Online -FeatureName IIS-ServerSideIncludes
Enable-WindowsOptionalFeature -Online -FeatureName IIS-CustomLogging
Enable-WindowsOptionalFeature -Online -FeatureName IIS-BasicAuthentication
Enable-WindowsOptionalFeature -Online -FeatureName IIS-IISCertificateMappingAuthentication
Enable-WindowsOptionalFeature -Online -FeatureName IIS-ManagementConsole
```

#### B. Verify IIS Installation
```powershell
# Check IIS service
Get-Service -Name W3SVC

# Test default website
Invoke-WebRequest -Uri "http://localhost" -UseBasicParsing

# Should return IIS default page
```

### 🔧 STEP 2: INSTALL PREREQUISITES

#### A. Install Microsoft Visual C++ Redistributable
```powershell
# Download and install VC++ 2015-2022 Redistributable
# URL: https://aka.ms/vs/17/release/vc_redist.x64.exe

# Download via PowerShell
Invoke-WebRequest -Uri "https://aka.ms/vs/17/release/vc_redist.x64.exe" -OutFile "vc_redist.x64.exe"

# Install silently
Start-Process -FilePath "vc_redist.x64.exe" -ArgumentList "/quiet" -Wait

# Verify installation
Get-ItemProperty "HKLM:\SOFTWARE\Classes\Installer\Dependencies\Microsoft.VS.VC_RuntimeMinimumVSU_amd64,v14"
```

#### B. Install ODBC Driver for SQL Server
```powershell
# Download Microsoft ODBC Driver 18 for SQL Server
# URL: https://go.microsoft.com/fwlink/?linkid=2223304

Invoke-WebRequest -Uri "https://go.microsoft.com/fwlink/?linkid=2223304" -OutFile "msodbcsql.msi"

# Install ODBC Driver
Start-Process -FilePath "msiexec.exe" -ArgumentList "/i msodbcsql.msi /quiet IACCEPTMSODBCSQLLICENSETERMS=YES" -Wait

# Verify installation
Get-OdbcDriver | Where-Object {$_.Name -like "*ODBC Driver*for SQL Server*"}
```

### 🔧 STEP 3: INSTALL & CONFIGURE PHP

#### A. Download PHP
```powershell
# Create PHP directory
New-Item -ItemType Directory -Path "C:\PHP" -Force

# Download PHP 8.1 Non-Thread Safe (for IIS)
$phpVersion = "8.1.30"
$phpUrl = "https://windows.php.net/downloads/releases/php-$phpVersion-nts-Win32-vs16-x64.zip"

Invoke-WebRequest -Uri $phpUrl -OutFile "C:\temp\php-$phpVersion-nts.zip"

# Extract PHP
Expand-Archive -Path "C:\temp\php-$phpVersion-nts.zip" -DestinationPath "C:\PHP" -Force
```

#### B. Download Microsoft Drivers for PHP
```powershell
# Download Microsoft Drivers for PHP for SQL Server
# URL: https://go.microsoft.com/fwlink/?linkid=2239775

Invoke-WebRequest -Uri "https://go.microsoft.com/fwlink/?linkid=2239775" -OutFile "C:\temp\SQLSRV.zip"

# Extract drivers
Expand-Archive -Path "C:\temp\SQLSRV.zip" -DestinationPath "C:\temp\SQLSRV" -Force

# Copy appropriate drivers to PHP ext directory
Copy-Item "C:\temp\SQLSRV\x64\php_sqlsrv_81_nts.dll" -Destination "C:\PHP\ext\"
Copy-Item "C:\temp\SQLSRV\x64\php_pdo_sqlsrv_81_nts.dll" -Destination "C:\PHP\ext\"
```

#### C. Configure PHP
```powershell
# Copy php.ini template
Copy-Item "C:\PHP\php.ini-production" -Destination "C:\PHP\php.ini"

# Configure php.ini
$phpIniPath = "C:\PHP\php.ini"
$phpIniContent = Get-Content $phpIniPath

# Set basic configurations
$phpIniContent = $phpIniContent -replace ';extension_dir = "ext"', 'extension_dir = "C:\PHP\ext"'
$phpIniContent = $phpIniContent -replace ';fastcgi.impersonate = 1', 'fastcgi.impersonate = 1'
$phpIniContent = $phpIniContent -replace ';cgi.fix_pathinfo=1', 'cgi.fix_pathinfo=0'
$phpIniContent = $phpIniContent -replace ';cgi.force_redirect = 1', 'cgi.force_redirect = 0'

# Enable required extensions
$extensions = @(
    'extension=curl',
    'extension=fileinfo',
    'extension=gd',
    'extension=mbstring',
    'extension=openssl',
    'extension=pdo_sqlsrv',
    'extension=sqlsrv',
    'extension=zip'
)

# Add extensions to php.ini
$phpIniContent += ""
$phpIniContent += "; SQL Server Extensions"
$phpIniContent += $extensions

# Write back to php.ini
$phpIniContent | Set-Content $phpIniPath

# Add PHP to PATH
$currentPath = [Environment]::GetEnvironmentVariable("PATH", "Machine")
if ($currentPath -notlike "*C:\PHP*") {
    [Environment]::SetEnvironmentVariable("PATH", $currentPath + ";C:\PHP", "Machine")
}
```

#### D. Verify PHP Installation
```powershell
# Test PHP CLI
& "C:\PHP\php.exe" --version

# Test extensions
& "C:\PHP\php.exe" -m | Select-String "sqlsrv"
& "C:\PHP\php.exe" -m | Select-String "pdo_sqlsrv"

# Create PHP info test file
$phpInfo = '<?php phpinfo(); ?>'
$phpInfo | Out-File -FilePath "C:\inetpub\wwwroot\phpinfo.php" -Encoding UTF8

# Test via browser (after configuring IIS)
```

### 🔧 STEP 4: CONFIGURE IIS FOR PHP

#### A. Install PHP Manager for IIS (Optional but Recommended)
```powershell
# Download PHP Manager
# URL: https://www.phpmanager.xyz/download/

Invoke-WebRequest -Uri "https://github.com/phpmanager/phpmanager/releases/download/v2.6.0/PHPManagerForIIS-2.6.0-x64.msi" -OutFile "C:\temp\PHPManager.msi"

# Install PHP Manager
Start-Process -FilePath "msiexec.exe" -ArgumentList "/i C:\temp\PHPManager.msi /quiet" -Wait
```

#### B. Configure IIS Handler Mapping
```powershell
# Import WebAdministration module
Import-Module WebAdministration

# Add PHP FastCGI module
New-WebHandler -Name "PHP_via_FastCGI" -Path "*.php" -Verb "*" -Modules "FastCgiModule" -ScriptProcessor "C:\PHP\php-cgi.exe" -ResourceType "Either"

# Configure FastCGI settings
$fastCgiPath = "C:\PHP\php-cgi.exe"

# Add FastCGI application
Add-WebConfigurationProperty -PSPath "MACHINE/WEBROOT/APPHOST" -Filter "system.webServer/fastCgi" -Name "application" -Value @{
    fullPath = $fastCgiPath
    arguments = ""
    maxInstances = 4
    idleTimeout = 600
    activityTimeout = 3600
    requestTimeout = 600
    instanceMaxRequests = 1000
    protocol = "NamedPipe"
    flushNamedPipe = $false
}

# Set environment variables for FastCGI
Set-WebConfigurationProperty -PSPath "MACHINE/WEBROOT/APPHOST" -Filter "system.webServer/fastCgi/application[@fullPath='$fastCgiPath']/environmentVariables" -Name "." -Value @{
    name = "PHP_FCGI_MAX_REQUESTS"
    value = "1000"
}

Set-WebConfigurationProperty -PSPath "MACHINE/WEBROOT/APPHOST" -Filter "system.webServer/fastCgi/application[@fullPath='$fastCgiPath']/environmentVariables" -Name "." -Value @{
    name = "PHPRC"
    value = "C:\PHP"
}
```

#### C. Test PHP Configuration
```powershell
# Restart IIS
iisreset

# Test PHP info page
Start-Sleep -Seconds 5
Invoke-WebRequest -Uri "http://localhost/phpinfo.php" -UseBasicParsing

# Should return PHP configuration page
```

### 🔧 STEP 5: INSTALL SQL SERVER

#### A. Download SQL Server 2019/2022
```powershell
# SQL Server 2019 Developer Edition (Free)
$sqlServerUrl = "https://go.microsoft.com/fwlink/?linkid=866662"
Invoke-WebRequest -Uri $sqlServerUrl -OutFile "C:\temp\SQL2019-SSEI-Dev.exe"

# Run SQL Server installer
Start-Process -FilePath "C:\temp\SQL2019-SSEI-Dev.exe" -ArgumentList "/Action=Download /MediaPath=C:\temp\SQLServer2019" -Wait
```

#### B. Install SQL Server (Unattended)
```powershell
# Create configuration file for unattended installation
$configContent = @"
[OPTIONS]
ACTION="Install"
FEATURES=SQLENGINE,REPLICATION,FULLTEXT,IS,CONN
INSTANCENAME="MSSQLSERVER"
SQLSVCACCOUNT="NT AUTHORITY\SYSTEM"
SQLSYSADMINACCOUNTS="BUILTIN\Administrators"
SECURITYMODE="SQL"
SAPWD="TjbobaSQL2023!"
TCPENABLED="1"
NPENABLED="1"
BROWSERSVCSTARTUPTYPE="Manual"
IACCEPTSQLSERVERLICENSETERMS="True"
"@

$configContent | Out-File -FilePath "C:\temp\ConfigurationFile.ini" -Encoding UTF8

# Run SQL Server installation
$sqlSetupPath = Get-ChildItem -Path "C:\temp\SQLServer2019" -Recurse -Name "setup.exe" | Select-Object -First 1
$fullSetupPath = "C:\temp\SQLServer2019\$sqlSetupPath"

Start-Process -FilePath $fullSetupPath -ArgumentList "/ConfigurationFile=C:\temp\ConfigurationFile.ini" -Wait
```

#### C. Configure SQL Server
```powershell
# Enable SQL Server Browser
Set-Service -Name "SQLBrowser" -StartupType Automatic
Start-Service -Name "SQLBrowser"

# Configure SQL Server for remote connections
Invoke-Sqlcmd -Query "
EXEC sp_configure 'show advanced options', 1;
RECONFIGURE;
EXEC sp_configure 'remote access', 1;
RECONFIGURE;
" -ServerInstance "localhost"

# Enable TCP/IP protocol
Import-Module SQLPS -DisableNameChecking

$smo = 'Microsoft.SqlServer.Management.Smo.'
$wmi = new-object ($smo + 'Wmi.ManagedComputer').

# Enable TCP/IP
$uri = "ManagedComputer[@Name='$env:COMPUTERNAME']/ServerInstance[@Name='MSSQLSERVER']/ServerProtocol[@Name='Tcp']"
$Tcp = $wmi.GetSmoObject($uri)
$Tcp.IsEnabled = $true
$Tcp.Alter()

# Restart SQL Server service
Restart-Service -Name "MSSQLSERVER" -Force
```

#### D. Create Tjboba Database
```powershell
# Create database and user
$sqlCommands = @"
-- Create database
CREATE DATABASE tjboba_db;
GO

-- Use database
USE tjboba_db;
GO

-- Create login
CREATE LOGIN tjboba_user WITH PASSWORD = 'TjbobaUser2023!';
GO

-- Create user
CREATE USER tjboba_user FOR LOGIN tjboba_user;
GO

-- Grant permissions
ALTER ROLE db_owner ADD MEMBER tjboba_user;
GO

-- Verify
SELECT name FROM sys.databases WHERE name = 'tjboba_db';
SELECT name FROM sys.database_principals WHERE name = 'tjboba_user';
GO
"@

Invoke-Sqlcmd -Query $sqlCommands -ServerInstance "localhost" -Username "sa" -Password "TjbobaSQL2023!"
```

### 🔧 STEP 6: INSTALL COMPOSER & NODE.JS

#### A. Install Composer
```powershell
# Download Composer installer
Invoke-WebRequest -Uri "https://getcomposer.org/Composer-Setup.exe" -OutFile "C:\temp\Composer-Setup.exe"

# Install Composer silently
Start-Process -FilePath "C:\temp\Composer-Setup.exe" -ArgumentList "/VERYSILENT /SUPPRESSMSGBOXES /NORESTART" -Wait

# Verify installation
& "composer" --version
```

#### B. Install Node.js
```powershell
# Download Node.js LTS
$nodeVersion = "18.18.2"
$nodeUrl = "https://nodejs.org/dist/v$nodeVersion/node-v$nodeVersion-x64.msi"
Invoke-WebRequest -Uri $nodeUrl -OutFile "C:\temp\nodejs.msi"

# Install Node.js
Start-Process -FilePath "msiexec.exe" -ArgumentList "/i C:\temp\nodejs.msi /quiet" -Wait

# Verify installation
& "node" --version
& "npm" --version
```

### 🔧 STEP 7: DEPLOY TJBOBA APPLICATION

#### A. Prepare Application Directory
```powershell
# Create application directory
$appPath = "C:\inetpub\wwwroot\tjboba"
New-Item -ItemType Directory -Path $appPath -Force

# Set permissions
icacls $appPath /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls $appPath /grant "IUSR:(OI)(CI)R" /T
```

#### B. Deploy Application
```powershell
# Clone or copy application files
# If using Git:
Set-Location "C:\inetpub\wwwroot"
& "git" clone "https://github.com/majnunfillah/Tjboba.git" tjboba

# Set location to app directory
Set-Location $appPath

# Install dependencies
& "composer" install --no-dev --optimize-autoloader

# Copy environment file
Copy-Item ".env.example" -Destination ".env"

# Generate application key
& "php" artisan key:generate

# Configure .env file
$envContent = Get-Content ".env"
$envContent = $envContent -replace "DB_CONNECTION=.*", "DB_CONNECTION=sqlsrv"
$envContent = $envContent -replace "DB_HOST=.*", "DB_HOST=localhost"
$envContent = $envContent -replace "DB_PORT=.*", "DB_PORT=1433"
$envContent = $envContent -replace "DB_DATABASE=.*", "DB_DATABASE=tjboba_db"
$envContent = $envContent -replace "DB_USERNAME=.*", "DB_USERNAME=tjboba_user"
$envContent = $envContent -replace "DB_PASSWORD=.*", "DB_PASSWORD=TjbobaUser2023!"

# Add SQL Server specific settings
$envContent += ""
$envContent += "# SQL Server Specific Settings"
$envContent += "DB_TRUST_SERVER_CERTIFICATE=true"
$envContent += "DB_ENCRYPT=false"

$envContent | Set-Content ".env"

# Run migrations
& "php" artisan migrate --force

# Install and build assets
& "npm" install
& "npm" run build

# Set storage permissions
icacls "storage" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "bootstrap\cache" /grant "IIS_IUSRS:(OI)(CI)F" /T

# Create symbolic link for storage
& "php" artisan storage:link
```

#### C. Configure IIS Site
```powershell
# Create new IIS site
Import-Module WebAdministration

$siteName = "Tjboba"
$sitePort = 80
$sitePath = "$appPath\public"

# Remove default site binding if exists
Get-WebBinding -Name "Default Web Site" -Port 80 -ErrorAction SilentlyContinue | Remove-WebBinding

# Create new site
New-Website -Name $siteName -Port $sitePort -PhysicalPath $sitePath

# Configure default document
Set-WebConfigurationProperty -PSPath "MACHINE/WEBROOT/APPHOST/$siteName" -Filter "system.webServer/defaultDocument/files" -Name "." -Value @{value="index.php"}

# Create web.config for Laravel
$webConfig = @"
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <defaultDocument>
            <files>
                <clear />
                <add value="index.php" />
                <add value="default.aspx" />
                <add value="Default.htm" />
                <add value="Default.asp" />
                <add value="index.htm" />
                <add value="index.html" />
            </files>
        </defaultDocument>
        <rewrite>
            <rules>
                <rule name="Imported Rule 1" stopProcessing="true">
                    <match url="^(.*)/$" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Redirect" redirectType="Permanent" url="/{R:1}" />
                </rule>
                <rule name="Imported Rule 2" stopProcessing="true">
                    <match url="^" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>
"@

$webConfig | Out-File -FilePath "$sitePath\web.config" -Encoding UTF8

# Restart IIS
iisreset
```

### ✅ STEP 8: TESTING & VERIFICATION

#### A. Test Database Connection
```powershell
Set-Location $appPath

# Test database connection
& "php" artisan tinker --execute="echo 'DB Connection: ' . (DB::connection()->getPdo() ? 'Success' : 'Failed');"

# Test specific SQL Server features
& "php" artisan tinker --execute="echo 'SQL Server Version: ' . DB::select('SELECT @@VERSION as version')[0]->version;"
```

#### B. Test Web Application
```powershell
# Test website response
$response = Invoke-WebRequest -Uri "http://localhost" -UseBasicParsing
Write-Host "HTTP Status: $($response.StatusCode)"
Write-Host "Response Length: $($response.Content.Length) bytes"

# Check if Laravel is running
if ($response.Content -like "*Laravel*") {
    Write-Host "✅ Laravel application is running successfully!"
} else {
    Write-Host "❌ Laravel application not detected"
}
```

#### C. Performance Testing
```powershell
# Test database performance
$timeTest = Measure-Command {
    & "php" artisan tinker --execute="User::count();"
}
Write-Host "Database query time: $($timeTest.TotalMilliseconds) ms"

# Test web response time
$webTimeTest = Measure-Command {
    Invoke-WebRequest -Uri "http://localhost" -UseBasicParsing
}
Write-Host "Web response time: $($webTimeTest.TotalMilliseconds) ms"
```

### 🚨 TROUBLESHOOTING WINDOWS SERVER + IIS

#### Issue 1: "HTTP Error 500.19 - Internal Server Error"
```powershell
# Solution 1: Check web.config syntax
& "php" -l "$appPath\public\web.config"

# Solution 2: Enable detailed error messages
Set-WebConfigurationProperty -PSPath "MACHINE/WEBROOT/APPHOST/$siteName" -Filter "system.webServer/httpErrors" -Name "errorMode" -Value "Detailed"

# Solution 3: Check IIS logs
Get-Content "C:\inetpub\logs\LogFiles\W3SVC1\*.log" | Select-Object -Last 10
```

#### Issue 2: "PHP extensions not loading"
```powershell
# Check PHP configuration
& "php" --ini

# Verify extension files exist
Test-Path "C:\PHP\ext\php_sqlsrv_81_nts.dll"
Test-Path "C:\PHP\ext\php_pdo_sqlsrv_81_nts.dll"

# Check PHP error log
Get-Content "C:\PHP\php_errors.log" | Select-Object -Last 20
```

#### Issue 3: "Database connection fails"
```powershell
# Test SQL Server connection
Test-NetConnection -ComputerName "localhost" -Port 1433

# Check SQL Server service
Get-Service -Name "MSSQLSERVER"

# Test with SQLCMD
& "sqlcmd" -S "localhost" -U "tjboba_user" -P "TjbobaUser2023!" -Q "SELECT 1"
```

#### Issue 4: "Permission denied errors"
```powershell
# Fix IIS permissions
icacls "$appPath" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "$appPath\storage" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "$appPath\bootstrap\cache" /grant "IIS_IUSRS:(OI)(CI)F" /T

# Reset IIS application pool
Restart-WebAppPool -Name "DefaultAppPool"
```

#### Issue 5: "Slow performance"
```powershell
# Enable PHP OPcache
$phpIni = Get-Content "C:\PHP\php.ini"
$phpIni += ""
$phpIni += "; Enable OPcache"
$phpIni += "zend_extension=opcache"
$phpIni += "opcache.enable=1"
$phpIni += "opcache.enable_cli=1"
$phpIni += "opcache.memory_consumption=128"
$phpIni += "opcache.interned_strings_buffer=8"
$phpIni += "opcache.max_accelerated_files=4000"
$phpIni += "opcache.revalidate_freq=2"
$phpIni += "opcache.fast_shutdown=1"

$phpIni | Set-Content "C:\PHP\php.ini"

# Restart IIS
iisreset
```

---

## 💻 WINDOWS 10/11 DEVELOPMENT

### 📋 Prerequisites

#### System Requirements
- **Windows 10** (version 2004+) or **Windows 11**
- **RAM**: 8GB minimum, 16GB recommended
- **Storage**: 50GB+ free space
- **Administrator privileges**

#### Enable Developer Features
```powershell
# Enable Developer Mode
Set-ItemProperty -Path "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\AppModelUnlock" -Name "AllowDevelopmentWithoutDevLicense" -Value 1

# Enable Windows Subsystem for Linux (optional)
dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart
dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart

# Restart required after WSL installation
```

### 🔧 INSTALL DEVELOPMENT STACK

#### A. Install Chocolatey Package Manager
```powershell
# Run as Administrator
Set-ExecutionPolicy Bypass -Scope Process -Force
[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))

# Verify installation
choco --version
```

#### B. Install Development Tools via Chocolatey
```powershell
# Install essential tools
choco install -y git
choco install -y nodejs-lts
choco install -y php
choco install -y composer
choco install -y vscode
choco install -y postman

# Install web servers (choose one)
choco install -y xampp           # Complete LAMP stack
# OR
choco install -y laragon         # Lightweight development environment

# Refresh environment variables
refreshenv
```

#### C. Alternative: XAMPP Installation
```powershell
# Download XAMPP
$xamppUrl = "https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.1.25/xampp-windows-x64-8.1.25-0-VS16-installer.exe"
Invoke-WebRequest -Uri $xamppUrl -OutFile "C:\temp\xampp-installer.exe"

# Install XAMPP silently
Start-Process -FilePath "C:\temp\xampp-installer.exe" -ArgumentList "--mode unattended --disable-components xampp_perl,xampp_tomcat" -Wait

# Start XAMPP services
Start-Process -FilePath "C:\xampp\xampp-control.exe"
```

### 🔧 CONFIGURE DEVELOPMENT ENVIRONMENT

#### A. Configure PHP for SQL Server
```powershell
# Stop Apache if running
Stop-Process -Name "httpd" -Force -ErrorAction SilentlyContinue

# Download Microsoft drivers for PHP
$driversUrl = "https://go.microsoft.com/fwlink/?linkid=2239775"
Invoke-WebRequest -Uri $driversUrl -OutFile "C:\temp\SQLSRV.zip"
Expand-Archive -Path "C:\temp\SQLSRV.zip" -DestinationPath "C:\temp\SQLSRV" -Force

# Copy drivers to PHP extension directory
$phpPath = "C:\xampp\php"  # Adjust path based on your PHP installation
Copy-Item "C:\temp\SQLSRV\x64\php_sqlsrv_81_ts.dll" -Destination "$phpPath\ext\"
Copy-Item "C:\temp\SQLSRV\x64\php_pdo_sqlsrv_81_ts.dll" -Destination "$phpPath\ext\"

# Configure php.ini
$phpIni = "$phpPath\php.ini"
$config = Get-Content $phpIni

# Enable extensions
$config = $config -replace ';extension=curl', 'extension=curl'
$config = $config -replace ';extension=fileinfo', 'extension=fileinfo'
$config = $config -replace ';extension=gd', 'extension=gd'
$config = $config -replace ';extension=mbstring', 'extension=mbstring'
$config = $config -replace ';extension=openssl', 'extension=openssl'
$config = $config -replace ';extension=zip', 'extension=zip'

# Add SQL Server extensions
$config += ""
$config += "; SQL Server Extensions"
$config += "extension=pdo_sqlsrv"
$config += "extension=sqlsrv"

$config | Set-Content $phpIni

# Restart Apache
Start-Process -FilePath "C:\xampp\apache\bin\httpd.exe"
```

#### B. Install SQL Server LocalDB (Lightweight)
```powershell
# Download SQL Server Express LocalDB
$localDbUrl = "https://go.microsoft.com/fwlink/?linkid=866658"
Invoke-WebRequest -Uri $localDbUrl -OutFile "C:\temp\SqlLocalDB.msi"

# Install LocalDB
Start-Process -FilePath "msiexec.exe" -ArgumentList "/i C:\temp\SqlLocalDB.msi /quiet IACCEPTSQLSERVERLICENSETERMS=YES" -Wait

# Create LocalDB instance
& "SqlLocalDB" create "tjboba" -s

# Connect and create database
$connectionString = "server=(localdb)\tjboba;integrated security=true"
Invoke-Sqlcmd -ConnectionString $connectionString -Query "CREATE DATABASE tjboba_db"
```

### 🔧 SETUP TJBOBA PROJECT

#### A. Create Project Directory
```powershell
# Create development directory
$devPath = "C:\Development\Tjboba"
New-Item -ItemType Directory -Path $devPath -Force
Set-Location $devPath

# Clone repository
git clone https://github.com/majnunfillah/Tjboba.git .

# Install dependencies
composer install
npm install
```

#### B. Configure Environment
```powershell
# Copy environment file
Copy-Item ".env.example" -Destination ".env"

# Generate application key
php artisan key:generate

# Configure .env for LocalDB
$envContent = @"
APP_NAME=Tjboba
APP_ENV=local
APP_KEY=$(php artisan key:generate --show)
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlsrv
DB_HOST=(localdb)\tjboba
DB_PORT=1433
DB_DATABASE=tjboba_db
DB_USERNAME=
DB_PASSWORD=
DB_TRUST_SERVER_CERTIFICATE=true
DB_ENCRYPT=false

CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
"@

$envContent | Set-Content ".env"

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Build assets
npm run dev
```

#### C. Start Development Server
```powershell
# Start Laravel development server
Start-Process -FilePath "php" -ArgumentList "artisan serve" -NoNewWindow

# Start asset watcher (in new terminal)
Start-Process -FilePath "cmd" -ArgumentList "/c npm run dev"

# Open browser
Start-Process "http://localhost:8000"
```

### 🚨 TROUBLESHOOTING WINDOWS DEVELOPMENT

#### Issue 1: "Port 8000 already in use"
```powershell
# Find process using port 8000
netstat -ano | findstr :8000

# Kill process by PID
taskkill /PID <PID> /F

# Start server on different port
php artisan serve --port=8080
```

#### Issue 2: "Composer install fails"
```powershell
# Clear Composer cache
composer clear-cache

# Increase memory limit
php -d memory_limit=2G composer install

# Update Composer
composer self-update
```

#### Issue 3: "NPM install fails"
```powershell
# Clear NPM cache
npm cache clean --force

# Delete node_modules and package-lock.json
Remove-Item -Recurse -Force node_modules, package-lock.json -ErrorAction SilentlyContinue

# Install with different registry
npm install --registry https://registry.npmjs.org/

# Use Yarn as alternative
npm install -g yarn
yarn install
```

---

## 🖥️ VPS LINUX (UBUNTU/CENTOS)

### 📋 VPS REQUIREMENTS

#### Minimum Specifications
- **CPU**: 1 vCPU (2+ recommended)
- **RAM**: 1GB (2GB+ recommended)
- **Storage**: 20GB SSD
- **OS**: Ubuntu 20.04/22.04 LTS or CentOS 8/9
- **Network**: 1TB bandwidth
- **Root access**

#### Popular VPS Providers
- **DigitalOcean**: Starting $5/month
- **Vultr**: Starting $2.50/month
- **Linode**: Starting $5/month
- **AWS EC2**: t2.micro (free tier)
- **Google Cloud**: e2-micro (free tier)

### 🚀 UBUNTU 20.04/22.04 INSTALLATION

#### STEP 1: Initial Server Setup
```bash
# Connect via SSH
ssh root@your-server-ip

# Update system packages
apt update && apt upgrade -y

# Install essential packages
apt install -y curl wget gnupg2 software-properties-common apt-transport-https ca-certificates lsb-release

# Create sudo user (security best practice)
adduser tjboba
usermod -aG sudo tjboba

# Configure SSH key authentication (recommended)
mkdir -p /home/tjboba/.ssh
cp ~/.ssh/authorized_keys /home/tjboba/.ssh/
chown -R tjboba:tjboba /home/tjboba/.ssh
chmod 700 /home/tjboba/.ssh
chmod 600 /home/tjboba/.ssh/authorized_keys

# Switch to new user
su - tjboba
```

#### STEP 2: Install Web Server (Nginx)
```bash
# Install Nginx
sudo apt install -y nginx

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Configure firewall
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw --force enable

# Test Nginx installation
curl -I http://localhost
```

#### STEP 3: Install PHP 8.1
```bash
# Add PHP repository
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

# Install PHP and required extensions
sudo apt install -y php8.1-fpm php8.1-cli php8.1-common php8.1-mysql php8.1-xml php8.1-xmlrpc php8.1-curl php8.1-gd php8.1-imagick php8.1-dev php8.1-imap php8.1-mbstring php8.1-opcache php8.1-soap php8.1-zip php8.1-intl php8.1-bcmath php8.1-ctype php8.1-json php8.1-fileinfo php8.1-dom php8.1-tokenizer

# Install Microsoft ODBC Driver for SQL Server
curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
curl https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/prod.list | sudo tee /etc/apt/sources.list.d/msprod.list
sudo apt update
sudo ACCEPT_EULA=Y apt install -y msodbcsql18 mssql-tools18 unixodbc-dev

# Install PHP SQL Server extensions
sudo pecl install sqlsrv pdo_sqlsrv

# Configure PHP extensions
echo "extension=sqlsrv.so" | sudo tee -a /etc/php/8.1/fpm/php.ini
echo "extension=pdo_sqlsrv.so" | sudo tee -a /etc/php/8.1/fpm/php.ini
echo "extension=sqlsrv.so" | sudo tee -a /etc/php/8.1/cli/php.ini
echo "extension=pdo_sqlsrv.so" | sudo tee -a /etc/php/8.1/cli/php.ini

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
sudo systemctl enable php8.1-fpm

# Verify PHP installation
php -v
php -m | grep -i sqlsrv
```

#### STEP 4: Install SQL Server on Linux
```bash
# Add Microsoft SQL Server repository
wget -qO- https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
sudo add-apt-repository "$(wget -qO- https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/mssql-server-2019.list)"

# Install SQL Server
sudo apt update
sudo apt install -y mssql-server

# Configure SQL Server
sudo /opt/mssql/bin/mssql-conf setup

# Choose edition: 2 (Developer - free)
# Accept license: Yes
# Set SA password: TjbobaSQL2023!
# Confirm password

# Start SQL Server service
sudo systemctl start mssql-server
sudo systemctl enable mssql-server

# Verify SQL Server installation
systemctl status mssql-server

# Install SQL Server command-line tools
sudo apt install -y mssql-tools18

# Add tools to PATH
echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> ~/.bashrc
source ~/.bashrc

# Test SQL Server connection
sqlcmd -S localhost -U sa -P 'TjbobaSQL2023!' -Q "SELECT @@VERSION"
```

#### STEP 5: Create Database and User
```bash
# Create database and user
sqlcmd -S localhost -U sa -P 'TjbobaSQL2023!' << EOF
CREATE DATABASE tjboba_db;
GO
USE tjboba_db;
GO
CREATE LOGIN tjboba_user WITH PASSWORD = 'TjbobaUser2023!';
GO
CREATE USER tjboba_user FOR LOGIN tjboba_user;
GO
ALTER ROLE db_owner ADD MEMBER tjboba_user;
GO
SELECT name FROM sys.databases WHERE name = 'tjboba_db';
GO
EXIT
EOF

# Test new user connection
sqlcmd -S localhost -U tjboba_user -P 'TjbobaUser2023!' -d tjboba_db -Q "SELECT DB_NAME() as current_database"
```

#### STEP 6: Install Composer
```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify Composer installation
composer --version
```

#### STEP 7: Install Node.js
```bash
# Install Node.js LTS via NodeSource repository
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs

# Verify installation
node --version
npm --version

# Install global packages
sudo npm install -g pm2
```

#### STEP 8: Deploy Tjboba Application
```bash
# Create application directory
sudo mkdir -p /var/www/tjboba
sudo chown -R $USER:www-data /var/www/tjboba

# Clone repository
cd /var/www
git clone https://github.com/majnunfillah/Tjboba.git tjboba
cd tjboba

# Set proper permissions
sudo chown -R $USER:www-data /var/www/tjboba
sudo chmod -R 755 /var/www/tjboba
sudo chmod -R 775 /var/www/tjboba/storage
sudo chmod -R 775 /var/www/tjboba/bootstrap/cache

# Install dependencies
composer install --optimize-autoloader --no-dev

# Copy and configure environment
cp .env.example .env

# Configure .env file
cat > .env << 'EOF'
APP_NAME=Tjboba
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=sqlsrv
DB_HOST=localhost
DB_PORT=1433
DB_DATABASE=tjboba_db
DB_USERNAME=tjboba_user
DB_PASSWORD=TjbobaUser2023!
DB_TRUST_SERVER_CERTIFICATE=true
DB_ENCRYPT=false

CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@tjboba.com"
MAIL_FROM_NAME="${APP_NAME}"
EOF

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Install and build assets
npm ci --production
npm run build

# Create storage symlink
php artisan storage:link

# Set final permissions
sudo chown -R www-data:www-data /var/www/tjboba/storage
sudo chown -R www-data:www-data /var/www/tjboba/bootstrap/cache
```

#### STEP 9: Configure Nginx
```bash
# Create Nginx configuration
sudo tee /etc/nginx/sites-available/tjboba << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/tjboba/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Handle requests
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ ^/(\.env|\.git|composer\.(json|lock)|package\.(json|lock)|\..*) {
        deny all;
    }

    # Asset caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Log files
    access_log /var/log/nginx/tjboba_access.log;
    error_log /var/log/nginx/tjboba_error.log;
}
EOF

# Enable site
sudo ln -s /etc/nginx/sites-available/tjboba /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

#### STEP 10: SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate (replace with your domain)
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run

# Auto-renewal cron job
echo "0 12 * * * /usr/bin/certbot renew --quiet" | sudo crontab -
```

### 🚀 CENTOS 8/9 INSTALLATION

#### STEP 1: Initial Server Setup
```bash
# Connect via SSH
ssh root@your-server-ip

# Update system
dnf update -y

# Install essential packages
dnf install -y curl wget gnupg2 epel-release

# Create sudo user
adduser tjboba
usermod -aG wheel tjboba

# Configure SSH (same as Ubuntu section)
mkdir -p /home/tjboba/.ssh
cp ~/.ssh/authorized_keys /home/tjboba/.ssh/
chown -R tjboba:tjboba /home/tjboba/.ssh
chmod 700 /home/tjboba/.ssh
chmod 600 /home/tjboba/.ssh/authorized_keys

# Switch user
su - tjboba
```

#### STEP 2: Install Nginx
```bash
# Install Nginx
sudo dnf install -y nginx

# Start and enable
sudo systemctl start nginx
sudo systemctl enable nginx

# Configure firewall
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --reload
```

#### STEP 3: Install PHP 8.1
```bash
# Enable Remi repository
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm

# Install PHP 8.1
sudo dnf module reset php
sudo dnf module enable php:remi-8.1 -y
sudo dnf install -y php php-fpm php-cli php-common php-mysql php-xml php-curl php-gd php-mbstring php-opcache php-zip php-intl php-bcmath php-json php-dom php-tokenizer

# Install ODBC driver and SQL Server extensions
curl https://packages.microsoft.com/config/rhel/8/prod.repo | sudo tee /etc/yum.repos.d/msprod.repo
sudo dnf install -y msodbcsql18 mssql-tools18 unixODBC-devel

# Install PHP SQL Server extensions
sudo dnf install -y php-pear php-devel gcc
sudo pecl install sqlsrv pdo_sqlsrv

# Configure extensions
echo "extension=sqlsrv.so" | sudo tee -a /etc/php.ini
echo "extension=pdo_sqlsrv.so" | sudo tee -a /etc/php.ini

# Configure PHP-FPM
sudo systemctl start php-fpm
sudo systemctl enable php-fpm
```

#### STEP 4: Install SQL Server (CentOS)
```bash
# Add Microsoft SQL Server repository
sudo curl -o /etc/yum.repos.d/mssql-server.repo https://packages.microsoft.com/config/rhel/8/mssql-server-2019.repo

# Install SQL Server
sudo dnf install -y mssql-server

# Configure SQL Server
sudo /opt/mssql/bin/mssql-conf setup

# Install command-line tools
sudo dnf install -y mssql-tools18

# Add to PATH
echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> ~/.bashrc
source ~/.bashrc
```

*Continue with steps 5-10 similar to Ubuntu, adjusting package manager commands from `apt` to `dnf`*

### 🚨 TROUBLESHOOTING VPS LINUX

#### Issue 1: "SSH Connection Refused"
```bash
# Check SSH service
sudo systemctl status ssh    # Ubuntu
sudo systemctl status sshd   # CentOS

# Check firewall
sudo ufw status              # Ubuntu
sudo firewall-cmd --list-all # CentOS

# Check SSH configuration
sudo nano /etc/ssh/sshd_config
# Ensure: Port 22, PermitRootLogin yes (initially)
```

#### Issue 2: "Nginx 403 Forbidden"
```bash
# Check file permissions
ls -la /var/www/tjboba/public/

# Fix permissions
sudo chown -R www-data:www-data /var/www/tjboba/  # Ubuntu
sudo chown -R nginx:nginx /var/www/tjboba/        # CentOS

# Check SELinux (CentOS)
sudo setsebool -P httpd_can_network_connect 1
sudo setsebool -P httpd_unified 1
```

#### Issue 3: "SQL Server Connection Failed"
```bash
# Check SQL Server service
sudo systemctl status mssql-server

# Check firewall
sudo ufw allow 1433           # Ubuntu
sudo firewall-cmd --permanent --add-port=1433/tcp # CentOS
sudo firewall-cmd --reload

# Test connection
sqlcmd -S localhost -U sa -P 'YourPassword' -Q "SELECT 1"
```

---

## 🌐 SHARED HOSTING

### 📋 SHARED HOSTING REQUIREMENTS

#### Hosting Specifications
- **PHP**: 8.1+ with required extensions
- **Database**: MySQL/MariaDB (SQL Server usually not available)
- **Storage**: 5GB+ for application files
- **Control Panel**: cPanel/Plesk preferred
- **SSH Access**: Optional but helpful
- **Composer Support**: Required

#### Recommended Providers
- **Hostinger**: PHP 8.1, SSH access, good performance
- **SiteGround**: PHP 8.1, staging environments
- **A2 Hosting**: PHP 8.1, SSH, developer tools
- **InMotion**: PHP 8.1, free SSL, staging

### 🚨 IMPORTANT: SQL SERVER LIMITATION

**⚠️ CRITICAL NOTE**: Most shared hosting providers do **NOT** support SQL Server. You'll need to modify Tjboba to use MySQL/MariaDB instead.

### 🔄 ADAPT TJBOBA FOR MYSQL

#### STEP 1: Modify Database Configuration
```php
// config/database.php - Change default connection
'default' => env('DB_CONNECTION', 'mysql'), // Changed from 'sqlsrv'

// Ensure MySQL configuration exists
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
],
```

#### STEP 2: Convert SQL Server Syntax to MySQL
```php
// Example: Replace SQL Server specific code

// ❌ SQL Server syntax (remove these)
// String concatenation: 'name + " - " + code'
// CASE WHEN statements can remain (MySQL supports)
// CONVERT(VARCHAR, date, format) - replace with DATE_FORMAT

// ✅ MySQL syntax (use these)
// String concatenation: CONCAT('name', ' - ', 'code')
// Date formatting: DATE_FORMAT(date, '%d/%m/%Y')

// Example in Models:
public function getDisplayNameAttribute(): string
{
    // SQL Server version:
    // return DB::selectOne("SELECT name + ' - ' + code as display_name FROM table WHERE id = ?", [$this->id])->display_name;
    
    // MySQL version:
    return DB::selectOne("SELECT CONCAT(name, ' - ', code) as display_name FROM table WHERE id = ?", [$this->id])->display_name;
}
```

#### STEP 3: Update Migrations for MySQL
```php
// database/migrations - Update for MySQL compatibility

// Example: Convert SQL Server data types
Schema::create('kas_bank', function (Blueprint $table) {
    $table->id();
    $table->string('kode', 20)->unique();
    $table->string('nama', 100);
    $table->decimal('saldo_awal', 15, 2)->default(0);
    $table->boolean('status')->default(true);
    $table->text('keterangan')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    // MySQL indexes
    $table->index(['status', 'deleted_at']);
    $table->index('kode');
    $table->index('created_at');
});
```

### 🚀 DEPLOYMENT TO SHARED HOSTING

#### STEP 1: Prepare Application Locally
```bash
# Clone and prepare application
git clone https://github.com/majnunfillah/Tjboba.git tjboba-shared
cd tjboba-shared

# Install dependencies
composer install --optimize-autoloader --no-dev

# Build assets
npm ci --production
npm run build

# Configure for shared hosting
cp .env.example .env

# Configure .env for MySQL
cat > .env << 'EOF'
APP_NAME=Tjboba
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
EOF

# Generate application key
php artisan key:generate

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear development files
rm -rf .git node_modules .env.example .gitignore
```

#### STEP 2: Upload via cPanel File Manager
```text
1. Login to cPanel
2. Open File Manager
3. Navigate to public_html (or subdomain folder)
4. Upload tjboba files:
   - Upload all files EXCEPT public folder contents to public_html/
   - Upload public folder contents directly to public_html/
   
Final structure:
public_html/
├── index.php (from public folder)
├── .htaccess (from public folder)
├── css/ (from public/build)
├── js/ (from public/build)
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
└── artisan
```

#### STEP 3: Create Database via cPanel
```text
1. In cPanel, open "MySQL Databases"
2. Create new database: "tjboba_db"
3. Create database user: "tjboba_user"
4. Set password: "secure_password_123"
5. Add user to database with ALL PRIVILEGES
6. Note the full database name (usually: username_tjboba_db)
```

#### STEP 4: Update Configuration
```bash
# Update .env file via cPanel File Manager
# Or via SSH if available:

cat > .env << 'EOF'
APP_NAME=Tjboba
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=username_tjboba_db
DB_USERNAME=username_tjboba_user
DB_PASSWORD=secure_password_123

# ... rest of config
EOF
```

#### STEP 5: Run Migrations via SSH (if available)
```bash
# Connect via SSH
ssh your_username@yourdomain.com

# Navigate to application
cd public_html

# Run migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### STEP 6: Alternative - Database Import
If SSH is not available:
```text
1. Export local database:
   mysqldump -u root -p tjboba_db > tjboba_db.sql

2. Import via cPanel phpMyAdmin:
   - Open phpMyAdmin
   - Select your database
   - Click Import
   - Upload tjboba_db.sql
   - Execute
```

### 🔧 SHARED HOSTING OPTIMIZATIONS

#### A. .htaccess Configuration
```apache
# public_html/.htaccess
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "no-referrer-when-downgrade"
</IfModule>

# Cache control
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

#### B. Cron Jobs for Laravel Scheduler
```text
# In cPanel, create cron job:
* * * * * /usr/bin/php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1
```

### 🚨 TROUBLESHOOTING SHARED HOSTING

#### Issue 1: "500 Internal Server Error"
```bash
# Check error logs
# In cPanel → Error Logs → View latest errors

# Common causes and solutions:
# 1. Wrong file permissions
chmod 644 .env
chmod 755 storage/ -R
chmod 755 bootstrap/cache/ -R

# 2. Missing .htaccess
# Ensure .htaccess exists in public_html with Laravel rewrite rules

# 3. PHP version mismatch
# In cPanel → PHP Selector → Choose PHP 8.1+

# 4. Memory limit exceeded
# Add to .htaccess:
php_value memory_limit 512M

# 5. Missing PHP extensions
# In cPanel → PHP Extensions → Enable required extensions
```

#### Issue 2: "Database Connection Error"
```bash
# Verify database credentials
# Check in cPanel → MySQL Databases

# Test connection
php -r "
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=username_dbname', 'username_user', 'password');
    echo 'Connection successful';
} catch(PDOException \$e) {
    echo 'Connection failed: ' . \$e->getMessage();
}
"

# Common issues:
# 1. Wrong database prefix (username_dbname)
# 2. User not added to database
# 3. Wrong host (might be server-specific)
```

#### Issue 3: "Class not found" errors
```bash
# Regenerate autoloader
composer dump-autoload --optimize

# Clear caches
php artisan cache:clear
php artisan config:clear

# If no SSH access, delete cache files manually:
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
```

#### Issue 4: "Storage not writable"
```bash
# Set correct permissions via cPanel File Manager
# Select storage folder → Permissions → 755
# Select bootstrap/cache → Permissions → 755

# Or via SSH:
chmod 755 storage/ -R
chmod 755 bootstrap/cache/ -R

# If using FTP:
# Set directory permissions to 755
# Set file permissions to 644
```

#### Issue 5: "Assets not loading"
```bash
# Check asset paths in .env
APP_URL=https://yourdomain.com

# Ensure assets are in correct location
# Laravel assets should be in public_html/build/

# Clear cache
php artisan config:cache
php artisan view:cache

# Force asset recompilation locally then upload
npm run build
```

---

## 💻 VIRTUAL MACHINE (VIRTUALBOX)

### 📋 VM REQUIREMENTS

#### Host System Requirements
- **CPU**: Intel VT-x or AMD-V virtualization support
- **RAM**: 8GB+ (4GB for host, 4GB+ for VM)
- **Storage**: 100GB+ free space
- **OS**: Windows 10/11, macOS, or Linux

#### VM Specifications
- **vCPU**: 2+ cores
- **RAM**: 4GB+ (8GB recommended)
- **Storage**: 50GB+ dynamic disk
- **Network**: NAT or Bridged Adapter

### 🔧 STEP 1: INSTALL VIRTUALBOX

#### Windows Installation
```powershell
# Download VirtualBox
$vboxUrl = "https://download.virtualbox.org/virtualbox/7.0.12/VirtualBox-7.0.12-159484-Win.exe"
Invoke-WebRequest -Uri $vboxUrl -OutFile "C:\temp\VirtualBox-installer.exe"

# Install VirtualBox
Start-Process -FilePath "C:\temp\VirtualBox-installer.exe" -ArgumentList "/S" -Wait

# Download Extension Pack
$extPackUrl = "https://download.virtualbox.org/virtualbox/7.0.12/Oracle_VM_VirtualBox_Extension_Pack-7.0.12.vbox-extpack"
Invoke-WebRequest -Uri $extPackUrl -OutFile "C:\temp\VirtualBox_ExtPack.vbox-extpack"

# Install Extension Pack
& "C:\Program Files\Oracle\VirtualBox\VBoxManage.exe" extpack install "C:\temp\VirtualBox_ExtPack.vbox-extpack" --accept-license
```

#### macOS Installation
```bash
# Download VirtualBox for macOS
curl -L "https://download.virtualbox.org/virtualbox/7.0.12/VirtualBox-7.0.12-159484-OSX.dmg" -o ~/Downloads/VirtualBox.dmg

# Mount and install
sudo hdiutil attach ~/Downloads/VirtualBox.dmg
sudo installer -pkg "/Volumes/VirtualBox/VirtualBox.pkg" -target /

# Install Extension Pack
curl -L "https://download.virtualbox.org/virtualbox/7.0.12/Oracle_VM_VirtualBox_Extension_Pack-7.0.12.vbox-extpack" -o ~/Downloads/VirtualBox_ExtPack.vbox-extpack
VBoxManage extpack install ~/Downloads/VirtualBox_ExtPack.vbox-extpack --accept-license
```

#### Linux Installation
```bash
# Ubuntu/Debian
wget -q https://www.virtualbox.org/download/oracle_vbox_2016.asc -O- | sudo apt-key add -
echo "deb [arch=amd64] https://download.virtualbox.org/virtualbox/debian $(lsb_release -cs) contrib" | sudo tee /etc/apt/sources.list.d/virtualbox.list
sudo apt update
sudo apt install -y virtualbox-7.0

# CentOS/RHEL
sudo dnf config-manager --add-repo=https://download.virtualbox.org/virtualbox/rpm/el/virtualbox.repo
sudo dnf install -y VirtualBox-7.0

# Install Extension Pack
wget https://download.virtualbox.org/virtualbox/7.0.12/Oracle_VM_VirtualBox_Extension_Pack-7.0.12.vbox-extpack
sudo VBoxManage extpack install Oracle_VM_VirtualBox_Extension_Pack-7.0.12.vbox-extpack --accept-license
```

### 🔧 STEP 2: CREATE VIRTUAL MACHINE

#### A. Ubuntu Server VM Creation
```bash
# Create VM via VBoxManage CLI
VBoxManage createvm --name "TjbobaServer" --ostype "Ubuntu_64" --register

# Configure VM settings
VBoxManage modifyvm "TjbobaServer" \
    --memory 4096 \
    --cpus 2 \
    --vram 128 \
    --accelerate3d on \
    --nic1 nat \
    --natpf1 "SSH,tcp,,2222,,22" \
    --natpf1 "HTTP,tcp,,8080,,80" \
    --natpf1 "HTTPS,tcp,,8443,,443" \
    --audio none \
    --usb off

# Create storage
VBoxManage createhd --filename "TjbobaServer.vdi" --size 51200 --format VDI

# Attach storage
VBoxManage storagectl "TjbobaServer" --name "SATA" --add sata --controller IntelAhci
VBoxManage storageattach "TjbobaServer" --storagectl "SATA" --port 0 --device 0 --type hdd --medium "TjbobaServer.vdi"

# Download Ubuntu Server ISO
curl -L "https://releases.ubuntu.com/22.04/ubuntu-22.04.3-live-server-amd64.iso" -o ubuntu-22.04-server.iso

# Attach ISO
VBoxManage storagectl "TjbobaServer" --name "IDE" --add ide
VBoxManage storageattach "TjbobaServer" --storagectl "IDE" --port 0 --device 0 --type dvddrive --medium ubuntu-22.04-server.iso

# Start VM
VBoxManage startvm "TjbobaServer" --type gui
```

#### B. Ubuntu Server Installation (GUI Steps)
```text
1. Boot from ISO
2. Select "Ubuntu Server"
3. Choose language: English
4. Keyboard layout: English (US)
5. Network configuration: 
   - Use DHCP (default)
   - Configure static IP if needed
6. Proxy: None (unless required)
7. Archive mirror: Default
8. Guided storage: Use entire disk
9. Profile setup:
   - Name: Tjboba Admin
   - Server name: tjboba-server
   - Username: tjboba
   - Password: SecurePassword123!
10. SSH setup: Install OpenSSH server
11. Snaps: Skip all (install manually later)
12. Installation: Wait for completion
13. Reboot and remove ISO
```

#### C. Post-Installation VM Configuration
```bash
# SSH into VM from host
ssh tjboba@localhost -p 2222

# Update system
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget gnupg2 software-properties-common apt-transport-https ca-certificates

# Install Guest Additions for better performance
sudo apt install -y build-essential dkms linux-headers-$(uname -r)

# Mount Guest Additions CD (from VirtualBox menu: Devices → Insert Guest Additions CD)
sudo mkdir /mnt/cdrom
sudo mount /dev/cdrom /mnt/cdrom
cd /mnt/cdrom
sudo ./VBoxLinuxAdditions.run

# Reboot to apply Guest Additions
sudo reboot
```

### 🔧 STEP 3: INSTALL TJBOBA STACK ON VM

#### A. Install Web Server Stack
```bash
# SSH back into VM
ssh tjboba@localhost -p 2222

# Install Nginx
sudo apt install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Install PHP 8.1
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.1-fpm php8.1-cli php8.1-common php8.1-mysql php8.1-xml php8.1-curl php8.1-gd php8.1-mbstring php8.1-zip php8.1-bcmath php8.1-json php8.1-tokenizer

# Configure PHP-FPM
sudo systemctl start php8.1-fpm
sudo systemctl enable php8.1-fpm
```

#### B. Install Database Server
```bash
# Option 1: MySQL (Recommended for VM)
sudo apt install -y mysql-server

# Secure MySQL installation
sudo mysql_secure_installation
# Set root password: TjbobaMySQL123!
# Remove anonymous users: Y
# Disallow root login remotely: Y
# Remove test database: Y
# Reload privilege tables: Y

# Create database and user
sudo mysql -u root -p << EOF
CREATE DATABASE tjboba_db;
CREATE USER 'tjboba_user'@'localhost' IDENTIFIED BY 'TjbobaUser123!';
GRANT ALL PRIVILEGES ON tjboba_db.* TO 'tjboba_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
EOF

# Option 2: SQL Server on Linux (if preferred)
# Follow previous SQL Server installation steps for Ubuntu
```

#### C. Install Development Tools
```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs

# Install Git
sudo apt install -y git

# Verify installations
php --version
composer --version
node --version
npm --version
git --version
```

### 🔧 STEP 4: DEPLOY TJBOBA APPLICATION

#### A. Clone and Setup Application
```bash
# Create web directory
sudo mkdir -p /var/www/tjboba
sudo chown -R tjboba:www-data /var/www/tjboba

# Clone repository
cd /var/www
git clone https://github.com/majnunfillah/Tjboba.git tjboba
cd tjboba

# Install dependencies
composer install --optimize-autoloader

# Copy environment file
cp .env.example .env

# Configure .env for MySQL
cat > .env << 'EOF'
APP_NAME=Tjboba
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tjboba_db
DB_USERNAME=tjboba_user
DB_PASSWORD=TjbobaUser123!

CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOF

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Install and build assets
npm install
npm run build

# Set permissions
sudo chown -R www-data:www-data /var/www/tjboba/storage
sudo chown -R www-data:www-data /var/www/tjboba/bootstrap/cache
sudo chmod -R 755 /var/www/tjboba/storage
sudo chmod -R 755 /var/www/tjboba/bootstrap/cache

# Create storage symlink
php artisan storage:link
```

#### B. Configure Nginx
```bash
# Create Nginx site configuration
sudo tee /etc/nginx/sites-available/tjboba << 'EOF'
server {
    listen 80;
    server_name localhost;
    root /var/www/tjboba/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable site
sudo ln -s /etc/nginx/sites-available/tjboba /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test and restart Nginx
sudo nginx -t
sudo systemctl restart nginx
```

### 🔧 STEP 5: ACCESS FROM HOST MACHINE

#### A. Port Forwarding Configuration
The VM is already configured with port forwarding:
- SSH: Host port 2222 → VM port 22
- HTTP: Host port 8080 → VM port 80
- HTTPS: Host port 8443 → VM port 443

#### B. Test Application Access
```bash
# From host machine browser:
# http://localhost:8080

# From host machine terminal:
curl -I http://localhost:8080

# SSH access from host:
ssh tjboba@localhost -p 2222
```

#### C. File Sharing Setup (Optional)
```bash
# Install VirtualBox Guest Additions (if not done earlier)
# From VirtualBox menu: Devices → Insert Guest Additions CD

# Create shared folder in VirtualBox:
# Settings → Shared Folders → Add
# Folder Path: C:\Development\tjboba-shared (on Windows host)
# Folder Name: tjboba-shared
# Auto-mount: Yes
# Make Permanent: Yes

# Mount shared folder in VM
sudo mkdir -p /mnt/shared
sudo mount -t vboxsf tjboba-shared /mnt/shared

# Add to fstab for permanent mounting
echo "tjboba-shared /mnt/shared vboxsf defaults 0 0" | sudo tee -a /etc/fstab

# Add user to vboxsf group
sudo usermod -aG vboxsf tjboba
```

### 🚨 TROUBLESHOOTING VIRTUALBOX

#### Issue 1: "VM won't start"
```bash
# Check VT-x/AMD-V enabled in BIOS
# Windows: Check Hyper-V is disabled
dism.exe /Online /Disable-Feature:Microsoft-Hyper-V

# Check if virtualization is enabled
# Windows PowerShell:
Get-ComputerInfo -Property "HyperV*"

# macOS: Check if System Integrity Protection allows virtualization
csrutil status
```

#### Issue 2: "Poor VM performance"
```bash
# Increase VM resources
VBoxManage modifyvm "TjbobaServer" --memory 8192 --cpus 4

# Enable 3D acceleration
VBoxManage modifyvm "TjbobaServer" --accelerate3d on

# Disable visual effects in Ubuntu
sudo apt install -y ubuntu-desktop-minimal
# Then configure minimal desktop environment
```

#### Issue 3: "Network connectivity issues"
```bash
# Check VM network settings
VBoxManage showvminfo "TjbobaServer" | grep -i nic

# Reset network interface
sudo systemctl restart networking

# Check firewall
sudo ufw status
sudo ufw allow 80
sudo ufw allow 443
sudo ufw allow 22
```

#### Issue 4: "Cannot access application from host"
```bash
# Verify port forwarding
VBoxManage showvminfo "TjbobaServer" | grep "NIC.*Rule"

# Test from within VM
curl -I http://localhost

# Check Nginx status
sudo systemctl status nginx

# Check PHP-FPM status
sudo systemctl status php8.1-fpm

# Check application logs
tail -f /var/log/nginx/error.log
tail -f /var/www/tjboba/storage/logs/laravel.log
```

#### Issue 5: "Shared folders not working"
```bash
# Reinstall Guest Additions
sudo apt install -y build-essential dkms linux-headers-$(uname -r)

# Mount Guest Additions CD from VirtualBox menu
sudo mkdir -p /mnt/cdrom
sudo mount /dev/cdrom /mnt/cdrom
cd /mnt/cdrom
sudo ./VBoxLinuxAdditions.run --nox11

# Reboot VM
sudo reboot

# Check vboxsf module
lsmod | grep vboxsf

# Manually mount shared folder
sudo mount -t vboxsf -o uid=1000,gid=1000 tjboba-shared /mnt/shared
```

---

## 🐳 DOCKER CONTAINERS

### 📋 DOCKER REQUIREMENTS

#### System Requirements
- **Docker**: 20.10+ with Docker Compose v2
- **RAM**: 4GB+ available for containers
- **Storage**: 10GB+ for images and volumes
- **OS**: Windows 10/11 with WSL2, macOS, or Linux

### 🔧 STEP 1: INSTALL DOCKER

#### Windows Installation
```powershell
# Install Docker Desktop for Windows
$dockerUrl = "https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe"
Invoke-WebRequest -Uri $dockerUrl -OutFile "C:\temp\DockerDesktop.exe"

# Install Docker Desktop
Start-Process -FilePath "C:\temp\DockerDesktop.exe" -ArgumentList "install --quiet" -Wait

# Restart computer
# Start Docker Desktop after restart

# Verify installation
docker --version
docker-compose --version
```

#### macOS Installation
```bash
# Install Docker Desktop for Mac
curl -L "https://desktop.docker.com/mac/main/amd64/Docker.dmg" -o ~/Downloads/Docker.dmg

# Mount and install
sudo hdiutil attach ~/Downloads/Docker.dmg
sudo cp -R "/Volumes/Docker/Docker.app" /Applications/
sudo hdiutil detach "/Volumes/Docker"

# Start Docker Desktop from Applications
# Verify installation
docker --version
docker-compose --version
```

#### Linux Installation
```bash
# Ubuntu/Debian
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user to docker group
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Start Docker service
sudo systemctl start docker
sudo systemctl enable docker

# Verify installation
docker --version
docker-compose --version
```

### 🔧 STEP 2: CREATE DOCKER ENVIRONMENT

#### A. Create Project Structure
```bash
# Create project directory
mkdir tjboba-docker
cd tjboba-docker

# Create directory structure
mkdir -p {nginx,php,mysql,logs}
```

#### B. Create Docker Compose Configuration
```yaml
# docker-compose.yml
version: '3.8'

services:
  # Nginx Web Server
  nginx:
    image: nginx:alpine
    container_name: tjboba_nginx
    ports:
      - "8080:80"
      - "8443:443"
    volumes:
      - ./:/var/www/html
      - ./nginx/nginx.conf:/etc/nginx/conf.d/default.conf
      - ./logs/nginx:/var/log/nginx
    depends_on:
      - php
    networks:
      - tjboba-network

  # PHP-FPM
  php:
    build: 
      context: ./php
      dockerfile: Dockerfile
    container_name: tjboba_php
    volumes:
      - ./:/var/www/html
      - ./php/php.ini:/usr/local/etc/php/php.ini
    environment:
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=tjboba_db
      - DB_USERNAME=tjboba_user
      - DB_PASSWORD=TjbobaUser123!
    depends_on:
      - mysql
    networks:
      - tjboba-network

  # MySQL Database
  mysql:
    image: mysql:8.0
    container_name: tjboba_mysql
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: TjbobaRoot123!
      MYSQL_DATABASE: tjboba_db
      MYSQL_USER: tjboba_user
      MYSQL_PASSWORD: TjbobaUser123!
    volumes:
      - mysql_data:/var/lib/mysql
      - ./mysql/my.cnf:/etc/mysql/conf.d/my.cnf
    networks:
      - tjboba-network

  # Redis (for caching and sessions)
  redis:
    image: redis:alpine
    container_name: tjboba_redis
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - tjboba-network

  # Composer (for dependency management)
  composer:
    image: composer:latest
    container_name: tjboba_composer
    volumes:
      - ./:/app
    working_dir: /app
    networks:
      - tjboba-network

  # Node.js (for asset compilation)
  node:
    image: node:18-alpine
    container_name: tjboba_node
    volumes:
      - ./:/var/www/html
    working_dir: /var/www/html
    networks:
      - tjboba-network

volumes:
  mysql_data:
  redis_data:

networks:
  tjboba-network:
    driver: bridge
```

#### C. Create PHP Dockerfile
```dockerfile
# php/Dockerfile
FROM php:8.1-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    postgresql-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        mbstring \
        xml \
        ctype \
        json \
        bcmath \
        zip \
        gd \
        fileinfo \
        tokenizer

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy PHP configuration
COPY php.ini /usr/local/etc/php/

# Create user for Laravel
RUN addgroup -g 1000 www && \
    adduser -u 1000 -G www -s /bin/sh -D www

# Change ownership of application directory
RUN chown -R www:www /var/www/html

# Switch to non-root user
USER www

# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"]
```

#### D. Create PHP Configuration
```ini
# php/php.ini
[PHP]
post_max_size = 100M
upload_max_filesize = 100M
variables_order = EGPCS
memory_limit = 512M
max_execution_time = 300
max_input_vars = 3000

[Date]
date.timezone = Asia/Jakarta

[opcache]
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

#### E. Create Nginx Configuration
```nginx
# nginx/nginx.conf
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
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
        fastcgi_pass php:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Asset caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    error_log /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
}
```

#### F. Create MySQL Configuration
```ini
# mysql/my.cnf
[mysqld]
default-storage-engine=innodb
innodb_buffer_pool_size=256M
innodb_log_file_size=256M
innodb_flush_log_at_trx_commit=1
innodb_flush_method=O_DIRECT

max_allowed_packet=256M
max_connections=200

sql_mode=STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO

[mysql]
default-character-set=utf8mb4

[client]
default-character-set=utf8mb4
```

### 🔧 STEP 3: DEPLOY TJBOBA APPLICATION

#### A. Clone Application
```bash
# Clone Tjboba repository
git clone https://github.com/majnunfillah/Tjboba.git .

# Ensure we're in the right directory
ls -la
# Should see: app/, bootstrap/, config/, docker-compose.yml, etc.
```

#### B. Build and Start Containers
```bash
# Build and start all containers
docker-compose up -d --build

# Check container status
docker-compose ps

# Should show all containers running:
# tjboba_nginx, tjboba_php, tjboba_mysql, tjboba_redis
```

#### C. Install Dependencies
```bash
# Install PHP dependencies
docker-compose run --rm composer install --optimize-autoloader

# Install Node.js dependencies and build assets
docker-compose run --rm node npm ci
docker-compose run --rm node npm run build
```

#### D. Configure Application
```bash
# Copy environment file
cp .env.example .env

# Configure .env for Docker
cat > .env << 'EOF'
APP_NAME=Tjboba
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=tjboba_db
DB_USERNAME=tjboba_user
DB_PASSWORD=TjbobaUser123!

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@tjboba.com"
MAIL_FROM_NAME="${APP_NAME}"
EOF

# Generate application key
docker-compose exec php php artisan key:generate

# Run migrations
docker-compose exec php php artisan migrate

# Seed database (optional)
docker-compose exec php php artisan db:seed

# Cache configurations
docker-compose exec php php artisan config:cache
docker-compose exec php php artisan route:cache
docker-compose exec php php artisan view:cache

# Create storage symlink
docker-compose exec php php artisan storage:link

# Set proper permissions
docker-compose exec php chown -R www:www /var/www/html/storage
docker-compose exec php chown -R www:www /var/www/html/bootstrap/cache
```

### 🔧 STEP 4: DOCKER DEVELOPMENT WORKFLOW

#### A. Daily Development Commands
```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# View logs
docker-compose logs -f nginx
docker-compose logs -f php
docker-compose logs -f mysql

# Execute commands in containers
docker-compose exec php php artisan migrate
docker-compose exec php php artisan tinker
docker-compose exec mysql mysql -u tjboba_user -p tjboba_db

# Install new PHP dependencies
docker-compose run --rm composer require package/name

# Install new Node dependencies
docker-compose run --rm node npm install package-name

# Watch assets for changes
docker-compose run --rm node npm run dev
```

#### B. Useful Docker Scripts
```bash
# Create convenience script: docker-dev.sh
cat > docker-dev.sh << 'EOF'
#!/bin/bash

case "$1" in
    start)
        docker-compose up -d
        echo "Tjboba containers started"
        echo "Application: http://localhost:8080"
        ;;
    stop)
        docker-compose down
        echo "Tjboba containers stopped"
        ;;
    restart)
        docker-compose down
        docker-compose up -d
        echo "Tjboba containers restarted"
        ;;
    logs)
        docker-compose logs -f
        ;;
    php)
        docker-compose exec php bash
        ;;
    mysql)
        docker-compose exec mysql mysql -u tjboba_user -p tjboba_db
        ;;
    artisan)
        shift
        docker-compose exec php php artisan "$@"
        ;;
    composer)
        shift
        docker-compose run --rm composer "$@"
        ;;
    npm)
        shift
        docker-compose run --rm node npm "$@"
        ;;
    fresh)
        docker-compose down -v
        docker-compose up -d --build
        docker-compose run --rm composer install
        docker-compose run --rm node npm ci
        docker-compose run --rm node npm run build
        docker-compose exec php php artisan migrate --seed
        echo "Fresh installation completed"
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|logs|php|mysql|artisan|composer|npm|fresh}"
        exit 1
        ;;
esac
EOF

chmod +x docker-dev.sh

# Usage examples:
./docker-dev.sh start
./docker-dev.sh artisan migrate
./docker-dev.sh composer install
./docker-dev.sh npm run dev
```

### 🚨 TROUBLESHOOTING DOCKER

#### Issue 1: "Port already in use"
```bash
# Check what's using the port
lsof -i :8080  # On macOS/Linux
netstat -ano | findstr :8080  # On Windows

# Kill process or change port in docker-compose.yml
# Change "8080:80" to "8081:80"

# Or stop conflicting services
sudo service apache2 stop
sudo service nginx stop
```

#### Issue 2: "Container exits immediately"
```bash
# Check container logs
docker-compose logs php
docker-compose logs mysql

# Check if services are healthy
docker-compose ps

# Restart specific container
docker-compose restart php
```

#### Issue 3: "Database connection failed"
```bash
# Check MySQL container logs
docker-compose logs mysql

# Verify database credentials
docker-compose exec mysql mysql -u root -p
# Password: TjbobaRoot123!

# Test connection from PHP container
docker-compose exec php php -r "
try {
    \$pdo = new PDO('mysql:host=mysql;dbname=tjboba_db', 'tjboba_user', 'TjbobaUser123!');
    echo 'Connection successful';
} catch(PDOException \$e) {
    echo 'Connection failed: ' . \$e->getMessage();
}
"
```

#### Issue 4: "Permission denied errors"
```bash
# Fix file permissions
docker-compose exec php chown -R www:www /var/www/html
docker-compose exec php chmod -R 755 /var/www/html/storage
docker-compose exec php chmod -R 755 /var/www/html/bootstrap/cache

# Rebuild containers with proper permissions
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

#### Issue 5: "Assets not loading"
```bash
# Rebuild assets
docker-compose run --rm node npm run build

# Check Nginx configuration
docker-compose exec nginx nginx -t

# Restart Nginx
docker-compose restart nginx

# Check asset paths
docker-compose exec php ls -la /var/www/html/public/build/
```

#### Issue 6: "Docker runs out of space"
```bash
# Clean up unused containers and images
docker system prune -a

# Remove unused volumes
docker volume prune

# Check disk usage
docker system df

# Remove specific containers/images
docker-compose down
docker rmi $(docker images -q)
```

---

## 🔚 CONCLUSION

Panduan instalasi super lengkap ini mencakup **6 environment berbeda** dengan detail troubleshooting untuk setiap skenario. Setiap environment memiliki karakteristik dan kebutuhan yang berbeda:

### 📊 **RINGKASAN ENVIRONMENT:**

| **Environment** | **Complexity** | **Performance** | **Best For** |
|----------------|----------------|-----------------|--------------|
| **Windows Server + IIS** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Production, Enterprise |
| **Windows Development** | ⭐⭐⭐ | ⭐⭐⭐⭐ | Local Development |
| **VPS Linux** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Production, Scalable |
| **Shared Hosting** | ⭐⭐ | ⭐⭐⭐ | Budget, Simple Deploy |
| **VirtualBox VM** | ⭐⭐⭐ | ⭐⭐⭐ | Learning, Testing |
| **Docker** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | Development, DevOps |

### 🎯 **PILIHAN RECOMMENDED:**

- **Pemula**: Docker atau Windows Development
- **Production**: VPS Linux atau Windows Server 
- **Budget Terbatas**: Shared Hosting (dengan MySQL)
- **Learning**: VirtualBox VM
- **Enterprise**: Windows Server + IIS

Setiap panduan dilengkapi dengan **troubleshooting section** yang comprehensive untuk membantu mengatasi masalah umum yang sering dihadapi developer pemula maupun advance.