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

        $builder->macro('summary', function (Builder $builder) {
            //获取参数
            $periodKey = static::addPrefix('period');
            $occurredBetweenKey = static::addPrefix('occurredBetween');
            $period = $this->$periodKey ?? strval("day");
            $occurredBetween = $this->$occurredBetweenKey ?? []; //

            $dateField       = 'created_at';
            $selectFields = QueryParamToSqlHelper::periodToSql($period, $dateField);

            // 增加时间段筛选条件。
            $builder->whereRaw(QueryParamToSqlHelper::transformWhere($occurredBetween, null, $dateField));

            // 按照时间分组。
            $builder->selectRaw($selectFields)->groupBy($period);

            // 处理排序问题，只支持按照 period 排序
            $orderBy = 'asc';
            $orders = $builder->getQuery()->orders;
            if ($orders == null) {
                $builder->orderBy($period, $orderBy);
            } elseif (sizeof($orders) == intval(1)) {
                if ($orders[0]['column'] != $period) {
                    throw new Exception('orderBy column error, orderBy column should same as period');
                }
            } else {
                throw new Exception('orderBy column error, too many orderBy');
            }

            // 获取 groups
            $groups = $builder->getQuery()->groups;
            if (sizeof($groups) > intval(2)) {
                throw new Exception('can not support too many groupBys, the largest number is 2 !');
            }

            // 获取数据
            $data = $builder->get();

            // 处理 2 次 groupBy 的情况，形成二维数组
            if (sizeof($groups) === intval(2)) {
                if ($groups[1] === $period) {
                    unset($groups[1]);
                } else {
                    unset($groups[0]);
                }
                $groupData = $data->groupBy($groups[0]);
            }

            // 补足确实日期
            $ret = (object)[];
            foreach ($groupData as $key => $item) {
                $processedData = QueryResultProcessHelper::fillMissedDateAndChangeNullValueWithZero(
                    $item,
                    $period,
                    $occurredBetween,
                    $orderBy
                );
                $ret->$key =  $processedData;
            }
            return $ret;
        });

        return $builder;
    }
}
