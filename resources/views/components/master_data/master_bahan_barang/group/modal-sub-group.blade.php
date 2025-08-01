<x-base-modal modalId="{{ $modalId }}" isDestroy="true" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" 
  modalTitle="Sub Group">
  <div class="row">
    <div class="col-sm-12">
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-body">
                <table id="datatableSubGroup" class="table table-bordered table-striped table-hover nowrap w-100" data-server="{{ route('master-data.master-bahan-dan-barang.sub-group', ['group' => '%kode']) }}">
                    <thead>
                        <tr>
                            <th>Kode Sub Group</th>
                            <th>Nama Sub Group</th>
                            <th>P. Persediaan</th>
                            <th>Perk Hutang</th>
                            <th>Perk PPN</th>
                            <th>Perk Biaya</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <!-- /.card -->
        </div>
    </div>
  
    <div class="col-sm-12 d-none" id="contentForm">
        <form action="" method="POST" id="formSubGroup">
            @csrf
            <div class="row">
                <x-form-part col="md-6" label="Kode" type="text" name="KodeSubGrp" required />
                <x-form-part col="md-6" label="Nama" type="text" name="NamaSubGrp" required/>
                <x-form-part col="md-6" label="Perk. Persediaan" type="select2" name="PerkPers" required/>
                <x-form-part col="md-6" label="Perk. Hutang" type="select2" name="PerkH" required/>
                <x-form-part col="md-6" label="Perk. PPN" type="select2" name="PerkPPN" required/>
                <x-form-part col="md-6" label="Perk. Biaya" type="select2" name="PerkBiaya" required/>
                <button class="col-sm-2 btn btn-primary w-100">Simpan</button>
            </div>
        </form>
    </div>
  </div>
</x-base-modal>

