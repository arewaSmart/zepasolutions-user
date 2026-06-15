<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>
    <!-- Meta Data -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Easy Verifications for your Business" />
    <meta name="keywords" content="NIMC, BVN, ZEPA, Verification, Airtime,Data,Bills, Identity">
    <meta name="author" content="Zepa Developers">
    <title>ZEPA Solutions - @yield('title') </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- fav icon -->
    <link rel="icon" href="{{ asset('assets/home/images/favicon/favicon.ico') }}" type="image/x-icon">
    <!-- Bootstrap Css -->
    <link id="style" href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Style Css -->
    <link href="{{ asset('assets/css/styles.min.css') }}" rel="stylesheet">
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/custom3.css') }}">
    @stack('page-css')
</head>

<body>
    <!-- start preLoader -->
    <div id="preloader">
        <span class="loader"></span>
    </div>
    <!-- end preLoader -->

    @yield('content')

    <!-- End::app-content -->
    <!-- Footer Start -->
    <footer class="footer mt-auto py-3 bg-white text-center">
        @include('components.footer')
    </footer>
    <!-- Footer End -->
    </div>
    <!-- Scroll To Top -->
    <div class="scrollToTop">
        <span class="arrow"><i class="ri-arrow-up-s-fill fs-20"></i></span>
    </div>
    <div id="responsive-overlay"></div>
    <!-- Scroll To Top -->
    <script src="{{ asset('assets/kyc/js/jquery-3.7.1.min.js') }}"></script>
    <!-- Popper JS -->
    <script src="{{ asset('assets/libs/@popperjs/core/umd/popper.min.js') }}"></script>
    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/defaultmenu.min.js') }}"></script>
    <!-- Sticky JS -->
    <script src="{{ asset('assets/js/sticky.js') }}"></script>
    <!-- Custom JS -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/logout.js') }}"></script>

    <!-- START::Generate Virtual Account Modal -->
    <div class="modal fade" id="generateVirtualAccountModal" tabindex="-1" aria-labelledby="generateVirtualAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-semibold text-primary" id="generateVirtualAccountModalLabel">Activate Virtual Account</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="generateVirtualAccountForm" method="POST" action="{{ route('verify-user') }}">
                    @csrf
                    <div class="modal-body text-start">
                        <p class="text-muted fs-13 mb-3">
                            To generate your virtual account numbers and enable instant bank transfer wallet funding, please verify your Bank Verification Number (BVN).
                        </p>
                        <div class="mb-3">
                            <label for="generate_bvn" class="form-label mb-2 fs-12 fw-semibold d-block text-center">Enter your 11-digit BVN</label>
                            <input type="text" id="generate_bvn" name="bvn" class="form-control text-center fs-16 fw-semibold" maxlength="11" placeholder="BVN Number" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="generateVirtualSubmitBtn">Verify & Activate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END::Generate Virtual Account Modal -->

    @auth
        @if (is_null(auth()->user()->pin) || auth()->user()->pin === '')
            <!-- START::Compulsory Transaction PIN Modal -->
            <div class="modal fade" id="compulsoryPinModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="compulsoryPinModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
                        <div class="modal-header border-0 bg-primary text-white p-4" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-white-transparent text-white me-3" style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2);">
                                    <i class="ti ti-shield-lock fs-22 text-white"></i>
                                </span>
                                <div>
                                    <h5 class="modal-title fw-semibold text-white mb-0" id="compulsoryPinModalLabel">Set Transaction Security PIN</h5>
                                    <small class="text-white-80">This is compulsory to secure your wallet operations</small>
                                </div>
                            </div>
                        </div>
                        <form id="compulsoryPinForm">
                            @csrf
                            <div class="modal-body p-4 text-start">
                                <div class="alert alert-danger d-none" id="compulsoryPinError"></div>
                                <p class="text-muted fs-13 mb-4">
                                    Welcome! To secure your funds, you must set a 4-digit Transaction Security PIN before accessing the application. Please enter your account password first to authorize this action.
                                </p>
                                
                                <div class="mb-3">
                                    <label for="compulsory_password" class="form-label mb-2 fs-12 fw-semibold text-muted">Account Password</label>
                                    <input type="password" id="compulsory_password" name="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label for="compulsory_pin" class="form-label mb-2 fs-12 fw-semibold text-muted">New 4-Digit PIN</label>
                                        <input type="password" id="compulsory_pin" name="pin" class="form-control text-center fw-bold fs-16" maxlength="4" placeholder="****" required pattern="[0-9]{4}">
                                    </div>
                                    <div class="col-6">
                                        <label for="compulsory_pin_confirmation" class="form-label mb-2 fs-12 fw-semibold text-muted">Confirm 4-Digit PIN</label>
                                        <input type="password" id="compulsory_pin_confirmation" name="pin_confirmation" class="form-control text-center fw-bold fs-16" maxlength="4" placeholder="****" required pattern="[0-9]{4}">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 p-4 pt-0 d-grid">
                                <button type="submit" class="btn btn-primary btn-wave py-2 fw-semibold" id="compulsoryPinSubmitBtn">Create Security PIN</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- END::Compulsory Transaction PIN Modal -->
        @elseif (is_null(auth()->user()->password_updated_at) || auth()->user()->password_updated_at->diffInDays(now()) >= 90)
            <!-- START::Compulsory Password Expiry Modal -->
            <div class="modal fade" id="compulsoryPasswordModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="compulsoryPasswordModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
                        <div class="modal-header border-0 bg-danger text-white p-4" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-white-transparent text-white me-3" style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2);">
                                    <i class="ti ti-key fs-22 text-white"></i>
                                </span>
                                <div>
                                    <h5 class="modal-title fw-semibold text-white mb-0" id="compulsoryPasswordModalLabel">Password Update Required</h5>
                                    <small class="text-white-80">Your current password has expired</small>
                                </div>
                            </div>
                        </div>
                        <form id="compulsoryPasswordForm">
                            @csrf
                            <div class="modal-body p-4 text-start">
                                <div class="alert alert-danger d-none" id="compulsoryPasswordError"></div>
                                <p class="text-muted fs-13 mb-4">
                                    Your account password has expired (90-day regular update policy). To protect your wallet assets and personal data, please choose a new password.
                                </p>
                                
                                <div class="mb-3">
                                    <label for="compulsory_current_password" class="form-label mb-2 fs-12 fw-semibold text-muted">Current Password</label>
                                    <input type="password" id="compulsory_current_password" name="current_password" class="form-control" placeholder="Enter current password" required autocomplete="current-password">
                                </div>

                                <div class="mb-3">
                                    <label for="compulsory_new_password" class="form-label mb-2 fs-12 fw-semibold text-muted">New Password (Min 8 chars)</label>
                                    <input type="password" id="compulsory_new_password" name="new_password" class="form-control" placeholder="Enter new password" required autocomplete="new-password">
                                </div>

                                <div class="mb-3">
                                    <label for="compulsory_new_password_confirmation" class="form-label mb-2 fs-12 fw-semibold text-muted">Confirm New Password</label>
                                    <input type="password" id="compulsory_new_password_confirmation" name="new_password_confirmation" class="form-control" placeholder="Confirm new password" required autocomplete="new-password">
                                </div>
                            </div>
                            <div class="modal-footer border-0 p-4 pt-0 d-grid">
                                <button type="submit" class="btn btn-danger btn-wave py-2 fw-semibold" id="compulsoryPasswordSubmitBtn">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- END::Compulsory Password Expiry Modal -->
        @endif
    @endauth

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Virtual account validation script
            const genForm = document.getElementById('generateVirtualAccountForm');
            const genBtn = document.getElementById('generateVirtualSubmitBtn');
            if (genForm && genBtn) {
                genForm.addEventListener('submit', function(e) {
                    const bvnVal = document.getElementById('generate_bvn').value;
                    if (bvnVal.length !== 11 || isNaN(bvnVal)) {
                        e.preventDefault();
                        document.getElementById('generate_bvn').classList.add('is-invalid');
                        return;
                    }
                    document.getElementById('generate_bvn').classList.remove('is-invalid');
                    genBtn.disabled = true;
                    genBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Activating...';
                });
            }

            // Trigger PIN modal if exists
            const pinModalEl = document.getElementById('compulsoryPinModal');
            if (pinModalEl) {
                const pinModal = new bootstrap.Modal(pinModalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
                pinModal.show();

                const pinForm = document.getElementById('compulsoryPinForm');
                const pinSubmit = document.getElementById('compulsoryPinSubmitBtn');
                const pinError = document.getElementById('compulsoryPinError');

                pinForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const pinVal = document.getElementById('compulsory_pin').value;
                    const pinConfVal = document.getElementById('compulsory_pin_confirmation').value;
                    
                    if (pinVal.length !== 4 || isNaN(pinVal)) {
                        pinError.innerText = 'PIN must be exactly 4 digits.';
                        pinError.classList.remove('d-none');
                        return;
                    }

                    if (pinVal !== pinConfVal) {
                        pinError.innerText = 'PIN confirmation does not match.';
                        pinError.classList.remove('d-none');
                        return;
                    }

                    pinError.classList.add('d-none');
                    pinSubmit.disabled = true;
                    pinSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...';

                    fetch("{{ route('pin.create') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            password: document.getElementById('compulsory_password').value,
                            pin: pinVal,
                            pin_confirmation: pinConfVal
                        })
                    })
                    .then(response => response.json().then(data => ({ status: response.status, body: data })))
                    .then(res => {
                        if (res.status === 200 && res.body.success) {
                            pinError.classList.add('d-none');
                            pinSubmit.className = 'btn btn-success py-2 fw-semibold';
                            pinSubmit.innerText = 'PIN Set Successfully!';
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            pinSubmit.disabled = false;
                            pinSubmit.innerText = 'Create Security PIN';
                            pinError.innerText = res.body.message || 'An error occurred. Please try again.';
                            pinError.classList.remove('d-none');
                        }
                    })
                    .catch(err => {
                        pinSubmit.disabled = false;
                        pinSubmit.innerText = 'Create Security PIN';
                        pinError.innerText = 'Network error. Please try again.';
                        pinError.classList.remove('d-none');
                    });
                });
            }

            // Trigger Password modal if exists
            const passModalEl = document.getElementById('compulsoryPasswordModal');
            if (passModalEl) {
                const passModal = new bootstrap.Modal(passModalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
                passModal.show();

                const passForm = document.getElementById('compulsoryPasswordForm');
                const passSubmit = document.getElementById('compulsoryPasswordSubmitBtn');
                const passError = document.getElementById('compulsoryPasswordError');

                passForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const newPass = document.getElementById('compulsory_new_password').value;
                    const confirmPass = document.getElementById('compulsory_new_password_confirmation').value;
                    
                    if (newPass.length < 8) {
                        passError.innerText = 'New password must be at least 8 characters long.';
                        passError.classList.remove('d-none');
                        return;
                    }

                    if (newPass !== confirmPass) {
                        passError.innerText = 'New password confirmation does not match.';
                        passError.classList.remove('d-none');
                        return;
                    }

                    passError.classList.add('d-none');
                    passSubmit.disabled = true;
                    passSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';

                    fetch("{{ route('profile.password') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: 'PUT',
                            current_password: document.getElementById('compulsory_current_password').value,
                            new_password: newPass,
                            new_password_confirmation: confirmPass
                        })
                    })
                    .then(response => response.json().then(data => ({ status: response.status, body: data })))
                    .then(res => {
                        if (res.status === 200 && res.body.success) {
                            passError.classList.add('d-none');
                            passSubmit.className = 'btn btn-success py-2 fw-semibold';
                            passSubmit.innerText = 'Password Updated!';
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            passSubmit.disabled = false;
                            passSubmit.innerText = 'Update Password';
                            passError.innerText = res.body.message || 'An error occurred. Please try again.';
                            passError.classList.remove('d-none');
                        }
                    })
                    .catch(err => {
                        passSubmit.disabled = false;
                        passSubmit.innerText = 'Update Password';
                        passError.innerText = 'Network error. Please try again.';
                        passError.classList.remove('d-none');
                    });
                });
            }

            // Clipboard Copy for Virtual Bank Accounts & Referral Details
            document.addEventListener('click', function(e) {
                // Find copy account button
                const accountBtn = e.target.closest('.copy-account-number');
                if (accountBtn) {
                    e.preventDefault();
                    const parent = accountBtn.closest('.d-flex');
                    if (!parent) return;
                    
                    const acctNoEl = parent.querySelector('.acctno');
                    if (!acctNoEl) return;
                    
                    const accountNo = acctNoEl.innerText.trim();
                    copyToClipboard(accountNo, accountBtn, 'Account Number');
                    return;
                }

                // Find copy referral code button
                const refCodeBtn = e.target.closest('.copy-ref-code');
                if (refCodeBtn) {
                    e.preventDefault();
                    const code = refCodeBtn.getAttribute('data-code');
                    if (code) {
                        copyToClipboard(code, refCodeBtn, 'Referral Code');
                    }
                    return;
                }

                // Find copy referral link button
                const refLinkBtn = e.target.closest('.copy-ref-link');
                if (refLinkBtn) {
                    e.preventDefault();
                    const link = refLinkBtn.getAttribute('data-link');
                    if (link) {
                        copyToClipboard(link, refLinkBtn, 'Referral Link');
                    }
                    return;
                }
            });

            function copyToClipboard(text, button, label) {
                const triggerSuccess = () => {
                    const originalText = button.innerHTML;
                    const originalClasses = button.className;
                    
                    button.innerHTML = '<i class="bi bi-check-lg me-1"></i> Copied!';
                    button.className = button.className.replace(/btn-(outline-)?(light|primary|secondary|info|danger|warning|success|dark)/g, '') + ' btn-success text-white';
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: `${label} Copied!`,
                            html: `<strong>${text}</strong> has been copied to your clipboard.`,
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                    }
                    
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.className = originalClasses;
                    }, 2000);
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(triggerSuccess).catch(err => {
                        console.error('Failed to copy to clipboard, trying fallback:', err);
                        fallbackCopyText(text, triggerSuccess);
                    });
                } else {
                    fallbackCopyText(text, triggerSuccess);
                }
            }

            function fallbackCopyText(text, callback) {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.top = "0";
                textArea.style.left = "0";
                textArea.style.position = "fixed";
                textArea.style.opacity = "0";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        callback();
                    } else {
                        console.error('Fallback copy failed');
                    }
                } catch (err) {
                    console.error('Fallback copy error:', err);
                }
                document.body.removeChild(textArea);
            }
        });
    </script>

    @stack('page-js')
</body>
</html>



