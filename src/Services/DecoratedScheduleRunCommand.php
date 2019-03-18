<?php

namespace Submtd\LaravelScheduleWatcher\Services;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Support\Facades\Cache;

class DecoratedScheduleRunCommand extends ScheduleRunCommand
{
    protected function runEvent($event)
    {
        $name = $event->getSummaryForDisplay();
        $startTime = Carbon::now();
        $this->line("<info>Running scheduled command:</info> $name");
        $event->run($this->laravel);
        $this->eventsRan = true;
        $endTime = Carbon::now();
        $laravelScheduleWatcherEvents = Cache::get('laravel-schedule-watcher-events') ?? [];
        $laravelScheduleWatcherEvents[$name][] = [
            'startTime' => $startTime,
            'endTime' => $endTime,
            'totalTime' => $startTime->diffInSeconds($endTime),
        ];
        $laravelScheduleWatcherEvents[$name] = array_slice($laravelScheduleWatcherEvents[$name], -10, 10);
        Cache::forever('laravel-schedule-watcher-events', $laravelScheduleWatcherEvents);
    }
}
