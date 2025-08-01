# Hitung Ulang Neraca - Implementasi Laravel

## Gambaran Umum

Implementasi `hitungUlangNeraca` di Laravel berdasarkan procedure Delphi `ProsesHitUlangNeraca`. Proses ini melakukan perhitungan ulang neraca untuk periode tertentu dengan memproses semua transaksi dan menghitung saldo akhir.

## Tahap Proses

### **1. Reset Balance Fields**
```sql
UPDATE DBNERACA 
SET MD = 0, MK = 0, MDRp = 0, MKRp = 0, 
    JPD = 0, JPK = 0, JPDRp = 0, JPKRp = 0, 
    RLD = 0, RLK = 0, RLDRp = 0, RLKRp = 0
WHERE Bulan = ? AND Tahun = ?
```
- Reset semua field balance ke 0 untuk periode yang dipilih
- Field yang direset: MD, MK, MDRp, MKRp, JPD, JPK, JPDRp, JPKRp, RLD, RLK, RLDRp, RLKRp

### **2. Ambil Data Transaksi**
```sql
SELECT 
    a.nobukti, A.Devisi, a.Perkiraan, a.Lawan, A.Valas, a.Kurs,
    a.Debet, a.DebetRp, b.DK AS DKP, c.DK AS DKL,
    A.StatusAktivaP, a.StatusAktivaL, a.NoAktivaP, a.NoAktivaL, a.TipeTrans
FROM vwTransaksi a
LEFT OUTER JOIN DBPERKIRAAN b ON b.Perkiraan = a.Perkiraan
LEFT OUTER JOIN DBPERKIRAAN c ON c.Perkiraan = a.Lawan
WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
ORDER BY A.nobukti
```
- Mengambil semua transaksi untuk periode tertentu
- Join dengan DBPERKIRAAN untuk mendapatkan informasi DK (Debit/Kredit)
- Menggunakan view `vwTransaksi` untuk performa yang lebih baik

### **3. Proses Setiap Transaksi**
Menggunakan stored procedure `sp_HitungUlangTransaksi` untuk setiap transaksi:

**Parameter:**
- `@Devisi` - Kode devisi
- `@Perkiraan` - Kode perkiraan
- `@DKP` - DK perkiraan (1=Debit, 2=Kredit)
- `@Lawan` - Kode perkiraan lawan
- `@DKL` - DK perkiraan lawan
- `@Debet` - Jumlah debet
- `@DebetRp` - Jumlah debet dalam Rupiah
- `@StatusAktivaP` - Status aktiva perolehan
- `@StatusAktivaL` - Status aktiva liabilitas
- `@NoAktivaP` - Nomor aktiva perolehan
- `@NoAktivaL` - Nomor aktiva liabilitas
- `@TipeTrans` - Tipe transaksi
- `@Bulan` - Bulan
- `@Tahun` - Tahun
- `@Valas` - Mata uang

### **4. Update Field DK**
```sql
UPDATE DBNERACA 
SET DK = ISNULL(B.DK, 0)
FROM DBNERACA A
LEFT OUTER JOIN DBPERKIRAAN B ON B.Perkiraan = A.Perkiraan
WHERE A.Tahun = ? AND A.Bulan = ?
```
- Update field DK dari tabel DBPERKIRAAN
- DK menentukan apakah akun bersifat Debit (1) atau Kredit (2)

### **5. Ambil Data Akun**
```sql
SELECT DISTINCT 
    A.Perkiraan, B.Keterangan, A.Devisi
FROM dbNeraca a
LEFT OUTER JOIN DBPERKIRAAN b ON b.Perkiraan = a.Perkiraan
WHERE Bulan = ? AND Tahun = ?
ORDER BY A.Perkiraan
```
- Mengambil semua akun yang ada di neraca untuk periode tersebut
- Digunakan untuk proses transfer saldo

### **6. Proses Transfer Saldo**
Menggunakan stored procedure `sp_PindahSaldoNeraca` untuk setiap akun:

