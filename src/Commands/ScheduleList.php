<?php

namespace Submtd\LaravelScheduleWatcher\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Cron\CronExpression;
use Carbon\Carbon;

class ScheduleList extends Command
{
    protected $signature = 'schedule:list {--json}';
    protected $description = 'Shows a list of scheduled events and when they last ran.';

    protected $schedule;

    public function __construct(Schedule $schedule)
    {
        parent::__construct();
        $this->schedule = $schedule;
    }

    public function handle()
    {
        $output = [];
        foreach ($this->schedule->events() as $event) {
            $cronExpression = CronExpression::factory($event->getExpression());
            $warnSince = Carbon::parse($cronExpression->getPreviousRunDate()->format('Y-m-d H:i:s'));
            $errorSince = Carbon::parse($cronExpression->getPreviousRunDate(null, 5)->format('Y-m-d H:i:s'));
            $lastRuns = Cache::tags(['laravel-schedule-watcher'])->get($event->id());
            $lastRun = !is_null($lastRuns) ? end($lastRuns)['startTime'] : null;
            $output[$event->id()] = [
                'command' => static::fixupCommand($event->getSummaryForDisplay()),
                'expression' => $event->getExpression(),
                'isDue' => $event->isDue(app()),
                'nextRun' => (string) $event->nextRunDate(),
                'lastRun' => (string) $lastRun,
            ];
            if ($warnSince > $lastRun) {
                $output[$event->id()]['warning'] = 'Last run should be greater than ' . (string) $warnSince;
            }
            if ($errorSince > $lastRun) {
                $output[$event->id()]['error'] = 'Last run should be greater than ' . (string) $errorSince;
            }
        }
        if ($this->option('json')) {
            $this->info(json_encode($output));
            return;
        }
    }

    protected static function fixupCommand($command)
    {
        $parts = explode(' ', $command);
        if (count($parts) > 2 && $parts[1] === "'artisan'") {
            array_shift($parts);
            array_shift($parts);
        }
        return trim(Str::before(implode(' ', $parts), '>'));
    }
}
