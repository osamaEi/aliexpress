@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ app()->getLocale() == 'ar' ? 'منتجاتي' : 'My Products' }}</h5>
            <a href="{{ route('distributor.products.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>
                {{ app()->getLocale() == 'ar' ? 'إضافة منتج جديد' : 'Create New Product' }}
            </a>
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
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-info" title="{{ app()->getLocale() == 'ar' ? 'عرض' : 'View' }}">
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
