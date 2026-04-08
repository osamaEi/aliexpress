@php $isAr = app()->getLocale() == 'ar'; $editing = isset($video) && $video !== null; @endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header"><h6 class="mb-0">{{ $isAr ? 'معلومات الفيديو' : 'Video Info' }}</h6></div>
        <div class="card-body">

            <div class="row g-3">

                {{-- Title EN --}}
                <div class="col-md-6">
                    <label class="form-label fw-medium">{{ $isAr ? 'العنوان (إنجليزي)' : 'Title (English)' }} <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $editing ? $video->title : '') }}"
                           placeholder="Enter video title in English">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Title AR --}}
                <div class="col-md-6">
                    <label class="form-label fw-medium">{{ $isAr ? 'العنوان (عربي)' : 'Title (Arabic)' }}</label>
                    <input type="text" name="title_ar" class="form-control" dir="rtl"
                           value="{{ old('title_ar', $editing ? $video->title_ar : '') }}"
                           placeholder="أدخل عنوان الفيديو بالعربي">
                </div>

                {{-- Description EN --}}
                <div class="col-md-6">
                    <label class="form-label fw-medium">{{ $isAr ? 'الوصف (إنجليزي)' : 'Description (English)' }}</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Short description...">{{ old('description', $editing ? $video->description : '') }}</textarea>
                </div>

                {{-- Description AR --}}
                <div class="col-md-6">
                    <label class="form-label fw-medium">{{ $isAr ? 'الوصف (عربي)' : 'Description (Arabic)' }}</label>
                    <textarea name="description_ar" class="form-control" rows="3" dir="rtl"
                              placeholder="وصف قصير...">{{ old('description_ar', $editing ? $video->description_ar : '') }}</textarea>
                </div>

            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header"><h6 class="mb-0">{{ $isAr ? 'مصدر الفيديو' : 'Video Source' }}</h6></div>
        <div class="card-body">

            {{-- Type Toggle --}}
            <div class="mb-4">
                <label class="form-label fw-medium">{{ $isAr ? 'نوع الفيديو' : 'Video Type' }}</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type" id="type_youtube"
                               value="youtube" {{ old('type', $editing ? $video->type : 'youtube') === 'youtube' ? 'checked' : '' }}>
                        <label class="form-check-label" for="type_youtube">
                            <i class="ri-youtube-line text-danger me-1"></i>
                            {{ $isAr ? 'رابط YouTube' : 'YouTube Link' }}
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type" id="type_upload"
                               value="upload" {{ old('type', $editing ? $video->type : '') === 'upload' ? 'checked' : '' }}>
                        <label class="form-check-label" for="type_upload">
                            <i class="ri-upload-cloud-line text-primary me-1"></i>
                            {{ $isAr ? 'رفع ملف فيديو' : 'Upload Video File' }}
                        </label>
                    </div>
                </div>
            </div>

            {{-- YouTube URL --}}
            <div id="youtube_section">
                <label class="form-label fw-medium">{{ $isAr ? 'رابط YouTube' : 'YouTube URL' }}</label>
                <div class="input-group">
                    <span class="input-group-text text-danger"><i class="ri-youtube-line"></i></span>
                    <input type="url" name="youtube_url"
                           class="form-control @error('youtube_url') is-invalid @enderror"
                           value="{{ old('youtube_url', $editing ? $video->youtube_url : '') }}"
                           placeholder="https://www.youtube.com/watch?v=...">
                    @error('youtube_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="text-muted">{{ $isAr ? 'يدعم روابط youtube.com و youtu.be و Shorts' : 'Supports youtube.com, youtu.be and Shorts links' }}</small>

                {{-- Live YouTube preview --}}
                <div id="youtube_preview" class="mt-3" style="display:none;">
                    <div class="ratio ratio-16x9" style="max-width:400px;">
                        <iframe id="yt_iframe" src="" allowfullscreen class="rounded"></iframe>
                    </div>
                </div>
            </div>

            {{-- Upload File --}}
            <div id="upload_section" style="display:none;">
                <label class="form-label fw-medium">{{ $isAr ? 'ملف الفيديو' : 'Video File' }}</label>
                <input type="file" name="video_file"
                       class="form-control @error('video_file') is-invalid @enderror"
                       accept="video/mp4,video/quicktime,video/x-msvideo,video/webm">
                @error('video_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">{{ $isAr ? 'الصيغ المقبولة: MP4, MOV, AVI, WebM — الحد الأقصى: 200MB' : 'Accepted: MP4, MOV, AVI, WebM — Max 200MB' }}</small>

                @if($editing && $video->video_path)
                <div class="mt-2">
                    <span class="badge bg-label-success">
                        <i class="ri-check-line me-1"></i>
                        {{ $isAr ? 'يوجد فيديو مرفوع — رفع ملف جديد سيستبدله' : 'Video uploaded — uploading replaces it' }}
                    </span>
                </div>
                @endif
            </div>

        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header"><h6 class="mb-0">{{ $isAr ? 'الإعدادات' : 'Settings' }}</h6></div>
        <div class="card-body">
            <div class="row g-3">

                {{-- Visibility --}}
                <div class="col-md-4">
                    <label class="form-label fw-medium">{{ $isAr ? 'يُعرض لـ' : 'Visible To' }}</label>
                    <select name="visibility" class="form-select">
                        <option value="all"          {{ old('visibility', $editing ? $video->visibility : 'all') === 'all'          ? 'selected' : '' }}>{{ $isAr ? '👥 الجميع' : '👥 Everyone' }}</option>
                        <option value="sellers"      {{ old('visibility', $editing ? $video->visibility : '') === 'sellers'      ? 'selected' : '' }}>{{ $isAr ? '🏪 التجار فقط' : '🏪 Sellers only' }}</option>
                        <option value="distributors" {{ old('visibility', $editing ? $video->visibility : '') === 'distributors' ? 'selected' : '' }}>{{ $isAr ? '🚚 الموزعون فقط' : '🚚 Distributors only' }}</option>
                        <option value="admins"       {{ old('visibility', $editing ? $video->visibility : '') === 'admins'       ? 'selected' : '' }}>{{ $isAr ? '🔒 الإدارة فقط' : '🔒 Admins only' }}</option>
                    </select>
                </div>

                {{-- Sort Order --}}
                <div class="col-md-4">
                    <label class="form-label fw-medium">{{ $isAr ? 'الترتيب' : 'Sort Order' }}</label>
                    <input type="number" name="sort_order" class="form-control" min="0"
                           value="{{ old('sort_order', $editing ? $video->sort_order : 0) }}">
                    <small class="text-muted">{{ $isAr ? 'الأصغر يظهر أولاً' : 'Lower = first' }}</small>
                </div>

                {{-- Thumbnail --}}
                <div class="col-md-4">
                    <label class="form-label fw-medium">{{ $isAr ? 'صورة مصغرة (اختياري)' : 'Custom Thumbnail (optional)' }}</label>
                    <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    @if($editing && $video->thumbnail)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $video->thumbnail) }}"
                             class="rounded" style="height:45px;">
                    </div>
                    @endif
                    <small class="text-muted">{{ $isAr ? 'لـ YouTube: تُستخدم صورة YouTube تلقائياً' : 'For YouTube: auto-uses YouTube thumbnail' }}</small>
                </div>

                {{-- Active --}}
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $editing ? $video->is_active : true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="is_active">
                            {{ $isAr ? 'نشط (مرئي للمستخدمين)' : 'Active (visible to users)' }}
                        </label>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="ri-save-line me-1"></i>
            {{ $editing ? ($isAr ? 'حفظ التغييرات' : 'Save Changes') : ($isAr ? 'إضافة الفيديو' : 'Add Video') }}
        </button>
        <a href="{{ route('admin.training-videos.index') }}" class="btn btn-outline-secondary">
            {{ $isAr ? 'إلغاء' : 'Cancel' }}
        </a>
    </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="type"]');
    const ytSection = document.getElementById('youtube_section');
    const upSection = document.getElementById('upload_section');
    const ytInput   = document.querySelector('input[name="youtube_url"]');
    const ytPreview = document.getElementById('youtube_preview');
    const ytIframe  = document.getElementById('yt_iframe');

    function toggleSections() {
        const val = document.querySelector('input[name="type"]:checked')?.value;
        ytSection.style.display = val === 'youtube' ? 'block' : 'none';
        upSection.style.display = val === 'upload'  ? 'block' : 'none';
    }

    radios.forEach(r => r.addEventListener('change', toggleSections));
    toggleSections();

    // Live YouTube preview
    function extractYtId(url) {
        const m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
        return m ? m[1] : null;
    }

    if (ytInput) {
        ytInput.addEventListener('input', function () {
            const id = extractYtId(this.value);
            if (id) {
                ytIframe.src = `https://www.youtube.com/embed/${id}`;
                ytPreview.style.display = 'block';
            } else {
                ytPreview.style.display = 'none';
                ytIframe.src = '';
            }
        });

        // Trigger on load if editing
        if (ytInput.value) ytInput.dispatchEvent(new Event('input'));
    }
});
</script>
