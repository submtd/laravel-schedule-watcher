<?php

namespace Submtd\LaravelScheduleWatcher\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleList extends Command
{
    protected $signature = 'schedule:list';
    protected $description = 'Shows a list of scheduled events and when they last ran.';

    protected $schedule;

    public function __construct(Schedule $schedule)
    {
        parent::__construct();
        $this->schedule = $schedule;
    }

    public function handle()
    {
        foreach ($this->schedule->events() as $event) {
            dd($event->nextRunDate());
        }
        $events = array_map(function ($event) {
            return [
                'cron' => $event->expression,
                'command' => static::fixUpCommand($event->command),
            ];
        }, $this->schedule->events());
        dd($events);
        $this->info(json_encode($events));
        $scheduledEvents = Cache::get('laravel-schedule-watcher-events') ?? [];
        $rows = [];
        foreach ($scheduledEvents as $event => $lastRun) {
            $rows[] = [$event, (string)$lastRun, $lastRun->diffInMinutes(Carbon::now())];
        }
        $this->table(['Event', 'Last Run', 'Difference'], $rows);
    }

    protected static function fixupCommand($command)
    {
        $parts = explode(' ', $command);
        if (count($parts) > 2 && $parts[1] === "'artisan'") {
            array_shift($parts);
        }
        return implode(' ', $parts);
    }
}
