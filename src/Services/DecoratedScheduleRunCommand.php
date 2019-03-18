<?php

namespace Submtd\LaravelScheduleWatcher\Services;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Support\Facades\Cache;

class DecoratedScheduleRunCommand extends ScheduleRunCommand
{
    protected function runEvent($event)
    {
        $laravelScheduleWatcherEvents = Cache::get('laravel-schedule-watcher-events') ?? [];
        $laravelScheduleWatcherEvents[$event->getSummaryForDisplay()] = Carbon::now();
        Cache::forever('laravel-schedule-watcher-events', $laravelScheduleWatcherEvents);
        $this->line('<info>Running scheduled command:</info> ' . $event->getSummaryForDisplay());
        $event->run($this->laravel);
        $this->eventsRan = true;
    }

    protected static function fixupCommand($command)
    {
        $parts = explode(' ', $command);
        if (count($parts) > 2 && $parts[1] === 'artisan') {
            array_shift($parts);
        }
        return implode(' ', $parts);
    }
}
