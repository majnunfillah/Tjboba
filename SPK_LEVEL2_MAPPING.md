# SPK Level 2 Data Mapping

## Struktur Tampilan

### Level 1 (Data SPK)
- **Urut**: Nomor urut SPK
- **Kode Barang**: Kode barang yang diproduksi
- **Nama Barang**: Nama barang yang diproduksi
- **Quantity**: Jumlah barang
- **Satuan**: Satuan barang
- **Keterangan**: Keterangan tambahan

### Level 2 (Data Jadwal Produksi)
Menggunakan kolom yang sama dengan Level 1, tapi dengan data yang berbeda:

- **Urut**: └─ [Urut Parent] (L2)
- **Kode Barang**: **KodePrs** (Kode Proses)
- **Nama Barang**: **KODEMSN** (Kode Mesin)
- **Quantity**: **TANGGAL** (Tanggal Produksi)
- **Satuan**: **JAMAWAL** (Jam Mulai)
- **Keterangan**: **JAMAKHIR** | Qty: **QNTSPK** (Jam Selesai + Quantity SPK)

## Contoh Tampilan

```
Urut 1 - BRGA001 - Barang A - 100,00 - PCS - Keterangan A
Urut 2 - BRGB002 - Barang B - 200,00 - KG - Keterangan B

(L2) - PRS001 - MSN001 - 2024-01-15 - 08:00 - 16:00 | Qty: 50,00
(L2) - PRS002 - MSN002 - 2024-01-16 - 09:00 - 17:00 | Qty: 100,00
```

### Urutan Data:
1. **Semua data Level 1** ditampilkan terlebih dahulu
2. **Semua data Level 2** ditampilkan setelah Level 1 selesai

## Database Source

- **Level 1**: Table `dbSPKDet`
- **Level 2**: Table `DBJADWALPRD` (joined dengan `dbSPK` dan `dbmesin`)

## Styling

- **Level 1**: Background putih, font bold
- **Level 2**: Background biru muda, font italic, border kiri biru
