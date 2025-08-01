<div class="row">
    <div class="col-sm-12">
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-body">
                <table id="datatableMain" class="table table-bordered table-striped table-hover nowrap w-100" data-server="{{ $res['datatableUrl'] }}">
                    <thead>
                        <tr>
                            <th>Perkiraan</th>
                            <th>Keterangan</th>
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
                <x-form-part type="hidden" name="oldPerkiraan" />
                <x-form-part col="md-12" label="Perkiraan" type="select2" name="Perkiraan">
                    {{-- @if ($res->karyawan !== null)
                        <option value="{{ $res->karyawan->keyNIK }}">{{ $res->karyawan->NIK . ' - ' . $res->karyawan->NAMA }}</option>
                    @endif --}}
                </x-form-part>
                <button class="col-sm-2 btn btn-primary w-100">Simpan</button>
            </div>
        </form>
    </div>
</div>
