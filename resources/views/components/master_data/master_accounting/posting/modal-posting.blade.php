<x-base-modal modalId="{{ $modalId }}" isDestroy="true" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" 
  modalTitle="{{ array_key_exists('modalTitle', $res) ? $res['modalTitle'] : '' }}">
  @include('components.master_data.master_accounting.posting.modal-' . $res['component'], ['res' => $res, 'formId' => $formId])
</x-base-modal>
