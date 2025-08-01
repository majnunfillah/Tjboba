# Baca dan konversi file laravel.log
$logFile = "C:\bobajetbrain\storage\logs\laravel.log"
$backupFile = "C:\bobajetbrain\storage\logs\laravel.log.bak"

# Backup file asli
Copy-Item $logFile $backupFile -Force

# Baca dengan encoding UTF-16LE (Unicode)
$content = [System.IO.File]::ReadAllText($logFile, [System.Text.Encoding]::Unicode)

# Tulis kembali dengan UTF-8
[System.IO.File]::WriteAllText($logFile, $content, [System.Text.Encoding]::UTF8)

Write-Host "File berhasil dikonversi ke UTF-8"
Write-Host "Backup disimpan sebagai: $backupFile"
