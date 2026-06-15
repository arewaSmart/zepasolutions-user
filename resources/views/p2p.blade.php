@extends('layouts.dashboard')
@section('title', 'Transfer Funds P2P')
@section('content')
    <div class="page">
        @include('components.app-header')
        @include('components.app-sidebar')
        <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">
                @push('page-css')
                <style>
                    /* Premium P2P Styling */
                    .p2p-card {
                        border-radius: 16px;
                        border: none;
                        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
                        overflow: hidden;
                    }
                    .p2p-header {
                        background: linear-gradient(135deg, #1a4082 0%, #2e5ca8 100%);
                        color: #fff;
                        padding: 1.5rem;
                        border-bottom: none;
                    }
                    .balance-badge {
                        background: rgba(255, 255, 255, 0.15);
                        border: 1px solid rgba(255, 255, 255, 0.25);
                        border-radius: 30px;
                        padding: 6px 16px;
                        font-weight: 600;
                        color: #fff;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                    }
                    .form-section {
                        padding: 2rem;
                    }
                    .info-section {
                        background-color: #f8fafc;
                        border-left: 1px solid #e2e8f0;
                        padding: 2rem;
                        height: 100%;
                    }
                    .input-group-text-custom {
                        background-color: #f1f5f9;
                        border: 1px solid #cbd5e1;
                        color: #475569;
                        font-weight: 600;
                    }
                    .wallet-id-card {
                        background: #fff;
                        border: 1px dashed #cbd5e1;
                        border-radius: 12px;
                        padding: 1.25rem;
                        text-align: center;
                        position: relative;
                        margin-bottom: 2rem;
                    }
                    .guideline-item {
                        display: flex;
                        gap: 12px;
                        margin-bottom: 1.25rem;
                        align-items: flex-start;
                        text-align: left;
                    }
                    .guideline-icon {
                        width: 32px;
                        height: 32px;
                        background-color: #e0e7ff;
                        color: #4f46e5;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        flex-shrink: 0;
                    }
                    .btn-transfer-submit {
                        background-color: #1a4082;
                        border: none;
                        border-radius: 10px;
                        padding: 12px 24px;
                        font-weight: 600;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 12px rgba(26, 64, 130, 0.2);
                    }
                    .btn-transfer-submit:hover {
                        background-color: #0f2e62;
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(26, 64, 130, 0.3);
                    }
                    .btn-transfer-submit:active {
                        transform: translateY(0);
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

                <div class="row justify-content-center mt-4">
                    <div class="col-xxl-10 col-xl-11">
                        
                        <div class="card p2p-card shadow-lg">
                            <div class="card-header p2p-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti ti-wallet text-white fs-24"></i>
                                    <h4 class="card-title mb-0 text-white fw-bold">Wallet Transfer (P2P)</h4>
                                </div>
                                <div class="balance-badge">
                                    <i class="ti ti-piggy-bank"></i>
                                    <span>Balance: ₦{{ number_format(\App\Models\Wallet::where('user_id', Auth::id())->value('balance') ?? 0, 2) }}</span>
                                </div>
                            </div>
                            
                            <div class="row g-0">
                                {{-- Left Column - Transfer Form --}}
                                <div class="col-lg-6">
                                    <div class="form-section">
                                        <h5 class="fw-bold text-dark mb-4">Send Funds Instantly</h5>

                                        @if (session('success'))
                                            <div class="alert alert-success alert-dismissible fade show text-center border-0 py-2 px-3 fs-13 mb-4" role="alert">
                                                {!! session('success') !!}
                                            </div>
                                        @endif

                                        @if (session('error'))
                                            <div class="alert alert-danger alert-dismissible fade show text-center border-0 py-2 px-3 fs-13 mb-4" role="alert">
                                                {{ session('error') }}
                                            </div>
                                        @endif

                                        @if ($errors->any())
                                            <div class="alert alert-danger alert-dismissible fade show border-0 py-2 px-3 fs-13 mb-4" role="alert">
                                                <ul class="mb-0 ps-3">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <form id="transferForm" action="{{ route('transfer-funds') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="pin" id="mainPinInput">

                                            <div class="mb-4">
                                                <label for="Wallet_ID" class="form-label fw-semibold text-secondary fs-13">Recipient Wallet ID</label>
                                                <div class="input-group">
                                                    <span class="input-group-text input-group-text-custom"><i class="ti ti-user fs-16"></i></span>
                                                    <input type="text" id="Wallet_ID" name="Wallet_ID"
                                                        value="" class="form-control form-control-lg text-center"
                                                        placeholder="Enter 11-digit phone number"
                                                        maxlength="11" required />
                                                </div>
                                                <p id="reciever" class="mb-0"></p>
                                            </div>

                                            <div class="mb-4">
                                                <label for="Amount" class="form-label fw-semibold text-secondary fs-13">Amount to Transfer</label>
                                                <div class="input-group">
                                                    <span class="input-group-text input-group-text-custom">₦</span>
                                                    <input type="number" id="Amount" name="Amount"
                                                        value="" class="form-control form-control-lg text-center fw-bold"
                                                        placeholder="0.00"
                                                        min="100" required />
                                                </div>
                                            </div>

                                            <div class="d-grid mt-5">
                                                <button type="button" id="btn-transfer-trigger" class="btn btn-primary btn-transfer-submit text-white btn-lg">
                                                    <i class="ti ti-arrow-narrow-right me-2 fs-16"></i> Initiate Transfer
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- Right Column - Reference & Guidelines --}}
                                <div class="col-lg-6">
                                    <div class="info-section">
                                        <div class="wallet-id-card">
                                            <span class="text-muted small d-block mb-1">Your Wallet ID (Reference)</span>
                                            <h3 class="fw-bold text-primary mb-2" id="userWalletID">{{ Auth::user()->phone_number }}</h3>
                                            <button type="button" class="btn btn-sm btn-light border" onclick="copyWalletId()">
                                                <i class="ti ti-copy me-1"></i> Copy Wallet ID
                                            </button>
                                        </div>

                                        <h6 class="fw-bold text-dark mb-3">P2P Guidelines</h6>
                                        
                                        <div class="guideline-item">
                                            <div class="guideline-icon">1</div>
                                            <div>
                                                <p class="mb-0 fw-semibold text-secondary fs-13">Verify Recipient</p>
                                                <small class="text-muted">Ensure the Wallet ID matches the recipient's phone number. The name verification check will load automatically.</small>
                                            </div>
                                        </div>

                                        <div class="guideline-item">
                                            <div class="guideline-icon">2</div>
                                            <div>
                                                <p class="mb-0 fw-semibold text-secondary fs-13">Enter Amount & Authorize</p>
                                                <small class="text-muted">Type the amount and click "Initiate Transfer". You'll be requested to enter your 4-digit transaction PIN to authenticate.</small>
                                            </div>
                                        </div>

                                        <div class="guideline-item">
                                            <div class="guideline-icon">3</div>
                                            <div>
                                                <p class="mb-0 fw-semibold text-secondary fs-13">Instant Credit</p>
                                                <small class="text-muted">Once confirmed, the funds will be credited to the recipient's wallet balance instantly.</small>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary-transparent rounded p-2 text-primary">
                                                <i class="ti ti-headset fs-24"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 fw-semibold text-secondary fs-13">Need Assistance?</p>
                                                <small class="text-muted">Contact our <a href="#" class="text-primary fw-semibold">Customer Support</a> team for help with any issues.</small>
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
                    <p class="text-white-50 mb-0 mt-1 small">Confirm authorization for this transfer</p>
                </div>
                
                <div class="modal-body py-4 px-4 text-center bg-light-subtle">
                    <div class="transaction-info-card">
                        <p class="text-muted mb-1 small">Transaction Detail</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-secondary small" id="modal-desc-label">P2P Wallet Transfer</span>
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
                    
                    <input type="hidden" id="pinModalInput">
                    
                    <div id="pinError" class="alert alert-danger-subtle text-danger border-0 d-none mt-3 py-2 px-3 rounded-3 small fw-semibold">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Incorrect PIN. Please try again.
                    </div>
                </div>
                
                <div class="modal-footer border-0 justify-content-center pb-4 px-4 bg-light-subtle pt-0">
                    <button type="button" class="btn btn-light pin-modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmPinBtn" class="btn btn-primary pin-modal-btn-confirm text-white" disabled>
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="pinLoader" role="status" aria-hidden="true"></span>
                        <span id="confirmPinText">Confirm & Transfer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-js')
    <script src="{{ asset('assets/js/wallet.js') }}"></script>
    <script>
        // Copy Wallet ID function
        function copyWalletId() {
            const walletId = document.getElementById('userWalletID').innerText;
            navigator.clipboard.writeText(walletId).then(() => {
                alert('Wallet ID copied to clipboard!');
            }).catch(() => {
                alert('Failed to copy Wallet ID.');
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const walletIdInput = document.getElementById('Wallet_ID');
            const amountInput = document.getElementById('Amount');
            const transferTriggerBtn = document.getElementById('btn-transfer-trigger');
            
            const pinModalEl = document.getElementById('pinModal');
            const pinModal = new bootstrap.Modal(pinModalEl);
            const confirmPinBtn = document.getElementById('confirmPinBtn');
            const pinBoxes = document.querySelectorAll('.pin-digit-box');
            const pinModalInput = document.getElementById('pinModalInput');
            const mainPinInput = document.getElementById('mainPinInput');
            
            const pinError = document.getElementById('pinError');
            const pinLoader = document.getElementById('pinLoader');
            const confirmPinText = document.getElementById('confirmPinText');

            // Handle P2P Transfer Button Trigger
            transferTriggerBtn.addEventListener('click', function () {
                const walletIdVal = walletIdInput.value.trim();
                const amountVal = parseFloat(amountInput.value || 0);

                if (!walletIdVal || walletIdVal.length < 10) {
                    alert('Please enter a valid recipient Wallet ID.');
                    walletIdInput.focus();
                    return;
                }

                if (isNaN(amountVal) || amountVal <= 0) {
                    alert('Please enter a valid amount to transfer.');
                    amountInput.focus();
                    return;
                }

                // Update amount display in modal
                document.getElementById('modal-amount-display').textContent = amountVal.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Show modal
                pinModal.show();
            });

            // Interactive Pin Inputs behavior
            pinModalEl.addEventListener('show.bs.modal', function () {
                pinBoxes.forEach((box, i) => {
                    box.value = '';
                    box.disabled = i > 0;
                });
                pinModalInput.value = '';
                mainPinInput.value = '';
                pinError.classList.add('d-none');
                confirmPinBtn.disabled = true;
                confirmPinText.textContent = "Confirm & Transfer";
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
                pinModalInput.value = pin;
                confirmPinBtn.disabled = pin.length !== 4;
            }

            // AJAX PIN Verification and Submission
            confirmPinBtn.addEventListener('click', function () {
                const pin = pinModalInput.value;

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
                        // Set the PIN to the hidden field on the main form and submit it
                        mainPinInput.value = pin;
                        document.getElementById('transferForm').submit();
                    } else {
                        pinError.textContent = data.message || 'Incorrect PIN. Please try again.';
                        pinError.classList.remove('d-none');
                        this.disabled = false;
                        pinLoader.classList.add('d-none');
                        confirmPinText.textContent = 'Confirm & Transfer';
                        
                        // Reset and focus first box
                        pinBoxes.forEach((box, i) => {
                            box.value = '';
                            box.disabled = i > 0;
                        });
                        pinModalInput.value = '';
                        pinBoxes[0].focus();
                    }
                })
                .catch(() => {
                    pinError.textContent = "Network error, please try again.";
                    pinError.classList.remove('d-none');
                    this.disabled = false;
                    pinLoader.classList.add('d-none');
                    confirmPinText.textContent = 'Confirm & Transfer';
                });
            });
        });
    </script>
@endpush
