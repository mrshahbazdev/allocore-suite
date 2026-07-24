<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationStreamController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $user = $request->user();
        $lastId = (int) ($request->header('Last-Event-ID', $request->input('last_id', 0)));

        $response = new StreamedResponse(function () use ($user, $lastId) {
            $lastId = (int) $lastId;
            $start = time();
            $maxLifetime = 60 * 5; // 5 minutes

            echo "event: connected\ndata: ".json_encode(['user_id' => $user->id])."\n\n";
            flush();

            while (true) {
                if (time() - $start > $maxLifetime) {
                    echo "event: close\ndata: ".json_encode(['reason' => 'timeout'])."\n\n";
                    flush();
                    break;
                }

                $notifications = $user->notifications()
                    ->where('id', '>', $lastId)
                    ->orderBy('id')
                    ->get(['id', 'data', 'read_at', 'created_at']);

                foreach ($notifications as $notification) {
                    $lastId = max($lastId, $notification->id);
                    $payload = [
                        'id' => $notification->id,
                        'title' => $notification->data['title'] ?? __('Notification'),
                        'body' => $notification->data['body'] ?? $notification->data['message'] ?? '',
                        'url' => $notification->data['url'] ?? null,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at->toIso8601String(),
                    ];

                    echo "id: {$notification->id}\nevent: notification\ndata: ".json_encode($payload)."\n\n";
                    flush();
                }

                echo "event: ping\ndata: ".json_encode(['time' => now()->toIso8601String()])."\n\n";
                flush();

                sleep(5);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);

        return $response;
    }
}
