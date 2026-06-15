<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light"
    data-header-styles="light" data-menu-styles="light" data-toggled="close">

<head>
    <!-- Meta Data -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Easy Verifications for your Business" />
    <meta name="keywords" content="NIMC, BVN, ZEPA, Verification, Airtime,Bills, Identity">
    <meta name="author" content="Zepa Developers">
    <title>ZEPA Solutions - @yield('title')</title>

    <!-- fav icon -->
    <link rel="icon" href="{{ asset('assets/home/images/favicon/favicon.ico') }}" type="image/x-icon">

    <!-- Main Theme Js -->
    <script src="{{ asset('assets/js/authentication-main.js') }}"></script>

    <!-- Bootstrap Css -->
    <link id="style" href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Style Css -->
    <link href="{{ asset('assets/css/styles.min.css') }}" rel="stylesheet">

    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet">

    <!-- Custom Css -->
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">

    <!-- Premium Auth Design Styles -->
    <style>
        body {
            background: radial-gradient(circle at 10% 10%, rgba(26, 64, 130, 0.04) 0%, transparent 45%),
                        radial-gradient(circle at 90% 90%, rgba(11, 58, 103, 0.04) 0%, transparent 45%),
                        #f4f6fc !important;
            min-height: 100vh;
        }
        .authentication-basic {
            padding: 2rem 0;
        }
        .custom-card {
            border: none !important;
            border-radius: 20px !important;
            box-shadow: 0 15px 35px rgba(26, 64, 130, 0.06) !important;
            background: #ffffff !important;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .custom-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 45px rgba(26, 64, 130, 0.09) !important;
        }
        .desktop-logo, .desktop-dark {
            transition: transform 0.3s ease;
        }
        .desktop-logo:hover, .desktop-dark:hover {
            transform: scale(1.08);
        }
        .form-label {
            font-weight: 600 !important;
            color: #4a5568 !important;
            margin-bottom: 7px !important;
            font-size: 13px !important;
        }
        .form-control {
            border-radius: 10px !important;
            padding: 12px 16px !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 14px !important;
            transition: all 0.2s ease-in-out !important;
            background-color: #f8fafc !important;
        }
        .form-control:focus {
            border-color: #1a4082 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(26, 64, 130, 0.12) !important;
        }
        .input-group {
            border-radius: 10px !important;
            overflow: hidden;
        }
        .input-group .form-control {
            border-radius: 10px 0 0 10px !important;
        }
        .input-group .btn-light {
            border-radius: 0 10px 10px 0 !important;
            border: 1px solid #cbd5e1 !important;
            border-left: none !important;
            background-color: #f1f5f9 !important;
            color: #64748b !important;
            transition: all 0.2s ease !important;
        }
        .input-group .btn-light:hover {
            background-color: #e2e8f0 !important;
            color: #334155 !important;
        }
        .btn-primary, .btn-primary.btn-pry {
            border-radius: 10px !important;
            padding: 12px 24px !important;
            font-weight: 600 !important;
            background: linear-gradient(135deg, #1a4082 0%, #0b3a67 100%) !important;
            border: none !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 14px rgba(26, 64, 130, 0.25) !important;
            color: #ffffff !important;
        }
        .btn-primary:hover, .btn-primary.btn-pry:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(26, 64, 130, 0.35) !important;
            background: linear-gradient(135deg, #22519e 0%, #0e4880 100%) !important;
        }
        .btn-outline-danger {
            border-radius: 10px !important;
            padding: 10px 24px !important;
            font-weight: 600 !important;
            transition: all 0.2s ease !important;
        }
        .alert {
            border: none !important;
            border-radius: 12px !important;
            padding: 14px 18px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02) !important;
        }
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.08) !important;
            color: #ef4444 !important;
        }
        .alert-success {
            background-color: rgba(34, 197, 94, 0.08) !important;
            color: #22c55e !important;
        }
        .svg-danger {
            fill: #ef4444 !important;
        }
        .form-check-input {
            border-radius: 4px !important;
            border: 1.5px solid #cbd5e1 !important;
            cursor: pointer;
        }
        .form-check-input:checked {
            background-color: #1a4082 !important;
            border-color: #1a4082 !important;
        }
        .form-check-label {
            font-size: 13px !important;
            color: #64748b !important;
            cursor: pointer;
        }
        @media (max-width: 575.98px) {
            .card-body {
                padding: 1.5rem !important;
            }
            .custom-card {
                border-radius: 12px !important;
                box-shadow: 0 8px 24px rgba(26, 64, 130, 0.04) !important;
            }
            body {
                background: #f8fafc !important;
            }
            .authentication-basic {
                padding: 1rem 0 !important;
            }
        }
    </style>
