<!-- SPK Produksi Modal Insert Component -->
<x-base-modal modalId="{{ $modalId }}" formId="{{ $formId }}" isDestroy="true"
    formAction="{{ $url }}" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}"
    modalTitle="Transaksi SPK">
    @if ($formMethod !== null)
        <x-slot name="formMethod">{{ $formMethod }}</x-slot>
    @endif
    <div class="row">
        <!-- HEADER SECTION -->
        <x-form-part col="sm-4" label="No Urut" type="select2" name="PerkiraanHd" readonly></x-form-part>
        <x-form-part col="sm-2" label="-" labelclass="nolabel" type="text" name="NoUrut" readonly></x-form-part>
        <x-form-part col="sm-6" label="No Bukti" type="text" name="NoBukti" readonly></x-form-part>
        <hr>
        <x-form-part col="sm-3" label="Tanggal" type="date" name="Tanggal"
            value="{{ date('Y-m-d', strtotime(($periode->TAHUN ?? date('Y')) . '-' . ($periode->BULAN ?? '01') . '-01')) }}">
        </x-form-part>
        <x-form-part col="sm-3" label="No SO" type="select2" name="NoSO"></x-form-part>
        <x-form-part col="sm-2" label="Urut SO" type="number" name="UrutSO" min="1"></x-form-part>
        <x-form-part col="sm-4" label="Keterangan" type="text" name="Note"></x-form-part>

        <!-- BARANG JADI SECTION -->
        <x-form-part col="sm-4" label="Kode Barang Jadi" type="select2" name="KodeBrg"></x-form-part>
        <x-form-part col="sm-6" label="Nama Barang Jadi" type="text" name="NamaBrg" readonly></x-form-part>
        <x-form-part col="sm-2" label="Satuan" type="text" name="SATBJ" readonly></x-form-part>
        <hr>
        <x-form-part col="sm-3" label="Qty" type="mask-money" name="Qnt"></x-form-part>
        <x-form-part col="sm-3" label="No Satuan" type="select" name="NoSat">
            <option value="1">Satuan 1</option>
            <option value="2">Satuan 2</option>
            <option value="3">Satuan 3</option>
        </x-form-part>
        <x-form-part col="sm-3" label="Tgl Expired" type="date" name="TglExpired"></x-form-part>
        <x-form-part col="sm-3" label="Tgl Kirim" type="date" name="TglKirim"></x-form-part>

        <!-- OTORISASI SECTION -->
        <x-form-part col="sm-3" label="Max Otorisasi Level" type="number" name="MaxOL" min="1" max="5" value="1"></x-form-part>
        <x-form-part col="sm-3" label="Is Otorisasi 1" type="checkbox" name="IsOtorisasi1" value="1"></x-form-part>
        <x-form-part col="sm-3" label="Oto User 1" type="text" name="OtoUser1" readonly></x-form-part>
        <x-form-part col="sm-3" label="Tgl Oto 1" type="datetime-local" name="TglOto1" readonly></x-form-part>
    </div>
</x-base-modal>