# ANALISIS OUTSTANDING SO - MENDALAM

## 🎯 **TUJUAN PERUBAHAN**
1. Mengganti tab "Laporan SPK" menjadi "Outstanding SO"
2. Membuat tampilan memorial style tanpa tombol action
3. Implementasi query Outstanding SO yang kompleks
4. Konversi query ke Laravel Eloquent/Query Builder

## 📊 **ANALISIS QUERY SQL OUTSTANDING SO**

### **Query Utama:**
```sql
select A.NOBUKTI, A.URUT, A.KODEBRG, SUM(QntSO) QntSO, SUM(QntSPK) QntSPK, SUM(Saldo) Saldo,
		B.NAMABRG, A.NOSAT,
		case when A.NOSAT = 1 then SAT1
			 when A.NOSAT = 2 then SAT2
			 when A.NOSAT = 3 then SAT3 end Satuan ,max ( tglmulai ) tglmulai ,max(tglkirim) tglkirim
                         ,tglkirim  tglselesai
from
(
	select NOBUKTI, URUT, KODEBRG, QNT QntSO, 0 QntSPK, QNT+qnt2 Saldo, NOSAT,null tglmulai,null tglselesai
	from DBSODET
	union all
	select A.NoSO, A.UrutSO, A.KODEBRG, 0 QntSO, A.Qnt QntSPK, -A.Qnt Saldo, A.Nosat,TglExpired tglmulai,tglselesai
	from DBSPK A
)A
left outer join DBBARANG B on B.KODEBRG = A.KODEBRG
left outer join dbso c on c.nobukti=a.nobukti
where isnull(c.isbatal,0)=0  
group by A.NOBUKTI, A.URUT, A.KODEBRG, B.NAMABRG, A.NOSAT, SAT1, SAT2, SAT3  ,tglkirim
having SUM(Saldo) <> 0
```

### **STRUKTUR ANALISIS:**

#### **1. UNION ALL Components:**
- **Part 1 (DBSODET):** Data SO Detail dengan QntSO
- **Part 2 (DBSPK):** Data SPK dengan QntSPK (negatif untuk saldo)

#### **2. JOIN Relations:**
- **DBBARANG:** Untuk mendapatkan nama barang
- **DBSO:** Untuk filter batal status

#### **3. Kolom Output:**
- NOBUKTI, URUT, KODEBRG (identifiers)
- QntSO, QntSPK, Saldo (quantities)
- NAMABRG (nama barang)
- NOSAT, Satuan (satuan)
- tglmulai, tglkirim, tglselesai (tanggal)

#### **4. Business Logic:**
- Saldo = SO - SPK (outstanding quantity)
- Filter hanya yang saldo != 0
- Filter SO yang tidak dibatal

## 🎨 **DESAIN TAMPILAN MEMORIAL STYLE**

### **Struktur Tabel:**
1. No SO | Urut | Kode Barang | Nama Barang
2. Qty SO | Qty SPK | Saldo | Satuan  
3. Tanggal Mulai | Tanggal Kirim | Tanggal Selesai

### **Styling Requirements:**
- Memorial container pattern
- Responsive table
- Color coding untuk saldo
- No action buttons (read-only)

## 🔄 **IMPLEMENTASI PLAN**

### **Phase 1: Backend**
1. Update SPKController - method outstandingSO
2. Update SPKRepository - query builder implementation
3. Update routes

### **Phase 2: Frontend**  
1. Update view - ganti tab label
2. Update JavaScript - load outstanding SO data
3. Update CSS - memorial styling

### **Phase 3: Testing**
1. Query validation
2. UI/UX testing
3. Performance testing
