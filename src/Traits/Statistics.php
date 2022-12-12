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
            // if (sizeof($groups) > intval(2)) {
            //     throw new Exception('can not support too many groupBys, the largest number is 2 !');
            // }

            // 获取数据
            $data = $builder->get();

            // 补足缺失日期时，使用的填充数据
            $fillValue = [];
            foreach ($data[0]->getAttributes() as $key => $value) {
                if (in_array($key, $groups) && $key != $period) {
                    $fillValue[$key] = $value;
                } else {
                    $fillValue[$key] = 0;
                }
            }

            if (sizeof($groups) === intval(1)) {
                // 补足缺失日期
                return QueryResultProcessHelper::fillMissedDateAndChangeNullValueWithZero(
                    $data,
                    $period,
                    $occurredBetween,
                    $orderBy,
                    $fillValue
                );
            }

            // >= 2 次 groupBy, 二次 groupBy 的key
            $regroupKey = '';
            foreach ($groups as $group) {
                if ($group != $period) {
                    $regroupKey .= $group;
                }
            }

            // >2 次 groupby 需要使用合并在一起的 key，降低格式的维度
            if (sizeof($groups) > intval(2)) {
                foreach ($data as $index => $item) {
                    $implodedValue = '';
                    foreach ($groups as $group) {
                        if ($group != $period) {
                            $implodedValue .= $item[$group];
                        }
                    }
                    $data[$index][$regroupKey] = $implodedValue;
                }
            }

            // 分组为 二维数据
            $twoDdimensionalData = $data->groupBy($regroupKey);

            // 补足缺失日期
            $ret = (object)[];
            foreach ($twoDdimensionalData as $key => $item) {
                $fillValue[$regroupKey] = $item[0][$regroupKey];
                $processedData          = QueryResultProcessHelper::fillMissedDateAndChangeNullValueWithZero(
                    $item,
                    $period,
                    $occurredBetween,
                    $orderBy,
                    $fillValue
                );
                $ret->$key = $processedData;
            }
            return $ret;
        });

        return $builder;
    }
}
