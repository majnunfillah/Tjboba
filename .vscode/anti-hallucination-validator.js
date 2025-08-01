/**
 * Anti-Hallucination Validator untuk GitHub Copilot
 * Memvalidasi suggestions terhadap actual codebase
 */

class AntiHallucinationValidator {
    constructor() {
        this.projectRoot = process.env.PROJECT_ROOT || process.cwd();
        this.allowedPatterns = this.loadAllowedPatterns();
        this.existingClasses = this.scanExistingClasses();
        this.validationRules = this.loadValidationRules();
    }

    // Load patterns yang diizinkan berdasarkan existing codebase
    loadAllowedPatterns() {
        return {
            controllers: [
                'Controller',
                'Resource',
                'JsonResponse',
                'Request',
                'Exception',
                'Log'
            ],
            repositories: [
                'Repository',
                'Interface',
                'Collection',
                'Model'
            ],
            models: [
                'Model',
                'HasFactory',
                'SoftDeletes',
                'BelongsTo',
                'HasMany',
                'HasOne'
            ],
            frameworks: [
                'Laravel',
                'Eloquent',
                'Bootstrap',
                'DataTables',
                'jQuery'
            ]
        };
    }

    // Scan existing classes dalam project
    scanExistingClasses() {
        // Simulated - in real implementation, scan actual files
        return [
            'SPK',
            'SPKController',
            'SPKRepository',
            'SPKService',
            'Customer',
            'Product',
            'SPKItem'
        ];
    }

    // Load validation rules
    loadValidationRules() {
        return {
            // Forbidden patterns
            forbidden: [
                'eval(',
                'exec(',
                'shell_exec(',
                'system(',
                'passthru(',
                'proc_open(',
                'file_get_contents(\'http',
                'curl_exec(',
                'mysql_',           // Deprecated MySQL functions
                'mysqli_',          // Should use Eloquent
                'PDO',             // Should use Eloquent
                'new DateTime(',   // Should use Carbon
                'mail(',           // Should use Mail facade
                'die(',            // Should use proper error handling
                'exit(',           // Should use proper error handling
                'var_dump(',       // Should use Log
                'print_r(',        // Should use Log
                'echo ',           // In controllers, should use response
                'include ',        // Should use proper autoloading
                'require ',        // Should use proper autoloading
            ],
            
            // Required patterns for specific contexts
            required: {
                controller: [
                    'extends Controller',
                    'JsonResponse',
                    'try \\{',
                    'catch \\('
                ],
                repository: [
                    'implements.*Interface',
                    'public function'
                ],
                model: [
                    'extends Model',
                    'protected \\$fillable'
                ]
            },
            
            // Laravel-specific validations
            laravel: {
                version: '10.x',
                requiredNamespaces: [
                    'Illuminate\\',
                    'App\\',
                    'Database\\',
                    'Tests\\'
                ],
                deprecatedFunctions: [
                    'Input::',        // Use Request
                    'DB::table(',     // Should use Eloquent when possible
                    'Route::controller(' // Use resource routes
                ]
            }
        };
    }

    // Validate suggestion against rules
    validateSuggestion(suggestion, context = {}) {
        const results = {
            isValid: true,
            score: 100,
            issues: [],
            warnings: []
        };

        // Check forbidden patterns
        this.validationRules.forbidden.forEach(pattern => {
            if (suggestion.includes(pattern)) {
                results.isValid = false;
                results.score -= 50;
                results.issues.push(`Forbidden pattern detected: ${pattern}`);
            }
        });

        // Check context-specific requirements
        if (context.type) {
            const required = this.validationRules.required[context.type];
            if (required) {
                required.forEach(pattern => {
                    if (!new RegExp(pattern).test(suggestion)) {
                        results.score -= 20;
                        results.warnings.push(`Missing recommended pattern: ${pattern}`);
                    }
                });
            }
        }

        // Check for non-existent classes
        const classMatches = suggestion.match(/new\s+([A-Z][a-zA-Z0-9_]+)/g);
        if (classMatches) {
            classMatches.forEach(match => {
                const className = match.replace('new ', '');
                if (!this.existingClasses.includes(className) && 
                    !this.isStandardLaravelClass(className)) {
                    results.score -= 15;
                    results.warnings.push(`Reference to non-existent class: ${className}`);
                }
            });
        }

        // Check for outdated patterns
        this.validationRules.laravel.deprecatedFunctions.forEach(deprecated => {
            if (suggestion.includes(deprecated)) {
                results.score -= 25;
                results.warnings.push(`Deprecated Laravel pattern: ${deprecated}`);
            }
        });

        // Adjust final score
        if (results.score < 60) {
            results.isValid = false;
        }

        return results;
    }

