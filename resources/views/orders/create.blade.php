@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Create New Order</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('orders.store') }}" method="POST">
                @csrf

                <!-- Product Selection -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="product_id" class="form-label">Product *</label>
                        @if(isset($product))
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <div class="card">
                                <div class="card-body d-flex align-items-center">
                                    @if($product->images && count($product->images) > 0)
                                        <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="me-3" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                    @endif
                                    <div>
                                        <h6 class="mb-1">{{ $product->name }}</h6>
                                        <p class="mb-0 text-primary"><strong>{{ $product->currency }} {{ number_format($product->price, 2) }}</strong></p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                <option value="">Select a product</option>
                                @foreach(App\Models\Product::active()->get() as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} - {{ $prod->currency }} {{ $prod->price }}</option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                <!-- Product Variant Selection (if available) -->
                @if(isset($product) && !empty($product->aliexpress_data))
                    @php
                        $skus = [];
                        if (isset($product->aliexpress_data['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'])) {
                            $skus = $product->aliexpress_data['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'];
                        }
                    @endphp

                    @if(count($skus) > 1)
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="selected_sku_attr" class="form-label">Product Variant * <small class="text-muted">(Color/Size/Type)</small></label>
                                <select name="selected_sku_attr" id="selected_sku_attr" class="form-select @error('selected_sku_attr') is-invalid @enderror" required>
                                    <option value="">Select a variant</option>
                                    @foreach($skus as $sku)
                                        @php
                                            $skuAttr = $sku['sku_attr'] ?? $sku['id'];
                                            $stock = $sku['sku_available_stock'] ?? 0;
                                            $price = $sku['offer_sale_price'] ?? $sku['sku_price'] ?? 0;

                                            // Extract display name from sku_attr (e.g., "14:496#Green 116Plus" -> "Green 116Plus")
                                            $displayName = $skuAttr;
                                            if (strpos($skuAttr, '#') !== false) {
                                                $displayName = explode('#', $skuAttr)[1];
                                            }

                                            // Get image if available
                                            $skuImage = null;
                                            if (isset($sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'][0]['sku_image'])) {
                                                $skuImage = $sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'][0]['sku_image'];
                                            }
                                        @endphp
                                        <option value="{{ $skuAttr }}"
                                                data-stock="{{ $stock }}"
                                                data-price="{{ $price }}"
                                                data-image="{{ $skuImage }}"
                                                data-details="{{ json_encode($sku) }}"
                                                {{ $stock <= 0 ? 'disabled' : '' }}>
                                            {{ $displayName }}
                                            - ${{ number_format($price, 2) }}
                                            ({{ $stock > 0 ? $stock . ' in stock' : 'Out of stock' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('selected_sku_attr')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="variant-preview" class="mt-2" style="display: none;">
                                    <img id="variant-image" src="" alt="Variant" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Quantity -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label for="quantity" class="form-label">Quantity *</label>
                        <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Customer Information -->
                <h6 class="mb-3">Customer Information</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label">Full Name *</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" required>
                        @error('customer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="customer_email" class="form-label">Email</label>
                        <input type="email" name="customer_email" id="customer_email" class="form-control @error('customer_email') is-invalid @enderror" value="{{ old('customer_email') }}">
                        @error('customer_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <label for="phone_country" class="form-label">Phone Country Code *</label>
                        <select name="phone_country" id="phone_country" class="form-select @error('phone_country') is-invalid @enderror" required>
                            <option value="971" {{ old('phone_country', '971') == '971' ? 'selected' : '' }}>+971 (UAE)</option>
                            <option value="966" {{ old('phone_country') == '966' ? 'selected' : '' }}>+966 (Saudi Arabia)</option>
                            <option value="20" {{ old('phone_country') == '20' ? 'selected' : '' }}>+20 (Egypt)</option>
                        </select>
                        @error('phone_country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="customer_phone" class="form-label">Phone Number * <small class="text-muted">(without country code or leading zero)</small></label>
                        <input type="text" name="customer_phone" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" placeholder="e.g., 501234567" required>
                        @error('customer_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <strong>Format examples:</strong><br>
                            • UAE: 501234567 (9 digits starting with 5)<br>
                            • Saudi Arabia: 501234567 (9 digits starting with 5)<br>
                            • Egypt: 1001234567 (10 digits)
                        </div>
                    </div>
                </div>

                <!-- Shipping Information -->
                <h6 class="mb-3">Shipping Information</h6>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="shipping_address" class="form-label">Address *</label>
                        <input type="text" name="shipping_address" id="shipping_address" class="form-control @error('shipping_address') is-invalid @enderror" value="{{ old('shipping_address') }}" required>
                        @error('shipping_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="shipping_address2" class="form-label">Address Line 2</label>
                        <input type="text" name="shipping_address2" id="shipping_address2" class="form-control @error('shipping_address2') is-invalid @enderror" value="{{ old('shipping_address2') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="shipping_city" class="form-label">City *</label>
                        <input type="text" name="shipping_city" id="shipping_city" class="form-control @error('shipping_city') is-invalid @enderror" value="{{ old('shipping_city') }}" required>
                        @error('shipping_city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="shipping_province" class="form-label">Emirate/Province *</label>
                        <select name="shipping_province" id="shipping_province" class="form-select @error('shipping_province') is-invalid @enderror" required>
                            <option value="">Select Emirate/Province</option>
                            <!-- UAE Emirates -->
                            <optgroup label="UAE Emirates" id="uae-provinces">
                                <option value="Abu Dhabi" {{ old('shipping_province') == 'Abu Dhabi' ? 'selected' : '' }}>Abu Dhabi</option>
                                <option value="Dubai" {{ old('shipping_province') == 'Dubai' ? 'selected' : '' }}>Dubai</option>
                                <option value="Sharjah" {{ old('shipping_province') == 'Sharjah' ? 'selected' : '' }}>Sharjah</option>
                                <option value="Ajman" {{ old('shipping_province') == 'Ajman' ? 'selected' : '' }}>Ajman</option>
                                <option value="Umm Al Quwain" {{ old('shipping_province') == 'Umm Al Quwain' ? 'selected' : '' }}>Umm Al Quwain</option>
                                <option value="Ras Al Khaimah" {{ old('shipping_province') == 'Ras Al Khaimah' ? 'selected' : '' }}>Ras Al Khaimah</option>
                                <option value="Fujairah" {{ old('shipping_province') == 'Fujairah' ? 'selected' : '' }}>Fujairah</option>
                            </optgroup>
                            <!-- Saudi Arabia Provinces -->
                            <optgroup label="Saudi Arabia Provinces" id="saudi-provinces" style="display:none;">
                                <option value="Riyadh" {{ old('shipping_province') == 'Riyadh' ? 'selected' : '' }}>Riyadh</option>
                                <option value="Makkah" {{ old('shipping_province') == 'Makkah' ? 'selected' : '' }}>Makkah</option>
                                <option value="Madinah" {{ old('shipping_province') == 'Madinah' ? 'selected' : '' }}>Madinah</option>
                                <option value="Eastern Province" {{ old('shipping_province') == 'Eastern Province' ? 'selected' : '' }}>Eastern Province</option>
                                <option value="Asir" {{ old('shipping_province') == 'Asir' ? 'selected' : '' }}>Asir</option>
                                <option value="Tabuk" {{ old('shipping_province') == 'Tabuk' ? 'selected' : '' }}>Tabuk</option>
                                <option value="Qassim" {{ old('shipping_province') == 'Qassim' ? 'selected' : '' }}>Qassim</option>
                                <option value="Ha\'il" {{ old('shipping_province') == 'Ha\'il' ? 'selected' : '' }}>Ha'il</option>
                                <option value="Northern Borders" {{ old('shipping_province') == 'Northern Borders' ? 'selected' : '' }}>Northern Borders</option>
                                <option value="Jazan" {{ old('shipping_province') == 'Jazan' ? 'selected' : '' }}>Jazan</option>
                                <option value="Najran" {{ old('shipping_province') == 'Najran' ? 'selected' : '' }}>Najran</option>
                                <option value="Al Bahah" {{ old('shipping_province') == 'Al Bahah' ? 'selected' : '' }}>Al Bahah</option>
                                <option value="Al Jawf" {{ old('shipping_province') == 'Al Jawf' ? 'selected' : '' }}>Al Jawf</option>
                            </optgroup>
                        </select>
                        @error('shipping_province')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="shipping_country" class="form-label">Country *</label>
                        <select name="shipping_country" id="shipping_country" class="form-select @error('shipping_country') is-invalid @enderror" required>
                            <option value="AE" {{ old('shipping_country', 'AE') == 'AE' ? 'selected' : '' }}>UAE (AE)</option>
                            <option value="SA" {{ old('shipping_country') == 'SA' ? 'selected' : '' }}>Saudi Arabia (SA)</option>
                        </select>
                        @error('shipping_country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="shipping_zip" class="form-label">Postal Code <span class="text-danger">*</span></label>
                        <input type="text" name="shipping_zip" id="shipping_zip" class="form-control @error('shipping_zip') is-invalid @enderror" value="{{ old('shipping_zip') }}" placeholder="e.g., 00000" required>
                        @error('shipping_zip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">For UAE, you can use "00000" if no postal code</small>
                    </div>
                </div>

                <!-- Customer Notes -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="customer_notes" class="form-label">Customer Notes</label>
                        <textarea name="customer_notes" id="customer_notes" class="form-control @error('customer_notes') is-invalid @enderror" rows="3">{{ old('customer_notes') }}</textarea>
                        @error('customer_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Back to Orders
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('customer_phone');
    const phoneCountrySelect = document.getElementById('phone_country');
    const shippingCountrySelect = document.getElementById('shipping_country');
    const shippingProvinceSelect = document.getElementById('shipping_province');
    const uaeProvinces = document.getElementById('uae-provinces');
    const saudiProvinces = document.getElementById('saudi-provinces');

    // Variant selection handling
    const variantSelect = document.getElementById('selected_sku_attr');
    const variantPreview = document.getElementById('variant-preview');
    const variantImage = document.getElementById('variant-image');

    if (variantSelect) {
        variantSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const imageUrl = selectedOption.getAttribute('data-image');

            if (imageUrl && imageUrl !== 'null') {
                variantImage.src = imageUrl;
                variantPreview.style.display = 'block';
            } else {
                variantPreview.style.display = 'none';
            }
        });
    }

    // Function to update province options based on country
    function updateProvinceOptions(country) {
        if (country === 'AE') {
            uaeProvinces.style.display = '';
            saudiProvinces.style.display = 'none';
            // Clear selection if it was a Saudi province
            const currentValue = shippingProvinceSelect.value;
            const validUAEValues = ['Abu Dhabi', 'Dubai', 'Sharjah', 'Ajman', 'Umm Al Quwain', 'Ras Al Khaimah', 'Fujairah'];
            if (!validUAEValues.includes(currentValue)) {
                shippingProvinceSelect.value = '';
            }
        } else if (country === 'SA') {
            uaeProvinces.style.display = 'none';
            saudiProvinces.style.display = '';
            // Clear selection if it was a UAE emirate
            const currentValue = shippingProvinceSelect.value;
            const validSAValues = ['Riyadh', 'Makkah', 'Madinah', 'Eastern Province', 'Asir', 'Tabuk', 'Qassim', "Ha'il", 'Northern Borders', 'Jazan', 'Najran', 'Al Bahah', 'Al Jawf'];
            if (!validSAValues.includes(currentValue)) {
                shippingProvinceSelect.value = '';
            }
        }
    }

    // Initialize province options on page load
    updateProvinceOptions(shippingCountrySelect.value);

    // Auto-fill postal code for UAE if empty
    const zipInput = document.getElementById('shipping_zip');
    if (shippingCountrySelect.value === 'AE' && !zipInput.value) {
        zipInput.value = '00000';
    }

    // Auto-sync phone country with shipping country and update provinces
    shippingCountrySelect.addEventListener('change', function() {
        const countryMap = {
            'AE': '971',  // UAE
            'SA': '966',  // Saudi Arabia
            'EG': '20'    // Egypt
        };

        const phoneCode = countryMap[this.value];
        if (phoneCode && phoneCountrySelect) {
            phoneCountrySelect.value = phoneCode;
        }

        // Update province options
        updateProvinceOptions(this.value);

        // Auto-fill default postal code for UAE
        if (this.value === 'AE' && !zipInput.value) {
            zipInput.value = '00000';
        }
    });

    // Clean phone number on input
    phoneInput.addEventListener('blur', function() {
        let phone = this.value.trim();

        // Remove any spaces or dashes
        phone = phone.replace(/[\s\-]/g, '');

        // Remove leading zeros
        phone = phone.replace(/^0+/, '');

        // Remove country code if accidentally included
        const countryCode = phoneCountrySelect.value;
        if (phone.startsWith(countryCode)) {
            phone = phone.substring(countryCode.length);
        }

        // Remove plus sign if present
        phone = phone.replace(/^\+/, '');

        this.value = phone;
    });
});
</script>
@endpush
@endsection
