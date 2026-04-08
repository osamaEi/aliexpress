@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- Subscription plan usage banner --}}
    @php $isAr = app()->getLocale() == 'ar'; @endphp
    @if($subscriptionPlan)
        @php
            $pct = $maxProducts ? min(100, round($productCount / $maxProducts * 100)) : 0;
            $barColor = $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
            $atLimit = $maxProducts && $productCount >= $maxProducts;
        @endphp
        <div class="alert alert-{{ $atLimit ? 'danger' : 'info' }} d-flex align-items-center justify-content-between gap-3 mb-3" style="border-radius:12px;">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="ri-vip-crown-line"></i>
                    <strong>{{ $isAr ? ($subscriptionPlan->name_ar ?: $subscriptionPlan->name) : $subscriptionPlan->name }}</strong>
                    @if(auth()->user()->activeSubscription?->payment_method === 'trial')
                        <span class="badge bg-warning text-dark">{{ $isAr ? 'تجريبي' : 'Trial' }}</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span style="font-size:13px;">
                        {{ $isAr ? 'المنتجات المستخدمة:' : 'Products used:' }}
                        <strong>{{ $productCount }}</strong> /
                        {{ $maxProducts ?? ($isAr ? 'غير محدود' : 'Unlimited') }}
                    </span>
                    @if($maxProducts)
                    <div class="flex-grow-1" style="max-width:200px;">
                        <div class="progress" style="height:8px;border-radius:4px;">
                            <div class="progress-bar bg-{{ $barColor }}" style="width:{{ $pct }}%;"></div>
                        </div>
                    </div>
                    @endif
                </div>
                @if($atLimit)
                    <p class="mb-0 mt-1 text-danger" style="font-size:13px;">
                        <i class="ri-error-warning-line me-1"></i>
                        {{ $isAr ? 'وصلت إلى الحد الأقصى للمنتجات. يرجى الترقية إلى باقة أعلى.' : 'You have reached your product limit. Please upgrade your plan.' }}
                    </p>
                @endif
            </div>
            <a href="{{ route('subscriptions.index') }}" class="btn btn-sm btn-outline-{{ $atLimit ? 'danger' : 'primary' }} text-nowrap">
                <i class="ri-arrow-up-circle-line me-1"></i>
                {{ $isAr ? 'ترقية الباقة' : 'Upgrade Plan' }}
            </a>
        </div>
    @elseif(!$subscriptionPlan)
        <div class="alert alert-warning mb-3" style="border-radius:12px;">
            <i class="ri-information-line me-1"></i>
            {{ $isAr ? 'لا توجد باقة اشتراك نشطة. المنتجات غير محدودة حالياً.' : 'No subscription plan linked. Products are currently unlimited.' }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $isAr ? 'منتجاتي' : 'My Products' }}</h5>
            @if(!($maxProducts && $productCount >= $maxProducts))
            <a href="{{ route('distributor.products.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>
                {{ $isAr ? 'إضافة منتج جديد' : 'Create New Product' }}
            </a>
            @else
            <a href="{{ route('subscriptions.index') }}" class="btn btn-warning">
                <i class="ri-arrow-up-circle-line me-1"></i>
                {{ $isAr ? 'ترقية الباقة لإضافة منتجات' : 'Upgrade Plan to Add Products' }}
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('messages.product') }}</th>
                            <th>{{ __('messages.category') }}</th>
                            <th>{{ __('messages.price') }}</th>
                            <th>{{ __('messages.stock') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product->photo)
                                        <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                    @elseif($product->images && count($product->images) > 0)
                                        <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="ri-image-line text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->name_ar)
                                            <br><small class="text-muted">{{ $product->name_ar }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>{{ $product->currency }} {{ number_format($product->price, 2) }}</td>
                            <td>
                                @if($product->track_inventory)
                                    <span class="badge bg-{{ $product->stock_quantity > 10 ? 'success' : ($product->stock_quantity > 0 ? 'warning' : 'danger') }}">
                                        {{ $product->stock_quantity }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.unlimited') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                    {{ $product->is_active ? __('messages.active') : __('messages.inactive') }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('distributor.products.show', $product->id) }}" class="btn btn-sm btn-info" title="{{ app()->getLocale() == 'ar' ? 'عرض' : 'View' }}">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('distributor.products.edit', $product->id) }}" class="btn btn-sm btn-primary" title="{{ app()->getLocale() == 'ar' ? 'تعديل' : 'Edit' }}">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                {{ app()->getLocale() == 'ar' ? 'لا توجد منتجات بعد' : 'No products yet' }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
