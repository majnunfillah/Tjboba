# Script untuk memperbaiki encoding file laravel.log
$inputFile = "C:\bobajetbrain\storage\logs\laravel.log"
$outputFile = "C:\bobajetbrain\storage\logs\laravel_utf8.log"

try {
    # Baca file dengan encoding UTF-16LE
    $content = Get-Content $inputFile -Encoding Unicode -Raw
    
    # Tulis ulang dengan encoding UTF-8
    $content | Out-File $outputFile -Encoding UTF8 -NoNewline
    
    Write-Host "Konversi berhasil! File tersimpan di: $outputFile"
    Write-Host "Menampilkan 10 baris pertama:"
    Get-Content $outputFile | Select-Object -First 10
}
catch {
    Write-Error "Error: $($_.Exception.Message)"
}
