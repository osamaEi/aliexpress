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
                        <input type="text" name="phone_country" id="phone_country" class="form-control @error('phone_country') is-invalid @enderror" value="{{ old('phone_country', '971') }}" required>
                        @error('phone_country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="customer_phone" class="form-label">Phone Number *</label>
                        <input type="text" name="customer_phone" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" required>
                        @error('customer_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <label for="shipping_province" class="form-label">Province/State</label>
                        <input type="text" name="shipping_province" id="shipping_province" class="form-control @error('shipping_province') is-invalid @enderror" value="{{ old('shipping_province') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="shipping_country" class="form-label">Country Code *</label>
                        <select name="shipping_country" id="shipping_country" class="form-select @error('shipping_country') is-invalid @enderror" required>
                            <option value="AE" {{ old('shipping_country') == 'AE' ? 'selected' : '' }}>UAE (AE)</option>
                            <option value="SA" {{ old('shipping_country') == 'SA' ? 'selected' : '' }}>Saudi Arabia (SA)</option>
                        </select>
                        @error('shipping_country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="shipping_zip" class="form-label">Postal Code</label>
                        <input type="text" name="shipping_zip" id="shipping_zip" class="form-control @error('shipping_zip') is-invalid @enderror" value="{{ old('shipping_zip') }}">
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
@endsection
