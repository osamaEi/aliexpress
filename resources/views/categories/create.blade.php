@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Create New Category</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('categories._form')
            </form>
        </div>
    </div>
</div>
@endsection
