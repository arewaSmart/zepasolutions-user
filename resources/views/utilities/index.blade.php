@extends('layouts.dashboard')
@section('title', $title ?? 'Buy Airtime')
@section('content')
    <div class="page">

        @include('components.app-header')
        @include('components.app-sidebar')

        <div class="main-content app-content">
            <div class="container-fluid">
    @push('page-css')
    <style>
        .network-option {
            cursor: pointer;
            padding: 10px;
            border: 2px solid transparent;
            border-radius: 10px;
            transition: all 0.2s ease-in-out;
        }
        .network-option:hover {
            background-color: #f8f9fa;
        }
        .network-option.active {
            border-color: #df6808ff; /* Bootstrap primary color */
            background-color: #e7f1ff;
        }
        .small-note {
            font-size: 0.8rem;
            color: #6c757d;
        }
        /* Enhanced PIN Modal Styles */
        .modal-content.pin-upgrade-modal {
            border-radius: 20px;
            overflow: hidden;
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .pin-upgrade-header {
            background-color: #1a4082;
            padding: 2.5rem 1.5rem 1.5rem 1.5rem;
            position: relative;
        }
        .pin-upgrade-header .btn-close {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            font-size: 0.8rem;
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .pulsing-icon-container {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
            animation: pulse-border 2s infinite ease-in-out;
        }
        @keyframes pulse-border {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }
        .pin-digit-input-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 1.5rem 0;
        }
        .pin-digit-box {
            width: 50px;
            height: 55px;
            font-size: 1.8rem;
            text-align: center;
            font-weight: bold;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background-color: #fff;
            transition: all 0.2s ease;
        }
        .pin-digit-box:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
            outline: none !important;
        }
        .pin-digit-box:disabled {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
            cursor: not-allowed;
        }
        .transaction-info-card {
            background-color: #fff;
            border-left: 4px solid #1a4082;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            text-align: left;
        }
        .pin-modal-btn-confirm {
            background-color: #1a4082;
            border: 1px solid #1a4082;
            box-shadow: 0 4px 6px rgba(26,64,130,0.2);
            border-radius: 30px;
            font-size: 0.9rem;
            padding: 10px 24px;
            transition: all 0.2s;
        }
        .pin-modal-btn-confirm:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(26,64,130,0.3);
            background-color: #0b3a67;
            border-color: #0b3a67;
        }
        .pin-modal-btn-confirm:active:not(:disabled) {
            transform: translateY(0);
        }
        .pin-modal-btn-cancel {
            border-radius: 30px;
            font-size: 0.9rem;
            padding: 10px 24px;
        }
    </style>
    @endpush

    <div class="row">
        <div class="col-xxl-12 col-xl-12">
            <div class="row mt-3">
              @if(true)
                {{-- Left Column for Airtime Form --}}
                <div class="col-xl-6 mb-3">
                    <div class="card custom-card shadow-sm border-0">
                        <div class="card-header justify-content-between bg-primary text-white rounded-top">
                            <div class="card-title fw-semibold">
                                <i class="bi bi-phone me-2"></i> Buy Airtime
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Alerts -->
                            <div class="mb-4">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show text-center">{!! session('success') !!}</div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show text-center">{{ session('error') }}</div>
                                @endif
                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        <ul class="mb-0 ps-3 small">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <!-- Airtime Form -->
                            <form id="buyAirtimeForm" method="POST" action="{{ route('buyairtime') }}">
                                @csrf
                                <input type="hidden" id="selectedNetwork" name="network" value="{{ old('network') }}">

                                <!-- Network Selection -->
                                <div class="network-selection mb-4">
                                    <h6 class="text-center mb-3 fw-semibold">Select Network Provider</h6>
                                    <div class="row text-center g-3">
                                        @php
                                            $networks = [
                                                'mtn' => ['name' => 'MTN', 'img' => 'mtn.jpg'],
                                                'airtel' => ['name' => 'Airtel', 'img' => 'Airtel.png'],
                                                'glo' => ['name' => 'Glo', 'img' => 'glo.jpg'],
                                                'etisalat' => ['name' => 'etisalat', 'img' => '9Mobile.jpg'],
                                            ];
                                        @endphp
                                        @foreach ($networks as $key => $network)
                                            <div class="col-3">
                                                <div class="network-option d-flex flex-column align-items-center" data-network="{{ $key }}" title="{{ $network['name'] }}">
                                                    <img src="{{ asset('assets/img/apps/' . $network['img']) }}" alt="{{ $network['name'] }}" class="rounded-circle mb-1 shadow-sm" style="width: 45px; height: 45px;">
                                                    <div class="small fw-semibold">{{ $network['name'] }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Phone Number -->
                                <div class="mb-3">
                                    <label for="mobileno" class="form-label fw-semibold">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                                        <input type="tel" id="mobileno" name="mobileno" value="{{ old('mobileno') }}" class="form-control text-center" maxlength="11" pattern="\d{11}" placeholder="Enter 11-digit phone number" required>
                                    </div>
                                    <div id="networkResult" class="mt-1 small-note text-center fw-bold text-primary"></div>
                                </div>

                                <!-- Amount -->
                                <div class="mb-4">
                                    <label for="amount" class="form-label d-flex justify-content-between align-items-center fw-semibold">
                                        <span>Amount</span>
                                        <small class="text-muted">
                                            Balance:
                                            <strong class="text-success">
                                                ₦{{ number_format($wallet->balance ?? 0, 2) }}
                                            </strong>
                                        </small>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold text-secondary">₦</span>
                                        <input type="number" id="amount" name="amount" value="{{ old('amount') }}" class="form-control form-control-lg text-center" min="50" max="10000" placeholder="e.g., 500" required>
                                    </div>
                                </div>

                                <!-- Amount Suggestions -->
                                <div class="amount-suggestions mb-4">
                                    <p class="text-center text-muted small mb-2">Or select a quick amount</p>
                                    <div class="row g-2">
                                        @php $amounts = [100, 200, 500, 1000, 2000, 5000]; @endphp
                                        @foreach ($amounts as $amt)
                                            <div class="col">
                                                <button type="button" class="btn btn-outline-secondary w-100 amount-btn btn-sm" data-amount="{{ $amt }}">
                                                    ₦{{ number_format($amt) }}
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid">
                                    <button type="button" id="buy-airtime" class="btn btn-primary btn-lg fw-semibold">
                                        <i class="bi bi-lightning-charge me-2"></i> Buy Now
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Right Column --}}
                @include('utilities.advert')
            </div>
        </div>
    </div>

    {{-- PIN Confirmation Modal --}}
    <div class="modal fade" id="pinModal" tabindex="-1" aria-labelledby="pinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content pin-upgrade-modal">
                <div class="modal-header d-flex flex-column align-items-center text-center border-0 pin-upgrade-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    
                    <div class="pulsing-icon-container">
                        <i class="bi bi-shield-lock-fill text-white fs-1" style="font-size: 2.2rem !important;"></i>
                    </div>
                    
                    <h5 class="modal-title fw-bold text-white fs-5" id="pinModalLabel">Security Verification</h5>
                    <p class="text-white-50 mb-0 mt-1 small">Confirm authorization for this transaction</p>
                </div>
                
                <div class="modal-body py-4 px-4 text-center bg-light-subtle">
                    <div class="transaction-info-card">
                        <p class="text-muted mb-1 small">Transaction Detail</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-secondary small" id="modal-desc-label">Wallet Debit</span>
                            <span class="fs-5 fw-bold text-dark" style="color: #1a4082;">₦<span id="modal-amount-display">0.00</span></span>
                        </div>
                    </div>

                    <p class="text-secondary small mb-3">
                        Please enter your <strong class="text-primary">4-digit transaction PIN</strong>.
                    </p>
                    
                    <div class="pin-digit-input-container">
                        <input type="password" class="pin-digit-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                        <input type="password" class="pin-digit-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required disabled>
                        <input type="password" class="pin-digit-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required disabled>
                        <input type="password" class="pin-digit-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required disabled>
                    </div>
                    
                    <input type="hidden" name="pin" id="pinInput">
                    
                    <div id="pinError" class="alert alert-danger-subtle text-danger border-0 d-none mt-3 py-2 px-3 rounded-3 small fw-semibold">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Incorrect PIN. Please try again.
                    </div>
                </div>
                
                <div class="modal-footer border-0 justify-content-center pb-4 px-4 bg-light-subtle pt-0">
                    <button type="button" class="btn btn-light pin-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmPinBtn" class="btn btn-primary pin-modal-btn-confirm text-white" disabled>
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="pinLoader" role="status" aria-hidden="true"></span>
                        <span id="confirmPinText">Confirm & Proceed</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
  <div class="container-fluid px-4 mt-4">


     @push('page-js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const networkOptions = document.querySelectorAll('.network-option');
                const selectedNetworkInput = document.getElementById('selectedNetwork');
                const amountInput = document.getElementById('amount');
                const amountButtons = document.querySelectorAll('.amount-btn');
                const phoneInput = document.getElementById('mobileno');
                const networkResultDiv = document.getElementById('networkResult');
                const buyButton = document.getElementById('buy-airtime');
                const confirmButton = document.getElementById('confirmPinBtn');

                // --- Network selection ---
                networkOptions.forEach(option => {
                    option.addEventListener('click', function () {
                        networkOptions.forEach(opt => opt.classList.remove('active'));
                        this.classList.add('active');
                        selectedNetworkInput.value = this.dataset.network;
                    });
                });

                // --- Amount suggestion ---
                amountButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        amountInput.value = this.dataset.amount;
                    });
                });

                // --- Network detection ---
                const networkPrefixes = {
                    'mtn': ['0803','0806','0703','0706','0810','0813','0814','0816','0903','0906','0913','0916','07025','07026','0704','09065'],
                    'glo': ['0805','0807','0705','0811','0815','0905','0915'],
                    'airtel': ['0802','0808','0701','0708','0812','0901','0902','0904','0907','0912'],
                    'etisalat': ['0809','0817','0818','0908','0909']
                };

                phoneInput.addEventListener('input', function () {
                    const phoneNumber = this.value;
                    networkResultDiv.textContent = '';
                    if (phoneNumber.length >= 4) {
                        const prefix = phoneNumber.substring(0, 4);
                        for (const network in networkPrefixes) {
                            if (networkPrefixes[network].includes(prefix)) {
                                networkResultDiv.textContent = `Looks like a ${network.toUpperCase()} number.`;
                                document.querySelector(`.network-option[data-network="${network}"]`)?.click();
                                break;
                            }
                        }
                    }
                });

                // --- Handle Buy Click ---
                buyButton.addEventListener('click', function () {
                    const networkName = (selectedNetworkInput.value || 'Airtime').toUpperCase();
                    const amountVal = parseFloat(amountInput.value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    
                    document.getElementById('modal-desc-label').textContent = `${networkName} Airtime`;
                    document.getElementById('modal-amount-display').textContent = amountVal;

                    const pinModal = new bootstrap.Modal(document.getElementById('pinModal'));
                    pinModal.show();
                });

                // --- Interactive 4-Digit PIN Input ---
                const pinBoxes = document.querySelectorAll('.pin-digit-box');
                const hiddenPinInput = document.getElementById('pinInput');
                const pinError = document.getElementById('pinError');
                const pinLoader = document.getElementById('pinLoader');
                const confirmPinText = document.getElementById('confirmPinText');

                // Reset inputs when modal is closed/opened
                const pinModalEl = document.getElementById('pinModal');
                pinModalEl.addEventListener('show.bs.modal', function () {
                    pinBoxes.forEach((box, i) => {
                        box.value = '';
                        box.disabled = i > 0;
                    });
                    hiddenPinInput.value = '';
                    pinError.classList.add('d-none');
                    confirmButton.disabled = true;
                    confirmPinText.textContent = "Confirm & Proceed";
                    pinLoader.classList.add('d-none');
                    setTimeout(() => pinBoxes[0].focus(), 300);
                });

                pinBoxes.forEach((box, index) => {
                    box.addEventListener('input', function (e) {
                        const val = e.target.value;
                        if (!/^[0-9]$/.test(val)) {
                            e.target.value = '';
                            return;
                        }
                        if (index < pinBoxes.length - 1 && val !== '') {
                            pinBoxes[index + 1].disabled = false;
                            pinBoxes[index + 1].focus();
                        }
                        updatePinValue();
                    });

                    box.addEventListener('keydown', function (e) {
                        if (e.key === 'Backspace') {
                            if (box.value === '' && index > 0) {
                                pinBoxes[index - 1].focus();
                                pinBoxes[index].disabled = true;
                                pinBoxes[index - 1].value = '';
                            } else {
                                box.value = '';
                            }
                            updatePinValue();
                        }
                    });

                    box.addEventListener('paste', function (e) {
                        e.preventDefault();
                        const pastedData = e.clipboardData.getData('text').trim();
                        if (/^[0-9]{4}$/.test(pastedData)) {
                            pinBoxes.forEach((inp, i) => {
                                inp.disabled = false;
                                inp.value = pastedData[i];
                            });
                            pinBoxes[pinBoxes.length - 1].focus();
                            updatePinValue();
                        }
                    });
                });

                function updatePinValue() {
                    let pin = '';
                    pinBoxes.forEach(box => pin += box.value);
                    hiddenPinInput.value = pin;
                    confirmButton.disabled = pin.length !== 4;
                }

                // --- Confirm PIN & Prevent Double Click ---
                confirmButton.addEventListener('click', function () {
                    const pin = hiddenPinInput.value;

                    this.disabled = true;
                    pinLoader.classList.remove('d-none');
                    confirmPinText.textContent = 'Verifying...';
                    pinError.classList.add('d-none');

                    fetch("{{ route('verify.pin') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ pin })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.valid) {
                            document.getElementById('buyAirtimeForm').submit();
                        } else {
                            pinError.textContent = data.message || 'Incorrect PIN. Please try again.';
                            pinError.classList.remove('d-none');
                            this.disabled = false;
                            pinLoader.classList.add('d-none');
                            confirmPinText.textContent = 'Confirm & Proceed';
                            
                            // Reset and focus first box
                            pinBoxes.forEach((box, i) => {
                                box.value = '';
                                box.disabled = i > 0;
                            });
                            hiddenPinInput.value = '';
                            pinBoxes[0].focus();
                        }
                    })
                    .catch(() => {
                        pinError.textContent = "Network error, please try again.";
                        pinError.classList.remove('d-none');
                        this.disabled = false;
                        pinLoader.classList.add('d-none');
                        confirmPinText.textContent = 'Confirm & Proceed';
                    });
                });
            });
        </script>
    @endpush

            </div>
        </div>
    </div>
@endsection
