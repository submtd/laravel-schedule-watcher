<?php

namespace Submtd\LaravelScheduleWatcher\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Scheduling\Schedule;
use Cron\CronExpression;
use Illuminate\Support\Str;

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
            $shouldHaveRan = Carbon::parse(CronExpression::factory($expression)->getPreviousRunDate()->format('Y-m-d H:i:s'));
            $lastRun = isset($lastRunDates[$name]) ? $lastRunDates[$name] : null;
            $isDue = $event->isDue(app());
            $rows[] = [
                static::fixupCommand($name),
                $expression,
                $isDue,
                (string) $nextRun,
                (string) $shouldHaveRan,
                (string) json_encode($lastRun),
                null,
                // $shouldHaveRan < $lastRun ? 0 : $shouldHaveRan->diffInMinutes($lastRun),
            ];
        }
        $this->table(['Event', 'Expression', 'Is Due', 'Next Run', 'Should Have Ran', 'Last Run', 'Difference'], $rows);
    }

    protected static function fixupCommand($command)
    {
        $parts = explode(' ', $command);
        if (count($parts) > 2 && $parts[1] === "'artisan'") {
            array_shift($parts);
            array_shift($parts);
        }
        return Str::before(implode(' ', $parts), '>');
    }
}
