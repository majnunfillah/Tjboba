@props(['modalId' => '', 'isFile' => false, 'modalWidth' => 'lg', 'formId' => null, 'isStatic' => false, 'isDestroy' => false, 'modalTitle' => '', 'formAction' => null])
<div class="modal fade" id={{ $modalId }} data-backdrop="static" data-keyboard="false"
    {{ isset($tabindex) ? '' : 'tabindex="-1"' }} data-focus="false" aria-labelledby="{{ $modalId }}Label"
    aria-hidden="true" {{ $isDestroy ? 'data-destroy=true' : '' }}>
    <div class="modal-dialog modal-{{ $modalWidth }}" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">{{ $modalTitle ?? '' }}</h5>
                <button class="btn-close" type="button" data-dismiss="modal" aria-label="Close">&times;</button>
                <div class="header-draggable" style="position: absolute; top:0; right:50px; background-color: transparent; width:100px;height:63px">
                </div>
            </div>
            @if ($formId !== null)
                <form action="{{ $formAction !== null ? $formAction : '' }}" method="POST"
                    {{ $isFile ? 'enctype="multipart/form-data"' : '' }} id="{{ $formId ?? '' }}">
                    @csrf
                    @isset($formMethod)
                        @method($formMethod)
                    @endisset
            @endif
            <div class="modal-body">
                {{ $slot }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm text-white" type="button"
                    data-dismiss="modal">{{ $textCancel ?? 'Tutup1' }}</button>
                @if ($formId !== null)
                    {!! $buttonSubmit ?? '<button class="btn btn-primary btn-sm" type="submit">Simpan</button>' !!}
                @endif
            </div>
            @if ($formId !== null)
                </form>
            @endif
        </div>
    </div>
</div>
