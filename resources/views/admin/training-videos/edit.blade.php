@extends('dashboard')

@section('title', app()->getLocale() == 'ar' ? 'تعديل فيديو تدريبي' : 'Edit Training Video')

@section('content')
@php $isAr = app()->getLocale() == 'ar'; @endphp

<div class="col-12 col-lg-8 mx-auto" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">{{ $isAr ? 'تعديل الفيديو' : 'Edit Video' }}</h4>
        <a href="{{ route('admin.training-videos.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ri-arrow-{{ $isAr ? 'right' : 'left' }}-line me-1"></i>{{ $isAr ? 'رجوع' : 'Back' }}
        </a>
    </div>

    @include('admin.training-videos._form', [
        'action' => route('admin.training-videos.update', $video),
        'method' => 'PUT',
    ])

</div>
@endsection
