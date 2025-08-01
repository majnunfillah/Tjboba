@extends('layouts.app', ['title' => 'SPK (Surat Perintah Kerja)'])
@push('css-plugins')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-fixedcolumns/css/fixedColumns.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/spk.css') }}">
@endpush
@section('body')
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="spk-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="daftar-spk-tab" data-toggle="pill" href="#daftar-spk" role="tab"
                               aria-controls="daftar-spk" aria-selected="true">
                                <i class="fas fa-list mr-2"></i>Daftar SPK
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="outstanding-so-tab" data-toggle="pill" href="#outstanding-so" role="tab"
                               aria-controls="outstanding-so" aria-selected="false">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Outstanding SO
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="spk-tabContent">
                        <!-- Tab Daftar SPK -->
                        <div class="tab-pane fade show active" id="daftar-spk" role="tabpanel" aria-labelledby="daftar-spk-tab">
                            <div class="mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-table mr-2"></i>Data SPK (Surat Perintah Kerja)
                                </h5>
                            </div>
                            <table id="datatableMain" class="table table-bordered table-striped table-hover nowrap w-100"
                                data-server="{{ route('produksi.spk.index') }}">
                                <thead>
                                    <tr>
                                        <th>⊞ Detail</th>
                                        <th>◐ Jadwal</th>
                                        <th>No Bukti</th>
                                        <th>Tanggal</th>
                                        <th>No SO</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Quantity</th>
                                        <th>Satuan</th>
                                        <th>Authorized 1</th>
                                        <th>Authorized User 1</th>
                                        <th>Authorized Date 1</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <!-- Tab Outstanding SO -->
                        <div class="tab-pane fade" id="outstanding-so" role="tabpanel" aria-labelledby="outstanding-so-tab">
                            <div class="mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>Outstanding Sales Order (SO)
                                </h5>
                                <p class="text-muted mb-0">Daftar SO yang belum selesai diproduksi (Saldo SO - SPK)</p>
                            </div>

                            <!-- Memorial Style Table Container -->
                            <div class="memorial-container">
                                <div class="table-responsive">
                                    <table id="outstandingSoTable" class="table table-bordered table-striped table-hover nowrap w-100"
                                        data-server="{{ route('produksi.spk.outstanding-so') }}">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="text-center">No SO</th>
                                                <th class="text-center">Urut</th>
                                                <th class="text-center">Kode Barang</th>
                                                <th class="text-center">Nama Barang</th>
                                                <th class="text-center">Qty SO</th>
                                                <th class="text-center">Qty SPK</th>
                                                <th class="text-center">Saldo</th>
                                                <th class="text-center">Satuan</th>
                                                <th class="text-center">Tgl Mulai</th>
                                                <th class="text-center">Tgl Kirim</th>
                                                <th class="text-center">Tgl Selesai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="11" class="text-center">
                                                    <i class="fas fa-spinner fa-spin mr-2"></i>Loading Outstanding SO...
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr>
                                                <th colspan="4" class="text-right font-weight-bold">Total:</th>
                                                <th class="text-right font-weight-bold" id="total-qty-so">0</th>
                                                <th class="text-right font-weight-bold" id="total-qty-spk">0</th>
                                                <th class="text-right font-weight-bold" id="total-saldo">0</th>
                                                <th colspan="4"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>


                            </div>
                        </div>
                    </div>
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
    <script src="{{ asset('assets/js/produksi/spk/spk.js') }}" type="module"></script>
@endpush
