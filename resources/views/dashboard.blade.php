@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('content')
    <div class="page">

        @include('components.app-header')
        @include('components.app-sidebar')

        <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">
                {{-- @include('components.news') --}}

                <!-- Start::page-header -->
                <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                    <div>
                        <p class="fw-semibold fs-18 mb-0">Welcome back, {{ Auth::user()->first_name }} !</p>
                        <span class="fs-semibold text-muted">Centralize your workflow and track all your activities,
                            from start to finish.</span>
                    </div>
                </div>
                @if ($note != '')
                    <div class="alert alert-secondary shadow-sm alert-dismissible fade show text-dark">
                        {!! $note->notes !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i
                                class="bi bi-x"></i></button>
                    </div>
                @endif
                @if ($status == 'Pending')
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        We're excited to have you on board! However, we need to verify your identity before activating your
                        account. Simply click the link below to complete the verification process<br> <a type="button"
                            class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#kycModal">
                            Verify KYC Status
                        </a>
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {!! session('success') !!}
                    </div>
                @endif
                <!-- End::page-header -->
                <!-- Start::row-1 -->
                <div class="row">
                    <div class="col-xxl-12 col-xl-12">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <a href="{{ route('funding') }}">
                                                            <span
                                                                class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                                                <i class="ti ti-wallet fs-16"></i>
                                                            </span>
                                                        </a>
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
                                    <div class="col-6 col-md-4 mb-3 mb-md-0">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <a href="{{ route('claim') }}">
                                                            <span
                                                                class="avatar avatar-md avatar-rounded bg-info-transparent">
                                                                <i class="ti ti-briefcase fs-16"></i>
                                                            </span>
                                                        </a>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Unclaimed Bonus</p>
                                                                <h4 class="fw-semibold mt-1">
                                                                    &#x20A6;{{ $bonus_balance }}</h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 mb-3 mb-md-0">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <a href="{{ route('transactions') }}">
                                                            <span
                                                                class="avatar avatar-md avatar-rounded bg-danger-transparent">
                                                                <i class="ri-exchange-funds-line fs-16"></i>
                                                            </span>
                                                        </a>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Transactions</p>
                                                                <h4 class="fw-semibold mt-1">
                                                                    {{ number_format($transaction_count) }}
                                                                </h4>
                                                            </div>
                                                            <div id="crm-total-deals"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="row">
                                    <div class="col-12 d-block d-md-none mb-3">
                                        <div class="card custom-card border-0 shadow-sm" style="border-radius: 16px;">
                                            <div class="card-body p-3">
                                                <h6 class="fw-semibold mb-3 text-dark px-1">Quick Services</h6>
                                                <div class="row g-2">
                                                    <div class="col-6 px-1">
                                                        <a href="{{ route('more-services', 'funding') }}" class="mobile-service-link text-decoration-none">
                                                            <img class="img-fluid mb-2" src="{{ asset('assets/images/apps/fund.png') }}" style="max-height: 28px; width: auto;">
                                                            <span class="fs-12 fw-medium text-dark">Fund Wallet</span>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 px-1">
                                                        <a href="{{ route('p2p') }}" class="mobile-service-link text-decoration-none">
                                                            <img class="img-fluid mb-2" src="{{ asset('assets/images/apps/transfer.png') }}" style="max-height: 28px; width: auto;">
                                                            <span class="fs-12 fw-medium text-dark">Transfer</span>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 px-1">
                                                        <a href="{{ route('airtime') }}" class="mobile-service-link text-decoration-none">
                                                            <img class="img-fluid mb-2" src="{{ asset('assets/images/apps/airtime.png') }}" style="max-height: 28px; width: auto;">
                                                            <span class="fs-12 fw-medium text-dark">Buy Airtime</span>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 px-1">
                                                        <a href="{{ route('buy-data') }}" class="mobile-service-link text-decoration-none">
                                                            <img class="img-fluid mb-2" src="{{ asset('assets/images/apps/data.png') }}" style="max-height: 28px; width: auto;">
                                                            <span class="fs-12 fw-medium text-dark">Internet Data</span>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 px-1">
                                                        <a href="{{ route('electricity') }}" class="mobile-service-link text-decoration-none">
                                                            <img class="img-fluid mb-2" src="{{ asset('assets/images/apps/electric.png') }}" style="max-height: 28px; width: auto;">
                                                            <span class="fs-12 fw-medium text-dark">Electricity Bills</span>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 px-1">
                                                        <a href="{{ route('cable') }}" class="mobile-service-link text-decoration-none">
                                                            <img class="img-fluid mb-2" src="{{ asset('assets/images/apps/tv.png') }}" style="max-height: 28px; width: auto;">
                                                            <span class="fs-12 fw-medium text-dark">TV Subscription</span>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 px-1">
                                                        <a href="{{ route('education') }}" class="mobile-service-link text-decoration-none">
                                                            <img class="img-fluid mb-2" src="{{ asset('assets/images/apps/education.png') }}" style="max-height: 28px; width: auto;">
                                                            <span class="fs-12 fw-medium text-dark">Educational Pin</span>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 px-1">
                                                        <a href="{{ route('more-services', 'verifications') }}" class="mobile-service-link text-decoration-none">
                                                            <img class="img-fluid mb-2" src="{{ asset('assets/images/apps/identity.png') }}" style="max-height: 28px; width: auto;">
                                                            <span class="fs-12 fw-medium text-dark">Verification</span>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 px-1">
                                                        <a href="{{ route('more-services', 'agency') }}" class="mobile-service-link text-decoration-none">
                                                            <img class="img-fluid mb-2" src="{{ asset('assets/images/apps/modify.png') }}" style="max-height: 28px; width: auto;">
                                                            <span class="fs-12 fw-medium text-dark">Agency Services</span>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 px-1">
                                                        <a href="{{ route('support') }}" class="mobile-service-link text-decoration-none">
                                                            <img class="img-fluid mb-2" src="{{ asset('assets/images/apps/support.png') }}" style="max-height: 28px; width: auto;">
                                                            <span class="fs-12 fw-medium text-dark">Contact Support</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-12">
                                        <div class="card custom-card">
                                            <div class="card-header justify-content-between">
                                                <div class="card-title">
                                                    Virtual Account Numbers
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <small class="fw-semibold">Fund your wallet instantly by depositing
                                                    into the virtual account number</small>
                                                <ul class="list-unstyled crm-top-deals mb-0 mt-3">
                                                    @if ($virtual_accounts != null && count($virtual_accounts) > 0)
                                                        @foreach ($virtual_accounts as $accounts)
                                                            <li>
                                                                <div class="d-flex align-items-top flex-wrap mb-3">
                                                                    <div class="me-2">
                                                                        <span class="avatar avatar-sm avatar-rounded">
                                                                            @if ($accounts->bankName == 'Wema bank')
                                                                                <img src="{{ asset('assets/images/wema.jpg') }}"
                                                                                    alt="">
                                                                            @elseif($accounts->bankName == 'Moniepoint Microfinance Bank')
                                                                                <img src="{{ asset('assets/images/moniepoint.jpg') }}"
                                                                                    alt="">
                                                                            @elseif($accounts->bankName == 'PalmPay')
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
                                                                            {{ $accounts->accountName }}</p>
                                                                        <span
                                                                            class="fs-14 acctno">{{ $accounts->accountNo }}</span>
                                                                        <br>
                                                                        <span class=" fs-12">{{ $accounts->bankName }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="fw-semibold fs-15">
                                                                        <a href="#" class="btn btn-primary btn-sm copy-account-number">
                                                                            <i class="bi bi-files me-1"></i> Copy
                                                                        </a>
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

                                                <center>
                                                    <a href="{{ route('support') }}">
                                                        <small class="fw-semibol text-danger">If your funds is not
                                                            received within 30mins.
                                                            Please Contact Support
                                                            <i class="bx bx-headphone side-menu__icon"
                                                                style="font-size:24px"></i>
                                                        </small> </a>
                                                </center>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-12 mb-3">
                                        <!-- Refer & Earn Card -->
                                        <div class="card custom-card overflow-hidden text-white shadow-lg" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px;">
                                            <div class="card-body p-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <span class="avatar avatar-md avatar-rounded bg-success-transparent me-3">
                                                        <i class="bi bi-gift-fill text-success fs-18"></i>
                                                    </span>
                                                    <h5 class="fw-semibold mb-0 text-white">Refer & Earn Bonus!</h5>
                                                </div>
                                                <p class="text-muted fs-12 mb-3">
                                                    Share your referral code or registration link with friends and earn commissions on their transactions!
                                                </p>
                                                <div class="p-3 rounded mb-3 text-center border border-dashed border-secondary" style="background: rgba(255,255,255,0.04);">
                                                    <small class="text-muted d-block mb-1 fs-10 fw-semibold">YOUR REFERRAL CODE</small>
                                                    <h3 class="fw-bold text-success mb-2 tracking-wider" id="refCodeText">{{ Auth::user()->referral_code }}</h3>
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        <button type="button" class="btn btn-sm copy-ref-code" data-code="{{ Auth::user()->referral_code }}" style="color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.4) !important; background-color: transparent;">
                                                            <i class="bi bi-files me-1"></i> Copy Code
                                                        </button>
                                                        <button type="button" class="btn btn-primary btn-sm copy-ref-link" data-link="{{ url('/register') . '?ref=' . Auth::user()->referral_code }}">
                                                            <i class="bi bi-link-45deg me-1"></i> Copy Link
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xl-8">
                                <div class="row">
                                    <div class="col-xl-12 d-none d-md-block ">
                                        <div class="card custom-card">
                                            <div class="card-header justify-content-between">
                                                <div class="card-title">
                                                    Our Services
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row ">
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('more-services', 'funding') }}"> <img
                                                                class="img-fluid" width="22%"
                                                                src="{{ asset('assets/images/apps/fund.png') }}">
                                                            <p>Fund Wallet</p>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('p2p') }}"> <img
                                                                class="img-fluid" width="22%"
                                                                src="{{ asset('assets/images/apps/transfer.png') }}">
                                                            <p>Transfer</p>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('airtime') }}"><img class="img-fluid"
                                                                width="22%"
                                                                src="{{ asset('assets/images/apps/airtime.png') }}">
                                                            <p>Buy Airtime</p>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('buy-data') }}"><img
                                                                class="img-fluid" width="22%"
                                                                src="{{ asset('assets/images/apps/data.png') }}">
                                                            <p>Buy Internet Data</p>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('electricity') }}"> <img class="img-fluid"
                                                                width="22%"
                                                                src="{{ asset('assets/images/apps/electric.png') }}">
                                                            <p>Pay Electricity Bills</p>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('cable') }}"> <img class="img-fluid"
                                                                width="22%"
                                                                src="{{ asset('assets/images/apps/tv.png') }}">
                                                            <p>Pay TV Subscription</p>
                                                        </a>
                                                    </div>

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('education') }}"> <img class="img-fluid"
                                                                width="22%"
                                                                src="{{ asset('assets/images/apps/education.png') }}">
                                                            <p>Educational Pin</p>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('more-services', 'verifications') }}"> <img
                                                                class="img-fluid" width="22%"
                                                                src="{{ asset('assets/images/apps/identity.png') }}">
                                                            <p>Verification</p>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('more-services', 'agency') }}"> <img
                                                                class="img-fluid" width="22%"
                                                                src="{{ asset('assets/images/apps/modify.png') }}">
                                                            <p>Agency Services</p>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('support') }}"> <img class="img-fluid" width="22%"
                                                                src="{{ asset('assets/images/apps/support.png') }}">
                                                            <p>Contact Support</p>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-12">
                                        <div class="card custom-card ">
                                            <div class="card-header justify-content-between">
                                                <div class="card-title">
                                                    Recent Transactions
                                                </div>
                                            </div>
                                            <div class="card-body" style="background:#fafafc;">
                                                @if (!$transactions->isEmpty())
                                                    @php
                                                        $currentPage = $transactions->currentPage();
                                                        $perPage = $transactions->perPage();
                                                        $serialNumber = ($currentPage - 1) * $perPage + 1;
                                                    @endphp
                                                    <div class="table-responsive">
                                                        <table class="table text-nowrap"
                                                            style="background:#fafafc !important">
                                                            <thead>
                                                                <tr class="table-primary">
                                                                    <th width="5%" scope="col">ID</th>
                                                                    <th scope="col">Date</th>
                                                                    <th scope="col">Type</th>
                                                                    <th scope="col">Status</th>
                                                                    <th scope="col">Description</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php $i = 1; @endphp
                                                                @foreach ($transactions as $transaction)
                                                                    <tr>
                                                                        <th scope="row">{{ $serialNumber++ }}</th>
                                                                        <td>{{ date('F j, Y', strtotime($transaction->created_at)) }}
                                                                        </td>
                                                                        <td>{{ $transaction->service_type }}</td>
                                                                        <td>
                                                                            @if (in_array(strtolower($transaction->status), ['approved', 'completed', 'success', 'successful']))
                                                                                <span
                                                                                    class="badge bg-outline-success">{{ ucfirst($transaction->status) }}</span>
                                                                            @elseif (in_array(strtolower($transaction->status), ['rejected', 'failed']))
                                                                                <span
                                                                                    class="badge bg-outline-danger">{{ ucfirst($transaction->status) }}</span>
                                                                            @elseif (in_array(strtolower($transaction->status), ['pending', 'processing']))
                                                                                <span
                                                                                    class="badge bg-outline-warning">{{ ucfirst($transaction->status) }}</span>
                                                                            @else
                                                                                 <span
                                                                                     class="badge bg-outline-secondary">{{ ucfirst($transaction->status) }}</span>
                                                                             @endif
                                                                        </td>
                                                                        <td>{{ $transaction->service_description }}
                                                                        </td>
                                                                    </tr>
                                                                    @php $i++ @endphp
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                        <!-- Pagination Links -->
                                                        <div class="d-flex justify-content-center">
                                                            {{ $transactions->links('vendor.pagination.bootstrap-4') }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <center><img width="65%"
                                                            src="{{ asset('assets/images/no-transaction.gif') }}"
                                                            alt=""></center>
                                                    <p class="text-center fw-semibold  fs-15"> No Available Transaction
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}"><img src="{{ asset('assets/images/advert1.jpg') }}"
                        class="mt-1 mb-3 img-fluid"></a>
            </div>

        </div>
    </div>

    <!-- START::KYC Onboarding Modal -->
    <div class="modal fade" id="kycModal" tabindex="-1" aria-labelledby="kycModal" data-bs-keyboard="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h6 class="modal-title fw-semibold text-primary" id="kycModalTitle">Complete Profile Onboarding</h6>
                    <span class="badge bg-primary-transparent text-primary" id="kycStepBadge">Step 1 of 3</span>
                </div>
                
                @if ($errors->any())
                    <div class="alert alert-danger mx-3 mt-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <form id="kycMultiStepForm" method="POST" action="{{ route('verify-user') }}">
                    @csrf
                    <div class="modal-body">
                        <!-- STEP 1: PERSONAL DETAILS -->
                        <div class="kyc-step-content" id="step-1-content">
                            <div class="alert alert-info text-center py-2 mb-3">
                                <small class="fw-semibold">Provide your personal details accurately to continue.</small>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label for="kyc_first_name" class="form-label mb-1 fs-12 fw-semibold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" id="kyc_first_name" name="first_name" class="form-control" placeholder="First Name" required value="{{ old('first_name') }}" />
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="kyc_last_name" class="form-label mb-1 fs-12 fw-semibold">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" id="kyc_last_name" name="last_name" class="form-control" placeholder="Last Name" required value="{{ old('last_name') }}" />
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label for="kyc_middle_name" class="form-label mb-1 fs-12 fw-semibold">Middle Name <span class="text-muted">(Optional)</span></label>
                                    <input type="text" id="kyc_middle_name" name="middle_name" class="form-control" placeholder="Middle Name" value="{{ old('middle_name') }}" />
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="kyc_gender" class="form-label mb-1 fs-12 fw-semibold">Gender <span class="text-danger">*</span></label>
                                    <select id="kyc_gender" name="gender" class="form-select" required>
                                        <option value="" disabled selected>Select Gender</option>
                                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="kyc_dob" class="form-label mb-1 fs-12 fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" id="kyc_dob" name="dob" class="form-control" required value="{{ old('dob') }}" />
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="kyc_phone_number" class="form-label mb-1 fs-12 fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" id="kyc_phone_number" name="phone_number" class="form-control" placeholder="Phone Number" maxlength="11" required value="{{ old('phone_number') }}" />
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2: VIRTUAL ACCOUNT DECISION -->
                        <div class="kyc-step-content d-none" id="step-2-content">
                            <div class="text-center py-3">
                                <div class="mb-3 text-primary">
                                    <i class="ti ti-building-bank fs-40"></i>
                                </div>
                                <h5 class="fw-semibold mb-2">Create a Virtual Bank Account?</h5>
                                <p class="text-muted fs-13 px-3">
                                    A virtual account allows you to fund your wallet instantly via standard bank transfer. You can skip this and create one later if you prefer.
                                </p>
                            </div>
                        </div>

                        <!-- STEP 3: BVN VERIFICATION -->
                        <div class="kyc-step-content d-none" id="step-3-content">
                            <div class="alert alert-info text-center py-2 mb-3">
                                <small class="fw-semibold">Provide your 11-digit BVN to verify identity and generate your virtual account.</small>
                            </div>
                            <div class="mb-3 px-3 text-center">
                                <label for="kyc_bvn" class="form-label mb-2 fs-12 fw-semibold text-center d-block">Enter your BVN No.</label>
                                <input type="text" id="kyc_bvn" name="bvn" class="form-control text-center fs-16 fw-semibold" maxlength="11" placeholder="BVN Number" />
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <div>
                            <button type="button" class="btn btn-secondary d-none" id="kycPrevBtn">Back</button>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="kycNextBtn">Next</button>
                            <button type="button" class="btn btn-outline-danger d-none" id="kycSkipVirtualBtn">No, Skip Virtual Account</button>
                            <button type="button" class="btn btn-primary d-none" id="kycCreateVirtualBtn">Yes, Generate Account</button>
                            <button type="submit" class="btn btn-primary d-none" id="kycSubmitBtn">Verify & Save</button>
                        </div>
                    </div>
                </form>
                
                <div class="px-3 pb-3">
                    <form method="POST" action="{{ route('logout') }}" class="text-center">
                        @csrf
                        <button type="submit" class="btn btn-link text-danger btn-sm text-decoration-none">
                            <i class="las la-sign-out-alt"></i> Cancel Onboarding & Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END::KYC Onboarding Modal -->

    {{-- <div class="modal fade" id="anouncement" aria-labelledby="anouncement" data-bs-keyboard="true"
        data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <!-- Scrollable modal -->
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="staticBackdropLabel2">🔔 Important Notice: VNIN Validation Issue
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <p>Dear Agents,</p>
                    <p>We are currently experiencing an issue with the VNIN (Virtual NIN) validation service. This is impacting two key processes:</p>

                    <p> BVN Modification (Agency Only)</p>
                    <p>VNIN submission to NIBSS</p>

                    <p>Please be assured that we are actively following up with NIMC to resolve the issue as quickly as possible. 🙏</p>
                    <p>✅ All other services are running smoothly and remain unaffected.</p>
                    <p>We sincerely appreciate your patience and understanding as we work to restore full functionality.</p>
                    <p> We're always here for you. ❤️</p>
                    <p></p>
                    <p>Warm regards,</p>
                    <center> <button id="proceed" class="btn btn-primary"> Continue </button></center>
                </div>
            </div>
        </div>
    </div> --}}
