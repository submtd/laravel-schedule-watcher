<?php

namespace Submtd\LaravelScheduleWatcher\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ScheduleList extends Command
{
    protected $signature = 'schedule:list';
    protected $description = 'Shows a list of scheduled events and when they last ran.';

    public function handle()
    {
        $scheduledEvents = Cache::get('laravel-schedule-watcher-events') ?? [];
        $rows = [];
        foreach ($scheduledEvents as $event => $lastRun) {
            $rows[] = [$event, (string)$lastRun, $lastRun->diffInMinutes(Carbon::now())];
        }
        $this->table(['Event', 'Last Run', 'Difference'], $rows);
    }
}
