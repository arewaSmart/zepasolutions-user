@extends('layouts.dashboard')
@section('title', 'Buy Educational Pin')
@section('content')
    <div class="page">

        @include('components.app-header')
        @include('components.app-sidebar')

        <div class="main-content app-content">
            <div class="container-fluid">
    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xxl-12 col-xl-12">
            <div class="row mt-3">
                <!-- Left Column: Purchase Form -->
                <div class="col-xl-4 mb-3">
                    <div class="card custom-card shadow-sm border-0">
                        <div class="card-header justify-content-between bg-primary text-white rounded-top">
                            <div class="card-title fw-semibold">
                                <i class="bi bi-credit-card me-2"></i> Buy Educational Pin
                            </div>
                        </div>
                        <div class="card-body">

                            <center class="mb-3">
                                <img class="img-fluid" src="{{ asset('assets/img/apps/pin.png') }}" width="35%">
                            </center>

                            <p class="text-center text-muted mb-4">
                                Select your educational pin service, choose the type, enter your phone number,
                                and continue to complete your purchase securely.
                            </p>

                            {{-- Alert Messages --}}
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form id="buy-pin" method="POST" action="{{ route('buypin') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Select Service</label>
                                    <select name="service" id="service_id" class="form-select text-center" required>
                                        <option value="">-- Select Service --</option>
                                        <option value="waec">WAEC Result Checker</option>
                                        <option value="waec-registration">WAEC Registration</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Select Type</label>
                                    <select name="type" id="type" class="form-select text-center" required>
                                        <option value="">-- Choose Type --</option>
                                        @foreach ($pins as $p)
                                            <option value="{{ $p->variation_code }}" data-amount="{{ $p->variation_amount }}">
                                                {{ strtoupper($p->name ?? $p->variation_code) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                 {{-- Amount --}}
                                            <div class="mb-4 text-start">
                                                <label for="amount" class="form-label fw-semibold d-flex justify-content-between">
                                                    <span>Amount</span>
                                                    <small class="text-muted">Balance: 
                                                        <strong class="text-success">
                                                            ₦{{ number_format($wallet->balance ?? 0, 2) }}
                                                        </strong>
                                                    </small>
                                                </label>
                                                <input type="text" id="amountToPay" name="amount" readonly class="form-control text-center" />
                                            </div>

                                      <div class="mb-3">
                                    <label class="form-label fw-semibold">Phone Number</label>
                                    <input type="text" id="mobileno" name="mobileno" maxlength="11"
                                           class="form-control text-center" placeholder="Enter 11-digit number" required>
                                </div>

                                <div class="d-grid mt-4">
                                <button type="button" class="btn btn-primary btn-lg fw-semibold"
                                    data-bs-toggle="modal" data-bs-target="#pinModal">
                                        Proceed to Buy
                                     </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Purchase History -->
                <div class="col-xl-8 d-none d-md-block">
                    <div class="card custom-card shadow-sm border-0">
                        <div class="card-header justify-content-between bg-light rounded-top">
                            <div class="card-title fw-semibold">
                                <i class="bi bi-list-task me-2 text-primary"></i> Purchase History
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Below is your recent educational pin purchase history:</p>

                            @if (isset($history) && !$history->isEmpty())
                                <div class="table-responsive">
                                    <table class="table align-middle text-nowrap table-hover">
                                        <thead class="table-primary text-center">
                                            <tr>
                                                <th>Date</th>
                                                <th>Service</th>
                                                <th>Token</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($history as $data)
                                                <tr>
                                                    <td class="text-center">{{ $data->created_at->format('d M Y') }}</td>
                                                    <td>{{ strtoupper($data->network) }}</td>
                                                    <td>
                                                        @php
                                                            // Extract token from description if not in a separate column
                                                            // Assuming format "Educational pin purchase (WAEC) - PIN: xxxx"
                                                            preg_match('/PIN: (.*)/', $data->description, $matches);
                                                            $token = $matches[1] ?? 'N/A';
                                                        @endphp
                                                        <span class="fw-bold text-dark font-monospace">{{ $token }}</span>
                                                    </td>
                                                    <td class="text-end">₦{{ number_format($data->amount, 2) }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success-subtle text-success fw-semibold">Successful</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <!-- Pagination -->
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $history->links('vendor.pagination.bootstrap-4') }}
                                    </div>
                                </div>
                            @else
                                <center>
                                    <img src="{{ asset('assets/img/landing/user3.png') }}" width="55%" alt="">
                                </center>
                              <div class="text-center mt-3">
                                 <p class="fw-semibold text-muted fs-15 mb-2">
                                    No pins purchased yet. Start your educational journey today!
                                 </p>
                                 <p class="text-primary mb-2">
                                    💡 Get instant access to your results and registrations
                                 </p>
                                 <p class="text-success mb-2">
                                    🎓 Secure your academic future with just a few clicks
                                 </p>
                                 <p class="text-info mb-2">
                                    ⚡ Fast, reliable, and available 24/7
                                 </p>
                                 <p class="text-dark">
                                    Buy your first educational pin now and take the first step towards success!
                                 </p>
                              </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


     {{-- PIN Confirmation Modal --}}
<div class="modal fade" id="pinModal" tabindex="-1" aria-labelledby="pinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;border:none;box-shadow:0 15px 35px rgba(0,0,0,0.1);">
            <div class="modal-header d-flex flex-column align-items-center text-center border-0" style="background-color:#1a4082;padding:2.5rem 1.5rem 1.5rem;position:relative;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="position:absolute;top:1.25rem;right:1.25rem;font-size:0.8rem;filter:invert(1) grayscale(100%) brightness(200%);"></button>
                <div style="width:70px;height:70px;background:rgba(255,255,255,0.15);border:2px solid rgba(255,255,255,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem auto;animation:pulse-border 2s infinite ease-in-out;">
                    <i class="bi bi-shield-lock-fill text-white" style="font-size:2.2rem;"></i>
                </div>
                <h5 class="modal-title fw-bold text-white fs-5" id="pinModalLabel">Security Verification</h5>
                <p class="text-white-50 mb-0 mt-1 small">Confirm authorization for this transaction</p>
            </div>
            <div class="modal-body py-4 px-4 text-center bg-light">
                <div style="background:#fff;border-left:4px solid #3b82f6;border-radius:8px;padding:10px 15px;margin-bottom:1.5rem;box-shadow:0 2px 4px rgba(0,0,0,0.02);text-align:left;">
                    <p class="text-muted mb-1 small">Transaction Detail</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-secondary small" id="modal-edu-desc">Educational PIN</span>
                        <span class="fs-5 fw-bold" style="color:#1a4082;">₦<span id="modal-edu-amount">0.00</span></span>
                    </div>
                </div>
                <p class="text-secondary small mb-3">Enter your <strong class="text-primary">4-digit transaction PIN</strong>.</p>
                <div style="display:flex;justify-content:center;gap:12px;margin:1.5rem 0;">
                    <input type="password" class="pin-digit-box" maxlength="1" inputmode="numeric" style="width:50px;height:55px;font-size:1.8rem;text-align:center;font-weight:bold;border:2px solid #e2e8f0;border-radius:12px;background:#fff;transition:all 0.2s;">
                    <input type="password" class="pin-digit-box" maxlength="1" inputmode="numeric" style="width:50px;height:55px;font-size:1.8rem;text-align:center;font-weight:bold;border:2px solid #e2e8f0;border-radius:12px;background:#fff;transition:all 0.2s;" disabled>
                    <input type="password" class="pin-digit-box" maxlength="1" inputmode="numeric" style="width:50px;height:55px;font-size:1.8rem;text-align:center;font-weight:bold;border:2px solid #e2e8f0;border-radius:12px;background:#fff;transition:all 0.2s;" disabled>
                    <input type="password" class="pin-digit-box" maxlength="1" inputmode="numeric" style="width:50px;height:55px;font-size:1.8rem;text-align:center;font-weight:bold;border:2px solid #e2e8f0;border-radius:12px;background:#fff;transition:all 0.2s;" disabled>
                </div>
                <input type="hidden" id="pinInput">
                <div id="pinError" class="alert alert-danger border-0 d-none mt-3 py-2 px-3 small fw-semibold" style="border-radius:10px;background:#fef2f2;color:#dc2626;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> <span id="pinErrorText">Incorrect PIN. Please try again.</span>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4 px-4 bg-light pt-0">
                <button type="button" class="btn btn-light px-4" style="border-radius:30px;font-size:0.9rem;" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmPinBtn" class="btn btn-primary px-4" style="border-radius:30px;font-size:0.9rem;" disabled>
                    <span class="spinner-border spinner-border-sm me-2 d-none" id="pinLoader" role="status" aria-hidden="true"></span>
                    <span id="confirmPinText">Confirm & Proceed</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulse-border {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(255,255,255,0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,255,255,0); }
    }
    .pin-digit-box:focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 4px rgba(59,130,246,0.15) !important; outline: none !important; }
