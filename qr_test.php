<?php
// Include error handling configuration
require_once __DIR__ . '/middleware/error_handler.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .test-section {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        h2 {
            margin-top: 0;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            overflow-x: auto;
            border-radius: 4px;
        }
        .qr-test {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .qr-test > div {
            flex: 1;
            min-width: 300px;
        }
        .url-display {
            word-break: break-all;
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>QR Code Generation Test</h1>
    <div class="container">
        <div class="test-section">
            <h2>Current Environment</h2>
            <pre id="env-info">Loading...</pre>
        </div>
        
        <div class="test-section">
            <h2>QR Code Generation Test</h2>
            <div class="qr-test">
                <div>
                    <h3>Equipment Details</h3>
                    <div>
                        <p><strong>Equipment ID:</strong> TEST123</p>
                        <p><strong>Equipment Name:</strong> Test Equipment</p>
                        <p><strong>Location:</strong> Test Room, Test Building</p>
                    </div>
                    <h3>Generated URL</h3>
                    <div class="url-display" id="qr-url">Loading...</div>
                </div>
                <div>
                    <h3>Generated QR Code</h3>
                    <div id="qr-display">Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get environment info
            const envInfo = document.getElementById('env-info');
            const qrUrl = document.getElementById('qr-url');
            const qrDisplay = document.getElementById('qr-display');
            
            // Get environment details
            const host = window.location.host;
            const protocol = window.location.protocol;
            const pathname = window.location.pathname;
            
            // Determine basePath
            let basePath = '';
            if (pathname.includes('/mcmod41/')) {
                basePath = '/mcmod41';
            }
            
            // Show environment info
            envInfo.textContent = JSON.stringify({
                host,
                protocol,
                pathname,
                basePath
            }, null, 2);
            
            // Create test equipment URL
            const baseUrl = `${protocol}//${host}${basePath}/users/equipment-qr.php`;
            const redirectUrl = new URL(baseUrl);
            redirectUrl.searchParams.set('id', 'TEST123');
            const qrContent = redirectUrl.toString();
            
            // Display URL
            qrUrl.textContent = qrContent;
            
            // Generate and display QR code
            const size = '200x200';
            const format = 'png';
            const errorCorrection = 'H'; // High error correction
            const encoding = 'UTF-8';
            
            // Encode the QR content for URL
            const encodedContent = encodeURIComponent(qrContent);
            const goQrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}&format=${format}&ecc=${errorCorrection}&charset=${encoding}&data=${encodedContent}`;
            
            // Create and display image
            const qrImage = document.createElement('img');
            qrImage.style.maxWidth = '100%';
            qrImage.style.height = 'auto';
            qrImage.alt = 'QR Code for Test Equipment';
            qrImage.src = goQrUrl;
            
            // Add to page
            qrDisplay.innerHTML = '';
            qrDisplay.appendChild(qrImage);
        });
    </script>
</body>
</html>