@endsection

@push('page-js')
    <script>
        // const marqueeInner = document.querySelector('.marquee-inner');

        // marqueeInner.addEventListener('mouseover', () => {
        //     marqueeInner.style.animationPlayState = 'paused';
        // });

        // marqueeInner.addEventListener('mouseout', () => {
        //     marqueeInner.style.animationPlayState = 'running';
        // });
    </script>
    <script>
        // Trigger modal if KYC is pending
        @if ($kycPending)
            const kycModal = new bootstrap.Modal(document.getElementById('kycModal'));
            kycModal.show();
        @endif
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentStep = 1;
        const form = document.getElementById('kycMultiStepForm');
        
        if (!form) return;

        // Elements
        const step1Content = document.getElementById('step-1-content');
        const step2Content = document.getElementById('step-2-content');
        const step3Content = document.getElementById('step-3-content');
        
        const prevBtn = document.getElementById('kycPrevBtn');
        const nextBtn = document.getElementById('kycNextBtn');
        const skipBtn = document.getElementById('kycSkipVirtualBtn');
        const createBtn = document.getElementById('kycCreateVirtualBtn');
        const submitBtn = document.getElementById('kycSubmitBtn');
        
        const stepBadge = document.getElementById('kycStepBadge');
        const bvnInput = document.getElementById('kyc_bvn');

        // Validation fields for Step 1
        const step1Fields = [
            document.getElementById('kyc_first_name'),
            document.getElementById('kyc_last_name'),
            document.getElementById('kyc_gender'),
            document.getElementById('kyc_dob'),
            document.getElementById('kyc_phone_number')
        ];

        function showStep(step) {
            currentStep = step;
            
            // Hide all step contents
            step1Content.classList.add('d-none');
            step2Content.classList.add('d-none');
            step3Content.classList.add('d-none');
            
            // Hide all actions by default
            prevBtn.classList.add('d-none');
            nextBtn.classList.add('d-none');
            skipBtn.classList.add('d-none');
            createBtn.classList.add('d-none');
            submitBtn.classList.add('d-none');

            if (step === 1) {
                step1Content.classList.remove('d-none');
                nextBtn.classList.remove('d-none');
                stepBadge.innerText = 'Step 1 of 3';
            } else if (step === 2) {
                step2Content.classList.remove('d-none');
                prevBtn.classList.remove('d-none');
                skipBtn.classList.remove('d-none');
                createBtn.classList.remove('d-none');
                stepBadge.innerText = 'Step 2 of 3';
            } else if (step === 3) {
                step3Content.classList.remove('d-none');
                prevBtn.classList.remove('d-none');
                submitBtn.classList.remove('d-none');
                stepBadge.innerText = 'Step 3 of 3';
            }
        }

        // Step 1 Validation
        function validateStep1() {
            let isValid = true;
            step1Fields.forEach(field => {
                if (!field.checkValidity()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            // Phone number length check
            const phone = document.getElementById('kyc_phone_number');
            if (phone.value.length !== 11 || isNaN(phone.value)) {
                phone.classList.add('is-invalid');
                isValid = false;
            }

            return isValid;
        }

        // Input feedback listeners
        step1Fields.forEach(field => {
            field.addEventListener('input', function() {
                if (field.checkValidity()) {
                    field.classList.remove('is-invalid');
                }
            });
        });

        nextBtn.addEventListener('click', function() {
            if (validateStep1()) {
                showStep(2);
            }
        });

        prevBtn.addEventListener('click', function() {
            if (currentStep === 2) {
                showStep(1);
            } else if (currentStep === 3) {
                showStep(2);
            }
        });

        // Skip Virtual Account (No) -> Submit profile directly
        skipBtn.addEventListener('click', function() {
            if (validateStep1()) {
                form.action = "{{ route('kyc.save-profile') }}";
                bvnInput.removeAttribute('required');
                bvnInput.disabled = true; // prevent sending empty bvn
                
                // Show loading state
                skipBtn.disabled = true;
                skipBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                form.submit();
            } else {
                showStep(1);
            }
        });

        // Create Virtual Account (Yes) -> Proceed to Step 3
        createBtn.addEventListener('click', function() {
            form.action = "{{ route('verify-user') }}";
            bvnInput.setAttribute('required', 'required');
            bvnInput.disabled = false;
            showStep(3);
        });

        // Step 3 Submission Validation
        form.addEventListener('submit', function(e) {
            if (currentStep === 3) {
                if (bvnInput.value.length !== 11 || isNaN(bvnInput.value)) {
                    e.preventDefault();
                    bvnInput.classList.add('is-invalid');
                    return;
                }
                bvnInput.classList.remove('is-invalid');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...';
            }
        });

        // Keep step on redirect back with errors
        @if(old('bvn') || session('error'))
            // If there was BVN input, it was step 3
            @if(old('bvn'))
                showStep(3);
            @else
                showStep(1);
            @endif
        @else
            showStep(1);
        @endif
    });
    </script>
@endpush

@push('page-css')
    <style>
        /* Card transition micro-interactions */
        .custom-card {
            transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px;
        }
        .custom-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08) !important;
        }
        .custom-card:hover .avatar-rounded {
            transform: scale(1.1) rotate(5deg);
        }
        .avatar-rounded {
            transition: transform 0.3s ease;
        }

        /* Mobile service link app-like layout */
        .mobile-service-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px 12px;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.01);
            transition: all 0.2s ease-in-out;
            height: 100%;
        }
        .mobile-service-link:active {
            transform: scale(0.95);
            background-color: #f8fafc;
            border-color: rgba(0, 0, 0, 0.1);
        }
        .mobile-service-link img {
            transition: transform 0.3s ease;
        }
        .mobile-service-link:hover img {
            transform: scale(1.1);
        }
        
        /* Copy buttons and referral widgets styles */
        .copy-account-number, .copy-ref-code, .copy-ref-link {
            transition: all 0.2s ease;
        }
    </style>
@endpush
