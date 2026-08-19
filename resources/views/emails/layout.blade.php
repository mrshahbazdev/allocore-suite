<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', config('app.name'))</title>
    <style>
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .email-padding { padding: 24px 16px !important; }
            .email-header { padding: 24px !important; }
            .email-body { padding: 24px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
    @php
        $brand = config('app.team_branding') ?? [];
        $primary = $brand['primary_color'] ?? '#ff9200';
        $accent = $brand['accent_color'] ?? '#0094af';
        $siteName = $brand['name'] ?? config('app.name', 'Allocore');
        $logo = $brand['logo'] ?? null;
    @endphp

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f1f5f9;">
        <tr>
            <td align="center" style="padding:40px 16px;" class="email-padding">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" class="email-container" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 10px 15px -3px rgba(0,0,0,0.05);">
                    <tr>
                        <td class="email-header" style="background:linear-gradient(135deg, {{ $primary }} 0%, {{ $accent }} 100%);padding:32px;text-align:center;">
                            @if ($logo)
                                <img src="{{ $logo }}" alt="" width="48" height="48" style="display:block;margin:0 auto 12px;border-radius:12px;background:#ffffff;">
                            @else
                                <div style="display:inline-block;width:48px;height:48px;line-height:48px;border-radius:12px;background:#ffffff;color:{{ $primary }};font-size:24px;font-weight:900;text-align:center;margin-bottom:12px;">A</div>
                            @endif
                            <h1 style="color:#ffffff;margin:0;font-size:20px;font-weight:700;letter-spacing:-0.025em;">{{ $siteName }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-body" style="padding:40px 32px;color:#334155;font-size:15px;line-height:1.65;">
                            @hasSection('title')
                                <h2 style="font-size:22px;font-weight:700;color:#0f172a;margin:0 0 16px 0;">@yield('title')</h2>
                            @endif
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;background-color:#f8fafc;color:#64748b;font-size:12px;text-align:center;border-top:1px solid #e2e8f0;">
                            &copy; {{ date('Y') }} {{ $siteName }}. {{ __('All rights reserved.') }}<br>
                            <a href="{{ config('app.url') }}" style="color:{{ $accent }};text-decoration:none;font-weight:600;">{{ config('app.url') }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
