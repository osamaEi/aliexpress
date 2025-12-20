@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('messages.categories') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('messages.category') }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الوصف' : 'Description' }}</th>
                            <th>{{ __('messages.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td>
                                <strong>{{ app()->getLocale() == 'ar' && $category->name_ar ? $category->name_ar : $category->name }}</strong>
                            </td>
                            <td>
                                @if(app()->getLocale() == 'ar' && $category->description_ar)
                                    {{ Str::limit($category->description_ar, 100) }}
                                @elseif($category->description)
                                    {{ Str::limit($category->description, 100) }}
                                @else
                                    {{ app()->getLocale() == 'ar' ? 'لا يوجد وصف' : 'No description' }}
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                    {{ $category->is_active ? __('messages.active') : __('messages.inactive') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">
                                {{ app()->getLocale() == 'ar' ? 'لا توجد تصنيفات' : 'No categories available' }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="mt-4">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
