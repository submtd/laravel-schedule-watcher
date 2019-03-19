<?php

namespace Submtd\LaravelScheduleWatcher\Services;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Support\Facades\Cache;

class DecoratedScheduleRunCommand extends ScheduleRunCommand
{
    protected function runEvent($event)
    {
        $startTime = Carbon::now();
        $this->line("<info>Running scheduled command:</info> $name");
        $event->run($this->laravel);
        $this->eventsRan = true;
        $endTime = Carbon::now();
        $eventInfo = Cache::tags(['laravel-schedule-watcher'])->get($event->id(), []);
        $eventInfo[] = [
            'startTime' => $startTime,
            'endTime' => $endTime,
            'totalTime' => $startTime->diffInSeconds($endTime),
        ];
        $eventInfo = array_slice($eventInfo, -10, 10);
        Cache::tags(['laravel-schedule-watcher'])->forever($event->id(), $eventInfo);
    }
}
