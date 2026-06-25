@extends('dashboard')

@php $ar = app()->getLocale() == 'ar'; @endphp

@section('content')
<div class="col-12" dir="{{ $ar ? 'rtl' : 'ltr' }}">

    <div class="mb-4">
        <h4 class="mb-1 fw-bold">{{ $ar ? 'منتجات المتاجر' : 'Store Products' }}</h4>
        <p class="text-muted mb-0">{{ $ar ? 'منتجات المتاجر المتاحة للترويج (عرض فقط)' : 'Store products available to promote (view only)' }}</p>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('marketer.products') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="{{ $ar ? 'ابحث عن منتج...' : 'Search products...' }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary"><i class="ri-search-line me-1"></i>{{ $ar ? 'بحث' : 'Search' }}</button>
                    <a href="{{ route('marketer.products') }}" class="btn btn-outline-secondary">{{ $ar ? 'إعادة' : 'Reset' }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @forelse($products as $product)
            @php
                $name = $ar && $product->name_ar ? $product->name_ar : $product->name;
                $img = $product->photo ? asset('storage/'.$product->photo) : (($product->images && count($product->images)) ? $product->images[0] : null);
            @endphp
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card h-100 product-card">
                    @if($img)
                        <img src="{{ $img }}" class="card-img-top" style="height:160px;object-fit:cover;" alt="{{ $name }}">
                    @else
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:160px;"><i class="ri-image-line text-muted fs-2"></i></div>
                    @endif
                    <div class="card-body">
                        <h6 class="mb-1 text-truncate">{{ $name }}</h6>
                        <div class="text-primary fw-bold">{{ number_format($product->price, 2) }} <small class="text-muted">{{ $product->currency ?: 'AED' }}</small></div>
                        <small class="text-muted">{{ optional($product->category)->name }}</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center py-5 text-muted">
                    <i class="ri-shopping-bag-3-line d-block mb-2" style="font-size:3rem;"></i>
                    {{ $ar ? 'لا توجد منتجات' : 'No products found' }}
                </div></div>
            </div>
        @endforelse
    </div>

    @if($products->hasPages())
        <div class="mt-4">{{ $products->links() }}</div>
    @endif
</div>

<style>
.product-card { border:1px solid #f0eae6;transition:all .2s; }
.product-card:hover { box-shadow:0 4px 14px rgba(86,28,4,.1);transform:translateY(-2px); }
</style>
@endsection
