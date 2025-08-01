# SPK (Surat Perintah Kerja) - Laravel Implementation

## Overview

This document describes the Laravel implementation of the SPK (Production Work Order) system, which replicates the functionality of the original Delphi forms `FrmMainSPK` and `FrmSPK`. The system manages production work orders with comprehensive features including material management, machine scheduling, and multi-level authorization.

## Features

### Core Functionality
- **SPK Management**: Create, edit, delete, and view production work orders
- **Material Management**: Manage BOM (Bill of Materials) and material requirements
- **Machine Scheduling**: Schedule production machines with time allocation
- **Multi-level Authorization**: 5-level authorization system with user tracking
- **SO Integration**: Link SPK with Sales Orders
- **Print Functionality**: Generate printable SPK documents
- **Export Capability**: Export SPK data to Excel format

### Advanced Features
- **Outstanding SO Tracking**: Monitor unfulfilled sales orders
- **Stock Management**: Track available stock vs. outstanding orders
- **BOM Processing**: Automatic material requirement calculation
- **Production Scheduling**: Conflict detection for machine scheduling
- **Audit Trail**: Complete tracking of all changes and authorizations

## Database Structure

### Main Tables

#### dbSPK (SPK Header)
```sql
- NoBukti (Primary Key) - SPK Number
- Tanggal - SPK Date
- NoUrut - Sequence Number
- KodeBrg - Finished Goods Code
- Qnt - Quantity
- NoSat - Unit Number (1,2,3)
- Satuan - Unit
- Isi - Content
- NoBatch - Batch Number
- TglExpired - Expiry Date
- KodeBOM - BOM Code
- IsClose - Status (Open/Close)
- NoSO - Sales Order Number
- UrutSO - SO Sequence
- BiayaLain - Other Costs
- TglSelesai - Completion Date
- QntCetak - Print Quantity
- JenisSpk - SPK Type (0=Normal, 1=Urgent, 2=Rush)
- TglJTSO - SO Due Date
- IsOtorisasi1-5 - Authorization Status
- OtoUser1-5 - Authorized Users
- TglOto1-5 - Authorization Dates
- MaxOL - Maximum Authorization Level
- IsBatal - Cancellation Status
- CreatedBy, CreatedAt, UpdatedBy, UpdatedAt - Audit Fields
```

#### dbSPKDet (SPK Details - Materials)
```sql
- NoBukti (Foreign Key) - SPK Number
- Urut - Sequence
- KodeBrg - Material Code
- Qnt - Quantity
- NoSat - Unit Number
- Satuan - Unit
- Isi - Content
- KodeBOMDet - BOM Detail Code
- CreatedBy, CreatedAt, UpdatedBy, UpdatedAt - Audit Fields
```

#### dbSPKMesin (SPK Machines)
```sql
- NoBukti (Foreign Key) - SPK Number
- Urut - Sequence
- KodePrs - Process Code
- KODEMSN - Machine Code
- Tanggal - Date
- JAMAWAL - Start Time
- JAMAKHIR - End Time
- QNTSPK - SPK Quantity
- Keterangan - Remarks
- TarifMesin - Machine Rate
- JamTenaker - Worker Hours
- JmlTenaker - Number of Workers
- TarifTenaker - Worker Rate
- CreatedBy, CreatedAt, UpdatedBy, UpdatedAt - Audit Fields
```

## File Structure

### Controllers
```
app/Http/Controllers/
├── SPKController.php          # Main SPK controller
```

### Repositories
```
app/Http/Repository/
├── SPKRepository.php          # SPK data operations
├── BarangRepository.php       # Item management
├── SORepository.php          # Sales Order operations
├── MesinRepository.php       # Machine management
├── BOMRepository.php         # Bill of Materials
└── KaryawanRepository.php    # Employee management
```

### Requests
```
app/Http/Requests/
├── SPKRequest.php            # SPK validation
├── SPKDetailRequest.php      # SPK detail validation
└── SPKMesinRequest.php       # SPK machine validation
```

### Views
```
resources/views/spk/
├── index.blade.php           # SPK listing page
├── form.blade.php            # SPK form (create/edit)
└── print.blade.php           # SPK print view
```

### JavaScript
```
public/assets/js/
├── spk.js                    # SPK listing functionality
└── spk-form.js               # SPK form functionality
```

### Exports
```
app/Exports/
└── SPKExport.php             # Excel export functionality
```

## API Endpoints

### Main SPK Operations
```
GET    /spk                    # SPK listing page
GET    /spk/create            # Create SPK form
POST   /spk                   # Store new SPK
GET    /spk/{id}/edit         # Edit SPK form
PUT    /spk/{id}              # Update SPK
DELETE /spk/{id}              # Delete SPK
GET    /spk/{id}/print        # Print SPK
POST   /spk/{id}/authorize    # Authorize SPK
POST   /spk/{id}/cancel-authorization # Cancel authorization
GET    /spk/export            # Export SPK data
```

### Data Endpoints
```
POST   /spk/get-data          # Get SPK data for DataTables
POST   /spk/get-outstanding-so # Get outstanding SO data
POST   /spk/get-stock-data    # Get stock data
GET    /spk/{spkId}/details   # Get SPK details
GET    /spk/{spkId}/mesin     # Get SPK machines
```

