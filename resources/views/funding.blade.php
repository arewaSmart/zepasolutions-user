@extends('layouts.dashboard')
@section('title', 'Funding')
@section('content')
    <div class="page">

        @include('components.app-header')
        @include('components.app-sidebar')

        <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">

                <!-- Start::page-header -->
                <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                    <div>
                        <p class="fw-semibold fs-18 mb-0">Wallet Funding</p>
                        <span class="fs-semibold text-muted">Select your preferred funding method to deposit funds
                            into
                            your wallet. If you need assistance, please don't hesitate to contact us.</span>
                    </div>
                </div>
                <!-- End::page-header -->
                <!-- Start::row-1 -->
                <div class="row">
                    <div class="col-xxl-12 col-xl-12">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="row">
                                    <!-- Wallet Balance -->
                                    <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span
                                                            class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                                            <i class="ti ti-wallet fs-16"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Wallet Balance</p>
                                                                <h4 class="fw-semibold mt-1">
                                                                    &#x20A6;{{ $wallet_balance }}
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hold Balance -->
                                    <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span
                                                            class="avatar avatar-md avatar-rounded bg-warning-transparent">
                                                            <i class="ti ti-lock fs-16"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Hold Balance</p>
                                                                <h4 class="fw-semibold mt-1">
                                                                    &#x20A6;{{ $hold_balance }}
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Deposited -->
                                    <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span class="avatar avatar-md avatar-rounded bg-info-transparent">
                                                            <i class="ti ti-briefcase fs-16"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Deposited</p>
                                                                <h4 class="fw-semibold mt-1">
                                                                    &#x20A6;{{ $deposit }}</h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Spent -->
                                    <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span class="avatar avatar-md avatar-rounded bg-danger-transparent">
                                                            <i class="ri-exchange-funds-line fs-16"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Spent</p>
                                                                <h4 class="fw-semibold mt-1">
                                                                    &#x20A6;{{ number_format($spent, 2) }}
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 col-xxl-10 mx-auto">
                                <div class="row">
                                    <!-- Left: Wallet Funding Advert -->
                                    <div class="col-xl-6 col-lg-6">
                                        <div class="card custom-card overflow-hidden text-white shadow-lg" style="background: linear-gradient(135deg, #111827 0%, #1f2937 100%); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px;">
                                            <div class="card-body p-4 d-flex flex-column justify-content-between" style="min-height: 380px;">
                                                <div>
                                                    <div class="d-flex align-items-center mb-3">
                                                        <span class="avatar avatar-md avatar-rounded bg-warning-transparent me-3">
                                                            <i class="bi bi-lightning-charge-fill text-warning fs-18"></i>
                                                        </span>
                                                        <h5 class="fw-semibold mb-0 text-white">Instantly Boost Your Wallet!</h5>
                                                    </div>
                                                    <p class="text-muted fs-13 mb-4">
                                                        Keep your account funded to enjoy uninterrupted access to educational PINs, automated validations, upgrades, and premium utility services.
                                                    </p>
                                                    
                                                    <div class="mb-4">
                                                        <div class="d-flex align-items-top mb-3">
                                                            <i class="bi bi-shield-fill-check text-success fs-16 me-3 mt-1"></i>
                                                            <div>
                                                                <p class="fw-semibold mb-0 text-white">Safe & Secure Transactions</p>
                                                                <span class="text-muted fs-12">Protected by state-of-the-art encryption and database-level security locks.</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex align-items-top mb-3">
                                                            <i class="bi bi-stopwatch-fill text-info fs-16 me-3 mt-1"></i>
                                                            <div>
                                                                <p class="fw-semibold mb-0 text-white">24/7 Automated Credit</p>
                                                                <span class="text-muted fs-12">Your wallet balance is updated automatically, even during holidays.</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex align-items-top">
                                                            <i class="bi bi-star-fill text-warning fs-16 me-3 mt-1"></i>
                                                            <div>
                                                                <p class="fw-semibold mb-0 text-white">No Manual Verification</p>
                                                                <span class="text-muted fs-12">No need to upload receipts or contact support for manual activation.</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-2 text-center text-md-start">
                                                    <span class="badge bg-warning-transparent text-warning py-2 px-3 fs-11 fw-semibold">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Highly Recommended
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Virtual Account Numbers -->
                                    <div class="col-xl-6 col-lg-6">
                                        <div class="card custom-card">
                                            <div class="card-header  justify-content-between">
                                                <div class="card-title">
                                                    Virtual Account Numbers
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <small class="fw-semibold">Fund your wallet instantly by depositing
                                                    into the virtual account number</small>
                                                <ul class="list-unstyled crm-top-deals mb-0 mt-3">
                                                    @if ($virtual_accounts != null && count($virtual_accounts) > 0)
                                                        @foreach ($virtual_accounts as $data)
                                                            <li>
                                                                <div class="d-flex align-items-top flex-wrap mb-3">
                                                                    <div class="me-2">
                                                                        <span class="avatar avatar-sm avatar-rounded">
                                                                            @if ($data->bankName == 'Wema bank')
                                                                                <img src="{{ asset('assets/images/wema.jpg') }}"
                                                                                    alt="">
                                                                            @elseif($data->bankName == 'Moniepoint Microfinance Bank')
                                                                                <img src="{{ asset('assets/images/moniepoint.jpg') }}"
                                                                                    alt="">
                                                                            @elseif($data->bankName == 'PalmPay')
                                                                                <img src="{{ asset('assets/images/palmpay.png') }}"
                                                                                    alt="">
                                                                            @else
                                                                                <img src="{{ asset('assets/images/sterling.png') }}"
                                                                                    alt="">
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                    <div class="flex-fill">
                                                                        <p class="fw-semibold mb-0">
                                                                            {{ $data->accountName }}</p>
                                                                        <span
                                                                            class="fs-14 acctno">{{ $data->accountNo }}</span>
                                                                        <br>
                                                                        <span class=" fs-12">{{ $data->bankName }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="fw-semibold fs-15"><a href="#"
                                                                            class="btn btn-light btn-sm copy-account-number">Copy</a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    @else
                                                        <li class="text-center py-3">
                                                            <div class="mb-2 text-warning">
                                                                <i class="ti ti-alert-triangle fs-28"></i>
                                                            </div>
                                                            <p class="text-muted fs-12 mb-3">No active virtual bank accounts found. Create one to fund your wallet instantly.</p>
                                                            <button type="button" class="btn btn-primary btn-sm btn-wave" data-bs-toggle="modal" data-bs-target="#generateVirtualAccountModal">
                                                                Generate Virtual Account
                                                            </button>
                                                        </li>
                                                    @endif
                                                </ul>
                                                <hr>
                                                <small class="fw-semibol mb-2 text-danger">If your funds is not
                                                    received within 30mins Please
                                                    <a href="{{ route('support') }}">Contact Support
                                                        <i class="bx bx-headphone side-menu__icon"></i>
                                                    </a>
                                                </small>

                                                <div class="alert alert-warning-transparent d-flex align-items-center mt-3 fs-12" role="alert">
                                                    <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-14"></i>
                                                    <div>
                                                        <strong>Security Guard:</strong> To prevent duplicate transactions, consecutive deposits of the exact same amount made within 5 minutes will be blocked. Please vary your deposit amount slightly or wait 5 minutes between transfers.
                                                    </div>
                                                </div>
                                                <div class="alert alert-danger alert-dismissible text-center" id="errorMsg"
                                                    style="display:none;" role="alert">
                                                    <small id="message">Processing your request.</small>
                                                </div>
                                                <div class="alert alert-success alert-dismissible text-center"
                                                    id="successMsg" style="display:none;" role="alert">
                                                    <small id="smessage">Processing your request.</small>
                                                </div>


                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
