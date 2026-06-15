@extends('layouts.dashboard')
@section('title', 'Contact Support')

@push('page-css')
<style>
    .support-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        background: #fff;
    }
    .form-label-custom {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    .form-control-custom {
        border-radius: 8px;
        padding: 11px 16px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }
    .form-control-custom:focus {
        border-color: #1a4082;
        box-shadow: 0 0 0 0.2rem rgba(26, 64, 130, 0.15);
    }
    .btn-custom {
        border-radius: 8px;
        padding: 11px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-custom:hover {
        transform: translateY(-1px);
    }
    .support-info-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        background: #fff;
        overflow: hidden;
    }
    .support-info-banner {
        height: 80px;
        background: linear-gradient(135deg, #1a4082 0%, #0b3a67 100%);
    }
</style>
@endpush

@section('content')
    <div class="page">

        @include('components.app-header')
        @include('components.app-sidebar')

        <div class="main-content app-content">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                    <div>
                        <p class="fw-semibold fs-18 mb-0 text-primary">Contact Support</p>
                        <span class="text-muted">Have an issue or inquiry? Submit your complaint below and we will respond to your email.</span>
                    </div>
                </div>

                <!-- Alert Messages Container -->
                <div class="mb-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
                            <i class="ti ti-circle-check fs-20 me-2 text-success"></i>
                            <div>{{ session('success') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
                            <i class="ti ti-alert-circle fs-20 me-2 text-danger"></i>
                            <div>{{ session('error') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti ti-alert-circle fs-20 me-2 text-danger"></i>
                                <strong class="fs-14">Validation errors occurred:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                </div>

                <div class="row">
                    <!-- Left: Support Form -->
                    <div class="col-xl-8 col-lg-7 col-md-12 mb-4">
                        <div class="card support-card">
                            <div class="card-header border-bottom-0 pb-0 pt-4 px-4">
                                <h5 class="card-title fw-bold text-dark">Submit Support Ticket</h5>
                            </div>
                            <div class="card-body p-4">
                                <form id="support-form" method="POST" action="{{ route('support.send') }}">
                                    @csrf

                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label-custom">Your Name</label>
                                            <input type="text" class="form-control form-control-custom bg-light" value="{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-custom">Your Email Address</label>
                                            <input type="email" class="form-control form-control-custom bg-light" value="{{ Auth::user()->email }}" disabled>
                                            <small class="text-muted">We will reply to this email address.</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="subject" class="form-label-custom">Subject <span class="text-danger">*</span></label>
                                        <input type="text" name="subject" id="subject" class="form-control form-control-custom" value="{{ old('subject') }}" required placeholder="e.g. Wallet funding delayed, upgrade issue...">
                                    </div>

                                    <div class="mb-4">
                                        <label for="message" class="form-label-custom">Complaint / Message <span class="text-danger">*</span></label>
                                        <textarea name="message" id="message" rows="6" class="form-control form-control-custom" required placeholder="Describe your issue or feedback in detail... If related to a transaction, please include reference numbers.">{{ old('message') }}</textarea>
                                    </div>

                                    <button type="submit" id="submit-ticket" class="btn btn-primary btn-custom px-4 d-flex align-items-center">
                                        <i class="ti ti-send me-2"></i> Send Message
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Info Panel -->
                    <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
                        <div class="card support-info-card">
                            <div class="support-info-banner d-flex align-items-center px-4">
                                <h5 class="text-white mb-0 fw-bold">Support Details</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="avatar avatar-md avatar-rounded bg-primary-transparent me-3" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: rgba(26,64,130,0.1);">
                                            <i class="ti ti-mail text-primary fs-18"></i>
                                        </span>
                                        <div>
                                            <small class="text-muted d-block">Support Email</small>
                                            <a href="mailto:customercare@zepasolutions.com" class="fw-semibold text-dark fs-14">customercare@zepasolutions.com</a>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center mb-3">
                                        <span class="avatar avatar-md avatar-rounded bg-success-transparent me-3" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: rgba(40,167,69,0.1);">
                                            <i class="ti ti-clock text-success fs-18"></i>
                                        </span>
                                        <div>
                                            <small class="text-muted d-block">Support Availability</small>
                                            <span class="fw-semibold text-dark fs-14">24/7 Hours Support</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-md avatar-rounded bg-info-transparent me-3" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: rgba(23,162,184,0.1);">
                                            <i class="ti ti-message-dots text-info fs-18"></i>
                                        </span>
                                        <div>
                                            <small class="text-muted d-block">Response Time</small>
                                            <span class="fw-semibold text-dark fs-14">Typically under 30 minutes</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info-transparent border-0 p-3 fs-13" role="alert" style="background-color: rgba(23,162,184,0.08); color: #0284c7; border-radius: 8px;">
                                    <i class="bi bi-info-circle-fill me-2 fs-14"></i>
                                    <strong>How it works:</strong>
                                    <p class="mb-0 mt-1">Once you submit this form, a ticket will be created and sent to our support desk. We will review your complaint and reply to your registered email address. You will receive notifications directly in your inbox.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('page-js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const supportForm = document.getElementById('support-form');
            const submitBtn = document.getElementById('submit-ticket');

            if (supportForm && submitBtn) {
                supportForm.addEventListener('submit', function() {
                    if (!submitBtn.disabled) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending...';
                    }
                });
            }
        });
    </script>
@endpush
