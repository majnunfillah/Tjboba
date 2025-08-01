@extends('layouts.app', ['title' => 'Master Aktiva'])
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
                    <h3 class="card-title">
                        Master Aktiva
                    </h3>
                </div>
                <div class="card-body">
                    <table id="datatableMain" class="table table-bordered table-striped table-hover nowrap w-100" data-server="{{ route('master-data.master-accounting.aktiva.index') }}">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Kode Aktiva</th>
                                <th>Keterangan</th>
                                <th>Tanggal</th>
                                <th>Devisi</th>
                                <th>Tipe Aktiva</th>
                                <th>Kelompok</th>
                                <th>Bagian</th>
                                <th>Quantity</th>
                                <th>Susut</th>
                                <th>Metode</th>
                                <th>Akumulasi</th>
                                <th>Biaya Penyusutan</th>
                                <th>Persen Biaya 1</th>
                                <th>Biaya Penyusutan 2</th>
                                <th>Persen Biaya 2</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
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
    <script src="{{ asset('assets/js/master-data/master-accounting/aktiva.js') }}" type="module"></script>
@endpush
