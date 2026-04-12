@extends('layouts.dashboard')
@section('title', 'Services')
@push('page-css')
    <style>
        @media (max-width: 576px) {
            .custom-margin-top {
                margin-top: -400px !important;
                /* Adjust the value as needed */

            }
        }
    </style>
@endpush
@section('content')
    <div class="page">

        @include('components.app-header')
        @include('components.app-sidebar')

        <div class="main-content app-content custom-margin-top">
            <div class="container-fluid">

                <!-- End::page-header -->
                <!-- Start::row-1 d-none d-md-block-->
                <div class="row">
                    <div class="col-xxl-12 col-xl-12">

                        <div class="col-xl-12 ">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Our {{ ucwords($type) }} Services
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row ">
                                                @if ($type == 'data')
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('data') }}"><img class="img-fluid border rounded"
                                                                width="30%"
                                                                src="{{ asset('assets/images/data-bundle.jpeg') }}">
                                                            <p class=" rounded fw-bold mt-2">Data Bundle</p>
                                                        </a>
                                                    </div>

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('sme-data') }}"><img
                                                                class="img-fluid border rounded" width="32%"
                                                                src="{{ asset('assets/images/sme.png') }}">
                                                            <p class=" rounded fw-bold mt-2">SME Data Bundle</p>
                                                        </a>
                                                    </div>
                                                @elseif ($type == 'verifications')
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('bvn') }}"> <img class="img-fluid  rounded"
                                                                width="40%" src="{{ asset('assets/images/BVN.jpeg') }}">
                                                            <p class=" rounded fw-bold mt-2 ">BVN Verification</p>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('bvn2') }}"> <img class="img-fluid  rounded"
                                                                width="40%" src="{{ asset('assets/images/BVN.jpeg') }}">
                                                            <p class=" rounded fw-bold mt-2 ">BVN Verification V2</p>
                                                        </a>
                                                    </div>

                                                    <!-- <div class="col-6 col-md-3 text-center  mt-2">
                              <a href="{{ route('bank') }}"> <img class="img-fluid  rounded" width="32%" src="{{ asset('assets/images/identity.png') }}">
                                <p class=" rounded fw-bold mt-2">Verify Bank Account</p>
                              </a>
                            </div>-->

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('nin') }}"> <img class="img-fluid  rounded"
                                                                width="40%" src="{{ asset('assets/images/nimc.png') }}">
                                                            <p class=" rounded fw-bold mt-2">Verify NIN </p>
                                                        </a>
                                                    </div>

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('nin2') }}"> <img class="img-fluid  rounded"
                                                                width="40%" src="{{ asset('assets/images/nimc.png') }}">
                                                            <p class="mt-2 fw-bold ">Verify NIN V2</p>
                                                        </a>
                                                    </div>

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('nin-phone') }}"> <img class="img-fluid  rounded"
                                                                width="40%" src="{{ asset('assets/images/nimc.png') }}">
                                                            <p class=" rounded fw-bold mt-2">Verify NIN Phone
                                                                Number</p>
                                                        </a>
                                                    </div>


                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('nin-track') }}"> <img class="img-fluid  rounded"
                                                                width="40%" src="{{ asset('assets/images/nimc.png') }}">
                                                            <p class=" rounded fw-bold mt-2">Verify NIN Tracking
                                                                Number</p>
                                                        </a>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('nin-demo') }}"> <img class="img-fluid  rounded"
                                                                width="40%" src="{{ asset('assets/images/nimc.png') }}">
                                                            <p class=" rounded fw-bold mt-2">Verify NIN Demographic</p>
                                                        </a>
                                                    </div>
                                                @elseif ($type == 'agency')
                                                    <p class="text-danger">To access our agency services, please
                                                        upgrade your account. Simply navigate to the dashboard page and
                                                        submit an upgrade request. Our team will assist you with the
                                                        next steps. Thank you for choosing our services!</p>
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('bvn-modification') }}"> <img
                                                                class="img-fluid  rounded" width="60%"
                                                                src="{{ asset('assets/images/bvn.jpg') }}">
                                                            <p class=" rounded fw-bold mt-2">BVN Modification</p>
                                                        </a>
                                                    </div>

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('crm') }}"> <img class="img-fluid rounded"
                                                                width="60%" src="{{ asset('assets/images/bvn.jpg') }}">
                                                            <p class="fw-bold mt-2">CRM Request</p>
                                                        </a>
                                                    </div>

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('account-upgrade') }}"> <img
                                                                class="img-fluid rounded" width="50%"
                                                                src="{{ asset('assets/images/bank.jpg') }}">
                                                            <p class="fw-bold mt-2">Account Upgrade</p>
                                                        </a>
                                                    </div>

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('crm2') }}"> <img class="img-fluid rounded"
                                                                width="60%" src="{{ asset('assets/images/bvn.jpg') }}">
                                                            <p class=" fw-bold mt-2">Find BVN Using Phone and DOB</p>
                                                        </a>
                                                    </div>

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('bvn-enrollment') }}"> <img
                                                                class="img-fluid rounded" width="60%"
                                                                src="{{ asset('assets/images/bvn.jpg') }}">
                                                            <p class="fw-bold mt-2">BVN Enrollement
                                                            </p>
                                                        </a>
                                                    </div>

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('nin-services') }}"> <img
                                                                class="img-fluid rounded" width="40%"
                                                                src="{{ asset('assets/images/nimc.png') }}">
                                                            <p class="fw-bold mt-1">NIN Service
                                                            </p>
                                                        </a>
                                                    </div>

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('vnin-to-nibss') }}"> <img
                                                                class="img-fluid rounded" width="40%"
                                                                src="{{ asset('assets/images/nimc.png') }}">
                                                            <p class="fw-bold mt-1">VNIN to NIBSS
                                                            </p>
                                                        </a>
                                                    </div>
                                                @elseif ($type == 'funding')
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('funding') }}"><img
                                                                class="img-fluid border rounded" width="35%"
                                                                src="{{ asset('assets/images/fund_wallet.png') }}">
                                                            <p class=" rounded fw-bold mt-2">Wallet Funding</p>
                                                        </a>
                                                    </div>



                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('claim') }}"><img
                                                                class="img-fluid border rounded" width="33%"
                                                                src="{{ asset('assets/images/referral_bonus.jpg') }}">
                                                            <p class=" rounded fw-bold mt-2">Claim Bonus</p>
                                                        </a>
                                                    </div>
                                                @elseif ($type == 'transfer')
                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('p2p') }}"><img
                                                                class="img-fluid border rounded" width="31%"
                                                                src="{{ asset('assets/images/p2p.jpg') }}">
                                                            <p class=" rounded fw-bold mt-2">Transfer to Zepa</p>
                                                        </a>
                                                    </div>

                                                    {{-- <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('transfer') }}"><img
                                                                class="img-fluid border rounded" width="31%"
                                                                src="{{ asset('assets/images/bank-img.png') }}">
                                                            <p class=" rounded fw-bold mt-2">Transfer to Bank</p>
                                                        </a>
                                                    </div> --}}
                                                @endif

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
