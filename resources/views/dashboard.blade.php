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
                    <div class="alert alert-outline-light d-flex align-items-center shadow-lg d-none d-md-block"
                        role="alert">
                        <div>
                            <small class="fw-semibold mb-0 fs-15 ">Referral Code :
                                {{ Auth::user()->referral_code }}</small>
                        </div>
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
                                    <div class="col-xxl-4 col-lg-4 col-md-4 ">
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
                                                            {{-- <div class="text-center">
                                                                <a href="{{ route('more-services', 'funding') }}">
                                                                    <img class="img-fluid" width="38%"
                                                                        src="{{ asset('assets/images/apps/fund.png') }}">
                                                                </a>
                                                                <p> Fund wallet</p>
                                                            </div> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-lg-4 col-md-4 d-none d-md-block">
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
                                    <div class="col-xxl-4 col-lg-4 col-md-4 d-none d-md-block">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div><a href="{{ route('transactions') }}">
                                                            <span
                                                                class="avatar avatar-md avatar-rounded bg-danger-transparent">
                                                                <i class="ri-exchange-funds-line fs-16"></i>
                                                            </span>
                                                    </div></a>
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
                                    <div class="col-md-3 text-center d-block d-md-none">
                                        <div class="card custom-card">
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
                                                        <a href="{{ route('more-services', 'transfer') }}"> <img
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
                                                        <a href="{{ route('data') }}"><img
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
                                                        <a href="{{ route('tv') }}"> <img class="img-fluid"
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
                                                        <a href="{{ route('support') }}"> <img class="img-fluid"
                                                                width="22%"
                                                                src="{{ asset('assets/images/apps/support.png') }}">
                                                            <p>Contact Support</p>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-12 d-none d-md-block">
                                        <div class="card custom-card">
                                            <div class="card-header  justify-content-between">
                                                <div class="card-title">
                                                    Virtual Account Numbers
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                @if ($virtual_funding->is_enabled)
                                                    <small class="fw-semibold">Fund your wallet instantly by depositing
                                                        into the virtual account number</small>
                                                    <ul class="list-unstyled crm-top-deals mb-0 mt-3">
                                                        @if ($virtual_accounts != null)
                                                            @foreach ($virtual_accounts as $accounts)
                                                                <li>
                                                                    <div class="d-flex align-items-top flex-wrap">
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
                                                                        <div class="fw-semibold fs-15"><a href="#"
                                                                                class="btn btn-light btn-sm copy-account-number">Copy</a>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                    <hr>
                                                @else
                                                    <p class="fw-bold text-center"> <i
                                                            class="bi bi-slash-circle text-danger">
                                                            &nbsp;</i>Virtual Account
                                                        Disabled</p>
                                                @endif

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
                                                        <a href="{{ route('more-services', 'transfer') }}"> <img
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
                                                        <a href="{{ route('data') }}"><img
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
                                                        <a href="{{ route('tv') }}"> <img class="img-fluid"
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
                                                        <a href="{{ route('support') }}"> <img class="img-fluid"
                                                                width="22%"
                                                                src="{{ asset('assets/images/apps/support.png') }}">
                                                            <p>Contact Support</p>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-12 d-none d-md-block">
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
                                                                            @if ($transaction->status == 'Approved')
                                                                                <span
                                                                                    class="badge bg-outline-success">{{ $transaction->status }}</span>
                                                                            @elseif ($transaction->status == 'Rejected')
                                                                                <span
                                                                                    class="badge bg-outline-danger">{{ $transaction->status }}</span>
                                                                            @elseif ($transaction->status == 'Pending')
                                                                                <span
                                                                                    class="badge bg-outline-warning">{{ $transaction->status }}</span>
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
    <div class="modal fade" id="kycModal" tabindex="-1" aria-labelledby="kycModal" data-bs-keyboard="true"
        data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="staticBackdropLabel2">Verify Account
                    </h6>
                </div>
                <div class="modal-body">
                    We're excited to have you on board! However, we need to verify your identity before activating your
                    account. provide your Identification number below.
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
                <div class="d-flex justify-content-center align-items-center">
                    <div class="col-md-6 col-lg-6">
                        <form id="verify" name="verifyForm" method="POST" action="{{ route('verify-user') }}">
                            @csrf
                            <div class="mb-3">
                                <p class="mb-2 text-muted text-center">Enter your BVN No.</p>
                                <input type="text" id="bvn" name="bvn" class="form-control text-center"
                                    maxlength="11" required />
                            </div>
                            <div class="text-center mb-3 d-flex justify-content-center gap-2">
                                <button type="submit" id="submit" class="btn btn-primary">
                                    <i class="lar la-check-circle"></i> Verify Now
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="text-center mb-3">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="las la-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>


            </div>
        </div>
    </div>


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
            const form = document.getElementById('verify');
            const submitButton = document.getElementById('submit');

            if (form && submitButton) {
                form.addEventListener('submit', function() {
                    submitButton.disabled = true;
                    submitButton.innerText = 'Verifying ...';
                });
            }
        });

        // $(document).ready(function() {
        //     if (!localStorage.getItem("anouncementModalShown")) {
        //         // Show the modal
        //         $("#anouncement").modal("show");

        //         // Set the flag to indicate that the modal has been shown
        //         localStorage.setItem("anouncementModalShown", "true");
        //     }

        //     // When the user clicks on <span> (x), close the modal
        //     $(".close").click(function() {
        //         $("#anouncement").fadeOut();
        //     });

        //     $("#proceed").click(function() {
        //         $("#anouncement").modal("hide");
        //     });

        // });
    </script>
@endpush