### Detail Management
```
POST   /spk/{spkId}/details           # Add SPK detail
PUT    /spk/{spkId}/details/{detailId} # Update SPK detail
DELETE /spk/{spkId}/details/{detailId} # Delete SPK detail
```

### Machine Management
```
POST   /spk/{spkId}/mesin           # Add SPK machine
PUT    /spk/{spkId}/mesin/{mesinId} # Update SPK machine
DELETE /spk/{spkId}/mesin/{mesinId} # Delete SPK machine
```

### Search Endpoints
```
GET    /spk/search/barang      # Search items
GET    /spk/search/barang-jadi # Search finished goods
GET    /spk/search/so          # Search sales orders
GET    /spk/search/mesin       # Search machines
GET    /spk/search/karyawan    # Search employees
GET    /spk/get-bom            # Get BOM data
```

## Business Logic

### SPK Number Generation
- Format: `SPK` + `YYYY` + `MM` + `XXXX` (4-digit sequence)
- Example: `SPK2024010001`
- Auto-increment based on year and month

### Authorization System
- 5-level authorization system
- Each level must be completed before proceeding
- Track user and timestamp for each authorization
- Prevent unauthorized modifications after authorization

### BOM Processing
- Automatic material requirement calculation
- Load BOM details into SPK details
- Calculate quantities based on SPK quantity
- Support for multiple units and conversions

### Machine Scheduling
- Conflict detection for machine scheduling
- Time-based scheduling with start/end times
- Resource allocation tracking
- Production capacity management

### Stock Management
- Real-time stock availability checking
- Outstanding order tracking
- Available stock calculation
- Stock minus detection

## Security Features

### Authentication & Authorization
- User authentication required for all operations
- Permission-based access control (Permission Code: 08103)
- Session management and timeout
- CSRF protection on all forms

### Data Validation
- Comprehensive input validation
- SQL injection prevention
- XSS protection
- File upload security

### Audit Trail
- Complete tracking of all changes
- User activity logging
- Timestamp recording
- Change history maintenance

## Performance Optimizations

### Database Optimization
- Efficient indexing on frequently queried columns
- Optimized SQL queries for large datasets
- Connection pooling
- Query result caching

### Frontend Optimization
- Server-side DataTables processing
- AJAX-based operations
- Progressive loading
- Client-side caching

### Memory Management
- Efficient data structures
- Garbage collection optimization
- Resource cleanup
- Memory leak prevention

## Error Handling

### Exception Management
- Comprehensive error catching
- User-friendly error messages
- Detailed logging for debugging
- Graceful degradation

### Validation Errors
- Field-level validation
- Custom error messages
- Real-time validation feedback
- Form state preservation

### Database Errors
- Connection error handling
- Transaction rollback
- Data integrity checks
- Recovery procedures

## Testing Strategy

### Unit Testing
- Controller method testing
- Repository method testing
- Validation rule testing
- Business logic testing

### Integration Testing
- API endpoint testing
- Database integration testing
- External service testing
- End-to-end workflow testing

### User Acceptance Testing
- Functional requirement testing
- User interface testing
- Performance testing
- Security testing

## Deployment Considerations

### Environment Setup
- Database migration execution
- Configuration file setup
- Environment variable configuration
- File permission setup

### Database Migration
```bash
php artisan migrate --path=database/migrations/2024_01_01_000000_add_spk_menu.php
```

### Configuration
- Database connection settings
- File storage configuration
- Email settings
- Logging configuration

### Monitoring
- Application performance monitoring
- Error tracking and alerting
- Database performance monitoring
- User activity monitoring

## Future Enhancements

### Planned Features
- Mobile application support
- Real-time notifications
- Advanced reporting
- Integration with ERP systems

### Performance Improvements
- Database query optimization
- Caching implementation
- CDN integration
- Load balancing

### Security Enhancements
- Two-factor authentication
- Advanced role-based access
- API rate limiting
- Enhanced audit logging

## Troubleshooting

### Common Issues

#### Database Connection Issues
- Check database credentials
- Verify network connectivity
- Review connection pool settings
- Check database server status

#### Performance Issues
- Monitor query execution time
- Check database indexing
- Review caching configuration
- Analyze server resources

#### Authorization Issues
- Verify user permissions
- Check authorization levels
- Review session configuration
- Validate user roles

### Debugging Tools
- Laravel Debug Bar
- Database query logging
- Error log analysis
- Performance profiling

## Support and Maintenance

### Documentation
- API documentation
- User manual
- Administrator guide
- Troubleshooting guide

### Training
- User training materials
- Administrator training
- Developer documentation
- Best practices guide

### Maintenance Schedule
- Regular security updates
- Performance monitoring
- Database maintenance
- Backup procedures

## Conclusion

The SPK Laravel implementation provides a comprehensive, secure, and scalable solution for production work order management. It successfully replicates all functionality from the original Delphi system while adding modern web-based features and improved user experience.

The system is designed with maintainability, performance, and security in mind, making it suitable for production use in manufacturing environments. 