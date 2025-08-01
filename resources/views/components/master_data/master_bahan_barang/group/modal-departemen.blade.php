<x-base-modal modalId="{{ $modalId }}" isDestroy="true" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" 
  modalTitle="Departemen Sub Group">
  <div class="row">
    <div class="col-sm-12">
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-body">
                <table id="datatableDepartemen" class="table table-bordered table-striped table-hover nowrap w-100" data-server="{{ route('master-data.master-bahan-dan-barang.sub-group.departemen', ['group' => '%kodegroup', 'subgroup' => '%kodesubgroup']) }}">
                    <thead>
                        <tr>
                            <th>Urut</th>
                            <th>Keterangan</th>
                            <th>Nama Departemen</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <!-- /.card -->
        </div>
    </div>
  
    <div class="col-sm-12 d-none" id="contentFormDepartemen">
        <form action="" method="POST" id="formDepartemen">
            @csrf
            <div class="row">
                <x-form-part col="md-6" label="Departemen" type="select2" name="Departemen" required />
                <div class="col-md-6"></div>
                <button class="col-sm-2 btn btn-primary w-100">Simpan</button>
            </div>
        </form>
    </div>
  </div>
</x-base-modal>

