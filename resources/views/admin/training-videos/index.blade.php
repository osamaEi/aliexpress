@extends('dashboard')

@section('title', app()->getLocale() == 'ar' ? 'الفيديوهات التدريبية' : 'Training Videos')

@section('content')
@php $isAr = app()->getLocale() == 'ar'; @endphp

<div class="col-12" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">{{ $isAr ? 'الفيديوهات التدريبية' : 'Training Videos' }}</h4>
            <p class="text-muted small mb-0">
                {{ $isAr ? 'إدارة الفيديوهات التعليمية للتجار والموزعين' : 'Manage educational videos for sellers and distributors' }}
            </p>
        </div>
        <a href="{{ route('admin.training-videos.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-1"></i>{{ $isAr ? 'إضافة فيديو' : 'Add Video' }}
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:80px;">{{ $isAr ? 'صورة' : 'Thumb' }}</th>
                        <th>{{ $isAr ? 'العنوان' : 'Title' }}</th>
                        <th>{{ $isAr ? 'النوع' : 'Type' }}</th>
                        <th>{{ $isAr ? 'الظهور لـ' : 'Visible To' }}</th>
                        <th class="text-center">{{ $isAr ? 'الترتيب' : 'Order' }}</th>
                        <th class="text-center">{{ $isAr ? 'الحالة' : 'Status' }}</th>
                        <th class="text-center pe-3">{{ $isAr ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($videos as $video)
                    @php
                        $title = $isAr && $video->title_ar ? $video->title_ar : $video->title;
                    @endphp
                    <tr>
                        <td class="ps-3">
                            @if($video->thumbnail_url)
                                <img src="{{ $video->thumbnail_url }}"
                                     alt="{{ $title }}"
                                     class="rounded"
                                     style="width:70px;height:45px;object-fit:cover;">
                            @else
                                <div class="rounded bg-label-secondary d-flex align-items-center justify-content-center"
                                     style="width:70px;height:45px;">
                                    <i class="ri-play-circle-line text-muted ri-24px"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ Str::limit($title, 50) }}</div>
                            @if($video->description || $video->description_ar)
                            <small class="text-muted">
                                {{ Str::limit($isAr && $video->description_ar ? $video->description_ar : $video->description, 60) }}
                            </small>
                            @endif
                        </td>
                        <td>
                            @if($video->type === 'youtube')
                                <span class="badge bg-label-danger">
                                    <i class="ri-youtube-line me-1"></i>YouTube
                                </span>
                            @else
                                <span class="badge bg-label-primary">
                                    <i class="ri-upload-cloud-line me-1"></i>
                                    {{ $isAr ? 'ملف مرفوع' : 'Uploaded' }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $visLabels = [
                                    'all'          => ['ar' => 'الجميع',         'en' => 'Everyone',      'color' => 'success'],
                                    'sellers'      => ['ar' => 'التجار فقط',     'en' => 'Sellers only',  'color' => 'warning'],
                                    'distributors' => ['ar' => 'الموزعون فقط',   'en' => 'Distributors',  'color' => 'info'],
                                    'admins'       => ['ar' => 'الإدارة فقط',    'en' => 'Admins only',   'color' => 'secondary'],
                                ];
                                $vis = $visLabels[$video->visibility] ?? ['ar' => $video->visibility, 'en' => $video->visibility, 'color' => 'secondary'];
                            @endphp
                            <span class="badge bg-label-{{ $vis['color'] }}">
                                {{ $isAr ? $vis['ar'] : $vis['en'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-label-secondary">{{ $video->sort_order }}</span>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input toggle-status"
                                       type="checkbox"
                                       data-id="{{ $video->id }}"
                                       {{ $video->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-center pe-3">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.training-videos.edit', $video) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ri-edit-line"></i>
                                </a>
                                <form action="{{ route('admin.training-videos.destroy', $video) }}" method="POST"
                                      onsubmit="return confirm('{{ $isAr ? 'حذف هذا الفيديو؟' : 'Delete this video?' }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="ri-video-line text-muted" style="font-size:3rem;opacity:.3;"></i>
                            <p class="text-muted mt-2">{{ $isAr ? 'لا توجد فيديوهات بعد' : 'No videos yet' }}</p>
                            <a href="{{ route('admin.training-videos.create') }}" class="btn btn-primary btn-sm">
                                <i class="ri-add-line me-1"></i>{{ $isAr ? 'أضف أول فيديو' : 'Add First Video' }}
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($videos->hasPages())
        <div class="card-footer">{{ $videos->links() }}</div>
        @endif
    </div>

</div>

<script>
document.querySelectorAll('.toggle-status').forEach(cb => {
    cb.addEventListener('change', function () {
        fetch(`{{ url('admin/training-videos') }}/${this.dataset.id}/toggle`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
    });
});
</script>
@endsection
