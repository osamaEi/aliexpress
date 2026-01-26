@props(['currency' => 'AED', 'width' => '20', 'height' => '20', 'class' => '', 'showIcon' => true, 'showText' => true])

@php
    $currencyCode = strtoupper($currency);
    $symbols = [
        'AED' => '',
        'SAR' => '',
        'USD' => '',
    ];
    $symbol = $symbols[$currencyCode] ?? $currency;
@endphp

<span class="currency-symbol-wrapper d-inline-flex align-items-center gap-1 {{ $class }}">
    @if($showIcon)
        @if($currencyCode === 'AED')
            <x-dirham-icon :width="$width" :height="$height" class="currency-icon" />
        @elseif($currencyCode === 'SAR')
            <x-riyal-icon :width="$width" :height="$height" class="currency-icon" />
        @elseif($currencyCode === 'USD')
            <x-dollar-icon :width="$width" :height="$height" class="currency-icon" />
        @endif
    @endif

    @if($showText)
        <span class="currency-text">{{ $symbol }}</span>
    @endif
</span>
