@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'status-banner']) }}>
        {{ $status }}
    </div>
@endif
