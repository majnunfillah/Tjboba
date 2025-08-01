# Dokumentasi Hitung Ulang HPP - Laravel Implementation

## Overview
Implementasi Laravel untuk fitur "Hitung Ulang HPP" (Recalculate Average Cost) berdasarkan Delphi form `FrmRata2`. Fitur ini digunakan untuk menghitung ulang harga pokok persediaan berdasarkan transaksi yang terjadi dalam periode tertentu.

## Fitur Utama

### 1. Interface Pengguna
- **Form Input**: Bulan, Tahun, Jenis Barang (Semua/Per Barang)
- **Range Barang**: Kode barang awal dan akhir (jika pilihan "Per Barang")
- **Progress Bar**: Menampilkan kemajuan proses dengan pesan detail
- **DataTable**: Menampilkan daftar stock minus
- **Export Excel**: Export data stock minus ke file Excel

### 2. Proses Perhitungan
- **Inisialisasi**: Reset data HPP periode yang dipilih
- **Proses Bahan**: Hitung ulang HPP untuk setiap barang
- **Update Transaksi**: Update nilai HPP di tabel transaksi
- **Proses Kemasan**: Handle perubahan kemasan
- **Akhir Bulan**: Transfer saldo ke periode berikutnya

## Struktur File

### Controller
```
app/Http/Controllers/HitungUlangHPPController.php
```

### View
```
resources/views/accounting/hitung_ulang_hpp.blade.php
```

### JavaScript
```
public/assets/js/accounting/hitungUlangHPP.js
```

### Stored Procedures
```
database/sql/sp_HitungUlangHPP.sql
```

### Routes
```
routes/web.php (ditambahkan route group)
```

## Implementasi Detail

### 1. Controller Methods

#### `index()`
- Menampilkan halaman utama
- Mengirim data bulan dan tahun saat ini

#### `prosesHitungUlangHPP()`
- Validasi periode (cek lock)
- Clear data stock minus sebelumnya
- Return response untuk memulai proses

#### `executeHitungUlangHPP()`
- Eksekusi proses utama
- Panggil stored procedure `sp_HitungUlangHPP`
- Handle error dan logging

#### `getStockMinusData()`
- DataTable server-side processing
- Filter berdasarkan user ID

#### `exportStockMinus()`
- Export data ke format CSV/Excel
- Generate file download

### 2. Stored Procedures

#### `sp_HitungUlangHPP`
Prosedur utama yang mengkoordinasikan seluruh proses:
- Clear data stock minus
- Initialize stock data
- Panggil sub-prosedur untuk setiap tahap

#### `sp_ProsesBahanHPP`
Memproses perhitungan HPP untuk setiap barang:
- Loop melalui setiap barang
- Hitung saldo dan rata-rata harga
- Deteksi stock minus

#### `sp_UpdateHPPtoTransactions`
Update nilai HPP di tabel transaksi:
- Update dbStockBrg dengan nilai terhitung
- Hitung rata-rata harga

#### `sp_ProsesKemasanHPP`
Handle perubahan kemasan:
- Loop dokumen ubah kemasan
- Update HPP untuk barang terkait

#### `sp_ProsesAkhirBulanHPP`
Transfer saldo ke periode berikutnya:
- Reset saldo awal periode berikutnya
- Transfer saldo dari periode saat ini

### 3. Frontend Implementation

#### Progress Tracking
```javascript
// Start progress monitoring
startProgressMonitoring() {
    this.progressInterval = setInterval(() => {
        if (this.currentProgress < 100) {
            this.currentProgress += Math.random() * 5;
            this.updateProgressBar(Math.round(this.currentProgress));
            this.updateProgressMessage();
        }
    }, 500);
}
```

#### Form Validation
```javascript
validateForm() {
    const bulan = $('#bulan').val();
    const tahun = $('#tahun').val();
    // ... validation logic
}
```

#### DataTable Integration
```javascript
this.stockMinusTable = $('#stock_minus_table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '/hitung-ulang-hpp/get-stock-minus',
        type: 'POST'
    },
    // ... configuration
});
```

## Database Tables

### Tables yang Terlibat
1. **vwKartuStock** - View kartu stock
2. **dbStockBrg** - Tabel stock barang
3. **dbBarang** - Master data barang
4. **dbGudang** - Master data gudang
5. **TempStockMinus** - Temporary table untuk stock minus
6. **dbTempRata2** - Temporary table untuk perhitungan rata-rata
7. **dbUbahKemasan** - Dokumen ubah kemasan

