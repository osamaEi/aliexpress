@props(['width' => '20', 'height' => '20', 'class' => '', 'currency' => 'AED'])

@php
    $currencyCode = strtoupper($currency);
@endphp

@if($currencyCode === 'SAR')
    <x-riyal-icon :width="$width" :height="$height" :class="$class" />
@elseif($currencyCode === 'AED')
    <x-dirham-icon :width="$width" :height="$height" :class="$class" />
@elseif($currencyCode === 'USD')
    <x-dollar-icon :width="$width" :height="$height" :class="$class" />
@else
    <span class="{{ $class }}">{{ $currencyCode }}</span>
@endif
