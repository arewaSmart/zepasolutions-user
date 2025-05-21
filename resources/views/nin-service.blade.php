@extends('layouts.dashboard')
@section('title', 'NIN Services')
@section('content')
    <div class="page">
        @include('components.app-header')
        @include('components.app-sidebar')

        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Start::page-header -->
                <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                    <div>
                        <p class="fw-semibold fs-18 mb-0">NIN Services</p>
                        <span class="fs-semibold text-muted">Submit NIN Service request with Tracking ID and NIN for
                            assistance with NIN Issues.</span>
                    </div>
                </div>
                <!-- End::page-header -->

                <!-- Start::row-1 -->
                <div class="row">
                    <div class="col-xxl-12 col-xl-12">
                        <!-- Stats Cards Row -->
                        <div class="row">
                            <div class="col-xxl-6 col-lg-6 col-md-6">
                                <div class="card custom-card overflow-hidden">
                                    <div class="card-body">
                                        <div class="d-flex align-items-top justify-content-between">
                                            <div>
                                                <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                                    <i class="las la-tasks"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill ms-3">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                    <div>
                                                        <p class="text-muted mb-0">Request</p>
                                                        <h4 class="fw-semibold mt-1">{{ $total_request }}</h4>
                                                    </div>
                                                    <div id="crm-total-customers"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xxl-3 col-lg-3 col-md-3">
                                <div class="card custom-card overflow-hidden">
                                    <div class="card-body">
                                        <div class="d-flex align-items-top justify-content-between">
                                            <div>
                                                <span class="avatar avatar-md avatar-rounded bg-success-transparent">
                                                    <i class="las la-check-double"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill ms-3">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                    <div>
                                                        <p class="text-muted mb-0">Resolved</p>
                                                        <h4 class="fw-semibold mt-1">{{ $resolved }}</h4>
                                                    </div>
                                                    <div id="crm-total-deals"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xxl-3 col-lg-3 col-md-3">
                                <div class="card custom-card overflow-hidden">
                                    <div class="card-body">
                                        <div class="d-flex align-items-top justify-content-between">
                                            <div>
                                                <span class="avatar avatar-md avatar-rounded bg-danger-transparent">
                                                    <i class="las la-list-alt"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill ms-3">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                    <div>
                                                        <p class="text-muted mb-0">Pending/Rejected</p>
                                                        <h4 class="fw-semibold mt-1">{{ $pending + $rejected }}</h4>
                                                    </div>
                                                    <div id="crm-total-deals"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Stats Cards Row -->

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <!-- Main Tabs Card -->
                        <div class="card custom-card mt-3">
                            <div class="card-header">
                                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="new-tab" data-bs-toggle="tab" href="#new-1"
                                            role="tab" aria-controls="new-1" aria-selected="true">
                                            My Request
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <!-- New Request Tab Content -->
                                    <div class="tab-pane fade show active" id="new-1" role="tabpanel"
                                        aria-labelledby="new-tab">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card custom-card">

                                                    <div class="card-body text-center">
                                                        <center>
                                                            <img class="img-fluid"
                                                                src="{{ asset('assets/images/nimc.png') }}" width="30%">
                                                        </center>
                                                        <small class="font-italic"><i>Please note that this request will be
                                                                processed within 2 working days. We appreciate your patience
                                                                and
                                                                will keep you updated on the status.</i></small>


                                                        <form id="form" name="nin-request" method="POST"
                                                            action="{{ route('request-nin-service') }}"
                                                            enctype="multipart/form-data">
                                                            @csrf
                                                            <div class="row mb-2">
                                                                <div class="row">
                                                                    <div class="col-md-12 mt-3 mb-3">
                                                                        <select name="service" id="service"
                                                                            class="form-select text-center" required
                                                                            aria-label="Default select example">
                                                                            <option value="">-- Service Type --
                                                                            </option>
                                                                            @foreach ($services as $service)
                                                                                <option
                                                                                    value="{{ $service->service_code }}">
                                                                                    {{ $service->name }} -
                                                                                    &#x20A6;{{ number_format($service->amount, 2) }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <p class="mb-2 form-label" id="modify_lbl"></p>
                                                                        <div id="input-container"></div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <p class="mb-2 mt-2 form-label" id="modify_lbl2">
                                                                        </p>
                                                                        <div id="input-container2"></div>
                                                                    </div>
                                                                </div>

                                                                <div id="photo"
                                                                    class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mt-1 mb-2">
                                                                    <label for="input-file" class="form-label">Photograph
                                                                        Requirement</label>
                                                                    <a type="button" data-bs-toggle="modal"
                                                                        data-bs-target="#requirements">
                                                                        <i class="las la-info-circle bg-light"
                                                                            style="font-size:24px"></i>
                                                                    </a>
                                                                    <p><small class="text-danger">Note: For more
                                                                            information click
                                                                            on the information icon</small></p>
                                                                    <input class="form-control mt-2" type="file"
                                                                        name="documents" id="documents" />
                                                                </div>
                                                            </div>
                                                            <button type="submit" id="nin-request"
                                                                class="btn btn-primary">
                                                                <i class="las la-share"></i> Submit Request
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="card custom-card">

                                                    <div class="card-body">
                                                        <p>Check the status of your request from this section. You can track
                                                            the
                                                            progress and updates on your request</p>
                                                        <p class="text-danger">For instant IPE Please use the check status
                                                            button after few minute of submission.</p>
                                                        @if (!$nin->isEmpty())
                                                            @php
                                                                $currentPage = $nin->currentPage();
                                                                $perPage = $nin->perPage();
                                                                $serialNumber = ($currentPage - 1) * $perPage + 1;
                                                            @endphp

                                                            <div class="table-responsive">
                                                                <table class="table text-nowrap"
                                                                    style="background:#fafafc !important">
                                                                    <thead>
                                                                        <tr class="table-primary">
                                                                            <th width="5%" scope="col">ID</th>
                                                                            <th scope="col">Tracking/NIN ID</th>
                                                                            <th scope="col" class="text-center">Status
                                                                            </th>
                                                                            <th class="text-center">Query</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($nin as $data)
                                                                            <tr>
                                                                                <th scope="row">{{ $serialNumber++ }}
                                                                                </th>
                                                                                <td>{{ $data->trackingId }}
                                                                                    @if ($data->service_type == 'IPE Instant' && $data->reason === null)
                                                                                        &nbsp; <a
                                                                                            href="{{ route('ipeStatus', [$data->trackingId, $data->tnx_id]) }}"
                                                                                            class="btn btn-sm btn-primary rounded">
                                                                                            <i class="bx bx-refresh"></i>
                                                                                            Check
                                                                                            Status
                                                                                        </a>
                                                                                    @endif

                                                                                </td>
                                                                                <td class="text-center">
                                                                                    @if ($data->status == 'resolved')
                                                                                        <span
                                                                                            class="badge bg-outline-success">{{ Str::upper($data->status) }}</span>
                                                                                    @elseif($data->status == 'rejected')
                                                                                        <span
                                                                                            class="badge bg-outline-danger">{{ Str::upper($data->status) }}</span>
                                                                                    @elseif($data->status == 'pending')
                                                                                        <span
                                                                                            class="badge bg-outline-warning">{{ Str::upper($data->status) }}</span>
                                                                                    @else
                                                                                        <span
                                                                                            class="badge bg-outline-primary">{{ Str::upper($data->status) }}</span>
                                                                                    @endif
                                                                                </td>
                                                                                <td class="text-center">

                                                                                    <a type="button"
                                                                                        data-bs-toggle="modal"
                                                                                        data-id="2"
                                                                                        data-reason="{{ $data->reason }}"
                                                                                        data-bs-target="#reason">
                                                                                        <i class="las la-info-circle bg-light"
                                                                                            style="font-size:24px"></i>
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                                <!-- Pagination Links -->
                                                                <div class="d-flex justify-content-center">
                                                                    {{ $nin->links('vendor.pagination.bootstrap-4') }}
                                                                </div>
                                                            </div>
                                                        @else
                                                            <center>
                                                                <img width="65%"
                                                                    src="{{ asset('assets/images/no-transaction.gif') }}"
                                                                    alt="">
                                                            </center>
                                                            <p class="text-center fw-semibold fs-15">No Request Available!
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Main Tabs Card -->
                    </div>
                </div>
                <!-- End::row-1 -->
            </div>
        </div>

        <!-- Modals -->
        <div class="modal fade" id="reason" tabindex="-1" aria-labelledby="reason" data-bs-keyboard="true"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="staticBackdropLabel2">Support Query</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="message">No Message Yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="requirements" aria-labelledby="requirements" data-bs-keyboard="true"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title text-primary">Customer Photograph Requirement</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>
                            A clear and recent passport-style photo of the customer is mandatory. Please ensure the image is
                            well-lit, unobstructed, and taken in front of a plain background.
                        </p>

                        <div class="row text-center mb-3">
                            <div class="col-md-6">
                                <img src="{{ asset('assets/images/photo-sample1.jpg') }}" alt="Sample 1"
                                    class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                                <p class="mt-2">Sample 1</p>
                            </div>
                            <div class="col-md-6">
                                <img src="{{ asset('assets/images/photo-sample2.jpg') }}" alt="Sample 2"
                                    class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                                <p class="mt-2">Sample 2</p>
                            </div>
                        </div>

                        <p>
                            We appreciate your cooperation in providing the necessary documentation to facilitate the
                            modification process.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-js')
    <script>
        $(document).ready(function() {
            //Pay Modal
            $('#reason').on('shown.bs.modal', function(event) {
                var button = $(event.relatedTarget)
                var reason = button.data('reason')
                if (reason != '')
                    $("#message").html(reason);
                else
                    $("#message").html('No Message Yet.');
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form');
            const submitButton = document.getElementById('nin-request');

            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerText = 'Please wait while we process your request...';
            });
        });

        $(document).ready(function() {
            hide();
            hide2();
            $("#photo").hide();

            $("#service").change(function() {
                const selectedIndex = this.selectedIndex;

                // Reset labels and input areas
                $("#tracking_id").remove();
                $("#description").remove();
                $("#modify_lbl").text("").hide();
                $("#modify_lbl2").text("").hide();
                $("#photo").hide();

                let newInput = '';
                let newInput2 = '';
                let labelText = '';
                let labelText2 = '';

                // First block: tracking_id logic
                if (selectedIndex === 1 || selectedIndex === 9) {
                    labelText = "Tracking ID";
                    newInput = $(
                        '<input type="text" id="tracking_id" maxlength="15" pattern="^[a-zA-Z0-9]{15}$" name="tracking_id" title="Tracking ID must be exactly 15 characters (letters/numbers)" class="form-control text-center" required />'
                    );
                    $("#modify_lbl").text(labelText).show();
                    $("#input-container").append(newInput);
                } else if (selectedIndex !== 0) {
                    labelText = "NIN Number";
                    newInput = $(
                        '<input type="text" id="tracking_id" maxlength="11" pattern="^\\d{11}$" name="tracking_id" title="NIN Number must be exactly 11 digits" class="form-control text-center" required />'
                    );
                    $("#modify_lbl").text(labelText).show();
                    $("#input-container").append(newInput);
                }

                // Second block: description logic
                if (selectedIndex === 13) {
                    labelText2 =
                        "Enter your Desired Names: e.g <span class='text-danger'>FirstName, MiddleName, Surname</span>";
                    newInput2 = $(
                        '<textarea id="description" name="description" class="form-control" required></textarea>'
                    );
                    $("#modify_lbl2").html(labelText2).show();
                    $("#input-container2").append(newInput2);
                    $("#photo").show();
                } else if (selectedIndex === 14) {
                    labelText2 = "Phone No";
                    newInput2 = $(
                        '<input type="text" id="description" maxlength="11" pattern="^\\d{11}$" name="description" title="Phone Number must be exactly 11 digits" class="form-control text-center" required />'
                    );
                    $("#modify_lbl2").html(labelText2).show();
                    $("#input-container2").append(newInput2);
                    $("#photo").show();
                } else if (selectedIndex === 15) {
                    labelText2 = "Enter your new Address";
                    newInput2 = $(
                        '<textarea id="description" name="description" class="form-control" required></textarea>'
                    );
                    $("#modify_lbl2").html(labelText2).show();
                    $("#input-container2").append(newInput2);
                    $("#photo").show();
                } else if (selectedIndex === 16) {
                    labelText2 = "Enter your new date of birth";
                    newInput2 = $(
                        '<input type="date" id="description" name="description" title="Please Enter a Valid Date of birth" class="form-control text-center" required />'
                    );
                    $("#modify_lbl2").html(labelText2).show();
                    $("#input-container2").append(newInput2);
                    $("#photo").show();
                }
            });
        });

        function hide() {
            $("#modify_lbl").hide();
        }

        function hide2() {
            $("#modify_lbl2").hide();
        }

        function show() {
            $("#modify_lbl").show();
        }

        function show2() {
            $("#modify_lbl2").show();
        }
    </script>
@endpush