### Temporary Tables
```sql
-- TempStockMinus
CREATE TABLE TempStockMinus (
    IDUser VARCHAR(30),
    Urut INT,
    JenisBahan VARCHAR(50),
    KodeGdg VARCHAR(15),
    KodeBrg VARCHAR(25),
    KodeBng VARCHAR(25),
    KodeJenis VARCHAR(10),
    KodeWarna VARCHAR(10)
);

-- dbTempRata2
CREATE TABLE dbTempRata2 (
    KodeGdg VARCHAR(15),
    QntSaldo DECIMAL(18,2),
    HrgSaldo DECIMAL(18,2),
    HrgRata DECIMAL(18,2)
);
```

## Business Logic

### 1. Perhitungan HPP
```sql
-- Formula rata-rata harga
HrgRata = CASE 
    WHEN SaldoQnt = 0 THEN 0 
    ELSE SaldoRp / SaldoQnt 
END
```

### 2. Jenis Transaksi
- **Masuk**: AWL, PBL, ADI, TRI, UKI, RPK, PBI
- **Keluar**: RPB, PNJ, ADO, UKO, TRO, PMK

### 3. Stock Minus Detection
```sql
-- Deteksi stock minus
IF ROUND(@Saldo, 4) < 0
BEGIN
    -- Insert ke TempStockMinus
END
```

## Error Handling

### 1. Validation Errors
- Periode sudah terkunci
- Input tidak valid
- Database connection error

### 2. Process Errors
- Timeout pada proses panjang
- Memory limit exceeded
- Stored procedure errors

### 3. User Feedback
```javascript
showError(message) {
    // Display error message to user
}

showSuccess(message) {
    // Display success message to user
}
```

## Security Considerations

### 1. Permission Control
```php
// Check user permission
if (hasPermission('08102')) {
    // Show menu and allow access
}
```

### 2. CSRF Protection
```php
// All POST requests include CSRF token
data: {
    _token: $('meta[name="csrf-token"]').attr('content')
}
```

### 3. User Session
```php
// Filter data by user ID
->where('IDUser', session('user_id'))
```

## Performance Optimization

### 1. Database Indexes
```sql
-- Recommended indexes
CREATE INDEX IX_vwKartuStock_Period ON vwKartuStock(Tahun, Bulan);
CREATE INDEX IX_dbStockBrg_Period ON dbStockBrg(Tahun, Bulan);
CREATE INDEX IX_TempStockMinus_User ON TempStockMinus(IDUser);
```

### 2. Batch Processing
- Proses barang per batch untuk menghindari timeout
- Progress tracking untuk user feedback

### 3. Memory Management
- Clear temporary tables setelah selesai
- Use cursor untuk data besar

## Testing

### 1. Unit Tests
```php
// Test controller methods
public function test_proses_hitung_ulang_hpp()
{
    // Test validation
    // Test process execution
    // Test error handling
}
```

### 2. Integration Tests
```php
// Test complete workflow
public function test_complete_hpp_calculation()
{
    // Test from form submission to completion
}
```

### 3. Database Tests
```sql
-- Test stored procedures
EXEC sp_HitungUlangHPP 12, 2024, NULL, NULL, 'TEST_USER'
```

## Deployment Considerations

### 1. Environment Setup
- SQL Server 2008 compatibility
- Proper database permissions
- Temporary table cleanup

### 2. Monitoring
- Log semua proses perhitungan
- Monitor performance dan memory usage
- Alert untuk error kritis

### 3. Backup
- Backup database sebelum proses
- Backup temporary data jika diperlukan

## Troubleshooting

### Common Issues

1. **Timeout Error**
   - Increase PHP execution time limit
   - Optimize database queries
   - Use batch processing

2. **Memory Limit**
   - Increase PHP memory limit
   - Process data in smaller chunks
   - Clear temporary data

3. **Permission Error**
   - Check database user permissions
   - Verify stored procedure access
   - Check file system permissions

### Debug Mode
```php
// Enable debug logging
Log::debug('HPP Calculation Progress', [
    'bulan' => $bulan,
    'tahun' => $tahun,
    'progress' => $currentProgress
]);
```

## Future Enhancements

### 1. Real-time Progress
- WebSocket untuk real-time progress update
- Live progress bar dengan detail per barang

### 2. Batch Processing
- Background job processing
- Queue system untuk proses besar

### 3. Advanced Reporting
- Detail perhitungan per barang
- Comparison dengan periode sebelumnya
- Trend analysis

### 4. Performance Monitoring
- Dashboard monitoring performa
- Alert system untuk error
- Performance metrics

## Conclusion

Implementasi Laravel untuk Hitung Ulang HPP ini mengikuti pola yang sama dengan sistem Delphi asli namun dengan teknologi modern. Fitur ini sangat penting untuk akurasi perhitungan harga pokok persediaan dan harus dijalankan dengan hati-hati karena mempengaruhi data keuangan perusahaan.

Pastikan untuk:
1. Test thoroughly sebelum production
2. Backup database sebelum menjalankan
3. Monitor performance dan error
4. Dokumentasikan semua perubahan
5. Train user untuk penggunaan yang benar 