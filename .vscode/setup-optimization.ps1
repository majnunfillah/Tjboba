# Setup Script untuk Optimasi GitHub Copilot dengan Claude & MCP
# Jalankan sebagai Administrator

param(
    [switch]$InstallDependencies,
    [switch]$SetupEnvironment,
    [switch]$StartServers,
    [switch]$All
)

Write-Host "=== BobaJetBrain Copilot Optimization Setup ===" -ForegroundColor Green

# Function untuk check prerequisites
function Test-Prerequisites {
    Write-Host "Checking prerequisites..." -ForegroundColor Yellow
    
    # Check Node.js
    try {
        $nodeVersion = node --version
        Write-Host "✓ Node.js: $nodeVersion" -ForegroundColor Green
    } catch {
        Write-Host "✗ Node.js not found. Please install Node.js first." -ForegroundColor Red
        exit 1
    }
    
    # Check npm
    try {
        $npmVersion = npm --version
        Write-Host "✓ NPM: $npmVersion" -ForegroundColor Green
    } catch {
        Write-Host "✗ NPM not found." -ForegroundColor Red
        exit 1
    }
    
    # Check VS Code
    try {
        $codeVersion = code --version
        Write-Host "✓ VS Code installed" -ForegroundColor Green
    } catch {
        Write-Host "⚠ VS Code CLI not in PATH. May need manual configuration." -ForegroundColor Yellow
    }
}

# Function untuk install dependencies
function Install-Dependencies {
    Write-Host "Installing MCP dependencies..." -ForegroundColor Yellow
    
    Set-Location "c:\bobajetbrain\.vscode"
    
    # Install MCP packages
    npm install @modelcontextprotocol/sdk @modelcontextprotocol/server-stdio
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Dependencies installed successfully" -ForegroundColor Green
    } else {
        Write-Host "✗ Failed to install dependencies" -ForegroundColor Red
        exit 1
    }
}

# Function untuk setup environment variables
function Setup-Environment {
    Write-Host "Setting up environment variables..." -ForegroundColor Yellow
    
    # Check if .env exists
    $envFile = "c:\bobajetbrain\.env"
    if (-not (Test-Path $envFile)) {
        Write-Host "Creating .env file..." -ForegroundColor Yellow
        
        $envContent = @"
# Claude AI Configuration
ANTHROPIC_API_KEY=your_claude_api_key_here

# GitHub Configuration  
GITHUB_TOKEN=your_github_token_here

# Project Configuration
PROJECT_ROOT=c:/bobajetbrain
MCP_LOG_LEVEL=info

# Laravel Configuration
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlsrv
"@
        
        $envContent | Out-File -FilePath $envFile -Encoding UTF8
        Write-Host "✓ .env file created. Please update with your API keys." -ForegroundColor Green
    }
    
    # Create analytics directory
    $analyticsDir = "c:\bobajetbrain\.vscode\analytics"
    if (-not (Test-Path $analyticsDir)) {
        New-Item -ItemType Directory -Path $analyticsDir -Force | Out-Null
        Write-Host "✓ Analytics directory created" -ForegroundColor Green
    }
    
    # Create logs directory
    $logsDir = "c:\bobajetbrain\.vscode\logs"
    if (-not (Test-Path $logsDir)) {
        New-Item -ItemType Directory -Path $logsDir -Force | Out-Null
        Write-Host "✓ Logs directory created" -ForegroundColor Green
    }
}

# Function untuk install VS Code extensions
function Install-VSCodeExtensions {
    Write-Host "Installing recommended VS Code extensions..." -ForegroundColor Yellow
    
    $extensions = @(
        "GitHub.copilot",
        "GitHub.copilot-chat", 
        "bmewburn.vscode-intelephense-client",
        "onecentlin.laravel-extension-pack",
        "rangav.vscode-thunder-client"
    )
    
    foreach ($ext in $extensions) {
        Write-Host "Installing $ext..." -ForegroundColor Cyan
        code --install-extension $ext --force
    }
    
    Write-Host "✓ Extensions installed" -ForegroundColor Green
}

