#!/usr/bin/env node
/**
 * Model Context Protocol Server untuk BobaJetBrain
 * Menyediakan context proyek Laravel untuk AI assistants
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
  Tool,
} from '@modelcontextprotocol/sdk/types.js';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import { readFileSync, existsSync } from 'fs';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const PROJECT_ROOT = join(__dirname, '..');

/**
 * Database Schema Context
 */
function getDatabaseSchema() {
  return {
    server: "SQL Server 2008",
    compatibility: {
      functions: {
        concat: "Gunakan operator + bukan CONCAT()",
        conditional: "Gunakan CASE WHEN bukan IIF()",
        formatting: "Gunakan CONVERT() bukan FORMAT()"
      }
    },
    tables: {
      // Accounting Tables
      "DBAKTIVA": {
        description: "Tabel master aktiva/aset perusahaan",
        columns: ["ID", "KodeAktiva", "NamaAktiva", "TglPerolehan", "NilaiPerolehan", "Depresiasi"],
        relationships: {
          "has_many": ["depreciation_records"]
        }
      },
      "DBFLPASS": {
        description: "Tabel transaksi kas bank",
        columns: ["NoBukti", "Tanggal", "Keterangan", "Debet", "Kredit", "Saldo"]
      },
      
      // Sales Order Tables
      "tbso": {
        description: "Tabel header sales order",
        columns: ["NoBukti", "Tanggal", "Customer", "Total", "Status"],
        relationships: {
          "has_many": ["tbsod"]
        }
      },
      "tbsod": {
        description: "Tabel detail sales order",
        columns: ["NoBukti", "Urut", "KodeBrg", "QntSO", "Harga"],
        relationships: {
          "belongs_to": ["tbso", "tbbrg"]
        }
      },
      
      // Inventory Tables
      "tbstok": {
        description: "Tabel stok barang",
        columns: ["KodeBrg", "NamaBrg", "Satuan", "Stok", "HargaBeli", "HargaJual"],
        relationships: {
          "belongs_to": ["tbbrg"]
        }
      },
      "tbbrg": {
        description: "Tabel master barang",
        columns: ["KodeBrg", "NamaBrg", "Satuan", "Kategori", "Status"]
      }
    }
  };
}

/**
 * Laravel Structure Context
 */
function getLaravelStructure() {
  return {
    architecture: "MVC + Repository Pattern",
    version: "Laravel 9.x with PHP 8.1+",
    structure: {
      controllers: {
        path: "app/Http/Controllers/",
        pattern: "PascalCase + Controller suffix",
        examples: ["SPKController", "BankOrKasController", "MemorialController"]
      },
      repositories: {
        path: "app/Http/Repository/",
        pattern: "PascalCase + Repository suffix", 
        baseClass: "BaseRepository",
        examples: ["SPKRepository", "BankOrKasRepository"]
      },
      models: {
        path: "app/Models/",
        pattern: "PascalCase singular",
        examples: ["DBAKTIVA", "SPK", "User"]
      },
      views: {
        path: "resources/views/",
        pattern: "snake_case",
        layout: "layouts.app",
        examples: ["spk/index.blade.php", "kas_bank/index.blade.php"]
      }
    }
  };
}

/**
 * SPK Module Context
 */
function getSPKModuleContext() {
  return {
    name: "SPK (Surat Perintah Kerja)",
    description: "Work Order Management System",
    files: {
      controller: "app/Http/Controllers/SPKController.php",
      repository: "app/Http/Repository/SPKRepository.php",
      views: [
        "resources/views/spk/index.blade.php",
        "resources/views/components/produksi/spk/modal-insert.blade.php"
      ]
    },
    methods: {
      "getData": "Retrieve outstanding SO data for DataTables",
      "getOutstandingSO": "Get pending sales orders",
      "getStock": "Get inventory/stock data",
      "requestAjax": "Handle DataTables server-side processing"
    },
    database_tables: ["tbso", "tbsod", "tbstok", "tbbrg"],
    business_logic: [
      "Convert Sales Order to Work Order",
      "Track inventory allocation",
      "Manage work order status"
    ]
  };
}

/**
 * Coding Standards Context
 */
function getCodingStandards() {
  return {
    php: {
      standard: "PSR-12",
      features: [
        "Strict types: declare(strict_types=1)",
        "Type hints untuk semua parameters",
        "Return types untuk semua methods",
        "Bahasa Indonesia untuk komentar"
      ]
    },
    laravel: {
      conventions: [
        "Eloquent ORM untuk database operations",
        "Repository pattern untuk data access",
        "Form Requests untuk validation",
        "Resource classes untuk API responses"
      ]
    },
    frontend: {
      framework: "AdminLTE + Bootstrap",
      javascript: "jQuery + DataTables",
      patterns: [
        "Server-side DataTables processing",
        "Modal forms dengan x-base-modal component",
        "x-form-part untuk form fields",
        "Expandable rows untuk detail data"
      ]
    },
    database: {
      server: "SQL Server 2008",
      compatibility_rules: [
        "TIDAK gunakan CONCAT() - pakai operator +",
        "TIDAK gunakan FORMAT() - pakai CONVERT()",
        "TIDAK gunakan IIF() - pakai CASE WHEN",
        "Gunakan TOP n bukan LIMIT n",
        "Gunakan ISNULL() bukan COALESCE() untuk performa"
      ]
    }
  };
}

