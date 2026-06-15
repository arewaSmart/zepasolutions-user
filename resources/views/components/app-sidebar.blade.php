<aside class="app-sidebar sticky" id="sidebar">
    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="{{ route('dashboard') }}" class="header-logo">
            <img src="{{ asset('assets/images/brand-logos/logo.png') }}" alt="logo" class="desktop-logo">
            <img src="{{ asset('assets/images/brand-logos/logo.png') }}" alt="logo" class="desktop-dark">
            <img src="{{ asset('assets/images/brand-logos/logo.png') }}" alt="logo" class="toggle-dark">
        </a>
    </div>
    <!-- End::main-sidebar-header -->
    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">
        <!-- Start::nav -->
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                     viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>

            <ul class="main-menu">
                <!-- Start::slide -->
                <li class="slide">
                    <a href="{{ route('dashboard') }}"
                        class="side-menu__item {{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="bx
                        bx-home side-menu__icon"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>
                <li
                    class="slide has-sub {{ request()->is('funding') || request()->is('p2p') || request()->is('claim') ? 'open' : '' }}">
                    <a href="javascript:void(0);"
                        class="side-menu__item {{ request()->is('funding') || request()->is('p2p') || request()->is('claim') ? 'active' : '' }}">
                        <i class="bx bx-wallet side-menu__icon"></i> <box-icon type='solid' name='wallet'></box-icon>
                        <span class="side-menu__label">Wallet</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>

                    <ul class="slide-menu child1">
                        <li class="slide">
                            <a href="{{ route('funding') }}"
                                class="side-menu__item {{ request()->is('funding') ? 'active' : '' }}">Funding</a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('p2p') }}"
                                class="side-menu__item {{ request()->is('p2p') ? 'active' : '' }}">P2P
                            </a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('claim') }}"
                                class="side-menu__item {{ request()->is('claim') ? 'active' : '' }}">Claim Bonus
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- End::slide -->
                <!-- Start::slide -->
                <li
                    class="slide has-sub {{ request()->is('nin-verification*') || request()->is('nin-phone-verification*') || request()->is('bvn-verification*') || request()->is('bvn2') || request()->is('nin-demo-verification*') ? 'open' : '' }}">
                    <a href="javascript:void(0);"
                        class="side-menu__item {{ request()->is('nin-verification*') || request()->is('nin-phone-verification*') || request()->is('bvn-verification*') || request()->is('bvn2') || request()->is('nin-demo-verification*') ? 'active' : '' }}">
                        <i class="bx bx-fingerprint side-menu__icon"></i>
                        <span class="side-menu__label">Verification</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide">
                            <a href="{{ route('nin.verification.index') }}"
                                class="side-menu__item {{ request()->is('nin-verification') ? 'active' : '' }}">Verify NIN (NIN)</a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('nin.phone.index') }}"
                                class="side-menu__item {{ request()->is('nin-phone-verification') ? 'active' : '' }}">Verify NIN (Phone No)</a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('nin.demo.index') }}"
                                class="side-menu__item {{ request()->is('nin-demo-verification') ? 'active' : '' }}">Verify NIN (Demographics)</a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('bvn.verification.index') }}"
                                class="side-menu__item {{ request()->is('bvn-verification') ? 'active' : '' }}">Verify BVN</a>
                        </li>
                    </ul>
                </li>
                <!-- End::slide -->
                <!-- Start::slide -->
                <li class="slide">
                    <a href="{{ route('airtime') }}"
                        class="side-menu__item {{ request()->is('airtime*') ? 'active' : '' }}">
                        <i class="bx bx-phone-call side-menu__icon"></i>
                        <span class="side-menu__label">Airtime</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('buy-data') }}"
                        class="side-menu__item {{ request()->is('data*') ? 'active' : '' }}">
                        <i class="bx bx-wifi side-menu__icon"></i>
                        <span class="side-menu__label">Data Bundle</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('buy-sme-data') }}"
                        class="side-menu__item {{ request()->is('sme-data*') ? 'active' : '' }}">
                        <i class="bx bx-data side-menu__icon"></i>
                        <span class="side-menu__label">SME Data Bundle</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('cable') }}"
                        class="side-menu__item {{ request()->is('cable*') ? 'active' : '' }}">
                        <i class="bx bx-tv side-menu__icon"></i>
                        <span class="side-menu__label">TV Subscriptions</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('electricity') }}"
                        class="side-menu__item {{ request()->is('electricity*') ? 'active' : '' }}">
                        <i class="bx bx-bulb side-menu__icon"></i>
                        <span class="side-menu__label">Electric Bills</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('education') }}"
                        class="side-menu__item {{ request()->is('education*') ? 'active' : '' }}">
                        <i class="bx bx-user-pin side-menu__icon"></i>
                        <span class="side-menu__label">Educational Pin</span>
                    </a>
                </li>
                <!-- Start::slide -->
                <li class="slide">
                    <a href="{{ route('nin-modification') }}"
                        class="side-menu__item {{ request()->is('nin-modification*') ? 'active' : '' }}">
                        <i class="bx bx-id-card side-menu__icon"></i>
                        <span class="side-menu__label">NIN Modification</span>
                    </a>
                </li>
                <!-- Start::slide -->
                <li class="slide">
                    <a href="{{ route('nin-validation.index') }}"
                        class="side-menu__item {{ request()->is('nin-validation*') ? 'active' : '' }}">
                        <i class="bx bx-shield-quarter side-menu__icon"></i>
                        <span class="side-menu__label">NIN Validation</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('ipe.index') }}"
                        class="side-menu__item {{ request()->is('ipe*') ? 'active' : '' }}">
                        <i class="bx bx-list-check side-menu__icon"></i>
                        <span class="side-menu__label">IPE Clearance</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('bvn-modification') }}"
                        class="side-menu__item {{ request()->is('bvn-modification*') ? 'active' : '' }}">
                        <i class="bx bx-edit side-menu__icon"></i>
                        <span class="side-menu__label">BVN Modification</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('crm') }}"
                        class="side-menu__item {{ request()->is('crm') ? 'active' : '' }}">
                        <i class="bx bx-support side-menu__icon"></i>
                        <span class="side-menu__label">CRM</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('phone.search.index') }}"
                        class="side-menu__item {{ request()->is('phone-search*') ? 'active' : '' }}">
                        <i class="bx bx-search-alt side-menu__icon"></i>
                        <span class="side-menu__label">BVN Search by Phone</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('transactions') }}"
                        class="side-menu__item  {{ request()->is('transactions') ? 'active' : '' }}">
                        <i class="bx bx-history side-menu__icon"></i>
                        <span class="side-menu__label">Transactions</span>
                    </a>
                </li>


                <li class="slide">
                    <a href="{{ route('support') }}"
                        class="side-menu__item {{ request()->is('support') ? 'active' : '' }}">
                        <i class="bx bx-headphone side-menu__icon"></i>
                        <span class="side-menu__label">Contact Support</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('profile.edit') }}"
                        class="side-menu__item {{ request()->is('profile') ? 'active' : '' }}">
                        <i class="bx bx-cog side-menu__icon"></i>
                        <span class="side-menu__label">Settings</span>
                    </a>
                </li>
                <li class="slide">
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                        @csrf
                        <button type="submit" class="side-menu__item"
                            style="border: none; background: none; width: 100%;">
                            <i class="bx bx-exit side-menu__icon"></i>
                            <span class="side-menu__label">Logout</span>
                        </button>
                    </form>
                </li>
                <!-- End::slide -->
            </ul>
            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                    viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg>
            </div>
        </nav>
        <!-- End::nav -->
    </div>
    <!-- End::main-sidebar -->
</aside>
<!-- End::app-sidebar -->
