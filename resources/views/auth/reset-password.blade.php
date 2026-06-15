@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
    <div class="card custom-card">
                    <div class="card-body p-4">
                        <div class="my-2 d-flex justify-content-center d-lg-none">
                            <a href="{{ route('login') }}">
                                <img src="{{ asset('assets/images/brand-logos/logo.png') }}" alt="logo"
                                    class="desktop-logo" style="width:60px; height:55px">
                            </a>
                        </div>
                        <p class="h5 fw-semibold mb-2 text-center">Reset Password</p>
                        <!-- Session Status -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible shadow-sm" role="alert">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <!-- End Session Status -->
                        <form method="POST" action="{{ route('password.store') }}" class="needs-validation" novalidate>
                            @csrf
                            <!-- Password Reset Token -->
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="recover-email" class="form-label text-default">Email Address</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email"
                                        readonly value="{{ $request->email }}" required />
                                </div>
                                <div class="col-xl-12">
                                    <label for="new-password" class="form-label text-default">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="password"
                                            name="password" placeholder="Password" tabindex="1" autofocus required />
                                        <button class="btn btn-light" onclick="createpassword('password',this)"
                                            type="button" id="button-addon2"><i
                                                class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="signup-confirmpassword" class="form-label text-default">Confirm
                                        Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg"
                                            id="password_confirmation" name="password_confirmation"
                                            placeholder="Confirm password" tabindex="2" required />
                                        <button class="btn btn-light" onclick="createpassword('password_confirmation',this)"
                                            type="button" id="button-addon21"><i
                                                class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="col-xl-12 d-grid mt-2">
                                    <button type="submit" id="reset" class="btn btn-lg btn-primary" tabindex="3">
                                        Reset Password </button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
@endsection
@push('page-js')
    <script src="{{ asset('assets/js/passwordReset.js') }}"></script>
@endpush
