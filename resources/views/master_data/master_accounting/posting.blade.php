@extends('layouts.app', ['title' => 'Master Posting'])
@push('css-plugins')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-fixedcolumns/css/fixedColumns.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush

@push('css')
    <style>
      /* zoom, shadow and overlay on hover */
      .cardPosting:hover{
        transform: scale(1.05);
        box-shadow: 0 0 11px rgba(33,33,33,.2);
        z-index: 1;
      }
    </style>
@endpush
@section('body')
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header">
                    <h3 class="card-title">
                        Master Posting
                    </h3>
                </div>
                <div class="card-body">
                    <div class="col-sm-12">
                        <input type="text" id="search-posting" placeholder="Cari Set Data" class="form-control">
                    </div>
                    <div class="row mt-3" id="contentPosting">
                      @foreach ($postings as $item)
                          <div class="col-md-2 col-sm-3 colPosting d-block">
                            <a href="#" id="{{ $item['cardId'] }}" class="btn w-100 h-100 cardPosting" 
                            data-component="{{ array_key_exists('cardComponent', $item) ? $item['cardComponent'] : '' }}"
                            data-width="{{ array_key_exists('modalWidth', $item) ? $item['modalWidth'] : '' }}">
                              <div class="card card-outline text-center">
                                <div class="card-body">
                                  <i class="{{ $item['cardIcon'] }} fa-4x text-gray d-block"></i>
                                  <h6 class="mt-4">{{ $item['cardName'] }}</h6>
                                </div>
                              </div>
                            </a>
                          </div>
                      @endforeach
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
    <script src="{{ asset('assets/js/master-data/master-accounting/posting.js') }}" type="module"></script>
@endpush
