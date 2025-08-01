<x-base-modal formAction="{{ $url }}" formId="{{ $formId }}" modalId="{{ $modalId }}" isDestroy="true" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" modalTitle="Data Aktiva">
    @if ($formMethod !== null)
        @method($formMethod)
    @endif
    <div class="row">
        <x-form-part col="md-6" label="Group Aktiva" type="select2" name="NoMuka" required>
            @if ($res->Perkiraan != null)
                <option value="{{ $res->nomuka->Perkiraan }}">{{ $res->nomuka->Perkiraan . ' - ' . $res->nomuka->Keterangan }}</option>
            @endif
        </x-form-part>
        <x-form-part col="md-6" label="Devisi" type="select2" name="Devisi" required>
            @if ($res->Perkiraan != null)
                <option value="{{ $res->devisi->Devisi }}">{{ $res->devisi->NamaDevisi }}</option>
            @endif
        </x-form-part>
        <x-form-part col="sm-6" label="No. Urut" type="number-text" name="NoBelakang" min="1" max-length="5" placeholder="Masukkan No Urut" value="{{ $res->NoBelakang }}" required />
        <x-form-part col="sm-6" label="No. Aktiva" type="text" name="Perkiraan" value="{{ $res->Perkiraan }}" required readonly />
        <x-form-part col="md-6" label="Tipe Aktiva" type="select2" name="TipeAktiva">
            <option value="0" {{ $res->TipeAktiva == 0 ? 'selected' : '' }}>Aktiva Tetap</option>
            <option value="1" {{ $res->TipeAktiva == 1 ? 'selected' : '' }}>Aktiva Yang Dibayarkan</option>
        </x-form-part>
        <x-form-part col="sm-6" label="Tanggal Perolehan" type="date" name="Tanggal" value="{{ $res->Tanggal == null ? date('Y-m-d') : date('Y-m-d', strtotime($res->Tanggal)) }}" required />
        <x-form-part col="md-12" label="Keterangan" type="textarea" name="Keterangan" style="resize: none;" required>
            {{ $res->Keterangan }}
        </x-form-part>
        <x-form-part col="sm-4" label="Kuantum" type="number" name="Quantity" placeholder="Masukkan Kuantum" value="{{ $res->Quantity }}" required />
        <x-form-part col="sm-4" label="Susut (%)" type="number" name="Persen" placeholder="Masukkan Susut" value="{{ $res->Persen }}" required />
        <x-form-part col="md-4" label="Metode Penyusutan" type="select2" name="Tipe">
            <option value="L" {{ $res->Tipe == 'L' ? 'selected' : '' }}>[L]urus</option>
            <option value="M" {{ $res->Tipe == 'M' ? 'selected' : '' }}>[M]enurun</option>
            <option value="P" {{ $res->Tipe == 'P' ? 'selected' : '' }}>[P]ajak</option>
        </x-form-part>
        <x-form-part col="md-12" label="Akumulasi Penyusutan" type="select2" name="Akumulasi" required>
            @if ($res->Perkiraan != null)
                <option value="{{ $res->akumulasi->Perkiraan }}">{{ $res->akumulasi->Perkiraan . ' - ' . $res->akumulasi->Keterangan }}</option>
            @endif
        </x-form-part>
        <x-form-part col="md-8" label="Biaya 1" type="select2" name="Biaya" required>
            @if ($res->Perkiraan != null)
                @if ($res->biaya != null)
                    <option value="{{ $res->biaya->Perkiraan }}">{{ $res->biaya->Perkiraan . ' - ' . $res->biaya->Keterangan }}</option>
                @endif
            @endif
        </x-form-part>
        <x-form-part col="sm-4" label="Persen Biaya 1 (%)" type="number" min="0" max="100" maxLength="3" name="PersenBiaya1" placeholder="Masukkan Persen Biaya 1" value="{{ $res->PersenBiaya1 != '.00' ? $res->PersenBiaya1 : '' }}" required />
        <x-form-part col="sm-8" label="Biaya 2" type="number-text" name="Biaya2" placeholder="Masukkan Biaya 2" value="{{ $res->biaya2 }}" />
        <x-form-part col="sm-4" label="Persen Biaya 2 (%)" type="number" min="0" max="100" maxLength="3" name="PersenBiaya2" placeholder="Masukkan Persen Biaya 2" value="{{ $res->PersenBiaya2 != '.00' ? $res->PersenBiaya2 : '' }}" />
        <x-form-part col="sm-8" label="Biaya 3" type="number-text" name="Biaya3" placeholder="Masukkan Biaya 3" value="{{ $res->biaya3 }}" />
        <x-form-part col="sm-4" label="Persen Biaya 3 (%)" type="number" min="0" max="100" maxLength="3" name="persenbiaya3" placeholder="Masukkan Persen Biaya 3" value="{{ $res->persenbiaya3 != '.00' ? $res->PersenBiaya3 : '' }}" />
    </div>
</x-base-modal>
