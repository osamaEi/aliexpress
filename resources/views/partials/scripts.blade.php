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
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('assets/js/main.js') }}"></script>

<!-- Global SweetAlert helpers + flash messages + confirm handler -->
<script>
(function () {
    const isAr = '{{ app()->getLocale() }}' === 'ar';

    // Brand-coloured defaults applied to every SweetAlert dialog
    if (typeof Swal !== 'undefined') {
        window.swalBase = Swal.mixin({
            confirmButtonColor: '#561C04',
            cancelButtonColor: '#6c757d',
            buttonsStyling: true,
            reverseButtons: isAr,
        });

        // Toast for quick success/error notifications
        window.swalToast = Swal.mixin({
            toast: true,
            position: isAr ? 'top-start' : 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (t) => {
                t.addEventListener('mouseenter', Swal.stopTimer);
                t.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    }

    // Convenience global helpers usable from any page
    window.showSuccess = (msg, title) => window.swalToast?.fire({ icon: 'success', title: title || msg, text: title ? msg : undefined });
    window.showError   = (msg, title) => window.swalBase?.fire({ icon: 'error', title: title || (isAr ? 'خطأ' : 'Error'), text: msg });
    window.showInfo    = (msg, title) => window.swalToast?.fire({ icon: 'info', title: title || msg, text: title ? msg : undefined });

    /**
     * Global confirm helper. Returns a Promise<boolean>.
     * Usage: const ok = await confirmAction('Delete this?'); if (ok) {...}
     */
    window.confirmAction = function (message, options = {}) {
        if (typeof Swal === 'undefined') {
            return Promise.resolve(window.confirm(message));
        }
        return window.swalBase.fire({
            title: options.title || (isAr ? 'هل أنت متأكد؟' : 'Are you sure?'),
            text: message || '',
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonText: options.confirmText || (isAr ? 'نعم، تأكيد' : 'Yes, confirm'),
            cancelButtonText: options.cancelText || (isAr ? 'إلغاء' : 'Cancel'),
        }).then(r => r.isConfirmed);
    };

    document.addEventListener('DOMContentLoaded', function () {
        // 1) Flash messages from the server -> SweetAlert
        @if(session('success'))
            window.showSuccess(@json(session('success')));
        @endif
        @if(session('error'))
            window.showError(@json(session('error')));
        @endif
        @if(session('warning'))
            window.showInfo(@json(session('warning')));
        @endif
        @if($errors->any())
            window.swalBase?.fire({
                icon: 'error',
                title: isAr ? 'يرجى تصحيح الأخطاء' : 'Please fix the errors',
                html: '<ul style="text-align:{{ app()->getLocale() == "ar" ? "right" : "left" }};margin:0;padding-{{ app()->getLocale() == "ar" ? "right" : "left" }}:18px;">{!! collect($errors->all())->map(fn($e) => "<li>".e($e)."</li>")->implode('') !!}</ul>'
            });
        @endif

        // 1b) Upgrade legacy inline `onsubmit="return confirm('...')"` forms to SweetAlert.
        // We strip the inline handler and route submission through confirmAction().
        document.querySelectorAll('form[onsubmit]').forEach(function (form) {
            const handler = form.getAttribute('onsubmit') || '';
            const m = handler.match(/confirm\((['"`])([\s\S]*?)\1\)/);
            if (!m) return;
            const msg = m[2];
            form.removeAttribute('onsubmit');
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === '1') return; // already approved
                e.preventDefault();
                confirmAction(msg).then(ok => {
                    if (ok) { form.dataset.confirmed = '1'; form.submit(); }
                });
            });
        });

        // 1c) Upgrade legacy inline `onclick="...confirm('...')..."` buttons/links.
        document.querySelectorAll('[onclick]').forEach(function (el) {
            const handler = el.getAttribute('onclick') || '';
            const m = handler.match(/confirm\((['"`])([\s\S]*?)\1\)/);
            if (!m) return;
            // Only handle the simple guard pattern: if(!confirm(..)) return; / return confirm(..)
            const msg = m[2];
            const rest = handler.replace(/if\s*\(\s*!?confirm\([^)]*\)\s*\)\s*\{?\s*return[^;]*;?\s*\}?/g, '')
                                 .replace(/return\s+confirm\([^)]*\)\s*;?/g, '')
                                 .trim();
            el.removeAttribute('onclick');
            el.addEventListener('click', function (e) {
                e.preventDefault();
                confirmAction(msg).then(ok => {
                    if (!ok) return;
                    if (rest) {
                        try { new Function(rest).call(el); } catch (_) {}
                    } else if (el.closest('form')) {
                        el.closest('form').submit();
                    } else if (el.href) {
                        window.location.href = el.href;
                    }
                });
            });
        });

        // 2) Any element with [data-confirm] -> SweetAlert before its default action
        document.body.addEventListener('click', function (e) {
            const el = e.target.closest('[data-confirm]');
            if (!el) return;
            // Already confirmed -> let it through
            if (el.dataset.confirmed === '1') return;

            e.preventDefault();
            e.stopPropagation();

            const message = el.getAttribute('data-confirm');
            confirmAction(message, {
                title: el.getAttribute('data-confirm-title') || undefined,
                confirmText: el.getAttribute('data-confirm-yes') || undefined,
                icon: el.getAttribute('data-confirm-icon') || 'warning',
            }).then(ok => {
                if (!ok) return;
                el.dataset.confirmed = '1';
                // Re-dispatch the action
                if (el.tagName === 'FORM') {
                    el.submit();
                } else if (el.closest('form') && (el.type === 'submit' || el.tagName === 'BUTTON')) {
                    el.closest('form').submit();
                } else if (el.href) {
                    window.location.href = el.href;
                } else {
                    el.click();
                }
            });
        }, true);
    });
})();
</script>

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
