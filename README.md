# BobaJetBrain - Laravel Financial Management System

## 🏢 Sistem Manajemen Akuntansi/Keuangan

Aplikasi Laravel untuk manajemen sistem keuangan dengan fitur lengkap termasuk SPK (Surat Perintah Kerja), kas bank, memorial, dan aktiva.

## 🤖 AI-Powered Development

Proyek ini dikonfigurasi untuk bekerja optimal dengan AI assistants seperti **GitHub Copilot**, **Cursor**, dan **Claude**. 

### 📋 Quick Setup untuk AI Assistant

1. **GitHub Copilot**: 
   - Sudah dikonfigurasi dengan context Indonesia
   - Otomatis mengikuti Laravel conventions
   - Support SQL Server 2008 compatibility

2. **Cursor**: 
   - Gunakan file `.cursorrules` untuk context lengkap
   - Aturan development vs production environment

3. **VS Code**: 
   - Konfigurasi sudah ada di `.vscode/settings.json`
   - Context khusus untuk proyek Laravel Indonesia

## 🔧 Tech Stack

- **Backend**: Laravel 9.x + PHP 8.1+
- **Database**: SQL Server 2008 (Legacy)
- **Frontend**: AdminLTE + Bootstrap + DataTables
- **JavaScript**: jQuery + Chart.js
- **AI**: GitHub Copilot ready dengan context Indonesia

## 🚀 Key Features

### 📊 Modul Keuangan
- **Kas Bank**: Manajemen kas dan rekening bank
- **Memorial**: Transaksi jurnal memorial  
- **Aktiva**: Manajemen aset dan depresiasi
- **Berkas**: Sistem dokumen keuangan

### 📋 Modul Operasional  
- **SPK**: Surat Perintah Kerja (Work Orders)
- **Inventory**: Manajemen stok dan barang
- **Sales Order**: Pengelolaan pesanan penjualan

## 🎯 AI Assistant Guidelines

### Context yang Sudah Dikonfigurasi
```json
{
  "projectType": "Laravel Financial System",
  "language": "Indonesian comments & docs",
  "database": "SQL Server 2008",
  "architecture": "MVC + Repository Pattern",
  "conventions": "PSR-12 + Laravel Standards"
}
```

### Pola Development dengan AI
1. **Analisis**: AI memahami struktur proyek Laravel Indonesia
2. **Coding**: Otomatis menggunakan Repository pattern dan type hints
3. **SQL**: Kompatibel dengan SQL Server 2008 (tanpa CONCAT, FORMAT, IIF)
4. **Testing**: Generate test dengan context yang tepat

## 🛡️ Security & Environment

### Development vs Production
- **Development**: `bobajetbrain/` - untuk coding dan testing
- **Production**: `me.pmk.my.id/` - READ ONLY reference
- **Safety**: AI assistant tidak akan memodifikasi production

### Database Compatibility
- ✅ SQL Server 2008 compatible syntax
- ✅ Menggunakan operator `+` untuk concatenation  
- ✅ `CASE WHEN` untuk conditional logic
- ✅ `CONVERT()` untuk formatting
- ❌ TIDAK menggunakan CONCAT(), FORMAT(), IIF()

---

## 📚 About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
