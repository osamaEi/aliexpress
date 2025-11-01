@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card mb-6">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ri-user-settings-line me-2"></i>Profile Settings
            </h5>
        </div>
        <div class="card-body">
            @if(session('status') === 'profile-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    Profile updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('status') === 'password-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    Password updated successfully!
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
                                    <i class="ri-upload-2-line me-1"></i>Upload Photo
                                </label>
                                <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*">
                                <p class="text-muted small mb-0">Allowed JPG, GIF or PNG. Max size of 2MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="col-12">
                        <h6 class="text-primary mb-3">
                            <i class="ri-information-line me-1"></i>Basic Information
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label for="name" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                               id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}">
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(!$user->email_verified_at)
                            <small class="text-warning">
                                <i class="ri-error-warning-line me-1"></i>Email not verified
                            </small>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                               id="company_name" name="company_name" value="{{ old('company_name', $user->company_name) }}">
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="country" class="form-label">Country</label>
                        <select class="form-select @error('country') is-invalid @enderror" id="country" name="country">
                            <option value="">Select Country</option>
                            <option value="AE" {{ old('country', $user->country) == 'AE' ? 'selected' : '' }}>🇦🇪 United Arab Emirates</option>
                            <option value="SA" {{ old('country', $user->country) == 'SA' ? 'selected' : '' }}>🇸🇦 Saudi Arabia</option>
                            <option value="EG" {{ old('country', $user->country) == 'EG' ? 'selected' : '' }}>🇪🇬 Egypt</option>
                            <option value="KW" {{ old('country', $user->country) == 'KW' ? 'selected' : '' }}>🇰🇼 Kuwait</option>
                            <option value="QA" {{ old('country', $user->country) == 'QA' ? 'selected' : '' }}>🇶🇦 Qatar</option>
                            <option value="BH" {{ old('country', $user->country) == 'BH' ? 'selected' : '' }}>🇧🇭 Bahrain</option>
                            <option value="OM" {{ old('country', $user->country) == 'OM' ? 'selected' : '' }}>🇴🇲 Oman</option>
                            <option value="JO" {{ old('country', $user->country) == 'JO' ? 'selected' : '' }}>🇯🇴 Jordan</option>
                            <option value="LB" {{ old('country', $user->country) == 'LB' ? 'selected' : '' }}>🇱🇧 Lebanon</option>
                        </select>
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="user_type" class="form-label">User Type</label>
                        <select class="form-select @error('user_type') is-invalid @enderror" id="user_type" name="user_type" disabled>
                            <option value="admin" {{ $user->user_type == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="seller" {{ $user->user_type == 'seller' ? 'selected' : '' }}>Seller</option>
                            <option value="buyer" {{ $user->user_type == 'buyer' ? 'selected' : '' }}>Buyer</option>
                        </select>
                        <small class="text-muted">Contact admin to change user type</small>
                        @error('user_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Business Information -->
                    <div class="col-12 mt-4">
                        <h6 class="text-primary mb-3">
                            <i class="ri-briefcase-line me-1"></i>Business Information
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label for="main_activity" class="form-label">Main Activity</label>
                        <input type="text" class="form-control @error('main_activity') is-invalid @enderror"
                               id="main_activity" name="main_activity" value="{{ old('main_activity', $user->main_activity) }}"
                               placeholder="e.g., E-commerce, Retail, Wholesale">
                        @error('main_activity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="sub_activity" class="form-label">Sub Activity</label>
                        <input type="text" class="form-control @error('sub_activity') is-invalid @enderror"
                               id="sub_activity" name="sub_activity" value="{{ old('sub_activity', $user->sub_activity) }}"
                               placeholder="e.g., Electronics, Fashion, Home & Garden">
                        @error('sub_activity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i>Save Changes
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="ri-close-line me-1"></i>Cancel
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
                <i class="ri-lock-password-line me-2"></i>Change Password
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                               id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12"></div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control"
                               id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-lock-line me-1"></i>Update Password
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
