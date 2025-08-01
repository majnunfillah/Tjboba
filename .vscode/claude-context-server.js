#!/usr/bin/env node

/**
 * Claude Context Server untuk GitHub Copilot
 * Menyediakan konteks proyek yang kaya untuk AI Claude
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import { promises as fs } from 'fs';
import path from 'path';
import { execSync } from 'child_process';

class ClaudeContextServer {
    constructor() {
        this.server = new Server(
            {
                name: "claude-context-server",
                version: "1.0.0"
            },
            {
                capabilities: {
                    tools: {},
                    resources: {},
                    prompts: {}
                }
            }
        );

        this.projectRoot = process.env.PROJECT_ROOT || process.cwd();
        this.setupHandlers();
    }

    setupHandlers() {
        // Tool untuk mendapatkan konteks proyek
        this.server.setRequestHandler('tools/list', async () => ({
            tools: [
                {
                    name: "get_project_context",
                    description: "Mendapatkan konteks lengkap proyek BobaJetBrain",
                    inputSchema: {
                        type: "object",
                        properties: {
                            scope: {
                                type: "string",
                                enum: ["full", "architecture", "recent_changes", "dependencies"],
                                description: "Ruang lingkup konteks yang diinginkan"
                            }
                        }
                    }
                },
                {
                    name: "analyze_code_patterns",
                    description: "Menganalisis pola kode dalam proyek",
                    inputSchema: {
                        type: "object",
                        properties: {
                            pattern_type: {
                                type: "string",
                                enum: ["laravel", "repository", "controller", "model"],
                                description: "Jenis pola yang akan dianalisis"
                            }
                        }
                    }
                }
            ]
        }));

        // Handler untuk get_project_context
        this.server.setRequestHandler('tools/call', async (request) => {
            if (request.params.name === "get_project_context") {
                return await this.getProjectContext(request.params.arguments?.scope || "full");
            }
            if (request.params.name === "analyze_code_patterns") {
                return await this.analyzeCodePatterns(request.params.arguments?.pattern_type || "laravel");
            }
            throw new Error(`Unknown tool: ${request.params.name}`);
        });

        // Resources untuk file-file penting
        this.server.setRequestHandler('resources/list', async () => ({
            resources: [
                {
                    uri: "project://context/architecture",
                    name: "Project Architecture",
                    description: "Arsitektur lengkap proyek BobaJetBrain"
                },
                {
                    uri: "project://context/conventions",
                    name: "Coding Conventions",
                    description: "Konvensi coding yang digunakan dalam proyek"
                },
                {
                    uri: "project://context/database",
                    name: "Database Schema",
                    description: "Skema database dan relasi"
                }
            ]
        }));

        // Handler untuk resources
        this.server.setRequestHandler('resources/read', async (request) => {
            const uri = request.params.uri;
            
            if (uri === "project://context/architecture") {
                return {
                    contents: [
                        {
                            type: "text",
                            text: await this.getArchitectureContext()
                        }
                    ]
                };
            }
            
            if (uri === "project://context/conventions") {
                return {
                    contents: [
                        {
                            type: "text",
                            text: await this.getCodingConventions()
                        }
                    ]
                };
            }

            if (uri === "project://context/database") {
                return {
                    contents: [
                        {
                            type: "text",
                            text: await this.getDatabaseSchema()
                        }
                    ]
                };
            }

            throw new Error(`Resource not found: ${uri}`);
        });
    }

    async getProjectContext(scope) {
        const context = {
            project: "BobaJetBrain - SPK Management System",
            type: "Laravel Web Application",
            framework: "Laravel 10.x",
            database: "SQL Server",
            frontend: "Bootstrap 5 + DataTables + jQuery",
            architecture: "MVC + Repository Pattern"
        };

        switch (scope) {
            case "architecture":
                return {
                    content: [
                        {
                            type: "text",
                            text: JSON.stringify({
                                ...context,
                                layers: [
                                    "Presentation Layer: Controllers & Views",
                                    "Business Logic Layer: Services & Repositories",
                                    "Data Access Layer: Models & Migrations",
                                    "External Layer: APIs & Integrations"
                                ],
                                patterns: [
                                    "Repository Pattern untuk data access",
                                    "Service Pattern untuk business logic",
                                    "Factory Pattern untuk model creation",
                                    "Observer Pattern untuk events"
                                ]
                            }, null, 2)
                        }
                    ]
                };

            case "recent_changes":
                return await this.getRecentChanges();

            case "dependencies":
                return await this.getDependencies();

            default:
                return {
                    content: [
                        {
                            type: "text",
                            text: JSON.stringify(context, null, 2)
                        }
                    ]
                };
        }
    }

    async analyzeCodePatterns(patternType) {
        const patterns = {
            laravel: {
                conventions: [
                    "PSR-12 coding standards",
                    "Laravel naming conventions",
                    "Eloquent ORM patterns",
                    "Blade templating patterns"
                ],
                examples: await this.getLaravelPatterns()
            },
            repository: {
                pattern: "Repository Pattern Implementation",
                structure: await this.getRepositoryPattern()
            },
            controller: {
                pattern: "Controller Pattern Implementation",
                structure: await this.getControllerPattern()
            },
            model: {
                pattern: "Model Pattern Implementation", 
                structure: await this.getModelPattern()
            }
        };

        return {
            content: [
                {
                    type: "text",
                    text: JSON.stringify(patterns[patternType] || patterns.laravel, null, 2)
                }
            ]
        };
    }

    async getArchitectureContext() {
        return `
# Arsitektur Proyek BobaJetBrain

## Struktur Aplikasi
- **Framework**: Laravel 10.x dengan PHP 8.1+
- **Database**: SQL Server dengan Eloquent ORM
- **Frontend**: Bootstrap 5 + DataTables + jQuery
- **Authentication**: Laravel Sanctum
- **API**: RESTful API dengan Resource Controllers

## Pola Desain Utama
1. **Repository Pattern**: Abstraksi data access layer
2. **Service Pattern**: Business logic separation
3. **Factory Pattern**: Model dan data creation
4. **Observer Pattern**: Event handling

## Modul Utama
- SPK (Surat Perintah Kerja) Management
- Inventory Management
- Sales Order Processing
- Accounting & Financial Reporting

## Konvensi Penamaan
- Controllers: PascalCase + "Controller" suffix
- Models: PascalCase singular
- Views: kebab-case dalam folder struktur
- Routes: kebab-case dengan resource naming
        `;
    }

    async getCodingConventions() {
        return `
# Konvensi Coding BobaJetBrain

## PHP & Laravel
- PSR-12 coding standards
- Type hints untuk semua parameter dan return values
- Dokumentasi PHPDoc untuk semua public methods
- Gunakan COALESCE() daripada ISNULL() untuk SQL Server
- Repository pattern untuk data access
- Service layer untuk business logic

## Database
- Naming: snake_case untuk tabel dan kolom
- Primary key: 'id' (auto-increment)
- Foreign key: 'table_name_id'
- Timestamps: created_at, updated_at
- Soft deletes: deleted_at

## Frontend
- Bootstrap 5 utility classes
- DataTables untuk table management
- jQuery untuk DOM manipulation
- Responsive design first

## Error Handling
- Try-catch untuk database operations
- Log semua errors ke Laravel log
- User-friendly error messages
- Validation menggunakan Form Requests
        `;
    }

    async getDatabaseSchema() {
        return `
# Database Schema BobaJetBrain

## Tabel Utama

### spk (Surat Perintah Kerja)
- id (primary key)
- no_spk (varchar, unique)
- customer_id (foreign key)
- tanggal_spk (datetime)
- status (enum: draft, approved, completed, cancelled)
- total_amount (decimal)
- created_at, updated_at, deleted_at

### spk_items (Detail SPK)
- id (primary key)  
- spk_id (foreign key)
- product_id (foreign key)
- quantity (int)
- unit_price (decimal)
- total_price (decimal)

### customers
- id (primary key)
- nama (varchar)
- alamat (text)
- telepon (varchar)
- email (varchar)

### products
- id (primary key)
- nama_produk (varchar)
- kode_produk (varchar, unique)
- harga (decimal)
- stok (int)

## Relasi
- SPK has many SPK Items
- SPK belongs to Customer
- SPK Item belongs to Product
        `;
    }

    async getLaravelPatterns() {
        return `
// Controller Pattern
class SPKController extends Controller {
    public function __construct(private SPKRepository $spkRepository) {}
    
    public function index(): JsonResponse {
        $data = $this->spkRepository->getDataTable();
        return response()->json($data);
    }
}

// Repository Pattern  
class SPKRepository implements SPKRepositoryInterface {
    public function getDataTable(): array {
        $query = SPK::with(['customer', 'items.product']);
        
        return [
            'draw' => request('draw'),
            'recordsTotal' => $query->count(),
            'recordsFiltered' => $query->count(),
            'data' => $query->get()
        ];
    }
}
        `;
    }

    async getRepositoryPattern() {
        return `
# Repository Pattern Implementation BobaJetBrain

## Interface Contract
interface SPKRepositoryInterface {
    public function getAll(): Collection;
    public function getById(int $id): ?SPK;
    public function create(array $data): SPK;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getDataTable(): array;
}

## Repository Implementation
class SPKRepository implements SPKRepositoryInterface {
    protected $model;

    public function __construct(SPK $model) {
        $this->model = $model;
    }

    public function getAll(): Collection {
        return $this->model->with(['customer', 'items.product'])->get();
    }

    public function getById(int $id): ?SPK {
        return $this->model->with(['customer', 'items.product'])->find($id);
    }

    public function create(array $data): SPK {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool {
        return $this->model->where('id', $id)->update($data);
    }

    public function delete(int $id): bool {
        return $this->model->where('id', $id)->delete();
    }

    public function getDataTable(): array {
        $query = $this->model->with(['customer', 'items.product']);
        
        // Search functionality
        if (request('search.value')) {
            $searchValue = request('search.value');
            $query->where(function($q) use ($searchValue) {
                $q->where('no_spk', 'like', "%{$searchValue}%")
                  ->orWhereHas('customer', function($q) use ($searchValue) {
                      $q->where('nama', 'like', "%{$searchValue}%");
                  });
            });
        }

        $recordsTotal = $this->model->count();
        $recordsFiltered = $query->count();
        
        // Ordering untuk DataTables
        if (request('order')) {
            $orderColumn = request('columns.' . request('order.0.column') . '.data');
            $orderDir = request('order.0.dir');
            $query->orderBy($orderColumn, $orderDir);
        }

        // Pagination
        $start = request('start', 0);
        $length = request('length', 10);
        $data = $query->skip($start)->take($length)->get();

        return [
            'draw' => (int) request('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }
}

## Service Provider Registration
// AppServiceProvider.php
public function register(): void {
    $this->app->bind(SPKRepositoryInterface::class, SPKRepository::class);
}
        `;
    }

    async getControllerPattern() {
        return `
# Controller Pattern Implementation BobaJetBrain

## Resource Controller Structure
class SPKController extends Controller {
    protected SPKRepositoryInterface $spkRepository;
    protected SPKService $spkService;

    public function __construct(
        SPKRepositoryInterface $spkRepository,
        SPKService $spkService
    ) {
        $this->spkRepository = $spkRepository;
        $this->spkService = $spkService;
    }

    /**
     * Tampilkan halaman utama SPK
     */
    public function index(): View {
        return view('spk.index');
    }

    /**
     * Data untuk DataTables AJAX
     */
    public function dataTable(): JsonResponse {
        try {
            $data = $this->spkRepository->getDataTable();
            return response()->json($data);
        } catch (Exception $e) {
            Log::error('SPK DataTable Error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat memuat data'], 500);
        }
    }

    /**
     * Form create SPK baru
     */
    public function create(): View {
        $customers = Customer::all();
        $products = Product::all();
        return view('spk.create', compact('customers', 'products'));
    }

    /**
     * Simpan SPK baru
     */
    public function store(StoreSPKRequest $request): JsonResponse {
        try {
            DB::beginTransaction();
            
            $spk = $this->spkService->createSPK($request->validated());
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil dibuat',
                'data' => $spk->load(['customer', 'items.product'])
            ], 201);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SPK Creation Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat SPK: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detail SPK
     */
    public function show(int $id): View {
        $spk = $this->spkRepository->getById($id);
        
        if (!$spk) {
            abort(404, 'SPK tidak ditemukan');
        }
        
        return view('spk.show', compact('spk'));
    }

    /**
     * Form edit SPK
     */
    public function edit(int $id): View {
        $spk = $this->spkRepository->getById($id);
        $customers = Customer::all();
        $products = Product::all();
        
        return view('spk.edit', compact('spk', 'customers', 'products'));
    }

    /**
     * Update SPK
     */
    public function update(UpdateSPKRequest $request, int $id): JsonResponse {
        try {
            DB::beginTransaction();
            
            $updated = $this->spkService->updateSPK($id, $request->validated());
            
            if (!$updated) {
                throw new Exception('SPK tidak ditemukan');
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil diupdate'
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SPK Update Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal update SPK: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus SPK (soft delete)
     */
    public function destroy(int $id): JsonResponse {
        try {
            $deleted = $this->spkRepository->delete($id);
            
            if (!$deleted) {
                throw new Exception('SPK tidak ditemukan');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil dihapus'
            ]);
            
        } catch (Exception $e) {
            Log::error('SPK Delete Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus SPK: ' . $e->getMessage()
            ], 500);
        }
    }
}
        `;
    }

    async getModelPattern() {
        return `
