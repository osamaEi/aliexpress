<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>{{ setting('site_name', 'EcommAli') }} - Dropshipping Platform</title>

    <meta name="description" content="{{ setting('site_description', '') }}" />
    <meta name="keywords" content="{{ setting('site_keywords', '') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon -->
    @if(setting('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . setting('site_favicon')) }}" />
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/remixicon/remixicon.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />

    <!-- Menu waves for no-customizer fix -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />

    <!-- Core CSS -->
    @if(app()->getLocale() === 'ar')
        <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" class="template-customizer-theme-css" />
    @else
        <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />

    <!-- Page CSS -->

    <!-- Custom CSS -->
    <style>
        /* Apply Cairo font for Arabic content */
        [dir="rtl"],
        [dir="rtl"] body,
        [dir="rtl"] .menu-inner,
        [lang="ar"] {
            font-family: 'Cairo', 'Inter', sans-serif !important;
        }

        /* English content keeps Inter font */
        [dir="ltr"] {
            font-family: 'Inter', sans-serif;
        }

        /* Custom Color Scheme */
        @php
            // Primary color: Deep Brown #561C04
            $primaryColor = setting('primary_color', '#561C04');
            $primaryHover = '#6e2305'; // Lighter brown for hover
            $primaryActive = '#3e1403'; // Darker brown for active
            $primaryLight = '#f5e6d3'; // Very light brown/cream

            // Secondary color: Dark Orange #CC5500
            $secondaryColor = '#CC5500';
            $secondaryHover = '#e66100'; // Lighter orange for hover
            $secondaryActive = '#b34c00'; // Darker orange for active
            $secondaryLight = '#ffe5cc'; // Light orange

            // Calculate RGB values
            $primaryColorRgb = sscanf($primaryColor, "#%02x%02x%02x");
            $secondaryColorRgb = sscanf($secondaryColor, "#%02x%02x%02x");
        @endphp

        :root {
            /* Primary Colors */
            --bs-primary: {{ $primaryColor }};
            --bs-primary-rgb: {{ $primaryColorRgb[0] }}, {{ $primaryColorRgb[1] }}, {{ $primaryColorRgb[2] }};
            --bs-primary-hover: {{ $primaryHover }};
            --bs-primary-active: {{ $primaryActive }};
            --bs-primary-light: {{ $primaryLight }};

            /* Secondary Colors */
            --bs-secondary: {{ $secondaryColor }};
            --bs-secondary-rgb: {{ $secondaryColorRgb[0] }}, {{ $secondaryColorRgb[1] }}, {{ $secondaryColorRgb[2] }};
            --bs-secondary-hover: {{ $secondaryHover }};
            --bs-secondary-active: {{ $secondaryActive }};
            --bs-secondary-light: {{ $secondaryLight }};
        }

        /* ========== PRIMARY COLOR STYLES ========== */
        .btn-primary,
        .badge.bg-primary,
        .bg-primary {
            background-color: {{ $primaryColor }} !important;
            border-color: {{ $primaryColor }} !important;
            color: #fff !important;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: {{ $primaryHover }} !important;
            border-color: {{ $primaryHover }} !important;
            color: #fff !important;
        }

        .btn-primary:active,
        .btn-primary.active {
            background-color: {{ $primaryActive }} !important;
            border-color: {{ $primaryActive }} !important;
            color: #fff !important;
        }

        .text-primary {
            color: {{ $primaryColor }} !important;
        }

        .btn-outline-primary {
            color: {{ $primaryColor }} !important;
            border-color: {{ $primaryColor }} !important;
            background-color: transparent !important;
        }

        .btn-outline-primary:hover {
            background-color: {{ $primaryColor }} !important;
            border-color: {{ $primaryColor }} !important;
            color: #fff !important;
        }

        .btn-outline-primary:active,
        .btn-outline-primary.active {
            background-color: {{ $primaryActive }} !important;
            border-color: {{ $primaryActive }} !important;
            color: #fff !important;
        }

        /* Primary Light Backgrounds */
        .badge.bg-label-primary {
            background-color: {{ $primaryLight }} !important;
            color: {{ $primaryColor }} !important;
        }

        .bg-primary-light {
            background-color: {{ $primaryLight }} !important;
        }

        .alert-primary {
            background-color: {{ $primaryLight }} !important;
            border-color: {{ $primaryColor }}33 !important;
            color: {{ $primaryColor }} !important;
        }

        /* ========== SECONDARY COLOR STYLES ========== */
        .btn-secondary {
            background-color: {{ $secondaryColor }} !important;
            border-color: {{ $secondaryColor }} !important;
            color: #fff !important;
        }

        .btn-secondary:hover,
        .btn-secondary:focus {
            background-color: {{ $secondaryHover }} !important;
            border-color: {{ $secondaryHover }} !important;
            color: #fff !important;
        }

        .btn-secondary:active,
        .btn-secondary.active {
            background-color: {{ $secondaryActive }} !important;
            border-color: {{ $secondaryActive }} !important;
            color: #fff !important;
        }

        .text-secondary {
            color: {{ $secondaryColor }} !important;
        }

        .bg-secondary {
            background-color: {{ $secondaryColor }} !important;
            color: #fff !important;
        }

        .btn-outline-secondary {
            color: {{ $secondaryColor }} !important;
            border-color: {{ $secondaryColor }} !important;
            background-color: transparent !important;
        }

        .btn-outline-secondary:hover {
            background-color: {{ $secondaryColor }} !important;
            border-color: {{ $secondaryColor }} !important;
            color: #fff !important;
        }

        .btn-outline-secondary:active,
        .btn-outline-secondary.active {
            background-color: {{ $secondaryActive }} !important;
            border-color: {{ $secondaryActive }} !important;
            color: #fff !important;
        }

        /* Secondary Light Backgrounds */
        .badge.bg-label-secondary {
            background-color: {{ $secondaryLight }} !important;
            color: {{ $secondaryColor }} !important;
        }

        .bg-secondary-light {
            background-color: {{ $secondaryLight }} !important;
        }

        /* ========== MENU & NAVIGATION ========== */
        .menu-item.active > .menu-link {
            background-color: {{ $primaryLight }} !important;
            color: {{ $primaryColor }} !important;
        }

        .menu-link:hover {
            background-color: {{ $primaryLight }}80 !important;
            color: {{ $primaryColor }} !important;
        }

        .menu-sub-item.active > .menu-link {
            color: {{ $secondaryColor }} !important;
        }

        /* ========== FORMS & INPUTS ========== */
        .form-check-input:checked {
            background-color: {{ $primaryColor }} !important;
            border-color: {{ $primaryColor }} !important;
        }

        .form-check-input:focus {
            border-color: {{ $primaryHover }} !important;
            box-shadow: 0 0 0 0.25rem {{ $primaryColor }}40 !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: {{ $primaryColor }} !important;
            box-shadow: 0 0 0 0.25rem {{ $primaryColor }}25 !important;
        }

        /* ========== LINKS ========== */
        a {
            color: {{ $primaryColor }};
        }

        a:hover {
            color: {{ $primaryHover }};
        }

        a:active {
            color: {{ $primaryActive }};
        }

        /* ========== BADGES ========== */
        .badge.bg-primary {
            background-color: {{ $primaryColor }} !important;
        }

        .badge.bg-secondary {
            background-color: {{ $secondaryColor }} !important;
        }

        /* ========== CARDS ========== */
        .card-primary {
            border-left: 3px solid {{ $primaryColor }} !important;
        }

        .card-secondary {
            border-left: 3px solid {{ $secondaryColor }} !important;
        }

        .card-primary-light {
            border-left: 3px solid {{ $primaryColor }} !important;
            background-color: {{ $primaryLight }}80 !important;
        }

        /* ========== PAGINATION ========== */
        .page-link {
            color: {{ $primaryColor }} !important;
        }

        .page-link:hover {
            color: {{ $primaryHover }} !important;
            background-color: {{ $primaryLight }} !important;
            border-color: {{ $primaryColor }} !important;
        }

        .page-item.active .page-link {
            background-color: {{ $primaryColor }} !important;
            border-color: {{ $primaryColor }} !important;
        }

        /* ========== PROGRESS BARS ========== */
        .progress-bar {
            background-color: {{ $primaryColor }} !important;
        }

        .progress-bar.bg-secondary {
            background-color: {{ $secondaryColor }} !important;
        }

        /* ========== TABS ========== */
        .nav-tabs .nav-link.active {
            color: {{ $primaryColor }} !important;
            border-bottom-color: {{ $primaryColor }} !important;
        }

        .nav-tabs .nav-link:hover {
            color: {{ $primaryHover }} !important;
        }

        .nav-pills .nav-link.active {
            background-color: {{ $primaryColor }} !important;
        }

        .nav-pills .nav-link:hover {
            background-color: {{ $primaryLight }} !important;
            color: {{ $primaryColor }} !important;
        }

        /* ========== DROPDOWNS ========== */
        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: {{ $primaryLight }} !important;
            color: {{ $primaryColor }} !important;
        }

        .dropdown-item.active {
            background-color: {{ $primaryColor }} !important;
            color: #fff !important;
        }

        /* ========== ALERTS ========== */
        .alert-secondary {
            background-color: {{ $secondaryLight }} !important;
            border-color: {{ $secondaryColor }}33 !important;
            color: {{ $secondaryActive }} !important;
        }

        /* ========== SIDEBAR GRADIENTS ========== */
        .bg-gradient-primary {
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $primaryActive }} 100%) !important;
        }

        .bg-gradient-secondary {
            background: linear-gradient(135deg, {{ $secondaryColor }} 0%, {{ $secondaryActive }} 100%) !important;
        }

        /* Hide template customizer button */
        .template-customizer,
        .template-customizer-open-btn {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        /* Ensure proper sidebar positioning based on language direction */
        /* RTL (Arabic) - Sidebar on the RIGHT */
        html[dir="rtl"] .layout-menu,
        [dir="rtl"] .layout-menu,
        body[dir="rtl"] .layout-menu {
            right: 0 !important;
            left: auto !important;
        }

        html[dir="rtl"] .layout-page,
        [dir="rtl"] .layout-page,
        body[dir="rtl"] .layout-page {
            margin-right: 260px !important;
            margin-left: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /* LTR (English) - Sidebar on the LEFT */
        html[dir="ltr"] .layout-menu,
        [dir="ltr"] .layout-menu,
        body[dir="ltr"] .layout-menu {
            left: 0 !important;
            right: auto !important;
        }

        html[dir="ltr"] .layout-page,
        [dir="ltr"] .layout-page,
        body[dir="ltr"] .layout-page {
            margin-left: 260px !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /* Smooth transition when switching language/direction */
        .layout-menu {
            transition: left 0.3s ease, right 0.3s ease !important;
        }

        .layout-page {
            transition: margin-left 0.3s ease, margin-right 0.3s ease !important;
        }

        /* Responsive adjustments */
        @media (max-width: 1199.98px) {
            html[dir="rtl"] .layout-page,
            html[dir="ltr"] .layout-page {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
        }
    </style>

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>
