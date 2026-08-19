<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->specificTemplates() as $template) {
            NotificationTemplate::updateOrCreate(
                [
                    'tool' => $template['tool'],
                    'key' => $template['key'],
                    'locale' => $template['locale'],
                    'type' => 'email',
                ],
                [
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'variables' => $template['variables'] ?? [],
                    'is_active' => true,
                ]
            );
        }

        foreach (Module::all() as $module) {
            foreach (['en', 'de'] as $locale) {
                NotificationTemplate::updateOrCreate(
                    [
                        'tool' => str_replace('-', '', $module->key),
                        'key' => 'default-notification',
                        'locale' => $locale,
                        'type' => 'email',
                    ],
                    [
                        'subject' => $locale === 'de' ? 'Neue Benachrichtigung von :appName' : 'New notification from :appName',
                        'body' => $this->wrap($locale === 'de'
                            ? '<h2>{{ $heading }}</h2><p>{{ $message }}</p>@if($url)<a href="{{ $url }}">'.'Öffnen'.'</a>@endif'
                            : '<h2>{{ $heading }}</h2><p>{{ $message }}</p>@if($url)<a href="{{ $url }}">Open</a>@endif'),
                        'variables' => ['heading', 'message', 'url', 'appName'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function specificTemplates(): array
    {
        $button = '<a href="{{ $url }}" style="display:inline-block;background-color:#ff9200;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:12px 28px;border-radius:8px;">{{ $buttonText }}</a>';

        return [
            [
                'tool' => 'core',
                'key' => 'test',
                'locale' => 'en',
                'subject' => 'Test email from {{ $appName }}',
                'body' => $this->wrap('<h2>Hello {{ $userName }}</h2><p style="color:#475569;font-size:15px;line-height:1.6;">This is a test email from <strong>{{ $appName }}</strong>. Your SMTP settings are working correctly.</p><p style="color:#94a3b8;font-size:13px;">If you did not request this test, you can ignore it.</p>'),
                'variables' => ['userName', 'appName'],
            ],
            [
                'tool' => 'core',
                'key' => 'team-invitation',
                'locale' => 'en',
                'subject' => 'You are invited to join {{ $teamName }}',
                'body' => $this->wrap('@if($projectName)<h2>You are invited to join {{ $teamName }} on project {{ $projectName }}</h2><p style="color:#475569;font-size:15px;line-height:1.6;">{{ $inviterName }} has invited you to join the team and project.</p>@else<h2>You are invited to join {{ $teamName }}</h2><p style="color:#475569;font-size:15px;line-height:1.6;">{{ $inviterName }} has invited you to join the team.</p>@endif<p style="margin:24px 0;text-align:center;"><a href="{{ $acceptUrl }}" style="display:inline-block;background-color:#ff9200;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:12px 28px;border-radius:8px;">Accept Invitation</a></p><p style="color:#94a3b8;font-size:13px;">This invitation will expire in 7 days.</p>'),
                'variables' => ['teamName', 'inviterName', 'acceptUrl', 'projectName'],
            ],
            [
                'tool' => 'core',
                'key' => 'scheduled-report',
                'locale' => 'en',
                'subject' => 'Scheduled report: {{ $title }}',
                'body' => $this->wrap('<h2>{{ $title }}</h2><p style="color:#475569;font-size:15px;line-height:1.6;">Your scheduled report is attached.</p><table style="width:100%;border-collapse:collapse;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;"><tr><td style="padding:16px;font-size:14px;color:#475569;"><p style="margin:0 0 8px 0;"><strong>Report type:</strong> {{ $reportType }}</p><p style="margin:0 0 8px 0;"><strong>Frequency:</strong> {{ $frequency }}</p><p style="margin:0;"><strong>Format:</strong> {{ strtoupper($format) }}</p></td></tr></table>'),
                'variables' => ['title', 'reportType', 'frequency', 'format'],
            ],
            [
                'tool' => 'planhive',
                'key' => 'project-member-added',
                'locale' => 'en',
                'subject' => 'You have been added to {{ $projectName }}',
                'body' => $this->wrap('<h2>You have been added to {{ $projectName }}</h2><p style="color:#475569;font-size:15px;line-height:1.6;">Your role on the project is: <strong>{{ $role }}</strong></p>'.$button),
                'variables' => ['projectName', 'role', 'url', 'buttonText'],
            ],
            [
                'tool' => 'focusmatrix',
                'key' => 'delegation-assigned',
                'locale' => 'en',
                'subject' => 'New delegation assigned to you',
                'body' => $this->wrap('<h2>New delegation assigned</h2>@if($taskTitle)<p style="color:#475569;font-size:15px;line-height:1.6;"><strong>{{ $taskTitle }}</strong></p>@endif<p style="color:#475569;font-size:15px;line-height:1.6;">{{ $goal }}</p>'.$button),
                'variables' => ['taskTitle', 'goal', 'url', 'buttonText'],
            ],
            [
                'tool' => 'invoicemaker',
                'key' => 'invoice',
                'locale' => 'en',
                'subject' => 'Invoice {{ $invoiceNumber }} from {{ $appName }}',
                'body' => $this->wrap('@if($bodyMessage)<p style="color:#475569;font-size:15px;line-height:1.6;">{{ $bodyMessage }}</p>@endif<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin:16px 0;"><p style="margin:0 0 8px 0;"><strong>Invoice number:</strong> {{ $invoiceNumber }}</p><p style="margin:0 0 8px 0;"><strong>Amount due:</strong> {{ $currencySymbol }}{{ number_format($amountDue, 2) }}</p><p style="margin:0;"><strong>Due date:</strong> {{ $dueDate }}</p></div><p style="margin:24px 0;text-align:center;"><a href="{{ $url }}" style="display:inline-block;background-color:#ff9200;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:12px 28px;border-radius:8px;">View invoice</a></p><p style="font-size:14px;line-height:1.6;">Download PDF: <a href="{{ $downloadUrl }}" style="color:#0094af;text-decoration:none;">{{ $downloadUrl }}</a></p><p style="font-size:14px;line-height:1.6;">If you have any questions, reply to this email.</p>'),
                'variables' => ['invoiceNumber', 'amountDue', 'currencySymbol', 'dueDate', 'url', 'downloadUrl', 'bodyMessage', 'appName'],
            ],
            [
                'tool' => 'financialplatform',
                'key' => 'kpi-report',
                'locale' => 'en',
                'subject' => 'KPI Report for {{ $teamName }}',
                'body' => $this->wrap('<h2>KPI Report for {{ $teamName }}</h2><p>Period: {{ $period }}</p><table style="width:100%;border-collapse:collapse;"><thead><tr style="background:#f8fafc;"><th style="text-align:left;padding:8px;border:1px solid #e2e8f0;">KPI</th><th style="text-align:left;padding:8px;border:1px solid #e2e8f0;">Value</th><th style="text-align:left;padding:8px;border:1px solid #e2e8f0;">Score</th><th style="text-align:left;padding:8px;border:1px solid #e2e8f0;">Status</th></tr></thead><tbody>@foreach($summaryRows as $row)<tr><td style="padding:8px;border:1px solid #e2e8f0;">{{ $row[\'name\'] }}</td><td style="padding:8px;border:1px solid #e2e8f0;">{{ $row[\'value\'] }}</td><td style="padding:8px;border:1px solid #e2e8f0;">{{ $row[\'score\'] }}</td><td style="padding:8px;border:1px solid #e2e8f0;">{{ $row[\'status\'] }}</td></tr>@endforeach</tbody></table><p>View the full dashboard in the app.</p>'),
                'variables' => ['teamName', 'period', 'summaryRows', 'appName'],
            ],
        ];
    }

    private function wrap(string $inner): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td style="padding:32px 16px;">
                <table role="presentation" width="600" align="center" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 10px 15px -3px rgba(0,0,0,0.05);">
                    <tr>
                        <td style="background-color:#ff9200;padding:28px 32px;text-align:center;background:linear-gradient(135deg,#ff9200 0%,#0094af 100%);">
                            <h1 style="color:#ffffff;margin:0;font-size:20px;font-weight:700;">{{ config(\'app.name\') }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;color:#334155;font-size:14px;line-height:1.6;">
                            '.$inner.'
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px;background-color:#f8fafc;color:#94a3b8;font-size:12px;text-align:center;">
                            '.date('Y').' {{ config(\'app.name\') }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
}
