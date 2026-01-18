@props(['width' => '24', 'height' => '24', 'class' => ''])

@php
    $sessionCurrency = session('currency_code', 'USD');
@endphp

@if($sessionCurrency === 'SAR')
    <x-riyal-icon :width="$width" :height="$height" :class="$class" />
@elseif($sessionCurrency === 'AED')
    <x-dirham-icon :width="$width" :height="$height" :class="$class" />
@elseif($sessionCurrency === 'USD')
    <x-dollar-icon :width="$width" :height="$height" :class="$class" />
@elseif($sessionCurrency === 'EUR')
    <span class="{{ $class }}" style="font-size: {{ $height }}px;">&euro;</span>
@elseif($sessionCurrency === 'GBP')
    <span class="{{ $class }}" style="font-size: {{ $height }}px;">&pound;</span>
@else
    <span class="{{ $class }}" style="font-size: {{ $height }}px;">{{ $sessionCurrency }}</span>
@endif
