<?php

namespace Shanjing\LaravelStatistics\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Shanjing\LaravelStatistics\Helper\QueryParamCorrectHelper;
use Shanjing\LaravelStatistics\Helper\QueryParamToSqlHelper;
use Shanjing\LaravelStatistics\Helper\QueryResultProcessHelper;

trait Statistics
{
    public static function addPrefix($key)
    {
        return 'statistics_property_' . $key;
    }

    /**
     * @param $query
     * @return mixed
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @author lou <lou@shanjing-inc.com>
     */
    public function newEloquentBuilder($query)
    {
        // 初始化 builder。
        $builder = parent::newEloquentBuilder($query);

        $builder->macro('period', function (Builder $builder, $period) {
            $property = static::addPrefix('period');
            $this->$property = QueryParamCorrectHelper::correctPeriod($period);
            return $builder;
        });

        $builder->macro('occurredAt', function (Builder $builder, $occurredAt) {
            $property = static::addPrefix('occurredBetween');
            $this->$property = QueryParamCorrectHelper::correctOccurredAt($occurredAt);
            return $builder;
        });

        $builder->macro('occurredBetween', function (Builder $builder, $occurredBetween) {
            $property = static::addPrefix('occurredBetween');
            $this->$property = QueryParamCorrectHelper::correctOccurredBetween($occurredBetween);
            return $builder;
        });

        $builder->macro('orderBy', function (Builder $builder, $orderBy) {
            $property = static::addPrefix('orderBy');
            $this->$property = QueryParamCorrectHelper::correctOrderBy($orderBy);
            return $builder;
        });

        $builder->macro('summary', function (Builder $builder) {
            //获取参数
            $periodKey = static::addPrefix('period');
            $orderByKey = static::addPrefix('orderBy');
            $occurredBetweenKey = static::addPrefix('occurredBetween');
            $period = $this->$periodKey ?? strval("day");
            $orderBy = $this->$orderByKey ?? strval("asc");
            $occurredBetween = $this->$occurredBetweenKey ?? []; //

            $dateField       = 'created_at';
            $selectFields = QueryParamToSqlHelper::periodToSql($period, $dateField);

            // orderBy
            $builder->orderBy($orderBy);

            // 增加时间段筛选条件。
            $builder->whereRaw(QueryParamToSqlHelper::transformWhere($occurredBetween, null, $dateField));

            // 按照时间分组。
            $builder->selectRaw($selectFields)->groupBy($period);

            return QueryResultProcessHelper::fillMissedDateAndChangeNullValueWithZero(
                $builder->get(),
                $period,
                $occurredBetween,
                $orderBy
            );
        });

        return $builder;
    }
}
