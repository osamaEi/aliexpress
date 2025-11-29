            <nav
                class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                id="layout-navbar" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                        <i class="ri-menu-fill ri-22px"></i>
                    </a>
                </div>

                <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                    <!-- Subscription Expiry Notice for Seller -->
                    @if(auth()->user()->user_type === 'seller')
                        @php
                            $activeSubscription = auth()->user()->activeSubscription;
                            $daysRemaining = null;

                            if ($activeSubscription && $activeSubscription->end_date) {
                                $daysRemaining = now()->diffInDays($activeSubscription->end_date, false);
                                $daysRemaining = floor($daysRemaining);
                            }
                        @endphp

                        @if($daysRemaining !== null && $daysRemaining >= 0 && $daysRemaining <= 20)
                            <div class="navbar-nav align-items-center me-3">
                                <div class="nav-item">
                                    <a class="nav-link fw-medium px-3 py-2 rounded-pill"
                                       href="{{ route('subscriptions.index') }}"
                                       style="background: {{ $daysRemaining <= 5 ? '#fee2e2' : '#fef3c7' }};
                                              color: {{ $daysRemaining <= 5 ? '#991b1b' : '#92400e' }};
                                              border: 1px solid {{ $daysRemaining <= 5 ? '#fecaca' : '#fde68a' }};">
                                        <i class="ri-error-warning-line ri-18px me-2"></i>
                                        <span class="d-none d-md-inline-block">
                                            {{ app()->getLocale() == 'ar' ? 'باقي ' . $daysRemaining . ' يوم لانتهاء الاشتراك' : $daysRemaining . ' days until subscription expires' }}
                                        </span>
                                        <span class="d-inline-block d-md-none">
                                            {{ $daysRemaining }}{{ app()->getLocale() == 'ar' ? ' يوم' : 'd' }}
                                        </span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endif
                    <!-- /Subscription Expiry Notice -->

                    <ul class="navbar-nav flex-row align-items-center ms-auto">
                        <!-- Language Switcher -->
                        <li class="nav-item dropdown me-2 me-xl-0">
                            <a
                                class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow"
                                href="javascript:void(0);"
                                data-bs-toggle="dropdown">
                                <i class="ri-translate-2 ri-22px"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}"
                                       href="{{ route('lang.switch', 'en') }}">
                                        <span class="align-middle">🇬🇧 English</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ app()->getLocale() == 'ar' ? 'active' : '' }}"
                                       href="{{ route('lang.switch', 'ar') }}">
                                        <span class="align-middle">🇸🇦 العربية</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!--/ Language Switcher -->

                        <!-- Currency Switcher -->
                        <li class="nav-item dropdown me-2 me-xl-0">
                            <a
                                class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow"
                                href="javascript:void(0);"
                                data-bs-toggle="dropdown">
                                <i class="ri-money-dollar-circle-line ri-22px"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @php
                                    $currencies = \App\Models\Currency::active();
                                    $currentCurrencyCode = session('currency_code', 'USD');
                                @endphp
                                @foreach($currencies as $currency)
                                <li>
                                    <a class="dropdown-item {{ $currentCurrencyCode == $currency->code ? 'active' : '' }}"
                                       href="{{ route('currency.switch', $currency->code) }}">
                                        <span class="align-middle">{{ $currency->symbol }} {{ $currency->name }} ({{ $currency->code }})</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                        <!--/ Currency Switcher -->

                        <!-- Style Switcher -->
                        <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                            <a
                                class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow"
                                href="javascript:void(0);"
                                data-bs-toggle="dropdown">
                                <i class="ri-22px"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                                        <span class="align-middle"><i class="ri-sun-line ri-22px me-3"></i>{{ __('messages.light') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                                        <span class="align-middle"><i class="ri-moon-clear-line ri-22px me-3"></i>{{ __('messages.dark') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                                        <span class="align-middle"><i class="ri-computer-line ri-22px me-3"></i>{{ __('messages.system') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- / Style Switcher-->


                        <!-- User -->
                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                <div class="avatar avatar-online">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded-circle" />
                                    @else
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-2">
                                                <div class="avatar avatar-online">
                                                    @if(auth()->user()->avatar)
                                                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded-circle" />
                                                    @else
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <span class="fw-medium d-block small">{{ auth()->user()->name }}</span>
                                                <small class="text-muted">{{ ucfirst(auth()->user()->user_type ?? 'user') }}</small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="ri-user-3-line ri-22px me-3"></i><span class="align-middle">{{ __('messages.my_profile') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('orders.index') }}">
                                        <i class="ri-shopping-bag-line ri-22px me-3"></i><span class="align-middle">{{ __('messages.my_orders') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                </li>
                                <li>
                                    <div class="d-grid px-4 pt-2 pb-1">
                                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger d-flex w-100 align-items-center justify-content-center">
                                                <small class="align-middle">{{ __('messages.logout') }}</small>
                                                <i class="ri-logout-box-r-line ms-2 ri-16px"></i>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <!--/ User -->
                    </ul>
                </div>
            </nav>

