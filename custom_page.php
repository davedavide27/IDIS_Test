<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Print Layout</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            /* Custom header */
            .custom-header {
                position: fixed;
                top: 0;
                width: 100%;
                text-align: center;
                font-size: 16px;
                font-weight: bold;
                padding: 10px;
                background-color: #f2f2f2;
            }

            /* Custom footer */
            .custom-footer {
                position: fixed;
                bottom: 0;
                width: 100%;
                text-align: center;
                font-size: 14px;
                padding: 10px;
                background-color: #f2f2f2;
            }

            /* Avoid content overlap with header/footer */
            .content {
                padding-top: 60px; /* Adjust based on your header height */
                padding-bottom: 60px; /* Adjust based on your footer height */
            }

            /* Hide browser's default header/footer */
            @page {
                margin: 0;
            }
        }

        .no-print {
            display: none;
        }
    </style>
</head>
<body>

    <div class="custom-header">
        Custom Print Header - Title or Other Info
    </div>

    <div class="content">
        <p>This is the main content that will be printed. You can add anything here.</p>
        <p>More content goes here...</p>
    </div>

    <div class="custom-footer">
        Page 1 of 1 | Custom Footer Information
    </div>

    <button class="no-print" onclick="window.print()">Print this page</button>

</body>
</html>