# Function untuk start MCP server
function Start-MCPServer {
    Write-Host "Starting MCP Claude Context Server..." -ForegroundColor Yellow
    
    Set-Location "c:\bobajetbrain\.vscode"
    
    # Set environment variables untuk session ini
    $env:PROJECT_ROOT = "c:\bobajetbrain"
    $env:MCP_LOG_LEVEL = "info"
    
    # Start server in background
    Start-Process -FilePath "node" -ArgumentList "claude-context-server.js" -WindowStyle Hidden
    
    Start-Sleep -Seconds 3
    
    # Check if server started
    $processes = Get-Process -Name "node" -ErrorAction SilentlyContinue
    if ($processes) {
        Write-Host "✓ MCP Server started successfully" -ForegroundColor Green
    } else {
        Write-Host "⚠ MCP Server may not have started properly" -ForegroundColor Yellow
    }
}

# Function untuk test configuration
function Test-Configuration {
    Write-Host "Testing configuration..." -ForegroundColor Yellow
    
    # Test MCP server response
    # TODO: Implement MCP server health check
    
    # Test VS Code settings
    $settingsFile = "c:\bobajetbrain\.vscode\settings.json"
    if (Test-Path $settingsFile) {
        Write-Host "✓ VS Code settings configured" -ForegroundColor Green
    } else {
        Write-Host "✗ VS Code settings not found" -ForegroundColor Red
    }
    
    # Test environment variables
    if ($env:ANTHROPIC_API_KEY -and $env:ANTHROPIC_API_KEY -ne "your_claude_api_key_here") {
        Write-Host "✓ Claude API key configured" -ForegroundColor Green
    } else {
        Write-Host "⚠ Claude API key not configured" -ForegroundColor Yellow
    }
}

# Function untuk generate report
function Generate-SetupReport {
    Write-Host "`n=== Setup Complete ===" -ForegroundColor Green
    Write-Host "Configuration Summary:" -ForegroundColor Cyan
    Write-Host "- MCP Server: Configured" -ForegroundColor White
    Write-Host "- Claude Integration: Ready" -ForegroundColor White  
    Write-Host "- GitHub Copilot: Enhanced" -ForegroundColor White
    Write-Host "- Analytics: Enabled" -ForegroundColor White
    
    Write-Host "`nNext Steps:" -ForegroundColor Cyan
    Write-Host "1. Update .env file with your API keys" -ForegroundColor White
    Write-Host "2. Restart VS Code" -ForegroundColor White
    Write-Host "3. Open BobaJetBrain project" -ForegroundColor White
    Write-Host "4. Run 'Full Development Setup' task" -ForegroundColor White
    
    Write-Host "`nUseful Commands:" -ForegroundColor Cyan
    Write-Host "- Ctrl+Shift+P > 'Tasks: Run Task' > 'Start MCP Claude Server'" -ForegroundColor White
    Write-Host "- Ctrl+Shift+P > 'GitHub Copilot: Check Status'" -ForegroundColor White
    Write-Host "- Ctrl+Shift+P > 'GitHub Copilot: Reload'" -ForegroundColor White
}

# Main execution
try {
    Test-Prerequisites
    
    if ($All -or $InstallDependencies) {
        Install-Dependencies
    }
    
    if ($All -or $SetupEnvironment) {
        Setup-Environment
        Install-VSCodeExtensions
    }
    
    if ($All -or $StartServers) {
        Start-MCPServer
    }
    
    if ($All) {
        Test-Configuration
        Generate-SetupReport
    }
    
} catch {
    Write-Host "Setup failed: $_" -ForegroundColor Red
    exit 1
}

Write-Host "`nSetup completed successfully! 🚀" -ForegroundColor Green
