<?php

namespace Shanjing\LaravelStatistics\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Shanjing\LaravelStatistics\Helper\QueryParamCorrectHelper;
use Shanjing\LaravelStatistics\Helper\QueryParamToSqlHelper;
use Shanjing\LaravelStatistics\Helper\QueryResultProcessHelper;
use Carbon\Carbon;

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @author lou <lou@shanjing-inc.com>
     */
    public function newEloquentBuilder($query)
    {
        // 初始化 builder。
        $builder = parent::newEloquentBuilder($query);

        $builder->macro('period', function (Builder $builder, $period) {
            $property        = static::addPrefix('period');
            $this->$property = QueryParamCorrectHelper::correctPeriod($period);
            return $builder;
        });

        $builder->macro('occurredAt', function (Builder $builder, $occurredAt) {
            $property        = static::addPrefix('occurredBetween');
            $this->$property = QueryParamCorrectHelper::correctOccurredAt($occurredAt);
            return $builder;
        });

        $builder->macro('occurredBetween', function (Builder $builder, $occurredBetween) {
            $property        = static::addPrefix('occurredBetween');
            $this->$property = QueryParamCorrectHelper::correctOccurredBetween($occurredBetween);
            return $builder;
        });

        $builder->macro('dateFieldInfo', function (Builder $builder, $dateFieldInfo) {
            $property        = static::addPrefix('dateFieldInfo');
            $this->$property = $dateFieldInfo;
            return $builder;
        });

        $builder->macro('summary', function (Builder $builder) {
            //获取参数
            $periodKey          = static::addPrefix('period');
            $occurredBetweenKey = static::addPrefix('occurredBetween');
            $dateFieldInfoKey   = static::addPrefix('dateFieldInfo');
            $period             = $this->$periodKey ?? strval("day");
            $occurredBetween    = $this->$occurredBetweenKey ?? []; //
            $dateFieldInfo      = $this->$dateFieldInfoKey
                ?? ['dateField' => 'created_at', 'dateType' => 'DateTime', 'timezone' => '0'];

            $dateField    = $dateFieldInfo['dateField'];
            $selectFields = QueryParamToSqlHelper::periodToSql(
                $period,
                $dateField,
                $dateFieldInfo['dateType'],
                $dateFieldInfo['timezone']
            );

            // 增加时间段筛选条件。
            if ($dateFieldInfo['dateType'] == strval('TimeStamp')) {
                $builder->whereRaw(QueryParamToSqlHelper::transformWhere([
                    Carbon::parse($occurredBetween[0])->timestamp,
                    Carbon::parse($occurredBetween[1])->timestamp
                ], null, $dateField));
            } else {
                $builder->whereRaw(QueryParamToSqlHelper::transformWhere($occurredBetween, null, $dateField));
            }

            // 按照时间分组。
            $builder->selectRaw($selectFields)->groupBy($period);

            // 处理排序问题，只支持按照 period 排序
            $orderBy = 'asc';
            $orders  = $builder->getQuery()->orders;
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

            // 获取数据
            $data = $builder->get();

            // 只有一次 groupBy 的情况
            if (sizeof($groups) === intval(1)) {
                return $this->processOneGroupByData($data, $groups, $period, $occurredBetween, $orderBy);
            }

            // 大于两次 groupBy 的情况
            return $this->processManyGroupByData($data, $groups, $period, $occurredBetween, $orderBy);
        });

        return $builder;
    }



    /**
     * 只有 1 次 groupby 数据
     *
     * @param [type] $data
     * @param [type] $groups
     * @param [type] $period
     * @param [type] $occurredBetween
     * @param [type] $orderBy
     * @return void
     * @example
     * @author lou@shanjing-inc.com
     * @since 2022-12-13
     */
    private function processOneGroupByData($data, $groups, $period, $occurredBetween, $orderBy)
    {
        // 补足缺失日期
        return QueryResultProcessHelper::fillMissedDateAndChangeNullValueWithZero(
            $data,
            $period,
            $occurredBetween,
            $orderBy,
            $this->getFillItem($data[0], $groups, $period)
        );
    }

    /**
     * 大于 2 次 groupBy 的数据时，格式化成 二维数组返回
     *
     * @param [type] $data
     * @param [type] $groups
     * @param [type] $period
     * @param [type] $occurredBetween
     * @param [type] $orderBy
     * @return void
     * @example
     * @author lou@shanjing-inc.com
     * @since 2022-12-13
     */
    private function processManyGroupByData($data, $groups, $period, $occurredBetween, $orderBy)
    {
        // 分组使用的 key
        $groupKey = $this->getGroupKey($groups, $period);
        // 如果超过两次 groupBy， 重新处理数据
        if (sizeof($groups) > intval(2)) {
            $data = $this->addGroupKeyToData($data, $period, $groups, $groupKey);
        }

        // 使用 groupkey 格式化数据 [成二维数组]
        $data = $data->groupBy($groupKey);

        // 补足缺失日期
        $ret = (object)[];
        foreach ($data as $key => $value) {
            // 补足缺失的日期
            $resultArray = QueryResultProcessHelper::fillMissedDateAndChangeNullValueWithZero(
                $value,
                $period,
                $occurredBetween,
                $orderBy,
                $this->getFillItem($value[0], $groups, $period, $groupKey)
            );
            $ret->$key = $resultArray;
        }
        return $ret;
    }

    /**
     * 大于 2 次 groupby 时，需要使用一个 组合的 key，用于数据格式化，
     * 所以在这里添加个 groupKey 和 groupValue 到数据中，用于后面的格式化
     *
     * @param [type] $data
     * @param [type] $period
     * @param [type] $groups
     * @param [type] $regroupKey
     * @example
     * @author lou@shanjing-inc.com
     * @since 2022-12-12
     */
    private function addGroupKeyToData($data, $period, $groups, $groupKey)
    {
        foreach ($data as $index => $item) {
            $groupValue = '';
            foreach ($groups as $group) {
                if ($group != $period) {
                    $groupValue .= '-' . $item[$group];
                }
            }
            $data[$index][$groupKey] = $groupValue;
        }

        return $data;
    }

    /**
     * 多次 groupBy 时，group 使用的 key
     *
     * @param [type] $groups
     * @param [type] $period
     * @return void
     * @example
     * @author lou@shanjing-inc.com
     * @since 2022-12-13
     */
    private function getGroupKey($groups, $period)
    {
        $key = '';
        // >2 次 groupby 需要使用合并在一起的 key，降低格式的维度
        if (sizeof($groups) > intval(2)) {
            foreach ($groups as $group) {
                if ($group != $period) {
                    $key .= $group;
                }
            }

            return $key;
        }

        foreach ($groups as $group) {
            if ($group != $period) {
                $key = $group;
            }
        }
        return $key;
    }

    /**
     * 用来填充空白日期的数据
     *
     * @param [type] $exampleData
     * @param [type] $groups
     * @param [type] $period
     * @param [type] $groupKey
     * @return void
     * @example
     * @author lou@shanjing-inc.com
     * @since 2022-12-13
     */
    private function getFillItem($exampleData, $groups, $period, $groupKey = null)
    {
        $fillValue = [];
        foreach ($exampleData->getAttributes() as $k => $v) {
            if (in_array($k, $groups) && $k != $period) {
                $fillValue[$k] = $v;
            } elseif ($k == $groupKey && $groupKey != null) {
                $fillValue[$k] = $exampleData[$groupKey];
            } else {
                $fillValue[$k] = 0;
            }
        }

        return $fillValue;
    }
}