# Model Pattern Implementation BobaJetBrain

## Model dengan Relationships & Business Logic
class SPK extends Model {
    use HasFactory, SoftDeletes;

    protected $table = 'spk';
    
    protected $fillable = [
        'no_spk',
        'customer_id', 
        'tanggal_spk',
        'status',
        'total_amount',
        'notes'
    ];

    protected $casts = [
        'tanggal_spk' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    protected $dates = ['deleted_at'];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Daftar status yang tersedia
     */
    public static function getStatusOptions(): array {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan'
        ];
    }

    /**
     * Relasi ke Customer
     */
    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relasi ke SPK Items
     */
    public function items(): HasMany {
        return $this->hasMany(SPKItem::class);
    }

    /**
     * Boot model untuk auto generate nomor SPK
     */
    protected static function boot(): void {
        parent::boot();

        static::creating(function ($spk) {
            if (empty($spk->no_spk)) {
                $spk->no_spk = $spk->generateSPKNumber();
            }
        });
    }

    /**
     * Generate nomor SPK dengan format: SPK/YYYY/MM/XXXX
     */
    private function generateSPKNumber(): string {
        $year = now()->format('Y');
        $month = now()->format('m');
        $prefix = "SPK/{$year}/{$month}/";
        
        $lastSPK = self::where('no_spk', 'like', $prefix . '%')
                      ->orderBy('no_spk', 'desc')
                      ->first();
        
        if ($lastSPK) {
            $lastNumber = (int) substr($lastSPK->no_spk, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeStatus($query, string $status) {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeDateRange($query, string $startDate, string $endDate) {
        return $query->whereBetween('tanggal_spk', [$startDate, $endDate]);
    }

    /**
     * Accessor untuk format tanggal Indonesia
     */
    public function getTanggalSpkFormattedAttribute(): string {
        return $this->tanggal_spk->format('d/m/Y');
    }

    /**
     * Accessor untuk status badge HTML
     */
    public function getStatusBadgeAttribute(): string {
        $badges = [
            self::STATUS_DRAFT => 'badge-secondary',
            self::STATUS_APPROVED => 'badge-primary', 
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger'
        ];
        
        $badgeClass = $badges[$this->status] ?? 'badge-secondary';
        $statusText = self::getStatusOptions()[$this->status] ?? $this->status;
        
        return "<span class='badge {$badgeClass}'>{$statusText}</span>";
    }

    /**
     * Hitung total amount dari items
     */
    public function calculateTotalAmount(): void {
        $this->total_amount = $this->items()->sum('total_price');
        $this->save();
    }

    /**
     * Check apakah SPK bisa diedit
     */
    public function isEditable(): bool {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_APPROVED]);
    }

    /**
     * Check apakah SPK bisa dihapus
     */
    public function isDeletable(): bool {
        return $this->status === self::STATUS_DRAFT;
    }
}
        `;
    }

    async getRecentChanges() {
        try {
            const { execSync } = await import('child_process');
            
            // Get recent commits
            const commits = execSync('git log --oneline -10 --since="7 days ago"', { 
                cwd: this.projectRoot,
                encoding: 'utf8' 
            }).split('\n').filter(line => line.trim());
            
            // Get changed files in last week
            const changedFiles = execSync('git diff --name-only HEAD~10..HEAD', {
                cwd: this.projectRoot,
                encoding: 'utf8'
            }).split('\n').filter(line => line.trim());
            
            // Analyze patterns
            const analysis = this.analyzeRecentChanges(changedFiles);
            
            return {
                content: [
                    {
                        type: "text",
                        text: JSON.stringify({
                            summary: `${commits.length} commits dalam 7 hari terakhir`,
                            recent_commits: commits.slice(0, 5),
                            changed_files: changedFiles.slice(0, 10),
                            analysis: analysis,
                            recommendations: this.getChangeRecommendations(analysis)
                        }, null, 2)
                    }
                ]
            };
        } catch (error) {
            return {
                content: [
                    {
                        type: "text",
                        text: JSON.stringify({
                            error: "Git analysis tidak tersedia",
                            message: error.message,
                            fallback: "Pastikan git tersedia dan project dalam git repository"
                        }, null, 2)
                    }
                ]
            };
        }
    }

    analyzeRecentChanges(changedFiles) {
        const analysis = {
            primary_areas: [],
            patterns_detected: [],
            file_types: {}
        };
        
        // Analyze file types
        changedFiles.forEach(file => {
            if (file.includes('Controller')) analysis.primary_areas.push('Controllers');
            if (file.includes('Repository')) analysis.primary_areas.push('Repositories');
            if (file.includes('Model')) analysis.primary_areas.push('Models');
            if (file.includes('migration')) analysis.primary_areas.push('Database');
            if (file.includes('.blade.php')) analysis.primary_areas.push('Views');
            if (file.includes('.js')) analysis.primary_areas.push('Frontend');
            
            // Pattern detection
            if (file.match(/SPK/i)) analysis.patterns_detected.push('SPK Module Development');
            if (file.match(/Customer/i)) analysis.patterns_detected.push('Customer Management');
            if (file.match(/Product/i)) analysis.patterns_detected.push('Product Management');
            
            // File type counting
            const ext = file.split('.').pop();
            analysis.file_types[ext] = (analysis.file_types[ext] || 0) + 1;
        });
        
        // Remove duplicates
        analysis.primary_areas = [...new Set(analysis.primary_areas)];
        analysis.patterns_detected = [...new Set(analysis.patterns_detected)];
        
        return analysis;
    }

    getChangeRecommendations(analysis) {
        const recommendations = [];
        
        if (analysis.primary_areas.includes('Controllers')) {
            recommendations.push("Focus pada Repository pattern injection dan error handling");
        }
        
        if (analysis.primary_areas.includes('Models')) {
            recommendations.push("Pastikan relationships dan scopes sudah optimal");
        }
        
        if (analysis.patterns_detected.includes('SPK Module Development')) {
            recommendations.push("Konsisten dengan SPK business logic dan validation rules");
        }
        
        return recommendations;
    }

    async getDependencies() {
        try {
            const packageJsonPath = path.join(this.projectRoot, 'package.json');
            const composerJsonPath = path.join(this.projectRoot, 'composer.json');
            
            let dependencies = {
                php_packages: {},
                js_packages: {},
                analysis: {}
            };
            
            // Read composer.json if exists
            try {
                const composerContent = await fs.readFile(composerJsonPath, 'utf8');
                const composer = JSON.parse(composerContent);
                dependencies.php_packages = {
                    require: composer.require || {},
                    require_dev: composer['require-dev'] || {}
                };
            } catch (e) {
                dependencies.php_packages.error = "composer.json not found";
            }
            
            // Read package.json if exists
            try {
                const packageContent = await fs.readFile(packageJsonPath, 'utf8');
                const packageJson = JSON.parse(packageContent);
                dependencies.js_packages = {
                    dependencies: packageJson.dependencies || {},
                    devDependencies: packageJson.devDependencies || {}
                };
            } catch (e) {
                dependencies.js_packages.error = "package.json not found";
            }
            
            // Analysis
            dependencies.analysis = {
                laravel_version: dependencies.php_packages.require?.['laravel/framework'] || 'unknown',
                php_version: dependencies.php_packages.require?.['php'] || 'unknown',
                database_driver: 'SQL Server (assumed)',
                frontend_framework: 'Bootstrap 5 + jQuery + DataTables'
            };
            
            return {
                content: [
                    {
                        type: "text",
                        text: JSON.stringify(dependencies, null, 2)
                    }
                ]
            };
        } catch (error) {
            return {
                content: [
                    {
                        type: "text",
                        text: JSON.stringify({
                            error: "Dependency analysis failed",
                            message: error.message
                        }, null, 2)
                    }
                ]
            };
        }
    }

    async run() {
        const transport = new StdioServerTransport();
        await this.server.connect(transport);
        console.error("Claude Context Server running...");
    }
}

// Jalankan server
if (import.meta.url === `file://${process.argv[1]}`) {
    const server = new ClaudeContextServer();
    server.run().catch(console.error);
}

export default ClaudeContextServer;
