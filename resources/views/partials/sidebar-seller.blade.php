<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="app-brand demo" style="justify-content: space-between;">
        <a href="{{ route('seller.dashboard') }}" class="app-brand-link">
          <span class="app-brand-logo demo">
            @if(setting('site_logo'))
                <img src="{{ asset('storage/' . setting('site_logo')) }}" alt="{{ setting('site_name', 'EcommAli') }}" style="max-height: 50px; max-width: 50px;">

            @endif
          </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large">
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
        <!-- Dashboard -->
        <li class="menu-item {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
            <a href="{{ route('seller.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-home-smile-line"></i>
                <div>{{ __('messages.dashboard') }}</div>
            </a>
        </li>

        <!-- Products Section -->
        <li class="menu-header mt-5">
            <span class="menu-header-text" >{{ __('messages.product_management') }}</span>
        </li>

        <!-- Products -->
        <!-- <li class="menu-item {{ request()->routeIs('products.*') && !request()->routeIs('products.search-*') && !request()->routeIs('products.my-assigned') ? 'active' : '' }}">
            <a href="{{ route('products.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-shopping-bag-3-line"></i>
                <div>{{ __('messages.products') }}</div>
            </a>
        </li> -->

        <!-- Categories -->
        <li class="menu-item {{ request()->routeIs('categories.*') || request()->routeIs('seller.request-category-assignment') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri-price-tag-3-line"></i>
                <div>{{ __('messages.categories') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('seller.request-category-assignment') ? 'active' : '' }}">
                    <a href="{{ route('seller.request-category-assignment') }}" class="menu-link">
                        <div>{{ __('messages.category_assignment') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('categories.index') ? 'active' : '' }}">
                    <a href="{{ route('categories.index') }}" class="menu-link">
                        <div>{{ __('messages.all_categories') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Product Import -->
        <li class="menu-item {{ request()->routeIs('products.search-*') || request()->routeIs('products.aliexpress.*') || request()->routeIs('products.my-assigned') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri-shopping-cart-line"></i>
                <div>{{ __('messages.product_import') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('products.search-*') ? 'active' : '' }}">
                    <a href="{{ route('products.search-page') }}" class="menu-link">
                        <div>{{ __('messages.search_products') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('products.my-assigned') ? 'active' : '' }}">
                    <a href="{{ route('products.my-assigned') }}" class="menu-link">
                        <div>{{ __('messages.my_assigned_products') }}</div>
                    </a>
                </li>


            </ul>
        </li>

        <!-- Orders Section -->
        <li class="menu-header mt-5">
            <span class="menu-header-text" >{{ __('messages.order_management') }}</span>
        </li>

        <!-- Orders -->
        <li class="menu-item {{ request()->routeIs('orders.*') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri-file-list-3-line"></i>
                <div>{{ __('messages.orders') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('orders.index') ? 'active' : '' }}">
                    <a href="{{ route('orders.index') }}" class="menu-link">
                        <div>{{ __('messages.all_orders') }}</div>
                    </a>
                </li>

                <!-- <li class="menu-item {{ request()->routeIs('orders.create') ? 'active' : '' }}">
                    <a href="{{ route('orders.create') }}" class="menu-link">
                        <div>{{ __('messages.create_order') }}</div>
                    </a>
                </li> -->
            </ul>
        </li>

        <!-- Shipping Tracking -->
        <!-- <li class="menu-item {{ request()->routeIs('seller.shipping.*') ? 'active' : '' }}">
            <a href="{{ route('seller.shipping.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-ship-line"></i>
                <div>{{ __('messages.shipping_tracking') }}</div>
            </a>
        </li> -->

        <!-- Financial Section -->
        <li class="menu-header mt-5">
            <span class="menu-header-text">{{ __('messages.financial_section') }}</span>
        </li>

        <!-- Profit Settings -->
        <li class="menu-item {{ request()->routeIs('seller.profit-settings.*') ? 'active' : '' }}">
            <a href="{{ route('seller.profit-settings.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-percent-line"></i>
                <div>{{ __('messages.profit_settings') }}</div>
            </a>
        </li>

        <!-- Wallet -->
        <li class="menu-item {{ request()->routeIs('wallet.*') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri-wallet-3-line"></i>
                <div>{{ __('messages.my_wallet') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('wallet.index') ? 'active' : '' }}">
                    <a href="{{ route('wallet.index') }}" class="menu-link">
                        <div>{{ __('messages.wallet_overview') }}</div>
                    </a>
                </li>
                
                <li class="menu-item {{ request()->routeIs('wallet.transactions') ? 'active' : '' }}">
                    <a href="{{ route('wallet.transactions') }}" class="menu-link">
                        <div>{{ __('messages.transactions') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Subscriptions -->
        <li class="menu-item {{ request()->routeIs('subscriptions.*') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri-vip-crown-line"></i>
                <div>{{ __('messages.subscriptions') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('subscriptions.index') ? 'active' : '' }}">
                    <a href="{{ route('subscriptions.index') }}" class="menu-link">
                        <div>{{ __('messages.subscription_plans') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('subscriptions.history') ? 'active' : '' }}">
                    <a href="{{ route('subscriptions.history') }}" class="menu-link">
                        <div>{{ __('messages.subscription_history') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Account Section -->
        <li class="menu-header mt-5">
            <span class="menu-header-text">{{ __('messages.account') }}</span>
        </li>

        <li class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <a href="{{ route('profile.edit') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-user-line"></i>
                <div>{{ __('messages.profile') }}</div>
            </a>
        </li>

        <!-- Training Videos -->
        <li class="menu-item {{ request()->routeIs('training-videos.index') ? 'active' : '' }}">
            <a href="{{ route('training-videos.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-play-circle-line"></i>
                <div>{{ app()->getLocale() == 'ar' ? 'الفيديوهات التدريبية' : 'Training Videos' }}</div>
            </a>
        </li>

        <!-- Support Tickets -->
        <li class="menu-item {{ request()->routeIs('seller.tickets.*') ? 'active' : '' }}">
            <a href="{{ route('seller.tickets.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ri-customer-service-line"></i>
                <div>{{ __('messages.support_tickets') }}</div>
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
