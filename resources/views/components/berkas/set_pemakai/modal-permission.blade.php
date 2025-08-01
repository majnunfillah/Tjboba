<x-base-modal formAction="{{ $url }}" formId="{{ $formId }}" modalId="{{ $modalId }}" isDestroy="true" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" modalTitle="Set Permission">
    @if ($formMethod !== null)
        @method($formMethod)
    @endif
    <div class="row">
        @if ($formId == 'formAccessCOA')
            <div class="col-12">
                <div class="form-group">
                    <label>AKSES COA</label>
                    <select name="Perkiraan[]" class="duallistbox" multiple="multiple" style="height: 50vh">
                        @foreach ($res as $item)
                            <option value="{{ $item->Perkiraan }}" {{ $item->UserId != null ? 'selected' : '' }}>{{ $item->Keterangan }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- /.form-group -->
            </div>
            <!-- /.col -->
        @endif
    </div>
</x-base-modal>
