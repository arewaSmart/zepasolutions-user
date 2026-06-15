@extends('layouts.dashboard')
@section('title', 'Verify NIN')
@section('content')

<div class="page">

        @include('components.app-header')
        @include('components.app-sidebar')
        <div class="main-content app-content">
            <div class="container-fluid">

            
    <div class="page-body">
        <div class="container-fluid px-0 px-md-3">
            <div class="page-title mb-3">
                <div class="row g-0 g-md-4">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">NIN Verification</h3>
                        <p class="text-muted small mb-0">Verify NIN instantly and download slips.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-0 px-md-3">
            <div class="row g-0 g-md-4 mt-3">
                <!-- NIN Verification Form -->
                <div class="col-12 col-xl-6 mb-4">
                    <div class="card shadow-sm border-0 rounded-0 rounded-md-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center rounded-0 rounded-top-md-4">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2"></i>Verify NIN</h5>
                            <span class="badge bg-light text-primary fw-semibold">Instant</span>
                        </div>

                        <div class="card-body">
                            <div class="text-center mb-3">
                                <p class="text-muted small mb-0">
                                    Enter the 11-digit NIN number below to verify.
                                </p>
                            </div>

                            {{-- Alerts --}}
                            @if (session('status') && session('message'))
                                <div class="alert alert-{{ session('status') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                                    {{ session('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0 small text-start">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('nin.verification.store') }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">NIN Number <span class="text-danger">*</span></label>
                                        <input class="form-control text-center form-control-lg" name="number_nin" type="text"
                                            placeholder="Enter 11 Digit NIN" maxlength="11" minlength="11" pattern="[0-9]{11}"
                                            required value="{{ old('number_nin') }}">
                                    </div>

                                    <div class="col-12">
                                        <div class="alert alert-info py-2 mb-0 d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">Service Fee:</span>
                                            <strong class="fs-15">₦{{ number_format($verificationPrice ?? 0, 2) }}</strong>
                                        </div>
                                        <div class="text-end mt-1">
                                            <small class="text-muted">
                                                Wallet Balance: <strong class="text-success">₦{{ number_format($wallet->balance ?? 0, 2) }}</strong>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-12 d-grid mt-3">
                                        <button class="btn btn-primary btn-lg fw-semibold d-flex align-items-center justify-content-center" type="submit" id="submitBtn">
                                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" id="submitSpinner"></span>
                                            <i class="bi bi-search me-2" id="submitIcon"></i> Verify Now
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Verification Info -->
                <div class="col-12 col-xl-6 mt-2 mt-md-0">
                    <div class="card shadow-sm border-0 rounded-0 rounded-md-4">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center rounded-0 rounded-top-md-4">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2"></i>Verification Result</h5>
                        </div>

                        <div class="card-body">
                            @if (session('verification'))
                                <div class="alert alert-success d-flex align-items-center mb-4 rounded-3 border-0 bg-success-transparent text-success">
                                    <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Verification Successful</h6>
                                        <small>The record has been verified successfully against the database.</small>
                                    </div>
                                </div>

                                @php
                                    $verificationData = session('verification')['data'] ?? [];
                                @endphp

                                <div class="row g-3 mb-4 align-items-center">
                                    <!-- Passport Photo -->
                                    <div class="col-12 col-md-4 text-center">
                                        <div class="d-inline-block position-relative">
                                            <div class="p-1 border border-2 border-primary rounded-4 bg-white shadow-sm overflow-hidden" style="width: 150px; height: 170px; margin: 0 auto;">
                                                @if (!empty($verificationData['photo']))
                                                    <img src="data:image/jpeg;base64,{{ $verificationData['photo'] }}"
                                                        alt="ID Photo" class="w-100 h-100 rounded-3"
                                                        style="object-fit: cover;">
                                                @else
                                                    <img src="{{ asset('assets/images/corrupt.jpg') }}" alt="No Image"
                                                        class="w-100 h-100 rounded-3" style="object-fit: cover;">
                                                @endif
                                            </div>
                                            <span class="position-absolute top-100 start-50 translate-middle badge bg-success border border-white rounded-pill px-3 py-1 shadow-sm">
                                                <i class="bi bi-shield-fill-check me-1"></i> VERIFIED
                                            </span>
                                        </div>
                                        <div class="mt-4 text-center">
                                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                                                ID: {{ $verificationData['nin'] ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Personal Details -->
                                    <div class="col-12 col-md-8">
                                        <div class="card bg-light border-0 rounded-4 p-3 shadow-none h-100 mb-0">
                                            <div class="row g-3">
                                                <div class="col-6 border-bottom pb-2">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">First Name</small>
                                                    <span class="fw-bold fs-14 text-dark">{{ $verificationData['firstName'] ?? ($verificationData['first_name'] ?? ($verificationData['firstname'] ?? 'N/A')) }}</span>
                                                </div>
                                                <div class="col-6 border-bottom pb-2">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Surname</small>
                                                    <span class="fw-bold fs-14 text-dark">{{ $verificationData['surname'] ?? ($verificationData['last_name'] ?? ($verificationData['lastname'] ?? ($verificationData['lastName'] ?? 'N/A'))) }}</span>
                                                </div>
                                                <div class="col-6 border-bottom pb-2">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Last Name</small>
                                                    <span class="fw-bold fs-14 text-dark">{{ $verificationData['middleName'] ?? ($verificationData['middle_name'] ?? ($verificationData['middlename'] ?? 'N/A')) }}</span>
                                                </div>
                                                <div class="col-6 border-bottom pb-2">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Gender</small>
                                                    <span class="fw-bold fs-14 text-dark">
                                                        @php
                                                            $g = strtolower(trim($verificationData['gender'] ?? ''));
                                                            echo in_array($g, ['m', 'male']) ? 'Male' : (in_array($g, ['f', 'female']) ? 'Female' : 'N/A');
                                                        @endphp
                                                    </span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Date of Birth</small>
                                                    <span class="fw-bold text-dark">
                                                        {{ !empty($verificationData['birthDate'])
                                                            ? \Carbon\Carbon::parse($verificationData['birthDate'])->format('d M, Y')
                                                            : (!empty($verificationData['birthdate'])
                                                                ? \Carbon\Carbon::parse($verificationData['birthdate'])->format('d M, Y')
                                                                : (!empty($verificationData['birthday'])
                                                                    ? \Carbon\Carbon::parse($verificationData['birthday'])->format('d M, Y')
                                                                    : (!empty($verificationData['dob'])
                                                                        ? \Carbon\Carbon::parse($verificationData['dob'])->format('d M, Y')
                                                                        : 'N/A'))) }}
                                                    </span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Phone Number</small>
                                                    <span class="fw-bold text-dark">{{ $verificationData['telephoneNo'] ?? ($verificationData['telephoneno'] ?? ($verificationData['phoneNumber'] ?? ($verificationData['phone'] ?? ($verificationData['phoneno'] ?? 'N/A')))) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h6 class="fw-bold mb-3 text-center text-secondary">Download Slips (Charges Apply)</h6>
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    @if (!empty($verificationData['nin']))
                                        <button onclick="confirmDownload('{{ route('regularSlip', $verificationData['nin']) }}', 'Regular Slip', {{ $regularSlipPrice ?? 0 }})" 
                                            class="btn btn-info btn-wave text-white">
                                            <i class="bi bi-file-earmark-richtext me-1"></i> Regular <br>
                                            <small class="badge bg-dark bg-opacity-25">₦{{ number_format($regularSlipPrice ?? 0, 2) }}</small>
                                        </button>

                                        <button onclick="confirmDownload('{{ route('standardSlip', $verificationData['nin']) }}', 'Standard Slip', {{ $standardSlipPrice ?? 0 }})" 
                                            class="btn btn-secondary btn-wave">
                                            <i class="bi bi-file-earmark-text me-1"></i> Standard <br>
                                            <small class="badge bg-dark bg-opacity-25">₦{{ number_format($standardSlipPrice ?? 0, 2) }}</small>
                                        </button>

                                        <button onclick="confirmDownload('{{ route('premiumSlip', $verificationData['nin']) }}', 'Premium Slip', {{ $premiumSlipPrice ?? 0 }})" 
                                            class="btn btn-primary btn-wave">
                                            <i class="bi bi-file-earmark-richtext me-1"></i> Premium <br>
                                            <small class="badge bg-dark bg-opacity-25">₦{{ number_format($premiumSlipPrice ?? 0, 2) }}</small>
                                        </button>

                                        <button onclick="confirmDownload('{{ route('vninSlip', $verificationData['nin']) }}', 'VNIN Slip', {{ $vninSlipPrice ?? 0 }})" 
                                            class="btn btn-warning btn-wave text-white">
                                            <i class="bi bi-qr-code me-1"></i> VNIN <br>
                                            <small class="badge bg-dark bg-opacity-25">₦{{ number_format($vninSlipPrice ?? 0, 2) }}</small>
                                        </button>
                                    @else
                                        <p class="text-muted small">NIN data not available for slip download.</p>
                                    @endif
                                </div>

                            @else
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <img src="{{ asset('assets/images/search.png') }}" alt="Search Icon" width="130" class="img-fluid opacity-75 mb-3" style="max-height: 130px; object-fit: contain;">
                                    </div>
                                    <h5 class="fw-bold text-dark">Verification Results</h5>
                                    <p class="text-muted mx-auto" style="max-width: 360px;">
                                        Enter the required identity details on the left form and hit search. The verified records will instantly show here.
                                    </p>
                                    <div class="mt-4">
                                        <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">
                                            <i class="bi bi-shield-lock-fill text-success me-1"></i> End-to-end Encrypted
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Slip Download Script -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // AI Voice Notification for Success
        @if (session('status') === 'success')
            window.addEventListener('load', () => {
                const speak = () => {
                    const message = "wow verification is successful Id number is valid";
                    const utterance = new SpeechSynthesisUtterance(message);
                    
                    const voices = window.speechSynthesis.getVoices();
                    if (voices.length === 0) return false;

                    const femaleVoice = voices.find(voice => 
                        voice.name.toLowerCase().includes('female') || 
                        voice.name.toLowerCase().includes('google uk english female') ||
                        voice.name.toLowerCase().includes('samantha') ||
                        voice.name.toLowerCase().includes('victoria')
                    );
                    
                    if (femaleVoice) utterance.voice = femaleVoice;
                    utterance.rate = 1.0;
                    utterance.pitch = 1.1;
                    window.speechSynthesis.speak(utterance);
                    return true;
                };

                if (!speak()) {
                    window.speechSynthesis.onvoiceschanged = speak;
                }
            });
        @endif

        function confirmDownload(url, type, price) {
            Swal.fire({
                title: 'Are you sure?',
                text: `You will be charged ₦${price.toLocaleString()} for the ${type}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, download it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

        $(document).ready(function() {
            $('form').on('submit', function() {
                $('#submitBtn').attr('disabled', true);
                $('#submitSpinner').removeClass('d-none');
                $('#submitIcon').addClass('d-none');
            });
        });
    </script>
            </div>
        </div>
    </div>
@endsection
