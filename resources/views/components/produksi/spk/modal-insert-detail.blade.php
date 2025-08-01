<x-base-modal modalId="{{ $modalId }}" formId="{{ $formId }}" isDestroy="true"
    formAction="{{ $url }}" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}"
    modalTitle="Detail SPK (Bahan)">
    @if ($formMethod !== null)
        <x-slot name="formMethod">{{ $formMethod }}</x-slot>
    @endif
    <div class="row">
        <!-- KODE BARANG (BAHAN) -->
        <x-form-part col="sm-6" label="Kode Barang" type="select2" name="KodeBrg"></x-form-part>
        <x-form-part col="sm-6" label="Nama Barang" type="text" name="NamaBrg" readonly></x-form-part>

        <!-- QUANTITY & SATUAN -->
        <x-form-part col="sm-3" label="Quantity" type="mask-money" name="Qnt"></x-form-part>
        <x-form-part col="sm-2" label="No Sat" type="select" name="NoSat">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
        </x-form-part>
        <x-form-part col="sm-3" label="Satuan" type="text" name="Satuan" readonly></x-form-part>
        <x-form-part col="sm-4" label="Isi" type="mask-money" name="Isi" readonly></x-form-part>

        <!-- BOM INFORMATION -->
        <x-form-part col="sm-4" label="Kode BOM Det" type="text" name="KodeBOMDet"></x-form-part>
        <x-form-part col="sm-4" label="Level BOM" type="number" name="IntLevelBOM" min="0"></x-form-part>
        <x-form-part col="sm-4" label="String Level" type="text" name="StrLevelBOM"></x-form-part>

        <!-- HIDDEN FIELDS FOR SPK HEADER INFO -->
        <x-form-part type="hidden" name="NoBukti" />
        <x-form-part type="hidden" name="Urut" />
    </div>
</x-base-modal>