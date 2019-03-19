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
    protected $signature = 'schedule:list {--json} {--detail}';
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
            $lastRuns = Cache::tags(['laravel-schedule-watcher'])->get($event->id(), []);
            $lastRun = !empty($lastRuns) ? end($lastRuns)['startTime'] : null;
            $output[$event->id()] = [
                'id' => $event->id(),
                'command' => static::fixupCommand($event->getSummaryForDisplay()),
                'expression' => $event->getExpression(),
                'isDue' => $event->isDue(app()),
                'nextRun' => Carbon::now()->diffInMinutes($event->nextRunDate()) . ' minutes',
                // 'nextRun' => (string) $event->nextRunDate(),
                'lastRun' => (string) $lastRun,
                'lastRuns' => $lastRuns,
            ];
            if ($warnSince > $lastRun) {
                $output[$event->id()]['warning'] = 'Last run should be greater than ' . (string) $warnSince;
            }
            if ($errorSince > $lastRun) {
                $output[$event->id()]['error'] = 'Last run should be greater than ' . (string) $errorSince;
            }
        }
        // return json
        if ($this->option('json')) {
            $this->info(json_encode($output));
            return;
        }
        // return tables
        foreach ($output as $event) {
            $this->line('');
            $this->line($event['id']);
            $this->info('Command: ' . $event['command']);
            $this->info('Expression: ' . $event['expression']);
            $this->info('Is Due: ' . $event['isDue']);
            $this->info('Next Run: ' . $event['nextRun']);
            $this->info('Last Run: ' . $event['lastRun']);
            if (isset($event['warning'])) {
                $this->warn($event['warning']);
            }
            if (isset($event['error'])) {
                $this->error($event['error']);
            }
            if ($this->option('detail')) {
                $rows = [];
                foreach ($event['lastRuns'] as $runs) {
                    $rows[] = [
                        isset($runs['startTime']) ? (string) $runs['startTime'] : null,
                        isset($runs['endTime']) ? (string) $runs['endTime'] : null,
                        isset($runs['totalTime']) ? $runs['totalTime'] : null,
                    ];
                }
                $this->table(['Start Time', 'End Time', 'Total Time'], $rows);
            }
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
