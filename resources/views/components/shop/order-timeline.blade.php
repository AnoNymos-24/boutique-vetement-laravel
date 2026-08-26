@props(['order'])

@php
    $steps = [
        ['key' => 'en_attente', 'label' => 'En attente', 'icon' => 'cash', 'date' => $order->created_at],
        ['key' => 'confirmee', 'label' => 'Confirmée', 'icon' => 'check', 'date' => $order->confirmed_at],
        ['key' => 'preparee', 'label' => 'Préparée', 'icon' => 'box', 'date' => $order->prepared_at],
        ['key' => 'expediee', 'label' => 'Expédiée', 'icon' => 'truck', 'date' => $order->shipped_at],
        ['key' => 'livree', 'label' => 'Livrée', 'icon' => 'package', 'date' => $order->delivered_at],
    ];
    $currentIndex = array_search($order->status, array_column($steps, 'key'), true);
@endphp

@if ($order->status === 'annulee')
    <div class="shop-status-banner shop-status-banner--error">
        Commande annulée le {{ $order->cancelled_at?->format('d/m/Y à H:i') }}.
    </div>
@else
    <div class="shop-timeline">
        <div class="shop-timeline__track">
            <div class="shop-timeline__track-fill" style="width: {{ $currentIndex > 0 ? ($currentIndex / (count($steps) - 1)) * 100 : 0 }}%"></div>
        </div>
        @foreach ($steps as $index => $step)
            <div class="shop-timeline__step {{ $index < $currentIndex ? 'is-done' : ($index === $currentIndex ? 'is-current' : '') }}">
                <div class="shop-timeline__dot">
                    @if ($index < $currentIndex || ($index === $currentIndex && $step['key'] === 'livree'))
                        <x-icon name="check" />
                    @else
                        <x-icon :name="$step['icon']" />
                    @endif
                </div>
                <div class="shop-timeline__label">{{ $step['label'] }}</div>
                @if ($step['date'])
                    <div class="shop-timeline__date">{{ $step['date']->format('d/m H:i') }}</div>
                @endif
            </div>
        @endforeach
    </div>
@endif
