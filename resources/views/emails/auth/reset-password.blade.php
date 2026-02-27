<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Cyberpunk Email Styling Reset */
        body,
        table,
        td,
        p,
        a,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Courier New', Courier, monospace;
            color: #d1d5db;
            /* gray-300 */
        }

        body {
            background-color: #030303;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table {
            border-spacing: 0;
        }

        table td {
            border-collapse: collapse;
        }

        img {
            border: 0;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #0d0d0d;
            border: 1px solid #1f2937;
            /* gray-800 */
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.2);
            /* Cyan glow */
        }

        .header {
            background: linear-gradient(90deg, #111827, #0f172a);
            padding: 30px;
            text-align: center;
            border-bottom: 2px solid #06b6d4;
            /* cyan-500 */
        }

        .header h1 {
            color: #06b6d4;
            /* cyan-500 */
            font-size: 24px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 0 0 10px rgba(6, 182, 212, 0.5);
        }

        .content {
            padding: 40px 30px;
            background-color: #0a0a0a;
        }

        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .button-container {
            text-align: center;
            margin: 40px 0;
        }

        .cyber-button {
            display: inline-block;
            background: linear-gradient(90deg, #9333ea, #06b6d4);
            /* purple to cyan */
            color: #ffffff !important;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 15px rgba(147, 51, 234, 0.5);
        }

        .footer {
            background-color: #111;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #1f2937;
        }

        .footer p {
            font-size: 12px;
            color: #6b7280;
            /* gray-500 */
            margin: 0;
        }

        .warning {
            color: #ef4444;
            /* red-500 */
            font-size: 14px;
            text-align: center;
            margin-top: 30px;
        }

        .trouble-link {
            font-size: 12px;
            color: #6b7280;
            margin-top: 30px;
            border-top: 1px solid #1f2937;
            padding-top: 20px;
            word-break: break-all;
        }

        .trouble-link a {
            color: #06b6d4;
        }
    </style>
</head>

<body>
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #030303; padding: 40px 0;">
        <tr>
            <td align="center">
                <table class="container" border="0" cellpadding="0" cellspacing="0">
                    <!-- Header -->
                    <tr>
                        <td class="header">
                            <h1>{{ __('Password Reset') }}</h1>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="content">
                            <p style="color: #fff; font-size: 18px; font-weight: bold;">{{ __('Hello!') }}</p>

                            <p>{{ __('You are receiving this email because we received a password reset request for your account.') }}</p>

                            <div class="button-container">
                                <a href="{{ $url }}" class="cyber-button">{{ __('Reset Password') }}</a>
                            </div>

                            <p>{{ __('This password reset link will expire in :count minutes.', ['count' => $count]) }}</p>

                            <p class="warning">{{ __('If you did not request a password reset, no further action is required.') }}</p>

                            <p>{{ __('Regards') }},<br><strong style="color: #06b6d4;">{{ config('app.name') }}</strong></p>

                            <!-- Subcopy -->
                            <div class="trouble-link">
                                @lang("If you're having trouble clicking the \"Reset Password\" button, copy and paste the URL below\ninto your web browser:") <a href="{{ $url }}">{{ $url }}</a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('System Online.') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>