<div class="row">
    <div class="col-sm-12">
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-body">
                <table id="datatableMain" class="table table-bordered table-striped table-hover nowrap w-100" data-server="{{ $res['datatableUrl'] }}">
                    <thead>
                        <tr>
                            <th>Perkiraan</th>
                            <th>Keterangan</th>
                            <th>Persen</th>
                            <th>Metode</th>
                            <th>Perkiraan Akumulasi</th>
                            <th>Perkiraan Biaya 1</th>
                            <th>Persen Biaya 1</th>
                            <th>Perkiraan Biaya 2</th>
                            <th>Persen Biaya 2</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <!-- /.card -->
        </div>
    </div>

    <div class="col-sm-12 d-none" id="contentForm">
        <form action="{{ array_key_exists('formAction', $res) ? $res['formAction'] : '' }}" method="POST" id="{{ $formId }}">
            @csrf
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="row">
                        <x-form-part type="hidden" name="oldPerkiraan" />
                        <x-form-part col="md-6" label="Perkiraan" type="select2" name="Perkiraan" />
                        <x-form-part col="md-6" label="Akumulasi Penyusutan" type="select2" name="Akumulasi" />
                        <x-form-part col="md-8" label="Biaya 1" type="select2" name="Biaya1" required>
                        </x-form-part>
                        <x-form-part col="sm-4" label="Persen Biaya 1 (%)" type="number" min="0" max="100" maxLength="3" name="PersenBiaya1" placeholder="Masukkan Persen Biaya 1" />
                        <x-form-part col="sm-8" label="Biaya 2" type="number-text" name="Biaya2" placeholder="Masukkan Biaya 2" />
                        <x-form-part col="sm-4" label="Persen Biaya 2 (%)" type="number" min="0" max="100" maxLength="3" name="PersenBiaya2" placeholder="Masukkan Persen Biaya 2" />
                        <x-form-part col="sm-4" label="Susut (%)" type="number" name="Persen" min="0" max="100" placeholder="Masukkan Susut" required />
                        <x-form-part col="md-4" label="Metode Penyusutan" type="select2" name="Tipe">
                            <option value="L">[L]urus</option>
                            <option value="M">[M]enurun</option>
                            <option value="P">[P]ajak</option>
                        </x-form-part>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                </div>
                <button class="col-sm-2 btn btn-primary w-100">Simpan</button>
            </div>
        </form>
    </div>
</div>
