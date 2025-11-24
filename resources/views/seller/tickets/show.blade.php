@extends('dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Ticket Header -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-2">{{ $ticket->subject }}</h4>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-{{ $ticket->status_color }}">
                                    {{ __('messages.status_' . $ticket->status) }}
                                </span>
                                <span class="badge bg-{{ $ticket->priority_color }}">
                                    {{ __('messages.priority_' . $ticket->priority) }}
                                </span>
                                <span class="badge bg-secondary">
                                    #{{ $ticket->id }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('seller.tickets.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i>
                            {{ __('messages.back') }}
                        </a>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <i class="ri-calendar-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                <strong>{{ __('messages.created_at') }}:</strong>
                                {{ $ticket->created_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                        @if($ticket->assignedAdmin)
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <i class="ri-user-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                    <strong>{{ __('messages.assigned_to') }}:</strong>
                                    {{ $ticket->assignedAdmin->name }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        <strong>{{ __('messages.description') }}:</strong>
                        <p class="mt-2">{{ $ticket->description }}</p>
                    </div>
                </div>
            </div>

            <!-- Replies -->
            @if($ticket->replies->count() > 0)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('messages.replies') }}</h6>
                    </div>
                    <div class="card-body">
                        @foreach($ticket->replies as $reply)
                            <div class="d-flex gap-3 mb-3 {{ !$loop->last ? 'pb-3 border-bottom' : '' }}">
                                <div>
                                    @if($reply->is_admin)
                                        <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <i class="ri-admin-line text-white"></i>
                                        </div>
                                    @else
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <span class="text-white">{{ strtoupper(substr($reply->user->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong>{{ $reply->user->name }}</strong>
                                            @if($reply->is_admin)
                                                <span class="badge bg-danger" style="font-size: 0.7rem;">
                                                    {{ __('messages.admin') }}
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $reply->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0">{{ $reply->message }}</p>

                                    @if($reply->attachments && count($reply->attachments) > 0)
                                        <div class="mt-3">
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($reply->attachments as $attachment)
                                                    <a href="{{ asset('storage/' . $attachment) }}" target="_blank" class="text-decoration-none">
                                                        <img src="{{ asset('storage/' . $attachment) }}"
                                                             alt="Attachment"
                                                             class="img-thumbnail"
                                                             style="max-width: 150px; max-height: 150px; object-fit: cover; cursor: pointer;"
                                                             data-bs-toggle="modal"
                                                             data-bs-target="#imageModal{{ $reply->id }}_{{ $loop->index }}">
                                                    </a>

                                                    <!-- Image Modal -->
                                                    <div class="modal fade" id="imageModal{{ $reply->id }}_{{ $loop->index }}" tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">{{ __('messages.attachment') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    <img src="{{ asset('storage/' . $attachment) }}"
                                                                         alt="Attachment"
                                                                         class="img-fluid"
                                                                         style="max-width: 100%; height: auto;">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <a href="{{ asset('storage/' . $attachment) }}"
                                                                       download
                                                                       class="btn btn-primary">
                                                                        <i class="ri-download-line me-1"></i>
                                                                        {{ __('messages.download') }}
                                                                    </a>
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                        {{ __('messages.close') }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Reply Form -->
            @if($ticket->status !== 'closed')
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('messages.add_reply') }}</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('seller.tickets.reply', $ticket) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.message') }}</label>
                                <textarea class="form-control @error('message') is-invalid @enderror"
                                          name="message"
                                          rows="5"
                                          placeholder="{{ __('messages.type_your_reply') }}"
                                          required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="ri-image-line me-1"></i>
                                    {{ __('messages.attachments') }} ({{ __('messages.optional') }})
                                </label>
                                <input type="file"
                                       class="form-control @error('attachments.*') is-invalid @enderror"
                                       name="attachments[]"
                                       accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                       multiple
                                       id="attachmentInput">
                                <small class="text-muted d-block mt-1">
                                    {{ __('messages.upload_images_info') }} (Max: 5MB per image)
                                </small>
                                @error('attachments.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- Image Preview -->
                                <div id="imagePreview" class="mt-3 d-flex flex-wrap gap-2"></div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="ri-send-plane-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                                {{ __('messages.send_reply') }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="ri-information-line {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                    {{ __('messages.ticket_closed_info') }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const attachmentInput = document.getElementById('attachmentInput');
    const imagePreview = document.getElementById('imagePreview');

    if (attachmentInput) {
        attachmentInput.addEventListener('change', function(e) {
            imagePreview.innerHTML = '';
            const files = Array.from(e.target.files);

            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'position-relative';
                        previewDiv.style.width = '100px';
                        previewDiv.style.height = '100px';

                        previewDiv.innerHTML = `
                            <img src="${e.target.result}"
                                 class="img-thumbnail"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                            <button type="button"
                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle"
                                    style="width: 25px; height: 25px; padding: 0; font-size: 12px;"
                                    onclick="removeImage(${index})">
                                <i class="ri-close-line"></i>
                            </button>
                        `;

                        imagePreview.appendChild(previewDiv);
                    };

                    reader.readAsDataURL(file);
                }
            });
        });
    }
});

function removeImage(index) {
    const attachmentInput = document.getElementById('attachmentInput');
    const dt = new DataTransfer();
    const files = Array.from(attachmentInput.files);

    files.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });

    attachmentInput.files = dt.files;
    attachmentInput.dispatchEvent(new Event('change'));
}
</script>
@endsection
