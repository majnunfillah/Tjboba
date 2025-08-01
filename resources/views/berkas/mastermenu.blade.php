@extends('layouts.app', ['title' => 'Set Pemakai'])
@push('css-plugins')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-fixedcolumns/css/fixedColumns.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css') }}">
@endpush
@push('css')
    <style>
        .treejs ul>li>.treejs-label {
            font-weight: bold;
        }

        .treejs ul>li> .treejs-nodes  .treejs-label {
            font-weight: normal;
        }

        .treejs ul>li {
            margin-bottom: 0.2rem !important;
        }

        .treejs ul>li:after {
            content: "";
            display: block;
            height: 0.5rem !important;
            border-top: 1px solid black;
            z-index: -1;
        }
        .treejs ul>li> .treejs-nodes >li::after {
            content: none;
        }
    </style>
@endpush
@section('body')
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header">
                    <h3 class="card-title">
                        Master Pemakai
                    </h3>
                </div>
                <div class="card-body">
                    <table id="datatableMain" class="table table-bordered table-striped table-hover nowrap w-100" data-server="{{ route('berkas.set-pemakai.index') }}">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Nama Lengkap</th>
                                <th>Departemen</th>
                                <th>Jabatan</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th>Online Dari</th>
                                <th>Kode Kasir</th>
                                <th>Counter</th>
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
    <script src="{{ asset('assets/plugins/treejs/tree.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js') }}"></script>
@endpush
@push('js')
    <script src="{{ asset('assets/js/berkas/set-pemakai.js') }}" type="module"></script>
@endpush