/**
 * Error Patterns Context
 */
function getErrorPatterns() {
  return {
    common_errors: {
      "sql_server_2008": [
        {
          error: "'CONCAT' is not a recognized built-in function name",
          solution: "Gunakan operator + untuk string concatenation",
          example: "field1 + ' - ' + field2"
        },
        {
          error: "'FORMAT' is not a recognized built-in function name", 
          solution: "Gunakan CONVERT() untuk formatting",
          example: "CONVERT(VARCHAR(10), date_field, 103)"
        },
        {
          error: "'IIF' is not a recognized built-in function name",
          solution: "Gunakan CASE WHEN untuk conditional logic",
          example: "CASE WHEN condition THEN value1 ELSE value2 END"
        }
      ]
    },
    error_handling: {
      controller_pattern: `
try {
    $data = $this->repository->getData($request->all());
    return response()->json(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    Log::error('Error in method: ' . $e->getMessage());
    return response()->json(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}`,
      logging: "Gunakan Log::error() untuk error logging",
      user_messages: "Pesan error dalam bahasa Indonesia yang user-friendly"
    }
  };
}

/**
 * DataTables Patterns Context
 */
function getDataTablesPatterns() {
  return {
    server_side: {
      controller_method: `
public function requestAjax(Request $request): JsonResponse
{
    try {
        $data = $this->repository->getData($request->all());
        return response()->json([
            'draw' => (int) $request->get('draw'),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ]);
    } catch (Exception $e) {
        Log::error($e->getMessage());
        return response()->json(['error' => 'Server error'], 500);
    }
}`,
      javascript_config: `
var datatableMain = $("#datatableMain").DataTable({
    ...mergeWithDefaultOptions({
        ajax: {
            url: $("#datatableMain").data("server"),
            type: 'GET'
        },
        columns: [
            { data: null, className: "dt-control" }, // Expand
            { data: "NoBukti", title: "No Bukti" },
            { data: "Tanggal", title: "Tanggal" },
            { data: "action", orderable: false, searchable: false }
        ]
    })
});`
    },
    expandable_rows: {
      pattern: "Selalu implementasikan expandable rows untuk detail data",
      css_class: "dt-control untuk expand column",
      javascript: "showChildDatatable(row, tr) function"
    }
  };
}

// MCP Server Setup
const server = new Server(
  {
    name: 'bobajetbrain-context',
    version: '1.0.0',
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

// Tools Definition
server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: [
      {
        name: 'get_database_schema',
        description: 'Get database schema dan compatibility rules untuk SQL Server 2008',
        inputSchema: {
          type: 'object',
          properties: {},
        },
      },
      {
        name: 'get_laravel_structure',
        description: 'Get struktur proyek Laravel dan conventions',
        inputSchema: {
          type: 'object',
          properties: {},
        },
      },
      {
        name: 'get_spk_context',
        description: 'Get context spesifik untuk modul SPK',
        inputSchema: {
          type: 'object',
          properties: {},
        },
      },
      {
        name: 'get_coding_standards',
        description: 'Get coding standards dan best practices',
        inputSchema: {
          type: 'object',
          properties: {},
        },
      },
      {
        name: 'get_error_patterns',
        description: 'Get common error patterns dan solutions',
        inputSchema: {
          type: 'object',
          properties: {},
        },
      },
      {
        name: 'get_datatables_patterns',
        description: 'Get DataTables implementation patterns',
        inputSchema: {
          type: 'object',
          properties: {},
        },
      },
    ] satisfies Tool[],
  };
});

// Tool Handlers
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name } = request.params;

  try {
    switch (name) {
      case 'get_database_schema':
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(getDatabaseSchema(), null, 2),
            },
          ],
        };

      case 'get_laravel_structure':
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(getLaravelStructure(), null, 2),
            },
          ],
        };

      case 'get_spk_context':
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(getSPKModuleContext(), null, 2),
            },
          ],
        };

      case 'get_coding_standards':
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(getCodingStandards(), null, 2),
            },
          ],
        };

      case 'get_error_patterns':
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(getErrorPatterns(), null, 2),
            },
          ],
        };

      case 'get_datatables_patterns':
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(getDataTablesPatterns(), null, 2),
            },
          ],
        };

      default:
        throw new Error(`Unknown tool: ${name}`);
    }
  } catch (error) {
    throw new Error(`Error executing tool ${name}: ${error.message}`);
  }
});

// Start Server
async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error('BobaJetBrain MCP Server running...');
}

main().catch((error) => {
  console.error('Server error:', error);
  process.exit(1);
});
