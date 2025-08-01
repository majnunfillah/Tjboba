<x-base-modal formAction="{{ $url }}" formId="{{ $formId }}" modalId="{{ $modalId }}" isDestroy="true" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" modalTitle="Data Group">
  @if ($formMethod !== null)
      @method($formMethod)
  @endif
  <div class="row">
      <x-form-part col="sm-12" label="Kode Jenis" type="text" name="KODEGRP" placeholder="Masukkan Kode Jenis" value="{{ $res->KODEGRP }}" required  />
      <x-form-part col="sm-12" label="Nama Jenis" type="text" name="NAMA" placeholder="Masukkan Nama Jenis" value="{{ $res->NAMA }}" required />
  </div>
</x-base-modal>
