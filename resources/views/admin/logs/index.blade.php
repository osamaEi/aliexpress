@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">{{ __('System Logs') }}</h5>
                        <div>
                            <form action="{{ route('admin.logs.clear') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to clear all logs?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Clear All Logs
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Log File Selection -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Select Log File</label>
                            <select class="form-select" id="logFileSelect" onchange="changeLogFile()">
                                @foreach($logFiles as $file)
                                    <option value="{{ $file['name'] }}" {{ $selectedFile === $file['name'] ? 'selected' : '' }}>
                                        {{ $file['name'] }} ({{ $file['size'] }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Last modified: {{ $logFiles->firstWhere('name', $selectedFile)['modified'] ?? 'N/A' }}</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Filter by Level</label>
                            <select class="form-select" id="levelFilter" onchange="applyFilters()">
                                <option value="">All Levels</option>
                                <option value="ERROR" {{ $level === 'ERROR' ? 'selected' : '' }}>Error</option>
                                <option value="WARNING" {{ $level === 'WARNING' ? 'selected' : '' }}>Warning</option>
                                <option value="INFO" {{ $level === 'INFO' ? 'selected' : '' }}>Info</option>
                                <option value="DEBUG" {{ $level === 'DEBUG' ? 'selected' : '' }}>Debug</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Search Logs</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" placeholder="Search in logs..." value="{{ $search }}">
                                <button class="btn btn-primary" type="button" onclick="applyFilters()">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    @if($selectedFile)
                    <div class="mb-3">
                        <a href="{{ route('admin.logs.download', ['file' => $selectedFile]) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <form action="{{ route('admin.logs.delete', ['file' => $selectedFile]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this log file?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete File
                            </button>
                        </form>
                        <span class="text-muted ms-3">Total Lines: {{ $totalLines }}</span>
                        <span class="text-muted ms-2">| Showing: {{ count($logContent) }} entries</span>
                    </div>
                    @endif

                    <!-- Log Entries -->
                    <div class="log-container" style="max-height: 600px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 15px;">
                        @if(count($logContent) > 0)
                            @foreach($logContent as $entry)
                                <div class="log-entry mb-3 p-3 bg-white rounded shadow-sm">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge me-2
                                            @if($entry['level'] === 'ERROR') bg-danger
                                            @elseif($entry['level'] === 'WARNING') bg-warning
                                            @elseif($entry['level'] === 'INFO') bg-info
                                            @elseif($entry['level'] === 'DEBUG') bg-secondary
                                            @else bg-primary
                                            @endif
                                        ">
                                            {{ $entry['level'] }}
                                        </span>
                                        <small class="text-muted">{{ $entry['timestamp'] }}</small>
                                    </div>
                                    <div class="log-message" style="font-family: monospace; font-size: 0.9rem; word-break: break-all;">
                                        {{ $entry['message'] }}
                                    </div>
                                    @if(count($entry['stack']) > 0)
                                        <details class="mt-2">
                                            <summary class="text-primary" style="cursor: pointer;">
                                                <small>Show Stack Trace ({{ count($entry['stack']) }} lines)</small>
                                            </summary>
                                            <pre class="mt-2 p-2 bg-light rounded" style="font-size: 0.8rem; max-height: 300px; overflow-y: auto;">{{ implode("\n", $entry['stack']) }}</pre>
                                        </details>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No log entries found</p>
                                @if($search || $level)
                                    <small>Try adjusting your filters</small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeLogFile() {
    const file = document.getElementById('logFileSelect').value;
    const url = new URL(window.location.href);
    url.searchParams.set('file', file);
    url.searchParams.delete('search');
    url.searchParams.delete('level');
    window.location.href = url.toString();
}

function applyFilters() {
    const file = document.getElementById('logFileSelect').value;
    const search = document.getElementById('searchInput').value;
    const level = document.getElementById('levelFilter').value;

    const url = new URL(window.location.href);
    url.searchParams.set('file', file);

    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }

    if (level) {
        url.searchParams.set('level', level);
    } else {
        url.searchParams.delete('level');
    }

    window.location.href = url.toString();
}

// Allow Enter key to trigger search
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>

<style>
.log-entry {
    transition: transform 0.2s;
}

.log-entry:hover {
    transform: translateX(5px);
}

.log-container::-webkit-scrollbar {
    width: 8px;
}

.log-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.log-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.log-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@endsection
