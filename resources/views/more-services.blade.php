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
                                                        <a href="{{ route('buy-data') }}"><img class="img-fluid border rounded"
                                                                width="30%"
                                                                src="{{ asset('assets/images/data-bundle.jpeg') }}">
                                                            <p class=" rounded fw-bold mt-2">Data Bundle</p>
                                                        </a>
                                                    </div>

                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                        <a href="{{ route('buy-sme-data') }}"><img
                                                                class="img-fluid border rounded" width="32%"
                                                                src="{{ asset('assets/images/sme.png') }}">
                                                            <p class=" rounded fw-bold mt-2">SME Data Bundle</p>
                                                        </a>
                                                    </div>
                                                @elseif ($type == 'verifications')
                                                     <div class="col-6 col-md-3 text-center  mt-2">
                                                         <a href="{{ route('bvn.verification.index') }}"> <img class="img-fluid  rounded"
                                                                 width="40%" src="{{ asset('assets/images/BVN.jpeg') }}">
                                                             <p class=" rounded fw-bold mt-2 ">BVN Verification</p>
                                                         </a>
                                                     </div>


                                                     <!-- <div class="col-6 col-md-3 text-center  mt-2">
                               <a href="{{ route('bank') }}"> <img class="img-fluid  rounded" width="32%" src="{{ asset('assets/images/identity.png') }}">
                                 <p class=" rounded fw-bold mt-2">Verify Bank Account</p>
                               </a>
                             </div>-->

                                                     <div class="col-6 col-md-3 text-center  mt-2">
                                                         <a href="{{ route('nin.verification.index') }}"> <img class="img-fluid  rounded"
                                                                 width="40%" src="{{ asset('assets/images/nimc.png') }}">
                                                             <p class=" rounded fw-bold mt-2">Verify NIN </p>
                                                         </a>
                                                     </div>

                                                     <div class="col-6 col-md-3 text-center  mt-2">
                                                         <a href="{{ route('nin.phone.index') }}"> <img class="img-fluid  rounded"
                                                                 width="40%" src="{{ asset('assets/images/nimc.png') }}">
                                                             <p class=" rounded fw-bold mt-2">Verify NIN Phone
                                                                 Number</p>
                                                         </a>
                                                     </div>

                                                     <div class="col-6 col-md-3 text-center  mt-2">
                                                         <a href="{{ route('nin.demo.index') }}"> <img class="img-fluid  rounded"
                                                                 width="40%" src="{{ asset('assets/images/nimc.png') }}">
                                                             <p class=" rounded fw-bold mt-2">Verify NIN Demographic</p>
                                                         </a>
                                                     </div>

                                                     <div class="col-6 col-md-3 text-center  mt-2">
                                                         <a href="{{ route('nin-validation.index') }}"> <img class="img-fluid  rounded"
                                                                 width="40%" src="{{ asset('assets/images/nimc.png') }}">
                                                             <p class=" rounded fw-bold mt-2">NIN Validation</p>
                                                         </a>
                                                     </div>
                                                @elseif ($type == 'agency')
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
                                                        <a href="{{ route('phone.search.index') }}"> <img class="img-fluid rounded"
                                                                width="60%" src="{{ asset('assets/images/bvn.jpg') }}">
                                                            <p class=" fw-bold mt-2">BVN Search by Phone</p>
                                                        </a>
                                                    </div>


                                                    <div class="col-6 col-md-3 text-center  mt-2">
                                                         <a href="{{ route('nin-modification') }}"> <img
                                                                 class="img-fluid rounded" width="40%"
                                                                 src="{{ asset('assets/images/nimc.png') }}">
                                                             <p class="fw-bold mt-1">NIN Modification
                                                             </p>
                                                         </a>
                                                     </div>

                                                      
                                                          
                                                                  
                                                                  
                                                              
                                                              
                                                          
                                                      

                                                      <div class="col-6 col-md-3 text-center  mt-2">
                                                          <a href="{{ route('nin-validation.index') }}"> <img
                                                                  class="img-fluid rounded" width="40%"
                                                                  src="{{ asset('assets/images/nimc.png') }}">
                                                              <p class="fw-bold mt-1">NIN Validation
                                                              </p>
                                                          </a>
                                                      </div>

                                                      <div class="col-6 col-md-3 text-center  mt-2">
                                                          <a href="{{ route('ipe.index') }}"> <img
                                                                  class="img-fluid rounded" width="40%"
                                                                  src="{{ asset('assets/images/nimc.png') }}">
                                                              <p class="fw-bold mt-1">IPE Clearance
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
