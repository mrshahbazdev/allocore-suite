<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f1f5f9; padding: 24px;">
    <div style="max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; padding: 32px; border: 1px solid #e2e8f0;">
        <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 16px;">{{ __('Hello :name', ['name' => $userName]) }}</h1>
        <p style="color: #475569; font-size: 14px; line-height: 1.5; margin-bottom: 24px;">{{ __('This is a test email from :app. Your SMTP settings are working correctly.', ['app' => $appName]) }}</p>
        <p style="color: #94a3b8; font-size: 12px; margin-top: 24px;">{{ __('If you did not request this test, you can ignore it.') }}</p>
    </div>
</body>
</html>
