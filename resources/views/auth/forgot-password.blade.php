@extends('layouts.auth')
@section('title', 'Forget Password')
@section('content')
    <div class="card custom-card">
                    <div class="card-body p-5">

                        <div class="my-2 d-flex justify-content-center d-lg-none">
                            <a href="{{ route('login') }}">
                                <img src="{{ asset('assets/images/brand-logos/logo.png') }}" alt="logo"
                                    class="desktop-logo" style="width:60px; height:55px">
                            </a>
                        </div>
                        <p class="mb-4 text-muted op-7 fw-normal text-center"> Forgot your password? No problem. Just let us
                            know your email address and we will email you a password reset link that will allow you to
                            choose a new one. </p>

                        <!-- Session Status -->
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible shadow-sm" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

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

                        <form method="POST" action="{{ route('password.email') }}" class="needs-validation" novalidate>
                            @csrf
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="signin-username" class="form-label text-default">Email ID</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email"
                                        placeholder="Email Address" autofocus required />
                                </div>
                                <div class="col-xl-12 d-grid mt-3">
                                    <button type="submit" id="reset" class="btn btn-lg btn-primary">Email Password
                                        Reset Link</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
@endsection

@push('page-js')
    <script src="{{ asset('assets/js/passwordReset.js') }}"></script>
@endpush
