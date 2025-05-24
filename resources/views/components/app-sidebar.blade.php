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
                    class="slide has-sub {{ request()->is('funding') || request()->is('p2p') || request()->is('claim') || request()->is('transfer') ? 'open' : '' }}">
                    <a href="javascript:void(0);"
                        class="side-menu__item {{ request()->is('funding') || request()->is('p2p') || request()->is('claim') || request()->is('transfer') ? 'active' : '' }}">
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
                        <li class="slide">
                            <a href="{{ route('transfer') }}"
                                class="side-menu__item {{ request()->is('transfer') ? 'active' : '' }}">Transfer</a>
                        </li>
                    </ul>
                </li>
                <!-- End::slide -->
                <!-- Start::slide -->
                <li
                    class="slide has-sub {{ request()->is('nin') || request()->is('nin2') || request()->is('nin-phone') || request()->is('bvn') || request()->is('bvn2') || request()->is('nin-track') ? 'open' : '' }}">
                    <a href="javascript:void(0);"
                        class="side-menu__item {{ request()->is('nin') || request()->is('nin2') || request()->is('nin-phone') || request()->is('bvn') || request()->is('bvn2') || request()->is('nin-track') ? 'active' : '' }}">
                        <i class="bx bx-fingerprint side-menu__icon"></i>
                        <span class="side-menu__label">Identity</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li
                            class="slide has-sub {{ request()->is('nin') || request()->is('nin-phone') || request()->is('nin-track') ? 'open' : '' }}">
                            <a href="javascript:void(0);"
                                class="side-menu__item {{ request()->is('nin') || request()->is('nin-phone') || request()->is('nin-track') ? 'active' : '' }}">
                                NIN
                                Verification <i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="{{ route('nin') }}"
                                        class="side-menu__item {{ request()->is('nin') ? 'active' : '' }}"> Verify
                                        NIN using NIN</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ route('nin-phone') }}"
                                        class="side-menu__item {{ request()->is('nin-phone') ? 'active' : '' }}">Verify
                                        NIN using Phone No
                                    </a>
                                </li>
                                <li class="slide">
                                    <a href="{{ route('nin-track') }}"
                                        class="side-menu__item {{ request()->is('nin-track') ? 'active' : '' }}">Verify
                                        NIN using Tracking No
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="slide has-sub {{ request()->is('nin2') ? 'open' : '' }}">
                            <a href="javascript:void(0);"
                                class="side-menu__item {{ request()->is('nin2') ? 'active' : '' }}">
                                NIN
                                Verification V2 <i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="{{ route('nin2') }}"
                                        class="side-menu__item {{ request()->is('nin2') ? 'active' : '' }}"> Verify
                                        NIN using NIN</a>
                                </li>
                            </ul>
                        </li>
                        <li class="slide has-sub {{ request()->is('bvn') ? 'open' : '' }}">
                            <a href="javascript:void(0);"
                                class="side-menu__item {{ request()->is('bvn') ? 'active' : '' }}">BVN Verification
                                <i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="{{ route('bvn') }}"
                                        class="side-menu__item {{ request()->is('bvn') ? 'active' : '' }}">Verify
                                        BVN</a>
                                </li>
                            </ul>
                        </li>
                        <li class="slide has-sub {{ request()->is('bvn2') ? 'open' : '' }}">
                            <a href="javascript:void(0);"
                                class="side-menu__item {{ request()->is('bvn') ? 'active' : '' }}">BVN Verification V2
                                <i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="{{ route('bvn2') }}"
                                        class="side-menu__item {{ request()->is('bvn2') ? 'active' : '' }}">Verify
                                        BVN</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <!-- End::slide -->
                <!-- Start::slide -->
                <li
                    class="slide has-sub {{ request()->is('airtime') || request()->is('data') || request()->is('sme-data') || request()->is('tv') ? 'open' : '' }}">
                    <a href="javascript:void(0);"
                        class="side-menu__item {{ request()->is('airtime') || request()->is('data') || request()->is('sme-data') || request()->is('tv') ? 'active' : '' }}">
                        <i class="bx bx-task side-menu__icon"></i>
                        <span class="side-menu__label">Utilities</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide">
                            <a href="{{ route('airtime') }}"
                                class="side-menu__item {{ request()->is('airtime') ? 'active' : '' }}">Airtime</a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('data') }}"
                                class="side-menu__item {{ request()->is('data') ? 'active' : '' }}">Data
                                Bundle</a>
                        </li>
                        <li class="slide">

                        {{-- <li
                            class="slide has-sub {{ request()->is('data') || request()->is('sme-data') ? 'open' : '' }}">
                            <a href="javascript:void(0);"
                                class="side-menu__item {{ request()->is('data') || request()->is('sme-data') ? 'active' : '' }}">
                                Data Top-up<i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                               
                                <li class="slide">
                                    <a href="{{ route('sme-data') }}"
                                        class="side-menu__item {{ request()->is('sme-data') ? 'active' : '' }}">SME
                                        Data Bundle</a>
                                </li>
                            </ul>
                        </li> --}}

                        <li class="slide">
                            <a href="#" onclick="return confirm('Comming soon!');"
                                class="side-menu__item {{ request()->is('tv') }}">TV
                                Subscriptions
                            </a>
                        </li>
                        <li class="slide">
                            <a href="#" onclick="return confirm('Comming Soon')"
                                class="side-menu__item">Electric Bills
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="slide">
                    <a href="#" onclick="return confirm('Comming Soon !');"
                        class="side-menu__item {{ request()->is('education') ? 'active' : '' }}">
                        <i class="bx bx-user-pin side-menu__icon"></i>
                        <span class="side-menu__label">Educational Pin</span>
                    </a>
                </li>
                </li>
                <!-- Start::slide -->
                @if (Auth::user()->role == 'agent')
                    <li
                        class="slide has-sub {{ request()->is('bvn-modification') || request()->is('crm') || request()->is('account-upgrade') || request()->is('crm2') || request()->is('bvn-enrollment') || request()->is('nin-services') || request()->is('vnin-to-nibss') ? 'open' : '' }}">
                        <a href="javascript:void(0);"
                            class="side-menu__item {{ request()->is('bvn-modification') || request()->is('crm') || request()->is('account-upgrade') || request()->is('crm2') || request()->is('bvn-enrollment') || request()->is('nin-services') || request()->is('vnin-to-nibss') ? 'active' : '' }}">
                            <i class="bx bx-user-plus side-menu__icon"></i>
                            <span class="side-menu__label">Agent Services </span>
                            <i class="fe fe-chevron-right side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">

                            <li class="slide">
                                <a href="{{ route('nin-services') }}"
                                    class="side-menu__item  {{ request()->is('nin-services') ? 'active' : '' }}">NIN
                                    Services </a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('vnin-to-nibss') }}"
                                    class="side-menu__item {{ request()->is('vnin-to-nibss') ? 'active' : '' }}">VNIN
                                    to
                                    NIBSS </a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('bvn-modification') }}"
                                    class="side-menu__item {{ request()->is('bvn-modification') ? 'active' : '' }}">BVN
                                    Modification </a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('crm') }}"
                                    class="side-menu__item {{ request()->is('crm') ? 'active' : '' }}">CRM</a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('account-upgrade') }}"
                                    class="side-menu__item {{ request()->is('account-upgrade') ? 'active' : '' }}">Account
                                    Upgrade
                                </a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('crm2') }}"
                                    class="side-menu__item {{ request()->is('crm2') ? 'active' : '' }}">Find
                                    BVN
                                    using Phone and DOB
                                </a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('bvn-enrollment') }}"
                                    class="side-menu__item {{ request()->is('bvn-enrollment') ? 'active' : '' }}">BVN
                                    Enrollement Agency Request
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <li class="slide">
                    <a href="{{ route('transactions') }}"
                        class="side-menu__item  {{ request()->is('transactions') ? 'active' : '' }}">
                        <i class="bx bx-history side-menu__icon"></i>
                        <span class="side-menu__label">Transactions</span>
                    </a>
                </li>


                <li class="slide">
                    <a href="{{ route('support') }}" target="_blank"
                        class="side-menu__item {{ request()->is('support') ? 'active' : '' }}">
                        <i class="bx bx-headphone side-menu__icon"></i>
                        <span class="side-menu__label">Support</span>
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
