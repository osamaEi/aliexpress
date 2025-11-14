@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@php
    $logoPath = public_path('logo/logo.png');
    $siteName = setting('site_name', config('app.name'));
@endphp
@if (file_exists($logoPath))
<img src="{{ asset('logo/logo.png') }}" class="logo" alt="{{ $siteName }} Logo" style="height: 60px; max-height: 60px; width: auto;">
@else
<span style="font-size: 24px; font-weight: bold; color: {{ setting('primary_color', '#666cff') }};">{{ $siteName }}</span>
@endif
</a>
</td>
</tr>
