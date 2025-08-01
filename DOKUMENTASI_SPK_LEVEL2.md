# Dokumentasi Implementasi SPK Level 2

## Overview
Fitur ini mengembangkan DataTable SPK agar mendukung tampilan nested (level 2) di bawah detail SPK (level 1), dengan data level 2 diambil dari query gabungan DBJADWALPRD, DBSPK, dan dbmesin.

## Struktur Data

### Level 1 (Data Utama SPK)
- Sumber: `dbSPKDet`
- Kolom: Urut, KodeBrg, NamaBrg, Qnt, Satuan, Keterangan

### Level 2 (Data Jadwal Produksi)
- Sumber: `DBJADWALPRD` + `DBSPK` + `dbmesin`
- Kolom: KodePrs, KODEMSN, TANGGAL, JAMAWAL, JAMAKHIR, QNTSPK, IsiJ, TarifMesin, JamTenaker, JmlTenaker, TarifTenaker

## Files yang Dimodifikasi

### 1. Controller: `app/Http/Controllers/SPKController.php`
**Method yang diubah:**
- `getSpkDetailByNoBukti()`: Menggabungkan data level 1 dan level 2 dalam satu response

**Perubahan utama:**
```php
// Dapatkan data level 1 terlebih dahulu
$level1Data = $datatableData['data'];
$allData = [];

// Loop melalui setiap row level 1 dan tambahkan data level 2 di bawahnya
foreach ($level1Data as $level1Row) {
    // Tambahkan row level 1
    $allData[] = $level1Row;
    
    // Dapatkan data level 2 untuk row ini
    $level2Data = $this->spkRepository->getSpkDetailLevel2ByNoBuktiAndUrut(request()->NoBukti, $level1Row->Urut);
    
    if ($level2Data && count($level2Data) > 0) {
        foreach ($level2Data as $level2Row) {
            // Format dan mapping data level 2
            // ...
            $allData[] = $level2Row;
        }
    }
}
```

### 2. Repository: `app/Http/Repository/SPKRepository.php`
**Method yang diubah:**
- `getSpkDetailLevel2ByNoBuktiAndUrut($noBukti, $urut)`

**Query SQL:**
```sql
SELECT A.NOSPK, A.KodePrs, A.Urut, A.KODEMSN, A.TANGGAL, A.JAMAWAL, A.JAMAKHIR, A.QNTSPK,
       A.Keterangan, C.ket,
       B.NoBatch, B.TglExpired, B.tanggal TanggalBukti, B.NoUrut,
       B.KodeBrg BrgJ, B.Qnt QntJ, B.Nosat NosatJ, B.Isi IsiJ, B.Satuan SatJ, B.KodeBOM,
       A.TarifMesin, A.JamTenaker, A.JmlTenaker, A.TarifTenaker
FROM DBJADWALPRD A
LEFT OUTER JOIN DBSPK B ON B.NOBUKTI = A.NOSPK
LEFT OUTER JOIN dbmesin C ON C.kodemsn = A.kodemsn
WHERE A.NOSPK = @NoBukti
  AND EXISTS (
      SELECT 1 FROM dbSPKDet D 
      WHERE D.NoBukti = @NoBukti 
      AND (D.Urut = @Urut OR ROW_NUMBER() OVER (ORDER BY D.KodeBrg) = @Urut)
  )
ORDER BY A.Urut
```

### 3. JavaScript: `public/assets/js/produksi/spk/spk.js`
**Konfigurasi DataTable dengan 19 kolom:**
```javascript
columns: [
    { data: "level", render: function(data, type, row) {
        return data == 2 ? '<span class="badge badge-info">L2</span>' : '<span class="badge badge-primary">L1</span>';
    }},
    { data: "Urut" },
    { data: "KodeBrg" },
    { data: "NamaBrg" },
    { data: "Qnt", className: "text-right" },
    { data: "Satuan" },
    { data: "Keterangan" },
    // Kolom khusus Level 2
    { data: "KodePrs", defaultContent: "", render: function(data, type, row) {
        return row.level == 2 ? (data || '') : '';
    }},
    // ... dan seterusnya untuk 12 kolom level 2 lainnya
]
```

