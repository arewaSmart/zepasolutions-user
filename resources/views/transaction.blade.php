@extends('layouts.dashboard')
@section('title', 'Transactions')
@section('content')
    <div class="page">

        @include('components.app-header')
        @include('components.app-sidebar')

        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Page Title -->
                <div class="page-title-box d-flex align-items-center justify-content-between my-4">
                    <div>
                        <h4 class="mb-1 fw-bold text-primary">Transaction History</h4>
                        <p class="text-muted small mb-0">Track and monitor your wallet credits, debits, and service transactions.</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <!-- Filter Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-4">
                                <form method="GET" action="{{ route('transactions') }}">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-muted small text-uppercase">Reference ID</label>
                                            <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                                <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                                                <input type="text" name="reference" class="form-control border-0"
                                                    placeholder="Search by Reference No."
                                                    value="{{ request('reference') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted small text-uppercase">Status</label>
                                            <select name="status" class="form-select shadow-sm border rounded-3">
                                                <option value="">All Statuses</option>
                                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted small text-uppercase">Service Type</label>
                                            <select name="service_type" class="form-select shadow-sm border rounded-3">
                                                <option value="">All Services</option>
                                                <option value="CRM" {{ request('service_type') == 'CRM' ? 'selected' : '' }}>CRM</option>
                                                <option value="Data" {{ request('service_type') == 'Data' ? 'selected' : '' }}>Data</option>
                                                <option value="Slip" {{ request('service_type') == 'Slip' ? 'selected' : '' }}>Slip</option>
                                                <option value="Top up" {{ request('service_type') == 'Top up' ? 'selected' : '' }}>Top up (P2P)</option>
                                                <option value="Wallet Topup" {{ request('service_type') == 'Wallet Topup' ? 'selected' : '' }}>Funding</option>
                                                <option value="Payout" {{ request('service_type') == 'Payout' ? 'selected' : '' }}>Payout</option>
                                                <option value="Upgrade" {{ request('service_type') == 'Upgrade' ? 'selected' : '' }}>Upgrade</option>
                                                <option value="Airtime" {{ request('service_type') == 'Airtime' ? 'selected' : '' }}>Airtime</option>
                                                <option value="Transfer" {{ request('service_type') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                                <option value="Verification" {{ request('service_type') == 'Verification' ? 'selected' : '' }}>Verification</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100 rounded-3 shadow-sm py-2">
                                                <i class="bi bi-funnel-fill me-1"></i> Filter
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Table Card -->
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-0">
                                @if (!$transactions->isEmpty())
                                    @php
                                        $currentPage = $transactions->currentPage();
                                        $perPage = $transactions->perPage();
                                        $serialNumber = ($currentPage - 1) * $perPage + 1;
                                    @endphp
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light text-uppercase fs-12 border-bottom">
                                                <tr>
                                                    <th class="ps-4 py-3" style="width: 5%">#</th>
                                                    <th class="py-3">Reference No</th>
                                                    <th class="py-3">Type</th>
                                                    <th class="py-3">Description</th>
                                                    <th class="py-3">Amount</th>
                                                    <th class="py-3">Date</th>
                                                    <th class="py-3 text-center" style="width: 15%">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($transactions as $data)
                                                    @php
                                                        $typeLower = strtolower($data->type ?? '');
                                                        $isCredit = false;
                                                        if (in_array($typeLower, ['credit', 'deposit'])) {
                                                            $isCredit = true;
                                                        } elseif ($typeLower === 'debit') {
                                                            $isCredit = false;
                                                        } else {
                                                            $desc = strtolower(($data->service_description ?? '') . ' ' . ($data->description ?? ''));
                                                            if (str_contains($desc, 'credited') || str_contains($desc, 'received') || str_contains($desc, 'refund') || str_contains($desc, 'deposit') || str_contains($desc, 'topup') || str_contains($desc, 'top-up') || str_contains($desc, 'funding') || str_contains($desc, 'increment')) {
                                                                $isCredit = true;
                                                            }
                                                        }
                                                        
                                                        $typeLabel = $data->type ? strtoupper($data->type) : ($isCredit ? 'CREDIT' : 'DEBIT');
                                                        
                                                        $statusClass = 'secondary';
                                                        $statusLabel = strtoupper($data->status);
                                                        
                                                        if (in_array(strtolower($data->status), ['approved', 'completed', 'success', 'successful'])) {
                                                            $statusClass = 'success';
                                                        } elseif (in_array(strtolower($data->status), ['rejected', 'failed'])) {
                                                            $statusClass = 'danger';
                                                        } elseif (in_array(strtolower($data->status), ['pending', 'processing'])) {
                                                            $statusClass = 'warning';
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td class="ps-4 fw-semibold text-muted">{{ $serialNumber++ }}</td>
                                                        <td>
                                                            <span class="font-monospace text-dark fw-bold">{{ strtoupper($data->referenceId) }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $isCredit ? 'success' : 'danger' }}-subtle text-{{ $isCredit ? 'success' : 'danger' }} rounded-pill px-2.5 py-1 text-uppercase fw-semibold fs-11">
                                                                {{ $typeLabel }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-dark">{{ $data->service_description }}</span>
                                                        </td>
                                                        <td class="fw-bold fs-15 text-{{ $isCredit ? 'success' : 'danger' }}">
                                                            {{ $isCredit ? '+' : '-' }}₦{{ number_format($data->amount, 2) }}
                                                        </td>
                                                        <td class="text-muted fs-13">
                                                            {{ optional($data->created_at)->format('M d, Y h:i A') ?? 'N/A' }}
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-{{ $statusClass }}-transparent rounded-pill px-3 py-1.5 fw-semibold fs-11">
                                                                {{ $statusLabel }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination Links -->
                                    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
                                        {{ $transactions->appends(request()->input())->links('vendor.pagination.bootstrap-5') }}
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <img width="200" src="{{ asset('assets/images/no-transaction.gif') }}" alt="No transactions" class="img-fluid mb-3">
                                        <h5 class="fw-bold text-muted">No Transactions Found</h5>
                                        <p class="text-muted small">Try adjusting your filters or check back later.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-css')
    <style>
        .fs-11 { font-size: 0.6875rem !important; }
        .fs-12 { font-size: 0.75rem !important; }
        .fs-13 { font-size: 0.8125rem !important; }
        .fs-15 { font-size: 0.9375rem !important; }
        .bg-success-subtle { background-color: rgba(37, 188, 120, 0.1) !important; }
        .bg-danger-subtle { background-color: rgba(235, 78, 107, 0.1) !important; }
        .badge.bg-success-transparent { background-color: rgba(37, 188, 120, 0.15); color: #25bc78; }
        .badge.bg-danger-transparent { background-color: rgba(235, 78, 107, 0.15); color: #eb4e6b; }
        .badge.bg-warning-transparent { background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .badge.bg-secondary-transparent { background-color: rgba(107, 114, 128, 0.15); color: #6b7280; }
        .table > :not(caption) > * > * { border-color: rgba(229, 231, 235, 0.6); }
        .table-light { background-color: #f9fafb !important; }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endpush
