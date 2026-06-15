@extends('layouts.auth')

@section('title', 'Sign Up')

@section('content')
    <div class="card custom-card">
                    <div class="card-body p-4">

                        @if (session()->has('status'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                {{ session()->get('status') }}
                            </div>
                        @endif

                        <p class="h5 fw-semibold mb-2 text-center">Sign Up</p>
                        <p class="mb-4 text-muted op-7 fw-normal text-center">Welcome & Join us by creating a free account!
                        </p>
                        <div class="alert alert-danger d-flex align-items-center" id="alert-danger" role="alert">
                            <svg class="flex-shrink-0 me-2 svg-danger" xmlns="http://www.w3.org/2000/svg"
                                enable-background="new 0 0 24 24" height="1.5rem" viewBox="0 0 24 24" width="1.5rem"
                                fill="#000000">
                                <g>
                                    <rect fill="none" height="24" width="24" />
                                </g>
                                <g>
                                    <g>
                                        <g>
                                            <path
                                                d="M15.73,3H8.27L3,8.27v7.46L8.27,21h7.46L21,15.73V8.27L15.73,3z M19,14.9L14.9,19H9.1L5,14.9V9.1L9.1,5h5.8L19,9.1V14.9z" />
                                            <rect height="6" width="2" x="11" y="7" />
                                            <rect height="2" width="2" x="11" y="15" />
                                        </g>
                                    </g>
                                </g>
                            </svg>
                            <div id="error"></div>
                        </div>
                        <form class="theme-form needs-validation" id="register_form" novalidate>
                            @csrf
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="signup-email" class="form-label text-default">Email Address</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email"
                                        placeholder="e.g email@gmil.com" tabindex="1" required />
                                </div>
                                <div class="col-xl-12">
                                    <label for="signup-password" class="form-label text-default">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="password"
                                            name="password" placeholder="Password" tabindex="3" required />
                                        <button class="btn btn-light" onclick="createpassword('password',this)"
                                            type="button" id="button-addon2"><i
                                                class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <label for="signup-confirmpassword" class="form-label text-default">Confirm
                                        Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg"
                                            id="password_confirmation" name="password_confirmation"
                                            placeholder="Confirm password" tabindex="4" required />
                                        <button class="btn btn-light" onclick="createpassword('password_confirmation',this)"
                                            type="button" id="button-addon21"><i
                                                class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="signup-firstname" class="form-label text-default">Referral Code</label>
                                    <a href="javascript:void(0);" data-bs-toggle="tooltip"
                                        title="Were you referred by someone? Enter their referral code below"
                                        data-bs-custom-class="tooltip-secondary" class="me-3"><i
                                            class="ri-information-line"></i></a>
                                    <input type="text" class="form-control form-control-lg" id="referral_code"
                                        name="referral_code" value="{{ request()->get('ref') }}" maxlength="6" placeholder="Idris19209" tabindex="2" />
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" value="" id="terms"
                                            name="terms" tabindex="5">
                                        <label class="form-check-label text-muted fw-normal" for="defaultCheck1">
                                            By creating an account you agree to our <a href="{{ route('terms') }}"
                                                class="text-success"><u>Terms & Conditions</u></a>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-xl-12 d-grid mt-2">
                                    <button class="btn btn-primary btn-lg" id="register" type="button">Create Account
                                        <div class="lds-ring" id="spinner">
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="text-center">
                            <p class="fs-12 text-muted mt-3">Already have an account? <a href="{{ route('login') }}"
                                    class="text-primary">Sign In</a></p>
                </div>
            </div>
@endsection
@push('page-js')
    <script src="{{ asset('assets/js/register.js') }}"></script>
    <script src="{{ asset('assets/js/validation2.js') }}"></script>
@endpush
