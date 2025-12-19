@php
    $currencies = \App\Models\Currency::active();
    $currentCurrencyCode = session('currency_code', 'USD');
    $currentCurrency = $currencies->firstWhere('code', $currentCurrencyCode) ?? \App\Models\Currency::default();
@endphp

<div class="dropdown currency-selector {{ $class ?? '' }}">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="currency-symbol">{{ $currentCurrency->symbol }}</span>
        <span class="currency-code">{{ $currentCurrency->code }}</span>
    </button>
    <ul class="dropdown-menu">
        @foreach($currencies as $currency)
        <li>
            <a class="dropdown-item {{ $currentCurrencyCode == $currency->code ? 'active' : '' }}"
               href="{{ route('currency.switch', $currency->code) }}">
                <span class="currency-symbol me-2">{{ $currency->symbol }}</span>
                <span class="currency-name">{{ $currency->name }}</span>
                <small class="text-muted">({{ $currency->code }})</small>
            </a>
        </li>
        @endforeach
    </ul>
</div>

<style>
.currency-selector .currency-symbol {
    font-weight: 600;
    margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 0.25rem;
}

.currency-selector .currency-code {
    font-size: 0.875rem;
}

.currency-selector .dropdown-item.active {
    background-color: #f0f9ff;
    color: #0369a1;
}
</style>
