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

        /* Dynamic Primary Color from Settings */
        @php
            $primaryColor = setting('primary_color', '#666cff');
            $primaryLightColor = setting('primary_light_color', '#e7e7ff');
            // Calculate RGB values for both colors
            $primaryColorRgb = sscanf($primaryColor, "#%02x%02x%02x");
            $primaryLightColorRgb = sscanf($primaryLightColor, "#%02x%02x%02x");
        @endphp

        :root {
            --bs-primary: {{ $primaryColor }};
            --bs-primary-rgb: {{ $primaryColorRgb[0] }}, {{ $primaryColorRgb[1] }}, {{ $primaryColorRgb[2] }};
            --bs-primary-light: {{ $primaryLightColor }};
            --bs-primary-light-rgb: {{ $primaryLightColorRgb[0] }}, {{ $primaryLightColorRgb[1] }}, {{ $primaryLightColorRgb[2] }};
        }

        /* Override template colors */
        .btn-primary,
        .badge.bg-primary,
        .bg-primary {
            background-color: {{ $primaryColor }} !important;
            border-color: {{ $primaryColor }} !important;
        }

        .text-primary {
            color: {{ $primaryColor }} !important;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: {{ $primaryColor }}dd !important;
            border-color: {{ $primaryColor }}dd !important;
        }

        /* Use light primary color for label badges and subtle backgrounds */
        .badge.bg-label-primary {
            background-color: {{ $primaryLightColor }} !important;
            color: {{ $primaryColor }} !important;
        }

        .btn-outline-primary {
            color: {{ $primaryColor }} !important;
            border-color: {{ $primaryColor }} !important;
        }

        .btn-outline-primary:hover {
            background-color: {{ $primaryColor }} !important;
            border-color: {{ $primaryColor }} !important;
            color: #fff !important;
        }

        .form-check-input:checked {
            background-color: {{ $primaryColor }} !important;
            border-color: {{ $primaryColor }} !important;
        }

        /* Use light primary for active menu items background */
        .menu-item.active > .menu-link {
            background-color: {{ $primaryLightColor }} !important;
            color: {{ $primaryColor }} !important;
        }

        /* Light background utilities */
        .bg-primary-light {
            background-color: {{ $primaryLightColor }} !important;
        }

        /* Alerts and info boxes with light primary */
        .alert-primary {
            background-color: {{ $primaryLightColor }} !important;
            border-color: {{ $primaryColor }}33 !important;
            color: {{ $primaryColor }} !important;
        }

        /* Card highlights */
        .card-primary-light {
            border-left: 3px solid {{ $primaryColor }} !important;
            background-color: {{ $primaryLightColor }}80 !important;
        }

        a {
            color: {{ $primaryColor }};
        }

        a:hover {
            color: {{ $primaryColor }}dd;
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
