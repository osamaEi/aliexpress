@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('messages.edit_category') }}: {{ $category->name }}</h5>
            <a href="{{ route('categories.index') }}" class="btn btn-sm btn-secondary">
                <i class="ri-arrow-left-line me-1"></i> {{ __('messages.back_to_list') }}
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('categories._form')
            </form>
        </div>
    </div>
</div>
@endsection
