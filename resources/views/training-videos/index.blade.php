@extends('dashboard')

@section('title', app()->getLocale() == 'ar' ? 'الفيديوهات التدريبية' : 'Training Videos')

@section('content')
@php $isAr = app()->getLocale() == 'ar'; @endphp

<div class="col-12" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

    <div class="mb-4">
        <h4 class="fw-bold mb-1">
            <i class="ri-play-circle-line me-2 text-primary"></i>
            {{ $isAr ? 'الفيديوهات التدريبية' : 'Training Videos' }}
        </h4>
        <p class="text-muted small mb-0">
            {{ $isAr ? 'فيديوهات تعليمية لمساعدتك في استخدام المنصة' : 'Educational videos to help you use the platform' }}
        </p>
    </div>

    @if($videos->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="ri-video-line text-muted" style="font-size:3.5rem;opacity:.3;"></i>
            <h6 class="text-muted mt-3">{{ $isAr ? 'لا توجد فيديوهات متاحة حالياً' : 'No videos available yet' }}</h6>
        </div>
    </div>
    @else
    <div class="row g-4">
        @foreach($videos as $video)
        @php $title = $isAr && $video->title_ar ? $video->title_ar : $video->title; @endphp
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 video-card"
                 style="cursor:pointer;"
                 data-type="{{ $video->type }}"
                 data-embed="{{ $video->embed_url }}"
                 data-video="{{ $video->video_path ? asset('storage/' . $video->video_path) : '' }}"
                 data-title="{{ $title }}"
                 data-desc="{{ $isAr && $video->description_ar ? $video->description_ar : $video->description }}">

                {{-- Thumbnail --}}
                <div class="position-relative" style="aspect-ratio:16/9;overflow:hidden;border-radius:.5rem .5rem 0 0;">
                    @if($video->thumbnail_url)
                        <img src="{{ $video->thumbnail_url }}"
                             alt="{{ $title }}"
                             class="w-100 h-100"
                             style="object-fit:cover;">
                    @else
                        <div class="w-100 h-100 bg-label-secondary d-flex align-items-center justify-content-center">
                            <i class="ri-video-line text-muted" style="font-size:2.5rem;"></i>
                        </div>
                    @endif
                    {{-- Play button overlay --}}
                    <div class="position-absolute top-50 start-50 translate-middle"
                         style="background:rgba(0,0,0,.55);border-radius:50%;width:52px;height:52px;display:flex;align-items:center;justify-content:center;">
                        <i class="ri-play-fill text-white" style="font-size:1.5rem;margin-{{ $isAr ? 'right' : 'left' }}:3px;"></i>
                    </div>
                    {{-- Type badge --}}
                    <div class="position-absolute top-0 end-0 m-2">
                        @if($video->type === 'youtube')
                            <span class="badge bg-danger" style="font-size:.7rem;">
                                <i class="ri-youtube-line me-1"></i>YouTube
                            </span>
                        @else
                            <span class="badge bg-primary" style="font-size:.7rem;">
                                <i class="ri-video-line me-1"></i>{{ $isAr ? 'فيديو' : 'Video' }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <h6 class="fw-semibold mb-1">{{ $title }}</h6>
                    @if($video->localizedDescription())
                    <p class="text-muted small mb-0">
                        {{ Str::limit($video->localizedDescription(), 80) }}
                    </p>
                    @endif
                </div>

            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

{{-- Video Modal --}}
<div class="modal fade" id="videoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="videoModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="ratio ratio-16x9 mb-3">
                    <iframe id="modalIframe" src="" allowfullscreen class="rounded" style="display:none;"></iframe>
                    <video id="modalVideo" controls class="rounded w-100" style="display:none;"></video>
                </div>
                <p class="text-muted small mb-0" id="videoModalDesc"></p>
            </div>
        </div>
    </div>
</div>

<style>
.video-card:hover { transform: translateY(-3px); transition: transform .2s; }
.video-card { transition: transform .2s, box-shadow .2s; }
.video-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,.12) !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal     = new bootstrap.Modal(document.getElementById('videoModal'));
    const iframe    = document.getElementById('modalIframe');
    const videoEl   = document.getElementById('modalVideo');
    const titleEl   = document.getElementById('videoModalTitle');
    const descEl    = document.getElementById('videoModalDesc');

    document.querySelectorAll('.video-card').forEach(card => {
        card.addEventListener('click', function () {
            const type    = this.dataset.type;
            const embed   = this.dataset.embed;
            const vidSrc  = this.dataset.video;
            const title   = this.dataset.title;
            const desc    = this.dataset.desc;

            titleEl.textContent = title;
            descEl.textContent  = desc || '';

            if (type === 'youtube' && embed) {
                iframe.src = embed + '?autoplay=1';
                iframe.style.display = 'block';
                videoEl.style.display = 'none';
            } else if (vidSrc) {
                videoEl.src = vidSrc;
                videoEl.style.display = 'block';
                iframe.style.display = 'none';
            }

            modal.show();
        });
    });

    // Stop video on close
    document.getElementById('videoModal').addEventListener('hidden.bs.modal', function () {
        iframe.src = '';
        videoEl.src = '';
        videoEl.pause();
    });
});
</script>
@endsection
