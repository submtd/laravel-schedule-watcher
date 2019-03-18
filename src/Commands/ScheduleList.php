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
        $lastRunDates = Cache::get('laravel-schedule-watcher-events', []);
        $rows = [];
        foreach ($this->schedule->events() as $event) {
            $name = $event->getSummaryForDisplay();
            $expression = $event->getExpression();
            $nextRun = $event->nextRunDate();
            $lastRun = isset($lastRunDates[$name]) ? $lastRunDates[$name] : null;
            $isDue = $event->isDue(app());
            $rows[] = [
                $name,
                $expression,
                $isDue,
                (string) $nextRun,
                (string) $lastRun,
                $nextRun->diffInMinutes(Carbon::now()),
            ];
        }
        $this->table(['Event', 'Expression', 'Is Due', 'Next Run', 'Last Run', 'Difference'], $rows);
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
