@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                {{ __('messages.edit_product') }}
                @if($product->isAliexpressProduct())
                    <span class="badge bg-info ms-2">
                        <i class="ri-shopping-cart-line"></i> Dropship Product
                    </span>
                @endif
            </h5>
            <div>
                @if($product->isAliexpressProduct())
                    <form action="{{ route('products.sync', $product) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm me-2">
                            <i class="ri-refresh-line me-1"></i> {{ __('messages.sync') }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> {{ __('messages.back') }}
                </a>
            </div>
        </div>

        <div class="card-body">
            @if($product->isAliexpressProduct())
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    <strong>{{ app()->getLocale() == 'ar' ? 'منتج من الصين 🇨🇳' : '🇨🇳 Product from China' }}</strong>
                    <a href="{{ $product->aliexpress_url }}" target="_blank" class="ms-2">{{ app()->getLocale() == 'ar' ? 'عرض على الموقع' : 'View on Store' }}</a>
                    @if($product->last_synced_at)
                        <br><small>{{ __('messages.last_synced') }}: {{ $product->last_synced_at->diffForHumans() }}</small>
                    @endif
                </div>
            @endif

        </div>
    </div>
</div>

    <script>
    // Only run price update JS when price-related inputs exist (keeps page safe when fields removed)
    document.addEventListener('DOMContentLoaded', function() {
        const originalPriceInput = document.getElementById('original_price');
        if (!originalPriceInput) return; // no price inputs on this page (description-only edit)

        const markupAmountInput = document.getElementById('markup_amount');
        const markupPercentageInput = document.getElementById('markup_percentage');
        const finalPriceInput = document.getElementById('price');
        const currencySelect = document.getElementById('currency');
        const currencySymbol = document.getElementById('currency-symbol');

        function calculateFinalPrice() {
            const originalPrice = parseFloat(originalPriceInput.value) || 0;
            const markupAmount = parseFloat(markupAmountInput.value) || 0;
            const markupPercentage = parseFloat(markupPercentageInput.value) || 0;

            const percentageAmount = originalPrice * (markupPercentage / 100);
            const finalPrice = originalPrice + markupAmount + percentageAmount;

            if (finalPriceInput) finalPriceInput.value = finalPrice.toFixed(2);
        }

        function updateCurrencySymbol() {
            if (currencySymbol && currencySelect) currencySymbol.textContent = currencySelect.value;
        }

        markupAmountInput?.addEventListener('input', calculateFinalPrice);
        markupPercentageInput?.addEventListener('input', calculateFinalPrice);
        originalPriceInput?.addEventListener('input', calculateFinalPrice);
        currencySelect?.addEventListener('change', updateCurrencySymbol);

        calculateFinalPrice();
    });
    </script>
@endsection