@extends('layouts.app', ['title' => 'Hitung Ulang HPP'])
@push('css-plugins')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-fixedcolumns/css/fixedColumns.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush
@section('body')
<div class="row">
    <div class="col-md-12 mt-2">
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header">
                <h3 class="card-title">Proses Hitung Ulang HPP</h3>
            </div>
            <div class="card-body">
                <form id="formHitungUlangHpp">
                    <div class="row">
                        <x-form-part col="sm-4" label="Jenis Barang" type="select" name="jenis_barang">
                            <option value="semua">Semua Barang</option>
                            <option value="per_barang">Per Barang</option>
                        </x-form-part>
                        <x-form-part col="sm-2" label="Bulan" type="number" name="bulan" min="1" max="12" value="{{ $currentMonth }}" />
                        <x-form-part col="sm-2" label="Tahun" type="number" name="tahun" min="1999" max="9999" value="{{ $currentYear }}" />
                        <div class="col-sm-4" id="barang_range" style="display:none;">
                            <div class="row">
                                <x-form-part col="sm-6" label="Kode Barang Awal" type="text" name="kode_barang_awal" />
                                <x-form-part col="sm-6" label="Kode Barang Akhir" type="text" name="kode_barang_akhir" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info mt-2 mb-2">
                                <strong>Proses ini akan menghitung harga pokok persediaan</strong>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="progress" id="progress_bar" style="display: none; height:30px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div id="progress_text" class="text-center mt-2" style="display: none; font-size:16px; font-weight:bold; color:#007bff;">
                                <span id="progress_message">Memulai proses...</span>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary" id="btn_proses">
                                <i class="fas fa-calculator"></i> Proses
                            </button>
                            <button type="button" class="btn btn-success" id="btn_export" style="display: none;">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Keluar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header">
                <h3 class="card-title">Daftar Stock Minus</h3>
            </div>
            <div class="card-body">
                <table id="datatableMain" class="table table-bordered table-striped table-hover nowrap w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Gudang</th>
                            <th>Kode Barang</th>
                            <th>Jenis Bahan</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js-plugins')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-fixedcolumns/js/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-maskmoney/jquery.maskMoney.js') }}"></script>
@endpush
@push('js')
<script>
$(document).ready(function() {
    // DataTable
    var datatableMain = $("#datatableMain").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("utilitas.hitung-ulang-hpp.get-stock-minus") }}',
            type: 'POST',
            data: function(d) {
                d._token = '{{ csrf_token() }}';
            }
        },
        columns: [
            { data: 'Urut', name: 'Urut' },
            { data: 'KodeGdg', name: 'KodeGdg' },
            { data: 'KodeBrg', name: 'KodeBrg' },
            { data: 'JenisBahan', name: 'JenisBahan' }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
        }
    });

    // Jenis barang change
    $(document).on('change', 'select[name="jenis_barang"]', function() {
        if ($(this).val() === 'per_barang') {
            $('#barang_range').show();
        } else {
            $('#barang_range').hide();
        }
    });

    // Proses button
    $('#btn_proses').click(function() {
        if (!validateForm()) return;
        const formData = {
            bulan: $('input[name="bulan"]').val(),
            tahun: $('input[name="tahun"]').val(),
            jenis_barang: $('select[name="jenis_barang"]').val(),
            kode_barang_awal: $('input[name="kode_barang_awal"]').val(),
            kode_barang_akhir: $('input[name="kode_barang_akhir"]').val(),
            _token: '{{ csrf_token() }}'
        };
        startProgress();
        $(this).prop('disabled', true);
        $.ajax({
            url: '{{ route("utilitas.hitung-ulang-hpp.proses") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    updateProgressBar(100);
                    $('#progress_message').text('Proses hitung ulang HPP selesai.');
                    $('#btn_export').show();
                    datatableMain.ajax.reload();
                    alert('Proses hitung ulang HPP berhasil diselesaikan!');
                } else {
                    stopProgress();
                    alert('Error: ' + response.message);
                }
                $('#btn_proses').prop('disabled', false);
            },
            error: function(xhr, status, error) {
                stopProgress();
                alert('Terjadi kesalahan: ' + error);
                $('#btn_proses').prop('disabled', false);
            }
        });
    });
    // Export button
    $('#btn_export').click(function() {
        window.location.href = '{{ route("utilitas.hitung-ulang-hpp.export") }}';
    });
    // Progress helpers
    function startProgress() {
        $('#progress_bar').show();
        $('#progress_text').show();
        $('#progress_message').text('Memulai proses...');
        updateProgressBar(0);
    }
    function stopProgress() {
        $('#progress_bar').hide();
        $('#progress_text').hide();
    }
    function updateProgressBar(percentage) {
        $('.progress-bar').css('width', percentage + '%');
        $('.progress-bar').text(percentage + '%');
    }
    function validateForm() {
        const bulan = $('input[name="bulan"]').val();
        const tahun = $('input[name="tahun"]').val();
        const jenisBarang = $('select[name="jenis_barang"]').val();
        if (!bulan || bulan < 1 || bulan > 12) {
            alert('Bulan harus antara 1-12');
            return false;
        }
        if (!tahun || tahun < 1999) {
            alert('Tahun harus minimal 1999');
            return false;
        }
        if (jenisBarang === 'per_barang') {
            const kodeAwal = $('input[name="kode_barang_awal"]').val();
            const kodeAkhir = $('input[name="kode_barang_akhir"]').val();
            if (!kodeAwal || !kodeAkhir) {
                alert('Kode barang awal dan akhir harus diisi');
                return false;
            }
        }
        return true;
    }
});
</script>
@endpush 