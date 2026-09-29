<?php

namespace Modules\TimeButler\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\InvoiceMaker\Jobs\PushAllocoreMetric;
use Modules\TimeButler\Models\TimeEntry;
use Nwidart\Modules\Support\ModuleServiceProvider;

class TimeButlerServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'TimeButler';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'timebutler';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $push = static function (TimeEntry $entry): void {
            $minutes = $entry->durationMinutes();
            if ($minutes === null || $minutes <= 0) {
                return;
            }

            PushAllocoreMetric::dispatch('timeentry_billable', [
                'entry_id' => (string) $entry->id,
                'hours' => round($minutes / 60, 2),
            ]);
        };

        TimeEntry::created(static fn (TimeEntry $entry) => $push($entry));
        TimeEntry::updated(static function (TimeEntry $entry) use ($push): void {
            if ($entry->wasChanged(['end_time', 'break_minutes', 'start_time'])) {
                $push($entry);
            }
        });
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
