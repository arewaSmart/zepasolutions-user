@extends('layouts.dashboard')
@section('title', 'Claim Referral Bonus')
@section('content')
    <div class="page">

        @include('components.app-header')
        @include('components.app-sidebar')


        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Start::page-header -->
                <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                    <div>
                        <p class="fw-semibold fs-18 mb-0">Claim Referral Bonus</p>
                        <span class="fs-semibold text-muted">To qualify for a bonus, each referral must complete a minimum of
                            {{ $transaction_count }}
                            transactions. Once this requirement is met, the bonus can be claimed. </span>
                    </div>
                    <div class="alert alert-outline-light d-flex align-items-center shadow-lg" role="alert">
                        <div>
                            <small class="fw-semibold mb-0 fs-15 ">Referral Code : {{ Auth::user()->referral_code }}</small>
                        </div>
                    </div>
                </div>
                <!-- End::page-header -->
                <!-- Start::row-1 -->
                <div class="row">
                    <div class="col-xxl-12 col-xl-12">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xxl-4 col-lg-4 col-md-4">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span
                                                            class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                                            <i class="ti ti-briefcase fs-16"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Referral Bonus</p>
                                                                <h4 class="fw-semibold mt-1">
                                                                    &#x20A6;{{ $bonus_balance }}</h4>
                                                            </div>
                                                            <div id="crm-total-customers"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-lg-4 col-md-4">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span class="avatar avatar-md avatar-rounded bg-danger-transparent">
                                                            <i class="ti ti-exchange fs-16"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Unclaimed Bonus</p>
                                                                <h4 class="fw-semibold mt-1">
                                                                    &#x20A6;{{ $unclaimed_balance }}</h4>
                                                            </div>
                                                            <div id="crm-total-deals"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-lg-4 col-md-4">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span class="avatar avatar-md avatar-rounded bg-info-transparent">
                                                            <i class="ri-exchange-funds-line fs-16"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Claimed Bonus</p>
                                                                <h4 class="fw-semibold mt-1">
                                                                    {{ $claimed_balance }}</h4>
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

                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="card custom-card ">
                                            <div class="card-header justify-content-between">
                                                <div class="card-title">
                                                    Referred Accounts
                                                </div>
                                            </div>

                                            <div class="card-body" style="background:#fafafc;">
                                                @if (session('success'))
                                                    <div class="alert alert-success alert-dismissible fade show"
                                                        role="alert">
                                                        {{ session('success') }}
                                                    </div>
                                                @endif

                                                @if (session('error'))
                                                    <div class="alert alert-danger alert-dismissible fade show"
                                                        role="alert">
                                                        {{ session('error') }}
                                                    </div>
                                                @endif
                                                @if (!$users->isEmpty())
                                                    @php
                                                        $currentPage = $users->currentPage(); // Current page number
                                                        $perPage = $users->perPage(); // Number of items per page
                                                        $serialNumber = ($currentPage - 1) * $perPage + 1; // Starting serial number for current page
                                                    @endphp
                                                    <div class="table-responsive" width="100%">
                                                        <table class="table" style="background:#fafafc !important">
                                                            <thead>
                                                                <tr class="table-primary ">
                                                                    <th>ID</th>
                                                                    <th>Email Address</th>
                                                                    <th>Claim</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                                @foreach ($users as $data)
                                                                    <tr>
                                                                        <th scope="row">{{ $serialNumber++ }}</th>
                                                                        <td>{{ $data->email }}</td>
                                                                        <td>
                                                                            &#8358;
                                                                            {{ number_format($data->total_bonus_amount, 2) }}
                                                                        </td>
                                                                        <td>
                                                                            @if ($data->transactions_count >= $transaction_count && $data->claim_id == 0)
                                                                                <a href="{{ route('claim-bonus', $data->id) }}"
                                                                                    class="btn btn-sm btn-success btn-wave waves-effect waves-light">
                                                                                    <i
                                                                                        class="ri-exchange-funds-line fs-16 align-middle me-1 d-inline-block"></i>Claim
                                                                                </a href>
                                                                            @elseif ($data->claim_id == 1)
                                                                                <span
                                                                                    class="badge bg-outline-primary">Claimed</span>
                                                                            @else
                                                                                <span
                                                                                    class="badge bg-outline-warning">Pending</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                        <!-- Pagination Links -->
                                                        <div class="d-flex justify-content-center">
                                                            {{ $users->links('vendor.pagination.bootstrap-4') }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <center><img width="65%"
                                                            src="{{ asset('assets/images/no-transaction.gif') }}"
                                                            alt=""></center>
                                                    <p class="text-center fw-semibold  fs-15"> You have not referred any
                                                        accounts yet. Invite friends and family to join and earn rewards
                                                        when
                                                        they complete the required number of transactions!</p>
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
