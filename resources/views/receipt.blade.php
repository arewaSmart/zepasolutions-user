@extends('layouts.dashboard')

@section('title', 'Transaction Receipt')

@push('page-css')
    <link rel="stylesheet" href="{{ asset('assets/css/receipt.css') }}">
    <style>
        /* Small overrides to perfectly center the receipt card inside dashboard main content */
        .main-content.app-content {
            min-height: calc(100vh - 60px);
        }
        .receipt-card {
            margin: 30px auto;
        }
    </style>
@endpush

@section('content')
<div class="page">
    @include('components.app-header')
    @include('components.app-sidebar')

    <div class="main-content app-content">
        <div class="container-fluid">
            @php
                $meta = is_array($transaction->metadata) ? $transaction->metadata : json_decode($transaction->metadata, true);
                $meta = $meta ?? [];
                $token = $meta['token'] ?? $meta['purchased_code'] ?? null;
                $phone = $meta['phone'] ?? $meta['mobileno'] ?? $meta['meter_number'] ?? $meta['profile_id'] ?? null;
                $network = $meta['network'] ?? $meta['service'] ?? $meta['service_id'] ?? null;
                $pin = $meta['pin'] ?? null;
                $serial = $meta['serial'] ?? null;
                
                if ($network) {
                    $network = str_replace(['-data', '-electric', '-payment'], '', $network);
                }
            @endphp

            <div class="receipt-card" id="receipt">
                <div class="receipt-header">
                    <div class="header-decoration"></div>
                    <div class="receipt-header-content">
                        <div class="status-badge">
                            <i class="fas fa-check-circle"></i> {{ strtoupper($transaction->status ?? 'SUCCESSFUL') }}
                        </div>
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h1>Transaction Receipt</h1>
                        <p>Your transaction has been processed successfully</p>
                    </div>
                </div>
                
                <div class="receipt-body">
                    <h2 class="section-title">Transaction Details</h2>
                    
                    <div class="info-grid">
                        <div class="info-label">
                            <i class="fas fa-receipt"></i> Reference No.
                        </div>
                        <div class="info-value">
                            {{ strtoupper($transaction->referenceId) }}
                        </div>
                        
                        <div class="info-label">
                            <i class="fas fa-tag"></i> Service Type
                        </div>
                        <div class="info-value">
                            {{ strtoupper($transaction->service_type) }}
                        </div>
                        
                        <div class="info-label">
                            <i class="fas fa-info-circle"></i> Description
                        </div>
                        <div class="info-value">
                            {{ $transaction->service_description }}
                        </div>

                        @if($phone)
                            <div class="info-label">
                                <i class="fas fa-user-alt"></i> Recipient / ID
                            </div>
                            <div class="info-value">
                                {{ $phone }}
                            </div>
                        @endif

                        @if($network)
                            <div class="info-label">
                                <i class="fas fa-network-wired"></i> Provider
                            </div>
                            <div class="info-value">
                                {{ strtoupper($network) }}
                            </div>
                        @endif
                        
                        <div class="info-label">
                            <i class="fas fa-calendar-alt"></i> Date & Time
                        </div>
                        <div class="info-value">
                            {{ $transaction->created_at ? $transaction->created_at->format('F j, Y, g:i a') : now()->format('F j, Y, g:i a') }}
                        </div>
                    </div>

                    @if($pin || $serial || ($token && $token !== 'Electricity Payment Successful' && $token !== 'Check History' && $token !== 'Check Transaction History'))
                        <div class="transaction-id" style="background: #fffcf0; border: 1px dashed #f3ce1d; color: #82263b; padding: 15px; margin: 20px 0; text-align: center;">
                            @if($pin)
                                <div style="margin-bottom: 10px;">
                                    <div style="font-weight: 600; font-size: 12px; text-transform: uppercase; color: #6c757d; margin-bottom: 2px;">
                                        PIN Number
                                    </div>
                                    <div style="font-size: 20px; font-weight: 700; letter-spacing: 1.5px;">
                                        {{ $pin }}
                                    </div>
                                </div>
                            @endif
                            
                            @if($serial)
                                <div style="margin-bottom: 10px;">
                                    <div style="font-weight: 600; font-size: 12px; text-transform: uppercase; color: #6c757d; margin-bottom: 2px;">
                                        Serial Number
                                    </div>
                                    <div style="font-size: 18px; font-weight: 700; letter-spacing: 1px; color: #114b5f;">
                                        {{ $serial }}
                                    </div>
                                </div>
                            @endif
                            
                            @if(!$pin && !$serial && $token)
                                <div>
                                    <div style="font-weight: 600; font-size: 12px; text-transform: uppercase; color: #6c757d; margin-bottom: 2px;">
                                        Purchased Code / Token
                                    </div>
                                    <div style="font-size: 18px; font-weight: 700; letter-spacing: 1px;">
                                        {{ $token }}
                                    </div>
                                </div>
                            @endif
                            
                            <div style="font-size: 11px; color: #6c757d; margin-top: 5px;">
                                Copy and use these details to access your service/result.
                            </div>
                        </div>
                    @endif
                    
                    <div class="divider"></div>
                    
                    <div class="total-section">
                        <div class="total-label">Total Amount Paid</div>
                        <div class="total-amount">₦{{ number_format($transaction->amount, 2) }}</div>
                    </div>
                    
                    <p class="thank-you">Thank you for your business! We appreciate your trust in our services.</p>
                    
                    <div class="transaction-id">
                        Transaction ID: {{ strtoupper($transaction->referenceId) }}
                    </div>
                    
                    <div class="buttons-container">
                        <button class="btn btn-primary" onclick="printReceipt()">
                            <i class="fas fa-print"></i> Print Receipt
                        </button>
                        <button class="btn btn-secondary" id="shareButton">
                            <i class="fas fa-share-alt"></i> Share
                        </button>
                        <button class="btn btn-success" id="downloadButton">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                    
                    <div style="text-align: center; margin-top: 25px;">
                        <a href="{{ route('dashboard') }}" style="color: var(--primary); text-decoration: none; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const transactionId = '{{ strtoupper($transaction->referenceId) }}';

            function printReceipt() {
                window.print();
            }
            window.printReceipt = printReceipt;

            const shareButton = document.getElementById('shareButton');
            const downloadButton = document.getElementById('downloadButton');
            const receiptElement = document.getElementById('receipt');
            const buttonsContainer = document.querySelector('.buttons-container');

            async function generateCanvas() {
                if (!receiptElement) return null;
                
                // Hide buttons before screenshotting
                if (buttonsContainer) {
                    buttonsContainer.style.opacity = '0';
                    buttonsContainer.style.pointerEvents = 'none';
                }

                // Temporary force styling for optimal high-quality receipt capture
                const originalWidth = receiptElement.style.width;
                const originalMaxWidth = receiptElement.style.maxWidth;
                const originalBoxShadow = receiptElement.style.boxShadow;
                const originalBorderRadius = receiptElement.style.borderRadius;

                receiptElement.style.width = '600px';
                receiptElement.style.maxWidth = '600px';
                receiptElement.style.boxShadow = 'none';
                receiptElement.style.borderRadius = '12px';

                // Short delay to ensure browser layout settles
                await new Promise(resolve => setTimeout(resolve, 200));

                try {
                    const canvas = await html2canvas(receiptElement, {
                        scale: 2, // High resolution scale
                        useCORS: true,
                        logging: false,
                        backgroundColor: '#ffffff'
                    });

                    // Restore styles
                    receiptElement.style.width = originalWidth;
                    receiptElement.style.maxWidth = originalMaxWidth;
                    receiptElement.style.boxShadow = originalBoxShadow;
                    receiptElement.style.borderRadius = originalBorderRadius;
                    
                    if (buttonsContainer) {
                        buttonsContainer.style.opacity = '1';
                        buttonsContainer.style.pointerEvents = 'auto';
                    }

                    return canvas;
                } catch (e) {
                    // Restore styles in case of error
                    receiptElement.style.width = originalWidth;
                    receiptElement.style.maxWidth = originalMaxWidth;
                    receiptElement.style.boxShadow = originalBoxShadow;
                    receiptElement.style.borderRadius = originalBorderRadius;
                    
                    if (buttonsContainer) {
                        buttonsContainer.style.opacity = '1';
                        buttonsContainer.style.pointerEvents = 'auto';
                    }
                    throw e;
                }
            }

            if (shareButton) {
                shareButton.addEventListener('click', async () => {
                    try {
                        const canvas = await generateCanvas();
                        if (!canvas) return;
                        
                        const imageData = canvas.toDataURL('image/png', 1.0);
                        const receiptName = `ZEPA_Receipt_${transactionId}.png`;

                        if (navigator.share && navigator.canShare && navigator.canShare({ files: [new File([], '')] })) {
                            const response = await fetch(imageData);
                            const blob = await response.blob();
                            const file = new File([blob], receiptName, { type: 'image/png' });

                            await navigator.share({
                                files: [file],
                                title: 'ZEPA Transaction Receipt',
                                text: `Here is my ZEPA transaction receipt for Reference No: ${transactionId}`,
                            });
                        } else {
                            if (navigator.clipboard) {
                                await navigator.clipboard.writeText(window.location.href);
                                alert('Direct image sharing is not supported by your browser/device. The receipt link has been copied to your clipboard!');
                            } else {
                                alert('Sharing is not supported by this browser. Please use the Download option.');
                            }
                        }
                    } catch (error) {
                        console.error('Error sharing receipt:', error);
                        alert('Unable to complete sharing. Try downloading the receipt instead.');
                    }
                });
            }

            if (downloadButton) {
                downloadButton.addEventListener('click', async () => {
                    try {
                        const canvas = await generateCanvas();
                        if (!canvas) return;
                        
                        const imageData = canvas.toDataURL('image/png', 1.0);
                        const receiptName = `ZEPA_Receipt_${transactionId}.png`;

                        const downloadLink = document.createElement('a');
                        downloadLink.href = imageData;
                        downloadLink.download = receiptName;
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    } catch (error) {
                        console.error('Error downloading receipt:', error);
                        alert('Could not download the receipt image. Please try using printing instead.');
                    }
                });
            }
        });
    </script>
@endpush