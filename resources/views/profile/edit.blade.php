@extends('dashboard')

@section('content')
<div class="col-12" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card mb-6">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ri-user-settings-line me-2"></i>{{ __('messages.profile_settings') }}
            </h5>
        </div>
        <div class="card-body">
            @if(session('status') === 'profile-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    {{ __('messages.profile_updated') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('status') === 'password-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    {{ __('messages.password_updated') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="row g-4">
                    <!-- Avatar Section -->
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-4">
                            <div class="avatar avatar-xl me-3">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded-circle" id="avatarPreview" />
                                @else
                                    <span class="avatar-initial rounded-circle bg-label-primary" style="font-size: 2rem;" id="avatarInitials">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </span>
                                @endif
                            </div>
                            <div>
                                <label for="avatar" class="btn btn-primary btn-sm mb-2">
                                    <i class="ri-upload-2-line me-1"></i>{{ __('messages.upload_photo') }}
                                </label>
                                <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                                <p class="text-muted small mb-0">{{ __('messages.allowed_formats') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="col-12">
                        <h6 class="text-primary mb-3">
                            <i class="ri-information-line me-1"></i>{{ __('messages.basic_information') }}
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ __('messages.username') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="full_name" class="form-label">{{ __('messages.full_name') }}</label>
                        <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                               id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}">
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">{{ __('messages.email') }} <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(!$user->email_verified_at)
                            <small class="text-warning">
                                <i class="ri-error-warning-line me-1"></i>{{ __('messages.email_not_verified') }}
                            </small>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label for="company_name" class="form-label">{{ __('messages.company_name') }}</label>
                        <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                               id="company_name" name="company_name" value="{{ old('company_name', $user->company_name) }}">
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="country" class="form-label">{{ __('messages.country') }}</label>
                        <select class="form-select @error('country') is-invalid @enderror" id="country" name="country">
                            <option value="">{{ __('messages.select_country') }}</option>
                            <option value="AE" {{ old('country', $user->country) == 'AE' ? 'selected' : '' }}>🇦🇪 {{ __('messages.united_arab_emirates') }}</option>
                            <option value="SA" {{ old('country', $user->country) == 'SA' ? 'selected' : '' }}>🇸🇦 {{ __('messages.saudi_arabia') }}</option>
                            <option value="EG" {{ old('country', $user->country) == 'EG' ? 'selected' : '' }}>🇪🇬 {{ __('messages.egypt') }}</option>
                            <option value="KW" {{ old('country', $user->country) == 'KW' ? 'selected' : '' }}>🇰🇼 {{ __('messages.kuwait') }}</option>
                            <option value="QA" {{ old('country', $user->country) == 'QA' ? 'selected' : '' }}>🇶🇦 {{ __('messages.qatar') }}</option>
                            <option value="BH" {{ old('country', $user->country) == 'BH' ? 'selected' : '' }}>🇧🇭 {{ __('messages.bahrain') }}</option>
                            <option value="OM" {{ old('country', $user->country) == 'OM' ? 'selected' : '' }}>🇴🇲 {{ __('messages.oman') }}</option>
                            <option value="JO" {{ old('country', $user->country) == 'JO' ? 'selected' : '' }}>🇯🇴 {{ __('messages.jordan') }}</option>
                            <option value="LB" {{ old('country', $user->country) == 'LB' ? 'selected' : '' }}>🇱🇧 {{ __('messages.lebanon') }}</option>
                        </select>
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="user_type" class="form-label">{{ __('messages.user_type') }}</label>
                        <select class="form-select @error('user_type') is-invalid @enderror" id="user_type" name="user_type" disabled>
                            <option value="admin" {{ $user->user_type == 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                            <option value="seller" {{ $user->user_type == 'seller' ? 'selected' : '' }}>{{ __('messages.seller') }}</option>
                            <option value="buyer" {{ $user->user_type == 'buyer' ? 'selected' : '' }}>{{ __('messages.buyer') }}</option>
                        </select>
                        <small class="text-muted">{{ __('messages.contact_admin_change') }}</small>
                        @error('user_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Business Information -->
                    <div class="col-12 mt-4">
                        <h6 class="text-primary mb-3">
                            <i class="ri-briefcase-line me-1"></i>{{ __('messages.business_information') }}
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label for="main_activity" class="form-label">{{ __('messages.main_activity') }}</label>
                        <input type="text" class="form-control @error('main_activity') is-invalid @enderror"
                               id="main_activity" name="main_activity" value="{{ old('main_activity', $user->main_activity) }}"
                               placeholder="{{ __('messages.main_activity_placeholder') }}">
                        @error('main_activity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="sub_activity" class="form-label">{{ __('messages.sub_activity') }}</label>
                        <input type="text" class="form-control @error('sub_activity') is-invalid @enderror"
                               id="sub_activity" name="sub_activity" value="{{ old('sub_activity', $user->sub_activity) }}"
                               placeholder="{{ __('messages.sub_activity_placeholder') }}">
                        @error('sub_activity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i>{{ __('messages.save_changes') }}
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="ri-close-line me-1"></i>{{ __('messages.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Card -->
    <div class="card mb-6">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ri-lock-password-line me-2"></i>{{ __('messages.change_password') }}
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="current_password" class="form-label">{{ __('messages.current_password') }} <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                               id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12"></div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">{{ __('messages.new_password') }} <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('messages.minimum_8_characters') }}</small>
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">{{ __('messages.confirm_new_password') }} <span class="text-danger">*</span></label>
                        <input type="password" class="form-control"
                               id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-lock-line me-1"></i>{{ __('messages.update_password') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Avatar preview
    document.getElementById('avatar')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const avatarPreview = document.getElementById('avatarPreview');
                const avatarInitials = document.getElementById('avatarInitials');

                if (avatarPreview) {
                    avatarPreview.src = e.target.result;
                } else if (avatarInitials) {
                    // Replace initials with image
                    const avatar = avatarInitials.parentElement;
                    avatar.innerHTML = '<img src="' + e.target.result + '" alt="Avatar" class="rounded-circle" id="avatarPreview" />';
                }
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
