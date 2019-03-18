<?php

namespace Submtd\LaravelScheduleWatcher\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Submtd\LaravelScheduleWatcher\Commands\ScheduleList;
use Submtd\LaravelScheduleWatcher\Services\DecoratedSchedule;
use Submtd\LaravelScheduleWatcher\Services\DecoratedScheduleRunCommand;

class LaravelScheduleWatcherServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        $this->app->extend(Schedule::class, function () {
            return new DecoratedSchedule();
        });
        $this->app->extend(ScheduleRunCommand::class, function () {
            return new DecoratedScheduleRunCommand($this->app->make(Schedule::class));
        });
        $this->commands([
            ScheduleList::class,
        ]);
    }
}
