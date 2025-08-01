<x-base-modal modalId="{{ $modalId }}" isDestroy="true"
    modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" modalTitle="Data Customer">
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="card card-primary card-outline card-outline-tabs">
                {{-- <div class="card-header">
                    <h3 class="card-title">
                        Data Kas Bank
                    </h3>
                </div> --}}
                <div class="card-body">
                    <table id="datatableCustomer" class="table table-bordered table-striped table-hover nowrap w-100"
                        data-server="{{ route('get-customer-hutang', ["JENIS" => $modalParams->JENIS]) }}">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Alamat</th>
                                <th>Kota</th>
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
</x-base-modal>
