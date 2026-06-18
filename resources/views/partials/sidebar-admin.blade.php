<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="app-brand demo">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
          <span class="app-brand-logo demo">
            @if(setting('site_logo'))
                <img src="{{ asset('storage/' . setting('site_logo')) }}" alt="{{ setting('site_name', 'EcommAli') }}" style="max-height: 50px; max-width: 50px;">
       
            @endif
          </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large {{ app()->getLocale() == 'ar' ? 'me-auto' : 'ms-auto' }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M8.47365 11.7183C8.11707 12.0749 8.11707 12.6531 8.47365 13.0097L12.071 16.607C12.4615 16.9975 12.4615 17.6305 12.071 18.021C11.6805 18.4115 11.0475 18.4115 10.657 18.021L5.83009 13.1941C5.37164 12.7356 5.37164 11.9924 5.83009 11.5339L10.657 6.707C11.0475 6.31653 11.6805 6.31653 12.071 6.707C12.4615 7.09747 12.4615 7.73053 12.071 8.121L8.47365 11.7183Z"
                    fill-opacity="0.9" />
                <path
                    d="M14.3584 11.8336C14.0654 12.1266 14.0654 12.6014 14.3584 12.8944L18.071 16.607C18.4615 16.9975 18.4615 17.6305 18.071 18.021C17.6805 18.4115 17.0475 18.4115 16.657 18.021L11.6819 13.0459C11.3053 12.6693 11.3053 12.0587 11.6819 11.6821L16.657 6.707C17.0475 6.31653 17.6805 6.31653 18.071 6.707C18.4615 7.09747 18.4615 7.73053 18.071 8.121L14.3584 11.8336Z"
                    fill-opacity="0.4" />
            </svg>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Admin Dashboard -->
        <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-dashboard-line"></i>
                <div>{{ __('messages.admin_dashboard') }}</div>
            </a>
        </li>

        <!-- System Management -->
        <li class="menu-header mt-5">
            <span class="menu-header-text" >{{ __('messages.system_management') }}</span>
        </li>

        <!-- Settings -->
        <li class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <a href="{{ route('admin.settings.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-settings-3-line"></i>
                <div>{{ __('messages.settings') }}</div>
            </a>
        </li>

        <!-- Payment Gateway Settings -->
        <li class="menu-item">
            <a href="{{ route('admin.settings.index') }}#payment-gateway-settings" class="menu-link">
                <i class="menu-icon tf-icons ri-secure-payment-line"></i>
                <div>{{ __('messages.payment_gateway_settings') }}</div>
            </a>
        </li>

        <!-- Currency Management -->
        <li class="menu-item {{ request()->routeIs('admin.currencies.*') ? 'active' : '' }}">
            <a href="{{ route('admin.currencies.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-money-dollar-circle-line"></i>
                <div>{{ __('messages.currency_management') }}</div>
            </a>
        </li>

        <!-- Countries Management -->
        <li class="menu-item {{ request()->routeIs('admin.countries.*') ? 'active' : '' }}">
            <a href="{{ route('admin.countries.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-global-line"></i>
                <div>{{ app()->getLocale() == 'ar' ? 'إدارة الدول' : 'Countries' }}</div>
            </a>
        </li>

        <!-- Distributor Products Moderation -->
        <li class="menu-item {{ request()->routeIs('admin.distributor-products.*') ? 'active' : '' }}">
            <a href="{{ route('admin.distributor-products.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-shield-check-line"></i>
                <div>{{ app()->getLocale() == 'ar' ? 'مراجعة منتجات التجار' : 'Product Moderation' }}</div>
            </a>
        </li>

        <!-- Token Management -->
        <li class="menu-item {{ request()->routeIs('admin.tokens') ? 'active' : '' }}">
            <a href="{{ route('admin.tokens') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-key-2-line"></i>
                <div>{{ __('messages.token_management') }}</div>
            </a>
        </li>

        <!-- Logs -->
        <li class="menu-item {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
            <a href="{{ route('admin.logs.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-file-list-3-line"></i>
                <div>{{ __('messages.system_logs') }}</div>
            </a>
        </li>

        <!-- Subscription Management -->
        <li class="menu-item {{ request()->routeIs('admin.subscriptions.*') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri-vip-crown-line"></i>
                <div>{{ __('messages.subscription_management') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.subscriptions.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.subscriptions.index') }}" class="menu-link">
                        <div>{{ __('messages.subscription_plans') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.subscriptions.sellers') ? 'active' : '' }}">
                    <a href="{{ route('admin.subscriptions.sellers') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-store-line"></i>
                        <div>{{ app()->getLocale() == 'ar' ? 'اشتراكات التجار' : 'Sellers' }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.subscriptions.distributors') ? 'active' : '' }}">
                    <a href="{{ route('admin.subscriptions.distributors') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-truck-line"></i>
                        <div>{{ app()->getLocale() == 'ar' ? 'اشتراكات الموزعين' : 'Distributors' }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.subscriptions.users') ? 'active' : '' }}">
                    <a href="{{ route('admin.subscriptions.users') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-group-line"></i>
                        <div>{{ app()->getLocale() == 'ar' ? 'الكل' : 'All' }}</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Business Management -->
        <li class="menu-header mt-5">
            <span class="menu-header-text" >{{ __('messages.business_management') }}</span>
        </li>

        <!-- Order Management -->
        <li class="menu-item {{ request()->routeIs('admin.orders.*') && !request()->routeIs('admin.order-profits.*') ? 'active' : '' }}">
            <a href="{{ route('admin.orders.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-file-list-3-line"></i>
                <div>{{ __('messages.order_management') }}</div>
            </a>
        </li>

        <!-- Order Profits -->
        <li class="menu-item {{ request()->routeIs('admin.order-profits.*') ? 'active' : '' }}">
            <a href="{{ route('admin.order-profits.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-funds-line"></i>
                <div>{{ __('messages.order_profits') }}</div>
            </a>
        </li>

        <!-- Shipping Tracking -->
        <li class="menu-item {{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}">
            <a href="{{ route('admin.shipping.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-ship-line"></i>
                <div>{{ __('messages.shipping_tracking') }}</div>
            </a>
        </li>

        <!-- Category Management -->
        <li class="menu-item {{ request()->routeIs('admin.categories.*') || request()->routeIs('categories.*') ? 'active' : '' }}">
            <a href="{{ route('categories.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-price-tag-3-line"></i>
                <div>{{ __('messages.manage_categories') }}</div>
            </a>
        </li>


        <!-- Product Management -->
        <li class="menu-item {{ request()->routeIs('products.*') && !request()->routeIs('admin.*') && !request()->routeIs('products.search-*') ? 'active' : '' }}">
            <a href="{{ route('products.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-shopping-bag-3-line"></i>
                <div>{{ __('messages.product_management') }}</div>
            </a>
        </li>

        <!-- China Product Search -->
        <li class="menu-item {{ request()->routeIs('products.search-*') ? 'active' : '' }}">
            <a href="{{ route('products.search-page') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-search-2-line"></i>
                <div>🇨🇳 {{ __('messages.search_products') }}</div>
            </a>
        </li>

        <!-- Affiliate Marketing -->
        <li class="menu-header mt-5">
            <span class="menu-header-text">{{ app()->getLocale() == 'ar' ? 'التسويق بالعمولة' : 'Affiliate Marketing' }}</span>
        </li>

        <!-- Stores -->
        <li class="menu-item {{ request()->routeIs('admin.affiliate.stores*') ? 'active' : '' }}">
            <a href="{{ route('admin.affiliate.stores') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-store-2-line"></i>
                <div>{{ app()->getLocale() == 'ar' ? 'المتاجر' : 'Stores' }}</div>
            </a>
        </li>

        <!-- Coupons Management -->
        <li class="menu-item {{ request()->routeIs('admin.affiliate.coupons.*') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri-coupon-2-line"></i>
                <div>{{ app()->getLocale() == 'ar' ? 'الكوبونات' : 'Coupons' }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.affiliate.coupons.active') ? 'active' : '' }}">
                    <a href="{{ route('admin.affiliate.coupons.active') }}" class="menu-link">
                        <div>{{ app()->getLocale() == 'ar' ? 'الكوبونات الفعالة' : 'Active Coupons' }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.affiliate.coupons.expired') ? 'active' : '' }}">
                    <a href="{{ route('admin.affiliate.coupons.expired') }}" class="menu-link">
                        <div>{{ app()->getLocale() == 'ar' ? 'الكوبونات المنتهية' : 'Expired Coupons' }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.affiliate.coupons.create') ? 'active' : '' }}">
                    <a href="{{ route('admin.affiliate.coupons.create') }}" class="menu-link">
                        <div>{{ app()->getLocale() == 'ar' ? 'إضافة كوبون' : 'Add Coupon' }}</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- User Management -->
        <li class="menu-header mt-5">
            <span class="menu-header-text" >{{ __('messages.users') }}</span>
        </li>

        <!-- All Users -->
        <li class="menu-item {{ request()->routeIs('admin.users.*') && !request('user_type') ? 'active' : '' }}">
            <a href="{{ route('admin.users.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-group-line"></i>
                <div>{{ __('messages.all_users') }}</div>
            </a>
        </li>

        <!-- Sellers -->
        <li class="menu-item {{ request()->routeIs('admin.users.*') && request('user_type') === 'seller' ? 'active' : '' }}">
            <a href="{{ route('admin.users.index', ['user_type' => 'seller']) }}" class="menu-link">
                <i class="menu-icon tf-icons ri-store-2-line"></i>
                <div>{{ app()->getLocale() == 'ar' ? 'البائعين' : 'Sellers' }}</div>
            </a>
        </li>

        <!-- Training Videos -->
        <li class="menu-item {{ request()->routeIs('admin.training-videos.*') ? 'active' : '' }}">
            <a href="{{ route('admin.training-videos.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-play-circle-line"></i>
                <div>{{ app()->getLocale() == 'ar' ? 'الفيديوهات التدريبية' : 'Training Videos' }}</div>
            </a>
        </li>

        <!-- Support Tickets -->
        <li class="menu-item {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
            <a href="{{ route('admin.tickets.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-customer-service-2-line"></i>
                <div>{{ __('messages.support_tickets') }}</div>
            </a>
        </li>

        <!-- Wallet Management -->
        <li class="menu-item {{ request()->routeIs('admin.wallets.*') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri-wallet-3-line"></i>
                <div>{{ __('messages.wallet_management') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.wallets.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.wallets.index') }}" class="menu-link">
                        <div>{{ __('messages.all_wallets') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.wallets.withdrawals') ? 'active' : '' }}">
                    <a href="{{ route('admin.wallets.withdrawals') }}" class="menu-link">
                        <div>{{ __('messages.withdrawal_requests') }}</div>
                    </a>
                </li>
                {{-- <li class="menu-item {{ request()->routeIs('admin.wallets.transactions') ? 'active' : '' }}">
                    <a href="{{ route('admin.wallets.transactions') }}" class="menu-link">
                        <div>{{ __('messages.all_transactions') }}</div>
                    </a>
                </li> --}}
            </ul>
        </li>

        <!-- Account -->
        <li class="menu-header mt-5">
            <span class="menu-header-text" >{{ __('messages.account') }}</span>
        </li>

        <li class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <a href="{{ route('profile.edit') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-user-line"></i>
                <div>{{ __('messages.profile') }}</div>
            </a>
        </li>

        <!-- Wallet -->
        <li class="menu-item {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
            <a href="{{ route('wallet.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-wallet-3-line"></i>
                <div>{{ __('messages.my_wallet') }}</div>
            </a>
        </li>

        <!-- Logout -->
        <li class="menu-item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}" class="menu-link"
                   onclick="event.preventDefault(); this.closest('form').submit();">
                    <i class="menu-icon tf-icons ri-logout-box-line"></i>
                    <div>{{ __('messages.logout') }}</div>
                </a>
            </form>
        </li>
    </ul>
</aside>
