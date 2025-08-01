# Konfigurasi Optimasi GitHub Copilot dengan Claude AI & MCP

## Panduan Setup

### 1. Environment Variables
Tambahkan ke file `.env` atau environment system:

```bash
# Claude AI API Key
ANTHROPIC_API_KEY=your_claude_api_key_here

# GitHub Token untuk enhanced context
GITHUB_TOKEN=your_github_token_here

# Project specific
PROJECT_ROOT=c:/bobajetbrain
MCP_LOG_LEVEL=info
```

### 2. Instalasi Dependencies MCP

```powershell
# Install MCP SDK
cd c:\bobajetbrain\.vscode
npm install

# Atau manual install
npm install @modelcontextprotocol/sdk @modelcontextprotocol/server-stdio
```

### 3. Menjalankan MCP Server

```powershell
# Development mode
npm run dev

# Production mode
npm start
```

## Fitur Optimasi

### 1. **Context-Aware Code Suggestions**
- Claude AI terintegrasi dengan konteks proyek Laravel
- Pemahaman pola Repository dan Service Layer
- Konvensi coding PSR-12 otomatis
- Database schema awareness (SQL Server)

### 2. **Enhanced Code Completion**
- Suggestions berdasarkan existing codebase patterns
- Laravel-specific completions
- Bootstrap + DataTables integration
- Indonesian comments dan dokumentasi

### 3. **Intelligent Code Analysis**
- Pattern recognition untuk MVC architecture
- Automatic error handling suggestions
- SQL Server optimization hints
- Performance recommendations

### 4. **Project Memory**
- Menyimpan context session sebelumnya
- Learning dari code changes
- Personalized suggestions berdasarkan style

## Konfigurasi VS Code Extensions

### Recommended Extensions:
- **GitHub Copilot** - AI pair programmer
- **GitHub Copilot Chat** - Conversational AI
- **Laravel Extension Pack** - Laravel development tools
- **PHP Intelephense** - PHP language server
- **Thunder Client** - API testing

### Settings Optimization:
```json
{
    "ai.claude.contextWindow": 200000,
    "github.copilot.advanced.length": 1000,
    "github.copilot.advanced.listCount": 15,
    "github.copilot.includeWorkspaceContext": true
}
```

## MCP Tools Available

### 1. **get_project_context**
Mendapatkan konteks lengkap proyek BobaJetBrain:
- Architecture overview
- Coding conventions
- Database schema
- Recent changes

### 2. **analyze_code_patterns**
Menganalisis pola kode:
- Laravel patterns
- Repository patterns
- Controller patterns
- Model patterns

### 3. **Resources**
- `project://context/architecture` - Arsitektur proyek
- `project://context/conventions` - Konvensi coding
- `project://context/database` - Schema database

## Penggunaan dalam Development

### 1. **Auto Code Generation**
```php
// Ketik comment, Copilot akan generate code
// Buat SPK controller dengan repository pattern
// Claude akan generate controller yang sesuai dengan pattern proyek
```

### 2. **Intelligent Refactoring**
- Suggest Repository pattern implementation
- Convert to Service Layer pattern
- Optimize database queries
- Add proper error handling

### 3. **Documentation Generation**
- PHPDoc generation berdasarkan function signature
- README.md updates
- API documentation
- Database documentation

## Troubleshooting

### MCP Server Issues
```powershell
# Check if server running
netstat -an | findstr :8080

# Restart MCP server
npm run dev

# Check logs
cat logs/mcp-server.log
```

### Copilot Issues
```bash
# Reload Copilot
Ctrl+Shift+P -> "GitHub Copilot: Reload"

# Check status
Ctrl+Shift+P -> "GitHub Copilot: Check Status"
```

## Performance Tips

1. **Context Management**
   - Tutup files yang tidak diperlukan
   - Gunakan workspace folders
   - Optimize file search patterns

2. **Memory Usage**
   - Restart VS Code setiap 4-6 jam development
   - Clear MCP cache berkala
   - Monitor RAM usage

3. **Network Optimization**
   - Gunakan fast internet connection
   - Enable caching untuk API calls
   - Batch similar requests

## Advanced Configuration

### Custom Prompts untuk Specific Tasks
```json
{
    "copilot.customPrompts": {
        "laravel_controller": "Generate Laravel controller following Repository pattern with proper error handling and Indonesian comments",
        "datatable_response": "Create DataTables response with draw, recordsTotal, recordsFiltered structure",
        "sql_server_query": "Optimize query for SQL Server using COALESCE instead of ISNULL"
    }
}
```

### Context Rules
- Always include type hints
- Follow PSR-12 standards
- Use Repository pattern
- Add proper error handling
- Include Indonesian comments
- Optimize for SQL Server

## Monitoring dan Analytics

### Usage Analytics
- Track suggestion acceptance rate
- Monitor code quality improvements
- Measure development speed increase
- Analyze error reduction

### Performance Metrics
- Response time untuk suggestions
- Context accuracy percentage
- Pattern recognition success rate
- User satisfaction score

---

**Note**: Konfigurasi ini dioptimalkan khusus untuk proyek BobaJetBrain dengan Laravel, SQL Server, dan pola development yang sudah established. Adjust sesuai kebutuhan specific project.
