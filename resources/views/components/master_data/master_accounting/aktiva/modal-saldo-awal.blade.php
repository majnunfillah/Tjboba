<x-base-modal formAction="{{ $url }}" formId="{{ $formId }}" modalId="{{ $modalId }}" isDestroy="true" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" modalTitle="Saldo Awal">
    <div class="row">
        <x-form-part col="sm-6" label="Valas" type="text" name="valas" value="{{ $res->valas ?? 'IDR' }}" />
        <x-form-part col="sm-6" label="Kurs" type="text" name="Kurs_val" class="mask-money" value="{{ $res->Kurs }}" />
        <x-form-part col="sm-6" label="Nilai Awal" type="text" name="Awal_val" class="mask-money" value="{{ $res->Awal }}" />
        <x-form-part col="sm-6" label="Nilai Penyusutan" type="text" name="AwalSusut_val" class="mask-money" value="{{ $res->AwalSusut }}" />
        <input type="hidden" name="Kurs" />
        <input type="hidden" name="Awal" />
        <input type="hidden" name="AwalSusut" />
    </div>
</x-base-modal>
