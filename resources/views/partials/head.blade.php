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

        /* Dynamic Colors from Settings */
        @php
            $primaryColor = setting('primary_color', '#666cff');
            $primaryLightColor = setting('primary_light_color', '#e7e7ff');
            $primaryHoverColor = setting('btn_primary_hover_color', '#4a1603');
            $primaryActiveColor = setting('btn_primary_active_color', '#3d1202');

            // Secondary colors
            $secondaryColor = setting('secondary_color', '#6d788d');
            $secondaryHoverColor = setting('btn_secondary_hover_color', '#5a6376');
            $secondaryActiveColor = setting('btn_secondary_active_color', '#4a5365');

            // Success colors
            $successColor = setting('btn_success_color', '#ff6f00');
            $successHoverColor = setting('btn_success_hover_color', '#e56300');
            $successActiveColor = setting('btn_success_active_color', '#cc5700');

            // Warning colors
            $warningColor = setting('btn_warning_color', '#fdb528');
            $warningHoverColor = setting('btn_warning_hover_color', '#e0a800');
            $warningActiveColor = setting('btn_warning_active_color', '#d39e00');

            // Danger colors
            $dangerColor = setting('btn_danger_color', '#ff4d49');
            $dangerHoverColor = setting('btn_danger_hover_color', '#e63946');
            $dangerActiveColor = setting('btn_danger_active_color', '#cc2936');

            // Info colors
            $infoColor = setting('btn_info_color', '#000000');
            $infoHoverColor = setting('btn_info_hover_color', '#333333');
            $infoActiveColor = setting('btn_info_active_color', '#1a1a1a');

            // Calculate RGB values
            $primaryColorRgb = sscanf($primaryColor, "#%02x%02x%02x");
            $primaryLightColorRgb = sscanf($primaryLightColor, "#%02x%02x%02x");
            $secondaryColorRgb = sscanf($secondaryColor, "#%02x%02x%02x");
            $successColorRgb = sscanf($successColor, "#%02x%02x%02x");
            $warningColorRgb = sscanf($warningColor, "#%02x%02x%02x");
            $dangerColorRgb = sscanf($dangerColor, "#%02x%02x%02x");
            $infoColorRgb = sscanf($infoColor, "#%02x%02x%02x");
        @endphp

        :root {
            --bs-primary: {{ $primaryColor }};
            --bs-primary-rgb: {{ $primaryColorRgb[0] }}, {{ $primaryColorRgb[1] }}, {{ $primaryColorRgb[2] }};
            --bs-primary-light: {{ $primaryLightColor }};
            --bs-primary-light-rgb: {{ $primaryLightColorRgb[0] }}, {{ $primaryLightColorRgb[1] }}, {{ $primaryLightColorRgb[2] }};

            --bs-secondary: {{ $secondaryColor }};
            --bs-secondary-rgb: {{ $secondaryColorRgb[0] }}, {{ $secondaryColorRgb[1] }}, {{ $secondaryColorRgb[2] }};

            --bs-success: {{ $successColor }};
            --bs-success-rgb: {{ $successColorRgb[0] }}, {{ $successColorRgb[1] }}, {{ $successColorRgb[2] }};

            --bs-warning: {{ $warningColor }};
            --bs-warning-rgb: {{ $warningColorRgb[0] }}, {{ $warningColorRgb[1] }}, {{ $warningColorRgb[2] }};

            --bs-danger: {{ $dangerColor }};
            --bs-danger-rgb: {{ $dangerColorRgb[0] }}, {{ $dangerColorRgb[1] }}, {{ $dangerColorRgb[2] }};

            --bs-info: {{ $infoColor }};
            --bs-info-rgb: {{ $infoColorRgb[0] }}, {{ $infoColorRgb[1] }}, {{ $infoColorRgb[2] }};
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
            background-color: {{ $primaryHoverColor }} !important;
            border-color: {{ $primaryHoverColor }} !important;
        }

        .btn-primary:active,
        .btn-primary.active {
            background-color: {{ $primaryActiveColor }} !important;
            border-color: {{ $primaryActiveColor }} !important;
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

        /* Success Button Colors */
        .btn-success,
        .badge.bg-success,
        .bg-success {
            background-color: {{ $successColor }} !important;
            border-color: {{ $successColor }} !important;
        }

        .text-success {
            color: {{ $successColor }} !important;
        }

        .btn-success:hover,
        .btn-success:focus {
            background-color: {{ $successHoverColor }} !important;
            border-color: {{ $successHoverColor }} !important;
        }

        .btn-success:active,
        .btn-success.active {
            background-color: {{ $successActiveColor }} !important;
            border-color: {{ $successActiveColor }} !important;
        }

        .btn-outline-success {
            color: {{ $successColor }} !important;
            border-color: {{ $successColor }} !important;
        }

        .btn-outline-success:hover {
            background-color: {{ $successColor }} !important;
            border-color: {{ $successColor }} !important;
            color: #fff !important;
        }

        .alert-success {
            background-color: {{ $successColor }}1a !important;
            border-color: {{ $successColor }}33 !important;
            color: {{ $successColor }} !important;
        }

        /* Warning Button Colors */
        .btn-warning,
        .badge.bg-warning,
        .bg-warning {
            background-color: {{ $warningColor }} !important;
            border-color: {{ $warningColor }} !important;
        }

        .text-warning {
            color: {{ $warningColor }} !important;
        }

        .btn-warning:hover,
        .btn-warning:focus {
            background-color: {{ $warningHoverColor }} !important;
            border-color: {{ $warningHoverColor }} !important;
        }

        .btn-warning:active,
        .btn-warning.active {
            background-color: {{ $warningActiveColor }} !important;
            border-color: {{ $warningActiveColor }} !important;
        }

        .btn-outline-warning {
            color: {{ $warningColor }} !important;
            border-color: {{ $warningColor }} !important;
        }

        .btn-outline-warning:hover {
            background-color: {{ $warningColor }} !important;
            border-color: {{ $warningColor }} !important;
            color: #fff !important;
        }

        .alert-warning {
            background-color: {{ $warningColor }}1a !important;
            border-color: {{ $warningColor }}33 !important;
            color: {{ $warningColor }} !important;
        }

        /* Danger Button Colors */
        .btn-danger,
        .badge.bg-danger,
        .bg-danger {
            background-color: {{ $dangerColor }} !important;
            border-color: {{ $dangerColor }} !important;
        }

        .text-danger {
            color: {{ $dangerColor }} !important;
        }

        .btn-danger:hover,
        .btn-danger:focus {
            background-color: {{ $dangerHoverColor }} !important;
            border-color: {{ $dangerHoverColor }} !important;
        }

        .btn-danger:active,
        .btn-danger.active {
            background-color: {{ $dangerActiveColor }} !important;
            border-color: {{ $dangerActiveColor }} !important;
        }

        .btn-outline-danger {
            color: {{ $dangerColor }} !important;
            border-color: {{ $dangerColor }} !important;
        }

        .btn-outline-danger:hover {
            background-color: {{ $dangerColor }} !important;
            border-color: {{ $dangerColor }} !important;
            color: #fff !important;
        }

        .alert-danger {
            background-color: {{ $dangerColor }}1a !important;
            border-color: {{ $dangerColor }}33 !important;
            color: {{ $dangerColor }} !important;
        }

        /* Info Button Colors */
        .btn-info,
        .badge.bg-info,
        .bg-info {
            background-color: {{ $infoColor }} !important;
            border-color: {{ $infoColor }} !important;
        }

        .text-info {
            color: {{ $infoColor }} !important;
        }

        .btn-info:hover,
        .btn-info:focus {
            background-color: {{ $infoHoverColor }} !important;
            border-color: {{ $infoHoverColor }} !important;
        }

        .btn-info:active,
        .btn-info.active {
            background-color: {{ $infoActiveColor }} !important;
            border-color: {{ $infoActiveColor }} !important;
        }

        .btn-outline-info {
            color: {{ $infoColor }} !important;
            border-color: {{ $infoColor }} !important;
        }

        .btn-outline-info:hover {
            background-color: {{ $infoColor }} !important;
            border-color: {{ $infoColor }} !important;
            color: #fff !important;
        }

        .alert-info {
            background-color: {{ $infoColor }}1a !important;
            border-color: {{ $infoColor }}33 !important;
            color: {{ $infoColor }} !important;
        }

        /* Secondary Button Colors */
        .btn-secondary,
        .badge.bg-secondary,
        .bg-secondary {
            background-color: {{ $secondaryColor }} !important;
            border-color: {{ $secondaryColor }} !important;
        }

        .text-secondary {
            color: {{ $secondaryColor }} !important;
        }

        .btn-secondary:hover,
        .btn-secondary:focus {
            background-color: {{ $secondaryHoverColor }} !important;
            border-color: {{ $secondaryHoverColor }} !important;
        }

        .btn-secondary:active,
        .btn-secondary.active {
            background-color: {{ $secondaryActiveColor }} !important;
            border-color: {{ $secondaryActiveColor }} !important;
        }

        .btn-outline-secondary {
            color: {{ $secondaryColor }} !important;
            border-color: {{ $secondaryColor }} !important;
        }

        .btn-outline-secondary:hover {
            background-color: {{ $secondaryColor }} !important;
            border-color: {{ $secondaryColor }} !important;
            color: #fff !important;
        }

        .alert-secondary {
            background-color: {{ $secondaryColor }}1a !important;
            border-color: {{ $secondaryColor }}33 !important;
            color: {{ $secondaryColor }} !important;
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
