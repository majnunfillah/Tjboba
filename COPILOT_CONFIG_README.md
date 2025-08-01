# Copilot Configuration untuk BobaJetBrain

Konfigurasi ini telah dibuat untuk memberikan konteks yang optimal kepada GitHub Copilot saat bekerja dengan proyek Laravel BobaJetBrain.

## File yang Dibuat

### 1. `.vscode/settings.json`
Konfigurasi utama VS Code yang mengatur:
- GitHub Copilot settings
- Language-specific configurations
- Project context dan personal context
- Editor behavior
- File associations

### 2. `.copilotrc.json`
File konfigurasi JSON yang berisi:
- Aturan coding standards
- Pola-pola umum (common patterns)
- Struktur modul
- Context proyek
- Template code

### 3. `.copilot-rules.md`
Dokumentasi markdown yang berisi:
- Coding standards
- Naming conventions
- Module structures
- Template patterns
- Guidelines untuk komentar

### 4. `.copilot-instructions`
File instruksi komprehensif untuk Copilot yang berisi:
- Project overview
- Core principles
- Key patterns dengan contoh code
- Module specifications
- Security checklist

### 5. MCP Server Integration

#### Setup MCP untuk Context yang Lebih Kaya
```bash
# Install MCP dependencies
cd mcp
npm install

# Start MCP server
npm run dev
```

#### Keunggulan MCP:
- **Real-time Context**: AI akses schema database dan struktur proyek
- **SQL Server 2008 Compatibility**: AI otomatis hindari syntax yang tidak kompatibel
- **Business Logic Awareness**: AI pahami flow SPK, kas bank, memorial
- **Pattern Recognition**: AI ikuti Repository pattern dan Laravel conventions

#### File MCP yang Dibuat:
- `mcp/mcp-server.js` - MCP server untuk context proyek
- `mcp/package.json` - Dependencies untuk MCP
- `.mcp-config.json` - Konfigurasi MCP servers
- `MCP_IMPLEMENTATION_GUIDE.md` - Panduan lengkap MCP

## Cara Kerja

1. **VS Code Settings**: Mengatur behavior Copilot dan memberikan context dasar
2. **JSON Config**: Menyediakan struktur data yang mudah dibaca oleh AI
3. **Markdown Rules**: Dokumentasi yang mudah dibaca manusia dan AI
4. **Instructions File**: Prompt engineering untuk memberikan konteks yang sangat spesifik

## Penggunaan

Setelah konfigurasi ini:
- Copilot akan memahami konteks proyek Laravel Anda
- Akan menggunakan bahasa Indonesia untuk komentar
- Akan mengikuti pattern Repository yang sudah ada
- Akan menghasilkan code yang kompatibel dengan SQL Server
- Akan mengikuti struktur DataTables yang benar

## Tips Tambahan

1. **Restart VS Code** setelah membuat konfigurasi ini
2. **Gunakan Copilot Chat** dengan context yang lebih baik
3. **Sesuaikan rules** sesuai kebutuhan tim Anda
4. **Update context** saat ada perubahan arsitektur

## Contoh Prompt yang Efektif

Dengan konfigurasi ini, Anda bisa menggunakan prompt seperti:
- "Buat method baru di SPKRepository untuk mengambil data stock"
- "Generate controller method untuk handle Ajax request DataTables"
- "Buat validasi untuk form SPK dengan Laravel Form Request"

Copilot akan memahami konteks dan menghasilkan code yang sesuai dengan pattern proyek Anda.

## 🔗 Model Context Protocol (MCP) Implementation

### Apa itu MCP?
Model Context Protocol (MCP) adalah protokol yang memungkinkan AI assistant (seperti GitHub Copilot) untuk mengakses context yang lebih kaya dan dinamis dari proyek Anda. Dengan MCP, Copilot dapat:
- Membaca struktur database real-time
- Mengakses dokumentasi API yang up-to-date
- Memahami relationship antar modul
- Menggunakan context dari file konfigurasi aktual

### Setup MCP untuk BobaJetBrain

#### 1. Struktur MCP Configuration
```json
// .mcp-config.json
{
  "servers": {
    "bobajetbrain-context": {
      "command": "node",
      "args": ["mcp-server.js"],
      "env": {
        "PROJECT_ROOT": ".",
        "DB_CONNECTION": "sqlsrv"
      }
    }
  },
  "tools": [
    "database-schema",
    "route-inspector", 
    "model-analyzer",
    "repository-context"
  ]
}
```

#### 2. Database Schema Context
```javascript
// mcp-tools/database-schema.js
export async function getDatabaseSchema() {
  return {
    tables: {
      "DBAKTIVA": {
        columns: ["ID", "KodeAktiva", "NamaAktiva", "TglPerolehan"],
        relationships: ["has_many:depreciations"]
      },
      "tbso": {
        columns: ["NoBukti", "Tanggal", "Keterangan"],
        relationships: ["has_many:tbsod"]
      },
      "tbstok": {
        columns: ["KodeBrg", "NamaBrg", "Satuan", "Stok"],
        relationships: ["belongs_to:tbbrg"]
      }
    },
    compatibility: "SQL Server 2008"
  };
}
```

#### 3. Repository Pattern Context
```javascript
// mcp-tools/repository-context.js
export async function getRepositoryPatterns() {
  return {
    baseRepository: "app/Http/Repository/BaseRepository.php",
    patterns: {
      "getData": "Standard data retrieval with filters",
      "requestAjax": "DataTables server-side processing",
      "store": "Create new record with validation",
      "update": "Update existing record",
      "destroy": "Soft delete with logging"
    },
    sqlServerCompatibility: {
      "concat": "Use + operator instead of CONCAT()",
      "conditional": "Use CASE WHEN instead of IIF()",
      "formatting": "Use CONVERT() instead of FORMAT()"
    }
  };
}
```

#### 4. Contoh Penggunaan dengan MCP

##### Prompt yang Efektif dengan MCP:
```
"Buat method di SPKRepository untuk ambil outstanding SO dengan join ke tbbrg"
```

##### AI Response dengan MCP Context:
```php
<?php
declare(strict_types=1);

namespace App\Http\Repository;

class SPKRepository extends BaseRepository
{
    public function getOutstandingSO(array $params = []): array
    {
        $query = DB::table('tbso as so')
            ->leftJoin('tbsod as sod', 'so.NoBukti', '=', 'sod.NoBukti')
            ->leftJoin('tbbrg as brg', 'sod.KodeBrg', '=', 'brg.KodeBrg')
            ->select([
                'so.NoBukti',
                'sod.KodeBrg', 
                'brg.NamaBrg',
                // SQL Server 2008 compatible - gunakan CASE WHEN
                DB::raw('CASE WHEN sod.QntSPK IS NULL THEN 0 ELSE sod.QntSPK END as QntSPK'),
                // SQL Server 2008 compatible - gunakan operator +
                DB::raw('so.NoBukti + \'-\' + CAST(sod.Urut as VARCHAR) as Referensi')
            ])
            ->where('so.Status', '=', 'Open');
            
        return $query->get()->toArray();
    }
}
```
