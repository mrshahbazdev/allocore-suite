<?php

namespace Modules\FocusMatrix\Services;

use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\Event as GoogleEvent;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\FocusMatrix\Models\Integration;

class GoogleCalendarService
{
    public const SCOPES = [
        'https://www.googleapis.com/auth/calendar.events',
        'https://www.googleapis.com/auth/calendar.readonly',
        'https://www.googleapis.com/auth/userinfo.email',
        'openid',
    ];

    public function isConfigured(): bool
    {
        return class_exists(GoogleClient::class)
            && ! empty(config('services.google.client_id'))
            && ! empty(config('services.google.client_secret'))
            && ! empty(config('services.google.redirect'));
    }

    public function client(): GoogleClient
    {
        $client = new GoogleClient;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->addScope(self::SCOPES);

        return $client;
    }

    public function authUrl(?string $state = null): string
    {
        $client = $this->client();
        if ($state) {
            $client->setState($state);
        }

        return $client->createAuthUrl();
    }

    public function handleCallback(User $user, string $code): Integration
    {
        $client = $this->client();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \RuntimeException('Google OAuth error: '.$token['error']);
        }

        $email = null;
        if (isset($token['id_token'])) {
            try {
                $payload = $client->verifyIdToken($token['id_token']);
                $email = $payload['email'] ?? null;
            } catch (\Throwable $e) {
                Log::warning('Google id_token verify failed', ['error' => $e->getMessage()]);
            }
        }

        return Integration::updateOrCreate(
            ['user_id' => $user->id, 'provider' => Integration::PROVIDER_GOOGLE],
            [
                'account_email' => $email,
                'access_token' => $token['access_token'] ?? null,
                'refresh_token' => $token['refresh_token'] ?? null,
                'expires_at' => isset($token['expires_in']) ? Carbon::now()->addSeconds((int) $token['expires_in']) : null,
                'scopes' => self::SCOPES,
                'meta' => ['token_type' => $token['token_type'] ?? 'Bearer'],
            ]
        );
    }

    public function authenticatedClient(Integration $integration): GoogleClient
    {
        $client = $this->client();
        $client->setAccessToken([
            'access_token' => $integration->access_token,
            'refresh_token' => $integration->refresh_token,
            'expires_in' => $integration->expires_at
                ? max(0, $integration->expires_at->diffInSeconds(Carbon::now(), false) * -1)
                : 0,
            'created' => $integration->updated_at?->timestamp ?? time(),
        ]);

        if ($client->isAccessTokenExpired() && $integration->refresh_token) {
            $refreshed = $client->fetchAccessTokenWithRefreshToken($integration->refresh_token);
            if (! isset($refreshed['error'])) {
                $integration->update([
                    'access_token' => $refreshed['access_token'] ?? $integration->access_token,
                    'expires_at' => isset($refreshed['expires_in'])
                        ? Carbon::now()->addSeconds((int) $refreshed['expires_in'])
                        : $integration->expires_at,
                ]);
            }
        }

        return $client;
    }

    public function upcomingEvents(Integration $integration, int $hours = 72): array
    {
        try {
            $client = $this->authenticatedClient($integration);
            $service = new GoogleCalendar($client);
            $events = $service->events->listEvents('primary', [
                'timeMin' => Carbon::now()->toRfc3339String(),
                'timeMax' => Carbon::now()->addHours($hours)->toRfc3339String(),
                'singleEvents' => true,
                'orderBy' => 'startTime',
                'maxResults' => 25,
            ]);

            $integration->update(['last_synced_at' => Carbon::now()]);

            return collect($events->getItems())->map(function (GoogleEvent $e) {
                $start = $e->getStart()?->getDateTime() ?? $e->getStart()?->getDate();
                $end = $e->getEnd()?->getDateTime() ?? $e->getEnd()?->getDate();
                $attendees = collect($e->getAttendees() ?? [])
                    ->map(fn ($a) => $a->getEmail())
                    ->filter()->values()->all();
                $description = (string) ($e->getDescription() ?? '');
                $title = (string) ($e->getSummary() ?? '(untitled)');

                return [
                    'id' => $e->getId(),
                    'title' => $title,
                    'start' => $start,
                    'end' => $end,
                    'link' => $e->getHtmlLink(),
                    'attendees' => $attendees,
                    'description' => $description,
                    'flags' => self::auditFlags($title, $description, $attendees),
                ];
            })->values()->all();
        } catch (\Throwable $e) {
            Log::warning('FocusMatrix Google Calendar fetch failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function createFocusBlock(Integration $integration, string $title, Carbon $start, int $minutes = 60): ?string
    {
        try {
            $client = $this->authenticatedClient($integration);
            $service = new GoogleCalendar($client);
            $event = new GoogleEvent([
                'summary' => '[Focus] '.$title,
                'description' => 'FocusMatrix deep-work block. Do not disturb.',
                'start' => new EventDateTime([
                    'dateTime' => $start->toRfc3339String(),
                    'timeZone' => config('app.timezone', 'UTC'),
                ]),
                'end' => new EventDateTime([
                    'dateTime' => $start->copy()->addMinutes($minutes)->toRfc3339String(),
                    'timeZone' => config('app.timezone', 'UTC'),
                ]),
                'colorId' => '9',
                'reminders' => ['useDefault' => true],
            ]);
            $created = $service->events->insert('primary', $event);

            return $created->getHtmlLink();
        } catch (\Throwable $e) {
            Log::warning('FocusMatrix Google Calendar insert failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public static function auditFlags(string $title, string $description, array $attendees): array
    {
        $flags = [];
        $desc = trim($description);
        $titleLower = strtolower($title);

        if ($desc === '') {
            $flags[] = 'no_agenda';
        }
        if (! preg_match('/(decision|decide|approve|sign[- ]?off|entsch|bewilligen|freigabe)/i', $title.' '.$description)) {
            $flags[] = 'no_decision';
        }
        if (count($attendees) >= 8) {
            $flags[] = 'too_many';
        }
        if (preg_match('/(status|sync|weekly|standup|daily|update)/', $titleLower)) {
            $flags[] = 'recurring_status';
        }

        return $flags;
    }
}
