<?php

namespace Submtd\LaravelScheduleWatcher\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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
        $output = [];
        foreach ($this->schedule->events() as $event) {
            $output[$event->id()] = [
                'command' => static::fixupCommand($event->getSummaryForDisplay()),
                'expression' => $event->getExpression(),
                'isDue' => $event->isDue(app()),
                'nextRun' => (string) $event->nextRunDate(),
                'lastRuns' => Cache::tags(['laravel-schedule-watcher'])->get($event->id(), []),
            ];
            // $name = $event->getSummaryForDisplay();
            // $this->info('Name: ' . static::fixupCommand($name));
            // $this->info('Expression: ' . $event->getExpression());
            // $this->info('Is Due: ' . $event->isDue(app()));
            // if (!isset($lastRunDates[md5($name)])) {
            //     $this->warn('No previous run dates found.');
            //     continue;
            // }
            // $rows = [];
            // foreach ($lastRunDates[md5($name)] as $lastRunDate) {
            //     $rows[] = [
            //         (string) $lastRunDate['startTime'],
            //         (string) $lastRunDate['endTime'],
            //         $lastRunDate['totalTime'],
            //     ];
            // }
            // $this->table(['Start Time', 'End Time', 'Total Time'], $rows);
            // $this->info('Last Run: ' . isset($lastRunDates[md5($name)]) ? $lastRunDates[md5($name)] : 'never');
            // $expression = $event->getExpression();
            // $nextRun = $event->nextRunDate();
            // $shouldHaveRan = Carbon::parse(CronExpression::factory($expression)->getPreviousRunDate()->format('Y-m-d H:i:s'));
            // $lastRun = isset($lastRunDates[md5($name)]) ? $lastRunDates[md5($name)] : null;
            // $isDue = $event->isDue(app());
            // $rows[] = [
            //     static::fixupCommand($name),
            //     $expression,
            //     $isDue,
            //     (string) $nextRun,
            //     (string) $shouldHaveRan,
            //     $this->table(['Start Time', 'End Time', 'Total Time'], $lastRun ?? []),
            //     null,
            //     // $shouldHaveRan < $lastRun ? 0 : $shouldHaveRan->diffInMinutes($lastRun),
            // ];
        }
        $this->info(json_encode($output));
        // $this->table(['Event', 'Expression', 'Is Due', 'Next Run', 'Should Have Ran', 'Last Run', 'Difference'], $rows);
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
