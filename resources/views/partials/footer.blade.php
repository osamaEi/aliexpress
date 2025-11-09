                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl">
                        <div
                            class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                            <div class="text-body mb-2 mb-md-0 d-flex align-items-center gap-2">
                                @if(setting('site_logo'))
                                    <img src="{{ asset('storage/' . setting('site_logo')) }}"
                                         alt="{{ setting('site_name', 'EcommAli') }}"
                                         style="max-height: 30px; max-width: 30px;">
                                @endif
                                ©
                                <script>
                                    document.write(new Date().getFullYear());
                                </script>
                                {{ setting('site_name', 'EcommAli') }}
                            </div>
                            <div class="text-body">
                                {{ __('messages.made_with') }} <span class="text-danger"><i class="tf-icons ri-heart-fill"></i></span> {{ __('messages.by') }}
                                <a href="https://coderarab.com" target="_blank" class="footer-link fw-semibold">Coder Arab</a>
                            </div>
                        </div>
                    </div>
                </footer>
