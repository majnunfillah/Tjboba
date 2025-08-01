<x-base-modal formAction="{{ $url }}" formId="{{ $formId }}" modalId="{{ $modalId }}" isDestroy="true" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" modalTitle="Data Perkiraan">
    @if ($formMethod !== null)
        @method($formMethod)
    @endif
    <div class="row">
        <x-form-part col="sm-12" label="Perkiraan" type="number-text" name="Perkiraan" placeholder="Masukkan Perkiraan" value="{{ $res->Perkiraan }}" required />
        <x-form-part col="sm-12" label="Keterangan" type="text" name="Keterangan" placeholder="Masukkan Keterangan" value="{{ $res->Keterangan }}" required />
        <x-form-part col="md-6" label="Kelompok" type="select2" name="Kelompok">
            <option value="0" {{ $res->Kelompok == 0 ? 'selected' : '' }}>Aktiva</option>
            <option value="1" {{ $res->Kelompok == 1 ? 'selected' : '' }}>Kewajiban</option>
            <option value="2" {{ $res->Kelompok == 2 ? 'selected' : '' }}>Modal</option>
            <option value="3" {{ $res->Kelompok == 3 ? 'selected' : '' }}>Pendapatan</option>
            <option value="4" {{ $res->Kelompok == 4 ? 'selected' : '' }}>Biaya</option>
        </x-form-part>
        <x-form-part col="md-6" label="Tipe" type="select2" name="Tipe">
            <option value="0" {{ $res->Tipe == 0 ? 'selected' : '' }}>General</option>
            <option value="1" {{ $res->Tipe == 1 ? 'selected' : '' }}>Detail</option>
        </x-form-part>
        <x-form-part col="md-12" label="Valas" type="select2" name="Valas" required>
            @if ($res->Perkiraan != null)
                @if ($res->valas != null)
                    <option value="{{ $res->valas->KODEVLS }}">{{ '(' . $res->valas->KODEVLS . ')' . $res->valas->NAMAVLS . ' - ' . $res->valas->KURS }}</option>
                @endif
            @endif
        </x-form-part>
        <x-form-part col="md-6" label="Debit / Kerdit" type="select2" name="DK">
            <option value="0" {{ $res->DK == 0 ? 'selected' : '' }}>Debet</option>
            <option value="1" {{ $res->DK == 1 ? 'selected' : '' }}>Kredit</option>
        </x-form-part>
        <x-form-part col="md-6" label="Simbol" type="text" name="Simbol" placeholder="" value="{{ $res->Simbol }}" readonly />
        <x-form-part col="md-6" label="Arus Kas" type="select2" name="KodeAK" required>
            @if ($res->aruskas != null)
                <option value="{{ $res->aruskas->KodeAK }}">{{ $res->aruskas->NamaAK }}</option>
            @endif
        </x-form-part>
        <x-form-part col="md-6" label="Sub Arus Kas" type="select2" name="KodeSAK" required>
            @if ($res->aruskasdet != null)
                <option value="{{ $res->aruskasdet->KodeSubAK }}">{{ $res->aruskasdet->NamaSubAK }}</option>
            @endif
        </x-form-part>

    </div>
</x-base-modal>
