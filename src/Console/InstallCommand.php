<?php

namespace Shanjing\LaravelStatistics\Console;

use Illuminate\Console\Command;
use Shanjing\LaravelStatistics\Models\StatisticsTablesSeeder;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'laravel-statistics:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the admin package';

    /**
     * Install directory.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->initDatabase();

        $this->info('Done.');
    }

    /**
     * Create tables and seed it.
     *
     * @return void
     */
    public function initDatabase()
    {
        $this->call('migrate');

        $statisticsModel = config('statistics.database.statistics_model');

        if ($statisticsModel::count() == 0) {
            $this->call('db:seed', ['--class' => StatisticsTablesSeeder::class]);
        }
    }
}