**Parameter:**
- `@Devisi` - Kode devisi
- `@Perkiraan` - Kode perkiraan
- `@Bulan` - Bulan
- `@Tahun` - Tahun

## Stored Procedures

### **sp_HitungUlangTransaksi**
Stored procedure untuk memproses setiap transaksi:

**Fitur:**
- Update balance berdasarkan tipe akun (Debit/Kredit)
- Handle aktiva transactions (JPD, JPK, JPDRp, JPKRp)
- Handle profit/loss transactions (RLD, RLK, RLDRp, RLKRp)
- Support foreign currency dengan kurs
- Auto-insert record jika belum ada

### **sp_PindahSaldoNeraca**
Stored procedure untuk transfer saldo:

**Fitur:**
- Ambil saldo awal dari bulan sebelumnya
- Hitung saldo akhir berdasarkan tipe akun
- Update field Awal, AwalD, Akhir, AkhirD
- Handle transisi tahun (Januari mengambil dari Desember tahun sebelumnya)

## Field Neraca

### **Balance Fields**
- `MD` - Mutasi Debet
- `MK` - Mutasi Kredit
- `MDRp` - Mutasi Debet Rupiah
- `MKRp` - Mutasi Kredit Rupiah

### **Asset Fields**
- `JPD` - Jurnal Penyusutan Debet
- `JPK` - Jurnal Penyusutan Kredit
- `JPDRp` - Jurnal Penyusutan Debet Rupiah
- `JPKRp` - Jurnal Penyusutan Kredit Rupiah

### **Profit/Loss Fields**
- `RLD` - Rugi Laba Debet
- `RLK` - Rugi Laba Kredit
- `RLDRp` - Rugi Laba Debet Rupiah
- `RLKRp` - Rugi Laba Kredit Rupiah

### **Balance Fields**
- `Awal` - Saldo Awal
- `AwalD` - Saldo Awal dalam Rupiah
- `Akhir` - Saldo Akhir
- `AkhirD` - Saldo Akhir dalam Rupiah
- `DK` - Debit/Kredit (1=Debit, 2=Kredit)

## Error Handling

### **Database Transaction**
- Menggunakan `DB::beginTransaction()` dan `DB::commit()`
- Rollback otomatis jika terjadi error
- Logging error yang komprehensif

### **Progress Tracking**
- Log progress setiap 100 transaksi
- Log progress setiap 50 akun
- Informasi jumlah data yang diproses

## SQL Server 2008 Compatibility

### **Syntax yang Digunakan**
- `ISNULL()` untuk null handling
- `LEFT OUTER JOIN` untuk join
- `CASE WHEN` untuk conditional logic
- Operator `+` untuk string concatenation

### **Stored Procedure Calls**
```php
DB::statement('EXEC sp_HitungUlangTransaksi ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?', $params);
DB::statement('EXEC sp_PindahSaldoNeraca ?, ?, ?, ?', $params);
```

## Logging

### **Info Logs**
- Start dan completion process
- Progress tracking
- Jumlah data yang diproses

### **Error Logs**
- Error detail dengan stack trace
- Parameter yang digunakan
- Rollback information

## Performance Considerations

### **Batch Processing**
- Proses transaksi satu per satu (sesuai Delphi)
- Progress logging untuk monitoring
- Memory management dengan garbage collection

### **Database Optimization**
- Menggunakan view `vwTransaksi` untuk performa
- Index pada field Bulan, Tahun, Perkiraan
- Stored procedures untuk business logic

## Testing

### **Test Cases**
1. **Normal Processing**: Periode dengan transaksi normal
2. **Empty Period**: Periode tanpa transaksi
3. **Year Transition**: Januari (ambil dari Desember tahun sebelumnya)
4. **Asset Transactions**: Transaksi dengan aktiva
5. **Profit/Loss Transactions**: Transaksi rugi laba
6. **Foreign Currency**: Transaksi dengan mata uang asing

### **Validation**
- Saldo akhir = Saldo awal + Mutasi
- Balance check: Total Debet = Total Kredit
- Asset balance validation
- Profit/Loss balance validation 