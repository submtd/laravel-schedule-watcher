<?php

namespace Submtd\LaravelScheduleWatcher\Services;

use Illuminate\Console\Scheduling\Schedule;
use Submtd\LaravelScheduleWatcher\DecoratedEvent;

class DecoratedSchedule extends Schedule
{
    /**
     * Add a new command event to the schedule.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \Illuminate\Console\Scheduling\Event
     */
    // public function exec($command, array $parameters = [])
    // {
    //     if (count($parameters)) {
    //         $command .= ' ' . $this->compileParameters($parameters);
    //     }
    //     $this->events[] = $event = new DecoratedEvent($this->eventMutex, $command, $this->timezone);
    //     return $event;
    // }
}
