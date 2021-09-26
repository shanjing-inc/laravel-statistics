<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Engine & Connection Name
    |--------------------------------------------------------------------------
    |
    | Supported Engines: "redis", "eloquent"
    | Connection Name: see config/database.php
    |
    */
    'engine' => 'redis',
    'connection' => 'laravel-statistics',

    /*
    |--------------------------------------------------------------------------
    | Counters periods
    |--------------------------------------------------------------------------
    |
    | Record visits (total) of each one of these periods in this set (can be empty)
    |
    */
    'periods' => [
        'day',
        'week',
        'month',
        'quarter',
        'year',
    ],

    /*
     |--------------------------------------------------------------------------
     | statistics database settings
     |--------------------------------------------------------------------------
     |
     | Here are database settings for statistics builtin model & tables.
     |
     */
    'database' => [

        // Database connection for following tables.
        'connection' => '',

        // statistics tables and model.
        'statistics_table' => 'laravel_statistics',
        'statistics_model' => Shanjing\LaravelStatistics\Models\StatisticsModel::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Redis prefix
    |--------------------------------------------------------------------------
    */
    'keys_prefix' =>  'statistics',

    /*
    |--------------------------------------------------------------------------
    | Always return uncached fresh top/low lists
    |--------------------------------------------------------------------------
    */
    'always_fresh' => false,
];

