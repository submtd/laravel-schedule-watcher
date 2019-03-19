<?php

namespace Submtd\LaravelScheduleWatcher;

use Illuminate\Console\Scheduling\Event;

class DecoratedEvent extends Event
{
    public function id()
    {
        return md5($this->getSummaryForDisplay());
    }
}
