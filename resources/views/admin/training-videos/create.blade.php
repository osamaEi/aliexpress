@extends('dashboard')

@section('title', app()->getLocale() == 'ar' ? 'إضافة فيديو تدريبي' : 'Add Training Video')

@section('content')
@php $isAr = app()->getLocale() == 'ar'; $video = null; @endphp

<div class="col-12 col-lg-8 mx-auto" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">{{ $isAr ? 'إضافة فيديو تدريبي' : 'Add Training Video' }}</h4>
        <a href="{{ route('admin.training-videos.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ri-arrow-{{ $isAr ? 'right' : 'left' }}-line me-1"></i>{{ $isAr ? 'رجوع' : 'Back' }}
        </a>
    </div>

    @include('admin.training-videos._form', ['action' => route('admin.training-videos.store'), 'method' => 'POST'])

</div>
@endsection
