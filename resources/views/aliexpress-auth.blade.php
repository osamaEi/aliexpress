@extends('dashboard')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="ri-key-2-line me-2"></i>
                AliExpress OAuth Authorization
            </h5>
            <span class="badge bg-label-primary">Step 1 of 2</span>
        </div>
        <div class="card-body">
            <!-- Instructions Section -->
            <div class="alert alert-info d-flex align-items-start" role="alert">
                <i class="ri-information-line me-2 fs-4"></i>
                <div>
                    <h6 class="alert-heading mb-2">Before you start</h6>
                    <p class="mb-0">
                        Make sure you have configured your <code>ALIEXPRESS_API_KEY</code> and
                        <code>ALIEXPRESS_API_SECRET</code> in your <code>.env</code> file.
                    </p>
                </div>
            </div>

            <!-- Configuration Status -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="ri-settings-3-line me-2"></i>
                                Current Configuration
                            </h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <strong>API Key:</strong>
                                    <span class="badge bg-label-{{ $appKey ? 'success' : 'danger' }} ms-2">
                                        {{ $appKey ?? 'NOT SET' }}
                                    </span>
                                </li>
                                <li class="mb-2">
                                    <strong>API Secret:</strong>
                                    <span class="badge bg-label-{{ $apiSecretSet ? 'success' : 'danger' }} ms-2">
                                        {{ $apiSecretSet ? '***SET***' : 'NOT SET' }}
                                    </span>
                                </li>
                                <li class="mb-2">
                                    <strong>Redirect URI:</strong>
                                    <code class="text-primary">{{ $redirectUri }}</code>
                                </li>
                                <li class="mb-0">
                                    <strong>State Token:</strong>
                                    <code class="text-muted small">{{ Str::limit($state, 20) }}</code>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="ri-roadmap-line me-2"></i>
                                Authorization Flow
                            </h6>
                            <ol class="mb-0 ps-3">
                                <li class="mb-2">Click the authorization button below</li>
                                <li class="mb-2">Login to your AliExpress account</li>
                                <li class="mb-2">Authorize the application</li>
                                <li class="mb-2">Get redirected back automatically</li>
                                <li class="mb-0">Receive your access token</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Authorization Button -->
            <div class="text-center py-4">
                @if($appKey && $apiSecretSet)
                    <a href="{{ $authUrl }}" class="btn btn-primary btn-lg">
                        <i class="ri-login-circle-line me-2"></i>
                        Authorize with AliExpress
                    </a>
                    <p class="text-muted mt-3 mb-0">
                        <small>You will be redirected to AliExpress to authorize this application</small>
                    </p>
                @else
                    <div class="alert alert-danger" role="alert">
                        <i class="ri-alert-line me-2"></i>
                        <strong>Configuration Error:</strong>
                        Please set your API credentials in the <code>.env</code> file first.
                    </div>
                @endif
            </div>

            <!-- Technical Details (Collapsible) -->
            <div class="mt-4">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#technicalDetails">
                    <i class="ri-code-line me-1"></i>
                    Show Technical Details
                </button>

                <div class="collapse mt-3" id="technicalDetails">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3">Authorization URL:</h6>
                            <div class="bg-white p-3 rounded border">
                                <code class="text-break">{{ $authUrl }}</code>
                            </div>

                            <h6 class="mt-4 mb-3">Alternative Method (Manual):</h6>
                            <p class="mb-2">If you prefer to handle the callback manually:</p>
                            <ol class="mb-0">
                                <li>Copy the authorization URL above and paste it in your browser</li>
                                <li>After authorization, copy the <code>code</code> parameter from the redirect URL</li>
                                <li>Visit: <code>{{ url('/test-aliexpress-token') }}?code=YOUR_CODE</code></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Resources -->
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="card-title mb-3">
                <i class="ri-links-line me-2"></i>
                Helpful Resources
            </h6>
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <a href="https://openservice.aliexpress.com/" target="_blank" class="text-decoration-none">
                        <div class="d-flex align-items-center">
                            <i class="ri-external-link-line fs-4 me-2 text-primary"></i>
                            <div>
                                <div class="fw-medium">AliExpress Open Platform</div>
                                <small class="text-muted">Developer portal</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <a href="{{ url('/test-aliexpress') }}" class="text-decoration-none">
                        <div class="d-flex align-items-center">
                            <i class="ri-test-tube-line fs-4 me-2 text-success"></i>
                            <div>
                                <div class="fw-medium">Test API Connection</div>
                                <small class="text-muted">After getting token</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ url('/test-aliexpress-all') }}" class="text-decoration-none">
                        <div class="d-flex align-items-center">
                            <i class="ri-flashlight-line fs-4 me-2 text-warning"></i>
                            <div>
                                <div class="fw-medium">Test All Endpoints</div>
                                <small class="text-muted">Full API test suite</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
