@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Category: {{ $category->name }}</h5>
            <a href="{{ route('categories.index') }}" class="btn btn-sm btn-secondary">
                <i class="ri-arrow-left-line me-1"></i> Back to List
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