**Styling dengan createdRow:**
```javascript
createdRow: function(row, data, dataIndex) {
    if (data.level == 2) {
        $(row).addClass('level-2-row');
    } else {
        $(row).addClass('level-1-row');
    }
}
```

### 4. CSS: `public/assets/css/spk.css`
**Styling untuk membedakan level:**
```css
.level-2-row {
    background-color: #f8f9fa !important;
    font-style: italic;
    border-left: 3px solid #007bff !important;
}

.level-2-row td:first-child::before {
    content: "└─ ";
    position: absolute;
    left: 8px;
    color: #007bff;
    font-weight: bold;
}
```

### 5. Template Blade: `resources/views/components/produksi/spk/expand_table.blade.php`
**Header tabel dengan 19 kolom:**
```html
<thead>
    <tr>
        <th>Level</th>
        <th>Urut</th>
        <th>Kode Barang</th>
        <th>Nama Barang</th>
        <th class="text-right">Quantity</th>
        <th>Satuan</th>
        <th>Keterangan</th>
        <!-- Kolom tambahan untuk Level 2 -->
        <th>Kode Prs</th>
        <th>Kode Mesin</th>
        <th>Tanggal</th>
        <th>Jam Awal</th>
        <th>Jam Akhir</th>
        <th class="text-right">Qty SPK</th>
        <th class="text-right">Isi</th>
        <th class="text-right">Tarif Mesin</th>
        <th class="text-right">Jam Tenaker</th>
        <th class="text-right">Jml Tenaker</th>
        <th class="text-right">Tarif Tenaker</th>
        <th class="text-center">Action</th>
    </tr>
</thead>
```

## Cara Kerja

1. **User membuka halaman SPK** → Tampil daftar SPK utama
2. **User klik expand (+) pada baris SPK** → Memanggil `getSpkDetailByNoBukti()`
3. **Controller menggabungkan data:**
   - Ambil data level 1 dari `dbSPKDet`
   - Untuk setiap row level 1, ambil data level 2 dari `DBJADWALPRD`
   - Gabungkan dalam satu array dengan field `level` (1 atau 2)
4. **DataTable menampilkan:**
   - Baris level 1 dengan styling normal (bold, background putih)
   - Baris level 2 dengan styling khusus (italic, background abu, indentasi, icon)
   - Kolom level 2 hanya tampil data jika `row.level == 2`

## Fitur Visual

- **Badge Level**: L1 (biru) untuk level 1, L2 (cyan) untuk level 2
- **Indentasi**: Baris level 2 memiliki prefix "└─ " dan padding kiri
- **Background**: Level 1 putih, Level 2 abu-abu
- **Border**: Level 2 memiliki border kiri biru
- **Hover Effect**: Background berubah saat mouse hover

## Action Buttons

- **Level 1**: Edit, Hapus (standard)
- **Level 2**: Edit L2, Hapus L2 (dengan parameter tambahan parent_urut dan child_urut)

## Testing

Untuk testing, buka halaman SPK dan:
1. Klik expand pada salah satu baris SPK
2. Periksa apakah data level 2 muncul dengan styling yang berbeda
3. Pastikan kolom-kolom level 2 menampilkan data sesuai query
4. Test action button edit/hapus untuk level 2

## Notes

- Data level 2 langsung digabungkan dengan level 1 (bukan menggunakan nested AJAX)
- Query level 2 menggunakan gabungan 3 tabel: DBJADWALPRD, DBSPK, dbmesin
- Field mapping disesuaikan agar konsisten dengan struktur DataTable
- CSS disimpan dalam file terpisah untuk maintainability yang lebih baik
