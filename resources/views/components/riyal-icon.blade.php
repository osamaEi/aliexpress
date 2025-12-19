@props(['width' => '20', 'height' => '20', 'class' => ''])

<svg xmlns="http://www.w3.org/2000/svg" width="{{ $width }}" height="{{ $height }}" viewBox="0 0 24 24" fill="none" class="inline-block {{ $class }}" style="vertical-align: middle;">
    <!-- Saudi Riyal Symbol (ر.س) stylized -->
    <g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <!-- الحرف ر -->
        <path d="M8 7V14C8 15.5 9 17 11 17H13" />
        <circle cx="13" cy="14" r="0.8" fill="currentColor" />

        <!-- خطوط العملة المتقاطعة -->
        <line x1="6" y1="10" x2="16" y2="10" stroke-width="1.2" />
        <line x1="6" y1="13" x2="16" y2="13" stroke-width="1.2" />
    </g>
</svg>
