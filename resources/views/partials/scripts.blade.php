<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

<!-- endbuild -->

<!-- Vendors JS -->

<!-- Main JS -->
<script src="{{ asset('assets/js/main.js') }}"></script>

<!-- Language Switcher with Page Reload -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click event to language switcher links
    const langLinks = document.querySelectorAll('a[href*="lang.switch"]');

    langLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');

            // Show loading indicator (optional)
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Switching Language...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            // Navigate to the language switch URL
            window.location.href = url;
        });
    });
});

// Hide template customizer on page load
window.addEventListener('load', function() {
    const customizer = document.querySelector('.template-customizer');
    const customizerBtn = document.querySelector('.template-customizer-open-btn');

    if (customizer) {
        customizer.style.display = 'none';
        customizer.remove();
    }

    if (customizerBtn) {
        customizerBtn.style.display = 'none';
        customizerBtn.remove();
    }
});
</script>

<!-- Page JS -->
@stack('scripts')