</style>

<script>
(function() {
    const pinBoxes = document.querySelectorAll('.pin-digit-box');
    const hiddenPinInput = document.getElementById('pinInput');
    const pinError = document.getElementById('pinError');
    const pinErrorText = document.getElementById('pinErrorText');
    const pinLoader = document.getElementById('pinLoader');
    const confirmPinText = document.getElementById('confirmPinText');
    const confirmBtn = document.getElementById('confirmPinBtn');

    document.getElementById('pinModal').addEventListener('show.bs.modal', function() {
        pinBoxes.forEach((box, i) => { box.value = ''; box.disabled = i > 0; });
        hiddenPinInput.value = '';
        pinError.classList.add('d-none');
        confirmBtn.disabled = true;
        confirmPinText.textContent = 'Confirm & Proceed';
        pinLoader.classList.add('d-none');
        setTimeout(() => pinBoxes[0].focus(), 300);
    });

    pinBoxes.forEach((box, index) => {
        box.addEventListener('input', function(e) {
            if (!/^[0-9]$/.test(e.target.value)) { e.target.value = ''; return; }
            if (index < pinBoxes.length - 1 && e.target.value) {
                pinBoxes[index + 1].disabled = false; pinBoxes[index + 1].focus();
            }
            updatePin();
        });
        box.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace') {
                if (box.value === '' && index > 0) {
                    pinBoxes[index - 1].focus(); pinBoxes[index].disabled = true; pinBoxes[index - 1].value = '';
                } else { box.value = ''; }
                updatePin();
            }
        });
        box.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasted = e.clipboardData.getData('text').trim();
            if (/^[0-9]{4}$/.test(pasted)) {
                pinBoxes.forEach((inp, i) => { inp.disabled = false; inp.value = pasted[i]; });
                pinBoxes[3].focus(); updatePin();
            }
        });
    });

    function updatePin() {
        let pin = ''; pinBoxes.forEach(b => pin += b.value);
        hiddenPinInput.value = pin;
        confirmBtn.disabled = pin.length !== 4;
    }

    confirmBtn.addEventListener('click', function() {
        const pin = hiddenPinInput.value;
        confirmBtn.disabled = true;
        pinLoader.classList.remove('d-none');
        confirmPinText.textContent = 'Verifying...';
        pinError.classList.add('d-none');

        fetch("{{ route('verify.pin') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ pin })
        })
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                document.getElementById('buy-pin').submit();
            } else {
                pinErrorText.textContent = data.message || 'Incorrect PIN. Please try again.';
                pinError.classList.remove('d-none');
                confirmBtn.disabled = false;
                pinLoader.classList.add('d-none');
                confirmPinText.textContent = 'Confirm & Proceed';
                pinBoxes.forEach((b, i) => { b.value = ''; b.disabled = i > 0; });
                hiddenPinInput.value = ''; pinBoxes[0].focus();
            }
        })
        .catch(err => {
            pinErrorText.textContent = 'Network error. Please try again.';
            pinError.classList.remove('d-none');
            confirmBtn.disabled = false;
            pinLoader.classList.add('d-none');
            confirmPinText.textContent = 'Confirm & Proceed';
        });
    });
})();
</script>

            </div>
        </div>
    </div>
@endsection
