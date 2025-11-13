@extends('layouts.dashboard')
@section('title', 'BVN Modification')
@section('content')
    <div class="page">

        @include('components.app-header')
        @include('components.app-sidebar')

        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Start::page-header -->
                <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                    <div>
                        <p class="fw-semibold fs-18 mb-0">BVN Modification</p>
                        <span class="fs-semibold text-muted">Submit a modification request using your BVN etc. to get
                            assistance with BVN-related concerns</span>
                    </div>
                </div>
                <!-- End::page-header -->
                <!-- Start::row-1 -->
                <div class="row">
                    <div class="col-xxl-12 col-xl-12">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xxl-6 col-lg-6 col-md-6">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span
                                                            class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                                            <i class="las la-tasks"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
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
                                                        <span
                                                            class="avatar avatar-md avatar-rounded bg-success-transparent">
                                                            <i class="las la-check-double"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
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
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Pending/Rejected</p>
                                                                <h4 class="fw-semibold mt-1">
                                                                    {{ $pending + $rejected }}</h4>
                                                            </div>
                                                            <div id="crm-total-deals"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-5">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="card custom-card">
                                            <div class="card-header  justify-content-between">
                                                <div class="card-title">
                                                    Modification Request
                                                </div>
                                            </div>
                                            <div class="card-body text-center">
                                                <center>
                                                    <img class="img-fluid" src="{{ asset('assets/images/bvn.jpg') }}"
                                                        width="40%">
                                                </center>
                                                <small class=" font-italic"><i>Please note that this request will be
                                                        processed within 2 working days. We appreciate your patience and
                                                        will keep you updated on the status.</i></small>
                                                @if (session('success'))
                                                    <div class="alert alert-success alert-dismissible fade show"
                                                        role="alert">
                                                        {{ session('success') }}
                                                    </div>
                                                @endif

                                                @if (session('error'))
                                                    <div class="alert alert-danger alert-dismissible fade show"
                                                        role="alert">
                                                        {{ session('error') }}
                                                    </div>
                                                @endif

                                                @if ($errors->any())
                                                    <div class="alert alert-danger alert-dismissible fade show"
                                                        role="alert">
                                                        <ul>
                                                            @foreach ($errors->all() as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                                <form id="form" name="modify-bvn" method="POST"
                                                    action="{{ route('modify-bvn') }}" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="mb-3 row">
                                                        <div class="row mt-4">
                                                            <div class="col-md-12 mt-2">
                                                                <p class="form-label"> Enrollment Center</p>
                                                                <select name="enrollment_center" id="enrollment_center"
                                                                    required class="form-select text-center"
                                                                    aria-label="Default select example">
                                                                    <option value="" selected disabled>Choose
                                                                    </option>
                                                                    <option>Agency Banking</option>
                                                                   <!-- <option>First Bank</option>-->
                                                                    <option>Heritage Bank</option>
                                                                    <option>Keystone Bank</option>
                                                                    <option>Taj Bank</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p class="form-label mt-1"> BVN Number</p>
                                                                <input type="text" id="bvn_number" name="bvn_number"
                                                                    maxlength="11" class="form-control  text-center"
                                                                    required />
                                                            </div>

                                                            <div class="col-md-6  mt-2">
                                                                <p class="form-label"> Modification Type</p>
                                                                <select name="field_to_modify" id="options"
                                                                    class="form-select text-center" required
                                                                    aria-label="Default select example">
                                                                    <option value="" selected disabled>-- Choose Type
                                                                        --
                                                                    </option>
                                                                    @foreach ($services as $service)
                                                                        <option value="{{ $service->service_code }}">
                                                                            {{ $service->name }} -
                                                                            {{ $service->status === 'enabled' ? '(Active)' : '(Not Active)' }}
                                                                            &#x20A6;{{ number_format($service->amount, 2) }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-1 row">
                                                        <div class="col-md-12">
                                                            <p class="mb-2 form-label" id="modify_lbl"></p>
                                                            <div id="input-container"></div>
                                                        </div>
                                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mt-1 mb-2">
                                                            <label for="input-file" class="form-label">Supporting
                                                                Document</label>

                                                            <a type="button"data-bs-toggle="modal"
                                                                data-bs-target="#requirements"><i
                                                                    class="las la-info-circle bg-light"
                                                                    style="font-size:24px"></i></a>
                                                            <p><small class="text-danger"> Note: For more infomation
                                                                    click on the information icon</small>
                                                            <div class="mt-2 row">
                                                                <div class="col-md-6">
                                                                    <p class="form-label mt-2"> ID Card</p>
                                                                    <input class="form-control mt-2" type="file"
                                                                        name="idcard" id="idcard" required />
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="form-label mt-2"> Court Affidavit </p>
                                                                    <input class="form-control mt-2" type="file"
                                                                        name="affidavit" id="affidavit" required />
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <button type="submit" id="modify" class="btn btn-primary"><i
                                                            class="las la-share"></i> Submit Request</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xl-7">
                                <div class="row ">
                                    <div class="col-xl-12">
                                        <div class="card custom-card ">
                                            <div class="card-header justify-content-between">
                                                <div class="card-title">
                                                    Request History
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p>Check the status of your request from the this section. You can track
                                                    the progress and updates on your request
                                                    @if (!$mod->isEmpty())
                                                        @php
                                                            $currentPage = $mod->currentPage(); // Current page number
                                                            $perPage = $mod->perPage(); // Number of items per page
                                                            $serialNumber = ($currentPage - 1) * $perPage + 1; // Starting serial number for current page
                                                        @endphp
                                                        <div class="table-responsive">
                                                            <table class="table text-nowrap"
                                                                style="background:#fafafc !important">
                                                                <thead>
                                                                    <tr class="table-primary">
                                                                        <th width="5%" scope="col">ID</th>
                                                                        <th scope="col">RefNo</th>
                                                                        <th scope="col">BVN No.</th>
                                                                        <th scope="col">Modification</th>
                                                                        <th scope="col" class="text-center">Status
                                                                        </th>
                                                                        <th scope="col">Query</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @php $i = 1; @endphp
                                                                    @foreach ($mod as $data)
                                                                        <tr>
                                                                            <th scope="row">{{ $serialNumber++ }}
                                                                            </th>
                                                                            <td>{{ Str::upper($data->refno) }}</td>
                                                                            <td>{{ $data->bvn_no }}</td>
                                                                            <td>{{ $data->type }}</td>
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
                                                                                <a type="button" data-bs-toggle="modal"
                                                                                    data-id="2"
                                                                                    data-reason="{{ $data->reason }}"
                                                                                    data-bs-target="#reason"> <i
                                                                                        class="las la-info-circle bg-light"
                                                                                        style="font-size:24px"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                        @php $i++ @endphp
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                            <!-- Pagination Links -->
                                                            <div class="d-flex justify-content-center">
                                                                {{ $mod->links('vendor.pagination.bootstrap-4') }}
                                                            </div>
                                                        </div>
                                                    @else
                                                        <center><img width="65%"
                                                                src="{{ asset('assets/images/no-transaction.gif') }}"
                                                                alt=""></center>
                                                        <p class="text-center fw-semibold  fs-15"> No Request
                                                            Available!</p>
                                                    @endif
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
        <!-- End::row-1 -->

        <!-----Nodal-->

        <div class="modal fade" id="reason" tabindex="-1" aria-labelledby="reason" data-bs-keyboard="true"
            aria-hidden="true">
            <!-- Scrollable modal -->
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="staticBackdropLabel2">Support Query
                        </h6>
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
            <!-- Scrollable modal -->
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="staticBackdropLabel2">Required Documents
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Please note that all modifications require a valid means of identification and a court
                            affidavit. Accepted forms of identification include:</p>
                        <ul>
                            <li>NIMC slip</li>
                            <li>Voter's card (please scan both the front and back of the card)</li>
                            <li>Driver's license</li>
                            <li>International passport</li>
                        </ul>
                        <p>We appreciate your cooperation in providing the necessary documentation to facilitate the
                            modification process.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
@push('page-js')
    <script src="{{ asset('assets/js/mod.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form');
            const submitButton = document.getElementById('modify');

            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerText = 'Please wait while we process your request...';
            });
        });
    </script>
@endpush
