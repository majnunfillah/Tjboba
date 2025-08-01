<x-base-modal formAction="{{ $url }}" formId="{{ $formId }}" modalId="{{ $modalId }}" isDestroy="true" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" modalTitle="Data Karyawan">
    @if ($formMethod !== null)
        @method($formMethod)
    @endif
    <div class="row">
        {{-- <x-form-part col="sm-10" label="NIK" type="text" name="keynik" disabled /> --}}
        <x-form-part col="md-12" label="NIK" type="select2" name="keynik">
            @if ($res->karyawan !== null)
                <option value="{{ $res->karyawan->keyNIK }}">{{ $res->karyawan->NIK . ' - ' . $res->karyawan->NAMA }}</option>
            @endif
        </x-form-part>
        {{-- <div class="col-sm-1">
          <button class="btn btn-success btnTableKaryawan no-label"><i class="fa fa-search"></i></button>
        </div> --}}
        <x-form-part col="sm-12" label="Nama Lengkap" type="text" name="FullName" placeholder="Masukkan Nama Lengkap" value="{{ $res->FullName }}" required />
        <x-form-part col="sm-6" label="User" type="text" name="USERID" placeholder="Masukkan User ID" value="{{ $res->USERID }}" required />
        <x-form-part col="md-6" label="Departemen" type="select2" name="kodeBag" required>
            @if ($res->departemen !== null)
                <option value="{{ $res->departemen->KDDEP }}">{{ $res->departemen->NMDEP }}</option>
            @else
                <option value="{{ $res->kodeBag }}">{{ $res->kodeBag }}</option>
            @endif
        </x-form-part>
        <x-form-part col="md-6" label="Jabatan" type="select2" name="KodeJab" required>
            @if ($res->jabatan !== null)
                <option value="{{ $res->jabatan->KODEJAB }}">{{ $res->jabatan->NamaJab }}</option>
            @else
                <option value="{{ $res->KodeJab }}">{{ $res->KodeJab }}</option>
            @endif
        </x-form-part>
        <x-form-part col="md-6" label="Status" type="select2" name="STATUS">
            <option value="0" {{ $res->STATUS == 0 ? 'selected' : '' }}>Offline</option>
            <option value="1" {{ $res->STATUS == 1 ? 'selected' : '' }}>Online</option>
        </x-form-part>
        <x-form-part col="md-6" label="Level" type="select2" name="TINGKAT" id="TINGKAT" required>
            <option value="0" {{ $res->TINGKAT == 0 ? 'selected' : '' }}>User</option>
            <option value="1" {{ $res->TINGKAT == 1 ? 'selected' : '' }}>Supervisor</option>
            <option value="2" {{ $res->TINGKAT == 2 ? 'selected' : '' }}>Administrator</option>
        </x-form-part>
        <x-form-part col="md-6" label="Kode Kasir" type="text" name="KodeKasir" placeholder="Masukkan Kode kasir" value="{{ $res->KodeKasir }}" />
        <x-form-part col="md-6" label="Password" type="password" name="UID" placeholder="Masukkan Password" />
        <x-form-part col="md-6" label="Konfirmasi Password" type="password" name="UID2" placeholder="Masukkan Password Konfirmasi" />

    </div>
</x-base-modal>
