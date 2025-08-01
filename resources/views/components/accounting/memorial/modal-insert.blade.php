<x-base-modal modalId="{{ $modalId }}" formId="{{ $formId }}" isDestroy="true"
    formAction="{{ $url }}" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}"
    modalTitle="Transaksi Memorial">
    @if ($formMethod !== null)
        <x-slot name="formMethod">{{ $formMethod }}</x-slot>
    @endif
    <div class="row">

        <x-form-part col="sm-2" label="Transaksi" type="select" name="TipeTransHd">
            <option value="BMM">BMM</option>
        </x-form-part>
        <x-form-part col="sm-4" label="No Urut" type="text" name="PerkiraanHd" readonly></x-form-part>
        <x-form-part col="sm-2" label="-" labelclass="nolabel" type="text" name="NoUrut" readonly>
        </x-form-part>
        <x-form-part col="sm-4" label="No Bukti" type="text" name="NoBukti" readonly></x-form-part>
        <hr>
        <x-form-part col="sm-3" label="Tanggal" type="date" name="Tanggal"
            value="{{ date('Y-m-d', strtotime(($periode->TAHUN ?? date('Y')) . '-' . ($periode->BULAN ?? '01') . '-01')) }}">
        </x-form-part>
        <x-form-part col="sm-9" label="Keterangan" type="text" name="Note"></x-form-part>

    </div>
</x-base-modal>