    // Check if class is standard Laravel class
    isStandardLaravelClass(className) {
        const standardClasses = [
            'Controller', 'Model', 'Request', 'Response', 'JsonResponse',
            'Collection', 'Builder', 'Factory', 'Seeder', 'Migration',
            'Middleware', 'ServiceProvider', 'Exception', 'Log', 'DB',
            'Route', 'View', 'Redirect', 'Session', 'Cache', 'Storage'
        ];
        return standardClasses.includes(className);
    }

    // Validate DataTables response format
    validateDataTablesResponse(suggestion) {
        const requiredKeys = ['draw', 'recordsTotal', 'recordsFiltered', 'data'];
        const results = {
            isValid: true,
            score: 100,
            issues: []
        };

        requiredKeys.forEach(key => {
            if (!suggestion.includes(`'${key}'`) && !suggestion.includes(`"${key}"`)) {
                results.score -= 25;
                results.issues.push(`Missing DataTables key: ${key}`);
            }
        });

        if (results.score < 75) {
            results.isValid = false;
        }

        return results;
    }

    // Generate validation report
    generateValidationReport(suggestions) {
        const report = {
            totalSuggestions: suggestions.length,
            validSuggestions: 0,
            invalidSuggestions: 0,
            averageScore: 0,
            issues: [],
            recommendations: []
        };

        let totalScore = 0;

        suggestions.forEach((suggestion, index) => {
            const validation = this.validateSuggestion(suggestion.code, suggestion.context);
            
            if (validation.isValid) {
                report.validSuggestions++;
            } else {
                report.invalidSuggestions++;
            }

            totalScore += validation.score;
            
            if (validation.issues.length > 0) {
                report.issues.push({
                    suggestionIndex: index,
                    issues: validation.issues,
                    warnings: validation.warnings
                });
            }
        });

        report.averageScore = totalScore / suggestions.length;

        // Generate recommendations
        if (report.averageScore < 70) {
            report.recommendations.push('Consider reducing Copilot suggestion length');
            report.recommendations.push('Add more specific context rules');
            report.recommendations.push('Review and update project patterns');
        }

        return report;
    }

    // Real-time validation for VS Code
    validateInRealTime(suggestion, filePath) {
        const context = this.detectContext(filePath);
        const validation = this.validateSuggestion(suggestion, context);
        
        // Return validation with confidence score
        return {
            confidence: validation.score / 100,
            recommendation: validation.isValid ? 'accept' : 'reject',
            reasons: validation.issues.concat(validation.warnings),
            alternatives: this.suggestAlternatives(suggestion, context)
        };
    }

    // Detect context from file path
    detectContext(filePath) {
        if (filePath.includes('Controller')) return { type: 'controller' };
        if (filePath.includes('Repository')) return { type: 'repository' };
        if (filePath.includes('Model')) return { type: 'model' };
        if (filePath.includes('.blade.php')) return { type: 'view' };
        return { type: 'general' };
    }

    // Suggest alternatives for rejected suggestions
    suggestAlternatives(suggestion, context) {
        const alternatives = [];
        
        // Example: Replace deprecated patterns
        if (suggestion.includes('Input::')) {
            alternatives.push('Use Request facade instead of Input');
        }
        
        if (suggestion.includes('var_dump(')) {
            alternatives.push('Use Log::info() for debugging');
        }
        
        return alternatives;
    }
}

// Usage examples
const validator = new AntiHallucinationValidator();

// Example validation
const testSuggestion = `
class SPKController extends Controller {
    public function dataTable(): JsonResponse {
        $data = SPK::all();
        return response()->json($data);
    }
}`;

const validation = validator.validateSuggestion(testSuggestion, { type: 'controller' });
console.log('Validation Result:', validation);

// Export for VS Code integration
if (typeof module !== 'undefined') {
    module.exports = AntiHallucinationValidator;
}

export default AntiHallucinationValidator;
