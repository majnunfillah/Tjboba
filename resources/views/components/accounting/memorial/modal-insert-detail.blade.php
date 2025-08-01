<x-base-modal modalId="{{ $modalId }}" formId="{{ $formId }}" isDestroy="true"
    formAction="{{ $url }}" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}"
    modalTitle="Detail Transaksi Memorial">
    @if ($formMethod !== null)
        <x-slot name="formMethod">{{ $formMethod }}</x-slot>
    @endif
    <div class="row">
        <x-form-part col="sm-4" label="Valas" type="select2" name="Valas">
        </x-form-part>
        <x-form-part type="hidden" name="Kurs" value="0" />
        <x-form-part col="sm-2" label="Kurs" type="mask-money" name="Kurs_val" readonly></x-form-part>
        <x-form-part type="hidden" name="Debet" value="0" />
        <x-form-part col="sm-6" label="Jumlah" type="mask-money" name="Debet_val"></x-form-part>
        
        {{-- <x-form-part col="sm-4" label="Tanggal" type="date" name="Tanggal" data-tahun="{{ $periode->TAHUN }}"
            data-bulan="{{ $periode->BULAN }}"
            value="{{ date('Y-m-d', strtotime(($periode->TAHUN ?? date('Y')) . '-' . ($periode->BULAN ?? '01') . '-01')) }}">
        </x-form-part> --}}
        
        <x-form-part col="sm-4" label="Keterangan" type="text" name="Keterangan">
        </x-form-part>
        <x-form-part col="sm-6" label="No SPK" type="text" name="KodeBag"></x-form-part>
        
        <!-- Perkiraan sebagai text field -->
        <x-form-part col="sm-6" label="Perkiraan" type="select2" name="Perkiraan"></x-form-part>
        
        <!-- Lawan sebagai text field (bukan hidden) -->
        <x-form-part col="sm-6" label="Lawan" type="select2" name="Lawan"></x-form-part>

    </div>
</x-base-modal>