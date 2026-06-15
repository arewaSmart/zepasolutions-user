@extends('layouts.email')
@section('title', 'Support Request')
@section('content')
<div class="email-container">
    <!-- Header Section -->
    <div class="email-header">
        <div class="email-logo">
            <img src="{{ asset('assets/home/images/logo/logo.png') }}" alt="ZEPA Logo">
        </div>
        <h2>New Support Complaint</h2>
    </div>

    <!-- Body Section -->
    <div class="email-body">
        <p><strong>Customer Details:</strong></p>
        <ul>
            <li><strong>Name:</strong> {{ $user->first_name }} {{ $user->last_name }}</li>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <li><strong>Phone:</strong> {{ $user->phone_number ?? 'N/A' }}</li>
            <li><strong>User ID:</strong> {{ $user->id }}</li>
        </ul>
        <hr style="border: 0; border-top: 1px solid #ddd; margin: 15px 0;">
        <p><strong>Subject:</strong> {{ $subjectText }}</p>
        <p><strong>Complaint / Message:</strong></p>
        <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; border-radius: 4px; margin-top: 10px; white-space: pre-wrap;">{{ $messageText }}</div>
        <p style="margin-top: 20px;"><small class="text-muted">To reply to this user, simply click reply in your email client. Replies will be automatically directed to <strong>{{ $user->email }}</strong>.</small></p>
    </div>

    <!-- Footer Section -->
    <div class="email-footer">
        <p>Warm regards,</p>
        <p><strong>ZEPA Solutions Automated Support System</strong></p>
    </div>
</div>
@endsection