</head>
@stack('page-css')

<body>
    <!-- start preLoader -->
    <div id="preloader">
        <span class="loader"></span>
    </div>
    <!-- end preLoader -->

    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            <!-- Left Side: ZEPA Advert Banner (hidden on mobile) -->
            <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #12284c 0%, #061122 100%);">
                <!-- Background Decorative Shapes -->
                <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.15) 0%, transparent 60%); pointer-events: none;"></div>
                <div class="position-absolute bottom-0 end-0 w-100 h-100 opacity-20" style="background-image: radial-gradient(circle at 80% 70%, rgba(26, 64, 130, 0.4) 0%, transparent 60%); pointer-events: none;"></div>
                
                <!-- Logo Section -->
                <div class="mb-5 z-1">
                    <a href="../" class="d-flex align-items-center gap-2 text-decoration-none">
                        <img src="{{ asset('assets/images/brand-logos/logo.png') }}" alt="logo" style="width: 55px; height: 50px;">
                        <span class="fs-20 fw-bold text-white tracking-wider" style="letter-spacing: 2px;">ZEPA SOLUTIONS</span>
                    </a>
                </div>

                <!-- Main Promotional Text -->
                <div class="my-auto py-5 pe-lg-5 z-1">
                    <span class="badge px-3 py-2 rounded-pill mb-4 fs-12 fw-semibold" style="background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2);">🚀 Multi-Service Portal</span>
                    <h1 class="display-6 fw-bold text-white mb-4" style="line-height: 1.3;">Accelerate Transactions & Fund Your Wallet Instantly</h1>
                    <p class="fs-15 text-white-50 mb-5" style="line-height: 1.7;">
                        Join thousands of businesses who rely on ZEPA Solutions for lightning-fast wallet funding, bills payment, and real-time biometric and digital validations. Fund your wallet today and unlock premium digital access.
                    </p>

                    <!-- Feature list with icons -->
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-3 rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.08);">
                                    <i class="ri-flashlight-line fs-20 text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold text-white">Instant Wallet Funding</h6>
                                    <p class="mb-0 fs-12 text-white-50">Auto-credited in seconds</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-3 rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.08);">
                                    <i class="ri-shield-user-line fs-20 text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold text-white">NIN & BVN Status</h6>
                                    <p class="mb-0 fs-12 text-white-50">Instant identity validation</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-3 rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.08);">
                                    <i class="ri-customer-service-2-line fs-20 text-info"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold text-white">24/7 Dedicated Support</h6>
                                    <p class="mb-0 fs-12 text-white-50">Direct care agents</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-3 rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.08);">
                                    <i class="ri-global-line fs-20 text-primary-light" style="color: #60a5fa;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold text-white">All-in-One Utility</h6>
                                    <p class="mb-0 fs-12 text-white-50">Airtime, Data, Electricity</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Text -->
                <div class="mt-5 pt-3 d-flex justify-content-between align-items-center fs-12 text-white-50" style="border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <span>© {{ date('Y') }} Zepa Solutions. All rights reserved.</span>
                    <a href="{{ route('terms') }}" class="text-white-50 text-decoration-none hover-white">Terms of Service</a>
                </div>
            </div>

            <!-- Right Side: Auth Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-sm-5" style="background-color: #f8fafc; min-height: 100vh;">
                <div class="w-100 py-4" style="max-width: 460px;">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Jquery-->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Show Password JS -->
    <script src="{{ asset('assets/js/show-password.js') }}"></script>

    <!-- Page Specific JS -->
    @stack('page-js')

    <!-- Config JS -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
</body>

</html>
