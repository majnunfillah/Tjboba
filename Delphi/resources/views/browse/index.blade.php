@extends('layouts.app')

@section('title', 'Cari Data')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cari Data</h3>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="filter">Filter Data</label>
                                <input type="text" class="form-control" id="filter" placeholder="Ketik kata yang dicari...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="kode_brows">Jenis Data</label>
                                <select class="form-control" id="kode_brows">
                                    <option value="">Pilih Jenis Data</option>
                                    <option value="100101">Gudang</option>
                                    <option value="120302">Barang</option>
                                    <option value="81">Customer Member</option>
                                    <option value="11001">Valas</option>
                                    <option value="11002">Gudang (All)</option>
                                    <option value="1005">Perkiraan</option>
                                    <option value="1006">Valas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-primary" id="btnSearch">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="btnClear">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="dataTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success" id="btnTambah" style="display: none;">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                            <button type="button" class="btn btn-warning" id="btnKoreksi" style="display: none;">
                                <i class="fas fa-edit"></i> Koreksi
                            </button>
                            <button type="button" class="btn btn-danger" id="btnHapus" style="display: none;">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-primary" id="btnOK">
                                <i class="fas fa-check"></i> OK
                            </button>
                            <button type="button" class="btn btn-secondary" id="btnBatal">
                                <i class="fas fa-times"></i> Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden inputs for data passing -->
<input type="hidden" id="selected_id" value="">
<input type="hidden" id="selected_data" value="">
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    .table th {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-action {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    let dataTable;
    let currentKodeBrows = '';
    
    // Initialize DataTable
    function initDataTable() {
        if (dataTable) {
            dataTable.destroy();
        }
        
        dataTable = $('#dataTable').DataTable({
            processing: true,
            serverSide: false,
            searching: false,
            paging: true,
            info: true,
            lengthChange: false,
            pageLength: 25,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'kode', name: 'kode' },
                { data: 'nama', name: 'nama' },
                { data: 'keterangan', name: 'keterangan' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    }
    
    // Search function
    function searchData() {
        const filter = $('#filter').val();
        const kodeBrows = $('#kode_brows').val();
        
        if (!kodeBrows) {
            alert('Pilih jenis data terlebih dahulu!');
            return;
        }
        
        currentKodeBrows = kodeBrows;
        
        $.ajax({
            url: '{{ route("browse.search") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                filter: filter,
                kode_brows: kodeBrows
            },
            success: function(response) {
                if (response.success) {
                    loadDataToTable(response.data, kodeBrows);
                } else {
                    alert('Terjadi kesalahan saat mencari data!');
                }
            },
            error: function() {
                alert('Terjadi kesalahan pada server!');
            }
        });
    }
    
    // Load data to table
    function loadDataToTable(data, kodeBrows) {
        const tableData = [];
        
        data.forEach((item, index) => {
            let row = {
                DT_RowIndex: index + 1,
                kode: item.KodeGdg || item.KodeBrg || item.KodeCustSupp || item.KodeVls || '',
                nama: item.NamaGdg || item.NamaBrg || item.NamaCustSupp || item.NamaVls || '',
                keterangan: getKeterangan(item, kodeBrows),
                action: `<button class="btn btn-sm btn-primary btn-action" onclick="selectData('${item.KodeGdg || item.KodeBrg || item.KodeCustSupp || item.KodeVls}')">
                            <i class="fas fa-check"></i> Pilih
                         </button>`
            };
            tableData.push(row);
        });
        
        dataTable.clear().rows.add(tableData).draw();
    }
    
    // Get keterangan based on kode_brows
    function getKeterangan(item, kodeBrows) {
        switch (kodeBrows) {
            case '100101':
                return item.IsRusak ? 'Rusak' : 'Baik';
            case '120302':
                return `${item.Sat1} / ${item.Sat2}`;
            case '81':
                return item.Telpon || '';
            case '11001':
                return `Kurs: ${item.Kurs}`;
            default:
                return '';
        }
    }
    
    // Select data
    window.selectData = function(id) {
        $('#selected_id').val(id);
        
        // Get selected data
        $.ajax({
            url: '{{ route("browse.getData") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                kode_brows: currentKodeBrows
            },
            success: function(response) {
                if (response.success) {
                    $('#selected_data').val(JSON.stringify(response.data));
                    $('#btnOK').click();
                }
            }
        });
    };
    
    // Event handlers
    $('#btnSearch').click(searchData);
    
    $('#btnClear').click(function() {
        $('#filter').val('');
        $('#kode_brows').val('');
        if (dataTable) {
            dataTable.clear().draw();
        }
    });
    
    $('#filter').keypress(function(e) {
        if (e.which == 13) { // Enter key
            searchData();
        }
    });
    
    $('#btnOK').click(function() {
        const selectedId = $('#selected_id').val();
        const selectedData = $('#selected_data').val();
        
        if (selectedId) {
            // Return data to parent window or form
            if (window.opener) {
                window.opener.setSelectedData(selectedId, selectedData);
                window.close();
            } else {
                // Handle for non-popup usage
                console.log('Selected:', selectedId, selectedData);
            }
        }
    });
    
    $('#btnBatal').click(function() {
        if (window.opener) {
            window.close();
        } else {
            window.history.back();
        }
    });
    
    // Initialize on page load
    initDataTable();
});
</script>
@endpush 