<?php

namespace Shanjing\LaravelStatistics\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property mixed key
 * @property mixed data
 * @property mixed occurred_at
 *
 */
class StatisticsModel extends Model
{
    protected $fillable = ['key', 'data', 'occurred_at'];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->init();

        parent::__construct($attributes);
    }

    protected function init()
    {
        $connection = config('statistics.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('statistics.database.statistics_table'));
    }
}
