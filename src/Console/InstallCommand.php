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
        $this->call(
            'migrate',
            array(
                '--path'     => 'database/migrations/2021_09_22_083561_create_statistics_table.php',
                '--database' => config('statistics.database.connection') ?: config('database.default'),
                '--force'    => true)
        );
    }
}
