@extends('layouts.dashboard')
@section('title', 'Settings')

@push('page-css')
<style>
    .profile-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        background: #fff;
        transition: all 0.3s ease;
    }
    .profile-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
    }
    .profile-banner {
        height: 120px;
        background: linear-gradient(135deg, #1a4082 0%, #0b3a67 100%);
    }
    .profile-avatar-container {
        margin-top: -60px;
        position: relative;
        display: inline-block;
    }
    .avatar-upload-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background-color: #1a4082;
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }
    .avatar-upload-btn:hover {
        background-color: #0b3a67;
        transform: scale(1.1);
        color: #fff;
    }
    .avatar-loading-overlay {
        position: absolute;
        top: 4px;
        left: 4px;
        width: 102px;
        height: 102px;
        border-radius: 50%;
        background-color: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }
    .profile-avatar {
        border: 4px solid #fff;
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        background-color: #fff;
        transition: all 0.3s ease;
    }
    .profile-avatar:hover {
        transform: scale(1.03);
    }
    .badge-role {
        background-color: rgba(26, 64, 130, 0.1) !important;
        color: #1a4082 !important;
        font-size: 13px;
        letter-spacing: 0.5px;
    }
    .nav-tabs-custom {
        border-bottom: 2px solid #f1f1f4;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        padding: 14px 20px;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #1a4082;
        background: transparent;
    }
    .nav-tabs-custom .nav-link.active {
        color: #1a4082;
        border-bottom: 2px solid #1a4082;
        background: transparent;
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
    .settings-tab-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        background: #fff;
    }
    /* OTP modal styling */
    .modal-content-custom {
        border-radius: 16px;
        border: none;
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    .modal-header-custom {
        background: #1a4082;
        color: #fff;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
        padding: 20px 24px;
    }
    .modal-body-custom {
        padding: 24px;
    }
    .modal-footer-custom {
        border-top: none;
        padding: 16px 24px 24px;
    }
    /* Notification check custom styling */
    .form-switch-custom .form-check-input {
        width: 3.2em;
        height: 1.7em;
        margin-left: -3.8em;
        cursor: pointer;
    }
    .form-switch-custom {
        padding-left: 3.8em;
    }
</style>
@endpush

@section('content')
    <div class="page">

        @include('components.app-header')
        @include('components.app-sidebar')

        <div class="main-content app-content custom-margin-top">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                    <div>
                        <p class="fw-semibold fs-18 mb-0 text-primary">Account Settings</p>
                        <span class="text-muted">Manage your profile, password security, transaction PIN, and notification preferences.</span>
                    </div>
                </div>

                <!-- Alert Messages Container -->
                <div class="mb-4">
                    @if (session('message'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
                            <i class="ti ti-circle-check fs-20 me-2 text-success"></i>
                            <div>{{ session('message') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
                            <i class="ti ti-circle-check fs-20 me-2 text-success"></i>
                            <div>{{ session('status') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

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
                    <!-- Left Column: Profile Card & Notification Settings -->
                    <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
                        
                        <!-- Profile Card -->
                        <div class="card profile-card mb-4">
                            <div class="profile-banner"></div>
                            <div class="card-body text-center pt-0">
                                <div class="profile-avatar-container">
                                    @if (Auth::user()->profile_pic != '')
                                        <img id="profile-pic-preview" alt="avatar" width="110" height="110" class="rounded-circle profile-avatar object-fit-cover"
                                            src="{{ 'data:image/;base64,' . Auth::user()->profile_pic }}">
                                    @else
                                        <img id="profile-pic-preview" alt="avatar" width="110" height="110" class="rounded-circle profile-avatar object-fit-cover"
                                            src="{{ asset('assets/images/zepa-logo.jpg') }}">
                                    @endif
                                    
                                    <!-- Loading Overlay -->
                                    <div id="avatar-loading-overlay" class="avatar-loading-overlay d-none">
                                        <div class="spinner-border spinner-border-sm text-white" role="status"></div>
                                    </div>
                                    
                                    <!-- Camera Overlay Icon -->
                                    <label for="avatar_upload_input" class="avatar-upload-btn" title="Change Profile Picture">
                                        <i class="ti ti-camera fs-16"></i>
                                    </label>
                                    
                                    <!-- Hidden Form for Automatic Avatar Upload -->
                                    <form id="avatar-upload-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="d-none">
                                        @csrf
                                        <input type="file" name="profile_pic" id="avatar_upload_input" accept="image/*">
                                    </form>
                                </div>
                                
                                <h4 class="mt-3 mb-1 fw-bold text-dark">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h4>
                                <p class="text-muted fs-13 mb-3">{{ Auth::user()->email }}</p>
                                <span class="badge badge-role rounded-pill px-3 py-2 fw-semibold">{{ ucfirst(Auth::user()->role) }} Account</span>

                                <div class="border-top mt-4 pt-3 text-start">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Phone Number</small>
                                            <span class="fw-semibold text-dark fs-13">{{ Auth::user()->phone_number ?: 'Not Provided' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Gender</small>
                                            <span class="fw-semibold text-dark fs-13">{{ Auth::user()->gender ?: 'Not Provided' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Settings Card -->
                        <div class="card custom-card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header border-bottom-0 pb-0">
                                <h5 class="card-title fw-bold text-dark">
                                    <i class="ti ti-bell-ringing me-1 text-primary"></i> Notifications
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="notification-form" method="post" action="{{ route('notification.update') }}">
                                    @csrf
                                    <div class="form-check form-switch form-switch-custom mb-4 mt-2">
                                        <input class="form-check-input" type="checkbox" id="notification_sound"
                                            name="notification_sound" {{ $is_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark ms-2" for="notification_sound">
                                            Enable Notification Sound
                                        </label>
                                    </div>
                                    <button type="submit" id="notify" class="btn btn-primary btn-custom w-100 d-flex align-items-center justify-content-center">
                                        <i class="ti ti-device-floppy me-2"></i> Save Preference
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Settings Tabs (Forms) -->
                    <div class="col-xl-8 col-lg-7 col-md-12 mb-4">
                        <div class="card settings-tab-card">
                            <div class="card-body p-4">
                                
                                <!-- Tab Navigation -->
                                <ul class="nav nav-tabs nav-tabs-custom mb-4" id="settingsTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab"
                                            data-bs-target="#profile-tab-pane" type="button" role="tab"
                                            aria-controls="profile-tab-pane" aria-selected="true">
                                            <i class="ti ti-user-edit me-2"></i>Profile
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="password-tab" data-bs-toggle="tab"
                                            data-bs-target="#password-tab-pane" type="button" role="tab"
                                            aria-controls="password-tab-pane" aria-selected="false">
                                            <i class="ti ti-lock me-2"></i>Password
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="pin-tab" data-bs-toggle="tab"
                                            data-bs-target="#pin-tab-pane" type="button" role="tab"
                                            aria-controls="pin-tab-pane" aria-selected="false">
                                            <i class="ti ti-shield-lock me-2"></i>Transaction PIN
                                        </button>
                                    </li>
                                    @if (Auth::user()->role != 'agent')
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="upgrade-tab" data-bs-toggle="tab"
                                                data-bs-target="#upgrade-tab-pane" type="button" role="tab"
                                                aria-controls="upgrade-tab-pane" aria-selected="false">
                                                <i class="ti ti-arrow-up-circle me-2"></i>Upgrade
                                            </button>
                                        </li>
                                    @endif
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content" id="settingsTabsContent">
                                    
                                    <!-- Tab 1: Profile Info Form -->
                                    <div class="tab-pane fade show active" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab">
                                        <div class="mb-3">
                                            <label class="form-label-custom">Email Address</label>
                                            <input type="email" class="form-control form-control-custom bg-light" value="{{ Auth::user()->email }}" disabled>
                                            <small class="text-muted">Registered email cannot be modified.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label-custom">Phone Number</label>
                                            <input type="text" class="form-control form-control-custom bg-light" value="{{ Auth::user()->phone_number }}" disabled>
                                            <small class="text-muted">Registered phone number cannot be modified. Contact admin to update.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label-custom">Gender</label>
                                            <input type="text" class="form-control form-control-custom bg-light" value="{{ Auth::user()->gender }}" disabled>
                                            <small class="text-muted">Registered gender cannot be modified. Contact admin to update.</small>
                                        </div>
                                    </div>

                                    <!-- Tab 2: Update Password Form -->
                                    <div class="tab-pane fade" id="password-tab-pane" role="tabpanel" aria-labelledby="password-tab">
                                        <form id="password-update-form" method="post" action="{{ route('profile.password') }}">
                                            @csrf
                                            @method('put')
                                            
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label-custom">Current Password</label>
                                                <input type="password" class="form-control form-control-custom" id="current_password" name="current_password" required placeholder="Enter current password">
                                            </div>

                                            <div class="mb-3">
                                                <label for="new_password" class="form-label-custom">New Password</label>
                                                <input type="password" class="form-control form-control-custom" id="new_password" name="new_password" required placeholder="Enter new password">
                                            </div>

                                            <div class="mb-4">
                                                <label for="new_password_confirmation" class="form-label-custom">Confirm New Password</label>
                                                <input type="password" class="form-control form-control-custom" id="new_password_confirmation" name="new_password_confirmation" required placeholder="Confirm new password">
                                            </div>

                                            <button type="submit" id="change_password" class="btn btn-primary btn-custom px-4">
                                                <i class="ti ti-key me-2"></i> Update Password
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Tab 3: Transaction PIN Section -->
                                    <div class="tab-pane fade" id="pin-tab-pane" role="tabpanel" aria-labelledby="pin-tab">
                                        <div class="alert alert-light border-0 shadow-sm mb-4 p-3 d-flex align-items-start">
                                            <i class="ti ti-info-circle fs-20 text-primary me-2 mt-1"></i>
                                            <div>
                                                <span class="fw-semibold text-dark d-block">Transaction Security PIN</span>
                                                <small class="text-dark">To create or update your transaction PIN, enter your account password. An OTP (One-Time Password) will be sent to your registered email address for validation.</small>
                                            </div>
                                        </div>

                                        <div class="mb-2" id="errMsg"></div>

                                        <form id="update-pin-form">
                                            @csrf
                                            <div class="mb-4">
                                                <label for="password_for_pin" class="form-label-custom">Account Password</label>
                                                <input type="password" class="form-control form-control-custom" id="password_for_pin" name="password" required placeholder="Enter your password">
                                            </div>

                                            <button type="submit" id="send-otp" class="btn btn-primary btn-custom px-4 d-flex align-items-center">
                                                <span class="me-2">Send OTP</span>
                                                <div class="lds-ring" id="spinner">
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                </div>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Tab 4: Account Upgrade Section -->
                                    @if (Auth::user()->role != 'agent')
                                        <div class="tab-pane fade" id="upgrade-tab-pane" role="tabpanel" aria-labelledby="upgrade-tab">
                                            
                                            <div class="alert alert-danger text-center border-0 shadow-sm" id="errorMsg" style="display:none;" role="alert">
                                                <i class="ti ti-alert-triangle me-1"></i> <small id="message">Processing your request.</small>
                                            </div>
                                            
                                            <div class="alert alert-success text-center border-0 shadow-sm" id="successMsg" style="display:none;" role="alert">
                                                <i class="ti ti-circle-check me-1"></i> <small id="smessage">Processing your request.</small>
                                            </div>

                                            <div class="text-center py-4 px-3">
                                                <div class="avatar avatar-xl avatar-rounded bg-primary-transparent mb-3 mx-auto" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; background-color: rgba(26,64,130,0.1); border-radius: 50%;">
                                                    <i class="ti ti-award text-primary fs-35"></i>
                                                </div>
                                                <h5 class="fw-bold text-dark">Upgrade to Agent Account</h5>
                                                <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
                                                    Upgrade your account now and unlock access to our exclusive agency services. Take your transactions and profit potential to the next level!
                                                </p>

                                                <form id="form" name="form" class="mx-auto" style="max-width: 400px;">
                                                    @csrf
                                                    <fieldset>
                                                        <div class="mb-4">
                                                            <label class="form-label-custom d-block text-start">Select Package</label>
                                                            <select id="type" name="type" class="form-select form-control-custom text-center">
                                                                <option value="">--- Select Package ---</option>
                                                                <option value="agent">Agent Package</option>
                                                            </select>
                                                        </div>
                                                        <button type="button" id="upgrade" class="btn btn-primary btn-custom w-100">
                                                            <i class="ti ti-refresh me-2"></i> Activate Now
                                                        </button>
                                                    </fieldset>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- OTP Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold" id="otpModalLabel">
                        <i class="ti ti-shield-lock me-2"></i> Verification Required
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <div class="mb-3" id="modal_err"></div>
                    <form id="otp-form">
                        @csrf
                        <div class="mb-4">
                            <p class="text-muted fs-14 mb-3">
                                An OTP has been sent to your registered email address. Please check your inbox and spam folders.
                            </p>
                            <label for="otp" class="form-label-custom">OTP Code</label>
                            <input type="text" class="form-control form-control-custom text-center fw-bold fs-18" style="letter-spacing: 5px;" maxlength="6" id="otp" name="otp" required placeholder="******">
                        </div>
                        <div class="mb-3">
                            <label for="new_pin" class="form-label-custom">New 4-Digit Security PIN</label>
                            <input type="password" class="form-control form-control-custom text-center fw-bold fs-18" style="letter-spacing: 5px;" maxlength="4" id="new_pin" name="pin" required placeholder="****">
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-custom justify-content-between">
                    <button type="button" class="btn btn-light btn-custom px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-custom px-4 d-flex align-items-center" id="verify-otp">
                        <span class="me-2">Verify & Save</span>
                        <div class="lds-ring" id="spinner2">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-js')
    <script>
        const pinVerifyRoute = @json(route('pin.verify'));
        const pinUpdateRoute = @json(route('pin.update'));
    </script>
    <script src="{{ asset('assets/js/settings.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Automatic Profile Picture Upload & Preview
            const avatarInput = document.getElementById('avatar_upload_input');
            const avatarForm = document.getElementById('avatar-upload-form');
            const imgPreview = document.getElementById('profile-pic-preview');
            const loadingOverlay = document.getElementById('avatar-loading-overlay');
            
            if (avatarInput && avatarForm) {
                avatarInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        // Instant client-side preview
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (imgPreview) {
                                imgPreview.src = e.target.result;
                            }
                        };
                        reader.readAsDataURL(file);

                        // Show loading indicator
                        if (loadingOverlay) {
                            loadingOverlay.classList.remove('d-none');
                        }

                        // Automatically submit the form
                        avatarForm.submit();
                    }
                });
            }

            // Password form submission processing state
            const passwordForm = document.getElementById('password-update-form');
            const passwordSubmit = document.getElementById('change_password');

            if (passwordForm && passwordSubmit) {
                passwordForm.addEventListener('submit', function(event) {
                    if (!passwordSubmit.disabled) {
                        passwordSubmit.disabled = true;
                        passwordSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Updating...';
                    }
                });
            }

            // Notification form submission processing state
            const notifyForm = document.getElementById('notification-form');
            const notifySubmit = document.getElementById('notify');
            if (notifyForm && notifySubmit) {
                notifyForm.addEventListener('submit', function() {
                    if (!notifySubmit.disabled) {
                        notifySubmit.disabled = true;
                        notifySubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
                    }
                });
            }
        });
    </script>
@endpush
