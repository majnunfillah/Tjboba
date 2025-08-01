<x-base-modal modalId="{{ $modalId }}" formId="{{ $formId }}" isDestroy="true"
    formAction="{{ $url }}" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}"
    modalTitle="Transaksi Kas Bank">
    @if ($formMethod !== null)
        <x-slot name="formMethod">{{ $formMethod }}</x-slot>
    @endif
    <div class="row">

        <x-form-part col="sm-2" label="Transaksi" type="select" name="TipeTransHd">
            <option value="BKK">BKK</option>
            <option value="BKM">BKM</option>
            <option value="BBK">BBK</option>
            <option value="BBM">BBM</option>
        </x-form-part>
        <x-form-part col="sm-4" label="No Urut" type="select2" name="PerkiraanHd" readonly></x-form-part>
        <x-form-part col="sm-2" label="-" labelclass="nolabel" type="text" name="NoUrut" readonly>
        </x-form-part>
        <x-form-part col="sm-4" label="No Bukti" type="text" name="NoBukti" readonly></x-form-part>
        <hr>
        <x-form-part col="sm-3" label="Tanggal" type="date" name="Tanggal"
            value="{{ date('Y-m-d', strtotime(($periode->TAHUN ?? date('Y')) . '-' . ($periode->BULAN ?? '01') . '-01')) }}">
        </x-form-part>
        <x-form-part type="hidden" name="Lawan"></x-form-part>
        <x-form-part col="sm-4" label="Kas" labelclass="label-lawan" type="text" name="Lawan_val" readonly>
        </x-form-part>
        <x-form-part col="sm-5" label="Kepada" labelclass="label-note" type="text" name="Note"></x-form-part>

    </div>
</x-base-modal>
