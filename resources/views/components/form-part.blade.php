@if ($attributes->has('col'))
    <div class="col-{{ $attributes->get('col') }}">
@endif

@if (!($attributes->get('type') === 'hidden'))
    <div class="form-group {{ $attributes->get('groupclass') }}" style="{{ $attributes->get('groupStyle') }}">
@endif

@if ($attributes->has('label'))
    <label for="{{ $attributes->get('id') ?? $attributes->get('name') }}" class="{{ $attributes->get('labelclass') ?? '' }}" style="{{ $attributes->get('labelstyle') ?? '' }}">{{ $attributes->get('label') }}</label>
@endif

@if ($attributes->get('type') === 'text' ||
    $attributes->get('type') === 'email' ||
    $attributes->get('type') === 'number' ||
    $attributes->get('type') === 'password' ||
    $attributes->get('type') === 'hidden' ||
    $attributes->get('type') === 'number-text' ||
    $attributes->get('type') === 'date' ||
    $attributes->get('type') === 'datetime-local')
    <input type="{{ $type }}" {{ $attributes->merge(['class' => 'form-control']) }}>
@elseif($attributes->get('type') === 'dropify')
    <input type="file" {{ $attributes->merge(['class' => 'dropify']) }}>
@elseif($attributes->get('type') === 'mask-money')
    <input type="text" {{ $attributes->merge(['class' => 'mask-money form-control']) }}>
@elseif($attributes->get('type') === 'select2')
    <select {{ $attributes->merge(['class' => 'form-control select2']) }}>
        {{ $slot }}
    </select>
@elseif($attributes->get('type') === 'select')
    <select {{ $attributes->merge(['class' => 'form-control']) }}>
        {{ $slot }}
    </select>
@elseif($attributes->get('type') === 'textarea')
    <textarea {{ $attributes->merge(['class' => 'form-control']) }}>{{ $attributes->get('value') ?? $slot }}</textarea>
@endif

@if (!($attributes->get('type') === 'hidden'))
    </div>
@endif

@if ($attributes->has('col'))
    </div>
@endif
