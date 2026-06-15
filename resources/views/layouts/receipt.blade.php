<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Meta Data -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Easy Verifications for your Business" />
    <meta name="keywords" content="NIMC, BVN, ZEPA, Verification, Airtime, Bills, Identity">
    <meta name="author" content="Zepa Developers">
    <title>ZEPA Solutions - @yield('title')</title>
    <!-- fav icon -->
    <link rel="icon" href="{{ asset('assets/home/images/favicon/favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/receipt.css') }}">
</head>

<body>
    @yield('content')
    
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
</body>

</html>