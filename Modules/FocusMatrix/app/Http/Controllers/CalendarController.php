<?php

namespace Modules\FocusMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Modules\FocusMatrix\Models\CalendarEvent;
use Modules\FocusMatrix\Models\Integration;
use Modules\FocusMatrix\Models\Task;
use Modules\FocusMatrix\Models\UserSetting;
use Modules\FocusMatrix\Services\GoogleCalendarService;

class CalendarController extends Controller
{
    public function __construct(private GoogleCalendarService $google) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $monthParam = $request->query('month');
        $anchor = $monthParam
            ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
            : now()->startOfMonth();
        $gridStart = (clone $anchor)->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd = (clone $anchor)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $nativeEvents = CalendarEvent::where('user_id', $user->id)
            ->whereBetween('starts_at', [$gridStart, $gridEnd])
            ->orderBy('starts_at')
            ->get();

        $taskDue = Task::where('user_id', $user->id)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$gridStart, $gridEnd])
            ->get();

        $focusBlocks = Task::where('user_id', $user->id)
            ->whereNotNull('focused_block_at')
            ->whereBetween('focused_block_at', [$gridStart, $gridEnd])
            ->get();

        $integration = Integration::where('user_id', $user->id)
            ->where('provider', Integration::PROVIDER_GOOGLE)
            ->first();
        $googleEvents = collect();
        $weak = [];
        if ($integration && $this->google->isConfigured()) {
            $raw = $this->google->upcomingEvents($integration, 96);
            $weak = collect($raw)->filter(fn ($e) => count($e['flags']) > 0)->values()->all();
            $googleEvents = collect($raw)->map(fn ($e) => [
                'id' => 'g-'.($e['id'] ?? md5($e['title'].($e['start'] ?? ''))),
                'type' => 'google',
                'source' => 'google',
                'title' => '📅 '.$e['title'],
                'color' => 'amber',
                'all_day' => false,
                'starts_at' => $e['start'] ?? null,
                'ends_at' => $e['end'] ?? null,
                'flags' => $e['flags'] ?? [],
                'attendees' => $e['attendees'] ?? null,
            ])->filter(fn ($e) => $e['starts_at']);
        }

        return view('focusmatrix::calendar.index', [
            'connected' => (bool) $integration && $this->google->isConfigured(),
            'account_email' => $integration?->account_email,
            'nativeEvents' => $nativeEvents,
            'taskDue' => $taskDue,
            'focusBlocks' => $focusBlocks,
            'googleEvents' => $googleEvents,
            'weak_meetings' => $weak,
            'anchor' => $anchor,
        ]);
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'in:'.implode(',', CalendarEvent::COLORS)],
            'all_day' => ['boolean'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
        ]);

        CalendarEvent::create([
            ...$data,
            'team_id' => $request->user()->current_team_id,
            'user_id' => $request->user()->id,
            'color' => $data['color'] ?? 'accent',
            'source' => CalendarEvent::SOURCE_MANUAL,
        ]);

        return redirect()->back()->with('success', __('Event added to your calendar.'));
    }

    public function updateEvent(Request $request, CalendarEvent $event): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);
        abort_if($event->source !== CalendarEvent::SOURCE_MANUAL, 422, 'Only manual events can be edited.');

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'in:'.implode(',', CalendarEvent::COLORS)],
            'all_day' => ['boolean'],
            'starts_at' => ['sometimes', 'required', 'date'],
            'ends_at' => ['sometimes', 'required', 'date', 'after_or_equal:starts_at'],
        ]);

        $event->update($data);

        return redirect()->back()->with('success', __('Event updated.'));
    }

    public function destroyEvent(Request $request, CalendarEvent $event): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);
        $event->delete();

        return redirect()->back()->with('success', __('Event deleted.'));
    }

    public function focusBlock(Request $request, Task $task): RedirectResponse
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'start' => ['required', 'date'],
            'minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
        ]);

        $start = Carbon::parse($data['start']);
        $minutes = $data['minutes'] ?? 60;

        CalendarEvent::create([
            'team_id' => $request->user()->current_team_id,
            'user_id' => $request->user()->id,
            'title' => '🎯 '.$task->title,
            'color' => 'navy',
            'all_day' => false,
            'starts_at' => $start,
            'ends_at' => (clone $start)->addMinutes($minutes),
            'source' => CalendarEvent::SOURCE_FOCUS_BLOCK,
            'task_id' => $task->id,
        ]);

        $task->update(['focused_block_at' => $start]);

        $integration = $request->user()->integrations()
            ->where('provider', Integration::PROVIDER_GOOGLE)
            ->first();
        if ($integration && $this->google->isConfigured()) {
            $this->google->createFocusBlock($integration, $task->title, $start, $minutes);
        }

        return redirect()->back()->with('success', __('Focus block created on your calendar.'));
    }

    public function importWeakToInbox(Request $request): RedirectResponse
    {
        $user = $request->user();
        $integration = Integration::where('user_id', $user->id)
            ->where('provider', Integration::PROVIDER_GOOGLE)
            ->first();

        if (! $integration || ! $this->google->isConfigured()) {
            return redirect()->route('focusmatrix.integrations.index')->with('error', 'Connect Google Calendar first.');
        }

        $events = $this->google->upcomingEvents($integration, 168);
        $created = 0;

        foreach ($events as $event) {
            if (! count($event['flags'])) {
                continue;
            }
            $title = 'Audit meeting: '.$event['title'];
            if (Task::where('user_id', $user->id)->where('title', $title)->exists()) {
                continue;
            }
            Task::create([
                'team_id' => $user->current_team_id,
                'user_id' => $user->id,
                'title' => $title,
                'description' => 'Flags: '.implode(', ', $event['flags']),
                'status' => Task::STATUS_INBOX,
                'source' => 'calendar',
            ]);
            $created++;
        }

        return redirect()->route('focusmatrix.tasks.index', ['status' => 'inbox'])
            ->with('success', "Imported {$created} meeting(s) to triage inbox.");
    }

    public function feed(string $token): Response
    {
        $setting = UserSetting::where('ics_token', $token)->first();
        if (! $setting) {
            abort(404);
        }

        $user = User::find($setting->user_id);
        if (! $user) {
            abort(404);
        }

        $events = CalendarEvent::where('user_id', $user->id)
            ->whereBetween('starts_at', [now()->subMonths(1), now()->addMonths(3)])
            ->orderBy('starts_at')
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//FocusMatrix//EN',
            'CALSCALE:GREGORIAN',
        ];

        foreach ($events as $event) {
            $uid = 'focusmatrix-'.$event->id.'@'.request()->getHost();
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$uid;
            $lines[] = 'SUMMARY:'.addcslashes($event->title, ',;\\');
            $lines[] = 'DESCRIPTION:'.addcslashes(strip_tags($event->description ?? ''), ',;\\');
            $lines[] = 'LOCATION:'.addcslashes($event->location ?? '', ',;\\');
            $lines[] = 'DTSTART:'.($event->all_day ? $event->starts_at->format('Ymd') : $event->starts_at->format('Ymd\THis\Z'));
            $lines[] = 'DTEND:'.($event->all_day ? $event->ends_at->format('Ymd') : $event->ends_at->format('Ymd\THis\Z'));
            $lines[] = 'DTSTAMP:'.now()->format('Ymd\THis\Z');
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lines), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
        ]);
    }
}
