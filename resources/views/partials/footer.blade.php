                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl">
                        <div class="footer-container py-4">
                            <!-- Top Row: Copyright and Made With -->
                            <div class="d-flex align-items-center justify-content-between mb-3 flex-md-row flex-column">
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

                            <!-- Bottom Row: BY EVORQ TECHNOLOGIES -->
                            <div class="d-flex justify-content-center align-items-center pt-3 border-top gap-2">
                                <span style="color: #808080; font-size: 16px; font-weight: 400;">BY</span>
                                <img src="{{ asset('footer.png') }}"
                                     alt="EVORQ Logo"
                                     style="height: 50px; opacity: 0.7; transition: opacity 0.3s;"
                                     onmouseover="this.style.opacity='1'"
                                     onmouseout="this.style.opacity='0.7'">
                                <span style="color: #808080; font-size: 16px; font-weight: 400; letter-spacing: 2px;">EVORQ TECHNOLOGIES</span>
                            </div>
                        </div>
                    </div>
                </footer>
