# Script untuk menghapus file Xdebug dengan elevated permission
$xdebugDll = "C:\xampp\php\ext\php_xdebug.dll"
$xdebugLog = "C:\xampp\php\logs\xdebug.log"

if (Test-Path $xdebugDll) {
    try {
        Remove-Item $xdebugDll -Force
        Write-Host "Successfully removed: $xdebugDll"
    } catch {
        Write-Host "Could not remove DLL (may need admin rights): $xdebugDll"
        Write-Host "Please delete manually or run as administrator"
    }
}

if (Test-Path $xdebugLog) {
    try {
        Remove-Item $xdebugLog -Force
        Write-Host "Successfully removed: $xdebugLog"
    } catch {
        Write-Host "Could not remove log (file may be in use): $xdebugLog"
        Write-Host "Please stop web server and delete manually"
    }
}
