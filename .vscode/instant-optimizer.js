/**
 * 🚀 INSTANT GitHub Copilot Optimizer untuk BobaJetBrain
 * Zero dependencies, langsung jalan!
 */

class InstantCopilotOptimizer {
    
    // Context patterns yang akan enhance Copilot suggestions
    static getPatterns() {
        return {
            // Repository Pattern
            repository: `
interface SPKRepositoryInterface {
    public function getDataTable(): array;
    public function create(array $data): SPK;
}

class SPKRepository implements SPKRepositoryInterface {
    public function __construct(private SPK $model) {}
    
    public function getDataTable(): array {
        $query = $this->model->with(['customer', 'items.product']);
        return [
            'draw' => request('draw'),
            'recordsTotal' => $query->count(),
            'recordsFiltered' => $query->count(),
            'data' => $query->get()
        ];
    }
}`,

            // Controller Pattern
            controller: `
class SPKController extends Controller {
    public function __construct(private SPKRepository $spkRepository) {}
    
    public function dataTable(): JsonResponse {
        try {
            $data = $this->spkRepository->getDataTable();
            return response()->json($data);
        } catch (Exception $e) {
            Log::error('SPK Error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }
}`,

            // Model Pattern
            model: `
class SPK extends Model {
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['no_spk', 'customer_id', 'tanggal_spk'];
    protected $casts = ['tanggal_spk' => 'datetime'];
    
    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class);
    }
}`
        };
    }
    
    // Konvensi coding yang akan guide Copilot
    static getConventions() {
        return `
# BobaJetBrain Coding Conventions:
- PSR-12 standards
- Repository pattern injection
- Type hints semua parameters
- COALESCE() untuk SQL Server
- Indonesian comments
- DataTables format: {draw, recordsTotal, recordsFiltered, data}
- Try-catch dengan Log::error()
        `;
    }
}

// Export untuk digunakan oleh VS Code dan ES modules
export default InstantCopilotOptimizer;

// CommonJS compatibility
if (typeof module !== 'undefined') {
    module.exports = InstantCopilotOptimizer;
}

// Browser compatibility
if (typeof window !== 'undefined') {
    window.InstantCopilotOptimizer = InstantCopilotOptimizer;
}

console.log('✅ Instant Copilot Optimizer loaded - Ready to enhance your coding!');
console.log('📋 Available patterns:', Object.keys(InstantCopilotOptimizer.getPatterns()));
console.log('🎯 Context optimized for BobaJetBrain Laravel project');
