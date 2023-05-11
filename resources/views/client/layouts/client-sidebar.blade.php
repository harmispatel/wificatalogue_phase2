@php
    // UserDetails
    if (auth()->user())
    {
        $userID = encrypt(auth()->user()->id);
        $userName = auth()->user()->firstname." ".auth()->user()->lastname;
        $userImage = auth()->user()->image;
    }
    else
    {
        $userID = '';
        $userName = '';
        $userImage = '';
    }

    // ShopName
    $shop_name = isset(Auth::user()->hasOneShop->shop['name']) ? Auth::user()->hasOneShop->shop['name'] : '';

    $shop_slug = isset(Auth::user()->hasOneShop->shop['shop_slug']) ? Auth::user()->hasOneShop->shop['shop_slug'] : '';

    // Current Route Name
    $routeName = Route::currentRouteName();

    // Route Params
    $routeParams = Route::current()->parameters();

    // Subscrption ID
    $subscription_id = Auth::user()->hasOneSubscription['subscription_id'];

    // Get Package Permissions
    $package_permissions = getPackagePermission($subscription_id);

@endphp

<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">

        {{-- Dashboard Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ ($routeName == 'client.dashboard') ? 'active-tab' : '' }}" href="{{ route('client.dashboard') }}">
                <i class="fa-solid fa-house-chimney {{ ($routeName == 'client.dashboard') ? 'icon-tab' : '' }}"></i>
                <span>{{ __('Dashboard') }}</span>
            </a>
        </li>

        {{-- Shop Details Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ (($routeName != 'client.subscription') && ($routeName != 'billing.info') && ($routeName != 'billing.info.edit')) ? 'collapsed' : '' }} {{ (($routeName == 'client.subscription') || ($routeName == 'billing.info') || ($routeName == 'billing.info.edit')) ? 'active-tab' : '' }}" data-bs-target="#shop-nav" data-bs-toggle="collapse" href="#" aria-expanded="{{ (($routeName == 'client.subscription') || ($routeName == 'billing.info') || ($routeName == 'billing.info.edit')) ? 'true' : 'false' }}">
                <i class="ri-restaurant-2-line  {{ (($routeName == 'client.subscription') || ($routeName == 'billing.info') || ($routeName == 'billing.info.edit')) ? 'icon-tab' : '' }}"></i><span>{{ $shop_name }}</span><i class="bi bi-chevron-down ms-auto {{ (($routeName == 'client.subscription') || ($routeName == 'billing.info') || ($routeName == 'billing.info.edit')) ? 'icon-tab' : '' }}"></i>
            </a>
            <ul id="shop-nav" class="nav-content sidebar-ul collapse  {{ (($routeName == 'client.subscription') || ($routeName == 'billing.info') || ($routeName == 'billing.info.edit')) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('billing.info') }}" class="{{ ($routeName == 'billing.info' || $routeName == 'billing.info.edit') ? 'active-link' : '' }}">
                        <span>{{ __('Billing Info') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('client.subscription',$userID) }}" class="{{ ($routeName == 'client.subscription') ? 'active-link' : '' }}">
                        <span>{{ __('Subscription') }}</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Design Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ (($routeName != 'design.general-info') && ($routeName != 'design.logo') && ($routeName != 'design.cover') && ($routeName != 'banners') && ($routeName != 'design.theme')) ? 'collapsed' : '' }} {{ (($routeName == 'design.general-info') || ($routeName == 'design.logo') || ($routeName == 'design.cover') || ($routeName == 'banners') || ($routeName == 'design.theme')) ? 'active-tab' : '' }}" data-bs-target="#design-nav" data-bs-toggle="collapse" href="#" aria-expanded="{{ (($routeName == 'design.general-info') || ($routeName == 'design.logo') || ($routeName == 'design.cover') || ($routeName == 'banners') || ($routeName == 'design.theme')) ? 'true' : 'false' }}">
                <i class="fa-solid fa-pen-nib {{ (($routeName == 'design.general-info') || ($routeName == 'design.logo') || ($routeName == 'design.cover') || ($routeName == 'banners') || ($routeName == 'design.theme')) ? 'icon-tab' : '' }}"></i><span>{{ __('Design') }}</span><i class="bi bi-chevron-down ms-auto {{ (($routeName == 'design.general-info') || ($routeName == 'design.logo') || ($routeName == 'design.cover') || ($routeName == 'banners') || ($routeName == 'design.theme')) ? 'icon-tab' : '' }}"></i>
            </a>
            <ul id="design-nav" class="nav-content sidebar-ul collapse {{ (($routeName == 'design.general-info') || ($routeName == 'design.logo') || ($routeName == 'design.cover') || ($routeName == 'banners') || ($routeName == 'design.theme')) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('design.general-info') }}" class="{{ ($routeName == 'design.general-info') ? 'active-link' : '' }}">
                        <span>{{ __('General Info') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('design.logo') }}" class="{{ ($routeName == 'design.logo') ? 'active-link' : '' }}">
                        <span>{{ __('Logo') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('design.cover') }}" class="{{ ($routeName == 'design.cover') ? 'active-link' : '' }}">
                        <span>{{ __('Cover') }}</span>
                    </a>
                </li>

                {{-- Banner --}}
                @if(isset($package_permissions['banner']) && !empty($package_permissions['banner']) && $package_permissions['banner'] == 1)
                    <li>
                        <a href="{{ route('banners') }}" class="{{ ($routeName == 'banners') ? 'active-link' : '' }}">
                            <span>{{ __('Banners') }}</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('design.theme') }}" class="{{ ($routeName == 'design.theme') ? 'active-link' : '' }}">
                        <span>{{ __('Themes') }}</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Menu Nav --}}
        <li class="nav-item">
            {{-- && --}}
            <a class="nav-link {{ (($routeName != 'categories') && ($routeName != 'items') && ($routeName != 'languages') && ($routeName != 'tags') && ($routeName != 'options')) ? 'collapsed' : '' }} {{ (($routeName == 'categories') || ($routeName == 'items') || ($routeName == 'languages') || ($routeName == 'tags') || ($routeName == 'options')) ? 'active-tab' : '' }}" data-bs-target="#menu-nav" data-bs-toggle="collapse" href="#" aria-expanded="{{ (($routeName == 'categories') || ($routeName == 'items') || ($routeName == 'languages') || ($routeName == 'tags') || ($routeName == 'options')) ? 'true' : 'false' }}">
                <i class="fa-solid fa-bars {{ (($routeName == 'categories') || ($routeName == 'items') || ($routeName == 'languages') || ($routeName == 'tags')) ? 'icon-tab' : '' }}"></i><span>{{ __('QR Catalogue') }}</span><i class="bi bi-chevron-down ms-auto {{ (($routeName == 'categories') || ($routeName == 'items') || ($routeName == 'languages') || ($routeName == 'tags') || ($routeName == 'options')) ? 'icon-tab' : '' }}"></i>
            </a>
            <ul id="menu-nav" class="nav-content sidebar-ul collapse {{ (($routeName == 'categories') || ($routeName == 'items') || ($routeName == 'languages') || ($routeName == 'tags') || ($routeName == 'options')) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('categories') }}" class="{{ (($routeName == 'categories') &&  count($routeParams) == 0) ? 'active-link' : '' }}">
                        <span>{{ __('Categories') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('items') }}" class="{{ ($routeName == 'items') ? 'active-link' : '' }}">
                        <span>{{ __('Items') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories','page') }}" class="{{ (($routeName == 'categories') && (isset($routeParams['cat_id']) && $routeParams['cat_id'] == 'page')) ? 'active-link' : '' }}">
                        <span>{{ __('Pages') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories','link') }}" class="{{ (($routeName == 'categories') && (isset($routeParams['cat_id']) && $routeParams['cat_id'] == 'link')) ? 'active-link' : '' }}">
                        <span>{{ __('Links') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories','image_gallary') }}" class="{{ (($routeName == 'categories') && (isset($routeParams['cat_id']) && $routeParams['cat_id'] == 'image_gallary')) ? 'active-link' : '' }}">
                        <span>{{ __('Galleries') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories','check_in_page') }}" class="{{ (($routeName == 'categories') && (isset($routeParams['cat_id']) && $routeParams['cat_id'] == 'check_in_page')) ? 'active-link' : '' }}">
                        <span>{{ __('Check-In Page') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories','pdf_category') }}" class="{{ (($routeName == 'categories') && (isset($routeParams['cat_id']) && $routeParams['cat_id'] == 'pdf_category')) ? 'active-link' : '' }}">
                        <span>{{ __('PDF') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tags') }}" class="{{ ($routeName == 'tags') ? 'active-link' : '' }}">
                        <span>{{ __('Tags') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('options') }}" class="{{ ($routeName == 'options') ? 'active-link' : '' }}">
                        <span>{{ __('Order Attributes') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('languages') }}" class="{{ ($routeName == 'languages') ? 'active-link' : '' }}">
                        <span>{{ __('Languages') }}</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Orders Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ (($routeName != 'order.settings') && ($routeName != 'client.orders') && ($routeName != 'client.orders.history') && ($routeName != 'view.order') && ($routeName != 'payment.settings')) ? 'collapsed' : '' }} {{ (($routeName == 'order.settings') || ($routeName == 'client.orders') || ($routeName == 'view.order') || ($routeName == 'payment.settings') || ($routeName == 'client.orders.history')) ? 'active-tab' : '' }}" data-bs-target="#orders-nav" data-bs-toggle="collapse" href="#" aria-expanded="{{ (($routeName == 'order.settings') || ($routeName == 'client.orders') || ($routeName == 'view.order') || ($routeName == 'payment.settings') || ($routeName == 'client.orders.history')) ? 'true' : 'false' }}">
                <i class="bi bi-cart-check {{ (($routeName == 'order.settings') || ($routeName == 'client.orders') || ($routeName == 'view.order') || ($routeName == 'payment.settings') || ($routeName == 'client.orders.history')) ? 'icon-tab' : '' }}"></i><span>{{ __('Orders') }}</span><i class="bi bi-chevron-down ms-auto {{ (($routeName == 'order.settings') || ($routeName == 'client.orders') || ($routeName == 'payment.settings') || ($routeName == 'client.orders.history')) ? 'icon-tab' : '' }}"></i>
            </a>
            <ul id="orders-nav" class="nav-content sidebar-ul collapse {{ (($routeName == 'order.settings') || ($routeName == 'client.orders') || ($routeName == 'view.order') || ($routeName == 'payment.settings') || ($routeName == 'client.orders.history')) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('client.orders') }}" class="{{ (($routeName == 'client.orders')) ? 'active-link' : '' }}">
                        <span>{{ __('Orders') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('client.orders.history') }}" class="{{ (($routeName == 'client.orders.history') || ($routeName == 'view.order')) ? 'active-link' : '' }}">
                        <span>{{ __('Orders History') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('order.settings') }}" class="{{ (($routeName == 'order.settings') &&  count($routeParams) == 0) ? 'active-link' : '' }}">
                        <span>{{ __('Order Settings') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('payment.settings') }}" class="{{ (($routeName == 'payment.settings')) ? 'active-link' : '' }}">
                        <span>{{ __('Payment Settings') }}</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Preview Nav --}}
        <li class="nav-item">
            <a class="nav-link" onclick="previewMyShop('{{ $shop_slug }}')" style="cursor: pointer">
                <i class="fa-solid fa-eye"></i>
                <span>{{ __('Preview') }}</span>
            </a>
        </li>

        {{-- QrCode Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ ($routeName == 'qrcode') ? 'active-tab' : '' }}" href="{{ route('qrcode') }}">
                <i class="fa-solid fa-qrcode {{ ($routeName == 'qrcode') ? 'icon-tab' : '' }}"></i>
            <span>{{ __('Get QR Code') }}</span>
            </a>
        </li>

        {{-- Statistics Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ ($routeName == 'statistics') ? 'active-tab' : '' }}" href="{{ route('statistics') }}">
                <i class="fa-solid fa-chart-line {{ ($routeName == 'statistics') ? 'icon-tab' : '' }}"></i>
            <span>{{ __('Statistics') }}</span>
            </a>
        </li>

        {{-- Tutorial Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ ($routeName == 'tutorial.show') ? 'active-tab' : '' }}" href="{{ route('tutorial.show')}}">
                <i class="fa-solid fa-circle-info {{ ($routeName == 'tutorial.show') ? 'icon-tab' : '' }}"></i>
            <span>{{ __('Tutorial') }}</span>
            </a>
        </li>

        {{-- Contact Nav --}}
        <li class="nav-item">
            <a class="nav-link {{ ($routeName == 'contact') ? 'active-tab' : '' }}" href="{{ route('contact') }}">
                <i class="fa-solid fa-address-card {{ ($routeName == 'contact') ? 'icon-tab' : '' }}"></i>
            <span>{{ __('Contact') }}</span>
            </a>
        </li>

        {{-- Logout Nav --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('logout') }}">
                <i class="bi bi-box-arrow-right"></i>
            <span>{{ __('Logout') }}</span>
            </a>
        </li>

        {{-- Design Nav --}}
        {{-- <li class="nav-item">
            <a class="nav-link {{ ($routeName == 'menu') ? 'active-tab' : '' }}" href="{{ route('menu') }}">
                <i class="fa-solid fa-pen-nib {{ ($routeName == 'menu') ? 'icon-tab' : '' }}"></i>
                <span>Design</span>
            </a>
        </li> --}}

    </ul>
</aside>
