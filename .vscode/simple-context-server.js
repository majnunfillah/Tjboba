#!/usr/bin/env node

/**
 * Simplified Claude Context Server untuk GitHub Copilot
 * Implementasi sederhana tanpa MCP SDK eksternal
 */

import { createServer } from 'http';
import { promises as fs } from 'fs';
import path from 'path';
import { execSync } from 'child_process';

class SimplifiedClaudeContextServer {
    constructor() {
        this.projectRoot = process.env.PROJECT_ROOT || process.cwd();
        this.port = process.env.PORT || 3001;
        this.server = null;
    }

    // Context data yang akan diberikan ke Copilot
    getContextData() {
        return {
            project: "BobaJetBrain - SPK Management System",
            framework: "Laravel 10.x",
            database: "SQL Server",
            architecture: "MVC + Repository Pattern",
            patterns: {
                repository: this.getRepositoryPattern(),
                controller: this.getControllerPattern(),
                model: this.getModelPattern()
            },
            conventions: this.getCodingConventions()
        };
    }

    getRepositoryPattern() {
        return `
// Repository Pattern untuk BobaJetBrain
interface SPKRepositoryInterface {
    public function getDataTable(): array;
    public function create(array $data): SPK;
    public function getById(int $id): ?SPK;
}

class SPKRepository implements SPKRepositoryInterface {
    public function __construct(private SPK $model) {}
    
    public function getDataTable(): array {
        $query = $this->model->with(['customer', 'items.product']);
        
        // DataTables format
        return [
            'draw' => request('draw'),
            'recordsTotal' => $query->count(),
            'recordsFiltered' => $query->count(), 
            'data' => $query->get()
        ];
    }
}`;
    }

    getControllerPattern() {
        return `
// Controller Pattern untuk BobaJetBrain
class SPKController extends Controller {
    public function __construct(private SPKRepository $spkRepository) {}
    
    public function index(): View {
        return view('spk.index');
    }
    
    public function dataTable(): JsonResponse {
        try {
            $data = $this->spkRepository->getDataTable();
            return response()->json($data);
        } catch (Exception $e) {
            Log::error('SPK Error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }
}`;
    }

    getModelPattern() {
        return `
// Model Pattern untuk BobaJetBrain
class SPK extends Model {
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['no_spk', 'customer_id', 'tanggal_spk', 'status'];
    protected $casts = ['tanggal_spk' => 'datetime'];
    
    // Relations
    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class);
    }
    
    public function items(): HasMany {
        return $this->hasMany(SPKItem::class);
    }
}`;
    }

    getCodingConventions() {
        return `
# Konvensi BobaJetBrain:
- PSR-12 coding standards
- Repository pattern untuk data access
- Type hints untuk semua parameters
- COALESCE() untuk SQL Server
- Indonesian comments
- DataTables format: {draw, recordsTotal, recordsFiltered, data}
        `;
    }

    // HTTP Server untuk menerima requests dari VS Code
    createHttpServer() {
        this.server = createServer((req, res) => {
            // Set CORS headers
            res.setHeader('Access-Control-Allow-Origin', '*');
            res.setHeader('Access-Control-Allow-Methods', 'GET, POST');
            res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
            res.setHeader('Content-Type', 'application/json');

            if (req.method === 'OPTIONS') {
                res.writeHead(200);
                res.end();
                return;
            }

            // Handle different endpoints
            const url = new URL(req.url, `http://localhost:${this.port}`);
            
            switch (url.pathname) {
                case '/context':
                    this.handleContextRequest(req, res);
                    break;
                case '/patterns':
                    this.handlePatternsRequest(req, res);
                    break;
                case '/health':
                    res.writeHead(200);
                    res.end(JSON.stringify({ status: 'OK', timestamp: new Date().toISOString() }));
                    break;
                default:
                    res.writeHead(404);
                    res.end(JSON.stringify({ error: 'Not found' }));
            }
        });
    }

    handleContextRequest(req, res) {
        try {
            const context = this.getContextData();
            res.writeHead(200);
            res.end(JSON.stringify(context, null, 2));
        } catch (error) {
            res.writeHead(500);
            res.end(JSON.stringify({ error: error.message }));
        }
    }

    handlePatternsRequest(req, res) {
        try {
            const patterns = {
                repository: this.getRepositoryPattern(),
                controller: this.getControllerPattern(),
                model: this.getModelPattern()
            };
            res.writeHead(200);
            res.end(JSON.stringify(patterns, null, 2));
        } catch (error) {
            res.writeHead(500);
            res.end(JSON.stringify({ error: error.message }));
        }
    }

    async start() {
        this.createHttpServer();
        
        return new Promise((resolve, reject) => {
            this.server.listen(this.port, (err) => {
                if (err) {
                    reject(err);
                } else {
                    console.log(`🚀 Claude Context Server running on http://localhost:${this.port}`);
                    console.log(`📁 Project root: ${this.projectRoot}`);
                    console.log(`⚡ Ready to provide context to GitHub Copilot!`);
                    resolve();
                }
            });
        });
    }

    stop() {
        if (this.server) {
            this.server.close();
        }
    }
}

// Start server
if (import.meta.url === `file://${process.argv[1]}`) {
    const server = new SimplifiedClaudeContextServer();
    
    server.start().catch(error => {
        console.error('❌ Failed to start server:', error);
        process.exit(1);
    });

    // Graceful shutdown
    process.on('SIGINT', () => {
        console.log('\n👋 Shutting down server...');
        server.stop();
        process.exit(0);
    });
}

export default SimplifiedClaudeContextServer;
